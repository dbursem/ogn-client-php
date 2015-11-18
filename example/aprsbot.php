<?php

use dbursem\phpaprs;
use dbursem\phpaprs\packets;
use dbursem\OGNClient;

$debug = true;

require "../vendor/autoload.php";

if (file_exists("local.aprsbot.cfg.php"))
    require "local.aprsbot.cfg.php";
else
    require "aprsbot.cfg.php";

$aprs = new phpaprs\APRS();
$aprs->_debug = $debug;
$ogn = new OGNClient\OGNClient(DB_NAME,DB_USER,DB_PASS,DB_HOST, $debug);

$beacon = new packets\APRS_Item(BEACON_LATITUDE, BEACON_LONGITUDE, MYCALL, BEACON_SYMBOL, BEACON_STATUS);
$beacon->setCallsign(MYCALL);

$filter = $ogn->getFilter();

if ($aprs->connect(HOST, PORT, MYCALL, PASSCODE, $filter) == FALSE)
{
    die( "Connect failed\n");
}

$lastbeacon = 1;

// Setup our callbacks to process incoming stuff
$aprs->addCallback(APRSCODE_POSITION_TS, "APRS", array($ogn,"handlePosition"));


while (1) {
    // Beacon every BEACON_INTERVAL seconds
    if (time() - $lastbeacon > BEACON_INTERVAL) {
        echo "Send beacon\n";
        $aprs->sendPacket($beacon);
        $lastbeacon = time();
    }
    $aprs->ioloop(5);    // handle I/O events
    $ogn->savePositions();
    sleep(1);    // sleep for a second to prevent cpu spinning
}
