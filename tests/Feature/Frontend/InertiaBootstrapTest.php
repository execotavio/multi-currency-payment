<?php

namespace Tests\Feature\Frontend;

use Tests\TestCase;

class InertiaBootstrapTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_login_page_renders_the_inertia_root(): void
    {
        $response = $this->get('/login');

        $response->assertOk()
            ->assertSee('data-page', false)
            ->assertSee('Auth\/Login', false)
            ->assertSee('Multi-Currency Payment');
    }

    public function test_frontend_routes_render(): void
    {
        foreach ([
            '/register',
            '/payment-requests',
            '/payment-requests/create',
            '/payment-requests/123',
        ] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertSee('data-page', false);
        }
    }
}
