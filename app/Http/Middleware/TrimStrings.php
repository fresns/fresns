<?php

namespace App\Http\Middleware;

use App\Helpers\DateHelper;
use App\Helpers\LangHelper;
use App\Http\Center\Common\GlobalService;
use Closure;
use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;

// Data conversion layer
class TrimStrings extends Middleware
{
    /**
     * The names of the attributes that should not be trimmed.
     *
     * @var array
     */
    protected $except = [
        'password',
        'password_confirmation',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->header('bdToken');

        if ($request->has('is_enable')) {
            $isEnable = $request->input('is_enable');

            if (! is_numeric($isEnable)) {
                if ($isEnable == 'true') {
                    $isEnable = 1;
                } else {
                    $isEnable = 0;
                }
            }
            $request->offsetSet('is_enable', $isEnable);
        }

        // Switching time
        DateHelper::initTimezone();

        // Switching languages
        LangHelper::initLocale();

        // Initialize global data
        GlobalService::loadData();

        return $next($request);
    }
}
