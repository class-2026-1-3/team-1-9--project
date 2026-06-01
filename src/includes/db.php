<?php

    $conn = mysqli_connect(
        "mysql",
        "user",
        "1234",
        "anonymous_db"
    );

    if(!$conn){
        die("DB 연결 실패");
    }

    ?>