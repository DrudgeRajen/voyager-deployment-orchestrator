<?php

namespace DrudgeRajen\VoyagerDeploymentOrchestrator\Commands;

use DrudgeRajen\VoyagerDeploymentOrchestrator\ContentManger\FileSystem;
use Illuminate\Console\Command;

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

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(FileSystem $fileSystem)
    {
        parent::__construct();
        $this->fileSystem = $fileSystem;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $tables = explode(",", $this->argument('tables'));

        foreach($tables as $table) {
            $this->fileSystem->generateSeederClassName($table, $this->suffix);
        }
    }

}
