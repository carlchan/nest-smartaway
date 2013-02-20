#!/usr/bin/php
<?PHP
/////////////////////////////////////////////////
// User editable section //
/////////////////////////////////////////////////

// You can either enter your username/password here, or specify it on the command line
//	$iCloudUser='';
//	$iCloudPassword='';

// Enter your home coordinates here
// I used this: http://itouchmap.com/latlong.html
	$home = array(
		'latitude' => '',
		'longitude' => ''
	);

////////////////////////////////////////////////
////////// Do not edit below this! /////////////
////////////////////////////////////////////////

//Allows either hardcoded username/password or accept it from command line
if (!isset($iCloudUser) || !isset($iCloudPassword)) {
	if (count($argv)==3) {
		$iCloudUser=$argv[1];
		$iCloudPassword=$argv[2];
	} else {
		echo "Username and password required\n";
		exit(1);
	}
}
//////////////

// from http://www.paul-norman.co.uk/2009/07/using-google-to-calculate-driving-distance-time-in-php/
function get_driving_information($start, $finish, $raw = false)
{
    if(strcmp($start, $finish) == 0)
    {
        $time = 0;
        if($raw)
        {
            $time .= ' seconds';
        }

        return array('distance' => 0, 'time' => $time);
    }

    $start  = urlencode($start);
    $finish = urlencode($finish);

    $distance   = 'unknown';
    $time       = 'unknown';

    $url = 'http://maps.googleapis.com/maps/api/directions/xml?origin='.$start.'&destination='.$finish.'&sensor=false';
    if($data = file_get_contents($url))
    {
        $xml = new SimpleXMLElement($data);

        if(isset($xml->route->leg->duration->value) AND (int)$xml->route->leg->duration->value > 0)
        {
            if($raw)
            {
                $distance = (string)$xml->route->leg->distance->text;
                $time     = (string)$xml->route->leg->duration->text;
            }
            else
            {
                $distance = (int)$xml->route->leg->distance->value / 1000; 
                $time     = (int)$xml->route->leg->duration->value;
            }
        }
        else
        {
            throw new Exception('Could not find that route');
        }

        return array('distance' => $distance, 'time' => $time);
    }
    else
    {
        throw new Exception('Could not resolve URL');
    }
}

/////////////
	require 'class.sosumi.php';

	try {
		$ssm = new Sosumi($iCloudUser, $iCloudPassword);
	} catch (Exception $e) {
		echo "Unable to log in to iCloud\n";
		exit(1);
	}

// Search for a valid phoneid
// required adding a different exception to class.sosumi.php to differentiate between
// "phone not found in valid time" and "phone id does not exist"
	$phoneid=0;
	while (!isset($phone)) {
		try {
			$phone=$ssm->locate($phoneid);
		} catch (Exception $e) {
			if (strpos($e->getMessage(),'Invalid') !== false) {
				break;
			} else {
				$phoneid++;
			}
		}
	}
	if (!isset($phone)) {
		echo 'No phones found!\n';
		exit(1);
	}

//calculate distance
// from http://www.movable-type.co.uk/scripts/latlong.html
	$R=6371;
	$dLat=deg2rad($home['latitude']-$phone['latitude']);
	$dLong=deg2rad($home['longitude']-$phone['longitude']);
	$radHomeLat=deg2rad($home['latitude']);
	$radPhoneLat=deg2rad($phone['latitude']);
	$a=(pow(sin($dLat/2),2) + pow(sin($dLong/2),2) * cos($radHomeLat) * cos($radPhoneLat));
	$c=(2 * atan2(sqrt($a),sqrt(1-$a)));
	$d=round(($R * $c),2);

	$info=get_driving_information(($phone['latitude'].",".$phone['longitude']),($home['latitude'].",".$home['longitude']));

// This gives direct line distance from Home
//	echo($d."KM away\n");

// This gives Google estimates of driving distance in KM
// or driving time in minutes
//	echo(($info['distance'])." KM away driving distance\n");

	echo(round(($info['time']/60),0)."\n");
?>
