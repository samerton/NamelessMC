<?php 
/*
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

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

    <title><?php echo $sitename; ?> &bull; AdminCP Buycraft Sync</title>
	
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
			<div class="well">
				<h2>Synchronise Buycraft</h2>
				The following will synchronise Buycraft with your site database. Please be patient, this process may take a while...
				<br /><br />
				<center><button onclick="syncBuycraft();" class="btn btn-primary">Synchronise</button></center>
				<h3>Automating the synchronisation</h3>
				In order to automate the synchronisation, you will need to set up a cron job on your webserver. It will need to load the following URL:
				<br />
				<?php
				// Get Buycraft sync code
				$buycraft_code = $queries->getWhere("settings", array("name", "=", "buycraft_sync_key"));
				$buycraft_code = $buycraft_code[0]->value;
				?>
				<code>http://<?php echo $_SERVER['SERVER_NAME']; ?>/admin/execute_buycraft_sync/?key=<?php echo $buycraft_code; ?></code>
				<br /><br />
				<strong>Please keep the above URL a secret!</strong>
				<br /><br />
				To avoid using the Buycraft API too often, please leave a reasonable time period between running the cron job.
			</div>
		</div>
      </div>	  

      <hr>
 
	  <?php require("inc/templates/footer.php"); ?> 
	  
    </div> <!-- /container -->
		
	<?php require("inc/templates/scripts.php"); ?>

	<!-- Modal -->
	<div class="modal fade" data-keyboard="false" data-backdrop="static" id="loadingModal" tabindex="-1" role="dialog" aria-labelledby="loadingModalLabel" aria-hidden="true">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<h4 class="modal-title" id="loadingModalLabel">Synchronising, please wait..</h4>
		  </div>
		  <div class="modal-body">
			<div class="progress">
			  <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
			  </div>
			</div>
		  </div>
		</div>
	  </div>
	</div>		

	<script type="text/javascript">
	function syncBuycraft()
	{
		$('#loadingModal').modal('show');
		$.ajax(
			{
				   type: "POST",
				   url: "/admin/execute_buycraft_sync",
				   cache: false,

				   success: function(response)
				   {
					$('#loadingModal').modal('hide');
					location.reload();
				   }
			 });
	}
	</script>

  </body>
</html>