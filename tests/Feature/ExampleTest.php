<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Pengguna yang sudah login diarahkan ke daftar mahasiswa dari halaman awal.
     */
    public function test_pengguna_login_diarahkan_ke_daftar_mahasiswa(): void
    {
        $this->actingAs(User::factory()->make());

        $this->get('/')->assertRedirect('/mahasiswa');
    }
}
