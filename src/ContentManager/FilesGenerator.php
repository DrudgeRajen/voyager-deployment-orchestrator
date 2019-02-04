<?php

namespace DrudgeRajen\VoyagerDeploymentOrchestrator\ContentManager;

use DrudgeRajen\VoyagerDeploymentOrchestrator\ContentManger\FileSystem;

class FilesGenerator
{
    /** @var string */
    const TYPE_SEEDER_SUFFIX = 'BreadTypeAdded';

    /** @var string */
    const ROW_SEEDER_SUFFIX = 'BreadRowAdded';

    /** @var string */
    const DELETED_SEEDER_SUFFIX = 'BreadDeleted';

    /** @var ContentGenerator */
    private $contentManager;

    /** @var FileSystem */
    private $deploymentFileSystem;

    /**
     * FilesGenerator constructor.
     *
     * @param ContentManager $contentManager
     * @param FileSystem $deploymentFileSystem
     */
    public function __construct(ContentManager $contentManager, FileSystem $deploymentFileSystem)
    {
        $this->contentManager = $contentManager;
        $this->deploymentFileSystem = $deploymentFileSystem;
    }

    /**
     * Generate Data Type Seed File.
     *
     * @param $dataType
     *
     * @return bool
     */
    public function generateDataTypeSeedFile($dataType) : bool
    {
        $seederClassName = $this->deploymentFileSystem->generateSeederClassName($dataType->slug,
            self::TYPE_SEEDER_SUFFIX
        );

        $stub = $this->deploymentFileSystem->readStubFile(
            $this->deploymentFileSystem->getStubPath() . '../stubs/data_seed.stub'
        );

        $seedFolderPath = $this->deploymentFileSystem->getSeedFolderPath();

        $seederFile = $this->deploymentFileSystem->getSeederFile($seederClassName, $seedFolderPath);

        $dataType->details = json_encode($dataType->details);

        $seedContent = $this->contentManager->populateContentToStubFile($seederClassName,
            $stub,
            $dataType,
            self::TYPE_SEEDER_SUFFIX
        );

        //We replace the #dataTypeId with the $dataTypeId variable
        // that will exsit in seeder file.
        $seedContent = $this->addDataTypeId($seedContent);
        $this->deploymentFileSystem->addContentToSeederFile($seederFile, $seedContent);

        return $this->updateOrchestraSeeder($seederClassName);
    }

    /**
     * Generate Data Row Seed File.
     *
     * @param $dataType
     * @return bool
     */
    public function generateDataRowSeedFile($dataType) : bool
    {
        $seederClassName = $this->deploymentFileSystem->generateSeederClassName(
            $dataType->slug,
            self::ROW_SEEDER_SUFFIX
        );

        $stub = $this->deploymentFileSystem->readStubFile(
            $this->deploymentFileSystem->getStubPath() . '../stubs/row_seed.stub'
        );

        $stub = str_replace('{{class}}', $seederClassName, $stub);

        $seedFolderPath = $this->deploymentFileSystem->getSeedFolderPath();

        $seederFile = $this->deploymentFileSystem->getSeederFile($seederClassName, $seedFolderPath);

        $seedContent = $this->contentManager->populateContentToStubFile($seederClassName,
            $stub,
            $dataType,
            self::ROW_SEEDER_SUFFIX
        );

        //We replace the #dataTypeId with the $dataTypeId variable
        // that will exsit in seeder file.
        $seedContent = $this->addDataTypeId($seedContent);

        $this->deploymentFileSystem->addContentToSeederFile($seederFile, $seedContent);

        return $this->updateOrchestraSeeder($seederClassName);
    }

    /**
     * Delete And Generate Seed Files.
     *
     * @param $dataType
     */
    public function deleteAndGenerate($dataType)
    {
        $this->deleteSeedFiles($dataType);
        $this->generateDataTypeSeedFile($dataType);
        $this->generateDataRowSeedFile($dataType);
    }

    /**
     * Update Orchestra Seeder Run Method.
     *
     * @param $className
     *
     * @return bool
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function updateOrchestraSeeder($className)
    {
        $databaseSeederPath = $this->deploymentFileSystem->getSeedFolderPath();

        $seederClassname = 'VoyagerDeploymentOrchestratorSeeder';

        $file = $this->deploymentFileSystem->getSeederFile($seederClassname, $databaseSeederPath);

        $content = $this->deploymentFileSystem->getFileContent($file);
        $content = $this->contentManager->updateDeploymentOrchestraSeederContent($className, $content);

        return $this->deploymentFileSystem->addContentToSeederFile($file, $content) !== false;
    }

    /**
     * Delete Seed Files.
     *
     * @param $dataType
     */
    public function deleteSeedFiles($dataType)
    {
        $dataTypSeederClass = $this->deploymentFileSystem->generateSeederClassName($dataType->slug,
            self::TYPE_SEEDER_SUFFIX
        );

        $dataRowSeederClass = $this->deploymentFileSystem->generateSeederClassName($dataType->slug,
            self::ROW_SEEDER_SUFFIX
        );

        $this->deploymentFileSystem->deleteSeedFiles($dataTypSeederClass);
        $this->deploymentFileSystem->deleteSeedFiles($dataRowSeederClass);
    }

    /**
     * Generate Seed File For Deleted Data.
     *
     * @param $dataType
     *
     * @return bool
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function generateSeedFileForDeletedData($dataType)
    {
        $seederClassName = $this->deploymentFileSystem->generateSeederClassName(
            $dataType->slug,
            self::DELETED_SEEDER_SUFFIX
        );

        $stub = $this->deploymentFileSystem->readStubFile(
            $this->deploymentFileSystem->getStubPath() . '../stubs/delete_seed.stub'
        );

        $seedFolderPath = $this->deploymentFileSystem->getSeedFolderPath();

        $seederFile = $this->deploymentFileSystem->getSeederFile($seederClassName, $seedFolderPath);

        $seedContent = $this->contentManager->populateContentToStubFile($seederClassName,
            $stub,
            $dataType,
            self::DELETED_SEEDER_SUFFIX
        );

        $this->deploymentFileSystem->addContentToSeederFile($seederFile, $seedContent);

        return $this->updateOrchestraSeeder($seederClassName);
    }

    /**
     * Repace with $dataType Variable.
     *
     * @param string $seedContent
     *
     * @return mixed|string
     */
    public function addDataTypeId(string $seedContent)
    {
        if (strpos($seedContent, '#dataTypeId') !== 'false') {
            $seedContent = str_replace('\'#dataTypeId\'', '$dataType->id', $seedContent);
        }

        return $seedContent;
    }
}
