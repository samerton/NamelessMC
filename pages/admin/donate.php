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
			<?php
				if(Session::exists('adm-donate')){
					echo Session::flash('adm-donate');
				}
			?>
			<?php 
			if(!isset($_GET["action"]) && !isset($_GET["pid"])){
				if(Input::exists()) {
					if(Token::check(Input::get('token'))) {
						try {
							$queries->update("settings", 20, array(
								'value' => Input::get('currency')
							));
							echo '<script>window.location.replace("/admin/donate");</script>';
							die();
						} catch(Exception $e){
							die($e->getMessage());
						}					
					}
				}
			?>
			<a href="/admin/donate/?action=new" class="btn btn-default">New Package</a>
			<br /><br />
			<?php 
			$packages = $queries->orderAll("donation_packages", "package_order", "ASC");
			?>

			<div class="panel panel-default">
				<div class="panel-heading">
					Buycraft Packages
				</div>
				<div class="panel-body">
					<?php 
					$number = count($packages);
					$i = 1;
					foreach($packages as $package){
					?>
					<div class="row">
						<div class="col-md-10">
							<?php echo '<a href="/admin/donate/?pid=' . $package->id . '">' . htmlspecialchars($package->name) . '</a>'; ?>
						</div>
						<div class="col-md-2">
							<span class="pull-right">
								<?php if($i !== 1){ ?><a href="/admin/donate?action=order&dir=up&pid=<?php echo $package->id;?>" class="btn btn-success btn-sm"><span class="glyphicon glyphicon-arrow-up"></span></a><?php } ?>
								<?php if($i !== $number){ ?><a href="/admin/donate?action=order&dir=down&pid=<?php echo $package->id;?>" class="btn btn-danger btn-sm"><span class="glyphicon glyphicon-arrow-down"></span></a><?php } ?>
								<a href="/admin/donate/?action=delete&pid=<?php echo $package->id;?>" class="btn btn-warning btn-sm" onclick="return confirm('Are you sure you want to delete this package?');"><span class="glyphicon glyphicon-trash"></span></a>
							</span>
						</div>
					</div>
					<hr> 
					<?php 
					$i++;
					}
					?>

				</div>
			</div>
			<?php 
			$currency = $queries->getWhere("settings", array("name", "=", "donation_currency"));
			$currency = $currency[0]->value;
			?>
			<form action="" method="post">
				<div class="form-group">
				  <label for="InputCurrency">Donation Currency</label>
				  <select class="form-control" id="InputCurrency" name="currency">
					<option value="0" <?php if($currency == 0){ echo ' selected="selected"'; } ?>>$</option>
					<option value="1" <?php if($currency == 1){ echo ' selected="selected"'; } ?>>£</option>
					<option value="2" <?php if($currency == 2){ echo ' selected="selected"'; } ?>>€</option>
				  </select>
				</div>
				<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
				<input type="submit" class="btn btn-primary" value="Update" />
			</form>
			
			<?php 
			} else if(isset($_GET["action"])){
				if($_GET["action"] === "new"){
					if(Input::exists()) {
						if(Token::check(Input::get('token'))) {
							$validate = new Validate();
							$validation = $validate->check($_POST, array(
								'package_name' => array(
									'required' => true,
									'min' => 2,
									'max' => 64
								),
								'package_info' => array(
									'required' => true,
									'min' => 2,
									'max' => 2048
								),
								'package_cost' => array(
									'required' => true,
									'max' => 8
								),
								'buycraft_id' => array(
									'required' => true,
									'max' => 8
								)
							));
							
							if($validation->passed()){
								try {
									$last_package_order = $queries->orderAll('donation_packages', 'package_order', 'DESC');
									$last_package_order = $last_package_order[0]->package_order;
									$queries->create("donation_packages", array(
										'name' => htmlspecialchars(Input::get('package_name')),
										'description' => htmlspecialchars(Input::get('package_info')),
										'cost' => htmlspecialchars(Input::get('package_cost')),
										'package_id' => Input::get('buycraft_id'),
										'active' => Input::get('active'),
										'package_order' => $last_package_order + 1
									));
									echo '<script>window.location.replace("/admin/donate");</script>';
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
						<h2>Create Package</h2>
						<div class="form-group">
							<input class="form-control" type="text" name="package_name" value="<?php echo htmlspecialchars(Input::get('package_name')); ?>" placeholder="Package Name" autocomplete="off">
						</div>
						<div class="form-group">
							<textarea id="description" name="package_info" class="form-control" rows="3">Package Description</textarea>
						</div>
						<div class="form-group">
							<input class="form-control" type="text" name="buycraft_id" value="<?php echo htmlspecialchars(Input::get('buycraft_id')); ?>" placeholder="Buycraft package ID" autocomplete="off">
						</div>
						<div class="form-group">
							<input class="form-control" type="text" name="package_cost" value="<?php echo htmlspecialchars(Input::get('package_cost')); ?>" placeholder="Package cost (without currency symbol)" autocomplete="off">
						</div>
						<input type="hidden" name="active" value="0">
						<label for="is_active">Display package on Donate page?</label>
						<input type="checkbox" name="active" id="is_active" value="1">
						<br />
						<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
						<input class="btn btn-success" type="submit" value="Create">	
					</form>
					<?php 
				} else if($_GET["action"] === "order"){
					if(!isset($_GET["dir"]) || !isset($_GET["pid"]) || !is_numeric($_GET["pid"])){
						echo 'Invalid action - <a href="/admin/donate">Back</a>';
						die();
					}
					if($_GET["dir"] === "up" || $_GET["dir"] === "down"){
						$dir = $_GET["dir"];
					} else {
						echo 'Invalid action - <a href="/admin/donate">Back</a>';
						die();
					}
					
					$package_id = $_GET["pid"];
					$package_order = $queries->getWhere('donation_packages', array("id", "=", $package_id));
					$package_order = $package_order[0]->package_order;
					$previous_packages = $queries->orderAll("donation_packages", "package_order", "ASC");
					
					if($dir == "up"){
						$n = 0;
						foreach($previous_packages as $previous_package){
							if($previous_package->id == $package_id){
								$previous_pid = $previous_packages[$n - 1]->id;
								$previous_p_order = $previous_packages[$n - 1]->package_order;
								break;
							}
							$n++;
						}

						try {
							$queries->update("donation_packages", $package_id, array(
								'package_order' => $previous_p_order
							));	
							$queries->update("donation_packages", $previous_pid, array(
								'package_order' => $previous_p_order + 1
							));	
						} catch(Exception $e){
							die($e->getMessage());
						}
						echo '<script>window.location.replace("/admin/donate");</script>';
						die();

					} else if($dir == "down"){
						$n = 0;
						foreach($previous_packages as $previous_package){
							if($previous_package->id == $package_id){
								$previous_pid = $previous_packages[$n + 1]->id;
								$previous_p_order = $previous_packages[$n + 1]->package_order;
								break;
							}
							$n++;
						}
						try {
							$queries->update("donation_packages", $package_id, array(
								'package_order' => $previous_p_order
							));	
							$queries->update("donation_packages", $previous_pid, array(
								'package_order' => $previous_p_order - 1
							));	
						} catch(Exception $e){
							die($e->getMessage());
						}
						echo '<script>window.location.replace("/admin/donate");</script>';
						die();
						
					}
					
				} else if($_GET["action"] === "delete"){
					if(!isset($_GET["pid"]) || !is_numeric($_GET["pid"])){
						echo 'Invalid package id - <a href="/admin/donate">Back</a>';
						die();
					}
					try {
						$queries->delete('donation_packages', array('id', '=' , $_GET["pid"]));
						echo '<script>window.location.replace("/admin/donate");</script>';
						die();
					} catch(Exception $e) {
						die($e->getMessage());
					}
				}
			} else if(isset($_GET["pid"])){
				if(Input::exists()) {
					if(Token::check(Input::get('token'))) {
						if(Input::get('action') === "update"){
							$validate = new Validate();
							$validation = $validate->check($_POST, array(
								'name' => array(
									'required' => true,
									'min' => 2,
									'max' => 64
								),
								'description' => array(
									'required' => true,
									'min' => 2,
									'max' => 2048
								),
								'package_cost' => array(
									'required' => true,
									'max' => 8
								),
								'buycraft_id' => array(
									'required' => true,
									'max' => 8
								)
							));
							
							if($validation->passed()){
								try {
									$queries->update('donation_packages', $_GET["pid"], array(
										'name' => htmlspecialchars(Input::get('name')),
										'description' => htmlspecialchars(Input::get('description')),
										'active' => Input::get('active'),
										'cost' => Input::get('package_cost'),
										'package_id' => Input::get('buycraft_id')
									));
									echo '<script>window.location.replace("/admin/donate/?pid=' . $_GET["pid"] . '");</script>';
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
						echo 'Invalid token - <a href="/admin/donate">Back</a>';
						die();
					}
				}
				if(!is_numeric($_GET["pid"])){
					die();
				} else {
					$package = $queries->getWhere("donation_packages", array("id", "=", $_GET["pid"]));
				}
				if(count($package)){
					$package = $package[0];
					echo '<h2>' . htmlspecialchars($package->name) . '</h2>';
					
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
					<form role="form" action="" method="post">
					  <div class="form-group">
						<label for="InputName">Package Name</label>
						<input type="text" name="name" class="form-control" id="InputName" placeholder="Name" value="<?php echo htmlspecialchars($package->name); ?>">
					  </div>
					  <div class="form-group">
					    <label for="description">Package Description</label>
						<textarea name="description" id="description" placeholder="Description" class="form-control" rows="3"><?php echo $purifier->purify(htmlspecialchars_decode($package->description)); ?></textarea>
				      </div>
					  <input type="hidden" name="active" value="0">	
					  <div class="form-group">
						<label for="InputActive">Display package on Donate page?</label>
						<input type="checkbox" name="active" id="InputActive" value="1"<?php if($package->active == '1'){ echo ' checked'; }?>>
					  </div>
					  <div class="form-group">
						<label for="InputBuycraft">Buycraft Package ID</label>
						<input type="text" name="buycraft_id" class="form-control" id="InputBuycraft" placeholder="Buycraft Package ID" value="<?php echo htmlspecialchars($package->package_id); ?>">
					  </div>
					  <div class="form-group">
						<label for="InputCost">Package Cost</label>
						<input type="text" name="package_cost" class="form-control" id="InputCost" placeholder="Cost" value="<?php echo htmlspecialchars($package->cost); ?>">
					  </div>

					  <input type="hidden" name="token" value="<?php echo $token; ?>">
					  <input type="hidden" name="action" value="update">
					  <input type="submit" value="Submit Changes" class="btn btn-default">
					</form>
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
	<script src="/assets/js/ckeditor.js"></script>
	<script type="text/javascript">
		CKEDITOR.replace( 'description', {
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
			removeButtons: 'Anchor,Styles,Specialchar,Font,About,Flash,Iframe'
		} );
	</script>
  </body>
</html>