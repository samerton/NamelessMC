<?php 
/* 
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

require('inc/includes/html/library/HTMLPurifier.auto.php');

/*
 *  Check if page is enabled
 */ 
$donate_enabled = $queries->getWhere("settings", array("name", "=", "donate"));

if($donate_enabled[0]->value === "false"){
	Redirect::to("/");
	die();
}

$donate_enabled = null;

/*
 *  Log the user in
 */

if(!$user->isLoggedIn()){
	if(Input::exists()) {
		if(Token::check(Input::get('token'))) {
			$validate = new Validate();
			$validation = $validate->check($_POST, array(
				'username' => array('required' => true, 'isbanned' => true, 'isactive' => true),
				'password' => array('required' => true)
			));
			
			if($validation->passed()) {
				$user = new User();
				
				$remember = (Input::get('remember') === 'on') ? true : false;
				$login = $user->login(Input::get('username'), Input::get('password'), $remember);
				
				if($login) {
					Session::flash('donate', '<div class="alert alert-info">  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>You have been successfully logged in</div>');
					Redirect::to("/donate");
					die();
				} else {
					echo '<p>Sorry, there was an unknown error whilst logging you in. <a href="/">Homepage</a></p>';
					die();
				}
			}
		}
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
    <link rel="icon" href="/favicon.ico">

    <title><?php echo $sitename; ?> &bull; Donate</title>
	
	<!-- Custom style -->
	<style>
	html {
		overflow-y: scroll;
	}
	</style>
	
	<?php require("inc/templates/header.php"); ?>

  </head>

  <body>

	<?php require("inc/templates/navbar.php"); ?>
	
	<div class="container">
	<?php 
	    if(Session::exists('donate')){
		  echo Session::flash('donate');
	    }
		if(!$user->isLoggedIn()){
	?>
		<div class="row">
			<div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3">
			<?php
			/*
			 *  Display validation errors
			 */
			if(Input::exists()) {
				if(!$validation->passed()) {
					echo '<div class="alert alert-danger">';
					foreach($validation->errors() as $error) {
						if (strpos($error,'is required') !== false) {
							if (strpos($error,'username') !== false) {
								echo 'You must input a username.<br />';
							} else if (strpos($error,'password') !== false) {
								echo 'You must input a password.<br />';
							}
						}
						if (strpos($error,'active') !== false){
							echo 'Your account is currently inactive. Did you request a password reset?';
						}
					}
					echo '</div>';
				}
			}
			?>
				<form role="form" action="" method="post">
					<h2>Sign In</h2>
					<hr class="colorgraph">
					<div class="form-group">
						<input type="text" name="username" id="username" autocomplete="off" value="<?php echo escape(Input::get('username')); ?>" class="form-control input-lg" placeholder="Username" tabindex="3">
					</div>
					<div class="form-group">
						<input type="password" name="password" id="password" class="form-control input-lg" placeholder="Password" tabindex="4">
					</div>
					<div class="form-group">
						<label for="remember">
							<input type="checkbox" name="remember" id="remember"> Remember me
						</label>				
					</div>
					<hr class="colorgraph">
					<div class="row">
						<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
						<div class="col-xs-12 col-md-6"><input type="submit" value="Sign In" class="btn btn-primary btn-block btn-lg" tabindex="4"></div>
						<div class="col-xs-12 col-md-6"><a href="/register" class="btn btn-success btn-block btn-lg">Register</a></div>
					</div>
				</form>
			</div>
		</div>
	<?php 
	} else {
	?>
	<div class="row">
		<div class="col-xs-12 col-md-3">
			<div class="well well-sm">
				<h3><strong>Latest Donors</strong></h3>
				<?php 
					$latest = $queries->orderAll("buycraft_data", "time", "DESC");
					
					/*
					 *  TODO: Get currency from database
					 */ 
					
					if(count($latest) < 5){
						$limit = count($latest);
					} else {
						$limit = 5;
					}
					for ($x=1; $x<=$limit; $x++){
						echo '<p><a href="/profile/' . htmlspecialchars($latest[$x-1]->ign) . '"><img class="img-rounded" src="https://cravatar.eu/avatar/' . htmlspecialchars($latest[$x-1]->ign) . '/30.png" /></a> ' . htmlspecialchars($latest[$x-1]->ign) . ' - £' . $latest[$x-1]->price . '</p>';
					}
				?>
			</div>
		</div>
		<div class="col-xs-12 col-md-9">
			<?php
			$packages = $queries->orderAll("donation_packages", "package_order", "ASC");
			
			$config = HTMLPurifier_Config::createDefault();
			$config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
			$config->set('URI.DisableExternalResources', false);
			$config->set('URI.DisableResources', false);
			$config->set('HTML.Allowed', 'u,p,b,i,small,blockquote,span[style],span[class],p,strong,em,li,ul,ol,div[align],br,img');
			$config->set('CSS.AllowedProperties', array('float', 'color','background-color', 'background', 'font-size', 'font-family', 'text-decoration', 'font-weight', 'font-style', 'font-size'));
			$config->set('HTML.AllowedAttributes', 'src, height, width, alt, class, *.style');
			$purifier = new HTMLPurifier($config);
			
			$n = 0;
			$finish = count($packages) - 1;
			foreach($packages as $package){
				if($n % 3 != 0){
					// Middle or end column
				} else {
					if($n !== 0){
			?>
			</div>
			<div class="row">
			<?php 
					} else {
			?>
			<div class="row">
			<?php
					}
				}
			?>
		      <div class="col-md-4">
				<div class="panel panel-primary">
				  <div class="panel-heading">
					<?php 
					// Get currency
					$currency = $queries->getWhere("settings", array("name", "=", "donation_currency"));
					$currency = $currency[0]->value;
					?>
					<?php echo htmlspecialchars($package->name); ?><span class="pull-right"><?php echo $queries->convertCurrency($currency); echo htmlspecialchars($package->cost); ?></span>
				  </div>
				  <div class="panel-body">
					<?php echo $purifier->purify(htmlspecialchars_decode($package->description)); ?>
				  </div>
				</div>
			  </div>
			<?php 
				if($n == $finish){
			?>
			</div>
			<?php
				}
				$n++;
			}
			
			?>
		</div>
	</div>
	<?php 
	}
	?>
		<hr>
	  <?php require("inc/templates/footer.php"); ?> 
	  
    </div> <!-- /container -->
		
	<?php require("inc/templates/scripts.php"); ?>	
  </body>
</html>