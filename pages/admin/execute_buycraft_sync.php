<?php 
/*
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */
 
// Admin check
if($user->isAdmLoggedIn()){
	// Is authenticated
	if($user->data()->group_id != 2){
		Redirect::to('/');
		die();
	}
} else {
	if(isset($_GET["key"])){
		$buycraft_code = $queries->getWhere("settings", array("name", "=", "buycraft_sync_key"));
		if($_GET["key"] !== $buycraft_code[0]->value){
			Redirect::to('/');
			die();
		}
	} else {
		Redirect::to('/');
		die();
	}
}
 
require('inc/integration/buycraft.php');

/*
 * LATEST DONORS SYNC
 */

foreach($buycraft["payload"] as $donation){
	$existing = $queries->getWhere("buycraft_data", array("time", "=", date('Y-m-d H:i:s', $donation["time"])));
	if(!count($existing)){
		try {
			$queries->create("buycraft_data", array(
				"time" => date('Y-m-d H:i:s', $donation["time"]),
				"uuid" => $donation["uuid"],
				"ign" => $donation["ign"],
				"price" => $donation["price"],
				"package" => $donation["packages"][0]
			));
		} catch(Exception $e){
			die($e->getMessage());
		}
	}
}

/*
 * GROUP SYNC
 * 1 - import donor groups from database
 * 2 - loop through donors
 * 3 - for each donor, check if they already have a DONOR/STANDARD group (ie not staff)
 * 4 - if the user is a staff member, do nothing, else:
 *       a - check if the user is a donor already, if so:
 *       		i - add most valuable package to the user
		 b - if not, add the most valuable package (if they've bought multiple) to the user
 */

$donor_groups = $queries->getWhere("groups", array("buycraft_id", "<>", "NULL"));

foreach($buycraft["payload"] as $donor){
	$donor_user = $queries->getWhere("users", array("uuid", "=", $donor["uuid"]));
	if(count($donor_user)){
		if($donor_user[0]->group_id == 2 || $donor_user[0]->group_id == 3){ 
			// Don't do anything as they're a staff member - we want them to keep their staff rank
		} else {
			if(count($donor_user)){ // if the user has registered..
				if(count($donor["packages"]) === 1){
					$donor_group = $queries->getWhere("groups", array("buycraft_id", "=", $donor["packages"][0]));
					$package_group_id = $donor_group[0]->id;
					if($donor_user[0]->group_id < $package_group_id){
						try {
							$queries->update("users", $donor_user[0]->id, array(
								'group_id' => $package_group_id
							));
						} catch(Exception $e){
							die($e->getMessage());
						}
					}
				}
				/*
				 * TODO: Run check if user has purchased multiple packages
				 */
			}
		}
	}
}

return "true";
?>