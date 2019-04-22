<?php

namespace DrudgeRajen\VoyagerDeploymentOrchestrator\Listeners;

use TCG\Voyager\Events\BreadChanged;
use DrudgeRajen\VoyagerDeploymentOrchestrator\VoyagerDeploymentOrchestrator;

class VoyagerBreadChanged
{
    /** @var VoyagerDeploymentOrchestrator */
    private $deploymentOrchestrator;

    /**
     * VoyagerBreadChanged constructor.
     *
     * @param VoyagerDeploymentOrchestrator $orchestrator
     */
    public function __construct(VoyagerDeploymentOrchestrator $orchestrator)
    {
        $this->deploymentOrchestrator = $orchestrator;
    }

    /**
     * @param BreadChanged $breadChanged
     */
    public function handle(BreadChanged $breadChanged)
    {
        return $this->deploymentOrchestrator->handle($breadChanged);
    }
}
