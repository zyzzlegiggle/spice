<?php
require_once 'lib/common.php';

function install_blog(PDO $pdo)
{
	
	// get couple useful project paths
	$root = get_root();
	$database = get_path_db();

	$error = '';

	// avoid resetting database if exist
	if ( is_readable($database) && filesize($database) > 0 )
	{
		$error = 'Please delete the existing database manually 
		before installing it afresh';
	}

	// create empty file for database
	if ( !$error )
	{
		$createdOk = @touch($database);
		if (!$createdOk)
		{
			$error = sprintf(
				'Could not create the database, please allow the server to create new files in \'%s\'',
				dirname($database)
			);
		}
	}

	// grab sql commands
	if ( !$error )
	{
		$sql = file_get_contents($root . '/data/init.sql');
		
		if ( $sql == false )
		{
			$error = 'cannot find sql commands file';
		}
	}
	// connect to new database
	if ( !$error )
	{
		$result = $pdo->exec($sql);
		if ( !$result )
		{
			$error = 'couldnt run sql: ' . print_r($pdo->errorInfo(), true);
		}
	}

	// see how many row created
	$count = array();
	foreach(array('post', 'comment') as $table_name)
	{
		if ( !$error )
		{
			$sql = 'SELECT COUNT(*) AS c FROM ' . $table_name;
			$stmt = $pdo->query($sql);
			if ($stmt)
			{
				// store each in its own arrays
				$count[$table_name] = $stmt->fetchColumn();
			}
		}
	}
	
	return array( $count, $error );
}

function create_user( PDO $pdo, $username, $length = 10 )
{
	// create random password algorithm
	$alphabet = range(ord('A'), ord('z'));
    $alphabetLength = count($alphabet);
    $password = '';
    for($i = 0; $i < $length; $i++)
    {
        $letterCode = $alphabet[rand(0, $alphabetLength - 1)];
        $password .= chr($letterCode);
    }
    $error = '';
	
	// insert to database
	$sql = '
		UPDATE user
		SET password = :password, created_at = :created_at, is_enabled = 1
		WHERE username = :username
	';
	$stmt = $pdo->prepare( $sql );
	if ( !$stmt )
	{
		$error = 'couldnt prepare update';
	}
	
	if ( !$error )
	{
		// create password hash
		$hash = password_hash( $password, PASSWORD_DEFAULT );
		if ( !$hash )
		{
			$error = 'password hashing failed';
		}
	}
	if ( !$error )
	{
		$result = $stmt->execute(
			array(
				'username'=> $username,
				'password'=> $hash,
				'created_at'=> get_sqldatenow(),
			)
		);
		if ( !$result )
		{
			$error = 'couldnt run user password updatecreation';
		}
	}
	
	if ( $error )
	{
		$password = '';
	}
	
	return array( $password, $error);
}
?>