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
		<li <?php if($step == "settings"){ ?>class="active"<?php } else { ?>class="disabled"<?php } ?>><a>Settings</a></li>
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
	  ?>
	  <h2>Settings</h2>
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
	      <label for="InputTwitterFeed">Twitter Feed ID <a href="#" target="_blank"><span class="label label-info">?</span></a></label>
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
	    <div class="checkbox">
		  <label>
		    <input type="checkbox" name="user_usernames"> Allow registering with non-Minecraft display names
		  </label>
	    </div>
	    <div class="checkbox">
		  <label>
		    <input type="checkbox" name="user_avatars"> Allow custom user avatars
		  </label>
	    </div>
		<h3>Pages</h3>
	    <div class="checkbox">
		  <label>
		    <input type="checkbox" name="page_donate"> Enable Donate page
		  </label>
	    </div>
	    <div class="checkbox">
		  <label>
		    <input type="checkbox" name="page_vote"> Enable Vote page
		  </label>
	    </div>
	    <div class="checkbox">
		  <label>
		    <input type="checkbox" name="page_vote"> Enable Infractions page (requires <a href="http://www.spigotmc.org/resources/bungee-admin-tools.444/" target="_blank">Bungee Admin Tools</a>)
		  </label>
	    </div>
		<br />
		<input type="submit" class="btn btn-primary" value="Submit">
	  </form>
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
	?>
	
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/bootstrap.min.js"></script>
  </body>
</html>
