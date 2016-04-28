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
    private $flowToken = NULL;

    /** @var Client */
    private $client = NULL;

    /** @var string */
    private $externalThreadID = NULL;

    /**
     * RocketeerFlowdockMessage constructor
     *
     * @param string $flowToken Your source_token provided after adding the RocketeerFlowdock integration
     * @param string @external_thread_id The ID to pass to flowdock to allow for clustered notifications
     * @param ClientInterface $client HTTP Client Interface
     */
    public function __construct($flowToken, $externalThreadID, ClientInterface $client = NULL)
    {
        if ($client == NULL) {
            $client = new Client();
        }

        $this->flowToken = $flowToken;
        $this->client = $client;
        $this->externalThreadID = $externalThreadID;
    }

    /**
     * Notifies the Flowdock channel of a deployment stage being initiated
     *
     * @param Closure @task
     * @param string $threadBody
     * @return bool
     * @throws \Exception
     */
    public function notify($task, $threadBody = NULL)
    {
        if ($threadBody == NULL) {
            $threadBody = "There is currently no message configured";
        }

        $body = json_encode([
            'flow_token' => $this->flowToken,
            'event' => 'activity',
            'author' => [
                'name' => get_current_user(),
            ],
            'title' => $this->formatThreadBody($task, $threadBody),
            'external_thread_id' => $this->externalThreadID,
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
     * @param string $threadBody
     * @return string
     */
    public function formatThreadBody($task, $threadBody)
    {
        $branch = NULL;
        if ($task->rocketeer->getOption('branch') == '') {
            $branch = $task->config->get('rocketeer-flowdock::branch');
        } else {
            $branch = $task->rocketeer->getOption('branch');
        }


        $application = NULL;
        if ($task->config->get('rocketeer-flowdock::application') != '') {
            $application = $task->config->get('rocketeer-flowdock::application');
        } else {
            $application = $task->rocketeer->getApplicationName();
        }

        $pattern = ['(:user)', '(:branch)', '(:repo)', '(:conn)'];
        $replacements = [
            ':user' => $task->config->get('rocketeer-flowdock::user'),
            ':branch' => $branch,
            ':repo' => $application,
            ':conn' => $task->connections->getConnection()
        ];

        $threadBody = preg_replace($pattern, $replacements, $threadBody);

        return $threadBody;
    }
}