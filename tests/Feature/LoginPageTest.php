<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoginPageTest extends TestCase
{
    public function test_login_page_returns_ok_when_not_installed_or_redirects(): void
    {
        $response = $this->get('/login');
        $this->assertTrue(in_array($response->status(), [200, 302], true));
    }

    public function test_install_route_returns_ok_or_redirects(): void
    {
        $response = $this->get('/install');
        $this->assertTrue(in_array($response->status(), [200, 302], true));
    }
}
