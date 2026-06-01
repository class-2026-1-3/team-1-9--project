<?php
    include "./includes/db.php";
    if($_SERVER["REQUEST_METHOD"]=="POST"){

        $login_id = $_POST['login_id'];
        $password = $_POST['password'];
        $nickname = $_POST['nickname'];

        $password = password_hash(
            $password,
            PASSWORD_DEFAULT
        );
    
    
    $sql="
    INSERT INTO users
    (login_id,password,nickname)
    VALUES
    (?,?,?)
    ";

    $stmt=mysqli_prepare(
        $conn,
        $sql
    );

    mysqli_stmt_bind_param(
        $stmt,
        "sss",
        $login_id,
        $password,
        $nickname
    );

    mysqli_stmt_execute($stmt);

    echo "회원가입 성공!";
}
?>
 
 <form method="POST">

 <input
 name="login_id"
 placeholder="아이디"
 required
 >

 <input
 type="password"
 name="password"
 placeholder="비밀번호"
 required
 >

 <input
 name="nickname"
 placeholder="닉네임"
 required
 >

 <button>
    회원가입
</button>

</form> 