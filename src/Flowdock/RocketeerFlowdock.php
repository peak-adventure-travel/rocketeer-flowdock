<?php

namespace Rocketeer\Plugins\Flowdock;

use Illuminate\Container\Container;
use Rocketeer\Abstracts\AbstractPlugin;
use Rocketeer\Services\TasksHandler;

class RocketeerFlowdock extends AbstractPlugin
{
    /** @var string */
    protected $external_thread_id = NULL;

    /**
     * RocketeerFlowdock constructor
     *
     * @param Container $app Application Dependency Injection Container
     */
    public function __construct(Container $app)
    {
        parent::__construct($app);

        $this->configurationFolder = __DIR__ . '/../config';
        $this->external_thread_id = date('YmdHis');
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
        $app->bind('RocketeerFlowdock', function($app) {
            return new RocketeerFlowdock($app);
        });
        return $app;
    }

    /**
     * Register Tasks with Rocketeer
     *
     * @param TasksHandler $queue
     */
    public function onQueue(TasksHandler $queue)
    {
        $queue->before('deploy', function ($task) {
            foreach ($task->config->get('rocketeer-flowdock::source_tokens') as $source_token) {
                $message = new RocketeerFlowdockMessage($source_token, $this->external_thread_id);
                $message->notify($task, $task->config->get('rocketeer-flowdock::deploy_before'));
            }
        });

        $queue->after('deploy', function ($task) {
            foreach ($task->config->get('rocketeer-flowdock::source_tokens') as $source_token) {
                $message = new RocketeerFlowdockMessage($source_token, $this->external_thread_id);
                $message->notify($task, $task->config->get('rocketeer-flowdock::deploy_after'));
            }
        });

        $queue->before('deploy.halt', function ($task) {
            foreach ($task->config->get('rocketeer-flowdock::source_tokens') as $source_token) {
                $message = new RocketeerFlowdockMessage($source_token, $this->external_thread_id);
                $message->notify($task, $task->config->get('rocketeer-flowdock::rollback_before'));
            }
        });

        $queue->after('deploy.halt', function ($task) {
            foreach ($task->config->get('rocketeer-flowdock::source_tokens') as $source_token) {
                $message = new RocketeerFlowdockMessage($source_token, $this->external_thread_id);
                $message->notify($task, $task->config->get('rocketeer-flowdock::rollback_after'));
            }
        });

    }

}
