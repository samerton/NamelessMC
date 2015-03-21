<?php 
/*
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */
 
require('inc/includes/password.php');

if(!isset($_GET['c'])){
	Redirect::to('/');
	die();
} else {
	$check = $queries->getWhere('users', array('reset_code', '=', $_GET['c']));
	if(count($check)){
		if(Input::exists()) {
			if(Token::check(Input::get('token'))) {
				
				$validate = new Validate();
				$validation = $validate->check($_POST, array(
					'password_new' => array(
						'required' => true,
						'min' => 6,
						'max' => 30
					),
					'password_new_again' => array(
						'required' => true,
						'min' => 6,
						'matches' => 'password_new'
					)
				));
				
				if($validation->passed()) {
					$password = password_hash(Input::get('password_new'), PASSWORD_BCRYPT, array("cost" => 13));
					$queries->update('users', $check[0]->id, array(
						'password' => $password,
						'reset_code' => '',
						'active' => 1
					));
					
					Session::flash('info', '<div class="alert alert-info">Your password has been changed.</div>');
					Redirect::to('/');
					die();
					
				} else {
					echo '<div class="alert alert-danger">';
					foreach($validation->errors() as $error) {
						echo $error, '<br />';
					}
					echo '</div>';
				}
				
			}
		}
	} else {
		Session::flash('error', '<div class="alert alert-danger">Error processing your request.</div>');
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
    <meta name="description" content="Change Password">
    <meta name="author" content="Samerton">
    <link rel="icon" href="/favicon.ico">
    <title><?php echo $sitename; ?> &bull; Change Password</title> 
	
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
		<div class="row">
			<div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3">
				<form action="/change_password/?c=<?php echo $_GET['c']; ?>" method="post">
				<h2>Change Password</h2>
				<?php
				if(Session::exists('error')){
					echo Session::flash('error');
				}
				?>
					<input class="form-control" type="password" name="password_new" id="password_new" placeholder="New password" autocomplete="off">
					<br />
					<input class="form-control" type="password" name="password_new_again" id="password_new_again" placeholder="New password again" autocomplete="off">
					<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
					<br />
					<center><input class="btn btn-success" type="submit" value="Submit"></center>

				</form>
			</div>
		</div>
		<hr>
	  <?php require("inc/templates/footer.php"); ?> 
	  
    </div> <!-- /container -->
		
	<?php require("inc/templates/scripts.php"); ?>
   </body>
</html>
<?php 
}
?>