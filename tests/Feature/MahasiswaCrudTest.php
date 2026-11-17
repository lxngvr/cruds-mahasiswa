<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MahasiswaCrudTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Penghitung NIM agar setiap data uji memiliki NIM 10 digit yang unik.
     */
    private int $nimCounter = 2024000000;

    /**
     * Semua skenario CRUD dijalankan sebagai pengguna yang sudah login.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    /**
     * Helper untuk membuat satu data mahasiswa uji.
     */
    private function buatMahasiswa(array $overrides = []): Mahasiswa
    {
        $this->nimCounter++;

        return Mahasiswa::create(array_merge([
            'nim' => (string) $this->nimCounter,
            'nama_lengkap' => 'Mahasiswa Uji',
            'program_studi' => 'Informatika',
            'angkatan' => 2024,
            'foto' => null,
            'catatan' => null,
        ], $overrides));
    }

    /**
     * Data valid untuk form create/update.
     */
    private function dataValid(array $overrides = []): array
    {
        return array_merge([
            'nim' => '2024000999',
            'nama_lengkap' => 'Siti Aminah',
            'program_studi' => 'Sistem Informasi',
            'angkatan' => 2024,
            'catatan' => null,
        ], $overrides);
    }

    public function test_index_menampilkan_data_dengan_paginasi_10_per_halaman(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            $this->buatMahasiswa(['nama_lengkap' => "Mahasiswa Ke-{$i}"]);
        }

        $this->get('/mahasiswa')
            ->assertOk()
            ->assertSee('Total: 12 mahasiswa')
            ->assertSee('dari 12 data');

        // Halaman kedua tetap dapat diakses dan parameter paginasi bekerja.
        $this->get('/mahasiswa?page=2')
            ->assertOk()
            ->assertSee('dari 12 data');
    }

    public function test_pencarian_nim_nama_dan_prodi_serta_filter(): void
    {
        $this->buatMahasiswa([
            'nim' => '2024000111',
            'nama_lengkap' => 'Andi Pratama',
            'program_studi' => 'Informatika',
            'angkatan' => 2024,
        ]);
        $this->buatMahasiswa([
            'nim' => '2024000222',
            'nama_lengkap' => 'Budi Santoso',
            'program_studi' => 'Sistem Informasi',
            'angkatan' => 2023,
        ]);
        $this->buatMahasiswa([
            'nim' => '2024000333',
            'nama_lengkap' => 'Citra Lestari',
            'program_studi' => 'Teknik Komputer',
            'angkatan' => 2024,
        ]);

        // Cari berdasarkan nama.
        $this->get('/mahasiswa?search=Andi')
            ->assertOk()
            ->assertSee('Andi Pratama')
            ->assertDontSee('Budi Santoso');

        // Cari berdasarkan NIM.
        $this->get('/mahasiswa?search=2024000222')
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertDontSee('Andi Pratama');

        // Cari berdasarkan program studi.
        $this->get('/mahasiswa?search='.urlencode('Sistem Informasi'))
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertDontSee('Andi Pratama');

        // Filter dropdown program studi.
        $this->get('/mahasiswa?program_studi='.urlencode('Informatika'))
            ->assertOk()
            ->assertSee('Andi Pratama')
            ->assertDontSee('Budi Santoso');

        // Filter dropdown angkatan.
        $this->get('/mahasiswa?angkatan=2023')
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertDontSee('Andi Pratama');

        // Tambah data agar hasil filter lebih dari satu halaman.
        for ($i = 1; $i <= 11; $i++) {
            $this->buatMahasiswa([
                'nama_lengkap' => "Andi Tambahan {$i}",
                'program_studi' => 'Informatika',
            ]);
        }

        // Parameter filter tetap terbawa pada tautan paginasi (withQueryString).
        $response = $this->get('/mahasiswa?search=Andi&program_studi=Informatika');
        $response->assertOk();
        $this->assertStringContainsString('search=Andi', $response->getContent());
        $this->assertStringContainsString('program_studi=Informatika', $response->getContent());
    }

    public function test_store_menyimpan_data_dan_mengunggah_foto(): void
    {
        Storage::fake('public');

        $response = $this->post(route('mahasiswa.store'), $this->dataValid([
            'foto' => UploadedFile::fake()->image('foto.jpg', 400, 400),
            'catatan' => 'Catatan dari pengujian.',
        ]));

        $response->assertRedirect(route('mahasiswa.index'))
            ->assertSessionHas('success');

        $mahasiswa = Mahasiswa::where('nim', '2024000999')->firstOrFail();

        $this->assertDatabaseHas('mahasiswas', [
            'nim' => '2024000999',
            'nama_lengkap' => 'Siti Aminah',
            'program_studi' => 'Sistem Informasi',
            'angkatan' => 2024,
            'catatan' => 'Catatan dari pengujian.',
        ]);
        $this->assertNotNull($mahasiswa->foto);
        $this->assertStringStartsWith('mahasiswa/', $mahasiswa->foto);
        Storage::disk('public')->assertExists($mahasiswa->foto);
    }

    public function test_store_menolak_nim_duplikat(): void
    {
        $this->buatMahasiswa(['nim' => '2024000999']);

        $this->post(route('mahasiswa.store'), $this->dataValid())
            ->assertSessionHasErrors('nim');

        $this->assertDatabaseCount('mahasiswas', 1);
    }

    public function test_store_menolak_input_yang_tidak_sesuai_aturan_keamanan(): void
    {
        // NIM harus tepat 10 digit angka.
        $this->post(route('mahasiswa.store'), $this->dataValid([
            'nim' => '202400000000999',
        ]))->assertSessionHasErrors('nim');

        // Nama tidak boleh mengandung angka.
        $this->post(route('mahasiswa.store'), $this->dataValid([
            'nim' => '2024000666',
            'nama_lengkap' => 'Nama 123',
        ]))->assertSessionHasErrors('nama_lengkap');

        // Angkatan di luar rentang 2018 sampai tahun ini.
        $this->post(route('mahasiswa.store'), $this->dataValid([
            'nim' => '2024000555',
            'angkatan' => 2017,
        ]))->assertSessionHasErrors('angkatan');

        $this->post(route('mahasiswa.store'), $this->dataValid([
            'nim' => '2024000556',
            'angkatan' => (int) date('Y') + 1,
        ]))->assertSessionHasErrors('angkatan');

        // Foto yang lebih kecil dari 100x100 piksel ditolak.
        $this->post(route('mahasiswa.store'), $this->dataValid([
            'nim' => '2024000444',
            'foto' => UploadedFile::fake()->image('kecil.jpg', 50, 50),
        ]))->assertSessionHasErrors('foto');

        // Format foto selain JPG/JPEG/PNG ditolak.
        $this->post(route('mahasiswa.store'), $this->dataValid([
            'nim' => '2024000333',
            'foto' => UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'),
        ]))->assertSessionHasErrors('foto');

        // Foto lebih dari 2 MB ditolak.
        $this->post(route('mahasiswa.store'), $this->dataValid([
            'nim' => '2024000222',
            'foto' => UploadedFile::fake()->create('besar.jpg', 3000, 'image/jpeg'),
        ]))->assertSessionHasErrors('foto');

        // Catatan maksimal 500 karakter.
        $this->post(route('mahasiswa.store'), $this->dataValid([
            'nim' => '2024000111',
            'catatan' => str_repeat('a', 501),
        ]))->assertSessionHasErrors('catatan');

        // Pastikan tidak ada data yang tetap tersimpan dari request tidak valid.
        $this->assertDatabaseCount('mahasiswas', 0);
    }

    public function test_catatan_disimpan_dan_ditampilkan_dengan_aman(): void
    {
        $catatan = '<script>alert("xss")</script>';

        $this->post(route('mahasiswa.store'), $this->dataValid([
            'nim' => '2024000123',
            'catatan' => $catatan,
        ]))->assertSessionHasNoErrors();

        $mahasiswa = Mahasiswa::where('nim', '2024000123')->firstOrFail();

        $this->assertSame($catatan, $mahasiswa->catatan);

        // Konten berbahaya harus tampil sebagai teks biasa, bukan HTML aktif.
        $this->get(route('mahasiswa.show', $mahasiswa))
            ->assertOk()
            ->assertSee('&lt;script&gt;alert', false)
            ->assertDontSee($catatan, false);
    }

    public function test_update_tanpa_foto_baru_mempertahankan_foto_lama(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('mahasiswa/foto-lama.jpg', 'isi-foto-lama');

        $mahasiswa = $this->buatMahasiswa([
            'nim' => '2024000444',
            'nama_lengkap' => 'Nama Lama',
            'foto' => 'mahasiswa/foto-lama.jpg',
        ]);

        $this->put(route('mahasiswa.update', $mahasiswa), $this->dataValid([
            'nim' => '2024000444',
            'nama_lengkap' => 'Nama Baru',
        ]))->assertRedirect(route('mahasiswa.index'))->assertSessionHasNoErrors();

        $mahasiswa->refresh();

        $this->assertSame('Nama Baru', $mahasiswa->nama_lengkap);
        $this->assertSame('mahasiswa/foto-lama.jpg', $mahasiswa->foto);
        Storage::disk('public')->assertExists('mahasiswa/foto-lama.jpg');
    }

    public function test_update_dengan_foto_baru_menghapus_foto_lama(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('mahasiswa/foto-lama.jpg', 'isi-foto-lama');

        $mahasiswa = $this->buatMahasiswa([
            'nim' => '2024000555',
            'foto' => 'mahasiswa/foto-lama.jpg',
        ]);

        $this->put(route('mahasiswa.update', $mahasiswa), $this->dataValid([
            'nim' => '2024000555',
            'foto' => UploadedFile::fake()->image('foto-baru.png', 300, 300),
        ]))->assertSessionHasNoErrors();

        $mahasiswa->refresh();

        $this->assertNotSame('mahasiswa/foto-lama.jpg', $mahasiswa->foto);
        Storage::disk('public')->assertMissing('mahasiswa/foto-lama.jpg');
        Storage::disk('public')->assertExists($mahasiswa->foto);
    }

    public function test_update_mengabaikan_nim_milik_sendiri(): void
    {
        $mahasiswa = $this->buatMahasiswa(['nim' => '2024000666']);

        // Mengirim NIM yang sama dengan data sendiri tidak boleh dianggap duplikat.
        $this->put(route('mahasiswa.update', $mahasiswa), $this->dataValid([
            'nim' => '2024000666',
            'nama_lengkap' => 'Nama Setelah Edit',
        ]))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('mahasiswas', [
            'id' => $mahasiswa->id,
            'nim' => '2024000666',
            'nama_lengkap' => 'Nama Setelah Edit',
        ]);
    }

    public function test_destroy_menghapus_data_dan_foto(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('mahasiswa/foto-hapus.jpg', 'isi-foto');

        $mahasiswa = $this->buatMahasiswa([
            'nim' => '2024000777',
            'foto' => 'mahasiswa/foto-hapus.jpg',
        ]);

        $this->delete(route('mahasiswa.destroy', $mahasiswa))
            ->assertRedirect(route('mahasiswa.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('mahasiswas', ['id' => $mahasiswa->id]);
        Storage::disk('public')->assertMissing('mahasiswa/foto-hapus.jpg');
    }

    public function test_halaman_show_dan_edit_menampilkan_data(): void
    {
        $mahasiswa = $this->buatMahasiswa([
            'nim' => '2024000123',
            'nama_lengkap' => 'Dewi Anggraini',
            'program_studi' => 'Teknik Komputer',
            'angkatan' => 2022,
            'catatan' => 'Catatan penting mahasiswa.',
        ]);

        $this->get(route('mahasiswa.show', $mahasiswa))
            ->assertOk()
            ->assertSee('Dewi Anggraini')
            ->assertSee('2024000123')
            ->assertSee('Teknik Komputer')
            ->assertSee('Catatan penting mahasiswa.');

        $this->get(route('mahasiswa.edit', $mahasiswa))
            ->assertOk()
            ->assertSee('Dewi Anggraini')
            ->assertSee('value="2024000123"', false);
    }
}
