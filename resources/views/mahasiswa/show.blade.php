@extends('layouts.mahasiswa')

@section('title', 'Detail Mahasiswa')

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('mahasiswa.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">
            &larr; Kembali ke daftar
        </a>

        <div class="flex gap-2">
            <a href="{{ route('mahasiswa.edit', $mahasiswa) }}"
               class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-600">
                Edit
            </a>
            <form action="{{ route('mahasiswa.destroy', $mahasiswa) }}" method="POST"
                  onsubmit="return confirm('Yakin ingin menghapus data mahasiswa ini?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700">
                    Hapus
                </button>
            </form>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
        <div class="flex flex-col gap-6 p-6 sm:flex-row">
            {{-- Foto besar atau inisial jika belum ada foto --}}
            @if ($mahasiswa->foto)
                <img src="{{ asset('storage/' . $mahasiswa->foto) }}"
                     alt="Foto {{ $mahasiswa->nama_lengkap }}"
                     class="h-40 w-40 shrink-0 rounded-xl object-cover ring-2 ring-slate-100">
            @else
                <div class="flex h-40 w-40 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-5xl font-bold text-indigo-600">
                    {{ strtoupper(substr($mahasiswa->nama_lengkap, 0, 1)) }}
                </div>
            @endif

            <div class="flex-1">
                <h1 class="text-2xl font-bold text-slate-900">{{ $mahasiswa->nama_lengkap }}</h1>
                <p class="mt-1 font-mono text-sm text-slate-500">{{ $mahasiswa->nim }}</p>

                <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Program Studi</dt>
                        <dd class="mt-1 font-medium text-slate-800">{{ $mahasiswa->program_studi }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Angkatan</dt>
                        <dd class="mt-1 font-medium text-slate-800">{{ $mahasiswa->angkatan }}</dd>
                    </div>
                    {{-- Catatan dirender dengan {{ }} agar aman dari XSS --}}
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Catatan</dt>
                        <dd class="mt-1 whitespace-pre-line text-slate-700">{{ $mahasiswa->catatan ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Dibuat</dt>
                        <dd class="mt-1 text-slate-700">{{ $mahasiswa->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Diperbarui</dt>
                        <dd class="mt-1 text-slate-700">{{ $mahasiswa->updated_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
@endsection
