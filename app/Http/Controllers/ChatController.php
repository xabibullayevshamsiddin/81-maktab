<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ValidatesTurnstile;
use App\Models\ChatMessage;
use App\Models\SiteSetting;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Result;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    use ValidatesTurnstile;

    private const MAX_MESSAGES = 50;

    public function messages(Request $request): JsonResponse
    {
        if (SiteSetting::get('global_chat_enabled', '1') !== '1') {
            return response()->json([
                'messages' => [],
                'last_id' => (int) $request->query('after', 0),
                'can_moderate' => false,
                'chat_disabled' => true,
                'disabled_message' => SiteSetting::get(
                    'global_chat_disabled_message',
                    'Global chat vaqtincha o‘chirilgan. Keyinroq urinib ko‘ring.'
                ),
            ]);
        }

        $afterId = (int) $request->query('after', 0);
        $currentUser = $request->user()->loadMissing('roleRelation');
        $currentUserId = (int) $currentUser->id;
        $canModerate = $currentUser->isAdmin() || $currentUser->isModerator();

        $query = ChatMessage::query()
            ->with('user:id,first_name,name,role_id,avatar,is_active')
            ->with('user.roleRelation:id,name');

        if ($afterId > 0) {
            $query->where('id', '>', $afterId);
        } else {
            $query->latest('id')->limit(self::MAX_MESSAGES);
        }

        $messages = $afterId > 0
            ? $query->orderBy('id')->get()
            : $query->get()->reverse()->values();

        $data = $messages->map(function (ChatMessage $m) use ($currentUser, $currentUserId, $canModerate) {
            $user = $m->user;
            $role = $user?->roleRelation?->name ?? 'user';
            $isSuperAdmin = $role === 'super_admin';
            $isAdmin = in_array($role, ['super_admin', 'admin'], true);
            // Hostda to‘g‘ri domen/HTTPS uchun asset() (APP_URL) ishonchliroq.
            $avatarUrl = $user && $user->avatar
                ? asset('storage/'.ltrim($user->avatar, '/'))
                : null;
            $isMine = (int) $m->user_id === $currentUserId;
            $canBlock = false;

            if ($user && ! $isMine) {
                if ($currentUser->isSuperAdmin()) {
                    $canBlock = $this->canControlUserFromChatPreview($currentUser, $user) && (bool) $user->is_active;
                } elseif ($canModerate) {
                    $canBlock = ! $user->isAdmin() && (bool) $user->is_active;
                }
            }

            return [
                'id' => $m->id,
                'user_id' => (int) $m->user_id,
                'body' => e($m->body),
                'is_mine' => $isMine,
                'is_admin' => $isAdmin,
                'is_super_admin' => $isSuperAdmin,
                'can_delete' => $isMine || $canModerate,
                'can_block' => $canBlock,
                'user_name' => $user->first_name ?: $user->name ?? '?',
                'user_initial' => mb_strtoupper(mb_substr(trim($user->first_name ?: $user->name ?? '?'), 0, 1)),
                'avatar_url' => $avatarUrl,
                'time' => $m->created_at?->format('H:i'),
                'date' => $m->created_at?->format('d.m'),
            ];
        });

        $this->cleanOldMessages();

        return response()->json([
            'messages' => $data,
            'last_id' => $messages->last()?->id ?? $afterId,
            'can_moderate' => $canModerate,
        ]);
    }

    /**
     * Chat kontekstida foydalanuvchi haqida ochiq ma’lumot.
     * Email va telefon faqat Super Admin ko‘rishi uchun qaytariladi.
     */
    public function userPreview(Request $request, User $user): JsonResponse
    {
        $viewer = $request->user()->loadMissing('roleRelation');
        $user->loadMissing('roleRelation');
        $viewerIsSuperAdmin = $viewer->isSuperAdmin();
        $viewerCanControl = $this->canControlUserFromChatPreview($viewer, $user);
        $isSelf = (int) $viewer->id === (int) $user->id;

        $roleName = $user->roleRelation?->name ?? User::ROLE_USER;
        $roleLabel = User::ROLE_LABELS['uz'][$roleName]
            ?? User::ROLES[$roleName]
            ?? $roleName;
        $roleLevel = User::ROLE_HIERARCHY[$roleName] ?? 1;

        $displayName = trim($user->buildNameFromParts());
        if ($displayName === '') {
            $displayName = $user->name ?: '?';
        }

        $avatarUrl = $user->avatar
            ? asset('storage/'.ltrim($user->avatar, '/'))
            : null;

        // O‘z profilidagi kabi: xodimlar uchun «Barcha sinflar», xom `grade` ustunini emas
        $gradeDisplay = trim($user->displayGrade(''));
        $grade = $gradeDisplay !== '' ? $gradeDisplay : null;

        $payload = [
            'display_name' => $displayName,
            'avatar_url' => $avatarUrl,
            'role_label' => $roleLabel,
            'role_level' => $roleLevel,
            'is_super_admin' => $roleName === User::ROLE_SUPER_ADMIN,
            'is_admin' => in_array($roleName, [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN], true),
            'grade' => $grade,
            'is_parent' => (bool) $user->is_parent,
            'member_year' => $user->created_at?->format('Y'),
            'viewer_is_super_admin' => $viewerIsSuperAdmin,
            'courses' => $this->buildUserPreviewCourses($user),
            'exam_stats' => in_array($roleName, [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN], true) ? null : $this->buildUserPreviewExamStats($user),
        ];

        if ($viewerIsSuperAdmin) {
            $payload['contact'] = [
                'email' => $user->email,
                'phone' => $user->phone ? trim((string) $user->phone) : null,
            ];
            $payload['admin_profile'] = $this->buildUserPreviewAdminProfile($user, $roleName, $roleLabel, $roleLevel);
        }

        if (($viewer->isAdmin() || $viewer->isModerator()) && ($viewerCanControl || $isSelf)) {
            $payload['super_admin_actions'] = [
                'is_active' => (bool) $user->is_active,
                'can_deactivate' => $viewerCanControl && (bool) $user->is_active,
                'can_activate' => $viewerCanControl && ! $user->is_active,
                'is_self' => $isSelf,
            ];
        }

        return response()->json($payload);
    }

    /**
     * Chat preview orqali akkauntni bloklash (is_active = false).
     */
    public function superAdminDeactivateUser(Request $request, User $user): JsonResponse
    {
        $current = $request->user()->loadMissing('roleRelation');
        $user->loadMissing('roleRelation');

        if (! $this->canControlUserFromChatPreview($current, $user)) {
            return response()->json(['ok' => false, 'error' => 'Ruxsat yo‘q.'], 403);
        }

        if ((int) $user->id === (int) $current->id) {
            return response()->json(['ok' => false, 'error' => 'O‘zingizni bloklab bo‘lmaydi.'], 422);
        }

        $user->update(['is_active' => false]);

        return response()->json(['ok' => true, 'is_active' => false]);
    }

    /**
     * Chat preview orqali bloklangan akkauntni qayta yoqish.
     */
    public function superAdminActivateUser(Request $request, User $user): JsonResponse
    {
        $current = $request->user()->loadMissing('roleRelation');
        $user->loadMissing('roleRelation');

        if (! $this->canControlUserFromChatPreview($current, $user)) {
            return response()->json(['ok' => false, 'error' => 'Ruxsat yo‘q.'], 403);
        }

        if ((int) $user->id === (int) $current->id) {
            return response()->json(['ok' => false, 'error' => 'Bu amalni bajarib bo‘lmaydi.'], 422);
        }

        $user->update(['is_active' => true]);

        return response()->json(['ok' => true, 'is_active' => true]);
    }

    /**
     * @return array{created: list<array{title: string, url: string|null}>, enrolled: list<array{title: string, url: string|null}>}
     */
    private function buildUserPreviewCourses(User $user): array
    {
        $created = Course::query()
            ->where('created_by', $user->id)
            ->orderByDesc('id')
            ->limit(10)
            ->get(['id', 'title', 'status'])
            ->map(function (Course $course) {
                $url = $course->status === Course::STATUS_PUBLISHED
                    ? route('courses.show', $course)
                    : null;

                return [
                    'title' => (string) $course->title,
                    'url' => $url,
                ];
            })
            ->values()
            ->all();

        $enrolled = CourseEnrollment::query()
            ->where('user_id', $user->id)
            ->where('status', CourseEnrollment::STATUS_APPROVED)
            ->with('course:id,title,status')
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->filter(fn (CourseEnrollment $e) => $e->course !== null)
            ->map(function (CourseEnrollment $e) {
                $course = $e->course;
                $url = $course->status === Course::STATUS_PUBLISHED
                    ? route('courses.show', $course)
                    : null;

                return [
                    'title' => (string) $course->title,
                    'url' => $url,
                ];
            })
            ->values()
            ->all();

        return [
            'created' => $created,
            'enrolled' => $enrolled,
        ];
    }

    private function canControlUserFromChatPreview(User $viewer, User $target): bool
    {
        if ((int) $viewer->id === (int) $target->id) {
            return false;
        }

        if ($viewer->canManage($target)) {
            return true;
        }

        return $viewer->isSuperAdmin();
    }

    private function buildUserPreviewAdminProfile(User $user, string $roleName, string $roleLabel, int $roleLevel): array
    {
        return [
            'id' => (int) $user->id,
            'name' => trim((string) ($user->name ?? '')) ?: null,
            'first_name' => trim((string) ($user->first_name ?? '')) ?: null,
            'last_name' => trim((string) ($user->last_name ?? '')) ?: null,
            'email' => trim((string) ($user->email ?? '')) ?: null,
            'phone' => trim((string) ($user->phone ?? '')) ?: null,
            'role_key' => $roleName,
            'role_label' => $roleLabel,
            'role_level' => $roleLevel,
            'status' => $user->is_active ? 'Faol' : 'Bloklangan',
            'is_active' => (bool) $user->is_active,
            'is_parent' => (bool) $user->is_parent,
            'grade' => $user->displayGrade('Kiritilmagan'),
            'registered_at' => $user->created_at?->format('d.m.Y H:i'),
            'email_verified_at' => $user->email_verified_at?->format('d.m.Y H:i'),
            'course_open_approved' => (bool) ($user->course_open_approved ?? false),
            'course_open_request_pending' => (bool) ($user->course_open_request_pending ?? false),
            'teacher_profile_linked' => $user->hasLinkedActiveTeacherProfile(),
        ];
    }

    /**
     * @return array{
     *     finished_total: int,
     *     passed: int,
     *     failed: int,
     *     pending_grade: int,
     *     started_incomplete: int,
     *     avg_percent: float|null,
     *     pass_rate_percent: float|null
     * }
     */
    private function buildUserPreviewExamStats(User $user): array
    {
        $base = Result::query()->where('user_id', $user->id);

        $finished = (clone $base)->whereIn('status', ['submitted', 'expired']);

        $finishedTotal = (clone $finished)->count();
        $passed = (clone $finished)->where('passed', true)->count();
        $failed = (clone $finished)->where('passed', false)->count();
        $pendingGrade = (clone $finished)->whereNull('passed')->count();
        $startedIncomplete = (clone $base)->where('status', 'started')->count();

        $avgPercent = null;
        $rows = Result::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['submitted', 'expired'])
            ->whereNotNull('points_max')
            ->where('points_max', '>', 0)
            ->get(['points_earned', 'points_max']);

        if ($rows->isNotEmpty()) {
            $sum = 0.0;
            foreach ($rows as $row) {
                $max = (int) $row->points_max;
                if ($max <= 0) {
                    continue;
                }
                $earned = (int) ($row->points_earned ?? 0);
                $sum += ($earned / $max) * 100.0;
            }
            $avgPercent = round($sum / $rows->count(), 1);
        }

        $decided = $passed + $failed;
        $passRatePercent = $decided > 0 ? round($passed / $decided * 100, 1) : null;

        return [
            'finished_total' => $finishedTotal,
            'passed' => $passed,
            'failed' => $failed,
            'pending_grade' => $pendingGrade,
            'started_incomplete' => $startedIncomplete,
            'avg_percent' => $avgPercent,
            'pass_rate_percent' => $passRatePercent,
        ];
    }

    public function send(Request $request): JsonResponse
    {
        if (SiteSetting::get('global_chat_enabled', '1') !== '1') {
            return response()->json([
                'ok' => false,
                'error' => SiteSetting::get(
                    'global_chat_disabled_message',
                    'Global chat vaqtincha o‘chirilgan.'
                ),
            ], 403);
        }

        $user = $request->user();

        if (! $user->is_active) {
            return response()->json(['ok' => false, 'error' => 'Sizning akkauntingiz bloklangan.'], 403);
        }

        $this->validateTurnstile($request);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $body = sanitize_plain_text($validated['body']);
        if ($body === '') {
            return response()->json([
                'ok' => false,
                'message' => 'Matn bo‘sh.',
                'errors' => ['body' => ['Matn kiritilishi kerak.']],
            ], 422);
        }

        // Idempotency check: bir xil xabarni 2 soniya ichida qayta yuborishni cheklash
        $lastMessage = ChatMessage::where('user_id', $user->id)
            ->latest('id')
            ->first();

        if ($lastMessage && $lastMessage->body === $body && $lastMessage->created_at->gt(now()->subSeconds(2))) {
            return response()->json([
                'ok' => true,
                'id' => $lastMessage->id,
                'duplicated' => true,
            ]);
        }

        $message = ChatMessage::create([
            'user_id' => $user->id,
            'body' => $body,
        ]);

        return response()->json(['ok' => true, 'id' => $message->id]);
    }

    public function destroy(Request $request, ChatMessage $chatMessage): JsonResponse
    {
        $user = $request->user()->loadMissing('roleRelation');
        $canModerate = $user->isAdmin() || $user->isModerator();

        if ((int) $chatMessage->user_id !== (int) $user->id && ! $canModerate) {
            return response()->json(['ok' => false], 403);
        }

        $chatMessage->delete();

        return response()->json(['ok' => true]);
    }

    public function blockUser(Request $request, User $user): JsonResponse
    {
        $current = $request->user()->loadMissing('roleRelation');
        $user->loadMissing('roleRelation');

        if (! $current->isAdmin() && ! $current->isModerator()) {
            return response()->json(['ok' => false], 403);
        }

        if ($current->isSuperAdmin()) {
            if (! $this->canControlUserFromChatPreview($current, $user)) {
                return response()->json(['ok' => false, 'error' => 'Bu foydalanuvchini bloklab bo\'lmaydi.'], 422);
            }
        } elseif ((int) $user->id === (int) $current->id || $user->isAdmin()) {
            return response()->json(['ok' => false, 'error' => 'Bu foydalanuvchini bloklab bo\'lmaydi.'], 422);
        }

        $user->update(['is_active' => false]);

        return response()->json(['ok' => true]);
    }

    private function cleanOldMessages(): void
    {
        $total = ChatMessage::count();
        if ($total <= self::MAX_MESSAGES) {
            return;
        }

        $keepFromId = ChatMessage::query()
            ->orderByDesc('id')
            ->skip(self::MAX_MESSAGES)
            ->value('id');

        if ($keepFromId) {
            ChatMessage::query()->where('id', '<=', $keepFromId)->delete();
        }
    }
}
