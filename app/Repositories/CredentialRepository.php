<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

/**
 * Repository untuk operasi raw SQL pada tabel credentials.
 */
class CredentialRepository
{
    public static function findByUserId(int $userId): ?object
    {
        return DB::selectOne('SELECT * FROM credentials WHERE user_id = ?', [$userId]);
    }

    /**
     * Insert credential baru, return ID.
     */
    public static function create(array $data): int
    {
        DB::insert(
            'INSERT INTO credentials (user_id, access_key, secret_key, created_at, updated_at)
             VALUES (?, ?, ?, NOW(), NOW())',
            [
                $data['user_id'],
                $data['access_key'],
                $data['secret_key'],
            ]
        );

        return (int) DB::getPdo()->lastInsertId();
    }
}
