<?php
session_start();
include(__DIR__ . "/db.php");

$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_id = $_POST['login_id'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($login_id) || empty($password)) {
        $error_msg = "아이디와 비밀번호를 모두 입력해 주세요.";
    } else {
        $sql = "SELECT * FROM users WHERE login_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $login_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['nickname'] = $user['nickname'];
            
            header("Location: index.php");
            exit;
        } else {
            $error_msg = "아이디 또는 비밀번호가 일치하지 않습니다.";
        }
    }
}

include(__DIR__ . "/header.php");
?>

<main class="container">
    <div class="auth-container">
        <!-- Left: Illustration Only for Login -->
        <div class="auth-left" style="align-items: center;">
            <div class="auth-left-illustration">
                <!-- Illustration removed -->
            </div>
        </div>
        
        <!-- Right: Login Card -->
        <div class="auth-right">
            <div class="card auth-card">
                <div class="auth-logo-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path><path d="M12 8c-1-1.2-2.5-1.8-4-1.8A4.5 4.5 0 0 0 3.5 10.7c0 3.3 4.5 7.3 8.5 9.3 4-2 8.5-6 8.5-9.3a4.5 4.5 0 0 0-4.5-4.5c-1.5 0-3 0.6-4 1.8Z" fill="currentColor"></path></svg>
                </div>
                <h2 class="auth-title">
                    다시 만나서 반가워요
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                </h2>
                <p class="auth-subtitle">로그인하면 더 따뜻한 고민 상담이 시작돼요.</p>
                
                <?php if (!empty($error_msg)): ?>
                    <div class="alert-error">
                        <?= htmlspecialchars($error_msg) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <div class="form-group">
                        <label for="login_id">아이디</label>
                        <input type="text" id="login_id" name="login_id" class="input-control" placeholder="아이디를 입력해주세요" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">비밀번호</label>
                        <div class="input-wrapper">
                            <input type="password" id="password" name="password" class="input-control" placeholder="비밀번호를 입력해주세요" required>
                            <button type="button" class="password-toggle" id="btn-toggle-pw">
                                <i data-lucide="eye" style="width: 18px; height: 18px;" id="toggle-icon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-extra-row">
                        <label class="checkbox-container">
                            로그인 상태 유지
                            <input type="checkbox" name="remember">
                            <span class="checkmark"></span>
                        </label>
                        <a href="#" class="form-link">아이디 / 비밀번호 찾기</a>
                    </div>
                    
                    <button type="submit" class="auth-btn-block btn btn-primary">로그인</button>
                </form>
                
                <div class="auth-divider">또는</div>
                
                <button type="button" class="auth-btn-block btn btn-secondary" onclick="location.href='board.php?type=worry'">
                    <i data-lucide="message-square" style="width: 16px; height: 16px;"></i>
                    비회원으로 둘러보기
                </button>
            </div>
        </div>
    </div>
</main>

<script>
// Interactive password toggle
document.getElementById('btn-toggle-pw').addEventListener('click', function() {
    const pwInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggle-icon');
    
    if (pwInput.type === 'password') {
        pwInput.type = 'text';
        toggleIcon.setAttribute('data-lucide', 'eye-off');
    } else {
        pwInput.type = 'password';
        toggleIcon.setAttribute('data-lucide', 'eye');
    }
    // Re-trigger Lucide icon replacement for this specific element
    lucide.createIcons({
        attrs: {
            class: 'lucide-icon'
        },
        nameAttr: 'data-lucide'
    });
});
</script>

<?php
include(__DIR__ . "/footer.php");
?>
