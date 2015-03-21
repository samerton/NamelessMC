<?php
/* 
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

// Set the page name for the active link in navbar
$page = "forum";
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Samerton">
    <link rel="icon" href="/assets/favicon.ico">

    <title><?php echo $sitename; ?> &bull; Forum</title>
	
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
      <div class="jumbotron">
	    <h1>Error</h1>
		<h4>Sorry, we couldn't find that forum or topic.</h4>
		Are you logged in?<br /><br />
		<div class="btn-group" role="group" aria-label="...">
		  <a href="#" class="btn btn-primary btn-lg" onclick="window.history.back()">Go back</a>
		  <a href="/" class="btn btn-success btn-lg">Homepage</a>
	    </div>
	  </div>
	  
	  <hr>
	  
	  <?php require('inc/templates/footer.php'); ?>
	</div>
	<?php 
	require('inc/templates/scripts.php');
	?>
  </body>
</html>