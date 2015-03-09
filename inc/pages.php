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
		"buycraft_sync",
		"documentation",
		"donate",
		"execute_buycraft_sync",
		"forum",
		"general",
		"groups",
		"minecraft",
		"stats",
		"pages",
		"reset_password",
		"search_users",
		"update_mcnames",
		"update_uuids",
		"users",
		"vote",
		"update"
	),
	"credits",
	"donate",
	"forum" => array(
		"create_post",
		"create_topic",
		"delete_post",
		"delete_thread",
		"edit_post",
		"lock_thread",
		"merge_thread",
		"move_thread",
		"report_post",
		"reputation",
		"view_forum",
		"view_topic"
	),
	"help" => array(
		"terms"
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