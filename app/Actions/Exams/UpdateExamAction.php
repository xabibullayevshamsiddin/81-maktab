<?php

namespace App\Actions\Exams;

use App\Contracts\Repositories\ExamRepositoryInterface;
use App\DataTransferObjects\Exams\ExamData;
use App\Models\Exam;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateExamAction
{
    public function __construct(private ExamRepositoryInterface $examRepository) {}

    public function handle(Exam $exam, ExamData $data): Exam
    {
        return DB::transaction(function () use ($exam, $data): Exam {
            $updatedExam = $this->examRepository->update($exam, $data->toAttributes());
            $updatedExam->syncActiveFromQuestions();
            forget_public_exam_caches();

            Log::info('exam.updated', [
                'exam_id' => (int) $updatedExam->id,
                'required_questions' => $updatedExam->required_questions,
                'total_points' => $updatedExam->total_points,
            ]);

            return $updatedExam;
        });
    }
}
