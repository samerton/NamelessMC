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

require('inc/includes/html/library/HTMLPurifier.auto.php'); // HTMLPurifier

if(Input::exists()) {
	if(Token::check(Input::get('token'))) {
		if(Input::get('type') === "update_status") {
			try {
				$queries->update("reports", Input::get('report_id'), array(
					'status' => 1,
					'date_updated' => date('Y-m-d H:i:s'),
					'updated_by' => $user->data()->id
				));
				Session::flash('success_comment_report', '<div class="alert alert-info alert-dismissable"> <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>Report closed</div>');
				Redirect::to('/mod/reports/?rid=' . Input::get('report_id'));
				die();
			} catch(Exception $e){
				die($e->getMessage());
			}
		} else if(Input::get('type') === "comment") {
			$validate = new Validate();
			$validation = $validate->check($_POST, array(
				'comment' => array(
					'required' => true,
					'min' => 2,
					'max' => 255
				)
			));
			if($validation->passed()){
				try {
					$queries->create("reports_comments", array(
						'report_id' => Input::get('report_id'),
						'commenter_id' => $user->data()->id,
						'comment_date' => date('Y-m-d H:i:s'),
						'comment_content' => htmlspecialchars(Input::get('comment'))
					));
					$queries->update("reports", Input::get('report_id'), array(
						'date_updated' => date('Y-m-d H:i:s'),
						'updated_by' => $user->data()->id
					));
					Session::flash('success_comment_report', '<div class="alert alert-info alert-dismissable"> <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>Comment added</div>');
					Redirect::to('/mod/reports/?rid=' . Input::get('report_id'));
					die();
				} catch(Exception $e){
					die($e->getMessage());
				}
			} else {
				foreach($validation->errors() as $error) {
					$error_string .= ucfirst($error) . '<br />';
				}
				Session::flash('failure_comment_report', '<div class="alert alert-danger alert-dismissable"> <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>' . $error_string . '</div>');
				Redirect::to('/mod/reports/?rid=' . Input::get('report_id'));
				die();
			}
		}
	} else {
		Redirect::to("/mod");
		die();
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
    <meta name="description" content="">
    <meta name="author" content="Samerton">
	<link rel="icon" href="/assets/favicon.ico">
	<meta name="robots" content="noindex">

    <title><?php echo $sitename; ?> &bull; ModCP Reports</title>
	
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
				  <li class="active"><a href="/mod/reports">Reports<?php if($reports == true){ ?> <span class="glyphicon glyphicon-exclamation-sign"></span><?php } ?></a></li>
				  <li><a href="/mod/punishments">Punishments</a></li>
				  <?php if($allow_moderators === "true" || $user->data()->group_id == 2){ ?><li><a href="/mod/applications">Staff Applications<?php if($open_apps === true){ ?> <span class="glyphicon glyphicon-exclamation-sign"></span><?php } ?></a></li><?php } ?>
				  <li><a href="/mod/announcements">Announcements</a></li>
				</ul>
			</div>
		</div>
		<div class="col-md-9">
			<?php 
			if(!isset($_GET["rid"])){
				if($reports == true){
					$reports = $queries->getWhere('reports', array('status', '<>', '1')); // Get a list of open reports
				?>

				<table class="table table-bordered">
				  <thead>
					<tr>
					  <th></th>
					  <th>User Reported</th>
					  <th>Type</th>
					  <th>Comments</th>
					  <th>Updated By</th>
					</tr>
				  </thead>
				  <tbody>
					<?php
					foreach($reports as $report){
					?>
					<tr>
					  <td><a href="/mod/reports/?rid=<?php echo $report->id; ?>"><strong>View</strong></a></td>
					  <td><a href="/profile/<?php echo htmlspecialchars($user->idToMCName($report->reported_id)); ?>"><?php echo htmlspecialchars($user->idToName($report->reported_id)); ?></a></td>
					  <td><?php
					  if($report->type == 0){
						echo 'Forum Post';
					  } else if ($report->type == 1) {
						echo 'User Profile';
					  }
					  ?></td>
					  <td><?php echo(count($queries->getWhere("reports_comments", array('report_id' , '=', $report->id)))); ?></td>
					  <td><a href="/profile/<?php echo htmlspecialchars($user->idToMCName($report->updated_by)); ?>"><img class="img-rounded" src="https://cravatar.eu/avatar/<?php echo htmlspecialchars($user->idToMCName($report->updated_by)); ?>/30.png" /></a>&nbsp;&nbsp;&nbsp;<a href="/profile/<?php echo htmlspecialchars($user->idToName($report->updated_by)); ?>"><?php echo htmlspecialchars($user->idToName($report->updated_by)); ?></a></td>
					</tr>				
					<?php
					}
					?>
				  </tbody>
				</table>
				<?php 
				} else {
				?>
				<div class="well well-sm">
					No active reports
				</div>
				<?php 
				}
			} else {
				if(!is_numeric($_GET["rid"])){
					echo '<script>window.location.replace("/mod/reports");</script>';
					die();
				}
				$report = $queries->getWhere("reports", array('id' , '=', $_GET["rid"]));
				if(!count($_GET["rid"])){
					echo 'No report with that ID';
				} else {
					if($report[0]->type == 0){
						$url = "/forum/view_topic/?tid=" . $report[0]->reported_post_topic . "&pid=" . $report[0]->reported_post;
					} else {
						$url = "/profile/" . $user->idToName($report[0]->reported_id);
					}
					if(Session::exists('failure_comment_report')){
						echo '<center>' . Session::flash('failure_comment_report') . '</center>';
					}
					if(Session::exists('success_comment_report')){
						echo '<center>' . Session::flash('success_comment_report') . '</center>';
					}
					?>
					<h2 style="display:inline;">Report: <a href="/profile/<?php echo htmlspecialchars($user->idToMCName($report[0]->reported_id)); ?>"><?php echo htmlspecialchars($user->idToName($report[0]->reported_id));?></a> | <small><a href="<?php echo $url; ?>">View Reported Content</a></small></h2>
					<span class="pull-right">
						<form action="" method="post">
							<?php echo '<input type="hidden" name="type" value="update_status">'; ?>
							<?php echo '<input type="hidden" name="token" value="' .  $token . '">'; ?>
							<?php echo '<input type="hidden" name="report_id" value="' . $_GET["rid"] . '">'; ?>
							<button style="display: inline;" type="submit" class="btn btn-danger">
							  Close issue
							</button>
						</form>
					</span>
					<br /><br />
					<div class="panel panel-primary">
						<div class="panel-heading">Reported by <a class="white-text" href="/profile/<?php echo htmlspecialchars($user->idToMCName($report[0]->reporter_id));?>"><?php echo htmlspecialchars($user->idToName($report[0]->reporter_id));?></a><span class="pull-right"><?php echo date("jS M Y , g:ia", strtotime($report[0]->date_reported)); ?></span></div>
						<div class="panel-body">
							<?php
							$config = HTMLPurifier_Config::createDefault();
							$config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
							$config->set('URI.DisableExternalResources', false);
							$config->set('URI.DisableResources', false);
							$config->set('HTML.Allowed', 'u,p,a,b,i,small,blockquote,span[style],span[class],p,strong,em,li,ul,ol,div[align],br,img');
							$config->set('CSS.AllowedProperties', array('float', 'color','background-color', 'background', 'font-size', 'font-family', 'text-decoration', 'font-weight', 'font-style', 'font-size'));
							$config->set('HTML.AllowedAttributes', 'src, height, width, alt, class, *.style');
							$purifier = new HTMLPurifier($config);
							echo $purifier->purify(htmlspecialchars_decode($report[0]->report_reason));
							?>
						</div>
					</div>
					<h3>Comments <small>Can only be viewed by staff</small></h3>
					<?php
					$comments = $queries->getWhere("reports_comments", array('report_id' , '=', $_GET["rid"]));
					if(count($comments)){
						foreach($comments as $comment){
					?>
					<div class="panel panel-primary">
						<div class="panel-heading"><a href="/profile/<?php echo htmlspecialchars($user->idToMCName($comment->commenter_id));?>"><?php echo htmlspecialchars($user->idToName($comment->commenter_id));?></a><span class="pull-right"><?php echo date("jS M Y , g:ia", strtotime($comment->comment_date)); ?></span></div>
						<div class="panel-body">
							<?php
							echo $purifier->purify(htmlspecialchars_decode($comment->comment_content));
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
								<?php echo '<input type="hidden" name="type" value="comment">'; ?>
								<?php echo '<input type="hidden" name="token" value="' .  $token . '">'; ?>
								<?php echo '<input type="hidden" name="report_id" value="' . $_GET["rid"] . '">'; ?>
								<button type="submit" class="btn btn-danger">
								  Submit
								</button>
							</form>
						</div>
					</div>
					<?php 
				}
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