<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

/**
 * Repository untuk operasi raw SQL pada tabel subscription_orders.
 * Tabel ini tidak ada di ERD tapi dipertahankan untuk fitur pembayaran Midtrans.
 */
class SubscriptionOrderRepository
{
    public static function findById(int $id): ?object
    {
        return DB::selectOne('SELECT * FROM subscription_orders WHERE id = ?', [$id]);
    }

    public static function findByOrderId(string $orderId): ?object
    {
        return DB::selectOne('SELECT * FROM subscription_orders WHERE order_id = ?', [$orderId]);
    }

    public static function findByOrderIdAndUserId(string $orderId, int $userId): ?object
    {
        return DB::selectOne(
            'SELECT * FROM subscription_orders WHERE order_id = ? AND user_id = ?',
            [$orderId, $userId]
        );
    }

    /**
     * Insert order baru, return ID.
     */
    public static function create(array $data): int
    {
        DB::insert(
            'INSERT INTO subscription_orders (user_id, subscription_package_id, order_id, gross_amount, transaction_status, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, NOW(), NOW())',
            [
                $data['user_id'],
                $data['subscription_package_id'],
                $data['order_id'],
                $data['gross_amount'],
                $data['transaction_status'] ?? 'pending',
            ]
        );

        return (int) DB::getPdo()->lastInsertId();
    }

    /**
     * Update order berdasarkan ID.
     */
    public static function update(int $id, array $data): bool
    {
        $sets = [];
        $values = [];

        foreach ($data as $column => $value) {
            $sets[] = "{$column} = ?";
            $values[] = is_array($value) ? json_encode($value) : $value;
        }

        $sets[] = 'updated_at = NOW()';
        $values[] = $id;

        return DB::update(
            'UPDATE subscription_orders SET ' . implode(', ', $sets) . ' WHERE id = ?',
            $values
        ) > 0;
    }

    /**
     * Ambil order terbaru milik user.
     */
    public static function getRecentByUserId(int $userId, int $limit = 5): array
    {
        return DB::select(
            'SELECT so.*, sp.name as package_name
             FROM subscription_orders so
             LEFT JOIN subscription_packages sp ON sp.id = so.subscription_package_id
             WHERE so.user_id = ?
             ORDER BY so.id DESC
             LIMIT ?',
            [$userId, $limit]
        );
    }
}
