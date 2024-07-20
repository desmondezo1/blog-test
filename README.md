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
git clone https://github.com/your-username/blog-post-api.git
cd blog-post-api
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

8. Add configuration cron to your server (If you are hosting this project)

This will run the scheduled task to publish all scheduled posts every minute
```
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```
8. Run the project 

```
php artisan serve
```

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

The Documentation was done using OpenAPI (Swagger)

