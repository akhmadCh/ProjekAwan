<?php

namespace App\Services;

use App\Repositories\SubscriptionOrderRepository;
use App\Repositories\SubscriptionPackageRepository;
use App\Repositories\UserSubscriptionRepository;
use Illuminate\Support\Facades\DB;
use RuntimeException;

use App\Models\User;

/**
 * Service untuk lifecycle subscription:
 * - Validasi upgrade
 * - Buat order upgrade
 * - Aktivasi setelah pembayaran
 * - Sinkronisasi status order
 */
class SubscriptionService
{
    public function __construct(
        private MidtransService $midtransService
    ) {}

    /**
     * Validasi apakah upgrade bisa dilakukan.
     *
     * @return array ['ok' => bool, 'message' => string]
     */
    public function validateUpgrade(int $userId, int $targetPackageId, float $usedGb): array
    {
        $activeSub = UserSubscriptionRepository::findActiveByUserId($userId);
        if (! $activeSub) {
            return ['ok' => false, 'message' => 'Tidak ada subscription aktif.'];
        }

        $currentPackage = SubscriptionPackageRepository::findById($activeSub->package_id);
        $targetPackage = SubscriptionPackageRepository::findById($targetPackageId);

        if (! $targetPackage) {
            return ['ok' => false, 'message' => 'Paket tidak ditemukan.'];
        }

        if ($targetPackage->id === $currentPackage->id) {
            return ['ok' => false, 'message' => 'Paket yang dipilih sama dengan paket aktif saat ini.'];
        }

        if ($targetPackage->price_per_month <= $currentPackage->price_per_month) {
            return ['ok' => false, 'message' => 'Untuk sementara hanya upgrade ke paket yang lebih tinggi yang bisa diproses langsung.'];
        }

        if ($targetPackage->storage_limit_gb < $usedGb) {
            return ['ok' => false, 'message' => 'Quota paket tujuan terlalu kecil untuk pemakaian storage saat ini.'];
        }

        return ['ok' => true, 'message' => 'OK'];
    }

    /**
     * Buat order upgrade subscription baru.
     *
     * @return array data order + snap_token untuk checkout
     * @throws RuntimeException jika validasi gagal atau Midtrans error
     */
    public function createUpgradeOrder(int $userId, int $packageId, float $usedGb): array
    {
        $validation = $this->validateUpgrade($userId, $packageId, $usedGb);
        if (! $validation['ok']) {
            throw new RuntimeException($validation['message']);
        }

        $targetPackage = SubscriptionPackageRepository::findById($packageId);

        // $user = DB::selectOne('SELECT * FROM users WHERE id = ?', [$userId]);
        $user = User::find($userId);

        $orderId = $this->midtransService->generateOrderId($userId, $packageId);

        // Insert order
        $id = SubscriptionOrderRepository::create([
            'user_id' => $userId,
            'subscription_package_id' => $targetPackage->id,
            'order_id' => $orderId,
            'gross_amount' => $targetPackage->price_per_month,
            'transaction_status' => 'pending',
        ]);

        $order = SubscriptionOrderRepository::findById($id);

        // Buat transaksi Midtrans
        try {
            // // Buat object sementara untuk Midtrans
            // $orderObj = (object) [
            //     'order_id' => $order->order_id,
            //     'gross_amount' => $order->gross_amount,
            //     'subscription_package_id' => $order->subscription_package_id,
            // ];
            // $orderObj->package = $targetPackage;
            // $orderObj->user = $user;

            // $snapResponse = $this->midtransService->createSnapTransaction($orderObj);

            // Langsung pasang relasi tambahan ke model asli agar tipe data tidak rusak
            $order->package = $targetPackage;
            $order->user = $user;

            // Kirim model asli yang tipenya valid (SubscriptionOrder)
            $snapResponse = $this->midtransService->createSnapTransaction($order);
        } catch (\Throwable $e) {
            SubscriptionOrderRepository::update($id, [
                'transaction_status' => 'failed',
                'raw_payload' => json_encode(['error' => $e->getMessage()]),
            ]);

            throw new RuntimeException($e->getMessage());
        }

        SubscriptionOrderRepository::update($id, [
            'snap_token' => $snapResponse['token'] ?? null,
            'redirect_url' => $snapResponse['redirect_url'] ?? null,
            'raw_payload' => json_encode($snapResponse),
        ]);

        return [
            'message' => 'Order upgrade berhasil dibuat.',
            'order_id' => $order->order_id,
            'snap_token' => $snapResponse['token'] ?? null,
            'redirect_url' => $snapResponse['redirect_url'] ?? null,
        ];
    }

