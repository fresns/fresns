<?php

namespace App\Exceptions;

use App\Http\Center\Common\LogService;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
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

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof Exception) {
            $msg = $exception->getMessage();
            $traceMsgArr = $exception->getTrace();

            $statusCode = 500;
            if ($exception instanceof NotFoundHttpException) {
                $statusCode = 404;
            }

            // Format error info
            $newTraceMsgArr = [];
            $needField = ['file', 'line', 'function', 'class'];
            foreach ($traceMsgArr as $trace) {
                $valid = true;
                foreach ($needField as $filed) {
                    if (! isset($trace[$filed])) {
                        $valid = false;
                    }
                }
                if ($valid) {
                    $newTraceMsgArr[] = $trace;
                }
            }

            // error info
            LogService::warning('error', $exception);

            return response()->view('commons.error', [
                'status'  => $statusCode,
                'msg' => $msg,
                'traceMsgArr' => $newTraceMsgArr,
            ], 500);
        }

        return parent::render($request, $exception);
    }
}
