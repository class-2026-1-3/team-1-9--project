<?php
session_start();
include(__DIR__ . "/db.php");

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$board_type_get = $_GET['board_type'] ?? 'worry';
$error_msg = "";

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $board_type = $_POST['board_type'] ?? 'worry';
    $category = ($board_type == 'worry') ? ($_POST['category'] ?? '기타') : null;
    $user_id = $_SESSION['user_id'];

    if (empty($title) || empty($content)) {
        $error_msg = "제목과 내용을 모두 입력해 주세요.";
    } else {
        $sql = "
            INSERT INTO posts (title, content, board_type, category, user_id)
            VALUES (?, ?, ?, ?, ?)
        ";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssi", $title, $content, $board_type, $category, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // Get last inserted ID to redirect
            $post_id = mysqli_insert_id($conn);
            header("Location: post.php?id=" . $post_id);
            exit;
        } else {
            $error_msg = "글 작성 중 오류가 발생했습니다. 다시 시도해 주세요.";
        }
    }
}

include(__DIR__ . "/header.php");
?>

<main class="container" style="max-width: 800px; padding-top: 40px;">
    <div class="board-header">
        <div class="board-title-wrapper">
            <h1>글쓰기</h1>
            <i data-lucide="pen-tool" style="color: var(--primary-purple); width: 28px; height: 28px;"></i>
        </div>
        <p class="board-subtitle">따뜻하고 소중한 글을 들려주세요. 모든 고민은 익명으로 안전하게 지켜집니다.</p>
    </div>

    <?php if (!empty($error_msg)): ?>
        <div class="alert-error">
            <?= htmlspecialchars($error_msg) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <form method="POST" action="write.php">
            <!-- Board Type Selection -->
            <div class="form-group">
                <label for="board_type">게시판 선택</label>
                <select name="board_type" id="board_type" class="input-control" required style="appearance: none; background-image: url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%238c8fa7\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3E%3Cpath d=\'m6 9 6 6 6-6\'/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 16px center; background-size: 16px;">
                    <option value="worry" <?= $board_type_get == 'worry' ? 'selected' : '' ?>>고민게시판</option>
                    <option value="free" <?= $board_type_get == 'free' ? 'selected' : '' ?>>자유게시판</option>
                </select>
            </div>

            <!-- Category Selection (Only for Worry Board) -->
            <div class="form-group" id="category_group" style="display: <?= $board_type_get == 'worry' ? 'block' : 'none' ?>;">
                <label for="category">고민 카테고리</label>
                <select name="category" id="category" class="input-control" style="appearance: none; background-image: url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%238c8fa7\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3E%3Cpath d=\'m6 9 6 6 6-6\'/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 16px center; background-size: 16px;">
                    <option value="공부">공부</option>
                    <option value="친구">친구</option>
                    <option value="연애">연애</option>
                    <option value="가족">가족</option>
                    <option value="진로">진로</option>
                    <option value="직장">직장</option>
                    <option value="기타" selected>기타</option>
                </select>
            </div>

            <!-- Title -->
            <div class="form-group">
                <label for="title">제목</label>
                <input type="text" name="title" id="title" class="input-control" placeholder="제목을 입력해 주세요 (최대 100자)" required maxlength="100">
            </div>

            <!-- Content -->
            <div class="form-group">
                <label for="content">내용</label>
                <textarea name="content" id="content" class="input-control" placeholder="공감과 조언을 받고 싶은 내용을 자유롭게 작성해 보세요." required style="min-height: 250px; resize: vertical; line-height: 1.7;"></textarea>
            </div>

            <!-- Buttons -->
            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 30px;">
                <button type="button" class="btn btn-secondary" onclick="history.back()">취소</button>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="check" style="width: 16px; height: 16px;"></i>
                    작성 완료
                </button>
            </div>
        </form>
    </div>
</main>

<script>
// Dynamic toggling of category selector
document.getElementById('board_type').addEventListener('change', function() {
    const catGroup = document.getElementById('category_group');
    if (this.value === 'worry') {
        catGroup.style.display = 'block';
    } else {
        catGroup.style.display = 'none';
    }
});
</script>

<?php
include(__DIR__ . "/footer.php");
?>
