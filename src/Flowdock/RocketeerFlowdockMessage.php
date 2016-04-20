<?php

namespace Rocketeer\Plugins\Flowdock;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class RocketeerFlowdockMessage
{

    const MESSAGE_API = 'https://api.flowdock.com/messages';

    /** @var string */
    private $flow_token = NULL;

    /** @var Client */
    private $client = NULL;

    /**
     * @param string $flow_token Your source_token provided after adding the RocketeerFlowdock integration
     * @param Client $client HTTP Client
     */
    public function __construct($flow_token, $client = NULL)
    {
        if($client == NULL) {
            $client = new Client();
        }

        $this->flow_token = $flow_token;
        $this->client = $client;
    }

    /**
     * Notifies the Flowdock channel of a deployment stage being initiated
     *
     * @param string $external_thread_id Defines the thread ID to allow messages to be displayed in the same event
     * @return bool
     * @throws \Exception
     */
    public function queueNotify($external_thread_id)
    {
        $body = json_encode(
            array(
                'flow_token' => $this->flow_token,
                'event' => 'activity',
                'author' => array(
                    'name' => '',
                ),
                'title' => '',
                'external_thread_id' => $external_thread_id,
                'thread' => array(
                    'title' => '',
                    'body' => '',
                ),
            )
        );

        $headers = array('Content-Type', 'application/json');

        $request = new Request('POST', self::MESSAGE_API, $headers, $body);
        $response = $this->client->send($request);

        if($response->getStatusCode() != 202) {
            throw new \Exception("Error: HTTP " . $response->getStatusCode() . " with message " . $response->getReasonPhrase());
        }

        return true;
    }
}