<?php
// 컴포넌트 로드
require_once SRC_PATH . '/components/ui/Pagination.php';
?>

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

/* 답글 스타일 대폭 개선 - 공지사항 스타일 적용 */
.comment-item.reply {
    margin-left: 50px;
    margin-top: 15px;
    border-left: 4px solid #3b82f6;
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border-radius: 0 12px 12px 0;
    position: relative;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.15);
    border: 1px solid rgba(59, 130, 246, 0.2);
    padding-left: 20px;
}

/* 답글 연결선 */
.comment-item.reply::before {
    content: '';
    position: absolute;
    top: -15px;
    left: -25px;
    width: 25px;
    height: 30px;
    border-left: 3px solid #3b82f6;
    border-bottom: 3px solid #3b82f6;
    border-bottom-left-radius: 12px;
    opacity: 0.7;
}

/* 답글 배지 */
.reply-to-info {
    background: rgba(59, 130, 246, 0.1);
    padding: 6px 12px;
    border-radius: 6px 6px 0 0;
    margin: -20px -20px 10px -20px;
    border-bottom: 1px solid rgba(59, 130, 246, 0.2);
    font-size: 13px;
    color: #1e40af;
    font-weight: 500;
}

.comment-card {
    background: #f8fafc;
    border-radius: 8px;
    padding: 20px;
    transition: background-color 0.3s ease;
}

/* 일반 댓글과 답글 구분을 위한 호버 효과 */
.comment-item {
    transition: all 0.3s ease;
}

.comment-item:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-1px);
}

