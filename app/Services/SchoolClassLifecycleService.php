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
    public function upsertClass(int $gradeNumber, string $section): array
    {
        $this->assertGradeNumber($gradeNumber);
        $section = SchoolClass::normalizeSection($section);
        if ($section === '') {
            throw new LogicException('Sinf harfi kiritilishi kerak.');
        }

        return DB::transaction(function () use ($gradeNumber, $section): array {
            $schoolClass = SchoolClass::query()
                ->where('grade_number', $gradeNumber)
                ->where('section', $section)
                ->first();

            $created = false;
            $reactivated = false;

            if (! $schoolClass) {
                $schoolClass = SchoolClass::query()->create([
                    'grade_number' => $gradeNumber,
                    'section' => $section,
                    'name' => SchoolClass::buildName($gradeNumber, $section),
                    'is_active' => true,
                    'sort_order' => ($gradeNumber * 100) + SchoolClass::query()->where('grade_number', $gradeNumber)->count(),
                ]);
                $created = true;
            } elseif (! $schoolClass->is_active) {
                $schoolClass->update(['is_active' => true]);
                $reactivated = true;
            }

            forget_school_grade_cache();

            return [
                'class' => $schoolClass->refresh(),
                'created' => $created,
                'reactivated' => $reactivated,
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
            $activeClasses = SchoolClass::query()
                ->active()
                ->orderByDesc('grade_number')
                ->orderBy('sort_order')
                ->get();

            foreach ($activeClasses as $schoolClass) {
                $summary['total_classes']++;
                $gn = (int) $schoolClass->grade_number;
                $sec = (string) $schoolClass->section;

                if ($gn >= 11) {
                    $summary['graduated_classes']++;
                    if (! $dryRun) {
                        $schoolClass->update(['is_active' => false]);
                    }
                } else {
                    $newGn = $gn + 1;
                    $newName = SchoolClass::buildName($newGn, $sec);
                    $summary['promoted_classes']++;
                    if (! $dryRun) {
                        $targetExisting = SchoolClass::query()
                            ->where('grade_number', $newGn)
                            ->where('section', $sec)
                            ->where('id', '!=', $schoolClass->id)
                            ->first();

                        if ($targetExisting) {
                            $targetExisting->update(['is_active' => true]);
                            $schoolClass->update(['is_active' => false]);
                        } else {
                            $schoolClass->update([
                                'grade_number' => $newGn,
                                'name' => $newName,
                                'sort_order' => ($newGn * 100) + (int) $schoolClass->id,
                            ]);
                        }
                    }
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
