<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

/**
 * Repository untuk operasi raw SQL pada tabel user_subscriptions.
 */
class UserSubscriptionRepository
{
    public static function findById(int $id): ?object
    {
        return DB::selectOne('SELECT * FROM user_subscriptions WHERE id = ?', [$id]);
    }

    /**
     * Cari subscription aktif terbaru milik user.
     */
    public static function findActiveByUserId(int $userId): ?object
    {
        return DB::selectOne(
            "SELECT * FROM user_subscriptions WHERE user_id = ? AND status = 'active' ORDER BY id DESC LIMIT 1",
            [$userId]
        );
    }

    /**
     * Insert subscription baru, return ID.
     */
    public static function create(array $data): int
    {
        DB::insert(
            'INSERT INTO user_subscriptions (user_id, package_id, start_date, end_date, status, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, NOW(), NOW())',
            [
                $data['user_id'],
                $data['package_id'],
                $data['start_date'],
                $data['end_date'],
                $data['status'] ?? 'active',
            ]
        );

        return (int) DB::getPdo()->lastInsertId();
    }

    /**
     * Cancel subscription: set status='cancelled' dan end_date=sekarang.
     */
    public static function cancelById(int $id): bool
    {
        return DB::update(
            "UPDATE user_subscriptions SET status = 'cancelled', end_date = CURDATE(), updated_at = NOW() WHERE id = ?",
            [$id]
        ) > 0;
    }

    /**
     * Update subscription.
     */
    public static function update(int $id, array $data): bool
    {
        $sets = [];
        $values = [];

        foreach ($data as $column => $value) {
            $sets[] = "{$column} = ?";
            $values[] = $value;
        }

        $sets[] = 'updated_at = NOW()';
        $values[] = $id;

        return DB::update(
            'UPDATE user_subscriptions SET ' . implode(', ', $sets) . ' WHERE id = ?',
            $values
        ) > 0;
    }
}
