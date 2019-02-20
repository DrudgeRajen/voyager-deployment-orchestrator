<?php

namespace DrudgeRajen\VoyagerDeploymentOrchestrator\Listeners;

use TCG\Voyager\Events\BreadChanged;
use DrudgeRajen\VoyagerDeploymentOrchestrator\VoyagerDeploymentOrchestrator;

class VoyagerBreadChanged
{
    private $deploymentOrchestrator;

    public function __construct(VoyagerDeploymentOrchestrator $orchestrator)
    {
        $this->deploymentOrchestrator = $orchestrator;
    }

    public function handle(BreadChanged $breadChanged)
    {
        return $this->deploymentOrchestrator->handle($breadChanged);
    }
}
