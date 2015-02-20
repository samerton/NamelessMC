<?php 
/* 
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

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

    <title><?php echo $sitename; ?> &bull; Credits</title>
	
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
	Website software written by <a href="https://worldscapemc.co.uk" target="_blank">Samerton (worldscapemc.co.uk)</a><br /><br />
	<strong>Contributors</strong>:<br />
	Wiki created by EvenSafe789<br />
	WordPress conversion script by dwilson390<br />
	<br />
	<strong>External libraries</strong>:<br />
	<a href="https://github.com/ircmaxell/password_compat" target="_blank">PasswordCompat</a> - Anthony Ferrara (MIT License)<br />
	<a href="https://github.com/ezyang/htmlpurifier" target="_blank">HTMLPurifier</a> - Edward Z. Yang (GNU Lesser General Public License)<br />
	<a href="https://github.com/summernote/summernote" target="_blank">Summernote Editor</a> - easylogic (MIT License)<br />
	<a href="http://getbootstrap.com/" target="_blank">Built with Bootstrap</a> - Twitter, Inc (MIT License)<br />
	<a href="http://bootswatch.com/" target="_blank">Bootswatch themes</a> - Thomas Park (MIT License)<br />
	<a href="https://github.com/xPaw/PHP-Minecraft-Query" target="_blank">PHP Minecraft Query</a> - xPaw (<a href="http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode" target="_blank">License</a>)<br />
	<a href="http://www.white-hat-web-design.co.uk/blog/resizing-images-with-php/" target="_blank">SimpleImage</a> - Simon Jarvis (GNU General Public License)<br />
	<a href="https://github.com/jimmiw/php-time-ago" target="_blank">PHP Time Ago</a> - Jimmi Westerberg (MIT License)<br />
	<a href="https://github.com/onassar/PHP-Pagination" target="_blank">PHP-Pagination</a> - onassar (<a href="https://github.com/onassar/PHP-Pagination/issues/4" target="_blank">Permission</a>)<br />
	Emoticons - Oscar Gruno and Andy Fedosjeenko
		<hr>
	  <?php include("inc/templates/footer.php"); ?> 
	  
    </div> <!-- /container -->
		
	<?php include("inc/templates/scripts.php"); ?>	
  </body>
</html>