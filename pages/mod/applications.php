<?php 
/* 
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

// Mod check
if($user->isLoggedIn()){
	if($user->data()->group_id == 2 || $user->data()->group_id == 3){} else {
		Redirect::to('/');
		die();
	}
} else {
	Redirect::to('/');
	die();
}

// Can moderators view applications?
if($allow_moderators !== "true" && $user->data()->group_id == 3){
	// No
	Redirect::to('/mod');
	die();
}

if(isset($_GET['app'])){
	// Does the application exist?
	$application = $queries->getWhere('staff_apps_replies', array('id', '=', htmlspecialchars($_GET['app'])));
	if(empty($application)){
		// Doesn't exist
		echo '<script>window.location.replace(\'/mod/applications\');</script>';
		die();
	} else {
		$application = $application[0];
		
		if(!isset($_GET['action'])){
			// Handle comment input
			if(Input::exists()){
				if(Token::check(Input::get('token'))){
					// Valid token
					$validate = new Validate();
					$validation = $validate->check($_POST, array(
						'comment' => array(
							'required' => true,
							'min' => 2,
							'max' => 2048
						)
					));
					if($validation->passed()){
						try {
							$queries->create("staff_apps_comments", array(
								'aid' => $application->id,
								'uid' => $user->data()->id,
								'time' => date('U'),
								'content' => htmlspecialchars(Input::get('comment'))
							));
							Session::flash('mod_staff_app', '<div class="alert alert-info alert-dismissable"> <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>Comment added</div>');
						} catch(Exception $e){
							die($e->getMessage());
						}
					} else {
						Session::flash('mod_staff_app', '<div class="alert alert-danger">Please ensure your comment is between 2 and 2048 characters long.</div>');
					}
				} else {
					// Invalid token
					Session::flash('mod_staff_app', '<div class="alert alert-danger">Invalid token. Please try again.</div>');
				}
			}
			
			// Decode the questions/answers
			$answers = json_decode($application->content, true);
			// Get questions
			$questions = $queries->getWhere('staff_apps_questions', array('id', '<>', 0));
		} else {
			if($_GET['action'] == 'accept'){
				$queries->update('staff_apps_replies', $application->id, array(
					'status' => 1
				));
			} else if($_GET['action'] == 'reject'){
				$queries->update('staff_apps_replies', $application->id, array(
					'status' => 2
				));
			}
			Redirect::to('/mod/applications/?app=' . $application->id);
			die();
		}
	}
}

$token = Token::generate();

require_once('inc/includes/html/library/HTMLPurifier.auto.php'); // HTMLPurifier
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
    <meta name="author" content="Samerton">
	<link rel="icon" href="/assets/favicon.ico">
	<meta name="robots" content="noindex">

    <title><?php echo $sitename; ?> &bull; ModCP Staff Applications</title>
	
	<?php require("inc/templates/header.php"); ?>

  </head>

  <body>

	<?php require("inc/templates/navbar.php"); ?>

    <div class="container">	

	  <div class="row">
		<div class="col-md-3">
			<div class="well well-sm">
				<ul class="nav nav-pills nav-stacked">
				  <li><a href="/mod">Overview</a></li>
				  <li><a href="/mod/reports">Reports<?php if($reports == true){ ?> <span class="glyphicon glyphicon-exclamation-sign"></span><?php } ?></a></li>
				  <li><a href="/mod/punishments">Punishments</a></li>
				  <?php if($allow_moderators === "true" || $user->data()->group_id == 2){ ?><li class="active"><a href="/mod/applications" class="active">Staff Applications<?php if($open_apps === true){ ?> <span class="glyphicon glyphicon-exclamation-sign"></span><?php } ?></a></li><?php } ?>
				  <li><a href="/mod/announcements">Announcements</a></li>
				</ul>
			</div>
		</div>
		<div class="col-md-9">
			<h2>Staff Applications</h2>
			<?php 
			if(!isset($_GET['app'])){
			?>
			<div class="well well-sm">
				<?php
					if(!isset($_GET['view'])){ 
						// Get open applications
						$applications = $queries->getWhere('staff_apps_replies', array('status', '=', 0));
				?>
				Viewing <span class="label label-info">open</span> applications. Change to <a href="/mod/applications/?view=accepted"><span class="label label-success">accepted</span></a> or <a href="/mod/applications/?view=declined"><span class="label label-danger">declined</span></a>.<br /><br />
				<?php 
					} else if(isset($_GET['view']) && $_GET['view'] == 'accepted'){ 
						// Get accepted applications
						$applications = $queries->getWhere('staff_apps_replies', array('status', '=', 1));
				?>
				Viewing <span class="label label-success">accepted</span> applications. Change to <a href="/mod/applications/"><span class="label label-info">open</span></a> or <a href="/mod/applications/?view=declined"><span class="label label-danger">declined</span></a>.<br /><br />
				<?php 
					} else if(isset($_GET['view']) && $_GET['view'] == 'declined'){ 
						// Get declined applications
						$applications = $queries->getWhere('staff_apps_replies', array('status', '=', 2));
				?>
				Viewing <span class="label label-danger">declined</span> applications. Change to <a href="/mod/applications/"><span class="label label-info">open</span></a> or <a href="/mod/applications/?view=accepted"><span class="label label-success">accepted</span></a>.<br /><br />
				<?php 
					} 
					if(count($applications)){
				?>
				<table class="table table-striped">
				  <thead>
				    <tr>
				      <th></th>
					  <th>Minecraft Username</th>
					  <th>Time Applied</th>
				    </tr>
				  </thead>
				  <?php 
					  foreach($applications as $application){ 
						// Get username
						$username = $user->IdToMCName($application->uid);
				  ?>
				  <tbody>
				    <tr>
				      <td><a href="/mod/applications/?app=<?php echo $application->id; ?>" class="btn btn-info btn-xs">View</a></td>
					  <td><a href="/profile/<?php echo htmlspecialchars($username); ?>"><?php echo htmlspecialchars($username); ?></a></td>
					  <td><?php echo date('d M Y, G:i', $application->time); ?></td>
				    </tr>
				  </tbody>
				  <?php } ?>
				</table>
				<?php
					} else {
				?>
				No applications in this category.
				<?php
					}
				?>
			</div>
			<?php
			} else {
				$username = htmlspecialchars($user->idToMCName($application->uid));

				if(Session::exists('mod_staff_app')){
				  echo Session::flash('mod_staff_app');
				}
			?>
			Viewing application from <strong><a href="/profile/<?php echo $username; ?>"><?php echo $username; ?></a></strong> <?php if($application->status == 0){ ?><span class="label label-info">Open</span><?php } else if($application->status == 1){ ?><span class="label label-success">Accepted</span><?php } else if($application->status == 2){ ?><span class="label label-danger">Declined</span><?php } ?>
			<span class="pull-right">
			  <?php 
			  if($application->status == 0 && $user->data()->group_id == 2){
				// Admins can accept/reject applications
			  ?>
			  <div class="btn-group">
			    <a href="/mod/applications/?app=<?php echo $application->id; ?>&action=accept" class="btn btn-success">Accept</a><a href="/mod/applications/?app=<?php echo $application->id; ?>&action=reject" class="btn btn-danger">Reject</a>
			  </div>
			  <?php
			  }
			  ?>
			</span><br /><br />
			<hr>
			<?php 
			foreach($answers as $answer){
				// Get the question itself from the ID
				foreach($questions as $key => $item){
					if($item->id == $answer[0]){
					  echo '<strong>' . htmlspecialchars($item->question) . '</strong>'; 
					}
				}
				echo '<p>' . htmlspecialchars($answer[1]) . '</p>';
			}
			?>
			<hr>
			<h4>Comments</h4>
			<?php
			// Get comments
			$comments = $queries->getWhere('staff_apps_comments', array('aid', '=', $application->id));
			if(!count($comments)){
			?>
			No comments yet.<br /><br />
			<?php 
			} else { 
				foreach($comments as $comment){
					$username = htmlspecialchars($user->idToName($comment->uid));
					$mcusername = htmlspecialchars($user->idToMCName($comment->uid));
			?>
			<div class="panel panel-primary">
			  <div class="panel-heading">
				<a href="/profile/<?php echo $mcusername; ?>"><?php echo $username; ?></a>
				<span class="pull-right">
				  <?php echo date('jS M Y , g:ia', $comment->time); ?>
				</span>
			  </div>
			  <div class="panel-body">
				<?php
				// Purify comment
				$config = HTMLPurifier_Config::createDefault();
				$config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
				$config->set('URI.DisableExternalResources', false);
				$config->set('URI.DisableResources', false);
				$config->set('HTML.Allowed', 'u,p,b,i,small,blockquote,span[style],span[class],p,strong,em,li,ul,ol,div[align],br,img');
				$config->set('CSS.AllowedProperties', array('float', 'color','background-color', 'background', 'font-size', 'font-family', 'text-decoration', 'font-weight', 'font-style', 'font-size'));
				$config->set('HTML.AllowedAttributes', 'src, height, width, alt, class, *.style');
				$purifier = new HTMLPurifier($config);
				echo $purifier->purify(htmlspecialchars_decode($comment->content));
				?>
			  </div>
			</div>
			<?php 
				} 
			}
			?>
			<div class="panel panel-default">
				<div class="panel-heading">
					New comment
				</div>
				<div class="panel-body">
					<form action="" method="post">
						<textarea name="comment" class="form-control" rows="3"></textarea>
						<br />
						<?php echo '<input type="hidden" name="token" value="' . $token . '">'; ?>
						<button type="submit" class="btn btn-danger">
						  Submit
						</button>
					</form>
				</div>
			</div>
			<?php
			}
			?>
		</div>
      </div>	  

      <hr>

	  <?php require("inc/templates/footer.php"); ?> 
	  
    </div> <!-- /container -->
		
	<?php require("inc/templates/scripts.php"); ?>
	
  </body>
</html>