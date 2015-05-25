<?php 
/* 
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

// Set page name for sidebar
$page = "admin-upgrade";

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

/*
 *  Version check
 */
$need_update = "false";

$uid = $queries->getWhere("settings", array("name", "=", "unique_id"));
$uid = htmlspecialchars($uid[0]->value);

if(!isset($version)){
	$version = $queries->getWhere('settings', array('id', '=', 30));
	$version = $version[0]->value;
}

$latest_version = file_get_contents("https://worldscapemc.co.uk/nl_core/stats.php?uid=" . $uid . "&version=" . $version);

if($latest_version !== "failed"){
	if($version < $latest_version){
		// Need to update!
		$queries->update("settings", 32, array(
			"value" => htmlspecialchars($latest_version)
		));
		$need_update = htmlspecialchars($latest_version);
		$instructions = file_get_contents("https://worldscapemc.co.uk/nl_core/update.php?version=" . $version);
	}
}

$uid = null;
$latest_version = null;

/*
 *  End version check
 */
 
// Get update instructions
if(isset($instructions)){
	// HTMLPurifier
	require('inc/includes/html/library/HTMLPurifier.auto.php'); 
	
	$config = HTMLPurifier_Config::createDefault();
	$config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
	$config->set('URI.DisableExternalResources', false);
	$config->set('URI.DisableResources', false);
	$config->set('HTML.Allowed', 'u,p,b,i,small,blockquote,span[style],span[class],p,strong,em,li,ul,ol,div[align],br,img');
	$config->set('CSS.AllowedProperties', array('float', 'color','background-color', 'background', 'font-size', 'font-family', 'text-decoration', 'font-weight', 'font-style', 'font-size'));
	$config->set('HTML.AllowedAttributes', 'src, height, width, alt, class, *.style');
	$purifier = new HTMLPurifier($config);
	
	$instructions = $purifier->purify($instructions);
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
    <link rel="icon" href="/assets/favicon.ico">

    <title><?php echo $sitename; ?> &bull; Admin Upgrade</title>
	
	<?php require('inc/templates/header.php'); ?>
	
	<!-- Custom style -->
	<style>
	html {
		overflow-y: scroll;
	}
	</style>
	
  </head>
  <body>
    <?php require('inc/templates/navbar.php'); ?>
	<div class="container">
		<div class="row">
		  <div class="col-md-3">
		    <?php require('pages/admin/sidebar.php'); ?>
		  </div>
		  <div class="col-md-9">
			<?php
			if($need_update !== "false"){
				// Update needed
				if(!isset($_GET["go"])){
				?>
				<div class="well">
				  <h2>Upgrade</h2>
				  <p>Follow the instructions below to upgrade your installation from version <?php echo $version; ?> to version <?php echo htmlspecialchars($need_update); ?>.</p>
				  <p>Please create a backup of your database and files before proceeding.</p>
				  <strong>Instructions</strong>
				  <p><?php echo $instructions; ?></p>
				  <p>Once this has been completed, please click "Start" below.</p>
				  <a href="/admin/upgrade/?go" class="btn btn-primary">Start</a>
				  <p><a target="_blank" href="https://raw.githubusercontent.com/samerton/NamelessMC/master/changelog.txt">Changelog</a></p>
				</div>
				<?php
				} else {
					// Update
					require('inc/includes/update.php');
				?>
				<div class="well">
				  <h2>Success!</h2>
				  <p>You are now up to date</p>
				</div>
				<?php
				}
			} else {
			?>
			<div class="alert alert-info">
			<p>Your installation is up to date!</p>
			</div>
			<?php 
			}
			?>
		  </div>
		</div>
		<?php require('inc/templates/footer.php'); ?>
	</div>
	<?php require('inc/templates/scripts.php'); ?>
  </body>
</html>