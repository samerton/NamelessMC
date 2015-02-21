<?php
/* 
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

if(!isset($user)){
	$user = new User();
}

if($user->isLoggedIn()){
	$user->admLogout();
	$user->logout();
	
	Session::flash('home', '<div class="alert alert-info alert-dismissible">  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>You have been logged out.</div>');
	Redirect::to('/');
} else {
	Redirect::to('/');
}

die(); // Ensure the script is killed