<?php
session_start();
include(__DIR__ . "/db.php");

if(!isset($_GET['id'])){
    header("Location: board.php");
    exit;
}

$id = $_GET['id'];

// 1. Increment View Count
mysqli_query($conn, "UPDATE posts SET views = views + 1 WHERE post_id = " . (int)$id);

// 2. Fetch Post Details
$sql = "
    SELECT p.*, u.nickname, u.user_id AS author_id
    FROM posts p
    JOIN users u ON p.user_id = u.user_id
    WHERE p.post_id = ?
";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$post = mysqli_fetch_assoc($result);

if(!$post){
    die("<main class='container' style='padding-top:100px; text-align:center;'><h2>게시글이 존재하지 않습니다.</h2><br><a href='board.php'>게시판으로 돌아가기</a></main>");
}

// 3. Fetch Likes Count
$sql_likes = "
    SELECT COUNT(*) AS like_count 
    FROM post_likes 
    WHERE post_id = ?
";
$stmt_like = mysqli_prepare($conn, $sql_likes);
mysqli_stmt_bind_param($stmt_like, "i", $id);
mysqli_stmt_execute($stmt_like);
$result_like = mysqli_stmt_get_result($stmt_like);
$like = mysqli_fetch_assoc($result_like);

// 4. Fetch Comments
$sql_comments = "
    SELECT c.*, u.nickname 
    FROM comments c
    JOIN users u ON c.user_id = u.user_id
    WHERE c.post_id = ?
    ORDER BY c.comment_id ASC
";
$stmt_comments = mysqli_prepare($conn, $sql_comments);
mysqli_stmt_bind_param($stmt_comments, "i", $id);
mysqli_stmt_execute($stmt_comments);
$comments = mysqli_stmt_get_result($stmt_comments);

// 5. Add Comment Handler
if(
    $_SERVER["REQUEST_METHOD"] == "POST" && 
    isset($_POST['comment_content']) && 
    isset($_SESSION['user_id'])
){
    $comment_content = $_POST['comment_content'] ?? '';

    if (!empty(trim($comment_content))) {
        $sql_add_comment = "
            INSERT INTO comments (post_id, user_id, content) 
            VALUES (?, ?, ?)
        ";
        $stmt_add = mysqli_prepare($conn, $sql_add_comment);
        mysqli_stmt_bind_param($stmt_add, "iis", $id, $_SESSION["user_id"], $comment_content);
        mysqli_stmt_execute($stmt_add);
        
        header("Location: post.php?id=" . $id);
        exit;
    }
}

include(__DIR__ . "/header.php");

// Category class mapper helper
if (!function_exists('get_category_class')) {
    function get_category_class($cat) {
        switch ($cat) {
            case '공부': return 'study';
            case '친구': return 'friend';
            case '연애': return 'love';
            case '가족': return 'family';
            case '진로': return 'career';
            case '직장': return 'work';
            default: return 'etc';
        }
    }
}
?>

