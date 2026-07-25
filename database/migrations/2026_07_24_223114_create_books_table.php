<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('title_en')->nullable();
            $table->text('description')->nullable();
            $table->text('description_en')->nullable();
            $table->string('author')->nullable();
            $table->string('subject')->nullable();   // Fan nomi
            $table->year('year')->nullable();         // Nashr yili
            $table->string('grade')->nullable();      // Sinf (1-11 yoki "Barcha")
            $table->string('file_path');              // storage/books/
            $table->string('cover_image')->nullable();
            $table->unsignedBigInteger('file_size')->default(0); // bytes
            $table->unsignedInteger('download_count')->default(0);
            $table->unsignedInteger('view_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('allow_download')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'book_category_id']);
            $table->index('grade');
            $table->index('subject');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
