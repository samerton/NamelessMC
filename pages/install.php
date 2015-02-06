<?php
/* 
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */
 
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
	  </ul>

	  <?php
	  if($step === "start"){
	  ?>
	  <h2>Welcome to NamelessMC</h2>
	  This page will guide you through the process of installing the NamelessMC website package.<br /><br />
	  You will need the following:
	  <ul>
	    <li>A MySQL database on the webserver</li>
		<li>PHP version 5.5+</li>
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
		if(version_compare(phpversion(), '5.5', '<')){
			echo 'PHP 5.5 - ' . $error;
			$php_error = true;
		} else {
			echo 'PHP 5.5 - ' . $success;
		}
		if(!extension_loaded('gd')){
			echo 'PHP GD Extension - ' . $error;
		} else {
			echo 'PHP GD Extension - ' . $success;
		}
		if(!extension_loaded('PDO')){
			echo 'PHP PDO Extension - ' . $error;
			$php_error = true;
		} else {
			echo 'PHP PDO Extension - ' . $success;
		}
	  ?>
	  <br />
	  <?php
	    if(isset($php_error)){
	  ?>
	  <div class="alert alert-danger">You must be running at least PHP version 5.5 with the PDO extension enabled in order to proceed with installation.</div>
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
				
				if(!empty(Input::get('db_password'))){
					$db_password = Input::get('db_password');
				}
				
				if(!empty(Input::get('cookie_name'))){
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
					
					if(is_writable('inc/init.php')){
						$config = file_get_contents('inc/init.php');
						$config = substr($config, 5);
						
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
									'		"token_name" => "token"' . PHP_EOL . 
									'	),' . PHP_EOL . 
									');';
						
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
						$config = str_replace("&lt;", "<", $config);
						?>
	  Your <strong>inc/init.php</strong> file is not writeable. Please copy/paste the following into your <strong>inc/init.php</strong> file, overwriting all existing text.
	  <div class="well">
		<?php
		echo $config;
		?>
	  </div>
						
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
	  <form action="?step=configuration" method="post">
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
		
		//$queries->dbInitialise($prefix);
		
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
					)
				);
				
				$c = "false"; // Redirect to the extra settings or not
				
				if(!empty(Input::get('youtube_url'))){
					$data[6]["value"] = htmlspecialchars(Input::get('youtube_url'));
				}
				if(!empty(Input::get('twitter_url'))){
					$data[7]["value"] = htmlspecialchars(Input::get('twitter_url'));
				}
				if(!empty(Input::get('twitter_feed'))){
					$data[15]["value"] = htmlspecialchars(Input::get('twitter_feed'));
				}
				if(!empty(Input::get('gplus_url'))){
					$data[8]["value"] = htmlspecialchars(Input::get('gplus_url'));
				}
				if(!empty(Input::get('fb_url'))){
					$data[9]["value"] = htmlspecialchars(Input::get('fb_url'));
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
			$buycraft = $queries->getWhere("settings", array("name", "=", "donate"))[0]->value;
			$infractions = $queries->getWhere("settings", array("name", "=", "infractions"))[0]->value;
			$stats = $queries->getWhere("settings", array("name", "=", "stats"))[0]->value;
	    ?>
	  <h2>Settings</h2>
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
	  ?>
	  <h2>Admin Account</h2>
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
			  <li><a href="http://www.spigotmc.org/resources/bungee-admin-tools.444/">BungeeAdminTools</a> (for infractions)</li>
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
			  <li><a href="http://dev.bukkit.org/bukkit-plugins/buycraft/">Buycraft</a></li>
			  <li><a href="http://www.spigotmc.org/resources/mcmmo.2445/">McMMO</a></li>
			  <li><a href="http://dev.bukkit.org/bukkit-plugins/lolmewnstats/">Stats</a></li>
			  <li><a href="http://www.spigotmc.org/resources/bukkitgames-hungergames.279/">BukkitGames</a></li>
			  <li><a href="http://dev.bukkit.org/bukkit-plugins/ban-management/">Ban Management</a></li>
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
