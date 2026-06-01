<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToCheckFileExistence;
use Illuminate\Support\Facades\Auth;
use App\Models\ObjectFile;
use App\Models\SubscriptionOrder;
use App\Models\UserSubscription;
use App\Models\SubscriptionPackage;
use App\Models\Credential;
use App\Models\Resource;
use App\Services\MidtransService;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    private function ensureUserSetup($user)
    {
        // 1. Ensure active subscription
        $activeSub = $user->subscriptions()->where('status', 'active')->latest('id')->first();
        if (!$activeSub) {
            $litePackage = SubscriptionPackage::where('name', 'like', '%Lite%')
                ->orWhere('name', 'like', '%Bronze%')
                ->first();
            if (!$litePackage) {
                $litePackage = SubscriptionPackage::create([
                    'name' => 'Lite (Bronze)',
                    'storage_quota_gb' => 5.00,
                    'price_per_month' => 0.00,
                    'description' => 'Cocok untuk belajar dan testing awal.'
                ]);
            }
            $activeSub = UserSubscription::create([
                'user_id' => $user->id,
                'package_id' => $litePackage->id,
                'start_date' => now(),
                'end_date' => now()->addYears(1),
                'status' => 'active',
            ]);
        }

        // 2. Ensure credentials
        if (!$user->credentials) {
            Credential::create([
                'user_id' => $user->id,
                'access_key' => 'MINI' . strtoupper(Str::random(12)),
                'secret_key' => Str::random(40),
            ]);
            $user->load('credentials');
        }

        return $activeSub;
    }

    private function getUsedStorageGb($user): float
    {
        $resourceIds = $user->resources()->pluck('id');
        $totalUsedMb = ObjectFile::whereIn('resource_id', $resourceIds)->sum('size_mb');

        return round($totalUsedMb / 1024, 4);
    }

    public function index()
    {
        $user = auth()->user();
        $activeSub = $this->ensureUserSetup($user);
        $package = $activeSub->package;

        // Dashboard data
        $userData = [
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'joinedDate' => $user->created_at->format('M d, Y'),
            'status' => 'Active',
        ];

        $subscriptionData = [
            'plan' => $package->name,
            'status' => ucfirst($activeSub->status),
            'storage' => $package->storage_quota_gb . ' GB',
            'renewalDate' => \Carbon\Carbon::parse($activeSub->end_date)->format('M d, Y'),
            'price' => 'Rp ' . number_format($package->price_per_month, 0, ',', '.'),
        ];

        // Total used storage in GB across all user's buckets
        $resourceIds = $user->resources()->pluck('id');
        $totalUsedMb = ObjectFile::whereIn('resource_id', $resourceIds)->sum('size_mb');
        $usedGB = round($totalUsedMb / 1024, 4);

        $totalQuotaGB = $package->storage_quota_gb;
        $remainingGB = max(0, $totalQuotaGB - $usedGB);
        $percentage = $totalQuotaGB > 0 ? round(($usedGB / $totalQuotaGB) * 100) : 0;
        $percentage = min(100, $percentage);

        $storageData = [
            'used' => $usedGB,
            'total' => $totalQuotaGB,
            'remaining' => $remainingGB,
            'percentage' => $percentage,
        ];

        $credentialsData = [
            'accessKey' => $user->credentials->access_key,
            'secretKey' => $user->credentials->secret_key,
        ];

        return view('dashboard.index', compact('userData', 'subscriptionData', 'storageData', 'credentialsData'));
    }

    public function storage()
    {
        $user = auth()->user();
        $activeSub = $this->ensureUserSetup($user);
        $package = $activeSub->package;
        $totalQuotaGB = $package->storage_quota_gb;

        $resources = $user->resources()->with('objects')->get();

        $totalUsedMb = 0;
        foreach ($resources as $resource) {
            $totalUsedMb += $resource->objects->sum('size_mb');
        }
        $usedGB = round($totalUsedMb / 1024, 4);
        $remainingGB = max(0, $totalQuotaGB - $usedGB);
        $percentage = $totalQuotaGB > 0 ? round(($usedGB / $totalQuotaGB) * 100) : 0;
        $percentage = min(100, $percentage);

        $storageData = [
            'used' => $usedGB,
            'total' => $totalQuotaGB,
            'remaining' => $remainingGB,
            'percentage' => $percentage,
            'buckets_count' => $resources->count(),
        ];

        return view('dashboard.storage', compact('storageData', 'resources'));
    }

    public function subscription()
    {
        $user = auth()->user();
        $activeSub = $this->ensureUserSetup($user);
        $currentPackage = $activeSub->package;
        $usedGB = $this->getUsedStorageGb($user);

        $packages = SubscriptionPackage::orderBy('storage_quota_gb')->get();
        $orders = SubscriptionOrder::with('package')
            ->where('user_id', $user->id)
            ->latest('id')
            ->limit(5)
            ->get();

        return view('dashboard.subscription', compact('activeSub', 'currentPackage', 'packages', 'orders', 'usedGB'));
    }

    public function createSubscriptionOrder(Request $request, MidtransService $midtransService)
    {
        $user = auth()->user();
        $activeSub = $this->ensureUserSetup($user);
        $currentPackage = $activeSub->package;

        $validated = $request->validate([
            'package_id' => ['required', 'exists:subscription_packages,id'],
        ]);

        $targetPackage = SubscriptionPackage::findOrFail($validated['package_id']);
        $usedGB = $this->getUsedStorageGb($user);

        if ($targetPackage->id === $currentPackage->id) {
            return response()->json([
                'message' => 'Paket yang dipilih sama dengan paket aktif saat ini.',
            ], 422);
        }

        if ($targetPackage->price_per_month <= $currentPackage->price_per_month) {
            return response()->json([
                'message' => 'Untuk sementara hanya upgrade ke paket yang lebih tinggi yang bisa diproses langsung.',
            ], 422);
        }

        if ($targetPackage->storage_quota_gb < $usedGB) {
            return response()->json([
                'message' => 'Quota paket tujuan terlalu kecil untuk pemakaian storage saat ini.',
            ], 422);
        }

        $orderId = $midtransService->generateOrderId($user->id, $targetPackage->id);

        $order = SubscriptionOrder::create([
            'user_id' => $user->id,
            'subscription_package_id' => $targetPackage->id,
            'order_id' => $orderId,
            'gross_amount' => $targetPackage->price_per_month,
            'transaction_status' => 'pending',
        ]);

        try {
            $snapResponse = $midtransService->createSnapTransaction($order);
        } catch (\Throwable $exception) {
            $order->update([
                'transaction_status' => 'failed',
                'raw_payload' => ['error' => $exception->getMessage()],
            ]);

            return response()->json([
                'message' => $exception->getMessage(),
            ], 502);
        }

        $order->update([
            'snap_token' => $snapResponse['token'] ?? null,
            'redirect_url' => $snapResponse['redirect_url'] ?? null,
            'raw_payload' => $snapResponse,
        ]);

        return response()->json([
            'message' => 'Order upgrade berhasil dibuat.',
            'order_id' => $order->order_id,
            'snap_token' => $order->snap_token,
            'redirect_url' => $order->redirect_url,
        ]);
    }

    private function activateOrderSubscription(SubscriptionOrder $order, array $payload, ?string $transactionStatus = null, ?string $fraudStatus = null): void
    {
        DB::transaction(function () use ($order, $payload, $transactionStatus, $fraudStatus) {
            $order->refresh();

            $linkedSubscription = $order->user_subscription_id
                ? UserSubscription::find($order->user_subscription_id)
                : null;

            if (
                $linkedSubscription
                && $order->transaction_status === 'paid'
                && (int) $linkedSubscription->package_id === (int) $order->subscription_package_id
                && $linkedSubscription->status === 'active'
            ) {
                return;
            }

            $user = $order->user()->lockForUpdate()->first();
            $currentActiveSubscription = $user->subscriptions()
                ->where('status', 'active')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if ($currentActiveSubscription) {
                $currentActiveSubscription->update([
                    'status' => 'canceled',
                    'end_date' => now(),
                ]);
            }

            $newSubscription = UserSubscription::create([
                'user_id' => $user->id,
                'package_id' => $order->subscription_package_id,
                'start_date' => now(),
                'end_date' => now()->addMonth(),
                'status' => 'active',
            ]);

            $order->update([
                'user_subscription_id' => $newSubscription->id,
                'paid_at' => now(),
                'transaction_status' => $transactionStatus ?? 'paid',
                'fraud_status' => $fraudStatus,
                'raw_payload' => $payload,
            ]);
        });
    }

    public function syncSubscriptionOrder(Request $request, MidtransService $midtransService)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'order_id' => ['required', 'string'],
        ]);

        $order = SubscriptionOrder::where('order_id', $validated['order_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        $statusPayload = $midtransService->getTransactionStatus($order->order_id);
        $transactionStatus = $statusPayload['transaction_status'] ?? 'pending';
        $fraudStatus = $statusPayload['fraud_status'] ?? null;

        $mappedStatus = match ($transactionStatus) {
            'settlement' => 'paid',
            'capture' => $fraudStatus === 'accept' ? 'paid' : 'pending',
            'deny', 'cancel', 'failure' => 'failed',
            'expire' => 'expired',
            'refund', 'partial_refund', 'chargeback' => 'refunded',
            default => 'pending',
        };

        $order->update([
            'payment_type' => $statusPayload['payment_type'] ?? $order->payment_type,
            'transaction_status' => $mappedStatus,
            'fraud_status' => $fraudStatus,
            'raw_payload' => $statusPayload,
        ]);

        if ($mappedStatus === 'paid') {
            $this->activateOrderSubscription($order, $statusPayload, $mappedStatus, $fraudStatus);
        }

        return response()->json([
            'message' => 'Order status berhasil disinkronkan.',
            'order_status' => $mappedStatus,
            'subscription_active' => $mappedStatus === 'paid',
        ]);
    }

    public function handleMidtransNotification(Request $request, MidtransService $midtransService)
    {
        $payload = $request->all();

        if (! $midtransService->isValidNotificationSignature($payload)) {
            return response()->json([
                'message' => 'Invalid signature',
            ], 403);
        }

        $order = SubscriptionOrder::where('order_id', $payload['order_id'] ?? null)->firstOrFail();
        $transactionStatus = $payload['transaction_status'] ?? 'pending';
        $fraudStatus = $payload['fraud_status'] ?? null;

        $mappedStatus = match ($transactionStatus) {
            'settlement' => 'paid',
            'capture' => $fraudStatus === 'accept' ? 'paid' : 'pending',
            'deny', 'cancel', 'failure' => 'failed',
            'expire' => 'expired',
            'refund', 'partial_refund', 'chargeback' => 'refunded',
            default => 'pending',
        };

        $order->update([
            'payment_type' => $payload['payment_type'] ?? $order->payment_type,
            'transaction_status' => $mappedStatus,
            'fraud_status' => $fraudStatus,
            'raw_payload' => $payload,
            'expired_at' => $payload['expiry_time'] ?? $order->expired_at,
        ]);

        if ($mappedStatus !== 'paid') {
            return response()->json(['message' => 'Notification received']);
        }

        $this->activateOrderSubscription($order, $payload, 'paid', $fraudStatus);

        return response()->json(['message' => 'Notification processed']);
    }

    public function storeBucket(Request $request)
    {
        $user = auth()->user();
        $activeSub = $this->ensureUserSetup($user);

        $request->validate([
            'bucket_name' => [
                'required',
                'string',
                'min:3',
                'max:63',
                'regex:/^[a-z0-9.-]+$/', // S3 bucket name standards
                'unique:resources,name',
            ],
        ]);

        $bucketName = $request->input('bucket_name');

        try {
            // 1. Create bucket physically in MiniStack S3
            $s3Client = Storage::disk('s3')->getClient();
            $s3Client->createBucket([
                'Bucket' => $bucketName,
            ]);

            // 2. Create the resource record in the DB
            Resource::create([
                'user_id' => $user->id,
                'subscription_id' => $activeSub->id,
                'name' => $bucketName,
                'type' => 'object_storage',
                'status' => 'active',
            ]);

            return redirect()->route('dashboard.storage')->with('success', "Bucket '{$bucketName}' berhasil dibuat!");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['bucket_name' => 'Gagal membuat bucket di MiniStack: ' . $e->getMessage()]);
        }
    }

    public function uploadObject(Request $request)
    {
        // Validasi input file dan resource_id
        $request->validate([
            'resource_id' => 'required|exists:resources,id',
            'dokumen' => 'required|file|max:20480', // Maksimal 20MB
        ]);

        $file = $request->file('dokumen');
        $userId = Auth::id();

        // Cari resource (bucket) milik user
        $resource = Resource::where('user_id', $userId)->findOrFail($request->input('resource_id'));

        // Atur bucket target secara dinamis ke disk s3
        config(['filesystems.disks.s3.bucket' => $resource->name]);

        // Logika Isolate: Kelompokkan file ke dalam folder berdasarkan ID User di MiniStack S3
        $targetPath = 'user-' . $userId . '/' . $file->getClientOriginalName();

        // Tembak langsung ke MiniStack
        Storage::disk('s3')->put($targetPath, file_get_contents($file));

        // Hitung ukuran dalam MB
        $sizeMb = round($file->getSize() / (1024 * 1024), 4);

        // Simpan ke database dengan mapping kolom tabel objects yang benar
        ObjectFile::create([
            'resource_id' => $resource->id,
            'key' => $targetPath,
            'size_mb' => $sizeMb,
            'mime_type' => $file->getClientMimeType(),
            'storage_path' => $targetPath,
        ]);

        return redirect()->back()->with('success', 'File berhasil disimpan di MiniStack Cloud Emulator!');
    }
}
