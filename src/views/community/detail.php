<?php
/**
 * 커뮤니티 게시글 상세보기 페이지
 */

// 로그인 상태 확인
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
require_once SRC_PATH . '/helpers/HtmlSanitizerHelper.php';
$isLoggedIn = AuthMiddleware::isLoggedIn();
$currentUserId = AuthMiddleware::getCurrentUserId();

// 게시글 정보가 없으면 404 처리
if (!isset($post) || !$post) {
    header('HTTP/1.1 404 Not Found');
    include SRC_PATH . '/views/templates/404.php';
    return;
}
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

.author-avatar-small {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 0.9rem;
    flex-shrink: 0;
    overflow: hidden;
    transition: transform 0.2s ease;
}

.author-avatar-small:hover {
    transform: scale(1.1);
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
    
    .profile-image-modal .modal-content {
        background: #2d3748;
    }
    
    .profile-image-modal .modal-header {
        background: #4a5568;
        border-color: #718096;
    }
    
    .profile-image-modal .modal-header h3 {
        color: #e2e8f0;
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
            <a href="/community">📋 커뮤니티</a>
            <span>›</span>
            <span>게시글 보기</span>
        </div>
    </div>
    
    <!-- 게시글 컨테이너 -->
    <div class="post-container">
        <!-- 게시글 헤더 -->
        <?php 
        // 변수를 먼저 정의
        $profileImage = $post['profile_image'] ?? null;
        $defaultImage = '/assets/images/default-avatar.png';
        $authorName = $post['author_name'] ?? $post['nickname'] ?? '익명';
        ?>
        
        <div class="post-header">
            <h1 class="post-title"><?= htmlspecialchars($post['title']) ?></h1>
            <div class="post-meta">
                <div class="meta-item author-meta-with-avatar">
                    <div class="author-avatar-small profile-image-clickable" 
                         data-user-id="<?= htmlspecialchars($post['user_id']) ?>" 
                         data-user-name="<?= htmlspecialchars($authorName) ?>" 
                         style="cursor: pointer;" 
                         title="프로필 이미지 크게 보기">
                        <?php if ($profileImage): ?>
                            <img src="<?= htmlspecialchars($profileImage) ?>" alt="<?= htmlspecialchars($authorName) ?>" 
                                 style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div style="display: none; width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.9rem;">
                                <?= mb_substr($authorName, 0, 1) ?>
                            </div>
                        <?php else: ?>
                            <?= mb_substr($authorName, 0, 1) ?>
                        <?php endif; ?>
                    </div>
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
                    <button class="btn btn-primary <?= $isLiked ? 'liked' : '' ?>" id="likeBtn">
                        <?= $isLiked ? '❤️' : '🤍' ?> 좋아요 <?= $post['like_count'] ?>
                    </button>
                    <button class="btn btn-success" id="shareBtn">
                        📤 공유
                    </button>
                    <?php if (!$isOwner && isset($post['user_id']) && $post['user_id']): ?>
                        <button class="btn btn-info" id="chatBtn" data-author-id="<?= htmlspecialchars($post['user_id']) ?>" data-author-name="<?= htmlspecialchars($authorName) ?>">
                            💬 채팅하기
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php if ($isOwner): ?>
                    <a href="/community/posts/<?= $post['id'] ?>/edit" class="btn btn-warning">
                        ✏️ 수정
                    </a>
                    <button class="btn btn-danger" id="deleteBtn">
                        🗑️ 삭제
                    </button>
                <?php endif; ?>
                
                <a href="/community" class="btn btn-secondary">
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
<button class="back-to-list" onclick="location.href='/community'" title="목록으로 돌아가기">
    📋
</button>

<!-- 프로필 이미지 확대 모달 -->
<div id="profileImageModal" class="profile-image-modal" onclick="closeProfileImageModal()">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 id="modalUserName">사용자 프로필</h3>
            <button class="modal-close" onclick="closeProfileImageModal()">&times;</button>
        </div>
        <div class="modal-body">
            <img id="modalProfileImage" src="" alt="프로필 이미지" style="max-width: 100%; max-height: 100%; width: auto; height: auto; border-radius: 12px; object-fit: contain;">
        </div>
    </div>
</div>

<!-- 댓글 시스템은 comment/list.php에서 내장 JavaScript 사용 -->

<!-- 현재 사용자 정보 (댓글 시스템용) -->
<?php if ($isLoggedIn): ?>
<div data-user-id="<?= $currentUserId ?>" style="display: none;"></div>
<?php endif; ?>

<!-- 게시글 정보 (댓글 시스템용) -->
<div data-post-id="<?= $post['id'] ?>" style="display: none;"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('📄 게시글 상세보기 페이지 로드 완료');
    console.log('📝 게시글 ID:', <?= $post['id'] ?>);
    console.log('👤 작성자:', '<?= addslashes(htmlspecialchars($post['author_name'] ?? $post['nickname'] ?? '익명')) ?>');
    console.log('🔑 소유자 여부:', <?= $isOwner ? 'true' : 'false' ?>);
    
    const isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;
    const isOwner = <?= $isOwner ? 'true' : 'false' ?>;
    const postId = <?= $post['id'] ?>;
    
    // 좋아요 버튼 처리
    const likeBtn = document.getElementById('likeBtn');
    if (likeBtn && isLoggedIn) {
        likeBtn.addEventListener('click', function() {
            // 로딩 상태 표시
            const originalText = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '🔄 처리 중...';
            
            // CSRF 토큰 가져오기
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            
            fetch(`/api/posts/${postId}/like`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 좋아요 상태에 따라 버튼 텍스트 변경
                    if (data.action === 'liked') {
                        this.innerHTML = '❤️ 좋아요 ' + data.like_count;
                        this.classList.add('liked');
                    } else {
                        this.innerHTML = '🤍 좋아요 ' + data.like_count;
                        this.classList.remove('liked');
                    }
                    
                    // 통계 업데이트
                    const likeStat = document.querySelector('.stat-item:has(❤️)');
                    if (likeStat) {
                        likeStat.innerHTML = '❤️ 좋아요 ' + data.like_count;
                    }
                } else {
                    alert(data.message || '좋아요 처리 중 오류가 발생했습니다.');
                    this.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('네트워크 오류가 발생했습니다. 다시 시도해주세요.');
                this.innerHTML = originalText;
            })
            .finally(() => {
                this.disabled = false;
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
                }).catch(console.error);
            } else {
                // 클립보드에 URL 복사
                navigator.clipboard.writeText(window.location.href).then(function() {
                    alert('게시글 링크가 클립보드에 복사되었습니다! 📋');
                }).catch(function() {
                    // 클립보드 접근 실패 시 대체 방법
                    const textArea = document.createElement('textarea');
                    textArea.value = window.location.href;
                    document.body.appendChild(textArea);
                    textArea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textArea);
                    alert('게시글 링크가 클립보드에 복사되었습니다! 📋');
                });
            }
        });
    }
    
    // 삭제 버튼 처리
    const deleteBtn = document.getElementById('deleteBtn');
    if (deleteBtn && isOwner) {
        deleteBtn.addEventListener('click', function() {
            if (!confirm('정말로 이 게시글을 삭제하시겠습니까?\n\n삭제된 게시글은 복구할 수 없습니다.')) {
                return;
            }
            
            // 삭제 확인 재요청
            if (!confirm('정말로 삭제하시겠습니까?\n\n이 작업은 되돌릴 수 없습니다.')) {
                return;
            }
            
            // 로딩 표시
            deleteBtn.disabled = true;
            deleteBtn.innerHTML = '🔄 삭제 중...';
            
            // CSRF 토큰 가져오기
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            
            fetch(`/community/posts/${postId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    _method: 'DELETE',
                    csrf_token: csrfToken
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.href = data.data?.redirectUrl || '/community';
                } else {
                    alert(data.message || '삭제 중 오류가 발생했습니다.');
                    deleteBtn.disabled = false;
                    deleteBtn.innerHTML = '🗑️ 삭제';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('네트워크 오류가 발생했습니다. 다시 시도해주세요.');
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
                alert('작성자 정보를 찾을 수 없습니다.');
                return;
            }
            
            // 채팅 페이지로 이동하면서 해당 사용자와 채팅 시작
            window.location.href = `/chat#user-${authorId}`;
        });
    }
    
    // 키보드 단축키
    document.addEventListener('keydown', function(e) {
        // ESC: 목록으로 돌아가기
        if (e.key === 'Escape') {
            if (confirm('목록으로 돌아가시겠습니까?')) {
                window.location.href = '/community';
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