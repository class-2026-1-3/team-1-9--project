<?php

session_start();

include "./includes/db.php";

if(!isset($_GET['id'])){
    die('잘못된 접근입니다');
}

$id = $_GET['id'];

$sql="
SELECT *
FROM posts
WHERE post_id=?
";

$stmt=mysqli_prepare(
    $conn,
    $sql
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute(
    $stmt
);

$result = mysqli_stmt_get_result(
    $stmt
);

$post=mysqli_fetch_assoc(
    $result
);

if(!$post){
    die('게시글이 존재하지 않습니다.');
}

$sql="
SELECT COUNT(*) AS like_count
FROM post_likes
WHERE post_id=?
";

$stmt_like=mysqli_prepare(
    $conn,
    $sql
);

mysqli_stmt_bind_param(
    $stmt_like,
    "i",
    $id
);

mysqli_stmt_execute(
    $stmt_like
);

$result_like=mysqli_stmt_get_result(
    $stmt_like
);

$like=mysqli_fetch_assoc(
    $result_like
);

$sql="
SELECT
comments.*,
users.nickname
FROM comments
JOIN users
ON comments.user_id=users.user_id
WHERE post_id=?
ORDER BY comment_id DESC 
";

$stmt_comments=mysqli_prepare(
    $conn,
    $sql
);

mysqli_stmt_bind_param(
    $stmt_comments,
    "i",
    $id
);

mysqli_stmt_execute(
    $stmt_comments
);

$comments=mysqli_stmt_get_result(
    $stmt_comments
);

if(
    $_SERVER["REQUEST_METHOD"] == "POST"
    &&
    isset($_SESSION['user_id'])
){
    $content = $_POST['content'];

    $sql="
    INSERT INTO comments
    (
        post_id,
        user_id,
        content
    )
    VALUES
    (
        ?,
        ?,
        ?
    )
    ";

    $stmt=mysqli_prepare(
        $conn,
        $sql
    );

    mysqli_stmt_bind_param(
        $stmt,
        "iis",
        $id,
        $_SESSION["user_id"],
        $content
    );

    mysqli_stmt_execute(
        $stmt
    );

    header(
        "Location: post.php?id=".$id
    );
    exit;
}
?>

<h1>
    <?=$post['title'] ?>
</h1>

<p>
    <?=$post['content'] ?>
</p>

<p>
    <?=$post['created_at'] ?>
</p>

<?php
if(
    isset($_SESSION['user_id'])
    &&
    $_SESSION['user_id']
    ==
    $post['user_id']
){
    ?>
    <a href="edit_post.php?id=<?=$post['post_id']?>">
    수정
    </a>

    <a
    href="delete_post.php?id=<?=$post['post_id']?>"
    onclick="return confirm('삭제하시겠습니까?')"
    >
    삭제
    </a>

    <?php
}
?>

<hr>

<h2>댓글</h2>

<?php
while(
    $comment=mysqli_fetch_assoc(
        $comments
    )
){
    ?>

    <div>
        <b>
            <?=$comment['nickname']?>
        </b>
        <p>
            <?=$comment['content']?>
        </p>
        <?php
        if(
            isset($_SESSION['user_id'])
            &&
            $_SESSION['user_id']
            ==
            $comment['user_id']
        ){
        ?>

        <a
        href="delete_comment.php?id=<?=$comment['comment_id']?>"
        onclick="return confirm('댓글을 삭제하시겠습니까?')"
        >
        삭제
        </a>

    <?php
    }
    ?>

    <hr>
    </div>
    <?php
}
?>

<?php

if(isset($_SESSION['user_id'])){
    ?>

    <form method="POST">

    <textarea
    name="content"
    placeholder="댓글을 입력하세요"
    required
    ></textarea>

    <br>

    <button>
        댓글 작성
    </button>

</form>

<?php
}
?>
<p>
❤️ <?=$like['like_count']?>
</p>

<a href="like_post.php?id=<?=$post['post_id']?>">
좋아요
</a>