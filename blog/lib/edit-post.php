<?php

function add_post( PDO $pdo, $title, $body, $user_id )
{
	$sql = '
		INSERT INTO post (title, body, user_id, created_at)
		VALUES (:title, :body, :user_id, :created_at)
	';
	
	$stmt = $pdo->prepare($sql);
	if ( !$stmt )
	{
		throw new Exception('couldnt prepare post insert query');
	}
	
	$result = $stmt->execute(
		array(
			'title' => $title,
			'body' => $body,
			'user_id' => $user_id,
			'created_at' => get_sqldatenow(),
		)
	);
	if ( !$result )
	{
		throw new Exception('couldnt insert new post query');
	}
	
	return $pdo->lastInsertId();
}

function edit_post( PDO $pdo, $title, $body, $user_id )
{
	$sql = '
		UPDATE post 
		SET
			title = :title,
			body = :body
		WHERE id = :post_id
	';
	$stmt = $pdo->prepare($sql);
	if ( !$stmt )
	{
        throw new Exception('Could not prepare post update query');
    }
	
	$result = $stmt->execute(
		array(
			'title' => $title,
			'body' => $body,
			'post_id' => $post_id
		)
	);
	if ($result === false)
    {
        throw new Exception('Could not run post update query');
    }
    return true;
}

?>