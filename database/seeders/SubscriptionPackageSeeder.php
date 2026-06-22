<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubscriptionPackageSeeder extends Seeder
{
    /**
     * Seed subscription packages sesuai ERD terbaru.
     * Menggunakan raw SQL (DB facade) tanpa Eloquent.
     */
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Lite (Bronze)',
                'storage_limit_gb' => 5.00,
                'max_buckets' => 3,
                'vcpu_limit' => 1,
                'ram_limit_mb' => 512,
                'bandwidth_limit_gb' => 10.00,
                'price_per_month' => 0.00,
                'description' => 'Cocok untuk belajar dan testing awal.',
                'is_active' => true,
            ],
            [
                'name' => 'Pro (Silver)',
                'storage_limit_gb' => 50.00,
                'max_buckets' => 10,
                'vcpu_limit' => 4,
                'ram_limit_mb' => 4096,
                'bandwidth_limit_gb' => 100.00,
                'price_per_month' => 50000.00,
                'description' => 'Penyimpanan luas untuk proyek skala menengah.',
                'is_active' => true,
            ],
            [
                'name' => 'Enterprise (Gold)',
                'storage_limit_gb' => 500.00,
                'max_buckets' => 50,
                'vcpu_limit' => 16,
                'ram_limit_mb' => 16384,
                'bandwidth_limit_gb' => 1000.00,
                'price_per_month' => 150000.00,
                'description' => 'Akses penuh tanpa batas penyimpanan ketat.',
                'is_active' => true,
            ],
        ];

        foreach ($packages as $package) {
            DB::insert(
                'INSERT INTO subscription_packages (name, storage_limit_gb, max_buckets, vcpu_limit, ram_limit_mb, bandwidth_limit_gb, price_per_month, description, is_active, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
                [
                    $package['name'],
                    $package['storage_limit_gb'],
                    $package['max_buckets'],
                    $package['vcpu_limit'],
                    $package['ram_limit_mb'],
                    $package['bandwidth_limit_gb'],
                    $package['price_per_month'],
                    $package['description'],
                    $package['is_active'],
                ]
            );
        }
    }
}
