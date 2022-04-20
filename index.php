<?php

require __DIR__.'/views/header.php';

require __DIR__.'/views/navigation.php';

isLoggenIn();

// Logged in user:
$userId = (int) $_SESSION['user']['id'];

$user = getUserById((int) $userId, $pdo);

?>

<main>
    <section class="feed">
        <?php if (isset($_SESSION['errors'][0])) { ?>
            <div class="message">
                <p>
                    <?php
                    displayErrorMessage();
                    ?>
                </p>
            </div>
        <?php } elseif (isset($_SESSION['messages'][0])) { ?>
            <div class="message">
                <p>
                    <?php
                    displayConfirmationMessage();
                    ?>
                </p>
            </div>
        <?php } ?>

        <?php
        $posts = getAllPosts($pdo);
        $comments = getAllComments($pdo);
        foreach ($posts as $post) {
            $postId = $post['id'];
            $postUser = (int) $post['user_id']; ?>
            <div class="feed__post">
                <div class="post__header bblg w-full">
                    <div class="post__header-profile">
                        <?php if ($post['avatar']) { ?>
                            <img src="/app/users/avatar/<?php echo $post['avatar']; ?>" alt="Profile image">
                        <?php } else { ?>
                            <img src="/app/users/avatar/placeholder2.png">
                        <?php } ?>
                        <h4><?php echo $post['first_name'].' '.$post['last_name']; ?></h4>
                    </div>

                    <?php
                    isFollowing($pdo, (int) $userId, (int) $postUser); ?>
                    <div class="profile__follow">
                        <form action="/app/users/follow.php" method="post">
                            <input type="hidden" name="following" value="<?php echo $postUser; ?>"></input>
                            <!--Only view follow/unfollow button on other users-->
                            <?php if ($_SESSION['user']['id'] != $postUser) { ?>
                                <?php if (isFollowing($pdo, (int) $userId, (int) $postUser)) { ?>
                                    <button name="follower" value="<?php echo $userId; ?>">Unfollow</button>
                                <?php } elseif (!isFollowing($pdo, (int) $userId, (int) $postUser)) { ?>
                                    <button name="follower" value="<?php echo $userId; ?>">Follow</button>
                                <?php } ?>
                            <?php } ?>
                        </form>
                    </div>
                </div>

                <div class="post__image">
                    <img src="/app/posts/uploads/<?php echo $post['post_image'] ?>" alt="Post image">
                </div>
               

                <?php
                global $userThatHasLiked;
                $likedPost = userHasLiked($pdo, (int) $userId, (int) $postId);
                if(is_array($likedPost)){
                    $userThatHasLiked = $likedPost['liked_by_user_id'];
                }
                ?>
                
                <div class="post__text-content">
                    <div class="post__text-content-header w-full">
                        <div class="flex">
                            <form action="/app/posts/like.php" method="post">
                                <button class="like-button" name="like-post" value="<?php echo $post['id']; ?>">
                                    <?php if ($userThatHasLiked == $userId) { ?>
                                        <img src="/views/icons/liked.svg" alt="Post is liked">
                                    <?php } else { ?>
                                        <img src="/views/icons/heart.svg" alt="Post is not liked">
                                    <?php } ?>
                                </button>
                            </form>

                            <img src="/views/icons/comment.svg" alt="Comment" class="comment-buttons">
                        </div>

                        <div class="date">
                            <p>
                                <?php
                                $postedDate = $post['date'];
            echo postedAgo($postedDate); ?>
                            </p>
                        </div>
                    </div>

                    <div class="number-of-likes">
                        <?php
                        $likes = numberOfLikes($pdo, (int) $postId); ?>
                        <?php foreach ($likes as $like) { ?>
                            <?php if ($like == 0) { ?>
                                <h5>Be the first one to like this post</h5>
                            <?php } elseif ($like == 1) { ?>
                                <h5><?php echo $like; ?> person likes this</h5>
                            <?php } else { ?>
                                <h5><?php echo $like; ?> people likes this</h5>
                            <?php } ?>
                        <?php } ?>
                    </div>

                    <div class="post__caption w-full">
                        <h5><?php echo $post['first_name'].' '.$post['last_name']; ?></h5>
                        <p><?php echo $post['post_caption']; ?></p>
                    </div>

                    <div class="post__comments w-full">
                        
                    </div>


                    <div>
                    <form action="/app/posts/insert-comment.php" method="post" class="submit-comment-form">
                            <input type="hidden" name="first-name" value="<?php echo $user['first_name']; ?>">
                            <input type="hidden" name="post-id" value="<?php echo $post['id']; ?>">
                            <input type="text" name="comment">
                            <button type="submit"> Comment</button>
                    </form>
                    </div>
                    
                    
                    <!-- Comment-conatiner -->
                    
                    <div class="comment-container">
                        
                        <div class="comments-posted">
                            <?php $comments = getCommentsById($pdo, (int) $postId); ?>
                            <?php if ($comments) { ?>
                                <?php foreach ($comments as $comment) { ?>
                                    <?php $commentId = $comment['id']; ?>

                                    <div class="comment-containers">
                                        <div class="username-comment">
                                            <h5 class="username"><?php echo $comment['username']; ?> </h5>
                                            <p class="content"><?php echo $comment['content']; ?></p>
                                        </div>

                                    <div class="username-comment">   
                                        <?php if ($user['first_name'] == $comment['username']) { ?>

                                            <form action="/app/posts/edit-comment.php" method="post">
                                                <input type="hidden" name="post-id-edit" value="<?php echo $comment['id']; ?>">
                                                <input class="edit-input" type="text" name="edit-comment">
                                                <button type="submit">Edit</button>
                                            </form>

                                            <form action="/app/posts/delete-comment.php" method="post">
                                                <input type="hidden" name="post-id-delete" value="<?php echo $comment['id']; ?>">
                                                <button type="submit">Delete</button>
                                            </form>

                                        <?php } ?>
                                
                                        <div class="date"></div>
                                        <p>
                                            <?php
                                            $postedDate = $post['date'];
                                            echo postedAgo($postedDate); ?>
                                        </p>
                                    </div>


                                    <!-- Them likes -->
                                    
                                <div class="like-comments">
                                        <form action="/app/posts/like.php" method="post">

                                            <button class="like-comment-button" name="like-comment" value="<?php echo $comment['id']; ?>">
                                                <?php $userHasLikedComment = userHasLikedComment($pdo, $userId, $commentId);
                                                if (is_array($userHasLikedComment)) {
                                                    $userThatHasLikedComment = $userHasLikedComment['comment_liked_by_user_id']
                                                    ?>
                                                    <img src="/views/icons/liked.svg" alt="Comment is liked">
                                                <?php } else { ?>
                                                    <img src="/views/icons/heart.svg" alt="Comment is not liked">
                                                <?php } ?>

                                            </button>
                                        </form>
                                    </div>

                    <div class="number-of-comment-likes">
                        <?php
                        $commentLikes = numberOfLikesComment($pdo, (int) $commentId); ?>
                        <?php foreach ($commentLikes as $commentLike) { ?>
                            <?php if ($commentLike == 0) { ?>
                                <h5 class="smaller-like">Be the first one to like this comment</h5>
                            <?php } elseif ($commentLike == 1) { ?>
                                <h5 class="smaller-like"><?php echo $commentLike; ?> person likes this</h5>
                            <?php } else { ?>
                                <h5 class="smaller-like"><?php echo $commentLike; ?> people likes this</h5>
                            <?php } ?>
                        <?php } ?>
                    </div>


                    <!-- Reply-conatiner -->

                    <div class="reply-form">
                    <div class="replies-container">
                        <form action="/app/posts/insert-reply.php" method="post" class="replies-comment-form">
                            <input type="hidden" name="first-name" value="<?php echo $user['first_name']; ?>">
                            <input type="hidden" name="comment-id" value="<?php echo $comment['id']; ?>">
                            <input type="text" name="reply">
                            <button type="submit" class="reply-button">Reply</button>
                        </form>
                    
                        <?php $replies = getRepliesById($pdo, (int) $commentId); ?>
                        <?php if ($replies) { ?>
                            <?php foreach ($replies as $reply) { ?>
                                <div class="comment-containers">
                                    <div class="username-comment">
                                        <h5 class="username"><?php echo $reply['username']; ?> replied: </h5>
                                        <p class="content"><?php echo $reply['content']; ?></p>
                                    </div>
                                </div>
                        </div>
                     </div>
                     </div>

                     <?php } ?>
                        <?php } ?>
                        </div>
                    </div>

                                </div>

                                    </div>

                    <p id="output"></p>
                </div>
            </div>
        <?php
        } 
    }
}

?>?>
        
    </section>
</main>

<?php require __DIR__.'/views/footer.php'; ?>