<?php
/**
 * 커뮤니티 게시판 메인 페이지 (게시글 목록)
 */

// 로그인 상태 확인
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
require_once SRC_PATH . '/helpers/HtmlSanitizerHelper.php';
require_once SRC_PATH . '/helpers/SearchHelper.php';
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
    padding: 10px 15px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    width: 250px;
    transition: border-color 0.3s ease;
}

.search-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

/* 프로필 이미지 모달 스타일 */
.profile-image-modal {
    display: none;
    position: fixed;
    z-index: 10000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(5px);
}

.profile-image-modal .modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border-radius: 16px;
    min-width: 400px;
    max-width: 95vw;
    max-height: 95vh;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    overflow: hidden;
}

.profile-image-modal .modal-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8fafc;
}

.profile-image-modal .modal-header h3 {
    margin: 0;
    color: #2d3748;
    font-size: 1.2rem;
    font-weight: 600;
}

.profile-image-modal .modal-close {
    background: none;
    border: none;
    font-size: 28px;
    color: #718096;
    cursor: pointer;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s ease;
}

.profile-image-modal .modal-close:hover {
    background: #e2e8f0;
    color: #2d3748;
}

.profile-image-modal .modal-body {
    padding: 24px;
    text-align: center;
    background: white;
    max-height: calc(95vh - 80px);
    overflow: auto;
}

.profile-image-modal .modal-body img {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    max-width: calc(95vw - 100px);
    max-height: calc(95vh - 150px);
    width: auto;
    height: auto;
    border-radius: 12px;
    object-fit: contain;
    transition: all 0.3s ease;
}

/* 모바일에서 더 큰 이미지 표시 */
@media (max-width: 768px) {
    .profile-image-modal .modal-content {
        min-width: 300px;
        max-width: 98vw;
        max-height: 98vh;
        margin: 10px;
    }
    
    .profile-image-modal .modal-body {
        padding: 16px;
        max-height: calc(98vh - 60px);
    }
    
    .profile-image-modal .modal-body img {
        max-width: calc(98vw - 50px);
        max-height: calc(98vh - 120px);
    }
    
    .profile-image-modal .modal-header {
        padding: 15px 20px;
    }
    
    .profile-image-modal .modal-header h3 {
        font-size: 1.1rem;
    }
}

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
    font-weight: 700;
}

.btn-write:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(55, 65, 81, 0.4);
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

/* 검색 성능 정보 */
.search-performance {
    background: #f0fff4;
    border: 1px solid #c6f6d5;
    border-radius: 6px;
    padding: 8px 12px;
    margin-bottom: 15px;
    font-size: 0.85rem;
    color: #276749;
    display: flex;
    align-items: center;
    gap: 8px;
}

.search-performance .icon {
    font-size: 1rem;
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
}

