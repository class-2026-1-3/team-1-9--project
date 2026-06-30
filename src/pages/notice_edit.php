<?php
include(__DIR__ . "/db.php");
session_start();

function require_admin() {
    global $conn;
    if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }
    $sid = $_SESSION['user_id'];
    $qr = mysqli_prepare($conn, "SELECT is_admin FROM users WHERE user_id = ?");
    mysqli_stmt_bind_param($qr, "i", $sid);
    mysqli_stmt_execute($qr);
    $r = mysqli_stmt_get_result($qr);
    $row = mysqli_fetch_assoc($r);
    if (!($row && $row['is_admin'] == 1)) { header('Location: index.php'); exit; }
}

require_admin();

include(__DIR__ . "/header.php");

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$title = '';
$content = '';
if ($id > 0) {
    $q = mysqli_prepare($conn, "SELECT * FROM notices WHERE notice_id = ? LIMIT 1");
    mysqli_stmt_bind_param($q, "i", $id);
    mysqli_stmt_execute($q);
    $res = mysqli_stmt_get_result($q);
    $row = mysqli_fetch_assoc($res);
    if ($row) { $title = $row['title']; $content = $row['content']; }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $p_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $p_title = $_POST['title'] ?? '';
    $p_content = $_POST['content'] ?? '';
    $uid = $_SESSION['user_id'];
    if ($p_id > 0) {
        $u = mysqli_prepare($conn, "UPDATE notices SET title = ?, content = ?, updated_at = NOW() WHERE notice_id = ?");
        mysqli_stmt_bind_param($u, "ssi", $p_title, $p_content, $p_id);
        mysqli_stmt_execute($u);
        header('Location: notice_view.php?id=' . $p_id);
        exit;
    } else {
        $i = mysqli_prepare($conn, "INSERT INTO notices (title, content, user_id) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($i, "ssi", $p_title, $p_content, $uid);
        mysqli_stmt_execute($i);
        $new_id = mysqli_insert_id($conn);
        header('Location: notice_view.php?id=' . $new_id);
        exit;
    }
}
?>

<main class="container">
    <div class="card">
        <h2><?= $id > 0 ? '공지사항 수정' : '공지사항 작성' ?></h2>
        <form method="POST">
            <?php if ($id > 0): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>
            <div class="form-group">
                <label for="title">제목</label>
                <input id="title" name="title" class="input-control" required value="<?= htmlspecialchars($title) ?>">
            </div>
            <div class="form-group">
                <label for="content">내용</label>
                <textarea id="content" name="content" class="input-control" style="min-height:200px;" required><?= htmlspecialchars($content) ?></textarea>
            </div>
            <button class="btn btn-primary" type="submit">저장</button>
            <a class="btn btn-secondary" href="notice.php" style="margin-left:8px;">취소</a>
        </form>
    </div>
</main>

<?php include(__DIR__ . "/footer.php"); ?>