.comment-item.reply:hover {
    box-shadow: 0 4px 16px rgba(59, 130, 246, 0.2);
    transform: translateY(-1px);
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

/* 삭제된 댓글 플레이스홀더 스타일 */
.comment-item.deleted-placeholder {
    opacity: 0.7;
    margin-bottom: 20px;
}

.comment-card.deleted-comment {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 20px;
}

.comment-content.deleted-content {
    text-align: center;
    padding: 10px 0;
}

.deleted-text {
    color: #6c757d;
    font-style: italic;
    font-weight: 500;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
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
        margin-left: 30px;
        padding-left: 15px;
    }
    
    .comment-item.reply::before {
        left: -20px;
        width: 20px;
        height: 25px;
    }
    
    .reply-to-info {
        margin: -20px -15px 10px -15px;
        font-size: 12px;
        padding: 5px 10px;
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

// 댓글 렌더링 함수 (삭제된 댓글 표시 지원)
function renderComment($comment, $currentUserId = null, $depth = 0, $parentAuthor = null, $parentStatus = null) {
    $isOwner = $currentUserId && $comment['user_id'] == $currentUserId;
    $isReply = $depth > 0;

    // 삭제된 부모 댓글에 대한 답글인지 확인
    $isReplyToDeleted = $isReply && $parentStatus === 'deleted';
    ?>
    <div class="comment-item <?= $isReply ? 'reply' : '' ?>" id="comment-<?= $comment['id'] ?>" data-comment-id="<?= $comment['id'] ?>" data-depth="<?= $depth ?>">
        <div class="comment-card">
            <?php if ($isReply): ?>
                <div class="reply-to-info">
                    <?php if ($isReplyToDeleted): ?>
                        📌 삭제된 댓글에 대한 답글
                    <?php else: ?>
                        📌 <?= htmlspecialchars($parentAuthor) ?>님에게 답글
                    <?php endif; ?>
                </div>
            <?php endif; ?>
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
                <textarea class="reply-textarea" placeholder="답글을 입력하세요..." rows="3" maxlength="2000"></textarea>
                <div class="reply-char-counter" style="margin-top: 5px; text-align: right;">
                    <span class="reply-char-count" style="color: #64748b; font-size: 12px;">0</span>
                    <span style="color: #94a3b8; font-size: 12px;">/2,000자</span>
                </div>
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

// 댓글 트리를 구성하는 함수 (올바른 삭제된 댓글 처리)
function buildCommentTree($comments) {
    $tree = [];
    $lookup = [];
    $orphanedReplies = [];

    // 1단계: 모든 댓글을 ID로 인덱싱
    foreach ($comments as $comment) {
        $comment['replies'] = [];
        $lookup[$comment['id']] = $comment;
    }

    // 2단계: 트리 구성 및 orphaned replies 식별
    foreach ($comments as $comment) {
        if ($comment['parent_id'] === null) {
            // 최상위 댓글
            $tree[] = $lookup[$comment['id']];
        } else {
            // 대댓글
            if (isset($lookup[$comment['parent_id']])) {
                // 부모 댓글이 존재하는 경우
                $lookup[$comment['parent_id']]['replies'][] = $comment;
            } else {
                // 부모 댓글이 삭제된 경우 - orphaned reply 그룹핑
                $parentId = $comment['parent_id'];
                if (!isset($orphanedReplies[$parentId])) {
                    $orphanedReplies[$parentId] = [];
                }
                $orphanedReplies[$parentId][] = $comment;
            }
        }
    }

    // 3단계: orphaned replies가 있는 경우에만 삭제된 댓글 플레이스홀더 생성
    foreach ($orphanedReplies as $parentId => $replies) {
        if (!empty($replies)) {
            // 답글이 있는 삭제된 댓글만 플레이스홀더 생성
            $deletedPlaceholder = [
                'id' => 'deleted_' . $parentId,
                'parent_id' => null,
                'content' => '삭제된 댓글입니다.',
                'author_name' => '[삭제된 사용자]',
                'user_id' => null,
                'created_at' => $replies[0]['created_at'], // 첫 번째 답글 시간 기준
                'updated_at' => $replies[0]['created_at'],
                'status' => 'deleted',
                'is_deleted_placeholder' => true,
                'replies' => $replies
            ];
            $tree[] = $deletedPlaceholder;
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
            maxlength="2000"
            required
        ></textarea>
        <div class="comment-actions">
            <div>
                <small style="color: #64748b;">마크다운 문법을 사용할 수 있습니다.</small>
                <div class="comment-char-counter" style="margin-top: 5px;">
                    <span id="comment-char-count" style="color: #64748b; font-size: 13px;">0</span>
                    <span style="color: #94a3b8; font-size: 13px;">/2,000자</span>
                </div>
            </div>
            <button type="submit" class="comment-submit" id="comment-submit-btn">
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
            <?php
            // 삭제된 댓글 플레이스홀더인지 확인
            $isDeletedPlaceholder = isset($comment['is_deleted_placeholder']) && $comment['is_deleted_placeholder'];

            if ($isDeletedPlaceholder): ?>
                <!-- 삭제된 댓글 플레이스홀더 -->
                <div class="comment-item deleted-placeholder" id="comment-<?= $comment['id'] ?>">
                    <div class="comment-card deleted-comment">
                        <div class="comment-content deleted-content">
                            <span class="deleted-text">🗑️ 삭제된 댓글입니다.</span>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php renderComment($comment, $currentUserId, 0); ?>
            <?php endif; ?>

            <!-- 대댓글 렌더링 -->
            <?php if (!empty($comment['replies'])): ?>
                <?php foreach ($comment['replies'] as $reply): ?>
                    <?php
                    $parentStatus = $isDeletedPlaceholder ? 'deleted' : ($comment['status'] ?? 'active');
                    renderComment($reply, $currentUserId, 1, $comment['author_name'], $parentStatus);
                    ?>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- 페이지네이션 (Pagination 컴포넌트 사용) -->
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

.pagination-btn:hover:not(.disabled):not(.active) {
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

.pagination-btn.disabled {
    background: #f9fafb;
    color: #9ca3af;
    border-color: #e5e7eb;
    cursor: not-allowed;
    opacity: 0.5;
    pointer-events: none;
}
</style>

<?php
// Pagination 컴포넌트 호출
if (isset($totalPages) && $totalPages > 1) {
    echo renderPagination($currentPage, $totalPages, [
        'pageParam' => 'comment_page',
        'anchor' => '#comments-section',
        'containerClass' => 'comments-pagination',
        'linkClass' => 'pagination-btn',
        'prevText' => '‹ 이전',
        'nextText' => '다음 ›'
    ]);
}
?>

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

// 🚀 v3.29.0: 글자수 카운터는 통합 CharacterCounter 클래스 사용 (footer.php에서 로드됨)

// 폼 제출 전 글자수 검증 함수
function validateCharacterLimit(content, maxLength = 2000) {
    if (content.length > maxLength) {
        Toast.warning(`댓글은 최대 ${maxLength.toLocaleString()}자까지 입력 가능합니다. (현재: ${content.length.toLocaleString()}자)`);
        return false;
    }
    return true;
}

// 댓글 작성
function submitComment(event) {
    event.preventDefault();
    
    const rawContent = document.getElementById('comment-content').value;
    const content = normalizeText(rawContent);
    if (!content) {
        Toast.error('댓글 내용을 입력해주세요.');
        return;
    }
    
    // 글자수 제한 검증 추가
    if (!validateCharacterLimit(content, 2000)) {
        return;
    }
    
    const submitBtn = document.querySelector('.comment-submit');
    submitBtn.disabled = true;
    submitBtn.textContent = '작성 중...';

    // 🚀 v3.42.0: ApiClient 사용 (fetch → ApiClient.post)
    ApiClient.post('/api/comments',
        {
            post_id: window.currentPostId,
            content: content
        },
        { noLoading: true } // 버튼 상태로 로딩 표시
    )
    .then(result => {
        console.log('댓글 작성 응답:', result);

        if (result.success) {
            // 페이지 새로고침으로 댓글 목록 업데이트
            console.log('댓글 작성 성공, 페이지 새로고침');
            location.reload();
        } else {
            Toast.error(result.message || '댓글 작성에 실패했습니다.');
        }
    })
    .catch(error => {
        console.error('댓글 작성 오류:', error);
        // ApiClient가 이미 Toast 표시했으므로 추가 표시 불필요
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
        Toast.error('답글 내용을 입력해주세요.');
        return;
    }
    
    // 글자수 제한 검증 추가
    if (!validateCharacterLimit(content, 2000)) {
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
    .then(response => {
        console.log('Reply response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Reply response data:', data);
        
        // 다양한 성공 응답 형태 처리
        const isSuccess = data.success === true || 
                         (data.status === 'success' && data.data && data.data.success === true) ||
                         (data.data && data.data.success === true);
        
        if (isSuccess) {
            console.log('답글 작성 성공, 페이지 새로고침');
            location.reload();
        } else {
            const errorMessage = data.message || 
                               (data.data && data.data.message) || 
                               '답글 작성에 실패했습니다.';
            console.error('답글 작성 실패:', errorMessage);
            Toast.error(errorMessage);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Toast.error('답글 작성 중 오류가 발생했습니다.');
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

// 댓글 수정 모드 (공지사항과 동일한 인라인 편집)
function editComment(commentId) {
    const contentDiv = document.getElementById('comment-content-' + commentId);
    const editForm = document.getElementById('edit-form-' + commentId);
    
    if (!contentDiv || !editForm) {
        // 수정 폼이 없는 경우 동적으로 생성
        showInlineEditForm(commentId);
        return;
    }
    
    // 기존 내용을 편집 폼에 설정
    const currentContent = contentDiv.textContent.trim();
    const textarea = editForm.querySelector('textarea');
    textarea.value = currentContent;
    
    // 내용 숨기고 편집 폼 표시
    contentDiv.style.display = 'none';
    editForm.style.display = 'block';
    textarea.focus();
}

// 인라인 편집 폼 동적 생성
function showInlineEditForm(commentId) {
    const commentItem = document.querySelector(`[data-comment-id="${commentId}"]`);
    const contentDiv = document.getElementById('comment-content-' + commentId);
    
    if (!commentItem || !contentDiv) {
        Toast.error('댓글을 찾을 수 없습니다.');
        return;
    }
    
    const currentContent = contentDiv.textContent.trim();
    
    // 편집 폼 HTML 생성 (글자수 카운터 포함)
    const editFormHTML = `
        <div class="comment-edit-form" id="edit-form-${commentId}" style="margin-top: 10px;">
            <textarea id="edit-content-${commentId}" maxlength="2000" style="width: 100%; min-height: 80px; padding: 10px 12px; border: 2px solid #e2e8f0; border-radius: 6px; font-family: inherit; font-size: 14px; resize: vertical; transition: border-color 0.3s ease; box-sizing: border-box;">${currentContent}</textarea>
            <div class="edit-char-counter" style="margin-top: 5px; text-align: right;">
                <span id="edit-char-count-${commentId}" style="color: #64748b; font-size: 12px;">0</span>
                <span style="color: #94a3b8; font-size: 12px;">/2,000자</span>
            </div>
            <div class="comment-edit-actions" style="display: flex; gap: 8px; margin-top: 8px; justify-content: flex-end;">
                <button type="button" onclick="cancelEditComment(${commentId})" style="font-size: 12px; padding: 6px 12px; min-width: 80px; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 4px; cursor: pointer; color: #4a5568;">취소</button>
                <button type="button" onclick="updateComment(${commentId})" style="font-size: 12px; padding: 6px 12px; min-width: 80px; background: #2563eb; color: white; border: 1px solid #2563eb; border-radius: 4px; cursor: pointer;">저장</button>
            </div>
        </div>
    `;
    
    // 댓글 내용 다음에 편집 폼 삽입
    contentDiv.insertAdjacentHTML('afterend', editFormHTML);
    
    // 내용 숨기고 편집 폼 표시
    contentDiv.style.display = 'none';
    
    const textarea = document.getElementById(`edit-content-${commentId}`);
    const countElement = document.getElementById(`edit-char-count-${commentId}`);
    textarea.focus();

    // 🚀 v3.29.0: 통합 CharacterCounter 클래스 사용
    new CharacterCounter(textarea, countElement, 2000, {
        showMaxLength: false,
        warningThreshold: 0.9,
        errorThreshold: 1.0
    });
    
    // textarea 포커스 시 테두리 색상 변경
    textarea.addEventListener('focus', function() {
        this.style.borderColor = '#2563eb';
        this.style.boxShadow = '0 0 0 3px rgba(37, 99, 235, 0.1)';
    });
    
    textarea.addEventListener('blur', function() {
        this.style.borderColor = '#e2e8f0';
        this.style.boxShadow = 'none';
    });
}

// 댓글 수정 취소
function cancelEditComment(commentId) {
    const contentDiv = document.getElementById('comment-content-' + commentId);
    const editForm = document.getElementById('edit-form-' + commentId);
    
    if (contentDiv) {
        contentDiv.style.display = 'block';
    }
    
    if (editForm) {
        editForm.remove(); // 동적으로 생성된 폼 제거
    }
}

// 댓글 수정 저장
function updateComment(commentId) {
    const textarea = document.getElementById(`edit-content-${commentId}`);
    
    if (!textarea) {
        Toast.error('편집 폼을 찾을 수 없습니다.');
        return;
    }
    
    const content = normalizeText(textarea.value);
    
    if (!content) {
        Toast.error('댓글 내용을 입력해주세요.');
        return;
    }
    
    if (content.length < 2) {
        Toast.error('댓글은 2자 이상 입력해주세요.');
        return;
    }
    
    // 글자수 제한 검증 (2,000자)
    if (!validateCharacterLimit(content, 2000)) {
        return;
    }
    
    // 저장 버튼 비활성화
    const saveBtn = textarea.parentElement.querySelector('button[onclick*="updateComment"]');
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.textContent = '저장 중...';
    }
    
    fetch('/api/comments/' + commentId, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': getCsrfToken()
        },
        body: JSON.stringify({
            content: content
        })
    })
    .then(response => {
        console.log('Edit response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Edit response data:', data);
        
        // 다양한 성공 응답 형태 처리
        const isSuccess = data.success === true || 
                         (data.status === 'success' && data.data && data.data.success === true) ||
                         (data.data && data.data.success === true);
        
        if (isSuccess) {
            console.log('댓글 수정 성공, 페이지 새로고침');
            location.reload();
        } else {
            const errorMessage = data.message || 
                               (data.data && data.data.message) || 
                               '댓글 수정에 실패했습니다.';
            console.error('댓글 수정 실패:', errorMessage);
            Toast.error(errorMessage);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Toast.error('댓글 수정 중 오류가 발생했습니다.');
    })
    .finally(() => {
        // 버튼 상태 복원
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.textContent = '저장';
        }
    });
}

// 댓글 삭제
async function deleteComment(commentId) {
    if (!(await Modal.confirm('정말로 이 댓글을 삭제하시겠습니까?', { type: 'warning' }))) {
        return;
    }
    
    fetch('/api/comments/' + commentId, {
        method: 'DELETE',
        headers: {
            'X-CSRF-Token': getCsrfToken()
        }
    })
    .then(response => {
        console.log('Delete response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Delete response data:', data);
        
        // 다양한 성공 응답 형태 처리 (댓글 작성과 동일한 로직)
        const isSuccess = data.success === true || 
                         (data.status === 'success' && data.data && data.data.success === true) ||
                         (data.data && data.data.success === true) ||
                         (data.status === 'success'); // ResponseHelper의 status 필드도 확인
        
        if (isSuccess) {
            console.log('댓글 삭제 성공, 페이지 새로고침');
            location.reload();
        } else {
            const errorMessage = data.message || 
                               (data.data && data.data.message) || 
                               '댓글 삭제에 실패했습니다.';
            console.error('댓글 삭제 실패:', errorMessage);
            Toast.error(errorMessage);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Toast.error('댓글 삭제 중 오류가 발생했습니다.');
    });
}

// 댓글 작성자와 채팅 시작
function startChatWithCommentAuthor(authorId, authorName) {
    if (!authorId) {
        Toast.error('댓글 작성자 정보를 찾을 수 없습니다.');
        return;
    }
    
    // 채팅 페이지로 이동하면서 해당 사용자와 채팅 시작
    window.location.href = `/chat#user-${authorId}`;
}

// 페이지 로드 시 해시 앵커로 스크롤
document.addEventListener('DOMContentLoaded', function() {
    // 메인 댓글 textarea 글자수 카운터 설정
    const mainCommentTextarea = document.getElementById('comment-content');
    const mainCommentCountElement = document.getElementById('comment-char-count');

    // 🚀 v3.29.0: 통합 CharacterCounter 클래스 사용
    if (mainCommentTextarea && mainCommentCountElement) {
        new CharacterCounter(mainCommentTextarea, mainCommentCountElement, 2000, {
            showMaxLength: false,
            warningThreshold: 0.9,
            errorThreshold: 1.0
        });
    }
    
    // 🚀 v3.29.0: 답글 textarea들에 통합 CharacterCounter 클래스 사용
    function setupReplyCounters() {
        const replyTextareas = document.querySelectorAll('.reply-textarea');
        replyTextareas.forEach(function(textarea) {
            const replyForm = textarea.closest('.reply-form');
            const countElement = replyForm ? replyForm.querySelector('.reply-char-count') : null;

            if (countElement) {
                new CharacterCounter(textarea, countElement, 2000, {
                    showMaxLength: false,
                    warningThreshold: 0.9,
                    errorThreshold: 1.0
                });
            }
        });
    }
    
    // 초기 답글 카운터 설정
    setupReplyCounters();
    
    // 답글 폼이 동적으로 생성될 때도 카운터 설정 (toggleReplyForm 함수가 호출될 때)
    const originalToggleReplyForm = window.toggleReplyForm;
    window.toggleReplyForm = function(commentId) {
        originalToggleReplyForm(commentId);
        // 약간의 딜레이 후 카운터 설정 (DOM 업데이트 후)
        setTimeout(setupReplyCounters, 50);
    };
    
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