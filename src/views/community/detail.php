<?php
/**
 * 커뮤니티 게시글 상세보기 페이지
 */

// 디버깅: 로깅 정책 준수 (WebLogger 사용)
if (class_exists('WebLogger')) {
    WebLogger::debug('커뮤니티 상세 페이지 - URL 파라미터 확인', [
        'get_params' => $_GET,
        'list_url' => $listUrl ?? 'NOT SET',
        'post_id' => $post['id'] ?? 'NOT SET'
    ]);
} else {
    error_log('🔍 [DEBUG] $_GET 파라미터: ' . json_encode($_GET));
    error_log('🔍 [DEBUG] $listUrl: ' . ($listUrl ?? 'NOT SET'));
}

// 로그인 상태 확인
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
require_once SRC_PATH . '/helpers/HtmlSanitizerHelper.php';
require_once SRC_PATH . '/helpers/ProfileImageHelper.php';

// 컴포넌트 로드
require_once SRC_PATH . '/components/ui/Button.php';

$isLoggedIn = AuthMiddleware::isLoggedIn();
$currentUserId = AuthMiddleware::getCurrentUserId();

// 게시글 정보가 없으면 404 처리
if (!isset($post) || !$post) {
    header('HTTP/1.1 404 Not Found');
    include SRC_PATH . '/views/templates/404.php';
    return;
}

// 프로필 이미지 모달 리소스 로드
include SRC_PATH . '/views/components/profile-modal-resources.php';
?>

<style>
/* 게시글 상세보기 페이지 스타일 */
.detail-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    min-height: calc(100vh - 200px);
}

.detail-navigation {
    margin-bottom: 20px;
}

.breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #718096;
}

.breadcrumb a {
    color: #4299e1;
    text-decoration: none;
}

.breadcrumb a:hover {
    text-decoration: underline;
}

.post-container {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
    margin-bottom: 30px;
}

.post-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
}

.post-title {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 15px;
    line-height: 1.4;
    word-break: break-word;
}

.post-meta {
    display: flex;
    align-items: center;
    gap: 20px;
    font-size: 0.9rem;
    opacity: 1 !important;
    flex-wrap: wrap;
    color: #ffffff !important;
}

.post-meta .meta-item {
    color: #ffffff !important;
}

.post-meta .meta-item strong {
    color: #ffffff !important;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
}

.post-content {
    padding: 30px;
}

.content-body {
    font-size: 1rem;
    line-height: 1.8;
    color: #2d3748;
    word-break: break-word;
    white-space: pre-wrap;
}

/* 게시글 내 이미지 반응형 크기 제한 */
.content-body img,
.post-content img {
    max-width: 100% !important;
    height: auto !important;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    margin: 10px 0;
    display: block;
}

/* 모바일에서 이미지 추가 최적화 */
@media (max-width: 768px) {
    .content-body img,
    .post-content img {
        max-width: 100% !important;
        width: 100% !important;
        object-fit: contain;
    }
}

.post-footer {
    padding: 20px 30px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
}

.post-stats {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 15px;
    font-size: 0.9rem;
    color: #718096;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 6px;
}

