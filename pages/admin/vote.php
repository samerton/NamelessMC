<?php 
/*
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

// Set page name for sidebar
$page = "admin-vote";

// Admin check
if($user->isAdmLoggedIn()){
	// Is authenticated
	if($user->data()->group_id != 2){
		Redirect::to('/');
		die();
	}
} else {
	Redirect::to('/admin');
	die();
}

require('inc/includes/html/library/HTMLPurifier.auto.php'); // HTMLPurifier
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Samerton">
	<meta name="robots" content="noindex">
    <link rel="icon" href="/favicon.ico">

    <title><?php echo $sitename; ?> &bull; AdminCP Vote</title>
	
	<?php require("inc/templates/header.php"); ?>
	
	<!-- Custom style -->
	<style>
	textarea {
		resize: none;
	}
	</style>

  </head>

  <body>

	<?php require("inc/templates/navbar.php"); ?>

    <div class="container">	
	<?php
		if(Session::exists('adm-alert')){
			echo Session::flash('adm-alert');
		}
	?>
	  <div class="row">
		<div class="col-md-3">
			<?php require('pages/admin/sidebar.php'); ?>
		</div>
		<div class="col-md-9">
			<?php 
			if(!isset($_GET["action"]) && !isset($_GET["vid"])){
				if(Input::exists()) {
					if(Token::check(Input::get('token'))) {
						$validate = new Validate();
						$validation = $validate->check($_POST, array(
							'vote_message' => array(
								'max' => 2048
							)
						));
						
						if($validation->passed()){
							try {
								$queries->update("settings", 21, array(
									'value' => Input::get('vote_message')
								));
								echo '<script>window.location.replace("/admin/vote");</script>';
								die();
							} catch(Exception $e){
								die($e->getMessage());
							}
						} else {
						?>
						<div class="alert alert-danger">Your vote message must be a maximum of 2048 characters</div>
						<?php 
						}						
					}
				}
			?>
			<a href="/admin/vote/?action=new" class="btn btn-default">New Vote Site</a>
			<br /><br />
			<?php 
			$vote_sites = $queries->getWhere("vote_sites", array("id", "<>", 0));
			?>

			<div class="panel panel-default">
				<div class="panel-heading">
					Vote Sites
				</div>
				<div class="panel-body">
					<?php 
					foreach($vote_sites as $site){
					?>
					<div class="row">
						<div class="col-md-10">
							<?php echo '<a href="/admin/vote/?vid=' . $site->id . '">' . htmlspecialchars($site->name) . '</a>'; ?>
						</div>
						<div class="col-md-2">
							<span class="pull-right">
								<a href="/admin/vote/?action=delete&vid=<?php echo $site->id;?>" class="btn btn-warning btn-sm" onclick="return confirm('Are you sure you want to delete this site?');"><span class="glyphicon glyphicon-trash"></span></a>
							</span>
						</div>
					</div>
					<hr> 
					<?php 
					}
					?>

				</div>
			</div>
			<?php 
			$vote_message = $queries->getWhere("settings", array("name", "=", "vote_message"));
			$vote_message = $vote_message[0]->value;
			
			$token = Token::generate();
			
			$config = HTMLPurifier_Config::createDefault();
			$config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
			$config->set('URI.DisableExternalResources', false);
			$config->set('URI.DisableResources', false);
			$config->set('HTML.Allowed', 'u,p,b,i,small,blockquote,span[style],span[class],p,strong,em,li,ul,ol,div[align],br,img');
			$config->set('CSS.AllowedProperties', array('float', 'color','background-color', 'background', 'font-size', 'font-family', 'text-decoration', 'font-weight', 'font-style', 'font-size'));
			$config->set('HTML.AllowedAttributes', 'src, height, width, alt, class, *.style');
			$purifier = new HTMLPurifier($config);
			?>
			<form action="" method="post">
				<div class="form-group">
					<strong>Message to display at top of Vote page (can be left blank):</strong>
					<textarea id="vote_message" name="vote_message" rows="3"><?php echo $purifier->purify(htmlspecialchars_decode($vote_message)); ?></textarea>
				</div>
				<input type="hidden" name="token" value="<?php echo $token; ?>">
				<input type="submit" class="btn btn-primary" value="Update" />
			</form>
			
			<?php 
			} else if(isset($_GET["action"])){
				if($_GET["action"] === "new"){
					if(Input::exists()) {
						if(Token::check(Input::get('token'))) {
							$validate = new Validate();
							$validation = $validate->check($_POST, array(
								'vote_name' => array(
									'required' => true,
									'min' => 2,
									'max' => 64
								),
								'vote_url' => array(
									'required' => true,
									'min' => 2,
									'max' => 255
								)
							));
							
							if($validation->passed()){
								try {
									$queries->create("vote_sites", array(
										'site' => htmlspecialchars(Input::get('vote_url')),
										'name' => htmlspecialchars(Input::get('vote_name'))
									));
									echo '<script>window.location.replace("/admin/vote");</script>';
									die();
								} catch(Exception $e){
									die($e->getMessage());
								}
							}
						} else {
							echo 'Invalid token';
							die();
						}						
					}
					if(isset($validation)){
						if(!$validation->passed()){
					?>
					<div class="alert alert-danger">
						<?php
						foreach($validation->errors() as $error) {
							echo str_replace("_", " ", ucfirst($error)), '<br />';
						}
						?>
					</div>
					<?php 
						}
					}
					?>
					<form action="" method="post">
						<h2>New Vote Site</h2>
						<div class="form-group">
							<input class="form-control" type="text" name="vote_name" value="<?php echo htmlspecialchars(Input::get('vote_name')); ?>" placeholder="Name" autocomplete="off">
						</div>
						<div class="form-group">
							<input class="form-control" type="text" name="vote_url" value="<?php echo htmlspecialchars(Input::get('vote_url')); ?>" placeholder="URL" autocomplete="off">
						</div>
						<br />
						<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
						<input class="btn btn-success" type="submit" value="Submit">	
					</form>
					<?php 
				} else if($_GET["action"] === "delete"){
					if(!isset($_GET["vid"]) || !is_numeric($_GET["vid"])){
						echo 'Invalid ID - <a href="/admin/vote">Back</a>';
						die();
					}
					try {
						$queries->delete('vote_sites', array('id', '=' , $_GET["vid"]));
						echo '<script>window.location.replace("/admin/vote");</script>';
						die();
					} catch(Exception $e) {
						die($e->getMessage());
					}
				}
			} else if(isset($_GET["vid"])){
				if(Input::exists()) {
					if(Token::check(Input::get('token'))) {
						if(Input::get('action') === "update"){
							$validate = new Validate();
							$validation = $validate->check($_POST, array(
								'vote_name' => array(
									'required' => true,
									'min' => 2,
									'max' => 64
								),
								'vote_url' => array(
									'required' => true,
									'min' => 2,
									'max' => 255
								)
							));
							
							if($validation->passed()){
								try {
									$queries->update('vote_sites', $_GET["vid"], array(
										'name' => htmlspecialchars(Input::get('vote_name')),
										'site' => str_replace("&amp;", "&", htmlspecialchars(Input::get('vote_url')))
									));
									echo '<script>window.location.replace("/admin/vote/?vid=' . $_GET["vid"] . '");</script>';
									die();
									
								} catch(Exception $e) {
									die($e->getMessage());
								}
								
							} else {
								echo '<div class="alert alert-danger">';
								foreach($validation->errors() as $error) {
									echo $error, '<br>';
								}
								echo '</div>';
							}
						}
					} else {
						echo 'Invalid token - <a href="/admin/vote">Back</a>';
						die();
					}
				}
				if(!is_numeric($_GET["vid"])){
					echo 'Error - <a href="/admin/vote">Back</a>';
					die();
				} else {
					$site = $queries->getWhere("vote_sites", array("id", "=", $_GET["vid"]));
				}
				if(count($site)){
					$site = $site[0];
					echo '<h2>' . htmlspecialchars($site->name) . '</h2>';
					
					$token = Token::generate();
					?>
					<form role="form" action="" method="post">
					  <div class="form-group">
						<label for="InputName">Name</label>
						<input type="text" name="vote_name" class="form-control" id="InputName" placeholder="Name" value="<?php echo htmlspecialchars($site->name); ?>">
					  </div>
					  <div class="form-group">
					    <label for="InputURL">URL</label>
						<input type="text" name="vote_url" id="InputURL" placeholder="URL" class="form-control" value="<?php echo htmlspecialchars($site->site); ?>">
				      </div>
					  <input type="hidden" name="token" value="<?php echo $token; ?>">
					  <input type="hidden" name="action" value="update">
					  <input type="submit" value="Submit Changes" class="btn btn-default">
					</form>
					<?php 
				} else {
					echo '<script>window.location.replace("/admin/vote");</script>';
					die();
				}
			}
			?>
		</div>
      </div>	  

      <hr>
	  
	  <?php require("inc/templates/footer.php"); ?> 
	  
    </div> <!-- /container -->
		
	<?php require("inc/templates/scripts.php"); ?>
	<script src="/assets/js/ckeditor.js"></script>
	<script type="text/javascript">
		CKEDITOR.replace( 'vote_message', {
			// Define the toolbar groups as it is a more accessible solution.
			toolbarGroups: [
				{"name":"basicstyles","groups":["basicstyles"]},
				{"name":"links","groups":["links"]},
				{"name":"paragraph","groups":["list","align"]},
				{"name":"insert","groups":["insert"]},
				{"name":"styles","groups":["styles"]},
				{"name":"about","groups":["about"]}
			],
			// Remove the redundant buttons from toolbar groups defined above.
			removeButtons: 'Anchor,Styles,Specialchar,Font,About,Flash'
		} );
	</script>
  </body>
</html>