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

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Samerton">
	<link rel="icon" href="/favicon.ico">
	<meta name="robots" content="noindex">

    <title><?php echo $sitename; ?> &bull; ModCP Announcements</title>
	
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
				  <li class="active"><a href="/mod/punishments">Punishments</a></li>
				  <li><a href="/mod/announcements">Announcements</a></li>
				</ul>
			</div>
		</div>
		<div class="col-md-9">
			<div class="well well-sm">
				<h2>Punishments</h2>
				<?php 
				if(isset($_GET["action"]) && isset($_GET["uid"]) && !isset($_GET["ip"])){
					if(!is_numeric($_GET["uid"])){
						echo '<script>window.location.replace("/mod/punishments");</script>';
						die();
					}
					$punished_user = $queries->getWhere("users", array("id", "=", $_GET["uid"]));
					if(!count($punished_user)){
						echo '<script>window.location.replace("/mod/punishments");</script>';
						die();
					}
					if(Input::exists()){
						if(Token::check(Input::get('token'))) {
							$validate = new Validate();
							$validation = $validate->check($_POST, array(
								'reason' => array(
									'required' => true,
									'min' => 2,
									'max' => 256
								)
							));
							if(!$validation->passed()){
								Session::flash('punishment_error', '<div class="alert alert-danger">Please enter a valid reason between 2 and 256 characters</div>');
							} else {
								if(Input::get('punishment') === "ban"){
									$type = 1;
								} else if(Input::get('punishment') === "warn"){
									$type = 2;
								}
							
								try {
									$queries->create("infractions", array(
										"type" => $type,
										"punished" => $_GET["uid"],
										"staff" => $user->data()->id,
										"reason" => htmlspecialchars(Input::get('reason')),
										"infraction_date" => date('Y-m-d H:i:s'),
										"acknowledged" => 0
									));
									if(Input::get('punishment') === "ban"){
										$queries->update("users", $_GET["uid"], array(
											"isbanned" => 1,
											"active" => 0
										));
										$queries->delete("users_session", array("user_id", "=", $_GET["uid"]));
									}
									echo '<script>window.location.replace("/mod/punishments");</script>';
								} catch(Exception $e) {
									die($e->getMessage());
								}
							}
						}
					}
					if(Session::exists('punishment_error')){
						echo Session::flash('punishment_error');
					}
				?>
					<h4>Reason:</h4>
					<form role="form" action="" method="post">
						<textarea name="reason" class="form-control"></textarea>
						<br />
						<input type="hidden" name="punishment" value="<?php echo htmlspecialchars($_GET["action"]); ?>">
						<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
						<input type="submit" value="Submit" class="btn btn-primary">
					</form>
				<?php 
				} else if(!isset($_GET["uid"]) && isset($_GET["action"]) && isset($_GET["ip"])){
				?>
					<h4>IP lookup: <?php echo str_replace("-", ".", htmlspecialchars($_GET["ip"])); ?></h4>
					<table class="table table-bordered">
					  <thead>
					    <tr>
						  <th>
						    Username
						  </th>
						  <th>
						    Minecraft username
						  </th>
						  <th>
						    Registered
						  </th>
						</tr>
					  </thead>
					  <tbody>
					    <?php 
					    $ip_users = $queries->getWhere("users", array("lastip", "=", str_replace("-", ".", $_GET["ip"]))); 
					    foreach($ip_users as $ip_user){
					    ?>
					    <tr>
						  <td>
					        <a href="/profile/<?php echo htmlspecialchars($ip_user->mcname); ?>"><?php echo htmlspecialchars($ip_user->username); ?></a>
						  </td>
						  <td>
						    <?php echo htmlspecialchars($ip_user->mcname); ?>
						  </td>
						  <td>
						    <?php echo date("d M Y, H:i", $ip_user->joined); ?>
						  </td>
						</tr>					   
					    <?php 
					    }
					    ?>
					  </tbody>
					</table>
				<?php 
				} else {
					if(Input::exists()){
						if(Token::check(Input::get('token'))) {
							if(Input::get('action') === "search"){
								$validate = new Validate();
								$validation = $validate->check($_POST, array(
									'user' => array(
										'required' => true
									)
								));

								if(!$validation->passed()){
									echo '<script>window.location.replace("/mod/punishments");</script>';
									die();
								}

								$search_result = $queries->getWhere("users", array("username", "=", Input::get('user')));
								$search_result = $search_result[0];
								if(!count($search_result)){
									echo '<script>window.location.replace("/mod/punishments");</script>';
									die();
								} else {
								?>
								<h3 style="display: inline;">User: <?php echo htmlspecialchars($search_result->username); ?></h3> <h4 style="display: inline;">(<strong>IP:</strong> <a target="_blank" href="/mod/punishments/?action=lookup&ip=<?php echo str_replace(".", "-", htmlspecialchars($search_result->lastip)); ?>"><?php echo htmlspecialchars($search_result->lastip); ?></a>)</h4>
								<br /><br />
								<a class="btn btn-danger" href="/mod/punishments/?action=ban&uid=<?php echo $search_result->id; ?>">Ban</a>
								<a class="btn btn-warning" href="/mod/punishments/?action=warn&uid=<?php echo $search_result->id; ?>">Warn</a>
								<?php 
								}
							}
						} else {
							echo 'Invalid token - <a href="/mod/punishments">Back</a>';
							die();
						}
					} else {
				?>
				<form role="form" action="" method="post">
					<div class="row">
					   <div class="col-xs-12">
							<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
							<input type="hidden" name="action" value="search">
							<div class="input-group">
								<input class="form-control" type="text" name="user" placeholder="Search for a user.." autocomplete="off">
								<div class="input-group-btn">
								  <button class="btn btn-default" type="submit"><span class="glyphicon glyphicon-search"></span></button>
								</div>
							</div>
					   </div>
					</div>
				</form>
				<?php 
					}
				}
				?>
			</div>
		</div>
      </div>	  

      <hr>

	  <?php require("inc/templates/footer.php"); ?> 
	  
    </div> <!-- /container -->
		
	<?php require("inc/templates/scripts.php"); ?>
	
  </body>
</html>