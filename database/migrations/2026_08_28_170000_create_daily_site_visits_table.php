<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('daily_site_visits')) {
            Schema::create('daily_site_visits', function (Blueprint $table) {
                $table->id();
                $table->date('date')->unique();
                $table->unsignedInteger('page_views')->default(0);
                $table->unsignedInteger('unique_visitors')->default(0);
                $table->unsignedInteger('auth_visits')->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_site_visits');
    }
};
