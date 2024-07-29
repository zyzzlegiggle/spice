<div class="top-menu">
	<div class="menu-options">
		<?php if (is_loggedin()): ?>
			<a href="index.php">Home</a>
			|
			<a href="list-posts.php">
				<?php if (get_auth_user() == 'admin'): ?>
					All Posts
				<?php else: ?>
					My Posts
				<?php endif ?>
			</a>
            |
			<a href="edit-post.php">New Post</a>
			|
			Hello <?php echo html_escape(get_auth_user()) ?>
			<a href="logout.php">Log out</a>
		<?php else: ?>
			<a href="login.php">Log in</a>
			|
			<a href="signup.php">Sign Up</a>
		<?php endif ?>
	</div>
</div>