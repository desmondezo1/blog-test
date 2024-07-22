# Blog Post API

This project is an API for a simple blog post system, built using laravel and tested using phpUnit.
Below are the requirements to get this project up and running on your computer and a guide on how to access the API documentation
## Prerequisites

Before you begin, you would need to double check that you have the following:
* You have installed PHP 8.0 or later
* You have installed [Composer](https://getcomposer.org/doc/00-intro.md#downloading-the-composer-executable)
* You have a MySQL database server running

## Setting up the project

Follow these steps to get your development environment set up:

1. Clone the repository

```
git clone https://github.com/desmondezo1/blog-test.git
cd blog-test
```
2. Install dependencies
```
composer install
```
3. Create a copy of the .env file

```
cp .env.example .env
```
4. Generate an app encryption key

```
php artisan key:generate
```

5. Configure your database in the .env file
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```
6. Run database migrations

```
php artisan migrate

```
7. Seed the database

```
php artisan db:seed
```

> **Note**: The Default Admin user is **desmond@test.com** and password for all users is "**password**"
>> **Email: desmond@test.com**
>> **Password: password**

8. (optional) Add configuration cron to your server (If you are hosting this project)

This will run the scheduled task to publish all scheduled posts every minute
```
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```
8. Run the project 

```
php artisan serve
```

## How to Use API

The project is running at http://localhost:800

Visit the api documentation at http://localhost:800/api/documentation

Login to default admin user accoun to get access token

> Make a POST request to http://localhost:800/api/auth/login
> The Login request object should contain the email and password :
>> **email: desmond@test.com**
>> **password: password**



## Additional Commands

Some additional commands you might need working on the project

### Running Tests
```
php artisan test
```
### Scheduling Posts

To view scheduled posts, run:

```
php artisan schedule:list
```

To run the schedular localy, run: 
```
php artisan schedule:work
```

To run the Schedular:

```
php artisan schedule:run
```

## API DOCUMENTATION

To view the documentation of the API's of this project, you would need to start the project and vist the route.

```
/api/documentation

```

For exmaple: https://localhost:8000/api/documentation

The API Documentation was done using OpenAPI (Swagger)

## Routes list

```

  POST            api/auth/login .................................. login › Api\AuthController@login
  POST            api/auth/logout ........................................ Api\AuthController@logout
  GET|HEAD        api/documentation l5-swagger.default.api › L5Swagger\Http › SwaggerController@api
  GET|HEAD        api/oauth2-callback l5-swagger.default.oauth2_callback › L5Swagger\Http › Swagger…
  POST            api/v1/admin/authors ............................... Api\V1\AuthorController@store
  GET|HEAD        api/v1/admin/posts ................................... Api\V1\PostController@index
  POST            api/v1/admin/posts ................................... Api\V1\PostController@store
  GET|HEAD        api/v1/admin/posts/status/{status} ............. Api\V1\PostController@getByStatus
  PUT             api/v1/admin/posts/{id} ............................. Api\V1\PostController@update
  DELETE          api/v1/admin/posts/{id} ............................ Api\V1\PostController@destroy
  POST            api/v1/admin/posts/{id}/publish .................... Api\V1\PostController@publish
  POST            api/v1/admin/posts/{id}/schedule .................. Api\V1\PostController@schedule
  POST            api/v1/admin/posts/{id}/unpublish ................ Api\V1\PostController@unpublish
  GET|HEAD        api/v1/admin/users ..................... users.index › Api\V1\UserController@index
  POST            api/v1/admin/users ..................... users.store › Api\V1\UserController@store
  GET|HEAD        api/v1/admin/users/{user} ................ users.show › Api\V1\UserController@show
  PUT|PATCH       api/v1/admin/users/{user} ............ users.update › Api\V1\UserController@update
  DELETE          api/v1/admin/users/{user} .......... users.destroy › Api\V1\UserController@destroy
  GET|HEAD        api/v1/posts ......................................... Api\V1\PostController@index
  GET|HEAD        api/v1/posts/author/{userId} ................... Api\V1\PostController@getByAuthor
  GET|HEAD        api/v1/posts/search ................................. Api\V1\PostController@search
  GET|HEAD        api/v1/posts/{id} ..................................... Api\V1\PostController@show
  GET|HEAD        api/v1/posts/{id}/comments ..................... Api\V1\PostController@getComments
       
```


## DO YOU PREFER TO USE DOCKER?

Here's how to run this project using Docker

### Prerequisites

### 1. You Obviously need to have docker installed on your computer

[Install Docker](https://docs.docker.com/engine/install/)
### 2. Build the Containers: 
 You will need to run:

```
docker-compose up -d --build
```

### 3. Install Laravel Dependencies

```
docker-compose exec app composer install
```

### 4. Edit/Create the .env file

Add the following to the .env file:

```
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret

```
### 5. Generate Application Key

```
docker-compose exec app php artisan key:generate
```

### 6. Run Database Migrations

```
docker-compose exec app php artisan migrate
```

### 7. Run Database seed

```
docker-compose exec app php artisan db:seed
```

### 8. The API is LIVE on Port 8000!

Visit [http://localhost:8000](http://localhost:8000)

### 9. Visit the API documentation

Visit ->

[http://localhost:8000/api/documentation](http://localhost:8000/api/documentation)
