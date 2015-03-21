<?php
/*
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */
 
if(!isset($_GET['c'])){
	Redirect::to('/');
	die();
} else {
	$check = $queries->getWhere('users', array('reset_code', '=', $_GET['c']));
	if(count($check)){
		$queries->update('users', $check[0]->id, array(
			'reset_code' => '',
			'active' => 1
		));
		Session::flash('info', '<div class="alert alert-info">Thanks for registering! You can now log in.</div>');
		Redirect::to('/');
		die();
	} else {
		Session::flash('error', '<div class="alert alert-danger">Error processing your request.</div>');
		Redirect::to('/');
		die();
	}
}
?>