<?php

use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// Halaman awal: tamu melihat halaman selamat datang, pengguna login diarahkan ke data mahasiswa.
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('mahasiswa.index')
        : view('welcome');
});

// Dashboard bawaan Breeze, tetap dilindungi middleware auth.
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Seluruh fitur CRUD mahasiswa dan profil wajib melalui autentikasi.
Route::middleware('auth')->group(function () {
    // Route resource mahasiswa dilindungi login.
    Route::resource('mahasiswa', MahasiswaController::class);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// =====================================================================
// DEMO SQL INJECTION — HANYA UNTUK PEMBELAJARAN, SEMENTARA!
// HAPUS KEDUA ROUTE DI BAWAH INI SETELAH DEMO KEAMANAN SELESAI.
// Jangan gunakan pola query rentan di kode produksi.
// =====================================================================
Route::middleware('auth')->group(function () {
    // Demo RENTAN: input pengguna langsung disisipkan ke string SQL.
    Route::get('/demo/sqli/rentan', function (Request $request) {
        $keyword = (string) $request->query('q', '');

        // RENTAN TERHADAP SQL INJECTION: jangan meniru pola ini!
        $hasil = DB::select(
            "SELECT nim, nama_lengkap FROM mahasiswas WHERE nama_lengkap LIKE '%{$keyword}%'"
        );

        return response()->json([
            'mode' => 'RENTAN',
            'keyword' => $keyword,
            'jumlah' => count($hasil),
            'hasil' => $hasil,
        ]);
    })->name('demo.sqli.rentan');

    // Demo AMAN: query builder memakai parameter binding (prepared statement).
    Route::get('/demo/sqli/aman', function (Request $request) {
        $keyword = (string) $request->query('q', '');

        $hasil = DB::table('mahasiswas')
            ->select('nim', 'nama_lengkap')
            ->where('nama_lengkap', 'like', "%{$keyword}%")
            ->get();

        return response()->json([
            'mode' => 'AMAN',
            'keyword' => $keyword,
            'jumlah' => $hasil->count(),
            'hasil' => $hasil,
        ]);
    })->name('demo.sqli.aman');
});
// =====================================================================
// AKHIR BAGIAN DEMO SQL INJECTION — HAPUS SAMPAI BATAS INI.
// =====================================================================