.post-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn {
    padding: 12px 20px;
    border: 1px solid transparent;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
    background: #ffffff;
    color: #374151;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    position: relative;
    overflow: hidden;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn:active {
    transform: translateY(0);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.btn-primary {
    border-color: #e5e7eb;
}

.btn-primary:hover {
    background: #f8fafc;
    border-color: #d1d5db;
}

.btn-primary.liked {
    background: linear-gradient(135deg, #ff6b6b, #ff5252);
    color: white;
    border-color: transparent;
}

.btn-primary.liked:hover {
    background: linear-gradient(135deg, #ff5252, #f44336);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
}

.btn-success {
    border-color: #e5e7eb;
}

.btn-success:hover {
    background: #f0fdf4;
    border-color: #d1d5db;
    color: #16a34a;
}

.btn-warning {
    border-color: #e5e7eb;
}

.btn-warning:hover {
    background: #fffbeb;
    border-color: #d1d5db;
    color: #d97706;
}

.btn-danger {
    border-color: #e5e7eb;
}

.btn-danger:hover {
    background: #fef2f2;
    border-color: #fecaca;
    color: #dc2626;
}

.btn-secondary {
    border-color: #e5e7eb;
}

.btn-secondary:hover {
    background: #f8fafc;
    border-color: #d1d5db;
    color: #4b5563;
}

.btn-info {
    border-color: #e5e7eb;
}

.btn-info:hover {
    background: #eff6ff;
    border-color: #d1d5db;
    color: #2563eb;
}

.author-meta-with-avatar {
    display: flex;
    align-items: center;
    gap: 8px;
}

.author-avatar-small,
.author-avatar-large {
    width: 60px !important;
    height: 60px !important;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 1rem;
    flex-shrink: 0;
    overflow: hidden;
    transition: transform 0.2s ease;
    cursor: pointer;
    border: 2px solid rgba(255, 255, 255, 0.2);
    object-fit: cover;
}

.author-avatar-small:hover,
.author-avatar-large:hover {
    transform: scale(1.05);
    border-color: rgba(255, 255, 255, 0.4);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.comments-section {
    background: white;
    border-radius: 12px;
    overflow: visible;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
    margin-top: 30px;
}

.comments-section h3 {
    padding: 20px 30px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    font-weight: 600;
    color: #2d3748;
    margin: 0;
}

.comment-form {
    padding: 20px 30px;
}

.comments-list {
    padding: 20px 30px;
}

.comment-item {
    margin-bottom: 15px;
}

.comment-content {
    white-space: pre-wrap;
    word-break: break-word;
}

.comment-edit-form textarea {
    font-family: inherit;
}

.reply-form {
    transition: all 0.3s ease;
}

/* 댓글 들여쓰기 스타일 */
.ml-12 { margin-left: 3rem; }
.ml-24 { margin-left: 6rem; }
.ml-36 { margin-left: 9rem; }
.ml-48 { margin-left: 12rem; }
.ml-60 { margin-left: 15rem; }

.back-to-list {
    position: fixed;
    bottom: 30px;
    right: 30px;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 50px;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    transition: all 0.3s ease;
    font-size: 1.2rem;
}

.back-to-list:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 25px rgba(102, 126, 234, 0.5);
}

/* 모바일 반응형 */
@media (max-width: 768px) {
    .detail-container {
        padding: 15px;
    }
    
    .post-header {
        padding: 20px;
    }
    
    .post-title {
        font-size: 1.4rem;
    }
    
    .post-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .post-content {
        padding: 20px;
    }
    
    .post-footer {
        padding: 15px 20px;
    }
    
    .post-actions {
        justify-content: center;
    }
    

    
    .back-to-list {
        bottom: 20px;
        right: 20px;
        width: 50px;
        height: 50px;
        font-size: 1rem;
    }
}

/* 기존 프로필 이미지 모달 CSS 제거됨 - profile-modal.css 통합 시스템 사용 */

/* 다크모드 대응 */
@media (prefers-color-scheme: dark) {
    .post-container,
    .comments-section {
        background: #2d3748;
        border-color: #4a5568;
    }
    
    .content-body {
        color: #e2e8f0;
    }
    
    .post-footer,
    .comments-header {
        background: #4a5568;
        border-color: #718096;
    }
}
    

}

/* 애니메이션 */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.post-container {
    animation: fadeInUp 0.6s ease-out;
}

.comments-section {
    animation: fadeInUp 0.6s ease-out 0.2s both;
}
</style>

<div class="detail-container">
    <!-- 네비게이션 -->
    <div class="detail-navigation">
        
        <div class="breadcrumb">
            <a href="<?= htmlspecialchars($listUrl) ?>">📋 커뮤니티</a>
            <span>›</span>
            <span>게시글 보기</span>
        </div>
    </div>
    
    <!-- 게시글 컨테이너 -->
    <div class="post-container">
        <!-- 게시글 헤더 -->
        <?php 
        // 변수를 먼저 정의
        $authorName = $post['author_name'] ?? $post['nickname'] ?? '익명';
        ?>
        
        <div class="post-header">
            <h1 class="post-title"><?= htmlspecialchars($post['title']) ?></h1>
            <div class="post-meta">
                <div class="meta-item author-meta-with-avatar">
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
                    $size = ProfileImageHelper::SIZE_PROFILE;
                    $mode = 'api';
                    $extraClasses = ['author-avatar-large'];
                    include SRC_PATH . '/views/components/profile-image.php';
                    ?>
                    <span><strong><?= htmlspecialchars($authorName) ?></strong></span>
                </div>
                <div class="meta-item">
                    📅 <?= date('Y년 m월 d일 H:i', strtotime($post['created_at'])) ?>
                </div>
            </div>
        </div>
        
        <!-- 게시글 내용 -->
        <div class="post-content">
            <!-- 디버깅 정보 (임시) -->
            <?php if (isset($_GET['debug'])): ?>
                <div style="background: #f0f0f0; padding: 10px; margin: 10px 0; border-left: 3px solid #007cba;">
                    <h4>🔍 디버깅 정보</h4>
                    <p><strong>원본 Content:</strong></p>
                    <pre style="background: white; padding: 10px; font-size: 12px; overflow-x: auto;"><?= htmlspecialchars($post['content']) ?></pre>
                    <p><strong>Sanitized Content:</strong></p>
                    <pre style="background: white; padding: 10px; font-size: 12px; overflow-x: auto;"><?= htmlspecialchars(HtmlSanitizerHelper::sanitizeRichText($post['content'])) ?></pre>
                </div>
            <?php endif; ?>
            
            <div class="content-body">
                <?= HtmlSanitizerHelper::sanitizeRichText($post['content']) ?>
            </div>
        </div>
        
        <!-- 게시글 푸터 -->
        <div class="post-footer">
            <!-- 통계 -->
            <div class="post-stats">
                <div class="stat-item">
                    👁️ 조회 <?= number_format($post['view_count'] ?? 0) ?>
                </div>
                <div class="stat-item">
                    💬 댓글 <?= number_format($post['comment_count'] ?? 0) ?>
                </div>
                <div class="stat-item">
                    ❤️ 좋아요 <?= number_format($post['like_count'] ?? 0) ?>
                </div>
            </div>
            
            <!-- 액션 버튼들 -->
            <div class="post-actions">
                <?php if ($isLoggedIn): ?>
                    <?= renderButton(
                        ($isLiked ? '❤️' : '🤍') . ' 좋아요 ' . $post['like_count'],
                        'primary',
                        'md',
                        [
                            'id' => 'likeBtn',
                            'class' => $isLiked ? 'liked' : ''
                        ]
                    ) ?>
                    <?= renderButton('📤 공유', 'success', 'md', [
                        'id' => 'shareBtn'
                    ]) ?>
                    <?php if (!$isOwner && isset($post['user_id']) && $post['user_id']): ?>
                        <?= renderButton('💬 채팅하기', 'info', 'md', [
                            'id' => 'chatBtn',
                            'attributes' => [
                                'data-author-id' => htmlspecialchars($post['user_id']),
                                'data-author-name' => htmlspecialchars($authorName)
                            ]
                        ]) ?>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php if ($isOwner): ?>
                    <a href="/community/posts/<?= $post['id'] ?>/edit" class="btn btn-warning">
                        ✏️ 수정
                    </a>
                    <?= renderButton('🗑️ 삭제', 'danger', 'md', [
                        'id' => 'deleteBtn'
                    ]) ?>
                <?php endif; ?>
                
                <a href="<?= htmlspecialchars($listUrl) ?>" class="btn btn-secondary">
                    📋 목록으로
                </a>
            </div>
        </div>

    </div>
    
    <!-- 댓글 섹션 -->
    <div class="comments-section" id="comments-section">
        <?php 
        // 댓글 모델 로드
        require_once SRC_PATH . '/models/Comment.php';
        $commentModel = new Comment();
        
        // 페이지네이션 설정
        $commentsPerPage = 20;
        $currentPage = isset($_GET['comment_page']) ? max(1, intval($_GET['comment_page'])) : 1;
        
        // 댓글 총 개수 조회
        $totalComments = $commentModel->getCountByPostId($post['id']);
        $totalPages = ceil($totalComments / $commentsPerPage);
        
        // 현재 페이지 댓글 조회
        $comments = $commentModel->getByPostId($post['id'], $currentPage, $commentsPerPage);
        
        // 댓글 뷰 포함
        $postId = $post['id'];
        $currentUserId = $_SESSION['user_id'] ?? null;
        include SRC_PATH . '/views/comment/list.php';
        ?>
    </div>
</div>

<!-- 목록으로 돌아가기 플로팅 버튼 -->
<?= renderButton('📋', 'secondary', 'md', [
    'id' => 'backToListBtn',
    'class' => 'back-to-list',
    'ariaLabel' => '목록으로 돌아가기'
]) ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const backToListBtn = document.getElementById('backToListBtn');
    const targetUrl = '<?= htmlspecialchars($listUrl ?? "/community") ?>';
    
    
    if (backToListBtn) {
        // 기존 onclick 이벤트 제거
        backToListBtn.onclick = null;
        
        // addEventListener로 이벤트 등록 (더 안정적)
        backToListBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            
            // 즉시 이동
            window.location.href = targetUrl;
        });
        
    } else {
    }
});
</script>

