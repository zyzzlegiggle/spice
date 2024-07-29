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
if ( $_POST )
{
	$pdo = get_pdo();
	$username = $_POST['username'];
	$try_signup = try_signup( $pdo, $username );
	if ( $try_signup )
	{
		signup( $pdo, $username, $_POST['password'] );
		redirect_exit('index.php');
	}
}

?>

<!DOCTYPE html>
<html>
	<head>
		<title>
			A blog application | Sign Up
		</title>
		<?php require 'templates/head.php' ?>
	</head>
	<body>
		<?php require 'templates/title.php' ?>
		
		<?php if ( $username ): ?>
			<div class="error box">
				The username already existed
			</div>
		<?php endif ?>

		<p>Sign Up:</p>
		
		<form method="post" class="user-form">
			<div>
				<label for="username">
				Username:
				</label>
				<input type="text" id="username" name="username"/>
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

			<input type="submit" name="submit" value="Signup" />
		</form>
	</body>
</html>