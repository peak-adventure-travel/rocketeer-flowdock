<?php

namespace Rocketeer\Plugins\Flowdock;

use Illuminate\Container\Container;
use Rocketeer\Abstracts\AbstractPlugin;
use Rocketeer\Services\TasksHandler;

class RocketeerFlowdock extends AbstractPlugin
{

    /**
     * RocketeerFlowdock constructor.
     *
     * @param \Illuminate\Container\Container $app Application Dependency Injection Container
     */
    public function __construct(Container $app)
    {
        parent::__construct($app);

        $this->configurationFolder = __DIR__ . '/../config';
    }

    /**
     * Register Tasks with Rocketeer.
     *
     * @param \Rocketeer\Services\TasksHandler $queue
     */
    public function onQueue(TasksHandler $queue)
    {
        $queue->before('deploy', function () {
            foreach ($this->config->get('rocketeer-flowdock:source_tokens') as $source_token) {
                $message = new RocketeerFlowdockMessage($source_token);
                $message->queueNotify();
            }
        });

        $queue->after('deploy', function () {
            foreach ($this->config->get('rocketeer-flowdock:source_tokens') as $source_token) {
                $message = new RocketeerFlowdockMessage($source_token);
                $message->queueNotify();
            }
        });

        $queue->after('deploy.halt', function () {
            foreach ($this->config->get('rocketeer-flowdock:source_tokens') as $source_token) {
                $message = new RocketeerFlowdockMessage($source_token);
                $message->queueNotify();
            }
        });
    }

}
