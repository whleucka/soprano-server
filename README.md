# Soprano Server
[![PHP Composer](https://github.com/libra-php/constellation/actions/workflows/php.yml/badge.svg?branch=main)](https://github.com/libra-php/constellation/actions/workflows/php.yml)

![image](https://user-images.githubusercontent.com/71740767/235468526-9a0eb8bb-886b-49d2-8b6f-85d8e5e8d53a.png)

A backend server for <a href="https://github.com/whleucka/soprano-react" title='Soprano'>Soprano</a>, a music player application.
Built on top of a custom PHP framework called <a href="https://github.com/libra-php/celestial" title="Celestial">Celestial</a>.

#### Installation
- WIP, detailed instructions coming soon.
- Read more about it <a href="https://github.com/libra-php/celestial" title="Celestial">here</a>.

#### Soprano Tool
```
❯ ./soprano -h

███████╗ ██████╗ ██████╗ ██████╗  █████╗ ███╗   ██╗ ██████╗
██╔════╝██╔═══██╗██╔══██╗██╔══██╗██╔══██╗████╗  ██║██╔═══██╗
███████╗██║   ██║██████╔╝██████╔╝███████║██╔██╗ ██║██║   ██║
╚════██║██║   ██║██╔═══╝ ██╔══██╗██╔══██║██║╚██╗██║██║   ██║
███████║╚██████╔╝██║     ██║  ██║██║  ██║██║ ╚████║╚██████╔╝
╚══════╝ ╚═════╝ ╚═╝     ╚═╝  ╚═╝╚═╝  ╚═╝╚═╝  ╚═══╝ ╚═════╝

Usage: soprano [options...]
-h, --help                      Get help for commands
-s, --music-scan                Scan music path for audio files and synchronize to database
--music-clean                   Look for orphan tracks in the database and remove them
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
h, --help                  Get help for commands
-p, --port                  Set development server port
-s, --serve                 Run development server
--cache-create              Create view cache directory
--storage-link              Create storage directory and public symlink
--migration                 Create an empty migration class
--migration-table           Create an empty migration class new table
--migration-list            Display all migration files
--migration-run             Run migration files and call up method
--migration-fresh           Drop database and call migration-run
--migration-up=file         Call up method from migration file
--migration-down=file       Call down method from migration file
--model                     Create a model class
```


#### License
The MIT License (MIT)
