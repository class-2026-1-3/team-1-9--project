<?php

session_start();

include "./includes/db.php";

if(!isset($_SESSION['user_id'])){
    die("로그인 필요");
}

$post_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

$sql="
SELECT *
FROM post_likes
WHERE post_id=?
AND user_id=?
";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $post_id,
    $user_id
);

mysqli_stmt_execute($stmt);

$result=mysqli_stmt_get_result($stmt);

if(mysqli_fetch_assoc($result)){

    $sql="
    DELETE FROM post_likes
    WHERE post_id=?
    AND user_id=?
    ";

}else{

    $sql="
    INSERT INTO post_likes
    (
        post_id,
        user_id
    )
    VALUES
    (
        ?,
        ?
    )
    ";
}

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $post_id,
    $user_id
);

mysqli_stmt_execute($stmt);

header(
    "Location: post.php?id=".$post_id
);