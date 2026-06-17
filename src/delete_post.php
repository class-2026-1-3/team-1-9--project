<?php
session_start();

include "./includes/db.php";

if(!isset($_SESSION['user_id'])){
    die('로그인 필요');
}

if (!isset($_GET['id'])){
    die('잘못된 접근');
}

$id = $_GET['id'];

$sql="
DELETE FROM posts
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

header(
    "Location: board.php"
);

exit;