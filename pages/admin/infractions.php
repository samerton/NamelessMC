<?php 
/*
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

// Set page name for sidebar
$page = "admin-infractions";

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

// Handle data input
if(Input::exists()){
	if(Token::check(Input::get('token'))){
		// Plugin
		$queries->update("settings", 28, array(
			"value" => Input::get('plugin')
		));
		
		Session::flash('infractions_post_success', '<div class="alert alert-success">Success</div>');
	}
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

    <title><?php echo $sitename; ?> &bull; AdminCP Infractions</title>
	
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
			<div class="well">
			  <br />
			  <h2 style="display: inline;" >Infractions - Settings</h2>
			  <br /><br />
			  <em>Disable the "Infractions" page from the Pages tab</em><hr>
			<?php
			if(Session::exists('infractions_post_success')){
				echo Session::flash('infractions_post_success');
			}

			// Query the database
			$query = $queries->getWhere("settings", array("id", "<>", 0));
			?>
			  <form action="" method="post">
				<strong>Plugin</strong><br />
				<div class="btn-group" data-toggle="buttons">
				  <label class="btn btn-primary<?php if($query[27]->value == "bat"){ ?> active<?php } ?>">
					<input type="radio" name="plugin" id="InputPlugin1" value="bat" autocomplete="off"<?php if($query[27]->value == "bat"){ ?> checked<?php } ?>> BungeeAdminTools
				  </label>
				  <label class="btn btn-primary<?php if($query[27]->value == "bm"){ ?> active<?php } ?>">
					<input type="radio" name="plugin" id="InputPlugin2" value="bm" autocomplete="off"<?php if($query[27]->value == "bm"){ ?> checked<?php } ?>> Ban Management
				  </label>
				  <label class="btn btn-primary<?php if($query[27]->value == "mb"){ ?> active<?php } ?>">
					<input type="radio" name="plugin" id="InputPlugin3" value="mb" autocomplete="off"<?php if($query[27]->value == "mb"){ ?> checked<?php } ?>> MaxBans
				  </label>
				  <label class="btn btn-primary<?php if($query[27]->value == "lb"){ ?> active<?php } ?>">
					<input type="radio" name="plugin" id="InputPlugin4" value="lb" autocomplete="off"<?php if($query[27]->value == "lb"){ ?> checked<?php } ?>> LiteBans
				  </label>
				</div>
				<br /><br />
				<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
				<input type="submit" class="btn btn-primary" value="Update" />
			  </form>
			</div>
		</div>
      </div>	  

      <hr>
	  
	  <?php require("inc/templates/footer.php"); ?> 
	  
    </div> <!-- /container -->
		
	<?php require("inc/templates/scripts.php"); ?>
  </body>
</html>