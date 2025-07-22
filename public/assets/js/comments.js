/**
 * 댓글 시스템 JavaScript
 */

// 전역 변수
let currentPostId = null;

// 페이지 로드 시 초기화
document.addEventListener('DOMContentLoaded', function() {
    // 게시글 ID 가져오기
    const postElement = document.querySelector('[data-post-id]');
    if (postElement) {
        currentPostId = postElement.dataset.postId;
        loadComments();
    }
});

/**
 * 댓글 목록 불러오기
 */
function loadComments() {
    if (!currentPostId) return;
    
    fetch(`/api/comments?post_id=${currentPostId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderComments(data.comments);
            updateCommentCount(data.count);
        }
    })
    .catch(error => {
        console.error('Error loading comments:', error);
    });
}

/**
 * 댓글 렌더링
 */
function renderComments(comments) {
    const container = document.getElementById('comments-list');
    if (!container) return;
    
    if (comments.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8 text-gray-500">
                첫 번째 댓글을 작성해보세요!
            </div>
        `;
        return;
    }
    
    container.innerHTML = comments.map(comment => renderCommentHTML(comment, 0)).join('');
}

/**
 * 단일 댓글 HTML 생성
 */
function renderCommentHTML(comment, depth) {
    const currentUserId = getCurrentUserId();
    const isOwner = currentUserId && comment.user_id == currentUserId;
    const indentClass = depth > 0 ? `ml-${Math.min(depth * 12, 60)}` : '';
    
    let html = `
        <div class="comment-item ${indentClass}" data-comment-id="${comment.id}" data-depth="${depth}">
            <div class="bg-white rounded-lg shadow-sm p-4 mb-3">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        ${comment.profile_image ? 
                            `<img src="${comment.profile_image}" alt="${comment.author_name}" class="w-10 h-10 rounded-full">` :
                            `<div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center">
                                <span class="text-gray-600 font-medium">${comment.author_name.charAt(0)}</span>
                            </div>`
                        }
                    </div>
                    <div class="ml-3 flex-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-medium text-gray-900">${escapeHtml(comment.author_name)}</span>
                                <span class="text-sm text-gray-500 ml-2">
                                    ${formatDate(comment.created_at)}
                                    ${comment.updated_at > comment.created_at ? '<span class="text-xs">(수정됨)</span>' : ''}
                                </span>
                            </div>
                            ${isOwner ? `
                                <div class="flex items-center space-x-2">
                                    <button onclick="editComment(${comment.id})" class="text-sm text-gray-600 hover:text-blue-600">
                                        수정
                                    </button>
                                    <button onclick="deleteComment(${comment.id})" class="text-sm text-gray-600 hover:text-red-600">
                                        삭제
                                    </button>
                                </div>
                            ` : ''}
                        </div>
                        <div class="comment-content mt-2 text-gray-700" id="comment-content-${comment.id}">
                            ${escapeHtml(comment.content).replace(/\n/g, '<br>')}
                        </div>
                        <div class="comment-edit-form hidden mt-2" id="comment-edit-form-${comment.id}">
                            <textarea class="w-full p-2 border border-gray-300 rounded-lg resize-none" rows="3">${escapeHtml(comment.content)}</textarea>
                            <div class="mt-2 flex justify-end space-x-2">
                                <button onclick="cancelEdit(${comment.id})" class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800">
                                    취소
                                </button>
                                <button onclick="updateComment(${comment.id})" class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">
                                    수정
                                </button>
                            </div>
                        </div>
                        ${currentUserId && depth < 2 ? `
                            <button onclick="showReplyForm(${comment.id})" class="mt-2 text-sm text-blue-600 hover:text-blue-700">
                                답글
                            </button>
                        ` : ''}
                    </div>
                </div>
            </div>
            
            ${currentUserId ? `
                <div class="reply-form hidden ml-12 mb-3" id="reply-form-${comment.id}">
                    <div class="bg-gray-50 rounded-lg p-3">
                        <textarea class="w-full p-2 border border-gray-300 rounded-lg resize-none" rows="2" placeholder="답글을 작성해주세요..."></textarea>
                        <div class="mt-2 flex justify-end space-x-2">
                            <button onclick="hideReplyForm(${comment.id})" class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800">
                                취소
                            </button>
                            <button onclick="submitReply(${comment.id})" class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">
                                답글 작성
                            </button>
                        </div>
                    </div>
                </div>
            ` : ''}
        </div>
    `;
    
    // 대댓글 추가
    if (comment.replies && comment.replies.length > 0) {
        html += comment.replies.map(reply => renderCommentHTML(reply, depth + 1)).join('');
    }
    
    return html;
}

/**
 * 댓글 작성
 */
function submitComment() {
    const textarea = document.getElementById('comment-content');
    const content = textarea.value.trim();
    
    if (!content) {
        alert('댓글 내용을 입력해주세요.');
        return;
    }
    
    const formData = new FormData();
    formData.append('post_id', currentPostId);
    formData.append('content', content);
    formData.append('csrf_token', getCsrfToken());
    
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
            textarea.value = '';
            loadComments();
        } else {
            alert(data.error || '댓글 작성에 실패했습니다.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('댓글 작성 중 오류가 발생했습니다.');
    });
}

