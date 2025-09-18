<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Karyawan;

class KaryawanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $karyawans = [
            'Andi Saputra',
            'Budi Santoso',
            'Citra Lestari',
            'Dewi Anggraini',
            'Eka Prasetya',
            'Fajar Nugroho',
            'Gita Ramadhani',
            'Hadi Wijaya',
            'Indah Permatasari',
            'Joko Susilo'
        ];

        foreach ($karyawans as $nama) {
            Karyawan::create(['nama' => $nama]);
        }
    }
}
