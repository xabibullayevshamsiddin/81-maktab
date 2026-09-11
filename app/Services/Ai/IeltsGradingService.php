<?php

namespace App\Services\Ai;

class IeltsGradingService
{
    public function __construct(protected AiService $aiService)
    {
        // Reuses your existing AiService (Gemini-based) constructor injection.
    }

    /**
     * Grade an IELTS Writing answer and return a band score + per-criterion feedback.
     *
     * @param string $taskPrompt   The writing task text shown to the student.
     * @param string $studentText  The student's submitted essay/response.
     * @param bool   $detailedFeedback  Pass true for donor ranks 1+ (per-criterion breakdown).
     */
    public function gradeWriting(string $taskPrompt, string $studentText, bool $detailedFeedback = false): array
    {
        $prompt = $this->buildGradingPrompt($taskPrompt, $studentText, $detailedFeedback);

        $raw = $this->aiService->askRaw(
            'Siz rasmiy IELTS Writing eksperti sifatida faqat so\'ralgan JSON formatida javob berasiz, boshqa hech narsa yozmaysiz.',
            $prompt
        );

        return $this->parseGradingResponse($raw, $detailedFeedback);
    }

    protected function buildGradingPrompt(string $taskPrompt, string $studentText, bool $detailedFeedback): string
    {
        $criteria = $detailedFeedback
            ? "Task Achievement, Coherence and Cohesion, Lexical Resource, Grammatical Range and Accuracy"
            : "an overall band only";

        return <<<PROMPT
You are an official IELTS Writing examiner. Grade the following response strictly using
the public IELTS Writing band descriptors (band 0-9, in 0.5 increments).

Task given to the student:
"""
{$taskPrompt}
"""

Student's response:
"""
{$studentText}
"""

Return ONLY valid JSON (no markdown fences) with this exact shape:
{
  "overall_band": <number>,
  "criteria": {
    "task_achievement": <number or null>,
    "coherence_cohesion": <number or null>,
    "lexical_resource": <number or null>,
    "grammar_accuracy": <number or null>
  },
  "comments": "<short actionable feedback in Uzbek, max 5 sentences>",
  "disclaimer": "Bu AI tomonidan taxminiy baholash, rasmiy IELTS natijasi emas."
}

Provide {$criteria}. If detailed criteria were not requested, set the unused criteria fields to null.
PROMPT;
    }

    protected function parseGradingResponse(string $raw, bool $detailedFeedback): array
    {
        $cleaned = trim($raw);
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $cleaned, $matches)) {
            $cleaned = trim($matches[1]);
        }

        $decoded = json_decode($cleaned, true);

        if (! is_array($decoded) || ! isset($decoded['overall_band'])) {
            // Fallback so a malformed AI response never crashes the request.
            return [
                'overall_band' => null,
                'criteria' => [
                    'task_achievement' => null,
                    'coherence_cohesion' => null,
                    'lexical_resource' => null,
                    'grammar_accuracy' => null,
                ],
                'comments' => 'AI javobini qayta ishlab bo\'lmadi, iltimos qayta urinib ko\'ring.',
                'disclaimer' => 'Bu AI tomonidan taxminiy baholash, rasmiy IELTS natijasi emas.',
            ];
        }

        return $decoded;
    }
}
