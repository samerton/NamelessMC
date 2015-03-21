<?php 
/*
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

// Set page name for sidebar
$page = "admin-pages";

require('inc/includes/html/library/HTMLPurifier.auto.php'); // HTML Purifier for page content

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

if(Input::exists()) {
	if(Token::check(Input::get('token'))) {
		$validate = new Validate();
		$validation = $validate->check($_POST, array(
			'url' => array(
				'required' => true,
				'min' => 1,
				'max' => 20
			),
			'title' => array(
				'required' => true,
				'min' => 1,
				'max' => 30
			),
			'content' => array(
				'required' => true,
				'min' => 5,
				'max' => 20480
			)
		));
		
		if($validation->passed()){
			if($_GET["page"] == 1){
				$url = "/help"; // Can't change the URL for the help page
				$title = "Help";
			} else {
				$url = Input::get('url');
				$title = Input::get('title');
			}

			try {
				$queries->update("custom_pages", $_GET["page"], array(
					"url" => htmlspecialchars($url),
					"title" => htmlspecialchars($title),
					"content" => htmlspecialchars(Input::get('content'))
				));
			} catch(Exception $e){
				die($e->getMessage());
			}
		} else {
			$error = '<div class="alert alert-warning"><p><strong>Unable to complete action</strong>.</p><p>Please ensure you have entered an URL between 1 and 20 characters long, a page title between 1 and 30 characters long, and page content between 5 and 20480 characters long.</p></div>';
		}
	}
}

$token = Token::generate();

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

    <title><?php echo $sitename; ?> &bull; Admin Custom Pages</title>
	
	<?php require("inc/templates/header.php"); ?>

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
				<?php if(!isset($_GET["page"])) { ?>
				<h2>Custom Pages</h2>
				Click on a page to edit it.<br /><br />
				<?php 
				$pages = $queries->getAll("custom_pages", array("id", "<>", "0")); 
				foreach($pages as $page){
				?>
				<a href="/admin/custom_pages/?page=<?php echo $page->id; ?>"><?php echo htmlspecialchars($page->title); ?></a><br />
				<?php 
				}
				?>
				<?php 
				} else {
					$page = $queries->getWhere("custom_pages", array("id", "=", $_GET["page"]));
					if(!count($page)){
						Redirect::to("/admin/custom_pages");
						die();
					}
				?>
				<h2>Page: <?php echo htmlspecialchars($page[0]->title); ?></h2>
				<strong>URL:</strong> http://<?php echo $_SERVER['SERVER_NAME'] . htmlspecialchars($page[0]->url); ?><br /><br />
				<?php
				if(isset($error)){
					echo $error;	
				}
				?>
				<form action="" method="post">
				  <div class="form-group">
				    <label for="url">Page URL</label>
				    <input class="form-control" type="text" name="url" id="url" value="<?php echo htmlspecialchars($page[0]->url); ?>" />
				  </div>
				  <div class="form-group">
				    <label for="title">Page Title</label>
					<input class="form-control" type="text" name="title" id="title" value="<?php echo htmlspecialchars($page[0]->title); ?>" />
				  </div>
				  <strong>Page content</strong><br />
				  <textarea rows="10" name="content" id="content_editor">
				  <?php 
			      // Initialise HTML Purifier
				  $config = HTMLPurifier_Config::createDefault();
				  $config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
				  $config->set('URI.DisableExternalResources', false);
				  $config->set('URI.DisableResources', false);
				  $config->set('HTML.Allowed', 'u,p,b,i,small,blockquote,span[style],span[class],p,strong,em,li,ul,ol,div[align],br,img');
				  $config->set('CSS.AllowedProperties', array('text-align', 'float', 'color','background-color', 'background', 'font-size', 'font-family', 'text-decoration', 'font-weight', 'font-style', 'font-size'));
				  $config->set('HTML.AllowedAttributes', 'href, src, height, width, alt, class, *.style');
				  $purifier = new HTMLPurifier($config);
				  echo $purifier->purify(htmlspecialchars_decode($page[0]->content)); 
				  ?>
				  </textarea>
				  <input type="hidden" name="token" value="<?php echo $token; ?>">
				  <br /><br />
				  <input type="submit" class="btn btn-primary" value="Submit">
				</form>
				<?php
				}
				?>
			</div>
		</div>
      </div>	  

      <hr>

	  <?php require("inc/templates/footer.php"); ?> 
	  
    </div> <!-- /container -->
		
	<?php require("inc/templates/scripts.php"); ?>
	<script src="/assets/js/ckeditor.js"></script>
	<script type="text/javascript">
		CKEDITOR.replace( 'content_editor', {
			// Define the toolbar groups as it is a more accessible solution.
			toolbarGroups: [
				{"name":"basicstyles","groups":["basicstyles"]},
				{"name":"links","groups":["links"]},
				{"name":"paragraph","groups":["list","align"]},
				{"name":"insert","groups":["insert"]},
				{"name":"styles","groups":["styles"]},
				{"name":"about","groups":["about"]}
			],
			// Remove the redundant buttons from toolbar groups defined above.
			removeButtons: 'Anchor,Styles,Specialchar,Font,About,Flash,Iframe'
		} );
	</script>

  </body>
</html>