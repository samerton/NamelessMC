<?php 
/* 
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

if(!$user->isLoggedIn()){
	Redirect::to('/');
	die();
}

require('inc/includes/html/library/HTMLPurifier.auto.php'); // HTMLPurifier

if(Input::exists()){
	if(Token::check(Input::get('token'))) {
		$validate = new Validate();
		$validation = $validate->check($_POST, array(
			'screenname' => array(
				'required' => true,
				'min' => 2,
				'max' => 20
			),
			'signature' => array(
				'max' => 256,
				'required' => true
			)
		));
		
		if($validation->passed()){
			try {
				$queries->update('users', $user->data()->id, array(
					'username' => htmlspecialchars(Input::get('screenname')),
					'signature' => htmlspecialchars(Input::get('signature'))
				));
				Redirect::to('/user/settings');
				die();
			} catch(Exception $e) {
				die($e->getMessage());
			}
			
		} else {
		
			$error_string = "";
			foreach($validation->errors() as $error){
				$error_string .= ucfirst($error) . '<br />';
			}
		
			Session::flash('usercp_settings', '<div class="alert alert-danger">' . $error_string . '</div>');
		}
	}
}


$token = Token::generate();

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
	<meta name="robots" content="noindex">

    <title><?php echo $sitename; ?> &bull; UserCP Settings</title>
	
	<?php require("inc/templates/header.php"); ?>

  </head>

  <body>

	<?php require("inc/templates/navbar.php"); ?>

    <div class="container">	

	  <div class="row">
		<div class="col-md-3">
			<div class="well well-sm">
				<ul class="nav nav-pills nav-stacked">
				  <li><a href="/user">Overview</a></li>
				  <li><a href="/user/messaging">Private Messages<?php if($unread_pms === true){ ?> <span class="glyphicon glyphicon-exclamation-sign"></span><?php }?></a></li>
				  <li class="active"><a href="/user/settings">Profile settings</a></li>
				</ul>
			</div>
		</div>
		<div class="col-md-9">
			<h2>Account Settings</h2>
			<?php 
			if(Session::exists('settings_avatar_error')){
				echo Session::flash('settings_avatar_error');
			}
			
			if(Session::exists('usercp_settings')){
				echo Session::flash('usercp_settings');
			}
			
			// HTML Purifier
			$config = HTMLPurifier_Config::createDefault();
			$config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
			$config->set('URI.DisableExternalResources', false);
			$config->set('URI.DisableResources', false);
			$config->set('HTML.Allowed', 'u,p,b,i,small,blockquote,span[style],span[class],p,strong,em,li,ul,ol,div[align],br,img');
			$config->set('CSS.AllowedProperties', array('float', 'color','background-color', 'background', 'font-size', 'font-family', 'text-decoration', 'font-weight', 'font-style', 'font-size'));
			$config->set('HTML.AllowedAttributes', 'src, height, width, alt, class, *.style');
			$purifier = new HTMLPurifier($config);
			
			$signature = $purifier->purify(htmlspecialchars_decode($user->data()->signature));
			?>
			<form action="" method="post">
			  <div class="form-group">
				<label for="InputScreenName">Screen name</label>
				<input type="text" name="screenname" class="form-control" id="InputScreenName" value="<?php echo htmlspecialchars($user->data()->username); ?>">
			  </div>
			  <div class="form-group">
				<label for="signature">Signature</label>
				<textarea rows="10" name="signature" id="signature">
					<?php echo $signature; ?>
				</textarea>
			  </div>
			  <input type="hidden" name="token" value="<?php echo $token; ?>" />
			  <input class="btn btn-primary" type="submit" name="submit" value="Submit" />
			</form>
			<br />
			<?php
			// Is avatar uploading enabled?
			$avatar_enabled = $queries->getWhere('settings', array('name', '=', 'user_avatars'));
			$avatar_enabled = $avatar_enabled[0]->value;

			if($avatar_enabled === "true"){
			?>
			<form action="/user/avatar_upload" method="post" enctype="multipart/form-data">
			  <strong>Upload an avatar (.jpg, .png or .gif only):</strong>
			  <input type="file" name="uploaded_avatar" />
			  <input type="hidden" name="token" value="<?php echo $token; ?>" /><br />
			  <input class="btn btn-primary" type="submit" name="submit" value="Upload" />
			</form>
			<?php
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
		CKEDITOR.replace( 'signature', {
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