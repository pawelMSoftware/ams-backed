<?php

namespace App\Providers;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\App;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstradp any application services.
     *
     * @return void
     */
    public function boot()
    {
        // log sql queries
        if (env('DB_LOGQUERY', false)) {
            DB::listen(function ($query) {
                Log::info(
                    $query->sql,
                    $query->bindings,
                    $query->time
                );
            });
        }

        Builder::macro('whereAllLike', function ($fields) {
            foreach ($fields as $field => $value) {
                $isNull = !is_null($value) && $value !== 'null';
                if (!empty($field) && $isNull) {
                    $this->where($field, 'LIKE', "%{$value}%");
                }
            }
            return $this;
        });

        if (App::environment() === 'testing') {
            Schema::defaultStringLength(191);
        }
    }
}
