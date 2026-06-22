<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

/**
 * Repository untuk operasi raw SQL pada tabel buckets.
 */
class BucketRepository
{
    public static function findById(int $id): ?object
    {
        return DB::selectOne('SELECT * FROM buckets WHERE id = ? AND deleted_at IS NULL', [$id]);
    }

    public static function findByResourceId(int $resourceId): ?object
    {
        return DB::selectOne('SELECT * FROM buckets WHERE resource_id = ? AND deleted_at IS NULL', [$resourceId]);
    }

    public static function findByBucketName(string $name): ?object
    {
        return DB::selectOne('SELECT * FROM buckets WHERE bucket_name = ? AND deleted_at IS NULL', [$name]);
    }

    /**
     * Ambil semua bucket milik user (JOIN resources).
     */
    public static function getByUserId(int $userId): array
    {
        return DB::select(
            'SELECT b.*, r.name as resource_name, r.status as resource_status, r.user_id
             FROM buckets b
             INNER JOIN resources r ON r.id = b.resource_id
             WHERE r.user_id = ? AND r.deleted_at IS NULL AND b.deleted_at IS NULL
             ORDER BY b.created_at DESC',
            [$userId]
        );
    }

    /**
     * Insert bucket baru, return ID.
     */
    public static function create(array $data): int
    {
        DB::insert(
            'INSERT INTO buckets (resource_id, bucket_name, storage_limit_gb, used_storage_gb, object_count, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, NOW(), NOW())',
            [
                $data['resource_id'],
                $data['bucket_name'],
                $data['storage_limit_gb'] ?? 0,
                $data['used_storage_gb'] ?? 0,
                $data['object_count'] ?? 0,
            ]
        );

        return (int) DB::getPdo()->lastInsertId();
    }

    /**
     * Perbarui statistik storage bucket (hitung ulang dari objects).
     */
    public static function updateStorageStats(int $bucketId): void
    {
        $stats = DB::selectOne(
            'SELECT COALESCE(SUM(size_mb), 0) as total_mb, COUNT(*) as total_count
             FROM objects
             WHERE bucket_id = ? AND deleted_at IS NULL',
            [$bucketId]
        );

        $usedGb = round(($stats->total_mb ?? 0) / 1024, 4);

        DB::update(
            'UPDATE buckets SET used_storage_gb = ?, object_count = ?, updated_at = NOW() WHERE id = ?',
            [$usedGb, $stats->total_count ?? 0, $bucketId]
        );
    }

    /**
     * Ambil semua bucket_id milik user.
     */
    public static function getIdsByUserId(int $userId): array
    {
        $rows = DB::select(
            'SELECT b.id
             FROM buckets b
             INNER JOIN resources r ON r.id = b.resource_id
             WHERE r.user_id = ? AND r.deleted_at IS NULL AND b.deleted_at IS NULL',
            [$userId]
        );

        return array_map(fn($row) => $row->id, $rows);
    }

    /**
     * Hitung jumlah bucket aktif milik user.
     */
    public static function countByUserId(int $userId): int
    {
        $result = DB::selectOne(
            'SELECT COUNT(*) as total
             FROM buckets b
             INNER JOIN resources r ON r.id = b.resource_id
             WHERE r.user_id = ? AND r.deleted_at IS NULL AND b.deleted_at IS NULL',
            [$userId]
        );

        return (int) ($result->total ?? 0);
    }
}
