<?php 
/*
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */
 
// Set page name for sidebar
$page = "admin-general";

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

if(Input::exists()) {
	if(Token::check(Input::get('token'))) {
		$validate = new Validate();
		$validation = $validate->check($_POST, array(
			'sitename' => array(
				'min' => 2,
				'max' => 32
			),
			'buycraftapi' => array(
				'min' => 4,
				'max' => 40
			),
			'buycrafturl' => array(
				'min' => 4,
				'max' => 64
			),
			'recaptcha' => array(
				'min' => 4,
				'max' => 40
			),
			'youtubeurl' => array(
				'min' => 4,
				'max' => 64
			),
			'twitterurl' => array(
				'min' => 4,
				'max' => 64
			),
			'gplusurl' => array(
				'min' => 4,
				'max' => 64
			),
			'fburl' => array(
				'min' => 4,
				'max' => 64
			),
			'twitter_id' => array(
				'min' => 4,
				'max' => 64
			)
		));
		
		if(Input::get('enable_recaptcha') == 1){
			$enable_recaptcha = "true";
		} else {
			$enable_recaptcha = "false";
		}
		
		if(Input::get('enable_avatars') == 1){
			$avatars = "true";
		} else {
			$avatars = "false";
		}
		
		if(Input::get('enable_displaynames') == 1){
			$displaynames = "true";
		} else {
			$displaynames = "false";
		}
		
		if($validation->passed()){
			$data = array(
				0 => array(
					'name' => 'sitename',
					'number' => 1,
					'value' => htmlspecialchars(Input::get('sitename'))
				),
				2 => array(
					'name' => 'youtube_url',
					'number' => 7,
					'value' => htmlspecialchars(Input::get('youtubeurl'))
				),
				3 => array(
					'name' => 'twitter_url',
					'number' => 8,
					'value' => htmlspecialchars(Input::get('twitterurl'))
				),
				4 => array(
					'name' => 'gplus_url',
					'number' => 9,
					'value' => htmlspecialchars(Input::get('gplusurl'))
				),
				5 => array(
					'name' => 'fb_url',
					'number' => 10,
					'value' => htmlspecialchars(Input::get('fburl'))
				),
				6 => array(
					'name' => 'recaptcha',
					'number' => 14,
					'value' => $enable_recaptcha
				),
				7 => array(
					'name' => 'recaptcha_key',
					'number' => 15,
					'value' => htmlspecialchars(Input::get('recaptcha'))
				),
				8 => array(
					'name' => 'twitter_feed_id',
					'number' => 16,
					'value' => htmlspecialchars(Input::get('twitter_id'))
				),
				9 => array(
					'name' => 'bootstrap_theme',
					'number' => 18,
					'value' => htmlspecialchars(Input::get('bootstrap_style'))
				),
				10 => array(
					'name' => 'navbar_style',
					'number' => 19,
					'value' => htmlspecialchars(Input::get('navbar_theme'))
				),
				11 => array(
					'name' => 'user_avatars',
					'number' => 26,
					'value' => $avatars
				),
				12 => array(
					'name' => 'displaynames',
					'number' => 27,
					'value' => $displaynames
				)
			);
			try {
				foreach($data as $setting){
					$queries->update("settings", $setting["number"], array(
						'value' => $setting["value"]
					));
				}
				Redirect::to('/admin/general');
				die();
			} catch(Exception $e) {
				die($e->getMessage());
			}
			
		} else {
			// Errors
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
	<meta name="robots" content="noindex">
    <link rel="icon" href="/favicon.ico">

    <title><?php echo $sitename; ?> &bull; Admin General Settings</title>
	
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
				<h2>General Settings</h2>
				<?php $settings = $queries->getAll("settings", array("name", "<>", "")); ?>
				<form role="form" action="" method="post">
					<div class="form-group">
						<label for="InputSiteName">Site Name</label>
						<input type="text" name="sitename" class="form-control" id="InputSiteName" placeholder="Site Name" value="<?php echo $settings[0]->value; ?>">
					</div>
					<input type="hidden" name="enable_recaptcha" value="0" />
					<label>Recaptcha Site Key</label>
					<div class="input-group">
						<span class="input-group-addon">
							<input name="enable_recaptcha" value="1" id="InputEnableRecaptcha" type="checkbox"<?php if($settings[13]->value === "true"){ echo ' checked'; } ?>>
					    </span>
						<input type="text" name="recaptcha" class="form-control" id="InputRecaptcha" placeholder="Recaptcha Key" value="<?php echo $settings[14]->value; ?>">
					</div>
					<div class="form-group">
						<label for="InputYoutube">YouTube URL</label>
						<input type="text" name="youtubeurl" class="form-control" id="InputYoutube" placeholder="YouTube URL (with preceding http)" value="<?php echo $settings[6]->value; ?>">
					</div>
					<div class="form-group">
						<label for="InputTwitter">Twitter URL</label>
						<input type="text" name="twitterurl" class="form-control" id="InputTwitter" placeholder="Twitter URL (with preceding http)" value="<?php echo $settings[7]->value; ?>">
					</div>
					<div class="form-group">
						<label for="InputTwitterID">Twitter Widget ID</label>
						<input type="text" name="twitter_id" class="form-control" id="InputTwitterID" placeholder="Twitter Widget ID" value="<?php echo $settings[15]->value; ?>">
					</div>
					<div class="form-group">
						<label for="InputGPlus">Google Plus URL</label>
						<input type="text" name="gplusurl" class="form-control" id="InputGPlus" placeholder="Google Plus URL (with preceding http)" value="<?php echo $settings[8]->value; ?>">
					</div>
					<div class="form-group">
						<label for="InputFacebook">Facebook URL</label>
						<input type="text" name="fburl" class="form-control" id="InputFacebook" placeholder="Facebook URL (with preceding http)" value="<?php echo $settings[9]->value; ?>">
					</div>
					<div class="form-group">
						<label for="InputBootstrap">Bootstrap Style</label>
						<?php 
						$theme = $queries->getWhere("settings", array("name", "=", "bootstrap_theme"));
						$theme = $theme[0]->value;
						?>
						<select class="form-control" id="InputBootstrap" name="bootstrap_style">
						  <option value="1"<?php if($theme === "1") { ?> selected="selected"<?php } ?>>Default Bootstrap</option>
						  <option value="2"<?php if($theme === "2") { ?> selected="selected"<?php } ?>>Cerulean</option>
						  <option value="3"<?php if($theme === "3") { ?> selected="selected"<?php } ?>>Cosmo</option>
						  <option value="4"<?php if($theme === "4") { ?> selected="selected"<?php } ?>>Cyborg</option>
						  <option value="5"<?php if($theme === "5") { ?> selected="selected"<?php } ?>>Darkly</option>
						  <option value="6"<?php if($theme === "6") { ?> selected="selected"<?php } ?>>Flatly</option>
						  <option value="7"<?php if($theme === "7") { ?> selected="selected"<?php } ?>>Journal</option>
						  <option value="8"<?php if($theme === "8") { ?> selected="selected"<?php } ?>>Lumen</option>
						  <option value="9"<?php if($theme === "9") { ?> selected="selected"<?php } ?>>Paper</option>
						  <option value="10"<?php if($theme === "10") { ?> selected="selected"<?php } ?>>Readable</option>
						  <option value="11"<?php if($theme === "11") { ?> selected="selected"<?php } ?>>Sandstone</option>
						  <option value="12"<?php if($theme === "12") { ?> selected="selected"<?php } ?>>Simplex</option>
						  <option value="13"<?php if($theme === "13") { ?> selected="selected"<?php } ?>>Slate</option>
						  <option value="14"<?php if($theme === "14") { ?> selected="selected"<?php } ?>>Spacelab</option>
						  <option value="15"<?php if($theme === "15") { ?> selected="selected"<?php } ?>>Superhero</option>
						  <option value="16"<?php if($theme === "16") { ?> selected="selected"<?php } ?>>United</option>
						  <option value="17"<?php if($theme === "17") { ?> selected="selected"<?php } ?>>Yeti</option>
						</select>
					</div>
					<div class="form-group">
						<label for="InputNavbar">Navbar Theme</label>
						<?php 
						$navbar = $queries->getWhere("settings", array("name", "=", "navbar_style"));
						$navbar = $navbar[0]->value;
						?>
						<select class="form-control" id="InputNavbar" name="navbar_theme">
						  <option value="0"<?php if($navbar === "0") { ?> selected="selected"<?php } ?>>Default</option>
						  <option value="1"<?php if($navbar === "1") { ?> selected="selected"<?php } ?>>Inverse</option>
						</select>
					</div>
					<div class="form-group">
						<label for="InputEnableAvatars">Enable Custom User Avatars</label>
						<input type="hidden" name="enable_avatars" value="0" />
						<input name="enable_avatars" value="1" id="InputEnableAvatars" type="checkbox"<?php if($settings[25]->value === "true"){ echo ' checked'; } ?>>
					</div>
					<div class="form-group">
						<label for="InputEnableDisplaynames">Enable Custom User Displaynames</label>
						<input type="hidden" name="enable_displaynames" value="0" />
						<input name="enable_displaynames" value="1" id="InputEnableDisplaynames" type="checkbox"<?php if($settings[26]->value === "true"){ echo ' checked'; } ?>>
					</div>
					<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
					<input type="submit" value="Submit Changes" class="btn btn-default">
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