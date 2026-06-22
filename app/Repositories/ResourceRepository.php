<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

/**
 * Repository untuk operasi raw SQL pada tabel resources.
 */
class ResourceRepository
{
    public static function findById(int $id): ?object
    {
        return DB::selectOne('SELECT * FROM resources WHERE id = ? AND deleted_at IS NULL', [$id]);
    }

    /**
     * Cari resource milik user tertentu berdasarkan ID.
     */
    public static function findByUserIdAndId(int $userId, int $resourceId): ?object
    {
        return DB::selectOne(
            'SELECT * FROM resources WHERE id = ? AND user_id = ? AND deleted_at IS NULL',
            [$resourceId, $userId]
        );
    }

    /**
     * Ambil semua resource milik user (belum di-soft-delete).
     */
    public static function findByUserId(int $userId): array
    {
        return DB::select(
            'SELECT * FROM resources WHERE user_id = ? AND deleted_at IS NULL ORDER BY created_at DESC',
            [$userId]
        );
    }

    /**
     * Ambil resource berdasarkan type.
     */
    public static function findByUserIdAndType(int $userId, string $type): array
    {
        return DB::select(
            'SELECT * FROM resources WHERE user_id = ? AND type = ? AND deleted_at IS NULL ORDER BY created_at DESC',
            [$userId, $type]
        );
    }

    /**
     * Ambil semua resource_id milik user.
     */
    public static function getIdsByUserId(int $userId): array
    {
        $rows = DB::select(
            'SELECT id FROM resources WHERE user_id = ? AND deleted_at IS NULL',
            [$userId]
        );

        return array_map(fn($row) => $row->id, $rows);
    }

    /**
     * Insert resource baru, return ID.
     */
    public static function create(array $data): int
    {
        DB::insert(
            'INSERT INTO resources (user_id, user_subscription_id, name, type, status, metadata, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())',
            [
                $data['user_id'],
                $data['user_subscription_id'],
                $data['name'],
                $data['type'] ?? 'storage_bucket',
                $data['status'] ?? 'pending',
                isset($data['metadata']) ? json_encode($data['metadata']) : null,
            ]
        );

        return (int) DB::getPdo()->lastInsertId();
    }

    /**
     * Update status resource.
     */
    public static function updateStatus(int $id, string $status): bool
    {
        return DB::update(
            'UPDATE resources SET status = ?, updated_at = NOW() WHERE id = ?',
            [$status, $id]
        ) > 0;
    }

    /**
     * Update metadata resource.
     */
    public static function updateMetadata(int $id, array $metadata): bool
    {
        return DB::update(
            'UPDATE resources SET metadata = ?, updated_at = NOW() WHERE id = ?',
            [json_encode($metadata), $id]
        ) > 0;
    }

    /**
     * Soft-delete resource.
     */
    public static function softDelete(int $id): bool
    {
        return DB::update(
            'UPDATE resources SET deleted_at = NOW(), updated_at = NOW() WHERE id = ?',
            [$id]
        ) > 0;
    }
}
