<?php

include "./includes/db.php";

if(!isset($_GET['id'])){
    die("잘못된 접근입니다.");
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