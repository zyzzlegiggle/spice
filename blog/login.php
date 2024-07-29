<?php
require_once 'lib/common.php';

if (version_compare(PHP_VERSION, '8.0.0') < 0 )
{
	throw new Exception ('this system needs PHP 8 or later');
}

session_start();

if (is_loggedin())
{
	redirect_exit('index.php');
}

$username = '';
if ($_POST)
{
	
	$pdo = get_pdo();
	
	$username = $_POST['username'];
	$try_login = try_login( $pdo, $username, $_POST['password'] );
	if ( $try_login )
	{
		login( $pdo, $username );
		redirect_exit('index.php');
	}
}
?>

<!DOCTYPE html>
<html>
	<head>
		<title>
			A blog application | Login
		</title>
		<?php require 'templates/head.php' ?>
	</head>
	<body>
		<?php require 'templates/title.php' ?>
		
		<?php if ( $username ): ?>
			<div class="error box">
				The username or password is incorrect, try again
			</div>
		<?php endif ?>

		<p>Login here:</p>
		
		<form method="post" class="user-form">
			<div>
				<label for="username">
				Username:
				</label>
				<input type="text" id="username" name="username" value="<?php echo html_escape($username) ?>" />
			</div>
            <div>
                <label for="password">
                    Password:
                </label>
                <input
                    type="password"
                    id="password"
                    name="password"
                />
            </div>

			<input type="submit" name="submit" value="Login" />
		</form>
	</body>
</html>