/**
 * 답글 폼 표시
 */
function showReplyForm(commentId) {
    // 다른 답글 폼 숨기기
    document.querySelectorAll('.reply-form').forEach(form => {
        form.classList.add('hidden');
    });
    
    // 선택한 답글 폼 표시
    const replyForm = document.getElementById(`reply-form-${commentId}`);
    if (replyForm) {
        replyForm.classList.remove('hidden');
        replyForm.querySelector('textarea').focus();
    }
}

/**
 * 답글 폼 숨기기
 */
function hideReplyForm(commentId) {
    const replyForm = document.getElementById(`reply-form-${commentId}`);
    if (replyForm) {
        replyForm.classList.add('hidden');
        replyForm.querySelector('textarea').value = '';
    }
}

/**
 * 답글 작성
 */
function submitReply(parentId) {
    const replyForm = document.getElementById(`reply-form-${parentId}`);
    const textarea = replyForm.querySelector('textarea');
    const content = textarea.value.trim();
    
    if (!content) {
        alert('답글 내용을 입력해주세요.');
        return;
    }
    
    const formData = new FormData();
    formData.append('post_id', currentPostId);
    formData.append('parent_id', parentId);
    formData.append('content', content);
    formData.append('csrf_token', getCsrfToken());
    
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
            hideReplyForm(parentId);
            loadComments();
        } else {
            alert(data.error || '답글 작성에 실패했습니다.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('답글 작성 중 오류가 발생했습니다.');
    });
}

/**
 * 댓글 수정 모드
 */
function editComment(commentId) {
    const contentDiv = document.getElementById(`comment-content-${commentId}`);
    const editForm = document.getElementById(`comment-edit-form-${commentId}`);
    
    if (contentDiv && editForm) {
        contentDiv.classList.add('hidden');
        editForm.classList.remove('hidden');
        editForm.querySelector('textarea').focus();
    }
}

/**
 * 댓글 수정 취소
 */
function cancelEdit(commentId) {
    const contentDiv = document.getElementById(`comment-content-${commentId}`);
    const editForm = document.getElementById(`comment-edit-form-${commentId}`);
    
    if (contentDiv && editForm) {
        contentDiv.classList.remove('hidden');
        editForm.classList.add('hidden');
    }
}

/**
 * 댓글 수정
 */
function updateComment(commentId) {
    const editForm = document.getElementById(`comment-edit-form-${commentId}`);
    const textarea = editForm.querySelector('textarea');
    const content = textarea.value.trim();
    
    if (!content) {
        alert('댓글 내용을 입력해주세요.');
        return;
    }
    
    const formData = new FormData();
    formData.append('comment_id', commentId);
    formData.append('content', content);
    formData.append('csrf_token', getCsrfToken());
    
    fetch('/api/comments', {
        method: 'PUT',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 수정된 내용 즉시 반영
            const contentDiv = document.getElementById(`comment-content-${commentId}`);
            contentDiv.innerHTML = escapeHtml(content).replace(/\n/g, '<br>');
            cancelEdit(commentId);
        } else {
            alert(data.error || '댓글 수정에 실패했습니다.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('댓글 수정 중 오류가 발생했습니다.');
    });
}

/**
 * 댓글 삭제
 */
function deleteComment(commentId) {
    if (!confirm('정말로 이 댓글을 삭제하시겠습니까?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('comment_id', commentId);
    formData.append('csrf_token', getCsrfToken());
    
    fetch('/api/comments', {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadComments();
        } else {
            alert(data.error || '댓글 삭제에 실패했습니다.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('댓글 삭제 중 오류가 발생했습니다.');
    });
}

/**
 * 댓글 수 업데이트
 */
function updateCommentCount(count) {
    const countElement = document.getElementById('comment-count');
    if (countElement) {
        countElement.textContent = count;
    }
}

/**
 * 현재 사용자 ID 가져오기
 */
function getCurrentUserId() {
    const userElement = document.querySelector('[data-user-id]');
    return userElement ? userElement.dataset.userId : null;
}

/**
 * CSRF 토큰 가져오기
 */
function getCsrfToken() {
    const tokenElement = document.querySelector('meta[name="csrf-token"]');
    return tokenElement ? tokenElement.getAttribute('content') : '';
}

/**
 * HTML 이스케이프
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * 날짜 포맷
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    
    // 1분 미만
    if (diff < 60000) {
        return '방금 전';
    }
    
    // 1시간 미만
    if (diff < 3600000) {
        const minutes = Math.floor(diff / 60000);
        return `${minutes}분 전`;
    }
    
    // 24시간 미만
    if (diff < 86400000) {
        const hours = Math.floor(diff / 3600000);
        return `${hours}시간 전`;
    }
    
    // 날짜 표시
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hour = String(date.getHours()).padStart(2, '0');
    const minute = String(date.getMinutes()).padStart(2, '0');
    
    return `${year}.${month}.${day} ${hour}:${minute}`;
}