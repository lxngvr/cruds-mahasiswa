<?php

namespace App\Http\Requests;

use App\Models\Mahasiswa;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMahasiswaSecureRequest extends FormRequest
{
    /**
     * Hanya pengguna yang sudah login yang boleh mengubah data mahasiswa.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Aturan validasi untuk memperbarui data mahasiswa.
     * NIM milik data yang sedang diedit dikecualikan dari aturan unik.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Route model binding mengirim instance Mahasiswa; ambil primary key-nya.
        $mahasiswa = $this->route('mahasiswa');
        $id = $mahasiswa instanceof Mahasiswa ? $mahasiswa->getKey() : $mahasiswa;

        return [
            'nim' => [
                'required',
                'string',
                'regex:/^\d{10}$/',
                Rule::unique('mahasiswas', 'nim')->ignore($id),
            ],
            'nama_lengkap' => ['required', 'string', 'max:100', "regex:/^[\p{L} .']+$/u"],
            'program_studi' => ['required', Rule::in(Mahasiswa::PROGRAM_STUDI)],
            'angkatan' => ['required', 'integer', 'between:2018,'.date('Y')],
            'foto' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png',
                'max:2048',
                'dimensions:min_width=100,min_height=100',
            ],
            'catatan' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Pesan validasi dalam bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nim.required' => 'NIM wajib diisi.',
            'nim.regex' => 'NIM harus tepat 10 digit angka.',
            'nim.unique' => 'NIM sudah terdaftar, gunakan NIM lain.',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nama_lengkap.max' => 'Nama lengkap maksimal 100 karakter.',
            'nama_lengkap.regex' => 'Nama lengkap hanya boleh berisi huruf, spasi, titik, dan apostrof.',
            'program_studi.required' => 'Program studi wajib dipilih.',
            'program_studi.in' => 'Program studi yang dipilih tidak valid.',
            'angkatan.required' => 'Angkatan wajib diisi.',
            'angkatan.integer' => 'Angkatan harus berupa angka tahun.',
            'angkatan.between' => 'Angkatan harus antara tahun 2018 dan tahun ini.',
            'foto.image' => 'Berkas foto harus berupa gambar.',
            'foto.mimes' => 'Format foto harus JPG, JPEG, atau PNG.',
            'foto.max' => 'Ukuran foto maksimal 2 MB.',
            'foto.dimensions' => 'Ukuran foto minimal 100x100 piksel.',
            'catatan.max' => 'Catatan maksimal 500 karakter.',
        ];
    }
}
