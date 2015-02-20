<?php 
/* 
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

require('inc/integration/uuid.php');

if(isset($_GET["uid"])){
	$individual = $queries->getWhere("users", array("id", "=", $_GET["uid"]));
	
	if(count($individual)){
		$uuid = $individual[0];
		$uuid = $uuid->uuid;
		
		$profile = ProfileUtils::getProfile($uuid);
		
		$result = $profile->getUsername();
		
		$queries->update("users", $individual[0]->id, array(
			"mcname" => $result
		));
	}

	Session::flash('adm-users', '<div class="alert alert-info">  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>Task run successfully.</div>');
	Redirect::to('/admin/users/?user=' . $individual[0]->id);
	die();
} else {
	// todo: sync all mcnames for cron job
}

return true;
?>