<?php

// Is required in autoload.php

declare(strict_types=1);

if (!function_exists('redirect')) {
    /**
     * Redirect the user to given path.
     *
     * @param string $path
     *
     * @return void
     */
    function redirect(string $path)
    {
        header("Location: ${path}");
        exit;
    }
}

/**
 * Redirects the user to the login page if she/he is not authorized to enter the page without being logged in.
 *
 * @return bool
 */
function isLoggenIn()
{
    if (!isset($_SESSION['user'])) {
        redirect('/login.php');
    }
}

/**
 * Generates a random number
 * Used for profile avatars
 * https://www.php.net/manual/en/function.com-create-guid.php.
 *
 * @return void
 */
function GUID()
{
    if (function_exists('com_create_guid') === true) {
        return trim(com_create_guid(), '{}');
    }

    return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}

/**
 * Function printing SQL error.
 *
 * @param PDO    $pdo
 * @param object $statement
 *
 * @return array
 */
function sqlQueryError($pdo, $statement)
{
    if (!$statement) {
        die(var_dump($pdo->errorInfo()));
    }
}

/////////////////////////// USER ///////////////////////////

/**
 * Returns user data.
 *
 * @param int $userId
 * @param PDO $pdo
 *
 * @return array
 */
function getUserById(int $userId, PDO $pdo)
{
    $statement = $pdo->prepare('SELECT * FROM users WHERE id = :id');

    sqlQueryError($pdo, $statement);

    $statement->execute([
        ':id' => $userId,
    ]);

    $user = $statement->fetch(PDO::FETCH_ASSOC);

    return $user;
}

/////////////////////////// MESSAGE ///////////////////////////

/**
 * Displays error messages if they occur.
 *
 * @return void
 */
function displayErrorMessage()
{
    if (isset($_SESSION['errors'][0])) {
        echo $_SESSION['errors'][0];
        unset($_SESSION['errors']);
    }
}

/**
 * Messages confirming success.
 *
 * @return void
 */
function displayConfirmationMessage()
{
    if (isset($_SESSION['messages'][0])) {
        echo $_SESSION['messages'][0];
        unset($_SESSION['messages']);
    }
}

/////////////////////////// POST ///////////////////////////

/**
 * Returns all posts.
 *
 * @param PDO $pdo
 *
 * @return array
 */
function getAllPosts($pdo)
{
    $statement = $pdo->prepare('SELECT posts.id, posts.user_id, posts.post_image, posts.post_caption, posts.date, users.first_name, users.last_name, users.avatar FROM posts JOIN users ON posts.user_id = users.id ORDER BY posts.date DESC');

    sqlQueryError($pdo, $statement);

    $statement->execute();

    $posts = $statement->fetchAll(PDO::FETCH_ASSOC);

    return $posts;
}

/**
 * Returns post by id.
 *
 * @param int $postId
 * @param PDO $pdo
 *
 * @return array
 */
function getPostById(PDO $pdo, int $postId)
{
    $statement = $pdo->prepare('SELECT * FROM posts WHERE id = :id');

    sqlQueryError($pdo, $statement);

    $statement->execute([
        'id' => $postId,
    ]);

    $post = $statement->fetch(PDO::FETCH_ASSOC);

    return $post;
}

/**
 * Returns post and user by post id.
 *
 * @param int $postId
 * @param PDO $pdo
 *
 * @return array
 */
function getPostAndUserById(PDO $pdo, int $postId)
{
    $statement = $pdo->prepare('SELECT * FROM posts JOIN users ON posts.user_id = users.id WHERE posts.id = :id');

    sqlQueryError($pdo, $statement);

    $statement->execute([
        'id' => $postId,
    ]);

    $post = $statement->fetch(PDO::FETCH_ASSOC);

    return $post;
}

/**
 * Returns post by user.
 *
 * @param PDO $pdo
 * @param int $userId
 *
 * @return array
 */
function getPostsByUser(PDO $pdo, int $userId)
{
    $statement = $pdo->prepare('SELECT * FROM posts WHERE user_id = :id ORDER BY date DESC');

    sqlQueryError($pdo, $statement);

    $statement->execute([
        'id' => $userId,
    ]);

    $postByUser = $statement->fetchAll(PDO::FETCH_ASSOC);

    return $postByUser;
}

if (!function_exists('postedAgo')) {
    /**
     * Calculates time since post was uploaded.
     *
     * @param [type] $datePostWasUploaded
     *
     * @return string
     */
    function postedAgo(string $datePostWasUploaded): string
    {
        $now = date('Y-m-d H:i:s');
        $uploaded = strtotime($now) - strtotime($datePostWasUploaded);
        if ($uploaded >= 172800) {
            $diff = floor($uploaded / 172800).' days ago';
        } elseif ($uploaded >= 86400) {
            $diff = floor($uploaded / 86400).' day ago';
        } elseif ($uploaded >= 3600) {
            $diff = floor($uploaded / 3600).' hours ago';
        } elseif ($uploaded >= 60) {
            $diff = floor($uploaded / 60).' minutes ago';
        } else {
            $diff = 'a few seconds ago';
        }

        return $diff;
    }
}

/////////////////////////// LIKE ///////////////////////////

/**
 * Checking if user has liked post.
 *
 * @param PDO $pdo
 * @param int $userId
 * @param int $postId
 *
 * @return array
 */
