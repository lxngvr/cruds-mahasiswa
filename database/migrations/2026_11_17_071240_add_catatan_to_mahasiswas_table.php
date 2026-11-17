<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambah kolom catatan (teks bebas, boleh kosong) pada tabel mahasiswas.
     */
    public function up(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            // Kolom catatan tambahan, nullable agar data lama tetap valid.
            $table->text('catatan')->nullable()->after('foto');
        });
    }

    /**
     * Menghapus kolom catatan saat rollback.
     */
    public function down(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->dropColumn('catatan');
        });
    }
};
