 <?php
require_once 'lib/common.php';

session_start();

// Connect to the database, run a query, handle errors
$pdo = get_pdo();
$posts = get_all_post( $pdo );

$not_found = isset($_GET['not-found']);

?>
<!DOCTYPE html>
<html>
    <head>
        <title>A blog application</title>
        <?php require 'templates/head.php' ?>
    </head>
    <body>
        <?php require 'templates/title.php' ?>

        <?php if ($not_found): ?>
            <div class="error box">
                Error: cannot find the requested blog post
            </div>
        <?php endif ?>

        <div class="post-list">
            <?php foreach ($posts as $post): ?>
                <div class="post-synopsis"> 
                    <h2>
                        <?php echo html_escape($post['title']) ?>
                    </h2>
                    <div class="meta">
						Author: <?php echo html_escape( $post['author'] ) ?>
						|
                        <?php echo convert_sqldate($post['created_at']) ?>
						
                        (<?php echo $post['comment_count'] ?> comments)
                    </div>
                    <p>
                        <?php echo html_escape($post['body']) ?>
                    </p>
                    <div class="post-controls">
                        <a
                            href="view-post.php?post_id=<?php echo $post['id'] ?>"
                        >Read more...</a>
						<?php if (is_user_post( $post )): ?>
							|
							<a href="edit-post.php?post_id=<?php echo $post['id'] ?>">
							Edit
							</a>
						<?php endif ?>
                    </div>
                </div>
            <?php endforeach ?>
        </div>

    </body>
</html>
