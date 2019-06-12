Welcome to DB / HTTP / Docker test 
-

**Installation**

1. Clone project locally
2. Configure your docker to be able to mount project folder as volume.
3. Run `docker-compose up` to setup environment. That would create two containers with network between them.

**Usage**

- Open `http://localhost:8000/prepare` to fill database with data according to task.

- Open `http://localhost:8000/dbs/foo/tables/source` to download generated data.


**Tests**

Execute `docker exec  dbhttpweb php phpunit-8.2.1.phar DbHttpTest.php` in terminal.
