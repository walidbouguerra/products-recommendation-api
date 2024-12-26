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

    public function testGetTemperatureForToday(): void
    {
        // Mock a successful response with valid data for "today"
        $weatherService = $this->createMockedWeatherService(200, [
            'forecast' => [
                'forecastday' => [
                    ['day' => ['avgtemp_c' => 18.5]]
                ]
            ]
        ]);

        // Call the method and assert the expected result
        $this->assertEquals(18.5, $weatherService->getTemperature(self::CITY, 'today'));
    }

    public function testGetTemperatureForTomorrow(): void
    {
        // Mock a successful response with valid data for "tomorrow"
        $weatherService = $this->createMockedWeatherService(200, [
            'forecast' => [
                'forecastday' => [
                    ['day' => ['avgtemp_c' => 18.5]],  // today's forecast
                    ['day' => ['avgtemp_c' => 20.0]]   // tomorrow's forecast
                ]
            ]
        ]);

        // Call the method and assert the expected result
        $this->assertEquals(20.0, $weatherService->getTemperature(self::CITY, 'tomorrow'));
    }

    public function testGetTemperatureWithNumericDate(): void
    {
        // Mock a successful response with valid data for a numeric date (e.g., day 5)
        $weatherService = $this->createMockedWeatherService(200, [
            'forecast' => [
                'forecastday' => [
                    ['day' => ['avgtemp_c' => 18.5]],  // today
                    ['day' => ['avgtemp_c' => 19.0]],  // tomorrow
                    ['day' => ['avgtemp_c' => 20.0]],  // day 3
                    ['day' => ['avgtemp_c' => 21.0]],  // day 4
                    ['day' => ['avgtemp_c' => 22.0]],  // day 5 (this is the one we want)
                ]
            ]
        ]);

        // Call the method for day 5 and assert the expected result
        $this->assertEquals(22.0, $weatherService->getTemperature(self::CITY, 5));
    }

    public function testGetTemperatureWithInvalidResponse(): void
    {
        // Mock a failed response (status code 500)
        $weatherService = $this->createMockedWeatherService(500, []);

        // Call the method and assert that the result is null
        $this->assertNull($weatherService->getTemperature(self::CITY, 'today'));
    }

    public function testGetTemperatureWithInvalidDate(): void
    {
        // Test an invalid date (out of range)
        $weatherService = $this->createMockedWeatherService(200, []);

        // Call the method with an invalid date and assert that the result is null
        $this->assertNull($weatherService->getTemperature(self::CITY, 15));
    }

    public function testGetTemperatureWithNetworkError(): void
    {
        // Create a mock HTTP client that throws an exception on request
        $mockClient = $this->createMock(HttpClientInterface::class);
        $mockClient->method('request')->will($this->throwException(new \Exception('Network error')));

        // Create the service with the mock client
        $weatherService = new WeatherService($mockClient, self::API_KEY);

        // Call the method and assert that the result is null due to the network error
        $this->assertNull($weatherService->getTemperature(self::CITY, 'today'));
    }

    public function testGetTemperatureWithMalformedResponse(): void
    {
        // Mock a response with malformed data (missing 'forecastday')
        $weatherService = $this->createMockedWeatherService(200, [
            'forecast' => []  // Malformed response
        ]);

        // Call the method and assert that the result is null due to malformed data
        $this->assertNull($weatherService->getTemperature(self::CITY, 'today'));
    }
}
