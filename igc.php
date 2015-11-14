<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 29-10-15
 * Time: 15:03
 */

//WARNING!! By no means use this file as-is! Only use it as example of how to generate an IGC file!


// This code wont work as is, so lets just exit here.
exit;


/*

$log_start = new DateTime($flight['starttime']);
$log_start->sub(new DateInterval('PT3M'));
$log_start->setTimezone(new DateTimeZone('UTC'));

$log_end = new DateTime($flight['landingtime']);
$log_end->add(new DateInterval('PT3M'));
$log_end->setTimezone(new DateTimeZone('UTC'));



$q = 'SELECT DATE_FORMAT(log_time,\'%H%i%s\') AS logtime, latitude, longitude, altitude
      FROM ognlogs
      WHERE flarm_id = ?
        AND log_time BETWEEN ? AND ?
        ';
$p = array(
    $flight['flarm_id'],
    $log_start->format('Y-m-d H:i:s'),
    $log_end->format('Y-m-d H:i:s'),
);

$result = $db->Execute($q,$p);//$db is an adodb instance
$b_record = '';
while ($result !== false && $row=$result->FetchRow())
{
    //build latitude string
    $lat_direction = 'N';
    if ($row['latitude'] < 0)
    {
        $row['latitude'] *= -1;
        $lat_direction = 'S';
    }
    $lat_deg = floor($row['latitude']);
    $lat_min = round( (($row['latitude'] - $lat_deg) * 60) , 3);

    $lat_string = str_pad($lat_deg,2,'0',STR_PAD_LEFT); //lat degrees is 2 bytes
    $lat_string .= str_pad(str_replace(',','',$lat_min),5,'0',STR_PAD_RIGHT); //minutes is 5 bytes, no decimal point
    $lat_string .= $lat_direction;

    //build longitude string
    $lon_direction = 'E';
    if ($row['longitude'] < 0)
    {
        $row['longitude'] *= -1;
        $lon_direction = 'W';
    }
    $lon_deg = floor($row['longitude']);
    $lon_min = round( (($row['longitude'] - $lon_deg) * 60) , 3);
    $lon_string = str_pad($lon_deg,3,'0',STR_PAD_LEFT); //lon degrees is 3 bytes
    $lon_string .= str_pad(str_replace(',','',$lon_min),5,'0',STR_PAD_RIGHT); //minutes is 5 bytes, no decimal point
    $lon_string .= $lon_direction;

    //build B-record
    $b_record .= 'B';
    $b_record .= $row['logtime'];
    $b_record .= $lat_string;
    $b_record .= $lon_string;
    $b_record .= 'A'; //fix quality, A or V
    $b_record .= str_pad($row['altitude'],5,'0',STR_PAD_LEFT); //pressure altitude
    $b_record .= str_pad($row['altitude'],5,'0',STR_PAD_LEFT); //GPS altitude
    $b_record .= "\n";

}

$igc_header = "AOGNXXX
HFFXA010
HFDTE{$log_start->format('dmy')}
HFPLTPILOTINCHARGE: {$flight['firstpilot_name']}
HFLCM2CREW2: {$flight['secondpilot_name']}
HFGTYGLIDERTYPE: {$flight['type']}
HFGLIDERID: {$flight['airplane']}
HFDTM100GPSDATUM: WGS-1984
HFRFWFIRMWAREVERSION: 0
HFRHWHARDWAREVERSION: 0
HFFTYFRTYPE: OGN-log by ZCFWeb
HFGPSGPS: N/A
HFPRSPRESSALTSENSOR: N/A
HFCIDCOMPETITIONID: {$flight['callsign']}
HFCCLCOMPETITIONCLASS:
";


header('Content-Type: text/igc');
header('Content-Disposition: attachment; filename="'.$params['flight_id'].'.igc"');

echo $igc_header . $b_record;
*/