<?php

namespace DrudgeRajen\VoyagerDeploymentOrchestrator\Providers;

use TCG\Voyager\Events\BreadChanged;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use DrudgeRajen\VoyagerDeploymentOrchestrator\Listeners\VoyagerBreadChanged;

class OrchestratorEventServiceProvider extends EventServiceProvider
{
    /** @var array $listen */
    protected $listen = [
        BreadChanged::class => [
            VoyagerBreadChanged::class,
        ],
    ];
}
