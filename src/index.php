<?php
    include(__DIR__ . "/includes/db.php");

    include(__DIR__ . "/components/header.php");

    include(__DIR__ . "/components/hero.php");

    include(__DIR__ . "/components/top_posts.php");

    include(__DIR__ . "/components/stats.php");

    include(__DIR__ . "/components/footer.php");

    session_start();

    if(isset($_SESSION['nickname'])){
        echo $_SESSION['nickname']. "님";
    }else{
        echo "로그인 안됨";
    }
    
?>

<!DOCTYPE html>
<html lang="kr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anonymous Community</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                익명 고민 상담소
            </div>
            <ul class="menu">
                <li class="menu-li"><a href="index.php">홈</a></li>
                <li class="menu-li">고민 게시판</li>
                <li class="menu-li">자유 게시판</li>
                <li class="menu-li">오늘의 한마디</li>
                <li class="menu-li">공지사항</li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="hero">
            <h2 class ="hero-title">
                혼자 고민하지 마세요,<br>
                여기, <span class="highlight">당신의 이야기</span>를 들어줄<br>
                누군가가 있어요.
            </h2>
            <p>익명으로 고민을 나누고 공감받아보세요.</p>

            <button>고민 털어놓기</button>
            <button>고민 보러가기</button>
        </section>
        
        <section>
            <div>
                <h2>지금, 인기 있는 고민</h2>
                <a href="">더보기</a>
            </div>

            <div>

            </div>
        </section>
    </main>
</body>
</html>