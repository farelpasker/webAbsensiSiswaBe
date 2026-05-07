<?php

namespace Database\Seeders;

use App\Models\Kelas;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KelasSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kelasData = [
            ['nama' => 'X-A'],
            ['nama' => 'X-B'],
            ['nama' => 'X-C'],
            ['nama' => 'XI-A'],
            ['nama' => 'XI-B'],
            ['nama' => 'XI-C'],
            ['nama' => 'XII-A'],
            ['nama' => 'XII-B'],
            ['nama' => 'XII-C'],
        ];

        foreach($kelasData as $kelas) {
            Kelas::create($kelas);
        }
    }
}
