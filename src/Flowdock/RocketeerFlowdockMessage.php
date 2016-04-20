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
     * @param Client $client HTTP Client
     */
    public function __construct($flow_token, $client = NULL)
    {
        if($client == NULL) {
            $client = new Client();
        }

        $this->flow_token = $flow_token;
        $this->client = $client;
        $this->external_thread_id = self::EXTERNAL_THREAD_ID_PREFIX . date('YmdHis');
    }

    /**
     * Notifies the Flowdock channel of a deployment stage being initiated
     *
     * @param string $user Name of the user announcing the notification
     * @return bool
     * @throws \Exception
     */
    public function queueNotify($thread_title = NULL, $thread_body = NULL, $user = NULL)
    {
        if($user == NULL) {
            $user = 'Rocketeer';
        }

        if($thread_title == NULL) {
            $thread_title = "Rocketeer Deployment";
        }

        if($thread_body == NULL) {
            $thread_body = "";
        }

        $body = json_encode(
            array(
                'flow_token' => $this->flow_token,
                'event' => 'activity',
                'author' => array(
                    'name' => $user,
                ),
                'title' => $thread_title,
                'external_thread_id' => $this->external_thread_id,
                'thread' => array(
                    'title' => $thread_title,
                    'body' => $thread_body,
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
}