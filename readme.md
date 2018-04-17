## README

This Product id developed with love for MSG91 sign-up lead management and will solve the headache of sales CRM.
The following is a quick guide to preparing the User Table.

Prerequisites
-------------
* PHP 7.0 or higher
* Mysql 5.6
* Laravel 5.5


Heroku
------------
heroku git:clone -a contacts-apis
cd contacts-apis


ENV Setup
=============
Copy 
  .env.example => .env 


Docker
=========

get logged in into docker bash (use `docker login` command)
Then run docker composer scripts.

  `docker-compose up`

Docker will start Msg91-panel at localhost:8080


Without Docker
=========

In project root directory, to start project server

    `php artisan serve --port=8080`

This will start server at localhost:8080


To migrate DB

    `php artisan migrate`
 
To seed data

    `php artisan db:seed`


Generate key for Laravel
-------------------
  docker-compose exec php php artisan key:generate


Update Composer & Migrate DB
-----------------

  docker-compose exec php composer install
  docker-compose exec php php artisan migrate:status
  docker-compose exec php php artisan migrate
  docker-compose exec php composer update



MSG91 &copy; 2017