.search-filter:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.search-input {
    padding: 12px 45px 12px 15px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    width: 250px;
    transition: all 0.3s ease;
    background: #fff;
    position: relative;
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
    position: absolute;
    right: 4px;
    top: 50%;
    transform: translateY(-50%);
    background: linear-gradient(135deg, #374151 0%, #1f2937 100%);
    border: none;
    border-radius: 6px;
    padding: 8px 12px;
    color: #ffffff;
    cursor: pointer;
    font-size: 14px;
    font-weight: bold;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    transition: all 0.2s ease;
}

.search-btn:hover {
    transform: translateY(-50%) scale(1.05);
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

@media (max-width: 768px) {
    .community-container {
        padding: 15px;
    }
    
    .community-header {
        padding: 30px 20px;
    }
    
    .community-header h1 {
        font-size: 2rem;
    }
    
    .board-controls {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-form {
        justify-content: center;
        margin-bottom: 15px;
    }
    
    .search-input {
        width: 100%;
        max-width: 300px;
    }
    
    .search-performance,
    .search-summary {
        font-size: 0.8rem;
        padding: 8px 12px;
    }
    
    .post-item {
        padding: 15px;
    }
    
    .post-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .pagination {
        flex-wrap: wrap;
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
                    <a href="/community" class="btn btn-secondary" style="margin-left: 10px;">
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
    
    <!-- 검색 성능 정보 -->
    <?php if (!empty($search)): ?>
        <div class="search-performance">
            <span class="icon">⚡</span>
            <span>검색 완료: <?= $searchTime ?>ms | 총 <?= number_format($totalCount) ?>건 발견</span>
            <?php if ($totalCount > $pageSize): ?>
                <span style="margin-left: 8px; opacity: 0.7;">
                    (페이지당 <?= $pageSize ?>건씩 표시)
                </span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- 검색 결과 요약 -->
    <?php if (!empty($search)): ?>
        <div class="search-summary">
            <div class="search-summary-title">
                "<?= htmlspecialchars($search) ?>" 검색 결과
            </div>
            <div class="search-summary-text">
                <?php
                $filterText = '';
                switch ($filter ?? 'all') {
                    case 'title': $filterText = '제목에서'; break;
                    case 'content': $filterText = '내용에서'; break;
                    case 'author': $filterText = '작성자에서'; break;
                    case 'all': 
                    default: $filterText = '전체에서'; break;
                }
                ?>
                <?= $filterText ?> <?= number_format($totalCount) ?>개의 관련 내용을 찾았습니다.
                <span style="font-size: 0.9em; color: #666; margin-left: 10px;">
                    ℹ️ 최근 500개 게시글 대상
                </span>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- 게시판 통계 -->
    <div class="board-stats">
        <p class="stats-text">
            📊 총 <strong><?= number_format($totalCount) ?></strong>개의 게시글이 있습니다
            <?php if (!empty($search)): ?>
                (검색 결과)
            <?php endif; ?>
        </p>
    </div>
    
    <!-- 게시글 목록 -->
    <?php if (!empty($posts)): ?>
        <div class="post-list">
            <?php foreach ($posts as $post): ?>
                <div class="post-item" onclick="location.href='/community/posts/<?= $post['id'] ?>'">
                    <?php 
                    // 변수를 먼저 정의
                    $profileImage = $post['profile_image'] ?? null;
                    $authorName = $post['author_name'] ?? $post['nickname'] ?? '익명';
                    ?>
                    
                    <!-- 작성자 프로필 이미지 -->
                    <div class="post-author-avatar profile-image-clickable" 
                         data-user-id="<?= htmlspecialchars($post['user_id']) ?>" 
                         data-user-name="<?= htmlspecialchars($authorName) ?>" 
                         style="cursor: pointer;" 
                         title="프로필 이미지 크게 보기">
                        <?php
                        
                        if ($profileImage): ?>
                            <img src="<?= htmlspecialchars($profileImage) ?>" 
                                 alt="<?= htmlspecialchars($authorName) ?>" 
                                 loading="lazy"
                                 width="50" 
                                 height="50"
                                 style="object-fit: cover; border-radius: 50%;"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div style="display: none; width: 50px; height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 1.1rem;">
                                <?= mb_substr($authorName, 0, 1) ?>
                            </div>
                        <?php else: ?>
                            <?= mb_substr($authorName, 0, 1) ?>
                        <?php endif; ?>
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
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <!-- 이전 페이지 -->
                <?php if ($hasPrevPage): ?>
                    <a href="?page=<?= $currentPage - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
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
                    <a href="?page=1<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                       class="page-link">1</a>
                    <?php if ($startPage > 2): ?>
                        <span class="page-link disabled">...</span>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <a href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                       class="page-link <?= $i === $currentPage ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($endPage < $totalPages): ?>
                    <?php if ($endPage < $totalPages - 1): ?>
                        <span class="page-link disabled">...</span>
                    <?php endif; ?>
                    <?php if ($totalPages > 0): ?>
                        <a href="?page=<?= $totalPages ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                           class="page-link"><?= number_format($totalPages) ?></a>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- 다음 페이지 -->
                <?php if ($hasNextPage): ?>
                    <a href="?page=<?= $currentPage + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
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
                    <?php else: ?>
                        탑마케팅 커뮤니티의 첫 번째 이야기를 시작해보세요.
                    <?php endif; ?>
                </p>
                <?php if ($isLoggedIn): ?>
                    <a href="/community/write" class="btn btn-primary">
                        <i class="fas fa-pen"></i> 글쓰기
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- 프로필 이미지 확대 모달 -->
<div id="profileImageModal" class="profile-image-modal" onclick="closeProfileImageModal()">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 id="modalUserName">사용자 프로필</h3>
            <button class="modal-close" onclick="closeProfileImageModal()">&times;</button>
        </div>
        <div class="modal-body">
            <img id="modalProfileImage" src="" alt="프로필 이미지" style="max-width: 100%; max-height: 80vh; border-radius: 8px;">
        </div>
    </div>
</div>

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
        
        // 검색 성능 측정 및 표시
        <?php if (!empty($search)): ?>
        const performanceInfo = document.querySelector('.search-performance');
        if (performanceInfo) {
            // 검색 완료 후 성능 정보 강조
            setTimeout(() => {
                performanceInfo.style.animation = 'fadeInUp 0.5s ease-out';
            }, 500);
        }
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
    
    // 프로필 이미지 클릭 이벤트 처리 (지연 로딩)
    const profileImages = document.querySelectorAll('.profile-image-clickable');
    profileImages.forEach(element => {
        element.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();
            
            const userId = this.getAttribute('data-user-id');
            const userName = this.getAttribute('data-user-name');
            
            if (!userId) {
                console.error('사용자 ID가 없습니다.');
                return false;
            }
            
            // AJAX로 원본 이미지 URL 가져오기
            fetchProfileImage(userId, userName);
            
            return false;
        });
    });
    
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



// AJAX로 원본 프로필 이미지 정보 가져오기
function fetchProfileImage(userId, userName) {
    const modal = document.getElementById('profileImageModal');
    const modalImage = document.getElementById('modalProfileImage');
    const modalUserName = document.getElementById('modalUserName');
    
    if (!modal || !modalImage || !modalUserName) {
        console.error('프로필 모달 요소를 찾을 수 없습니다.');
        return;
    }
    
    // 모달 열기 및 로딩 상태 표시
    modalUserName.textContent = userName + '님의 프로필';
    modalImage.style.display = 'none';
    modalImage.src = ''; // 기존 이미지 제거
    modal.style.display = 'block';
    
    // 로딩 스피너 표시
    const modalBody = modalImage.parentNode;
    const loadingSpinner = document.createElement('div');
    loadingSpinner.id = 'imageLoadingSpinner';
    loadingSpinner.innerHTML = '<div style="text-align: center; padding: 50px; color: #666;"><div style="display: inline-block; width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #667eea; border-radius: 50%; animation: spin 1s linear infinite;"></div><p style="margin-top: 15px;">이미지 로딩 중...</p></div>';
    modalBody.appendChild(loadingSpinner);
    
    // 스피너 애니메이션 CSS 추가
    if (!document.getElementById('spinnerStyle')) {
        const style = document.createElement('style');
        style.id = 'spinnerStyle';
        style.textContent = '@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }';
        document.head.appendChild(style);
    }
    
    // AJAX 요청
    fetch(`/api/users/${userId}/profile-image`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            // 로딩 스피너 제거
            const spinner = document.getElementById('imageLoadingSpinner');
            if (spinner) {
                spinner.remove();
            }
            
            if (data.original_image) {
                showProfileImageModal(data.original_image, userName);
            } else {
                alert('원본 프로필 이미지를 찾을 수 없습니다.');
                closeProfileImageModal();
            }
        })
        .catch(error => {
            console.error('프로필 이미지 로딩 오류:', error);
            
            // 로딩 스피너 제거
            const spinner = document.getElementById('imageLoadingSpinner');
            if (spinner) {
                spinner.remove();
            }
            
            alert('이미지를 불러오는 중 오류가 발생했습니다.');
            closeProfileImageModal();
        });
        
    // ESC 키로 모달 닫기
    document.addEventListener('keydown', handleModalEscKey);
}

// 프로필 이미지 모달 함수
function showProfileImageModal(imageSrc, userName) {
    if (!imageSrc || imageSrc.trim() === '') {
        alert('원본 프로필 이미지를 찾을 수 없습니다.');
        return;
    }
    
    const modal = document.getElementById('profileImageModal');
    const modalImage = document.getElementById('modalProfileImage');
    const modalUserName = document.getElementById('modalUserName');
    
    if (!modal || !modalImage || !modalUserName) {
        console.error('프로필 모달 요소를 찾을 수 없습니다.');
        return;
    }
    
    // 이미지 미리 로딩 후 표시
    const img = new Image();
    img.onload = function() {
        modalImage.src = imageSrc;
        modalImage.style.display = 'block';
    };
    img.onerror = function() {
        modalImage.style.display = 'none';
        alert('이미지를 로딩할 수 없습니다.');
        closeProfileImageModal();
    };
    img.src = imageSrc;
}

function closeProfileImageModal() {
    const modal = document.getElementById('profileImageModal');
    if (modal) {
        modal.style.display = 'none';
    }
    
    // ESC 키 이벤트 제거
    document.removeEventListener('keydown', handleModalEscKey);
}

function handleModalEscKey(event) {
    if (event.key === 'Escape') {
        closeProfileImageModal();
    }
}
</script>

<!-- 성능 디버그 정보 (검색 시 또는 debug 파라미터 시에만 표시) -->
<?php if (isset($showDebugInfo) && $showDebugInfo && isset($performanceLogs)): ?>
<div style="background: #f8f9fa; padding: 15px; margin: 20px 0; border-radius: 5px; font-family: monospace; font-size: 12px; border-left: 4px solid #007bff;">
    <h4 style="margin: 0 0 10px 0; color: #007bff;">🔍 실시간 성능 로그</h4>
    <?php foreach ($performanceLogs as $log): ?>
        <?php
        $color = '#333';
        if (strpos($log, '[CONTROLLER]') !== false) $color = '#007bff';
        if (strpos($log, '[SEARCH]') !== false) $color = '#28a745';
        if (strpos($log, '[COUNT]') !== false) $color = '#ffc107';
        if (strpos($log, '[CACHE]') !== false) $color = '#6f42c1';
        ?>
        <div style="color: <?= $color ?>; margin: 2px 0; line-height: 1.3;">
            <?= htmlspecialchars($log) ?>
        </div>
    <?php endforeach; ?>
    <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #ddd; font-size: 11px; color: #666;">
        💡 이 정보는 검색 시 또는 URL에 ?debug 파라미터 추가 시에만 표시됩니다.
    </div>
</div>
<?php endif; ?>