<?php 
/*
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

// Set page name for sidebar
$page = "admin-groups";

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

    <title><?php echo $sitename; ?> &bull; Admin Groups</title>
	
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
				if(Session::exists('adm-groups')){
					echo Session::flash('adm-groups');
				}
			?>
			<?php 
			if(!isset($_GET["action"]) && !isset($_GET["group"])){
			?>
			<a href="/admin/groups/?action=new" class="btn btn-default">New Group</a>
			<br /><br />
			<?php 
			$groups = $queries->getAll("groups", array("id", "<>", 0));
			?>
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>ID</th>
						<th>Name</th>
						<th>Users</th>
					</tr>
				</thead>
				<tbody>
			<?php 
			foreach($groups as $group){
			?>
					<tr>
						<td><?php echo $group->id; ?></td>
						<td><a href="/admin/groups/?group=<?php echo $group->id; ?>"><?php echo $group->name; ?></a></td>
						<td><?php echo count($queries->getWhere("users", array("group_id", "=", $group->id))); ?></td>
					</tr>
			<?php 
			}
			?>
				</tbody>
			</table>
			<?php 
			} else if(isset($_GET["action"])){
				if($_GET["action"] === "new"){
					if(Input::exists()) {
						if(Token::check(Input::get('token'))) {
							$validate = new Validate();
							$validation = $validate->check($_POST, array(
								'groupname' => array(
									'required' => true,
									'min' => 2,
									'max' => 20
								)
							));
							
							if($validation->passed()){
								try {
									$queries->create("groups", array(
										'name' => htmlspecialchars(Input::get('groupname')),
										'buycraft_id' => htmlspecialchars(Input::get('buycraft_id'))
									));

									Redirect::to('/admin/groups');
									die();
								
								} catch(Exception $e){
									die($e->getMessage());
								}
							}
						}						
					}
					
					// Generate token for form
					$token = Token::generate();
					
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
						<h2>Create Group</h2>
						<div class="form-group">
							<input class="form-control" type="text" name="groupname" id="groupname" value="<?php echo escape(Input::get('groupname')); ?>" placeholder="Group Name" autocomplete="off">
						</div>
						<div class="form-group">
							<input class="form-control" type="text" name="buycraft_id" id="buycraft_id" placeholder="Buycraft Group ID">
						</div>
						<input type="hidden" name="token" value="<?php echo $token; ?>">
						<input class="btn btn-success" type="submit" value="Create">	
					</form>
					<br />
					<div class="well">
						<h3>Buycraft group instructions</h3>
						<p>Buycraft groups must be created in the order of <strong>lowest value to highest value</strong>.</p>
						<p>For example, a £10 package will be created before a £20 package.</p>
						<p>This will be changed before the beta stage is over.</p>
					</div>
					<?php 
				}
			} else if(isset($_GET["group"])){
				if(Input::exists()) {
					if(Token::check(Input::get('token'))) {
						if(Input::get('action') === "update"){
							$validate = new Validate();
							$validation = $validate->check($_POST, array(
								'groupname' => array(
									'required' => true,
									'min' => 2,
									'max' => 20
								),
								'html' => array(
									'required' => true,
									'min' => 2,
									'max' => 1024
								),
								'html_lg' => array(
									'required' => true,
									'min' => 2,
									'max' => 1024
								)
							));
							
							if($validation->passed()){
								try {
									$queries->update('groups', $_GET["group"], array(
										'name' => Input::get('groupname'),
										'buycraft_id' => Input::get('buycraft_id'),
										'group_html' => Input::get('html'),
										'group_html_lg' => Input::get('html_lg')
									));
									Redirect::to('/admin/groups/?group=' . $_GET['group']);
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
								$queries->delete('groups', array('id', '=' , Input::get('id')));
								Redirect::to('/admin/groups');
								die();
							} catch(Exception $e) {
								die($e->getMessage());
							}				
						}
					}
				}
				
				// Generate token for form
				$token = Token::generate();
				
				if(!is_numeric($_GET["group"])){
					$group = $queries->getWhere("groups", array("name", "=", $_GET["group"]));
				} else {
					$group = $queries->getWhere("groups", array("id", "=", $_GET["group"]));
				}
				if(count($user)){
					echo '<h2>' . htmlspecialchars($group[0]->name) . '</h2>';
					?>
					<form role="form" action="" method="post">
					  <div class="form-group">
						<label for="InputGroupname">Group Name</label>
						<input type="text" name="groupname" class="form-control" id="InputGroupname" placeholder="Group Name" value="<?php echo htmlspecialchars($group[0]->name); ?>">
					  </div>
					  <div class="form-group">
						<label for="InputHTML">Group HTML</label>
						<input type="text" name="html" class="form-control" id="InputHTML" placeholder="HTML" value="<?php echo htmlspecialchars($group[0]->group_html); ?>">
					  </div>
					  <div class="form-group">
						<label for="InputHTML_Lg">Group HTML Large</label>
						<input type="text" name="html_lg" class="form-control" id="InputHTML_Lg" placeholder="HTML Large" value="<?php echo htmlspecialchars($group[0]->group_html_lg); ?>">
					  </div>
					  <?php 
					  if($group[0]->id == 2 || $group[0]->id == 3 || $group[0]->id == 1){} else {
					  ?>
					  <div class="form-group">
						<label for="InputBuycraft">Buycraft ID</label>
						<input type="text" name="buycraft_id" class="form-control" id="InputBuycraft" placeholder="Buycraft ID" value="<?php echo htmlspecialchars($group[0]->buycraft_id); ?>">
					  </div>
					  <?php 
					  }
					  ?>
					  <input type="hidden" name="token" value="<?php echo $token; ?>">
					  <input type="hidden" name="action" value="update">
					  <input type="submit" value="Submit Changes" class="btn btn-default">
					</form>
					<?php 
					if($group[0]->id == 2 || $group[0]->id == 3 || $group[0]->id == 1){} else {
					?>
					<br />
					<form role="form" action="" method="post">
					  <input type="hidden" name="token" value="<?php echo $token; ?>">
					  <input type="hidden" name="action" value="delete">
					  <input type="hidden" name="id" value="<?php echo $group[0]->id; ?>">
					  <input type="submit" value="Delete Group" class="btn btn-danger">
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
  </body>
</html>