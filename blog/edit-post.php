<?php
require_once 'lib/common.php';
require_once 'lib/edit-post.php';
require_once 'lib/view-post.php';

session_start();

if (!is_loggedin())
{
	redirect_exit('index.php');
}

// empty default
$title = $body = '';

$pdo = get_pdo();

$post_id = null;
if (isset( $_GET['post_id']))
{
	$post = get_post_row( $pdo, $_GET['post_id'] );
	if ( $post )
	{
		$post_id = $_GET['post_id'];
		$title = $post['title'];
		$body = $post['body'];
	}
}

$errors = array();

if ($_POST)
{
	$title = $_POST['post-title'];
	if ( !$title )
	{
		$errors[] = 'The post must have a title';
	}
	
	$body = $_POST['post-body'];
	if ( !$body )
	{
		$errors[] = 'The post must have body';
	}
	
	if ( !$errors )
	{
		// if editing or adding
		if ( $post_id )
		{
			if (is_user_post( $post ))
			{
				edit_post( $pdo, $title, $body, $post_id);
			}
			else
			{
				redirect_exit('index.php');
			}
		}
		else
		{
			$user_id = get_auth_id( $pdo );
			$post_id= add_post( $pdo, $title, $body, $user_id );
			
			if ($post_id === false)
			{
				$errors[] = 'Post operation failed';
			}
		}	
	}

	if ( !$errors )
	{
		redirect_exit('edit-post.php?post_id='. $post_id);
	}
	
}

?>

<!DOCTYPE html>
<html>
	<head>
		<title>A blog application | New post</title>
		<?php require 'templates/head.php' ?>
	</head>
	<body>
		<?php require 'templates/top-menu.php' ?>
		
		<?php if (isset( $_GET['post_id'] )): ?>
			<h1>Edit post</h1>
		<?php else: ?>
			<h1>New post</h1>
		<?php endif ?>
		
		<?php if ( $errors ): ?>
			<div class="error box">
				<ul>
					<?php foreach ($errors as $error): ?>
						<li><?php echo $error ?></li>
					<?php endforeach ?>
				</ul>
			</div>
		<?php endif ?>
		
		<form method="post" class="post-form user-form">
			<div>
				<label for="post-title">Title:</label>
				<input
					id="post-title"
					name="post-title"
					type="text"
					value="<?php echo html_escape( $title ) ?>"
				/>
			</div>
			<div>
				<label for="post-body">Body:</label>
                <textarea
                    id="post-body"
                    name="post-body"
                    rows="12"
                    cols="70"
                ><?php echo html_escape( $body ) ?></textarea>
            </div>
            <div>
                <input
                    type="submit"
                    value="Save post"
                />
				<a href="index.php">Cancel</a>
            </div>
        </form>
    </body>
</html>

		