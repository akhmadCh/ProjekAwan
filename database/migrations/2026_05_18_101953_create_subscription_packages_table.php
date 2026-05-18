<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscription_packages', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // Nama paket (Gold, Silver, Bronze)
                $table->decimal('storage_quota_gb', 8, 2); // Kuota penyimpanan
                $table->decimal('price_per_month', 12, 2); // Harga per bulan
                $table->text('description')->nullable(); // Deskripsi paket
                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_packages');
    }
};
