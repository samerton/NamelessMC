<?php 
/*
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

// Set page name for sidebar
$page = "admin-documentation";

// Admin check
if($user->isAdmLoggedIn()){
	// Is authenticated
	if($user->data()->group_id != 2){
		Redirect::to('/');
		die();
	}
} else {
	Redirect::to('/admin');
	die();
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Samerton">
	<meta name="robots" content="noindex">
    <link rel="icon" href="/favicon.ico">

    <title><?php echo $sitename; ?> &bull; AdminCP Documentation</title>
	
	<?php require("inc/templates/header.php"); ?>

  </head>

  <body>

	<?php require("inc/templates/navbar.php"); ?>

    <div class="container">	
	<?php
		if(Session::exists('adm-alert')){
			echo Session::flash('adm-alert');
		}
	?>
	  <div class="row">
		<div class="col-md-3">
			<?php require('pages/admin/sidebar.php'); ?>
		</div>
		<div class="col-md-9">
			<div class="well">
				<h2>AdminCP Documentation</h2>
				<hr>
				<h3>General Settings</h3>
				<b>Site Name</b>: Website name, which will appear in the navbar and on the front page.<br /><br />
				<b>Buycraft API Key</b>: API key from the Buycraft Bukkit plugin. If you leave it as "null", the Donate page will not display.<br /><br />
				<b>Recaptcha Site Key</b>: The tickbox will enable Recaptcha on the registration form. Paste the <strong>site</strong> key from your Google Recaptcha account.<br /><br />
				<b>Site URLs</b>: Paste links to your pages on various Social Media sites. If left as "null", the link to that site will not display in the footer.<br /><br />
				<h3>Pages</h3>
				<b>Forum maintenance mode</b>: Disable your forums to everyone but admins.<br /><br />
				<b>Enable pages</b>: The tickboxes will enable/disable certain pages on your site.<br /><br />
				<h3>Groups</h3>
				<b>Important</b>: Create your Buycraft-linked groups <strong>in order of lowest to highest value</strong>.<br /><br />
				<h3>Users</h3>
				<b>Synchronise with Buycraft</b>: This will update your users' groups with any Buycraft packages they may have purchased, and also store a list of donations in your database. This should be run by an automatic process on your dedicated server.<br /><br />
				<b>Individual user page</b>: On this page you can edit a user's details and view their IP address. The "Tasks" dropdown can get their UUID, their Minecraft username associated with that UUID, reset their password, and punishments can be issued.<br /><br />
				<h3>Forum</h3>
				<b>Categories</b>: Specify which order the categories will display in your forum with the "up" and "down" buttons. <strong>IMPORTANT!</strong> Please create at least one category as a "parent" category. All following categories you create must have a "parent" category set.<br /><br />
				<b>Individual category page</b>: Tick the "Display threads as news on front page?" checkbox <strong>for at least one category</strong>!<br /><br />
				<h3>Minecraft</h3>
				<b>New server</b>: Unless your port is 25565, add the port onto the end of the IP (eg example-ip.com:25566)<br /><br />
				<b>Main server</b>: The IP which players connect to your server through. <strong>Must select one!</strong>
			</div>
		</div>
      </div>	  

      <hr>
	  
	  <?php require("inc/templates/footer.php"); ?> 
	  
    </div> <!-- /container -->
		
	<?php require("inc/templates/scripts.php"); ?>
	
  </body>
</html>