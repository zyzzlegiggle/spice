<?php
require_once 'lib/common.php';
require_once 'lib/install.php';

// store stuff in session, so its not remade to self
session_start();

// run installer if responding to the form (check if human not search engine)
if ($_POST)
{
	// install Blog
	$pdo = get_pdo();
	list($row_counts, $error) = install_blog( $pdo );
	$password = '';
	if ( !$error )
	{
		$username = 'admin';
		list( $password, $error) = create_user( $pdo, $username );
	}
	
	$_SESSION['count'] = $row_counts;
	$_SESSION['error'] = $error;
	$_SESSION['password'] = $password;
	$_SESSION['logged_in_username'] = $username;
	$_SESSION['try-install'] = true;
	// redirect from post to get
	
	redirect_exit('install.php');
}

// chech if successful
$attempted = false;
if (isset($_SESSION['try-install']))
{
	$attempted = true;
	$count = $_SESSION['count'];
	$error = $_SESSION['error'];
	$username = $_SESSION['logged_in_username'];
	$password = $_SESSION['password'];
	
	// reset
	unset($_SESSION['count']);
	unset($_SESSION['error']);
	unset($_SESSION['try-install']);
	unset($_SESSION['logged_in_username']);
}	


?>


<!DOCTYPE html>
<html>
    <head>
        <title>Blog installer</title>
		<?php require 'templates/head.php' ?>
    </head>
    <body>
        <?php if ( $attempted ): ?>
			<?php if ( $error ): ?>
				<div class="error-box">
					<?php echo $error ?>
				</div>
			<?php else: ?>
				<div class="success box">
					The database and demo data was installed
				<?php // report count for each table ?>
				<?php foreach (array('post', 'comment') as $table_name): ?>
					<?php if (isset($count[$table_name])): ?>
						<?php echo $count[$table_name] ?>
						<?php echo $table_name ?>
						were created.
					<?php endif ?>
				<?php endforeach ?>
				
				<?php //report new password ?>
				The new '<?php echo html_escape( $username ) ?>' password is
				<span class="install-password"><?php echo html_escape( $password ) ?></span>
				(copy to clipboard if necessary)
				</div>
				<p>
					<a href="index.php">View The Blog</a>,
					or <a href="install.php">install again</a>.
			<?php endif ?>
		<?php else: ?>
		
			<p>Click the install button to reset the database.</p>
			
			<form method="post">
				<input 
					name="install"
					type="submit"
					value="Install"
				/>
			</form>
		<?php endif ?>
		
    </body>
</html>
