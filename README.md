# Celestial
[![PHP Composer](https://github.com/libra-php/constellation/actions/workflows/php.yml/badge.svg?branch=main)](https://github.com/libra-php/constellation/actions/workflows/php.yml)

A PHP web framework.

(Very early stages of development)

#### Installation
- Note: the composer.json file has a local repository dependency `libra-php/constellation`
- It is currently set up like this to make development easier. This will be removed once the package is available on packagist. 
- For now, you can clone the repository into the working directory.

Example)
```
git clone git@github.com:/libra-php/constellation.git ./constellation/
cd constellation && composer install && cd -
comoser install
```

- The views/.cache directory must created be owned by www-data or equivalent

Example)
```
mkdir -p views/.cache && chown www-data:www-data views/.cache
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
-h, --help                      Get help for commands
-p, --port                      Set development server port
-s, --serve                     Run development server
migration-run                   Run migration files and calling up method
migration-fresh                 Drop database and run migration files and call migration-run
```


#### License
The MIT License (MIT)
