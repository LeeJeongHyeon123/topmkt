<?php
/**
 * 댓글 작성 폼 템플릿
 * 
 * @var int $postId 게시글 ID
 * @var int|null $parentId 부모 댓글 ID (대댓글인 경우)
 * @var bool $isLoggedIn 로그인 여부
 */
?>

<?php if ($isLoggedIn): ?>
<div class="comment-form-container">
    <form id="comment-form" class="comment-form" data-post-id="<?= $postId ?>" data-parent-id="<?= $parentId ?? '' ?>">
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="mb-3">
                <label for="comment-textarea" class="sr-only">댓글 내용</label>
                <textarea 
                    id="comment-textarea" 
                    name="content" 
                    class="w-full p-3 border border-gray-300 rounded-lg resize-none focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                    rows="3" 
                    placeholder="<?= $parentId ? '답글을 작성해주세요...' : '댓글을 작성해주세요...' ?>"
                    required></textarea>
            </div>
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-500">
                    <span id="char-count">0</span> / 1000
                </div>
                <div class="flex space-x-2">
                    <?php if ($parentId): ?>
                    <button 
                        type="button" 
                        onclick="cancelReply()" 
                        class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                        취소
                    </button>
                    <?php endif; ?>
                    <button 
                        type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        id="submit-button">
                        <?= $parentId ? '답글 작성' : '댓글 작성' ?>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// 문자 수 카운트
document.getElementById('comment-textarea').addEventListener('input', function(e) {
    const charCount = e.target.value.length;
    document.getElementById('char-count').textContent = charCount;
    
    // 1000자 제한
    if (charCount > 1000) {
        e.target.value = e.target.value.substring(0, 1000);
        document.getElementById('char-count').textContent = 1000;
    }
});

// 폼 제출
document.getElementById('comment-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = e.target;
    const submitButton = document.getElementById('submit-button');
    const textarea = document.getElementById('comment-textarea');
    const content = textarea.value.trim();
    
    if (!content) {
        alert('댓글 내용을 입력해주세요.');
        return;
    }
    
    // 버튼 비활성화
    submitButton.disabled = true;
    submitButton.textContent = '처리중...';
    
    // AJAX 요청
    const formData = new FormData();
    formData.append('post_id', form.dataset.postId);
    formData.append('content', content);
    if (form.dataset.parentId) {
        formData.append('parent_id', form.dataset.parentId);
    }
    
    fetch('/api/comments', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 댓글 목록 새로고침
            loadComments();
            // 폼 초기화
            textarea.value = '';
            document.getElementById('char-count').textContent = '0';
            // 답글 폼인 경우 숨기기
            if (form.dataset.parentId) {
                cancelReply();
            }
        } else {
            alert(data.error || '댓글 작성에 실패했습니다.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('댓글 작성 중 오류가 발생했습니다.');
    })
    .finally(() => {
        // 버튼 다시 활성화
        submitButton.disabled = false;
        submitButton.textContent = form.dataset.parentId ? '답글 작성' : '댓글 작성';
    });
});
</script>

<?php else: ?>
<div class="comment-form-container">
    <div class="bg-gray-100 rounded-lg p-4 text-center">
        <p class="text-gray-600">
            댓글을 작성하려면 <a href="/auth/login?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="text-blue-600 hover:underline font-medium">로그인</a>이 필요합니다.
        </p>
    </div>
</div>
<?php endif; ?>