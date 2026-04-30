<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Support\Facades\Log;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->reportable(function (ExamAccessDeniedException $exception): void {
            $request = request();

            Log::warning('exam.access_denied', [
                'message' => $exception->getMessage(),
                'url' => $request?->fullUrl(),
                'method' => $request?->method(),
                'user_id' => $request?->user()?->id,
            ]);
        });

        $this->reportable(function (ExamResourceMismatchException $exception): void {
            $request = request();

            Log::notice('exam.resource_mismatch', [
                'message' => $exception->getMessage(),
                'url' => $request?->fullUrl(),
                'method' => $request?->method(),
                'user_id' => $request?->user()?->id,
            ]);
        });

        $this->reportable(function (ExamStateException $exception): void {
            $request = request();

            Log::info('exam.invalid_state', [
                'message' => $exception->getMessage(),
                'url' => $request?->fullUrl(),
                'method' => $request?->method(),
                'user_id' => $request?->user()?->id,
            ]);
        });
    }

    public function render($request, Throwable $e)
    {
        if ($e instanceof PostTooLargeException) {
            $hint = 'OSPanel: «PHP» → loyiha uchun PHP versiyasi → «Sozlamalar» (yoki php.ini): '
                .'upload_max_filesize va post_max_size ni oshiring (masalan 512M). '
                .'post_max_size yuklanadigan fayldan katta bo‘lishi kerak (video + forma maydonlari).';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'So‘rov hajmi juda katta. '.$hint,
                ], 413);
            }

            return redirect()->back()
                ->withInput($request->except(['image', 'video_file']))
                ->with(
                    'error',
                    'Post saqlanmadi: so‘rov hajmi juda katta. '.$hint
                        .' Katta videoni YouTube ga yuklab, shu yerga havola qoldirish yengilroq.'
                );
        }

        return parent::render($request, $e);
    }
}
