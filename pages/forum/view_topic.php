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
require('inc/includes/html/library/HTMLPurifier.auto.php'); // HTML Purifier

if(!isset($_GET['tid']) || !is_numeric($_GET['tid'])){
	Redirect::to('/forum/error/?error=not_exist');
	die();
}

$tid = (int) $_GET['tid'];

// Does the topic exist, and can the user view it?
$list = $forum->topicExist($tid, $user->data()->group_id);
if(!$list){
	Redirect::to('/forum/error/?error=not_exist');
	die();
}

// Is the URL pointing to a specific post?
if(isset($_GET['pid'])){
	$posts = $queries->getWhere("posts", array("topic_id", "=", $tid));
	if(count($posts)){
		$i = 0;
		while($i < count($posts)){
			if ($posts[$i]->id == $_GET['pid']) {
				$output = $i + 1;
				break;
			}
			$i++;
		}
		if(ceil($output / 10) != $p){
			Redirect::to('/forum/view_topic/?tid=' . $tid . '&p=' . ceil($output / 10) . '#post-' . $_GET['pid']);
			die();
		} else {
			Redirect::to('/forum/view_topic/?tid=' . $tid . '#post-' . $_GET['pid']);
			die();
		}
		
	} else {
		Redirect::to('/forum/error/?error=not_exist');
		die();
	}
}

// Get page
if(isset($_GET['p'])){
	if(!is_numeric($_GET['p'])){
		Redirect::to("/forum");
		die();
	} else {
		if($_GET['p'] == 1){ 
			// Avoid bug in pagination class
			Redirect::to('/forum/view_topic/?tid=' . $tid);
			die();
		}
		$p = $_GET['p'];
	}
} else {
	$p = 1;
}

// Get the topic information
$topic = $queries->getWhere("topics", array("id", "=", $tid));
$topic = $topic[0];

// Get all posts in the topic
$posts = $queries->getWhere("posts", array("topic_id", "=", $tid));

// Can the user post a reply in this topic?
$can_reply = $forum->canPostReply($topic->forum_id, $user->data()->group_id);

// Quick reply
if(Input::exists()) {
	if(!$user->isLoggedIn() && !$can_reply){ 
		Redirect::to('/forum');
		die();
	}
	if(Token::check(Input::get('token'))){
		$validate = new Validate();
		$validation = $validate->check($_POST, array(
			'content' => array(
				'required' => true,
				'min' => 2,
				'max' => 20480
			)
		));
		if($validation->passed()){
			try {
				$queries->create("posts", array(
					'forum_id' => $topic->forum_id,
					'topic_id' => $tid,
					'post_creator' => $user->data()->id,
					'post_content' => htmlspecialchars(Input::get('content')),
					'post_date' => date('Y-m-d H:i:s')
				));
				$queries->update("forums", $topic->forum_id, array(
					'last_topic_posted' => $tid,
					'last_user_posted' => $user->data()->id,
					'last_post_date' => date('Y-m-d H:i:s')
				));
				$queries->update("topics", $tid, array(
					'topic_last_user' => $user->data()->id,
					'topic_reply_date' => date('U')
				));
				Session::flash('success_post', '<div class="alert alert-info alert-dismissable"> <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>Post submitted.</div>');
				Redirect::to('/forum/view_topic/?tid=' . $tid);
				die();
			} catch(Exception $e){
				die($e->getMessage());
			}
		} else {
			$error_string = "";
			foreach($validation->errors() as $error) {
				$error_string .= ucfirst($error) . '<br />';
			}
			Session::flash('failure_post', '<div class="alert alert-danger alert-dismissable"> <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>' . $error_string . '</div>');
		}
	} else {
		// Invalid token - TODO: improve
		//echo 'Invalid token';

	}
}

// Generate a post token
if($user->isLoggedIn()){
	$token = Token::generate();
}

