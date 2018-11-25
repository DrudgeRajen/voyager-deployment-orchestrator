<?php

namespace DrudgeRajen\VoyagerDeploymentOrchestra\OrchestraHandlers;

use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Events\BreadChanged;
use DrudgeRajen\VoyagerDeploymentOrchestrator\ContentManager\FilesGenerator;

class BreadDeletedHandler
{
    private $deploymentFileGenerator;

    /**
     * VoyagerDeleted constructor.
     * @param FilesGenerator $deploymentFilesGenerator
     */
    public function __construct(FilesGenerator $deploymentFilesGenerator)
    {
        $this->deploymentFileGenerator = $deploymentFilesGenerator;
    }

    public function handle(BreadChanged $breadDataDeleted)
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
        $this->deploymentFileGenerator->deleteSeedFiles($dataType);

        // After deleting seeds file, we create new seed file in order to rollback
        // the seeded data.
        return $this->deploymentFileGenerator->generateSeedFileForDeletedData($dataType);
    }
}
