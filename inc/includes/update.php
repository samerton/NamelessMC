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

if($version == '0.3.1'){
	// Database changes:
	// Create staff apps questions table
	$data = $queries->createTable("staff_apps_questions", " `id` int(11) NOT NULL AUTO_INCREMENT, `type` int(11) NOT NULL, `name` varchar(16) NOT NULL, `question` varchar(256) NOT NULL, `options` text, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");

	// Create staff apps replies table
	$data = $queries->createTable("staff_apps_replies", " `id` int(11) NOT NULL AUTO_INCREMENT, `uid` int(11) NOT NULL, `time` int(11) NOT NULL, `content` mediumtext NOT NULL, `status` int(11) NOT NULL DEFAULT '0', PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");

	// Create staff apps comments table
	$data = $queries->createTable("staff_apps_comments", " `id` int(11) NOT NULL AUTO_INCREMENT, `aid` int(11) NOT NULL, `uid` int(11) NOT NULL, `time` int(11) NOT NULL, `content` mediumtext NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
	
	// Add column to private messages users table which will contain whether they have read a PM or not
	$data = $queries->alterTable("private_messages_users", "`read`", "tinyint(4) NOT NULL DEFAULT '0'");
	
	// Add row to settings table regarding whether moderators can view staff apps or not
	$queries->create("settings", array(
		"name" => "mods_view_apps",
		"value" => "false"
	));
	
	// Update version name
	$queries->update("settings", 30, array(
		"value" => "0.3.2"
	));
	$queries->update("settings", 32, array(
		"value" => "false"
	));
	
	// Modify init.php
	$insert = file_get_contents('inc/init.php');
	$insert = rtrim($insert, "}");
	
	$insert .= '
	
	/*
	 *  Are there any unread private messages for the user?
	 */
	
	if($user->isLoggedIn()){
		if($user->getUnreadPMs($user->data()->id) != 0){
			$unread_pms = true;
		} else {
			$unread_pms = false;
		}
	}
	
	/*
	 *  Are staff applications enabled, and if so, are there any open applications?
	 */
	 
	$staff_enabled = $queries->getWhere("settings", array("name", "=", "staff_apps"));
	 
	if($staff_enabled[0]->value == \'true\' && ($user->data()->group_id == 3 || $user->data()->group_id == 2)){
		// First, check if moderators can view apps or not
		$allow_moderators = $queries->getWhere(\'settings\', array(\'id\', \'=\', \'37\'));
		$allow_moderators = $allow_moderators[0]->value;
		
		// Get any open applications
		$open_apps = $queries->getWhere(\'staff_apps_replies\', array(\'status\', \'=\', 0));
		
		if(count($open_apps)){
			// Moderators
			if($allow_moderators === "true"){
				if($user->data()->group_id == 3){
					$open_apps = true;
				}
			}
			
			// Admins
			if($user->data()->group_id == 2){
				$open_apps = true;
			}
		} else {
			// No apps open
			$open_apps = false;
		}
	}
	
	/*
	 *  Get version number
	 */
	 
	$version = $queries->getWhere(\'settings\', array(\'id\', \'=\', 30));
	$version = $version[0]->value;
	
}
	';
	
	if(is_writable('inc/init.php')){
		$file = fopen('inc/init.php','w');
		fwrite($file, $insert);
		fclose($file);
	} else {
		echo 'Error updating: your <strong>inc/init.php</strong> file is not writable.';
		die();
	}
	
	
}

if($version == '0.3.2'){
	// Insert new external query row into database settings table
	$queries->create("settings", array(
		"name" => "external_query",
		"value" => "false"
	));
	
	// Insert new row into database regarding moderators accepting or denying applications
	$queries->create("settings", array(
		"name" => "mods_accept_apps",
		"value" => "false"
	));
	
	// Update version name
	$queries->update("settings", 30, array(
		"value" => "0.3.3"
	));
	$queries->update("settings", 32, array(
		"value" => "false"
	));
}
?>