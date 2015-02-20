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
		if(!empty($individual[0]->mcname)){
			$profile = ProfileUtils::getProfile($individual[0]->mcname);
		} else {
			$profile = ProfileUtils::getProfile($individual[0]->username);
		}
		$result = $profile->getProfileAsArray();
		$queries->update("users", $individual[0]->id, array(
			"uuid" => $result['uuid']
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