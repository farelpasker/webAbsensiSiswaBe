<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cek apakah admin sudah ada
        $adminExists = User::where('email', 'admin@sekolah.com')->exists();
        
        if ($adminExists) {
            $this->command->warn('Admin sudah ada, skip seeding');
            return;
        }

        // Buat user admin
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@sekolah.com',
            'password' => Hash::make('password'),
            'phone' => '+628123456789',
        ]);

        // Assign role admin
        $admin->assignRole('admin');

        $this->command->info('✓ Admin account berhasil dibuat');
        $this->command->line('  Email: admin@sekolah.com');
        $this->command->line('  Password: password');
    }
}

