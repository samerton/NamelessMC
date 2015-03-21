<?php 
/*
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

// Set page name for sidebar
$page = "admin-users";

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

require('inc/includes/password.php'); // Password compat library
require('inc/includes/html/library/HTMLPurifier.auto.php'); // HTMLPurifier
require('inc/functions/paginate.php'); // Get number of users on a page

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

    <title><?php echo $sitename; ?> &bull; AdminCP Users</title>
	
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
			<?php
			if(Session::exists('adm-users')){
				echo Session::flash('adm-users');
			}
			if(!isset($_GET["action"]) && !isset($_GET["user"])){
				if(isset($_GET['p'])){
					if (!is_numeric($_GET['p'])){
						Redirect::to("/admin/users");
					} else {
						if($_GET['p'] == 1){ 
							// Avoid bug in pagination class
							Redirect::to('/admin/users/');
							die();
						}
						$p = $_GET['p'];
					}
				} else {
					$p = 1;
				}
				
				$users = $queries->orderAll("users", "USERNAME", "ASC");
				$groups = $queries->getAll("groups", array("id", "<>", 0));
				
				// instantiate; set current page; set number of records
				$pagination = (new Pagination());
				$pagination->setCurrent($p);
				$pagination->setTotal(count($users));
				$pagination->alwaysShowPagination();

				// Get number of users we should display on the page
				$paginate = PaginateArray($p);
				
				$n = $paginate[0];
				$f = $paginate[1];
				
				if(count($users) > $f){
					$d = $p * 10;
				} else {
					$d = count($users) - $n;
					$d = $d + $n;
				}
			?>
			<a href="/admin/users/?action=new" class="btn btn-default">New User</a>
			<a href="/admin/buycraft_sync" class="btn btn-default">Synchronise with Buycraft</a>
			<span class="pull-right">
				<form class="form-inline" role="form" action="/admin/search_users" method="post">
				  <div class="form-group">
					<div class="input-group">
					  <input type="text" class="form-control" id="inputSearch" placeholder="Search">
					  <input type="hidden" value="<?php echo Token::generate(); ?>">
					  <span class="input-group-btn">
						<button class="btn btn-default" type="submit"><span class="glyphicon glyphicon-search"></span></button>
					  </span>
					</div>
				  </div>
				</form>
			</span>
			<br /><br />
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>Username</th>
						<th>Email</th>
						<th>Group</th>
						<th>Registered</th>
					</tr>
				</thead>
				<tbody>
				<?php 
				while ($n < $d) {
					$i = 0;
					$user_group = "";
					foreach($groups as $group){
						if($group->id === $users[$n]->group_id){
							$user_group = $group->name;
							break;
						} else {
							$i++;
						}
					}
				?>
				  <tr>
					<td><a href="/admin/users/?user=<?php echo $users[$n]->id; ?>"><?php echo htmlspecialchars($users[$n]->username); ?></a></td>
					<td><?php echo htmlspecialchars($users[$n]->email); ?></td>
					<td><?php echo htmlspecialchars($user_group); ?></td>
					<td><?php echo date("d M Y, H:i", $users[$n]->joined); ?></td>
				  </tr>
				<?php
					$n++;
				}
				?>
				</tbody>
			</table>
				<?php 
				echo $pagination->parse(); // Print pagination
			} else if(isset($_GET["action"])){
				if($_GET["action"] === "new"){
					if(Input::exists()) {
						if(Token::check(Input::get('token'))) {
							$validate = new Validate();
							$validation = $validate->check($_POST, array(
								'username' => array(
									'required' => true,
									'min' => 2,
									'max' => 20,
									'isvalid' => true,
									'unique' => 'users'
								),
								'password' => array(
									'required' => true,
									'min' => 6
								),
								'password_again' => array(
									'required' => true,
									'matches' => 'password'
								),
								'email' => array(
									'required' => true,
									'min' => 4,
									'max' => 50
								),
								'group' => array(
									'required' => true
								)					
							));
							
							if($validation->passed()){
								$user = new User();
								
								$password = password_hash(Input::get('password'), PASSWORD_BCRYPT, array("cost" => 13));
								
								// Get current unix time
								$date = new DateTime();
								$date = $date->getTimestamp();
								
								try {
									$user->create(array(
										'username' => htmlspecialchars(Input::get('username')),
										'password' => $password,
										'pass_method' => 'default',
										'joined' => $date,
										'group_id' => Input::get('group'),
										'email' => htmlspecialchars(Input::get('email'))
									));

									Redirect::to('/admin/users');
									die();
								} catch(Exception $e){
									die($e->getMessage());
								}
							}
						}
					}
					if(isset($validation)){
						if(!$validation->passed()){
					?>
					<div class="alert alert-danger">
						<?php
						foreach($validation->errors() as $error) {
							echo $error, '<br />';
						}
						?>
					</div>
					<?php 
						}
					}
					?>
					<form action="" method="post">
						<h2>Create Account</h2>
						<div class="form-group">
							<input class="form-control" type="text" name="username" id="username" value="<?php echo escape(Input::get('username')); ?>" placeholder="Username" autocomplete="off">
						</div>
						<div class="form-group">
							<input class="form-control" type="text" name="email" id="email" value="<?php echo escape(Input::get('email')); ?>" placeholder="Email">
						</div>
						<div class="form-group">
							<input class="form-control" type="password" name="password" id="password" placeholder="Password">
						</div>
						<input class="form-control" type="password" name="password_again" id="password_again" placeholder="Password again">	
						<input type="hidden" name="token" value="<?php echo Token::generate(); ?>"><br />
						<b>Group:</b>
						<select name="group" id="group" size="5" class="form-control">
						  <?php
							$groups = $queries->orderAll('groups', 'name', 'ASC'); 
							$n = 0;
							while ($n < count($groups)){
								$result = (array)$groups[$n];
								echo '<option value="' . $result["id"] . '">' . $result["name"] . '</option>';
								$n++;
							}
						  ?>
						</select>
						<br />
						<input class="btn btn-success" type="submit" value="Create">	
					</form>
					<?php 
				}
			} else if(isset($_GET["user"])){
				if(Input::exists()) {
					if(Token::check(Input::get('token'))) {
						if(Input::get('action') === "update"){
							$validate = new Validate();
							$validation = $validate->check($_POST, array(
								'email' => array(
									'required' => true,
									'min' => 2,
									'max' => 50
								),
								'group' => array(
									'required' => true
								),
								'username' => array(
									'required' => true,
									'min' => 2,
									'max' => 20
								),
								'MCUsername' => array(
									'isvalid' => true
								),
								'UUID' => array(
									'max' => 32
								),
								'signature' => array(
									'max' => 256
								),
								'ip' => array(
									'max' => 256
								)
							));
							
							if($validation->passed()){
								try {
									$queries->update('users', $_GET["user"], array(
										'username' => htmlspecialchars(Input::get('username')),
										'email' => htmlspecialchars(Input::get('email')),
										'group_id' => Input::get('group'),
										'mcname' => htmlspecialchars(Input::get('MCUsername')),
										'uuid' => htmlspecialchars(Input::get('UUID')),
										'signature' => htmlspecialchars(Input::get('signature')),
										'lastip' => Input::get('ip')
									));
									Redirect::to('/admin/users/?user=' . $_GET['user']);
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
						} else if(Input::get('action') == "delete"){
							try {
								$queries->delete('users', array('id', '=' , $data[0]->id));
								
							} catch(Exception $e) {
								die($e->getMessage());
							}
							Redirect::to('/admin/users');
							die();
						} else if(Input::get('action') == "avatar_disable"){
							try {
								$queries->update('users', $_GET["user"], array(
									"has_avatar" => "0"
								));
							} catch(Exception $e) {
								die($e->getMessage());
							}
						}
					}
				}
				if(!is_numeric($_GET["user"])){
					$user = $queries->getWhere("users", array("username", "=", $_GET["user"]));
				} else {
					$user = $queries->getWhere("users", array("id", "=", $_GET["user"]));
				}
				if(count($user)){
					$token = Token::generate();
					
				    // Initialise HTML Purifier
					$config = HTMLPurifier_Config::createDefault();
					$config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
					$config->set('URI.DisableExternalResources', false);
					$config->set('URI.DisableResources', false);
					$config->set('HTML.Allowed', 'u,p,b,i,small,blockquote,span[style],span[class],p,strong,em,li,ul,ol,div[align],br,img');
					$config->set('CSS.AllowedProperties', array('text-align', 'float', 'color','background-color', 'background', 'font-size', 'font-family', 'text-decoration', 'font-weight', 'font-style', 'font-size'));
					$config->set('HTML.AllowedAttributes', 'href, src, height, width, alt, class, *.style');
					$purifier = new HTMLPurifier($config);
					
					$signature = $purifier->purify(htmlspecialchars_decode($user[0]->signature));
					
					echo '<h2 style="display: inline;">' . htmlspecialchars($user[0]->username) . '</h2>';
					?>
					<span class="pull-right">
						<div class="btn-group">
						  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
							Tasks <span class="caret"></span>
						  </button>
						  <ul class="dropdown-menu" role="menu">
							<li><a href="/admin/update_uuids/?uid=<?php echo $user[0]->id; ?>">Update UUID</a></li>
							<li><a href="/admin/update_mcnames/?uid=<?php echo $user[0]->id; ?>">Update Minecraft Name</a></li>
							<li><a href="/admin/reset_password/?uid=<?php echo $user[0]->id; ?>">Reset Password</a></li>
							<li><a href="/mod/punishments/?uid=<?php echo $user[0]->id; ?>">Punish User</a></li>
						  </ul>
						</div>
					</span>
					<br /><br />
					<form role="form" action="" method="post">
					  <div class="form-group">
						<label for="InputUsername">Username</label>
						<input type="text" name="username" class="form-control" id="InputUsername" placeholder="Username" value="<?php echo htmlspecialchars($user[0]->username); ?>">
					  </div>
					  <div class="form-group">
						<label for="InputEmail">Email address</label>
						<input type="email" name="email" class="form-control" id="InputEmail" placeholder="Email" value="<?php echo htmlspecialchars($user[0]->email); ?>">
					  </div>
					  <?php
					  $displaynames = $queries->getWhere("settings", array("name", "=", "displaynames"));
					  $displaynames = $displaynames[0]->value;
					  if($displaynames === "true"){
					  ?>
					  <div class="form-group">
						<label for="InputMCUsername">Minecraft Username</label>
						<input type="text" name="MCUsername" class="form-control" name="MCUsername" id="InputMCUsername" placeholder="Minecraft Username" value="<?php echo htmlspecialchars($user[0]->mcname); ?>">
					  </div>
					  <?php
					  }
					  ?>
					  <div class="form-group">
						<label for="InputUUID">Minecraft UUID</label>
						<input type="text" name="UUID" class="form-control" id="InputUUID" placeholder="Minecraft UUID" value="<?php echo htmlspecialchars($user[0]->uuid); ?>">
					  </div>
					  <div class="form-group">
					    <label for="InputSignature">Signature</label>
						<textarea class="signature" rows="10" name="signature" id="InputSignature"><?php echo $signature; ?></textarea>
				      </div>
					  <div class="form-group">
						<label for="InputIP">IP address</label>
						<input class="form-control" name="ip" id="InputIP" type="text" placeholder="<?php echo htmlspecialchars($user[0]->lastip); ?>" readonly>
					  </div>
					  <?php 
					  $groups = $queries->orderAll('groups', 'name', 'ASC');
					  ?>
					  <div class="form-group">
						 <label for="InputGroup">Group</label>
						 <select class="form-control" id="InputGroup" name="group">
						<?php 
						foreach($groups as $group){ 
						?>
						  <option value="<?php echo $group->id; ?>" <?php if($group->id === $user[0]->group_id){ echo 'selected="selected"'; } ?>><?php echo $group->name; ?></option>
						<?php 
						} 
						?>
						</select> 
					  </div>
					  <input type="hidden" name="token" value="<?php echo $token; ?>">
					  <input type="hidden" name="action" value="update">
					  <input type="submit" value="Submit Changes" class="btn btn-default">
					</form>
					<br />
					<?php
					// Is avatar uploading enabled?
					$avatar_enabled = $queries->getWhere('settings', array('name', '=', 'user_avatars'));
					$avatar_enabled = $avatar_enabled[0]->value;

					if($avatar_enabled === "true"){
					?>
					<strong>Other actions:</strong><br />
					<form role="form" action="" method="post">
					  <input type="hidden" name="token" value="<?php echo $token; ?>">
					  <input type="hidden" name="action" value="avatar_disable">
					  <input type="submit" value="Disable avatar" class="btn btn-danger">
					</form>
					<?php 
					}
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