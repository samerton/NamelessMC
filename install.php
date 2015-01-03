<?php 
/* 
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

$page = "install";
$path = "";

require_once 'inc/init.php'; // Initialise
require_once 'inc/integration/uuid.php'; // Include UUIDs

/*
 *  Process inputted data
 */

if(Input::exists()) {
	if(Token::check(Input::get('token'))) {
		if(Input::get('action') === "adminacc"){
			$validate = new Validate();
			$validation = $validate->check($_POST, array(
				'username' => array(
					'required' => true,
					'min' => 4,
					'max' => 20,
					'unique' => 'users'
				),
				'mcname' => array(
					'required' => true,
					'min' => 4,
					'max' => 20,
					'isvalid' => true
				),
				'password' => array(
					'required' => true,
					'min' => 6,
					'max' => 30
				),
				'password_again' => array(
					'required' => true,
					'matches' => 'password'
				),
				'email' => array(
					'required' => true,
					'min' => 4,
					'max' => 50
				)
			));
			
			if($validation->passed()){
				$queries = new Queries();
				$user = new User();
				
				$salt = Hash::salt(32);
				
				$profile = ProfileUtils::getProfile(Input::get('mcname'));
				$uuid = $profile->getProfileAsArray()['uuid'];
				
				try {
					
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
				
					$user->create(array(
						'username' => Input::get('username'),
						'password' => Hash::make(Input::get('password'), $salt),
						'salt' => $salt,
						'mcname' => Input::get('mcname'),
						'uuid' => $uuid,
						'joined' => date('Y-m-d H:i:s'),
						'group_id' => 2,
						'email' => Input::get('email'),
						'lastip' => "",
						'active' => 1
					));
					
					$login = $user->login(Input::get('username'), Input::get('password'), true);
					if($login) {					
						Redirect::to('install.php?step=settings');
						die();
					} else {
						echo '<p>Sorry, there was an unknown error logging you in. <a href="install.php?step=adminacc">Try again</a></p>';
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
							- Your passwords are at between 6 and 30 characters long and they match<br />
							- Your email address is between 4 and 50 characters<br />
						</div>');
				Redirect::to('install.php?step=adminacc');
				die();
			}
		} else if(Input::get('action') === "settings"){
			$validate = new Validate();
			$validation = $validate->check($_POST, array(
				'sitename' => array(
					'required' => true,
					'min' => 2,
					'max' => 20
				),
				'buycraft' => array(
					'min' => 39,
					'max' => 40
				)
			));
			
			if($validation->passed()){
				
				if(!isset($queries)){
					$queries = new Queries();
				}
				
				$random = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 30);
				
				$data = array(
					0 => array(
						'name' => 'sitename',
						'value' => htmlspecialchars(Input::get('sitename'))
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
						'value' => htmlspecialchars(Input::get('siteemail'))
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
					)
				);
				
				if(Input::get('maintenance') == "on"){
					$data[1]["value"] = "true";
				} else {
					$data[1]["value"] = "false";
				}
				if(Input::get('vote') == "on"){
					$data[2]["value"] = "true";
				} else {
					$data[2]["value"] = "false";
				}
				if(Input::get('donate') == "on"){
					$data[3]["value"] = "true";
				} else {
					$data[3]["value"] = "false";
				}
				if(Input::get('stats') == "on"){
					$data[4]["value"] = "true";
				} else {
					$data[4]["value"] = "false";
				}
				if(Input::get('infractions') == "on"){
					$data[21]["value"] = "true";
				} else {
					$data[21]["value"] = "false";
				}
				
				if(!empty(Input::get('buycraft'))){
					$data[5]["value"] = htmlspecialchars(Input::get('buycraft'));
				}
				
				try {
				
					foreach($data as $setting){
						$queries->create("settings", array(
							'name' => $setting["name"],
							'value' => $setting["value"]
						));
					}
					
					Redirect::to('install.php?step=finalise');
					die();
					
				} catch(Exception $e){
					die($e->getMessage());
				}
				
			} else {
				Session::flash('settings-error', '
						<div class="alert alert-danger">
							Unable to proceed. Please check:<br />
							- You have entered a site name between 2 and 20 characters long<br />
							- You have entered an outgoing email address<br />
							- If you entered a Buycraft API key, please ensure it is the correct length (40 characters)
						</div>');
				Redirect::to('install.php?step=settings');
				die();
			}
			
		}
	} else {
		echo 'Invalid token';
		die();
	}
}

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Forum software install">
    <meta name="author" content="Samerton, WorldscapeMC">

    <title>Nameless MC - Install</title>
	
	<?php include("inc/templates/header.php"); ?>

  </head>

  <body>

    <div class="container">	
		<div class="well">
			<ul class="nav nav-pills" role="tablist">
			  <li<?php if(!isset($_GET['step'])){?> class="active"<?php } ?>><a>Welcome</a></li>
			  <li<?php if($_GET['step'] === "dbcheck" || $_GET['step'] === "dbinit"){?> class="active"<?php } ?>><a>Database</a></li>
			  <li<?php if($_GET['step'] === "adminacc"){?> class="active"<?php } ?>><a>Admin account</a></li>
			  <li<?php if($_GET['step'] === "settings"){?> class="active"<?php } ?>><a>Settings</a></li>
			  <li<?php if($_GET['step'] === "finalise"){?> class="active"<?php } ?>><a>Finalise</a></li>
			</ul>
		</div>
	<?php 
	if($GLOBALS['config']['mysql']['db'] !== ""){
		if(!isset($_GET['step'])){
	?>
		<div class="well">
			<h2>Welcome</h2>
			<p>The following steps will guide you through the installation process.</p>
			<div class="alert alert-danger"><strong>It's highly recommended that you don't use a beta release on a production server!</strong></div>
			<center>
				<button class="btn btn-primary" onclick="location.href='install.php?step=dbcheck'">Proceed &raquo;</button>
			</center>
		</div>
	<?php
		} else if($_GET['step'] === "dbcheck"){
	?>
		<div class="well">
			<h2>Database Check</h2>
			<p>The installer will now check the database settings you inputted in the <strong>init.php</strong> file.</p>
			<?php 
			$db = DB::getInstance();
			?>
			<div class="alert alert-success">
				<strong>Connection successful</strong>
			</div>
			<center>
				<button class="btn btn-primary" onclick="location.href='install.php?step=dbinit'">Proceed &raquo;</button>
			</center>
		</div>
	<?php 
		} else if($_GET['step'] === "dbinit"){
	?>
		<div class="well">
			<h2>Database Initialisation</h2>
			<p>The installer will now initialise the database.</p>
			<?php 
			$queries = new Queries();
			$is_initialised = $queries->dbInitialise();
			echo $is_initialised;
			?>
			<center>
				<button class="btn btn-primary" onclick="location.href='install.php?step=adminacc'">Proceed &raquo;</button>
			</center>
		</div>
	<?php 
		} else if($_GET['step'] === "adminacc"){
	?>
		<div class="well">
			<?php
				if(Session::exists('admin-acc-error')){
					echo Session::flash('admin-acc-error');
				}
			?>
			<h2>Create Admin Account</h2>
			<p>Please enter the admin account details.</p>
			<form role="form" action="" method="post">
			  <div class="form-group">
				<label for="InputUsername">Username</label>
				<input type="text" class="form-control" id="InputUsername" name="username" placeholder="Username" tabindex="1">
			  </div>
			  <div class="form-group">
				<label for="InputMCUsername">Minecraft Username</label>
				<input type="text" class="form-control" id="InputMCUsername" name="mcname" placeholder="Minecraft Username" tabindex="2">
			  </div>
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
			  <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
			  <input type="hidden" name="action" value="adminacc">
			  <button type="submit" class="btn btn-default">Submit</button>
			</form>
		</div>
	<?php 
		} else if($_GET['step'] === "settings"){
			if(!isset($user)){
				$user = new User();
			}
			if(!$user->isLoggedIn() || $user->data()->group_id != 2){
				Redirect::to('install.php?step=adminacc');
				die();
			}
	?>
		<div class="well">
			<?php
				if(Session::exists('settings-error')){
					echo Session::flash('settings-error');
				}
			?>
			<h2>Settings</h2>
			<p>Please fill out the following settings. You can leave some blank and fill them in at a later date through the AdminCP.</p>
			<form role="form" action="" method="post">
			  <div class="form-group">
				<label for="SiteName">Site Name</label>
				<input type="text" class="form-control" id="SiteName" name="sitename" placeholder="Site Name">
			  </div>
			  <div class="form-group">
				<label for="InputSiteEmail">Outgoing Email Address</label>
				<input type="text" class="form-control" id="InputSiteEmail" name="siteemail" placeholder="Outgoing Email Address">
			  </div>
			  <div class="checkbox">
				<label>
				  <input name="maintenance" type="checkbox"> Enable Maintenance Mode
				</label>
			  </div>
			  <div class="checkbox">
				<label>
				  <input name="vote" type="checkbox"> Enable Vote Page
				</label>
			  </div>
			  <div class="checkbox">
				<label>
				  <input name="donate" type="checkbox"> Enable Donate Page
				</label>
			  </div>
			  <div class="checkbox">
				<label>
				  <input name="stats" type="checkbox"> Enable Stats Page
				</label>
			  </div>
			  <div class="checkbox">
				<label>
				  <input name="infractions" type="checkbox"> Enable Minecraft Infractions (Requires Bungee Admin Tools)
				</label>
			  </div>
			  <div class="form-group">
				<label for="InputBuycraft">Buycraft API Key (can be left blank)</label>
				<input type="text" class="form-control" id="InputBuycraft" name="buycraft" placeholder="Buycraft API Key">
			  </div>
			  <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
			  <input type="hidden" name="action" value="settings">
			  <button type="submit" class="btn btn-default">Proceed</button>
			</form>
		</div>
	<?php 
		} else if($_GET['step'] === "finalise"){
			if(!isset($user)){
				$user = new User();
			}
			if(!$user->isLoggedIn() || $user->data()->group_id != 2){
				Redirect::to('install.php?step=adminacc');
				die();
			}
	?>
		<div class="well">
			<h2>Finalise installation</h2>
			<p>Installation complete. Please visit the AdminCP to finalise your installation.</p>
			<div class="alert alert-danger"><strong>Please delete the install.php file before using the board!</strong></div>
			<center><a href="admin" class="btn btn-primary">Proceed to AdminCP &raquo;</a></center>
		</div>
	<?php 
		}
	} else {
	?>
	<br />
	<div class="alert alert-danger">
		<strong>Error</strong>
		<p>In order to proceed with the installation, please fill in your database information in the <strong>init.php</strong> file, found in the <strong>inc</strong> folder.</p>
	</div>
	<?php
	}
	?>
    </div> <!-- /container -->
	<?php include("inc/templates/scripts.php"); ?>

  </body>
</html>