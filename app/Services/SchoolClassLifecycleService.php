<?php

namespace App\Services;

use App\Models\AcademicYearPromotion;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LogicException;

class SchoolClassLifecycleService
{
    /**
     * @return array{class: SchoolClass, created: bool, reactivated: bool}
     */
    public function upsertClass(int $gradeNumber, string $section, ?int $maxStudents = null): array
    {
        $this->assertGradeNumber($gradeNumber);
        $section = SchoolClass::normalizeSection($section);
        if ($section === '') {
            throw new LogicException('Sinf harfi kiritilishi kerak.');
        }

        return DB::transaction(function () use ($gradeNumber, $section, $maxStudents): array {
            $schoolClass = SchoolClass::query()
                ->where('grade_number', $gradeNumber)
                ->where('section', $section)
                ->first();

            $created = false;
            $reactivated = false;

            if (! $schoolClass) {
                $schoolClass = SchoolClass::query()->create([
                    'grade_number' => $gradeNumber,
                    'section'      => $section,
                    'name'         => SchoolClass::buildName($gradeNumber, $section),
                    'is_active'    => true,
                    'sort_order'   => ($gradeNumber * 100) + SchoolClass::query()->where('grade_number', $gradeNumber)->count(),
                    'max_students' => $maxStudents,
                ]);
                $created = true;
            } else {
                $updateData = ['is_active' => true];
                if ($maxStudents !== null) {
                    $updateData['max_students'] = $maxStudents;
                }
                if (! $schoolClass->is_active) {
                    $schoolClass->update($updateData);
                    $reactivated = true;
                } elseif ($maxStudents !== null) {
                    $schoolClass->update(['max_students' => $maxStudents]);
                }
            }

            forget_school_grade_cache();

            return [
                'class'        => $schoolClass->refresh(),
                'created'      => $created,
                'reactivated'  => $reactivated,
            ];
        });
    }

    /**
     * @return array{affected_users: int, class_name: string}
     */
    public function disbandClass(SchoolClass $schoolClass, ?int $actorId = null, ?string $reason = null): array
    {
        $className = $schoolClass->display_name;
        $reason = trim((string) ($reason ?: "Sizning {$className} sinfingiz saytdagi faol sinflar ro'yxatidan o'chirildi."));

        return DB::transaction(function () use ($schoolClass, $className, $actorId, $reason): array {
            $schoolClass->update(['is_active' => false]);
            forget_school_grade_cache();

            $affected = 0;

            User::query()
                ->where('grade', $className)
                ->where('is_parent', false)
                ->whereHas('roleRelation', fn ($query) => $query->where('name', User::ROLE_USER))
                ->orderBy('id')
                ->chunkById(100, function ($users) use (&$affected, $className, $reason): void {
                    foreach ($users as $user) {
                        $user->forceFill([
                            'grade' => null,
                            'grade_needs_selection' => true,
                            'grade_selection_reason' => $reason,
                        ])->save();

                        $this->notifyUser(
                            $user,
                            'warning',
                            'Sinfingiz qayta tanlanishi kerak',
                            "{$className} sinfi faol ro'yxatdan o'chirildi. Saytdan foydalanishni davom ettirish uchun mavjud sinflardan birini tanlang."
                        );

                        $affected++;
                    }
                });

            return [
                'affected_users' => $affected,
                'class_name' => $className,
            ];
        });
    }

