<?php

declare(strict_types=1);

namespace Ryudith\MezzioCustomLog\Test;

use PHPUnit\Framework\TestCase;

class CustomLogTest extends TestCase
{
    private ?\GuzzleHttp\Client $http;
    private string $logPath;

    public function setUp () : void 
    {
        $this->logPath = '../mezzio_modular/data/log/';
        $this->http = new \GuzzleHttp\Client();
    }

    public function tearDown () : void 
    {
        $this->http = null;
    }

    public function testLogFileGenerated ()
    {
        $response = $this->http->request('GET', 'http://localhost:8080/about');
        $logFileName = $this->logPath.'log_'.date('Ymd');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertFileExists($logFileName);

    }
}