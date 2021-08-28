# Iceberg Estates

## The Goal of The Project

A real estate agent named Iceberg Estates in England; It has difficulty keeping records of which house its employees will show, when and to which customers. The company manager, who wants to use his employees more effectively, especially wants to be able to control the length of the appointments, the time they allocate for appointments, and all these appointments without conflicts.

We want to develop an API system that will solve the problems of real estate agent named Iceberg Estates.

## Tech Stack and Prerequisites

* PHP
* MySQL
* Composer
* Laravel Framework

## Setup

1. Install latest version of the PHP and configure/activate the php.ini file for pdo extensions.
2. Install MySQL and then set the root password or install other servers for mysql (ex: xampp..).
3. Download and install composer on your system.
4. Install laravel through composer.
5. Download the project from this repo and open through vscode or with alternatives.
6. Open the terminal (Make sure it's in the same location with the project.) and install packages for jwt token fallowing below code.

    ***composer require tymon/jwt-auth --ignore-platform-reqs***
    
7. Duplicate .env.example file and rename to .env
8. Create a new key for new laravel project fallowing below code.

    ***php artisan key:generate***
    
9. Define custom variables in config file and don't forget clear the cache of the config files.

    ***php artisan config:cache***
    
11. Let's we start the project with command is below.

    ***php artisan serve***
    
11. If you are working on the local system open chrome and navigate the below link that is use adminer for mysql database interface. Then type root as username and type the password you set.
12. Create a new database named Iceberg_Estates.
13. Configure .env file for db connection.
14. Last step is migration process of the tables code is below.

    ***php artisan migrate***
    
15. Our project is ready, let's start now.



## Endpoints Documentation Link on Postman

<a href="https://www.postman.com/burak34/workspace/iceberg-estates/documentation/10659077-d3dad476-fff1-47aa-964e-f9e8810f08f3" target="_blank">Documentation Link</a>

