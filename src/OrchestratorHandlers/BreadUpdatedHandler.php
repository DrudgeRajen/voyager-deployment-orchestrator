<?php

namespace DrudgeRajen\VoyagerDeploymentOrchestrator\OrchestratorHandlers;

use TCG\Voyager\Events\BreadChanged;
use DrudgeRajen\VoyagerDeploymentOrchestrator\ContentManager\FileGenerator;

class BreadUpdatedHandler
{
    /** @var FileGenerator */
    private $fileGenerator;

    /**
     * BreadUpdatedHandler constructor.
     *
     * @param FilesGenerator $fileGenerator
     */
    public function __construct(FileGenerator $fileGenerator)
    {
        $this->fileGenerator = $fileGenerator;
    }

    /**
     * Bread Updated Handler.
     *
     * @param BreadChanged $breadUpdated
     */
    public function handle(BreadChanged $breadUpdated)
    {
        $dataType = $breadUpdated->dataType;

        return $this->fileGenerator->deleteAndGenerate($dataType);
    }
}
