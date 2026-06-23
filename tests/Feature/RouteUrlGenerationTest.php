<?php

namespace Tests\Feature;

use Tests\TestCase;

class RouteUrlGenerationTest extends TestCase
{
    public function test_named_routes_generate_application_root_paths_not_subdirectory_prefix(): void
    {
        $routes = [
            'home' => '/',
            'about' => '/about',
            'login' => '/login',
            'register.store' => '/register',
            'logout' => '/logout',
            'profile.show' => '/profile',
            'courses' => '/courses',
        ];

        foreach ($routes as $name => $expectedPath) {
            $this->assertSame($expectedPath, route($name, [], false), "Route [{$name}] path mismatch.");

            $absoluteUrl = route($name);
            $this->assertStringStartsWith('http://localhost', $absoluteUrl, "Route [{$name}] absolute URL mismatch.");
            $this->assertStringNotContainsString('/81-maktab/public', $absoluteUrl, "Route [{$name}] must not use subdirectory prefix.");
        }
    }

    public function test_post_requests_to_auth_routes_are_reachable(): void
    {
        $this->post(route('register.store'), [])
            ->assertInvalid(['first_name', 'last_name', 'email', 'phone', 'password']);

        $this->post(route('authenticate'), [])
            ->assertInvalid(['email', 'password']);
    }
}
