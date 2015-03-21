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
	  ?>
		<table class="table table-bordered">
		  <thead>
			<tr>
			  <th>ID</th>
			  <th>User</th>
			  <th>Staff</th>
			  <th>Action</th>
			  <th>Reason</th>
			  <th>Expires</th>
			</tr>
		  </thead>
	  <?php 
		$all_infractions = $infractions->bat_getAllInfractions();
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
	  ?>
		<tbody>
			<?php 
			while ($n < $d) {
				$infraction = $all_infractions[$n];
			?>
			<tr>
			  <td><a href="/infractions/?type=<?php echo $infraction["type"]; ?>&amp;id=<?php echo $infraction["id"]; ?>"><?php echo $infraction["id"]; ?></a></td>
			  <td><?php 
				$infractions_query = $queries->getWhere('users', array('uuid', '=', $infraction["uuid"]));
				if(empty($infractions_query)){
					$infractions_query = $queries->getWhere('uuid_cache', array('uuid', '=', $infraction["uuid"]));
					if(empty($infractions_query)){
						require('inc/integration/uuid.php');
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
			  ?></td>
			  <td><?php if($infraction["staff"] !== "CONSOLE"){?><a href="/profile/<?php echo $infraction["staff"]; ?>"><?php echo $infraction["staff"]; ?></a><?php } else { ?>Console<?php } ?></td>
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
		if(!isset($_GET["type"]) || !isset($_GET["id"]) || !is_numeric($_GET["id"])){
			Redirect::to('/infractions');
			die();
		}
		
		if($_GET["type"] !== "ban" && $_GET["type"] !== "kick" && $_GET["type"] !== "mute"){
			Redirect::to('/infractions');
			die();
		}
		
		$infraction = $infractions->bat_getInfraction($_GET["type"], $_GET["id"]);
		$infractions_query = $queries->getWhere('users', array('uuid', '=', $infraction[0]->UUID));
		if(empty($infractions_query)){
			$infractions_query = $queries->getWhere('uuid_cache', array('uuid', '=', $infraction[0]->UUID));
			if(empty($infractions_query)){
				require('inc/integration/uuid.php');
				$profile = ProfileUtils::getProfile($infraction[0]->UUID);
				if(empty($profile)){
					echo 'Could not find that player';
					die();
				}
				$result = $profile->getProfileAsArray();
				$mcname = htmlspecialchars($result["username"]);
				$uuid = htmlspecialchars($infraction[0]->UUID);
				try {
					$queries->create("uuid_cache", array(
						'mcname' => $mcname,
						'uuid' => $uuid
					));
				} catch(Exception $e){
					die($e->getMessage());
				}
			} else {
				$mcname = $queries->getWhere('uuid_cache', array('uuid', '=', $infraction[0]->UUID));
				$mcname = $mcname[0]->mcname;
			}
		} else {
			$mcname = $queries->getWhere('users', array('uuid', '=', $infraction[0]->UUID));
			$mcname = $mcname[0]->mcname;
		}
		
		
	  ?>
		<a href="/infractions" class="btn btn-primary">Back</a>
		<h3>Player: <?php echo htmlspecialchars($mcname); ?></h3>
		Infraction type: <?php $type = strtolower($_GET["type"]); echo htmlspecialchars(ucfirst($type)); ?><br />
		Date of infraction: <?php echo date("jS M Y", strtotime($infraction[0]->ban_begin)); ?><br />
		Infraction ends: 
		<?php 
		if($type === "ban"){
			if($infraction[0]->ban_end !== null){
				date("jS M Y", strtotime($infraction[0]->ban_end));
			} else {
				echo 'Never';
			}
		} else if($type === "mute"){
			if($infraction[0]->mute_end !== null){
				date("jS M Y", strtotime($infraction[0]->mute_end));
			} else {
				echo 'Never';
			}
		} else if($type === "kick"){
			"n/a";
		}
		?><br />
		Reason for infraction: 
		<?php 
		if($type === "ban"){
			if($infraction[0]->ban_reason !== null){
				echo htmlspecialchars($infraction[0]->ban_reason);
			} else {
				echo "Not set";
			}
		} else if($type === "mute"){
			if($infraction[0]->mute_reason !== null){
				echo htmlspecialchars($infraction[0]->mute_reason);
			} else {
				echo "Not set";
			}
		} else if($type === "kick"){
			if($infraction[0]->kick_reason !== null){
				echo htmlspecialchars($infraction[0]->kick_reason);
			} else {
				echo "Not set";
			}
		}
		?><br />
		Issued by: <a href="/profile/<?php 
		if($type === "ban"){
			echo htmlspecialchars($infraction[0]->ban_staff); ?>"><?php echo htmlspecialchars($infraction[0]->ban_staff); 
		} else if($type === "mute"){
			echo htmlspecialchars($infraction[0]->mute_staff); ?>"><?php echo htmlspecialchars($infraction[0]->mute_staff); 
		} else if($type === "kick"){
			echo htmlspecialchars($infraction[0]->kick_staff); ?>"><?php echo htmlspecialchars($infraction[0]->kick_staff); 
		}
		?></a><br />
		Infraction revoked: 
		<?php 
		if($type === "ban"){
			if($infraction[0]->ban_unbandate !== null){
				echo "Yes, by <a href=\"/profile/" . htmlspecialchars($infraction[0]->unbanstaff) . "\">" . htmlspecialchars($infraction[0]->unbanstaff) . "</a> on " . date("jS M Y", strtotime($infraction[0]->ban_unbandate));
			} else {
				echo "No";
			}
		} else if($type === "mute"){
			if($infraction[0]->mute_unmutedate !== null){
				echo "Yes, by <a href=\"/profile/" . htmlspecialchars($infraction[0]->mute_unmutestaff) . "\">" . htmlspecialchars($infraction[0]->mute_unmutestaff) . "</a> on " . date("jS M Y", strtotime($infraction[0]->mute_unmutedate));
			} else {
				echo "No";
			}
		} else if($type === "kick"){
			echo "n/a";
		}
		?><br />
		
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