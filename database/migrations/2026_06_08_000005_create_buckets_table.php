<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel baru sesuai ERD:
     * buckets — relasi 1:1 dengan resources, menyimpan detail bucket storage.
     */
    public function up(): void
    {
        Schema::create('buckets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->unique()->constrained('resources')->cascadeOnDelete();
            $table->string('bucket_name')->unique();
            $table->float('storage_limit_gb')->default(0);
            $table->float('used_storage_gb')->default(0);
            $table->integer('object_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buckets');
    }
};
