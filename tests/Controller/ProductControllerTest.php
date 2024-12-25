<?php

namespace App\Tests;

use App\Service\WeatherService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductControllerTest extends WebTestCase
{
    private const URL = '/api/products';
    private $client;
    private $weatherService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the client and mock WeatherService
        $this->client = static::createClient();
        $this->weatherService = $this->createMock(WeatherService::class);

        // Inject the mock service
        $this->client->getContainer()->set(WeatherService::class, $this->weatherService);
    }

    public function testGetProductsValidRequest(): void
    {
        // Simulate valid request data
        $data = [
            'weather' => ['city' => 'Marseille'],
            'date' => 'tomorrow',
        ];

        // Mock WeatherService response
        $this->weatherService->method('getTemperature')->willReturn(25.0);

        $this->client->request('POST', self::URL, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        $this->assertResponseIsSuccessful();
        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Marseille', $responseData['weather']['city']);
        $this->assertEquals('tomorrow', $responseData['weather']['date']);
        $this->assertNotEmpty($responseData['products']);
    }

    public function testGetProductsInvalidRequest(): void
    {
        // Invalid request: Missing 'city'
        $invalidData = ['weather' => []];

        $this->client->request('POST', self::URL, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($invalidData));

        $this->assertResponseStatusCodeSame(400);
    }

    public function testGetProductsWithoutDate(): void
    {
        // Valid request without 'date' (defaults to 'today')
        $data = [
            'weather' => ['city' => 'Marseille'],
        ];

        $this->weatherService->method('getTemperature')->willReturn(25.0);

        $this->client->request('POST', self::URL, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        $this->assertResponseIsSuccessful();
        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('today', $responseData['weather']['date']);
    }

    public function testGetProductsMissingCity(): void
    {
        // Invalid request: Empty 'city' field
        $data = ['weather' => ['city' => ''], 'date' => 'today'];

        $this->client->request('POST', self::URL, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        $this->assertResponseStatusCodeSame(400);
        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('city field is missing.', $responseData['message']);
    }

    public function testGetProductsTemperatureError(): void
    {
        // Simulate an error when retrieving the temperature
        $this->weatherService->method('getTemperature')->willReturn(null);

        $data = ['weather' => ['city' => 'Marseille'], 'date' => 'today'];

        $this->client->request('POST', self::URL, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        $this->assertResponseStatusCodeSame(500);
        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Failed to retrieve temperature data.', $responseData['message']);
    }
}
