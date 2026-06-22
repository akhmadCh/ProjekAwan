<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Selaraskan tabel objects dengan ERD:
     * - Ganti FK resource_id → bucket_id (FK ke buckets)
     * - Rename key → object_key
     * - Tambah original_filename
     * - Rename created_at → uploaded_at
     * - Tambah deleted_at (soft delete)
     */
    public function up(): void
    {
        Schema::table('objects', function (Blueprint $table) {
            // Drop old FK
            $table->dropForeign(['resource_id']);
        });

        Schema::table('objects', function (Blueprint $table) {
            // Rename kolom
            $table->renameColumn('resource_id', 'bucket_id');
            $table->renameColumn('key', 'object_key');
            $table->renameColumn('created_at', 'uploaded_at');
        });

        Schema::table('objects', function (Blueprint $table) {
            // Tambah kolom baru
            $table->string('original_filename', 255)->nullable()->after('object_key');
            $table->softDeletes();

            // Tambah FK baru ke buckets
            $table->foreign('bucket_id')->references('id')->on('buckets')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('objects', function (Blueprint $table) {
            $table->dropForeign(['bucket_id']);
            $table->dropSoftDeletes();
            $table->dropColumn('original_filename');
        });

        Schema::table('objects', function (Blueprint $table) {
            $table->renameColumn('bucket_id', 'resource_id');
            $table->renameColumn('object_key', 'key');
            $table->renameColumn('uploaded_at', 'created_at');
        });

        Schema::table('objects', function (Blueprint $table) {
            $table->foreign('resource_id')->references('id')->on('resources')->cascadeOnDelete();
        });
    }
};
