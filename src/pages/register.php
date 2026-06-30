<?php
session_start();
include(__DIR__ . "/db.php");

$error_msg = "";
$success_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_id = $_POST['login_id'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $nickname = $_POST['nickname'] ?? '';
    
    // Server-side validation
    if (empty($login_id) || empty($password) || empty($nickname)) {
        $error_msg = "필수 입력 항목을 모두 작성해 주세요.";
    } elseif (!preg_match('/^[a-zA-Z0-9]{4,16}$/', $login_id)) {
        $error_msg = "아이디는 4~16자의 영문, 숫자 조합이어야 합니다.";
    } elseif (strlen($password) < 8 || strlen($password) > 16) {
        $error_msg = "비밀번호는 8~16자여야 합니다.";
    } elseif ($password !== $password_confirm) {
        $error_msg = "비밀번호가 일치하지 않습니다.";
    } elseif (mb_strlen($nickname) < 2 || mb_strlen($nickname) > 10) {
        $error_msg = "닉네임은 2~10자 사이여야 합니다.";
    } else {
        // Check if ID already exists
        $sql_check = "SELECT COUNT(*) AS cnt FROM users WHERE login_id = ?";
        $stmt_check = mysqli_prepare($conn, $sql_check);
        mysqli_stmt_bind_param($stmt_check, "s", $login_id);
        mysqli_stmt_execute($stmt_check);
        $res_check = mysqli_stmt_get_result($stmt_check);
        $row_check = mysqli_fetch_assoc($res_check);
        
        if ($row_check['cnt'] > 0) {
            $error_msg = "이미 사용 중인 아이디입니다.";
        } else {
            // Success: Insert User
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql_insert = "INSERT INTO users (login_id, password, nickname) VALUES (?, ?, ?)";
            $stmt_insert = mysqli_prepare($conn, $sql_insert);
            mysqli_stmt_bind_param($stmt_insert, "sss", $login_id, $hashed_password, $nickname);
            
            if (mysqli_stmt_execute($stmt_insert)) {
                $_SESSION['reg_success'] = "회원가입이 완료되었습니다. 로그인 해주세요!";
                header("Location: login.php");
                exit;
            } else {
                $error_msg = "회원가입 처리 중 오류가 발생했습니다. 다시 시도해 주세요.";
            }
        }
    }
}

include(__DIR__ . "/header.php");
?>

<main class="container">
    <div class="auth-container">
        <!-- Left Column: Marketing/Intro -->
        <div class="auth-left">
            <h2>
                함께하면 고민이<br>
                조금은 가벼워져요.
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--primary-purple); fill: rgba(139,92,246,0.3); display: inline-block; vertical-align: middle; margin-left: 4px;"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
            </h2>
            <p>익명으로 안전하게, 따뜻한 공감을 나눠보세요.</p>
            <div class="auth-left-illustration">
                <!-- Illustration removed -->
            </div>
        </div>
        
        <!-- Right Column: Registration Card -->
        <div class="auth-right">
            <div class="card auth-card">
                <div class="auth-logo-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path><path d="M12 8c-1-1.2-2.5-1.8-4-1.8A4.5 4.5 0 0 0 3.5 10.7c0 3.3 4.5 7.3 8.5 9.3 4-2 8.5-6 8.5-9.3a4.5 4.5 0 0 0-4.5-4.5c-1.5 0-3 0.6-4 1.8Z" fill="currentColor"></path></svg>
                </div>
                <h2 class="auth-title">
                    회원가입
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                </h2>
                <p class="auth-subtitle">따뜻한 공간에 오신 것을 환영합니다.</p>
                
                <?php if (!empty($error_msg)): ?>
                    <div class="alert-error" id="error-banner">
                        <?= htmlspecialchars($error_msg) ?>
                    </div>
                <?php endif; ?>
                
                <div class="alert-error" id="js-error-banner" style="display: none;"></div>

                <form method="POST" action="register.php" id="register-form">
                    <!-- ID Input with Double Check -->
                    <div class="form-group">
                        <label for="login_id">아이디</label>
                        <div class="input-with-action">
                            <input type="text" id="login_id" name="login_id" class="input-control" placeholder="아이디를 입력해주세요" required autocomplete="off">
                            <button type="button" class="input-action-btn" id="btn-check-duplicate">중복확인</button>
                        </div>
                        <div class="input-hint">4~16자의 영문, 숫자 (특수문자 제외)</div>
                    </div>
                    
                    <!-- Password Input -->
                    <div class="form-group">
                        <label for="password">비밀번호</label>
                        <div class="input-wrapper">
                            <input type="password" id="password" name="password" class="input-control" placeholder="비밀번호를 입력해주세요" required>
                            <button type="button" class="password-toggle" id="btn-toggle-pw">
                                <i data-lucide="eye" style="width: 18px; height: 18px;" id="toggle-icon"></i>
                            </button>
                        </div>
                        <div class="input-hint">8~16자의 영문, 숫자, 특수문자를 포함해주세요</div>
                    </div>
                    
                    <!-- Password Confirm Input -->
                    <div class="form-group">
                        <label for="password_confirm">비밀번호 확인</label>
                        <div class="input-wrapper">
                            <input type="password" id="password_confirm" name="password_confirm" class="input-control" placeholder="비밀번호를 다시 입력해주세요" required>
                            <button type="button" class="password-toggle" id="btn-toggle-pw-confirm">
                                <i data-lucide="eye" style="width: 18px; height: 18px;" id="toggle-icon-confirm"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Nickname Input -->
                    <div class="form-group">
                        <label for="nickname">닉네임</label>
                        <input type="text" id="nickname" name="nickname" class="input-control" placeholder="닉네임을 입력해주세요" required>
                        <div class="input-hint">2~10자의 한글, 영문, 숫자</div>
                    </div>
                    
                    <!-- Email input removed (not collected) -->
                    
                    <!-- Terms Checkbox -->
                    <div class="form-extra-row" style="justify-content: flex-start; margin-bottom: 24px;">
                        <label class="checkbox-container">
                            이용약관 및 개인정보처리방침에 동의합니다.
                            <input type="checkbox" id="agree-terms" name="agree_terms" required>
                            <span class="checkmark"></span>
                        </label>
                        <div style="margin-left: auto; font-size: 13px;">
                            <a href="#" class="form-link" style="text-decoration: underline;">이용약관</a>
                            <span style="color: var(--text-muted);">/</span>
                            <a href="#" class="form-link" style="text-decoration: underline;">개인정보처리방침</a>
                        </div>
                    </div>
                    
                    <button type="submit" class="auth-btn-block btn btn-primary">가입하기</button>
                </form>
                
                <div class="auth-divider">또는</div>
                
                <div style="font-size: 14px; color: var(--text-secondary);">
                    이미 계정이 있으신가요? <a href="login.php" class="form-link" style="color: var(--primary-purple); font-weight: 600; text-decoration: underline;">로그인</a>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
