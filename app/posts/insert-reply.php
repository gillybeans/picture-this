<?php

require __DIR__ . '/../autoload.php';

if (isset($_POST['reply'], $_POST['comment-id'], $_POST['first-name'])) {
    if ($_POST['reply'] == '') {
        redirect('/');
    }

    $commentId = $_POST['comment-id'];
    $reply = trim($_POST['reply']);
    $firstName = $_POST['first-name'];
    $dateAndTime = date('Y-m-d-h-i-s-a');

    $statement = $pdo->prepare('INSERT INTO replies (comment_id, content, username, date_posted) VALUES(:id, :reply, :fistname, :date)');

    sqlQueryError($pdo, $statement);

    $statement->execute([
        'id'       => $commentId,
        'reply'  => $reply,
        'fistname' => $firstName,
        'date'     => $dateAndTime,
    ]);
}
redirect('/');
