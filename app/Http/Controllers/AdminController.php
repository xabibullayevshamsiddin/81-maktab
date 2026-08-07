<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Comment;
use App\Models\ContactMessage;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Exam;
use App\Models\Post;
use App\Models\Result;
use App\Models\Role;
use App\Models\Teacher;
use App\Models\TeacherComment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = cache()->remember('admin_dashboard_stats', 300, function () {
            return [
                'users' => User::count(),
                'teachers' => Teacher::count(),
                'posts' => Post::count(),
                'categories' => Category::count(),
                'comments' => Comment::count() + TeacherComment::count(),
                'pending_comments' => Comment::pending()->count() + TeacherComment::query()->where('is_approved', false)->count(),
                'contact_messages' => ContactMessage::count(),
                'today_messages' => ContactMessage::query()->whereDate('created_at', today())->count(),
                'courses' => Course::count(),
                'published_courses' => Course::query()->where('status', Course::STATUS_PUBLISHED)->count(),
                'pending_courses' => Course::query()->where('status', Course::STATUS_PENDING_VERIFICATION)->count(),
                'pending_enrollments' => CourseEnrollment::pending()->count(),
                'exams' => Exam::count(),
                'active_exams' => Exam::query()->where('is_active', true)->count(),
                'exam_results' => Result::count(),
                'passed_results' => Result::query()->where('passed', true)->count(),
            ];
        });

        $recentPosts = Post::query()
            ->with('category')
            ->latest()
            ->take(5)
            ->get();

        $recentMessages = ContactMessage::query()
            ->latest()
            ->take(5)
            ->get();

        $recentEnrollments = CourseEnrollment::query()
            ->with(['course', 'user'])
            ->latest()
            ->take(5)
            ->get();

        $recentResults = Result::query()
            ->with(['exam', 'user'])
            ->latest()
            ->take(5)
            ->get();

        $recentUsers = User::query()
            ->with('roleRelation')
            ->latest()
            ->take(5)
            ->get();

        // Haftalik faollik (oxirgi 7 kun ro'yxatdan o'tganlar)
        $weeklyActivity = cache()->remember('admin_weekly_activity', 300, function () {
            $days = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $days[] = [
                    'label' => $date->translatedFormat('D'),
                    'count' => User::query()
                        ->whereDate('created_at', $date)
                        ->count(),
                ];
            }
            return $days;
        });

        // Joriy oy statistikasi
        $monthlyStats = cache()->remember('admin_monthly_stats', 300, function () {
            return [
                'new_users_this_month' => User::query()->whereMonth('created_at', now()->month)->count(),
                'new_posts_this_month' => Post::query()->whereMonth('created_at', now()->month)->count(),
                'new_courses_this_month' => Course::query()->whereMonth('created_at', now()->month)->count(),
                'server_uptime' => $this->getServerUptime(),
            ];
        });

        return view('admin.dashboard', compact(
            'stats',
            'recentPosts',
            'recentMessages',
            'recentEnrollments',
            'recentResults',
            'recentUsers',
            'weeklyActivity',
            'monthlyStats',
        ));
    }

    public function user(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $selectedGrade = normalize_school_grade($request->query('grade'));
        $selectedStatus = (string) $request->query('status', '');
        $selectedRoleId = (int) $request->query('role_id', 0);

        if (! in_array($selectedGrade, school_student_grade_options(), true)) {
            $selectedGrade = '';
        }

        if (! in_array($selectedStatus, ['active', 'blocked'], true)) {
            $selectedStatus = '';
        }

        if ($selectedRoleId < 1) {
            $selectedRoleId = 0;
        }

        $query = User::with('roleRelation')
            ->withCount('createdCourses')
            ->latest();

        if ($q !== '') {
            $query->where(function ($w) use ($q): void {
                $w->where('name', 'like', '%'.$q.'%')
                    ->orWhere('email', 'like', '%'.$q.'%')
                    ->orWhere('phone', 'like', '%'.$q.'%')
                    ->orWhere('grade', 'like', '%'.$q.'%');
            });
        }

        if ($selectedRoleId > 0) {
            $query->where('role_id', $selectedRoleId);
        }

        if ($selectedStatus !== '') {
            if ($selectedStatus === 'blocked') {
                $query->where('is_blocked', true);
            } else {
                $query->where(function ($q) {
                    $q->where('is_blocked', false)->orWhereNull('is_blocked');
                });
            }
        }

        if ($selectedGrade !== '') {
            $query
                ->where('grade', $selectedGrade)
                ->whereHas('roleRelation', function ($builder): void {
                    $builder->where('name', User::ROLE_USER);
                });
        }

        $users = $query->paginate(10)->appends(request()->query());

        $filterRoles = Role::query()
            ->orderByDesc('level')
            ->orderBy('label')
            ->get();

        $assignableRoles = $filterRoles
            ->filter(fn (Role $role) => auth()->user()->canAssignRole($role))
            ->values();

        return view('admin.user', compact(
            'users',
            'assignableRoles',
            'filterRoles',
            'selectedGrade',
            'selectedStatus',
            'selectedRoleId',
            'q',
        ));
    }

    public function updateUser(Request $request, User $user)
    {
        if ($request->exists('grade')) {
            $request->merge([
                'grade' => normalize_school_grade($request->input('grade')),
            ]);
        }

        $validated = $request->validate([
            'role_id' => ['sometimes', 'required', 'integer', 'exists:roles,id'],
            'is_active' => ['sometimes', 'required', 'boolean'],
            'grade' => ['sometimes', 'nullable', 'string', 'max:10', \Illuminate\Validation\Rule::in(school_student_grade_options())],
        ], [
            'grade.in' => school_grade_validation_message(),
        ]);

        $currentUser = auth()->user();

        if (! $currentUser->canManage($user)) {
            return redirect()->route('user')->with('error', "Siz bu foydalanuvchining rolini o'zgartira olmaysiz.");
        }

        if ($user->id === $currentUser->id) {
            return redirect()->route('user')->with('error', "O'zingizning rolni o'zgartira olmaysiz.");
        }

        $updatePayload = [];
        $effectiveRoleName = $user->role;

        if (array_key_exists('role_id', $validated)) {
            $newRole = Role::query()->findOrFail($validated['role_id']);
            if (! $currentUser->canAssignRole($newRole)) {
                return redirect()->route('user')->with('error', 'Bu rolni tayinlash huquqingiz yo\'q.');
            }

            $updatePayload['role_id'] = $validated['role_id'];
            $effectiveRoleName = $newRole->name;
        }

        if (array_key_exists('is_active', $validated)) {
            $updatePayload['is_active'] = $validated['is_active'];
        }

        if (array_key_exists('grade', $validated) && $effectiveRoleName === User::ROLE_USER) {
            $updatePayload['grade'] = $validated['grade'] ?: null;
        }

        if ($updatePayload === []) {
            return redirect()->route('user')->with('error', 'Yangilash uchun ma\'lumot yuborilmadi.');
        }

        $user->update($updatePayload);

        return redirect()->route('user')
            ->with('success', 'Foydalanuvchi yangilandi.')
            ->with('toast_type', 'warning');
    }

    public function destroyUser(User $user)
    {
        $currentUser = auth()->user();

        if ($user->id === $currentUser->id) {
            return redirect()->route('user')->with('error', "O'zingizni o'chira olmaysiz.");
        }

        if (! $currentUser->canManage($user)) {
            return redirect()->route('user')->with('error', "Siz bu foydalanuvchini o'chira olmaysiz.");
        }

        $user->delete();

        return redirect()->route('user')
            ->with('error', "Foydalanuvchi o'chirildi.")
            ->with('toast_type', 'error');
    }

    /**
     * Admin foydalanuvchiga Telegram xabar yuboradi.
     */
    public function sendTelegramMessage(Request $request, User $user)
    {
        $admin = $request->user();

        if (! $admin || ! $admin->canManage($user)) {
            return redirect()->route('user')
                ->with('error', 'Siz bu foydalanuvchiga xabar yubora olmaysiz.')
                ->with('toast_type', 'error');
        }

        if (! $user->telegram_chat_id) {
            return redirect()->route('user')
                ->with('error', "{$user->name} Telegram bilan bog'lanmagan.")
                ->with('toast_type', 'warning');
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
        ], [
            'message.required' => 'Xabar matnini kiriting.',
            'message.max' => 'Xabar 4000 belgidan oshmasligi kerak.',
        ]);

        $adminName = htmlspecialchars($admin->buildNameFromParts() ?: $admin->name);
        $userName = htmlspecialchars($user->buildNameFromParts() ?: $user->name);

        $text = "📩 <b>Admin xabari</b>\n"
            ."━━━━━━━━━━━━━━━━━━━━\n\n"
            ."👤 <b>Kimdan:</b> {$adminName}\n"
            ."🎯 <b>Kim uchun:</b> {$userName}\n\n"
            ."💬 <b>Xabar:</b>\n"
            .nl2br(htmlspecialchars($validated['message']));

        $telegram = app(\App\Services\TelegramService::class);
        $telegram->sendMessage((int) $user->telegram_chat_id, $text);

        return redirect()->route('user')
            ->with('success', "Xabar {$user->name} ga muvaffaqiyatli yuborildi.")
            ->with('toast_type', 'success');
    }

    /**
     * Foydalanuvchini bloklash.
     */
    public function blockUser(Request $request, User $user)
    {
        $admin = $request->user();

        if (! $admin || ! $admin->canManage($user)) {
            return redirect()->route('user')
                ->with('error', 'Siz bu foydalanuvchini bloklay olmaysiz.')
                ->with('toast_type', 'error');
        }

        if ($user->isAdmin()) {
            return redirect()->route('user')
                ->with('error', 'Admin foydalanuvchilarini bloklay olmaysiz.')
                ->with('toast_type', 'error');
        }

        $validated = $request->validate([
            'duration' => ['required', 'in:1h,1d,1w,1m,forever'],
            'reason' => ['required', 'string', 'max:500'],
        ], [
            'duration.required' => 'Blok muddatini tanlang.',
            'reason.required' => 'Blok sababini kiriting.',
        ]);

        $blockedUntil = match ($validated['duration']) {
            '1h' => now()->addHour(),
            '1d' => now()->addDay(),
            '1w' => now()->addWeek(),
            '1m' => now()->addMonth(),
            'forever' => null,
        };

        $durationText = match ($validated['duration']) {
            '1h' => '1 soat',
            '1d' => '1 kun',
            '1w' => '1 hafta',
            '1m' => '1 oy',
            'forever' => 'Butun umr',
        };

        $user->update([
            'is_blocked' => true,
            'is_active' => false,
            'blocked_until' => $blockedUntil,
            'blocked_reason' => $validated['reason'],
            'blocked_by' => $admin->id,
        ]);

        // Telegram xabar yuborish
        if ($user->telegram_chat_id) {
            $adminName = htmlspecialchars($admin->buildNameFromParts() ?: $admin->name);
            $userName = htmlspecialchars($user->buildNameFromParts() ?: $user->name);
            $unblockTime = $blockedUntil ? $blockedUntil->format('d.m.Y H:i') : 'Cheksiz';

            $text = "🚫 <b>Hisobingiz bloklandi</b>\n"
                ."━━━━━━━━━━━━━━━━━━━━\n\n"
                ."👤 <b>Foydalanuvchi:</b> {$userName}\n"
                ."👨‍💼 <b>Bloklagan:</b> {$adminName}\n"
                ."⏰ <b>Muddat:</b> {$durationText}\n"
                ."📅 <b>Qachon gacha:</b> {$unblockTime}\n\n"
                ."📝 <b>Sabab:</b>\n"
                .htmlspecialchars($validated['reason']) . "\n\n"
                ."━━━━━━━━━━━━━━━━━━━━\n\n"
                ."⚠️ Blok muddati tugagandan keyin avtomatik yechiladi.";

            $telegram = app(\App\Services\TelegramService::class);
            $telegram->sendMessage((int) $user->telegram_chat_id, $text);
        }

        return redirect()->route('user')
            ->with('success', "{$user->name} {$durationText} muddatga bloklandi.")
            ->with('toast_type', 'success');
    }

    /**
     * Foydalanuvchini blokdan chiqarish.
     */
    public function unblockUser(User $user)
    {
        $admin = auth()->user();

        if (! $admin || ! $admin->canManage($user)) {
            return redirect()->route('user')
                ->with('error', 'Siz bu foydalanuvchini blokdan chiqa olmaysiz.')
                ->with('toast_type', 'error');
        }

        $user->update([
            'is_blocked' => false,
            'is_active' => true,
            'blocked_until' => null,
            'blocked_reason' => null,
            'blocked_by' => null,
        ]);

        // Telegram xabar yuborish
        if ($user->telegram_chat_id) {
            $userName = htmlspecialchars($user->buildNameFromParts() ?: $user->name);

            $text = "✅ <b>Hisobingiz blokdan chiqarildi</b>\n"
                ."━━━━━━━━━━━━━━━━━━━━\n\n"
                ."👤 <b>Foydalanuvchi:</b> {$userName}\n"
                ."👨‍💼 <b>Chiqargan:</b> " . htmlspecialchars($admin->buildNameFromParts() ?: $admin->name) . "\n\n"
                ."Endi tizimga kirishingiz mumkin.";

            $telegram = app(\App\Services\TelegramService::class);
            $telegram->sendMessage((int) $user->telegram_chat_id, $text);
        }

        return redirect()->route('user')
            ->with('success', "{$user->name} blokdan chiqarildi.")
            ->with('toast_type', 'success');
    }

    private function getServerUptime(): string
    {
        try {
            $uptime = @file_get_contents('/proc/uptime');
            if ($uptime !== false) {
                $seconds = (float) explode(' ', $uptime)[0];
                $days = floor($seconds / 86400);
                $hours = floor(($seconds % 86400) / 3600);
                return $days . ' kun ' . $hours . ' soat';
            }
        } catch (\Throwable $e) {
            // ignore
        }
        return 'Noma\'lum';
    }

    public function approveCourseOpenRequest(User $user)
    {
        $currentUser = auth()->user();
        abort_unless($currentUser && $currentUser->canManage($user), 403);
        abort_unless($user->isTeacher(), 422);

        if (! $user->course_open_request_pending) {
            return redirect()
                ->route('admin.courses.requests')
                ->with('error', "Bu foydalanuvchida kutilayotgan kurs ochish so'rovi yo'q.")
                ->with('toast_type', 'warning');
        }

        DB::transaction(function () use ($user): void {
            $user->update([
                'course_open_approved' => true,
                'course_open_request_pending' => false,
                'course_open_approved_at' => now(),
            ]);
        });

        // Teacherga Telegram xabar yuborish
        $this->notifyTeacherCourseOpenDecision($user, true);

        return redirect()
            ->route('admin.courses.requests')
            ->with('success', "Kurs ochish ruxsati berildi ({$user->email}).")
            ->with('toast_type', 'success');
    }

    public function rejectCourseOpenRequest(User $user)
    {
        $currentUser = auth()->user();
        abort_unless($currentUser && $currentUser->canManage($user), 403);
        abort_unless($user->isTeacher(), 422);

        if (! $user->course_open_request_pending) {
            return redirect()
                ->route('admin.courses.requests')
                ->with('error', "Bu foydalanuvchida kutilayotgan so'rov yo'q.")
                ->with('toast_type', 'warning');
        }

        DB::transaction(function () use ($user): void {
            $user->update([
                'course_open_approved' => false,
                'course_open_request_pending' => false,
                'course_open_approved_at' => null,
            ]);
        });

        // Teacherga Telegram xabar yuborish
        $this->notifyTeacherCourseOpenDecision($user, false);

        return redirect()
            ->route('admin.courses.requests')
            ->with('success', "Kurs ochish so'rovi rad etildi ({$user->email}).")
            ->with('toast_type', 'warning');
    }

    private function notifyTeacherCourseOpenDecision(User $teacher, bool $approved): void
    {
        if (! $teacher->telegram_chat_id) {
            return;
        }

        $telegram = app(\App\Services\TelegramService::class);
        
        if ($approved) {
            $text = "✅ <b>Kurs ochish so'rovi tasdiqlandi!</b>\n"
                ."━━━━━━━━━━━━━━━━━━━━\n\n"
                ."Hurmatli <b>".htmlspecialchars($teacher->name)."</b>,\n\n"
                ."Sizning kurs ochish so'rovingiz admin tomonidan tasdiqlandi.\n\n"
                ."🎓 Endi kurs yaratishingiz mumkin!\n"
                ."🔗 Kurs yaratish: ".route('teacher.courses.create');
        } else {
            $text = "❌ <b>Kurs ochish so'rovi rad etildi</b>\n"
                ."━━━━━━━━━━━━━━━━━━━━\n\n"
                ."Hurmatli <b>".htmlspecialchars($teacher->name)."</b>,\n\n"
                ."Sizning kurs ochish so'rovingiz admin tomonidan rad etildi.\n\n"
                ."💬 Sababini bilish uchun admin bilan bog'laning.";
        }

        $telegram->sendMessage((int) $teacher->telegram_chat_id, $text);
    }
}
