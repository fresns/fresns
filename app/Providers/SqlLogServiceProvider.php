<?php

namespace App\Providers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SqlLogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerQueryLogger();
    }

    /**
     * SQL time-consuming query log at development time
     */
    protected function registerQueryLogger()
    {
        if (!$this->app['config']->get('app.debug')) {
            return;
        }

        $this->app['config']->set('logging.channels.sql', config('logging.channels.daily'));
        $this->app['config']->set('logging.channels.sql.path', storage_path('logs/sql.log'));

        DB::listen(function (QueryExecuted $query) {
            $sqlWithPlaceholders = str_replace(['%', '?'], ['%%', '%s'], $query->sql);
            $bindings            = $query->connection->prepareBindings($query->bindings);
            $pdo                 = $query->connection->getPdo();
            $realSql             = vsprintf($sqlWithPlaceholders, array_map([$pdo, 'quote'], $bindings));
            $duration            = $this->formatDuration($query->time / 1000);
            Log::channel('sql')->debug(sprintf('[%s] %s | %s: %s', $duration, $realSql, request()->method(), request()->getRequestUri()));
        });
    }

    /**
     * Time unit conversion
     *
     * @param $seconds
     *
     * @return string
     */
    private function formatDuration($seconds)
    {
        if ($seconds < 0.001) {
            return round($seconds * 1000000) . 'μs';
        } elseif ($seconds < 1) {
            return round($seconds * 1000, 2) . 'ms';
        }

        return round($seconds, 2) . 's';
    }
}
