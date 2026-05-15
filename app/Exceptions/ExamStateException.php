<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ExamStateException extends HttpException
{
    public function __construct(string $message = 'Imtihon holati ushbu amalni bajarishga ruxsat bermaydi.')
    {
        parent::__construct(422, $message);
    }
}
