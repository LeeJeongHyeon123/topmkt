<?php
/**
 * 커뮤니티 게시판 메인 페이지 (게시글 목록)
 */

// 로그인 상태 확인
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
require_once SRC_PATH . '/helpers/HtmlSanitizerHelper.php';
require_once SRC_PATH . '/helpers/SearchHelper.php';
require_once SRC_PATH . '/helpers/ProfileImageHelper.php';

// 컴포넌트 로드
require_once SRC_PATH . '/components/ui/Pagination.php';
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

<!-- 커뮤니티 페이지 스타일 include -->
<style>
<?php
// 커뮤니티 페이지 스타일 파일 include
$styleFile = SRC_PATH . '/views/community/components/community-styles.css';
if (file_exists($styleFile)) {
    echo file_get_contents($styleFile);
}
?>
    
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
    <!-- 헤더 컴포넌트 -->
    <?php include_once SRC_PATH . '/components/CommunityHeader.php'; ?>

    <!-- 게시판 컨트롤 영역 -->
    <div class="board-controls">
        <!-- 검색 폼 - 🚀 v3.37.0: SearchFilter 컴포넌트 적용 -->
        <div class="search-wrapper">
            <?php
            require_once SRC_PATH . '/components/ui/SearchFilter.php';

            echo SearchFilter::create([
                'action' => '/community',
                'method' => 'GET',
                'layout' => 'inline',
                'filters' => [
                    [
                        'type' => 'select',
                        'name' => 'filter',
                        'id' => 'searchFilter',
                        'options' => [
                            'all' => '전체',
                            'title' => '제목만',
                            'content' => '내용만',
                            'author' => '작성자'
                        ],
                        'value' => $filter ?? 'all'
                    ]
                ],
                'searchInput' => true,
                'searchName' => 'search',
                'searchPlaceholder' => '검색어를 입력하세요...',
                'searchValue' => $search ?? '',
                'submitButton' => true,
                'submitText' => '<i data-lucide="search" width="18" height="18"></i>',
                'resetButton' => !empty($search),  // 검색어 있을 때만 표시
                'resetText' => '✖️ 검색 해제',
                'cssClass' => 'community-search-filter'
            ]);
            ?>

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
                <i data-lucide="pen" width="20" height="20"></i> 글쓰기
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
        
        <!-- 페이지네이션 (Pagination 컴포넌트 사용) -->
        <?php
        if ($totalPages > 1) {
            echo renderPagination($currentPage, $totalPages, [
                'pageParam' => 'page',
                'preserveParams' => ['search', 'filter'],
                'containerClass' => 'pagination',
                'linkClass' => 'page-link'
            ]);
        }
        ?>
        
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
    



    <?php if (!empty($search)): ?>

    <?php endif; ?>
    
    const loadEndTime = performance.now();
    const loadTime = Math.round(loadEndTime - loadStartTime);
    
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
                    Toast.error('검색어는 2자 이상 입력해주세요.');
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
        }
    });
    
    // 전역 오류 핸들러 (커뮤니티 페이지 전용)
    window.addEventListener('error', function(event) {
        if (event.filename && event.filename.includes('community')) {
            
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

    // Lucide 아이콘 초기화 (v5.0.0)
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});



// 기존 프로필 이미지 모달 JavaScript 함수들 제거됨 - profile-modal.js 통합 시스템 사용
</script>

