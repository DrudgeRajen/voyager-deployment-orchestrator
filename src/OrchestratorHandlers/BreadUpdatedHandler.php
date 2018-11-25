<?php

namespace DrudgeRajen\VoyagerDeploymentOrchestrator\OrchestratorHandlers;

use TCG\Voyager\Events\BreadChanged;
use DrudgeRajen\VoyagerDeploymentOrchestrator\ContentManager\FilesGenerator;

class BreadUpdatedHandler
{
    private $deploymentFileGenerator;

    public function __construct(FilesGenerator $deploymentFileGenerator)
    {
        $this->deploymentFileGenerator = $deploymentFileGenerator;
    }

    public function handle(BreadChanged $breadUpdated)
    {
        $dataType = $breadUpdated->dataType;

        return $this->deploymentFileGenerator->deleteAndGenerate($dataType);
    }
}
