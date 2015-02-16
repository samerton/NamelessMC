<?php
/* 
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

require('inc/includes/password.php'); // Include the password compatibility functions

if(!isset($user)){
	$user = new User();
}

if($user->isLoggedIn()){
	Redirect::to('../');
}

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
				Session::flash('home', '<div class="alert alert-info">  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>You have been successfully logged in</div>');
				Redirect::to("../");
				die();
			} else {
				Session::flash('signin_error', '<div class="alert alert-danger">  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>Incorrect details</div>');
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
    <link rel="icon" href="/assets/favicon.ico">

    <title><?php echo $sitename; ?> &bull; Sign In</title>
	
	<?php require('inc/templates/header.php'); ?>
	
	<!-- Custom style -->
	<style>
	html {
		overflow-y: scroll;
	}
	</style>
	
  </head>
  <body>
	<?php require('inc/templates/navbar.php'); ?>
	<div class="container">
		<div class="row">
			<div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3">
			<?php
			if(Session::exists('signin_error')){
				echo Session::flash('signin_error');
			}
			if(Input::exists()) {
				if($validation->passed()) {	} 
				else {
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
							echo 'Your account is currently inactive. Did you request a password reset?<br />';
						}
						if (strpos($error,'banned') !== false){
							echo 'Your account has been banned.<br />';
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
						<input type="text" name="username" id="username" autocomplete="off" value="<?php echo htmlspecialchars(Input::get('username')); ?>" class="form-control input-lg" placeholder="Username" tabindex="3">
					</div>
					<div class="form-group">
						<input type="password" name="password" id="password" class="form-control input-lg" placeholder="Password" tabindex="4">
					</div>
					<div class="row">
						<div class="col-xs-12 col-md-6">
							<div class="form-group">
								<label for="remember">
									<input type="checkbox" name="remember" id="remember"> Remember me
								</label>				
							</div>
						</div>
						<div class="col-xs-12 col-md-6">
							<span class="pull-right"><a class="btn btn-sm btn-primary" href="/forgot_password">Forgot password?</a></span>
						</div>
					</div>
					<hr class="colorgraph">
					<div class="row">
						<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
						<div class="col-xs-12 col-md-6"><input type="submit" value="Sign In" class="btn btn-primary btn-block btn-lg" tabindex="4"></div>
						<div class="col-xs-12 col-md-6"><a href="../register" class="btn btn-success btn-block btn-lg">Register</a></div>
					</div>
				</form>
			</div>
		</div>
		<hr>
	  <?php include('inc/templates/footer.php'); ?> 
	  
    </div> <!-- /container -->
	<?php include('inc/templates/scripts.php'); ?>
  </body>
	
</html>