// View count
if(!Cookie::exists('nl-topic-' . $tid)) {
	$queries->increment("topics", $tid, "topic_views");
	Cookie::put("nl-topic-" . $tid, "true", 3600);
}

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo $sitename; ?> Forum - Topic: <?php echo htmlspecialchars($topic->topic_title); ?>">
    <meta name="author" content="Samerton">
    <link rel="icon" href="/assets/favicon.ico">

    <title><?php echo $sitename; ?> &bull; Forum - <?php echo htmlspecialchars($topic->topic_title); ?></title>
	
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
	  <ol class="breadcrumb">
	    <li><a href="/forum">Home</a></li>
		<li><a href="/forum/view_forum/?fid=<?php echo htmlspecialchars($topic->forum_id); ?>"><?php echo htmlspecialchars($forum->getForumTitle($topic->forum_id)); ?></a></li>
	    <li class="active"><?php echo htmlspecialchars($topic->topic_title); ?></li>
	  </ol>
	<?php
	if(Session::exists('success_post')){
		echo '<center>' . Session::flash('success_post') . '</center>';
	}
	if(Session::exists('failure_post')){
		echo '<center>' . Session::flash('failure_post') . '</center>';
	}

	// Can the user post a reply?
	if($user->isLoggedIn() && $can_reply){
		// Is the topic locked?
		if($topic->locked != 1){ // Not locked
	?>
	  <a href="/forum/create_post/?tid=<?php echo $tid; ?>&amp;fid=<?php echo $topic->forum_id; ?>" class="btn btn-primary">New Reply</a>
	<?php 
		} else { // Locked
	?>
	  <a class="btn btn-primary" disabled="disabled">Topic locked</a>
	<?php
		}
	}
	
	// Is the user a moderator?
	if($user->isLoggedIn() && ($user->data()->group_id == 2 || $user->data()->group_id == 3)){
	?>
	  <span class="pull-right">
		<div class="btn-group">
		  <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
			Mod Actions <span class="caret"></span>
		  </button>
		  <ul class="dropdown-menu" role="menu">
			<li><a href="/forum/lock_thread/?tid=<?php echo $tid; ?>"><?php if($topic->locked == 1){ echo 'Unl'; } else { echo 'L'; } ?>ock Thread</a></li>
			<li><a href="/forum/merge_thread/?tid=<?php echo $tid; ?>">Merge Thread</a></li>
			<li><a href="/forum/delete_thread/?tid=<?php echo $tid; ?>" onclick="return confirm('Are you sure you want to delete this thread?')">Delete Thread</a></li>
			<li><a href="/forum/move_thread/?tid=<?php echo $tid; ?>">Move Thread</a></li>
			<li><a href="/forum/sticky_thread/?tid=<?php echo $tid; ?>">Sticky Thread</a></li>
		  </ul>
		</div>
	  </span>
	<?php 
	}
	?>
	  <br /><br />
	  <?php 
	  // PAGINATION
	  // instantiate; set current page; set number of records
	  $pagination = new Pagination();
	  $pagination->setCurrent($p);
	  $pagination->setTotal(count($posts));
	  $pagination->alwaysShowPagination();

	  // Get number of users we should display on the page
	  $paginate = PaginateArray($p);

	  $n = $paginate[0];
	  $f = $paginate[1];
		
	  // Get the number we need to finish on ($d)
	  if(count($posts) > $f){
		$d = $p * 10;
	  } else {
		$d = count($posts) - $n;
		$d = $d + $n;
      }

	  echo $pagination->parse(); // Print pagination

      // Initialise HTML Purifier
	  $config = HTMLPurifier_Config::createDefault();
	  $config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
	  $config->set('URI.DisableExternalResources', false);
	  $config->set('URI.DisableResources', false);
	  $config->set('HTML.Allowed', 'u,p,b,i,small,blockquote,span[style],span[class],p,strong,em,li,ul,ol,div[align],br,img');
	  $config->set('CSS.AllowedProperties', array('text-align', 'float', 'color','background-color', 'background', 'font-size', 'font-family', 'text-decoration', 'font-weight', 'font-style', 'font-size'));
	  $config->set('HTML.AllowedAttributes', 'href, src, height, width, alt, class, *.style');
	  $config->set('HTML.SafeIframe', true);
	  $config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%');
	  $purifier = new HTMLPurifier($config);

	  // Display the correct number of posts
	  while ($n < $d) {
	  	// Get user's group HTML formatting and their signature
	  	$user_group = $user->getGroup($posts[$n]->post_creator, "true");
		$signature = $user->getSignature($posts[$n]->post_creator);
	  ?>
	  <div class="panel panel-primary">
  	    <div class="panel-heading">
  	      <a href="/forum/view_topic/?tid=<?php echo $tid; ?>&amp;pid=<?php echo $posts[$n]->id; ?>" class="white-text"><?php if($n != 0){ echo "RE: "; } echo htmlspecialchars($topic->topic_title); ?></a> <?php if($topic->locked == 1){ echo '<span class="glyphicon glyphicon-lock"></span>'; } ?>
  	    </div>
  	    <div class="panel-body" id="post-<?php echo $posts[$n]->id; ?>">
  	      <div class="row">
  	      	<div class="col-md-3">
  	      	    <center>
  	      	    <?php 
  	      	    // Avatar
  	      	    $post_user = $queries->getWhere("users", array("id", "=", $posts[$n]->post_creator));
  	      	    $has_avatar = $post_user[0]->has_avatar;
				if($has_avatar == '0'){ 
				?>
				<img class="img-rounded" src="https://cravatar.eu/avatar/<?php echo htmlspecialchars($post_user[0]->mcname); ?>/100.png" />
				<?php } else { ?>
				<img class="img-rounded" style="width:100px; height:100px;" src="<?php echo $user->getAvatar($posts[$n]->post_creator, "../../"); ?>" />
				<?php } ?>

				<br /><br />

				<strong><a href="/profile/<?php echo htmlspecialchars($post_user[0]->mcname); ?>"><?php echo htmlspecialchars($post_user[0]->username); ?></a></strong>
  	      	  	<br />
  	      	  	<?php echo $purifier->purify($user_group); ?>

  	      	  	<hr>
  	      	  	<?php echo count($queries->getWhere("posts", array("post_creator", "=", $posts[$n]->post_creator))); ?> posts<br />
  	      	  	<?php echo $post_user[0]->reputation; ?> reputation

  	      	  	<br /><br />

  	      	  	</center>

				<blockquote>
					<small>IGN: <?php echo htmlspecialchars($post_user[0]->mcname); ?></small>
				</blockquote>
  	      	</div>

  	      	<div class="col-md-9">
  	      	  By <a href="/profile/<?php echo htmlspecialchars($post_user[0]->mcname); ?>"><?php echo htmlspecialchars($post_user[0]->username); ?></a> &raquo; <span rel="tooltip" data-trigger="hover" data-original-title="<?php echo date('d M Y, H:i', strtotime($posts[$n]->post_date)); ?>"><?php echo $timeago->inWords($posts[$n]->post_date); ?> ago</span>
  	      	  <?php if($user->isLoggedIn()) { ?>
  	      	  <span class="pull-right">
  	      	    <?php 
  	      	    // Edit button
  	      	    if($user->data()->group_id == 2 || $user->data()->group_id == 3){ 
  	      	    ?>
  	      	    <a rel="tooltip" title="Edit post" href="/forum/edit_post/?pid=<?php echo $posts[$n]->id; ?>&amp;tid=<?php echo $tid; ?>" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-pencil"></span></a>
  	      	    <?php 
  	      	    } else if($user->data()->id == $posts[$n]->post_creator) { 
  	      	    	if($topic->locked != 1){ 
  	      	    ?>
  	      	    <a rel="tooltip" title="Edit post" href="/forum/edit_post/?pid=<?php echo $posts[$n]->id; ?>&amp;tid=<?php echo $tid; ?>" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-pencil"></span></a>
  	      	    <?php 
  	      	    	}
  	      	    } 

  	      	    // Delete button
				if($user->data()->group_id == 2 || $user->data()->group_id == 3){ // Mods/admins only
				?>
				<form onsubmit="return confirm(\'Are you sure you want to delete this post?\');" style="display: inline;" action="/forum/delete_post" method="post">
					<input type="hidden" name="pid" value="<?php echo $posts[$n]->id; ?>" />
					<input type="hidden" name="tid" value="<?php echo $tid; ?>" />
					<input type="hidden" name="number" value="<?php echo $n; ?>" />
					<input type="hidden" name="token" value="<?php echo $token; ?>">
					<button rel="tooltip" title="Delete post" type="submit" class="btn btn-danger btn-xs">
					  <span class="glyphicon glyphicon-trash"></span>
					</button>
				</form>
				<?php
				}

				// Report button
				?>
				<a rel="tooltip" title="Report post" href="/forum/report_post/?pid=<?php echo $posts[$n]->id; ?>&amp;tid=<?php echo $tid; ?>" class="btn btn-warning btn-xs"><span class="glyphicon glyphicon-exclamation-sign"></span></a>

				<?php 
				// Quote button
				if($can_reply){
					if($user->data()->group_id == 2 || $user->data()->group_id == 3){ 
					?>
						<a rel="tooltip" title="Quote post" href="/forum/create_post/?tid=<?php echo $tid; ?>&amp;qid=<?php echo $posts[$n]->id; ?>&amp;fid=<?php echo $topic->forum_id; ?>" class="btn btn-info btn-xs"><span class="glyphicon glyphicon-share"></span></a>
					<?php 
					} else { 
						if($topic->locked != 1){ 
					?>
						<a rel="tooltip" title="Quote post" href="/forum/create_post/?tid=<?php echo $tid; ?>&amp;qid=<?php echo $posts[$n]->id; ?>&amp;fid=<?php echo $topic->forum_id; ?>" class="btn btn-info btn-xs"><span class="glyphicon glyphicon-share"></span></a>
					<?php
						}
					}
				?>

				<?php 
				}
				?>

  	      	  </span>
  	      	  <?php } ?>

  	      	  <hr>

  	      	  <?php 
  	      	  // Purify the post content
  	      	  $clean = $purifier->purify(htmlspecialchars_decode($posts[$n]->post_content));
  	      	  echo $clean;

  	      	  // Get the post's reputation
  	      	  $reputation = $forum->getReputation(htmlspecialchars($posts[$n]->id));
  	      	  ?>
  	      	  <br />
  	      	  <span class="pull-right">
  	      	  	<?php
  	      	  	// Reputation - guests and the post author can't upvote this post
  	      	  	if($user->isLoggedIn() && $user->data()->id !== $posts[$n]->post_creator){
  	      	  	  if(count($reputation)){
  	      	  	  	foreach($reputation as $rep){
  	      	  	  	  // Has the user already given reputation to this post?
					  if($user->data()->id == $rep->user_given){
						$user_has_given = true;
						break;
					  } else {
						$user_has_given = false;
					  }
					}
					if($user_has_given === false){
  	      	  		?>
				<form style="display: inline;" action="/forum/reputation/" method="post">
					<input type="hidden" name="token" value="<?php echo $token; ?>">
					<input type="hidden" name="pid" value="<?php echo $posts[$n]->id; ?>" />
					<input type="hidden" name="tid" value="<?php echo $tid; ?>" />
					<input type="hidden" name="uid" value="<?php echo $posts[$n]->post_creator; ?>" />
					<input type="hidden" name="type" value="positive" />
					<button rel="tooltip" title="Give reputation" type="submit" class="btn btn-success btn-sm give-rep"><span class="glyphicon glyphicon-thumbs-up"></span></button>
				</form>
  	      	  		<?php 
  	      	  	    } else {
  	      	  	    ?>
				<form style="display: inline;" action="/forum/reputation/" method="post">
					<input type="hidden" name="token" value="<?php echo $token; ?>">
					<input type="hidden" name="pid" value="<?php echo $posts[$n]->id; ?>" />
					<input type="hidden" name="tid" value="<?php echo $tid; ?>" />
					<input type="hidden" name="uid" value="<?php echo $posts[$n]->post_creator; ?>" />
					<input type="hidden" name="type" value="negative" />
					<button rel="tooltip" title="Remove reputation" type="submit" class="btn btn-danger btn-sm give-rep"><span class="glyphicon glyphicon-thumbs-down"></span></button>
				</form>
					<?php 
  	      	  	    }
  	      	  	  } else { // No reputation for this post yet
  	      	  	  ?>
				<form style="display: inline;" action="/forum/reputation/" method="post">
					<input type="hidden" name="token" value="<?php echo $token; ?>">
					<input type="hidden" name="pid" value="<?php echo $posts[$n]->id; ?>" />
					<input type="hidden" name="tid" value="<?php echo $tid; ?>" />
					<input type="hidden" name="uid" value="<?php echo $posts[$n]->post_creator; ?>" />
					<input type="hidden" name="type" value="positive" />
					<button rel="tooltip" title="Give reputation" type="submit" class="btn btn-success btn-sm give-rep"><span class="glyphicon glyphicon-thumbs-up"></span></button>
				</form>
  	      	  	  <?php 
  	      	  	  }
  	      	 	}
  	      	 	// Display the reputation count
  	      	 	?>
				<button class="btn btn-<?php if(count($reputation)){ echo "success"; } else { echo "default"; } ?> btn-sm count-rep" data-toggle="modal" data-target="#repModal<?php echo $posts[$n]->id; ?>"><strong><?php echo count($reputation); ?></strong></button>
  	      	  </span>
  	      	  <br /><br />
  	      	  <hr>
  	      	  <?php 
  	      	  // Purify the user's signature
  	      	  $clean = $purifier->purify(htmlspecialchars_decode($signature));
  	      	  echo $clean;
  	      	  ?>
  	      	</div>

  	      </div>
  	    </div>
	  </div>

	  <!-- Reputation modal --> 
	  <div class="modal fade" id="repModal<?php echo $posts[$n]->id; ?>" tabindex="-1" role="dialog" aria-labelledby="repModalLabel<?php echo $posts[$n]->id; ?>" aria-hidden="true">
	    <div class="modal-dialog modal-sm">
		  <div class="modal-content">
		    <div class="modal-header">
			  <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
			  <h4 class="modal-title" id="repModalLabel<?php echo $posts[$n]->id; ?>">Post Reputation</h4>
		    </div>
		    <div class="modal-body">
		    <?php 
		    if(count($reputation)){
			?>
			<table>
			<?php
			  foreach($reputation as $rep){
			?>
			  <tr>
				<td style="width:40px"><a href="/profile/<?php echo htmlspecialchars($user->IdToMCName($rep->user_given)); ?>"><img class="img-rounded" src="https://cravatar.eu/avatar/<?php echo htmlspecialchars($user->IdToMCName($rep->user_given)); ?>/30.png" /></a></td>
				<td style="width:100px"><a href="/profile/<?php echo htmlspecialchars($user->IdToMCName($rep->user_given)); ?>"><?php echo htmlspecialchars($user->IdToName($rep->user_given)); ?></a></td>
			  </tr>
			<?php 
			  }
			?>
			</table>
			<?php 
		  	} else {
		  	?>
			No reputation for this post yet.
			<?php 
		  	}
		  	?>
		    </div>
		  </div>
	    </div>
	  </div>


	  <?php
	    $n++;
	  }

	  // Pagination
	  echo $pagination->parse(); // Print pagination

	  // Quick reply 
	  if($user->isLoggedIn() && $can_reply){
		if($topic->locked != 1){
	    ?>
	  <h3>Create new reply</h3>
	  <form action="" method="post">
		<textarea name="content" id="quickreply" rows="3">
		<?php echo htmlspecialchars(Input::get('content')); ?>
		</textarea>
		<br />
		<?php echo '<input type="hidden" name="token" value="' .  $token . '">'; ?>
		<button type="submit" class="btn btn-primary">
		  Submit
		</button>
	  </form>
	    <?php 
		}
	  }
	  ?>

      <hr>
	  
	  <?php require("inc/templates/footer.php"); ?> 
	  
    </div> <!-- /container -->
		
	<?php require("inc/templates/scripts.php"); ?>
	<script src="/assets/js/jquery-ui.min.js"></script>
	<script>
	$(document).ready(function(){
		$("[rel=tooltip]").tooltip({ placement: 'top'});
		var hash = window.location.hash.substring(1);
		$("#" + hash).effect("highlight", {}, 2000);
	});
	</script>
	<?php
	if($user->isLoggedIn()){
	?>
	<script src="/assets/js/ckeditor.js"></script>
	<script type="text/javascript">
		CKEDITOR.replace( 'quickreply', {
			// Define the toolbar groups as it is a more accessible solution.
			toolbarGroups: [
				{"name":"basicstyles","groups":["basicstyles"]},
				{"name":"paragraph","groups":["list","align"]},
				{"name":"styles","groups":["styles"]},
				{"name":"colors","groups":["colors"]},
				{"name":"links","groups":["links"]},
				{"name":"insert","groups":["insert"]}
			],
			// Remove the redundant buttons from toolbar groups defined above.
			removeButtons: 'Anchor,Styles,Specialchar,Font,About,Flash,Iframe'
		} );
	</script>
	<?php
	}

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