<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToCheckFileExistence;
use Illuminate\Support\Facades\Auth;
use App\Models\ObjectFile;
use App\Models\UserSubscription;
use App\Models\SubscriptionPackage;
use App\Models\Credential;
use App\Models\Resource;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    private function ensureUserSetup($user)
    {
        // 1. Ensure active subscription
        $activeSub = $user->subscriptions()->where('status', 'active')->first();
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
        // data dummy khusus halaman langganan
        return view('dashboard.subscription');
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
