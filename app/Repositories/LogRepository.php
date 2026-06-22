<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

/**
 * Repository untuk operasi raw SQL pada tabel logs.
 */
class LogRepository
{
    /**
     * Insert log entry baru, return ID.
     */
    public static function create(array $data): int
    {
        DB::insert(
            'INSERT INTO logs (user_id, action, target_type, target_id, description, logged_at)
             VALUES (?, ?, ?, ?, ?, NOW())',
            [
                $data['user_id'],
                $data['action'],
                $data['target_type'],
                $data['target_id'],
                $data['description'] ?? null,
            ]
        );

        return (int) DB::getPdo()->lastInsertId();
    }

    /**
     * Ambil log terbaru milik user.
     */
    public static function getByUserId(int $userId, int $limit = 50): array
    {
        return DB::select(
            'SELECT * FROM logs WHERE user_id = ? ORDER BY logged_at DESC LIMIT ?',
            [$userId, $limit]
        );
    }
}
