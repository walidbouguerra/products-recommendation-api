<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class WeatherRequest
{
    #[Assert\NotBlank(message: "City is required.", groups: ['string', 'numeric'])]
    private string $city;

    #[Assert\Choice(
        choices: ['today', 'tomorrow'],
        message: "Date must be 'today', 'tomorrow' or a number between 1 and 14.",
        groups: ['string']
    )]
    #[Assert\Range(
        min: 1,
        max: 14,
        notInRangeMessage: 'Date must be between 1 and 14 if numeric.',
        groups: ['numeric']
    )]
    private string|int $date = 'today';

    // Getters et setters
    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function getDate(): string|int
    {
        return $this->date;
    }

    public function setDate(string|int $date): void
    {
        $this->date = $date;
    }
}