function userHasLiked(PDO $pdo, int $userId, int $postId)
{
    $statement = $pdo->prepare('SELECT * FROM likes WHERE post_id = :post_id AND liked_by_user_id = :user_id');

    sqlQueryError($pdo, $statement);

    $statement->execute([
        ':user_id' => $userId,
        ':post_id' => $postId,
    ]);

    $isLiked = $statement->fetch(PDO::FETCH_ASSOC);

    return $isLiked;
}

/**
 * Checking if user has liked comment.
 *
 * @param PDO $pdo
 * @param int $userId
 * @param int $commentId
 *
 * @return array
 */
function userHasLikedComment(PDO $pdo, int $userId, int $commentId)
{
    $statement = $pdo->prepare('SELECT * FROM comments_likes WHERE comment_id = :comment_id AND comment_liked_by_user_id = :user_id');

    sqlQueryError($pdo, $statement);

    $statement->execute([
        ':user_id' => $userId,
        ':comment_id' => $commentId,
    ]);

    $commentIsLiked = $statement->fetch(PDO::FETCH_ASSOC);

    return $commentIsLiked;
}

/**
 * Get number of likes on a post.
 *
 * @param PDO $pdo
 * @param int $postId
 *
 * @return int
 */
function numberOfLikes(PDO $pdo, int $postId)
{
    $statement = $pdo->prepare('SELECT COUNT(post_id) FROM likes where post_id = :post_id');

    sqlQueryError($pdo, $statement);

    $statement->execute([
        ':post_id' => $postId,
    ]);

    $likes = $statement->fetch(PDO::FETCH_ASSOC);

    return $likes;
}

/**
 * Get number of likes on a comment.
 *
 * @param PDO $pdo
 * @param int $commentId
 *
 * @return int
 */
function numberOfLikesComment(PDO $pdo, int $commentId)
{
    $statement = $pdo->prepare('SELECT COUNT(comment_id) FROM comments_likes where comment_id = :comment_id');

    sqlQueryError($pdo, $statement);

    $statement->execute([
        ':comment_id' => $commentId,
    ]);

    $commentLikes = $statement->fetch(PDO::FETCH_ASSOC);

    return $commentLikes;
}


/////////////////////////// FOLLOW ///////////////////////////

/**
 * Checking if logged in user is following another user.
 *
 * @param PDO $pdo
 * @param int $follower
 * @param int $isFollowingUserId
 *
 * @return array
 */
function isFollowing(PDO $pdo, int $follower, int $isFollowingUserId)
{
    $statement = $pdo->prepare('SELECT * FROM follows WHERE user_id = :user_id AND following_user_id = :is_following_id');

    sqlQueryError($pdo, $statement);

    $statement->execute([
        ':user_id'         => $follower,
        ':is_following_id' => $isFollowingUserId,
    ]);

    $isFollowed = $statement->fetch(PDO::FETCH_ASSOC);

    return $isFollowed;
}

/**
 * Count followers.
 *
 * @param PDO $pdo
 * @param int $userId
 *
 * @return void
 */
function followersCount(PDO $pdo, int $follower, int $isFollowingUserId)
{
    $statement = $pdo->prepare('SELECT * FROM follows WHERE user_id = :user_id AND following_user_id = :is_following_id');

    sqlQueryError($pdo, $statement);

    $statement->execute([
        ':user_id' => $follower,
        ':is_following_id' => $isFollowingUserId
    ]);

    $followers = $statement->fetch(PDO::FETCH_ASSOC);

    return $followers;
}

/**
 * Count followings.
 *
 * @param PDO $pdo
 * @param int $follower
 *
 * @return void
 */
function followingsCount(PDO $pdo, int $follower)
{
    $statement = $pdo->prepare('SELECT * FROM follows WHERE user_id = :follower');

    sqlQueryError($pdo, $statement);

    $statement->execute([
        ':follower' => $follower,
    ]);

    $isFollowingCount = $statement->fetchAll(PDO::FETCH_ASSOC);

    $followings = count($isFollowingCount);

    return $followings;
}


/////////////////////////// COMMENTS ///////////////////////////

function getAllComments($pdo)
{

    $statement = $pdo->prepare('SELECT comments.id, comments.post_id, users.first_name, users.last_name, users.avatar FROM comments JOIN users ON comments.username = users.first_name ORDER BY comments.date_posted DESC');

    sqlQueryError($pdo, $statement);

    $statement->execute();

    $comments = $statement->fetchAll(PDO::FETCH_ASSOC);

    return $comments;
}


/**
 * Returns comments by post id.
 *
 * @param int $postId
 * @param PDO $pdo
 *
 * @return array
 */
function getCommentsById(PDO $pdo, int $postId)
{
    $statement = $pdo->prepare('SELECT * FROM comments WHERE comments.post_id = :id ORDER BY date_posted DESC');

    sqlQueryError($pdo, $statement);

    $statement->execute([
        'id' => $postId,
    ]);

    $comments = $statement->fetchAll(PDO::FETCH_ASSOC);

    return $comments;
}

/**
 * Returns replies by comment id.
 *
 * @param int $commentId
 * @param PDO $pdo
 *
 * @return array
 */
function getRepliesById(PDO $pdo, int $commentId)
{
    $statement = $pdo->prepare('SELECT * FROM replies WHERE replies.comment_id = :id ORDER BY date_posted DESC');

    sqlQueryError($pdo, $statement);

    $statement->execute([
        'id' => $commentId,
    ]);

    $replies = $statement->fetchAll(PDO::FETCH_ASSOC);

    return $replies;
}