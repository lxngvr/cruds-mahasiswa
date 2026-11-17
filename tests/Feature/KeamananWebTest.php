<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KeamananWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_tamu_tidak_bisa_mengakses_fitur_mahasiswa(): void
    {
        $this->get(route('mahasiswa.index'))->assertRedirect(route('login'));
        $this->get(route('mahasiswa.create'))->assertRedirect(route('login'));
        $this->post(route('mahasiswa.store'), [])->assertRedirect(route('login'));
    }

    public function test_security_headers_terpasang_pada_semua_response(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk()
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        $csp = (string) $response->headers->get('Content-Security-Policy');

        $this->assertStringContainsString('https://cdn.tailwindcss.com', $csp);
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);
    }

    public function test_meta_csrf_token_tersedia_di_layout(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('mahasiswa.index'))
            ->assertOk()
            ->assertSee('name="csrf-token"', false);
    }

    public function test_demo_sqli_rentan_bocor_dan_versi_aman_tidak(): void
    {
        $user = User::factory()->create();

        Mahasiswa::create([
            'nim' => '2024000001',
            'nama_lengkap' => 'Andi Pratama',
            'program_studi' => 'Informatika',
            'angkatan' => 2024,
        ]);
        Mahasiswa::create([
            'nim' => '2024000002',
            'nama_lengkap' => 'Budi Santoso',
            'program_studi' => 'Sistem Informasi',
            'angkatan' => 2023,
        ]);

        // Payload klasik: menutup string lalu memaksa kondisi selalu benar.
        // Bagian setelah "--" adalah komentar, valid di MySQL maupun SQLite.
        $payload = urlencode("' OR 1=1 -- x");

        // Versi rentan mengembalikan seluruh baris karena input masuk ke string SQL.
        $this->actingAs($user)
            ->get('/demo/sqli/rentan?q='.$payload)
            ->assertOk()
            ->assertJsonPath('mode', 'RENTAN')
            ->assertJsonPath('jumlah', 2);

        // Versi aman memperlakukan payload sebagai teks biasa sehingga tidak ada hasil.
        $this->actingAs($user)
            ->get('/demo/sqli/aman?q='.$payload)
            ->assertOk()
            ->assertJsonPath('mode', 'AMAN')
            ->assertJsonPath('jumlah', 0);
    }

    public function test_route_demo_sqli_tetap_wajib_login(): void
    {
        $this->get('/demo/sqli/rentan')->assertRedirect(route('login'));
        $this->get('/demo/sqli/aman')->assertRedirect(route('login'));
    }
}
