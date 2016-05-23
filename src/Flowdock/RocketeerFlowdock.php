<?php

namespace Rocketeer\Plugins\Flowdock;

use Illuminate\Container\Container;
use Rocketeer\Abstracts\AbstractPlugin;
use Rocketeer\Services\TasksHandler;

class RocketeerFlowdock extends AbstractPlugin
{
    /** @var string */
    protected $externalThreadID = null;

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
        $queue->listenTo($queue->config->get('rocketeer-flowdock::stage_before'), function () {
            foreach ($this->config->get('rocketeer-flowdock::source_tokens') as $sourceToken) {
                $message = new RocketeerFlowdockMessage($sourceToken, $this->externalThreadID);
                $message->notify(
                    $this->rocketeer->getOption('branch'),
                    $this->rocketeer->getApplicationName(),
                    $this->connections->getConnection(),
                    $this->config->get('rocketeer-flowdock::message_before'),
                    $this->config->get('rocketeer-flowdock::thread_title')
                );
            }
        });

        $queue->listenTo($queue->config->get('rocketeer-flowdock::stage_after'), function () {
            foreach ($this->config->get('rocketeer-flowdock::source_tokens') as $sourceToken) {
                $message = new RocketeerFlowdockMessage($sourceToken, $this->externalThreadID);
                $message->notify(
                    $this->rocketeer->getOption('branch'),
                    $this->rocketeer->getApplicationName(),
                    $this->connections->getConnection(),
                    $this->config->get('rocketeer-flowdock::message_after'),
                    $this->config->get('rocketeer-flowdock::thread_title')
                );
            }
        });

        $queue->listenTo($queue->config->get('rocketeer-flowdock::stage_rollback'), function () {
            foreach ($this->config->get('rocketeer-flowdock::source_tokens') as $sourceToken) {
                $message = new RocketeerFlowdockMessage($sourceToken, $this->externalThreadID);
                $message->notify(
                    $this->rocketeer->getOption('branch'),
                    $this->rocketeer->getApplicationName(),
                    $this->connections->getConnection(),
                    $this->config->get('rocketeer-flowdock::message_rollback'),
                    $this->config->get('rocketeer-flowdock::thread_title')
                );
            }
        });
    }
}
