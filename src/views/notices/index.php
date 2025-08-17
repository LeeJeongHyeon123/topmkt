<?php
/**
 * 공지사항 목록 페이지
 * 기존 커뮤니티 패턴을 따라 개발
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

// ProfileImageHelper 로드
require_once SRC_PATH . '/helpers/ProfileImageHelper.php';
?>

<!-- 성능 최적화 리소스 힌트 -->
<link rel="preconnect" href="https://www.topmktx.com">
<link rel="dns-prefetch" href="//www.topmktx.com">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="theme-color" content="#2563eb">

<!-- 프로필 이미지 모달 통합 리소스 -->
<?php include SRC_PATH . '/views/components/profile-modal-resources.php'; ?>

<style>
/* 공지사항 게시판 스타일 - 커뮤니티와 구분되는 고유한 테마 */
.notices-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    min-height: calc(100vh - 200px);
}

.notices-header {
    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
    color: white;
    padding: 40px 0;
    text-align: center;
    margin-top: 60px;
    margin-bottom: 30px;
    border-radius: 12px;
}

.notices-header h1 {
    font-size: 2.5rem;
    margin-bottom: 10px;
    font-weight: 700;
}

.notices-header p {
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
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.company-filter {
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 16px;
    background: #fff;
    color: #4a5568;
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 120px;
    min-height: 44px;
    box-sizing: border-box;
}

.company-filter:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.btn {
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    line-height: 1.4;
    white-space: nowrap;
    min-height: 44px;
    box-sizing: border-box;
}

.btn i {
    font-size: 16px !important;
}

.btn-primary {
    background: #2563eb;
    color: white;
}

.btn-primary:hover {
    background: #1d4ed8;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
}

.btn-secondary {
    background: #718096;
    color: white;
}

.btn-secondary:hover {
    background: #4a5568;
}

/* 공지사항 전용 작성 버튼 - 다른 버튼과 조화 */
a.btn-write {
    background: #059669 !important;
    color: white !important;
    font-weight: 500 !important;
    padding: 12px 20px !important;
    font-size: 16px !important;
    border-radius: 8px !important;
    border: none !important;
    cursor: pointer !important;
    text-decoration: none !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    transition: all 0.3s ease !important;
    line-height: 1.4 !important;
    white-space: nowrap !important;
    box-shadow: none !important;
    height: auto !important;
    min-height: 44px !important;
    vertical-align: top !important;
    margin: 0 !important;
    overflow: hidden !important;
    box-sizing: border-box !important;
}

/* Font Awesome 아이콘 - 다른 버튼과 일치 */
a.btn-write i {
    font-size: 16px !important;
    line-height: 1.4 !important;
    display: inline-block !important;
    vertical-align: middle !important;
    text-align: center !important;
    margin: 0 !important;
    padding: 0 !important;
}

a.btn-write i::before {
    line-height: 1.4 !important;
    vertical-align: baseline !important;
}

a.btn-write span {
    line-height: 1.3 !important;
    display: inline-block !important;
    vertical-align: middle !important;
}

a.btn-write:hover {
    background: #047857 !important;
    transform: translateY(-1px) !important;
    text-decoration: none !important;
    box-shadow: 0 2px 8px rgba(5, 150, 105, 0.2) !important;
}

a.btn-write:active {
    transform: translateY(0) !important;
}

.board-stats {
    background: #f0f9ff;
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-left: 4px solid #2563eb;
}

.stats-text {
    color: #4a5568;
    font-size: 14px;
    margin: 0;
}

.notice-list {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
}

.notice-item {
    padding: 20px;
    border-bottom: 1px solid #e2e8f0;
    transition: background-color 0.2s ease;
    cursor: pointer;
    display: flex;
    gap: 15px;
    align-items: flex-start;
}

.notice-item:hover {
    background-color: #f8fafc;
}

.notice-item.featured {
    background: linear-gradient(135deg, #fef3c7 0%, #fbbf24 10%, #fef3c7 100%);
    border-left: 4px solid #f59e0b;
}

.notice-item.featured:hover {
    background: linear-gradient(135deg, #fef3c7 0%, #f59e0b 15%, #fef3c7 100%);
}

.notice-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 12px;
    margin-left: 8px;
}

.notice-badge.featured {
    background: #dc2626;
    color: white;
}

.company-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
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

.company-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    will-change: auto;
    transition: none;
}

.notice-content-wrapper {
    flex: 1;
    min-width: 0;
}

.notice-item:last-child {
    border-bottom: none;
}

.notice-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 8px;
    line-height: 1.4;
}

