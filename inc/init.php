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

// Redirect to a URL ending with "/", without it, pagination breaks
if(substr($_SERVER['REQUEST_URI'], -1) !== '/' && !strpos($_SERVER['REQUEST_URI'], '?')){
	$parts = explode('?', $_SERVER['REQUEST_URI'], 2);
    Redirect::to($parts[0] . '/' . (isset($parts[1]) ? '?' . $parts[1] : ''));
	die();
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
	 *  Are there any open reports for moderators?
	 */
	
	if($user->isLoggedIn() && ($user->data()->group_id == 2 || $user->data()->group_id == 3)){
		$reports = $queries->getWhere("reports", array('status' , '<>', '1'));
		if(count($reports)){
			$reports = true; // Open reports
		} else {
			$reports = false; // No open reports
		}
	}
	
	/*
	 *  Get the sitename
	 */
	 
	$sitename = $queries->getWhere("settings", array("name", "=", "sitename"));
	$sitename = htmlspecialchars($sitename[0]->value);
	
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
	 
	if($staff_enabled[0]->value == 'true' && ($user->data()->group_id == 3 || $user->data()->group_id == 2)){
		// First, check if moderators can view apps or not
		$allow_moderators = $queries->getWhere('settings', array('id', '=', '37'));
		$allow_moderators = $allow_moderators[0]->value;
		
		// Get any open applications
		$open_apps = $queries->getWhere('staff_apps_replies', array('status', '=', 0));
		
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
	 
	$version = $queries->getWhere('settings', array('id', '=', 30));
	$version = $version[0]->value;
	
}