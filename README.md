# Soprano Server
- This project is no longer maintained.

![image](https://user-images.githubusercontent.com/71740767/235468526-9a0eb8bb-886b-49d2-8b6f-85d8e5e8d53a.png)

A backend server for <a href="https://github.com/whleucka/soprano-react" title='Soprano'>Soprano</a>, a music player application.

#### Soprano Tool
```
❯ ./soprano -h
Usage: soprano [options...]
-h, --help                      Get help for commands
-s, --music-scan                Scan music path for audio files and synchronize to database
--music-clean                   Look for orphan tracks in the database and remove them
```


### Celestial Tool
```
ede7e27f30d2# ./celestial -h
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
