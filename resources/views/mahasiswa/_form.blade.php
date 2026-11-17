{{-- Partial form yang dipakai bersama oleh halaman create dan edit --}}
@if ($errors->any())
    <div class="rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700">
        <p class="font-semibold">Periksa kembali isian berikut:</p>
        <ul class="mt-1 list-inside list-disc">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Input NIM --}}
<div>
    <label for="nim" class="mb-1 block text-sm font-medium text-slate-700">
        NIM <span class="text-red-500">*</span>
    </label>
    <input type="text" name="nim" id="nim"
           value="{{ old('nim', $mahasiswa?->nim) }}"
           maxlength="10" inputmode="numeric" placeholder="Contoh: 2023110001"
           class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 @error('nim') border-red-500 @else border-slate-300 @enderror">
    <p class="mt-1 text-xs text-slate-500">Tepat 10 digit angka dan belum terdaftar.</p>
    @error('nim')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

{{-- Input nama lengkap --}}
<div>
    <label for="nama_lengkap" class="mb-1 block text-sm font-medium text-slate-700">
        Nama Lengkap <span class="text-red-500">*</span>
    </label>
    <input type="text" name="nama_lengkap" id="nama_lengkap"
           value="{{ old('nama_lengkap', $mahasiswa?->nama_lengkap) }}"
           maxlength="100" placeholder="Nama lengkap mahasiswa"
           class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 @error('nama_lengkap') border-red-500 @else border-slate-300 @enderror">
    <p class="mt-1 text-xs text-slate-500">Hanya huruf, spasi, titik, dan apostrof.</p>
    @error('nama_lengkap')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div class="grid gap-4 sm:grid-cols-2">
    {{-- Pilihan program studi --}}
    <div>
        <label for="program_studi" class="mb-1 block text-sm font-medium text-slate-700">
            Program Studi <span class="text-red-500">*</span>
        </label>
        <select name="program_studi" id="program_studi"
                class="w-full rounded-lg border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 @error('program_studi') border-red-500 @else border-slate-300 @enderror">
            <option value="" disabled @selected(! old('program_studi', $mahasiswa?->program_studi))>
                -- Pilih Program Studi --
            </option>
            @foreach ($programStudi as $prodi)
                <option value="{{ $prodi }}" @selected(old('program_studi', $mahasiswa?->program_studi) === $prodi)>
                    {{ $prodi }}
                </option>
            @endforeach
        </select>
        @error('program_studi')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Input tahun angkatan --}}
    <div>
        <label for="angkatan" class="mb-1 block text-sm font-medium text-slate-700">
            Angkatan <span class="text-red-500">*</span>
        </label>
        <input type="number" name="angkatan" id="angkatan"
               value="{{ old('angkatan', $mahasiswa?->angkatan) }}"
               min="2018" max="{{ date('Y') }}" placeholder="Contoh: 2024"
               class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 @error('angkatan') border-red-500 @else border-slate-300 @enderror">
        <p class="mt-1 text-xs text-slate-500">Antara tahun 2018 sampai {{ date('Y') }}.</p>
        @error('angkatan')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

{{-- Upload foto --}}
<div>
    <label for="foto" class="mb-1 block text-sm font-medium text-slate-700">
        Foto <span class="text-xs font-normal text-slate-400">(opsional)</span>
    </label>

    {{-- Tampilkan foto lama saat mode edit --}}
    @if ($mahasiswa?->foto)
        <div class="mb-2 flex items-center gap-3">
            <img src="{{ asset('storage/' . $mahasiswa->foto) }}"
                 alt="Foto saat ini"
                 class="h-16 w-16 rounded-lg object-cover ring-1 ring-slate-200">
            <p class="text-xs text-slate-500">Foto saat ini. Unggah file baru untuk menggantinya.</p>
        </div>
    @endif

    <input type="file" name="foto" id="foto" accept=".jpg,.jpeg,.png,image/jpeg,image/png"
           class="block w-full cursor-pointer rounded-lg border px-3 py-2 text-sm text-slate-600 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100 @error('foto') border-red-500 @else border-slate-300 @enderror">
    <p class="mt-1 text-xs text-slate-500">JPG, JPEG, atau PNG. Maksimal 2 MB dan minimal 100x100 piksel.</p>
    @error('foto')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

{{-- Input catatan tambahan (teks bebas, maksimal 500 karakter) --}}
<div>
    <label for="catatan" class="mb-1 block text-sm font-medium text-slate-700">
        Catatan <span class="text-xs font-normal text-slate-400">(opsional, maksimal 500 karakter)</span>
    </label>
    <textarea name="catatan" id="catatan" rows="4" maxlength="500"
              placeholder="Catatan tambahan tentang mahasiswa..."
              class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 @error('catatan') border-red-500 @else border-slate-300 @enderror">{{ old('catatan', $mahasiswa?->catatan) }}</textarea>
    @error('catatan')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
