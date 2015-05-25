<?php 
/*
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

// Set page name for sidebar
$page = "admin-staff_apps";

// Admin check
if($user->isAdmLoggedIn()){
	// Is authenticated
	if($user->data()->group_id != 2){
		Redirect::to('/');
		die();
	}
} else {
	Redirect::to('/admin');
	die();
}

if(Input::exists() && !isset($_GET['action']) && !isset($_GET['question'])){
	// Handle input for allowing moderators to view staff apps
	if(Input::get('allow_moderators') == 1){
		$allow_moderators_input = "true";
	} else {
		$allow_moderators_input = "false";
	}
	$queries->update("settings", 37, array(
		'value' => $allow_moderators_input
	));
	Redirect::to('/admin/staff_apps/');
	die();
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
	<meta name="robots" content="noindex">
    <link rel="icon" href="/favicon.ico">

    <title><?php echo $sitename; ?> &bull; AdminCP Staff Applications</title>
	
	<?php require("inc/templates/header.php"); ?>
	
	<!-- Custom style -->
	<style>
	textarea {
		resize: none;
	}
	</style>

  </head>

  <body>

	<?php require("inc/templates/navbar.php"); ?>

    <div class="container">	
	<?php
		if(Session::exists('adm-alert')){
			echo Session::flash('adm-alert');
		}
	?>
	  <div class="row">
		<div class="col-md-3">
			<?php require('pages/admin/sidebar.php'); ?>
		</div>
		<div class="col-md-9">
			<div class="well">
			  <h2>Staff Applications</h2>
			  <br />
			<?php if(!isset($_GET['action']) && !isset($_GET['question'])){ ?>
			  <em>Disable staff applications from the Pages tab</em><hr>
			<?php
			if(Session::exists('apps_post_success')){
				echo Session::flash('apps_post_success');
			}
			?>
			<strong>Settings:</strong><br />
			<form role="form" action="" method="post">
			  <div class="form-group">
				<label for="InputAllowModerators">Allow moderators to view applications?</label>
				<input type="hidden" name="allow_moderators" value="0" />
				<input name="allow_moderators" value="1" id="InputAllowModerators" type="checkbox"<?php if($allow_moderators === "true"){ echo ' checked'; } ?>>
			    <a class="btn btn-info btn-xs" data-toggle="popover" title="Moderators will only be able to comment; not accept or reject applications"><span class="glyphicon glyphicon-question-sign"></span></a>
			  </div>
			  <input type="submit" class="btn btn-default" value="Submit changes">
			</form>
			
			<br /><br />
			<strong>Questions:</strong> <span class="pull-right"><a href="/admin/staff_apps/?action=new" class="btn btn-primary">New Question</a></span>
			<?php 
			// Get a list of questions
			$questions = $queries->getWhere('staff_apps_questions', array('id', '<>', 0));
			if(count($questions)){
			?>
			<table class="table table-striped">
			  <tr>
			    <th>Name</th>
			    <th>Question</th>
				<th>Type</th>
				<th>Options</th>
			  </tr>
			<?php
				foreach($questions as $question){
			?>
			  <tr>
			    <td><a href="/admin/staff_apps/?question=<?php echo $question->id; ?>"><?php echo ucfirst(htmlspecialchars($question->name)); ?></a></td>
			    <td><?php echo htmlspecialchars($question->question); ?></td>
			    <td><?php echo $queries->convertQuestionType($question->type); ?></td>
				<td><?php 
				$options = explode(',', $question->options);
				foreach($options as $option){
					echo htmlspecialchars($option) . '<br />';
				}
				?></td>
			  </tr>
			<?php
					echo '<a href="/admin/staff_apps/?question=' . $question->id . '"></a><br />';
				}
			} else {
				echo 'No questions defined yet.';
			}
			?>
			</table>
			<?php } else if(isset($_GET['question'])) { 
			// Get the question
			if(!is_numeric($_GET['question'])){
				echo '<script>window.location.replace(\'/admin/staff_apps\');</script>';
				die();
			}
			$question_id = $_GET['question'];
			$question = $queries->getWhere('staff_apps_questions', array('id', '=', $question_id));
			
			// Does the question exist?
			if(!count($question)){
				echo '<script>window.location.replace(\'/admin/staff_apps\');</script>';
				die();
			}
			
			// Deal with the input
			if(Input::exists()){
				if(Token::check(Input::get('token'))){
					$validate = new Validate();
					$validation = $validate->check($_POST, array(
						'name' => array(
							'required' => true,
							'min' => 2,
							'max' => 16
						),
						'question' => array(
							'required' => true,
							'min' => 2,
							'max' => 255
						)
					));
					
					if($validation->passed()){
						// Get options into a string
						$options = str_replace("\n", ',', Input::get('options'));
						
						$queries->update('staff_apps_questions', $question_id, array(
							'type' => Input::get('type'),
							'name' => htmlspecialchars(Input::get('name')),
							'question' => htmlspecialchars(Input::get('question')),
							'options' => htmlspecialchars($options)
						));

						Session::flash('apps_post_success', '<div class="alert alert-info">Question successfully edited</div>');
						echo '<script>window.location.replace(\'/admin/staff_apps\');</script>';
						die();
					}
					
				} else {
					// Invalid token
				}
			}
			
			$question = $question[0];
			?>
			<strong>Editing question '<?php echo htmlspecialchars($question->name); ?>'</strong><br /><br />
			
			<form method="post" action="/admin/staff_apps/?question=<?php echo $question_id; ?>">
			  <label for="name">Question Name</label>
			  <input class="form-control" type="text" name="name" id="name" placeholder="Name" value="<?php echo htmlspecialchars($question->name); ?>">
			  <br />
 			  <label for="question">Question</label>
			  <input class="form-control" type="text" name="question" id="question" placeholder="Question" value="<?php echo htmlspecialchars($question->question); ?>">
			  <br />
			  <label for="type">Type</label>
			  <select name="type" id="type" class="form-control">
			    <option value="1"<?php if($question->type == 1){ ?> selected<?php } ?>>Dropdown</option>
			    <option value="2"<?php if($question->type == 2){ ?> selected<?php } ?>>Text</option>
			    <option value="3"<?php if($question->type == 3){ ?> selected<?php } ?>>Text Area</option>
			  </select>
			  <br />
			  <label for="options">Options - <em>Each option on a new line; can be left empty (dropdowns only)</em></label>
			  <?php
			  // Get already inputted options
			  if($question->options == null){
				  $options = '';
			  } else {
				  $options = str_replace(',', "\n", htmlspecialchars($question->options));
			  }
			  ?>
			  <textarea rows="5" class="form-control" name="options" id="options" placeholder="Options"><?php echo $options; ?></textarea>
			  <br />
			  <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
			  <input type="submit" class="btn btn-primary" value="Edit">
			</form>
			
			
			<?php } else if(isset($_GET['action']) && $_GET['action'] == 'new') { 
			// Deal with the input
			if(Input::exists()){
				if(Token::check(Input::get('token'))){
					$validate = new Validate();
					$validation = $validate->check($_POST, array(
						'name' => array(
							'required' => true,
							'min' => 2,
							'max' => 16
						),
						'question' => array(
							'required' => true,
							'min' => 2,
							'max' => 255
						)
					));
					
					if($validation->passed()){
						// Get options into a string
						$options = str_replace("\n", ',', Input::get('options'));
						
						$queries->create('staff_apps_questions', array(
							'type' => Input::get('type'),
							'name' => htmlspecialchars(Input::get('name')),
							'question' => htmlspecialchars(Input::get('question')),
							'options' => htmlspecialchars($options)
						));

						Session::flash('apps_post_success', '<div class="alert alert-info">Question successfully created</div>');
						echo '<script>window.location.replace(\'/admin/staff_apps\');</script>';
						die();
					}
					
				} else {
					// Invalid token
				}
			}

			?>
			<strong>New Question</strong><br /><br />
			
			<form method="post" action="/admin/staff_apps/?action=new">
			  <label for="name">Question Name</label>
			  <input class="form-control" type="text" name="name" id="name" placeholder="Name">
			  <br />
 			  <label for="question">Question</label>
			  <input class="form-control" type="text" name="question" id="question" placeholder="Question">
			  <br />
			  <label for="type">Type</label>
			  <select name="type" id="type" class="form-control">
			    <option value="1">Dropdown</option>
			    <option value="2">Text</option>
			    <option value="3">Text Area</option>
			  </select>
			  <br />
			  <label for="options">Options - <em>Each option on a new line; can be left empty (dropdowns only)</em></label>
			  <textarea rows="5" class="form-control" name="options" id="options" placeholder="Options"></textarea>
			  <br />
			  <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
			  <input type="submit" class="btn btn-primary" value="Create">
			</form>
			<?php } ?>
			</div>
		</div>
      </div>	  

      <hr>
	  
	  <?php require("inc/templates/footer.php"); ?> 
	  
    </div> <!-- /container -->
		
	<?php require("inc/templates/scripts.php"); ?>
	<script>
	$(function () { $("[data-toggle='popover']").popover({trigger: 'hover', placement: 'top'}); });
	</script>
  </body>
</html>