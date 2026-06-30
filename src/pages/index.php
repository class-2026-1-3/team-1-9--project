<?php
include(__DIR__ . "/db.php");
include(__DIR__ . "/header.php");

// Query top 3 posts based on likes
$sql_popular = "
    SELECT p.*, u.nickname,
           (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.post_id) AS comment_count
    FROM posts p
    JOIN users u ON p.user_id = u.user_id
    WHERE p.board_type = 'worry'
    ORDER BY p.likes DESC, p.post_id DESC
    LIMIT 3
";
$result_popular = mysqli_query($conn, $sql_popular);
$popular_posts = [];
if ($result_popular && mysqli_num_rows($result_popular) > 0) {
    while ($row = mysqli_fetch_assoc($result_popular)) {
        $popular_posts[] = $row;
    }
}

// Stats Query
$sql_stats = "SELECT COUNT(*) AS total_count FROM posts WHERE board_type='worry' AND DATE(created_at) = CURDATE()";
$res_stats = mysqli_query($conn, $sql_stats);
$total_worries = 0;
if ($res_stats) {
    $row_stats = mysqli_fetch_assoc($res_stats);
    $total_worries = $row_stats['total_count'];
}
$display_stats_count = $total_worries;

// 어제 고민 수 쿼리
$sql_yesterday = "SELECT COUNT(*) AS yesterday_count FROM posts WHERE board_type='worry' AND DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
$res_yesterday = mysqli_query($conn, $sql_yesterday);
$yesterday_count = 0;
if ($res_yesterday) {
    $row_yesterday = mysqli_fetch_assoc($res_yesterday);
    $yesterday_count = $row_yesterday['yesterday_count'];
}
$display_stat_increase = $total_worries - $yesterday_count;

// Helper function to render human-readable relative time
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

// Category mapping helper for badge class
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
?>

<main class="container">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <div class="hero-eyebrow">
                <span></span>
                지금도 누군가 고민을 털어놓고 있어요
            </div>
            <h1>
                혼자 고민하지 마세요,<br>
                여기, <span class="accent">당신의 이야기</span>를<br>
                들어줄 누군가가 있어요.
                <svg class="heart-icon" xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
            </h1>
            <p>익명으로 자유롭게 고민을 털어놓고,
