<?php

require __DIR__ . '/views/header.php';

require __DIR__ . '/views/navigation.php';

isLoggenIn();

// Logged in user:
$userId = (int) $_SESSION['user']['id'];



$user = getUserById((int) $userId, $pdo);


?>

<main>
    <section class="feed">
        <?php if (isset($_SESSION['errors'][0])) : ?>
            <div class="message">
                <p>
                    <?php
                    displayErrorMessage();
                    ?>
                </p>
            </div>
        <?php elseif (isset($_SESSION['messages'][0])) : ?>
            <div class="message">
                <p>
                    <?php
                    displayConfirmationMessage();
                    ?>
                </p>
            </div>
        <?php endif; ?>

        <?php
        $posts = getAllPosts($pdo);
        foreach ($posts as $post) :
            $postId = $post['id'];
            $postUser = (int) $post['user_id'];
        ?>
            <div class="feed__post">
                <div class="post__header bblg w-full">
                    <div class="post__header-profile">
                        <?php if ($post['avatar']) : ?>
                            <img src="/app/users/avatar/<?php echo $post['avatar']; ?>" alt="Profile image">
                        <?php else : ?>
                            <img src="/app/users/avatar/placeholder2.png">
                        <?php endif; ?>
                        <h4><?php echo $post['first_name'] . ' ' . $post['last_name']; ?></h4>
                    </div>

                    <?php
                    isFollowing($pdo, (int) $userId, (int) $postUser);
                    ?>
                    <div class="profile__follow">
                        <form action="/app/users/follow.php" method="post">
                            <input type="hidden" name="following" value="<?php echo $postUser; ?>"></input>
                            <!--Only view follow/unfollow button on other users-->
                            <?php if ($_SESSION['user']['id'] != $postUser) : ?>
                                <?php if (isFollowing($pdo, (int) $userId, (int) $postUser)) : ?>
                                    <button name="follower" value="<?php echo $userId; ?>">Unfollow</button>
                                <?php elseif (!isFollowing($pdo, (int) $userId, (int) $postUser)) : ?>
                                    <button name="follower" value="<?php echo $userId; ?>">Follow</button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <div class="post__image">
                    <img src="/app/posts/uploads/<?php echo $post['post_image'] ?>" alt="Post image">
                </div>
               

                <?php
                $likedPost = userHasLiked($pdo, (int) $userId, (int) $postId);
                $userThatHasLiked = $likedPost['liked_by_user_id'];
                ?>
                <div class="post__text-content">
                    <div class="post__text-content-header w-full">
                        <div class="flex">
                            <form action="/app/posts/like.php" method="post">
                                <button class="like-button" name="like-post" value="<?php echo $post['id']; ?>">
                                    <?php if ($userThatHasLiked == $userId) : ?>
                                        <img src="/views/icons/liked.svg" alt="Post is liked">
                                    <?php else : ?>
                                        <img src="/views/icons/heart.svg" alt="Post is not liked">
                                    <?php endif; ?>
                                </button>
                            </form>

                            <img src="/views/icons/comment.svg" alt="Comment" class="comment-buttons">
                        </div>

                        <div class="date">
                            <p>
                                <?php
                                $postedDate = $post['date'];
                                echo postedAgo($postedDate);
                                ?>
                            </p>
                        </div>
                    </div>

                    <div class="number-of-likes">
                        <?php
                        $likes = numberOfLikes($pdo, (int) $postId);
                        ?>
                        <?php foreach ($likes as $like) : ?>
                            <?php if ($like == 0) : ?>
                                <h5>Be the first one to like this post</h5>
                            <?php elseif ($like == 1) : ?>
                                <h5><?php echo $like; ?> person likes this</h5>
                            <?php else : ?>
                                <h5><?php echo $like; ?> people likes this</h5>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                    <div class="post__caption w-full">
                        <h5><?php echo $post['first_name'] . ' ' . $post['last_name']; ?></h5>
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
                            <?php $comments = getCommentsById($pdo, (int) $postId);?>
                            <?php if ($comments): ?>
                                <?php foreach ($comments as $comment): ?>

                                    <div class="comment-containers">
                                        <div class="username-comment">
                                            <h5 class="username"><?php echo $comment['username']; ?> </h5>
                                            <p class="content"><?php echo $comment['content']; ?></p>
                                        </div>

                                    <div class="username-comment">   
                                        <?php if ($user['first_name'] == $comment['username']): ?>

                                            <form action="/app/posts/edit-comment.php" method="post">
                                                <input type="hidden" name="post-id-edit" value="<?php echo $comment['comment_id']; ?>">
                                                <input class ="edit-input" type="text" name="edit-comment">
                                                <button type="submit">Edit</button>
                                            </form>

                                            <form action="/app/posts/delete-comment.php" method="post">
                                                <input type="hidden" name="post-id-delete" value="<?php echo $comment['comment_id']; ?>">
                                                <button type="submit">Delete</button>
                                            </form>

                                        <?php endif; ?>
                                        
                                    </div>
                                </div>

                            <?php endforeach; ?>
                        <?php endif; ?>

                        </div>


                     </div>

                    
                    <p id="output"></p>
                </div>
            </div>
        <?php endforeach; ?>
    </section>
</main>

<?php require __DIR__ . '/views/footer.php'; ?>