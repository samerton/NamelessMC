<?php 
/*
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */
 
$youtube_url = $queries->getWhere("settings", array("name", "=", "youtube_url"));
$youtube_url = $youtube_url[0]->value;

$twitter_url = $queries->getWhere("settings", array("name", "=", "twitter_url"));
$twitter_url = $twitter_url[0]->value;

$gplus_url = $queries->getWhere("settings", array("name", "=", "gplus_url"));
$gplus_url = $gplus_url[0]->value;

$fb_url = $queries->getWhere("settings", array("name", "=", "fb_url"));
$fb_url = $fb_url[0]->value;

$infractions = $queries->getWhere("settings", array("name", "=", "infractions"));
$infractions = $infractions[0]->value;

$server_rules_url = ""; // todo
$forum_rules_url = ""; // todo
 
?>
      <footer>
		<?php if($youtube_url != "null"){ ?><a href="<?php echo htmlspecialchars($youtube_url); ?>"><i id="social" class="fa fa-youtube-square fa-3x social-gp"></i></a><?php } ?>
		<?php if($twitter_url != "null"){ ?><a href="<?php echo htmlspecialchars($twitter_url); ?>"><i id="social" class="fa fa-twitter-square fa-3x social-tw"></i></a><?php } ?>
		<?php if($gplus_url != "null"){ ?><a href="<?php echo htmlspecialchars($gplus_url); ?>"><i id="social" class="fa fa-google-plus-square fa-3x social-gp"></i></a><?php } ?>
		<?php if($fb_url != "null"){ ?><a href="<?php echo htmlspecialchars($fb_url); ?>"><i id="social" class="fa fa-facebook-square fa-3x social-fb"></i></a><?php } ?>
		<span class="pull-right">

			<ul class="nav nav-pills dropup">
				<li class="dropdown">
					<a class="dropdown-toggle" data-toggle="dropdown" href="#">&copy; <?php echo htmlspecialchars($sitename) . ' ' . date('Y'); ?></a>
					<ul class="dropdown-menu">
						<li><a href="/credits">Site software &copy; Samerton</a></li>
					</ul>
				</li>
				<?php if($infractions != "false"){ ?>
				<li><a href="/infractions">Infractions</a></li>
				<?php } ?>
				<li><a href="/help">Help</a></li>
				<li class="dropdown">
					<a class="dropdown-toggle" data-toggle="dropdown" href="#">Rules <b class="caret"></b></a>
					<ul class="dropdown-menu">
						<li><a href="/forum">Server</a></li>
						<li><a href="/forum">Forum</a></li>
					</ul>
				</li>
			</ul>
		</span>
      </footer>	 