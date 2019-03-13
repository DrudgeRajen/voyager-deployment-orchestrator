<?php

namespace DrudgeRajen\VoyagerDeploymentOrchestrator\OrchestratorHandlers;

use TCG\Voyager\Events\BreadChanged;
use DrudgeRajen\VoyagerDeploymentOrchestrator\ContentManager\FilesGenerator;

class BreadAddedHandler
{
    /** @var FilesGenerator */
    private $filesGenerator;

    /**
     * BreadAddedHandler constructor.
     *
     * @param FilesGenerator $filesGenerator
     */
    public function __construct(FilesGenerator $filesGenerator)
    {
        $this->filesGenerator = $filesGenerator;
    }

    /**
     * Bread Added Handler
     *
     * @param BreadChanged $breadAdded
     *
     * @return bool
     */
    public function handle(BreadChanged $breadAdded)
    {
        $dataType = $breadAdded->dataType;

        //Generate Data Type Seeder File.
        $this->filesGenerator->generateDataTypeSeedFile($dataType);

        //Generate Data Row Seeder File.
        return $this->filesGenerator->generateDataRowSeedFile($dataType);
    }
}
