@extends('layouts.app')

@section('title', 'Daftar Mahasiswa')

@section('content')
    {{-- Judul halaman dan jumlah total data --}}
    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Daftar Mahasiswa</h1>
            <p class="text-sm text-slate-500">Kelola data mahasiswa: tambah, lihat, ubah, cari, dan hapus.</p>
        </div>
        <span class="inline-flex w-fit items-center rounded-full bg-indigo-100 px-3 py-1 text-sm font-medium text-indigo-700">
            Total: {{ $mahasiswas->total() }} mahasiswa
        </span>
    </div>

    {{-- Form pencarian dan filter (dikirim dengan method GET) --}}
    <form action="{{ route('mahasiswa.index') }}" method="GET"
          class="mb-6 rounded-xl bg-white p-4 shadow-sm">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="lg:col-span-2">
                <label for="search" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Pencarian
                </label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                       placeholder="Cari NIM, nama, atau program studi..."
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            </div>

            <div>
                <label for="program_studi" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Program Studi
                </label>
                <select name="program_studi" id="program_studi"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="">Semua Program Studi</option>
                    @foreach ($programStudi as $prodi)
                        <option value="{{ $prodi }}" @selected(request('program_studi') === $prodi)>
                            {{ $prodi }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="angkatan" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Angkatan
                </label>
                <select name="angkatan" id="angkatan"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="">Semua Angkatan</option>
                    @foreach ($angkatans as $angkatan)
                        <option value="{{ $angkatan }}" @selected((string) request('angkatan') === (string) $angkatan)>
                            {{ $angkatan }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-3 flex gap-2">
            <button type="submit"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                Terapkan
            </button>
            <a href="{{ route('mahasiswa.index') }}"
               class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100">
                Reset
            </a>
        </div>
    </form>

    {{-- Tabel data mahasiswa --}}
    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">No</th>
                        <th class="px-4 py-3">Foto</th>
                        <th class="px-4 py-3">NIM</th>
                        <th class="px-4 py-3">Nama Lengkap</th>
                        <th class="px-4 py-3">Program Studi</th>
                        <th class="px-4 py-3">Angkatan</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($mahasiswas as $mahasiswa)
                        <tr class="hover:bg-slate-50">
                            {{-- Nomor urut mengikuti halaman paginasi --}}
                            <td class="px-4 py-3 text-slate-500">
                                {{ $mahasiswas->firstItem() + $loop->index }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($mahasiswa->foto)
                                    <img src="{{ asset('storage/' . $mahasiswa->foto) }}"
                                         alt="Foto {{ $mahasiswa->nama_lengkap }}"
                                         class="h-12 w-12 rounded-full object-cover ring-2 ring-slate-100">
                                @else
                                    {{-- Placeholder inisial jika foto belum diunggah --}}
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-100 font-semibold text-indigo-600">
                                        {{ strtoupper(substr($mahasiswa->nama_lengkap, 0, 1)) }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-mono text-slate-700">{{ $mahasiswa->nim }}</td>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $mahasiswa->nama_lengkap }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full bg-sky-100 px-2.5 py-1 text-xs font-medium text-sky-700">
                                    {{ $mahasiswa->program_studi }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $mahasiswa->angkatan }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('mahasiswa.show', $mahasiswa) }}"
                                       class="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-100">
                                        Detail
                                    </a>
                                    <a href="{{ route('mahasiswa.edit', $mahasiswa) }}"
                                       class="rounded-md bg-amber-500 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-amber-600">
                                        Edit
                                    </a>
                                    {{-- Form hapus dengan konfirmasi --}}
                                    <form action="{{ route('mahasiswa.destroy', $mahasiswa) }}" method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus data mahasiswa ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="rounded-md bg-red-600 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-red-700">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-slate-500">
                                Tidak ada data mahasiswa yang cocok dengan pencarian/filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Informasi dan navigasi paginasi --}}
    @if ($mahasiswas->count())
        <div class="mt-4 flex flex-col gap-3 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between">
            <p>
                Menampilkan {{ $mahasiswas->firstItem() }}&ndash;{{ $mahasiswas->lastItem() }}
                dari {{ $mahasiswas->total() }} data.
            </p>
            <div>{{ $mahasiswas->links() }}</div>
        </div>
    @endif
@endsection
