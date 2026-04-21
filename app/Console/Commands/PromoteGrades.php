<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class PromoteGrades extends Command
{
    protected $signature = 'grades:promote {--dry-run : Hech narsani o\'zgartirmaydi, faqat ko\'rsatadi}';

    protected $description = 'Barcha o\'quvchilarning sinfini 1 ga ko\'taradi. 11-sinf -> ota-ona.';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('[DRY RUN] Hech narsa o\'zgartirilmaydi.');
        }

        $userRoleId = Role::defaultUserRoleId();

        $students = User::query()
            ->where('role_id', $userRoleId)
            ->where('is_parent', false)
            ->whereNotNull('grade')
            ->where('grade', '!=', '')
            ->get();

        $promoted = 0;
        $graduated = 0;
        $skipped = 0;

        foreach ($students as $student) {
            $grade = trim((string) $student->grade);

            if (! preg_match('/^(\d{1,2})-([A-Z0-9]+)$/i', $grade, $m)) {
                $this->warn("Noto'g'ri format: #{$student->id} {$student->name} — \"{$grade}\" (o'tkazib yuborildi)");
                $skipped++;
                continue;
            }

            $num = (int) $m[1];
            $section = strtoupper($m[2]);

            // YT ni olib tashlash (har ehtimolga qarshi)
            $section = str_replace('YT', '', $section);

            if ($num >= 11) {
                if (! $dryRun) {
                    $student->update([
                        'grade' => null,
                        'is_parent' => true,
                    ]);
                }
                $this->line("  Bitiruvchi: #{$student->id} {$student->name} ({$grade} -> ota-ona)");
                $graduated++;
            } else {
                $newGrade = ($num + 1) . '-' . $section;
                if (! $dryRun) {
                    $student->update(['grade' => $newGrade]);
                }
                $this->line("  Ko'tarildi: #{$student->id} {$student->name} ({$grade} -> {$newGrade})");
                $promoted++;
            }
        }

        $this->newLine();
        $this->info("Jami: {$students->count()} o'quvchi. Ko'tarildi: {$promoted}, Bitiruvchi: {$graduated}, O'tkazib yuborildi: {$skipped}.");

        if ($dryRun) {
            $this->warn('Bu DRY RUN edi — hech narsa saqlanmadi.');
        }

        return self::SUCCESS;
    }
}
