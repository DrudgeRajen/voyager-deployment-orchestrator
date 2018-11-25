<?php

namespace DrudgeRajen\VoyagerDeploymentOrchestrator\OrchestratorHandlers;

use TCG\Voyager\Events\BreadChanged;
use DrudgeRajen\VoyagerDeploymentOrchestrator\ContentManager\FilesGenerator;

class BreadAddedHandler
{
    private $deploymentFilesGenerator;

    public function __construct(FilesGenerator $deploymentFilesGenerator)
    {
        $this->deploymentFilesGenerator = $deploymentFilesGenerator;
    }

    public function handle(BreadChanged $breadAdded)
    {
        $dataType = $breadAdded->dataType;

        //Generate Data Type Seeder File.
        $this->deploymentFilesGenerator->generateDataTypeSeedFile($dataType);

        //Generate Data Row Seeder File.
        return $this->deploymentFilesGenerator->generateDataRowSeedFile($dataType);
    }
}
