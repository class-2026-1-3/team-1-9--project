<?php
include(__DIR__ . "/db.php");
include(__DIR__ . "/header.php");

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: notice.php'); exit;
}

$sql = "SELECT n.*, u.nickname FROM notices n JOIN users u ON n.user_id = u.user_id WHERE n.notice_id = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$notice = mysqli_fetch_assoc($res);
if (!$notice) { header('Location: notice.php'); exit; }

function is_admin_user() {
    if (empty($_SESSION['user_id'])) return false;
    global $conn;
    $sid = $_SESSION['user_id'];
    $qr = mysqli_prepare($conn, "SELECT is_admin FROM users WHERE user_id = ?");
    mysqli_stmt_bind_param($qr, "i", $sid);
    mysqli_stmt_execute($qr);
    $r = mysqli_stmt_get_result($qr);
    $row = mysqli_fetch_assoc($r);
    return $row && $row['is_admin'] == 1;
}
?>

<main class="container">
    <div class="card">
        <h2 style="margin-bottom:8px;"><?= htmlspecialchars($notice['title']) ?></h2>
        <div style="color:var(--text-muted); font-size:13px; margin-bottom:16px;">작성자: <?= htmlspecialchars($notice['nickname']) ?> · <?= date('Y-m-d H:i', strtotime($notice['created_at'])) ?></div>
        <div style="white-space:pre-wrap; color:var(--text-primary);"><?= nl2br(htmlspecialchars($notice['content'])) ?></div>

        <?php if (is_admin_user()): ?>
            <div style="margin-top:18px; display:flex; gap:8px;">
                <button class="btn btn-primary" onclick="location.href='notice_edit.php?id=<?= $notice['notice_id'] ?>'">수정</button>
                <form method="POST" action="notice_delete.php" onsubmit="return confirm('정말 삭제하시겠습니까?');">
                    <input type="hidden" name="id" value="<?= $notice['notice_id'] ?>">
                    <button type="submit" class="btn btn-danger">삭제</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include(__DIR__ . "/footer.php"); ?>
