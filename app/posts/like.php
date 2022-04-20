<?php

// Back-end

declare(strict_types=1);

require __DIR__.'/../autoload.php';

// In this file we add or remove likes on posts in the database

if (isset($_POST['like-post'])) {
    $postId = (int) $_POST['like-post'];
    $userId = (int) $_SESSION['user']['id'];

    if (userHasLiked($pdo, $userId, $postId)) {
        $removeLike = $pdo->prepare('DELETE FROM likes WHERE post_id = :post_id AND liked_by_user_id = :liked_by_user_id');

        if (!$removeLike) {
            die(var_dump($pdo->errorInfo()));
        }

        $removeLike->execute([
            ':post_id'          => $postId,
            ':liked_by_user_id' => $userId,
        ]);
    } else {
        $like = $pdo->prepare('INSERT INTO likes (post_id, liked_by_user_id) VALUES (:post_id, :liked_by_user_id)');

        if (!$like) {
            die(var_dump($pdo->errorInfo()));
        }

        $like->execute([
            ':post_id'          => $postId,
            ':liked_by_user_id' => $userId,
        ]);
    }

    redirect('/');
}

if (isset($_POST['like-comment'])) {
    $commentId = (int) $_POST['like-comment'];
    $userId = (int) $_SESSION['user']['id'];

    if (userHasLikedComment($pdo, $userId, $commentId)) {
        $removeCommentLike = $pdo->prepare('DELETE FROM comments_likes WHERE comment_id = :comment_id AND comment_liked_by_user_id = :comment_liked_by_user_id');

        if (!$removeCommentLike) {
            die(var_dump($pdo->errorInfo()));
        }

        $removeCommentLike->execute([
            ':comment_id'          => $commentId,
            ':comment_liked_by_user_id' => $userId,
        ]);
    } else {
        $commentLike = $pdo->prepare('INSERT INTO comments_likes (comment_id, comment_liked_by_user_id) VALUES (:comment_id, :comment_liked_by_user_id)');

        if (!$commentLike) {
            die(var_dump($pdo->errorInfo()));
        }

        $commentLike->execute([
            ':comment_id'          => $commentId,
            ':comment_liked_by_user_id' => $userId,
        ]);
    }

    redirect('/');
}