<?php 
/*
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

// Set the page name for the active link in navbar
$page = "forum";

require('inc/includes/html/library/HTMLPurifier.auto.php'); // HTML Purifier

$forum = new Forum();

// User must be logged in to proceed
if(!$user->isLoggedIn()){
	Redirect::to('/forum');
	die();
}

// Ensure a topic is set via URL parameters
if(isset($_GET["tid"])){
	if(is_numeric($_GET["tid"])){
		$topic_id = $_GET["tid"];
	} else {
		Redirect::to('/forum/error/?error=not_exist');
		die();
	}
} else {
	Redirect::to('/forum/error/?error=not_exist');
	die();
}

if($user->data()->group_id == 2 || $user->data()->group_id == 3){
	// Does the thread exist?
	$topic = $queries->getWhere("topics", array("id", "=", $topic_id));
	if(count($topic)){
		// Is it sticky already?
		if($topic[0]->sticky == 0){
			$sticky = 1;
			$status = "now a sticky thread";
		} else {
			$sticky = 0;
			$status = "no longer a sticky thread";
		}

		$queries->update("topics", $topic_id, array(
			"sticky" => $sticky
		));

		Session::flash('success_post', '<div class="alert alert-info alert-dismissable"> <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>Thread is ' . $status . '</div>');
	}
} 

Redirect::to("/forum/view_topic/?tid=" . $topic_id);
die();


?>