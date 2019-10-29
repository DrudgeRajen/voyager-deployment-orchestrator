<?php

namespace DrudgeRajen\VoyagerDeploymentOrchestrator\OrchestratorHandlers;

use TCG\Voyager\Events\BreadChanged;
use DrudgeRajen\VoyagerDeploymentOrchestrator\ContentManager\FileGenerator;

class BreadAddedHandler
{
    /** @var FileGenerator */
    private $fileGenerator;

    /**
     * BreadAddedHandler constructor.
     *
     * @param FilesGenerator $fileGenerator
     */
    public function __construct(FileGenerator $fileGenerator)
    {
        $this->filesGenerator = $fileGenerator;
    }

    /**
     * Bread Added Handler.
     *
     * @param BreadChanged $breadAdded
     *
     * @return bool
     */
    public function handle(BreadChanged $breadAdded)
    {
        $dataType = $breadAdded->dataType;

        // Generate Data Type Seeder File.
        $this->filesGenerator->generateDataTypeSeedFile($dataType);

        // Generate Data Row Seeder File.
        return $this->filesGenerator->generateDataRowSeedFile($dataType);
    }
}
