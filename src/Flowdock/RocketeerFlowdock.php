<?php

namespace Rocketeer\Plugins\Flowdock;

use Illuminate\Container\Container;
use Rocketeer\Abstracts\AbstractPlugin;
use Rocketeer\Services\TasksHandler;

class RocketeerFlowdock extends AbstractPlugin
{

    const EXTERNAL_THREAD_ID_PREFIX = 'rocketeer:deploy:';

    /** @var string */
    private $external_thread_id = NULL;

    /**
     * RocketeerFlowdock constructor.
     *
     * @param \Illuminate\Container\Container $app Application Dependency Injection Container
     */
    public function __construct(Container $app)
    {
        parent::__construct($app);

        $this->configurationFolder = __DIR__ . '/../config';
        $this->external_thread_id = self::EXTERNAL_THREAD_ID_PREFIX . date('YmdHis');
    }

    /**
     * Register Tasks with Rocketeer.
     *
     * @param \Rocketeer\Services\TasksHandler $queue
     */
    public function onQueue(TasksHandler $queue)
    {


        $queue->before('deploy', function ($task) {
            // TODO: Add deployment has started to Flowdock

            foreach($this->config->get('rocketeerflowdock:source_tokens') as $flow_token) {
                $message = new RocketeerFlowdockMessage($flow_token);
                $message->queueNotify($this->external_thread_id);
            }
        });

        $queue->after('deploy', function ($task) {
            // TODO: Add deployment was successful to Flowdock
        });

        $queue->after('deploy.halt', function ($task) {
            // TODO: Add deployment was unsuccessful to Flowdock
        });
    }
}