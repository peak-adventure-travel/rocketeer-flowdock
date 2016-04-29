<?php

namespace Rocketeer\Plugins\Flowdock;

use Illuminate\Container\Container;
use Rocketeer\Abstracts\AbstractPlugin;
use Rocketeer\Services\TasksHandler;

class RocketeerFlowdock extends AbstractPlugin
{
    /** @var string */
    protected $externalThreadID = NULL;

    /**
     * RocketeerFlowdock constructor
     *
     * @param Container $app Application Dependency Injection Container
     */
    public function __construct(Container $app)
    {
        parent::__construct($app);

        $this->configurationFolder = __DIR__ . '/../config';
        $this->externalThreadID = date('YmdHis');
    }

    /**
     * Bind additional classes to the Container
     *
     * @param Container $app
     *
     * @return Container $app
     */
    public function register(Container $app)
    {
        $app->bind('flowdock');
        return $app;
    }

    /**
     * Register Tasks with Rocketeer
     *
     * @param TasksHandler $queue
     */
    public function onQueue(TasksHandler $queue)
    {
        $queue->listenTo($queue->config->get('rocketeer-flowdock::stage_before'), function ($task) {
            foreach ($task->config->get('rocketeer-flowdock::source_tokens') as $sourceToken) {
                $message = new RocketeerFlowdockMessage($sourceToken, $this->externalThreadID);
                $message->notify(
                    $task->rocketeer,
                    $task->config,
                    $task->connections,
                    $task->config->get('rocketeer-flowdock::message_before')
                );
            }
        });

        $queue->listenTo($queue->config->get('rocketeer-flowdock::stage_after'), function ($task) {
            foreach ($task->config->get('rocketeer-flowdock::source_tokens') as $sourceToken) {
                $message = new RocketeerFlowdockMessage($sourceToken, $this->externalThreadID);
                $message->notify(
                    $task->rocketeer,
                    $task->config,
                    $task->connections,
                    $task->config->get('rocketeer-flowdock::message_after')
                );
            }
        });

        $queue->listenTo($queue->config->get('rocketeer-flowdock::stage_rollback'), function ($task) {
            foreach ($task->config->get('rocketeer-flowdock::source_tokens') as $sourceToken) {
                $message = new RocketeerFlowdockMessage($sourceToken, $this->externalThreadID);
                $message->notify(
                    $task->rocketeer,
                    $task->config,
                    $task->connections,
                    $task->config->get('rocketeer-flowdock::message_rollback')
                );
            }
        });
    }

}
