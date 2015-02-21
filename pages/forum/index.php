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
		$categories = $forum->getCategories($user->data()->group_id);
	  ?>
	  <div class="row">
		<div class="col-md-9">
		<?php 
		$latest_discussions = $forum->getLatestDiscussions($user->data()->group_id);
		?>
		  <table class="table table-striped">
			<tr>
			  <th>Discussion</th>
			  <th>Stats</th>
			  <th>Last Reply</th>
			</tr>
			<?php
			foreach($latest_discussions as $discussion){
			?>
			<tr>
			  <td>
			    <a href="view_topic/?tid=<?php echo $discussion["id"]; ?>"><?php echo $discussion["title"]; ?></a>
				<br /><small><span rel="tooltip" data-trigger="hover" data-original-title="<?php echo date('d M Y, H:i', strtotime($discussion["date"])); ?>"><?php echo $timeago->inWords($discussion["date"]); ?> ago</span> by <a href="/profile/<?php echo htmlspecialchars($user->IdToMCName($discussion["creator"])); ?>"><?php echo htmlspecialchars($user->IdToName($discussion["creator"])); ?></a> in <a href="view_category/?cid=<?php echo $discussion["category_id"]; ?>"><?php echo str_replace("&amp;", "&", $discussion["category"]); ?></a></small>
			  </td>
			  <td>
				<b><?php echo $discussion["views"]; ?></b> views<br />
				<b><?php echo $discussion["replies"]; ?></b> posts
			  </td>
			  <td>
				<div class="row">
				  <div class="col-md-3">
				    <div class="frame">
					  <a href="/profile/<?php echo htmlspecialchars($user->IdToMCName($discussion["last_user"])); ?>">
					  <?php 
					  $last_user_avatar = $queries->getWhere("users", array("id", "=", $discussion["last_user"]));
					  $last_user_avatar = $last_user_avatar[0]->has_avatar;
					  if($last_user_avatar == '0'){ 
					  ?>
					  <img class="img-centre img-rounded" src="https://cravatar.eu/avatar/<?php echo htmlspecialchars($user->IdToMCName($discussion["last_user"])); ?>/30.png" />
					  <?php } else { 
					  ?>
					  <img class="img-centre img-rounded" style="width:30px; height:30px;" src="<?php echo $user->getAvatar($discussion["last_user"]); ?>" />
					  <?php } ?>
					  </a>
					</div>
				  </div>
				  <div class="col-md-9">
				    <span rel="tooltip" data-trigger="hover" data-original-title="<?php echo date('d M Y, H:i', strtotime($discussion["reply_date"])); ?>"><?php echo $timeago->inWords($discussion["reply_date"]); ?> ago</span><br />by <a href="/profile/<?php echo htmlspecialchars($user->IdToMCName($discussion["last_user"])); ?>"><?php echo htmlspecialchars($user->IdToName($discussion["last_user"])); ?></a>
				  </div>
				</div>
			  </td>
			</tr>
			<?php 
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
				foreach($categories as $category){
				  if($category["parent"] == "true"){
				    ?>
					<li class="nav-header"><?php echo $category["title"]; ?></li>
					<?php 
				  } else {
				    ?>
					<li><a href="view_forum/?fid=<?php echo $category["id"]; ?>"><?php echo str_replace("&amp;", "&", $category["title"]); ?></a></li>
			        <?php 
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
					<?php
					$list = $forum->listCategories($user->data()->group_id);
					$n = 0;
					while ($n < count($list[0])) {
						$topics = $forum->listTopics(escape($list[0][$n]));
						$topics = count($topics[0]);
						$posts = $forum->countPosts(escape($list[0][$n]), 'forum_id');
						echo '<tr><td><a href="view_forum/?fid=' . $list[0][$n] . '"><strong>' . str_replace("&amp;", "&", $list[1][$n]) . '</strong></a><br />' . $list[2][$n] . '</td><td><strong>' . $topics . '</strong> topics<br /><strong>' . $posts . '</strong> posts</td><td><div class="row"><div class="col-md-2"><div class="frame"><a href="/profile/' . htmlspecialchars($user->IdToMCName($list[4][$n])) . '">';
					    if($list[4][$n] !== null){
							$has_avatar = $queries->getWhere("users", array("id", "=", $list[4][$n]));
							$has_avatar = $has_avatar[0]->has_avatar;
							if($has_avatar == '0'){
							echo '<img class="img-centre img-rounded" src="https://cravatar.eu/avatar/' .  htmlspecialchars($user->IdToMCName($list[4][$n])) . '/30.png" />';
							} else { 
							echo '<img class="img-centre img-rounded" style="width:30px; height:30px;" src="' .  $user->getAvatar($list[4][$n]) . '" />';
							}
						} else {
							echo '<img class="img-centre img-rounded" src="https://cravatar.eu/avatar/Steve/30.png" />';
						}
						echo '</a></div></div><div class="col-md-9"><a href="view_topic/?tid=' . $list[5][$n] . '">' . htmlspecialchars($forum->getTitle($list[5][$n])) . '</a><br />by <a href="/profile/' . htmlspecialchars($user->IdToMCName($list[4][$n])) . '">' . htmlspecialchars($user->IdToName($list[4][$n])) . '</a><br />' . date("d M Y, H:i", strtotime($list[3][$n])) . '</div></div></td></tr>';
						$n++;
						$topics = 0;
					}
					?>
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