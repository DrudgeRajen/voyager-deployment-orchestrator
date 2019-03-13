<?php

namespace DrudgeRajen\VoyagerDeploymentOrchestrator\OrchestratorHandlers;

use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Events\BreadChanged;
use DrudgeRajen\VoyagerDeploymentOrchestrator\ContentManager\FilesGenerator;

class BreadDeletedHandler
{
    /** @var FilesGenerator */
    private $filesGenerator;

    /**
     * VoyagerDeleted constructor.
     *
     * @param FilesGenerator $filesGenerator
     */
    public function __construct(FilesGenerator $filesGenerator)
    {
        $this->filesGenerator = $filesGenerator;
    }

    /**
     * Bread Deleted Handler
     *
     * @param BreadChanged $breadDataDeleted
     *
     * @return bool
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle(BreadChanged $breadDataDeleted) : bool
    {
        $dataType = $breadDataDeleted->dataType;

        // Delete Translations, if present
        if (is_bread_translatable($dataType)) {
            $dataType->deleteAttributeTranslations($dataType->getTranslatableAttributes());
        }

        $dataType->destroy($dataType->id);

        if (! is_null($dataType)) {
            Voyager::model('Permission')->removeFrom($dataType->name);
        }

        //Finally, We can delete seed files.
        $this->filesGenerator->deleteSeedFiles($dataType);

        // After deleting seeds file, we create new seed file in order to rollback
        // the seeded data.
        return $this->filesGenerator->generateSeedFileForDeletedData($dataType);
    }
}
