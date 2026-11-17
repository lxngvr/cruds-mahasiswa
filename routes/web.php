<?php

use App\Http\Controllers\MahasiswaController;
use Illuminate\Support\Facades\Route;

// Halaman awal diarahkan langsung ke daftar mahasiswa.
Route::redirect('/', '/mahasiswa');

// Satu baris route resource untuk seluruh aksi CRUD mahasiswa.
Route::resource('mahasiswa', MahasiswaController::class);
