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
	
	if(count($sites) > 4){
		// How many buttons on the second row?
		$second_row = count($sites) - 4;
		if($second_row == 1){
			// one central button
			$col = '12';
		} else if($second_row == 2){
			// two central buttons
			$col = '6';
		} else if($second_row == 3){
			// three wider buttons
			$col = '4';
		} else if($second_row == 4){
			// four buttons
			$col = '3';
		}
	} else {
		// How many buttons on the top row?
		$top_row = count($sites);
		if($top_row == 1){
			// one central button
			$col = '12';
		} else if($top_row == 2){
			// two central buttons
			$col = '6';
		} else if($top_row == 3){
			// three wider buttons
			$col = '4';
		} else if($top_row == 4){
			// four buttons
			$col = '3';
		}
	}
	
	if(isset($top_row)){
		// One row only
	?>
	<div class="row">
	<?php
		foreach($sites as $site){
	?>
	  <div class="col-md-<?php echo $col; ?>">
	    <center><a class="btn btn-lg btn-block btn-primary" href="<?php echo str_replace("&amp;", "&", htmlspecialchars($site->site)); ?>" target="_blank" role="button"><?php echo htmlspecialchars($site->name); ?></a></center>
	  </div>
	<?php 
		} 
	?>
	</div>
	<?php
	} else if(isset($second_row)){
		// Two rows
	?>
	<div class="row">
	<?php
		$n = 0;
		while($n < 4){
	?>
	  <div class="col-md-3">
	    <center><a class="btn btn-lg btn-block btn-primary" href="<?php echo str_replace("&amp;", "&", htmlspecialchars($sites[$n]->site)); ?>" target="_blank" role="button"><?php echo htmlspecialchars($sites[$n]->name); ?></a></center>
	  </div>
	<?php
			$n++;
		}
	?>
	</div><br /><br />
	<div class="row">
	<?php
		$n = 0;
		while($n < $second_row){
	?>
	  <div class="col-md-<?php echo $col; ?>">
        <center><a class="btn btn-lg btn-block btn-primary" href="<?php echo str_replace("&amp;", "&", htmlspecialchars($sites[$n + 4]->site)); ?>" target="_blank" role="button"><?php echo htmlspecialchars($sites[$n + 4]->name); ?></a></center>
        <br>
	  </div>
	<?php
			$n++;
		}
	?>
	</div>
	<?php
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
