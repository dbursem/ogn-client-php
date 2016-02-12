<?php
// this is an example configuration for example.
// you should create a new file named "local.example.cfg.php" instead of modifying
// this one.


define("MYCALL","N0CALL");
define("PASSCODE","-1");

define("BEACON_LATITUDE","4917.04N");
define("BEACON_LONGITUDE","12306.44W");
define("BEACON_STATUS","hi there");
define("BEACON_SYMBOL","\\?");

define("HOST","aprs.glidernet.org");
define("PORT",14580);

define("BEACON_INTERVAL",60*5);

define("FILTER","");
// Filter example: "r/48/7/700" would filter a big part of europe, so it'll give you lots of test data.
// Check http://www.aprs-is.net/javAPRSFilter.aspx for more valid filters
// or leave it blank and provide some aircraft registrations to the $ogn->updateAirplaneTable() method.

define('DB_USER','example');
define('DB_PASS','example');
define('DB_NAME','example');
define('DB_HOST','localhost');