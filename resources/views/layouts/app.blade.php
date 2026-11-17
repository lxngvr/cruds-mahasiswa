<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CRUD Mahasiswa')</title>
    {{-- Tailwind CSS melalui CDN, sesuai modul praktikum --}}
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
    {{-- Navigasi utama aplikasi --}}
    <nav class="bg-indigo-700 text-white shadow">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
            <a href="{{ route('mahasiswa.index') }}" class="text-lg font-bold tracking-tight">
                CRUD Mahasiswa
            </a>
            <a href="{{ route('mahasiswa.create') }}"
               class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-50">
                + Tambah Mahasiswa
            </a>
        </div>
    </nav>

    <main class="mx-auto max-w-6xl px-4 py-8">
        {{-- Notifikasi sukses setelah create/update/delete --}}
        @if (session('success'))
            <div class="mb-5 rounded-lg border border-green-300 bg-green-100 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="border-t border-slate-200 bg-white py-4 text-center text-xs text-slate-500">
        Praktikum Pemrograman Web &middot; CRUD Data Mahasiswa
    </footer>
</body>
</html>
