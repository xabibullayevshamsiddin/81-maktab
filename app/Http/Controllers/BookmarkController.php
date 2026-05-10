<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\Course;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    public function index(Request $request)
    {
        $bookmarks = Bookmark::query()
            ->where('user_id', $request->user()->id)
            ->with(['bookmarkable' => function (MorphTo $morphTo): void {
                $morphTo->constrain([
                    Post::class => fn ($q) => $q
                        ->select([
                            'id',
                            'category_id',
                            'title',
                            'title_en',
                            'short_content',
                            'short_content_en',
                            'image',
                            'slug',
                            'views',
                            'post_kind',
                            'video_path',
                            'video_url',
                            'created_at',
                        ])
                        ->with(['category:id,name,name_en'])
                        ->withCount(['comments', 'likes']),
                    Teacher::class => fn ($q) => $q
                        ->select([
                            'id',
                            'full_name',
                            'slug',
                            'subject',
                            'subject_en',
                            'lavozim',
                            'lavozim_en',
                            'toifa',
                            'toifa_en',
                            'experience_years',
                            'grades',
                            'achievements',
                            'achievements_en',
                            'image',
                            'is_active',
                        ])
                        ->withCount('likes'),
                    Course::class => fn ($q) => $q
                        ->select([
                            'id',
                            'teacher_id',
                            'created_by',
                            'title',
                            'title_en',
                            'price',
                            'price_en',
                            'duration',
                            'duration_en',
                            'description',
                            'description_en',
                            'image',
                            'start_date',
                            'status',
                            'created_at',
                        ])
                        ->with([
                            'teacher:id,full_name,slug,image,is_active,subject,subject_en',
                            'creator:id,name,first_name,last_name,avatar,role_id',
                            'creator.roleRelation:id,name,label,level',
                        ]),
                ]);
            }])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $pageBookmarks = $bookmarks->getCollection();

        $postIds = $pageBookmarks
            ->filter(fn (Bookmark $b) => $b->bookmarkable instanceof Post)
            ->pluck('bookmarkable_id');

        $likedPostIds = $postIds->isNotEmpty()
            ? PostLike::query()
                ->where('user_id', $request->user()->id)
                ->whereIn('post_id', $postIds)
                ->pluck('post_id')
            : collect();

        $bookmarkedPostIds = $postIds;
        $bookmarkedTeacherIds = $pageBookmarks
            ->filter(fn (Bookmark $b) => $b->bookmarkable instanceof Teacher)
            ->pluck('bookmarkable_id');
        $bookmarkedCourseIds = $pageBookmarks
            ->filter(fn (Bookmark $b) => $b->bookmarkable instanceof Course)
            ->pluck('bookmarkable_id');

        return view('profile.bookmarks.index', [
            'bookmarks' => $bookmarks,
            'likedPostIds' => $likedPostIds,
            'bookmarkedPostIds' => $bookmarkedPostIds,
            'bookmarkedTeacherIds' => $bookmarkedTeacherIds,
            'bookmarkedCourseIds' => $bookmarkedCourseIds,
        ]);
    }

    public function togglePost(Request $request, Post $post)
    {
        return $this->toggleFor($request, Post::class, $post->id);
    }

    public function toggleTeacher(Request $request, Teacher $teacher)
    {
        abort_unless($teacher->is_active, 404);

        return $this->toggleFor($request, Teacher::class, $teacher->id);
    }

    public function toggleCourse(Request $request, Course $course)
    {
        abort_unless(
            $course->status === Course::STATUS_PUBLISHED
            && (! $course->teacher_id || ($course->teacher && $course->teacher->is_active)),
            404
        );

        return $this->toggleFor($request, Course::class, $course->id);
    }

    private function toggleFor(Request $request, string $bookmarkableClass, int $bookmarkableId)
    {
        if ($response = $this->ensureCanBookmark($request)) {
            return $response;
        }

        $bookmark = Bookmark::query()
            ->where('user_id', $request->user()->id)
            ->where('bookmarkable_type', $bookmarkableClass)
            ->where('bookmarkable_id', $bookmarkableId)
            ->first();

        if ($bookmark) {
            $bookmark->delete();
            $bookmarked = false;
            $message = __('public.bookmark.removed');
            $toastType = 'warning';
        } else {
            Bookmark::query()->create([
                'user_id' => $request->user()->id,
                'bookmarkable_type' => $bookmarkableClass,
                'bookmarkable_id' => $bookmarkableId,
            ]);
            $bookmarked = true;
            $message = __('public.bookmark.added');
            $toastType = 'success';
        }

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $message,
                'bookmarked' => $bookmarked,
                'toast_type' => $toastType,
            ]);
        }

        return back()
            ->with('success', $message)
            ->with('toast_type', $toastType);
    }

    private function ensureCanBookmark(Request $request)
    {
        if (! $request->user()) {
            return $this->denyBookmark($request, __("Like bosish va izoh yozish uchun avval ro'yxatdan o'ting."), 401);
        }

        if (! $request->user()->isActive()) {
            return $this->denyBookmark($request, 'Siz block qilingansiz. Saqlash mumkin emas.', 403);
        }

        return null;
    }

    private function denyBookmark(Request $request, string $message, int $statusCode)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'ok' => false,
                'message' => $message,
                'toast_type' => 'warning',
            ], $statusCode);
        }

        return back()
            ->with('error', $message)
            ->with('toast_type', 'warning');
    }
}
