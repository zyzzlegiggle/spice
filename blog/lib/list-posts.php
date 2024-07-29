<?php 

function delete_post( PDO $pdo, $post_id )
{
	$sqls = array(
        // Delete comments first, to remove the foreign key objection
        'DELETE FROM
            comment
        WHERE
            post_id = :id',
        // Now we can delete the post
        'DELETE FROM
            post
        WHERE
            id = :id',
    );

	foreach ($sqls as $sql)
	{
		
		$stmt = $pdo->prepare($sql);
		if ($stmt === false)
		{
			throw new Exception('There was a problem preparing this query');
		}
		$result = $stmt->execute(
			array('id' => $post_id, )
		);
		if ( $result == false )
		{
			break;
		}
	}
    return $result !== false;
}
?>