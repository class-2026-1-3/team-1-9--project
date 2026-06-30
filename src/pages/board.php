<?php
include(__DIR__ . "/db.php");
include(__DIR__ . "/header.php");

$type = $_GET['type'] ?? 'worry';
$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';

// Pagination Config
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build query conditions
$where_clauses = ["p.board_type = ?"];
$params = [$type];
$types_str = "s";

if ($type == 'worry' && !empty($category)) {
    $where_clauses[] = "p.category = ?";
    $params[] = $category;
    $types_str .= "s";
}

if (!empty($search)) {
    $where_clauses[] = "(p.title LIKE ? OR p.content LIKE ?)";
    $params[] = "%" . $search . "%";
    $params[] = "%" . $search . "%";
    $types_str .= "ss";
}

$where_sql = implode(" AND ", $where_clauses);

// Count Query for Pagination
$count_sql = "
    SELECT COUNT(*) AS total 
    FROM posts p 
    WHERE $where_sql
";
$stmt_count = mysqli_prepare($conn, $count_sql);
mysqli_stmt_bind_param($stmt_count, $types_str, ...$params);
mysqli_stmt_execute($stmt_count);
$res_count = mysqli_stmt_get_result($stmt_count);
$total_rows = mysqli_fetch_assoc($res_count)['total'];
$total_pages = ceil($total_rows / $limit);
if ($total_pages < 1) $total_pages = 1;

// Main List Query
$sql = "
    SELECT p.*, u.nickname,
           (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.post_id) AS comment_count
    FROM posts p
    JOIN users u ON p.user_id = u.user_id
    WHERE $where_sql
    ORDER BY p.post_id DESC
    LIMIT ? OFFSET ?
";

$params_list = array_merge($params, [$limit, $offset]);
$types_str_list = $types_str . "ii";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types_str_list, ...$params_list);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$posts = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
}

// Relative time helper
if (!function_exists('get_time_ago')) {
    function get_time_ago($time_string) {
        $time = strtotime($time_string);
        $diff = time() - $time;
        if ($diff < 60) return '방금 전';
        $diff_min = round($diff / 60);
        if ($diff_min < 60) return $diff_min . '분 전';
        $diff_hours = round($diff / 3600);
        if ($diff_hours < 24) return $diff_hours . '시간 전';
        return date('Y-m-d', $time);
    }
}

// Icon category helper
function get_category_icon($cat) {
    switch ($cat) {
        case '공부': return 'book-open';
        case '친구': return 'users';
        case '연애': return 'heart';
        case '가족': return 'home';
        case '진로': return 'compass';
        case '직장': return 'briefcase';
        default: return 'help-circle';
    }
}

// Category mapping helper for badge class
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

