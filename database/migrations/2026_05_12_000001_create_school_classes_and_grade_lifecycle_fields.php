<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('school_classes')) {
            Schema::create('school_classes', function (Blueprint $table): void {
                $table->id();
                $table->unsignedTinyInteger('grade_number');
                $table->string('section', 10);
                $table->string('name', 20);
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();

                $table->unique(['grade_number', 'section']);
                $table->index(['is_active', 'grade_number', 'section']);
            });

            $now = now();
            $map = [
                1 => ['A', 'B', 'D', 'E', 'G', 'K', 'V', 'Z'],
                2 => ['A', 'B', 'D', 'E', 'G', 'K', 'V'],
                3 => ['A', 'B', 'D', 'E', 'G', 'K', 'V'],
                4 => ['A', 'B', 'D', 'E', 'G', 'V'],
                5 => ['A', 'G', 'V', 'B'],
                6 => ['A', 'B', 'D', 'G', 'V'],
                7 => ['A', 'B', 'D', 'G', 'V'],
                8 => ['A', 'B', 'G', 'V', 'D'],
                9 => ['A', 'D', 'E', 'V', 'B'],
                10 => ['A', 'D', 'E', 'V', 'B'],
                11 => ['D', 'G', 'V', 'B'],
            ];

            $rows = [];
            foreach ($map as $gradeNumber => $sections) {
                foreach ($sections as $index => $section) {
                    $rows[] = [
                        'grade_number' => $gradeNumber,
                        'section' => $section,
                        'name' => $gradeNumber.'-'.$section,
                        'is_active' => true,
                        'sort_order' => ($gradeNumber * 100) + $index,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            DB::table('school_classes')->insert($rows);
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table): void {
                if (! Schema::hasColumn('users', 'grade_needs_selection')) {
                    $table->boolean('grade_needs_selection')->default(false)->after('grade');
                    $table->index('grade_needs_selection');
                }

                if (! Schema::hasColumn('users', 'grade_selection_reason')) {
                    $table->string('grade_selection_reason')->nullable()->after('grade_needs_selection');
                }
            });
        }

        if (! Schema::hasTable('academic_year_promotions')) {
            Schema::create('academic_year_promotions', function (Blueprint $table): void {
                $table->id();
                $table->unsignedSmallInteger('from_year');
                $table->unsignedSmallInteger('to_year');
                $table->unsignedInteger('promoted_count')->default(0);
                $table->unsignedInteger('graduated_count')->default(0);
                $table->unsignedInteger('selection_required_count')->default(0);
                $table->unsignedInteger('skipped_count')->default(0);
                $table->foreignId('executed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('executed_at');
                $table->timestamps();

                $table->unique(['from_year', 'to_year']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_year_promotions');

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table): void {
                if (Schema::hasColumn('users', 'grade_needs_selection')) {
                    $table->dropIndex(['grade_needs_selection']);
                    $table->dropColumn('grade_needs_selection');
                }

                if (Schema::hasColumn('users', 'grade_selection_reason')) {
                    $table->dropColumn('grade_selection_reason');
                }
            });
        }

        Schema::dropIfExists('school_classes');
    }
};