    /**
     * @return array{total: int, promoted: int, graduated: int, selection_required: int, skipped: int, dry_run: bool}
     */
    public function promoteAcademicYear(int $fromYear, int $toYear, bool $dryRun = false, bool $force = false, ?int $actorId = null): array
    {
        if ($toYear !== $fromYear + 1) {
            throw new LogicException('Keyingi o\'quv yili joriy yildan 1 yil katta bo\'lishi kerak.');
        }

        if (! $dryRun && ! $force && AcademicYearPromotion::query()->where([
            'from_year' => $fromYear,
            'to_year' => $toYear,
        ])->exists()) {
            throw new LogicException("{$fromYear}-{$toYear} o'quv yili ko'tarilishi oldin bajarilgan. Qayta yuritish uchun force kerak.");
        }

        $summary = [
            'total_classes' => 0,
            'promoted_classes' => 0,
            'graduated_classes' => 0,
            'new_first_grade_classes' => 0,
            'total_students' => 0,
            'promoted_students' => 0,
            'graduated_students' => 0,
            'selection_required' => 0,
            'skipped' => 0,
            'dry_run' => $dryRun,
            /* Legacy aliases for backward compatibility */
            'promoted' => 0,
            'graduated' => 0,
        ];

        $runner = function () use (&$summary, $dryRun): void {
            /* 1. Promote active SchoolClass entities (even if 0 students) */
            $activeClasses = SchoolClass::query()->active()->get();
            $sections = $activeClasses->pluck('section')->unique()->values();

            foreach ($sections as $sec) {
                $classesInSec = $activeClasses->where('section', $sec)->keyBy('grade_number');

                // Map of old limits for active classes in this section
                $oldLimits = [];
                foreach ($classesInSec as $gn => $sc) {
                    $oldLimits[$gn] = $sc->max_students;
                }

                // A) Grade 11:
                if (isset($classesInSec[11])) {
                    $summary['graduated_classes']++;
                }

                if (isset($classesInSec[10])) {
                    $summary['promoted_classes']++;
                    if (! $dryRun) {
                        $c11 = SchoolClass::query()->firstOrCreate(
                            ['grade_number' => 11, 'section' => $sec],
                            ['name' => SchoolClass::buildName(11, $sec), 'sort_order' => 1100]
                        );
                        $c11->update([
                            'is_active' => true,
                            'max_students' => $oldLimits[10] ?? null,
                        ]);
                    }
                } else {
                    // No 10th grade promoting into 11th grade -> deactivate 11th grade if it existed
                    if (isset($classesInSec[11]) && ! $dryRun) {
                        $classesInSec[11]->update(['is_active' => false]);
                    }
                }

                // B) Grades 2..10:
                for ($g = 10; $g >= 2; $g--) {
                    $prev = $g - 1; // e.g. 9 for 10
                    if (isset($classesInSec[$prev])) {
                        $summary['promoted_classes']++;
                        if (! $dryRun) {
                            $cg = SchoolClass::query()->firstOrCreate(
                                ['grade_number' => $g, 'section' => $sec],
                                ['name' => SchoolClass::buildName($g, $sec), 'sort_order' => ($g * 100)]
                            );
                            $cg->update([
                                'is_active' => true,
                                'max_students' => $oldLimits[$prev] ?? null,
                            ]);
                        }
                    } else {
                        // No class promoting into grade $g -> deactivate grade $g if it existed
                        if (isset($classesInSec[$g]) && ! $dryRun) {
                            $classesInSec[$g]->update(['is_active' => false]);
                        }
                    }
                }

                // C) Grade 1:
                $summary['new_first_grade_classes']++;
                if (! $dryRun) {
                    $c1 = SchoolClass::query()->firstOrCreate(
                        ['grade_number' => 1, 'section' => $sec],
                        ['name' => SchoolClass::buildName(1, $sec), 'sort_order' => 100]
                    );
                    $c1->update([
                        'is_active' => true,
                        'max_students' => null, // New 1st grade class starts unlimited for new intake
                    ]);
                }
            }

            /* 2. Promote Users / Students */
            User::query()
                ->whereNotNull('grade')
                ->where('grade', '!=', '')
                ->orderBy('id')
                ->chunkById(100, function ($students) use (&$summary, $dryRun): void {
                    foreach ($students as $student) {
                        $summary['total_students']++;
                        $grade = normalize_school_grade((string) ($student->grade ?? ''));

                        if ($grade === null || preg_match('/^(\d{1,2})-([A-Z0-9]+)$/', $grade, $matches) !== 1) {
                            if (! $student->is_parent) {
                                $summary['selection_required']++;
                                if (! $dryRun) {
                                    $this->requireGradeSelection($student, 'Sinfingiz formati eski yoki bo\'sh. Iltimos, mavjud sinflardan birini tanlang.');
                                }
                            }
                            continue;
                        }

                        $gradeNumber = (int) $matches[1];
                        $section = $matches[2];

                        if ($gradeNumber >= 11) {
                            $summary['graduated_students']++;
                            $summary['graduated']++;
                            if (! $dryRun) {
                                $updateData = ['grade' => null];
                                if (! $student->is_parent) {
                                    $updateData['is_parent'] = true;
                                    $updateData['grade_needs_selection'] = false;
                                    $updateData['grade_selection_reason'] = null;
                                }
                                $student->forceFill($updateData)->save();

                                $this->notifyUser(
                                    $student,
                                    'success',
                                    'Bitiruvchi akkaunt ota-ona rejimiga o\'tkazildi',
                                    '11-sinf yakunlangani uchun sinf ma\'lumotlaringiz yangilandi.'
                                );
                            }
                            continue;
                        }

                        $newGrade = ($gradeNumber + 1).'-'.$section;

                        $summary['promoted_students']++;
                        $summary['promoted']++;
                        if (! $dryRun) {
                            $student->forceFill([
                                'grade' => $newGrade,
                                'grade_needs_selection' => false,
                                'grade_selection_reason' => null,
                            ])->save();

                            $this->notifyUser(
                                $student,
                                'info',
                                'Sinfingiz yangilandi',
                                "{$grade} sinfdan {$newGrade} sinfga avtomatik o'tkazildingiz."
                            );
                        }
                    }
                });

            forget_school_grade_cache();
        };

        if ($dryRun) {
            $runner();

            return $summary;
        }

        DB::transaction(function () use ($runner, &$summary, $fromYear, $toYear, $actorId, $force): void {
            if ($force) {
                AcademicYearPromotion::query()->where([
                    'from_year' => $fromYear,
                    'to_year' => $toYear,
                ])->delete();
            }

            $runner();

            AcademicYearPromotion::query()->create([
                'from_year' => $fromYear,
                'to_year' => $toYear,
                'promoted_count' => $summary['promoted'],
                'graduated_count' => $summary['graduated'],
                'selection_required_count' => $summary['selection_required'],
                'skipped_count' => $summary['skipped'],
                'executed_by' => $actorId,
                'executed_at' => now(),
            ]);
        });

        return $summary;
    }

    public function requireGradeSelection(User $user, string $reason): void
    {
        $user->forceFill([
            'grade' => null,
            'grade_needs_selection' => true,
            'grade_selection_reason' => $reason,
        ])->save();

        $this->notifyUser(
            $user,
            'warning',
            'Sinfingizni tanlash majburiy',
            $reason
        );
    }

    private function notifyUser(User $user, string $type, string $title, string $body): void
    {
        if (! Schema::hasTable('user_notifications')) {
            return;
        }

        UserNotification::query()->create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'link' => route('profile.grade-selection.show', absolute: false),
        ]);
    }

    private function assertGradeNumber(int $gradeNumber): void
    {
        if ($gradeNumber < 1 || $gradeNumber > 11) {
            throw new LogicException('Sinf raqami 1 dan 11 gacha bo\'lishi kerak.');
        }
    }
}
