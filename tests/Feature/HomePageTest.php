<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_loads(): void
    {
        $response = $this->get(route("home"));

        $response->assertOk();
    }

    public function test_about_page_loads(): void
    {
        $response = $this->get(route("about"));

        $response->assertOk();
    }

    public function test_contact_page_loads(): void
    {
        $response = $this->get(route("contact"));

        $response->assertOk();
    }

    public function test_courses_page_loads(): void
    {
        $response = $this->get(route("courses"));

        $response->assertOk();
    }

    public function test_teacher_page_loads(): void
    {
        $response = $this->get(route("teacher"));

        $response->assertOk();
    }

    public function test_post_page_loads(): void
    {
        $response = $this->get(route("post"));

        $response->assertOk();
    }

    public function test_calendar_page_loads(): void
    {
        $response = $this->get(route("calendar"));

        $response->assertOk();
    }

    public function test_feature_requests_page_loads(): void
    {
        $response = $this->get(route("feature-requests.index"));

        $response->assertOk();
    }

    public function test_privacy_policy_page(): void
    {
        $response = $this->get(route("privacy-policy"));

        $response->assertOk();
    }

    public function test_terms_page(): void
    {
        $response = $this->get(route("terms"));

        $response->assertOk();
    }

    public function test_search_returns_json(): void
    {
        $response = $this->getJson(route("search", ["q" => "matematika"]));

        $response->assertOk();
        $response->assertJsonStructure(["results"]);
    }

    public function test_sitemap_loads(): void
    {
        $response = $this->get(route("sitemap"));

        $response->assertOk();
    }

    public function test_robots_loads(): void
    {
        $response = $this->get(route("robots"));

        $response->assertOk();
    }

    public function test_contact_store(): void
    {
        $response = $this->post(route("contact.store"), [
            "name" => "Ali",
            "email" => "ali@example.com",
            "message" => "Test message.",
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas("contact_messages", ["email" => "ali@example.com"]);
    }

    public function test_contact_validation(): void
    {
        $response = $this->post(route("contact.store"), [
            "name" => "",
            "email" => "invalid",
            "message" => "",
        ]);

        $response->assertSessionHasErrors(["name", "email", "message"]);
    }
}