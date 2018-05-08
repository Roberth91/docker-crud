# The Brief
The requirement of this test is to develop a server-side solution to perform CRUD operations (Create, Read, Update and Delete) on the list displayed in the supplied markup.

The list should be limited to 10 records in total, and a maximum of 4 records assigned per "Job Role".

For data storage, the solution should use a MySQL database, with a schema that you deem most appropriate.

You don't need to go beyond the above requirements, however feel free to add any additional functionality that you feel will enhance the solution that you produce.

## Requirements
* Docker - Tested with V17.12.0
* Docker compose - Tested with V1.18.0

## Getting Started

I have implemented STRTA (Scripts To Rule Them All) in this project, the scripts are as follows:

* `script/bootstrap`
* `script/setup`
* `script/logs`
* `script/server`
* `script/test`

The scripts can be run in isolation however the recommended way to run this project is to execute `script/setup` from the project root dir. This will check for a local Docker / Docker Compose installation before building and bringing up the containers required to run the project.

After running the script/setup copy the env.dist to .env and ensure the DATABASE_URL is set to
`mysql://dbuser:dbpw@127.0.0.1:8002:3306/docker_symfony4`

Run
`php bin/console doctrine:migrations:migrate`

Set DATABASE_URL to
`mysql://dbuser:dbpw@docker-symfony4-mysql:3306/docker_symfony4`

Access http://localhost:8000/user/index

## Known Issues
* There is an issue when running unit tests where the web container is sometimes unreachable - this could be resolved by starting again with my docker config however this would be a time consuming process.
* There is another docker related issue when running setup where the database url needs changing and a php command is run on the host machine. This is again related to my base containers and if I had the time I would use a different setup which would eliminate these problems - the database would be correctly linked and the php command would be run inside the application container.

## TODO
* Fix docker containers for unit tests