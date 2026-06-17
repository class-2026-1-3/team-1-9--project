<?php

include "./includes/db.php";

$type = $_GET['type'] ?? 'worry';


$sql="
SELECT *
FROM posts
WHERE board_type=?
ORDER BY post_id DESC
";

$stmt=mysqli_prepare(
    $conn,
    $sql
);

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $type
);

mysqli_stmt_execute(
    $stmt
);

$result=mysqli_stmt_get_result(
    $stmt
    
);

?>

<h1>게시판</h1>

<?php
if($type == "worry"){
    echo "<h1>고민게시판</h1>";
}else{
    echo "<h1>자유게시판</h1>";
}
?>

<?php

while(
    $post=mysqli_fetch_assoc(
        $result
    )
){

?>
<div>
    <h3>
        <a href="
        post.php?id=<?=
        $post['post_id']
        ?>
        ">

        <?=$post['title']?>

        </a>
    </h3>
    <p>
        <?= $post['board_type'] ?>
    </p>
    <hr>
</div>

<?php

}

?>