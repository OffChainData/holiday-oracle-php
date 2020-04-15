<?php

namespace OffChainData\HolidayOracle;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client as GuzzleClient;

class Client
{
    /**
     * User's API Token
     *
     * @var string
     */
    private $token;

    /**
     * API's Base URL
     *
     * @var string
     */
    private $baseUrl = 'https://staging.holidayoracle.io';

    /**
     * Headers sent with every client request
     *
     * @var array
     */
    private $requestHeaders = [];
    
    /**
     * Guzzle HTTP Client
     *
     * @var GuzzleClient
     */
    private $client;

    /**
     * Response status code and message
     * 
     * @var array
     */
    private $responseStatus = [];

    /**
     * Constructor
     *
     * @param string $token
     * @param array  $options
     */
    public function __construct($token, array $options = [])
    {
        $this->token = $token;
        $this->requestHeaders['Authorization'] = "Bearer {$token}";
        $this->requestHeaders['Content-Type'] = "application/json";

        if (isset($options['baseUrl'])) {
            $this->baseUrl = $options['baseUrl'];
        }

        $this->client = new GuzzleClient([
                    'base_uri' => $this->baseUrl,
                    'headers' => $this->requestHeaders
                ]
        );
    }

    /**
     * Returns the last response status code and message
     * 
     * @return array
     */
    public function getLastResponseStatus()
    {
        return $this->responseStatus;
    }

    /**
     * Parses a Guzzle Http Response
     * 
     * @param Response $response
     * @return array
     * @throws Exception
     */
    public function parseResponse(Response $response)
    {
        $this->responseStatus = [
            'code' => $response->getStatusCode(),
            'reason' => $response->getReasonPhrase(),
        ];
        if ($response->getStatusCode() != 200) {
            throw new \Exception('Invalid response "' . $response->getStatusCode() . '"');                        
        }
        $response = json_decode($response->getBody(), true);

        if (!isset($response['status']) || !isset($response['data'])) {
            throw new \Exception('Invalid response');            
        }
        return $response['data'];

    }

    /**
     * Sends a request to the API
     * 
     * @param string $method
     * @param string $endPoint
     * @param array  $params
     * @return array
     * @throws Exception
     */
    public function request($method, $endPoint, array $params = [])
    {
        try {
            $response = $this->client->request($method, $endPoint, $params);
            $data = $this->parseResponse($response);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $this->responseStatus = [
                'code' => $e->getCode(),
                'reason' => $e->getMessage(),
            ];
            throw $e;
        }
        return $data;
    }

    /**
     * Locations end point
     * 
     * See https://holidayoracle.io/docs/index.html#locations
     * 
     * @return array
     */
    public function locations()
    {
        $data = $this->request('POST', 'api/v1/date/locations');
        return $data;
    }
    
    /**
     * Date end point
     * 
     * See https://holidayoracle.io/docs/index.html#date
     * 
     * @param integer|string $dateOrTimestamp
     * @param string         $country
     * @param array          $options
     * @return array
     * @throws Exception
     */
    public function date($dateOrTimestamp, $country, array $options = [])
    {
        $jsonData = [
            'country' => $country
        ];
        $jsonData = array_merge($jsonData, $options);
        if (!ctype_digit((string)$dateOrTimestamp)) {
            if (!preg_match('/(\d){4}-\d{2}-\d{2}/', $dateOrTimestamp)) {
                throw new \Exception('Invalid date format');
            }

            $jsonData['date'] = $dateOrTimestamp;
        } else {
            $jsonData['timestamp'] = $dateOrTimestamp;
        }
        
        $data = $this->request('POST', 'api/v1/date', [
            'json' => $jsonData
        ]);
        return $data;
    }

    /**
     * Holidays end point
     * 
     * See https://holidayoracle.io/docs/index.html#holidays
     * 
     * @param string $country
     * @param string $year
     * @param array  $options
     * @return array
     */
    public function holidays($country, $year, array $options = [])
    {
        $jsonData = [
            'country' => $country,
            'year' => $year
        ];
        $jsonData = array_merge($jsonData, $options);
        $data = $this->request('POST', 'api/v1/date/holidays', [
            'json' => $jsonData
        ]);
        return $data;
    }

    /**
     * Business Days end point
     * 
     * See https://holidayoracle.io/docs/index.html#business-days
     * 
     * @param string $date1
     * @param string $date2
     * @param string $country
     * @param array  $options
     * @return array
     * @throws Exception
     */
    public function businessDays($date1, $date2, $country, array $options = [])
    {
        if (!preg_match('/(\d){4}-\d{2}-\d{2}/', $date1)) {
            throw new \Exception('Invalid date format');
        }
        if (!preg_match('/(\d){4}-\d{2}-\d{2}/', $date2)) {
            throw new \Exception('Invalid date format');
        }

        $jsonData = [
            'country' => $country,
            'date1' => $date1,
            'date2' => $date2,
        ];
        $jsonData = array_merge($jsonData, $options);
        $data = $this->request('POST', 'api/v1/date/business-days', [
            'json' => $jsonData
        ]);
        return $data;
    }
}