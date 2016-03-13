<?php

use dbursem\phpaprs;
use dbursem\phpaprs\packets;
use dbursem\OGNClient;

$debug = true;

//automatically include required vendor namespaces
require "../vendor/autoload.php";

//include config file
if (file_exists("local.aprsbot.cfg.php"))
    require "local.aprsbot.cfg.php";
else
    require "aprsbot.cfg.php";

//initiate APRS and OGNClient
$aprs = new phpaprs\APRS();
$aprs->_debug = $debug;
$ogn = new OGNClient\OGNClient(DB_NAME,DB_USER,DB_PASS,DB_HOST, $debug);

//prepare our own beacon message
$beacon = new packets\APRS_Item(BEACON_LATITUDE, BEACON_LONGITUDE, MYCALL, BEACON_SYMBOL, BEACON_STATUS);
$beacon->setCallsign(MYCALL);

//update the airplane table with most recent DDB data
//optionally provide an array of aircraft registrations you want to add to the airplane table
//for example: You could select all known aircraft from your flight administration database.
$ogn->updateAirplaneTable();

// set the APRS filter (to specify what messages we want to receive)
$filter = FILTER;
if (empty($filter))
    $filter = $ogn->getFilter();

// connect to OGN's APRS Server
if ($aprs->connect(HOST, PORT, MYCALL, PASSCODE, $filter) == FALSE)
{
    die( "Connect failed\n");
}

$lastbeacon = 1;

// Setup our callbacks to process incoming stuff
$aprs->addCallback(APRSCODE_POSITION_TS, "APRS", array($ogn,"handlePosition"));

while (1) {
    // send our own beacon every N seconds to keep connection alive
    if (time() - $lastbeacon > BEACON_INTERVAL) {
        echo "Send beacon\n";
        $aprs->sendPacket($beacon);
        $lastbeacon = time();
    }

    // handle any received APRS messages
    $aprs->ioloop(5);
    // Save buffered messages to database
    $ogn->savePositions();

    sleep(1);    // sleep for a second to prevent cpu spinning
}
