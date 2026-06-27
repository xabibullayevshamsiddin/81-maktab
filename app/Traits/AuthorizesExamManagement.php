<?php

namespace App\Traits;

use App\Exceptions\ExamAccessDeniedException;
use App\Models\Exam;
use App\Models\User;

trait AuthorizesExamManagement
{
    protected function ensureUserCanManageExams(?User $user): void
    {
        if (! $user?->canManageExams()) {
            throw new ExamAccessDeniedException();
        }
    }

    protected function ensureUserOwnsExam(?User $user, Exam $exam): void
    {
        $this->ensureUserCanManageExams($user);

        if (! $exam->ownsExam($user)) {
            throw new ExamAccessDeniedException();
        }
    }
}
