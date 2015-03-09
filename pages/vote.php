<?php 
/* 
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

require('inc/includes/html/library/HTMLPurifier.auto.php');

/*
 *  Check if page is enabled
 */ 
$vote_enabled = $queries->getWhere("settings", array("name", "=", "vote"));

if($vote_enabled[0]->value === "false"){
	Redirect::to("/");
	die();
}

$vote_enabled = null;
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

    <title><?php echo $sitename; ?> &bull; Vote</title>

	<?php require("inc/templates/header.php"); ?>
	
  </head>

  <body>

    <?php require("inc/templates/navbar.php"); ?>
  
    <div class="container">
	
	<?php 
	$vote_message = $queries->getWhere("settings", array("name", "=", "vote_message"));
	$vote_message = $vote_message[0]->value;
	
	if(!empty($vote_message)){
		$config = HTMLPurifier_Config::createDefault();
		$config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
		$config->set('URI.DisableExternalResources', false);
		$config->set('URI.DisableResources', false);
		$config->set('HTML.Allowed', 'u,p,b,i,small,blockquote,span[style],span[class],p,strong,em,li,ul,ol,div[align],br,img');
		$config->set('CSS.AllowedProperties', array('float', 'color','background-color', 'background', 'font-size', 'font-family', 'text-decoration', 'font-weight', 'font-style', 'font-size'));
		$config->set('HTML.AllowedAttributes', 'src, height, width, alt, class');
		$purifier = new HTMLPurifier($config);
	?>
	<div class="alert alert-info"><center><?php echo $purifier->purify(htmlspecialchars_decode($vote_message)); ?></center></div>
	<?php 
	}
	
	$sites = $queries->getWhere("vote_sites", array("id", "<>", 0));
	$n = 0;
	$finish = count($sites) - 1;
	foreach($sites as $site){
		if($n % 4 != 0){
			// Middle or end column
		} else {
			if($n !== 0){
	?>
	</div>
	<div class="row">
	<?php 
			} else {
	?>
	<div class="row">
	<?php
			}
		}
	?>
	  <div class="col-md-3">
		<center><a class="btn btn-lg btn-primary" href="<?php echo str_replace("&amp;", "&", htmlspecialchars($site->site)); ?>" target="_blank" role="button"><?php echo htmlspecialchars($site->name); ?></a></center>
	  </div>
	<?php 
		if($n == $finish){
	?>
	</div>
	<?php
		}
		$n++;
	}
	?>
	<hr>
	
	<div class="alert alert-info">Top voters lists coming soon</div>
	
    <hr>

	<?php require("inc/templates/footer.php"); ?>
	  
    </div> <!-- /container -->
		
	<?php require("inc/templates/scripts.php"); ?>
	<script>
	$(document).ready(function(){
    $("[rel=tooltip]").tooltip({ placement: 'top'});
	});
	</script>
  </body>
</html>
