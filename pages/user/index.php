<?php 
/* 
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

if(!$user->isLoggedIn()){
	Redirect::to('/');
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
	<link rel="icon" href="/favicon.ico">
	<meta name="robots" content="noindex">

    <title><?php echo $sitename; ?> &bull; UserCP Index</title>
	
	<?php require("inc/templates/header.php"); ?>

  </head>

  <body>

	<?php require("inc/templates/navbar.php"); ?>

    <div class="container">	

	  <div class="row">
		<div class="col-md-3">
			<div class="well well-sm">
				<ul class="nav nav-pills nav-stacked">
				  <li class="active"><a href="/user">Overview</a></li>
				  <li><a href="/user/messaging">Private Messages</a></li>
				  <li><a href="/user/settings">Profile settings</a></li>
				</ul>
			</div>
		</div>
		<div class="col-md-9">
			<div class="well well-sm">
				Nothing here yet, please use the navigation on the left
			</div>
		</div>
      </div>	  

      <hr>

	  <?php require("inc/templates/footer.php"); ?> 
	  
    </div> <!-- /container -->
		
	<?php require("inc/templates/scripts.php"); ?>
	
  </body>
</html>