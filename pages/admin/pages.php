<?php 
/*
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

// Set page name for sidebar
$page = "admin-pages";

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
		if(Input::get('action') == 'rules'){
			$validate = new Validate();
			$validation = $validate->check($_POST, array(
				'forum_rules' => array(
					'max' => 10
				),
				'server_rules' => array(
					'max' => 10
				)
			));
			
			if($validation->passed()){
				try {
					$queries->update("settings", 23, array(
						"value" => escape(Input::get('forum_rules'))
					));
					$queries->update("settings", 24, array(
						"value" => escape(Input::get('server_rules'))
					));
				} catch(Exception $e){
					die($e->getMessage());
				}
			}
		} else {
			if (Input::get('value') === "false"){
				$value = "true";
			} else if (Input::get('value') === "true"){
				$value = "false";
			}
			try {
				$queries->update('settings', (Input::get('id')), array(
					'value' => $value
				));
				Redirect::to('/admin/pages');
				die();
			} catch(Exception $e){
				die($e->getMessage());
			}
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
	<meta name="robots" content="noindex">
    <link rel="icon" href="/favicon.ico">

    <title><?php echo $sitename; ?> &bull; Admin Pages</title>
	
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
				<h2>Page Settings</h2>
				<a href="/admin/custom_pages" class="btn btn-primary">Edit custom pages</a><br /><br />
				<?php $pages = $queries->getAll("settings", array("name", "<>", "")); ?>
				<strong>Forum maintenance mode:</strong>
				<?php 
				echo '
				<form name="maintenance" style="display: inline;" action="" method="post">
					<input type="hidden" name="id" value="2" />
					<input type="hidden" name="value" value="' . $pages[1]->value . '" />
					<input type="hidden" name="token" value="' . $token . '" />
					<a href="#" onclick="document.forms[\'maintenance\'].submit();">' . htmlspecialchars(ucfirst($pages[1]->value)) . '</a>
				</form>
				';
				?>
				<br />
				<strong>Enable Donate page:</strong>
				<?php 
				echo '
				<form name="donate" style="display: inline;" action="" method="post">
					<input type="hidden" name="id" value="4" />
					<input type="hidden" name="value" value="' . $pages[3]->value . '" />
					<input type="hidden" name="token" value="' . $token . '" />
					<a href="#" onclick="document.forms[\'donate\'].submit();">' . htmlspecialchars(ucfirst($pages[3]->value)) . '</a>
				</form>
				';
				?>
				<br />
				<strong>Enable Vote page:</strong>
				<?php 
				echo '
				<form name="vote" style="display: inline;" action="" method="post">
					<input type="hidden" name="id" value="3" />
					<input type="hidden" name="value" value="' . $pages[2]->value . '" />
					<input type="hidden" name="token" value="' . $token . '" />
					<a href="#" onclick="document.forms[\'vote\'].submit();">' . htmlspecialchars(ucfirst($pages[2]->value)) . '</a>
				</form>
				';
				?>
				<br /><br />
				<?php
				// URLs to server rules and forum rules
				$forum_rules = $queries->getWhere("settings", array("name", "=", "rules_forum_url"));
				$forum_rules = $forum_rules[0]->value;
				
				$server_rules = $queries->getWhere("settings", array("name", "=", "rules_server_url"));
				$server_rules = $server_rules[0]->value;
				?>
				<form action="" method="post">
					<div class="form-group">
						<label for="forum_rules">Forum Rules Topic ID:</label>
						<input class="form-control" type="text" name="forum_rules" id="forum_rules" value="<?php echo escape($forum_rules); ?>" placeholder="Forum Rules Topic ID" autocomplete="off">
					</div>
					<div class="form-group">
						<label for="forum_rules">Server Rules Topic ID:</label>
						<input class="form-control" type="text" name="server_rules" id="server_rules" value="<?php echo escape($server_rules); ?>" placeholder="Server Rules Topic ID" autocomplete="off">
					</div>
					<input type="hidden" name="token" value="<?php echo $token; ?>">
					<input type="hidden" name="action" value="rules">
					<input class="btn btn-success" type="submit" value="Update">	
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