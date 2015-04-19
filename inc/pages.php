<?php
/*
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */
 
 /*
  *  Define a list of the pages which exist
  */

$pages = array(
	"", // Index
	"install",
	"install_stats",
	"admin" => array(
		"donate_sync",
		"custom_pages",
		"documentation",
		"donate",
		"execute_donate_sync",
		"forum",
		"general",
		"groups",
		"infractions",
		"minecraft",
		"stats",
		"pages",
		"phpinfo",
		"reset_password",
		"search_users",
		"update_mcnames",
		"update_uuids",
		"upgrade",
		"users",
		"vote",
		"update"
	),
	"credits",
	"donate",
	"forum" => array(
		"create_post",
		"delete_post",
		"delete_thread",
		"edit_post",
		"error",
		"lock_thread",
		"merge_thread",
		"move_thread",
		"new_topic",
		"report_post",
		"reputation",
		"sticky_thread",
		"view_forum",
		"view_topic"
	),
	"mod" => array(
		"announcements",
		"punishments",
		"reports"
	),
	"play",
	"register",
	"signin",
	"user" => array(
		"acknowledge",
		"messaging",
		"settings",
		"avatar_upload"
	),
	"vote",
	"change_password",
	"forgot_password",
	"infractions",
	"profile",
	"signout",
	"validate"
)

?>