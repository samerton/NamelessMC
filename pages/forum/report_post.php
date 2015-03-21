<?php 
/*
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

// Set the page name for the active link in navbar
$page = "forum";

require('inc/includes/html/library/HTMLPurifier.auto.php'); // HTML Purifier

$forum = new Forum();

// User must be logged in to proceed
if(!$user->isLoggedIn()){
	Redirect::to('/forum');
	die();
}

// Ensure a post and topic is set via URL parameters
if(isset($_GET["pid"]) && isset($_GET["tid"])){
	if(is_numeric($_GET["pid"]) && is_numeric($_GET["tid"])){
		$post_id = $_GET["pid"];
		$topic_id = $_GET["tid"];
	} else {
		Redirect::to('/forum/error/?error=not_exist');
		die();
	}
} else {
	Redirect::to('/forum/error/?error=not_exist');
	die();
}

// Deal with inputted data
if(Input::exists()) {
	if(Token::check(Input::get('token'))) {
		$validate = new Validate();
		$validation = $validate->check($_POST, array(
			'reason' => array(
				'required' => true,
				'min' => 2,
				'max' => 255
			)
		));
		if($validation->passed()){
			try {
				$queries->create("reports", array(
					'type' => 0,
					'reporter_id' => $user->data()->id,
					'reported_id' => Input::get('reported_user'),
					'status' => 0,
					'date_reported' => date('Y-m-d H:i:s'),
					'date_updated' => date('Y-m-d H:i:s'),
					'report_reason' => htmlspecialchars(Input::get('reason')),
					'updated_by' => $user->data()->id,
					'reported_post' => Input::get('post_id'),
					'reported_post_topic' => Input::get('topic_id')
				));
				Session::flash('success_post', '<div class="alert alert-info alert-dismissable"> <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>Report Submitted.</div>');
				Redirect::to('/forum/view_topic/?tid=' . Input::get('topic_id'));
				die();

			} catch(Exception $e){
				die($e->getMessage());
			}
		} else {
			foreach($validation->errors() as $error) {
				$error_string .= ucfirst($error) . '<br />';
			}
			Session::flash('failure_post', '<div class="alert alert-danger alert-dismissable"> <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>' . $error_string . '</div>');
			Redirect::to('/forum/report_post/?pid=' . Input::get('post_id') . '&amp;tid=' . Input::get('topic_id'));
			die();
		}
	} else {
		// Invalid token - TODO: improve this
	}
}

$token = Token::generate();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo $sitename; ?> Forum - Report Post">
    <meta name="author" content="Samerton">
    <meta name="robots" content="noindex">
    <link rel="icon" href="/favicon.ico">

    <title><?php echo $sitename; ?> &bull; Forum - Report Post</title>
	
	<?php require('inc/templates/header.php'); ?>
	
	<!-- Custom style -->
	<style>
	textarea {
		resize: none;
	}
	</style>
	
  </head>

  <body>

	<?php require('inc/templates/navbar.php'); ?>

    <div class="container">
  
	  <h2>Report Post #<?php echo $post_id; ?></h2>
		<div class="panel-group" id="accordion">
		  <div class="panel panel-default">
			<div class="panel-heading">
			  <h4 class="panel-title">
				<a data-toggle="collapse" data-parent="#accordion" href="#postContent">
				  View Post Content
				</a>
			  </h4>
			</div>
			<div id="postContent" class="panel-collapse collapse">
			  <div class="panel-body">
				<?php $reported_post = $forum->getIndividualPost($post_id); // Get an array containing information about the post ?>
				<div class="row">
					<div class="col-md-3">
						<center><img src="https://cravatar.eu/avatar/<?php echo htmlspecialchars($user->IdToName($reported_post[0][0])); ?>/100.png"  alt="" class="img-rounded">
						<br /><br />
						<b><a href="/profile/<?php echo htmlspecialchars($user->IdToName($reported_post[0][0])); ?>"><?php echo htmlspecialchars($user->IdToName($reported_post[0][0])); ?></a></b>
						</center>
						<hr>
					</div>
					<div class="col-md-9">
					<?php 
					echo'
					By <a href="/profile/' . htmlspecialchars($user->IdToName($reported_post[0][0])) . '">' . htmlspecialchars($user->IdToName($reported_post[0][0])) . '</a> &raquo; ' . date("d M Y, H:i", strtotime($reported_post[2][0])) . '<hr>';
				    
				    // Initialise HTML Purifier
					$config = HTMLPurifier_Config::createDefault();
					$config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
					$config->set('URI.DisableExternalResources', false);
					$config->set('URI.DisableResources', false);
					$config->set('HTML.Allowed', 'u,p,b,i,small,blockquote,span[style],span[class],p,strong,em,li,ul,ol,div[align],br,img');
					$config->set('CSS.AllowedProperties', array('text-align', 'float', 'color','background-color', 'background', 'font-size', 'font-family', 'text-decoration', 'font-weight', 'font-style', 'font-size'));
					$config->set('HTML.AllowedAttributes', 'href, src, height, width, alt, class, *.style');
					$purifier = new HTMLPurifier($config);

					$clean = $purifier->purify(htmlspecialchars_decode($reported_post[1][0]));
					echo $clean; ?>
					</div>
				</div>
			  </div>
			</div>
		  </div>
		</div>
		<?php
		if(Session::exists('failure_post')){
			echo '<center>' . Session::flash('failure_post') . '</center>';
		}
		?>
		<div class="panel panel-default">
			<div class="panel-heading">
				Report Reason
			</div>
			<div class="panel-body">
				<form action="" method="post">
					<textarea name="reason" class="form-control" rows="3"></textarea>
					<br />
					<?php echo '<input type="hidden" name="token" value="' .  $token . '">'; ?>
					<?php echo '<input type="hidden" name="post_id" value="' . $_GET["pid"] . '">'; ?>
					<?php echo '<input type="hidden" name="reported_user" value="' . $reported_post[0][0] . '">'; ?>
					<?php echo '<input type="hidden" name="topic_id" value="' . $_GET["tid"] . '">'; ?>
					<button type="submit" class="btn btn-danger">
					  Submit
					</button>
				</form>
			</div>
		</div>
	  
      <hr>

	  <?php require('inc/templates/footer.php'); ?> 
	  
    </div> <!-- /container -->
		
	<?php require('inc/templates/scripts.php'); ?>

  </body>
</html>