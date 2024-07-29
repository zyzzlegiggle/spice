<?php
require_once 'lib/common.php';
require_once 'lib/view-post.php';

session_start();

// Get the post ID
if (isset($_GET['post_id']))
{
    $post_id = $_GET['post_id'];
}
else
{
    // So we always have a post ID var defined
    $post_id = 0;
}

// Connect to the database, run a query, handle errors
$pdo = get_pdo();
$row = get_post_row($pdo, $post_id);
$comment_count = $row['comment_count'];

// If the post does not exist, let's deal with that here
if (!$row)
{
    redirect_exit('index.php?not-found=1');
}

$errors = null;
if ($_POST)
{
	switch ( $_GET['action'] )
	{
		case 'add-comment':
			$comment_data = array(
				'name' => $_POST['comment-name'],
				'website' => $_POST['comment-website'],
				'text' => $_POST['comment-text'],
			);
			$errors = handle_add_comment( $pdo, $post_id, $comment_data );
			break;
		case 'delete-comment':
			$delete_response = $_POST['delete_comment'];
			handle_delete_comment( $pdo, $post_id, $delete_response );
			break;
	}    
}
else
{
    $comment_data = array(
        'name' => '',
        'website' => '',
        'text' => '',
    );
}

?>
<!DOCTYPE html>
<html>
    <head>
        <title>
            A blog application |
            <?php echo html_escape($row['title']) ?>
        </title>
        <?php require 'templates/head.php' ?>
    </head>
    <body>
        <?php require 'templates/title.php' ?>

        <div class="post">
            <h2>
                <?php echo html_escape($row['title']) ?>
            </h2>
            <div class="date">
                <?php echo convert_sqldate($row['created_at']) ?>
            </div>
				
            <?php // This is already escaped, so doesn't need further escaping ?>
            <?php echo convert_newline($row['body']) ?>
        </div>

        <?php require 'templates/list-comments.php' ?>
		
		<?php // we use $comment_data ?>
        <?php require 'templates/comment-form.php' ?>
    </body>
</html>
