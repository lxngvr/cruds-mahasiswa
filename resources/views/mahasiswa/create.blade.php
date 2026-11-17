@extends('layouts.mahasiswa')

@section('title', 'Tambah Mahasiswa')

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="mb-5">
            <a href="{{ route('mahasiswa.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">
                &larr; Kembali ke daftar
            </a>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">Tambah Mahasiswa</h1>
            <p class="text-sm text-slate-500">Lengkapi data mahasiswa, lalu tekan tombol Simpan.</p>
        </div>

        <form action="{{ route('mahasiswa.store') }}" method="POST" enctype="multipart/form-data"
              class="space-y-5 rounded-xl bg-white p-6 shadow-sm">
            @csrf

            {{-- Field form dipakai bersama dengan halaman edit --}}
            @include('mahasiswa._form', ['mahasiswa' => null])

            <div class="flex items-center gap-3 border-t border-slate-100 pt-4">
                <button type="submit"
                        class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
                    Simpan
                </button>
                <a href="{{ route('mahasiswa.index') }}"
                   class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
