<?php

namespace DrudgeRajen\VoyagerDeploymentOrchestrator\OrchestratorHandlers;

use TCG\Voyager\Events\BreadChanged;
use DrudgeRajen\VoyagerDeploymentOrchestrator\ContentManager\FilesGenerator;

class BreadUpdatedHandler
{
    /** @var FilesGenerator */
    private $fileGenerator;

    /**
     * BreadUpdatedHandler constructor.
     *
     * @param FilesGenerator $filesGenerator
     */
    public function __construct(FilesGenerator $filesGenerator)
    {
        $this->fileGenerator = $filesGenerator;
    }

    /**
     * Bread Updated Handler
     *
     * @param BreadChanged $breadUpdated
     */
    public function handle(BreadChanged $breadUpdated)
    {
        $dataType = $breadUpdated->dataType;

        return $this->fileGenerator->deleteAndGenerate($dataType);
    }
}
