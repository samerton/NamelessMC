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


if(!isset($_GET["tid"]) || !is_numeric($_GET["tid"])){
	Redirect::to('/forum/error/?error=not_exist');
	die();
} else {
	$topic_id = $_GET["tid"];
	$forum_id = $queries->getWhere('topics', array('id', '=', $topic_id));
	$forum_id = $forum_id[0]->forum_id;
}

if($user->data()->group_id == 2 || $user->data()->group_id == 3){
	if(Input::exists()) {
		if(Token::check(Input::get('token'))) {
			$validate = new Validate();
			$validation = $validate->check($_POST, array(
				'merge' => array(
					'required' => true
				)
			));
			$posts_to_move = $queries->getWhere('posts', array('topic_id', '=', $topic_id));
			if($validation->passed()){
				try {
					foreach($posts_to_move as $post_to_move){
						$queries->update('posts', $post_to_move->id, array(
							'topic_id' => Input::get('merge')
						));
					}
					$queries->delete('topics', array('id', '=' , $topic_id));

					// Update latest posts in categories
					$forum->updateForumLatestPosts();
					$forum->updateTopicLatestPosts();

					Redirect::to('/forum/view_topic/?tid=' . Input::get('merge'));
					die();
				} catch(Exception $e){
					die($e->getMessage());
				}
			} else {
				echo 'Error processing that action. <a href="/forum">Forum index</a>';
				die();
			}
		}
	}
} else {
	Redirect::to("/forum");
	die();
}

$token = Token::generate();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo $sitename; ?> Forum - Merge Threads">
    <meta name="author" content="Samerton">
    <meta name="robots" content="noindex">
    <link rel="icon" href="/favicon.ico">

    <title><?php echo $sitename; ?> &bull; Forum - Merge Threads</title>
	
	<?php require('inc/templates/header.php'); ?>

  </head>
  <body>
	<?php require('inc/templates/navbar.php'); ?>
	
    <div class="container">
	  <h2>Merge threads</h2>
	  <p>The thread to merge with <b>must</b> be within the same forum. Move a thread if necessary.</p>
	  <?php 
		$threads = $queries->getWhere('topics', array('forum_id', '=', $forum_id));
	  ?>
	  <form action="" method="post">
		<div class="form-group">
		  <label for="InputMerge">Merge with:</label>
		  <select class="form-control" id="InputMerge" name="merge">
		  <?php 
		  foreach($threads as $thread){
			if($thread->id !== $topic_id){
		  ?>
		  <option value="<?php echo $thread->id; ?>"><?php echo htmlspecialchars($thread->topic_title); ?></option>
		  <?php 
			}
		  } 
		  ?>
		  </select> 
		</div>
		<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
		<input type="submit" value="Submit" class="btn btn-default">
	  </form>
	  
      <hr>
	  
	  <?php require('inc/templates/footer.php'); ?> 
	</div>
	<?php require('inc/templates/scripts.php'); ?>
  </body>
</html>