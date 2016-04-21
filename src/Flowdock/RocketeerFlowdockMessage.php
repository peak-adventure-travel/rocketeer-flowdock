<?php

namespace Rocketeer\Plugins\Flowdock;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class RocketeerFlowdockMessage
{

    const MESSAGE_API = 'https://api.flowdock.com/messages';
    const EXTERNAL_THREAD_ID_PREFIX = 'rocketeer:deploy:';

    /** @var string */
    private $flow_token = NULL;

    /** @var Client */
    private $client = NULL;

    /** @var string */
    private $external_thread_id = NULL;

    /**
     * @param string $flow_token Your source_token provided after adding the RocketeerFlowdock integration
     * @param string @external_thread_id The ID to pass to flowdock to allow for clustered notifications
     * @param Client $client HTTP Client
     */
    public function __construct($flow_token, $external_thread_id, $client = NULL)
    {
        if($client == NULL) {
            $client = new Client();
        }

        $this->flow_token = $flow_token;
        $this->client = $client;
        $this->external_thread_id = $external_thread_id;
    }

    /**
     * Notifies the Flowdock channel of a deployment stage being initiated
     *
     * @param Closure @task
     * @param string $thread_body
     * @return bool
     * @throws \Exception
     */
    public function notify($task, $thread_body = NULL)
    {
        if($thread_body == NULL) {
            $thread_body = "There is currently no message configured";
        }

        $body = json_encode(
            array(
                'flow_token' => $this->flow_token,
                'event' => 'activity',
                'author' => array(
                    'name' => $task->config->get('rocketeer-flowdock::user'),
                ),
                'title' => $task->config->get('rocketeer-flowdock::title'),
                'external_thread_id' => $this->external_thread_id,
                'thread' => array(
                    'title' => $task->config->get('rocketeer-flowdock::thread_title'),
                    'body' => $this->formatThreadBody($task, $thread_body),
                ),
            ), JSON_PRETTY_PRINT
        );

        $headers = array('Content-Type' => 'application/json');

        $request = new Request('POST', self::MESSAGE_API, $headers, $body);
        $response = $this->client->send($request);

        if($response->getStatusCode() != 202) {
            throw new \Exception("Error: HTTP " . $response->getStatusCode() . " with message " . $response->getReasonPhrase());
        }

        return true;
    }

    /**
     * Formats the thread body with variables as stated in the src/config/config.php
     *
     * @param Closure @task
     * @param string $thread_body
     * @return string
     */
    public function formatThreadBody($task, $thread_body) {
        $thread_body = str_replace("{1}", $task->config->get('rocketeer-flowdock::user'), $thread_body);
        $thread_body = str_replace("{2}", $task->rocketeer->getOption('branch'), $thread_body);

        if($task->config->get('rocketeer-flowdock::application') != '') {
            $thread_body = str_replace("{3}", $task->config->get('rocketeer-flowdock::application'), $thread_body);
        } else {
            $thread_body = str_replace("{3}", $task->rocketeer->getApplicationName(), $thread_body);
        }

        $branches = NULL;
        foreach($task->rocketeer->getOption('on') as $option) {
            $branches = $branches . $option . ", ";
        }

        $thread_body = str_replace("{4}", $branches, $thread_body);

        return $thread_body;
    }
}