<main class="container" style="max-width: 900px; padding-top: 20px;">
    <!-- Back to Board Navigation Link -->
    <a href="board.php?type=<?= $post['board_type'] ?>" style="display: inline-flex; align-items: center; gap: 6px; color: var(--text-secondary); margin-bottom: 24px; font-size: 14px;">
        <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
        게시판 목록으로 돌아가기
    </a>

    <!-- Post Detail Card -->
    <article class="card post-detail-card">
        <header class="post-detail-header">
            <?php if ($post['board_type'] == 'worry'): ?>
                <div class="post-detail-category">
                    <span class="badge-tag <?= get_category_class($post['category']) ?>"><?= htmlspecialchars($post['category']) ?></span>
                </div>
            <?php endif; ?>
            
            <h1 class="post-detail-title"><?= htmlspecialchars($post['title']) ?></h1>
            
            <div class="post-detail-meta">
                <div class="post-detail-meta-left">
                    <span style="font-weight: 500;">작성자: <?= htmlspecialchars($post['nickname']) ?></span>
                    <span>작성일: <?= date('Y-m-d H:i', strtotime($post['created_at'])) ?></span>
                </div>
                <div class="post-meta-stats">
                    <div class="post-meta-item">
                        <i data-lucide="eye" style="width: 15px; height: 15px;"></i>
                        <span><?= $post['views'] ?></span>
                    </div>
                    <div class="post-meta-item likes">
                        <i data-lucide="heart" style="width: 15px; height: 15px;"></i>
                        <span><?= $like['like_count'] ?></span>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Post Body -->
        <section class="post-detail-body">
            <?= htmlspecialchars($post['content']) ?>
        </section>
        
        <!-- Post Actions -->
        <footer class="post-detail-actions">
            <!-- Like Button -->
            <button class="post-like-btn" onclick="location.href='like_post.php?id=<?= $post['post_id'] ?>'">
                <i data-lucide="heart" style="width: 18px; height: 18px; fill: rgba(244, 114, 182, 0.25);"></i>
                좋아요 <?= $like['like_count'] ?>
            </button>
            
            <!-- Owner Actions -->
            <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['author_id']): ?>
                <div class="post-owner-actions">
                    <a href="edit_post.php?id=<?= $post['post_id'] ?>" class="btn btn-secondary" style="padding: 8px 16px; font-size: 13px; font-weight: 500;">수정</a>
                    <a href="delete_post.php?id=<?= $post['post_id'] ?>" class="btn btn-secondary" onclick="return confirm('이 글을 삭제하시겠습니까?')" style="padding: 8px 16px; font-size: 13px; font-weight: 500; border-color: rgba(239, 68, 68, 0.3); color: #f87171;">삭제</a>
                </div>
            <?php endif; ?>
        </footer>

        <!-- Comments Section -->
        <section class="comments-section">
            <h3 class="comments-title">
                댓글 (<?= mysqli_num_rows($comments) ?>)
            </h3>
            
            <!-- Comment List -->
            <div class="comment-list">
                <?php if (mysqli_num_rows($comments) == 0): ?>
                    <div style="color: var(--text-muted); font-size: 14px; text-align: center; padding: 24px 0;">첫 댓글을 남겨 따뜻한 위로와 공감을 전해보세요.</div>
                <?php else: ?>
                    <?php while($comment = mysqli_fetch_assoc($comments)): ?>
                        <div class="comment-item">
                            <div class="comment-item-header">
                                <span class="comment-author"><?= htmlspecialchars($comment['nickname']) ?></span>
                                <span class="comment-date"><?= date('m-d H:i', strtotime($comment['created_at'])) ?></span>
                            </div>
                            <p class="comment-content"><?= htmlspecialchars($comment['content']) ?></p>
                            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $comment['user_id']): ?>
                                <div class="comment-actions">
                                    <a href="delete_comment.php?id=<?= $comment['comment_id'] ?>" class="comment-delete-link" onclick="return confirm('댓글을 삭제하시겠습니까?')">삭제</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>

            <!-- Comment Form -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <form method="POST" action="post.php?id=<?= $post['post_id'] ?>" class="comment-form">
                    <textarea name="comment_content" placeholder="따뜻한 한마디와 조언을 입력해보세요..." required></textarea>
                    <button type="submit" class="comment-submit-btn">댓글 등록</button>
                </form>
            <?php else: ?>
                <div style="background: rgba(255,255,255,0.02); border: 1px dashed rgba(255,255,255,0.08); padding: 20px; border-radius: 10px; text-align: center; font-size: 14px; color: var(--text-secondary);">
                    댓글을 남기려면 <a href="login.php" style="color: var(--primary-purple); text-decoration: underline; font-weight: 500;">로그인</a>이 필요합니다.
                </div>
            <?php endif; ?>
        </section>
    </article>
</main>

<?php
include(__DIR__ . "/footer.php");
?>
