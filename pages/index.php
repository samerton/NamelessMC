<?php
/* 
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

// Set the page name for the active link in navbar
$page = "home";

require('inc/includes/html/library/HTMLPurifier.auto.php'); // HTML Purifier for news items
 
// Get the default server IP
$default_server = $queries->getWhere("mc_servers", array("is_default", "=", "1"));
$default_server = htmlspecialchars($default_server[0]->ip);
$parts = explode(':', $default_server);
if(count($parts) == 1){
	$default_ip = $parts[0];
	$default_port = 25565;
} else if(count($parts) == 2){
	$default_ip = $parts[0];
	$default_port = $parts[1];
} else {
	echo 'Invalid IP';
	die();
}

if($default_port == 25565){
	$default_server = $default_ip;
}

// Query the server
define( 'MQ_SERVER_ADDR', $default_ip );
define( 'MQ_SERVER_PORT', $default_port );
define( 'MQ_TIMEOUT', 1 );

require('inc/integration/status/MinecraftServerPing.php');

$Info = false;
$Query = null;

try {
	$Query = new MinecraftPing( MQ_SERVER_ADDR, MQ_SERVER_PORT, MQ_TIMEOUT );
	
	$Info = $Query->Query( );
	
	if( $Info === false )
	{
		$Query->Close( );
		$Query->Connect( );
		
		$Info = $Query->QueryOldPre17( );
	}
} catch( MinecraftPingException $e ) {
	$Exception = $e;
}

if( $Query !== null ){
	$Query->Close( );
}
 
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="The homepage for the <?php echo $sitename; ?> online community.">
    <meta name="author" content="Samerton">
    <link rel="icon" href="/assets/favicon.ico">

    <title><?php echo $sitename; ?> &bull; Home</title>
	
	<?php require('inc/templates/header.php'); ?>
	
	<!-- Custom style -->
	<style>
	html {
		overflow-y: scroll;
	}
	.jumbotron {
		margin-bottom: 0px;
		background-image: url(assets/img/background-1920x828.jpg);
		background-position: 0% 25%;
		background-size: cover;
		background-repeat: no-repeat;
		color: white;
	}
	</style>
	
  </head>
  <body>
    <?php require('inc/templates/navbar.php'); ?>
	<div class="container">
	  <?php
		// Display any messages
		if(Session::exists('home')){
			echo Session::flash('home');
		}
		if(Session::exists('info')){
			echo Session::flash('info');
		}
		if(Session::exists('error')){
			echo Session::flash('error');
		}
		
		// Display cookie message if the user's not logged in
		if(!$user->isLoggedIn()) {
	  ?>
	  <!-- Display cookie message -->
	  <div class="alert alert-cookie alert-info alert-dismissible" role="alert">
        <button type="button" class="close close-cookie" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
		<strong>This site uses cookies to enhance your experience.</strong>
		<p>By continuing to browse and interact with this website, you agree with their use.</p>
	  </div>
	  <!-- Display guest message -->
	  <div class="alert alert-info alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
		<strong>Welcome, Guest!</strong>
		<p>In order to gain access to our forums and some new features ingame, please <a class="alert-link" href="signin">sign in</a> or <a class="alert-link" href="register">register</a>!</p>
	  </div>
	  <?php 
	    }
	  ?>
	  
      <div class="jumbotron">
        <h1><?php echo $sitename; ?></h1>
        <p>Join with <strong><?php echo htmlspecialchars($default_server); ?></strong></p>
        <p>The<?php
		if(!empty($Info)){
			if($Info['players']['online'] == 1){
				echo 're is currently ';
			} else {
				echo 're are currently <strong>';
			}
			echo $Info['players']['online'] . '</strong> player';
			if($Info['players']['online'] == 1){
				echo ' ';
			} else {
				echo 's ';
			}
			echo 'online.';
		} else {
			echo ' server is currently offline.';
		}
		?>
		</p>
        <p>
          <a class="btn btn-lg btn-primary" href="/play" role="button">Play &raquo;</a>
        </p>
      </div>
	  
	  <div class="row">
		<div class="col-md-9">
			<h2>News</h2>
			<?php
			$forum = new Forum(); // Initialise the forum to get the latest news
			$latest_news = $forum->getLatestNews(5); // Get latest 5 items

			// Display the news
			if(count($latest_news)){

			    // Initialise HTML Purifier
				$config = HTMLPurifier_Config::createDefault();
				$config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
				$config->set('URI.DisableExternalResources', false);
				$config->set('URI.DisableResources', false);
				$config->set('HTML.Allowed', 'u,p,b,i,small,blockquote,span[style],span[class],p,strong,em,li,ul,ol,div[align],br,img');
				$config->set('CSS.AllowedProperties', array('text-align', 'float', 'color','background-color', 'background', 'font-size', 'font-family', 'text-decoration', 'font-weight', 'font-style', 'font-size'));
				$config->set('HTML.AllowedAttributes', 'href, src, height, width, alt, class, *.style');
				$purifier = new HTMLPurifier($config);
				
				foreach($latest_news as $item){
				?>
		  <div class="panel panel-primary">
		    <div class="panel-heading">
		      <a class="white-text" href="/forum/view_topic/?tid=<?php echo $item["topic_id"]; ?>"><?php echo htmlspecialchars($item["topic_title"]); ?></a>
		      <span class="pull-right">
		        <a href="/profile/<?php echo htmlspecialchars($user->idToMCName($item["author"])); ?>"><?php echo htmlspecialchars($user->idToName($item["author"])); ?></a>
  	      	    <?php 
  	      	    // Avatar
  	      	    $post_user = $queries->getWhere("users", array("id", "=", $item["author"]));
  	      	    $has_avatar = $post_user[0]->has_avatar;
				if($has_avatar == '0'){ 
				?>
				<img class="img-rounded" src="https://cravatar.eu/avatar/<?php echo htmlspecialchars($user->idToMCName($item["author"])); ?>/25.png" />
				<?php } else { ?>
				<img class="img-rounded" style="width:25px; height:25px;" src="<?php echo $user->getAvatar($item["author"], ""); ?>" />
				<?php } ?>
		      </span>
		    </div>
		    <div class="panel-body">
		      <?php echo $purifier->purify(htmlspecialchars_decode($item["content"])); ?>
		      <br />
		      <span class="label label-info"><?php echo date('d M Y, H:i', $item["topic_date"]); ?></span>
		      <span class="pull-right">
		        <span class="label label-danger"><span class="glyphicon glyphicon-comment"></span> <?php echo $item["replies"]; ?> | <span class="glyphicon glyphicon-eye-open"></span> <?php echo $item["topic_views"]; ?></span>
		      </span>
		    </div>
		  </div>
				<?php 
				}
			} else {
			?>
			<strong>No news items yet</strong>
			<?php 
			}
			?>
		</div>
		<div class="col-md-3">
			<h2>Social</h2>
			<?php 
			$twitter_feed = $queries->getWhere("settings", array("name", "=", "twitter_feed_id"));
			$twitter_feed = $twitter_feed[0]->value;
			
			if($twitter_feed !== "null"){ 
			
				$twitter_url = $queries->getWhere("settings", array("name", "=", "twitter_url"));
				$twitter_url = $twitter_url[0]->value;
			
			?>
			<a class="twitter-timeline" data-dnt="true" href="<?php echo htmlspecialchars($twitter_url); ?>"  data-widget-id="<?php echo htmlspecialchars($twitter_feed); ?>">Tweets</a>
			<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
			<?php } else {	?>
			<div class="alert alert-warning">Twitter feed not enabled</div>
			<?php } ?>
		</div>
      </div>
	  
	  <hr>
	  
	  <?php require('inc/templates/footer.php'); ?>
	</div>
	<?php 
	require('inc/templates/scripts.php');
	if(!$user->isLoggedIn()) {
    ?>
	<script>
	jQuery(function( $ ){

		// Check if alert has been closed
		if( $.cookie('alert-box') === 'closed' ){

			$('.alert-cookie').hide();

		}

		 // Grab your button (based on your posted html)
		$('.close-cookie').click(function( e ){

			// Do not perform default action when button is clicked
			e.preventDefault();

			/* If you just want the cookie for a session don't provide an expires
			 Set the path as root, so the cookie will be valid across the whole site */
			$.cookie('alert-box', 'closed', { path: '/' });

		});

	});
	</script>
	<?php 
	}
	?>
  </body>
</html>