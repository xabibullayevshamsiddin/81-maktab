<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExamResourceMismatchException extends NotFoundHttpException
{
    public function __construct(string $message = "So'ralgan imtihon resursi topilmadi.")
    {
        parent::__construct($message);
    }
}
