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

    /** @var ClientInterface */
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
     * @param \Rocketeer\Rocketeer $rocketeer
     * @param \Illuminate\Config\Repository $config
     * @param \Rocketeer\Services\Connections\ConnectionsHandler $connections
     * @param String|null $eventTitle
     *
     * @return bool
     *
     * @throws \InvalidArgumentException
     * @throws FlowdockApiException
     */
    public function notify($rocketeer, $config, $connections, $eventTitle)
    {
        if ($eventTitle == NULL) {
            throw new \InvalidArgumentException("Event Title is NULL and needs to have a content body to alert Flowdock");
        }

        $title = $this->formatEventTitle($rocketeer, $config, $connections, $eventTitle);

        $body = json_encode([
            'flow_token' => $this->flowToken,
            'event' => 'activity',
            'author' => [
                'name' => get_current_user(),
            ],
            'title' => $title,
            'external_thread_id' => $this->externalThreadID,
            'thread' => [
                'title' => $config->get('rocketeer-flowdock::thread_title'),
                'body' => ''
            ]
        ]);

        $headers = ['Content-Type' => 'application/json'];

        $request = new Request('POST', self::MESSAGE_API, $headers, $body);
        $response = $this->client->send($request);

        if ($response->getStatusCode() != 202) {
            throw new FlowdockApiException(
                "Error: HTTP " . $response->getStatusCode() .
                " with message " . $response->getReasonPhrase()
            );
        }

        return true;
    }

    /**
     * Formats the events title with variables as stated in the src/config/config.php
     *
     * @param \Rocketeer\Rocketeer $rocketeer
     * @param \Illuminate\Config\Repository $config
     * @param \Rocketeer\Services\Connections\ConnectionsHandler $connections
     * @param string $eventTitle
     *
     * @return string
     */
    private function formatEventTitle($rocketeer, $config, $connections, $eventTitle)
    {
        $branch = NULL;
        if ($rocketeer->getOption('branch') == '') {
            $branch = $config->get('rocketeer-flowdock::branch');
        } else {
            $branch = $rocketeer->getOption('branch');
        }

        $application = NULL;
        if ($config->get('rocketeer-flowdock::application') != '') {
            $application = $config->get('rocketeer-flowdock::application');
        } else {
            $application = $rocketeer->getApplicationName();
        }

        $pattern = ['(:user)', '(:branch)', '(:repo)', '(:conn)'];
        $replacements = [
            ':user' => get_current_user(),
            ':branch' => $branch,
            ':repo' => $application,
            ':conn' => $connections->getConnection()
        ];

        $eventTitle = preg_replace($pattern, $replacements, $eventTitle);

        return $eventTitle;
    }
}