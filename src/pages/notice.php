<?php
include(__DIR__ . "/db.php");
include(__DIR__ . "/header.php");

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$sql = "SELECT n.*, u.nickname FROM notices n JOIN users u ON n.user_id = u.user_id ORDER BY n.created_at DESC LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $limit, $offset);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$notices = [];
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) $notices[] = $row;
}

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
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <h2>공지사항</h2>
            <?php if (is_admin_user()): ?>
                <button class="btn btn-primary" onclick="location.href='notice_edit.php'">글쓰기</button>
            <?php endif; ?>
        </div>

        <?php if (empty($notices)): ?>
            <div style="text-align:center; color:var(--text-muted); padding:40px 0;">등록된 공지사항이 없습니다.</div>
        <?php else: ?>
            <ul style="display:block;">
            <?php foreach ($notices as $n): ?>
                <li style="padding:12px 0; border-bottom:1px solid rgba(255,255,255,0.03);">
                    <a href="notice_view.php?id=<?= $n['notice_id'] ?>" style="font-weight:700; color:var(--text-primary);"><?= htmlspecialchars($n['title']) ?></a>
                    <div style="color:var(--text-muted); font-size:13px;">작성자: <?= htmlspecialchars($n['nickname']) ?> · <?= date('Y-m-d H:i', strtotime($n['created_at'])) ?></div>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</main>

<?php include(__DIR__ . "/footer.php"); ?>
