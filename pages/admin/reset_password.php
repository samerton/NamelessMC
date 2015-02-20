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

if(!isset($_GET["uid"])){
	Redirect::to('/admin/users');
	die();
}

$siteemail = $queries->getWhere("settings", array("name", "=", "outgoing_email"));
$siteemail = $siteemail[0]->value;
$individual = $queries->getWhere("users", array("id", "=", $_GET["uid"]));

if(count($individual)){
	$code = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 60);
	
	$to      = $individual[0]->email;
	$subject = 'Password Reset';
	$message = 'Hello, ' . htmlspecialchars($individual[0]->username) . '

				You are receiving this email because an administrator has reset your password.

				In order to reset your password, please use the following link:
				http://' . $_SERVER['SERVER_NAME'] . '/change_password/?c=' . $code . '
				
				If you did not request the password reset, please contact us at ' . htmlspecialchars($siteemail) . '
				Please note that your account will not be accessible until this action is complete.
				
				Thanks,
				' . htmlspecialchars($sitename) . ' staff.';
	$headers = 'From: ' . $siteemail . "\r\n" .
		'Reply-To: ' . $siteemail . "\r\n" .
		'X-Mailer: PHP/' . phpversion();

	mail($to, $subject, $message, $headers);
	
	$queries->update('users', $individual[0]->id, array(
		'reset_code' => $code,
		'active' => 0
	));
	
	Session::flash('adm-users', '<div class="alert alert-info">  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>Task run successfully.</div>');
	Redirect::to('/admin/users/user=' . $individual[0]->id);
	die();
}
Redirect::to('/admin/users');
die();
?>