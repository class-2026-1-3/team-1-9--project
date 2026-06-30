<?php
include(__DIR__ . "/db.php");
session_start();

// admin check
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$sid = $_SESSION['user_id'];
$qr = mysqli_prepare($conn, "SELECT is_admin FROM users WHERE user_id = ?");
mysqli_stmt_bind_param($qr, "i", $sid);
mysqli_stmt_execute($qr);
$r = mysqli_stmt_get_result($qr);
$row = mysqli_fetch_assoc($r);
if (!($row && $row['is_admin'] == 1)) { header('Location: index.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if ($id > 0) {
        $d = mysqli_prepare($conn, "DELETE FROM notices WHERE notice_id = ?");
        mysqli_stmt_bind_param($d, "i", $id);
        mysqli_stmt_execute($d);
    }
}
header('Location: notice.php');
exit;
