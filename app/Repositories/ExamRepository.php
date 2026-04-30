<?php

namespace App\Repositories;

use App\Contracts\Repositories\ExamRepositoryInterface;
use App\Models\Exam;

class ExamRepository implements ExamRepositoryInterface
{
    public function create(array $attributes): Exam
    {
        return Exam::query()->create($attributes);
    }

    public function update(Exam $exam, array $attributes): Exam
    {
        $exam->update($attributes);

        return $exam->refresh();
    }
}
