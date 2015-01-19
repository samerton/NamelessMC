<?php 
/*
 *	Made by dwilson390
 *  http://minersrealm.org
 *
 *  License: MIT
 */
 
/*
 *  Convert WordPress to NamelessMC
 *  
 *  All ways Converts:
 *     - Users [Done]
 *     - Posts
 *  ...and if bbPress is installed additionally converts:
 *     - Forums
 *     - Topics and replies
 */
 
if(!isset($queries)){
	$queries = new Queries();
}
 
/*
 *  First, check the database connection specified in the form submission
 */
 
$mysqli = new mysqli(Input::get("db_address"), Input::get("db_username"), Input::get("db_password"), Input::get("db_name"));

if($mysqli->connect_errno) {
	Redirect::to('install.php?step=convert&convert=yes&from=wordpress&error=true');
	die();
}

/*
 *  Get the table prefix
 */

$prefix = '';

if(!empty(Input::get('db_prefix'))){
	$prefix = escape(Input::get('db_prefix'));
}

/*
 *  Users
 */
 
/*
 *  Query the database
 */

$wordpress_users = $mysqli->query("SELECT * FROM {$prefix}users");


$wordpress_users->data_seek(0);

/*
 *  Loop through the users
 */
while ($row = $wordpress_users->fetch_assoc()) {
		// Get the user's group info
#		$group = $row["ID"];
#		$group = $mysqli->query("SELECT * FROM {$prefix}usermeta WHERE ID='{$group}' && meta_value ='wp_capabilities'");
		
#		if(strstr($row["wp_capabilities"], "administrator") !== FALSE){ 
#			$group_id = 2; // Admin
#		} else {
			$group_id = 1; //The string "administrator" is not found in the users meta so they must be a member.
#			}
		
		/*
		 * At this point the admin account has already been created with an ID of 1 so we must increment it.
		 */
		
		$id = $mysqli->query("SELECT * FROM 'users' ORDER BY 'id' DESC LIMIT 1");
		$id;
		$user_id = $id['id'];
		echo $user_id;
		$user_id++;
		
		$group = null;
		
		$code = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 60);
		
		$queries->create("users", array(
			"id" => $row['ID']+ 1,
			"username" => htmlspecialchars($row["user_login"]),
			"password" => htmlspecialchars($row["user_pass"]),
			"salt" => "wordpress",
			"mcname" => htmlspecialchars($row["display_name"]),
			"uuid" => "",
			"joined" => "",
			"group_id" => $group_id,
			"email" => $row["user_email"],
			"lastip" => "",
			"active" => 0,
			"signature" => "",
			"reset_code" => $code,
			"pf_location" => ""
		));	
	}

$wordpress_users = null;

$wordpress_date_registered = NULL;

/*
 *  Posts
 */
 
/*
 * We create a forum to put the posts in
 */
 
$queries->create("categories", array(
	"id" => 1,
	"category_title" => "Old Wordpress Posts",
	"category_description" => "These are the old posts imported from WordPress. Please note, this is the parent forum which is required by NamelessMC, if you wish to delete this, please move the posts out of the forum below.",
	"last_post_date" => date('Y-m-d H:i:s'),
	"last_user_posted" => 1,
	"last_topic_posted" => date('Y-m-d H:i:s'),
	"parent" => 0,
	"cat_order" => 1
));
$queries->create("categories", array(
	"id" => 2,
	"category_title" => "Old Wordpress Posts",
	"category_description" => "These are the old posts imported from WordPress. Feel Free to rename this forum or move the posts accordingly.",
	"last_post_date" => date('Y-m-d H:i:s'),
	"last_user_posted" => 1,
	"last_topic_posted" => date('Y-m-d H:i:s'),
	"parent" => 1,
	"cat_order" => 2
));

/*
 *  Query the database
 */
 

$wordpress_posts = $mysqli->query("SELECT * FROM {$prefix}posts WHERE post_type='post'");

$wordpress_posts->data_seek(0);

$loop_no = 1;
 
/*
 *  Loop through the posts
 */
while ($row = $wordpress_posts->fetch_assoc()) {
	$queries->create("topics", array(
		"id" => $row["ID"],
		"category_id" => 2,
		"topic_title" => $row['post_title'],
		"topic_creator" => $row['post_author'] + 1,
		"topic_last_user" => $row['post_author'],
		"topic_date" => $row['post_date'],
		"topic_reply_date" => $row['post_date']
	));
	$queries->create("posts", array(
		"id" => $row["ID"],
		"category_id" => 2,
		"topic_id" => $row['ID'],
		"post_creator" => $row["post_author"] + 1,
		"post_content" => $row["post_content"],
		"post_date" => $row["post_date"]
	));
	/*
	 *  Find all comments, convert them to replies.
	 */
#	$queries->create("topics", array(
#		"category_id" => 2,
#		"topic_title" => $row['post_title'],
#		"topic_creator" => $row['post_author'],
#		"topic_last_user" => $row['post_author'],
#		"topic_date" => $row['post_date'],
#		"topic_reply_date" => "",
#	));
	$inner_loop_no = NULL;
}

