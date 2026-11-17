<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Menjalankan seeder utama aplikasi.
     */
    public function run(): void
    {
        // Panggil seeder data mahasiswa.
        $this->call(MahasiswaSeeder::class);
    }
}
