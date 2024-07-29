<?php 
function get_post_row( PDO $pdo, $post_id )
{
	$stmt = $pdo->prepare(
		'SELECT
        id, title, created_at, body,
		(SELECT COUNT(*) FROM comment WHERE comment.post_id = post.id) comment_count
		FROM
			post
		WHERE
			id = :id
	'
	);
	if ( !$stmt )
	{
		throw new Exception('There was a problem preparing this query');
	}
	$result = $stmt->execute(
		array('id' => $post_id)
	);
	if ( !$result )
	{
		throw new Exception('There was a problem running this query');
	}
	// get row
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	if ( !$row )
	{
		throw new Exception('No post found');
	}
	
	return $row;
}

function add_comment( PDO $pdo, $post_id, array $comment_data)
{
	$errors = array();
	
	if (empty( $comment_data['name'] ))
	{
		$errors['name'] = 'A name is required';
	}
	if (empty( $comment_data['text'] ))
	{
		$errors['text'] = 'comment is required';
	}
	
	if ( !$errors )
	{
		$sql = "
			INSERT INTO comment (name, website, text, created_at, post_id)
			VALUES (:name, :website, :text, :created_at, :post_id)
		";
		$stmt = $pdo->prepare($sql);
		if ( !$stmt )
		{
			throw new Exception('cannot insert comment');
		}
		
		$result = $stmt->execute(
			array_merge($comment_data, array('post_id' => $post_id, 
			'created_at' => get_sqldatenow(), ))
		);
		
		
		
		
		if ( !$result )
		{
			$error_info = $stmt->errorInfo();
			if( $error_info )
			{
				 $errors[] = $error_info[2];
			}
		}
	}
	return $errors;
}	

function handle_add_comment ( PDO $pdo, $post_id, array $comment_data )
{
	$errors = add_comment(
        $pdo,
        $post_id,
        $comment_data
    );

    // If there are no errors, redirect back to self and redisplay
    if (!$errors)
    {
        redirect_exit('view-post.php?post_id=' . $post_id);
    }
	
	return $errors;
}

function delete_comment( PDO $pdo, $post_id, $comment_id )
{
	$sql = '
		DELETE FROM comment
		WHERE
			post_id = :post_id
			AND id = :comment_id
	';
	$stmt = $pdo->prepare($sql);
	if ( !$stmt )
	{
        throw new Exception('There was a problem preparing this query');
    }
    $result = $stmt->execute(
        array(
            'post_id' => $postId,
            'comment_id' => $commentId,
        )
    );
    return $result !== false;
}

function handle_delete_comment( PDO $pdo, $post_id, array $delete_response )
{
	if (is_loggedin())
	{
		$keys = array_keys($delete_response);
		$delete_comment_id = $keys[0];
		if ( $delete_comment_id )
		{
			delete_comment( $pdo, $post_id, $delete_comment_id );
		}
		redirect_exit('view-post.php?post_id=' . $post_id);
	}
}

?>
