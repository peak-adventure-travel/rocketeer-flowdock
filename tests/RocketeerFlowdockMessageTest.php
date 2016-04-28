<?php

namespace Rocketeer\Plugins\Flowdock;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class RocketeerFlowdockMessageTest extends \PHPUnit_Framework_TestCase
{

    /** @var Client */
    protected $client;

    protected function setUp()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.flowdock.com',
        ]);
    }

    public function testSourceTokens()
    {
        $request = new Request('GET', '/sources?flow_token=' . getenv('SOURCE_TOKEN'));
        $response = $this->client->send($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testOnQueue()
    {
        $message = new RocketeerFlowdockMessage(getenv('SOURCE_TOKEN'), date('YmdHis'));
        $result = $message->notify(null, "Testing API PUSH via PHPUnit");

        $this->assertTrue($result);
    }

}