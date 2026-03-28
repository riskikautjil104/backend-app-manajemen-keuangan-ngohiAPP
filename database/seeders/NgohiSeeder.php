<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use App\Models\Category;
use App\Models\FinancialTarget;
use App\Models\User;
use Illuminate\Database\Seeder;

class NgohiSeeder extends Seeder
{
    public function run(): void
    {
        AppSetting::query()->create([
            'display_name' => 'NGOHI',
            'tagline' => 'Wujudkan mimpi finansialmu',
            'primary_color' => '#1565C0',
            'secondary_color' => '#F9A825',
            'accent_color' => '#FFFFFF',
        ]);

        $defaults = [
            ['name' => 'Makanan', 'icon' => 'restaurant', 'type' => 'expense'],
            ['name' => 'Rokok', 'icon' => 'smoking_rooms', 'type' => 'expense'],
            ['name' => 'Kos', 'icon' => 'home', 'type' => 'expense'],
            ['name' => 'Transportasi', 'icon' => 'directions_car', 'type' => 'expense'],
            ['name' => 'Belanja Bulanan', 'icon' => 'shopping_cart', 'type' => 'expense'],
            ['name' => 'Hiburan', 'icon' => 'sports_esports', 'type' => 'expense'],
            ['name' => 'Gaji / Pemasukan', 'icon' => 'payments', 'type' => 'income'],
        ];

        foreach ($defaults as $row) {
            Category::query()->create([
                'user_id' => null,
                'name' => $row['name'],
                'icon' => $row['icon'],
                'type' => $row['type'],
            ]);
        }

        User::query()->create([
            'name' => 'Admin NGOHI',
            'email' => 'admin@ngohi.test',
            'password' => 'password',
            'level' => 'kawasa',
            'is_admin' => true,
        ]);

        $demo = User::query()->create([
            'name' => 'Demo Ngohi',
            'email' => 'demo@ngohi.test',
            'password' => 'password',
            'level' => 'kawasa',
            'is_admin' => false,
        ]);

        FinancialTarget::query()->create([
            'user_id' => $demo->id,
            'name' => 'Tabungan Laptop',
            'target_amount' => 5_000_000,
            'saved_amount' => 2_000_000,
            'target_date' => now()->addMonths(4),
            'completed' => false,
        ]);
    }
}
