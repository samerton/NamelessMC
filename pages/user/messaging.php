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

    <title><?php echo $sitename; ?> &bull; UserCP Messaging</title>
	
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

	  <div class="row">
		<div class="col-md-3">
			<div class="well well-sm">
				<ul class="nav nav-pills nav-stacked">
				  <li><a href="/user">Overview</a></li>
				  <li class="active"><a href="/user/messaging">Private Messages<?php if($unread_pms === true){ ?> <span class="glyphicon glyphicon-exclamation-sign"></span><?php }?></a></li>
				  <li><a href="/user/settings">Profile settings</a></li>
				</ul>
			</div>
		</div>
		<div class="col-md-9">
		  <?php
		    if(!isset($_GET['action']) && !isset($_GET['mid'])){
		  ?>
		  <h2 style="display: inline;">Messages</h2><span class="pull-right"><a href="/user/messaging/?action=new" class="btn btn-primary">New</a></span>
		  <br /><br />
		  <?php
		    $pms = $user->listPMs($user->data()->id);
		  ?>
	      <div class="well well-sm">
		    <?php 
			if(count($pms)){
				foreach($pms as $pm){ 
					// Get the first 4 users who have access to the PM, then display a "and x more" label
					$user_string = '';
					$n = 1;
					
					foreach($pm['users'] as $item){
						if($n == 5){
							$user_string .= ' <span class="label label-info">and ' . (count($pm['users']) - $n) . ' more</span>';
							break;
						} else {
							if($n == count($pm['users'])){
								if($item != 0){
									$user_string .= '<a href="/profile/' . $user->idToMCName($item) . '">' . $user->idToName($item) . '</a>';
								} else {
									$user_string .= 'System';
								}
								break;
							} else {
								if($item != 0){
									$user_string .= '<a href="/profile/' . $user->idToMCName($item) . '">' . $user->idToName($item) . '</a>, ';
								} else {
									$user_string .= 'System, ';
								}
							}
						}
						$n++;
					}
				?>
				<div class="row">
				  <div class="col-md-3"><a href="/user/messaging/?mid=<?php echo $pm['id']; ?>"><?php echo htmlspecialchars($pm['title']); ?></a></div>
				  <div class="col-md-5"><?php echo $user_string; ?></div>
				  <div class="col-md-4"><?php echo date('d M Y, H:i', strtotime($pm['date'])); ?></div>
				</div>
				<?php 
				} 
			} else {
			?>
			No messages
			<?php
			}
			?>
		  </div>
		  <?php
		    } else if(isset($_GET['action']) && $_GET['action'] === 'new'){
				if(isset($_GET['uid'])){
					if(!is_numeric($_GET['uid'])){
						echo '<script>window.location.replace(\'/user/messaging/?action=new\');</script>';
						die();
					}
					$to_user = $queries->getWhere('users', array('id', '=', $_GET['uid']));
					if(!count($to_user)){
						echo '<script>window.location.replace(\'/user/messaging/?action=new\');</script>';
						die();
					}
					$to_user = htmlspecialchars($to_user[0]->username);
				}
				if(Input::exists()){
					// Input into database
					if(Token::check(Input::get('token'))) {
						$validate = new Validate();
						if(!isset($_GET['mid'])){
							$validation = $validate->check($_POST, array(
								'title' => array(
									'required' => true,
									'min' => 2,
									'max' => 64
								),
								'message' => array(
									'required' => true,
									'min' => 2,
									'max' => 10000
								),
								'to' => array(
									'required' => true
								)
							));
						} else {
							$validation = $validate->check($_POST, array(
								'message' => array(
									'required' => true,
									'min' => 2,
									'max' => 10000
								)
							));
						}
						
						if($validation->passed()){
							if(!isset($_GET['mid'])){
								$users = Input::get('to');
								$users = explode(',', $users);
								$n = 0;
								
								// Replace white space at start of username, also limit to 10 users
								foreach($users as $item){
									if($item[0] === ' '){
										$users[$n] = substr($item, 1);
									}
									if($n == 10){
										$max_users = true;
										echo "true";
										break;
									}
									$n++;
								}
								
								$title = htmlspecialchars(Input::get('title'));
							} else {
								$author = $queries->getWhere("private_messages", array("id", "=", $_GET["mid"]));
								$title = $author[0]->title;
								$author = $author[0]->author_id;
								
								$users_query = $queries->getWhere("private_messages_users", array("pm_id", "=", $_GET["mid"]));
								foreach($users_query as $item){
									$users[] = $item->user_id;
								}
								
								$users[] = $author;
								
								// Prefix with "RE:"
								if(substr($title, 0, 3) == "RE:"){
									$title = htmlspecialchars($title);
								} else {
									$title = htmlspecialchars("RE: " . $title);
								}
								
							}
							
							// Ensure people haven't been added twice
							$users = array_unique($users);
							
							// Ensure the person who actually created the PM hasn't been added
							foreach($users as $key => $item){
								if($item == $user->data()->username){
									unset($users[$key]);
								}
							}
							
							if(!isset($max_users)){
								try {
									// Input the content
									$queries->create("private_messages", array(
										'author_id' => $user->data()->id,
										'title' => $title,
										'content' => htmlspecialchars(Input::get('message')),
										'sent_date' => date('Y-m-d H:i:s')
									));
									
									// Get the PM ID
									$last_id = $queries->getLastId();
									
									// Loop through the users and give them access to the message
									foreach($users as $item){
										if(!isset($_GET['mid'])){
											// Get ID
											$user_id = $user->NameToId($item);
										} else {
											$user_id = $item;
										}
										
										if($user_id){
											if($user_id !== $user->data()->id){
												// Not the author
												$queries->create("private_messages_users", array(
													'pm_id' => $last_id,
													'user_id' => $user_id
												));
											} else {
												// Is the author, automatically set as read
												$queries->create("private_messages_users", array(
													'pm_id' => $last_id,
													'user_id' => $user_id,
													'read' => 1
												));
											}
										}
									}
									echo '<script>window.location.replace("/user/messaging");</script>';
									die();
								} catch(Exception $e){
									die($e->getMessage());
								}
							}
						}
					}
				}
		  ?>
		  <h2 style="display: inline;">New <?php if(!isset($_GET['mid'])){ ?>Message<?php } else { ?>Reply<?php } ?></h2>
		  <br /><br />
		  <form action="" method="post">
			<?php if(!isset($_GET['mid'])){ ?>
			<div class="form-group">
			  <label for="InputTitle">Message Title</label>
			  <input type="text" name="title" class="form-control" id="InputTitle" value="<?php echo htmlspecialchars(Input::get('title')); ?>">
			</div>
			<div class="form-group">
			  <label for="InputTo">To: <small><em>Separate users with a comma</em></small></label>
			  <input class="form-control" type="text" id="InputTo" name="to" <?php if(isset($to_user)){ ?>value="<?php echo $to_user; ?>"<?php } ?>data-provide="typeahead" data-items="4" data-source='[<?php echo $user->listAllUsers(); ?>]'>
			</div>
			<?php } ?>
			<div class="form-group">
			  <label for="message">Message</label>
			  <?php
			    $message = Input::get('message');

				if(!empty($message)){
					// HTML Purifier - Purify message, only if token/validation fails
					$config = HTMLPurifier_Config::createDefault();
					$config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
					$config->set('URI.DisableExternalResources', false);
					$config->set('URI.DisableResources', false);
					$config->set('HTML.Allowed', 'u,a,p,b,i,small,blockquote,span[style],span[class],p,strong,em,li,ul,ol,div[align],br,img');
					$config->set('CSS.AllowedProperties', array('float', 'color','background-color', 'background', 'font-size', 'font-family', 'text-decoration', 'font-weight', 'font-style', 'font-size'));
					$config->set('HTML.AllowedAttributes', 'src, height, width, alt, href, class, *.style');
					$purifier = new HTMLPurifier($config);
					$message = $purifier->purify(htmlspecialchars_decode($message));
				} else {
					$message = "";
				}
			  ?>
			  <textarea rows="10" name="message" id="message">
				<?php echo $message; ?>
			  </textarea>
			</div>
			<input type="hidden" name="token" value="<?php echo Token::generate(); ?>" />
			<input class="btn btn-primary" type="submit" name="submit" value="Send" />
		  </form>
		  <?php
		    } else if(isset($_GET['action']) && $_GET['action'] === 'delete'){
				if(!isset($_GET['mid'])){
					echo '<script>window.location.replace("/user/messaging");</script>';
					die();
				}
				
				$delete_pm = $user->deletePM($_GET['mid'], $user->data()->id); // Checks to see if the user is part of conversation, and deletes it accordingly
				echo '<script>window.location.replace("/user/messaging");</script>';
				die();
			
			} else if(isset($_GET['mid']) && !isset($_GET['action'])){
				$pm = $user->getPM($_GET["mid"], $user->data()->id); // Get the PM - this also handles setting it as "read" 
				if($pm == false){ // Either PM doesn't exist, or the user doesn't have permission to view it
					echo '<script>window.location.replace("/user/messaging");</script>';
					die();
				}
				
				// Format the users into a string
				$user_string = '';
				$n = 1;
				
				foreach($pm[1] as $item){
					if($n == count($pm[1])){
						if($item != 0){
							$user_string .= '<a href="/profile/' . htmlspecialchars($user->idToMCName($item)) . '">' . htmlspecialchars($user->idToName($item)) . '</a>';
						} else {
							$user_string .= 'System';
						}
					} else {
						if($item != 0){
							$user_string .= '<a href="/profile/' . htmlspecialchars($user->idToMCName($item)) . '">' . htmlspecialchars($user->idToName($item)) . '</a>, ';
						} else {
							$user_string .= 'System, ';
						}
					}
					$n++;
				}
		  ?>
		  <h2 style="display: inline;">Viewing Message</h2><span class="pull-right"><a href="/user/messaging/?action=delete&mid=<?php echo $pm[0]->id; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this message?');">Delete</a></span>
		  <br /><br />
		  <h4><?php echo htmlspecialchars($pm[0]->title); ?></h4>
		  <?php echo $user_string; ?>
		  <br /><br />
		  <div class="panel panel-primary">
		    <div class="panel-heading">
			  <?php 
			  if($item != 0){
				echo '<a class="white-text" href="/profile/' . htmlspecialchars($user->idToMCName($item)) . '">' . htmlspecialchars($user->idToName($item)) . '</a>'; 
			  } else {
				echo '<span class="white-text">System</span>';
			  }
			  ?>
			  <span class="pull-right"><?php echo date('d M Y, H:i', strtotime($pm[0]->sent_date)); ?></span>
			</div>
			<div class="panel-body">
			  <?php
				$config = HTMLPurifier_Config::createDefault();
				$config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
				$config->set('URI.DisableExternalResources', false);
				$config->set('URI.DisableResources', false);
				$config->set('HTML.Allowed', 'u,p,a,b,i,small,blockquote,span[style],span[class],p,strong,em,li,ul,ol,div[align],br,img');
				$config->set('CSS.AllowedProperties', array('float', 'color','background-color', 'background', 'font-size', 'font-family', 'text-decoration', 'font-weight', 'font-style', 'font-size'));
				$config->set('HTML.AllowedAttributes', 'src, href, height, width, alt, class, *.style');
				$purifier = new HTMLPurifier($config);
				
				echo $purifier->purify(htmlspecialchars_decode($pm[0]->content));
			  ?>
			</div>
		  </div>
		  
		  <a href="/user/messaging/?action=new&mid=<?php echo $pm[0]->id; ?>" class="btn btn-primary">Reply</a>
		  <?php
			}
		  ?>
		</div>
      </div>	  

      <hr>

	  <?php require("inc/templates/footer.php"); ?> 
	  
    </div> <!-- /container -->
		
	<?php require("inc/templates/scripts.php"); ?>
	<script src="/assets/js/bootstrap-3-typeahead.min.js"></script>
	<script src="/assets/js/ckeditor.js"></script>
	<script type="text/javascript">
		CKEDITOR.replace( 'message', {
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