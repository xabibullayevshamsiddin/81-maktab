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

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
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

        return view('admin.dashboard', compact(
            'stats',
            'recentPosts',
            'recentMessages',
            'recentEnrollments',
            'recentResults',
            'recentUsers',
        ));
    }

    public function user(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $selectedGrade = normalize_school_grade($request->query('grade'));
        $selectedStatus = (string) $request->query('status', '');
        $selectedRoleId = (int) $request->query('role_id', 0);

        if (! in_array($selectedGrade, school_grade_options(), true)) {
            $selectedGrade = '';
        }

        if (! in_array($selectedStatus, ['active', 'blocked'], true)) {
            $selectedStatus = '';
        }

        if ($selectedRoleId < 1) {
            $selectedRoleId = 0;
        }

        $query = User::with('roleRelation')->latest();

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
            $query->where('is_active', $selectedStatus === 'active');
        }

        if ($selectedGrade !== '') {
            $query
                ->where('grade', $selectedGrade)
                ->whereHas('roleRelation', function ($builder): void {
                    $builder->where('name', User::ROLE_USER);
                });
        }

        $users = $query->get();

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
            'grade' => ['sometimes', 'nullable', 'string', 'max:10', \Illuminate\Validation\Rule::in(school_grade_options())],
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
}
