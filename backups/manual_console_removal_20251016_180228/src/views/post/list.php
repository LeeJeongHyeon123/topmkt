<?php
// Pagination 컴포넌트 로드
require_once SRC_PATH . '/components/ui/Pagination.php';

include SRC_PATH . '/views/templates/header.php';
?>

<div class="posts-container">
    <div class="posts-header">
        <h2>게시판</h2>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="/posts/create" class="btn btn-primary">글쓰기</a>
        <?php endif; ?>
    </div>
    
    <div class="posts-filter">
        <div class="search-box">
            <form action="/posts" method="get">
                <input type="text" name="search" placeholder="검색어 입력...">
                <button type="submit">검색</button>
            </form>
        </div>
    </div>
    
    <div class="posts-list">
        <?php if (empty($posts)): ?>
            <div class="posts-empty">
                <p>게시글이 없습니다.</p>
            </div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <div class="post-item">
                    <div class="post-info">
                        <h3 class="post-title">
                            <a href="/posts/<?= $post['id'] ?>"><?= $post['title'] ?></a>
                        </h3>
                        <div class="post-meta">
                            <span class="post-author"><?= $post['author'] ?></span>
                            <span class="post-date"><?= $post['created_at'] ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <?php
    // 페이지네이션 렌더링 (Pagination 컴포넌트 사용)
    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $totalPages = $pagination['total_pages'] ?? 1; // Controller에서 전달받은 총 페이지 수

    if ($totalPages > 1) {
        echo renderPagination($currentPage, $totalPages, [
            'containerClass' => 'posts-pagination',
            'linkClass' => 'page-link',
            'preserveParams' => ['search'] // 검색어 유지
        ]);
    }
    ?>
</div>

<?php include SRC_PATH . '/views/templates/footer.php'; ?> 