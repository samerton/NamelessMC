<?php 
/* 
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

require('inc/includes/html/library/HTMLPurifier.auto.php'); // HTML Purifier

/*
 *  User must be logged in
 */
if(!$user->isLoggedIn()){
	Redirect::to('/');
	die();
}

/*
 *  Check if page is enabled
 */ 

if($staff_enabled[0]->value === "false"){
	Redirect::to('/');
	die();
}

/* 
 *  Handle input
 */
if(Input::exists()){
	if(Token::check(Input::get('token'))){
		// Get all answers into one string
		unset($_POST['token']);
		
		$content = array();
		foreach($_POST as $key => $item){
			$content[] = array($key, htmlspecialchars($item));
		}
		
		$content = json_encode($content);
		
		$queries->create('staff_apps_replies', array(
			'uid' => $user->data()->id,
			'time' => date('U'),
			'content' => $content
		));
		
		Session::flash('app_success', '<div class="alert alert-success">Application submitted successfully.</div>');
		$completed = 1;
		
	} else {
		// Invalid token
	}
}

if(!isset($completed)){
	// Has the user already submitted an application?
	$already_submitted = $queries->getWhere('staff_apps_replies', array('uid', '=', $user->data()->id));
	foreach($already_submitted as $item){
		if($item->status == 0){
			$completed = 2;
			break;
		}
	}
	$already_submitted = null;
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Samerton">
    <link rel="icon" href="/assets/favicon.ico">

    <title><?php echo $sitename; ?> &bull; Staff Application</title>
	
	<!-- Custom style -->
	<style>
	html {
		overflow-y: scroll;
	}
	</style>
	
	<?php require("inc/templates/header.php"); ?>

  </head>

  <body>

	<?php require("inc/templates/navbar.php"); ?>
	
	<div class="container">
	<?php 
	    if(Session::exists('staff_app')){
		  echo Session::flash('staff_app');
	    }
	?>
		<h2>Staff Application</h2>
		<?php
		if(!isset($completed)){
		?>
		<div class="row">
		  <div class="col-md-5">
			<form action="" method="post">
			<?php 
			// Get all questions
			$questions = $queries->getWhere('staff_apps_questions', array('id', '<>', 0)); 
			
			foreach($questions as $question){
				if($question->type == 3){
					// text area
			?>
			  <label for="<?php echo htmlspecialchars($question->name); ?>"><?php echo htmlspecialchars($question->question); ?></label>
			  <textarea class="form-control" id="<?php echo htmlspecialchars($question->name); ?>" name="<?php echo $question->id; ?>"></textarea><br />
			<?php
				} else if($question->type == 1){
					// dropdown
			?>
			  <label for="<?php echo htmlspecialchars($question->name); ?>"><?php echo htmlspecialchars($question->question); ?></label>
			  <select name="<?php echo $question->id; ?>" id="<?php echo htmlspecialchars($question->name); ?>" class="form-control">
			    <?php
				$options = explode(',', $question->options);
				foreach($options as $option){
				?>
				  <option value="<?php echo htmlspecialchars($option); ?>"><?php echo htmlspecialchars($option); ?></option>
				<?php
				}
				?>
			  </select><br />
			<?php
				} else {
					// normal input tag
			?>
			  <label for="<?php echo htmlspecialchars($question->name); ?>"><?php echo htmlspecialchars($question->question); ?></label>
			  <input type="text" class="form-control" id="<?php echo htmlspecialchars($question->name); ?>" name="<?php echo $question->id; ?>"><br />
			<?php
				}
			}
			
			?>
			  <br />
			  <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
			  <input type="submit" class="btn btn-primary" value="Submit">
			</form>
		  </div>
		</div>
		<?php
		} else {
			if(Session::exists('app_success')){
				echo Session::flash('app_success');
			}
			if($completed === 2){
		?>
		<div class="alert alert-info">You've already submitted an application. Please wait until it is complete before submitting another.</div>
		<?php
			}
		}
		?>
		<hr>
	  <?php require("inc/templates/footer.php"); ?> 
	  
    </div> <!-- /container -->

	<?php require("inc/templates/scripts.php"); ?>	
  </body>
</html>