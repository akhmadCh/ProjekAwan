<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Selaraskan tabel resources dengan ERD:
     * - Rename subscription_id → user_subscription_id
     * - Ubah type ke ENUM('storage_bucket','compute_metadata','network_metadata')
     * - Ubah status ke ENUM('pending','running','stopped','failed')
     * - Tambah metadata JSON
     */
    public function up(): void
    {
        Schema::table('resources', function (Blueprint $table) {
            $table->renameColumn('subscription_id', 'user_subscription_id');
        });

        // Update existing type values
        DB::statement("UPDATE resources SET type = 'storage_bucket' WHERE type = 'object_storage'");

        // Update existing status values
        DB::statement("UPDATE resources SET status = 'running' WHERE status = 'active'");
        DB::statement("UPDATE resources SET status = 'stopped' WHERE status = 'suspended'");
        DB::statement("UPDATE resources SET status = 'failed' WHERE status = 'deleted'");

        // Alter type column to ENUM
        DB::statement("ALTER TABLE resources MODIFY COLUMN type ENUM('storage_bucket', 'compute_metadata', 'network_metadata') DEFAULT 'storage_bucket'");

        // Alter status column to match ERD
        DB::statement("ALTER TABLE resources MODIFY COLUMN status ENUM('pending', 'running', 'stopped', 'failed') DEFAULT 'pending'");

        Schema::table('resources', function (Blueprint $table) {
            $table->json('metadata')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('resources', function (Blueprint $table) {
            $table->dropColumn('metadata');
        });

        DB::statement("ALTER TABLE resources MODIFY COLUMN status ENUM('active', 'suspended', 'deleted') DEFAULT 'active'");
        DB::statement("ALTER TABLE resources MODIFY COLUMN type VARCHAR(50) DEFAULT 'object_storage'");

        DB::statement("UPDATE resources SET status = 'active' WHERE status = 'running'");
        DB::statement("UPDATE resources SET status = 'suspended' WHERE status = 'stopped'");
        DB::statement("UPDATE resources SET type = 'object_storage' WHERE type = 'storage_bucket'");

        Schema::table('resources', function (Blueprint $table) {
            $table->renameColumn('user_subscription_id', 'subscription_id');
        });
    }
};
