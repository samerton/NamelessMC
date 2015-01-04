<?php 
/*
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 */
?>
    <!-- Bootstrap core CSS -->
    <link href="<?php echo $path; ?>assets/css/<?php echo htmlspecialchars($queries->getWhere("settings", array("name", "=", "bootstrap_theme"))[0]->value); ?>.css?version=1" rel="stylesheet">
	<link href="<?php echo $path; ?>assets/css/custom.css" rel="stylesheet">
	<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="<?php echo $path; ?>assets/css/summernote.css" /> 