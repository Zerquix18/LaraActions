<?php

namespace Zerquix18\LaraActions;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Action;
use Ban;

class LaraActionsServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            // Publishing the configuration file.
            $this->publishes([
                __DIR__.'/../config/laraactions.php' => config_path('laraactions.php'),
            ], 'laraactions.config');
        }

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->call(function () {
                $days_for_actions = env('max_actions_storage_time');
                $days_for_bans    = env('max_bans_storage_time');

                Action::where(
                    'created_at',
                    '<=',
                    Carbon::parse("-{$days_for_actions} days")
                )->delete();

                Ban::where(
                    'created_at',
                    '<=',
                    Carbon::parse("-{$days_for_bans} days")
                )
                ->where('until', '<', Carbon::now())
                ->delete();
            })->daily();
        });
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laraactions.php', 'laraactions');

        // Register the service the package provides.
        $this->app->singleton('laraactions', function ($app) {
            return new Actions;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['laraactions'];
    }
}
