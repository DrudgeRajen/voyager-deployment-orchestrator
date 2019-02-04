[![StyleCI](https://github.styleci.io/repos/159037396/shield?branch=master)](https://github.styleci.io/repos/159037396)
### Introduction
<a href = "https://github.com/the-control-group/voyager">Voyager </a> is an awesome admin tool for laravel.
It's easy to create admin tool for CMS using it awesome features ```BREAD```.

Voyager has limitation for the deployment (after you finish the work, create bread and need to publish in production. :) )

Voyager Deployment Orchestrator is the package which will listen ```BREAD  Events``` (Add, Update Delete) and will generate
files, so that BREAD data can be easily seeded.

### Installation

```composer require drudge-rajen/voyager-deployment-orchestrator```

Publish the default config and seeder file.

``php artisan vendor:publish --provider=DrudgeRajen\VoyagerDeploymentOrchestrator\VoyagerDeploymentOrchestratorServiceProvider``

Next, open the `config/voyager-deployment-orchestrator.php` and add the tables name of which BREAD seeders will be generated.

```
return [
    'tables' => [
        users
    ],
];
```


After Publishing the ``VoyagerDeploymentOrchestratorSeeder`` class file
autoload the composer so that laravel knows about the seeder.

``composer dump-autoload``

### Usage
Voyager Fires Events on Insert, Update, and Delete of BREAD.
This package listens to those events and creates the respective seeder files.

To run the seeder file :

``php artisan db:seed --class=VoyagerDeploymentOrchestratorSeeder``


### Future Tasks
- [x] Generating Seed Files On BREAD Events.

- [ ] Writing Tests
