<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Selaraskan ENUM status di user_subscriptions sesuai ERD:
     * 'canceled' → 'cancelled' (British spelling sesuai ERD)
     */
    public function up(): void
    {
        // Update existing 'canceled' rows to 'cancelled'
        DB::statement("UPDATE user_subscriptions SET status = 'cancelled' WHERE status = 'canceled'");

        // Alter ENUM to match ERD
        DB::statement("ALTER TABLE user_subscriptions MODIFY COLUMN status ENUM('active', 'expired', 'cancelled') DEFAULT 'active'");
    }

    public function down(): void
    {
        DB::statement("UPDATE user_subscriptions SET status = 'canceled' WHERE status = 'cancelled'");
        DB::statement("ALTER TABLE user_subscriptions MODIFY COLUMN status ENUM('active', 'expired', 'canceled') DEFAULT 'active'");
    }
};
