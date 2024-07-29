<div class="comment-list">
<form 
	action="view-post.php?action=delete-comment&amp;post_id=<?php echo $post_id ?>&amp;"
	method="post"
	class="comment-list"
>
	<h3><?php echo $comment_count ?> comments</h3>

	<?php foreach (get_comments($pdo, $post_id) as $comment): ?>
		<div class="comment">
			<div class="comment-meta">
				Comment from
				<?php echo html_escape($comment['name']) ?>
				on
				<?php echo convert_sqldate($comment['created_at']) ?>
				<?php if (is_loggedin()): ?>
					<input
						type="submit"
						name="delete-comment[<?php echo $comment['id'] ?>]"
						value="Delete"
					/>
				<?php endif ?>
			</div>
			<div class="comment-body">
				<?php // This is already escaped ?>
				<?php echo convert_newline($comment['text']) ?>
			</div>
		</div>
	<?php endforeach ?>
</form>