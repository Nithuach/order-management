<?php

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the application returns a successful response', function () {
    Product::factory()->count(3)->create();

    $response = $this->get('/');

    $response->assertStatus(200);
});
