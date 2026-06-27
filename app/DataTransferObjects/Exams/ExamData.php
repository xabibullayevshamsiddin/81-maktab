<?php

namespace App\DataTransferObjects\Exams;

use App\Http\Requests\Exams\SaveExamRequest;

class ExamData
{
    /**
     * @param list<string> $allowedGrades
     */
    public function __construct(
        public string $title,
        public int $durationMinutes,
        public int $requiredQuestions,
        public int $totalPoints,
        public int $passingPoints,
        public array $allowedGrades,
        public ?string $availableFrom,
    ) {}

    public static function fromRequest(SaveExamRequest $request): self
    {
        return new self(
            title: (string) $request->validated('title'),
            durationMinutes: (int) $request->validated('duration_minutes'),
            requiredQuestions: (int) $request->validated('required_questions'),
            totalPoints: (int) $request->validated('total_points'),
            passingPoints: (int) $request->validated('passing_points'),
            allowedGrades: normalize_school_grade_list((array) $request->input('allowed_grades', [])),
            availableFrom: $request->validated('available_from'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(?int $createdBy = null, bool $forceInactive = false): array
    {
        $attributes = [
            'title' => $this->title,
            'duration_minutes' => $this->durationMinutes,
            'required_questions' => $this->requiredQuestions,
            'total_points' => $this->totalPoints,
            'passing_points' => $this->passingPoints,
            'allowed_grades' => $this->allowedGrades,
            'available_from' => $this->availableFrom,
        ];

        if ($createdBy !== null) {
            $attributes['created_by'] = $createdBy;
        }

        if ($forceInactive) {
            $attributes['is_active'] = false;
        }

        return $attributes;
    }
}
