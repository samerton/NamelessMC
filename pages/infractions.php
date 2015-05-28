<?php 
/* 
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

require_once 'inc/ext_conf.php';
require_once 'inc/functions/paginate.php';

$infractions = $queries->getWhere("settings", array("name", "=", "infractions"));
if($infractions[0]->value == "false"){
	Redirect::to('/');
	die();
}

$infractions = new Infractions();

// Get the plugin in use
$infractions_plugin = $queries->getWhere("settings", array("name", "=", "infractions_plugin"));
$infractions_plugin = $infractions_plugin[0]->value;

if(isset($_GET['p'])){
	if(!is_numeric($_GET['p'])){
		Redirect::to("/infractions");
	} else {
		$p = $_GET['p'];
	}
} else {
	$p = 1;
}

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo $sitename; ?> Infractions List">
    <meta name="author" content="Samerton">
    <link rel="icon" href="/assets/favicon.ico">

    <title><?php echo $sitename; ?> &bull; Infractions</title>
	
	<?php require("inc/templates/header.php"); ?>

  </head>

  <body>

	<?php require("inc/templates/navbar.php"); ?>

    <div class="container">	
	  
	  <?php 
	  if(!isset($_GET["type"]) && !isset($_GET["id"])){ 
	    if($infractions_plugin == "bat"){
			$all_infractions = $infractions->bat_getAllInfractions();
		} else if($infractions_plugin == "bm"){
			$all_infractions = $infractions->bm_getAllInfractions();
		} else if($infractions_plugin == "mb"){
			$all_infractions = $infractions->mb_getAllInfractions();
		} else if($infractions_plugin == "lb"){
			$all_infractions = $infractions->lb_getAllInfractions();
		}
			
		$paginate = PaginateArray($p);
		
		$n = $paginate[0];
		$f = $paginate[1];
		
		if(count($all_infractions) > $f){
			$d = $p * 10;
		} else {
			$d = count($all_infractions) - $n;
			$d = $d + $n;
		}
		
		$sn = 1;
		$fn = ceil(count($all_infractions) / 10);
		if(count($all_infractions)){
	  ?>
		<table class="table table-bordered">
		  <thead>
			<tr>
			  <th></th>
			  <th>User</th>
			  <th>Staff</th>
			  <th>Action</th>
			  <th>Reason</th>
			  <th>Expires</th>
			</tr>
		  </thead>
		<tbody>
			<?php 
			while ($n < $d) {
				$infraction = $all_infractions[$n];
				if($infractions_plugin == "mb"){
					$exploded = explode('.', $infraction["id"]);
					$mcname = $exploded[0];
					$time = $exploded[1];
				}
				if($infractions_plugin == "lb"){
					$mcname = $infraction["username"];
				}
			?>
			<tr>
			  <td><a href="/infractions/?type=<?php echo $infraction["type"]; ?>&amp;id=<?php echo $infraction["id"]; ?>">View</a></td>
			  <td><?php 
			    if($infractions_plugin !== "mb" && $infractions_plugin !== "lb"){
					$infractions_query = $queries->getWhere('users', array('uuid', '=', $infraction["uuid"]));
					if(empty($infractions_query)){
						$infractions_query = $queries->getWhere('uuid_cache', array('uuid', '=', $infraction["uuid"]));
						if(empty($infractions_query)){
							require_once('inc/integration/uuid.php');
							$profile = ProfileUtils::getProfile($infraction["uuid"]);
							if(empty($profile)){
								echo 'Could not find that player';
								die();
							}
							$result = $profile->getProfileAsArray();
							$mcname = htmlspecialchars($result["username"]);
							$uuid = htmlspecialchars($infraction["uuid"]);
							try {
								$queries->create("uuid_cache", array(
									'mcname' => $mcname,
									'uuid' => $uuid
								));
							} catch(Exception $e){
								die($e->getMessage());
							}
						}
						$mcname = $queries->getWhere('uuid_cache', array('uuid', '=', $infraction["uuid"]));
						echo '<a href="/profile/' . htmlspecialchars($mcname[0]->mcname) . '">' . htmlspecialchars($mcname[0]->mcname) . '</a>'; 
					} else {
						$mcname = $queries->getWhere('users', array('uuid', '=', $infraction["uuid"]));
						echo '<a href="/profile/' . htmlspecialchars($mcname[0]->mcname) . '">' . htmlspecialchars($mcname[0]->mcname) . '</a>';
					}
				} else {
					echo '<a href="/profile/' . $mcname . '">' . $mcname . '</a>';
				}
			  ?></td>
			  <td><?php if(strtolower($infraction["staff"]) !== "console"){?><a href="/profile/<?php echo $infraction["staff"]; ?>"><?php if($infractions_plugin !== "mb"){ echo $infraction["staff"]; } else { echo $infractions->mb_getUsernameFromName($infraction["staff"]); }?></a><?php } else { ?>Console<?php } ?></td>
			  <td><?php echo $infraction["type_human"]; ?></td>
			  <td><?php echo $infraction["reason"]; ?></td>
			  <td><?php echo $infraction["expires_human"]; ?><?php if(isset($infraction["unbanned"])){ echo ' <span class="label label-success" rel="tooltip" data-trigger="hover" data-original-title="' . date("jS M Y", strtotime($infraction["unbanned_date"])) . ' by ' . $infraction["unbanned_by"] . '">Unbanned</span>'; } else if(isset($infraction["unmuted"])){ echo ' <span class="label label-success" rel="tooltip" data-trigger="hover" data-original-title="' . date("jS M Y", strtotime($infraction["unmuted_date"])) . ' by ' . $infraction["unmuted_by"] . '">Unmuted</span>'; }?></td>
			</tr>
			<?php 
			$n++;
			}
			?>
		</tbody>
		</table>
		<?php 
			echo '
			<ul class="pagination pagination-sm">
			<li';
			if ($p == 1){
			echo ' class="disabled"><span>&laquo;</span></li>';
			} else {
			echo '><a href="/infractions/?p=' . ($p - 1) . '">&laquo;</a></li>';
			}
			while ($sn < ($fn + 1)) {
			echo '<li';
			if ($sn == $p){
			echo ' class="active"';
			}
			echo '><a href="/infractions/?p=' . $sn . '"> ' . $sn . '</a></li>';
			$sn++;
			}
			echo '
			<li';
			if ($p != $fn){
			echo'><a href="/infractions/?p=' . ($p + 1) . '">&raquo;</a>';
			} else {
			echo' class="disabled"><span>&raquo;</span></li>';
			}
			echo '
			</ul>';
		} else {
			echo '<strong>No infractions</strong>';
		}

	  } else {
		if(!isset($_GET["type"]) || !isset($_GET["id"])){
			if($infractions_plugin != "mb" && !is_numeric($_GET["id"])){
				Redirect::to('/infractions');
				die();
			}
		}
		
		if($_GET["type"] !== "ban" && $_GET["type"] !== "kick" && $_GET["type"] !== "mute" && $_GET["type"] !== "temp_ban" && $_GET["type"] !== "warning"){
			Redirect::to('/infractions');
			die();
		}
		
		// The following is different for each infractions plugin.
	    if($infractions_plugin == "bat"){
			// BungeeAdminTools
			$infraction = $infractions->bat_getInfraction($_GET["type"], $_GET["id"]);
			
			// Get the username from the UUID - has the user registered on the website?
			$infractions_query = $queries->getWhere('users', array('uuid', '=', $infraction[0]->UUID));
			
			if(empty($infractions_query)){
				// User hasn't registered on the website, check the cache
				$infractions_query = $queries->getWhere('uuid_cache', array('uuid', '=', $infraction[0]->UUID));
				
				if(empty($infractions_query)){
					// Not in the cache, get it from Mojang's servers
					require_once('inc/integration/uuid.php');
					$profile = ProfileUtils::getProfile($infraction[0]->UUID);
					
					if(empty($profile)){
						// No return from Mojang's servers
						echo 'Could not find that player';
						die();
					}
					
					$result = $profile->getProfileAsArray();
					$mcname = htmlspecialchars($result["username"]);
					$uuid = htmlspecialchars($infraction[0]->UUID);
					
					// Input into cache
					try {
						$queries->create("uuid_cache", array(
							'mcname' => $mcname,
							'uuid' => $uuid
						));
					} catch(Exception $e){
						die($e->getMessage());
					}
				} else {
					// User is in UUID cache
					$mcname = $queries->getWhere('uuid_cache', array('uuid', '=', $infraction[0]->UUID));
					$mcname = $mcname[0]->mcname;
				}
			} else {
				// User has registered on the website, use the Minecraft username value
				$mcname = $queries->getWhere('users', array('uuid', '=', $infraction[0]->UUID));
				$mcname = $mcname[0]->mcname;
			}
			
			// Next, get some variables to display on the page. This depends on the type of infraction.
			if($_GET["type"] == "ban" || $_GET["type"] == "temp_ban"){
				// Ban
				// Date of infraction
				$start_date = date("jS M Y", strtotime($infraction[0]->ban_begin));
				
				// End of infraction
				if($infraction[0]->ban_end !== null){
					$end_date = date("jS M Y", strtotime($infraction[0]->ban_end));
				} else {
					$end_date = 'Never';
				}
				
				// Reason
				if($infraction[0]->ban_reason !== null){
					$reason = htmlspecialchars($infraction[0]->ban_reason);
				} else {
					$reason = "Not set";
				}
				
				// Staff
				$issued_by = htmlspecialchars($infraction[0]->ban_staff);
				
				// Revoked?
				if($infraction[0]->ban_unbandate !== null){
					$revoked = "Yes, by <a href=\"/profile/" . htmlspecialchars($infraction[0]->ban_unbanstaff) . "\">" . htmlspecialchars($infraction[0]->ban_unbanstaff) . "</a> on " . date("jS M Y", strtotime($infraction[0]->ban_unbandate));
				} else {
					$revoked = "No";
				}
			
			} else if($_GET["type"] == "mute"){
				// Mute
				// Date of infraction
				$start_date = date("jS M Y", strtotime($infraction[0]->mute_begin));
				
				// End of infraction
				if($infraction[0]->mute_end !== null){
					$end_date = date("jS M Y", strtotime($infraction[0]->mute_end));
				} else {
					$end_date = 'Never';
				}
				
				// Reason
				if($infraction[0]->mute_reason !== null){
					$reason = htmlspecialchars($infraction[0]->mute_reason);
				} else {
					$reason = "Not set";
				}
				
				// Staff
				$issued_by = htmlspecialchars($infraction[0]->mute_staff);
				
				// Revoked?
				if($infraction[0]->mute_unmutedate !== null){
					$revoked = "Yes, by <a href=\"/profile/" . htmlspecialchars($infraction[0]->mute_unmutestaff) . "\">" . htmlspecialchars($infraction[0]->mute_unmutestaff) . "</a> on " . date("jS M Y", strtotime($infraction[0]->mute_unmutedate));
				} else {
					$revoked = "No";
				}
				
			} else if($_GET["type"] == "kick"){
				// Kick
				// Date of infraction
				$start_date = date("jS M Y", strtotime($infraction[0]->kick_date));
				
				// End of infraction
				$end_date = 'n/a';
				
				// Reason
				if($infraction[0]->kick_reason !== null){
					$reason = htmlspecialchars($infraction[0]->kick_reason);
				} else {
					$reason = 'Not set';
				}
				
				// Staff
				$issued_by = htmlspecialchars($infraction[0]->kick_staff);
				
				// Revoked?
				$revoked = 'n/a';
			}
			
		} else if($infractions_plugin == "bm"){
			// Ban Management
			$infraction = $infractions->bm_getInfraction($_GET["type"], $_GET["id"]);
			
			// First, get the username
			$mcname = $infractions->bm_getUsernameFromID($infraction[0]->player_id);
			
			// Date of infraction
			$start_date = date("jS M Y", $infraction[0]->created);
			
			// Reason
			if($infraction[0]->reason !== null){
				$reason = htmlspecialchars($infraction[0]->reason);
			} else {
				$reason = "Not set";
			}
			
			$revoked = "No";
			$end_date = "n/a";
			
			// Staff
			if(isset($infraction[0]->pastActor_id)){
				$issued_by = htmlspecialchars($infractions->bm_getUsernameFromID($infraction[0]->pastActor_id));
			} else {
				$issued_by = htmlspecialchars($infractions->bm_getUsernameFromID($infraction[0]->actor_id));
			}
			
			// Ban and mute specific:
			if($_GET['type'] == "ban" || $_GET['type'] == "temp_ban" || $_GET['type'] == "mute"){
				// End of infraction
				if(isset($infraction[0]->expires)){
					// Not expired yet, or is permanent
					if($infraction[0]->expires != 0){
						// Will expire
						$end_date = date("jS M Y", $infraction[0]->expires);
						
					} else {
						// Permanent
						$end_date = 'Never';
						
					}
				} else if(isset($infraction[0]->expired)){
					// Expired or unbanned
					if($infraction[0]->expired != 0){
						// Has expired
						$end_date = date("jS M Y", $infraction[0]->expired);
						
					} else {
						// Unbanned
						$end_date = 'Never';
						$revoked = 'Yes, on ' . date("jS M Y", $infraction[0]->created);
					}
				}
			}
			
		} else if($infractions_plugin == "mb"){
			// MaxBans
			$infraction = $infractions->mb_getInfraction($_GET["type"], $_GET["id"]);
			$exploded = explode('.', $_GET["id"]);
			$mcname = $exploded[0];
			$time = $exploded[1];
			
			if($_GET["type"] == "ban"){
				$end_date = 'Never';
			} else if($_GET["type"] == "mute" || $_GET["type"] == "temp_ban" || $_GET["type"] == "warning"){
				if($infraction->expires != 0){
					$end_date = date('jS M Y', $infraction->expires / 1000);
				} else {
					$end_date = 'Never';
				}
			}
			
			if(isset($infraction->time)){
				$start_date = date('jS M Y', $infraction->time / 1000);
			} else {
				$start_date = date('jS M Y', strtotime('-3 days', $infraction->expires / 1000));
			}
			
			// Reason
			if($infraction->reason !== null){
				$reason = htmlspecialchars($infraction->reason);
			} else {
				$reason = "Not set";
			}
			
			if($infraction->banner !== "console"){
				$issued_by = $infractions->mb_getUsernameFromName($infraction->banner);
			} else {
				$issued_by = "console";
			}
			
		} else if($infractions_plugin == "lb"){
			// LiteBans
			$infraction = $infractions->lb_getInfraction($_GET["type"], $_GET["id"]);
			$mcname = $infraction[1];
			$infraction = $infraction[0];
			
			if($_GET["type"] == "ban"){
				$end_date = 'Never';
				if($infraction->active == null){
					// active
				} else {
					if(ord($infraction->active) == '1'){
						// active
					} else {
						// revoked
						$unbanned = true;
					}
				}
			} else if($_GET["type"] == "mute" || $_GET["type"] == "temp_ban" || $_GET["type"] == "warning"){
				if($infraction->until != '-1'){
					$end_date = date('jS M Y', $infraction->until / 1000);
				} else {
					$end_date = 'Never';
				}
				if($_GET["type"] == "mute"){
					if($infraction->active == null){
						// active
					} else {
						if(ord($infraction->active) == '1'){
							// active
						} else {
							// revoked
							$unbanned = true;
						}
					}
				}
			}
			
			$start_date = date('jS M Y', $infraction->time / 1000);
			
			// Reason
			if($infraction->reason !== null){
				$reason = htmlspecialchars($infraction->reason);
			} else {
				$reason = "Not set";
			}
			
			if($infraction->banned_by_uuid !== "CONSOLE"){
				// Get username of staff from UUID
				$staff_uuid = str_replace('-', '', $infraction->banned_by_uuid);
				$infractions_query = $queries->getWhere('users', array('uuid', '=', htmlspecialchars($staff_uuid)));
				if(empty($infractions_query)){
					$infractions_query = $queries->getWhere('uuid_cache', array('uuid', '=', htmlspecialchars($staff_uuid)));
					if(empty($infractions_query)){
						require_once('inc/integration/uuid.php');
						$profile = ProfileUtils::getProfile($staff_uuid);
						if(empty($profile)){
							echo 'Could not find that player';
							die();
						}
						$result = $profile->getProfileAsArray();
						$staff = htmlspecialchars($result["username"]);
						$uuid = htmlspecialchars($staff_uuid);
						try {
							$queries->create("uuid_cache", array(
								'mcname' => $staff,
								'uuid' => $uuid
							));
						} catch(Exception $e){
							die($e->getMessage());
						}
					}
					$staff = $queries->getWhere('uuid_cache', array('uuid', '=', $staff_uuid));
					$issued_by = $staff[0]->mcname;
				} else {
					$staff = $queries->getWhere('users', array('uuid', '=', $staff_uuid));
					$issued_by = $staff[0]->mcname;
				}
			} else {
				$issued_by = "console";
			}
		}
	  ?>
		<a href="/infractions" class="btn btn-primary">Back</a>
		<h3>Player: <?php echo htmlspecialchars($mcname); ?></h3>
		Infraction type: <?php $type = strtolower($_GET["type"]); echo htmlspecialchars(str_replace('_', ' ', ucfirst($type))); ?><?php if($infractions_plugin == "lb" && isset($unbanned)){ ?> - <strong>Revoked</strong><?php } ?><br />
		Date of infraction: <?php echo $start_date; ?><br />
		Infraction ends: <?php echo $end_date; ?><br />
		Reason for infraction: <?php echo $reason; ?><br />
		Issued by: <?php if(strtolower($issued_by) != "console"){ ?><a href="/profile/<?php echo $issued_by; ?>"><?php echo $issued_by; ?></a><?php } else { ?>Console<?php } ?><br />
		<?php if($infractions_plugin !== "mb" && $infractions_plugin !== "lb"){ ?>Infraction revoked: <?php echo $revoked; ?><br /><?php } ?>
	  <?php 
	  }
	  ?>
		
      <hr>

	<?php require("inc/templates/footer.php"); ?>
	  
    </div> <!-- /container -->
		
	<?php require("inc/templates/scripts.php"); ?>
	<script>
	$(document).ready(function(){
    $("[rel=tooltip]").tooltip({ placement: 'top'});
	});
	</script>	
  </body>
</html>