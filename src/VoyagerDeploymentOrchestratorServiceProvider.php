<?php

namespace DrudgeRajen\VoyagerDeploymentOrchestrator;

use Illuminate\Support\ServiceProvider;
use DrudgeRajen\VoyagerDeploymentOrchestrator\Providers\OrchestratorEventServiceProvider;

class VoyagerDeploymentOrchestratorServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $publishablePath = dirname(__DIR__) . '/publishable';

        $this->publishes([
            "{$publishablePath}/database/seeds/" => database_path('seeds/breads'),
        ]);
    }

    public function register()
    {
        $this->app->register(OrchestratorEventServiceProvider::class);
    }
}
