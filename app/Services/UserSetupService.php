<?php

namespace App\Services;

use App\Repositories\CredentialRepository;
use App\Repositories\SubscriptionPackageRepository;
use App\Repositories\UserSubscriptionRepository;
use Illuminate\Support\Str;

/**
 * Service untuk setup awal user:
 * - Auto-assign paket subscription gratis jika belum punya
 * - Generate S3 credentials jika belum punya
 *
 * Dipanggil setiap kali user mengakses dashboard.
 */
class UserSetupService
{
    /**
     * Pastikan user siap digunakan (punya subscription aktif + credentials).
     *
     * @return object subscription aktif milik user
     */
    public function ensureUserIsReady(int $userId): object
    {
        $activeSub = $this->ensureActiveSubscription($userId);
        $this->ensureCredentials($userId);

        return $activeSub;
    }

    /**
     * Pastikan user memiliki subscription aktif.
     * Jika belum, buat subscription gratis otomatis.
     */
    private function ensureActiveSubscription(int $userId): object
    {
        $activeSub = UserSubscriptionRepository::findActiveByUserId($userId);

        if ($activeSub) {
            return $activeSub;
        }

        // Cari paket gratis
        $freePackage = SubscriptionPackageRepository::findFreePackage();

        if (! $freePackage) {
            // Buat paket gratis jika belum ada
            $packageId = SubscriptionPackageRepository::create([
                'name' => 'Lite (Bronze)',
                'storage_limit_gb' => 5.00,
                'max_buckets' => 3,
                'vcpu_limit' => 1,
                'ram_limit_mb' => 512,
                'bandwidth_limit_gb' => 10,
                'price_per_month' => 0.00,
                'description' => 'Cocok untuk belajar dan testing awal.',
                'is_active' => true,
            ]);

            $freePackage = SubscriptionPackageRepository::findById($packageId);
        }

        $subId = UserSubscriptionRepository::create([
            'user_id' => $userId,
            'package_id' => $freePackage->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'status' => 'active',
        ]);

        return UserSubscriptionRepository::findById($subId);
    }

    /**
     * Pastikan user memiliki S3 credentials.
     * Jika belum, generate access_key dan secret_key baru.
     */
    private function ensureCredentials(int $userId): void
    {
        $credentials = CredentialRepository::findByUserId($userId);

        if ($credentials) {
            return;
        }

        CredentialRepository::create([
            'user_id' => $userId,
            'access_key' => 'MINI' . strtoupper(Str::random(12)),
            'secret_key' => Str::random(40),
        ]);
    }
}
