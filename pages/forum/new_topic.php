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

if(!isset($_GET['fid']) || !is_numeric($_GET['fid'])){
	Redirect::to('/forum/error/?error=not_exist');
	die();
}

$fid = (int) $_GET['fid'];

// Does the topic exist, and can the user view it?
$list = $forum->forumExist($fid, $user->data()->group_id);
if(!$list){
	Redirect::to('/forum/error/?error=not_exist');
	die();
}

// Can the user post a reply in this forum?
$can_reply = $forum->canPostTopic($fid, $user->data()->group_id);
if(!$can_reply){
	Redirect::to('/forum/view_topic/?tid=' . $tid);
	die();
}

// Deal with any inputted data
if(Input::exists()) {
	if(Token::check(Input::get('token'))) {
		$validate = new Validate();
		$validation = $validate->check($_POST, array(
			'title' => array(
				'required' => true,
				'min' => 2,
				'max' => 64
			),
			'content' => array(
				'required' => true,
				'min' => 2,
				'max' => 20480
			)
		));
		if($validation->passed()){
			try {
				$queries->create("topics", array(
					'forum_id' => $fid,
					'topic_title' => Input::get('title'),
					'topic_creator' => $user->data()->id,
					'topic_last_user' => $user->data()->id,
					'topic_date' => date('U'),
					'topic_reply_date' => date('U')
				));
				$topic_id = $queries->getLastId();
				$queries->create("posts", array(
					'forum_id' => $fid,
					'topic_id' => $topic_id,
					'post_creator' => $user->data()->id,
					'post_content' => htmlspecialchars(Input::get('content')),
					'post_date' => date('Y-m-d H:i:s')
				));
				$queries->update("forums", $fid, array(
					'last_post_date' => date('Y-m-d H:i:s'),
					'last_user_posted' => $user->data()->id,
					'last_topic_posted' => $topic_id
				));
				Session::flash('success_post', '<div class="alert alert-info alert-dismissable"> <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>Topic submitted.</div>');
				Redirect::to('/forum/view_topic/?tid=' . $topic_id);
				die();
			} catch(Exception $e){
				die($e->getMessage());
			}
		} else {
			foreach($validation->errors() as $error) {
				$error_string .= ucfirst($error) . '<br />';
			}
			Session::flash('failure_post', '<div class="alert alert-danger alert-dismissable"> <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>' . $error_string . '</div>');
		}
	} else {
		Redirect::to("/forum");
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
    <meta name="description" content="<?php echo $sitename; ?> Forum - New Topic ?>">
    <meta name="author" content="Samerton">
    <meta name="robots" content="noindex">
    <link rel="icon" href="/favicon.ico">

    <title><?php echo $sitename; ?> &bull; Forum - New Topic</title>
	
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
      <h4>New topic in forum "<?php echo htmlspecialchars($forum->getForumTitle($fid)); ?>"</h4>
	  <?php
	  if(Session::exists('failure_post')){
		echo '<center>' . Session::flash('failure_post') . '</center>';
	  }
	  ?>
	  <form action="" method="post">
		<input type="text" class="form-control input-lg" name="title" placeholder="Thread title">
		<br />
		<textarea name="content" id="reply" rows="3"><?php echo htmlspecialchars(Input::get('content')); ?></textarea>
		<br />
		<?php echo '<input type="hidden" name="token" value="' .  $token . '">'; ?>
		<button type="submit" class="btn btn-primary">
		  Submit
		</button>
		<a class="btn btn-danger" href="/forum" onclick="return confirm('Are you sure?')">Cancel</a>
	  </form>	

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
	</script>
  </body>
</html>