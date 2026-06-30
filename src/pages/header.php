<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$script_name = basename($_SERVER['SCRIPT_NAME']);
$current_page = 'index';
if ($script_name == 'board.php') {
    $current_page = ($_GET['type'] ?? 'worry') == 'free' ? 'free' : 'worry';
} elseif ($script_name == 'login.php') {
    $current_page = 'login';
} elseif ($script_name == 'register.php') {
    $current_page = 'register';
} elseif ($script_name == 'mypage.php') {
    $current_page = 'mypage';
} elseif (in_array($script_name, ['notice.php', 'notice_view.php', 'notice_edit.php'])) {
    $current_page = 'notice';
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="익명으로 자유롭게 고민을 털어놓고 따뜻한 공감과 조언을 받아보세요.">
    <title>익명고민상담소</title>
    <!-- Pretendard Font -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="stylesheet" as="style" crossorigin href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/variable/pretendardvariable-dynamic-subset.min.css">
    <!-- Fallback Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Styles -->
    <?php
        $style_ver = file_exists(__DIR__ . '/../css/style.css') ? filemtime(__DIR__ . '/../css/style.css') : time();
    ?>
    <link rel="stylesheet" href="../css/style.css?v=<?= $style_ver ?>">
    <?php if ($script_name == 'index.php'): ?>
        <?php $home_ver = file_exists(__DIR__ . '/../css/home.css') ? filemtime(__DIR__ . '/../css/home.css') : time(); ?>
        <link rel="stylesheet" href="../css/home.css?v=<?= $home_ver ?>">
    <?php elseif (in_array($script_name, ['board.php', 'post.php', 'write.php', 'edit_post.php'])): ?>
        <?php $board_ver = file_exists(__DIR__ . '/../css/board.css') ? filemtime(__DIR__ . '/../css/board.css') : time(); ?>
        <link rel="stylesheet" href="../css/board.css?v=<?= $board_ver ?>">
    <?php endif; ?>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
<?php
// Refresh session nickname from DB to avoid showing stale/garbled value
if (isset($_SESSION['user_id']) && isset($conn)) {
    $sid = (int)$_SESSION['user_id'];
    $qr = mysqli_prepare($conn, "SELECT nickname FROM users WHERE user_id = ? LIMIT 1");
    if ($qr) {
        mysqli_stmt_bind_param($qr, "i", $sid);
        mysqli_stmt_execute($qr);
        $r = mysqli_stmt_get_result($qr);
        if ($row = mysqli_fetch_assoc($r)) {
            $_SESSION['nickname'] = $row['nickname'];
        }
    }
}
?>
    <header>
        <div class="container navbar">
            <a href="index.php" class="logo">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="logo-icon"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path><path d="M12 8c-1-1.2-2.5-1.8-4-1.8A4.5 4.5 0 0 0 3.5 10.7c0 3.3 4.5 7.3 8.5 9.3 4-2 8.5-6 8.5-9.3a4.5 4.5 0 0 0-4.5-4.5c-1.5 0-3 0.6-4 1.8Z" fill="currentColor"></path></svg>
                익명고민상담소
            </a>
            <ul class="menu">
                <li class="<?= $current_page == 'index' ? 'active' : '' ?>"><a href="index.php">홈</a></li>
                <li class="<?= $current_page == 'worry' ? 'active' : '' ?>"><a href="board.php?type=worry">고민 게시판</a></li>
                <li class="<?= $current_page == 'free' ? 'active' : '' ?>"><a href="board.php?type=free">자유 게시판</a></li>
                <li class="<?= $current_page == 'index' ? 'active' : '' ?>"><a href="index.php#today-quote">오늘의 한마디</a></li>
                <li class="<?= $current_page == 'notice' ? 'active' : '' ?>"><a href="notice.php">공지사항</a></li>
            </ul>
            <div class="nav-right">
                <button class="search-trigger" onclick="location.href='board.php?type=worry'">
                    <i data-lucide="search" style="width: 20px; height: 20px;"></i>
                </button>
                <?php if(isset($_SESSION['nickname'])): ?>
                    <a href="mypage.php" class="login-btn">마이페이지</a>
                    <a href="logout.php" class="signup-btn">로그아웃</a>
                <?php else: ?>
                    <a href="login.php" class="login-btn">로그인</a>
                    <a href="register.php" class="signup-btn">회원가입</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
