<?php 
/*
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

// Set page name for sidebar
$page = "admin-forum";

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

    <title><?php echo $sitename; ?> &bull; AdminCP Forums</title>
	
	<?php require("inc/templates/header.php"); ?>
	
	<!-- Custom style -->
	<style>
	textarea {
		resize: none;
	}
	</style>

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
				if(Session::exists('adm-forums')){
					echo Session::flash('adm-forums');
				}
			?>
			<?php 
			if(!isset($_GET["action"]) && !isset($_GET["forum"])){
				if(Input::exists()) {
					if(Token::check(Input::get('token'))) {
						try {
							$queries->update("settings", 17, array(
								'value' => Input::get('layout')
							));
							echo '<script>window.location.replace("/admin/forum");</script>';
							die();
						} catch(Exception $e){
							die($e->getMessage());
						}					
					}
				}
			?>
			<a href="/admin/forum/?action=new" class="btn btn-default">New Forum</a>
			<br /><br />
			<?php 
			$forums = $queries->orderAll("forums", "forum_order", "ASC");
			$forum_layout = $queries->getWhere("settings", array("name", "=", "forum_layout"));
			$forum_layout = $forum_layout[0]->value;
			?>

			<div class="panel panel-default">
				<div class="panel-heading">
					Forums
				</div>
				<div class="panel-body">
					<?php 
					$number = count($forums);
					$i = 1;
					foreach($forums as $forum){
					?>
					<div class="row">
						<div class="col-md-10">
							<?php echo '<a href="/admin/forum/?forum=' . $forum->id . '">' . htmlspecialchars($forum->forum_title) . '</a><br />' . htmlspecialchars($forum->forum_description); ?>
						</div>
						<div class="col-md-2">
							<span class="pull-right">
								<?php if($i !== 1){ ?><a href="/admin/forum/?action=order&dir=up&fid=<?php echo $forum->id;?>" class="btn btn-success btn-sm"><span class="glyphicon glyphicon-arrow-up"></span></a><?php } ?>
								<?php if($i !== $number){ ?><a href="/admin/forum/?action=order&dir=down&fid=<?php echo $forum->id;?>" class="btn btn-danger btn-sm"><span class="glyphicon glyphicon-arrow-down"></span></a><?php } ?>
								<a href="/admin/forum/?action=delete&fid=<?php echo $forum->id;?>" class="btn btn-warning btn-sm"><span class="glyphicon glyphicon-trash"></span></a>
							</span>
						</div>
					</div>
					<hr> 
					<?php 
					$i++;
					}
					?>

				</div>
			</div>
			
			<form action="" method="post">
				<div class="form-group">
				  <label for="InputLayout">Forum Index Layout</label>
				  <select class="form-control" id="InputLayout" name="layout">
					<option value="0" <?php if($forum_layout == 0){ echo ' selected="selected"'; } ?>>Table view</option>
					<option value="1" <?php if($forum_layout == 1){ echo ' selected="selected"'; } ?>>Latest Discussions view</option>
				  </select>
				</div>
				<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
				<input type="submit" class="btn btn-primary" value="Update" />
			</form>
			
			<?php 
			} else if(isset($_GET["action"])){
				if($_GET["action"] === "new"){
					if(Input::exists()) {
						if(Token::check(Input::get('token'))) {
							$validate = new Validate();
							$validation = $validate->check($_POST, array(
								'forumname' => array(
									'required' => true,
									'min' => 2,
									'max' => 150
								),
								'forumdesc' => array(
									'required' => true,
									'min' => 2,
									'max' => 255
								)
							));
							
							if($validation->passed()){
								$last_forum_order = $queries->orderAll('forums', 'forum_order', 'DESC');
								$last_forum_order = $last_forum_order[0]->forum_order;
								try {
									$queries->create("forums", array(
										'forum_title' => htmlspecialchars(Input::get('forumname')),
										'forum_description' => htmlspecialchars(Input::get('forumdesc')),
										'forum_order' => $last_forum_order + 1
									));
									echo '<script>window.location.replace("/admin/forum");</script>';
									die();
								} catch(Exception $e){
									die($e->getMessage());
								}
							}
						} else {
							echo 'Invalid token - <a href="/admin/forum">Back</a>';
							die();
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
						<h2>Create Forum</h2>
						<div class="form-group">
							<input class="form-control" type="text" name="forumname" id="forumname" value="<?php echo escape(Input::get('forumname')); ?>" placeholder="Forum Name" autocomplete="off">
						</div>
						<div class="form-group">
							<textarea name="forumdesc" placeholder="Forum Description" class="form-control" rows="3"></textarea>
						</div>
						<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
						<input class="btn btn-success" type="submit" value="Create">	
					</form>
					<?php 
				} else if($_GET["action"] === "order"){
					if(!isset($_GET["dir"]) || !isset($_GET["fid"]) || !is_numeric($_GET["fid"])){
						echo 'Invalid action - <a href="/admin/forum">Back</a>';
						die();
					}
					if($_GET["dir"] === "up" || $_GET["dir"] === "down"){
						$dir = $_GET["dir"];
					} else {
						echo 'Invalid action - <a href="/admin/forum">Back</a>';
						die();
					}
					
					$forum_id = $queries->getWhere('forums', array("id", "=", $_GET["fid"]));
					$forum_id = $forum_id[0]->id;
					
					$forum_order = $queries->getWhere('forums', array("id", "=", $_GET["fid"]));
					$forum_order = $forum_order[0]->forum_order;
					
					$previous_forums = $queries->orderAll("forums", "forum_order", "ASC");
					
					if($dir == "up"){
						$n = 0;
						foreach($previous_forums as $previous_forum){
							if($previous_forum->id == $_GET["fid"]){
								$previous_fid = $previous_forum[$n - 1]->id;
								$previous_f_order = $previous_forums[$n - 1]->forum_order;
								break;
							}
							$n++;
						}

						try {
							$queries->update("forums", $forum_id, array(
								'forum_order' => $previous_f_order
							));	
							$queries->update("forums", $previous_fid, array(
								'forum_order' => $previous_f_order + 1
							));	
						} catch(Exception $e){
							die($e->getMessage());
						}
						echo '<script>window.location.replace("/admin/forum");</script>';
						die();

					} else if($dir == "down"){
						$n = 0;
						foreach($previous_forums as $previous_forum){
							if($previous_forum->id == $_GET["fid"]){
								$previous_fid = $previous_forums[$n + 1]->id;
								$previous_f_order = $previous_forums[$n + 1]->forum_order;
								break;
							}
							$n++;
						}
						try {
							$queries->update("forums", $forum_id, array(
								'forum_order' => $previous_f_order
							));	
							$queries->update("forums", $previous_fid, array(
								'forum_order' => $previous_f_order - 1
							));	
						} catch(Exception $e){
							die($e->getMessage());
						}
						echo '<script>window.location.replace("/admin/forum");</script>';
						die();
						
					}
					
				} else if($_GET["action"] === "delete"){
					if(!isset($_GET["fid"]) || !is_numeric($_GET["fid"])){
						echo 'Invalid forum id - <a href="/admin/forum">Back</a>';
						die();
					}
					
					if(Input::exists()) {
						if(Token::check(Input::get('token'))) {
							if(Input::get('confirm') === 'true'){
								$forum_perms = $queries->getWhere('forums_permissions', array('forum_id', '=', $_GET["fid"])); // Get permissions to be deleted
								if(Input::get('move_forum') === 'none'){
									$posts = $queries->getWhere('posts', array('forum_id', '=', $_GET["fid"]));
									$topics = $queries->getWhere('topics', array('forum_id' , '=', $_GET["fid"]));
									try {
										foreach($posts as $post){
											$queries->delete('posts', array('id', '=' , $post->id));
										}
										foreach($topics as $topic){
											$queries->delete('topics', array('id', '=' , $topic->id));
										}
										$queries->delete('forums', array('id', '=' , $_GET["fid"]));
										// Forum perm deletion
										foreach($forum_perms as $perm){
											$queries->delete('forums_permissions', array('id', '=', $perm->id));
										}
										
										echo '<script>window.location.replace("/admin/forum");</script>';
										die();
									} catch(Exception $e) {
										die($e->getMessage());
									}
								} else {
									$new_forum = Input::get('move_forum');
									$posts = $queries->getWhere('posts', array('forum_id', '=', $_GET["fid"]));
									$topics = $queries->getWhere('topics', array('forum_id' , '=', $_GET["fid"]));
									try {
										foreach($posts as $post){
											$queries->update('posts', $post->id, array(
												'forum_id' => $new_forum
											));
										}
										foreach($topics as $topic){
											$queries->update('topics', $topic->id, array(
												'forum_id' => $new_forum
											));
										}
										$queries->delete('forums', array('id', '=' , $_GET["fid"]));
										// Forum perm deletion
										foreach($forum_perms as $perm){
											$queries->delete('forums_permissions', array('id', '=', $perm->id));
										}
										echo '<script>window.location.replace("/admin/forum");</script>';
										die();
									} catch(Exception $e) {
										die($e->getMessage());
									}
								}
							}
						} else {
							echo 'Invalid token - <a href="/admin/forum">Back</a>';
							die();
						}
					}
					?>
					<h2>Delete forum</h2>
					<form role="form" action="" method="post">
						<select class="form-control" name="move_forum">
						  <option value="none" selected>Delete topics and posts</option>
						  <?php 
							$forums = $queries->orderAll("forums", "forum_order", "ASC");
							foreach($forums as $forum){
								if($forum->id !== $_GET["fid"]){
									echo '<option value="' . $forum->id . '">' . htmlspecialchars($forum->forum_title) . '</option>';
								}
							}
						  ?>
						</select>
					  <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
					  <input type="hidden" name="confirm" value="true">
					  <br />
					  <input type="submit" value="Delete" class="btn btn-danger">
					</form>
					<?php 
				}
			} else if(isset($_GET["forum"])){
				$available_forums = $queries->getWhere("forums", array("parent", "=", 0)); // Get a list of forums without a parent
				$groups = $queries->getWhere('groups', array('id', '<>', '0')); // Get a list of all groups
				
				if(Input::exists()) {
					if(Token::check(Input::get('token'))) {
						if(Input::get('action') === "update"){
							$validate = new Validate();
							$validation = $validate->check($_POST, array(
								'title' => array(
									'required' => true,
									'min' => 2,
									'max' => 150
								),
								'description' => array(
									'required' => true,
									'min' => 2,
									'max' => 255
								)
							));
							
							if($validation->passed()){
								try {
									// Update the forum
									$queries->update('forums', $_GET["forum"], array(
										'forum_title' => htmlspecialchars(Input::get('title')),
										'forum_description' => htmlspecialchars(Input::get('description')),
										'news' => Input::get('display'),
										'parent' => Input::get('parent_forum')
									));
									
								} catch(Exception $e) {
									die($e->getMessage());
								}
								
								// Guest forum permissions
								$view = Input::get('perm-view-0');
								$create = Input::get('perm-topic-0');
								$post = Input::get('perm-post-0');
								
								$forum_perm_exists = 0;
								
								$forum_perm_query = $queries->getWhere('forums_permissions', array('forum_id', '=', $_GET["forum"]));
								// Todo: change to an AND query, then remove the foreach within the group forum permissions (*)
								if(count($forum_perm_query)){ 
									foreach($forum_perm_query as $query){
										if($query->group_id == 0){
											$forum_perm_exists = 1;
											$update_id = $query->id;
											break;
										}
									}
								}
								
								try {
									if($forum_perm_exists != 0){ // Permission already exists, update
									
										// Update the forum
										$queries->update('forums_permissions', $update_id, array(
											'view' => $view,
											'create_topic' => $create,
											'create_post' => $post
										));
									} else { // Permission doesn't exist, create
										$queries->create('forums_permissions', array(
											'group_id' => 0,
											'forum_id' => $_GET["forum"],
											'view' => $view,
											'create_topic' => $create,
											'create_post' => $post
										));
									}
									
								} catch(Exception $e) {
									die($e->getMessage());
								}
								
								// Group forum permissions
								foreach($groups as $group){ 
									$view = Input::get('perm-view-' . $group->id);
									$create = Input::get('perm-topic-' . $group->id);
									$post = Input::get('perm-post-' . $group->id);
									
									$forum_perm_exists = 0;
									
									// *
									if(count($forum_perm_query)){ 
										foreach($forum_perm_query as $query){
											if($query->group_id == $group->id){
												$forum_perm_exists = 1;
												$update_id = $query->id;
												break;
											}
										}
									}
									
									try {
										if($forum_perm_exists != 0){ // Permission already exists, update
										
											// Update the forum
											$queries->update('forums_permissions', $update_id, array(
												'view' => $view,
												'create_topic' => $create,
												'create_post' => $post
											));
										} else { // Permission doesn't exist, create
											$queries->create('forums_permissions', array(
												'group_id' => $group->id,
												'forum_id' => $_GET["forum"],
												'view' => $view,
												'create_topic' => $create,
												'create_post' => $post
											));
										}
										
									} catch(Exception $e) {
										die($e->getMessage());
									}
								}
								
								echo '<script>window.location.replace("/admin/forum/?forum=' . $_GET['forum'] . '");</script>';
								die();
								
							} else {
								echo '<div class="alert alert-danger">';
								foreach($validation->errors() as $error) {
									echo $error, '<br>';
								}
								echo '</div>';
							}
						}
					} else {
						echo 'Invalid token - <a href="/admin/forum">Back</a>';
						die();
					}
				}
				if(!is_numeric($_GET["forum"])){
					die();
				} else {
					$forum = $queries->getWhere("forums", array("id", "=", $_GET["forum"]));
				}
				if(count($forum)){
					echo '<h2 style="display: inline;">' . htmlspecialchars($forum[0]->forum_title) . '</h2>';
					?>
					<br /><br />
					<form role="form" action="" method="post">
					  <div class="form-group">
						<label for="InputTitle">Forum Name</label>
						<input type="text" name="title" class="form-control" id="InputTitle" placeholder="Title" value="<?php echo htmlspecialchars($forum[0]->forum_title); ?>">
					  </div>
					  <div class="form-group">
					    <label for="InputDescription">Forum Description</label>
						<textarea name="description" id="InputDescription" placeholder="Forum Description" class="form-control" rows="3"><?php echo htmlspecialchars($forum[0]->forum_description); ?></textarea>
				      </div>
					  <div class="form-group">
						<label for="InputParentForum">Parent Forum</label>
						<select class="form-control" id="InputParentForum" name="parent_forum">
						  <option value="0" <?php if($forum[0]->parent == 0){ echo ' selected="selected"'; } ?>>Has no parent</option>
						  <?php
							foreach($available_forums as $available_forum){
							  if($available_forum->id !== $forum[0]->id){
							?>
						  <option value="<?php echo $available_forum->id; ?>" <?php if($available_forum->id == $forum[0]->parent){ ?> selected="selected"<?php } ?>><?php echo htmlspecialchars($available_forum->forum_title); ?></option>
							<?php 
							  }
							}
						  ?>
						</select>
					  </div>
					  <div class="form-group">
						<strong>Forum Permissions</strong><br />
						<?php
						// Get all forum permissions
						$group_perms = $queries->getWhere('forums_permissions', array('forum_id', '=', $_GET["forum"]));
						// Todo: change to an AND query and remove the later foreach (*)
						?>
						<strong>Guests:</strong><br />
						<?php
						foreach($group_perms as $group_perm){
							if($group_perm->group_id == 0){
								$view = $group_perm->view;
								break;
							}
						}
						?>
					    <input type="hidden" name="perm-view-0" value="0" />
					    <label for="Input-view-0">Can view forum:</label>
					    <input name="perm-view-0" id="Input-view-0" value="1" type="checkbox"<?php if(isset($view) && $view == 1){ echo ' checked'; } ?>><br />
					    
						<input type="hidden" name="perm-topic-0" value="0" />
						<input type="hidden" name="perm-post-0" value="0" />
						<br />
						<?php
						foreach($groups as $group){
							// Get the existing group permissions
							
							// *
							foreach($group_perms as $group_perm){
								if($group_perm->group_id == $group->id){
									$view = $group_perm->view;
									$topic = $group_perm->create_topic;
									$post = $group_perm->create_post;
									break;
								}
							}
						?>
						<strong><?php echo htmlspecialchars($group->name); ?>:</strong><br />
						
					    <input type="hidden" name="perm-view-<?php echo $group->id; ?>" value="0" />
					    <label for="Input-view-<?php echo $group->id; ?>">Can view forum:</label>
					    <input name="perm-view-<?php echo $group->id; ?>" id="Input-view-<?php echo $group->id; ?>" value="1" type="checkbox"<?php if(isset($view) && $view == 1){ echo ' checked'; } ?>><br />
					    
						<input type="hidden" name="perm-topic-<?php echo $group->id; ?>" value="0" />
						<label for="Input-topic-<?php echo $group->id; ?>">Can create topic:</label>
					    <input name="perm-topic-<?php echo $group->id; ?>" id="Input-topic-<?php echo $group->id; ?>" value="1" type="checkbox"<?php if(isset($topic) && $topic == 1){ echo ' checked'; } ?>><br />
						
						<input type="hidden" name="perm-post-<?php echo $group->id; ?>" value="0" />
						<label for="Input-post-<?php echo $group->id; ?>">Can post reply:</label>
					    <input name="perm-post-<?php echo $group->id; ?>" id="Input-post-<?php echo $group->id; ?>" value="1" type="checkbox"<?php if(isset($post) && $post == 1){ echo ' checked'; } ?>><br />
						<br />
						<?php
						}
						?>
					  </div>
					  <input type="hidden" name="display" value="0" />
					  <label for="InputDisplay">Display threads as news on front page?</label>
					  <input name="display" id="InputDisplay" value="1" type="checkbox"<?php if($forum[0]->news == 1){ echo ' checked'; } ?>>
					  <br /><br />
					  <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
					  <input type="hidden" name="action" value="update">
					  <input type="submit" value="Submit Changes" class="btn btn-default">
					</form>
					<?php 
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