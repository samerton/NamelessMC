<?php 
/*
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */
?>
      <footer>
		<?php if($queries->getWhere("settings", array("name", "=", "youtube_url"))[0]->value != "null"){ ?><a href="<?php echo $queries->getWhere("settings", array("name", "=", "youtube_url"))[0]->value; ?>"><i id="social" class="fa fa-youtube-square fa-3x social-gp"></i></a><?php } ?>
		<?php if($queries->getWhere("settings", array("name", "=", "twitter_url"))[0]->value != "null"){ ?><a href="<?php echo $queries->getWhere("settings", array("name", "=", "twitter_url"))[0]->value; ?>"><i id="social" class="fa fa-twitter-square fa-3x social-tw"></i></a><?php } ?>
		<?php if($queries->getWhere("settings", array("name", "=", "gplus_url"))[0]->value != "null"){ ?><a href="<?php echo $queries->getWhere("settings", array("name", "=", "gplus_url"))[0]->value; ?>"><i id="social" class="fa fa-google-plus-square fa-3x social-gp"></i></a><?php } ?>
		<?php if($queries->getWhere("settings", array("name", "=", "fb_url"))[0]->value != "null"){ ?><a href="<?php echo $queries->getWhere("settings", array("name", "=", "fb_url"))[0]->value; ?>"><i id="social" class="fa fa-facebook-square fa-3x social-fb"></i></a><?php } ?>
		<span class="pull-right">

			<ul class="nav nav-pills dropup">
				<li class="dropdown">
					<a class="dropdown-toggle" data-toggle="dropdown" href="#">&copy; <?php echo htmlspecialchars($queries->getWhere("settings", array("name", "=", "sitename"))[0]->value) . ' ' . date('Y'); ?></a>
					<ul class="dropdown-menu">
						<li><a href="<?php echo $path; ?>credits">Site software &copy; Samerton</a></li>
					</ul>
				</li>
				<?php if($queries->getWhere("settings", array("name", "=", "infractions"))[0]->value != "false"){ ?>
				<li><a href="<?php echo $path; ?>infractions.php">Infractions</a></li>
				<?php } ?>
				<!--<li><a href="<?php //echo $path; ?>help">Help</a></li>-->
				<li class="dropdown">
					<a class="dropdown-toggle" data-toggle="dropdown" href="#">Rules <b class="caret"></b></a>
					<ul class="dropdown-menu">
						<li><a href="<?php echo $path; ?>forum/view_topic.php?tid=1">Server</a></li>
						<li><a href="<?php echo $path; ?>forum/view_topic.php?tid=2">Forum</a></li>
					</ul>
				</li>
			</ul>
		</span>
      </footer>	 