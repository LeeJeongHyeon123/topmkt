<?php
/**
 * 커뮤니티 게시판 메인 페이지 (게시글 목록)
 */

// 로그인 상태 확인
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
require_once SRC_PATH . '/helpers/HtmlSanitizerHelper.php';
require_once SRC_PATH . '/helpers/SearchHelper.php';
require_once SRC_PATH . '/helpers/ProfileImageHelper.php';
$isLoggedIn = AuthMiddleware::isLoggedIn();
$currentUserId = AuthMiddleware::getCurrentUserId();

// 검색어 유효성 검증 및 성능 측정
$searchValidated = null;
$searchTime = 0;
$pageLoadStart = microtime(true);

if (!empty($search)) {
    $searchStart = microtime(true);
    $searchValidated = SearchHelper::validateSearchTerm($search);
    $searchTime = round((microtime(true) - $searchStart) * 1000, 2);
}

// 페이지 로드 시간 계산 (뷰 끝에서 사용)
$pageLoadTime = round((microtime(true) - $pageLoadStart) * 1000, 2);
?>

<!-- 성능 최적화 리소스 힌트 -->
<link rel="preconnect" href="https://www.topmktx.com">
<link rel="dns-prefetch" href="//www.topmktx.com">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="theme-color" content="#667eea">

<!-- 프로필 이미지 모달 통합 리소스 -->
<?php include SRC_PATH . '/views/components/profile-modal-resources.php'; ?>

<style>
/* 커뮤니티 게시판 스타일 */
.community-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    min-height: calc(100vh - 200px);
}

.community-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 40px 0;
    text-align: center;
    margin-top: 60px;
    margin-bottom: 30px;
    border-radius: 12px;
}

.community-header h1 {
    font-size: 2.5rem;
    margin-bottom: 10px;
    font-weight: 700;
}

.community-header p {
    font-size: 1.1rem;
    opacity: 0.9;
    margin: 0;
}

.board-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.search-form {
    display: flex;
    gap: 10px;
    align-items: center;
}

.search-input {
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 16px;
    width: 250px;
    transition: border-color 0.3s ease;
    min-height: 44px;
    box-sizing: border-box;
}

.search-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    min-height: 44px;
    box-sizing: border-box;
}

/* 기존 프로필 이미지 모달 스타일 제거됨 - 통합 CSS 사용 */


.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover {
    background: #5a67d8;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: #718096;
    color: white;
}

.btn-secondary:hover {
    background: #4a5568;
}

.btn-write {
    background: linear-gradient(135deg, #374151 0%, #1f2937 100%);
    color: white;
    font-weight: 500;
    font-size: 14px;
    padding: 12px 16px;
    min-height: 44px;
}

.btn-write:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(55, 65, 81, 0.3);
    text-decoration: none;
}

.board-stats {
    background: #f8fafc;
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-left: 4px solid #667eea;
}

.stats-text {
    color: #4a5568;
    font-size: 14px;
    margin: 0;
}

.post-list {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
}

.post-item {
    padding: 20px;
    border-bottom: 1px solid #e2e8f0;
    transition: background-color 0.2s ease;
    cursor: pointer;
    display: flex;
    gap: 15px;
    align-items: flex-start;
}

.post-item:hover {
    background-color: #f8fafc;
}

.post-author-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 1.1rem;
    flex-shrink: 0;
    overflow: hidden;
    position: relative;
}

.post-author-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    will-change: auto;
    transition: none;
}

.post-content-wrapper {
    flex: 1;
    min-width: 0;
}

.post-item:last-child {
    border-bottom: none;
}

.post-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 8px;
    line-height: 1.4;
}

.post-title:hover {
    color: #667eea;
}

.post-meta {
    display: flex;
    align-items: center;
    gap: 15px;
    font-size: 0.9rem;
    color: #718096;
    margin-bottom: 10px;
}

.post-author {
    font-weight: 600;
    color: #4a5568;
}

.post-date {
    color: #a0aec0;
}

