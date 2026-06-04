<?php

session_start();

include "./includes/db.php";

if(!isset($_SESSION['user_id'])) {
    die("로그인 필요");
}

if($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = $_POST['title'];

    $content = $_POST['content'];

    $board_type = $_POST['board_type'];

    $user_id = $_SESSION['user_id'];

    $sql="
    INSERT INTO posts
    (
        title,
        content,
        board_type,
        user_id
    )
    
    VALUES

    (
        ?,
        ?,
        ?,
        ?
    )
    ";

    $stmt = mysqli_prepare(
        $conn,
        $sql
    );

    mysqli_stmt_bind_param(
        $stmt,
        "sssi",
        $title,
        $content,
        $board_type,
        $user_id
    );

    mysqli_stmt_execute(
        $stmt
    );

    echo "작성 완료";
}

?>

<form method = "POST">

<input
name="title"
placeholder="제목"
required
>

<br>

<textarea
name = "content"
placeholder = "내용"
required
></textarea>

<br>

<select 
name = "board_type"
>

<option value = "worry">

고민게시판

</option>

<option value = "free">

자유개시판

</option>

</select>

<br>

<button>

작성

</button>

</form>