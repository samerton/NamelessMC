<?php
/* 
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

require('inc/includes/password.php'); // Include the password compatibility functions
require('inc/includes/html/library/HTMLPurifier.auto.php'); // For T & Cs
require('inc/integration/uuid.php'); // For UUID stuff

// Redirect if logged in
if($user->isLoggedIn()){
	Redirect::to("../");
	die();
}

// Use recaptcha?
$recaptcha = $queries->getWhere("settings", array("name", "=", "recaptcha"));
$recaptcha = $recaptcha[0]->value;

// Validate user input, and register the account
if(Input::exists()) {
	if(Token::check(Input::get('token'))) {

		$validate = new Validate();
		$to_validation = array(
			'username' => array(
				'required' => true,
				'min' => 4,
				'max' => 20,
				'unique' => 'users'
			),
			'mcname' => array(
				'required' => true,
				'isvalid' => true,
				'min' => 4,
				'max' => 20
			),
			'password' => array(
				'required' => true,
				'min' => 6,
				'max' => 30
			),
			'password_again' => array(
				'required' => true,
				'matches' => 'password'
			),
			'email' => array(
				'required' => true,
				'min' => 4,
				'max' => 50,
				'unique' => 'users'
			),
			't_and_c' => array(
				'required' => true,
				'agree' => true
			)
		);
		
		if($recaptcha === "true"){
			$to_validation['g-recaptcha-response'] = array(
				'required' => true
			);
		}

		$validation = $validate->check($_POST, $to_validation);
		
		if($validation->passed()){
		
			$profile = ProfileUtils::getProfile(htmlspecialchars(Input::get('mcname')));
			$result = $profile->getProfileAsArray();
			$uuid = $result["uuid"];
		
			$user = new User();
			
			$ip = $user->getIP();
			if(filter_var($ip, FILTER_VALIDATE_IP)){
				// Valid IP
			} else {
				// TODO: Invalid IP, do something else
			}
			
			$password = password_hash(Input::get('password'), PASSWORD_BCRYPT, array("cost" => 13));
			// Get current unix time
			$date = new DateTime();
			$date = $date->getTimestamp();
			
			try {
				$code = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 60);
				$user->create(array(
					'username' => htmlspecialchars(Input::get('username')),
					'mcname' => htmlspecialchars(Input::get('mcname')),
					'uuid' => $uuid,
					'password' => $password,
					'pass_method' => 'default',
					'joined' => $date,
					'group_id' => 1,
					'email' => htmlspecialchars(Input::get('email')),
					'reset_code' => $code,
					'lastip' => htmlspecialchars($ip)
				));

				$siteemail = $queries->getWhere("settings", array("name", "=", "outgoing_email"));
				$siteemail = $siteemail[0]->value;
				
				$to      = Input::get('email');
				$subject = 'Welcome to ' . $sitename . '!';
				$message = 'Hello, ' . htmlspecialchars(Input::get('username')) . '

							Thanks for registering!

							In order to complete your registration, please click the following link:
							http://' . $_SERVER['SERVER_NAME'] . '/validate/?c=' . $code . '

							Please note that your account will not be accessible until this action is complete.
							
							Thanks,
							' . $sitename . ' staff.';
				$headers = 'From: ' . $siteemail . "\r\n" .
					'Reply-To: ' . $siteemail . "\r\n" .
					'X-Mailer: PHP/' . phpversion();

				mail($to, $subject, $message, $headers);
				
				Session::flash('home', '<div class="alert alert-info alert-dismissible">  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>Please check your emails for a validation link. You won\'t be able to log in until this is clicked.</div>');
				Redirect::to('../');
				die();
			
			} catch(Exception $e){
				die($e->getMessage());
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

    <title><?php echo $sitename; ?> &bull; Register</title>
	
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
			if(Input::exists()) {
				if($validation->passed()) {	} else {
					echo '<div class="alert alert-danger">';
					foreach($validation->errors() as $error) {
						if (strpos($error,'is required') !== false) {
							if (strpos($error,'username') !== false) {
								echo 'You must input a username.<br />';
							} else if (strpos($error,'email') !== false) {
								echo 'You must input an email address.<br />';
							} else if (strpos($error,'password') !== false) {
								echo 'You must input a password.<br />';
							} else if (strpos($error,'mcname') !== false) {
								echo 'You must input a valid Minecraft username.<br />';
							} else if (strpos($error,'mcquestion') !== false) {
								echo 'You must answer the question.<br />';
							} else if (strpos($error,'t_and_c') !== false) {
								echo 'You must agree to our terms and conditions in order to register.<br />';
							} else if (strpos($error,'g-recaptcha-response') !== false) {
								echo 'You haven\'t answered the captcha.';
							}
						}
						if (strpos($error,'already exists!') !== false) {
							echo 'That username/email has already been used.<br />';
						}
						if (strpos($error,'must be a minimum of 6 characters') !== false) {
							echo 'Your password must be a minimum of 6 characters.<br />';
						}
						if (strpos($error,'must be a minimum of 4 characters') !== false) {
							echo 'Your username must be a minimum of 4 characters.<br />';
						}
						if (strpos($error,'must be a maximum of 30 characters') !== false) {
							echo 'Your password must be a maximum of 30 characters.<br />';
						}
						if (strpos($error,'not a valid Minecraft account.') !== false) {
							echo 'Your Minecraft username is not a valid Minecraft account.<br />';
						}
						if (strpos($error,'password must match password_again.') !== false) {
							echo 'Your passwords do not match.<br />';
						}
						if (strpos($error,'The question was not answered correctly.') !== false) {
							echo 'The question was not answered correctly.<br />';
						}
					}
					echo '</div>';
				}
			}
			?>
				<form role="form" action="" method="post">
					<h2>Create an account</h2>
					<hr class="colorgraph">
					<div class="form-group">
						<input type="text" name="username" id="username" autocomplete="off" value="<?php echo escape(Input::get('username')); ?>" class="form-control input-lg" placeholder="Username" tabindex="1">
					</div>
					<div class="form-group">
						<input type="text" name="mcname" id="mcname" autocomplete="off" class="form-control input-lg" placeholder="Minecraft Username" tabindex="2">
					</div>
					<div class="form-group">
						<input type="email" name="email" id="email" value="<?php echo escape(Input::get('email')); ?>" class="form-control input-lg" placeholder="Email Address" tabindex="3">
					</div>
					<div class="row">
						<div class="col-xs-12 col-sm-6 col-md-6">
							<div class="form-group">
								<input type="password" name="password" id="password" class="form-control input-lg" placeholder="Password" tabindex="4">
							</div>
						</div>
						<div class="col-xs-12 col-sm-6 col-md-6">
							<div class="form-group">
								<input type="password" name="password_again" id="password_again" class="form-control input-lg" placeholder="Confirm Password" tabindex="5">
							</div>
						</div>
					</div>
					<?php 
					if($recaptcha === "true"){
						$recaptcha_key = $queries->getWhere("settings", array("name", "=", "recaptcha_key"));
					?>
					<center>
						<div class="g-recaptcha" data-sitekey="<?php echo $recaptcha_key[0]->value; ?>"></div>
					</center>
					<br />
					<?php 
					}
					?>
					<div class="row">
						<div class="col-xs-4 col-sm-3 col-md-3">
							<span class="button-checkbox">
								<button type="button" class="btn" data-color="info" tabindex="7"> I Agree</button>
								<input type="checkbox" name="t_and_c" id="t_and_c" class="hidden" value="1">
							</span>
						</div>
						<div class="col-xs-8 col-sm-9 col-md-9">
							 By clicking <strong class="label label-primary">Register</strong>, you agree to our <a href="#" data-toggle="modal" data-target="#t_and_c_m">Terms and Conditions</a>.
						</div>
					</div>
					
					<hr class="colorgraph">
					<div class="row">
						<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
						<div class="col-xs-12 col-md-6"><input type="submit" value="Register" class="btn btn-primary btn-block btn-lg" tabindex="8"></div>
						<div class="col-xs-12 col-md-6"><a href="../signin" class="btn btn-success btn-block btn-lg">Sign In</a></div>
					</div>
				</form>
			</div>
		</div>
		<!-- Modal -->
		<div class="modal fade" id="t_and_c_m" tabindex="-1" role="dialog" aria-labelledby="t_and_c_m_Label" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h4 class="modal-title" id="t_and_c_m_Label">Terms & Conditions</h4>
					</div>
					<div class="modal-body">
						<?php 
						$config = HTMLPurifier_Config::createDefault();
						$config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
						$config->set('URI.DisableExternalResources', false);
						$config->set('URI.DisableResources', false);
						$config->set('HTML.Allowed', 'u,p,b,i,small,blockquote,span[style],p,strong,em,li,ul,ol,div[align],br');
						$config->set('CSS.AllowedProperties', array('float', 'color','background-color', 'background', 'font-size', 'font-family', 'text-decoration', 'font-weight', 'font-style', 'font-size'));
						$config->set('HTML.AllowedAttributes', 'src, height, width, alt');
						$purifier = new HTMLPurifier($config);
						$t_and_c = $queries->getWhere("settings", array("name", "=", "t_and_c"));
						echo $purifier->purify(htmlspecialchars_decode($t_and_c[0]->value));
						?>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
					</div>
				</div><!-- /.modal-content -->
			</div><!-- /.modal-dialog -->
		</div><!-- /.modal -->

		<hr>

	  <?php include('inc/templates/footer.php'); ?> 
	  
    </div> <!-- /container -->
	<?php 
	include('inc/templates/scripts.php'); 
	if($recaptcha === "true"){
	?>
	<script src='https://www.google.com/recaptcha/api.js'></script>
	<?php 
	}
	?>
	<script>
	$(function () {
		$('.button-checkbox').each(function () {

			// Settings
			var $widget = $(this),
				$button = $widget.find('button'),
				$checkbox = $widget.find('input:checkbox'),
				color = $button.data('color'),
				settings = {
					on: {
						icon: 'glyphicon glyphicon-check'
					},
					off: {
						icon: 'glyphicon glyphicon-unchecked'
					}
				};

			// Event Handlers
			$button.on('click', function () {
				$checkbox.prop('checked', !$checkbox.is(':checked'));
				$checkbox.triggerHandler('change');
				updateDisplay();
			});
			$checkbox.on('change', function () {
				updateDisplay();
			});

			// Actions
			function updateDisplay() {
				var isChecked = $checkbox.is(':checked');

				// Set the button's state
				$button.data('state', (isChecked) ? "on" : "off");

				// Set the button's icon
				$button.find('.state-icon')
					.removeClass()
					.addClass('state-icon ' + settings[$button.data('state')].icon);

				// Update the button's color
				if (isChecked) {
					$button
						.removeClass('btn-default')
						.addClass('btn-' + color + ' active');
				}
				else {
					$button
						.removeClass('btn-' + color + ' active')
						.addClass('btn-default');
				}
			}

			// Initialisation
			function init() {

				updateDisplay();

				// Inject the icon if applicable
				if ($button.find('.state-icon').length == 0) {
					$button.prepend('<i class="state-icon ' + settings[$button.data('state')].icon + '"></i>');
				}
			}
			init();
		});
	});
	</script>
	
  </body>
	
</html>
