#OGN APRS client

This client is by no-means feature complete or ready for real world usage. It needs a lot of work. The only reason I've uploaded it already is because I don't want to keep it from you any longer.

## Basic usage
* get the [phpaprs](https://github.com/dbursem/phpaprs) library
* in the aprsbot.php file, create an instance of OGNClient
* make a call to savePositions() of the above created instance of OGNClient in the while loop
* make sure you have a database table to save the positions to. Check the savePositions method for some hints about the database structure.

As said before, this class has some rough edges so handle with care!