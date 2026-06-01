<?php

session_start();

include "./includes/db.php";

if($_SERVER["REQUEST_METHOD"]=="POST"){

    $login_id = $_POST['login_id'];
    $password = $_POST['password'];

    $sql="
    SELECT * 
    FROM users
    WHERE login_id=?
    ";

    $stmt=mysqli_prepare(
        $conn,
        $sql
    );

    mysqli_stmt_bind_param(
        $stmt,
        "s",
        $login_id
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result(
        $stmt
    );

    $user = mysqli_fetch_assoc(
        $result
    );

    if(
        $user &&
        password_verify(
            $password,
            $user['password']
        )
    ){
        $_SESSION['user_id']
        =
        $user['user_id'];

        $_SESSION['nickname']
        =
        $user['nickname'];

        echo "로그인 성공";
    }else{
        echo "로그인 실패";
    }

}

?>

<form method="POST">

<input
name = "login_id"
placeholder="아이디"
required
>

<input
type = "password"
name = "password"
placeholder = "비밀번호"
required
>

<button>
    로그인
</button>

</form>
