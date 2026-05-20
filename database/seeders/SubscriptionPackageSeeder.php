<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubscriptionPackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Lite (Bronze)',
                'storage_quota_gb' => 5.00,
                'price_per_month' => 0.00,
                'description' => 'Cocok untuk belajar dan testing awal.'
            ],
            [
                'name' => 'Pro (Silver)',
                'storage_quota_gb' => 50.00,
                'price_per_month' => 50000.00,
                'description' => 'Penyimpanan luas untuk proyek skala menengah.'
            ],
            [
                'name' => 'Enterprise (Gold)',
                'storage_quota_gb' => 500.00,
                'price_per_month' => 150000.00,
                'description' => 'Akses penuh tanpa batas penyimpanan ketat.'
            ],
        ];

        foreach ($packages as $package) {
            \App\Models\SubscriptionPackage::create($package);
        }
    }
}
