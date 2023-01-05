<?php

namespace App\Exceptions;

use GuzzleHttp\Exception\ClientException;
use http\Exception;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;
use PDOException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Exception $exception)
    {
        if($exception instanceof NotFoundHttpException){
            return response()->view('errors/404', ['invalid_url'=>true], 404);
        }

        if ($exception instanceof TokenMismatchException && Auth::guest()) {
            error_log('Error :' . $exception->getMessage());
            abort(500);
        }

        if ($exception instanceof TokenMismatchException && getenv('APP_ENV') != 'local') {
            return redirect()->back()->withInput();
        }

        if($exception instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException && getenv('APP_ENV') != 'local') {
            error_log('Error :' . $exception->getMessage());
            abort(404);
        }

        if(($exception instanceof PDOException || $exception instanceof QueryException) && getenv('APP_ENV') != 'local') {
            error_log('Error :' . $exception->getMessage());
            abort(500);
        }

        if ($exception instanceof ClientException) {
            error_log('Error :' . $exception->getMessage());
            abort(500);
        }

        return parent::render($request, $exception);
    }
}
