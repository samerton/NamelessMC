<?php 
/* 
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

if(!isset($user)){
	$user = new User();
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
    <link rel="icon" href="../../favicon.ico">

    <title><?php echo htmlspecialchars($queries->getWhere("settings", array("name", "=", "sitename"))[0]->value); ?> &bull; Credits</title>
	
	<!-- Custom style -->
	<style>
	html {
		overflow-y: scroll;
	}
	</style>
	
	<?php include("inc/templates/header.php"); ?>

  </head>

  <body>

	<?php include("inc/templates/navbar.php"); ?>
	
	<div class="container">
	<h3>Credits</h3>
	Website software written by <a href="https://worldscapemc.co.uk">Samerton (worldscapemc.co.uk)</a><br /><br />
	<strong>Contributors</strong>:<br />
	Wiki created by EvenSafe789<br />
	WordPress conversion script by dwilson390<br />
	<br /><br />
	<a href="https://github.com/ezyang/htmlpurifier">HTMLPurifier</a> - Edward Z. Yang (GNU Lesser General Public License)<br />
	<a href="https://github.com/summernote/summernote">Summernote Editor</a> - easylogic (MIT License)<br />
	<a href="http://getbootstrap.com/">Built with Bootstrap</a> - Twitter, Inc (MIT License)<br />
	<a href="http://bootswatch.com/">Bootswatch themes</a> - Thomas Park (MIT License)<br />
	<a href="https://github.com/xPaw/PHP-Minecraft-Query">PHP Minecraft Query</a> - xPaw (<a href="http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode">License</a>)<br />
	<a href="http://www.white-hat-web-design.co.uk/blog/resizing-images-with-php/">SimpleImage</a> - Simon Jarvis (GNU General Public License)<br />
	<a href="https://github.com/jimmiw/php-time-ago">PHP Time Ago</a> - Jimmi Westerberg (MIT License)<br />
	Emoticons - Oscar Gruno and Andy Fedosjeenko
		<hr>
	  <?php include("inc/templates/footer.php"); ?> 
	  
    </div> <!-- /container -->
		
	<?php include("inc/templates/scripts.php"); ?>	
  </body>
</html>