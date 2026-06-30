<?php
session_start();
include(__DIR__ . "/db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$nickname = $_SESSION['nickname'];

// Fetch user's posts
$sql_posts = "
    SELECT * 
    FROM posts 
    WHERE user_id = ? 
    ORDER BY post_id DESC
";
$stmt_posts = mysqli_prepare($conn, $sql_posts);
mysqli_stmt_bind_param($stmt_posts, "i", $user_id);
mysqli_stmt_execute($stmt_posts);
$result_posts = mysqli_stmt_get_result($stmt_posts);

// Fetch user's comments
$sql_comments = "
    SELECT c.*, p.title AS post_title 
    FROM comments c
    JOIN posts p ON c.post_id = p.post_id
    WHERE c.user_id = ? 
    ORDER BY c.comment_id DESC
";
$stmt_comments = mysqli_prepare($conn, $sql_comments);
mysqli_stmt_bind_param($stmt_comments, "i", $user_id);
mysqli_stmt_execute($stmt_comments);
$result_comments = mysqli_stmt_get_result($stmt_comments);

include(__DIR__ . "/header.php");

// Short string helper for preview
function get_snippet($str, $len = 45) {
    if (mb_strlen($str) > $len) {
        return mb_substr($str, 0, $len) . '...';
    }
    return $str;
}
?>

<main class="container" style="padding-top: 40px;">
    <!-- Profile Card Header -->
    <div class="card" style="margin-bottom: 40px; display: flex; align-items: center; justify-content: space-between; padding: 30px;">
        <div style="display: flex; align-items: center; gap: 20px;">
            <!-- Profile initial avatar badge -->
            <div style="width: 60px; height: 60px; border-radius: 50%; background: var(--primary-gradient); display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 700; color: white; text-transform: uppercase;">
                <?= mb_substr($nickname, 0, 1) ?>
            </div>
            <div>
                <h2 style="font-size: 22px; font-weight: 700; margin-bottom: 4px;"><?= htmlspecialchars($nickname) ?> 님</h2>
                <p style="font-size: 13px; color: var(--text-secondary);">따뜻한 온기를 나누어주셔서 대단히 감사드립니다.</p>
            </div>
        </div>
        <button class="btn btn-secondary" onclick="location.href='logout.php'" style="padding: 10px 20px; font-size: 14px;">
            <i data-lucide="log-out" style="width: 15px; height: 15px;"></i>
            로그아웃
        </button>
    </div>

    <!-- Mypage Grid Split -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
        <!-- Left: My Posts -->
        <div>
            <div class="section-header">
                <div class="section-title">
                    <i data-lucide="book-open" style="width: 18px; height: 18px;"></i>
                    내가 작성한 글 (<?= mysqli_num_rows($result_posts) ?>)
                </div>
            </div>
            
            <div class="card" style="padding: 0; overflow: hidden; min-height: 250px;">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width: 70%;">제목</th>
                            <th class="cell-center" style="width: 30%;">작성일</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result_posts) == 0): ?>
                            <tr>
                                <td colspan="2" class="cell-center" style="color: var(--text-muted); padding: 50px 0;">작성한 게시글이 없습니다.</td>
                            </tr>
                        <?php else: ?>
                            <?php while($post = mysqli_fetch_assoc($result_posts)): ?>
                                <tr>
                                    <td>
                                        <a href="post.php?id=<?= $post['post_id'] ?>" style="font-weight: 500; hover: color: var(--primary-purple);"><?= htmlspecialchars($post['title']) ?></a>
                                    </td>
                                    <td class="cell-center" style="font-size: 13px; color: var(--text-muted);"><?= date('Y-m-d', strtotime($post['created_at'])) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right: My Comments -->
        <div>
            <div class="section-header">
                <div class="section-title">
                    <i data-lucide="message-circle" style="width: 18px; height: 18px;"></i>
                    내가 작성한 댓글 (<?= mysqli_num_rows($result_comments) ?>)
                </div>
            </div>
            
            <div class="card" style="padding: 0; overflow: hidden; min-height: 250px;">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width: 50%;">댓글 내용</th>
                            <th style="width: 50%;">원문 제목</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result_comments) == 0): ?>
                            <tr>
                                <td colspan="2" class="cell-center" style="color: var(--text-muted); padding: 50px 0;">작성한 댓글이 없습니다.</td>
                            </tr>
                        <?php else: ?>
                            <?php while($comment = mysqli_fetch_assoc($result_comments)): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 500; color: var(--text-primary);"><?= htmlspecialchars(get_snippet($comment['content'])) ?></div>
                                        <span style="font-size: 11px; color: var(--text-muted); display: block; margin-top: 4px;"><?= date('Y-m-d', strtotime($comment['created_at'])) ?></span>
                                    </td>
                                    <td style="color: var(--text-secondary); font-size: 14px;">
                                        <a href="post.php?id=<?= $comment['post_id'] ?>" style="text-decoration: underline; text-underline-offset: 3px;"><?= htmlspecialchars(get_snippet($comment['post_title'], 25)) ?></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php
include(__DIR__ . "/footer.php");
?>
