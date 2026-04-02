<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TeacherController extends Controller
{
    public function index()
    {
        $teachers = Teacher::query()->latest()->get();

        return view('admin.teachers.index', compact('teachers'));
    }

    public function create()
    {
        return view('admin.teachers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:60'],
            'grades' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = $this->makeUniqueSlug($validated['full_name']);
        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('teachers', 'public');
        }

        Teacher::create($validated);

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
        return view('admin.teachers.edit', compact('teacher'));
    }

    public function update(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:60'],
            'grades' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($teacher->full_name !== $validated['full_name']) {
            $validated['slug'] = $this->makeUniqueSlug($validated['full_name'], $teacher->id);
        }

        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        if ($request->hasFile('image')) {
            if (! empty($teacher->image)) {
                Storage::disk('public')->delete($teacher->image);
            }
            $validated['image'] = $request->file('image')->store('teachers', 'public');
        }

        $teacher->update($validated);

        return redirect()->route('teachers.index')
            ->with('success', "Ustoz yangilandi.")
            ->with('toast_type', 'warning');
    }

    public function destroy(Teacher $teacher)
    {
        if (! empty($teacher->image)) {
            Storage::disk('public')->delete($teacher->image);
        }

        $teacher->delete();

        return redirect()->route('teachers.index')
            ->with('error', "Ustoz o'chirildi.")
            ->with('toast_type', 'error');
    }

    private function makeUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base !== '' ? $base : 'teacher';

        $existsQuery = Teacher::query()->where('slug', $slug);
        if ($ignoreId) {
            $existsQuery->where('id', '!=', $ignoreId);
        }

        if (! $existsQuery->exists()) {
            return $slug;
        }

        $i = 2;
        while (true) {
            $candidate = "{$slug}-{$i}";
            $q = Teacher::query()->where('slug', $candidate);
            if ($ignoreId) {
                $q->where('id', '!=', $ignoreId);
            }

            if (! $q->exists()) {
                return $candidate;
            }
            $i++;
        }
    }
}

