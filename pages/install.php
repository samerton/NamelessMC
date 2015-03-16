<?php
/* 
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */
 
require('inc/includes/password.php'); // Require password compatibility
require('inc/integration/uuid.php'); // Require UUID integration
 
if(isset($_GET["step"])){
	$step = strtolower(htmlspecialchars($_GET["step"]));
} else {
	$step = "start";
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="/assets/favicon.ico">

    <title>NamelessMC &bull; Install</title>

    <!-- Bootstrap core CSS -->
    <link href="/assets/css/6.css" rel="stylesheet">

  </head>

  <body>
	<div class="container">
	  <br />
	  <ul class="nav nav-tabs">
	    <li <?php if($step == "start"){ ?>class="active"<?php } else { ?>class="disabled"<?php } ?>><a>Start</a></li>
		<li <?php if($step == "requirements"){ ?>class="active"<?php } else { ?>class="disabled"<?php } ?>><a>Requirements</a></li>
		<li <?php if($step == "configuration"){ ?>class="active"<?php } else { ?>class="disabled"<?php } ?>><a>Configuration</a></li>
	    <li <?php if($step == "database"){ ?>class="active"<?php } else { ?>class="disabled"<?php } ?>><a>Database</a></li>
		<li <?php if($step == "settings" || $step == "settings_extra"){ ?>class="active"<?php } else { ?>class="disabled"<?php } ?>><a>Settings</a></li>
		<li <?php if($step == "account"){ ?>class="active"<?php } else { ?>class="disabled"<?php } ?>><a>Account</a></li>
		<li <?php if($step == "convert"){ ?>class="active"<?php } else { ?>class="disabled"<?php } ?>><a>Convert</a></li>
		<li <?php if($step == "finish"){ ?>class="active"<?php } else { ?>class="disabled"<?php } ?>><a>Finish</a></li>
	  </ul>

	  <?php
	  if($step === "start"){
	  ?>
	  <h2>Welcome to NamelessMC</h2>
	  This page will guide you through the process of installing the NamelessMC website package.<br /><br />
	  You will need the following:
	  <ul>
	    <li>A MySQL database on the webserver</li>
		<li>PHP version 5.3+</li>
	  </ul>
	  <br />
	  The following are not required, but are recommended:
	  <ul>
	    <li>A MySQL database for a Bungee instance <a data-toggle="modal" href="#bungee_plugins">(Supported Plugins)</a></li>
		<li>A MySQL database for your Minecraft servers <a data-toggle="modal" href="#mc_plugins">(Supported Plugins)</a></li>
	  </ul>
	  <br />
	  We can convert from:
	  <ul>
	    <li>ModernBB</li>
		<li>Wordpress and bbPress</li>
		<li>phpBB</li>
		<li>XenForo</li>
		<li>IPBoard</li>
		<li>MyBB</li>
		<li>Vanilla</li>
	  </ul>
	  <br />
	  
	  <button type="button" onclick="location.href='/install/?step=requirements'" class="btn btn-primary">Proceed &raquo;</button>
	  
	  <?php 
	  } else if($step === "requirements"){
		$error = '<p style="display: inline;" class="text-danger"><span class="glyphicon glyphicon-remove-sign"></span></p><br />';
		$success = '<p style="display: inline;" class="text-success"><span class="glyphicon glyphicon-ok-sign"></span></p><br />';
	  ?>
	  <h2>Requirements</h2>
	  <?php
		if(version_compare(phpversion(), '5.3', '<')){
			echo 'PHP 5.3 - ' . $error;
			$php_error = true;
		} else {
			echo 'PHP 5.3 - ' . $success;
		}
		if(!extension_loaded('gd')){
			echo 'PHP GD Extension - ' . $error;
			$php_error = true;
		} else {
			echo 'PHP GD Extension - ' . $success;
		}
		if(!extension_loaded('PDO')){
			echo 'PHP PDO Extension - ' . $error;
			$php_error = true;
		} else {
			echo 'PHP PDO Extension - ' . $success;
		}
		if(!function_exists("mcrypt_encrypt")) {
			echo 'mcrypt - ' . $error;
			$php_error = true;
		} else {
			echo 'mcrypt - ' . $success;
		}
	  ?>
	  <br />
	  <?php
	    if(isset($php_error)){
	  ?>
	  <div class="alert alert-danger">You must be running at least PHP version 5.5 with the PDO and mcrypt extensions enabled in order to proceed with installation.</div>
	  <?php
		} else {
	  ?>
	  <button type="button" onclick="location.href='/install/?step=configuration'" class="btn btn-primary">Proceed &raquo;</button>
	  <?php
		}
	  } else if($step === "configuration"){
		if(Input::exists()){
			$validate = new Validate();
			$validation = $validate->check($_POST, array(
				'db_address' => array(
					'required' => true
				),
				'db_username' => array(
					'required' => true
				),
				'db_name' => array(
					'required' => true
				)
			));

			if($validation->passed()) {
				$db_password = "";
				$db_prefix = Input::get('db_prefix');
				$cookie_name = "nlmc";
				
				if(!empty($db_prefix) && substr($db_prefix, -1) !== "_"){
					$db_prefix .= "_";
				}
				
				$db_password = Input::get('db_password');
				
				if(!empty($db_password)){
					$db_password = Input::get('db_password');
				}
				
				$db_cookie = Input::get('cookie_name');
				
				if(!empty($db_cookie)){
					$cookie_name = Input::get('cookie_name');
				}

				/*
				 *  Test connection - use MySQLi here, as the config for PDO is not written
				 */
				$mysqli = new mysqli(Input::get('db_address'), Input::get('db_username'), $db_password, Input::get('db_name'));
				if($mysqli->connect_errno) {
					$mysql_error = $mysqli->connect_errno . ' - ' . $mysqli->connect_error;
				} else {
					/*
					 *  Write to config file
					 */
					$insert = 	'<?php' . PHP_EOL . 
								'$GLOBALS[\'config\'] = array(' . PHP_EOL . 
								'	"mysql" => array(' . PHP_EOL . 
								'		"host" => "' . Input::get('db_address') . '", // Web server database IP (Likely to be 127.0.0.1)' . PHP_EOL . 
								'		"username" => "' . Input::get('db_username') . '", // Web server database username' . PHP_EOL . 
								'		"password" => "' . $db_password . '", // Web server database password' . PHP_EOL . 
								'		"db" => "' . Input::get('db_name') . '", // Web server database name' . PHP_EOL .
								'		"prefix" => "' . $db_prefix . '" // Web server table prefix' . PHP_EOL .
								'	),' . PHP_EOL . 
								'	"remember" => array(' . PHP_EOL . 
								'		"cookie_name" => "' . $cookie_name . '", // Name for website cookies' . PHP_EOL . 
								'		"cookie_expiry" => 604800' . PHP_EOL . 
								'	),' . PHP_EOL . 
								'	"session" => array(' . PHP_EOL . 
								'		"session_name" => "user",' . PHP_EOL . 
								'		"admin_name" => "admin",' . PHP_EOL .
								'		"token_name" => "token"' . PHP_EOL . 
								'	)' . PHP_EOL . 
								');';
					
					if(is_writable('inc/init.php')){
						$config = file_get_contents('inc/init.php');
						$config = substr($config, 5);
						
						$file = fopen('inc/init.php','w');
						fwrite($file, $insert . $config);
						fclose($file);
						
						header('Location: /install/?step=database');
						die();
						
					} else {
						/*
						 *  File not writeable, display code to add to file manually
						 */
						$config = file_get_contents('inc/init.php');
						$config = substr($config, 5);
						$config = nl2br(htmlspecialchars($insert . $config));
						?>
	  Your <strong>inc/init.php</strong> file is not writeable. Please copy/paste the following into your <strong>inc/init.php</strong> file, overwriting all existing text.
	  <div class="well">
		<?php
		echo $config;
		?>
	  </div>
	  <a href="/install/?step=database" class="btn btn-primary">Continue</a>
						
      <hr>

      <footer>
        <p>&copy; NamelessMC <?php echo date("Y"); ?></p>
      </footer>
	</div> <!-- /container -->
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/bootstrap.min.js"></script>
  </body>
</html>
						<?php
						die();
					}
				}	
			} else {
				$errors = "";
				
				foreach($validation->errors() as $error){
					if(strstr($error, 'db_address')){
						$errors .= "Please input a database address<br />";
					}
					if(strstr($error, "db_username")){
						$errors .= "Please input a database username<br />";
					}
					if(strstr($error, "db_name")){
						$errors .= "Please input a database name<br />";
					}
				}
			}
		}
	  ?>
	  <h2>Configuration</h2>
	  <?php
		if(isset($errors)){
	  ?>
	  <div class="alert alert-danger">
	  <?php
		echo $errors;
	  ?>
	  </div>
	  <?php
		}
		if(isset($mysql_error)){
	  ?>
	  <div class="alert alert-danger">
	  <?php
		echo $mysql_error;
	  ?>
	  </div>
	  <?php
		}
	  ?>
	  <small><em>Fields marked with a * are required</em></small>
	  <form action="" method="post">
	    <div class="form-group">
	      <label for="InputDBIP">Database Address * </label>
		  <input type="text" class="form-control" name="db_address" id="InputDBIP" value="<?php echo Input::get('db_address'); ?>" placeholder="Database Address">
	    </div>
	    <div class="form-group">
		  <label for="InputDBUser">Database Username *</label>
		  <input type="text" class="form-control" name="db_username" id="InputDBUser" value="<?php echo Input::get('db_username'); ?>" placeholder="Database Username">
	    </div>
	    <div class="form-group">
		  <label for="InputDBPass">Database Password</label>
		  <input type="password" class="form-control" name="db_password" id="InputDBPass" placeholder="Database Password">
	    </div>
	    <div class="form-group">
		  <label for="InputDBName">Database Name *</label>
		  <input type="text" class="form-control" name="db_name" id="InputDBName" value="<?php echo Input::get('db_name'); ?>" placeholder="Database Name">
	    </div>
	    <div class="form-group">
		  <label for="InputDBPrefix">Table Prefix</label>
		  <input type="text" class="form-control" name="db_prefix" id="InputDBPrefix" value="<?php echo Input::get('db_prefix'); ?>" placeholder="Table Prefix">
	    </div>
	    <div class="form-group">
		  <label for="InputCookieName">Cookie Name</label>
		  <input type="text" class="form-control" name="cookie_name" id="InputCookieName" value="<?php echo Input::get('cookie_name'); ?>" placeholder="Cookie Name">
	    </div>
	    <input type="submit" class="btn btn-default" value="Submit">
	  </form>
	  <?php
	  } else if($step === "database"){
	  ?>
	  <h2>Database Initialisation</h2>
	  The installer will now initialise the database.<br /><br />
	  
	  <?php
		if(!isset($queries)){
			$queries = new Queries(); // Initialise queries
		}
		$prefix = Config::get('mysql/prefix');
		
		$queries->dbInitialise($prefix); // Initialise the database
		
	  ?>
	  <button type="button" onclick="location.href='/install/?step=settings'" class="btn btn-primary">Proceed &raquo;</button>
	  <?php
	  } else if($step === "settings"){
		if(Input::exists()){
			$validate = new Validate();
			$validation = $validate->check($_POST, array(
				'site_name' => array(
					'required' => true,
					'min' => 2,
					'max' => 1024
				),
				'outgoing_email' => array(
					'required' => true,
					'min' => 2,
					'max' => 1024
				)
			));
			
			if($validation->passed()) {
			
				if(!isset($queries)){
					$queries = new Queries(); // Initialise queries
				}
				
				$random = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 30);
				$uid = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 62);
				// Get current unix time
				$date = new DateTime();
				$date = $date->getTimestamp();
				
				$data = array(
					0 => array(
						'name' => 'sitename',
						'value' => htmlspecialchars(Input::get('site_name'))
					),
					1 => array(
						'name' => 'maintenance',
						'value' => 'false'
					),
					2 => array(
						'name' => 'vote',
						'value' => 'false'
					),
					3 => array(
						'name' => 'donate',
						'value' => 'false'
					),
					4 => array(
						'name' => 'stats',
						'value' => 'false'
					),
					5 => array(
						'name' => 'buycraft_key',
						'value' => 'null'
					),
					6 => array(
						'name' => 'youtube_url',
						'value' => 'null'
					),
					7 => array(
						'name' => 'twitter_url',
						'value' => 'null'
					),
					8 => array(
						'name' => 'gplus_url',
						'value' => 'null'
					),
					9 => array(
						'name' => 'fb_url',
						'value' => 'null'
					),
					10 => array(
						'name' => 'buycraft_sync_key',
						'value' => $random
					),
					11 => array(
						'name' => 'outgoing_email',
						'value' => htmlspecialchars(Input::get('outgoing_email'))
					),
					12 => array(
						'name' => 't_and_c',
						'value' => 'By registering on our website, you agree to the following:<p>This website uses "Nameless" website software. The "Nameless" software creators will not be held responsible for any content that may be experienced whilst browsing this site, nor are they responsible for any loss of data which may come about, for example a hacking attempt. The website is run independently from the software creators, and any content is the responsibility of the website administration.</p><p>You agree to be bound by our website rules and any laws which may apply to this website and your participation.</p><p>The website administration have the right to terminate your account at any time, delete any content you may have posted, and your IP address and any data you input to the website is recorded to assist the site staff with their moderation duties.</p><p>The site administration have the right to change these terms and conditions, and any site rules, at any point without warning. Whilst you may be informed of any changes, it is your responsibility to check these terms and the rules at any point.</p>'
					),
					13 => array(
						'name' => 'recaptcha',
						'value' => 'false'
					),
					14 => array(
						'name' => 'recaptcha_key',
						'value' => 'null'
					),
					15 => array(
						'name' => 'twitter_feed_id',
						'value' => 'null'
					),
					16 => array(
						'name' => 'forum_layout',
						'value' => '0'
					),
					17 => array(
						'name' => 'bootstrap_theme',
						'value' => '6'
					),
					18 => array(
						'name' => 'navbar_style',
						'value' => '0'
					),
					19 => array(
						'name' => 'donation_currency',
						'value' => '0'
					),
					20 => array(
						'name' => 'vote_message',
						'value' => ''
					),
					21 => array(
						'name' => 'infractions',
						'value' => 'false'
					),
					22 => array(
						'name' => 'rules_forum_url',
						'value' => ''
					),
					23 => array(
						'name' => 'rules_server_url',
						'value' => ''
					),
					24 => array(
						'name' => 'staff_apps',
						'value' => 'false'
					),
					25 => array(
						'name' => 'user_avatars',
						'value' => 'false'
					),
					26 => array(
						'name' => 'displaynames',
						'value' => 'false'
					),
					27 => array(
						'name' => 'infractions_plugin',
						'value' => 'null'
					),
					28 => array(
						'name' => 'unique_id',
						'value' => $uid
					),
					29 => array(
						'name' => 'version',
						'value' => '0.5'
					),
					30 => array(
						'name' => 'version_checked',
						'value' => $date
					),
					31 => array(
						'name' => 'version_update',
						'value' => 'false'
					),
					32 => array(
						'name' => 'server_stats',
						'value' => 'false'
					),
					33 => array(
						'name' => 'ingame_register',
						'value' => 'false'
					)
				);
				
				$c = "false"; // Redirect to the extra settings or not
				
				$youtube_url = Input::get('youtube_url');
				if(!empty($youtube_url)){
					$data[6]["value"] = htmlspecialchars($youtube_url);
				}
				$twitter_url = Input::get('twitter_url');
				if(!empty($twitter_url)){
					$data[7]["value"] = htmlspecialchars($twitter_url);
				}
				$twitter_feed = Input::get('twitter_feed');
				if(!empty($twitter_feed)){
					$data[15]["value"] = htmlspecialchars($twitter_feed);
				}
				$gplus_url = Input::get('gplus_url');
				if(!empty($gplus_url)){
					$data[8]["value"] = htmlspecialchars($gplus_url);
				}
				$fb_url = Input::get('fb_url');
				if(!empty($fb_url)){
					$data[9]["value"] = htmlspecialchars($fb_url);
				}
				if(Input::get('user_usernames') == 1){
					$data[26]["value"] = "true";
				}
				if(Input::get('user_avatars') == 1){
					$data[25]["value"] = "true";
				}
				if(Input::get('page_donate') == 1){
					$data[3]["value"] = "true";
					$c = "true";
				}
				if(Input::get('page_vote') == 1){
					$data[2]["value"] = "true";
				}
				if(Input::get('page_infractions') == 1){
					$data[21]["value"] = "true";
					$c = "true";
				}
				if(Input::get('page_staff_app') == 1){
					$data[24]["value"] = "true";
				}
				if(Input::get('page_stats') == 1){
					$data[4]["value"] = "true";
					$c = "true";
				}
				
				try {
					foreach($data as $setting){
						$queries->create("settings", array(
							'name' => $setting["name"],
							'value' => $setting["value"]
						));
					}
					
					header('Location: /install/?step=settings_extra&c=' . $c);
					die();
					
				} catch(Exception $e){
					die($e->getMessage());
				}
				
			} else {
				$errors = "";
				
				foreach($validation->errors() as $error){
					if(strstr($error, 'site_name')){
						$errors .= "Please input a site name<br />";
					}
					if(strstr($error, "outgoing_email")){
						$errors .= "Please input an outgoing email address<br />";
					}
				}
			}
		}
	  ?>
	  <h2>Settings</h2>
	  <?php
	    if(isset($errors)){
	  ?>
	  <div class="alert alert-danger">
	  <?php
	    echo $errors;
	  ?>
	  </div>
	  <?php
		}
	  ?>
	  <small><em>Fields marked with a * are required</em></small>
	  <form action="?step=settings" method="post">
	    <h3>General</h3>
	    <div class="form-group">
	      <label for="InputSiteName">Site Name * </label>
		  <input type="text" class="form-control" name="site_name" id="InputSiteName" value="<?php echo Input::get('site_name'); ?>" placeholder="Site Name">
	    </div>
	    <div class="form-group">
	      <label for="InputOGEmail">Outgoing Email Address * </label>
		  <input type="email" class="form-control" name="outgoing_email" id="InputOGEmail" value="<?php echo Input::get('outgoing_email'); ?>" placeholder="Outgoing Email">
	    </div>
	    <div class="form-group">
	      <label for="InputYT">Youtube URL</label>
		  <input type="text" class="form-control" name="youtube_url" id="InputYT" value="<?php echo Input::get('youtube_url'); ?>" placeholder="Youtube URL">
	    </div>
	    <div class="form-group">
	      <label for="InputTwitter">Twitter URL</label>
		  <input type="text" class="form-control" name="twitter_url" id="InputTwitter" value="<?php echo Input::get('twitter_url'); ?>" placeholder="Twitter URL">
	    </div>
	    <div class="form-group">
	      <label for="InputTwitterFeed">Twitter Feed ID <a data-toggle="modal" href="#twitter_id"><span class="label label-info">?</span></a></label>
		  <input type="text" class="form-control" name="twitter_feed" id="InputTwitterFeed" value="<?php echo Input::get('twitter_feed'); ?>" placeholder="Twitter Feed ID">
	    </div>
	    <div class="form-group">
	      <label for="InputGPlus">Google+ URL</label>
		  <input type="text" class="form-control" name="gplus_url" id="InputGPlus" value="<?php echo Input::get('gplus_url'); ?>" placeholder="Google+ URL">
	    </div>
	    <div class="form-group">
	      <label for="InputFB">Facebook URL</label>
		  <input type="text" class="form-control" name="fb_url" id="InputFB" value="<?php echo Input::get('fb_url'); ?>" placeholder="Facebook URL">
	    </div>
	    <h3>User Accounts</h3>
		<input type="hidden" name="user_usernames" value="0" />
	    <div class="checkbox">
		  <label>
		    <input type="checkbox" name="user_usernames" value="1"> Allow registering with non-Minecraft display names
		  </label>
	    </div>
		<input type="hidden" name="user_avatars" value="0" />
	    <div class="checkbox">
		  <label>
		    <input type="checkbox" name="user_avatars" value="1"> Allow custom user avatars
		  </label>
	    </div>
		<h3>Pages</h3>
		<input type="hidden" name="page_stats" value="0" />
	    <div class="checkbox">
		  <label>
		    <input type="checkbox" name="page_stats" value="1"> Enable Statistics integration (requires <a href="http://dev.bukkit.org/bukkit-plugins/lolmewnstats/" target="_blank">Stats</a>)
		  </label>
	    </div>
		<input type="hidden" name="page_donate" value="0" />
	    <div class="checkbox">
		  <label>
		    <input type="checkbox" name="page_donate" value="1"> Enable Donate page (requires <a href="http://dev.bukkit.org/bukkit-plugins/buycraft/" target="_blank">Buycraft</a>)
		  </label>
	    </div>
		<input type="hidden" name="page_vote" value="0" />
	    <div class="checkbox">
		  <label>
		    <input type="checkbox" name="page_vote" value="1"> Enable Vote page
		  </label>
	    </div>
		<input type="hidden" name="page_infractions" value="0" />
	    <div class="checkbox">
		  <label>
		    <input type="checkbox" name="page_infractions" value="1"> Enable Infractions page (requires <a href="http://www.spigotmc.org/resources/bungee-admin-tools.444/" target="_blank">Bungee Admin Tools</a> or <a href="http://dev.bukkit.org/bukkit-plugins/ban-management/" target="_blank">Ban Management</a>)
		  </label>
	    </div>
		<input type="hidden" name="page_staff_app" value="0" />
	    <div class="checkbox">
		  <label>
		    <input type="checkbox" name="page_staff_app" value="1"> Enable Staff Applications
		  </label>
	    </div>
		<br />
		<input type="submit" class="btn btn-primary" value="Submit">
	  </form>
	  <?php
	  } else if($step === "settings_extra"){
		if(isset($_GET["c"]) && $_GET["c"] === "true"){
			if(!isset($queries)){
				$queries = new Queries(); // Initialise queries
			}

			$buycraft = $queries->getWhere("settings", array("name", "=", "donate"));
			$buycraft = $buycraft[0]->value;
			$infractions = $queries->getWhere("settings", array("name", "=", "infractions"));
			$infractions = $infractions[0]->value;
			$stats = $queries->getWhere("settings", array("name", "=", "stats"));
			$stats = $stats[0]->value;

			if(Input::exists()){
				
				$proceed = true; // Proceed to inputting the data
				
				$buycraft_key = Input::get('buycraft_api');
				$infractions_plugin = Input::get('inf_type');
				
				$data = array(
					1 => array(
						'id' => 6,
						'name' => 'buycraft_key',
						'value' => $buycraft_key
					),
					2 => array(
						'id' => 28,
						'name' => 'infractions_plugin',
						'value' => $infractions_plugin
					)
				);
				
				$inf_address_check = Input::get('inf_address');
				
				$inf_db = array(
					$inf_address_check,
					Input::get('inf_user'),
					Input::get('inf_pass'),
					Input::get('inf_name')
				);
				
				$stats_address_check = Input::get('stats_address');
				
				$stats_db = array(
					$stats_address_check,
					Input::get('stats_user'),
					Input::get('stats_pass'),
					Input::get('stats_name')
				);
				
				if(!empty($inf_address_check)){
					// connect to the infractions database
					$mysqli = new mysqli($inf_address_check, Input::get('inf_user'), Input::get('inf_pass'), Input::get('inf_name'));
					if($mysqli->connect_errno) {
						$mysql_error = $mysqli->connect_errno . ' - ' . $mysqli->connect_error;
						$proceed = false;
					}
				} else {
					if($infractions !== "false"){
						$errors = "Please input Infractions database information";
						$proceed = false; // Error connecting to the Infractions MySQL database - stop
					}
				}
				
				if(!empty($stats_address_check)){
					// connect to the stats database
					$mysqli = new mysqli($stats_address_check, Input::get('stats_user'), Input::get('stats_pass'), Input::get('stats_name'));
					if($mysqli->connect_errno) {
						$mysql_error = $mysqli->connect_errno . ' - ' . $mysqli->connect_error;
						$proceed = false;
					}
				} else {
					if($stats !== "false"){
						$errors = "Please input Stats database information";
						$proceed = false; // Error connecting to the Stats MySQL database - stop
					}
				}
				
				if($proceed !== false){
					// write DB connection info to 'inc/ext_conf.php'
					$insert = 	'<?php' . PHP_EOL . 
								'$GLOBALS[\'mcdb\'] = array(' . PHP_EOL . 
								'	"inf_db" => array(' . PHP_EOL . 
								'		"host" => "' . $inf_address_check . '", // Infractions database address' . PHP_EOL . 
								'		"username" => "' . Input::get('inf_user') . '", // Infractions database username' . PHP_EOL . 
								'		"password" => "' . Input::get('inf_pass') . '", // Infractions database password' . PHP_EOL . 
								'		"db" => "' . Input::get('inf_name') . '" // Infractions database name' . PHP_EOL .
								'	),' . PHP_EOL . 
								'	"stats_db" => array(' . PHP_EOL . 
								'		"host" => "' . $stats_address_check . '", // Stats database address' . PHP_EOL . 
								'		"username" => "' . Input::get('stats_user') . '", // Stats database username' . PHP_EOL . 
								'		"password" => "' . Input::get('stats_pass') . '", // Stats database password' . PHP_EOL . 
								'		"db" => "' . Input::get('stats_name') . '" // Stats database name' . PHP_EOL .
								'	)' . PHP_EOL . 
								');' . PHP_EOL . ' ';
					
					if(is_writable('inc/ext_conf.php')){
						$file = fopen('inc/ext_conf.php','w');
						fwrite($file, $insert);
						fclose($file);
					} else {
						/*
						 *  File not writeable, display code to add to file manually
						 */
						$insert = nl2br(htmlspecialchars($insert));
						?>
	  Your <strong>inc/ext_conf.php</strong> file is not writeable. Please copy/paste the following into your <strong>inc/ext_conf.php</strong> file, overwriting any existing text.
	  <div class="well">
		<?php
		echo $insert;
		?>
	  </div>
	  <a href="/install/?step=account" class="btn btn-primary">Continue</a>
	  <hr>

	  <footer>
		<p>&copy; NamelessMC <?php echo date("Y"); ?></p>
	  </footer>
	</div> <!-- /container -->
	<!-- Bootstrap core JavaScript
	================================================== -->
	<!-- Placed at the end of the document so the pages load faster -->
	<script src="/assets/js/jquery.min.js"></script>
	<script src="/assets/js/bootstrap.min.js"></script>
  </body>
</html>
					<?php
						try {
							foreach($data as $setting){
								$id = $setting["id"];
								$queries->update("settings", $id, array(
									"name" => $setting["name"],
									"value" => $setting["value"]
								));
							}
						} catch(Exception $e){
							die($e->getMessage());
						}
						die();
					}
				
					try {
						foreach($data as $setting){
							$id = $setting["id"];
							$queries->update("settings", $id, array(
								"name" => $setting["name"],
								"value" => $setting["value"]
							));
						}
						
						header('Location: /install/?step=account');
						die();
						
					} catch(Exception $e){
						die($e->getMessage());
					}
				}
			}
	    ?>
	  <h2>Settings</h2>
	    <?php
		if(isset($errors)){
			echo '<div class="alert alert-danger">' . $errors . '</div>';
		}
		if(isset($mysql_error)){
			echo '<div class="alert alert-danger">' . $mysql_error . '</div>';
		}
		?>
	  <form action="?step=settings_extra&c=true" method="post">
		<?php 
		if($buycraft !== "false"){ 
		?>
		<h4>Buycraft</h4>
	    <div class="form-group">
	      <label for="InputBuycraft">Buycraft API Key</label>
		  <input type="text" class="form-control" name="buycraft_api" id="InputBuycraft" value="<?php echo Input::get('buycraft_api'); ?>" placeholder="Buycraft API Key">
	    </div>
	    <?php 
		} 
		if($infractions !== "false"){
		?>
		<h4>Infraction Plugin</h4>
		<div class="btn-group" data-toggle="buttons">
		  <label class="btn btn-primary active">
			<input type="radio" name="inf_type" id="InputInfType1" value="bat" autocomplete="off" checked> Bungee Admin Tools
		  </label>
		  <label class="btn btn-primary">
			<input type="radio" name="inf_type" id="InputInfType2" value="bm" autocomplete="off"> Ban Management
		  </label>
		</div>
		<br /><br />
	    <div class="form-group">
	      <label for="InputInfMySQLAdd">Infractions MySQL Database Address</label>
		  <input type="text" class="form-control" name="inf_address" id="InputInfMySQLAdd" value="<?php echo Input::get('inf_address'); ?>" placeholder="Infractions MySQL Address">
	    </div>
	    <div class="form-group">
	      <label for="InputInfMySQLUser">Infractions MySQL Database Username</label>
		  <input type="text" class="form-control" name="inf_user" id="InputInfMySQLUser" value="<?php echo Input::get('inf_user'); ?>" placeholder="Infractions MySQL Username">
	    </div>
	    <div class="form-group">
	      <label for="InputInfMySQLPass">Infractions MySQL Database Password</label>
		  <input type="password" class="form-control" name="inf_pass" id="InputInfMySQLPass" value="<?php echo Input::get('inf_pass'); ?>" placeholder="Infractions MySQL Password">
	    </div>
	    <div class="form-group">
	      <label for="InputInfMySQLName">Infractions MySQL Database Name</label>
		  <input type="text" class="form-control" name="inf_name" id="InputInfMySQLName" value="<?php echo Input::get('inf_name'); ?>" placeholder="Infractions MySQL Database Name">
	    </div>
		<?php 
		}
		if($stats !== "false"){
		?>
		<h4>Stats Plugin</h4>
	    <div class="form-group">
	      <label for="InputStatsMySQLAdd">Stats MySQL Database Address</label>
		  <input type="text" class="form-control" name="stats_address" id="InputStatsMySQLAdd" value="<?php echo Input::get('stats_address'); ?>" placeholder="Stats MySQL Address">
	    </div>
	    <div class="form-group">
	      <label for="InputStatsMySQLUser">Stats MySQL Database Username</label>
		  <input type="text" class="form-control" name="stats_user" id="InputStatsMySQLUser" value="<?php echo Input::get('stats_user'); ?>" placeholder="Stats MySQL Username">
	    </div>
	    <div class="form-group">
	      <label for="InputStatsMySQLPass">Stats MySQL Database Password</label>
		  <input type="password" class="form-control" name="stats_pass" id="InputStatsMySQLPass" value="<?php echo Input::get('stats_pass'); ?>" placeholder="Stats MySQL Password">
	    </div>
	    <div class="form-group">
	      <label for="InputStatsMySQLName">Stats MySQL Database Name</label>
		  <input type="text" class="form-control" name="stats_name" id="InputStatsMySQLName" value="<?php echo Input::get('stats_name'); ?>" placeholder="Stats MySQL Database Name">
	    </div>
		<?php 
		}
		?>
		<br />
		<input type="submit" class="btn btn-primary" value="Submit">
	  </form>
	    <?php
		} else {
			header('Location: /install/?step=account');
			die();
	    }
	  } else if($step === "account"){
		if(!isset($queries)){
			$queries = new Queries(); // Initialise queries 
		}
		$allow_mcnames = $queries->getWhere("settings", array("name", "=", "displaynames"));
		$allow_mcnames = $allow_mcnames[0]->value; // Can the user register with a non-Minecraft username?
		
		if(Input::exists()){
			$validate = new Validate();
			
			$data = array(
				'email' => array(
					'required' => true,
					'min' => 2,
					'max' => 64
				),
				'password' => array(
					'required' => true,
					'min' => 6,
					'max' => 64
				),
				'password_again' => array(
					'required' => true,
					'matches' => 'password'
				)
			);
			
			if($allow_mcnames === "false"){ // Custom usernames are disabled
				$data['username'] = array(
					'min' => 2,
					'max' => 20,
					'isvalid' => true
				);
			} else { // Custom usernames are enabled
				$data['username'] = array(
					'min' => 2,
					'max' => 20
				);
				$data['mcname'] = array(
					'min' => 2,
					'max' => 20,
					'isvalid' => true
				);
			}
			
			$validation = $validate->check($_POST, $data); // validate
			
			if($validation->passed()){
				$user = new User();
				
				// Get Minecraft UUID of user
				if($allow_mcnames !== "false"){
					$mcname = Input::get('mcname');
					$profile = ProfileUtils::getProfile($mcname);
				} else {
					$mcname = "";
					$profile = ProfileUtils::getProfile(Input::get('username'));
				}
				$uuid = $profile->getProfileAsArray();
				$uuid = $uuid['uuid']; 
				
				// Hash password
				$password = password_hash(Input::get('password'), PASSWORD_BCRYPT, array("cost" => 13));
				
				// Get current unix time
				$date = new DateTime();
				$date = $date->getTimestamp();
				
				try {
					// Create groups
					$queries->create("groups", array(
						'id' => 1,
						'name' => 'Standard',
						'group_html' => '<span class="label label-success">Member</span>',
						'group_html_lg' => '<span class="label label-success">Member</span>'
					));
					$queries->create("groups", array(
						'id' => 2,
						'name' => 'Admin',
						'group_html' => '<span class="label label-danger">Admin</span>',
						'group_html_lg' => '<span class="label label-danger">Admin</span>'
					));
					$queries->create("groups", array(
						'id' => 3,
						'name' => 'Moderator',
						'group_html' => '<span class="label label-info">Moderator</span>',
						'group_html_lg' => '<span class="label label-info">Moderator</span>'
					));
				
					// Create admin account
					$user->create(array(
						'username' => Input::get('username'),
						'password' => $password,
						'mcname' => $mcname,
						'uuid' => $uuid,
						'joined' => $date,
						'group_id' => 2,
						'email' => Input::get('email'),
						'lastip' => "",
						'active' => 1
					));
					
					$login = $user->login(Input::get('username'), Input::get('password'), true);
					if($login) {					
						header('Location: /install/?step=finish');
						die();
					} else {
						echo '<p>Sorry, there was an unknown error logging you in. <a href="/install/?step=account">Try again</a></p>';
						die();
					}
				
				} catch(Exception $e){
					die($e->getMessage());
				}
				
				
			} else {
				Session::flash('admin-acc-error', '
						<div class="alert alert-danger">
							Unable to create account. Please check:<br />
							- You have entered a username between 4 and 20 characters long<br />
							- Your Minecraft username is a valid account<br />
							- Your passwords are at between 6 and 64 characters long and they match<br />
							- Your email address is between 4 and 64 characters<br />
						</div>');
			}
		}
	  ?>
	  <h2>Admin Account</h2>
	  <?php
		if(Session::exists('admin-acc-error')){
			echo Session::flash('admin-acc-error');
		}
	  ?>
	  <p>Please enter the admin account details.</p>
	  <form role="form" action="?step=account" method="post">
	    <div class="form-group">
	  	  <label for="InputUsername">Username</label>
		  <input type="text" class="form-control" id="InputUsername" name="username" placeholder="Username" tabindex="1">
	    </div>
		<?php
		if($allow_mcnames !== "false"){
		?>
	    <div class="form-group">
		  <label for="InputMCUsername">Minecraft Username</label>
		  <input type="text" class="form-control" id="InputMCUsername" name="mcname" placeholder="Minecraft Username" tabindex="2">
	    </div>
		<?php
		}
		?>
	    <div class="form-group">
		  <label for="InputEmail">Email</label>
		  <input type="email" name="email" id="InputEmail" class="form-control" placeholder="Email Address" tabindex="3">
	    </div>
	    <div class="row">
		  <div class="col-xs-12 col-sm-6 col-md-6">
			  <div class="form-group">
				<label for="InputPassword">Password</label>
				<input type="password" class="form-control" id="InputPassword" name="password" placeholder="Password" tabindex="4">
			  </div>
		  </div>
		  <div class="col-xs-12 col-sm-6 col-md-6">
			  <div class="form-group">
				<label for="InputConfirmPassword">Confirm Password</label>
				<input type="password" class="form-control" id="InputConfirmPassword" name="password_again" placeholder="Confirm Password" tabindex="5">
			  </div>
		  </div>
	    </div>
	    <button type="submit" class="btn btn-default">Submit</button>
	  </form>
	  <?php 
	  } else if($_GET['step'] === "convert"){
		if(!isset($user)){
			$user = new User();
		}
		if(!$user->isLoggedIn() || $user->data()->group_id != 2){
			header('Location: /install/?step=account');
			die();
		}
		if(isset($_GET["convert"]) && !isset($_GET["from"])){
	  ?>
		<div class="well">
			<h4>Which forum software are you converting from?</h4>
			<a href="#" onclick="location.href='/install/?step=convert&convert=yes&from=modernbb'">ModernBB</a><br />
			<a href="#" onclick="location.href='/install/?step=convert&convert=yes&from=phpbb'">phpBB</a><br />
			<a href="#" onclick="location.href='/install/?step=convert&convert=yes&from=mybb'">MyBB</a><br />
			<a href="#" onclick="location.href='/install/?step=convert&convert=yes&from=wordpress'">WordPress</a><br /><br />
			<button class="btn btn-danger" onclick="location.href='/install/?step=convert'">Cancel</button>
		</div>
	  <?php
		} else if(isset($_GET["convert"]) && isset($_GET["from"])){
	  ?>
		<div class="well">
	  <?php
		if(strtolower($_GET["from"]) === "modernbb"){
			if(!Input::exists()){
	  ?>
			<h4>Converting from ModernBB:</h4>
			
	  <?php
				if(isset($_GET["error"])){
	  ?>
			<div class="alert alert-danger">
			  Error connecting to the database. Are you sure you entered the correct credentials?
			</div>
	  <?php
				}
	  ?>
			
			<form action="?step=convert&convert=yes&from=modernbb" method="post">
			  <div class="form-group">
			    <label for="InputDBAddress">ModernBB Database Address</label>
				<input class="form-control" type="text" id="InputDBAddress" name="db_address" placeholder="Database address">
			  </div>
			  <div class="form-group">
			    <label for="InputDBName">ModernBB Database Name</label>
				<input class="form-control" type="text" id="InputDBName" name="db_name" placeholder="Database name">
			  </div>
			  <div class="form-group">
			    <label for="InputDBUsername">ModernBB Database Username</label>
				<input class="form-control" type="text" id="InputDBUsername" name="db_username" placeholder="Database username">
			  </div>
			  <div class="form-group">
			    <label for="InputDBPassword">ModernBB Database Password</label>
				<input class="form-control" type="password" id="InputDBPassword" name="db_password" placeholder="Database password">
			  </div>
			  <div class="form-group">
			    <label for="InputDBPrefix">ModernBB Table Prefix (blank for none)</label>
				<input class="form-control" type="text" id="InputDBPrefix" name="db_prefix" placeholder="Table prefix">
			  </div>
			  <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
			  <input type="hidden" name="action" value="convert">
			  <input class="btn btn-primary" type="submit" value="Convert">
			  <a href="#" class="btn btn-danger" onclick="location.href='/install/?step=convert&convert=yes'">Cancel</a>
			</form>
			
	  <?php
			} else {
				require 'converters/modernbb.php';
	  ?>
			<div class="alert alert-success">
				Successfully imported ModernBB data. <strong>Important:</strong> Please redefine any private categories in the Admin panel.<br />
				<center><button class="btn btn-primary"  onclick="location.href='/install/?step=finish'">Proceed</button></center>
			</div>
	  <?php
			}
		} else if(strtolower($_GET["from"]) === "phpbb"){
			if(!Input::exists()){
	  ?>
			<h4>Converting from phpBB:</h4>
			
	  <?php
				if(isset($_GET["error"])){
	  ?>
			<div class="alert alert-danger">
			  Error connecting to the database. Are you sure you entered the correct credentials?
			</div>
	  <?php
				}
	  ?>
			
			<form action="?step=convert&convert=yes&from=phpbb" method="post">
			  <div class="form-group">
			    <label for="InputDBAddress">phpBB Database Address</label>
				<input class="form-control" type="text" id="InputDBAddress" name="db_address" placeholder="Database address">
			  </div>
			  <div class="form-group">
			    <label for="InputDBName">phpBB Database Name</label>
				<input class="form-control" type="text" id="InputDBName" name="db_name" placeholder="Database name">
			  </div>
			  <div class="form-group">
			    <label for="InputDBUsername">phpBB Database Username</label>
				<input class="form-control" type="text" id="InputDBUsername" name="db_username" placeholder="Database username">
			  </div>
			  <div class="form-group">
			    <label for="InputDBPassword">phpBB Database Password</label>
				<input class="form-control" type="password" id="InputDBPassword" name="db_password" placeholder="Database password">
			  </div>
			  <div class="form-group">
			    <label for="InputDBPrefix">phpBB Table Prefix (blank for none)</label>
				<input class="form-control" type="text" id="InputDBPrefix" name="db_prefix" placeholder="Table prefix">
			  </div>
			  <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
			  <input type="hidden" name="action" value="convert">
			  <input class="btn btn-primary" type="submit" value="Convert">
			  <a href="#" class="btn btn-danger" onclick="location.href='/install/?step=convert&convert=yes'">Cancel</a>
			</form>
			
	  <?php
			} else {
				require 'converters/phpbb.php';
	  ?>
			<div class="alert alert-success">
				Successfully imported phpBB data. <strong>Important:</strong> Please redefine any private categories in the Admin panel.<br />
				<center><button class="btn btn-primary"  onclick="location.href='/install/?step=finish'">Proceed</button></center>
			</div>
	  <?php
			}
/* 
 * ---- NEW, By dwilson390 -----
 */
		} else if(strtolower($_GET["from"]) === "wordpress"){
			if(!Input::exists()){
	  ?>
			<h4>Converting from WordPress:</h4>
			
	  <?php
				if(isset($_GET["error"])){
	  ?>
			<div class="alert alert-danger">
			  Error connecting to the database. Are you sure you entered the correct credentials?
			</div>
	  <?php
				}
	  ?>
			<div class="alert alert-success">
				WordPress conversion script created by dwilson390.<br />
			</div>	
			<form action="?step=convert&convert=yes&from=wordpress" method="post">
			  <div class="form-group">
			    <label for="InputDBAddress">Wordpress Database Address</label>
				<input class="form-control" type="text" id="InputDBAddress" name="db_address" placeholder="Database address">
			  </div>
			  <div class="form-group">
			    <label for="InputDBName">Wordpress Database Name</label>
				<input class="form-control" type="text" id="InputDBName" name="db_name" placeholder="Database name">
			  </div>
			  <div class="form-group">
			    <label for="InputDBUsername">Wordpress Database Username</label>
				<input class="form-control" type="text" id="InputDBUsername" name="db_username" placeholder="Database username">
			  </div>
			  <div class="form-group">
			    <label for="InputDBPassword">Wordpress Database Password</label>
				<input class="form-control" type="password" id="InputDBPassword" name="db_password" placeholder="Database password">
			  </div>
			  <div class="form-group">
			    <label for="InputDBPrefix">Wordpress Table Prefix (blank for none) (<strong>Remember the '_'</strong>)</label>
				<input class="form-control" type="text" id="InputDBPrefix" name="db_prefix" placeholder="Table prefix">
			  </div>
			  <div class="form-group">
			    <label for="InputDBCheckbox">I have bbPress installed (selecting this option will also import your forums and topics)</label>
				<input class="form-control" type="checkbox" id="InputDBCheckbox" name="db_checkbox" placeholder="Table prefix">
			  </div>
			  <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
			  <input type="hidden" name="action" value="convert">
			  <input class="btn btn-primary" type="submit" value="Convert">
			  <a href="#" class="btn btn-danger" onclick="location.href='/install/?step=convert&convert=yes'">Cancel</a>
			</form>
			
	  <?php
			} else {
				require 'converters/wordpress.php';
	  ?>
			<div class="alert alert-success">
				Successfully imported Wordpress data. <strong>Important:</strong> Please redefine any private categories in the Admin panel.<br />
				<center><button class="btn btn-primary"  onclick="location.href='/install/?step=finish'">Proceed</button></center>
			</div>
	  <?php
			}
		
/*
 * ---- END, By dwilson390 -----
 */
		} else if(strtolower($_GET["from"]) === "mybb"){
	?>
			<h4>Converting from MyBB:</h4>
	<?php
		}
	?>
		</div>
	<?php
		} else if(!isset($_GET["convert"]) && !isset($_GET["from"]) && !isset($_GET["action"])){
	?>
	  <h2>Convert</h2>
	  <p>Convert from another forum software?</p>
	  <div class="btn-group">
		<button class="btn btn-success" onclick="location.href='/install/?step=convert&convert=yes'">Yes</button>
		<button class="btn btn-primary" onclick="location.href='/install/?step=finish'">No</button>
	  </div>
	<?php
		}
	  } else if($step === "finish"){
	  ?>
	  <h2>Finish</h2>
	  <p>Thanks for using NamelessMC website software.</p>
	  <p>Before you start using the website, please configure the forums and Minecraft servers via the AdminCP.</p>
	  <p>Links:
	  <ul>
	    <li><a target="_blank"href="https://github.com/samerton/NamelessMC">GitHub</a></li>
	    <li><a target="_blank" href="http://www.spigotmc.org/threads/nameless-minecraft-website-software.34810/">SpigotMC thread</a></li>
	  </ul>
	  </p>
	  <button type="button" onclick="location.href='/admin/?from=install'" class="btn btn-primary">Finish &raquo;</button>
	  <?php
	  }
	  ?>
      <hr>

      <footer>
        <p>&copy; NamelessMC <?php echo date("Y"); ?></p>
      </footer>
    </div> <!-- /container -->

	<?php 
	if($step === "start"){ 
	?>
	
    <div class="modal fade" id="bungee_plugins" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">Bungee Plugins</h4>
          </div>
          <div class="modal-body">
            NamelessMC includes support for the following BungeeCord plugins:
			<ul>
			  <li><a target="_blank" href="http://www.spigotmc.org/resources/bungee-admin-tools.444/">BungeeAdminTools</a> (for infractions)</li>
			</ul>
          </div>
          <div class="modal-footer">
		    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
	
    <div class="modal fade" id="mc_plugins" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">Minecraft Plugins</h4>
          </div>
          <div class="modal-body">
            NamelessMC includes support for the following Bukkit/Spigot plugins:
			<ul>
			  <li><a target="_blank" href="http://dev.bukkit.org/bukkit-plugins/buycraft/">Buycraft</a></li>
			  <li><a target="_blank" href="http://www.spigotmc.org/resources/mcmmo.2445/">McMMO</a></li>
			  <li><a target="_blank" href="http://dev.bukkit.org/bukkit-plugins/lolmewnstats/">Stats</a></li>
			  <li><a target="_blank" href="http://www.spigotmc.org/resources/bukkitgames-hungergames.279/">BukkitGames</a></li>
			  <li><a target="_blank" href="http://dev.bukkit.org/bukkit-plugins/ban-management/">Ban Management</a></li>
			</ul>
          </div>
          <div class="modal-footer">
		    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

	<?php
	}
	if($step === "settings"){
	?>
    <div class="modal fade" id="twitter_id" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">Twitter Feed ID</h4>
          </div>
          <div class="modal-body">
			To find your Twitter feed ID, first head into the <a target="_blank" href="https://twitter.com/settings/widgets">Twitter Widgets tab</a> in your settings. Click the "Create new" button in the top right corner of the panel, set the "Height" to "500", and then click Create Widget.<br /><br />Underneath the Preview, a new textarea will appear with some HTML code. You need to find <code>data-widget-id=</code> and copy the number between the "". <br /><br />This is your Twitter feed ID.
          </div>
          <div class="modal-footer">
		    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
	<?php
	}
	?>
	
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/bootstrap.min.js"></script>
  </body>
</html>
