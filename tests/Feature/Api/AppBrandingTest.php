<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppBrandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_branding_is_public_and_returns_structure(): void
    {
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\NgohiSeeder']);

        $response = $this->getJson('/api/v1/app/branding');

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'data' => ['display_name', 'tagline', 'primary_color', 'secondary_color', 'accent_color'],
            ]);
    }
}
