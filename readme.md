#OGN APRS client

This client is by no-means feature complete or ready for real world usage. It needs a lot of work. The only reason I've uploaded it already is because I don't want to keep it from you any longer.

## Basic usage
* get the [phpaprs](https://github.com/dbursem/phpaprs) library
* in the aprsbot.php file, create an instance of OGNClient
* make a call to savePositions() of the above created instance of OGNClient in the while loop
* make sure you have an "ognlogs" table to save the positions to. Check the savePositions method for some hints about the table structure
* you'll also need an "airplanes" table with flarm ID's you want to track, for the getfilter() method. Or you can just hardcode a filter for the aprs client.

**Do \_NOT\_ use the igc.php file!**
this file is only present to serve as an example of how to generate an IGC file from the logs in ognlogs. 
check the [openlayers igc example](http://openlayers.org/en/v3.8.1/examples/igc.html) if you want to show the IGC in a webbrowser.

As said before, this class has some rough edges so handle with care!