<?php

namespace DrudgeRajen\VoyagerDeploymentOrchestrator\ContentManager;

use Illuminate\Support\Composer;
use Illuminate\Filesystem\Filesystem as LaravelFileSystem;

class FileSystem
{
    /** @var LaravelFileSystem */
    private $filesystem;

    /** @var Composer */
    private $composer;

    /**
     * Create the event listener.
     *
     * @param LaravelFileSystem $filesystem
     */
    public function __construct(LaravelFileSystem $filesystem, Composer $composer)
    {
        $this->filesystem = $filesystem;
        $this->composer   = $composer;
    }

    /**
     * Get seeder file.
     *
     * @param string $name
     * @param string $path
     *
     * @return string
     */
    public function getSeederFile(string $name, string $path) : string
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
     * @param string $fileName
     *
     * @return bool
     */
    public function deleteSeedFiles(string $fileName) : bool
    {
        $seederFile = $this->getSeederFile($fileName, $this->getSeedFolderPath());

        if ($this->filesystem->exists($seederFile)) {
            return $this->filesystem->delete($seederFile);
        }

        return false;
    }

    /**
     * Generate Seeder Class Name.
     *
     * @param string $modelSlug
     * @param string $suffix
     *
     * @return string
     */
    public function generateSeederClassName(string $modelSlug, string $suffix) : string
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
     * @param string $seederFile
     * @param string $seederContents
     *
     * @return bool
     */
    public function addContentToSeederFile(string $seederFile, string $seederContents) : bool
    {
        if (! $this->filesystem->put($seederFile, $seederContents)) {
            return false;
        }

        $this->composer->dumpAutoloads();

        return true;
    }

    /**
     * Get File Content.
     *
     * @param $file
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getFileContent($file) :string
    {
        return $this->filesystem->get($file);
    }
}