let idChecked = false;
let checkedIdValue = "";

// Password Toggles
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
    lucide.createIcons();
});

document.getElementById('btn-toggle-pw-confirm').addEventListener('click', function() {
    const pwInput = document.getElementById('password_confirm');
    const toggleIcon = document.getElementById('toggle-icon-confirm');
    if (pwInput.type === 'password') {
        pwInput.type = 'text';
        toggleIcon.setAttribute('data-lucide', 'eye-off');
    } else {
        pwInput.type = 'password';
        toggleIcon.setAttribute('data-lucide', 'eye');
    }
    lucide.createIcons();
});

// Reset ID Checked when typing
document.getElementById('login_id').addEventListener('input', function() {
    idChecked = false;
});

// AJAX Duplicate Check
document.getElementById('btn-check-duplicate').addEventListener('click', function() {
    const idVal = document.getElementById('login_id').value.trim();
    const jsError = document.getElementById('js-error-banner');
    
    jsError.style.display = 'none';
    
    if (!idVal) {
        alert("아이디를 입력해주세요.");
        return;
    }
    
    if (!/^[a-zA-Z0-9]{4,16}$/.test(idVal)) {
        alert("아이디는 4~16자의 영문, 숫자 조합이어야 합니다.");
        return;
    }
    
    fetch(`check_id.php?login_id=${encodeURIComponent(idVal)}`)
        .then(response => response.json())
        .then(data => {
            if (!data.valid) {
                alert("아이디 형식이 올바르지 않습니다.");
            } else if (data.exists) {
                alert("이미 존재하는 아이디입니다.");
            } else {
                alert("사용 가능한 아이디입니다.");
                idChecked = true;
                checkedIdValue = idVal;
            }
        })
        .catch(err => {
            console.error(err);
            alert("중복확인 중 에러가 발생했습니다.");
        });
});

// Form Submission Verification
document.getElementById('register-form').addEventListener('submit', function(e) {
    const idVal = document.getElementById('login_id').value.trim();
    const pwVal = document.getElementById('password').value;
    const pwConfirmVal = document.getElementById('password_confirm').value;
    const nicknameVal = document.getElementById('nickname').value.trim();
    const jsError = document.getElementById('js-error-banner');
    
    jsError.style.display = 'none';
    
    if (!idChecked || checkedIdValue !== idVal) {
        e.preventDefault();
        alert("아이디 중복확인을 완료해주세요.");
        return;
    }
    
    if (pwVal !== pwConfirmVal) {
        e.preventDefault();
        alert("비밀번호 확인이 일치하지 않습니다.");
        return;
    }
});
</script>

<?php
include(__DIR__ . "/footer.php");
?>
