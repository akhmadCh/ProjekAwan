<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Selaraskan tabel subscription_packages dengan ERD:
     * - Rename storage_quota_gb → storage_limit_gb
     * - Tambah max_buckets, vcpu_limit, ram_limit_mb, bandwidth_limit_gb, is_active
     * - Aktifkan timestamps
     */
    public function up(): void
    {
        Schema::table('subscription_packages', function (Blueprint $table) {
            $table->renameColumn('storage_quota_gb', 'storage_limit_gb');
        });

        Schema::table('subscription_packages', function (Blueprint $table) {
            $table->integer('max_buckets')->default(5)->after('storage_limit_gb');
            $table->integer('vcpu_limit')->default(0)->after('max_buckets');
            $table->integer('ram_limit_mb')->default(0)->after('vcpu_limit');
            $table->float('bandwidth_limit_gb')->default(0)->after('ram_limit_mb');
            $table->boolean('is_active')->default(true)->after('description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('subscription_packages', function (Blueprint $table) {
            $table->dropColumn(['max_buckets', 'vcpu_limit', 'ram_limit_mb', 'bandwidth_limit_gb', 'is_active', 'created_at', 'updated_at']);
        });

        Schema::table('subscription_packages', function (Blueprint $table) {
            $table->renameColumn('storage_limit_gb', 'storage_quota_gb');
        });
    }
};
