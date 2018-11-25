<?php

namespace DrudgeRajen\VoyagerDeploymentOrchestrator\Listeners;

use TCG\Voyager\Events\BreadChanged;
use DrudgeRajen\VoyagerDeploymentOrchestrator\VoyagerDeploymentOrchestrator;

class VoyagerBreadChanged
{
    private $deploymentOrchestretor;

    public function __construct(VoyagerDeploymentOrchestrator $orchestretor)
    {
        $this->deploymentOrchestretor = $orchestretor;
    }

    public function handle(BreadChanged $breadChanged)
    {
        return $this->deploymentOrchestretor->handle($breadChanged);
    }
}
