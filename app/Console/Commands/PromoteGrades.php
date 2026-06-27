<?php

namespace App\Console\Commands;

use App\Services\SchoolClassLifecycleService;
use Illuminate\Console\Command;
use LogicException;

class PromoteGrades extends Command
{
    protected $signature = 'grades:promote
        {--from-year= : Joriy o\'quv yilining boshlanish yili}
        {--to-year= : Keyingi o\'quv yilining boshlanish yili}
        {--dry-run : Hech narsani o\'zgartirmaydi, faqat ko\'rsatadi}
        {--force : Shu o\'quv yili promotionini qayta ishga tushirishga ruxsat beradi}';

    protected $description = 'O\'quvchilarni keyingi sinfga o\'tkazadi, 11-sinflarni ota-ona rejimiga chiqaradi.';

    public function __construct(
        private readonly SchoolClassLifecycleService $schoolClassLifecycleService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');
        $fromYear = (int) ($this->option('from-year') ?: now()->year);
        $toYear = (int) ($this->option('to-year') ?: ($fromYear + 1));

        if ($dryRun) {
            $this->info('[DRY RUN] Hech narsa o\'zgartirilmaydi.');
        }

        try {
            $summary = $this->schoolClassLifecycleService->promoteAcademicYear(
                fromYear: $fromYear,
                toYear: $toYear,
                dryRun: $dryRun,
                force: $force,
            );
        } catch (LogicException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->info("{$fromYear}-{$toYear} o'quv yili promotion natijasi:");
        $this->line("Jami ko'rilgan foydalanuvchilar: {$summary['total']}");
        $this->line("Ko'tarildi: {$summary['promoted']}");
        $this->line("Bitiruvchi -> ota-ona: {$summary['graduated']}");
        $this->line("Majburiy sinf tanlashga yuborildi: {$summary['selection_required']}");
        $this->line("O'tkazib yuborildi: {$summary['skipped']}");

        if ($dryRun) {
            $this->warn('Bu DRY RUN edi — hech narsa saqlanmadi.');
        }

        return self::SUCCESS;
    }
}
