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

        //           hours_____minutes___seconds___ lat-deg___lat-min_____________dir___ lon-deg___lon-min_____________dir___    altitude__   prec___prec__
        preg_match('/([0-9]{2})([0-9]{2})([0-9]{2})h([0-9]{2})([0-9]{2}\.[0-9]{2})([NS]).([0-9]{3})([0-9]{2}\.[0-9]{2})([EW]).*\/A=([0-9]{6})/',$data,$matches);
        //           12        34        56        h01        23       . 45       N     /001       23       . 45       E        /A=012345

        preg_match('/!W([0-9])([0-9])!/',$data,$precision);

        if (empty($precision)) {
            //enhanced precision string not found
            $precision = ['', '', ''];
        }
        $datetime = new \DateTime('NOW',new \DateTimeZone('UTC'));
        $datetime->setTime($matches[1],$matches[2],$matches[3]);

        $lat_deg = $matches[4];
        $lat_min = $matches[5] . $precision[1]; //concat, don't add!!
        $lat = floatval($lat_deg) + ( floatval($lat_min) / 60 );
        $lat_direction = $matches[6];
        if ($lat_direction == 'S'){
            $lat *= -1;
        }

        $lon_deg = $matches[7];
        $lon_min = $matches[8] . $precision[2]; //concat, don't add!!
        $lon = floatval($lon_deg) + ( floatval($lon_min) / 60 );
        $lon_direction = $matches[9];
        if ($lon_direction == 'W'){
            $lon *= -1;
        }
        $altitude = intval($matches[10]);
        $output = array(
            $header['src'],
            $datetime->format('Y-m-d H:i:s'), //formats the date to current timezone, which is fine for MySQL DateTime (assuming MySQL and PHP timezone are equivalent)
            $lat,
            $lon,
            $altitude,
            $header['path'][2],
        );
        $this->buffer[] = $output;
        $this->debug('added to buffer: '. implode($output,' / '));
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
            $qm_array[] = '(?,?,?,?,?,?)';
            unset($this->buffer[$key]);
        }
        $qm = implode(',',$qm_array);
        $q = 'INSERT INTO ogn_logs (flarm_id, log_time, latitude, longitude, altitude, receiver ) VALUES '. $qm;
        $statement = $this->db->prepare($q);
        $statement->execute($params);
        $this->lastsave = time();
        $this->debug("buffer saved to DB");
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
            echo "An Error occured!"; //user friendly message
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
        $q = 'SELECT flarm_id FROM airplanes';
        if ($statement = $this->db->query($q))
        {
            $airplanes = $statement->fetchAll(\PDO::FETCH_COLUMN);
            return 'b/' . implode('/', $airplanes);
        }
        else
            return '';
    }
}
