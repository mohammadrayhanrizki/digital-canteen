<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Admin/Cashier
        User::create([
            'name' => 'Admin Kantin',
            'email' => 'admin@school.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 2. Create Student (User + Wallet)
        $student = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@school.com',
            'password' => Hash::make('password'), // Optional for students
            'rfid_uid' => 'A1B2C3D4', // Dummy UID
            'role' => 'customer',
        ]);

        Wallet::create([
            'user_id' => $student->id,
            'balance' => 50000, // Rp 50.000 Initial Balance
        ]);

        // 3. Create Products
        Product::create([
            'name' => 'Nasi Goreng Spesial',
            'price' => 15000,
            'stock' => 50,
        ]);
        Product::create([
            'name' => 'Es Then Manis',
            'price' => 3000,
            'stock' => 100,
        ]);
    }
}
