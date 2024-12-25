<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\ProductType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        
        // Create a Faker instance for generating fake data
        $faker = Factory::create('fr-FR');
        
        // Initialize an array to hold the product types
        $productTypes = [];
        $types = ['Pull', 'Sweat', 'T-Shirt'];

        // Persist the product types to the database
        foreach ($types as $type) {
            $productType = (new ProductType())
                ->setName($type);
            $manager->persist($productType);

            $productTypes[] = $productType;
        }

        // Generate 30 products with random data
        for ($i = 0; $i < 30; $i++) {
            // Randomly select a product type from the available types
            $productType = $faker->randomElement($productTypes);

            // Create a new product with a random name and price
            $product = (new Product())
                ->setName($productType->getName() . ' ' . $faker->colorName())
                ->setPrice($faker->randomFloat(2, 10, 50)) // Random price between 10 and 50
                ->setProductType($productType);

            // Persist the product to the database
            $manager->persist($product);
        }
        
        // Flush all persisted entities to the database
        $manager->flush();
    }
}
