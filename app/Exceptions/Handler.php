<?php

namespace App\Exceptions;
use Exception;
use App\Mail\ExceptionOccurred;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
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
    }
    /**
     * Report or log an exception.
     *
     * @param \Throwable $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        if ($this->shouldReport($exception)) {
            $this->debounceEmailNotification($exception);
        }
        parent::report($exception);
    }
    public function debounceEmailNotification(Throwable $exception)
    {
        // Define a unique key for this error type
        $cacheKey = 'error_notification:' . md5($exception->getMessage());

        // Set the debounce period (e.g., 120 seconds)
        $debouncePeriod = 120;

        // Check if this error has been reported recently
        if (!Cache::has($cacheKey)) {
            // Send the email notification
            $this->sendEmail($exception);

            // Store a cache entry to prevent re-sending within the debounce period
            Cache::put($cacheKey, true, $debouncePeriod);
        }
    }
    public function sendEmail(Throwable $exception)
    {
        try {
            $isException = $exception instanceof Exception;
            if ($isException === false) {
                $exception = new Exception($exception);
            }

            $e = FlattenException::create($exception);

            $handler = new HtmlErrorRenderer(true);
            $css = $handler->getStylesheet();
            $content = $handler->getBody($e);
            $full_url = request()->fullUrl();
            $content = "<h2> $full_url </h2> <br> $content";
            if (Str::contains($exception->getMessage(), 'Deadlock found when trying to get lock')) {
                //
            } elseif (Str::contains($exception->getMessage(), 'Lock wait timeout exceeded')) {
                //
            } elseif (env('SEND_ERROR_MAIL')) {
                \Illuminate\Support\Facades\Mail::to('jatin.patel@payomatix.com')
                    ->cc(['anil.mathukiya@payomatix.com'])
                    ->send(new ExceptionOccurred($content, $css));
            }
        } catch (Throwable $ex) {
            Log::info('error_mail_report => ' . $ex->getMessage());
        }
    }
}
