<?php

function get_root()
{
	return realpath(__DIR__. '/..');
}

function get_path_db()
{
	return get_root() . '/data/data.sqlite';
}

function get_dsn()
{
	return 'sqlite:' . get_path_db();
}

function get_pdo()
{
	$pdo = new PDO(get_dsn());
	
	// enable foreign key
	$result = $pdo->query('PRAGMA foreign_keys = ON');
	if ( !$result )
	{
		throw new Exception('couldnt turn on foreign key constraints');
	}
	return $pdo;
}

function html_escape( $html )
{
	return htmlspecialchars($html, ENT_HTML5,'UTF-8');
}

function convert_sqldate($sql_date)
{
	$date = DateTime::createFromFormat('Y-m-d H:i:s', $sql_date);
	if (!$date)
	{
		echo "error";
	}
    return $date->format('d M Y, H:i');

}

function get_comments( $pdo, $post_id )
{
	$sql = '
		SELECT
			id, name, text, created_at, website
		FROM
			comment
		WHERE
			post_id = :post_id
	';
	$stmt = $pdo->prepare( $sql );
	$stmt->execute(
		array('post_id' => $post_id)
	);
	
	return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_sqldatenow()
{
	date_default_timezone_set('UTC');
	return date('Y-m-d H:i:s');
}

function convert_newline( $text )
{
	$escaped = html_escape( $text );
	
	return '<p>' . str_replace("\n", "</p><p>", $escaped) . '</p>' ;
}

function redirect_exit( $script )
{
    // Get the domain-relative URL (e.g. /blog/whatever.php or /whatever.php) and work
    // out the folder (e.g. /blog/ or /).
    $relative_url = $_SERVER['PHP_SELF'];
    $url_folder = substr($relative_url, 0, strrpos($relative_url, '/') + 1);

    // Redirect to the full URL (http://myhost/blog/script.php)
    $host = $_SERVER['HTTP_HOST'];
    $full_url = 'http://' . $host . $url_folder . $script;
    header('Location: ' . $full_url);
    exit();
}

function try_login( PDO $pdo, $username, $password )
{
	$sql = '
		SELECT password
		FROM user
		WHERE username = :username 
		AND is_enabled = 1
	';
	$stmt = $pdo->prepare($sql);
	$stmt->execute(
		array('username' => strtolower($username) )
	);
	
	$hash = $stmt->fetchColumn();
	$success = password_verify( $password, $hash );
	
	return $success;
}

function try_signup( PDO $pdo, $username )
{
	$sql = '
		SELECT username
		FROM user
		WHERE username = :username
	';
	$stmt = $pdo->prepare($sql);
	$stmt->execute(
		array('username' => strtolower($username) )
	);
	$result = $stmt->fetchColumn();
	return !$result; // we want the value to be false (mean row is empty)
}

function signup ( $pdo, $username, $password )
{
	$error = '';
	
	
	$sql = '
		INSERT INTO user (username, password, created_at, is_enabled)
		VALUES (:username, :password, :created_at, :is_enabled);
	';
	$stmt = $pdo->prepare( $sql );
	if ( !$stmt )
	{
		$error = 'couldnt prepare sql';
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
				'username'=> strtolower($username),
				'password'=> $hash,
				'created_at'=> get_sqldatenow(),
				'is_enabled' => 1
			)
		);
		if ( !$result )
		{
			$error = 'couldnt run user account creation';
		}
		else
		{
			session_regenerate_id();
			$_SESSION['logged_in_username'] = get_real_username( $pdo, $username );
		}
	}
		
}

/**
 * Logs the user in
 *
 * For safety, we ask PHP to regenerate the cookie, so if a user logs onto a site that a cracker
 * has prepared for him/her (e.g. on a public computer) the cracker's copy of the cookie ID will be
 * useless.
 *
 * @param string $username
 */
function login( PDO $pdo, $username )
{
	session_regenerate_id();
	
	
	$_SESSION['logged_in_username'] = get_real_username( $pdo, $username );
}

function get_real_username ( PDO $pdo, $username )
{
	$sql = '
		SELECT username
		FROM user
		WHERE username = :username
	';
	$stmt = $pdo->prepare($sql);
	$stmt->execute(
		array('username' => strtolower($username) )
	);
	return $stmt->fetchColumn();
}

function is_loggedin()
{
	return isset($_SESSION['logged_in_username']);
}

function logout()
{
	unset( $_SESSION['logged_in_username'] );
}

function get_auth_user()
{ 
	return is_loggedin() ? $_SESSION['logged_in_username'] : null;
}

function get_auth_id( PDO $pdo )
{
	if (!is_loggedin())
	{
		return null;
	}
	$sql = '
		SELECT id FROM user WHERE username = :username
		AND is_enabled = 1
	';
	
	$stmt = $pdo->prepare( $sql );
	$stmt->execute(
		array('username' => get_auth_user())
	);
	
	return $stmt->fetchColumn();
}

function get_all_post( PDO $pdo )
{
	$stmt = $pdo->query(
    'SELECT
        id, title, created_at, body,
		(SELECT COUNT(*) FROM comment WHERE comment.post_id = post.id) comment_count,
		(SELECT username FROM user WHERE user.id = post.user_id) author
    FROM post
    ORDER BY
        created_at DESC'
	);
	if ($stmt === false)
	{
		throw new Exception('There was a problem running this query');
	}
	
	return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_user_post( PDO $pdo )
{
	$user_id = get_auth_id ( $pdo );
	
	$stmt = $pdo->prepare(
    'SELECT
        id, title, created_at, body,
		(SELECT COUNT(*) FROM comment WHERE comment.post_id = post.id) comment_count
    FROM post
	WHERE user_id = :user_id
    ORDER BY
        created_at DESC'
	);
	
	$stmt->execute(
		array( 'user_id' => $user_id)
	);
	
	
	return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function is_user_post( $post )
{
	if (is_loggedin())
	{	
		$username = get_auth_user();
		if ( $post['author'] == $username )
		{
			return true;
		}
	}
	
	return false;
	
}
?>