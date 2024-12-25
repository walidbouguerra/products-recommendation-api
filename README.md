
# Products Recommendation API

API that lists products based on the weather of a targeted city.

## Table of Contents
1. [Installation](#installation)
2. [API Endpoints](#api-endpoints)
3. [Tests](#tests)

## Installation

### Prerequisites
- PHP 8.2+
- Composer
- Database (e.g., MySQL or PostgreSQL)

### Installation Steps

1. Clone the repository:
```
git clone https://github.com/walidbouguerra/products-recommendation-api.git
```

2. Navigate into the project directory:  
```
cd products-recommendation-api
```

3. Install dependencies:
```
composer install
```

4. Configure environment variables: 
```    
Copy .env to .env.local and configure it with your database and API credentials.
```

5. Create the database and run migrations:
```
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

6. Load sample data (fixtures):
```
php bin/console doctrine:fixtures:load
```

7. Start the Symfony server:
```
symfony server:start
```

## API Endpoints

### POST /api/products
Retrieves a list of products based on the weather data for a specific city and date.

**Request:**
```
{
    "weather": {
        "city": "Marseille"
    },
    "date": "tomorrow"
}
```  
**Response:**
```
{
    "products": [
        {
            "id": 1,
            "name": "Pull Aquamarine",
            "price": "15.57"
        },
        {
            "id": 3,
            "name": "Pull MediumVioletRed",
            "price": "43.56"
        }
    ],
    "weather": {
        "city": "Marseille",
        "is": "cold",
        "date": "tomorrow"
    }
}
```

## Tests

### Setting Up the Test Environment
1. Set up the test database by configuring .env.test.local with the appropriate test database credentials.

2. Create the test database and run the migrations:
```
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:migrate --env=test
```

3. Load the fixtures for testing:
```
php bin/console doctrine:fixtures:load --env=test
```

### Running Tests
Once the test environment is set up, you can run the tests:
```
php bin/phpunit
```
