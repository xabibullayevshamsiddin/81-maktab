<?php

namespace App\Console\Commands;

use App\Support\PublicStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PruneOrphanedPublicMedia extends Command
{
    protected $signature = 'storage:prune-orphaned-media
        {--delete : Actually delete orphaned files}
        {--limit=0 : Limit how many orphaned files to print/delete}';

    protected $description = 'Find or delete public storage media files that are no longer referenced by database records.';

    public function handle(): int
    {
        $referenced = $this->referencedMediaPaths();
        $orphans = collect($this->managedMediaFiles())
            ->reject(static fn (string $path): bool => isset($referenced[$path]))
            ->values();

        $limit = max(0, (int) $this->option('limit'));
        $visibleOrphans = $limit > 0 ? $orphans->take($limit) : $orphans;

        if ($orphans->isEmpty()) {
            $this->info('Orphaned media topilmadi.');

            return self::SUCCESS;
        }

        $this->warn('Orphaned media: '.$orphans->count().' ta fayl.');
        foreach ($visibleOrphans as $path) {
            $this->line($path);
        }

        if ($limit > 0 && $orphans->count() > $limit) {
            $this->line('Yana '.($orphans->count() - $limit).' ta fayl bor.');
        }

        if (! $this->option('delete')) {
            $this->info('Dry-run. O\'chirish uchun: php artisan storage:prune-orphaned-media --delete');

            return self::SUCCESS;
        }

        $deleted = 0;
        foreach ($orphans as $path) {
            if (PublicStorage::delete($path)) {
                $deleted++;
            }
        }

        $this->info("O'chirildi: {$deleted} ta fayl.");

        return self::SUCCESS;
    }

    /**
     * @return array<string, true>
     */
    private function referencedMediaPaths(): array
    {
        $references = [];

        foreach ($this->mediaColumns() as [$table, $column]) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            DB::table($table)
                ->whereNotNull($column)
                ->orderBy('id')
                ->select($column)
                ->chunk(500, function ($rows) use (&$references, $column): void {
                    foreach ($rows as $row) {
                        $path = PublicStorage::normalize($row->{$column});
                        if ($path !== null) {
                            $references[$path] = true;
                        }
                    }
                });
        }

        return $references;
    }

    /**
     * @return array<int, array{0: string, 1: string}>
     */
    private function mediaColumns(): array
    {
        return [
            ['posts', 'image'],
            ['posts', 'video_path'],
            ['teachers', 'image'],
            ['courses', 'image'],
            ['questions', 'image_path'],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function managedMediaFiles(): array
    {
        $files = [];

        foreach (['posts', 'posts/videos', 'teachers', 'courses', 'exam-questions'] as $directory) {
            if (Storage::disk('public')->exists($directory)) {
                array_push($files, ...Storage::disk('public')->allFiles($directory));
            }
        }

        return array_values(array_unique($files));
    }
}
