<?php

namespace DrudgeRajen\VoyagerDeploymentOrchestrator\ContentManager;

use Exception;
use TCG\Voyager\Models\DataType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\DatabaseManager;
use DrudgeRajen\VoyagerDeploymentOrchestrator\ContentManager\FileSystem;

class FileGenerator
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
    private $fileSystem;

    /** @var DatabaseManager */
    private $databaseManager;

    /**
     * FilesGenerator constructor.
     *
     * @param ContentManager $contentManager
     * @param FileSystem $deploymentFileSystem
     */
    public function __construct(
        ContentManager $contentManager,
        FileSystem $fileSystem,
        DatabaseManager $databaseManager
    ) {
        $this->contentManager  = $contentManager;
        $this->fileSystem      = $fileSystem;
        $this->databaseManager = $databaseManager;
    }

    /**
     * Generate Data Type Seed File.
     *
     * @param DataType $dataType
     *
     * @return bool
     */
    public function generateDataTypeSeedFile(DataType $dataType) : bool
    {
        $seederClassName = $this->fileSystem->generateSeederClassName(
            $dataType->slug,
            self::TYPE_SEEDER_SUFFIX
        );

        $stub = $this->fileSystem->getFileContent(
            $this->fileSystem->getStubPath() . '../stubs/data_seed.stub'
        );

        $seedFolderPath = $this->fileSystem->getSeedFolderPath();

        $seederFile = $this->fileSystem->getSeederFile($seederClassName, $seedFolderPath);

        $dataType->details = json_encode($dataType->details);

        $stub = $this->contentManager->replaceString('{{class}}', $seederClassName, $stub);

        $seedContent = $this->contentManager->populateContentToStubFile(
            $stub,
            $dataType,
            self::TYPE_SEEDER_SUFFIX
        );

        // We replace the #dataTypeId with the $dataTypeId variable
        // that will exist in seeder file.
        $seedContent = $this->addDataTypeId($seedContent);

        $this->fileSystem->addContentToSeederFile($seederFile, $seedContent);

        return $this->updateOrchestraSeeder($seederClassName);
    }

    /**
     * Generate Data Row Seed File.
     *
     * @param $dataType
     *
     * @return bool
     */
    public function generateDataRowSeedFile(DataType $dataType) : bool
    {
        $seederClassName = $this->fileSystem->generateSeederClassName(
            $dataType->slug,
            self::ROW_SEEDER_SUFFIX
        );

        $stub = $this->fileSystem->getFileContent(
            $this->fileSystem->getStubPath() . '../stubs/row_seed.stub'
        );

        $seedFolderPath = $this->fileSystem->getSeedFolderPath();

        $seederFile = $this->fileSystem->getSeederFile($seederClassName, $seedFolderPath);

        $stub = $this->contentManager->replaceString('{{class}}', $seederClassName, $stub);

        $seedContent = $this->contentManager->populateContentToStubFile(
            $stub,
            $dataType,
            self::ROW_SEEDER_SUFFIX
        );

        // We replace the #dataTypeId with the $dataTypeId variable
        // that will exist in seeder file.
        $seedContent = $this->addDataTypeId($seedContent);

        $this->fileSystem->addContentToSeederFile($seederFile, $seedContent);

        return $this->updateOrchestraSeeder($seederClassName);
    }

    /**
     * Delete And Generate Seed Files.
     *
     * @param $dataType
     */
    public function deleteAndGenerate(DataType $dataType)
    {
        $this->deleteSeedFiles($dataType);

        $this->generateDataTypeSeedFile($dataType);

        $this->generateDataRowSeedFile($dataType);
    }

    /**
     * Update Orchestra Seeder Run Method.
     *
     * @param string $className
     *
     * @return bool
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function updateOrchestraSeeder(string $className) : bool
    {
        $databaseSeederPath = $this->fileSystem->getSeedFolderPath();

        $seederClassname = 'VoyagerDeploymentOrchestratorSeeder';

        $file = $this->fileSystem->getSeederFile($seederClassname, $databaseSeederPath);

        $content = $this->fileSystem->getFileContent($file);

        $content = $this->contentManager->updateDeploymentOrchestraSeederContent($className, $content);

        return $this->fileSystem->addContentToSeederFile($file, $content);
    }

    /**
     * Delete Seed Files.
     *
     * @param DataType $dataType
     */
    public function deleteSeedFiles(DataType $dataType)
    {
        $dataTypSeederClass = $this->fileSystem->generateSeederClassName(
            $dataType->slug,
            self::TYPE_SEEDER_SUFFIX
        );

        $dataRowSeederClass = $this->fileSystem->generateSeederClassName(
            $dataType->slug,
            self::ROW_SEEDER_SUFFIX
        );

        $this->fileSystem->deleteSeedFiles($dataTypSeederClass);

        $this->fileSystem->deleteSeedFiles($dataRowSeederClass);
    }

    /**
     * Generate Seed File For Deleted Data.
     *
     * @param DataType $dataType
     *
     * @return bool
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function generateSeedFileForDeletedData(DataType $dataType) : bool
    {
        $seederClassName = $this->fileSystem->generateSeederClassName(
            $dataType->slug,
            self::DELETED_SEEDER_SUFFIX
        );

        $stub = $this->fileSystem->getFileContent(
            $this->fileSystem->getStubPath() . '../stubs/delete_seed.stub'
        );

        $seedFolderPath = $this->fileSystem->getSeedFolderPath();

        $seederFile = $this->fileSystem->getSeederFile($seederClassName, $seedFolderPath);

        $stub = $this->contentManager->replaceString('{{class}}', $seederClassName, $stub);

        $seedContent = $this->contentManager->populateContentToStubFile(
            $stub,
            $dataType,
            self::DELETED_SEEDER_SUFFIX
        );

        $this->fileSystem->addContentToSeederFile($seederFile, $seedContent);

        return $this->updateOrchestraSeeder($seederClassName);
    }

    /**
     * Replace with $dataType Variable.
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

    /**
     * @param string $tableName
     * @param string $suffix
     * @return bool
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function generateVDOSeedFile(string $tableName, string $suffix) : bool
    {
        if (! Schema::hasTable($tableName)) {
            throw new Exception(sprintf('%s table does\'nt exist.'));
        }

        $data = $this->repackSeedData($this->databaseManager->table($tableName)->get());

        $seederClassName = $this->fileSystem->generateSeederClassName($tableName, $suffix);

        $stub = $this->fileSystem->getFileContent(
            $this->fileSystem->getStubPath() . '../stubs/seed.stub'
        );

        $seedFolderPath = $this->fileSystem->getSeedFolderPath();

        $seederFile = $this->fileSystem->getSeederFile($seederClassName, $seedFolderPath);

        $stub = $this->contentManager->replaceString('{{class}}', $seederClassName, $stub);
        $stub = $this->contentManager->replaceString('{{table}}', $tableName, $stub);

        $seedContent = $this->contentManager->populateTableContentToSeeder($stub, $tableName, $data);

        return $this->fileSystem->addContentToSeederFile($seederFile, $seedContent);
    }

    /**
     * Repacks data read from the database.
     *
     * @param  array|object $data
     *
     * @return array
     */
    public function repackSeedData($data) : array
    {
        if (! is_array($data)) {
            $data = $data->toArray();
        }

        $dataArray = [];
        if (! empty($data)) {
            foreach ($data as $row) {
                $rowArray = [];
                foreach ($row as $columnName => $columnValue) {
                    $rowArray[$columnName] = $columnValue;
                }
                $dataArray[] = $rowArray;
            }
        }

        return $dataArray;
    }
}
