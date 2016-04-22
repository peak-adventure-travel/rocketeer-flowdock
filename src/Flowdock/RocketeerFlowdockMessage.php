<?php

namespace Rocketeer\Plugins\Flowdock;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
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
     * RocketeerFlowdockMessage constructor
     *
     * @param string $flow_token Your source_token provided after adding the RocketeerFlowdock integration
     * @param string @external_thread_id The ID to pass to flowdock to allow for clustered notifications
     * @param ClientInterface $client HTTP Client Interface
     */
    public function __construct($flow_token, $external_thread_id, ClientInterface $client = NULL)
    {
        if ($client == NULL) {
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
        if ($thread_body == NULL) {
            $thread_body = "There is currently no message configured";
        }

        $body = json_encode([
            'flow_token' => $this->flow_token,
            'event' => 'activity',
            'author' => [
                'name' => $task->config->get('rocketeer-flowdock::user')
            ],
            'title' => $this->formatThreadBody($task, $thread_body),
            'external_thread_id' => $this->external_thread_id,
            'thread' => [
                'title' => $task->config->get('rocketeer-flowdock::thread_title'),
                'body' => ''
            ]
        ]);

        $headers = ['Content-Type' => 'application/json'];

        $request = new Request('POST', self::MESSAGE_API, $headers, $body);
        $response = $this->client->send($request);

        if ($response->getStatusCode() != 202) {
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
    public function formatThreadBody($task, $thread_body)
    {
        $branch = NULL;
        if ($task->rocketeer->getOption('branch') != '') {
            $branch = $task->rocketeer->getOption('branch');
        } else {
            $branch = $task->config->get('rocketeer-flowdock::default_branch');
        }

        $application = NULL;
        if ($task->config->get('rocketeer-flowdock::application') != '') {
            $application = $task->config->get('rocketeer-flowdock::application');
        } else {
            $application = $task->rocketeer->getApplicationName();
        }

        $pattern = [':user', ':branch', ':repo', ':conn'];
        $replacements = [
            ':user' => $task->config->get('rocketeer-flowdock::user'),
            ':branch' => $branch,
            ':repo' => $application,
            ':conn' => $task->connections->getConnection()
        ];

        $thread_body = preg_replace($pattern, $replacements, $thread_body);

        return $thread_body;
    }
}