<!-- DEBUG: listUrl 변수 상태 확인 -->
<script>


</script>

<!-- 기존 프로필 이미지 모달 HTML 제거됨 - profile-modal.js 통합 시스템 사용 -->

<!-- 댓글 시스템은 comment/list.php에서 내장 JavaScript 사용 -->

<!-- 현재 사용자 정보 (댓글 시스템용) -->
<?php if ($isLoggedIn): ?>
<div data-user-id="<?= $currentUserId ?>" style="display: none;"></div>
<?php endif; ?>

<!-- 게시글 정보 (댓글 시스템용) -->
<div data-post-id="<?= $post['id'] ?>" style="display: none;"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    
    const isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;
    const isOwner = <?= $isOwner ? 'true' : 'false' ?>;
    const postId = <?= $post['id'] ?>;
    
    // 좋아요 버튼 처리
    const likeBtn = document.getElementById('likeBtn');
    if (likeBtn && isLoggedIn) {
        likeBtn.addEventListener('click', function() {
            const buttonElement = this; // this 컨텍스트 저장

            // 로딩 상태 표시
            const originalText = buttonElement.innerHTML;
            buttonElement.disabled = true;
            buttonElement.innerHTML = '🔄 처리 중...';

            console.log('🔍 [LIKE] 좋아요 버튼 클릭, postId:', postId);

            // v3.42.0: ApiClient 사용
            ApiClient.post(`/api/posts/${postId}/like`, {}, { noLoading: true })
            .then(data => {
                console.log('🔍 [LIKE] 응답 데이터:', data);

                if (data.status === 'success' && data.data) {
                    console.log('🔍 [LIKE] action:', data.data.action, 'like_count:', data.data.like_count);

                    // 좋아요 수 포맷팅
                    const likeCount = Number(data.data.like_count) || 0;
                    const formattedCount = likeCount.toLocaleString('ko-KR');

                    // 좋아요 상태에 따라 버튼 텍스트 및 스타일 변경
                    if (data.data.action === 'liked') {
                        buttonElement.innerHTML = '❤️ 좋아요 ' + formattedCount;
                        buttonElement.classList.add('liked');
                        console.log('✅ [LIKE] 좋아요 추가 완료');
                    } else if (data.data.action === 'unliked') {
                        buttonElement.innerHTML = '🤍 좋아요 ' + formattedCount;
                        buttonElement.classList.remove('liked');
                        console.log('✅ [LIKE] 좋아요 취소 완료');
                    }

                    // 통계 업데이트 - 좋아요 수 표시하는 모든 요소 찾기
                    const likeStats = document.querySelectorAll('.stat-item');
                    console.log('🔍 [LIKE] 통계 요소 개수:', likeStats.length);
                    likeStats.forEach(stat => {
                        if (stat.textContent.includes('좋아요')) {
                            stat.innerHTML = '❤️ 좋아요 ' + formattedCount;
                            console.log('✅ [LIKE] 통계 업데이트 완료:', stat.innerHTML);
                        }
                    });

                    Toast.success(data.message || '처리되었습니다.');
                } else {
                    console.error('❌ [LIKE] 응답 형식 오류:', data);
                    Toast.error(data.message || '좋아요 처리 중 오류가 발생했습니다.');
                    buttonElement.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('❌ [LIKE] 네트워크 오류:', error);
                Toast.error('네트워크 오류가 발생했습니다. 다시 시도해주세요.');
                buttonElement.innerHTML = originalText;
            })
            .finally(() => {
                buttonElement.disabled = false;
            });
        });
    }
    
    // 공유 버튼 처리
    const shareBtn = document.getElementById('shareBtn');
    if (shareBtn) {
        shareBtn.addEventListener('click', function() {
            if (navigator.share) {
                // Web Share API 사용 (모바일 등)
                navigator.share({
                    title: '<?= htmlspecialchars($post['title']) ?>',
                    text: '탑마케팅 커뮤니티의 게시글을 확인해보세요!',
                    url: window.location.href
                }).catch(() => {}); // 에러 무시
            } else {
                // 🚀 Phase 8: navigator.clipboard → copyToClipboard 사용
                copyToClipboard(window.location.href, {
                    successMessage: '게시글 링크가 클립보드에 복사되었습니다! 📋'
                });
            }
        });
    }
    
    // 삭제 버튼 처리
    const deleteBtn = document.getElementById('deleteBtn');
    if (deleteBtn && isOwner) {
        deleteBtn.addEventListener('click', async function() {
            if (!(await Modal.confirm('정말로 이 게시글을 삭제하시겠습니까?\n\n삭제된 게시글은 복구할 수 없습니다.', { type: 'warning' }))) {
                return;
            }
            
            // 로딩 표시
            deleteBtn.disabled = true;
            deleteBtn.innerHTML = '🔄 삭제 중...';
            
            // CSRF 토큰 가져오기
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            // v3.42.0: ApiClient 사용
            ApiClient.delete(`/community/posts/${postId}`, {
                body: { csrf_token: csrfToken },
                noLoading: true
            })
            .then(response => {
                // ApiClient는 응답을 { success, data, message } 형태로 정규화
                if (response.success) {
                    Toast.success(response.message || '게시글이 삭제되었습니다.');
                    // Toast 표시 후 리다이렉트 (300ms 딜레이)
                    setTimeout(() => {
                        window.location.href = response.data?.redirectUrl || '/community';
                    }, 300);
                } else {
                    Toast.error(response.message || '삭제 중 오류가 발생했습니다.');
                    deleteBtn.disabled = false;
                    deleteBtn.innerHTML = '🗑️ 삭제';
                }
            })
            .catch(error => {
                Toast.error('네트워크 오류가 발생했습니다. 다시 시도해주세요.');
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = '🗑️ 삭제';
            });
        });
    }
    
    // 채팅 버튼 처리
    const chatBtn = document.getElementById('chatBtn');
    if (chatBtn) {
        chatBtn.addEventListener('click', function() {
            const authorId = this.getAttribute('data-author-id');
            const authorName = this.getAttribute('data-author-name');
            
            if (!authorId) {
                Toast.error('작성자 정보를 찾을 수 없습니다.');
                return;
            }
            
            // 채팅 페이지로 이동하면서 해당 사용자와 채팅 시작
            window.location.href = `/chat#user-${authorId}`;
        });
    }
    
    // 키보드 단축키
    document.addEventListener('keydown', async function(e) {
        // ESC: 목록으로 돌아가기
        if (e.key === 'Escape') {
            if (await Modal.confirm('목록으로 돌아가시겠습니까?')) {
                window.location.href = '<?= htmlspecialchars($listUrl) ?>';
            }
        }
        
        // E: 수정 (소유자만)
        if (e.key === 'e' || e.key === 'E') {
            if (isOwner && !e.ctrlKey && !e.metaKey && !e.altKey) {
                const activeElement = document.activeElement;
                if (activeElement.tagName !== 'INPUT' && activeElement.tagName !== 'TEXTAREA') {
                    window.location.href = `/community/posts/${postId}/edit`;
                }
            }
        }
    });
    
    // 조회수 증가 (Ajax로 처리, 실제 구현 시)
    // setTimeout(function() {
    //     fetch(`/community/posts/${postId}/view`, { method: 'POST' });
    // }, 2000);
    
    // 스크롤 시 플로팅 버튼 표시/숨김
    const backToListBtn = document.querySelector('.back-to-list');
    let lastScrollTop = 0;
    
    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        if (scrollTop > 300) {
            backToListBtn.style.display = 'flex';
        } else {
            backToListBtn.style.display = 'none';
        }
        
        lastScrollTop = scrollTop;
    });
    
    // 기존 프로필 이미지 모달 JavaScript 함수들 제거됨 - profile-modal.js 통합 시스템 사용
});
</script>