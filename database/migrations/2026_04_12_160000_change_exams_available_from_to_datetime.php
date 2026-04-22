<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE exams MODIFY available_from DATETIME NULL');

            return;
        }

        Schema::table('exams', function (Blueprint $table) {
            $table->dateTime('available_from_dt')->nullable();
        });

        $rows = DB::table('exams')->select('id', 'available_from')->whereNotNull('available_from')->get();
        foreach ($rows as $row) {
            $d = $row->available_from;
            if ($d === null || $d === '') {
                continue;
            }
            $s = (string) $d;
            $dt = strlen($s) > 10 ? $s : ($s . ' 00:00:00');
            DB::table('exams')->where('id', $row->id)->update(['available_from_dt' => $dt]);
        }

        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('available_from');
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->renameColumn('available_from_dt', 'available_from');
        });
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE exams MODIFY available_from DATE NULL');

            return;
        }

        Schema::table('exams', function (Blueprint $table) {
            $table->date('available_from_date')->nullable();
        });

        $rows = DB::table('exams')->select('id', 'available_from')->whereNotNull('available_from')->get();
        foreach ($rows as $row) {
            $s = (string) $row->available_from;
            $d = strlen($s) >= 10 ? substr($s, 0, 10) : $s;
            DB::table('exams')->where('id', $row->id)->update(['available_from_date' => $d]);
        }

        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('available_from');
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->renameColumn('available_from_date', 'available_from');
        });
    }
};
