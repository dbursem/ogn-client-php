<?php
/**
 * Created by DBUrsem
 * License: GPLv3
 */
namespace dbursem\OGNClient;

class OGNClient
{
    private $buffer;
    /** @var  $db \PDO */
    private $db;
    private $debug = false;
    private $lastsave = 0;

    function __construct($dbname,$dbuser,$dbpass,$dbhost='localhost', $debug = false)
    {
        $this->pdo_connect($dbname,$dbuser,$dbpass,$dbhost);

        $this->debug = $debug;
    }

    function handlePosition($header,$line)
    {
        $this->debug('in handlePosition()');
        $data=$header['aprsdat'];


        // first, match all info from the APRS Message.
        // I know regexes are hard to read, so I tried to explain what each
        // section selects above, and an examle of the APRS sentence below.

        // index:    1         2         3          4         5                   6     7  8         9                   10    11
        // name:     hours_____minutes___seconds___ lat-deg___lat-min_____________dir___   lon-deg___lon-min_____________dir___
        preg_match('@([0-9]{2})([0-9]{2})([0-9]{2})h([0-9]{2})([0-9]{2}\.[0-9]{2})([NS])(.)([0-9]{3})([0-9]{2}\.[0-9]{2})([EW])(.)@',$data,$APRS);
        //APRS line: 12        34        56        h01        23       . 45       N     /  001       23       . 45       E     '

        if ($APRS[7] == '/' && $APRS[11] == "'")
        {
            //small aircraft, continue
            //check http://www.aprs.org/doc/APRS101.PDF page 114 for documentation
        }
        else
        {
            //something else, ignore!
            //$this->debug('not an aircraft, ignored.');
            //return true;
        }

        // index:    1           2          3
        // name:     course____  speed_____ altitude_____
        preg_match('@([0-9]{3})/([0-9]{3})/A=([0-9]{6})@',$data,$APRS2);
        //APRS line   360        /100        /A=012345

        if (!isset($APRS2[1]))
        {
            // course/speed not available, check for altitude only
            preg_match('@/A=([0-9]{6})@',$data,$APRS2);
            $course = 0;
            $speed = 0;
            $altitude = intval( $APRS2[1]);
        }
        else
        {
            $course = intval($APRS2[1]);
            $speed = intval($APRS2[2]);
            $altitude = intval( $APRS2[3]);
        }

        //precision
        preg_match('/!W([0-9])([0-9])!/',$data,$precision);
        if (empty($precision)) {
            //enhanced precision string not found
            $precision = ['', '', ''];
        }


        //Next, match the OGN specific extras
        //APRS line: id0ADDE626 -019 fpm +0.0rot 5.5dB 3e -4.3kHz
        preg_match('@\sid([0-9A-F]{2})([0-9A-F]{6})\s@',$data,$bitfield_id);
        preg_match('@\s([\+\-][0-9]{3})fpm\s@',$data,$climbrate);
        preg_match('@\s([\+\-][0-9\.]{3})rot\s@',$data,$rotation);
        preg_match('@\s([0-9\.]{3})dB\s@',$data,$signaltonoise);
        preg_match('@\s([0-9])e\s@',$data,$biterrors);
        preg_match('@\s([\+\-][0-9\.]{3})kHz@',$data,$freqoffset);



        //bitfield: STttttaa -> S=stealth, T=Notrack, tttt=aicraft type, aa=device type

        if (isset($bitfield_id[1]))
        {
            $bitfield = intval('0x'.$bitfield_id[1],0);
            $device   = ($bitfield & 0b00000011);
            $aircraft = ($bitfield & 0b00111100) >> 2;
            $notrack  = ($bitfield & 0b01000000) >> 6; //this will always be 0 because notrack packages are dropped at the receiver level
            $stealth  = ($bitfield & 0b10000000) >> 7;
        }
        else
            $device = $aircraft = $notrack = $stealth = $bitfield_id[2] = 0;


        if ($notrack) //redundant but still...
        {
            $this->debug('aircraft should not be tracked!');
            return true;
        }

        $device_id = $bitfield_id[2];

        $datetime = new \DateTime('NOW',new \DateTimeZone('UTC'));
        $datetime->setTime($APRS[1],$APRS[2],$APRS[3]);

        $lat_deg = $APRS[4];
        $lat_min = $APRS[5] . $precision[1]; //concat, don't add!!
        $lat = floatval($lat_deg) + ( floatval($lat_min) / 60 );
        $lat_direction = $APRS[6];
        if ($lat_direction == 'S'){
            $lat *= -1;
        }

        $lon_deg = $APRS[8];
        $lon_min = $APRS[9] . $precision[2]; //concat, don't add!!
        $lon = floatval($lon_deg) + ( floatval($lon_min) / 60 );
        $lon_direction = $APRS[10];
        if ($lon_direction == 'W'){
            $lon *= -1;
        }

        $output = array(
            $header['src'], // APRS Callsign
            $datetime->format('Y-m-d H:i:s'), //formats the date to current timezone, which is fine for MySQL DateTime (assuming MySQL and PHP timezone are equivalent)
            $lat,
            $lon,
            $altitude,
            $header['path'][2], //receiver
            $course,
            $speed,
            $device,
            $aircraft,
            $notrack,
            $stealth,
            $device_id,
            isset($climbrate[1])?$climbrate[1]:'', //the isset construction is to prevent illegal offset warnings
            isset($rotation[1])?$rotation[1]:'',   // these values are not set if the log is not an airplane
            isset($signaltonoise[1])?$signaltonoise[1]:'',
            isset($biterrors[1])?$biterrors[1]:'',
            isset($freqoffset[1])?$freqoffset[1]:'',
            $data,
        );

        $this->buffer[] = $output;
        $this->debug('added to buffer: '. implode($output,' / '));
        return true;
    }
    function savePositions($timer=10)
    {
        if (time() - $this->lastsave < $timer) {
            //not time to save yet
            $this->debug('in savePositions() - not time to save yet');
            return;
        }

        if (count($this->buffer) == 0) {
            //no positions in buffer yet
            $this->debug('in savePositions() - no positions in buffer');
            return;
        }

        $this->debug('in savePositions() - trying to save buffer to db');

        $params = [];
        $qm_array = [];
        foreach ($this->buffer as $key => $value)
        {
            $params = array_merge($params, $value);
            $qm_array[] = '(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
            unset($this->buffer[$key]);
        }
        $qm = implode(',',$qm_array);
        $q = 'INSERT INTO ogn_logs (
          aprs_callsign,
          log_time,
          latitude,
          longitude,
          altitude,
          receiver,
          course,
          speed,
          device_type,
          aircraft_category,
          notrack,
          stealth,
          device_id,
          climbrate,
          rotation,
          signaltonoise,
          biterrors,
          freqency_offset,
          raw
          ) VALUES '. $qm;
        $statement = $this->db->prepare($q);
        if ($statement->execute($params))
        {
            $this->debug("buffer saved to DB");
            $this->debug('used query: ' . $q);
            $this->lastsave = time();
            return true;
        }
        else
        {
            $this->debug("buffer not saved");
            return true;
        }
    }

    function pdo_connect($dbname,$dbuser,$dbpass,$dbhost='localhost')
    {
        try
        {
            $this->db = new \PDO("mysql:host=$dbhost;dbname=$dbname;charset=utf8", $dbuser, $dbpass);
            if ($this->debug) {
                $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            }
        }
        catch(\PDOException $e)
        {
            $this->debug( "Could not connect to database"); //user friendly message
            echo $e->getMessage();
        }
    }

    function debug($str)
    {
        if($this->debug) {
            echo "OgnPosition: $str\n";
        }
    }
    function getFilter()
    {
        $q = 'SELECT aprs_callsign FROM ogn_airplanes WHERE tracked = 1';
        if ($statement = $this->db->query($q))
        {
            $airplanes = $statement->fetchAll(\PDO::FETCH_COLUMN);
            return 'b/' . implode('/', $airplanes);
        }
        else
            return '';
    }

    function updateAirplaneTable($new_registrations = [])
    {
        $known_aircraft = [];

        //add new registrations to known aircraft
        foreach ($new_registrations as $row)
        {
            //canonicalize registration for matching purposes
            $known_aircraft[] = strtoupper(str_replace([' ', '_', '-'], '', $row));
        }

        //add existing registrations to known aircraft
        $q = 'SELECT registration_canonical FROM ogn_airplanes';
        foreach ($this->db->query($q) as $row)
        {
            $known_aircraft[] = $row['registration_canonical'];
        }

        //get a copy of the DDB
        $ddb_url = 'http://ddb.glidernet.org/download?j=1';
        $ddb = json_decode(file_get_contents($ddb_url), true);

        //prepare the update query
        $q = 'REPLACE INTO ogn_airplanes (
        device_type,
        device_id,
        aprs_callsign,
        aircraft_model,
        registration,
        callsign,
        tracked,
        identified,
        registration_canonical
        )
        VALUES (?,?,?,?,?,?,?,?,?)';
        $stmt = $this->db->prepare($q);

        //cycle through the DDB to find matches for our known aircraft
        foreach ($ddb['devices'] as $ddb_device)
        {
            $ddb_acreg_canonical = strtoupper(str_replace([' ', '_', '-'], '', $ddb_device['registration']));

            if (!in_array($ddb_acreg_canonical, $known_aircraft))
            {
                //no match, skip to next iteration
                continue;
            }

            //match found!

            //compose APRS callsign
            switch ($ddb_device['device_type'])
            {
                case 'O':
                    $aprs_callsign = 'OGN' . $ddb_device['device_id'];
                    break;
                case 'I':
                    $aprs_callsign = 'ICA' . $ddb_device['device_id'];
                    break;
                case 'F':
                default:
                    $aprs_callsign = 'FLR' . $ddb_device['device_id'];
                    break;
            }

            //insert this record into database
            $params = array(
                $ddb_device['device_type'],
                $ddb_device['device_id'],
                $aprs_callsign,
                $ddb_device['aircraft_model'],
                $ddb_device['registration'],
                $ddb_device['cn'],
                ($ddb_device['tracked'] == 'Y'),
                ($ddb_device['identified'] == 'Y'),
                $ddb_acreg_canonical,
            );
            $stmt->execute($params);
        }

        $this->debug("ddb update completed");
    }
}
