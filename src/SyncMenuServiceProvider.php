<?php

namespace Ws\SyncMenu;

use Illuminate\Support\ServiceProvider;
use Ws\SyncMenu\Commands\SyncMenu;

class SyncMenuServiceProvider extends ServiceProvider
{
    /**
     * 服务提供者加是否延迟加载.
     *
     * @var bool
     */

    protected $defer = true; // 延迟加载服务

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/database/migrations' => database_path('migrations'),
            __DIR__.'/config/menu.php' => config_path('menu.php'), // 发布配置文件到 laravel 的config 下

        ]);
    }

    /**
     * Register commands.
     */
    protected function registerCommands()
    {
        $this->commands([
            SyncMenu::class,
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('menus',function ($app) {
            return new MenuTest();
        });
        $this->registerCommands();
    }

    public function provides()
    {
        return ['sync-menu'];
    }
}