.post-content-preview {
    color: #718096;
    font-size: 0.9rem;
    line-height: 1.5;
    margin-top: 8px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.post-stats {
    display: flex;
    gap: 15px;
    font-size: 0.85rem;
    color: #a0aec0;
    margin-top: 10px;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 4px;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    margin-top: 30px;
}

.page-link {
    padding: 8px 12px;
    border: 1px solid #e2e8f0;
    background: white;
    color: #4a5568;
    text-decoration: none;
    border-radius: 6px;
    font-size: 14px;
    transition: all 0.2s ease;
}

.page-link:hover {
    background: #f8fafc;
    border-color: #667eea;
    color: #667eea;
}

.page-link.active {
    background: #667eea;
    border-color: #667eea;
    color: white;
}

.page-link.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #718096;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 20px;
    color: #cbd5e0;
}

.empty-state h3 {
    font-size: 1.2rem;
    margin-bottom: 10px;
    color: #4a5568;
}

.empty-state p {
    font-size: 0.9rem;
    margin-bottom: 20px;
}

.empty-state .btn {
    font-size: 14px;
    padding: 10px 20px;
    min-height: 40px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

/* 검색 하이라이트 스타일 */
.search-highlight {
    background: linear-gradient(135deg, #fef5e7 0%, #fed7aa 100%);
    color: #c05621;
    padding: 2px 4px;
    border-radius: 3px;
    font-weight: 600;
    border: 1px solid #fdba74;
    box-shadow: 0 1px 2px rgba(251, 191, 36, 0.1);
}


/* 검색 결과 요약 */
.search-summary {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 6px;
    padding: 12px 16px;
    margin-bottom: 20px;
    color: #1e40af;
}

.search-summary-title {
    font-weight: 600;
    margin-bottom: 4px;
    font-size: 0.9rem;
}

.search-summary-text {
    font-size: 0.85rem;
    opacity: 0.8;
}

/* 향상된 검색 폼 스타일 */
.search-form {
    position: relative;
    display: flex;
    align-items: center;
    gap: 10px;
}

.search-filter {
    padding: 12px 15px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    background: #fff;
    color: #4a5568;
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 100px;
    min-height: 44px;
    box-sizing: border-box;
}

.search-filter:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.search-input {
    padding: 12px 15px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    width: 250px;
    transition: all 0.3s ease;
    background: #fff;
    min-height: 44px;
    box-sizing: border-box;
}

.search-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    transform: translateY(-1px);
}

.search-input:not(:placeholder-shown) {
    border-color: #48bb78;
    background: #f0fff4;
}

.search-btn {
    background: linear-gradient(135deg, #374151 0%, #1f2937 100%);
    border: none;
    border-radius: 8px;
    padding: 12px 16px;
    color: #ffffff;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    transition: all 0.2s ease;
    min-height: 44px;
    min-width: 44px;
    box-sizing: border-box;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.search-btn:hover {
    transform: translateY(-1px) scale(1.05);
    box-shadow: 0 4px 12px rgba(55, 65, 81, 0.4);
}

/* 검색 힌트 */
.search-hints {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 8px 12px;
    margin-top: 8px;
    font-size: 0.8rem;
    color: #64748b;
    display: none;
}

.search-hints.show {
    display: block;
}

.search-hint-item {
    display: inline-block;
    background: #e2e8f0;
    padding: 2px 6px;
    border-radius: 3px;
    margin: 2px;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.search-hint-item:hover {
    background: #cbd5e0;
}

/* 모바일 반응형 */
/* 하이라이트 애니메이션 */
@keyframes highlight-pulse {
    0% {
        background: linear-gradient(135deg, #fef5e7 0%, #fed7aa 100%);
        transform: scale(1);
    }
    50% {
        background: linear-gradient(135deg, #fed7aa 0%, #fb923c 100%);
        transform: scale(1.05);
    }
    100% {
        background: linear-gradient(135deg, #fef5e7 0%, #fed7aa 100%);
        transform: scale(1);
    }
}

/* 페이드인 애니메이션 */
@keyframes fadeInUp {
    0% {
        opacity: 0;
        transform: translateY(10px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

/* 모바일 반응형 최적화 - UltraThink */
@media (max-width: 768px) {
    .community-container {
        padding: 16px;
        max-width: 100%;
        margin: 0;
        box-sizing: border-box;
    }
    
    .community-header {
        padding: 24px 16px;
        margin-top: 20px;
        margin-left: -16px;
        margin-right: -16px;
        border-radius: 0;
    }
    
    .community-header h1 {
        font-size: 1.75rem;
        line-height: 1.3;
    }
    
    .community-header p {
        font-size: 1rem;
    }
    
    .board-controls {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 24px;
    }

    .search-wrapper {
        width: 100%;
    }

    .search-form {
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
        width: 100%;
    }

    .search-filter {
        flex: 0 0 auto;
        width: auto;
        min-width: 80px;
        height: 44px;
        padding: 10px 12px;
        box-sizing: border-box;
    }

    .search-input {
        flex: 1 1 200px;
        min-width: 200px;
        height: 44px;
        padding: 10px 14px;
        box-sizing: border-box;
        font-size: 16px;
    }

    .search-btn {
        flex: 0 0 auto;
        width: 44px;
        height: 44px;
        padding: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
    }

    /* 액션 버튼들 컨테이너 */
    .board-controls > :not(.search-wrapper) {
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }
    
    .btn {
        justify-content: center;
        font-size: 14px;
        padding: 10px 16px;
        min-height: 44px;
        width: fit-content;
        max-width: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
    }

    .btn-write {
        justify-content: center;
        font-size: 14px;
        padding: 12px 16px;
        min-height: 44px;
        width: fit-content;
        max-width: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
        box-sizing: border-box;
    }
    
    .search-performance,
    .search-summary {
        font-size: 14px;
        padding: 12px 16px;
        margin: 16px 0;
        border-radius: 8px;
    }
    
    .post-item {
        padding: 20px 16px;
        margin-bottom: 8px;
        border-radius: 12px;
    }
    
    .post-title {
        font-size: 16px;
        line-height: 1.4;
        margin-bottom: 8px;
    }
    
    .post-content {
        font-size: 14px;
        line-height: 1.5;
    }
    
    .post-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
        font-size: 14px;
    }
    
    .pagination {
        flex-wrap: wrap;
        gap: 8px;
        justify-content: center;
    }
    
    .pagination a,
    .pagination span {
        min-width: 44px;
        min-height: 44px;
        padding: 12px;
        font-size: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* 프로필 이미지 최적화 */
    .profile-image {
        width: 48px;
        height: 48px;
        min-width: 48px;
        min-height: 48px;
    }
    
    /* 로딩 및 빈 상태 최적화 */
    .loading, .empty-state {
        padding: 40px 16px;
        text-align: center;
        font-size: 16px;
    }
}

/* PC와 모바일 모든 화면 크기에서 화이트 배경 일관성 유지 */

/* 글로벌 화이트 배경 강제 적용 */
body {
    background-color: white !important;
}

/* 모든 화면 크기에서 화이트 배경 유지 */
.community-container {
    background: white !important;
}

.post-list {
    background: white !important;
    border-color: #e2e8f0 !important;
}

.post-item {
    background: white !important;
    border-color: #e2e8f0 !important;
    color: #333 !important;
}

.post-item:hover {
    background-color: #f8fafc !important;
}

.post-title {
    color: #2d3748 !important;
}

.post-content {
    color: #4a5568 !important;
}

.post-meta {
    color: #718096 !important;
}

.board-controls {
    background: white !important;
    border-color: #e2e8f0 !important;
}

.search-form input, .search-form select {
    background: white !important;
    border-color: #e2e8f0 !important;
    color: #333 !important;
}

/* 모바일에서도 화이트 배경 강제 유지 */
@media (max-width: 768px) {
    body {
        background-color: white !important;
    }
    
    .community-container {
        background: white !important;
        padding: 15px;
    }
    
    .post-list {
        background: white !important;
        border-color: #e2e8f0 !important;
    }
    
    .post-item {
        background: white !important;
        border-color: #e2e8f0 !important;
        color: #333 !important;
    }
    
    .post-item:hover {
        background-color: #f8fafc !important;
    }
}

/* 다크모드 감지되어도 화이트 배경 강제 유지 */
/* 소형 모바일 최적화 */
@media (max-width: 480px) {
    .community-container {
        padding: 12px;
    }
    
    .community-header {
        padding: 20px 12px;
        margin-left: -12px;
        margin-right: -12px;
    }
    
    .community-header h1 {
        font-size: 1.5rem;
        line-height: 1.2;
    }
    
    .community-header p {
        font-size: 0.9rem;
    }
    
    .search-input {
        font-size: 16px; /* iOS 줌 방지 */
        padding: 10px 14px;
        min-height: 44px;
        height: 44px;
        box-sizing: border-box;
    }
    
    .btn {
        font-size: 14px !important;
        padding: 10px 14px !important;
        min-height: 42px !important;
        width: auto !important;
        display: inline-flex !important;
    }
    
    .btn-write {
        font-size: 14px !important;
        padding: 10px 14px !important;
        min-height: 42px !important;
        width: auto !important;
        display: inline-flex !important;
    }
    
    .post-item {
        padding: 16px 12px;
        gap: 10px;
    }
    
    .post-title {
        font-size: 15px;
    }
    
    .post-content,
    .post-meta {
        font-size: 13px;
    }
    
    .pagination a,
    .pagination span {
        min-width: 48px;
        min-height: 48px;
        font-size: 15px;
    }
    
    .profile-image {
        width: 44px;
        height: 44px;
        min-width: 44px;
        min-height: 44px;
    }
}

@media (prefers-color-scheme: dark) {
    body {
        background-color: white !important;
    }
    
    .community-container {
        background: white !important;
    }
    
    .post-list {
        background: white !important;
        border-color: #e2e8f0 !important;
    }
    
    .post-item {
        background: white !important;
        border-color: #e2e8f0 !important;
        color: #333 !important;
    }
    
    .post-item:hover {
        background-color: #f8fafc !important;
    }
    
    .post-title {
        color: #2d3748 !important;
    }
    
    .post-content {
        color: #4a5568 !important;
    }
    
    .post-meta {
        color: #718096 !important;
    }
    
    .board-controls {
        background: white !important;
        border-color: #e2e8f0 !important;
    }
    
    .search-form input, .search-form select {
        background: white !important;
        border-color: #e2e8f0 !important;
        color: #333 !important;
    }
}
</style>

<div class="community-container">
    <!-- 헤더 섹션 -->
    <div class="community-header">
        <h1>💬 커뮤니티 게시판</h1>
        <p>탑마케팅 커뮤니티에서 정보를 공유하고 함께 성장하세요</p>
    </div>
    
    <!-- 게시판 컨트롤 영역 -->
    <div class="board-controls">
        <!-- 검색 폼 -->
        <div class="search-wrapper">
            <form method="GET" action="/community" class="search-form">
                <!-- 검색 필터 선택 -->
                <select name="filter" class="search-filter" id="searchFilter">
                    <option value="all" <?= ($filter ?? 'all') === 'all' ? 'selected' : '' ?>>전체</option>
                    <option value="title" <?= ($filter ?? '') === 'title' ? 'selected' : '' ?>>제목만</option>
                    <option value="content" <?= ($filter ?? '') === 'content' ? 'selected' : '' ?>>내용만</option>
                    <option value="author" <?= ($filter ?? '') === 'author' ? 'selected' : '' ?>>작성자</option>
                </select>
                
                <input type="text" 
                       name="search" 
                       value="<?= htmlspecialchars($search ?? '') ?>" 
                       placeholder="검색어를 입력하세요..."
                       class="search-input"
                       maxlength="100"
                       autocomplete="off"
                       id="searchInput">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i>
                </button>
                <?php if (!empty($search)): ?>
                    <a href="/community<?= $filter && $filter !== 'all' ? '?filter=' . urlencode($filter) : '' ?>" class="btn btn-secondary" style="margin-left: 15px;">
                        ✖️ 검색 해제
                    </a>
                <?php endif; ?>
            </form>
            
            <!-- 검색 힌트 -->
            <div class="search-hints" id="searchHints">
                💡 검색 팁: 
                <span class="search-hint-item" data-search="마케팅">마케팅</span>
                <span class="search-hint-item" data-search="SNS">SNS</span>
                <span class="search-hint-item" data-search="광고">광고</span>
                <span class="search-hint-item" data-search="브랜딩">브랜딩</span>
                <span class="search-hint-item" data-search="전략">전략</span>
            </div>
        </div>
        
        <!-- 글쓰기 버튼 -->
        <?php if ($isLoggedIn): ?>
            <a href="/community/write" class="btn btn-write">
                <i class="fas fa-pen"></i> 글쓰기
            </a>
        <?php else: ?>
            <a href="/auth/login?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-primary">
                🔑 로그인 후 글쓰기
            </a>
        <?php endif; ?>
    </div>
    
    <!-- 통합 게시판 통계 -->
    <div class="board-stats">
        <p class="stats-text">
            <?php if (!empty($search)): ?>
                🔍 "<?= htmlspecialchars($search) ?>" 검색 결과: <strong><?= number_format($totalCount) ?></strong>개
                <?php
                $filterText = '';
                switch ($filter ?? 'all') {
                    case 'title': $filterText = '(제목에서 검색)'; break;
                    case 'content': $filterText = '(내용에서 검색)'; break;
                    case 'author': $filterText = '(작성자에서 검색)'; break;
                    case 'all': 
                    default: $filterText = '(전체에서 검색)'; break;
                }
                ?>
                <span style="font-size: 0.9em; color: #666; margin-left: 8px;"><?= $filterText ?></span>
            <?php else: ?>
                📊 총 <strong><?= number_format($totalCount) ?></strong>개의 게시글이 있습니다
            <?php endif; ?>
        </p>
    </div>
    
    <!-- 게시글 목록 -->
    <?php if (!empty($posts)): ?>
        <div class="post-list">
            <?php foreach ($posts as $post): ?>
                <div class="post-item" onclick="location.href='/community/posts/<?= $post['id'] ?><?php
                    $params = [];
                    if (isset($page) && $page > 1) $params['page'] = $page;
                    if (!empty($search)) $params['search'] = $search;
                    if (!empty($filter) && $filter !== 'all') $params['filter'] = $filter;
                    echo !empty($params) ? '?' . http_build_query($params) : '';
                ?>'">
                    <?php 
                    // 변수를 먼저 정의
                    $profileImage = $post['profile_image'] ?? null;
                    $authorName = $post['author_name'] ?? $post['nickname'] ?? '익명';
                    ?>
                    
                    <!-- 작성자 프로필 이미지 (통합 컴포넌트 사용) -->
                    <div class="post-author-avatar">
                        <?php 
                            // 사용자 정보만 추출 (게시글 ID 제외)
                            $user = [
                                'id' => $post['user_id'], // 실제 사용자 ID 사용
                                'user_id' => $post['user_id'],
                                'nickname' => $post['author_name'],
                                'author_name' => $post['author_name'],
                                'profile_image_original' => $post['profile_image_original'] ?? null,
                                'profile_image_profile' => $post['profile_image_profile'] ?? null,
                                'profile_image_thumb' => $post['profile_image_thumb'] ?? null,
                                'profile_image' => $post['profile_image'] ?? null
                            ];
                            $mode = 'api';
                            $extraClasses = [];
                            include SRC_PATH . '/views/components/profile-image.php';
                        ?>
                    </div>
                    
                    <!-- 게시글 내용 -->
                    <div class="post-content-wrapper">
                        <div class="post-title">
                            <?php 
                            $displayTitle = htmlspecialchars($post['title']);
                            if (!empty($search)) {
                                $displayTitle = SearchHelper::highlightSearchTerm($displayTitle, $search);
                            }
                            echo $displayTitle;
                            ?>
                            <?php if (($post['comment_count'] ?? 0) > 0): ?>
                                <span style="color: #e53e3e; font-size: 0.9rem;">[<?= $post['comment_count'] ?>]</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="post-meta">
                            <span class="post-author">👤 <?= htmlspecialchars($authorName) ?></span>
                            <span class="post-date">📅 <?= date('Y-m-d H:i', strtotime($post['created_at'])) ?></span>
                        </div>
                        
                        <div class="post-content-preview">
                            <?php
                            // 성능 최적화: 이미 DB에서 잘린 content_preview 사용
                            $content = $post['content_preview'] ?? $post['content'] ?? '';
                            
                            if (!empty($search)) {
                                // 검색어 중심의 스니펫 생성
                                $snippet = SearchHelper::generateSearchSnippet($content, $search, 150);
                                $preview = htmlspecialchars($snippet);
                                // 검색어 하이라이트 적용
                                $preview = SearchHelper::highlightSearchTerm($preview, $search);
                            } else {
                                // 일반 미리보기
                                $preview = htmlspecialchars(mb_substr(strip_tags($content), 0, 150));
                                if (mb_strlen($content) > 150) {
                                    $preview .= '...';
                                }
                            }
                            echo $preview;
                            ?>
                        </div>
                        
                        <div class="post-stats">
                            <span class="stat-item">
                                👁️ <?= number_format($post['view_count'] ?? 0) ?>
                            </span>
                            <span class="stat-item">
                                💬 <?= number_format($post['comment_count'] ?? 0) ?>
                            </span>
                            <span class="stat-item">
                                ❤️ <?= number_format($post['like_count'] ?? 0) ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- 페이지네이션 -->
        <?php 
        // 페이지네이션 링크 생성 함수
        function buildPaginationUrl($page, $search = null, $filter = 'all') {
            $params = ['page' => $page];
            if (!empty($search)) {
                $params['search'] = $search;
            }
            if ($filter && $filter !== 'all') {
                $params['filter'] = $filter;
            }
            return '?' . http_build_query($params);
        }
        ?>
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <!-- 이전 페이지 -->
                <?php if ($hasPrevPage): ?>
                    <a href="<?= buildPaginationUrl($currentPage - 1, $search, $filter) ?>" 
                       class="page-link">
                        ← 이전
                    </a>
                <?php else: ?>
                    <span class="page-link disabled">← 이전</span>
                <?php endif; ?>
                
                <!-- 페이지 번호들 -->
                <?php
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalPages, $currentPage + 2);
                
                if ($startPage > 1): ?>
                    <a href="<?= buildPaginationUrl(1, $search, $filter) ?>" 
                       class="page-link">1</a>
                    <?php if ($startPage > 2): ?>
                        <span class="page-link disabled">...</span>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <a href="<?= buildPaginationUrl($i, $search, $filter) ?>" 
                       class="page-link <?= $i === $currentPage ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($endPage < $totalPages): ?>
                    <?php if ($endPage < $totalPages - 1): ?>
                        <span class="page-link disabled">...</span>
                    <?php endif; ?>
                    <?php if ($totalPages > 0): ?>
                        <a href="<?= buildPaginationUrl($totalPages, $search, $filter) ?>" 
                           class="page-link"><?= number_format($totalPages) ?></a>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- 다음 페이지 -->
                <?php if ($hasNextPage): ?>
                    <a href="<?= buildPaginationUrl($currentPage + 1, $search, $filter) ?>" 
                       class="page-link">
                        다음 →
                    </a>
                <?php else: ?>
                    <span class="page-link disabled">다음 →</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
    <?php else: ?>
        <!-- 빈 상태 -->
        <div class="post-list">
            <div class="empty-state">
                <i>📝</i>
                <h3>
                    <?php if (!empty($search)): ?>
                        "<?= htmlspecialchars($search) ?>" 검색 결과가 없습니다
                    <?php elseif (isset($page) && isset($totalPages) && $page > $totalPages): ?>
                        접근할 수 없는 페이지입니다 (최대 <?= number_format($totalPages) ?>페이지)
                    <?php else: ?>
                        첫 번째 게시글을 작성해보세요!
                    <?php endif; ?>
                </h3>
                <p>
                    <?php if (!empty($search)): ?>
                        💡 검색 팁:<br>
                        • 검색어의 철자를 확인해보세요<br>
                        • 더 간단한 키워드로 다시 검색해보세요<br>
                        • 관련된 다른 단어로 검색해보세요
                    <?php elseif (isset($page) && isset($totalPages) && $page > $totalPages): ?>
                        요청하신 페이지 번호가 지원 범위를 초과합니다. (최대 <?= number_format($totalPages) ?>페이지)<br>
                        <a href="/community" style="color: #667eea;">커뮤니티 메인으로 이동</a>
                    <?php else: ?>
                        탑마케팅 커뮤니티의 첫 번째 이야기를 시작해보세요.
                    <?php endif; ?>
                </p>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- 프로필 이미지 모달은 profile-modal.js에서 동적 생성됨 -->

<script>
// 성능 모니터링 및 사용자 경험 개선
document.addEventListener('DOMContentLoaded', function() {
    const loadStartTime = performance.now();
    
    console.log('📋 커뮤니티 게시판 로드 완료');
    console.log('📊 게시글 수:', <?= count($posts ?? []) ?>);
    console.log('📄 현재 페이지:', <?= isset($currentPage) ? $currentPage : 1 ?>);
    console.log('📄 총 페이지:', <?= isset($totalPages) ? $totalPages : 1 ?>);
    <?php if (!empty($search)): ?>
    console.log('🔍 검색어:', '<?= addslashes($search) ?>');
    console.log('⚡ 검색 시간:', '<?= $searchTime ?>ms');
    <?php endif; ?>
    
    const loadEndTime = performance.now();
    const loadTime = Math.round(loadEndTime - loadStartTime);
    console.log(`⚡ 페이지 렌더링 완료: ${loadTime}ms`);
    
    // 검색 폼 향상된 기능
    const searchInput = document.querySelector('#searchInput');
    const searchForm = document.querySelector('.search-form');
    const searchHints = document.querySelector('#searchHints');
    
    if (searchInput && searchForm) {
        // 엔터키 처리
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                
                // 검색어 유효성 검사
                const searchTerm = this.value.trim();
                if (searchTerm.length < 2) {
                    alert('검색어는 2자 이상 입력해주세요.');
                    return;
                }
                
                searchForm.submit();
            }
        });
        
        // 실시간 검색어 길이 체크 및 힌트 표시
        searchInput.addEventListener('input', function(e) {
            const length = this.value.length;
            if (length > 100) {
                this.value = this.value.substring(0, 100);
            }
            
            // 시각적 피드백
            if (length >= 2) {
                this.style.borderColor = '#48bb78';
                this.style.background = '#f0fff4';
            } else if (length > 0) {
                this.style.borderColor = '#f56565';
                this.style.background = '#fef2f2';
            } else {
                this.style.borderColor = '#e2e8f0';
                this.style.background = '#fff';
            }
        });
        
        // 포커스 시 힌트 표시
        searchInput.addEventListener('focus', function() {
            if (this.value.length === 0 && searchHints) {
                searchHints.classList.add('show');
            }
        });
        
        // 포커스 아웃 시 힌트 숨김 (딜레이 추가)
        searchInput.addEventListener('blur', function() {
            setTimeout(() => {
                if (searchHints) {
                    searchHints.classList.remove('show');
                }
            }, 200);
        });
        
        // 검색 힌트 클릭 처리
        if (searchHints) {
            const hintItems = searchHints.querySelectorAll('.search-hint-item');
            hintItems.forEach(item => {
                item.addEventListener('click', function() {
                    const searchTerm = this.getAttribute('data-search');
                    searchInput.value = searchTerm;
                    searchForm.submit();
                });
            });
        }
        
        // 검색 결과 하이라이트 애니메이션
        const highlights = document.querySelectorAll('.search-highlight');
        highlights.forEach((highlight, index) => {
            setTimeout(() => {
                highlight.style.animation = 'highlight-pulse 0.6s ease-in-out';
            }, index * 100);
        });
        
        // 검색 완료 후 입력 필드 상태 정상화
        <?php if (!empty($search)): ?>
        // 검색어가 있을 때만 실행 - 검색 완료 후 1초 후 시각적 상태 정상화
        setTimeout(() => {
            if (searchInput) {
                // 검색 입력 필드 스타일 초기화 (정상 상태로)
                searchInput.style.borderColor = '#e2e8f0';
                searchInput.style.background = '#fff';
                
                // 검색 버튼 hover 효과 정상화
                const searchBtn = document.querySelector('.search-btn');
                if (searchBtn) {
                    searchBtn.style.transform = 'none';
                }
                
                console.log('🔍 검색 완료: 입력 필드 상태 정상화');
            }
        }, 1000);
        <?php endif; ?>
        
    }
    
    // 게시글 항목 호버 효과 개선
    const postItems = document.querySelectorAll('.post-item');
    postItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(4px)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });
    
    // 프로필 이미지 클릭 이벤트는 통합 컴포넌트에서 자동 처리됨
    
    // 커뮤니티 관련 전역 객체 정의 (오류 방지용)
    if (typeof window.community === 'undefined') {
        window.community = {
            initialized: true,
            currentPage: <?= isset($currentPage) ? $currentPage : 1 ?>,
            totalPages: <?= isset($totalPages) ? $totalPages : 1 ?>,
            postCount: <?= count($posts ?? []) ?>,
            searchQuery: '<?= addslashes($search ?? '') ?>',
            hasNextPage: <?= isset($hasNextPage) ? ($hasNextPage ? 'true' : 'false') : 'false' ?>,
            hasPrevPage: <?= isset($hasPrevPage) ? ($hasPrevPage ? 'true' : 'false') : 'false' ?>
        };
    }
    
    // 추가 안전장치: 필수 DOM 요소 존재 확인
    const requiredElements = [
        '.search-input',
        '.post-item',
        '.pagination'
    ];
    
    requiredElements.forEach(selector => {
        const elements = document.querySelectorAll(selector);
        if (elements.length === 0) {
            console.log(`🚀 요소 없음: ${selector} (정상 - 빈 게시판일 수 있음)`);
        }
    });
    
    // 전역 오류 핸들러 (커뮤니티 페이지 전용)
    window.addEventListener('error', function(event) {
        if (event.filename && event.filename.includes('community')) {
            console.warn('🚀 커뮤니티 페이지 JavaScript 오류 감지:', {
                message: event.message,
                filename: event.filename,
                lineno: event.lineno,
                colno: event.colno
            });
            
            // 사용자에게는 오류를 표시하지 않고 조용히 처리
            event.preventDefault();
        }
    });
    
    // 브라우저 확장 프로그램 비동기 오류 무시
    window.addEventListener('unhandledrejection', function(event) {
        if (event.reason && event.reason.message && 
            event.reason.message.includes('message channel closed')) {
            // 브라우저 확장 프로그램 오류는 조용히 무시
            event.preventDefault();
        }
    });
});



// 기존 프로필 이미지 모달 JavaScript 함수들 제거됨 - profile-modal.js 통합 시스템 사용
</script>