$wordpress_posts = null;

 /*
  * Only execute the following code if the user said they had bbPress installed in 'install.php'.
  */

if(null !== Input::get('InputDBCheckbox')){
	/*
	 *  Forums
	 */
	 
	/*
	 *  Query the database
	 */
	 
	$wordpress_forums = $mysqli->query("SELECT * FROM {$prefix}posts WHERE post_type='forum'");

	$wordpress_forums->data_seek(0);

	$n = 1;
	/*
	 *  Loop through the forums
	 */
	while ($row = $wordpress_forums->fetch_assoc()) {
		$queries->create("categories", array(
			"id" => $row["ID"],
			"category_title" => htmlspecialchars($row["post_title"]),
			"category_description" => htmlspecialchars($row["post_content"]),
			"last_post_date" => NULL,
			"last_user_posted" => NULL,
			"last_topic_posted" => NULL,
			"parent" => $row["post_parent"],
			"cat_order" => $n
		));
		$n++;
	}

	$wordpress_forums = null;
	 
	/*
	 *  Topics
	 */
	 
	/*
	 *  Query the database
	 */

	$wordpress_topics = $mysqli->query("SELECT * FROM {$prefix}posts WHERE post_type='topic'");

	$wordpress_topics->data_seek(0);
	 
	/*
	 *  Loop through the topics
	 */
	while ($row = $wordpress_topics->fetch_assoc()) {
		// Get new category ID
#		$category = $row["parent_id"];
#		$category = $mysqli->query("SELECT * FROM {$prefix}posts WHERE id = {$category}");
#		$category->data_seek(0);
#		$category = $category->fetch_assoc();
#		$category = $category["forum_name"];
#		$category_id = $queries->getWhere("categories", array("category_title", "=", $category))[0]->id;

		// Get original poster's ID
#		$poster = "'" . escape($row["poster"]) . "'";
#		$poster = $mysqli->query("SELECT * FROM {$prefix}users WHERE username = {$poster}");
#		$poster = $poster->fetch_assoc();
#		$poster_id = $poster["id"];
		
#		$poster = null;
#		$category = null;

		$queries->create("topics", array(
			"id" => $row["ID"],
			"category_id" => $row['post_parent'],
			"topic_title" => htmlspecialchars($row['post_title']),
			"topic_creator" => $row['post_author'] + 1,
			"topic_last_user" => $row['post_author'],
			"topic_date" => $row["post_date"],
			"topic_reply_date" => $row["post_date"]
		));
		$queries->create("posts", array(
			"id" => $row["ID"],
			"category_id" => $row['post_parent'],
			"topic_id" => $row['ID'],
			"post_creator" => $row['post_author'] + 1,
			"post_content" => $row['post_content'],
			"post_date" => $row["post_date"],
		));
	}

	$wordpress_topics = null;
	
	/*
	 * Topic replies
	 */
	 
	/*
	 * Query the database
	 */
	
	$wordpress_replies = $mysqli->query("SELECT * FROM {$prefix}posts WHERE post_type='reply'");

	$wordpress_replies->data_seek(0);
	
	/*
	 *  Loop through the topic replies
	 */
	while ($row = $wordpress_replies->fetch_assoc()) {
		// Get new category ID
		$topic = $row["post_parent"];
		$topic = $mysqli->query("SELECT * FROM {$prefix}posts WHERE ID={$topic} && post_type='topic'");
		$topic = $topic->fetch_assoc();
		$category = $topic["post_parent"];
#		$category = $mysqli->query("SELECT * FROM {$prefix}posts WHERE  = {$category}");
#		$category = $category->fetch_assoc();
#		$category = $category["forum_name"];
#		$category_id = $queries->getWhere("categories", array("category_title", "=", $category))[0]->id;

		$category_id = $category;

		$topic = null;
		$category = null;

		$queries->create("posts", array(
			"category_id" => $category_id,
			"topic_id" => $row["post_parent"],
			"post_creator" => $row["post_author"] + 1,
			"post_content" => htmlspecialchars($row["post_content"]),
			"post_date" => $row["post_date"]
		));
	}

	$wordpress_replies = null;
	
}

 /*
  * Everything has been imported now lets do a little tidying up!
  */

$forum = new Forum();

$forum->updateCatLatestPosts(); // To update categories' latest posts
$forum->updateTopicLatestPosts(); // To update topics' latest posts

//End of file. Issues? Contact dwilson390 via the Spigot forums.
?>