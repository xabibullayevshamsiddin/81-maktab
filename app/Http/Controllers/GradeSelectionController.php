<?php

namespace App\Http\Controllers;

use App\Http\Requests\SelectStudentGradeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GradeSelectionController extends Controller
{
    public function show(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->needsGradeSelection()) {
            return redirect()->route('profile.show');
        }

        return redirect()
            ->route('profile.show')
            ->with('error', $user->grade_selection_reason ?: 'Davom etish uchun sinfingizni tanlang.')
            ->with('toast_type', 'warning');
    }

    public function update(SelectStudentGradeRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->forceFill([
            'grade' => $validated['grade'],
            'grade_needs_selection' => false,
            'grade_selection_reason' => null,
        ])->save();

        return redirect()
            ->intended(route('profile.show'))
            ->with('success', 'Sinfingiz saqlandi. Saytdan foydalanishni davom ettirishingiz mumkin.')
            ->with('toast_type', 'success');
    }
}
