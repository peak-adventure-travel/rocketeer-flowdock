<?php

namespace Rocketeer\Plugins\Flowdock;

use GuzzleHttp\Psr7\Response;
use \Mockery;

class RocketeerFlowdockMessageTest extends \PHPUnit_Framework_TestCase
{

    /** @var \Mockery\Mock */
    protected $mockedHttpClient;

    protected function setUp()
    {
        $this->mockedHttpClient = Mockery::Mock('\GuzzleHttp\ClientInterface');
    }

    public static function invalidStringDataProvider()
    {
        return [
            ['', 'Empty strings are not valid'],
            [null, 'Nulls are not valid']
        ];
    }

    /**
     * @dataProvider invalidStringDataProvider
     * @param mixed $invalidValue The value which should fail
     * @param string $errorMessage A descriptive error message
     *
     * @throws \InvalidArgumentException
     */
    public function testNotifyThrowsInvalidArgumentExceptionWithEmptyBranchName($invalidValue, $errorMessage)
    {
        $message = new RocketeerFlowdockMessage('dummyToken', '20010203040506', $this->mockedHttpClient);
        try {
            $message->notify(
                $invalidValue,
                'dummyApplication',
                'dummyConnection',
                'dummyEventTitle',
                'dummyThreadTitle'
            );
            $this->fail(
                "An expected InvalidArgumentException was not thrown for invalid value " .
                var_export($invalidValue, true) .
                " $errorMessage"
            );
        } catch (\InvalidArgumentException $expected) {
        }
    }

    /**
     * @dataProvider invalidStringDataProvider
     * @param mixed $invalidValue The value which should fail
     * @param string $errorMessage A descriptive error message
     *
     * @throws \InvalidArgumentException
     */
    public function testNotifyThrowsInvalidArgumentExceptionWithEmptyApplicationName($invalidValue, $errorMessage)
    {
        $message = new RocketeerFlowdockMessage('dummyToken', '20010203040506', $this->mockedHttpClient);
        try {
            $message->notify(
                'dummyBranch',
                '',
                'dummyConnection',
                'dummyEventTitle',
                'dummyThreadTitle'
            );
            $this->fail(
                "An expected InvalidArgumentException was not thrown for invalid value " .
                var_export($invalidValue, true) .
                " $errorMessage"
            );
        } catch (\InvalidArgumentException $expected) {
        }
    }

    /**
     * @dataProvider invalidStringDataProvider
     * @param mixed $invalidValue The value which should fail
     * @param string $errorMessage A descriptive error message
     *
     * @throws \InvalidArgumentException
     */
    public function testNotifyThrowsInvalidArgumentExceptionWithEmptyConnectionName($invalidValue, $errorMessage)
    {
        $message = new RocketeerFlowdockMessage('dummyToken', '20010203040506', $this->mockedHttpClient);
        try {
            $message->notify(
                'dummyBranch',
                'dummyApplication',
                '',
                'dummyEventTitle',
                'dummyThreadTitle'
            );
            $this->fail(
                "An expected InvalidArgumentException was not thrown for invalid value " .
                var_export($invalidValue, true) .
                " $errorMessage"
            );
        } catch (\InvalidArgumentException $expected) {
        }
    }

    /**
     * @dataProvider invalidStringDataProvider
     * @param mixed $invalidValue The value which should fail
     * @param string $errorMessage A descriptive error message
     *
     * @throws \InvalidArgumentException
     */
    public function testNotifyThrowsInvalidArgumentExceptionWithEmptyEventTitle($invalidValue, $errorMessage)
    {
        $message = new RocketeerFlowdockMessage('dummyToken', '20010203040506', $this->mockedHttpClient);
        try {
            $message->notify(
                'dummyBranch',
                'dummyApplication',
                'dummyConnection',
                '',
                'dummyThreadTitle'
            );
            $this->fail(
                "An expected InvalidArgumentException was not thrown for invalid value " .
                var_export($invalidValue, true) .
                " $errorMessage"
            );
        } catch (\InvalidArgumentException $expected) {
        }
    }

    /**
     * @dataProvider invalidStringDataProvider
     * @param mixed $invalidValue The value which should fail
     * @param string $errorMessage A descriptive error message
     *
     * @throws \InvalidArgumentException
     */
    public function testNotifyThrowsInvalidArgumentExceptionWithEmptyThreadTitle($invalidValue, $errorMessage)
    {
        $message = new RocketeerFlowdockMessage('dummyToken', '20010203040506', $this->mockedHttpClient);
        try {
            $message->notify(
                'dummyBranch',
                'dummyApplication',
                'dummyConnection',
                'dummyEventTitle',
                ''
            );
            $this->fail(
                "An expected InvalidArgumentException was not thrown for invalid value " .
                var_export($invalidValue, true) .
                " $errorMessage"
            );
        } catch (\InvalidArgumentException $expected) {
        }
    }

    public function testNotifySendsValidDeploymentMessage()
    {
        $expectedHeaders = ['Content-Type' => 'application/json'];

        $expectedBody = json_encode([
            'flow_token' => 'dummyToken',
            'event' => 'activity',
            'author' => [
                'name' => get_current_user(),
            ],
            'title' => 'dummyEventTitle',
            'external_thread_id' => '20010203040506',
            'thread' => [
                'title' => 'dummyThreadTitle',
                'body' => ''
            ]
        ]);

        $this->mockedHttpClient
            ->shouldReceive('post')
            ->once()
            ->withArgs([
                RocketeerFlowdockMessage::MESSAGE_API,
                [
                    'headers'=>$expectedHeaders,
                    'body'=>$expectedBody
                ]
            ])
            ->andReturn(new Response(202));

        $message = new RocketeerFlowdockMessage('dummyToken', '20010203040506', $this->mockedHttpClient);
        $message->notify('dummyBranch', 'dummyApplication', 'dummyConnection', 'dummyEventTitle', 'dummyThreadTitle');
    }

    protected function tearDown()
    {
        Mockery::close();
    }

}