<?php 
/* 
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

// First, check to see if avatar uploading is enabled..

$avatar_enabled = $queries->getWhere('settings', array('name', '=', 'user_avatars'));
$avatar_enabled = $avatar_enabled[0]->value;

if($avatar_enabled === "true"){
	$image = new SimpleImage();

	if(!$user->isLoggedIn()){
		Redirect::to('/');
		die();
	}

	if(Input::exists()) {
		if(Token::check(Input::get('token'))) {
			
			if(!isset($_FILES['uploaded_avatar']['tmp_name'])){
				// TODO
				echo 'No file chosen - <a href="/user/settings">Back</a>';
				die();
			}
			
			if(file_exists("avatars/" . $user->data()->id . ".jpg")){
				unlink("avatars/" . $user->data()->id . ".jpg");
			} else if(file_exists("avatars/" . $user->data()->id . ".png")){
				unlink("avatars/" . $user->data()->id . ".png");
			} else if(file_exists("avatars/" . $user->data()->id . ".gif")){
				unlink("avatars/" . $user->data()->id . ".gif");
			}
			
			$image->load($_FILES['uploaded_avatar']['tmp_name']);
			$image->resize('100', '100');
			$image->save("avatars/" . $user->data()->id);

			$queries->update("users", $user->data()->id, array(
				"has_avatar" => 1
			));
			
			Redirect::to('/user/settings');
			die();
			
		} else {
			// TODO
			echo 'Invalid token - <a href="/user/settings">Back</a>';
			die();
		}
	} else {
		// TODO
		Redirect::to('/user/settings');
		die();
	}
} else {
	Redirect::to('/user/settings');
	die();	
}

?>