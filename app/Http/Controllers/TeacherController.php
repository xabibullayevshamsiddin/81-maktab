<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $query = Teacher::query()->with('user')->latest();

        if ($q !== '') {
            $query->where(function ($w) use ($q): void {
                $w->where('full_name', 'like', '%'.$q.'%')
                    ->orWhere('subject', 'like', '%'.$q.'%')
                    ->orWhere('subject_en', 'like', '%'.$q.'%')
                    ->orWhere('lavozim', 'like', '%'.$q.'%')
                    ->orWhere('lavozim_en', 'like', '%'.$q.'%')
                    ->orWhere('toifa', 'like', '%'.$q.'%')
                    ->orWhere('toifa_en', 'like', '%'.$q.'%')
                    ->orWhere('grades', 'like', '%'.$q.'%')
                    ->orWhere('achievements', 'like', '%'.$q.'%')
                    ->orWhere('achievements_en', 'like', '%'.$q.'%')
                    ->orWhereHas('user', function ($u) use ($q): void {
                        $u->where('name', 'like', '%'.$q.'%')
                            ->orWhere('email', 'like', '%'.$q.'%')
                            ->orWhere('phone', 'like', '%'.$q.'%');
                    });
            });
        }

        $teachers = $query->paginate(10)->withQueryString();

        return view('admin.teachers.index', compact('teachers'));
    }

    public function create()
    {
        $teacherUsers = $this->teacherUsers();

        return view('admin.teachers.create', compact('teacherUsers'));
    }

    public function store(Request $request)
    {
        if (! $request->filled('user_id')) {
            $request->merge(['user_id' => null]);
        }

        $validated = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id', 'unique:teachers,user_id'],
            'full_name' => ['required', 'string', 'max:255'],
            'lavozim' => ['nullable', 'string', 'max:255'],
            'lavozim_en' => ['nullable', 'string', 'max:255'],
            'toifa' => ['nullable', 'string', 'max:255'],
            'toifa_en' => ['nullable', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'subject_en' => ['nullable', 'string', 'max:255'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:60'],
            'grades' => ['nullable', 'string', 'max:255'],
            'achievements' => ['nullable', 'string', 'max:10000'],
            'achievements_en' => ['nullable', 'string', 'max:10000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $this->normalizeTeacherSubjectFields($validated);

        $validated['slug'] = $this->makeUniqueSlug($validated['full_name']);
        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);
        $validated['sort_order'] = $this->nextSortOrder();
        $validated['user_id'] = $validated['user_id'] ?? null;
        $gradesTrim = trim((string) ($validated['grades'] ?? ''));
        $validated['grades'] = $gradesTrim !== '' ? $gradesTrim : null;

        if (! $request->hasFile('image')) {
            unset($validated['image']);
        } else {
            $validated['image'] = $request->file('image')->store('teachers', 'public');
        }

        Teacher::create($validated);
        forget_public_teacher_caches();

        return redirect()->route('teachers.index')
            ->with('success', "Ustoz qo'shildi.")
            ->with('toast_type', 'success');
    }

    public function show(Teacher $teacher)
    {
        return view('admin.teachers.show', compact('teacher'));
    }

    public function edit(Teacher $teacher)
    {
        $teacherUsers = $this->teacherUsers($teacher);

        return view('admin.teachers.edit', compact('teacher', 'teacherUsers'));
    }

    public function update(Request $request, Teacher $teacher)
    {
        if (! $request->filled('user_id')) {
            $request->merge(['user_id' => null]);
        }

        $validated = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id', 'unique:teachers,user_id,'.$teacher->id],
            'full_name' => ['required', 'string', 'max:255'],
            'lavozim' => ['nullable', 'string', 'max:255'],
            'lavozim_en' => ['nullable', 'string', 'max:255'],
            'toifa' => ['nullable', 'string', 'max:255'],
            'toifa_en' => ['nullable', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'subject_en' => ['nullable', 'string', 'max:255'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:60'],
            'grades' => ['nullable', 'string', 'max:255'],
            'achievements' => ['nullable', 'string', 'max:10000'],
            'achievements_en' => ['nullable', 'string', 'max:10000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $this->normalizeTeacherSubjectFields($validated);

        if ($teacher->full_name !== $validated['full_name']) {
            $validated['slug'] = $this->makeUniqueSlug($validated['full_name'], $teacher->id);
        }

        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);
        $validated['user_id'] = $validated['user_id'] ?? null;
        $gradesTrim = trim((string) ($validated['grades'] ?? ''));
        $validated['grades'] = $gradesTrim !== '' ? $gradesTrim : null;

        if (! $request->hasFile('image')) {
            unset($validated['image']);
        } else {
            if (! empty($teacher->image)) {
                Storage::disk('public')->delete($teacher->image);
            }
            $validated['image'] = $request->file('image')->store('teachers', 'public');
        }

        $teacher->update($validated);
        forget_public_teacher_caches();

        return redirect()->route('teachers.index')
            ->with('success', 'Ustoz yangilandi.')
            ->with('toast_type', 'warning');
    }

    public function destroy(Teacher $teacher)
    {
        if (! empty($teacher->image)) {
            Storage::disk('public')->delete($teacher->image);
        }

        $teacher->delete();
        forget_public_teacher_caches();

        return redirect()->route('teachers.index')
            ->with('error', "Ustoz o'chirildi.")
            ->with('toast_type', 'error');
    }

    private function makeUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base !== '' ? $base : 'teacher';

        $existingSlugs = Teacher::query()
            ->where('slug', 'like', $slug.'%')
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->pluck('slug')
            ->all();

        if (! in_array($slug, $existingSlugs, true)) {
            return $slug;
        }

        $i = 2;
        while (in_array("{$slug}-{$i}", $existingSlugs, true)) {
            $i++;
        }

        return "{$slug}-{$i}";
    }

    private function teacherUsers(?Teacher $ignoreTeacher = null)
    {
        $ignoreId = $ignoreTeacher?->id;

        $takenUserIds = Teacher::query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->whereNotNull('user_id')
            ->pluck('user_id');

        return User::query()
            ->with('roleRelation')
            ->whereHas('roleRelation', fn ($q) => $q->where('name', User::ROLE_TEACHER))
            ->whereNotIn('id', $takenUserIds)
            ->orderBy('name')
            ->get();
    }

    private function nextSortOrder(): int
    {
        return (int) Teacher::query()->max('sort_order') + 1;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function normalizeTeacherSubjectFields(array &$validated): void
    {
        $subject = trim((string) ($validated['subject'] ?? ''));
        $validated['subject'] = $subject !== '' ? $subject : null;
        $subjectEn = trim((string) ($validated['subject_en'] ?? ''));
        $validated['subject_en'] = $subjectEn !== '' ? $subjectEn : null;
    }
}
