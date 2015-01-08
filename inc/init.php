<?php
session_start();

// Uncomment the following 3 lines to enable error reporting
//ini_set('display_startup_errors',1);
//ini_set('display_errors',1);
//error_reporting(-1);

if(!isset($page)){
	die();
}

/* 
 *  Define global variables
 */

$GLOBALS['config'] = array(
	'mysql' => array(
		'host' => '', // Web server database IP (Likely to be 127.0.0.1)
		'username' => '', // Web server database username
		'password' => '', // Web server database password
		'db' => '' // Web server database name
	),
	'mysql_mc' => array( 
		'host' => '', // Minecraft database IP
		'username' => '', // Minecraft database username
		'password' => '', // Minecraft database password
		'db' => '' // Minecraft database name
	),
	'mysql_bungee' => array( 
		'host' => '', // Bungee database IP
		'username' => '', // Bungee database username
		'password' => '', // Bungee database password
		'db' => '' // Bungee database name
	),
	'remember' => array(
		'cookie_name' => '', // Name for website cookies
		'cookie_expiry' => 604800
	),
	'session' => array(
		'session_name' => 'user',
		'token_name' => 'token'
	)
);

/*
 *  Autoload classes, include sanitize function
 */ 

spl_autoload_register(function($class) {
	require_once 'inc/classes/' . $class . '.php';
});
require_once 'inc/functions/sanitize.php';

/*
 *  Perform page checks
 */

if(strtolower($page) !== "install"){

	/*
	 * Check cookies to see if the user has ticked "remember me" whilst logging in, if so log them in
	 */

	if(Cookie::exists(Config::get('remember/cookie_name')) && !Session::exists(Config::get('session/session_name'))) {
		$hash = Cookie::get(Config::get('remember/cookie_name'));
		$hashCheck = DB::getInstance()->get('users_session', array('hash', '=', $hash));
		
		if ($hashCheck->count()) {
			$user = new User($hashCheck->first()->user_id);
			$user->login();
		}
	}

	if(!isset($user)){
		$user = new User();
	}

	/*
	 * If the user is logged in, update their "last IP" field for moderation purposes
	 */
	
	if($user->isLoggedIn()){
		$ip = $user->getIP();
		if(filter_var($ip, FILTER_VALIDATE_IP)){
			$user->update(array(
				'lastip' => $ip
			));
		}
	}
	
	if(!isset($queries)){
		$queries = new Queries();
	}

}

/* 
 * Install file check 
 */ 
 
if(strtolower($page) === "admin"){
	clearstatcache();
	if(file_exists("install.php")){
		Session::flash('adm-alert', '<div class="alert alert-danger">The installation file (install.php) exists. Please remove it!</div>');
	}
}

/*
 * Maintenance mode 
 * TODO: Tidy up the message
 */ 
if(strtolower($page) === "forum"){
	if($queries->getWhere("settings", array("name", "=", "maintenance"))[0]->value === "true"){
		if($user->data()->group_id != 2){
			echo 'Sorry, the forums are in maintenance mode. Please head back to the <a href="../">homepage</a>.';
			die();
		}
	}
}