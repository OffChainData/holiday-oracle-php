<?php

namespace OffChainData\HolidayOracle\Tests;

use PHPUnit\Framework\TestCase;
use OffChainData\HolidayOracle\Client;
use GuzzleHttp\Psr7\Response;

class ClientTest extends TestCase
{
    /**
     * Test response parsing
     *
     * @covers \OffChainData\HolidayOracle\Client
     * @return void
     */
    public function testParseResponseException()
    {
        $response = new Response('400');
        $client = new Client('');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid response "400"');
        $client->parseResponse($response);
    }

    /**
     * Test response parsing
     *
     * @covers \OffChainData\HolidayOracle\Client
     * @return void
     */
    public function testGetLastResponseStatus()
    {
        $response = new Response('200', [], json_encode(['status' => 'ok', 'data' => []]));
        $client = new Client('');
        $client->parseResponse($response);
        $status = $client->getLastResponseStatus();
        $this->assertEquals(200, $status['code']);
        $this->assertEquals('OK', $status['reason']);
    }

    /**
     * Test date validation
     *
     * @covers \OffChainData\HolidayOracle\Client
     * @return void
     */
    public function testDateValidation()
    {
        $client = new Client('');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid date format');
        $client->date('not a date', 'AU');
    }

    /**
     * Test date validation
     *
     * @covers \OffChainData\HolidayOracle\Client
     * @return void
     */
    public function testBusinessDaysValidation()
    {
        $client = new Client('');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid date format');
        $client->businessDays('not a date', 'not a date', 'AU');
    }
}