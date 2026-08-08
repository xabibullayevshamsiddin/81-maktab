<?php

namespace App\Http\Controllers;

use App\Http\Requests\PromoteAcademicYearRequest;
use App\Http\Requests\StoreSchoolClassRequest;
use App\Models\AcademicYearPromotion;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\SchoolClassLifecycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use LogicException;

class AdminSchoolClassController extends Controller
{
    public function __construct(
        private readonly SchoolClassLifecycleService $schoolClassLifecycleService,
    ) {
    }

    public function index(): View
    {
        $classes = SchoolClass::query()
            ->orderByDesc('is_active')
            ->orderBy('grade_number')
            ->orderBy('sort_order')
            ->orderBy('section')
            ->get()
            ->groupBy('grade_number');

        $studentCounts = User::query()
            ->selectRaw('grade, COUNT(*) as aggregate')
            ->whereNotNull('grade')
            ->where('grade', '!=', '')
            ->where('is_parent', false)
            ->whereHas('roleRelation', fn ($query) => $query->where('name', User::ROLE_USER))
            ->groupBy('grade')
            ->pluck('aggregate', 'grade')
            ->map(fn ($count) => (int) $count)
            ->all();

        $latestPromotion = AcademicYearPromotion::query()
            ->latest('executed_at')
            ->first();

        return view('admin.school-classes.index', compact('classes', 'studentCounts', 'latestPromotion'));
    }

    public function store(StoreSchoolClassRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $result = $this->schoolClassLifecycleService->upsertClass(
            (int) $validated['grade_number'],
            (string) $validated['section'],
            isset($validated['max_students']) ? (int) $validated['max_students'] : null,
        );

        $message = match (true) {
            $result['created']     => "{$result['class']->display_name} sinfi qo'shildi.",
            $result['reactivated'] => "{$result['class']->display_name} sinfi qayta faollashtirildi.",
            default                => "{$result['class']->display_name} sinfi allaqachon faol.",
        };

        return redirect()
            ->route('admin.school-classes.index')
            ->with('success', $message)
            ->with('toast_type', 'success');
    }

    public function destroy(SchoolClass $schoolClass): RedirectResponse
    {
        if (! $schoolClass->is_active) {
            return redirect()
                ->route('admin.school-classes.index')
                ->with('error', "{$schoolClass->display_name} sinfi allaqachon faol emas.")
                ->with('toast_type', 'warning');
        }

        $summary = $this->schoolClassLifecycleService->disbandClass(
            $schoolClass,
            auth()->id(),
        );

        return redirect()
            ->route('admin.school-classes.index')
            ->with('success', "{$summary['class_name']} sinfi o'chirildi. {$summary['affected_users']} ta o'quvchi majburiy qayta sinf tanlashga yuborildi.")
            ->with('toast_type', 'warning');
    }

    /**
     * Update the max_students (capacity) for a specific class.
     */
    public function updateCapacity(Request $request, SchoolClass $schoolClass): RedirectResponse
    {
        if (! $request->user()?->canManageSystem()) {
            abort(403);
        }

        $validated = $request->validate([
            'max_students' => ['nullable', 'integer', 'min:1', 'max:9999'],
        ], [
            'max_students.min' => 'Limit kamida 1 ta bo\'lishi kerak.',
            'max_students.max' => 'Limit 9999 tadan oshmasligi kerak.',
        ]);

        $newLimit = isset($validated['max_students']) && $validated['max_students'] !== ''
            ? (int) $validated['max_students']
            : null;

        $schoolClass->update(['max_students' => $newLimit]);
        forget_school_grade_cache();

        $limitText = $newLimit !== null ? "{$newLimit} ta" : 'cheksiz';

        return redirect()
            ->route('admin.school-classes.index')
            ->with('success', "{$schoolClass->display_name} sinfi limiti: {$limitText}.")
            ->with('toast_type', 'success');
    }

    public function promote(PromoteAcademicYearRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $summary = $this->schoolClassLifecycleService->promoteAcademicYear(
                fromYear: (int) $validated['from_year'],
                toYear: (int) $validated['to_year'],
                dryRun: (bool) ($validated['dry_run'] ?? false),
                force: (bool) ($validated['force'] ?? false),
                actorId: auth()->id(),
            );
        } catch (LogicException $exception) {
            return redirect()
                ->route('admin.school-classes.index')
                ->with('error', $exception->getMessage())
                ->with('toast_type', 'warning');
        }

        $prefix = $summary['dry_run'] ? '[DRY RUN] ' : '';
        $newGrade1Msg = ($summary['new_first_grade_classes'] > 0)
            ? ", yangi 1-sinf: {$summary['new_first_grade_classes']} ta"
            : '';

        return redirect()
            ->route('admin.school-classes.index')
            ->with(
                'success',
                "{$prefix}Ko'tarildi: {$summary['promoted_classes']} ta sinf ({$summary['promoted_students']} ta o'quvchi), Bitiruvchi: {$summary['graduated_classes']} ta sinf ({$summary['graduated_students']} ta o'quvchi){$newGrade1Msg}."
            )
            ->with('toast_type', $summary['dry_run'] ? 'warning' : 'success');
    }
}
