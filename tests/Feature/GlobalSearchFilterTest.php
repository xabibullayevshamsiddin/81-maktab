<?php

namespace Tests\Feature;

use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GlobalSearchFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_search_ignores_noise_and_metadata_only_terms(): void
    {
        DB::table('users')->insert([
            'id' => 1,
            'name' => 'Course Creator',
            'email' => 'global-search@example.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('courses')->insert([
            [
                'id' => 1,
                'teacher_id' => null,
                'created_by' => 1,
                'title' => 'kimyo',
                'price' => 'fsd',
                'duration' => 'dsfds',
                'description' => 'Kimyo tayyorlov kursi.',
                'start_date' => '2026-04-25',
                'status' => Course::STATUS_PUBLISHED,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'teacher_id' => null,
                'created_by' => 1,
                'title' => 'Noise test',
                'price' => "300 000 so'm",
                'duration' => '2 oy',
                'description' => 'fsd dsfds foydasiz dali davomiyligi boshlanishi narxida',
                'start_date' => '2026-05-10',
                'status' => Course::STATUS_PUBLISHED,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        foreach (['fsd', 'dsfds', 'foydasiz', 'dali', 'davomiyligi', 'boshlanishi', 'narxida', '25.04.2026'] as $query) {
            $this->getJson(route('search', ['q' => $query]))
                ->assertOk()
                ->assertJsonCount(0, 'results');
        }

        $this->getJson(route('search', ['q' => 'kimyo']))
            ->assertOk()
            ->assertJsonPath('results.0.type', 'course')
            ->assertJsonPath('results.0.title', 'kimyo');
    }
}
