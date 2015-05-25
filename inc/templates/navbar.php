<?php 
/*
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 */
	$navbar_style = $queries->getWhere("settings", array("name", "=", "navbar_style"));
	$navbar_style = $navbar_style[0]->value;
	
	$sitename = $queries->getWhere("settings", array("name", "=", "sitename"));
	$sitename = $sitename[0]->value;
	
	$donate = $queries->getWhere("settings", array("name", "=", "donate"));
	$donate = $donate[0]->value;
	
	$vote = $queries->getWhere("settings", array("name", "=", "vote"));
	$vote = $vote[0]->value;
	
	// Extra links
	$extra = array();
	
	if($staff_enabled[0]->value === 'true'){
		$extra['staff_application'] = 'Staff Application';
	}
	
	?>
	<!-- Static navbar -->
    <div class="navbar navbar-<?php if($navbar_style === "0"){ ?>default<?php } else { ?>inverse<?php } ?> navbar-static-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="/"><?php echo htmlspecialchars($sitename); ?></a>
        </div>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            <li<?php if($page === "home"){?> class="active"<?php } ?>><a href="/">Home</a></li>
            <li<?php if($page === "play"){?> class="active"<?php } ?>><a href="/play">Play</a></li>
            <li<?php if($page === "forum"){?> class="active"<?php } ?>><a href="/forum">Forum</a></li>
			<?php if($donate !== "false"){ ?>
            <li<?php if($page === "donate"){?> class="active"<?php } ?>><a href="/donate">Donate</a></li>
			<?php } ?>
			<?php if($vote !== "false"){ ?>
            <li<?php if($page === "vote"){?> class="active"<?php } ?>><a href="/vote">Vote</a></li>
			<?php } ?>
			<?php
			if(!empty($extra)){
			?>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">More <span class="caret"></span></a>
              <ul class="dropdown-menu" role="menu">
			    <?php foreach($extra as $key => $item){ ?>
				<li><a href="/<?php echo $key; ?>"><?php echo $item; ?></a></li>
				<?php } ?>
              </ul>
            </li>
			<?php
			}
			?>
          </ul>
		  <?php 
		  if($page != "signin" && $page != "register"){
		  ?>
          <ul class="nav navbar-nav navbar-right">
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php if($user->isLoggedIn()) { echo '<img class="img-rounded" style="margin: -10px 0px;" src="https://cravatar.eu/avatar/' . htmlspecialchars($user->data()->mcname) . '/25.png" />&nbsp;&nbsp;' . htmlspecialchars($user->data()->username); if((isset($reports) && $reports == true) || $unread_pms === true || (isset($open_apps) && $open_apps === true)){?> <span class="glyphicon glyphicon-exclamation-sign"></span><?php } } else { ?>Guest<?php } ?> <span class="caret"></span></a>
              <ul class="dropdown-menu" role="menu">
				<?php if($user->isLoggedIn()) { ?> 
				  <li><a href="/<?php echo 'profile/' . htmlspecialchars($user->data()->username);?>">Profile</a></li>
				  <li class="divider"></li>	
				  <li<?php if($page === "user"){?> class="active"<?php } ?>><a href="/user">UserCP<?php if($unread_pms === true){ ?> <span class="glyphicon glyphicon-exclamation-sign"></span><?php } ?></a></li>
				  <?php
				  if($user->data()->group_id == 2 || $user->data()->group_id == 3){
				  ?><li<?php if($page === "mod"){?> class="active"<?php } ?>><a href="/mod">ModCP<?php if($reports === true || (isset($open_apps) && $open_apps === true)){?> <span class="glyphicon glyphicon-exclamation-sign"></span><?php } ?></a></li><?php 
				  }
				  if($user->data()->group_id == 2){?><li<?php if($page === "admin"){?> class="active"<?php } ?>><a href="/admin">AdminCP</a></li><?php }
				  ?>
				  <li class="divider"></li>
				  <li><a href="/signout">Sign Out</a></li>				
				<?php } else { ?>
				  <li><a href="/signin">Sign In</a></li>
				  <li><a href="/register">Register</a></li>
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
		  <a href="/user/acknowledge/?iid=<?php echo $infraction[0]["id"]; ?>" class="btn btn-primary">Acknowledge</a>
		  </center>
		</div>
	</div>
	<?php 
		}
	}
	?>