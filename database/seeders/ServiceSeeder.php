<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'name' => 'IIAS Web',
                'slug' => 'iias-web',
                'type' => 'web',
                'description' => 'Nuxt.js PWA 管理画面',
                'is_active' => true,
            ],
            [
                'name' => 'IIAS Android',
                'slug' => 'iias-android',
                'type' => 'android',
                'description' => 'React Native Android クライアント',
                'is_active' => true,
            ],
            [
                'name' => 'IIAS Desktop',
                'slug' => 'iias-desktop',
                'type' => 'desktop',
                'description' => 'Tauri デスクトップ クライアント',
                'is_active' => true,
            ],
            [
                'name' => 'IIAS Chrome',
                'slug' => 'iias-chrome',
                'type' => 'web',
                'description' => 'Chrome 拡張機能',
                'is_active' => true,
            ],
        ];

        foreach ($services as $service) {
            Service::firstOrCreate(['slug' => $service['slug']], $service);
        }
    }
}
