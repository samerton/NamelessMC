<?php 
/*
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */
 
/*
 *  Converter from ModernBB
 *  
 *  Converts:
 *     - Bans
 *     - Categories/Forums
 *     - Groups
 *     - Posts and Topics
 *     - Reports
 *     - Users
 */
 
if(!isset($queries)){
	$queries = new Queries();
}
 
/*
 *  First, check the database connection specified in the form submission
 */
 
$mysqli = new mysqli(Input::get("db_address"), Input::get("db_username"), Input::get("db_password"), Input::get("db_name"));

if($mysqli->connect_errno) {
	header('Location: /install/?step=convert&convert=yes&from=modernbb&error=true');
	die();
}

/*
 *  Get the table prefix
 */

$prefix = '';

$inputted_prefix = Input::get('db_prefix');

if(!empty($inputted_prefix)){
	$prefix = escape($inputted_prefix);
}

/*
 *  Users
 */
 
/*
 *  Query the database
 */

$modernbb_users = $mysqli->query("SELECT * FROM {$prefix}users");

$modernbb_users->data_seek(0);

/*
 *  Loop through the users
 */
while ($row = $modernbb_users->fetch_assoc()) {
	if($row["username"] === "Guest"){
		continue;
	}
	
	if($row["username"] === $user->data()->username){
		$queries->update("users", $user->data()->id, array(
			"id" => $row["id"]
		));
		$queries->update("users_session", 1, array(
			"user_id" => $row["id"]
		));
	} else {
		// Get the user's group info
		$group = $row["group_id"];
		$group = $mysqli->query("SELECT * FROM {$prefix}groups WHERE g_id = {$group}");
		$group->data_seek(0);
		$group = $group->fetch_assoc();
		
		if($group["g_id"] == 1){ // admin
			$group_id = 2;
		} else if($group["g_id"] == 2){ // moderator
			$group_id = 3;
		} else if($group["g_id"] == 4){ // member
			$group_id = 1;
		} else if($group["g_id"] == 3){ // guest, needs to be member
			$group_id = 1;
		} else {
			$group_id = $group["g_id"];
		}
		
		$group = null;
		
		$queries->create("users", array(
			"id" => $row["id"],
			"username" => htmlspecialchars($row["username"]),
			"password" => htmlspecialchars($row["password"]),
			"pass_method" => "modernbb",
			"mcname" => htmlspecialchars($row["username"]),
			"uuid" => "",
			"joined" => date('U', $row["registered"]),
			"group_id" => $group_id,
			"email" => $row["email"],
			"lastip" => "",
			"active" => 1,
			"signature" => htmlspecialchars($row["signature"]),
			"reset_code" => ""
		));
	}
}

$modernbb_users = null;

/*
 *  Groups
 */
 
/*
 *  Query the database
 */

$modernbb_groups = $mysqli->query("SELECT * FROM {$prefix}groups");

$modernbb_groups->data_seek(0);

/*
 *  Loop through the groups
 */
while ($row = $modernbb_groups->fetch_assoc()) {
	if($row["g_id"] == 1 || $row["g_id"] == 2 || $row["g_id"] == 3 || $row["g_id"] == 4){
		continue;
	}
	
	$queries->create("groups", array(
		"id" => $row["g_id"],
		"name" => htmlspecialchars($row["g_title"])
	));
}

$modernbb_groups = null;

/*
 *  Categories
 */
 
/*
 *  Query the database
 */
 
$modernbb_categories = $mysqli->query("SELECT * FROM {$prefix}categories");

$modernbb_categories->data_seek(0);
$n = 1;

/*
 *  Loop through the categories
 */
while ($row = $modernbb_categories->fetch_assoc()) {
	$queries->create("forums", array(
		"forum_title" => htmlspecialchars($row["cat_name"]),
		"forum_description" => "Parent category",
		"forum_order" => $n
	));
	$n++;
}

$modernbb_categories = null;

/*
 *  Forums
 */
 
/*
 *  Query the database
 */
 
$modernbb_forums = $mysqli->query("SELECT * FROM {$prefix}forums");

$modernbb_forums->data_seek(0);

/*
 *  Loop through the forums
 */
while ($row = $modernbb_forums->fetch_assoc()) {
	// Get the last topic ID
	$topic = "'" . escape($row["last_topic"]) . "'";
	$topic = $mysqli->query("SELECT * FROM {$prefix}topics WHERE subject={$topic}");
	$topic = $topic->fetch_assoc();
	$topic_id = $topic["id"];
	
	$topic = null;

	$queries->create("forums", array(
		"forum_title" => htmlspecialchars($row["forum_name"]),
		"forum_description" => htmlspecialchars($row["forum_desc"]),
		"last_post_date" => date('Y-m-d H:i:s', $row["last_post"]),
		"last_user_posted" => $row["last_poster_id"],
		"last_topic_posted" => $topic_id,
		"parent" => $row["cat_id"],
		"forum_order" => $n
	));
	$n++;
}

$modernbb_forums = null;
 
/*
 *  Topics
 */
 
/*
 *  Query the database
 */

$modernbb_topics = $mysqli->query("SELECT * FROM {$prefix}topics");

$modernbb_topics->data_seek(0);
 
