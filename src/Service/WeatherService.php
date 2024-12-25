<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherService
{
    private HttpClientInterface $client;
    private string $weatherApiKey;

    public function __construct(HttpClientInterface $client, string $weatherApiKey)
    {
        $this->client = $client;
        $this->weatherApiKey = $weatherApiKey;
    }

    /**
     * Get the temperature for a specific city and date.
     * 
     * @param string $city The city to get the weather for.
     * @param string|null $date The date to get the weather for, can be 'today' or 'tomorrow'.
     * @return float|null The average temperature in Celsius or null if there was an error.
     */
    public function getTemperature(string $city, ?string $date): ?float
    {
        // Attempt to fetch the weather data
        try {
            // Prepare the API request with query parameters
            $response = $this->client->request(
                'GET',
                'http://api.weatherapi.com/v1/forecast.json', [
                    'query' => [
                        'key' => $this->weatherApiKey,
                        'q' => $city,
                        'days' => ($date === 'today') ? 1 : 2, // Fetch weather for today or tomorrow
                        'lang' => 'fr'
                    ]
                ]
            );

            // Check if the response status is OK (200)
            if ($response->getStatusCode() === 200) {
                // Parse the response into an associative array
                $data = $response->toArray();
                // Return the average temperature based on the date provided
                return $data['forecast']['forecastday'][($date === 'today') ? 0 : 1]['day']['avgtemp_c'];
            }

        } catch (TransportExceptionInterface $e) {
            // Return null in case of transport errors (e.g. network issues)
            return null; 
        } catch (\Exception $e) {
            // Return null in case of any other errors (e.g. API errors)
            return null;
        }
        
        return null;
    }
}
