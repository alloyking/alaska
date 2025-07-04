## Synopsis
We are recipe search engine that allows users to find based on keyword or ingredients. Search is our most important product feature, and we are constantly improving it to provide the best experience for our users.

At the time we do not want to pay for a search engine like Algolia or ElasticSearch, so we are building our own search engine using MySQL full text search capabilities.  

To do this well we are testing `whereFullText()` search in either **boolean mode** or **natural language** mode with `fullText()` indexing capabilities of MySQL.

## How it works
We are making use of the `RecipeSearchInterface` and we have two implementations of it:
- `EloquentBooleanRecipeSearch.php` - This implementation uses MySQL's full text search in **Boolean Mode** to perform searches on the recipes.
- `EloquentNaturalLanguageRecipeSearch.php` - This implementation uses MySQL's **Natural Language Mode** to perform searches on the recipes.

The `RecipeSearchInterface` is registered in the service container and can be swapped out for either of the implementations. This allows us to easily switch between the two search modes without changing the code that uses the interface. You can see the implementation in the `RecipeSearchProvider.php` file.

With this is place we can now test two different search modes and see which one performs better for our use case. With the idea of adding Laravel Scout or other options in the future

## Rules we followed for search
### Create a search UI with the following requirements:
- Author email - exact match
- Keyword - should match ANY of these fields: name, description, ingredients, or steps
- Ingredient - this could be a partial match; for example, “potato” should match “3 large potatoes” in the ingredients list
- Allow for combinations of search parameters, which will be queried as AND conditions


## Additional Considerations
- The search should be fast and efficient, even with a large number of recipes.
- The search should work even when non-us characters are used in the search query. (e.g. "Pâté" or "Bánh mì")
- The search should still work if we type "Pate" or "Banh mì"

## Testing
We used both PHPUnit and Pest to test the search functionality. The tests are located in the `tests` directory. We used both simply because it was more or less a learning experience for us. We wanted to see how both testing frameworks work and how they can be used to test the search functionality. 

We used a testing mysql database (not sqlite) so that the fulltext indexing could be tested properly. See the command below to create the testing database.

```bash
./vendor/bin/sail mysql -e "CREATE DATABASE IF NOT EXISTS testing;"
```

#### _Any advice you would like to share with us on how to improve our tests is welcomed!!!!_

## AI Use
 - Copilot was used in the PHPStorm IDE for code completion.
 - ChatGPT o4-mini-high was used to explore the unique differences between Natural Language Mode and Boolean Mode in MySQL full text search.
 - Claude 3.7 was used to write the initial Vue tests (which wasn't great, but it was a start).

## Some of the conventions we followed
 - Interfaces
 - Resources
 - Value Objects
 - Service Providers
 - Repositories
 - Dependency Injection
 - Service Container
 - Repository Pattern
 - Form Requests

## Getting started

### Pre-requisites
- docker
- docker-compose
  

### Check out this repository
`git clone git@github.com:alloyking/alaska.git`


### Run composer to kickstart laravel sail

```bash
docker run --rm \
    --pull=always \
    -v "$(pwd)":/opt \
    -w /opt \
    laravelsail/php82-composer:latest \
    bash -c "composer install"
```

### Run the application
`cp .env.example .env`

`./vendor/bin/sail up -d`

`./vendor/bin/sail artisan key:generate`

`./vendor/bin/sail artisan migrate`

-`./vendor/bin/sail mysql -e "CREATE DATABASE IF NOT EXISTS testing;"`

### Kickstart the nuxt frontend
`./vendor/bin/sail npm install --prefix frontend`

### Run the frontend
`./vendor/bin/sail npm run dev --prefix frontend`

### Confirm your application
visit the frontend http://localhost:3000

visit the backend http://localhost:8888


### Connecting to your database from localhost
`docker exec -it laravel-mysql-1 bash -c "mysql -u root -p password"`

Or use any database GUI and connect to 127.0.0.1 port 3333


### Other tips
`./vendor/bin/sail down` to bring down the stack
