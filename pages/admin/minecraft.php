<?php 
/*
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

// Set page name for sidebar
$page = "admin-minecraft";

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

    <title><?php echo $sitename; ?> &bull; AdminCP Minecraft</title>
	
	<?php require("inc/templates/header.php"); ?>

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
		if(!isset($_GET["action"]) && !isset($_GET["sid"])){ 
			if(Input::exists()) {
				if(Token::check(Input::get('token'))) {
					$current_default = $queries->getWhere("mc_servers", array("is_default", "=", 1));
					try {
						if(count($current_default)){
							$queries->update("mc_servers", $current_default[0]->id, array(
								'is_default' => 0
							));
						}
						$queries->update("mc_servers", Input::get('main'), array(
							'is_default' => 1
						));
						if(Input::get('external') == '0'){
							$external_query = "false";
						} else {
							$external_query = "true";
						}

						$queries->update('settings', 38, array(
							'value' => $external_query
						));
						echo '<script>window.location.replace("/admin/minecraft");</script>';
						die();
					} catch(Exception $e){
						die($e->getMessage());
					}
				} else {
					echo 'Invalid token - <a href="/admin/minecraft">Back</a>';
					die();
				}
			}
		?>

			<a href="/admin/minecraft/?action=new" class="btn btn-default">New Server</a>
			<br /><br />
			<div class="panel panel-default">
				<div class="panel-heading">Servers</div>
				<div class="panel-body">
					<?php 
					$servers = $queries->getWhere("mc_servers", array("id", "<>", 0));
					$number = count($servers);
					$i = 1;
					
					// Are stats enabled?
					$stats_enabled = $queries->getWhere("settings", array("id", "=", 33));
					$stats_enabled = $stats_enabled[0]->value;
					
					foreach($servers as $server){
					?>
					<div class="row">
						<div class="col-md-6">
							<a href="/admin/minecraft/?sid=<?php echo $server->id; ?>"><?php echo htmlspecialchars($server->name) . '</a><br />' . htmlspecialchars($server->ip); ?>
						</div>
						<div class="col-md-6">
							<span class="pull-right">
								<a href="/admin/stats/?sid=<?php echo $server->id; ?>" <?php if($stats_enabled === "false"){ ?>disabled="disabled" <?php } ?>class="btn btn-primary btn-sm"><span class="glyphicon glyphicon-globe"></span></a>
								<a onclick="return confirm('Are you sure you want to delete this server?');" href="/admin/minecraft/?action=delete_server&sid=<?php echo $server->id; ?>" class="btn btn-danger btn-sm"><span class="glyphicon glyphicon-trash"></span></a>
							</span>
						</div>
					</div>
					<hr>
					<?php 
					}
					?>
				</div>
			</div>
			<form action="" method="post">
			  <div class="form-group">
				 <label for="InputMain">Main Server <a class="btn btn-info btn-xs" data-toggle="popover" title="The server players connect through. Normally this will be the Bungee instance."><span class="glyphicon glyphicon-question-sign"></span></a></label>
				 <select class="form-control" id="InputMain" name="main">
				<?php 
				$default_server = false;
				foreach($servers as $server){
				?>
				  <option value="<?php echo $server->id; ?>" <?php if($server->is_default == 1){ echo 'selected="selected"'; $default_server = true; } ?>><?php echo htmlspecialchars($server->name); ?></option>
				<?php 
				} 
				if($default_server === false){
				?>
				  <option selected disabled>Choose a main server..</option>
				<?php 
				}
				?>
				</select> 
			  </div>
			  <?php
			    // value for external query
				$external_query = $queries->getWhere('settings', array('name', '=', 'external_query'));
				$external_query = $external_query[0]->value;
			  ?>
			  <label for="external_query">Use external query?</label>
			  <input type="hidden" name="external" value="0">
			  <input name="external" value="1" id="external_query" type="checkbox"<?php if($external_query === "true"){ echo ' checked'; } ?>>
			  <a class="btn btn-info btn-xs" data-toggle="popover" title="Use an external API to query the Minecraft server? Only use this if the built in query doesn't work; it's highly recommended that this is unticked."><span class="glyphicon glyphicon-question-sign"></span></a>
			  <br /><br />
  			  <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
			  <input type="submit" value="Submit" class="btn btn-default">
			</form>
		<?php 
		} else if(isset($_GET["sid"]) && !isset($_GET["action"])) { 
			$server = $queries->getWhere("mc_servers", array("id", "=", $_GET["sid"]));
			if(Input::exists()) {
				if(Token::check(Input::get('token'))) {
				$validate = new Validate();
				$validation = $validate->check($_POST, array(
					'servername' => array(
						'required' => true,
						'min' => 2,
						'max' => 20
					),
					'serverip' => array(
						'required' => true,
						'min' => 2,
						'max' => 64
					)				
				));
				
				if($validation->passed()){
					try {
						$queries->update("mc_servers", $_GET["sid"], array(
							'ip' => htmlspecialchars(Input::get('serverip')),
							'name' => htmlspecialchars(Input::get('servername')),
							'display' => Input::get('display'),
							'pre' => Input::get('pre')
						));
						echo '<script>window.location.replace("/admin/minecraft");</script>';
						die();
					} catch(Exception $e){
						die($e->getMessage());
					}
				}
				} else {
					echo 'Invalid token - <a href="/admin/minecraft">Back</a>';
					die();
				}						
			}
			if(isset($validation)){
				if(!$validation->passed()){
			?>
			<div class="alert alert-danger">
				<?php
				foreach($validation->errors() as $error) {
					echo $error, '<br />';
				}
				?>
			</div>
			<?php 
				}
			}
			?>
			<h2>Editing <?php echo htmlspecialchars($server[0]->name);?></h2>
			<form action="" method="post">
				<div class="form-group">
					<input class="form-control" type="text" name="servername" id="servername" value="<?php echo htmlspecialchars($server[0]->name); ?>" autocomplete="off">
				</div>
				<div class="form-group">
					<input class="form-control" type="text" name="serverip" id="serverip" value="<?php echo htmlspecialchars($server[0]->ip); ?>" placeholder="Server IP (with port)" autocomplete="off">
				</div>
				<input type="hidden" name="display" value="0" />
				<label for="InputDisplay">Show on Play page?</label>
				<input name="display" id="InputDisplay" value="1" type="checkbox"<?php if($server[0]->display == 1){ echo ' checked'; } ?>>
				<input type="hidden" name="pre" value="0" />
				<label for="InputPre">Pre 1.7 Minecraft version?</label>
				<input name="pre" id="InputPre" value="1" type="checkbox"<?php if($server[0]->pre == 1){ echo ' checked'; } ?>>
				<br /><br />
				<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
				<input class="btn btn-success" type="submit" value="Update">
			</form>
		<?php 
		} else if(isset($_GET["action"])) { 
			if($_GET["action"] === "new"){
				if(Input::exists()) {
					if(Token::check(Input::get('token'))) {
					$validate = new Validate();
					$validation = $validate->check($_POST, array(
						'servername' => array(
							'required' => true,
							'min' => 2,
							'max' => 20
						),
						'serverip' => array(
							'required' => true,
							'min' => 2,
							'max' => 64
						)				
					));
					
					if($validation->passed()){
						try {
							$queries->create("mc_servers", array(
								'ip' => htmlspecialchars(Input::get('serverip')),
								'name' => htmlspecialchars(Input::get('servername')),
								'display' => Input::get('display'),
								'pre' => Input::get('pre')
							));
							echo '<script>window.location.replace("/admin/minecraft");</script>';
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
						echo $error, '<br />';
					}
					?>
				</div>
				<?php 
					}
				}
				?>
				<form action="" method="post">
					<h2>Add Server</h2>
					<div class="form-group">
						<input class="form-control" type="text" name="servername" id="servername" value="<?php echo htmlspecialchars(Input::get('servername')); ?>" placeholder="Server Name" autocomplete="off">
					</div>
					<div class="form-group">
						<input class="form-control" type="text" name="serverip" id="serverip" value="<?php echo htmlspecialchars(Input::get('serverip')); ?>" placeholder="Server IP (with port)" autocomplete="off">
					</div>
					<input type="hidden" name="display" value="0" />
					<label for="InputDisplay">Show on Play page?</label>
					<input name="display" id="InputDisplay" value="1" type="checkbox">
					<input type="hidden" name="pre" value="0" />
					<label for="InputPre">Pre 1.7 Minecraft version?</label>
					<input name="pre" id="InputPre" value="1" type="checkbox">
					<br /><br />
					<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
					<input class="btn btn-success" type="submit" value="Add">
				</form>
		<?php
			 } else if($_GET["action"] === "delete_server"){
				if(!isset($_GET["sid"]) || !is_numeric($_GET["sid"])){		
					echo 'Invalid server ID - <a href="/admin/minecraft">Back</a>';
					die();
				}
				$server_id = $_GET["sid"];
				try {
					$queries->delete('mc_servers', array('id', '=' , $server_id));
					echo '<script>window.location.replace("/admin/minecraft");</script>';
					die();
				} catch(Exception $e) {
					die($e->getMessage());
				}
			 }
		}
		?>
		</div>
      </div>	  

      <hr>
	  
	  <?php require("inc/templates/footer.php"); ?> 
	  
    </div> <!-- /container -->
		
	<?php require("inc/templates/scripts.php"); ?>
	<script>
	$(function () { $("[data-toggle='popover']").popover({trigger: 'hover', placement: 'top'}); });
	</script>
	
  </body>
</html>
