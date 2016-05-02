<?php

namespace Rocketeer\Plugins\Flowdock;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

class RocketeerFlowdockMessage
{

    const MESSAGE_API = 'https://api.flowdock.com/messages';
    const EXTERNAL_THREAD_ID_PREFIX = 'rocketeer:deploy:';

    /** @var string */
    private $flowToken = null;

    /** @var ClientInterface */
    private $client = null;

    /** @var string */
    private $externalThreadID = null;

    /**
     * RocketeerFlowdockMessage constructor
     *
     * @param string $flowToken Your source_token provided after adding the RocketeerFlowdock integration
     * @param string @external_thread_id The ID to pass to flowdock to allow for clustered notifications
     * @param ClientInterface $client HTTP Client Interface
     */
    public function __construct($flowToken, $externalThreadID, ClientInterface $client = null)
    {
        if ($client == null) {
            $client = new Client();
        }

        $this->flowToken = $flowToken;
        $this->client = $client;
        $this->externalThreadID = $externalThreadID;
    }

    /**
     * Notifies the Flowdock channel of a deployment stage being initiated
     *
     * @param string $branchName
     * @param string $applicationName
     * @param string $connectionName
     * @param string $eventTitle
     * @param string $threadTitle
     * @return ResponseInterface
     * @throws FlowdockApiException
     */
    public function notify($branchName, $applicationName, $connectionName, $eventTitle, $threadTitle)
    {
        if (empty($branchName)) {
            throw new \InvalidArgumentException('branchName is an Invalid Argument');
        }
        if (empty($applicationName)) {
            throw new \InvalidArgumentException('applicationName is an Invalid Argument');
        }
        if (empty($connectionName)) {
            throw new \InvalidArgumentException('connectionName is an Invalid Argument');
        }
        if (empty($eventTitle)) {
            throw new \InvalidArgumentException('eventTitle is an Invalid Argument');
        }
        if (empty($threadTitle)) {
            throw new \InvalidArgumentException('threadTitle is an Invalid Argument');
        }

        $title = $this->formatEventTitle($branchName, $applicationName, $connectionName, $eventTitle);

        $body = json_encode([
            'flow_token' => $this->flowToken,
            'event' => 'activity',
            'author' => [
                'name' => get_current_user(),
            ],
            'title' => $title,
            'external_thread_id' => $this->externalThreadID,
            'thread' => [
                'title' => $threadTitle,
                'body' => ''
            ]
        ]);

        $clientOptions = [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => $body
        ];

        $response = $this->client->post(self::MESSAGE_API, $clientOptions);

        if ($response->getStatusCode() != 202) {
            throw new FlowdockApiException(
                "Error: HTTP " . $response->getStatusCode() .
                " with message " . $response->getReasonPhrase()
            );
        }

        return $response;
    }

    /**
     * Formats the events title with variables as stated in the src/config/config.php
     *
     * @param string $branchName
     * @param string $applicationName
     * @param string $connectionName
     * @param string $eventTitle
     * @return string
     */
    private function formatEventTitle($branchName, $applicationName, $connectionName, $eventTitle)
    {
        $pattern = ['(:user)', '(:branch)', '(:repo)', '(:conn)'];
        $replacements = [
            ':user' => get_current_user(),
            ':branch' => $branchName,
            ':repo' => $applicationName,
            ':conn' => $connectionName
        ];

        $eventTitle = preg_replace($pattern, $replacements, $eventTitle);

        return $eventTitle;
    }
}