.notice-title:hover {
    color: #2563eb;
}

.notice-meta {
    display: flex;
    align-items: center;
    gap: 15px;
    font-size: 0.9rem;
    color: #718096;
    margin-bottom: 10px;
}

.notice-company {
    font-weight: 600;
    color: #4a5568;
}

.notice-date {
    color: #a0aec0;
}

.notice-content-preview {
    color: #718096;
    font-size: 0.9rem;
    line-height: 1.5;
    margin-top: 8px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.notice-stats {
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
    border-color: #2563eb;
    color: #2563eb;
}

.page-link.active {
    background: #2563eb;
    border-color: #2563eb;
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

/* 검색 결과 요약 */
.search-summary {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 6px;
    padding: 12px 16px;
    margin-bottom: 20px;
    color: #1e40af;
}

/* 모바일 반응형 */
@media (max-width: 768px) {
    .notices-container {
        padding: 15px;
    }
    
    .notices-header {
        padding: 30px 20px;
    }
    
    .notices-header h1 {
        font-size: 2rem;
    }
    
    .board-controls {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-form {
        justify-content: center;
        margin-bottom: 15px;
        flex-wrap: wrap;
    }
    
    .search-input, .company-filter {
        width: 100%;
        max-width: 300px;
        margin-bottom: 10px;
    }
    
    .notice-item {
        padding: 15px;
        flex-direction: column;
        gap: 10px;
    }
    
    .company-avatar {
        width: 40px;
        height: 40px;
        font-size: 1rem;
        align-self: flex-start;
    }
    
    .notice-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .pagination {
        flex-wrap: wrap;
    }
}

/* 기존 프로필 이미지 모달 스타일 제거됨 - 통합 CSS 사용 */


/* 화이트 배경 일관성 유지 */
body {
    background-color: white !important;
}

.notices-container {
    background: white !important;
}

.notice-list {
    background: white !important;
    border-color: #e2e8f0 !important;
}

.notice-item {
    background: white !important;
    border-color: #e2e8f0 !important;
    color: #333 !important;
}

.notice-item:hover {
    background-color: #f8fafc !important;
}

.notice-title {
    color: #2d3748 !important;
}

.notice-content-preview {
    color: #718096 !important;
}

.notice-meta {
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

.notice-item.featured {
    background: linear-gradient(135deg, #fef3c7 0%, #fbbf24 10%, #fef3c7 100%) !important;
}
/* 소형 모바일에서 추가 최적화 */
@media (max-width: 480px) {
    .notices-container {
        padding: 12px;
    }
    
    .notices-header {
        padding: 20px 12px;
        margin-left: -12px;
        margin-right: -12px;
    }
    
    .notices-header h1 {
        font-size: 1.5rem;
        line-height: 1.2;
    }
    
    .notices-header p {
        font-size: 0.9rem;
    }
    
    .search-input, .company-filter {
        font-size: 16px; /* iOS 줌 방지 */
        padding: 16px;
        min-height: 52px;
    }
    
    .btn, .btn-write {
        font-size: 16px !important;
        padding: 16px 20px !important;
        min-height: 52px !important;
    }
    
    .notice-item {
        padding: 16px 12px;
        gap: 10px;
    }
    
    .company-avatar {
        width: 44px;
        height: 44px;
        font-size: 1rem;
    }
    
    .notice-title {
        font-size: 15px;
    }
    
    .notice-content-preview,
    .notice-meta {
        font-size: 13px;
    }
    
    .pagination a,
    .pagination span {
        min-width: 48px;
        min-height: 48px;
        font-size: 15px;
    }
}
</style>

<div class="notices-container">
    <!-- 헤더 섹션 -->
    <div class="notices-header">
        <h1>📢 공지사항</h1>
        <p>중요한 소식과 업데이트를 확인하세요</p>
    </div>
    
    <!-- 게시판 컨트롤 영역 -->
    <div class="board-controls">
        <!-- 검색 폼 -->
        <div class="search-wrapper">
            <form method="GET" action="/notices" class="search-form">
                <!-- 기업명 검색 -->
                <input type="text" 
                       name="company" 
                       value="<?= htmlspecialchars($company ?? '') ?>" 
                       placeholder="기업명 검색..."
                       class="company-filter"
                       id="companyFilter"
                       autocomplete="off"
                       maxlength="50">
                
                <!-- 검색 필터 선택 -->
                <select name="filter" class="company-filter" id="searchFilter">
                    <option value="all" <?= ($filter ?? 'all') === 'all' ? 'selected' : '' ?>>전체</option>
                    <option value="title" <?= ($filter ?? '') === 'title' ? 'selected' : '' ?>>제목만</option>
                    <option value="content" <?= ($filter ?? '') === 'content' ? 'selected' : '' ?>>내용만</option>
                    <option value="company" <?= ($filter ?? '') === 'company' ? 'selected' : '' ?>>기업명만</option>
                </select>
                
                <input type="text" 
                       name="search" 
                       value="<?= htmlspecialchars($search ?? '') ?>" 
                       placeholder="검색어를 입력하세요..."
                       class="search-input"
                       maxlength="100"
                       autocomplete="off"
                       id="searchInput">
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> 검색
                </button>
                
                <?php if (!empty($search) || !empty($company)): ?>
                    <a href="/notices" class="btn btn-secondary">
                        <i class="fas fa-times"></i> 초기화
                    </a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- 글쓰기 버튼 (기업 사용자만) -->
        <?php if ($isLoggedIn && $canWrite): ?>
            <a href="/notices/write" class="btn-write">
                <i class="fas fa-edit"></i>
                <span>공지 작성</span>
            </a>
        <?php elseif ($isLoggedIn): ?>
            <span class="btn" style="background: #e2e8f0; color: #718096; cursor: not-allowed;">
                <i class="fas fa-lock"></i> 기업 회원만 작성 가능
            </span>
        <?php else: ?>
            <a href="/auth/login?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-primary">
                <i class="fas fa-sign-in-alt"></i> 로그인
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
    <?php if (!empty($search) || !empty($company)): ?>
        <div class="search-summary">
            <div style="font-weight: 600; margin-bottom: 4px; font-size: 0.9rem;">
                <?php if (!empty($search)): ?>
                    "<?= htmlspecialchars($search) ?>" 검색 결과
                <?php endif; ?>
                <?php if (!empty($company)): ?>
                    - "<?= htmlspecialchars($company) ?>" 기업 공지사항
                <?php endif; ?>
            </div>
            <div style="font-size: 0.85rem; opacity: 0.8;">
                <?php
                $filterText = '';
                switch ($filter ?? 'all') {
                    case 'title': $filterText = '제목에서'; break;
                    case 'content': $filterText = '내용에서'; break;
                    case 'all': 
                    default: $filterText = '전체에서'; break;
                }
                ?>
                <?= $filterText ?> <?= number_format($totalCount) ?>개의 공지사항을 찾았습니다.
            </div>
        </div>
    <?php endif; ?>
    
    <!-- 게시판 통계 -->
    <div class="board-stats">
        <p class="stats-text">
            📊 총 <strong><?= number_format($totalCount) ?></strong>개의 공지사항이 있습니다
            <?php if (!empty($search) || !empty($company)): ?>
                (검색/필터 결과)
            <?php endif; ?>
        </p>
    </div>
    
    <!-- 공지사항 목록 -->
    <?php if (!empty($notices)): ?>
        <div class="notice-list">
            <?php foreach ($notices as $notice): ?>
                <div class="notice-item" 
                     onclick="location.href='/notices/<?= $notice['id'] ?>'">
                    
                    <!-- 기업 아바타 (통합 컴포넌트 사용) -->
                    <div class="company-avatar">
                        <?php 
                            // 사용자 정보만 추출 (공지사항 ID와 구분)
                            $user = [
                                'id' => $notice['user_id'], // 실제 사용자 ID 사용
                                'user_id' => $notice['user_id'],
                                'nickname' => $notice['nickname'] ?? $notice['author_name'] ?? '알 수 없음',
                                'author_name' => $notice['nickname'] ?? $notice['author_name'] ?? '알 수 없음',
                                'profile_image_original' => $notice['profile_image_original'] ?? null,
                                'profile_image_profile' => $notice['profile_image_profile'] ?? null,
                                'profile_image_thumb' => $notice['profile_image_thumb'] ?? null,
                                'profile_image' => $notice['profile_image'] ?? null
                            ];
                            $mode = 'api';
                            $extraClasses = [];
                            include SRC_PATH . '/views/components/profile-image.php';
                        ?>
                    </div>
                    
                    <!-- 공지사항 내용 -->
                    <div class="notice-content-wrapper">
                        <div class="notice-title">
                            <?php 
                            $displayTitle = htmlspecialchars($notice['title']);
                            if (!empty($search)) {
                                $displayTitle = SearchHelper::highlightSearchTerm($displayTitle, $search);
                            }
                            echo $displayTitle;
                            ?>
                            <?php if (($notice['comment_count'] ?? 0) > 0): ?>
                                <span style="color: #2563eb; font-size: 0.9rem; margin-left: 8px;">[<?= $notice['comment_count'] ?>]</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="notice-meta">
                            <span class="notice-company">🏢 <?= htmlspecialchars($notice['company_name']) ?></span>
                            <span class="notice-date">📅 <?= date('Y-m-d H:i', strtotime($notice['created_at'])) ?></span>
                        </div>
                        
                        <div class="notice-content-preview">
                            <?php
                            $content = $notice['content_preview'] ?? $notice['content'] ?? '';
                            
                            if (!empty($search)) {
                                $snippet = SearchHelper::generateSearchSnippet($content, $search, 150);
                                $preview = htmlspecialchars($snippet);
                                $preview = SearchHelper::highlightSearchTerm($preview, $search);
                            } else {
                                $preview = htmlspecialchars(mb_substr(strip_tags($content), 0, 150));
                                if (mb_strlen($content) > 150) {
                                    $preview .= '...';
                                }
                            }
                            echo $preview;
                            ?>
                        </div>
                        
                        <div class="notice-stats">
                            <span class="stat-item">
                                👁️ <?= number_format($notice['view_count'] ?? 0) ?>
                            </span>
                            <span class="stat-item">
                                💬 <?= number_format($notice['comment_count'] ?? 0) ?>
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
                    <a href="?page=<?= $currentPage - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($company) ? '&company=' . urlencode($company) : '' ?><?= !empty($filter) ? '&filter=' . urlencode($filter) : '' ?>" 
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
                    <a href="?page=1<?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($company) ? '&company=' . urlencode($company) : '' ?><?= !empty($filter) ? '&filter=' . urlencode($filter) : '' ?>" 
                       class="page-link">1</a>
                    <?php if ($startPage > 2): ?>
                        <span class="page-link disabled">...</span>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <a href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($company) ? '&company=' . urlencode($company) : '' ?><?= !empty($filter) ? '&filter=' . urlencode($filter) : '' ?>" 
                       class="page-link <?= $i === $currentPage ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($endPage < $totalPages): ?>
                    <?php if ($endPage < $totalPages - 1): ?>
                        <span class="page-link disabled">...</span>
                    <?php endif; ?>
                    <?php if ($totalPages > 0): ?>
                        <a href="?page=<?= $totalPages ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($company) ? '&company=' . urlencode($company) : '' ?><?= !empty($filter) ? '&filter=' . urlencode($filter) : '' ?>" 
                           class="page-link"><?= number_format($totalPages) ?></a>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- 다음 페이지 -->
                <?php if ($hasNextPage): ?>
                    <a href="?page=<?= $currentPage + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($company) ? '&company=' . urlencode($company) : '' ?><?= !empty($filter) ? '&filter=' . urlencode($filter) : '' ?>" 
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
        <div class="notice-list">
            <div class="empty-state">
                <i class="fas fa-megaphone" style="font-size: 3rem; margin-bottom: 20px; color: #cbd5e0;"></i>
                <h3>
                    <?php if (!empty($search) || !empty($company)): ?>
                        검색 조건에 맞는 공지사항이 없습니다
                    <?php else: ?>
                        첫 번째 공지사항을 작성해보세요!
                    <?php endif; ?>
                </h3>
                <p>
                    <?php if (!empty($search)): ?>
                        💡 검색 팁:<br>
                        • 검색어의 철자를 확인해보세요<br>
                        • 더 간단한 키워드로 다시 검색해보세요<br>
                        • 전체 기업에서 검색해보세요
                    <?php elseif (!empty($company)): ?>
                        해당 기업의 공지사항이 아직 없습니다.<br>
                        전체 기업 공지사항을 확인해보세요.
                    <?php else: ?>
                        중요한 소식을 공유해보세요.
                    <?php endif; ?>
                </p>
                <?php if ($isLoggedIn && $canWrite): ?>
                    <a href="/notices/write" class="btn-write">
                        <i class="fas fa-edit"></i>
                        <span>공지 작성</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- 프로필 이미지 모달은 profile-modal.js에서 동적 생성됨 -->

<script>
// 공지사항 목록 페이지 JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const loadStartTime = performance.now();
    
    console.log('📢 공지사항 게시판 로드 완료');
    console.log('📊 공지사항 수:', <?= count($notices ?? []) ?>);
    console.log('📄 현재 페이지:', <?= isset($currentPage) ? $currentPage : 1 ?>);
    console.log('📄 총 페이지:', <?= isset($totalPages) ? $totalPages : 1 ?>);
    <?php if (!empty($search)): ?>
    console.log('🔍 검색어:', '<?= addslashes($search) ?>');
    console.log('⚡ 검색 시간:', '<?= $searchTime ?>ms');
    <?php endif; ?>
    <?php if (!empty($company)): ?>
    console.log('🏢 선택된 기업:', '<?= addslashes($selectedCompany['company_name'] ?? '') ?>');
    <?php endif; ?>
    
    const loadEndTime = performance.now();
    const loadTime = Math.round(loadEndTime - loadStartTime);
    console.log(`⚡ 페이지 렌더링 완료: ${loadTime}ms`);
    
    // 검색 폼 기능
    const searchInput = document.querySelector('#searchInput');
    const searchForm = document.querySelector('.search-form');
    const companyFilter = document.querySelector('#companyFilter');
    
    if (searchInput && searchForm) {
        // 엔터키 처리
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                
                // 검색어 유효성 검사
                const searchTerm = this.value.trim();
                if (searchTerm.length > 0 && searchTerm.length < 2) {
                    alert('검색어는 2자 이상 입력해주세요.');
                    return;
                }
                
                searchForm.submit();
            }
        });
        
        // 실시간 검색어 길이 체크
        searchInput.addEventListener('input', function(e) {
            const length = this.value.length;
            if (length > 100) {
                this.value = this.value.substring(0, 100);
            }
            
            // 시각적 피드백
            if (length >= 2 || length === 0) {
                this.style.borderColor = '#2563eb';
                this.style.background = '#f0f9ff';
            } else if (length > 0) {
                this.style.borderColor = '#f56565';
                this.style.background = '#fef2f2';
            }
        });
    }
    
    // 기업명 검색 필드 처리 (엔터키와 입력 완료 시 검색)
    if (companyFilter) {
        let companySearchTimeout;
        
        // 엔터키 처리
        companyFilter.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchForm.submit();
            }
        });
        
        // 입력 중 실시간 검색 (디바운싱)
        companyFilter.addEventListener('input', function(e) {
            clearTimeout(companySearchTimeout);
            
            // 입력이 2자 이상이거나 비어있을 때만 검색
            const value = e.target.value.trim();
            if (value.length >= 2 || value.length === 0) {
                companySearchTimeout = setTimeout(() => {
                    // 자동 검색 (옵션: 사용자가 원하지 않을 수 있음)
                    // searchForm.submit();
                }, 500);
                
                // 시각적 피드백
                e.target.style.borderColor = '#2563eb';
                e.target.style.background = '#f0f9ff';
            } else if (value.length > 0) {
                e.target.style.borderColor = '#f56565';
                e.target.style.background = '#fef2f2';
            }
        });
    }
    
    // 공지사항 항목 호버 효과
    const noticeItems = document.querySelectorAll('.notice-item');
    noticeItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            if (!this.classList.contains('featured')) {
                this.style.transform = 'translateX(4px)';
            }
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });
    
    // 검색 결과 하이라이트 애니메이션
    const highlights = document.querySelectorAll('.search-highlight');
    highlights.forEach((highlight, index) => {
        setTimeout(() => {
            highlight.style.animation = 'highlight-pulse 0.6s ease-in-out';
        }, index * 100);
    });
    
    // 일반 공지사항 스타일
    const featuredNotices = document.querySelectorAll('.notice-item.featured');
    featuredNotices.forEach((notice, index) => {
        setTimeout(() => {
            notice.style.animation = 'fadeInUp 0.5s ease-out';
        }, index * 150);
    });
    
    // 공지사항 관련 전역 객체 정의
    if (typeof window.notices === 'undefined') {
        window.notices = {
            initialized: true,
            currentPage: <?= isset($currentPage) ? $currentPage : 1 ?>,
            totalPages: <?= isset($totalPages) ? $totalPages : 1 ?>,
            noticeCount: <?= count($notices ?? []) ?>,
            searchQuery: '<?= addslashes($search ?? '') ?>',
            selectedCompany: '<?= addslashes($company ?? '') ?>',
            hasNextPage: <?= isset($hasNextPage) ? ($hasNextPage ? 'true' : 'false') : 'false' ?>,
            hasPrevPage: <?= isset($hasPrevPage) ? ($hasPrevPage ? 'true' : 'false') : 'false' ?>
        };
    }
    
    // 프로필 이미지 클릭 이벤트는 통합 컴포넌트에서 자동 처리됨
});

// 하이라이트 애니메이션 CSS 추가
const style = document.createElement('style');
style.textContent = `
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
`;
document.head.appendChild(style);

// 기존 프로필 이미지 모달 JavaScript 함수들 제거됨 - profile-modal.js 통합 시스템 사용
</script>