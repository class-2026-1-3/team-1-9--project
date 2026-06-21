<?php

session_start();

include "./includes/db.php";

if(!isset($_SESSION['user_id'])){
    die("로그인 필요");
}

$user_id = $_SESSION['user_id'];

$sql="
SELECT *
FROM posts
WHERE user_id=?
ORDER BY post_id DESC
";

$stmt=mysqli_prepare(
    $conn,
    $sql
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

mysqli_stmt_execute(
    $stmt
);

$result=mysqli_stmt_get_result(
    $stmt
);

$sql="
SELECT
comments.*,
posts.title
FROM comments
JOIN posts
ON comments.post_id=posts.post_id
WHERE comments.user_id=?
ORDER BY comments.comment_id DESC
";

$stmt_comments=mysqli_prepare(
    $conn,
    $sql
);

mysqli_stmt_bind_param(
    $stmt_comments,
    "i",
    $user_id
);

mysqli_stmt_execute(
    $stmt_comments
);

$comments=mysqli_stmt_get_result(
    $stmt_comments
);
?>


<h1>마이페이지</h1>

<p>
닉네임 :
<?= $_SESSION['nickname'] ?>
</p>

<hr>

<h2>내가 작성한 글</h2>

<?php
while(
    $post=mysqli_fetch_assoc(
        $result
    )
){
?>

<div>

<a href="post.php?id=<?=$post['post_id']?>">

<?=$post['title']?>

</a>

</div>

<?php
}
?>
<hr>

<h2>내가 작성한 댓글</h2>

<?php
while(
    $comment=mysqli_fetch_assoc(
        $comments
    )
){
?>

<div>

<p>
게시글 :
<?=$comment['title']?>
</p>

<p>
댓글 :
<?=$comment['content']?>
</p>

</div>

<hr>

<?php
}
?>
<a href="logout.php">
로그아웃
</a>