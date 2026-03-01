<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Kelas dasar untuk semua test dalam aplikasi
 * 
 * Gunakan kelas ini sebagai parent untuk semua test case
 * Semua fungsionalitas umum untuk testing bisa ditambahkan di sini
 */
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
}
