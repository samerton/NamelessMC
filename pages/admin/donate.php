<?php 
/*
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

// Set page name for sidebar
$page = "admin-donate";

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

// Handle data input
if(Input::exists()){
	if(Token::check(Input::get('token'))){
		// Store type
		$queries->update("settings", 36, array(
			"value" => Input::get('store_type')
		));
		
		// API Key
		$queries->update("settings", 6, array(
			"value" => htmlspecialchars(Input::get('apikey'))
		));
		
		// Store URL
		$queries->update("settings", 35, array(
			"value" => htmlspecialchars(Input::get('storeurl'))
		));
		
		// Store currency
		$queries->update("settings", 20, array(
			'value' => Input::get('currency')
		));
		
		Session::flash('donate_post_success', '<div class="alert alert-success">Success</div>');
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

    <title><?php echo $sitename; ?> &bull; AdminCP Donate</title>
	
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
			  <h2 style="display: inline;" >Donate - Settings</h2>
			  <span class="pull-right"><a href="/admin/donate_sync" class="btn btn-primary">Synchronise with web store</a></span>
			  <br /><br />
			  <em>Disable the "Donate" page from the Pages tab</em><hr>
			<?php
			if(Session::exists('donate_post_success')){
				echo Session::flash('donate_post_success');
			}

			// Query the database
			$query = $queries->getWhere("settings", array("id", "<>", 0));

			?>
			  <form action="" method="post">
				<strong>Store Type</strong><br />
				<div class="btn-group" data-toggle="buttons">
				  <label class="btn btn-primary<?php if($query[35]->value == "bc"){ ?> active<?php } ?>">
					<input type="radio" name="store_type" id="InputStoreType1" value="bc" autocomplete="off"<?php if($query[35]->value == "bc"){ ?> checked<?php } ?>> Buycraft
				  </label>
				  <label class="btn btn-primary<?php if($query[35]->value == "mm"){ ?> active<?php } ?>">
					<input type="radio" name="store_type" id="InputStoreType2" value="mm" autocomplete="off"<?php if($query[35]->value == "mm"){ ?> checked<?php } ?>> Minecraft Market
				  </label>
				</div>
				<br /><br />
				<div class="form-group">
					<label for="InputAPIKey">API Key</label>
					<input type="text" name="apikey" class="form-control" id="InputAPIKey" placeholder="API Key" value="<?php echo $query[5]->value; ?>">
				</div>
				<div class="form-group">
					<label for="InputStoreUrl">Store URL (<em>With trailing /</em>)</label>
					<input type="text" name="storeurl" class="form-control" id="InputStoreUrl" placeholder="Store URL" value="<?php echo htmlspecialchars($query[34]->value); ?>">
				</div>
				<div class="form-group">
				  <label for="InputCurrency">Donation Currency</label>
				  <select class="form-control" id="InputCurrency" name="currency">
					<option value="0" <?php if($query[19]->value == 0){ echo ' selected="selected"'; } ?>>$</option>
					<option value="1" <?php if($query[19]->value == 1){ echo ' selected="selected"'; } ?>>£</option>
					<option value="2" <?php if($query[19]->value == 2){ echo ' selected="selected"'; } ?>>€</option>
				  </select>
				</div>
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