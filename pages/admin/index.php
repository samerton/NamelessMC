<?php 
/*
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */
 
$page = "admin";

if(!isset($user)){
	$user = new User(); // Initialise users
}

/*
 *  Admin check
 */ 

if($user->isLoggedIn()){
	if($user->data()->group_id == 2){} else {
		Redirect::to('/');
		die();
	}
} else {
	Redirect::to('/');
	die();
}

/* 
 *  TODO
 *  Re-authenticate upon opening the admin panel
 */ 

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Admin panel">
    <meta name="author" content="Samerton">
	<meta name="robots" content="noindex,nofollow" />

    <title><?php echo htmlspecialchars($queries->getWhere("settings", array("name", "=", "sitename"))[0]->value); ?> &bull; AdminCP</title>
	
	<?php include("inc/templates/header.php"); ?>

  </head>

  <body>

	<?php include("inc/templates/navbar.php"); ?>

    <div class="container">	
	<?php
		if(Session::exists('adm-alert')){
			echo Session::flash('adm-alert');
		}
	?>
	  <div class="row">
		<div class="col-md-3">
			<div class="well well-sm">
				<ul class="nav nav-pills nav-stacked">
				  <li class="active"><a href="/admin">Overview</a></li>
				  <li><a href="/admin/general">General Settings</a></li>
				  <li><a href="/admin/pages">Pages</a></li>
				  <li><a href="/admin/groups">Groups</a></li>
				  <li><a href="/admin/users">Users</a></li>
				  <li><a href="/admin/forum">Forum</a></li>
				  <li><a href="/admin/minecraft">Minecraft</a></li>
				  <li><a href="/admin/donate">Donate</a></li>
				  <li><a href="/admin/vote">Vote</a></li>
				  <li><a href="/admin/documentation">Documentation</a></li>
				</ul>
			</div>
		</div>
		<div class="col-md-9">
			<div class="well well-sm">
				<b>WorldscapeMC website version:</b> 0.1
				<b>Current PHP version:</b> <?php echo phpversion(); ?>
			</div>
		</div>
      </div>	  

      <hr>
	  
	  <?php include("inc/templates/footer.php"); ?> 
	  
    </div> <!-- /container -->
		
	<?php include("inc/templates/scripts.php"); ?>
	
  </body>
</html>