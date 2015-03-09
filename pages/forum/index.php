<?php 
/* 
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

// Set the page name for the active link in navbar
$page = "forum";

// Initialise
$forum = new Forum();
$timeago = new Timeago();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo $sitename; ?> Forum Index">
    <meta name="author" content="Samerton">

    <title><?php echo $sitename; ?> &bull; Forum Index</title>
	
	<?php require("inc/templates/header.php"); ?>

	<!-- Custom style -->
	<style>
	.frame {  
		height: 50px;
		width: 100%;
		position: relative;
	}
	.frame-sidebar {  
		height: 50px;
		width: 100%;
		position: relative;
	}
	.img-centre {  
		width: auto;
		height: auto;
		position: absolute;  
		top: 0;  
		bottom: 0;  
		left: 0;  
		right: 0;  
		margin: auto;
	}
	</style>
	
  </head>

  <body>

	<?php require("inc/templates/navbar.php"); ?>

    <div class="container">
	  <?php
		if(!$user->isLoggedIn()) {
	  ?>
	  <!-- Display cookie message -->
	  <div class="alert alert-cookie alert-info alert-dismissible" role="alert">
        <button type="button" class="close close-cookie" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
		<strong>This site uses cookies to enhance your experience.</strong>
		<p>By continuing to browse and interact with this website, you agree with their use.</p>
	  </div>
	  <?php 
	    }
		$forum_layout = $queries->getWhere("settings", array("name", "=", "forum_layout"));
		$forum_layout = $forum_layout[0]->value;
		if($forum_layout == '1'){
			$discussions = $forum->getLatestDiscussions($user->data()->group_id);
			// Order the discussions by date
			usort($discussions, function($a, $b) {
				return $a['topic_reply_date'] - $b['topic_reply_date'];
			});
	  ?>
	  <div class="row">
		<div class="col-md-9">
		  <table class="table table-striped">
			<tr>
			  <th>Discussion</th>
			  <th>Stats</th>
			  <th>Last Reply</th>
			</tr>
			<?php
			$n = 0;
			// Calculate the number of discussions to display (10 max)
			if(count($discussions) <= 10){
				$limit = count($discussions);
			} else {
				$limit = 10;
			}
			
			
			while ($n < $limit) { 
				// Get the name of the forum from the ID
				$forum_name = $queries->getWhere('forums', array('id', '=', $discussions[$n]['forum_id']));
				$forum_name = htmlspecialchars($forum_name[0]->forum_title);
				
				// Get the number of replies
				$posts = $queries->getWhere('posts', array('topic_id', '=', $discussions[$n]['id']));
				$posts = count($posts);
			?>
			<tr>
			  <td>
			    <a href="/forum/view_topic/?tid=<?php echo $discussions[$n]['id']; ?>"><?php echo htmlspecialchars($discussions[$n]['topic_title']); ?></a>
				<br /><small><span rel="tooltip" data-trigger="hover" data-original-title="<?php echo date('d M Y, H:i', $discussions[$n]['topic_date']); ?>"><?php echo $timeago->inWords(date('d M Y, H:i', $discussions[$n]['topic_date'])); ?> ago</span> by <a href="/profile/<?php echo $user->IdToMCName($discussions[$n]['topic_creator']); ?>"><?php echo $user->IdToName($discussions[$n]['topic_creator']); ?></a> in <a href="/forum/view_forum/?fid=<?php echo $discussions[$n]['forum_id']; ?>"><?php echo $forum_name; ?></a></small>
			  </td>
			  <td>
				<b><?php echo $discussions[$n]['topic_views']; ?></b> views<br />
				<b><?php echo $posts; ?></b> posts
			  </td>
			  <td>
				<div class="row">
				  <div class="col-md-3">
				    <div class="frame">
					  <a href="/profile/<?php echo $user->IdToMCName($discussions[$n]['topic_last_user']); ?>">
					  <?php 
					  $last_user_avatar = $queries->getWhere("users", array("id", "=", $discussions[$n]['topic_last_user']));
					  $last_user_avatar = $last_user_avatar[0]->has_avatar;
					  if($last_user_avatar == '0'){ 
					  ?>
					  <img class="img-centre img-rounded" src="https://cravatar.eu/avatar/<?php echo $user->IdToMCName($discussions[$n]['topic_last_user']); ?>/30.png" />
					  <?php } else { ?>
					  <img class="img-centre img-rounded" style="width:30px; height:30px;" src="<?php echo $user->getAvatar($discussions[$n]['topic_last_user'], "../"); ?>" />
					  <?php } ?>
					  </a>
					</div>
				  </div>
				  <div class="col-md-9">
				    <span rel="tooltip" data-trigger="hover" data-original-title="<?php echo date('d M Y, H:i', $discussions[$n]['topic_reply_date']); ?>"><?php echo $timeago->inWords(date('d M Y, H:i', $discussions[$n]['topic_reply_date'])); ?> ago</span><br />by <a href="/profile/<?php echo $user->IdToMCName($discussions[$n]['topic_last_user']); ?>"><?php echo $user->IdToName($discussions[$n]['topic_last_user']); ?></a>
				  </div>
				</div>
			  </td>
			</tr>
			<?php
				$n++;
			}
			?>
		  </table>
		 </div>
		<div class="col-md-3">
		  <div class="well">
			<h4>Forums</h4>
			<ul class="nav nav-list">
			  <li class="nav-header">Overview</li>
			  <li class="active"><a href="/forum">Latest Discussions</a></li>
			  <?php
			  $forums = $forum->listAllForums($user->data()->group_id);
			  foreach($forums as $item => $value){ 
			    $value = array_filter($value);
				if(empty($value)){
			  ?>
			  <li class="nav-header"><?php echo htmlspecialchars($item); ?></li>
			  <?php
			    } else {
				  foreach($value as $sub_forum){
					// Get the forum ID
					$forum_id = $queries->getWhere("forums", array("forum_title", "=", $sub_forum));
					$forum_id = $forum_id[0]->id;
			  ?>
			  <li><a href="/forum/view_forum/?fid=<?php echo $forum_id; ?>"><?php echo htmlspecialchars($sub_forum); ?></a></li>
			  <?php
				  }
				}
			  }
			  ?>
			</ul>
		  </div>
		  <div class="well">
			<h4>Statistics</h4>
			<?php $user_stats = $queries->orderAll("users", "joined", "DESC"); ?>
			<strong>Users registered:</strong> <?php echo count($user_stats); ?><br />
			<strong>Latest member:</strong> <a href="/profile/<?php echo htmlspecialchars($user_stats[0]->mcname); ?>"><?php echo htmlspecialchars($user_stats[0]->username); ?></a>
		  </div>
		</div>
	  </div>
	  <?php 
		} else {
	  ?>
	  <div class="row">
	    <div class="col-md-9">
		    <table class="table table-bordered">
			    <thead>
					<tr>
					  <th>Forum</th>
					  <th>Stats</th>
					  <th>Last Post</th>
					</tr>
			    </thead>
			    <tbody>
					<tr>
					  <td><a href="/forum/view_forum/?fid=1">General Discussion</a><br />General server discussion</td>
					  <td><strong>1</strong> topic<br /><strong>1</strong> post</td>
					  <td>
					  <div class="row">
					    <div class="col-md-2">
						  <div class="frame">
						    <a href="/profile/Samerton"><img class="img-centre img-rounded" src="https://cravatar.eu/avatar/Samerton/30.png" /></a>
						  </div>
						</div>
					    <div class="col-md-9">
						  <a href="/forum/view_topic/?tid=2">Website Rules</a><br />
						  by <a href="/profile/Samerton">Samerton</a><br />17 Jan 2015, 19:03
						</div>
					  </div>
					  </td>
					</tr>
			    </tbody>
		    </table>
			<div class="panel panel-default">
				<div class="panel-heading">
					Site statistics
				</div>
				<div class="panel-body">
					<?php $user_stats = $queries->orderAll("users", "joined", "DESC"); ?>
					<strong>Users registered:</strong> <?php echo count($user_stats); ?><br />
					<strong>Latest member:</strong> <a href="/profile/<?php echo $user_stats[0]->mcname; ?>"><?php echo $user_stats[0]->username; ?></a>
				</div>
			</div>
	    </div>
		<div class="col-md-3">
			<div class="panel panel-default">
				<div class="panel-heading">
					Latest Posts
				</div>
				<div class="panel-body">
					Coming soon
				</div>
			</div>
		</div>
	  </div>
      <?php 	  
		}
	  ?>
      <hr>

	  <?php require("inc/templates/footer.php"); ?> 
	  
    </div> <!-- /container -->
		
	<?php require("inc/templates/scripts.php"); ?>
	<script>
	$(document).ready(function(){
		$("[rel=tooltip]").tooltip({ placement: 'top'});
	});
	</script>
	<?php
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