<?php

session_start();

include "./includes/db.php";

if(!isset($_SESSION['user_id'])){
    die("로그인 필요");
}

if(!isset($_GET['id'])){
    die("잘못된 접근");
}

$id = $_GET['id'];

$sql="
SELECT *
FROM posts
WHERE post_id=?
AND user_id=?
";

$stmt=mysqli_prepare(
    $conn,
    $sql
);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $id,
    $_SESSION['user_id']
);

mysqli_stmt_execute(
    $stmt
);

$result=mysqli_stmt_get_result(
    $stmt
);

$post=mysqli_fetch_assoc(
    $result
);

if(!$post){
    die("수정 권한이 없습니다.");
}

if($_SERVER["REQUEST_METHOD"]=="POST"){

    $title = $_POST['title'];
    $content = $_POST['content'];

    $sql="
    UPDATE posts
    SET
        title=?,
        content=?
    WHERE
        post_id=?
    AND
        user_id=?
    ";

    $stmt=mysqli_prepare(
        $conn,
        $sql
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ssii",
        $title,
        $content,
        $id,
        $_SESSION['user_id']
    );

    mysqli_stmt_execute(
        $stmt
    );

    header(
        "Location: post.php?id=".$id
    );

    exit;
}

<h1>게시글 수정</h1>

<form method="POST">

<input
name="title"
value="<?=$post['title']?>"
required
>

<br><br>

<textarea
name="content"
required
><?=$post['content']?></textarea>

<br><br>

<button>
수정 완료
</button>

</form>