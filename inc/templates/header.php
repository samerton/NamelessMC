<?php 
/*
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */
 
$bs_theme = $queries->getWhere("settings", array("name", "=", "bootstrap_theme"));
$bs_theme = htmlspecialchars($bs_theme[0]->value);
 
?>
    <!-- Bootstrap core CSS -->
    <link href="/assets/css/<?php echo $bs_theme; ?>.css?version=1" rel="stylesheet">
	<link href="/assets/css/custom.css" rel="stylesheet">
	<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">