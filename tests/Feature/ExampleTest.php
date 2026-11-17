<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Halaman awal harus diarahkan ke daftar mahasiswa.
     */
    public function test_halaman_awal_mengarah_ke_daftar_mahasiswa(): void
    {
        $this->get('/')->assertRedirect('/mahasiswa');
    }
}
