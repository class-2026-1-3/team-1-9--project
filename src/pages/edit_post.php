<?php
session_start();
include(__DIR__ . "/db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

if(!isset($_GET['id'])){
    header("Location: board.php");
    exit;
}

$id = $_GET['id'];

// Fetch post details to edit
$sql = "
    SELECT *
    FROM posts
    WHERE post_id = ?
    AND user_id = ?
";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $id, $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$post = mysqli_fetch_assoc($result);

if(!$post){
    die("<main class='container' style='padding-top:100px; text-align:center;'><h2>수정 권한이 없거나 게시글이 존재하지 않습니다.</h2><br><a href='board.php'>게시판으로 돌아가기</a></main>");
}

$error_msg = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $category = ($post['board_type'] == 'worry') ? ($_POST['category'] ?? '기타') : null;

    if (empty($title) || empty($content)) {
        $error_msg = "제목과 내용을 모두 입력해 주세요.";
    } else {
        $sql = "
            UPDATE posts
            SET title = ?, content = ?, category = ?
            WHERE post_id = ?
            AND user_id = ?
        ";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssii", $title, $content, $category, $id, $_SESSION['user_id']);
        
        if (mysqli_stmt_execute($stmt)) {
            header("Location: post.php?id=" . $id);
            exit;
        } else {
            $error_msg = "수정 처리 중 오류가 발생했습니다. 다시 시도해 주세요.";
        }
    }
}

include(__DIR__ . "/header.php");
?>

<main class="container" style="max-width: 800px; padding-top: 40px;">
    <div class="board-header">
        <div class="board-title-wrapper">
            <h1>게시글 수정</h1>
            <i data-lucide="edit" style="color: var(--primary-purple); width: 28px; height: 28px;"></i>
        </div>
        <p class="board-subtitle">작성한 내용을 수정하고 저장할 수 있습니다.</p>
    </div>

    <?php if (!empty($error_msg)): ?>
        <div class="alert-error">
            <?= htmlspecialchars($error_msg) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <form method="POST" action="edit_post.php?id=<?= $id ?>">
            <!-- Category Selection (Only for Worry Board) -->
            <?php if ($post['board_type'] == 'worry'): ?>
                <div class="form-group">
                    <label for="category">고민 카테고리</label>
                    <select name="category" id="category" class="input-control" style="appearance: none; background-image: url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%238c8fa7\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3E%3Cpath d=\'m6 9 6 6 6-6\'/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 16px center; background-size: 16px;">
                        <option value="공부" <?= $post['category'] == '공부' ? 'selected' : '' ?>>공부</option>
                        <option value="친구" <?= $post['category'] == '친구' ? 'selected' : '' ?>>친구</option>
                        <option value="연애" <?= $post['category'] == '연애' ? 'selected' : '' ?>>연애</option>
                        <option value="가족" <?= $post['category'] == '가족' ? 'selected' : '' ?>>가족</option>
                        <option value="진로" <?= $post['category'] == '진로' ? 'selected' : '' ?>>진로</option>
                        <option value="직장" <?= $post['category'] == '직장' ? 'selected' : '' ?>>직장</option>
                        <option value="기타" <?= $post['category'] == '기타' ? 'selected' : '' ?>>기타</option>
                    </select>
                </div>
            <?php endif; ?>

            <!-- Title -->
            <div class="form-group">
                <label for="title">제목</label>
                <input type="text" name="title" id="title" class="input-control" value="<?= htmlspecialchars($post['title']) ?>" required maxlength="100">
            </div>

            <!-- Content -->
            <div class="form-group">
                <label for="content">내용</label>
                <textarea name="content" id="content" class="input-control" required style="min-height: 250px; resize: vertical; line-height: 1.7;"><?= htmlspecialchars($post['content']) ?></textarea>
            </div>

            <!-- Buttons -->
            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 30px;">
                <button type="button" class="btn btn-secondary" onclick="history.back()">취소</button>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="check" style="width: 16px; height: 16px;"></i>
                    수정 완료
                </button>
            </div>
        </form>
    </div>
</main>

<?php
include(__DIR__ . "/footer.php");
?>
