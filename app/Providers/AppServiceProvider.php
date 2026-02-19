<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Helpers\SettingsHelper;

use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\EnsureIsAdmin;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function run(): void
    {
        Schema::defaultStringLength(191);
    }

     public function boot()
    {
        // Share settings with all views
        view()->composer('*', function ($view) {
            $view->with([
                'siteName' => SettingsHelper::get('site_name', 'Pet Social'),
                'siteEmail' => SettingsHelper::get('site_email', 'admin@petsocial.com'),
                'isMaintenance' => SettingsHelper::get('maintenance_mode', '0') == '1',
            ]);      
        });
        
        $router = $this->app['router'];
        $router->aliasMiddleware('admin', EnsureIsAdmin::class);
        $router->aliasMiddleware('permission', CheckPermission::class);

        
    }
}