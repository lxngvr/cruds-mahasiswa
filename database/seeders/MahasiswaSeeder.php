<?php

namespace Database\Seeders;

use App\Models\Mahasiswa;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class MahasiswaSeeder extends Seeder
{
    /**
     * Membuat 50 data mahasiswa palsu menggunakan Faker.
     */
    public function run(): void
    {
        // Kosongkan tabel agar seeder aman dijalankan ulang tanpa duplikasi NIM.
        Mahasiswa::query()->delete();

        // Gunakan locale Indonesia supaya nama yang dihasilkan lebih natural.
        $faker = Faker::create('id_ID');

        for ($i = 0; $i < 50; $i++) {
            Mahasiswa::create([
                // NIM 15 digit angka yang dijamin unik dalam satu proses seeding.
                'nim' => $faker->unique()->numerify(str_repeat('#', 15)),
                'nama_lengkap' => $faker->name(),
                'program_studi' => $faker->randomElement(Mahasiswa::PROGRAM_STUDI),
                'angkatan' => $faker->numberBetween(2019, (int) date('Y')),
                'foto' => null, // Data seed tidak memakai foto.
            ]);
        }
    }
}
