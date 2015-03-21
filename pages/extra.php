<?php 
/* 
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

require('inc/includes/html/library/HTMLPurifier.auto.php'); // HTML Purifier for page content

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

    <title><?php echo $sitename; ?> &bull; <?php echo htmlspecialchars($page_title); ?></title>
	
	<!-- Custom style -->
	<style>
	html {
		overflow-y: scroll;
	}
	</style>
	
	<?php require("inc/templates/header.php"); ?>

  </head>

  <body>

	<?php require("inc/templates/navbar.php"); ?>
	
	<div class="container">
	  <?php 
	    // Initialise HTML Purifier
		$config = HTMLPurifier_Config::createDefault();
		$config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
		$config->set('URI.DisableExternalResources', false);
		$config->set('URI.DisableResources', false);
		$config->set('HTML.Allowed', 'u,p,b,i,small,blockquote,span[style],span[class],p,strong,em,li,ul,ol,div[align],br,img');
		$config->set('CSS.AllowedProperties', array('text-align', 'float', 'color','background-color', 'background', 'font-size', 'font-family', 'text-decoration', 'font-weight', 'font-style', 'font-size'));
		$config->set('HTML.AllowedAttributes', 'href, src, height, width, alt, class, *.style');
		$purifier = new HTMLPurifier($config);

		echo $purifier->purify(htmlspecialchars_decode($page_content));
	  ?>
	  <hr>
	  <?php require("inc/templates/footer.php"); ?> 
	  
    </div>
		
	<?php require("inc/templates/scripts.php"); ?>	
  </body>
</html>