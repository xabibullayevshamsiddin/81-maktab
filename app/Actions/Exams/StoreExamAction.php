<?php

namespace App\Actions\Exams;

use App\Contracts\Repositories\ExamRepositoryInterface;
use App\DataTransferObjects\Exams\ExamData;
use App\Models\Exam;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StoreExamAction
{
    public function __construct(private ExamRepositoryInterface $examRepository) {}

    public function handle(ExamData $data, ?int $createdBy = null): Exam
    {
        return DB::transaction(function () use ($data, $createdBy): Exam {
            $exam = $this->examRepository->create(
                $data->toAttributes($createdBy, true)
            );

            forget_public_exam_caches();

            Log::info('exam.created', [
                'exam_id' => (int) $exam->id,
                'created_by' => $createdBy,
                'required_questions' => $exam->required_questions,
                'total_points' => $exam->total_points,
            ]);

            return $exam;
        });
    }
}
