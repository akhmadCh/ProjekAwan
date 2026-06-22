<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

/**
 * Repository untuk operasi raw SQL pada tabel objects.
 */
class ObjectRepository
{
    public static function findById(int $id): ?object
    {
        return DB::selectOne('SELECT * FROM objects WHERE id = ? AND deleted_at IS NULL', [$id]);
    }

    /**
     * Ambil semua object dalam sebuah bucket.
     */
    public static function findByBucketId(int $bucketId): array
    {
        return DB::select(
            'SELECT * FROM objects WHERE bucket_id = ? AND deleted_at IS NULL ORDER BY uploaded_at DESC',
            [$bucketId]
        );
    }

    /**
     * Total ukuran (MB) dari semua object dalam bucket-bucket tertentu.
     */
    public static function getTotalSizeMbByBucketIds(array $bucketIds): float
    {
        if (empty($bucketIds)) {
            return 0.0;
        }

        $placeholders = implode(',', array_fill(0, count($bucketIds), '?'));

        $result = DB::selectOne(
            "SELECT COALESCE(SUM(size_mb), 0) as total_mb FROM objects WHERE bucket_id IN ({$placeholders}) AND deleted_at IS NULL",
            $bucketIds
        );

        return (float) ($result->total_mb ?? 0);
    }

    /**
     * Insert object baru, return ID.
     */
    public static function create(array $data): int
    {
        DB::insert(
            'INSERT INTO objects (bucket_id, object_key, original_filename, size_mb, mime_type, storage_path, uploaded_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())',
            [
                $data['bucket_id'],
                $data['object_key'],
                $data['original_filename'] ?? null,
                $data['size_mb'],
                $data['mime_type'],
                $data['storage_path'],
            ]
        );

        return (int) DB::getPdo()->lastInsertId();
    }

    /**
     * Soft-delete object.
     */
    public static function softDelete(int $id): bool
    {
        return DB::update('UPDATE objects SET deleted_at = NOW() WHERE id = ?', [$id]) > 0;
    }
}