<main class="container">
    <div class="board-header">
        <div class="board-title-wrapper">
            <h1><?= $type == 'worry' ? '고민 게시판' : '자유 게시판' ?></h1>
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
        </div>
        <p class="board-subtitle">
            <?= $type == 'worry' ? '익명으로 자유롭게 고민을 나누고, 따뜻한 조언을 받아보세요.' : '일상, 취미, 정보 등 무엇이든 자유롭게 이야기해요!' ?>
        </p>
    </div>

    <!-- Actions Bar -->
    <div class="board-actions">
        <form method="GET" action="board.php" class="search-form">
            <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
            <?php if ($type == 'worry' && !empty($category)): ?>
                <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
            <?php endif; ?>
            <div class="search-input-wrapper">
                <input type="text" name="search" placeholder="<?= $type == 'worry' ? '고민을 검색해보세요...' : '제목 또는 내용을 검색해주세요...' ?>" value="<?= htmlspecialchars($search) ?>">
            </div>
            <button type="submit" class="search-btn">검색</button>
        </form>
        
        <button class="btn btn-primary" onclick="location.href='write.php?board_type=<?= $type ?>'">
            <i data-lucide="edit-3" style="width: 16px; height: 16px;"></i>
            글쓰기
        </button>
    </div>

    <!-- Category Tabs (Only for Worry Board) -->
    <?php if ($type == 'worry'): ?>
        <?php $categories = ['전체', '공부', '친구', '연애', '가족', '진로', '직장', '기타']; ?>
        <div class="category-tabs">
            <?php foreach ($categories as $cat): ?>
                <?php 
                    $active_class = ($category == $cat || ($cat == '전체' && empty($category))) ? 'active' : '';
                    $target_url = "board.php?type=worry" . ($cat == '전체' ? '' : '&category=' . urlencode($cat)) . (!empty($search) ? '&search=' . urlencode($search) : '');
                ?>
                <button class="category-tab <?= $active_class ?>" onclick="location.href='<?= $target_url ?>'"><?= $cat ?></button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Table List Card -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <table class="custom-table">
            <thead>
                <tr>
                    <?php if ($type == 'worry'): ?>
                        <th style="width: 50%;">제목</th>
                        <th class="cell-center" style="width: 10%;">카테고리</th>
                        <th class="cell-center" style="width: 8%;">작성자</th>
                        <th class="cell-center" style="width: 10%;">작성일</th>
                        <th class="cell-center" style="width: 8%;">조회수</th>
                        <th class="cell-center" style="width: 7%;">좋아요</th>
                        <th class="cell-center" style="width: 7%;">댓글</th>
                    <?php else: ?>
                        <th class="cell-center" style="width: 8%;">번호</th>
                        <th style="width: 45%;">제목</th>
                        <th class="cell-center" style="width: 15%;">작성자</th>
                        <th class="cell-center" style="width: 10%;">작성일</th>
                        <th class="cell-center" style="width: 8%;">조회수</th>
                        <th class="cell-center" style="width: 7%;">좋아요</th>
                        <th class="cell-center" style="width: 7%;">댓글</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($posts)): ?>
                    <tr>
                        <td colspan="7">
                            <div style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
                                <i data-lucide="inbox" style="width: 48px; height: 48px; margin-bottom: 16px; opacity: 0.4;"></i>
                                <p style="font-size: 15px; margin: 0;">아직 게시글이 없습니다.<br>첫 번째 글을 작성해보세요! 😊</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <?php 
                            $post_link = $post['post_id'] === '#' ? 'board.php?type=' . $type : 'post.php?id=' . $post['post_id'];
                            $cat = $post['category'] ?? '기타';
                        ?>
                        
                        <?php if ($type == 'worry'): ?>
                            <tr class="worry-item-row">
                                <td>
                                    <div class="worry-title-cell">
                                        <div class="worry-icon-box <?= get_category_class($cat) ?>">
                                            <i data-lucide="<?= get_category_icon($cat) ?>" style="width: 20px; height: 20px;"></i>
                                        </div>
                                        <div class="worry-title-content">
                                            <div class="worry-title-text-group">
                                                <a href="<?= $post_link ?>" class="worry-title-link"><?= htmlspecialchars($post['title']) ?></a>
                                                <?php if (!empty($post['is_new'])): ?>
                                                    <span class="badge-new">N</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="worry-description-preview"><?= htmlspecialchars($post['content']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="cell-center">
                                    <span class="badge-tag <?= get_category_class($cat) ?>"><?= htmlspecialchars($cat) ?></span>
                                </td>
                                <td class="cell-center" style="color: var(--text-secondary);"><?= htmlspecialchars($post['nickname']) ?></td>
                                <td class="cell-center" style="color: var(--text-muted);"><?= get_time_ago($post['created_at']) ?></td>
                                <td class="cell-center" style="color: var(--text-muted);"><?= $post['views'] ?? 0 ?></td>
                                <td class="cell-center">
                                    <span class="stat-icon-wrapper likes">
                                        <i data-lucide="heart" style="width: 14px; height: 14px;"></i>
                                        <?= $post['likes'] ?>
                                    </span>
                                </td>
                                <td class="cell-center">
                                    <span class="stat-icon-wrapper comments">
                                        <i data-lucide="message-circle" style="width: 14px; height: 14px;"></i>
                                        <?= $post['comment_count'] ?>
                                    </span>
                                </td>
                            </tr>
                        <?php else: ?>
                            <!-- Free Board Row -->
                            <tr class="worry-item-row">
                                <td class="cell-center" style="color: var(--text-muted);"><?= $post['post_number'] ?? $post['post_id'] ?></td>
                                <td>
                                    <div class="worry-title-text-group">
                                        <a href="<?= $post_link ?>" class="worry-title-link" style="font-weight: 500;"><?= htmlspecialchars($post['title']) ?></a>
                                        <?php if (!empty($post['is_new'])): ?>
                                            <span class="badge-new">N</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="cell-center">
                                    <span class="anonymous-user-badge">
                                        <i data-lucide="message-square" style="width: 12px; height: 12px;"></i>
                                        <?= htmlspecialchars($post['nickname']) ?>
                                    </span>
                                </td>
                                <td class="cell-center" style="color: var(--text-muted);"><?= get_time_ago($post['created_at']) ?></td>
                                <td class="cell-center" style="color: var(--text-muted);"><?= $post['views'] ?? 0 ?></td>
                                <td class="cell-center">
                                    <span class="stat-icon-wrapper likes">
                                        <i data-lucide="heart" style="width: 14px; height: 14px;"></i>
                                        <?= $post['likes'] ?>
                                    </span>
                                </td>
                                <td class="cell-center">
                                    <span class="stat-icon-wrapper comments">
                                        <i data-lucide="message-circle" style="width: 14px; height: 14px;"></i>
                                        <?= $post['comment_count'] ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endif; ?>
                        
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <button class="pagination-btn <?= $page == 1 ? 'disabled' : '' ?>" onclick="location.href='board.php?type=<?= $type ?>&page=<?= $page - 1 ?><?= !empty($category) ? '&category='.urlencode($category) : '' ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>'">
                <i data-lucide="chevron-left" style="width: 16px; height: 16px;"></i>
            </button>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <button class="pagination-btn <?= $page == $i ? 'active' : '' ?>" onclick="location.href='board.php?type=<?= $type ?>&page=<?= $i ?><?= !empty($category) ? '&category='.urlencode($category) : '' ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>'"><?= $i ?></button>
            <?php endfor; ?>
            <button class="pagination-btn <?= $page == $total_pages ? 'disabled' : '' ?>" onclick="location.href='board.php?type=<?= $type ?>&page=<?= $page + 1 ?><?= !empty($category) ? '&category='.urlencode($category) : '' ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>'">
                <i data-lucide="chevron-right" style="width: 16px; height: 16px;"></i>
            </button>
        </div>
    <?php endif; ?>
</main>

<?php
include(__DIR__ . "/footer.php");
?>
