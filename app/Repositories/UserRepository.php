<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

/**
 * Repository untuk operasi raw SQL pada tabel users.
 * Catatan: User model Eloquent (Authenticatable) tetap dipakai untuk Auth.
 * Repository ini untuk query data di luar kebutuhan Auth.
 */
class UserRepository
{
    public static function findById(int $id): ?object
    {
        return DB::selectOne('SELECT * FROM users WHERE id = ?', [$id]);
    }

    public static function findByUsername(string $username): ?object
    {
        return DB::selectOne('SELECT * FROM users WHERE username = ?', [$username]);
    }

    public static function findByEmail(string $email): ?object
    {
        return DB::selectOne('SELECT * FROM users WHERE email = ?', [$email]);
    }

    public static function updateRole(int $userId, string $role): bool
    {
        return DB::update('UPDATE users SET role = ?, updated_at = NOW() WHERE id = ?', [$role, $userId]) > 0;
    }

    public static function updateStatus(int $userId, string $status): bool
    {
        return DB::update('UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?', [$status, $userId]) > 0;
    }

    public static function isActive(int $userId): bool
    {
        $user = DB::selectOne('SELECT status FROM users WHERE id = ?', [$userId]);

        return $user && $user->status === 'active';
    }

    public static function isAdmin(int $userId): bool
    {
        $user = DB::selectOne('SELECT role FROM users WHERE id = ?', [$userId]);

        return $user && $user->role === 'admin';
    }
}
