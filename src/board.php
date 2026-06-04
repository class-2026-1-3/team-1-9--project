<?php

include "./includes/db.php";

$sql="
SELECT *
FROM posts
ORDER BY post_id DESC
";

$result=mysqli_query(
    $conn,
    $sql
);

?>

<h1>게시판</h1>

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

        <?= $post['title'] ?>

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