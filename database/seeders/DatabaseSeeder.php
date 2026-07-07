<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        User::query()->updateOrCreate([
            'email' => env('ADMIN_EMAIL', 'admin@azraq.test'),
        ], [
            'name' => env('ADMIN_NAME', 'Azraq Admin'),
            'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
            'is_admin' => true,
            'role' => 'super_admin',
        ]);

        if (class_exists(Category::class)) {
            $this->call(CatalogSeeder::class);
            $this->call(HomepageSectionsSeeder::class);
        }
    }
}
