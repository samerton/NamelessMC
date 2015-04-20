<?php
/* 
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */
?>
			<div class="well well-sm">
				<ul class="nav nav-pills nav-stacked">
				  <li<?php if($page === "admin-index"){ ?> class="active"<?php } ?>><a href="/admin">Overview</a></li>
				  <li<?php if($page === "admin-general"){ ?> class="active"<?php } ?>><a href="/admin/general">General Settings</a></li>
				  <li<?php if($page === "admin-pages"){ ?> class="active"<?php } ?>><a href="/admin/pages">Pages</a></li>
				  <li<?php if($page === "admin-groups"){ ?> class="active"<?php } ?>><a href="/admin/groups">Groups</a></li>
				  <li<?php if($page === "admin-users"){ ?> class="active"<?php } ?>><a href="/admin/users">Users</a></li>
				  <li<?php if($page === "admin-forum"){ ?> class="active"<?php } ?>><a href="/admin/forum">Forum</a></li>
				  <li<?php if($page === "admin-minecraft"){ ?> class="active"<?php } ?>><a href="/admin/minecraft">Minecraft</a></li>
				  <li<?php if($page === "admin-infractions"){ ?> class="active"<?php } ?>><a href="/admin/infractions">Infractions</a></li>
				  <li<?php if($page === "admin-donate"){ ?> class="active"<?php } ?>><a href="/admin/donate">Donate</a></li>
				  <li<?php if($page === "admin-vote"){ ?> class="active"<?php } ?>><a href="/admin/vote">Vote</a></li>
				  <li<?php if($page === "admin-upgrade"){ ?> class="active"<?php } ?>><a href="/admin/upgrade">Upgrade</a></li>
				  <li<?php if($page === "admin-documentation"){ ?> class="active"<?php } ?>><a href="/admin/documentation">Documentation</a></li>
				</ul>
			</div>