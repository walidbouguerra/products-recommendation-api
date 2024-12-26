<?php

namespace App\Controller;

use App\DTO\WeatherRequest;
use App\Repository\ProductRepository;
use App\Service\WeatherService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductController extends AbstractController
{
    /**
     * Handles the API request to get products based on weather conditions.
     *
     * @param Request $request The HTTP request containing the input data.
     * @param WeatherService $weatherService The weather service to fetch temperature data.
     * @param ProductRepository $productRepository The repository to fetch products from the database.
     * @param SerializerInterface $serializer The serializer to convert products to JSON.
     * 
     * @return JsonResponse The JSON response containing the products and weather information.
     */
    #[Route('/api/products', name: 'product.products', methods: ['POST'])]
    public function getProducts(
        Request $request, 
        WeatherService $weatherService, 
        ProductRepository $productRepository, 
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
        // Decode the incoming JSON request content
        $data = json_decode($request->getContent(), true);
      
        // Initialize the WeatherRequest DTO
        $weatherRequest = new WeatherRequest();
        $weatherRequest->setCity($data['weather']['city'] ?? '');
        $weatherRequest->setDate($data['date'] ?? 'today');

        // Determine the appropriate validation group based on the type of date
        $validationGroups = is_numeric($weatherRequest->getDate()) ? ['numeric'] : ['string'];

        // Validate the DTO
        $errors = $validator->validate($weatherRequest, null, $validationGroups);

        // If there are validation errors, return them
        if (count($errors) > 0) {
            $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, implode(', ', $errorMessages));
            }

        // Use the DTO to retrieve the city and the date
        $city = $weatherRequest->getCity();
        $date = $weatherRequest->getDate();

        // Retrieve temperature data from the WeatherService
        $temperature = $weatherService->getTemperature($city, $date);

        // Handle case where temperature data retrieval fails
        if ($temperature === null) {
            throw new HttpException(JsonResponse::HTTP_INTERNAL_SERVER_ERROR, 'Failed to retrieve temperature data.');
        }

        // Determine the product type based on the temperature
        if ($temperature < 10) {
            $products = $productRepository->findByType(1);  // Pull products
            $weather = ['city' => $city, 'is' => 'cold', 'date' => $date];
        } elseif ($temperature <= 20) {
            $products = $productRepository->findByType(2);  // Sweat products
            $weather = ['city' => $city, 'is' => 'warm', 'date' => $date];
        } else {
            $products = $productRepository->findByType(3);  // T-Shirt products
            $weather = ['city' => $city, 'is' => 'hot', 'date' => $date];
        }
        
        // Serialize the products to JSON
        $serializedProducts = $serializer->serialize($products, 'json', ['groups' => 'getProducts']);

        // Prepare the response data
        $response = [
            'products' => json_decode($serializedProducts),
            'weather' => $weather,
        ];

        // Return the response as JSON
        return new JsonResponse($response, Response::HTTP_OK);
    }
}