/*
 *  Loop through the topics
 */
while ($row = $modernbb_topics->fetch_assoc()) {
	// Get new category ID
	$category = $row["forum_id"];
	$category = $mysqli->query("SELECT * FROM {$prefix}forums WHERE id = {$category}");
	$category->data_seek(0);
	$category = $category->fetch_assoc();
	$category = $category["forum_name"];
	$category_id = $queries->getWhere("forums", array("forum_title", "=", $category));
	$category_id = $category_id[0]->id;

	// Get original poster's ID
	$poster = "'" . escape($row["poster"]) . "'";
	$poster = $mysqli->query("SELECT * FROM {$prefix}users WHERE username = {$poster}");
	$poster = $poster->fetch_assoc();
	$poster_id = $poster["id"];
	
	$poster = null;
	$category = null;

	$queries->create("topics", array(
		"id" => $row["id"],
		"forum_id" => $category_id,
		"topic_title" => htmlspecialchars($row["subject"]),
		"topic_creator" => $poster_id,
		"topic_last_user" => $row["last_poster_id"],
		"topic_date" => date('Y-m-d H:i:s', $row["posted"]),
		"topic_reply_date" => date('Y-m-d H:i:s', $row["last_post"]),
		"topic_views" => $row["num_views"],
		"locked" => $row["closed"],
		"sticky" => $row["sticky"]
	));
}

$modernbb_topics = null;

/*
 *  Posts
 */
 
/*
 *  Query the database
 */

$modernbb_posts = $mysqli->query("SELECT * FROM {$prefix}posts");

$modernbb_posts->data_seek(0);
 
/*
 *  Loop through the posts
 */
while ($row = $modernbb_posts->fetch_assoc()) {
	// Get new category ID
	$topic = $row["topic_id"];
	$topic = $mysqli->query("SELECT * FROM {$prefix}topics WHERE id = {$topic}");
	$topic = $topic->fetch_assoc();
	$category = $topic["forum_id"];
	$category = $mysqli->query("SELECT * FROM {$prefix}forums WHERE id = {$category}");
	$category = $category->fetch_assoc();
	$category = $category["forum_name"];
	$category_id = $queries->getWhere("forums", array("forum_title", "=", $category));
	$category_id = $category_id[0]->id;

	$topic = null;
	$category = null;

	$queries->create("posts", array(
		"id" => $row["id"],
		"forum_id" => $category_id,
		"topic_id" => $row["topic_id"],
		"post_creator" => $row["poster_id"],
		"post_content" => htmlspecialchars($row["message"]),
		"post_date" => date('Y-m-d H:i:s', $row["posted"])
	));
}

$modernbb_posts = null;
 
/*
 *  Bans
 */
 
/*
 *  Query the database
 */

$modernbb_bans = $mysqli->query("SELECT * FROM {$prefix}bans");

$modernbb_bans->data_seek(0);
 
/*
 *  Loop through the bans
 */
while ($row = $modernbb_bans->fetch_assoc()) {
	// Get user ID
	$banned_user = "'" . escape($row["username"]) . "'";
	$banned_user = $mysqli->query("SELECT * FROM {$prefix}users WHERE username = {$banned_user}");
	$banned_user = $banned_user->fetch_assoc();
	$user_id = $banned_user["id"];
	
	$banned_user = null;

	$queries->update("users", $user_id, array(
		"isbanned" => 1,
		"active" => 0
	));
	
	$queries->create("infractions", array(
		"type" => 1,
		"punished" => $user_id,
		"staff" => $row["ban_creator"],
		"reason" => htmlspecialchars($row["message"]),
		"infraction_date" => date('Y-m-d H:i:s')
	));
}

$modernbb_bans = null;
 
/*
 *  Reports
 */
 
/*
 *  Query the database
 */

$modernbb_reports = $mysqli->query("SELECT * FROM {$prefix}reports");

$modernbb_reports->data_seek(0);
 
/*
 *  Loop through the reports
 */
while ($row = $modernbb_reports->fetch_assoc()) {
	// Get user ID
	$reported_post = $row["post_id"];
	$reported_post = $mysqli->query("SELECT * FROM {$prefix}posts WHERE id = {$reported_post}");
	$reported_post = $reported_post->fetch_assoc();
	
	if(!count($reported_post)){
		continue; 
	} else {
		$user_id = $reported_post["poster_id"];
	}
	
	$reported_post = null;
	
	// See if report is closed
	if(empty($row["zapped"])){
		$status = 0;
		$updated_by = $row["reported_by"];
	} else {
		$status = 1;
		$updated_by = $row["zapped_by"];
	}

	$queries->create("reports", array(
		"id" => $row["id"],
		"type" => 0,
		"reporter_id" => $row["reported_by"],
		"reported_id" => $user_id,
		"status" => $status,
		"date_reported" => date('Y-m-d H:i:s', $row["created"]),
		"date_updated" => date('Y-m-d H:i:s', $row["created"]),
		"report_reason" => htmlspecialchars($row["message"]),
		"updated_by" => $updated_by,
		"reported_post" => $row["post_id"],
		"reported_post_topic" => $row["topic_id"]
	));
}

$modernbb_bans = null;
 
?>