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
// This is where the upgrade script will run
?>