    /**
     * Aktivasi subscription setelah pembayaran berhasil.
     * Menggunakan database transaction untuk konsistensi data.
     */
    public function activateOrder(int $orderId, array $payload, ?string $transactionStatus = null, ?string $fraudStatus = null): void
    {
        DB::transaction(function () use ($orderId, $payload, $transactionStatus, $fraudStatus) {
            $order = SubscriptionOrderRepository::findById($orderId);
            if (! $order) {
                return;
            }

            // Cek apakah sudah diaktivasi sebelumnya
            if ($order->user_subscription_id && $order->transaction_status === 'paid') {
                $existingSub = UserSubscriptionRepository::findById($order->user_subscription_id);
                if ($existingSub
                    && (int) $existingSub->package_id === (int) $order->subscription_package_id
                    && $existingSub->status === 'active'
                ) {
                    return; // Sudah aktif, skip
                }
            }

            // Cancel subscription lama
            $activeSub = UserSubscriptionRepository::findActiveByUserId($order->user_id);
            if ($activeSub) {
                UserSubscriptionRepository::cancelById($activeSub->id);
            }

            // Buat subscription baru
            $newSubId = UserSubscriptionRepository::create([
                'user_id' => $order->user_id,
                'package_id' => $order->subscription_package_id,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonth()->toDateString(),
                'status' => 'active',
            ]);

            // Update order
            SubscriptionOrderRepository::update($orderId, [
                'user_subscription_id' => $newSubId,
                'paid_at' => now()->toDateTimeString(),
                'transaction_status' => $transactionStatus ?? 'paid',
                'fraud_status' => $fraudStatus,
                'raw_payload' => json_encode($payload),
            ]);
        });
    }

    /**
     * Sinkronisasi status order dari Midtrans.
     *
     * @return array ['order_status' => string, 'subscription_active' => bool]
     */
    public function syncOrderStatus(int $userId, string $orderId): array
    {
        $order = SubscriptionOrderRepository::findByOrderIdAndUserId($orderId, $userId);
        if (! $order) {
            throw new RuntimeException('Order tidak ditemukan.');
        }

        $statusPayload = $this->midtransService->getTransactionStatus($order->order_id);
        $transactionStatus = $statusPayload['transaction_status'] ?? 'pending';
        $fraudStatus = $statusPayload['fraud_status'] ?? null;

        $mappedStatus = $this->mapMidtransStatus($transactionStatus, $fraudStatus);

        SubscriptionOrderRepository::update($order->id, [
            'payment_type' => $statusPayload['payment_type'] ?? $order->payment_type,
            'transaction_status' => $mappedStatus,
            'fraud_status' => $fraudStatus,
            'raw_payload' => json_encode($statusPayload),
        ]);

        if ($mappedStatus === 'paid') {
            $this->activateOrder($order->id, $statusPayload, $mappedStatus, $fraudStatus);
        }

        return [
            'message' => 'Order status berhasil disinkronkan.',
            'order_status' => $mappedStatus,
            'subscription_active' => $mappedStatus === 'paid',
        ];
    }

    /**
     * Handle notifikasi Midtrans (webhook).
     */
    public function handleNotification(array $payload): void
    {
        if (! $this->midtransService->isValidNotificationSignature($payload)) {
            throw new RuntimeException('Invalid signature');
        }

        $order = SubscriptionOrderRepository::findByOrderId($payload['order_id'] ?? '');
        if (! $order) {
            throw new RuntimeException('Order tidak ditemukan.');
        }

        $transactionStatus = $payload['transaction_status'] ?? 'pending';
        $fraudStatus = $payload['fraud_status'] ?? null;
        $mappedStatus = $this->mapMidtransStatus($transactionStatus, $fraudStatus);

        SubscriptionOrderRepository::update($order->id, [
            'payment_type' => $payload['payment_type'] ?? $order->payment_type,
            'transaction_status' => $mappedStatus,
            'fraud_status' => $fraudStatus,
            'raw_payload' => json_encode($payload),
            'expired_at' => $payload['expiry_time'] ?? $order->expired_at,
        ]);

        if ($mappedStatus === 'paid') {
            $this->activateOrder($order->id, $payload, 'paid', $fraudStatus);
        }
    }

    /**
     * Map status transaksi Midtrans ke status internal.
     */
    private function mapMidtransStatus(string $transactionStatus, ?string $fraudStatus): string
    {
        return match ($transactionStatus) {
            'settlement' => 'paid',
            'capture' => $fraudStatus === 'accept' ? 'paid' : 'pending',
            'deny', 'cancel', 'failure' => 'failed',
            'expire' => 'expired',
            'refund', 'partial_refund', 'chargeback' => 'refunded',
            default => 'pending',
        };
    }
}
