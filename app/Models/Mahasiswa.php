<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    /**
     * Daftar program studi yang boleh dipakai di form, validasi, dan filter.
     */
    public const PROGRAM_STUDI = [
        'Informatika',
        'Sistem Informasi',
        'Teknik Komputer',
    ];

    /**
     * Kolom yang boleh diisi secara massal (mass assignment).
     */
    protected $fillable = [
        'nim',
        'nama_lengkap',
        'program_studi',
        'angkatan',
        'foto',
        'catatan',
    ];

    /**
     * Scope untuk pencarian dan filter data mahasiswa.
     * Filter yang didukung: search (NIM/nama/prodi), program_studi, dan angkatan.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            // Pencarian kata kunci pada NIM, nama lengkap, atau program studi
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('nim', 'like', "%{$search}%")
                        ->orWhere('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('program_studi', 'like', "%{$search}%");
                });
            })
            // Filter berdasarkan program studi yang dipilih
            ->when($filters['program_studi'] ?? null, function (Builder $query, string $programStudi) {
                $query->where('program_studi', $programStudi);
            })
            // Filter berdasarkan tahun angkatan yang dipilih
            ->when($filters['angkatan'] ?? null, function (Builder $query, string $angkatan) {
                $query->where('angkatan', $angkatan);
            });
    }
}
