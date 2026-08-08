<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_shows_member_login(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Member sign in');
    }

    public function test_admin_login_page_is_displayed(): void
    {
        $response = $this->get('/admin');

        $response->assertOk();
        $response->assertSee('System access login');
    }
}
