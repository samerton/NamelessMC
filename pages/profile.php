<?php
/* 
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

require('inc/integration/uuid.php'); // For UUID stuff
require('inc/ext_conf.php');

$profile_user = $queries->getWhere("users", array("username", "=", $profile)); // Is it their username?
if(!count($profile_user)){ // No..
	$profile_user = $queries->getWhere("users", array("mcname", "=", $profile)); // Is it their Minecraft username?
	if(!count($profile_user)){ // No..
		$exists = false;
		$uuid = $queries->getWhere("uuid_cache", array("mcname", "=", $profile)); // Get the UUID, maybe they haven't registered yet
		if(!count($uuid)){
			$profile_utils = ProfileUtils::getProfile($profile);
			if(empty($profile)){ // Not a Minecraft user, end the page
				Redirect::to('/404.php');
				die();
			}
			// A valid Minecraft user..
			$result = $profile_utils->getProfileAsArray(); 
			$uuid = $result["uuid"];
			$mcname = htmlspecialchars($profile);
			// Cache the UUID so we don't have to keep looking it up via Mojang's servers
			try {
				$queries->create("uuid_cache", array(
					'mcname' => $mcname,
					'uuid' => $uuid
				));
			} catch(Exception $e){
				die($e->getMessage());
			}
		} else {
			$uuid = $uuid[0]->uuid;
			$mcname = htmlspecialchars($profile);
		}
	} else {
		$exists = true;
		$uuid = htmlspecialchars($profile_user[0]->uuid);
		$mcname = htmlspecialchars($profile_user[0]->mcname);
	}
} else {
	$exists = true;
	$uuid = htmlspecialchars($profile_user[0]->uuid);
	$mcname = htmlspecialchars($profile_user[0]->mcname);
}

if($user->isLoggedIn()){
	if(isset($_POST['AddFriend'])) {
		$user->addfriend($user->data()->id, $profile_user[0]->id);
	}
	if(isset($_POST['RemoveFriend'])){
		$user->removefriend($user->data()->id, $profile_user[0]->id);
	}
}

$servers = $queries->getWhere("mc_servers", array("display", "=", "1"));
define( 'MQ_TIMEOUT', 1 );
require('inc/integration/status/MinecraftServerPing.php');
require('inc/integration/status/server.php');
$serverStatus = new ServerStatus();
foreach($servers as $server){
	$parts = explode(':', $server->ip);
	if(count($parts) == 1){
		$server_ip = htmlspecialchars($parts[0]);
		$server_port = 25565;
	} else if(count($parts) == 2){
		$server_ip = htmlspecialchars($parts[0]);
		$server_port = htmlspecialchars($parts[1]);
	} else {
		echo 'Invalid IP</div>';
		die();
	}
	if($serverStatus->isOnline($server_ip, $server_port, $mcname) === true){
		$is_online = $server->name;
		break;
	}
}

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo $sitename; ?> profile - <?php echo htmlspecialchars($profile); ?>">
    <meta name="author" content="Samerton">
    <link rel="icon" href="/assets/favicon.ico">

    <title><?php echo $sitename; ?> &bull; Profile</title>
	
	<?php require('inc/templates/header.php'); ?>
	
	<!-- Custom style -->
	<style>
	html {
		overflow-y: scroll;
	}
	.jumbotron {
		margin-bottom: 0px;
		background-image: url(/assets/img/profile.jpg);
		background-position: 0% 25%;
		background-size: cover;
		background-repeat: no-repeat;
		color: white;
	}
	</style>
	
  </head>
  <body>
    <?php require('inc/templates/navbar.php'); ?>
	<div class="container">
	  <?php if(isset($profile)){ ?>
	  <div class="row">
		<div class="col-md-9">
			<div class="jumbotron"><h2><img class="img-rounded" src="https://cravatar.eu/avatar/<?php echo $mcname; ?>/60.png" /> <strong><?php echo $mcname; ?></strong> <?php if($exists == true){ echo $user->getGroup($profile_user[0]->id, null, "true"); } else { echo '<span class="label label-default">Player</span>'; } ?> <span class="label label-<?php if(!isset($is_online)){ echo 'danger">Offline'; } else { echo 'success" rel="tooltip" data-trigger="hover" data-original-title="' . htmlspecialchars($is_online) . '">Online'; }?></span></h2></div>
		    <br />
		    <div role="tabpanel">
			  <!-- Nav tabs -->
			  <ul class="nav nav-tabs" role="tablist">
				<li class="active"><a href="#forum" role="tab" data-toggle="tab">Forum</a></li>
				<?php
				// Are statistics enabled?
				$statistics = $queries->getWhere("settings", array("name", "=", "server_stats"));
				if($statistics[0]->value == "false"){
					$statistics = $queries->getWhere("settings", array("name", "=", "stats"));
					if($statistics[0]->value != "false"){
						$statistics_enabled = true;
					}
				} else {
					$statistics_enabled = true;
				}
				if(isset($statistics_enabled)){
					// Disabled temporarily
				?>
				<!--<li><a href="#ingame" role="tab" data-toggle="tab">Ingame</a></li>-->
				<?php 
				}
				// Are infractions enabled?
				$infractions = $queries->getWhere("settings", array("name", "=", "infractions"));
				if($infractions[0]->value != "false"){
				?>
				<li><a href="#infractions" role="tab" data-toggle="tab">Infractions</a></li>
				<?php
				}
				?>
			  </ul>

			  <!-- Tab panes -->
			  <div class="tab-content">
				<div role="tabpanel" class="tab-pane active" id="forum">
					<br />
					<?php 
					// Check if the user has registered on the website
					if($exists == true){
					?>
					<strong>Registered:</strong> <?php echo date("d M Y, G:i", $profile_user[0]->joined); ?><br />
					<strong>Posts:</strong> <?php echo count($queries->getWhere("posts", array("post_creator", "=", $profile_user[0]->id))); ?><br />
					<strong>Reputation:</strong> <?php echo count($queries->getWhere("reputation", array("user_received", "=", $profile_user[0]->id))); ?><br />
					<?php 
					} else {
					?>
					This user hasn't registered on our website yet.
					<?php 
					} 
					?>
				</div>
				<div role="tabpanel" class="tab-pane" id="ingame">
					<br />
					<h4>Network stats</h4>
					<strong>Disabled</strong>
				</div>
				<?php
				$infractions_query = $queries->getWhere("settings", array("name", "=", "infractions"));
				if($infractions_query[0]->value != "false"){
					$infractions = new Infractions();
				?>
				<div role="tabpanel" class="tab-pane" id="infractions">
					<br />
					<?php 
					// Get the infractions plugin in use
					$infractions_plugin = $queries->getWhere("settings", array("name", "=", "infractions_plugin"));
					$infractions_plugin = $infractions_plugin[0]->value;
					
					if($infractions_plugin == "bat"){
						$all_infractions = $infractions->bat_getAllInfractions($uuid);
					} else if($infractions_plugin == "bm"){
						$all_infractions = $infractions->bm_getAllInfractions($uuid);
					} else if($infractions_plugin == "mb"){
						$all_infractions = $infractions->mb_getAllInfractions(htmlspecialchars($profile));
					}
					?>
					<table class="table table-bordered">
					  <thead>
						<tr>
						  <th></th>
						  <th>Action</th>
						  <th>Reason</th>
						  <th>Punished By</th>
						  <th>Expires</th>
						</tr>
					  </thead>
					  <tbody>
						<?php 
						foreach($all_infractions as $infraction){
						?>
						<tr>
						  <td><a href="/infractions/?type=<?php echo $infraction["type"]; ?>&amp;id=<?php echo $infraction["id"]; ?>">View</a></td>
						  <td><?php echo $infraction["type_human"]; ?></td>
						  <td><?php echo $infraction["reason"]; ?></td>
						  <td><?php if(strtolower($infraction["staff"]) !== "console"){?><a href="/profile/<?php echo $infraction["staff"]; ?>"><?php echo $infraction["staff"]; ?></a><?php } else { ?>Console<?php } ?></td>
						  <td><?php echo $infraction["expires_human"]; ?><?php if(isset($infraction["unbanned"])){ echo ' <span class="label label-success" rel="tooltip" data-trigger="hover" data-original-title="' . date("jS M Y", strtotime($infraction["unbanned_date"])) . ' by ' . $infraction["unbanned_by"] . '">Unbanned</span>'; } else if(isset($infraction["unmuted"])){ echo ' <span class="label label-success" rel="tooltip" data-trigger="hover" data-original-title="' . date("jS M Y", strtotime($infraction["unmuted_date"])) . ' by ' . $infraction["unmuted_by"] . '">Unmuted</span>'; }?></td>
						</tr>
						<?php 
						}
						?>
					  </tbody>
					</table>
				</div>
				<?php
				}
				?>
			  </div>
		    </div>
		</div>
		<div class="col-md-3">
			<div class="well well-sm">
				<h3>Friends</h3>
				<?php
				if($exists == true){
					$friends = $user->listFriends($profile_user[0]->id);
					if($friends !== false){
						foreach($friends as $friend){
							echo '<span rel="tooltip" title="' . $user->IdToName($friend->friend_id) . '"><a href="/profile/' . $user->IdToMCName($friend->friend_id) . '"><img class="img-rounded" src="https://cravatar.eu/avatar/' . $user->IdToMCName($friend->friend_id) . '/40.png" /></a></span> ';
						}
					} else {
						echo 'This user has not added any friends';
					}
					echo '<br /><br />';
					if($user->isLoggedIn()){
						if($user->isfriend($user->data()->id, $profile_user[0]->id) === 0){
							if($user->data()->id === $profile_user[0]->id){
								// echo "Can't add yourself as a friend!";
							} else {
								echo '<center>
								<form style="display: inline"; method="post">
								<input type="submit" class="btn btn-success" name="AddFriend" value="Add Friend">
								</form>
								<a href="/user/messaging/?action=new&uid=' . $profile_user[0]->id . '" class="btn btn-primary">Send Message</a>
								</center>';
							}
						} else {
							if($user->data()->id === $profile_user[0]->id){
								// echo "Can't remove yourself as a friend!";
							} else {
								echo '<center>
								<form style="display: inline"; method="post">
								<input type="submit" class="btn btn-danger" name="RemoveFriend" value="Remove Friend">
								</form>
								<a href="/user/messaging/?action=new&uid=' . $profile_user[0]->id . '" class="btn btn-primary">Send Message</a>
								</center>';
							}
						}
					}
				} else {
				?>
				This user has not added any friends.<br /><br />
				<?php 
				}
				?>
			</div>
		</div>
	  </div>

	  <?php } else { 
		if(Input::exists()){
			Redirect::to('/profile/' . htmlspecialchars(Input::get('username')));
			die();
		}
	  
	  
	  ?>
	    <h2>Find a user</h2>
		<?php if(Input::exists() && isset($error)){ ?>
		<div class="alert alert-danger">Can't find that user</div>
		<?php } ?>
		<form role="form" action="" method="post">
		  <input type="text" name="username" id="username" autocomplete="off" value="<?php echo escape(Input::get('username')); ?>" class="form-control input-lg" placeholder="Username" tabindex="1">
		  <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
		  <br />
		  <input type="submit" value="Search" class="btn btn-primary btn-lg" tabindex="2">
		</form>
	  <?php } ?>
	  <hr>
	  
	  <?php require('inc/templates/footer.php'); ?>
	</div>
	<?php require('inc/templates/scripts.php'); ?>
	<script>
	$(document).ready(function(){
		$("[rel=tooltip]").tooltip({ placement: 'top'});
	});
	</script>
  </body>
</html>