따뜻한 공감과 조언을 받아보세요.</p>
            <div class="hero-actions">
                <button class="btn btn-primary" onclick="location.href='write.php'">
                    <i data-lucide="edit-3" style="width: 16px; height: 16px;"></i>
                    고민 털어놓기
                </button>
                <button class="btn btn-secondary" onclick="location.href='board.php?type=worry'">
                    고민 보러가기
                    <i data-lucide="arrow-right" style="width: 16px; height: 16px;"></i>
                </button>
            </div>
        </div>
        <div class="hero-illustration">
            <!-- Illustration removed -->
        </div>
    </section>

    <!-- Content Grid -->
    <div class="home-grid">
        <!-- Left: Popular Top 3 -->
        <section class="popular-posts-section">
            <div class="section-header">
                <div class="section-title">
                    <i data-lucide="flame" style="width: 20px; height: 20px;"></i>
                    지금, 인기 있는 고민 TOP 3
                </div>
                <a href="board.php?type=worry" class="more-link">
                    더보기
                    <i data-lucide="chevron-right" style="width: 16px; height: 16px;"></i>
                </a>
            </div>
            
            <div class="popular-posts-list">
                <?php if (empty($popular_posts)): ?>
                    <div style="text-align: center; padding: 40px 20px; color: var(--text-muted);">
                        <i data-lucide="flame" style="width: 40px; height: 40px; margin-bottom: 12px; opacity: 0.3;"></i>
                        <p style="font-size: 14px; margin: 0;">아직 인기 게시글이 없어요.<br>첫 번째 고민을 올려보세요! 💬</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($popular_posts as $post): ?>
                        <?php 
                            $link = 'post.php?id=' . $post['post_id'];
                            $cat = $post['category'] ?? '기타';
                        ?>
                        <div class="popular-post-card" onclick="location.href='<?= $link ?>'">
                            <div class="post-card-right">
                                <div class="post-card-header">
                                    <span class="badge-tag <?= get_category_class($cat) ?>"><?= htmlspecialchars($cat) ?></span>
                                    <span style="font-size: 13px; color: var(--text-muted);"><?= get_time_ago($post['created_at']) ?></span>
                                </div>
                                <h3 class="post-card-title"><?= htmlspecialchars($post['title']) ?></h3>
                                <p class="post-card-body"><?= htmlspecialchars($post['content']) ?></p>
                                <div class="post-card-footer">
                                    <span style="color: var(--text-muted);">작성자: 익명</span>
                                    <div class="post-meta-stats">
                                        <div class="post-meta-item likes">
                                            <i data-lucide="heart" style="width: 15px; height: 15px;"></i>
                                            <span><?= $post['likes'] ?></span>
                                        </div>
                                        <div class="post-meta-item comments">
                                            <i data-lucide="message-circle" style="width: 15px; height: 15px;"></i>
                                            <span><?= $post['comment_count'] ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Right: Stats & Daily Quote -->
        <aside class="sidebar-widgets">
            <!-- Stats widget -->
            <div class="card stats-widget">
                <div class="section-title" style="margin-bottom: 12px;">
                    <i data-lucide="trending-up" style="width: 18px; height: 18px;"></i>
                    오늘의 고민 통계
                </div>
                <div class="stats-container">
                    <div>
                        <div class="stats-number"><?= $display_stats_count ?></div>
                        <div class="stats-label">오늘 올라온 고민 수</div>
                    </div>
                    <div class="stats-badge">
                        <div class="stats-badge-label">어제보다</div>
                        <div class="stats-badge-value">
                            <?php if ($display_stat_increase > 0): ?>
                                <i data-lucide="triangle" style="width: 10px; height: 10px; fill: #f87171; stroke: none;"></i>
                                +<?= $display_stat_increase ?>
                            <?php elseif ($display_stat_increase < 0): ?>
                                <i data-lucide="triangle" style="width: 10px; height: 10px; fill: #60a5fa; stroke: none; transform: rotate(180deg);"></i>
                                <?= $display_stat_increase ?>
                            <?php else: ?>
                                <span style="font-size: 12px;">-</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quote widget -->
            <div class="card quote-widget" id="today-quote">
                <div class="quote-icon">
                    <i data-lucide="quote" style="width: 24px; height: 24px; transform: scaleX(-1);"></i>
                </div>
                <div class="quote-text" id="quote-display">
                    "지금 이 순간도, 누군가에게는 용기가 되고 있어요."
                </div>
                <div class="quote-author" id="quote-author">
                    - 익명의 누군가 -
                </div>
                <button class="quote-refresh-btn" id="btn-refresh-quote">
                    <i data-lucide="rotate-cw" style="width: 14px; height: 14px;" id="refresh-icon"></i>
                    새로운 한마디 보기
                </button>
            </div>
        </aside>
    </div>
</main>

<script>
// Interactive Quote Pool
const quotes = [
    { text: "지금 이 순간도, 누군가에게는 용기가 되고 있어요.", author: "익명의 누군가" },
    { text: "다 괜찮을 거예요. 당신은 혼자가 아니니까요.", author: "익명의 동반자" },
    { text: "가장 어두운 밤도 결국 지나가고 아침이 옵니다.", author: "희망을 담은 한마디" },
    { text: "실수해도 괜찮아요. 그것도 성장의 한 과정이니까요.", author: "따뜻한 공감" },
    { text: "오늘 하루도 정말 고생 많으셨어요. 토닥토닥.", author: "마음의 안식처" },
    { text: "작은 쉼표 하나가 인생의 문장을 완성하기도 합니다.", author: "지나가는 바람" }
];

document.getElementById('btn-refresh-quote').addEventListener('click', function() {
    const icon = document.getElementById('refresh-icon');
    const quoteText = document.getElementById('quote-display');
    const quoteAuthor = document.getElementById('quote-author');
    
    // Add rotating animation
    icon.classList.add('spinning');
    
    // Pick random quote different from current
    let currentQuote = quoteText.innerText.replace(/"/g, "");
    let availableQuotes = quotes.filter(q => q.text !== currentQuote);
    let randomIndex = Math.floor(Math.random() * availableQuotes.length);
    let selectedQuote = availableQuotes[randomIndex];
    
    // Fade out and in effect
    quoteText.style.opacity = 0;
    quoteAuthor.style.opacity = 0;
    
    setTimeout(() => {
        quoteText.innerText = `"${selectedQuote.text}"`;
        quoteAuthor.innerText = `- ${selectedQuote.author} -`;
        quoteText.style.opacity = 1;
        quoteAuthor.style.opacity = 1;
        quoteText.style.transition = "opacity 0.3s";
        quoteAuthor.style.transition = "opacity 0.3s";
        icon.classList.remove('spinning');
    }, 400);
});
</script>

<?php
include(__DIR__ . "/footer.php");
?>
