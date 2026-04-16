<?php

use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the application returns a successful response', function () {
    $this->seed(CatalogSeeder::class);

    $response = $this->get('/');

    $response->assertStatus(200);
});
