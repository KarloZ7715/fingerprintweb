<?php

namespace Database\Seeders;

use App\Models\Administrador;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Administrador::create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@fingerprint.local',
            'password' => Hash::make('password'),
        ]);
    }
}