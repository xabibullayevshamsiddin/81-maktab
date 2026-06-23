<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Post;
use App\Models\Teacher;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * Butun saytning sitemap.xml faylini generatsiya qiladi.
     * Qidiruv tizimlari (Google, Yandex) shu yo'l orqali barcha
     * ommaviy sahifalarni topadi va indekslaydi.
     */
    public function index(): Response
    {
        $urls = Cache::remember('sitemap_urls', now()->addHours(6), function () {
            $urls = [];

            // Statik ommaviy sahifalar
            $staticRoutes = [
                ['route' => 'home', 'priority' => '1.0', 'freq' => 'daily'],
                ['route' => 'about', 'priority' => '0.7', 'freq' => 'monthly'],
                ['route' => 'courses', 'priority' => '0.9', 'freq' => 'weekly'],
                ['route' => 'post', 'priority' => '0.9', 'freq' => 'daily'],
                ['route' => 'teacher', 'priority' => '0.8', 'freq' => 'weekly'],
                ['route' => 'calendar', 'priority' => '0.6', 'freq' => 'weekly'],
                ['route' => 'contact', 'priority' => '0.5', 'freq' => 'monthly'],
            ];

            foreach ($staticRoutes as $item) {
                $urls[] = [
                    'loc' => route($item['route']),
                    'lastmod' => now()->toAtomString(),
                    'changefreq' => $item['freq'],
                    'priority' => $item['priority'],
                ];
            }

            // Maqolalar (posts)
            Post::query()
                ->select(['slug', 'updated_at'])
                ->whereNotNull('slug')
                ->latest('updated_at')
                ->limit(2000)
                ->get()
                ->each(function (Post $post) use (&$urls) {
                    $urls[] = [
                        'loc' => route('post.show', $post->slug),
                        'lastmod' => optional($post->updated_at)->toAtomString() ?? now()->toAtomString(),
                        'changefreq' => 'weekly',
                        'priority' => '0.7',
                    ];
                });

            // Ustozlar (teachers)
            Teacher::query()
                ->select(['slug', 'updated_at'])
                ->where('is_active', true)
                ->whereNotNull('slug')
                ->get()
                ->each(function (Teacher $teacher) use (&$urls) {
                    $urls[] = [
                        'loc' => route('teacher.show', $teacher->slug),
                        'lastmod' => optional($teacher->updated_at)->toAtomString() ?? now()->toAtomString(),
                        'changefreq' => 'monthly',
                        'priority' => '0.6',
                    ];
                });

            // Kurslar (faqat ommaviy/published)
            Course::query()
                ->select(['id', 'updated_at'])
                ->where('status', Course::STATUS_PUBLISHED)
                ->get()
                ->each(function (Course $course) use (&$urls) {
                    $urls[] = [
                        'loc' => route('courses.show', $course->id),
                        'lastmod' => optional($course->updated_at)->toAtomString() ?? now()->toAtomString(),
                        'changefreq' => 'weekly',
                        'priority' => '0.6',
                    ];
                });

            return $urls;
        });

        $content = view('sitemap', compact('urls'))->render();

        return response($content, 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * robots.txt — qidiruv tizimlariga sitemap manzilini ko'rsatadi
     * va admin/profil sahifalarini indekslashdan chiqaradi.
     */
    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /dashboard',
            'Disallow: /profile',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /exam-session',
            '',
            'Sitemap: '.url('sitemap.xml'),
            '',
        ];

        return response(implode("\n", $lines), 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
