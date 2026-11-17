<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel mahasiswas sesuai kebutuhan modul praktikum.
     */
    public function up(): void
    {
        Schema::create('mahasiswas', function (Blueprint $table) {
            $table->id(); // Primary key auto increment
            $table->string('nim', 15)->unique(); // NIM unik, maksimal 15 karakter
            $table->string('nama_lengkap', 100); // Nama lengkap maksimal 100 karakter
            $table->enum('program_studi', [ // Pilihan program studi yang tersedia
                'Informatika',
                'Sistem Informasi',
                'Teknik Komputer',
            ]);
            $table->year('angkatan'); // Tahun angkatan masuk
            $table->string('foto')->nullable(); // Path foto, boleh kosong
            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    /**
     * Menghapus tabel mahasiswas saat rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahasiswas');
    }
};
