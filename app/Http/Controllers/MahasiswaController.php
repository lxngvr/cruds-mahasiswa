<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
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
    public function store(Request $request): RedirectResponse
    {
        // Validasi seluruh input terlebih dahulu.
        $validated = $request->validate(
            $this->validationRules(),
            $this->validationMessages()
        );

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
    public function update(Request $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        // Validasi dengan mengabaikan NIM milik mahasiswa yang sedang diedit.
        $validated = $request->validate(
            $this->validationRules($mahasiswa),
            $this->validationMessages()
        );

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

    /**
     * Aturan validasi. Saat update, NIM milik data sendiri dikecualikan.
     */
    private function validationRules(?Mahasiswa $mahasiswa = null): array
    {
        return [
            'nim' => [
                'required',
                'digits:15',
                Rule::unique('mahasiswas', 'nim')->ignore($mahasiswa?->id),
            ],
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'program_studi' => ['required', Rule::in(Mahasiswa::PROGRAM_STUDI)],
            'angkatan' => ['required', 'integer', 'min:1900', 'max:'.(date('Y') + 1)],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'], // Maksimal 2 MB.
        ];
    }

    /**
     * Pesan validasi dalam bahasa Indonesia agar mudah dipahami pengguna.
     */
    private function validationMessages(): array
    {
        return [
            'nim.required' => 'NIM wajib diisi.',
            'nim.digits' => 'NIM harus terdiri dari 15 digit angka.',
            'nim.unique' => 'NIM sudah terdaftar, gunakan NIM lain.',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nama_lengkap.max' => 'Nama lengkap maksimal 100 karakter.',
            'program_studi.required' => 'Program studi wajib dipilih.',
            'program_studi.in' => 'Program studi yang dipilih tidak valid.',
            'angkatan.required' => 'Angkatan wajib diisi.',
            'angkatan.integer' => 'Angkatan harus berupa angka tahun.',
            'angkatan.min' => 'Angkatan tidak boleh sebelum tahun 1900.',
            'angkatan.max' => 'Angkatan tidak boleh melebihi tahun depan.',
            'foto.image' => 'Berkas foto harus berupa gambar.',
            'foto.mimes' => 'Format foto harus JPG, JPEG, atau PNG.',
            'foto.max' => 'Ukuran foto maksimal 2 MB.',
        ];
    }
}
