<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subscription_package_id')->constrained('subscription_packages')->restrictOnDelete();
            $table->foreignId('user_subscription_id')->nullable()->constrained('user_subscriptions')->nullOnDelete();
            $table->string('order_id', 80)->unique();
            $table->decimal('gross_amount', 12, 2);
            $table->string('snap_token', 255)->nullable();
            $table->text('redirect_url')->nullable();
            $table->string('payment_type', 50)->nullable();
            $table->string('transaction_status', 50)->default('pending');
            $table->string('fraud_status', 50)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_orders');
    }
};