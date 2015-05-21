<?php
/* 
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */
 
// Some initial checks..
if(!isset($page) || !isset($queries) || !$user->isAdmLoggedIn() || $user->data()->group_id != 2 || !isset($version) || !isset($need_update) || ($version == $need_update)){
	die('Error - step 1. Please ensure you are logged in, you are running this script from the admin panel, and that your version is not already up to date.');
}

// Proceed with the upgrade
if($version == 0.1){
	// Check to see if there's already a database entry for this update somehow
	$query = $queries->getWhere("settings", array("name", "=", "store_type"));
	if(count($query)){
		die('Error - database is already up to date!');
	}
	
	// Insert new store type row into database settings table
	$queries->create("settings", array(
		"name" => "store_type",
		"value" => "bc"
	));
	
	// Create table for donation categories, which will be implemented in a future release
	$data = $queries->createTable("donation_categories", " `id` int(11) NOT NULL AUTO_INCREMENT, `name` varchar(64) NOT NULL, `cid` int(11) NOT NULL, `order` int(11) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
	
	// Add column to donation packages which will contain the URL for the package (for MM)
	$data = $queries->alterTable("donation_packages", "url", "VARCHAR(255) NOT NULL");
	
	// Update version name
	$queries->update("settings", 30, array(
		"value" => "0.2"
	));
	$queries->update("settings", 32, array(
		"value" => "false"
	));
}

if($version == 0.2){
	// Check to see if there's already a database entry for this update
	if($queries->tableExists('donation_categories') == false){
		$query = $queries->getWhere("settings", array("name", "=", "store_type"));
		if(!count($query)){
			// Insert new store type row into database settings table
			$queries->create("settings", array(
				"name" => "store_type",
				"value" => "bc"
			));
		}
		
		// Create table for donation categories, which will be implemented in a future release
		$data = $queries->createTable("donation_categories", " `id` int(11) NOT NULL AUTO_INCREMENT, `name` varchar(64) NOT NULL, `cid` int(11) NOT NULL, `order` int(11) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
		
		// Add column to donation packages which will contain the URL for the package (for MM)
		$data = $queries->alterTable("donation_packages", "url", "VARCHAR(255) NOT NULL");
		
		// Update version name
		$queries->update("settings", 30, array(
			"value" => "0.2.1"
		));
		$queries->update("settings", 32, array(
			"value" => "false"
		));

	} else {
		// No need to take action
		// Update version name
		$queries->update("settings", 30, array(
			"value" => "0.2.1"
		));
		$queries->update("settings", 32, array(
			"value" => "false"
		));
	}
}

if($version == '0.2.1'){
	// No database changes needed
	// Update version name
	$queries->update("settings", 30, array(
		"value" => "0.2.2"
	));
	$queries->update("settings", 32, array(
		"value" => "false"
	));
}

if($version == '0.2.2'){
	// No database changes needed
	// Update version name
	$queries->update("settings", 30, array(
		"value" => "0.3.0"
	));
	$queries->update("settings", 32, array(
		"value" => "false"
	));
}

if($version == '0.3.0'){
	// No database changes needed
	// Update version name
	$queries->update("settings", 30, array(
		"value" => "0.3.1"
	));
	$queries->update("settings", 32, array(
		"value" => "false"
	));
}
?>