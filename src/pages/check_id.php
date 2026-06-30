<?php
header('Content-Type: application/json');
include(__DIR__ . "/db.php");

$login_id = $_GET['login_id'] ?? '';
$response = ['exists' => false, 'valid' => true];

if (empty($login_id)) {
    $response['valid'] = false;
} else {
    // Check validation format (4-16 alphanumeric characters)
    if (!preg_match('/^[a-zA-Z0-9]{4,16}$/', $login_id)) {
        $response['valid'] = false;
    } else {
        $sql = "SELECT COUNT(*) AS cnt FROM users WHERE login_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $login_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        if ($row['cnt'] > 0) {
            $response['exists'] = true;
        }
    }
}

echo json_encode($response);
exit;
?>
