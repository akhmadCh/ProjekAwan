<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

/**
 * Repository untuk operasi raw SQL pada tabel subscription_packages.
 */
class SubscriptionPackageRepository
{
    public static function findById(int $id): ?object
    {
        return DB::selectOne('SELECT * FROM subscription_packages WHERE id = ?', [$id]);
    }

    /**
     * Ambil semua paket aktif, diurutkan berdasarkan storage_limit_gb.
     */
    public static function getAllActive(): array
    {
        return DB::select('SELECT * FROM subscription_packages WHERE is_active = 1 ORDER BY storage_limit_gb ASC');
    }

    /**
     * Ambil semua paket diurutkan berdasarkan storage_limit_gb.
     */
    public static function getOrderedByStorage(): array
    {
        return DB::select('SELECT * FROM subscription_packages ORDER BY storage_limit_gb ASC');
    }

    /**
     * Cari paket gratis/Lite untuk auto-assign.
     */
    public static function findFreePackage(): ?object
    {
        return DB::selectOne(
            "SELECT * FROM subscription_packages WHERE name LIKE '%Lite%' OR name LIKE '%Bronze%' LIMIT 1"
        );
    }

    /**
     * Insert paket baru, return ID yang baru dibuat.
     */
    public static function create(array $data): int
    {
        DB::insert(
            'INSERT INTO subscription_packages (name, storage_limit_gb, max_buckets, vcpu_limit, ram_limit_mb, bandwidth_limit_gb, price_per_month, description, is_active, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
            [
                $data['name'],
                $data['storage_limit_gb'],
                $data['max_buckets'] ?? 5,
                $data['vcpu_limit'] ?? 0,
                $data['ram_limit_mb'] ?? 0,
                $data['bandwidth_limit_gb'] ?? 0,
                $data['price_per_month'],
                $data['description'] ?? null,
                $data['is_active'] ?? true,
            ]
        );

        return (int) DB::getPdo()->lastInsertId();
    }
}
