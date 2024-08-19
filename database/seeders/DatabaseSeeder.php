<?php

namespace Database\Seeders;


// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
           CountriesTableSeeder::class,
           StatesTableSeeder::class,
           CitiesTableSeeder::class,
        ]);

        User::factory()->create([
           'name'=>'Admin User',
           'email' => 'test@admin.com',
            'password' => 'hunter123',
            'is_admin' => true
        ]);

    }
}
