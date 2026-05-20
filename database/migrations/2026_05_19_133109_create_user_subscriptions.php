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
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->foreignId('subscription_id')
                  ->constrained('user_subscriptions')
                  ->onDelete('cascade');
            $table->enum('type', ['bucket', 'vm', 'storage', 'network'])
                  ->default('bucket');
            $table->string('name');
            $table->string('ministack_id')->nullable(); 
            $table->float('size_gb')->nullable();    
            $table->json('config')->nullable();      
            $table->enum('status', ['pending', 'active', 'stopped', 'deleted'])
                  ->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};
