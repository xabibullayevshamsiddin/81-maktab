<?php

namespace App\Actions\Exams;

use App\Models\Answer;
use App\Models\Result;
use App\Services\TelegramService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GradeTextAnswerAction
{
    public function handle(Result $result, Answer $answer, bool $isCorrect): Result
    {
        return DB::transaction(function () use ($result, $answer, $isCorrect): Result {
            $result->loadMissing('exam:id,passing_points,title');

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
                'has_pending_review' => $hasPendingManualReview,
            ]);

            // Barcha yozma javoblar baholangan bo'lsa — talabaga Telegram xabar yuborish
            if (! $hasPendingManualReview) {
                $this->sendGradingNotification($result);
            }

            return $result->refresh();
        });
    }

    /**
     * Baholash tugagandan keyin talabaga Telegram xabar yuborish.
     */
    private function sendGradingNotification(Result $result): void
    {
        try {
            $user = $result->user ?? \App\Models\User::find($result->user_id);
            if (! $user || ! $user->telegram_chat_id) {
                return;
            }

            $exam = $result->exam ?? \App\Models\Exam::find($result->exam_id);
            $examTitle = $exam?->title ?? 'Noma\'lum imtihon';
            $score = $result->points_earned ?? 0;
            $maxScore = $result->points_max ?? 0;
            $passed = $result->passed;
            $correctCount = $result->score ?? 0;
            $totalQuestions = $result->total_questions ?? 0;

            $statusEmoji = $passed === true ? '✅' : ($passed === false ? '❌' : '⏳');
            $statusText = $passed === true ? 'O\'tdi' : ($passed === false ? 'Yiqildi' : 'Kutilmoqda');

            $text = "📝 <b>Yozma imtihon baholandi!</b>\n"
                ."━━━━━━━━━━━━━━━━━━━━\n\n"
                ."📚 <b>Imtihon:</b> ".htmlspecialchars($examTitle)."\n"
                ."{$statusEmoji} <b>Natija:</b> {$statusText}\n"
                ."📈 <b>Ball:</b> {$score} / {$maxScore}\n"
                ."✅ <b>To'g'ri:</b> {$correctCount} / {$totalQuestions}\n"
                ."\n━━━━━━━━━━━━━━━━━━━━\n"
                ."Baholash tugadi. Saytda natijangizni ko'rishingiz mumkin.";

            $telegram = app(TelegramService::class);
            $telegram->sendMessage((int) $user->telegram_chat_id, $text);

            // Ulangan ota-onalarga ham xabar yuborish
            $user->notifyLinkedParents($text);

        } catch (\Throwable $e) {
            Log::error('Telegram exam grading notification failed', [
                'result_id' => $result->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
