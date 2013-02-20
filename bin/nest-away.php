#!/usr/bin/php
<?php
require_once('nest.class.php');

define('USERNAME', '');
define('PASSWORD', '');

$nest = new Nest();

$infos = $nest->getDeviceInfo();

if (count($argv) == 1) {

	if ($infos->current_state->auto_away > -1) {
		if (strpos($infos->current_state->mode,"away") !== FALSE) {
			$away=1;
		}
	} else {
		$away=$infos->current_state->manual_away;
	}

	if (isset($away) && ($away > 0)) {
		echo "Away\n";
	} else {
		echo "Home\n";
	}
} else if ($argv[1]=="Away") {
	echo "Setting Away Mode\n";
	// Set Away mode
	$nest->setAway(TRUE);
	//Why waste water in humidifier if I'm not home?
//	$nest->setHumidity(20);
} else if ($argv[1]=="Home") {
	echo "Setting Home\n";
	// Turn off Away mode
	$nest->setAway(FALSE);
	//Make house comfortable
//	$nest->setHumidity(35);
} else {
	echo "Error\n";
	exit(1);
}

exit(0);

?>
