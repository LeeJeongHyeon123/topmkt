<style>
/* 댓글 섹션 스타일 */
.comments-section {
    margin-top: 40px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    overflow: hidden;
}

.comments-header {
    background: #f8fafc;
    padding: 20px 30px;
    border-bottom: 1px solid #e2e8f0;
}

.comments-title {
    font-size: 1.3rem;
    font-weight: 600;
    color: #2d3748;
    margin: 0;
}

.comments-count {
    color: #667eea;
    font-weight: 500;
}

.comment-form {
    padding: 30px;
    border-bottom: 1px solid #e2e8f0;
}

.comment-textarea {
    width: 100%;
    min-height: 100px;
    padding: 15px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.95rem;
    resize: vertical;
    transition: border-color 0.3s ease;
    font-family: inherit;
}

.comment-textarea:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.comment-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 15px;
}

.comment-submit {
    background: #667eea;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.comment-submit:hover {
    background: #5a67d8;
}

.comment-submit:disabled {
    background: #cbd5e0;
    cursor: not-allowed;
}

.comments-list {
    padding: 20px 30px;
}

.comment-item {
    margin-bottom: 20px;
}

.comment-item.reply {
    margin-left: 40px;
    border-left: 3px solid #e2e8f0;
    padding-left: 20px;
}

.comment-card {
    background: #f8fafc;
    border-radius: 8px;
    padding: 20px;
    transition: background-color 0.3s ease;
}

.comment-card:hover {
    background: #f1f5f9;
}

.comment-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
}

.comment-author {
    display: flex;
    align-items: center;
    gap: 10px;
}

.comment-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #667eea;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 0.9rem;
}

.comment-author-name {
    font-weight: 600;
    color: #2d3748;
    font-size: 0.95rem;
}

.comment-time {
    color: #64748b;
    font-size: 0.85rem;
}

.comment-actions-btn {
    display: flex;
    gap: 10px;
}

