<?php

namespace App\Http\Controllers;

use App\Http\Requests\PromoteAcademicYearRequest;
use App\Http\Requests\StoreSchoolClassRequest;
use App\Models\AcademicYearPromotion;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\SchoolClassLifecycleService;
use Illuminate\Http\RedirectResponse;
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
        );

        $message = match (true) {
            $result['created'] => "{$result['class']->display_name} sinfi qo'shildi.",
            $result['reactivated'] => "{$result['class']->display_name} sinfi qayta faollashtirildi.",
            default => "{$result['class']->display_name} sinfi allaqachon faol.",
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

        return redirect()
            ->route('admin.school-classes.index')
            ->with(
                'success',
                "{$prefix}Ko'tarildi: {$summary['promoted']}, bitiruvchi: {$summary['graduated']}, qayta sinf tanlash kerak: {$summary['selection_required']}."
            )
            ->with('toast_type', $summary['dry_run'] ? 'warning' : 'success');
    }
}
