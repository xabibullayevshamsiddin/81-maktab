<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('courses') || ! Schema::hasColumn('courses', 'teacher_id')) {
            return;
        }

        match (DB::getDriverName()) {
            'mysql' => $this->makeNullableOnMysql(),
            'pgsql' => $this->makeNullableOnPostgres(),
            default => null,
        };
    }

    public function down(): void
    {
        if (! Schema::hasTable('courses') || ! Schema::hasColumn('courses', 'teacher_id')) {
            return;
        }

        match (DB::getDriverName()) {
            'mysql' => $this->restoreCascadeOnMysql(),
            'pgsql' => $this->restoreCascadeOnPostgres(),
            default => null,
        };
    }

    private function makeNullableOnMysql(): void
    {
        DB::statement('ALTER TABLE courses DROP FOREIGN KEY courses_teacher_id_foreign');
        DB::statement('ALTER TABLE courses MODIFY teacher_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE courses ADD CONSTRAINT courses_teacher_id_foreign FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL');
    }

    private function makeNullableOnPostgres(): void
    {
        DB::statement('ALTER TABLE courses DROP CONSTRAINT IF EXISTS courses_teacher_id_foreign');
        DB::statement('ALTER TABLE courses ALTER COLUMN teacher_id DROP NOT NULL');
        DB::statement('ALTER TABLE courses ADD CONSTRAINT courses_teacher_id_foreign FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL');
    }

    private function restoreCascadeOnMysql(): void
    {
        DB::statement('ALTER TABLE courses DROP FOREIGN KEY courses_teacher_id_foreign');
        DB::statement('ALTER TABLE courses MODIFY teacher_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE courses ADD CONSTRAINT courses_teacher_id_foreign FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE');
    }

    private function restoreCascadeOnPostgres(): void
    {
        DB::statement('ALTER TABLE courses DROP CONSTRAINT IF EXISTS courses_teacher_id_foreign');
        DB::statement('ALTER TABLE courses ALTER COLUMN teacher_id SET NOT NULL');
        DB::statement('ALTER TABLE courses ADD CONSTRAINT courses_teacher_id_foreign FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE');
    }
};
