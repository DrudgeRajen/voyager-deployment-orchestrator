<?php

namespace DrudgeRajen\VoyagerDeploymentOrchestrator;

use Illuminate\Support\ServiceProvider;
use DrudgeRajen\VoyagerDeploymentOrchestrator\Providers\OrchestratorEventServiceProvider;

class VoyagerDeploymentOrchestratorServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $publishablePath = dirname(__DIR__) . '/publishable';

        $publishable = [
            'seeds' => [
                "{$publishablePath}/database/seeds/" => database_path('seeds/breads'),
            ],
            'config' => [
                "{$publishablePath}/config/voyager-deployment-orchestrator.php" => config_path('voyager-deployment-orchestrator.php'),
            ],
        ];

        foreach ($publishable as $group => $paths) {
            $this->publishes($paths, $group);
        }

        $this->commands(Commands\VDOSeed::class);
    }

    public function register()
    {
        $this->app->register(OrchestratorEventServiceProvider::class);
    }
}
