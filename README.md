# Celestial
[![PHP Composer](https://github.com/libra-php/constellation/actions/workflows/php.yml/badge.svg?branch=main)](https://github.com/libra-php/constellation/actions/workflows/php.yml)

A PHP web framework.
Don't use this in production, it may never be deployable! 🤣
100% experimental.

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
- Copy example environment and fill out the secrets
```
cp .env.example .env
```

- Start containers
```
sudo docker-compose up -d
```

- Create database (from database container)
- You can set the password in docker-compose.yml by modifying `MYSQL_ROOT_PASSWORD`
```
sudo docker-compose exec database sh
mysql -p
create database celestial
```

- Run migrations (from database container)
```
./celestial --migration-run
```

- By default, the database user is root. You should consider setting up a different database user for your application.


- If you receive this message "Fatal error: Uncaught RuntimeException: Unable to create the cache directory", then please create the view cache directory (from php container)
```
sudo docker-compose exec php zsh
./celestial --cache-create
```
- The views/.cache directory must created be owned by www-data or equivalent


- Congrats! 🥳
- You should now see the test message Hello, World!

#### Installation FAQ
- Why does it have to be this way?
    - It won't be for long, no worries. I am working on a fully automated docker setup.


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
