<?php

namespace App\Actions\Exams;

use App\Models\Answer;
use App\Models\Result;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GradeTextAnswerAction
{
    public function handle(Result $result, Answer $answer, bool $isCorrect): Result
    {
        return DB::transaction(function () use ($result, $answer, $isCorrect): Result {
            $result->loadMissing('exam:id,passing_points');

            $answer->update([
                'is_correct_override' => $isCorrect,
            ]);

            $answers = Answer::query()
                ->where('result_id', $result->id)
                ->with(['option:id,is_correct', 'question:id,points,question_type'])
                ->get();

            $correctCount = 0;
            $pointsEarned = 0;
            $hasPendingManualReview = false;

            foreach ($answers as $item) {
                if ($item->question?->isTextType()) {
                    if ($item->is_correct_override === null && filled($item->text_answer)) {
                        $hasPendingManualReview = true;
                        continue;
                    }
                }

                if ($item->isCorrectAnswer()) {
                    $correctCount++;
                    $pointsEarned += (int) ($item->question?->points ?? 0);
                }
            }

            $passing = (int) ($result->exam?->passing_points ?? 0);
            $passed = $hasPendingManualReview
                ? null
                : ($passing > 0 ? $pointsEarned >= $passing : true);

            $result->update([
                'score' => $correctCount,
                'points_earned' => $pointsEarned,
                'passed' => $passed,
            ]);

            Log::info('exam.text_answer.graded', [
                'result_id' => (int) $result->id,
                'answer_id' => (int) $answer->id,
                'is_correct' => $isCorrect,
                'points_earned' => $pointsEarned,
            ]);

            return $result->refresh();
        });
    }
}
