<?php

namespace DrudgeRajen\VoyagerDeploymentOrchestrator\ContentManger;

use Illuminate\Filesystem\Filesystem as LaravelFileSystem;

class FileSystem
{
    /** @var LaravelFileSystem */
    private $filesystem;

    /**
     * Create the event listener.
     *
     * @param LaravelFileSystem $filesystem
     */
    public function __construct(LaravelFileSystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Read Stub File.
     *
     * @param $file
     *
     * @return string
     */
    public function readStubFile($file) : string
    {
        $buffer = file($file, FILE_IGNORE_NEW_LINES);

        return implode(PHP_EOL, $buffer);
    }

    public function getSeederFile($name, $path)
    {
        return $path . '/' . $name . '.php';
    }

    /**
     * Get Seed Folder Path.
     *
     * @return string
     */
    public function getSeedFolderPath() : string
    {
        return base_path() . '/database/seeds/breads';
    }

    /**
     * Get Stub Path.
     *
     * @return string
     */
    public function getStubPath() : string
    {
        return __DIR__ . DIRECTORY_SEPARATOR;
    }

    /**
     * Delete Seed File.
     *
     * @param $fileName
     */
    public function deleteSeedFiles($fileName)
    {
        $seederFile = $this->getSeederFile($fileName, $this->getSeedFolderPath());

        if ($this->filesystem->exists($seederFile)) {
            $this->filesystem->delete($seederFile);
        }
    }

    /**
     * Generate Seeder Class Name.
     *
     * @param $modelSlug
     * @param $suffix
     *
     * @return string
     */
    public function generateSeederClassName($modelSlug, $suffix) : string
    {
        $modelString = '';
        $modelName = explode('-', $modelSlug);
        foreach ($modelName as $modelNameExploded) {
            $modelString .= ucfirst($modelNameExploded);
        }

        return ucfirst($modelString) . $suffix;
    }

    /**
     * Add Content to Seeder file.
     *
     * @param $seederFile
     * @param $seederContents
     *
     * @return int
     */
    public function addContentToSeederFile($seederFile, $seederContents) : int
    {
        return $this->filesystem->put($seederFile, $seederContents);
    }

    /**
     * Get File Content.
     *
     * @param $file
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getFileContent($file)
    {
        return $this->filesystem->get($file);
    }
}
