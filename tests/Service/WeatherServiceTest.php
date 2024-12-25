<?php

namespace App\Tests\Service;

use App\Service\WeatherService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class WeatherServiceTest extends TestCase
{
    private const API_KEY = 'your-api-key';
    private const CITY = 'Marseille';
    private const DATE = 'today';

    private function createMockedWeatherService(int $statusCode, array $responseData): WeatherService
    {
        // Create a mock of the HTTP client
        $mockClient = $this->createMock(HttpClientInterface::class);

        // Create a mock of the HTTP response
        $mockResponse = $this->createMock(ResponseInterface::class);

        // Configure the mock response
        $mockResponse->method('getStatusCode')->willReturn($statusCode);
        $mockResponse->method('toArray')->willReturn($responseData);

        // Configure the mock client to return the mocked response
        $mockClient->method('request')->willReturn($mockResponse);

        // Create and return an instance of WeatherService with the mocked client
        return new WeatherService($mockClient, self::API_KEY);
    }

    public function testGetTemperature(): void
    {
        // Mock a successful response with valid data
        $weatherService = $this->createMockedWeatherService(200, [
            'forecast' => [
                'forecastday' => [
                    ['day' => ['avgtemp_c' => 18.5]]
                ]
            ]
        ]);

        // Call the method and assert the expected result
        $this->assertEquals(18.5, $weatherService->getTemperature(self::CITY, self::DATE));
    }

    public function testGetTemperatureWithInvalidResponse(): void
    {
        // Mock a failed response (status code 500)
        $weatherService = $this->createMockedWeatherService(500, []);

        // Call the method and assert the result is null
        $this->assertNull($weatherService->getTemperature(self::CITY, self::DATE));
    }

    public function testGetTemperatureWithNetworkError(): void
    {
        // Create a mock of the HTTP client that throws an exception on request
        $mockClient = $this->createMock(HttpClientInterface::class);
        $mockClient->method('request')->will($this->throwException(new \Exception('Network error')));

        // Create the service with the mock client
        $weatherService = new WeatherService($mockClient, self::API_KEY);

        // Call the method and assert the result is null due to the network error
        $this->assertNull($weatherService->getTemperature(self::CITY, self::DATE));
    }

    public function testGetTemperatureWithMalformedResponse(): void
    {
        // Mock a response with malformed data (missing 'forecastday')
        $weatherService = $this->createMockedWeatherService(200, [
            'forecast' => []  // Malformed response
        ]);

        // Call the method and assert the result is null due to malformed data
        $this->assertNull($weatherService->getTemperature(self::CITY, self::DATE));
    }
}
