#OGN APRS client

This client is by no-means feature complete or ready for real world usage. It needs a lot of work. The only reason I've uploaded it already is because I don't want to keep it from you any longer.

##Install
* ~~run composer require dbursem/ogn-client-php.~~ create a composer.json file with the following content:
 ```
 {"require":"dbursem/ogn-client-php":"1.*@dev","dbursem/phpaprs":"1.*@dev"}}
 ```
 then, run `composer install`. I think this is the best way untill I dare to remove the beta flag from the packages. 
 Check http://getcomposer.org for more info on composer.
* install the database schema from the ogn-client-db.sql file

##Usage
* There's an example aprsbot.php file in the example directory that implements the OGN client. Using this will be the easiest way to get started. Call it from the commandline with `php aprsbot.php`.  
* Copy the aprsbot.cfg.php file to local.aprsbot.cfg.php and edit it with your personal settings. OGN server and port are already in it.  
* Set the filter to a valid [APRS filter](http://www.aprs-is.net/javAPRSFilter.aspx), or use the $ogn->getFilter() method to automatically create a filter that selects all known aircraft from the aircraft table. 


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
