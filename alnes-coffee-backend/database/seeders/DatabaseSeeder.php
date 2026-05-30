<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Banner;
use App\Models\CafeTable;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─────────────────────────────────────────
        // 1. SETTINGS — data outlet café
        // ─────────────────────────────────────────
        Setting::create([
            'cafe_name'      => 'Alnes Coffee and Venue Batu',
            'address'        => 'Jl. Raya Batu No. 1, Kota Batu, Jawa Timur',
            'phone'          => '081234567890',
            'email'          => 'hello@alnescoffee.com',
            'instagram'      => '@alnescoffee',
            'open_time'      => '07:00:00',
            'close_time'     => '22:00:00',
            'tax_percentage' => 0,
            'service_fee'    => 1000,
            'is_open'        => true,
        ]);

        // ─────────────────────────────────────────
        // 2. USERS — admin, cashier, kitchen
        // ─────────────────────────────────────────
        User::create([
            'name'      => 'Administrator',
            'email'     => 'admin@alnescoffee.com',
            'password'  => Hash::make('password'),
            'role'      => UserRole::Admin,
            'is_active' => true,
        ]);

        User::create([
            'name'      => 'Kasir Satu',
            'email'     => 'kasir@alnescoffee.com',
            'password'  => Hash::make('password'),
            'role'      => UserRole::Cashier,
            'is_active' => true,
        ]);

        User::create([
            'name'      => 'Dapur Satu',
            'email'     => 'kitchen@alnescoffee.com',
            'password'  => Hash::make('password'),
            'role'      => UserRole::Kitchen,
            'is_active' => true,
        ]);

        // ─────────────────────────────────────────
        // 3. CAFE TABLES — meja café
        // ─────────────────────────────────────────
        $tables = [
            ['table_number' => 'MEJA-01', 'slug' => 'meja-01'],
            ['table_number' => 'MEJA-02', 'slug' => 'meja-02'],
            ['table_number' => 'MEJA-03', 'slug' => 'meja-03'],
            ['table_number' => 'MEJA-04', 'slug' => 'meja-04'],
            ['table_number' => 'MEJA-05', 'slug' => 'meja-05'],
            ['table_number' => 'BAR-01',  'slug' => 'bar-01'],
            ['table_number' => 'BAR-02',  'slug' => 'bar-02'],
            ['table_number' => 'VIP-01',  'slug' => 'vip-01'],
        ];

        foreach ($tables as $table) {
            CafeTable::create($table);
        }

        // ─────────────────────────────────────────
        // 4. CATEGORIES
        // ─────────────────────────────────────────
        $categories = [
            ['name' => 'Favorit Banyak Orang!',  'slug' => 'favorit',       'type' => 'beverages', 'sort_order' => 1,  'icon' => '⭐'],
            ['name' => 'Coffee',                  'slug' => 'coffee',        'type' => 'beverages', 'sort_order' => 2,  'icon' => '☕'],
            ['name' => 'Non Coffee',              'slug' => 'non-coffee',    'type' => 'beverages', 'sort_order' => 3,  'icon' => '🧃'],
            ['name' => 'Signature Drinks',        'slug' => 'signature',     'type' => 'beverages', 'sort_order' => 4,  'icon' => '✨'],
            ['name' => 'Food & Snack',            'slug' => 'food-snack',    'type' => 'food',      'sort_order' => 5,  'icon' => '🍟'],
            ['name' => 'Special of The Day',      'slug' => 'special-day',   'type' => 'food',      'sort_order' => 6,  'icon' => '👨‍🍳'],
            ['name' => 'Dessert',                 'slug' => 'dessert',       'type' => 'food',      'sort_order' => 7,  'icon' => '🍰'],
            ['name' => 'Traditional',             'slug' => 'traditional',   'type' => 'food',      'sort_order' => 8,  'icon' => '🍲'],
        ];

        $createdCategories = [];
        foreach ($categories as $cat) {
            $createdCategories[$cat['slug']] = Category::create(array_merge($cat, ['is_active' => true]));
        }

        // ─────────────────────────────────────────
        // 5. PRODUCTS — sample menu
        // ─────────────────────────────────────────
        $products = [
            // Coffee
            ['name' => 'Americano',           'category' => 'coffee',     'price' => 22000, 'is_popular' => true,  'is_active' => true],
            ['name' => 'Cappuccino',          'category' => 'coffee',     'price' => 28000, 'is_best_seller' => true, 'is_active' => true],
            ['name' => 'Latte',               'category' => 'coffee',     'price' => 28000, 'is_popular' => true,  'is_active' => true],
            ['name' => 'Espresso',            'category' => 'coffee',     'price' => 18000, 'is_active' => true],
            ['name' => 'V60 Pour Over',       'category' => 'coffee',     'price' => 32000, 'is_recommended' => true, 'is_active' => true],
            ['name' => 'Cold Brew',           'category' => 'coffee',     'price' => 30000, 'is_best_seller' => true, 'is_active' => true],
            ['name' => 'Dalgona Coffee',      'category' => 'coffee',     'price' => 28000, 'is_active' => true],

            // Non Coffee
            ['name' => 'Matcha Latte',        'category' => 'non-coffee', 'price' => 28000, 'is_popular' => true,  'is_active' => true],
            ['name' => 'Chocolicious',        'category' => 'non-coffee', 'price' => 27000, 'is_best_seller' => true, 'is_popular' => true, 'is_active' => true],
            ['name' => 'Thai Tea',            'category' => 'non-coffee', 'price' => 22000, 'is_active' => true],
            ['name' => 'Taro Latte',          'category' => 'non-coffee', 'price' => 28000, 'is_recommended' => true, 'is_active' => true],
            ['name' => 'Red Velvet Latte',    'category' => 'non-coffee', 'price' => 30000, 'is_active' => true],

            // Signature
            ['name' => 'Alnes Signature',     'category' => 'signature',  'price' => 35000, 'is_special' => true, 'is_recommended' => true, 'is_active' => true],
            ['name' => 'Sunset Breeze',       'category' => 'signature',  'price' => 32000, 'is_special' => true, 'is_active' => true],

            // Food & Snack
            ['name' => 'French Fries',        'category' => 'food-snack', 'price' => 18000, 'is_popular' => true, 'is_active' => true],
            ['name' => 'Potato Cheese Ball',  'category' => 'food-snack', 'price' => 28000, 'is_best_seller' => true, 'is_active' => true],
            ['name' => 'Spaghetti Bolognese', 'category' => 'food-snack', 'price' => 35000, 'is_active' => true],
            ['name' => 'Mie Beijing',         'category' => 'food-snack', 'price' => 35000, 'is_active' => true],
            ['name' => 'Pancake',             'category' => 'food-snack', 'price' => 25000, 'is_active' => true],

            // Special
            ['name' => 'Kudo Platter',        'category' => 'special-day','price' => 28000, 'is_special' => true, 'is_recommended' => true, 'is_active' => true],

            // Dessert
            ['name' => 'Ice Cream Sundae',    'category' => 'dessert',    'price' => 22000, 'is_active' => true],
            ['name' => 'Tiramisu',            'category' => 'dessert',    'price' => 28000, 'is_recommended' => true, 'is_active' => true],

            // Traditional
            ['name' => 'Nasi Goreng',         'category' => 'traditional','price' => 28000, 'is_popular' => true, 'is_active' => true],
            ['name' => 'Nasi Goreng Nenek Mojang Jawa', 'category' => 'traditional', 'price' => 35000, 'is_special' => true, 'is_active' => true],
            ['name' => 'Soto Ayam',           'category' => 'traditional','price' => 25000, 'is_active' => true],
        ];

        foreach ($products as $index => $product) {
            $categorySlug = $product['category'];
            $category     = $createdCategories[$categorySlug] ?? null;

            if (!$category) continue;

            Product::create([
                'category_id'    => $category->id,
                'name'           => $product['name'],
                'slug'           => Str::slug($product['name']),
                'price'          => $product['price'],
                'stock'          => 50,
                'is_best_seller' => $product['is_best_seller'] ?? false,
                'is_special'     => $product['is_special']     ?? false,
                'is_popular'     => $product['is_popular']     ?? false,
                'is_recommended' => $product['is_recommended'] ?? false,
                'is_active'      => $product['is_active']      ?? true,
                'sku'            => 'SKU-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
            ]);
        }

        // ─────────────────────────────────────────
        // 6. BANNERS — banner halaman utama
        // ─────────────────────────────────────────
        Banner::create([
            'title'      => 'Promo Spesial Weekend!',
            'image'      => 'banners/promo-1.jpg',
            'sort_order' => 1,
            'is_active'  => true,
        ]);

        Banner::create([
            'title'      => 'Menu Baru: Alnes Signature',
            'image'      => 'banners/promo-2.jpg',
            'sort_order' => 2,
            'is_active'  => true,
        ]);

        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('');
        $this->command->info('Login credentials:');
        $this->command->info('  Admin   → admin@alnescoffee.com   / password');
        $this->command->info('  Kasir   → kasir@alnescoffee.com   / password');
        $this->command->info('  Kitchen → kitchen@alnescoffee.com / password');
        $this->command->info('');
        $this->command->info('Admin panel: http://localhost:8000/admin');
    }
}