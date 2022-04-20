<?php

require __DIR__.'/../autoload.php';

if (isset($_POST['post-id-edit'], $_POST['edit-comment'])) {
    if ($_POST['edit-comment'] == '') {
        redirect('/');
    }

    $postId = $_POST['post-id-edit'];
    $comment = trim($_POST['edit-comment']);
    $dateAndTime = date('Y-m-d-h-i-s-a');

    $statement = $pdo->prepare('UPDATE comments SET content = :comment, date_posted = :date WHERE id = :id');

    sqlQueryError($pdo, $statement);

    $statement->execute([
        'id'      => $postId,
        'comment' => $comment,
        'date'    => $dateAndTime,
    ]);
}
redirect('/');
