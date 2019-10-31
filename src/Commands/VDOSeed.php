<?php

namespace DrudgeRajen\VoyagerDeploymentOrchestrator\Commands;

use Exception;
use Illuminate\Console\Command;
use DrudgeRajen\VoyagerDeploymentOrchestrator\ContentManager\FileGenerator;

class VDOSeed extends Command
{
    protected $suffix = 'TableSeeder';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vdo:generate {tables}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Seed files for voyager tables, except for BREAD.';

    /** @var FileGenerator */
    private $fileGenerator;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(FileGenerator $fileGenerator)
    {
        parent::__construct();

        $this->fileGenerator = $fileGenerator;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $tables = explode(',', $this->argument('tables'));

        try {
            foreach ($tables as $table) {
                $this->printResult(
                    $table,
                    $this->fileGenerator->generateVDOSeedFile($table, $this->suffix)
                );
            }
        } catch (Exception $exception) {
            $this->printResult($table, false);
        }
    }

    /**
     * Print Result.
     *
     * @param string $table
     * @param bool $isSuccess
     */
    public function printResult(string $table, bool $isSuccess=true)
    {
        if ($isSuccess) {
            $this->info("Created a seed file from table {$table}");

            return;
        }

        $this->error("Could not create seed file from table {$table}");
    }
}
