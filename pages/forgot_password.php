<?php 
/*
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

if($user->isLoggedIn()) { // User must be logged out to view this page
	Redirect::to("/");
	die();
} else {

$siteemail = $queries->getWhere("settings", array("name", "=", "outgoing_email"));
$siteemail = $siteemail[0]->value;

if(Input::exists()) {
	if(Token::check(Input::get('token'))) {
		$check = $queries->getWhere('users', array('username', '=', Input::get('username')));
		if(count($check)){
			$code = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 60);
			
			$to      = $check[0]->email;
			$subject = 'Password Reset';
			$message = 'Hello, ' . htmlspecialchars($check[0]->username) . '

						You are receiving this email because you requested a password reset.

						In order to reset your password, please use the following link:
						http://' . $_SERVER['SERVER_NAME'] . '/change_password/?c=' . $code . '
						
						If you did not request the password reset, please ignore this email.
						
						Thanks,
						' . $sitename . ' staff.';
			$headers = 'From: ' . $siteemail . "\r\n" .
				'Reply-To: ' . $siteemail . "\r\n" .
				'X-Mailer: PHP/' . phpversion();

			mail($to, $subject, $message, $headers);
			
			$queries->update('users', $check[0]->id, array(
				'reset_code' => $code
			));
			
			Session::flash('info', '<div class="alert alert-info">Success. Please check your emails for further instructions.</div>');
			Redirect::to("/");	
		} else {
			Session::flash('error', '<div class="alert alert-info">That username does not exist.</div>');
		}
		
	
	} else {
		Session::flash('error', '<div class="alert alert-info">Error processing your request.</div>');
	}
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Forgot Password">
    <meta name="author" content="Samerton">
    <link rel="icon" href="/favicon.ico">
    <title><?php echo $sitename; ?> &bull; Forgot Password</title> 
	
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
		<form action="" method="post">
		<h2>Forgot Password</h2>
		<?php
		if(Session::exists('error')){
			echo Session::flash('error');
		}
		?>
			<input class="form-control" type="text" name="username" id="username" placeholder="Username" autocomplete="off">				
			<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
			<br />
			<center><input class="btn btn-success" type="submit" value="Submit"></center>
		</form>
		<hr>
	  <?php require("inc/templates/footer.php"); ?> 
	  
    </div> <!-- /container -->
		
	<?php require("inc/templates/scripts.php"); ?>
	
   </body>
<?php } ?>
