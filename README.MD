[![StyleCI](https://github.styleci.io/repos/159037396/shield?branch=master)](https://github.styleci.io/repos/159037396)

### Introduction

<a href = "https://github.com/the-control-group/voyager">Voyager </a> is an awesome admin tool for laravel.
It is very easy to create admin tool for CMS using Voyager's awesome feature [BREAD](https://voyager.readme.io/docs/bread).

Voyager has limitation for the deployment (after you finish the work, create bread and need to publish in production. :) )

Voyager Deployment Orchestrator is the package which will listen `BREAD  Events` (Add, Update Delete) and will generate
files, so that BREAD data can be easily seeded.

### Installation

```composer require drudge-rajen/voyager-deployment-orchestrator```

Then publish the default config and seeder file.

```
php artisan vendor:publish --provider="DrudgeRajen\VoyagerDeploymentOrchestrator\VoyagerDeploymentOrchestratorServiceProvider"
```

Next, open the `config/voyager-deployment-orchestrator.php` and add the tables name of which BREAD seeders will be generated.

```
return [
    'tables' => [
        users
    ],
];
```

After Publishing the `VoyagerDeploymentOrchestratorSeeder` class file,
autoload composer so that laravel knows about new seeders.

``composer dump-autoload``

### Usage
Voyager Fires Events on Insert, Update, and Delete of BREAD.
This package listens for those events and creates respective seeder files.

### To run the seeder file:

```php artisan db:seed --class=VoyagerDeploymentOrchestratorSeeder```

### Generating seeder files for tables
VDO also provides an artisan command to generate the seed file for table.
In order to generate the seeder file for tables,

```php artisan vdo:generate table-name1```

It also supports the generation for multiple tables.

``` php artisan vdo:generate table-name1,table-name2,table-name3```

VDO seed generator command will keep the generated seed files inside `/database/seeds/breads` with prefix `TableSeeder`

***Note:*** VDO seed generator will not add the seeder file in `VoyagerDeploymentOrchestratorSeeder.php` class automatically, because
this class in only used for BREAD seeders not the tables seeder. If you want to run all the vdo generated seeder at once,
please add those in `DatabaseSeeder.php` class.

### Contributing

Run phpcs linter to check for any error that may happen during PR.

```composer lint```

Fix errors reported by CI during Pull request.

```composer fix```

### Future Tasks
- [ ] Writing Tests
