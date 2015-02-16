<?php

session_start();

// Uncomment the following 3 lines to enable error reporting, and comment the 4th and 5th lines
//ini_set('display_startup_errors',1);
//ini_set('display_errors',1);
//error_reporting(-1);
error_reporting(0);
ini_set('display_errors', 0);

if(!isset($page)){
	die();
}

/*
 *  Autoload classes
 */

if($path === ""){
	spl_autoload_register(function($class) {
		require_once 'inc/classes/' . $class . '.php';
	});
	require_once 'inc/functions/sanitize.php';
} else if($path === "../../../"){
	spl_autoload_register(function($class) {
		require_once '../../classes/' . $class . '.php';
	});
	require_once '../../functions/sanitize.php';
}

if($page !== "install"){
	/*
	 * Check cookies to see if the user has ticked "remember me" whilst logging in, if so log them in
	 */

	if(Cookie::exists(Config::get('remember/cookie_name')) && !Session::exists(Config::get('session/session_name'))) {
		$hash = Cookie::get(Config::get('remember/cookie_name'));
		$hashCheck = DB::getInstance()->get('users_session', array('hash', '=', $hash));
		
		if($hashCheck->count()){
			$user = new User($hashCheck->first()->user_id);
			$user->login();
		}
	}

	/*
	 *  Initialise users
	 */
	
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
	
	/*
	 *  Initialise queries
	 */
	
	if(!isset($queries)){
		$queries = new Queries();
	}

	/*
	 * Install file check 
	 */
	 
	if($page === "admin"){
		clearstatcache();
		if(file_exists($path . "install.php")){
			Session::flash('adm-alert', '<div class="alert alert-danger">The installation file (install.php) exists. Please remove it!</div>');
		}
	}

	/*
	 * Maintenance mode 
	 * TODO: Tidy up the message
	 */
	 
	if($page === "forum"){
		$maintenance = $queries->getWhere("settings", array("name", "=", "maintenance"));
		if($maintenance[0]->value === "true"){
			if($user->data()->group_id != 2){
				echo 'Sorry, the forums are in maintenance mode. Please head back to the <a href="../">homepage</a>.';
				die();
			}
		}
	}
	
	/*
	 *  Get the sitename
	 */
	 
	$sitename = $queries->getWhere("settings", array("name", "=", "sitename"));
	$sitename = htmlspecialchars($sitename[0]->value);
	
}