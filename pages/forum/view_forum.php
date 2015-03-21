<?php 
/*
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

// Set the page name for the active link in navbar
$page = "forum";

$forum = new Forum();
$timeago = new Timeago();
$paginate = new Pagination();

require('inc/functions/paginate.php'); // Get number of users on a page

if(!isset($_GET['fid']) || !is_numeric($_GET['fid'])){
	Redirect::to('/forum/error/?error=not_exist');
	die();
}

$fid = (int) $_GET['fid'];

// Does the forum exist, and can the user view it?
$list = $forum->forumExist($fid, $user->data()->group_id);
if(!$list){
	Redirect::to('/forum/error/?error=not_exist');
	die();
}


// Get page
if(isset($_GET['p'])){
	if(!is_numeric($_GET['p'])){
		Redirect::to("/forum");
		die();
	} else {
		if($_GET['p'] == 1){ 
			// Avoid bug in pagination class
			Redirect::to('/forum/view_forum/?fid=' . $fid);
			die();
		}
		$p = $_GET['p'];
	}
} else {
	$p = 1;
}

// Get data from the database
$forum_query = $queries->getWhere("forums", array("id", "=", $fid));
$forum_query = $forum_query[0];

// Is it a parent forum? If so, the user shouldn't be browsing it
if($forum_query->parent == 0){
	Redirect::to('/forum/error/?error=not_exist');
	die();
}

// Get all topics
$topics = $queries->orderWhere("topics", "forum_id = ". $fid . " AND sticky = 0", "topic_reply_date", "DESC");

// Get sticky topics
$stickies = $queries->orderWhere("topics", "forum_id = " . $fid . " AND sticky = 1", "topic_reply_date", "DESC");

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo $sitename; ?> Forum - <?php echo htmlspecialchars($forum_query->forum_title); ?>">
    <meta name="author" content="Samerton">
    <link rel="icon" href="/favicon.ico">

    <title><?php echo $sitename; ?> &bull; Forum - <?php echo htmlspecialchars($forum_query->forum_title); ?></title>
	
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
	  <!-- Display guest message -->
	  <div class="alert alert-info alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
		<strong>Welcome, Guest!</strong>
		<p>In order to gain access to our forums and some new features ingame, please <a class="alert-link" href="/signin">sign in</a> or <a class="alert-link" href="/register">register</a>!</p>
	  </div>
	  <?php 
	    }
	  if(Session::exists('success_post')){
		echo '<center>' . Session::flash('success_post') . '</center>';
	  }
	  $forum_layout = $queries->getWhere("settings", array("name", "=", "forum_layout"));
	  $forum_layout = $forum_layout[0]->value;
	  if($forum_layout == '1'){
	  ?>
	  <div class="row">
		<div class="col-md-9">
		  <?php if(count($topics) || count($stickies)) { ?>
		  <h3 style="display: inline;"><?php echo htmlspecialchars($forum_query->forum_title); ?></h3><?php if($user->isLoggedIn() && $forum->canPostTopic($fid, $user->data()->group_id)){ ?><span class="pull-right"><a style="display: inline;" class="btn btn-primary" href="/forum/new_topic/?fid=<?php echo $fid; ?>">New Topic</a></span><?php } ?>
		  <br /><br />
		  <table class="table table-striped">
			<tr>
			  <th>Discussion</th>
			  <th>Stats</th>
			  <th>Last Reply</th>
			</tr>
			<?php
			// First, get sticky threads
			foreach($stickies as $sticky){
				// Get number of replies to a topic
				$replies = $queries->getWhere("posts", array("topic_id", "=", $sticky->id));
				$replies = count($replies);
			?>
			<tr>
			  <td>
			    <span class="glyphicon glyphicon-pushpin"></span> <a href="/forum/view_topic/?tid=<?php echo $sticky->id; ?>"><?php echo htmlspecialchars($sticky->topic_title); ?></a>
				<br /><small><span rel="tooltip" data-trigger="hover" data-original-title="<?php echo date('d M Y, H:i', $sticky->topic_date); ?>"><?php echo $timeago->inWords(date('d M Y, H:i', $sticky->topic_date)); ?> ago</span> by <a href="/profile/<?php echo htmlspecialchars($user->idToMCName($sticky->topic_creator)); ?>"><?php echo htmlspecialchars($user->idToName($sticky->topic_creator)); ?></a></small>
			  </td>
			  <td>
				<b><?php echo $sticky->topic_views; ?></b> views<br />
				<b><?php echo $replies; ?></b> posts
			  </td>
			  <td>
				<div class="row">
				  <div class="col-md-3">
				    <div class="frame">
					  <a href="/profile/<?php echo htmlspecialchars($user->idToMCName($sticky->topic_last_user)); ?>">
					  <?php 
					  $last_user_avatar = $queries->getWhere("users", array("id", "=", $sticky->topic_last_user));
					  $last_user_avatar = $last_user_avatar[0]->has_avatar;
					  if($last_user_avatar == '0'){ 
					  ?>
					  <img class="img-centre img-rounded" src="https://cravatar.eu/avatar/<?php echo htmlspecialchars($user->idToMCName($sticky->topic_last_user)); ?>/30.png" />
					  <?php } else { ?>
					  <img class="img-centre img-rounded" style="width:30px; height:30px;" src="<?php echo $user->getAvatar($sticky->topic_last_user, "../../"); ?>" />
					  <?php } ?>
					  </a>
					</div>
				  </div>
				  <div class="col-md-9">
				    <span rel="tooltip" data-trigger="hover" data-original-title="<?php echo date('d M Y, H:i', $sticky->topic_reply_date); ?>"><?php echo $timeago->inWords(date('d M Y, H:i', $sticky->topic_reply_date)); ?> ago</span><br />by <a href="/profile/<?php echo htmlspecialchars($user->idToMCName($sticky->topic_last_user)); ?>"><?php echo htmlspecialchars($user->idToName($sticky->topic_last_user)); ?></a>
				  </div>
				</div>
			  </td>
			</tr>
			<?php 
			}
			?>
			<?php
			// PAGINATION
			// instantiate; set current page; set number of records
			$pagination = (new Pagination());
			$pagination->setCurrent($p);
			$pagination->setTotal(count($topics));
			$pagination->alwaysShowPagination();

			// Get number of users we should display on the page
			$paginate = PaginateArray($p);

			$n = $paginate[0];
			$f = $paginate[1];
			
			// Get the number we need to finish on ($d)
			if(count($topics) > $f){
				$d = $p * 10;
			} else {
				$d = count($topics) - $n;
				$d = $d + $n;
			}

			// Get a list of all topics from the forum, and paginate
			while ($n < $d) {
				// Get number of replies to a topic
				$replies = $queries->getWhere("posts", array("topic_id", "=", $topics[$n]->id));
				$replies = count($replies);
			?>
			<tr>
			  <td>
			    <a href="/forum/view_topic/?tid=<?php echo $topics[$n]->id; ?>"><?php echo htmlspecialchars($topics[$n]->topic_title); ?></a>
				<br /><small><span rel="tooltip" data-trigger="hover" data-original-title="<?php echo date('d M Y, H:i', $topics[$n]->topic_date); ?>"><?php echo $timeago->inWords(date('d M Y, H:i', $topics[$n]->topic_date)); ?> ago</span> by <a href="/profile/<?php echo htmlspecialchars($user->idToMCName($topics[$n]->topic_creator)); ?>"><?php echo htmlspecialchars($user->idToName($topics[$n]->topic_creator)); ?></a></small>
			  </td>
			  <td>
				<b><?php echo $topics[$n]->topic_views; ?></b> views<br />
				<b><?php echo $replies; ?></b> posts
			  </td>
			  <td>
				<div class="row">
				  <div class="col-md-3">
				    <div class="frame">
					  <a href="/profile/<?php echo htmlspecialchars($user->idToMCName($topics[$n]->topic_last_user)); ?>">
					  <?php 
					  $last_user_avatar = $queries->getWhere("users", array("id", "=", $topics[$n]->topic_last_user));
					  $last_user_avatar = $last_user_avatar[0]->has_avatar;
					  if($last_user_avatar == '0'){ 
					  ?>
					  <img class="img-centre img-rounded" src="https://cravatar.eu/avatar/<?php echo htmlspecialchars($user->idToMCName($topics[$n]->topic_last_user)); ?>/30.png" />
					  <?php } else { ?>
					  <img class="img-centre img-rounded" style="width:30px; height:30px;" src="<?php echo $user->getAvatar($topics[$n]->topic_last_user, "../../"); ?>" />
					  <?php } ?>
					  </a>
					</div>
				  </div>
				  <div class="col-md-9">
				    <span rel="tooltip" data-trigger="hover" data-original-title="<?php echo date('d M Y, H:i', $topics[$n]->topic_reply_date); ?>"><?php echo $timeago->inWords(date('d M Y, H:i', $topics[$n]->topic_reply_date)); ?> ago</span><br />by <a href="/profile/<?php echo htmlspecialchars($user->idToMCName($topics[$n]->topic_last_user)); ?>"><?php echo htmlspecialchars($user->idToName($topics[$n]->topic_last_user)); ?></a>
				  </div>
				</div>
			  </td>
			</tr>
			<?php
				$n++;
			}
			?>	
		  </table>
		  <?php
		    echo $pagination->parse(); // Print pagination
		  } else { ?>
		  <h4 style="display: inline;">No topics in <strong><?php echo htmlspecialchars($forum_query->forum_title); ?></strong> yet</h4>
		    <?php if($user->isLoggedIn() && $forum->canPostTopic($fid, $user->data()->group_id)){ ?>
		  <span class="pull-right"><a style="display: inline;" href="/forum/new_topic/?fid=<?php echo $fid; ?>" class="btn btn-primary">New Topic</a></span>
		    <?php 
			} 
		  } 
		  ?>
		 </div>
		<div class="col-md-3">
		  <div class="well">
			<h4>Forums</h4>
			<ul class="nav nav-list">
			  <li class="nav-header">Overview</li>
			  <li><a href="/forum">Latest Discussions</a></li>
			    <?php
			    $forums = $forum->listAllForums($user->data()->group_id);
			    foreach($forums as $item => $value){ 
			      $value = array_filter($value);
				  if(!empty($value)){
			    ?>
			    <li class="nav-header"><?php echo htmlspecialchars($item); ?></li>
			    <?php
			  	  foreach($value as $sub_forum){
					// Get the forum ID
					$forum_id = $queries->getWhere("forums", array("forum_title", "=", $sub_forum));
					$forum_id = $forum_id[0]->id;
			    ?>
			    <li<?php if($forum_query->forum_title == $sub_forum){ ?> class="active"<?php } ?>><a href="/forum/view_forum/?fid=<?php echo $forum_id; ?>"><?php echo htmlspecialchars($sub_forum); ?></a></li>
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
		    <?php
		    if(count($topics)){
		    ?>
			<h3 style="display: inline;"><?php echo htmlspecialchars($forum_query->forum_title); ?></h3><?php if($user->isLoggedIn() && $forum->canPostTopic($fid, $user->data()->group_id)){ ?><span class="pull-right"><a style="display: inline;" class="btn btn-primary" href="/forum/new_topic/?fid=<?php echo $fid; ?>">New Topic</a></span><?php } ?>
		    <br /><br />
			<table class="table table-bordered">
			    <thead>
					<tr>
					  <th>Topic</th>
					  <th>Stats</th>
					  <th>Last Post</th>
					</tr>
			    </thead>
			    <tbody>
			    <?php
				// PAGINATION
				// instantiate; set current page; set number of records
				$pagination = (new Pagination());
				$pagination->setCurrent($p);
				$pagination->setTotal(count($topics));
				$pagination->alwaysShowPagination();

				// Get number of users we should display on the page
				$paginate = PaginateArray($p);

				$n = $paginate[0];
				$f = $paginate[1];
				
				// Get the number we need to finish on ($d)
				if(count($topics) > $f){
					$d = $p * 10;
				} else {
					$d = count($topics) - $n;
					$d = $d + $n;
				}
				// Get a list of all topics from the forum, and paginate
				while ($n < $d) {
					// Get number of replies to a topic
					$replies = $queries->getWhere("posts", array("topic_id", "=", $topics[$n]->id));
					$replies = count($replies);
				?>
				  <tr>
				    <td><a href="/forum/view_topic/?tid=<?php echo $topics[$n]->id; ?>"><?php echo htmlspecialchars($topics[$n]->topic_title); ?></a><br />
				    By <a href="/profile/<?php echo htmlspecialchars($user->idToMCName($topics[$n]->topic_creator)); ?>"><?php echo htmlspecialchars($user->idToName($topics[$n]->topic_creator)); ?></a> | <span rel="tooltip" data-trigger="hover" data-original-title="<?php echo date('d M Y, H:i', $topics[$n]->topic_date); ?>"><?php echo $timeago->inWords(date('d M Y, H:i', $topics[$n]->topic_date)); ?> ago</span>
				    </td>
				    <td><strong><?php echo $topics[$n]->topic_views; ?></strong> views<br /><strong><?php echo $replies; ?></strong> posts</td>
				    <td>
				    <div class="row">
				      <div class="col-md-2">
					    <div class="frame">
					      <a href="/profile/<?php echo htmlspecialchars($user->idToMCName($topics[$n]->topic_last_user)); ?>">
						  <?php 
						  $last_user_avatar = $queries->getWhere("users", array("id", "=", $topics[$n]->topic_last_user));
						  $last_user_avatar = $last_user_avatar[0]->has_avatar;
						  if($last_user_avatar == '0'){ 
						  ?>
						  <img class="img-centre img-rounded" src="https://cravatar.eu/avatar/<?php echo htmlspecialchars($user->idToMCName($topics[$n]->topic_last_user)); ?>/30.png" />
						  <?php } else { ?>
						  <img class="img-centre img-rounded" style="width:30px; height:30px;" src="<?php echo $user->getAvatar($topics[$n]->topic_last_user, "../../"); ?>" />
						  <?php } ?>
					      </a>
					    </div>
					  </div>
				      <div class="col-md-9">
					    <a href="/profile/<?php echo htmlspecialchars($user->idToMCName($topics[$n]->topic_last_user)); ?>"><?php echo htmlspecialchars($user->idToName($topics[$n]->topic_last_user)); ?></a><br /><span rel="tooltip" data-trigger="hover" data-original-title="<?php echo date('d M Y, H:i', $topics[$n]->topic_reply_date); ?>"><?php echo $timeago->inWords(date('d M Y, H:i', $topics[$n]->topic_reply_date)); ?> ago</span>
					  </div>
				    </div>
				    </td>
				  </tr>
				<?php
					$n++;
				}
				?>
			    </tbody>
		    </table>
			<?php 
			  echo $pagination->parse(); // Print pagination
		    } else { ?>
		    <h4 style="display: inline;">No topics in <strong><?php echo htmlspecialchars($forum_query->forum_title); ?></strong> yet</h4>
		      <?php if($user->isLoggedIn() && $forum->canPostTopic($fid, $user->data()->group_id)){ ?>
		    <span class="pull-right"><a style="display: inline;" href="/forum/new_topic/?fid=<?php echo $fid; ?>" class="btn btn-primary">New Topic</a></span><br /><br /><br />
		      <?php 
			  } 
		    } 
		    ?>
			<div class="panel panel-default">
				<div class="panel-heading">
					Site statistics
				</div>
				<div class="panel-body">
					<?php $user_stats = $queries->orderAll("users", "joined", "DESC"); ?>
					<strong>Users registered:</strong> <?php echo count($user_stats); ?><br />
					<strong>Latest member:</strong> <a href="/profile/<?php echo htmlspecialchars($user_stats[0]->mcname); ?>"><?php echo htmlspecialchars($user_stats[0]->username); ?></a>
				</div>
			</div>
	    </div>
		<div class="col-md-3">
			<div class="panel panel-default">
				<div class="panel-heading">
					Latest Posts
				</div>
				<div class="panel-body">
					<?php
					// Here we can use the getLatestDiscussions function
					$latest = $forum->getLatestDiscussions($user->data()->group_id);
					$n = 0;
					foreach($latest as $item){
						if($n >= 5){
							break;
						}
					?>
				  <div class="row">
					<div class="col-md-2">
					  <div class="frame">
					    <a href="/profile/<?php echo htmlspecialchars($user->IdToMCName($item["topic_last_user"])); ?>"><img class="img-centre img-rounded" src="https://cravatar.eu/avatar/<?php echo htmlspecialchars($user->IdToMCName($item["topic_last_user"])); ?>/30.png" /></a>
					  </div>
					</div>
					<div class="col-md-9">
					  <a href="/forum/view_topic/?tid=<?php echo $item["id"]; ?>"><?php echo htmlspecialchars($item["topic_title"]); ?></a><br />
					  by <a href="/profile/<?php echo htmlspecialchars($user->IdToMCName($item["topic_last_user"])); ?>"><?php echo htmlspecialchars($user->IdToName($item["topic_last_user"])); ?></a><br /><span rel="tooltip" data-trigger="hover" data-original-title="<?php echo date('d M Y, H:i', $item["topic_reply_date"]); ?>"><?php echo $timeago->inWords(date('d M Y, H:i', $item["topic_reply_date"])); ?> ago</span>
					</div>
				  </div>
				  <hr>
					<?php
						$n++;
					}
					?>
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