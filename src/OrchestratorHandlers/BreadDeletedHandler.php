<?php

namespace DrudgeRajen\VoyagerDeploymentOrchestrator\OrchestratorHandlers;

use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Events\BreadChanged;
use Illuminate\Support\Facades\Artisan;
use DrudgeRajen\VoyagerDeploymentOrchestrator\ContentManager\FileGenerator;

class BreadDeletedHandler
{
    /** @var FileGenerator */
    private $fileGenerator;

    /**
     * VoyagerDeleted constructor.
     *
     * @param FilesGenerator $fileGenerator
     */
    public function __construct(FileGenerator $fileGenerator)
    {
        $this->fileGenerator = $fileGenerator;
    }

    /**
     * Bread Deleted Handler.
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

        // Finally, We can delete seed files.
        $this->fileGenerator->deleteSeedFiles($dataType);

        // Since, voyager cache the menu, after seed deletion we clear admin menu cache as well.
        Artisan::call('cache:forget', ['key' => 'voyager_menu_admin']);

        // After deleting seeds file, we create new seed file in order to rollback
        // the seeded data.
        return $this->fileGenerator->generateSeedFileForDeletedData($dataType);
    }
}
