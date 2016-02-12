#OGN APRS client

This client can be used to track aircraft and save their logs to a database. Saving other beacons is not (yet) supported.

##Install
* run `composer require dbursem/ogn-client-php`. Check http://getcomposer.org for more info on composer.
* install the database schema from the ogn-client-db.sql file.

##Usage
* Copy the example directory to your install directory  
* Copy the aprsbot.cfg.php file to local.aprsbot.cfg.php and edit it with your personal settings. OGN server and port are already in it.  
* Set the filter to a valid [APRS filter](http://www.aprs-is.net/javAPRSFilter.aspx). If you leave it empty, a filter will be used that selects only the airplanes in the database. 
* Call the example aprsbot from the commandline with `php aprsbot.php`.


###Minimal steps to create your own aprsbot:
 
* Create an instance of APRS:
```php
$aprs = new dbursem\phpaprs\APRS();
```
* Connect to the APRS host
```php
if ($aprs->connect(HOST, PORT, MYCALL, PASSCODE, $filter) == FALSE) 
{
    die( "Connect failed\n");
}
```
* Create an instance of OGNClient:
```php
$ogn = new OGNClient\OGNClient(DB_NAME,DB_USER,DB_PASS,DB_HOST, $debug);
```

* Create a loop to handle the input/output and save records to the database
```php
while (1)
{
    if (time() - $lastbeacon > BEACON_INTERVAL) 
    {
        //send beacon (every 5 minutes) to keep connection alive
        echo "Send beacon\n";
        $aprs->sendPacket($beacon);
        $lastbeacon = time();
    }
    $aprs->ioloop(5);    // handle Socket I/O events
    $ogn->savePositions(); //save received positions to database 
}
```

##About the igc.php file
This code won't work as-is, I only bundled the file to serve as an example of how to generate an IGC file from the logs in ognlogs. Create your own functions to do this in your environment or wait till I have time to make A proper class for it.

check the [openlayers igc example](http://openlayers.org/en/v3.8.1/examples/igc.html) if you want to show the IGC in a webbrowser.

As said before, this class has some rough edges so handle with care!
