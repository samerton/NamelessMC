<?php 
/*
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

// Set the page name for the active link in navbar
$page = "forum";

// User must be logged in to proceed
if(!$user->isLoggedIn()){
	Redirect::to('/forum');
	die();
}

$forum = new Forum();

require('inc/includes/html/library/HTMLPurifier.auto.php'); // HTML Purifier

if(!isset($_GET['tid']) || !is_numeric($_GET['tid']) || !isset($_GET['fid']) || !is_numeric($_GET['fid'])){
	Redirect::to('/forum/error/?error=not_exist');
	die();
}

$tid = (int) $_GET['tid'];
$fid = (int) $_GET['fid'];

// Does the topic exist, and can the user view it?
$list = $forum->topicExist($tid, $user->data()->group_id);
if(!$list){
	Redirect::to('/forum/error/?error=not_exist');
	die();
}

// Get the topic information
$topic = $queries->getWhere("topics", array("id", "=", $tid));
$topic = $topic[0];

// Can the user post a reply in this topic?
$can_reply = $forum->canPostReply($topic->forum_id, $user->data()->group_id);
if(!$can_reply){
	Redirect::to('/forum/view_topic/?tid=' . $tid);
	die();
}

// Deal with inputted data
if(Input::exists()) {
	if(Token::check(Input::get('token'))) {
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
					'forum_id' => $fid,
					'topic_id' => $tid,
					'post_creator' => $user->data()->id,
					'post_content' => htmlspecialchars(Input::get('content')),
					'post_date' => date('Y-m-d H:i:s')
				));
				$queries->update("forums", $fid, array(
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
		// Invalid token - TODO: improve this
	}
}

// Is there a quote?
if(isset($_GET["qid"])){
	if(is_numeric($_GET["qid"])){
		$quoted_post = $queries->getWhere("posts", array("id", "=", $_GET["qid"]));
		$quoted_post = $quoted_post[0];
	} else {
		Redirect::to('/forum/view_topic/?tid=' . $tid);
		die();
	}
}

// Generate a token
$token = Token::generate();

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo $sitename; ?> Forum - Topic: <?php echo htmlspecialchars($topic->topic_title); ?>">
    <meta name="author" content="Samerton">
    <meta name="robots" content="noindex">
    <link rel="icon" href="/assets/favicon.ico">

    <title><?php echo $sitename; ?> &bull; Forum - New Reply</title>
	
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
	if(Session::exists('failure_post')){
		echo '<center>' . Session::flash('failure_post') . '</center>';
	}

	if($topic->locked != 1 || ($user->data()->group_id == 2 || $user->data()->group_id == 3)){ // Ensure the topic isn't locked
      // Initialise HTML Purifier
	  $config = HTMLPurifier_Config::createDefault();
	  $config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
	  $config->set('URI.DisableExternalResources', false);
	  $config->set('URI.DisableResources', false);
	  $config->set('HTML.Allowed', 'u,p,b,i,small,blockquote,span[style],span[class],p,strong,em,li,ul,ol,div[align],br,img');
	  $config->set('CSS.AllowedProperties', array('text-align', 'float', 'color','background-color', 'background', 'font-size', 'font-family', 'text-decoration', 'font-weight', 'font-style', 'font-size'));
	  $config->set('HTML.AllowedAttributes', 'href, src, height, width, alt, class, *.style');
	  $purifier = new HTMLPurifier($config);
	?>
	<h3>Create new reply in topic "<?php echo htmlspecialchars($topic->topic_title); ?>"</h3>
	<?php 
	if($topic->locked == 1 && ($user->data()->group_id == 2 || $user->data()->group_id == 3)){
	?>
	<div class="alert alert-info">Note: This topic is locked, however your permissions allow you to reply.</div>
	<?php 
	}
	?>
	<form action="" method="post">
	  <textarea name="content" id="reply" rows="3">
	  <?php echo Input::get('content'); ?>
	  </textarea>
	  <br />
	  <?php echo '<input type="hidden" name="token" value="' .  $token . '">'; ?>
	  <button type="submit" class="btn btn-primary">
		Submit
	  </button>
	</form>
	<?php 
	} else {
	  Redirect::to('/forum/view_topic/?tid=' . $tid);
	  die();
	}
	?>

      <hr>
	  
	  <?php require("inc/templates/footer.php"); ?> 
	  
    </div> <!-- /container -->
		
	<?php require("inc/templates/scripts.php"); ?>
	<script src="/assets/js/ckeditor.js"></script>
	<script type="text/javascript">
		CKEDITOR.replace( 'reply', {
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
		CKEDITOR.config.extraAllowedContent = 'blockquote small';
	    <?php 
	    // Quote
	    if(isset($quoted_post) && !Session::exists('failure_post')){
	  	  $clean = $purifier->purify(htmlspecialchars_decode($quoted_post->post_content));
	    ?>
		CKEDITOR.on('instanceReady', function(ev) {
		     CKEDITOR.instances.reply.insertHtml('<blockquote><small><a href="/forum/view_topic/?tid=<?php echo $tid; ?>&amp;pid=<?php echo $_GET["qid"]; ?>"><?php echo htmlspecialchars($user->IdToName($quoted_post->post_creator)); ?> said:<\/a><?php echo str_replace(array("\r", "\n"), '', $clean); ?><\/small></blockquote>');
		});
		<?php
		}
		?>
	</script>
  </body>
</html>