<?php

namespace App\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

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
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // 管理者用のパス（/admin/*）だけ、別のセッションCookie名を使う
        if (request()->is('admin/*')) {
            Config::set('session.cookie', 'worklog_admin_session');
        } else {
            Config::set('session.cookie', 'worklog_user_session');
        }
    }
}
