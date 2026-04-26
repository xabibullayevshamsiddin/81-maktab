<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ExamAccessDeniedException extends HttpException
{
    public function __construct(string $message = "Bu imtihon resursiga kirish huquqingiz yo'q.")
    {
        parent::__construct(403, $message);
    }
}
