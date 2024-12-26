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
     * @param string|int $date The date to get the weather for, can be 'today', 'tomorrow' or a number between 1 and 14.
     * @return float|null The average temperature in Celsius or null if there was an error.
     */
    public function getTemperature(string $city, $date): ?float
    {
        // Attempt to fetch the weather data
        try {
            // Determine the correct `days` parameter based on the input
            $days = match ($date) {
                'today' => 1,
                'tomorrow' => 2,
                default => (is_numeric($date) && $date >= 1 && $date <= 14) ? $date : null
            };

            // If days is invalid, return null
            if ($days === null) {
                return null;
            }

            // Prepare the API request with query parameters
            $response = $this->client->request(
                'GET',
                'http://api.weatherapi.com/v1/forecast.json', [
                    'query' => [
                        'key' => $this->weatherApiKey,
                        'q' => $city,
                        'days' => $days,
                        'lang' => 'fr'
                    ]
                ]
            );

            // Check if the response status is OK (200)
            if ($response->getStatusCode() === 200) {
                // Parse the response into an associative array
                $data = $response->toArray();
                // Find the correct forecast day index (0 for today, 1 for tomorrow, etc.)
                $forecastIndex = ($days - 1); // Adjust for 0-based index
                // Return the average temperature based on the date provided
                return $data['forecast']['forecastday'][$forecastIndex]['day']['avgtemp_c'];
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
