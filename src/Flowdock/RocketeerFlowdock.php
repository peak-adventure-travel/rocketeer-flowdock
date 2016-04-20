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
     * RocketeerFlowdock constructor.
     *
     * @param \Illuminate\Container\Container $app Application Dependency Injection Container
     */
    public function __construct(Container $app)
    {
        parent::__construct($app);

        $this->configurationFolder = __DIR__ . '/../config';
        $this->external_thread_id = date('YmdHis');
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
                $message = new RocketeerFlowdockMessage($source_token, $this->external_thread_id);
                $message->queueNotify(
                    $this->config->get('rocketeer-flowdock:title'),
                    $this->config->get('rocketeer-flowdock:thread_title'),
                    self::formatThreadBody($this->config->get('rocketeer-flowdock:deploy_before'))
                );
            }
        });

        $queue->after('deploy', function () {
            foreach ($this->config->get('rocketeer-flowdock:source_tokens') as $source_token) {
                $message = new RocketeerFlowdockMessage($source_token, $this->external_thread_id);
                $message->queueNotify(
                    $this->config->get('rocketeer-flowdock:title'),
                    $this->config->get('rocketeer-flowdock:thread_title'),
                    self::formatThreadBody($this->config->get('rocketeer-flowdock:deploy_after'))
                );
            }
        });

        $queue->before('deploy.halt', function () {
            foreach ($this->config->get('rocketeer-flowdock:source_tokens') as $source_token) {
                $message = new RocketeerFlowdockMessage($source_token, $this->external_thread_id);
                $message->queueNotify(
                    $this->config->get('rocketeer-flowdock:title'),
                    $this->config->get('rocketeer-flowdock:thread_title'),
                    self::formatThreadBody($this->config->get('rocketeer-flowdock:rollback_before'))
                );
            }
        });

        $queue->after('deploy.halt', function () {
            foreach ($this->config->get('rocketeer-flowdock:source_tokens') as $source_token) {
                $message = new RocketeerFlowdockMessage($source_token, $this->external_thread_id);
                $message->queueNotify(
                    $this->config->get('rocketeer-flowdock:title'),
                    $this->config->get('rocketeer-flowdock:thread_title'),
                    self::formatThreadBody($this->config->get('rocketeer-flowdock:rollback_after'))
                );
            }
        });
    }

    /**
     * Formats the thread body with variables as stated in the src/config/config.php
     *
     * @param string $thread_body
     * @return string
     */
    public function formatThreadBody($thread_body) {
        $thread_body = str_replace("{1}", $this->config->get('rocketeer-flowdock:user'), $thread_body);
        $thread_body = str_replace("{2}", $this->getOption('branch'), $thread_body);

        if($this->config->get('rocketeer-flowdock:application') != '') {
            $thread_body = str_replace("{3}", $this->config->get('rocketeer-flowdock:application'), $thread_body);
        } else {
            $thread_body = str_replace("{3}", $this['application_name'], $thread_body);
        }
        $thread_body = str_replace("{4}", $this->getOption('on'), $thread_body);

        return $thread_body;
    }

}
