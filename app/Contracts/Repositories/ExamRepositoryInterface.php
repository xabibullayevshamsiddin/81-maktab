<?php

namespace App\Contracts\Repositories;

use App\Models\Exam;

interface ExamRepositoryInterface
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): Exam;

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(Exam $exam, array $attributes): Exam;
}
