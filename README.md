# Celestial
[![PHP Composer](https://github.com/libra-php/constellation/actions/workflows/php.yml/badge.svg?branch=main)](https://github.com/libra-php/constellation/actions/workflows/php.yml)

A PHP web framework.

(Very early stages of development)

#### Installation
- Composer Installation
```
composer create-project libra-php/celestial
```

- Note: the composer.json file has a local repository dependency `libra-php/constellation`
- Composer install method will not work until v0.0.1 release.
- For now, you can clone the repository into the working directory.
```
git clone git@github.com:/libra-php/constellation.git ./constellation/
cd constellation && composer install && cd -
composer install
```

#### Docker
- Docker Setup
```
sudo docker-compose up -d
```

- Create database
You can set the password in docker-compose.yml by modifying `MYSQL_ROOT_PASSWORD`
```
sudo docker-compose exec mysql sh
sudo mysql -p
create database celestial
```
By default, the database user is root. You should consider setting up a different database user for your application.


If you receive the "Fatal error: Uncaught RuntimeException: Unable to create the cache directory"message, then please create the view cache directory.
- The views/.cache directory must created be owned by www-data or equivalent
```
sudo docker-compose exec php zsh
./celestial --cache-create
```

You should now see the test message Hello, World!


### Celestial Tool
```
ede7e27f30d2# ./celestial -h            

██████╗███████╗██╗     ███████╗███████╗████████╗██╗ █████╗ ██╗     
██╔════╝██╔════╝██║     ██╔════╝██╔════╝╚══██╔══╝██║██╔══██╗██║     
██║     █████╗  ██║     █████╗  ███████╗   ██║   ██║███████║██║     
██║     ██╔══╝  ██║     ██╔══╝  ╚════██║   ██║   ██║██╔══██║██║     
╚██████╗███████╗███████╗███████╗███████║   ██║   ██║██║  ██║███████╗
 ╚═════╝╚══════╝╚══════╝╚══════╝╚══════╝   ╚═╝   ╚═╝╚═╝  ╚═╝╚══════╝
Usage: celestial [options...]
-h, --help              Get help for commands
-p, --port              Set development server port
-s, --serve             Run development server
--cache-create          Create view cache directory
--migration-run         Run migration files and calling up method
--migration-fresh       Drop database and call migration-run
```


#### License
The MIT License (MIT)
