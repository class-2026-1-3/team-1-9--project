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

    // Ensure connection uses UTF-8 to prevent garbled non-ASCII text
    mysqli_set_charset($conn, 'utf8mb4');

    // Auto-migration for category column
    $check_col = mysqli_query($conn, "SHOW COLUMNS FROM `posts` LIKE 'category'");
    if ($check_col && mysqli_num_rows($check_col) == 0) {
        mysqli_query($conn, "ALTER TABLE `posts` ADD COLUMN `category` VARCHAR(50) DEFAULT NULL AFTER `board_type`");
    }

    // Auto-migration for views column
    $check_views = mysqli_query($conn, "SHOW COLUMNS FROM `posts` LIKE 'views'");
    if ($check_views && mysqli_num_rows($check_views) == 0) {
        mysqli_query($conn, "ALTER TABLE `posts` ADD COLUMN `views` INT DEFAULT 0 AFTER `likes`");
    }

    // Auto-create notices table if missing
    $check_notices = mysqli_query($conn, "SHOW TABLES LIKE 'notices'");
    if ($check_notices && mysqli_num_rows($check_notices) == 0) {
        $create_notices_sql = "CREATE TABLE IF NOT EXISTS notices (
            notice_id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            content TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL,
            user_id INT NOT NULL
        )";
        mysqli_query($conn, $create_notices_sql);
    }

    ?>