.comment-btn {
    background: none;
    border: none;
    color: #64748b;
    font-size: 0.85rem;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.comment-btn:hover {
    background: #e2e8f0;
    color: #2d3748;
}

.comment-btn.edit:hover {
    color: #2563eb;
}

.comment-btn.delete:hover {
    color: #dc2626;
}

.comment-btn.chat {
    color: #000000;
    font-size: 1rem;
}

.comment-btn.chat:hover {
    color: #000000;
    background: #f3f4f6;
}

.comment-content {
    color: #374151;
    line-height: 1.6;
    margin-bottom: 10px;
    white-space: pre-line;
    word-break: break-word;
}

.comment-reply-btn {
    background: none;
    border: none;
    color: #667eea;
    font-size: 0.85rem;
    cursor: pointer;
    font-weight: 500;
}

.comment-reply-btn:hover {
    color: #5a67d8;
    text-decoration: underline;
}

.reply-form {
    margin-top: 15px;
    padding: 15px;
    background: white;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    display: none;
}

.reply-textarea {
    width: 100%;
    min-height: 80px;
    padding: 10px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 0.9rem;
    resize: vertical;
}

.reply-actions {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

.reply-submit, .reply-cancel {
    padding: 6px 12px;
    border: none;
    border-radius: 4px;
    font-size: 0.85rem;
    cursor: pointer;
}

.reply-submit {
    background: #667eea;
    color: white;
}

.reply-cancel {
    background: #e2e8f0;
    color: #4a5568;
}

.no-comments {
    text-align: center;
    padding: 60px 30px;
    color: #64748b;
}

.no-comments-icon {
    font-size: 3rem;
    margin-bottom: 15px;
    opacity: 0.5;
}

.login-required {
    text-align: center;
    padding: 30px;
    background: #f8fafc;
    color: #64748b;
}

.login-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
}

.login-link:hover {
    text-decoration: underline;
}

/* 반응형 */
@media (max-width: 768px) {
    .comments-section {
        margin-top: 30px;
        border-radius: 8px;
    }
    
    .comments-header,
    .comment-form,
    .comments-list {
        padding: 20px;
    }
    
    .comment-item.reply {
        margin-left: 20px;
        padding-left: 15px;
    }
    
    .comment-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .comment-actions-btn {
        gap: 5px;
    }
}
</style>

<?php
/**
 * 댓글 목록 표시 템플릿
 */

// 댓글 렌더링 함수
function renderComment($comment, $currentUserId = null, $depth = 0) {
    $isOwner = $currentUserId && $comment['user_id'] == $currentUserId;
    $isReply = $depth > 0;
    ?>
    <div class="comment-item <?= $isReply ? 'reply' : '' ?>" id="comment-<?= $comment['id'] ?>" data-comment-id="<?= $comment['id'] ?>" data-depth="<?= $depth ?>">
        <div class="comment-card">
            <div class="comment-header">
                <div class="comment-author">
                    <div class="comment-avatar">
                        <?php 
                        $profileImage = $comment['profile_image'] ?? null;
                        $authorName = $comment['author_name'];
                        
                        if ($profileImage): ?>
                            <img src="<?= htmlspecialchars($profileImage) ?>" alt="<?= htmlspecialchars($authorName) ?>" 
                                 style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div style="display: none; width: 100%; height: 100%; background: #667eea; border-radius: 50%; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.9rem;">
                                <?= mb_substr($authorName, 0, 1) ?>
                            </div>
                        <?php else: ?>
                            <?= mb_substr($authorName, 0, 1) ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="comment-author-name"><?= htmlspecialchars($comment['author_name']) ?></div>
                        <div class="comment-time">
                            <?= date('Y.m.d H:i', strtotime($comment['created_at'])) ?>
                            <?php if ($comment['updated_at'] > $comment['created_at']): ?>
                                <span>(수정됨)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="comment-actions-btn">
                    <?php if ($currentUserId && !$isOwner && $comment['user_id']): ?>
                        <button onclick="startChatWithCommentAuthor(<?= $comment['user_id'] ?>, '<?= addslashes(htmlspecialchars($comment['author_name'])) ?>')" class="comment-btn chat" title="채팅하기">
                            <i class="fas fa-comment"></i>
                        </button>
                    <?php endif; ?>
                    <?php if ($isOwner): ?>
                        <button onclick="editComment(<?= $comment['id'] ?>)" class="comment-btn edit">
                            수정
                        </button>
                        <button onclick="deleteComment(<?= $comment['id'] ?>)" class="comment-btn delete">
                            삭제
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="comment-content" id="comment-content-<?= $comment['id'] ?>">
                <?php
                    // 추가 정규화 처리
                    $content = $comment['content'];
                    $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $content);
                    $content = preg_replace('/\r\n|\r/', "\n", $content);
                    $content = trim($content);
                    echo nl2br(htmlspecialchars($content));
                ?>
            </div>
            
            <!-- 대댓글 버튼 (1단계 댓글에만 표시) -->
            <?php if ($depth < 1 && $currentUserId): ?>
            <div style="margin-top: 10px;">
                <button onclick="toggleReplyForm(<?= $comment['id'] ?>)" class="comment-reply-btn">
                    답글 달기
                </button>
            </div>
            <?php endif; ?>
            
            <!-- 대댓글 작성 폼 -->
            <?php if ($depth < 1 && $currentUserId): ?>
            <div id="reply-form-<?= $comment['id'] ?>" class="reply-form">
                <textarea class="reply-textarea" placeholder="답글을 입력하세요..." rows="3"></textarea>
                <div class="reply-actions">
                    <button onclick="submitReply(<?= $comment['id'] ?>)" class="reply-submit">
                        답글 작성
                    </button>
                    <button onclick="cancelReply(<?= $comment['id'] ?>)" class="reply-cancel">
                        취소
                    </button>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

// 댓글 트리를 구성하는 함수 (최신순 정렬 고려)
function buildCommentTree($comments) {
    $tree = [];
    $lookup = [];
    
    // 1단계: 모든 댓글을 ID로 인덱싱
    foreach ($comments as $comment) {
        $comment['replies'] = [];
        $lookup[$comment['id']] = $comment;
    }
    
    // 2단계: 트리 구성
    foreach ($comments as $comment) {
        if ($comment['parent_id'] === null) {
            // 최상위 댓글 - 최신순으로 이미 정렬됨
            $tree[] = $lookup[$comment['id']];
        } else {
            // 대댓글
            if (isset($lookup[$comment['parent_id']])) {
                $lookup[$comment['parent_id']]['replies'][] = $comment;
            }
        }
    }
    
    // 각 댓글의 replies를 시간순으로 정렬 (답글은 오래된 것부터)
    foreach ($tree as &$parentComment) {
        if (!empty($parentComment['replies'])) {
            usort($parentComment['replies'], function($a, $b) {
                return strtotime($a['created_at']) - strtotime($b['created_at']);
            });
        }
    }
    
    return $tree;
}

$commentTree = buildCommentTree($comments);
$commentCount = count($comments);
?>

<!-- 댓글 헤더 -->
<div class="comments-header">
    <h3 class="comments-title">
        💬 댓글 <span class="comments-count"><?= number_format($totalComments ?? $commentCount) ?></span>
    </h3>
</div>

<!-- 댓글 작성 폼 -->
<?php if ($currentUserId): ?>
<div class="comment-form">
    <form id="comment-form" onsubmit="submitComment(event)">
        <textarea 
            id="comment-content" 
            class="comment-textarea" 
            placeholder="댓글을 입력하세요..."
            required
        ></textarea>
        <div class="comment-actions">
            <div>
                <small style="color: #64748b;">마크다운 문법을 사용할 수 있습니다.</small>
            </div>
            <button type="submit" class="comment-submit">
                댓글 작성
            </button>
        </div>
    </form>
</div>
<?php else: ?>
<div class="login-required">
    <p>
        <a href="/auth/login?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="login-link">
            로그인
        </a>
        하시면 댓글을 작성할 수 있습니다.
    </p>
</div>
<?php endif; ?>

<!-- 댓글 목록 -->
<div class="comments-list" id="comments-list">
    <?php if (empty($commentTree)): ?>
        <div class="no-comments">
            <div class="no-comments-icon">💭</div>
            <p>아직 댓글이 없습니다.<br>첫 번째 댓글을 작성해보세요!</p>
        </div>
    <?php else: ?>
        <?php foreach ($commentTree as $comment): ?>
            <?php renderComment($comment, $currentUserId, 0); ?>
            
            <!-- 대댓글 렌더링 -->
            <?php if (!empty($comment['replies'])): ?>
                <?php foreach ($comment['replies'] as $reply): ?>
                    <?php renderComment($reply, $currentUserId, 1); ?>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- 페이지네이션 -->
<?php if (isset($totalPages) && $totalPages > 1): ?>
<div class="comments-pagination">
    <style>
    .comments-pagination {
        padding: 20px 30px;
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
    }
    
    .pagination-btn {
        background: white;
        border: 1px solid #d1d5db;
        color: #374151;
        padding: 8px 12px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s ease;
        min-width: 40px;
        text-align: center;
        display: inline-block;
    }
    
    .pagination-btn:hover {
        background: #f3f4f6;
        border-color: #9ca3af;
        text-decoration: none;
        color: #374151;
    }
    
    .pagination-btn.active {
        background: #667eea;
        border-color: #667eea;
        color: white;
    }
    
    .pagination-btn.active:hover {
        background: #5a67d8;
        border-color: #5a67d8;
        color: white;
    }
    
    .pagination-btn:disabled {
        background: #f9fafb;
        color: #9ca3af;
        border-color: #e5e7eb;
        cursor: not-allowed;
    }
    
    .pagination-info {
        color: #64748b;
        font-size: 14px;
        margin: 0 15px;
    }
    </style>
    
    <?php 
    // 현재 URL에서 comment_page 파라미터 제외
    $currentUrl = strtok($_SERVER['REQUEST_URI'], '?');
    $queryParams = $_GET;
    unset($queryParams['comment_page']);
    $baseUrl = $currentUrl . (!empty($queryParams) ? '?' . http_build_query($queryParams) . '&' : '?');
    
    // 페이지 범위 계산
    $startPage = max(1, $currentPage - 2);
    $endPage = min($totalPages, $currentPage + 2);
    ?>
    
    <!-- 이전 페이지 -->
    <?php if ($currentPage > 1): ?>
        <a href="<?= $baseUrl ?>comment_page=<?= $currentPage - 1 ?>#comments-section" class="pagination-btn">‹ 이전</a>
    <?php else: ?>
        <span class="pagination-btn" style="opacity: 0.5; pointer-events: none;">‹ 이전</span>
    <?php endif; ?>
    
    <!-- 첫 페이지 -->
    <?php if ($startPage > 1): ?>
        <a href="<?= $baseUrl ?>comment_page=1#comments-section" class="pagination-btn">1</a>
        <?php if ($startPage > 2): ?>
            <span class="pagination-btn" style="border: none; background: none; color: #9ca3af;">...</span>
        <?php endif; ?>
    <?php endif; ?>
    
    <!-- 페이지 번호들 -->
    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
        <?php if ($i == $currentPage): ?>
            <span class="pagination-btn active"><?= $i ?></span>
        <?php else: ?>
            <a href="<?= $baseUrl ?>comment_page=<?= $i ?>#comments-section" class="pagination-btn"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>
    
    <!-- 마지막 페이지 -->
    <?php if ($endPage < $totalPages): ?>
        <?php if ($endPage < $totalPages - 1): ?>
            <span class="pagination-btn" style="border: none; background: none; color: #9ca3af;">...</span>
        <?php endif; ?>
        <a href="<?= $baseUrl ?>comment_page=<?= $totalPages ?>#comments-section" class="pagination-btn"><?= $totalPages ?></a>
    <?php endif; ?>
    
    <!-- 다음 페이지 -->
    <?php if ($currentPage < $totalPages): ?>
        <a href="<?= $baseUrl ?>comment_page=<?= $currentPage + 1 ?>#comments-section" class="pagination-btn">다음 ›</a>
    <?php else: ?>
        <span class="pagination-btn" style="opacity: 0.5; pointer-events: none;">다음 ›</span>
    <?php endif; ?>
    
</div>
<?php endif; ?>

<script>
// 전역 변수
window.currentPostId = <?= $postId ?>;
window.currentUserId = <?= $currentUserId ?? 'null' ?>;

// CSRF 토큰 가져오기
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

// 텍스트 정규화 함수
function normalizeText(text) {
    return text
        .replace(/\r\n/g, '\n')  // Windows line breaks
        .replace(/\r/g, '\n')    // Mac line breaks
        .replace(/\n+/g, '\n')   // Multiple line breaks
        .trim();                 // Trim whitespace
}

// 댓글 작성
function submitComment(event) {
    event.preventDefault();
    
    const rawContent = document.getElementById('comment-content').value;
    const content = normalizeText(rawContent);
    if (!content) {
        alert('댓글 내용을 입력해주세요.');
        return;
    }
    
    const submitBtn = document.querySelector('.comment-submit');
    submitBtn.disabled = true;
    submitBtn.textContent = '작성 중...';
    
    fetch('/api/comments', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': getCsrfToken()
        },
        body: JSON.stringify({
            post_id: window.currentPostId,
            content: content
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 페이지 새로고침으로 댓글 목록 업데이트
            location.reload();
        } else {
            alert(data.message || '댓글 작성에 실패했습니다.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('댓글 작성 중 오류가 발생했습니다.');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = '댓글 작성';
    });
}

// 대댓글 폼 토글
function toggleReplyForm(commentId) {
    const form = document.getElementById('reply-form-' + commentId);
    const isVisible = form.style.display === 'block';
    
    // 모든 답글 폼 숨기기
    document.querySelectorAll('.reply-form').forEach(f => f.style.display = 'none');
    
    if (!isVisible) {
        form.style.display = 'block';
        form.querySelector('.reply-textarea').focus();
    }
}

// 대댓글 작성
function submitReply(parentId) {
    const form = document.getElementById('reply-form-' + parentId);
    const textarea = form.querySelector('.reply-textarea');
    const rawContent = textarea.value;
    const content = normalizeText(rawContent);
    
    if (!content) {
        alert('답글 내용을 입력해주세요.');
        return;
    }
    
    const submitBtn = form.querySelector('.reply-submit');
    submitBtn.disabled = true;
    submitBtn.textContent = '작성 중...';
    
    fetch('/api/comments', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': getCsrfToken()
        },
        body: JSON.stringify({
            post_id: window.currentPostId,
            parent_id: parentId,
            content: content
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || '답글 작성에 실패했습니다.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('답글 작성 중 오류가 발생했습니다.');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = '답글 작성';
    });
}

// 대댓글 취소
function cancelReply(commentId) {
    const form = document.getElementById('reply-form-' + commentId);
    form.style.display = 'none';
    form.querySelector('.reply-textarea').value = '';
}

// 댓글 수정
function editComment(commentId) {
    // 간단히 prompt로 구현 (향후 인라인 편집으로 개선 가능)
    const currentContent = document.getElementById('comment-content-' + commentId).textContent.trim();
    const rawNewContent = prompt('댓글을 수정하세요:', currentContent);
    
    if (rawNewContent === null) {
        return;
    }
    
    const newContent = normalizeText(rawNewContent);
    if (newContent === currentContent) {
        return;
    }
    
    if (!newContent) {
        alert('댓글 내용을 입력해주세요.');
        return;
    }
    
    fetch('/api/comments/' + commentId, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': getCsrfToken()
        },
        body: JSON.stringify({
            content: newContent
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || '댓글 수정에 실패했습니다.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('댓글 수정 중 오류가 발생했습니다.');
    });
}

// 댓글 삭제
function deleteComment(commentId) {
    if (!confirm('정말로 이 댓글을 삭제하시겠습니까?')) {
        return;
    }
    
    fetch('/api/comments/' + commentId, {
        method: 'DELETE',
        headers: {
            'X-CSRF-Token': getCsrfToken()
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || '댓글 삭제에 실패했습니다.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('댓글 삭제 중 오류가 발생했습니다.');
    });
}

// 댓글 작성자와 채팅 시작
function startChatWithCommentAuthor(authorId, authorName) {
    if (!authorId) {
        alert('댓글 작성자 정보를 찾을 수 없습니다.');
        return;
    }
    
    // 채팅 페이지로 이동하면서 해당 사용자와 채팅 시작
    window.location.href = `/chat#user-${authorId}`;
}

// 페이지 로드 시 해시 앵커로 스크롤
document.addEventListener('DOMContentLoaded', function() {
    // URL 해시가 comment-로 시작하는 경우 해당 댓글로 스크롤
    if (window.location.hash && window.location.hash.startsWith('#comment-')) {
        const commentId = window.location.hash.substring(9); // #comment- 제거
        const commentElement = document.getElementById('comment-' + commentId);
        
        if (commentElement) {
            // 약간의 딜레이 후 스크롤 (페이지 완전 로드 대기)
            setTimeout(function() {
                commentElement.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
                
                // 댓글 하이라이트 효과
                commentElement.style.transition = 'background-color 0.3s ease';
                commentElement.style.backgroundColor = '#fef3cd';
                
                // 3초 후 하이라이트 제거
                setTimeout(function() {
                    commentElement.style.backgroundColor = '';
                }, 3000);
            }, 500);
        }
    }
});
</script>