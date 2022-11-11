# Celestial
[![PHP Composer](https://github.com/libra-php/constellation/actions/workflows/php.yml/badge.svg?branch=main)](https://github.com/libra-php/constellation/actions/workflows/php.yml)

A PHP web framework.
A work in progress. 100% experimental 🤣


#### Installation
- Composer Installation
```
composer create-project libra-php/celestial
npm install
npm run build
```

#### Docker
- Copy example environment and fill in the secrets
```
cp .env.example .env
```

- Start containers
```
sudo docker-compose up -d
```

- Create database (from database container)
- You can set the secret database password in the .env file by modifying
`DB_PASSWORD`
```
sudo docker-compose exec database sh
mysql -p
create database celestial
```

- Run migrations (from database container)
```
./celestial --migration-run
```

- By default, the database user is root. You should consider setting up a different database user for your application. Set the password secret in the .env file


- If you receive this message "Fatal error: Uncaught RuntimeException: Unable to create the cache directory", then please create the view cache directory (from php container)
```
sudo docker-compose exec php zsh
./celestial --cache-create
```

- Congrats! 🥳
- You should now see the test message Hello, World!


#### Installation FAQ
- WIP


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
-h, --help                  Get help for commands
-p, --port                  Set development server port
-s, --serve                 Run development server
--cache-create              Create view cache directory
--migration-run             Run migration files and call up method
--migration-fresh           Drop database and call migration-run
--migration-up=file         Call up method from migration file
--migration-down=file       Call down method from migration file
```


#### License
The MIT License (MIT)
