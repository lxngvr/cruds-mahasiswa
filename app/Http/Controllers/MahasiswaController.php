<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMahasiswaSecureRequest;
use App\Http\Requests\UpdateMahasiswaSecureRequest;
use App\Models\Mahasiswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MahasiswaController extends Controller
{
    /**
     * Menampilkan daftar mahasiswa beserta pencarian, filter, dan paginasi.
     */
    public function index(Request $request): View
    {
        // Ambil data dengan scopeFilter, urutkan terbaru, dan tampilkan 10 per halaman.
        $mahasiswas = Mahasiswa::query()
            ->filter($request->only('search', 'program_studi', 'angkatan'))
            ->latest()
            ->paginate(10)
            ->withQueryString(); // Parameter pencarian/filter tetap terbawa saat pindah halaman.

        // Daftar angkatan unik untuk dropdown filter.
        $angkatans = Mahasiswa::query()
            ->select('angkatan')
            ->distinct()
            ->orderByDesc('angkatan')
            ->pluck('angkatan');

        return view('mahasiswa.index', [
            'mahasiswas' => $mahasiswas,
            'programStudi' => Mahasiswa::PROGRAM_STUDI,
            'angkatans' => $angkatans,
        ]);
    }

    /**
     * Menampilkan form tambah mahasiswa.
     */
    public function create(): View
    {
        return view('mahasiswa.create', [
            'programStudi' => Mahasiswa::PROGRAM_STUDI,
        ]);
    }

    /**
     * Menyimpan data mahasiswa baru, termasuk upload foto jika ada.
     */
    public function store(StoreMahasiswaSecureRequest $request): RedirectResponse
    {
        // Validasi dilakukan oleh Form Request khusus keamanan.
        $validated = $request->validated();

        // Jika foto diunggah, simpan ke disk public pada folder mahasiswa.
        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('mahasiswa', 'public');
        }

        Mahasiswa::create($validated);

        return redirect()
            ->route('mahasiswa.index')
            ->with('success', 'Data mahasiswa berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail satu mahasiswa.
     */
    public function show(Mahasiswa $mahasiswa): View
    {
        return view('mahasiswa.show', compact('mahasiswa'));
    }

    /**
     * Menampilkan form edit mahasiswa.
     */
    public function edit(Mahasiswa $mahasiswa): View
    {
        return view('mahasiswa.edit', [
            'mahasiswa' => $mahasiswa,
            'programStudi' => Mahasiswa::PROGRAM_STUDI,
        ]);
    }

    /**
     * Memperbarui data mahasiswa. Foto lama hanya dihapus jika foto diganti.
     */
    public function update(UpdateMahasiswaSecureRequest $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        // Form Request update sudah mengabaikan NIM milik data yang sedang diedit.
        $validated = $request->validated();

        // Foto baru hanya diproses jika pengguna mengunggah file baru.
        if ($request->hasFile('foto')) {
            // Hapus foto lama dari storage agar tidak menumpuk.
            if ($mahasiswa->foto) {
                Storage::disk('public')->delete($mahasiswa->foto);
            }

            $validated['foto'] = $request->file('foto')->store('mahasiswa', 'public');
        }

        // Jika tidak ada foto baru, key 'foto' tidak ada sehingga foto lama dipertahankan.
        $mahasiswa->update($validated);

        return redirect()
            ->route('mahasiswa.index')
            ->with('success', 'Data mahasiswa berhasil diperbarui.');
    }

    /**
     * Menghapus data mahasiswa beserta file fotonya.
     */
    public function destroy(Mahasiswa $mahasiswa): RedirectResponse
    {
        // Hapus file foto terlebih dahulu jika memang ada.
        if ($mahasiswa->foto) {
            Storage::disk('public')->delete($mahasiswa->foto);
        }

        $mahasiswa->delete();

        return redirect()
            ->route('mahasiswa.index')
            ->with('success', 'Data mahasiswa berhasil dihapus.');
    }
}
