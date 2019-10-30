<?php

namespace DrudgeRajen\VoyagerDeploymentOrchestrator;

use Exception;
use TCG\Voyager\Events\BreadChanged;
use Illuminate\Foundation\Application;
use DrudgeRajen\VoyagerDeploymentOrchestrator\OrchestratorHandlers\BreadAddedHandler;
use DrudgeRajen\VoyagerDeploymentOrchestrator\OrchestratorHandlers\BreadDeletedHandler;
use DrudgeRajen\VoyagerDeploymentOrchestrator\OrchestratorHandlers\BreadUpdatedHandler;
use DrudgeRajen\VoyagerDeploymentOrchestrator\Exceptions\OrchestratorHandlerNotFoundException;

class VoyagerDeploymentOrchestrator
{
    /** @var string */
    const BREAD_ADDED = 'Added';

    /** @var string */
    const BREAD_UPDATED = 'Updated';

    /** @var string */
    const BREAD_DELETED = 'Deleted';

    /** @var array */
    const HANDLERS = [
        self::BREAD_ADDED   => BreadAddedHandler::class,
        self::BREAD_UPDATED => BreadUpdatedHandler::class,
        self::BREAD_DELETED => BreadDeletedHandler::class,
    ];

    /** @var Application */
    private $app;

    /**
     * VoyagerDeploymentOrchestrator constructor.
     *
     * @param Composer $composer
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->app      = $application;
    }

    /**
     * BreadChanged Handlers.
     *
     * @param BreadChanged $breadChanged
     *
     * @throws DeploymentHandlerNotFoundException
     */
    public function handle(BreadChanged $breadChanged)
    {
        if (! in_array(
            $breadChanged->dataType->name,
            config('voyager-deployment-orchestrator.tables')
        )
        ) {
            return;
        }

        try {
            $handler = $this->getHandle($breadChanged->changeType);

            if ($handler) {
                $handler->handle($breadChanged);
            }
        } catch (Exception $e) {
            throw new OrchestratorHandlerNotFoundException($e->getMessage());
        }
    }

    /**
     * @param string $changeType
     *
     * @return mixed
     */
    private function getHandle(string $changeType)
    {
        if (isset(self::HANDLERS[$changeType])) {
            return $this->app->make(self::HANDLERS[$changeType]);
        }
    }
}
