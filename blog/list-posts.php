<?php 
require_once 'lib/common.php';
require_once 'lib/list-posts.php';

session_start();

if (!is_loggedin())
{
	redirect_exit('index.php');
}

$pdo = get_pdo();

if ( $_POST )
{
	$delete_response = $_POST['delete-post'];
	if ( $delete_response )
	{
		$keys = array_keys( $delete_response );
		$delete_post_id = $keys[0];
		if ( $delete_post_id )
		{
			delete_post( $pdo, $delete_post_id );
			redirect_exit('list-posts.php');
		}
	}
}

$posts = (get_auth_user() == 'admin') ? get_all_post( $pdo ) : get_user_post( $pdo );
?>
<!DOCTYPE html>
<html>
	<head>
		<title>A blog application | Blog posts</title>
		<?php require 'templates/head.php' ?>
	</hhad>
	<body>
		<?php require 'templates/top-menu.php' ?>
		<h1>Post list</h1>
		<p>You have <?php echo count($posts) ?> posts.
		<?php if (count( $posts ) > 0): ?>
			<form method="post">
			
				
				<table id ="post-list">
				<thead>
					<tr>
						<th>Title</th>
						<th>Creation date</th>
						<th>Comments</th>
						<th />
						<th />
					</tr>
				</thead>
					<tbody>
						<?php foreach ( $posts as $post ): ?>
							<tr>
								<td>
									<a href="view-post.php?post_id=<?php echo $post['id']?>">
									<?php echo html_escape( $post['title'] ) ?>
								</td>
								<td>
									<?php echo convert_sqldate( $post['created_at'] ) ?>
								</td>
								<td>
									<?php echo $post['comment_count'] ?>
								 <td>
									<a href="edit-post.php?post_id=<?php echo $post['id']?>">Edit</a>
								</td>
								<td>
									<input
										type="submit"
										name="delete-post[<?php echo $post['id']?>]"
										value="Delete"
									/>
								</td>
							</tr>
						<?php endforeach ?>
					</tbody>
				</table>
			</form>
		<?php endif ?>
    </body>
</html>

