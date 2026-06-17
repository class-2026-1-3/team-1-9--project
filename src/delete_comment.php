<?php

session_start();

include "./includes/db.php";

if(!isset($_SESSION['user_id'])){
    die("로그인 필요");
}

if(!isset($_GET['id'])){
    die("잘못된 접근");
}

$comment_id = $_GET['id'];

$sql="
DELETE FROM comments
WHERE comment_id=?
AND user_id=?
";

$stmt=mysqli_prepare(
    $conn,
    $sql
);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $comment_id,
    $_SESSION['user_id']
);

mysqli_stmt_execute(
    $stmt
);

header(
    "Location: ".$_SERVER['HTTP_REFERER']
);

exit;