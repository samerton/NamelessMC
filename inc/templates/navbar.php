<?php 
/*
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 */

	if(!isset($queries)){
		$queries = new Queries();
	}
	if(!isset($forum)){
		$forum = new Forum();
	}
	?>
	<!-- Static navbar -->
    <div class="navbar navbar-<?php if($queries->getWhere("settings", array("name", "=", "navbar_style"))[0]->value === "0"){ ?>default<?php } else { ?>inverse<?php } ?> navbar-static-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="<?php echo $path; if($page === "profile" || $page === "infractions"){echo 'index.php';}?>"><?php echo htmlspecialchars($queries->getWhere("settings", array("name", "=", "sitename"))[0]->value); ?></a>
        </div>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            <li<?php if($page === "home"){?> class="active"<?php } ?>><a href="<?php echo $path; if($page === "profile" || $page === "infractions"){echo './';}?>">Home</a></li>
            <li<?php if($page === "play"){?> class="active"<?php } ?>><a href="<?php echo $path;?>play">Play</a></li>
            <li<?php if($page === "forum"){?> class="active"<?php } ?>><a href="<?php echo $path;?>forum">Forum</a></li>
			<?php if($queries->getWhere("settings", array("name", "=", "donate"))[0]->value !== "false"){ ?>
            <li<?php if($page === "donate"){?> class="active"<?php } ?>><a href="<?php echo $path;?>donate">Donate</a></li>
			<?php } ?>
			<?php if($queries->getWhere("settings", array("name", "=", "vote"))[0]->value !== "false"){ ?>
            <li<?php if($page === "vote"){?> class="active"<?php } ?>><a href="<?php echo $path;?>vote">Vote</a></li>
			<?php } ?>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">More <span class="caret"></span></a>
              <ul class="dropdown-menu" role="menu">
                <li><a href="#">Players</a></li>
                <li><a href="#">Staff</a></li>
                <li class="divider"></li>
                <li class="dropdown-header">Live Maps</li>
                <li><a href="#">Survival</a></li>
                <li><a href="#">Creative</a></li>
              </ul>
            </li>
          </ul>
		  <?php 
		  if($page != "signin" && $page != "register"){
			if($user->isLoggedIn()) { 
				$messages = $forum->hasUnreadMessages($user->data()->id);
				$exclaim = false;
				if($user->data()->group_id == 2 || $user->data()->group_id == 3){
					if($reports = $queries->getWhere("reports", array('status' , '<>', '1')) != false){ 
						$exclaim = true; 
					}
				}
			}
		?>
          <ul class="nav navbar-nav navbar-right">
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php if($user->isLoggedIn()) { echo htmlspecialchars($user->data()->username); if($exclaim === true || $messages === true){?> <span class="glyphicon glyphicon-exclamation-sign"></span><?php } } else { ?>Guest<?php } ?> <span class="caret"></span></a>
              <ul class="dropdown-menu" role="menu">
				<?php if($user->isLoggedIn()) { ?> 
				  <li><a href="<?php echo $path . 'profile.php?user=' . htmlspecialchars($user->data()->username);?>">Profile</a></li>
				  <li class="divider"></li>	
				  <li<?php if($page === "user"){?> class="active"<?php } ?>><a href="<?php echo $path;?>user">UserCP<?php if($messages === true){ ?> <span class="glyphicon glyphicon-exclamation-sign"></span><?php } ?></a></li>
				  <?php
				  if($user->data()->group_id == 2 || $user->data()->group_id == 3){
				  ?><li<?php if($page === "mod"){?> class="active"<?php } ?>><a href="<?php echo $path;?>mod">ModCP<?php if($exclaim === true){?> <span class="glyphicon glyphicon-exclamation-sign"></span><?php } ?></a></li><?php 
				  }
				  if($user->data()->group_id == 2){?><li<?php if($page === "admin"){?> class="active"<?php } ?>><a href="<?php echo $path;?>admin">AdminCP</a></li><?php }
				  ?>
				  <li class="divider"></li>
				  <li><a href="<?php echo $path; ?>signout.php">Sign Out</a></li>				
				<?php } else { ?>
				  <li><a href="<?php echo $path; ?>signin">Sign In</a></li>
				  <li><a href="<?php echo $path; ?>register">Register</a></li>
				<?php } ?>
              </ul>
            </li>
          </ul>
		  <?php } ?>
        </div><!--/.nav-collapse -->
      </div>
    </div>
	
	<?php
	if($user->isLoggedIn()){
		if($infraction = $user->hasInfraction($user->data()->id)){
	?>
	<div class="container">
		<div class="alert alert-danger">
		  <center>
		  You have received a warning from <?php echo htmlspecialchars($user->IdToName($infraction[0]["staff"])); ?> dated <?php echo date("jS F Y", strtotime($infraction[0]["date"])); ?>.<br /><br />
		  "<?php echo htmlspecialchars($infraction[0]["reason"]); ?>"<br /><br />
		  <a href="<?php echo $path; ?>user/acknowledge.php?iid=<?php echo $infraction[0]["id"]; ?>" class="btn btn-primary">Acknowledge</a>
		  </center>
		</div>
	</div>
	<?php 
		}
	}
	?>