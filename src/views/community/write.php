<?php
/**
 * 커뮤니티 게시글 작성/수정 페이지
 * v3.14.1+ - 취소 버튼 confirm 시스템
 */

// 강력한 캐시 무효화 헤더
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("ETag: \"" . md5(time()) . "\"");

// 로그인 상태 확인
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
require_once SRC_PATH . '/config/upload.php';
$isLoggedIn = AuthMiddleware::isLoggedIn();
$currentUserId = AuthMiddleware::getCurrentUserId();

// 로그인하지 않은 경우 로그인 페이지로 리다이렉트
if (!$isLoggedIn) {
    header('Location: /auth/login');
    exit;
}

$isEdit = isset($action) && $action === 'edit';
$pageTitle = $isEdit ? '게시글 수정' : '새 게시글 작성';
$submitText = $isEdit ? '수정하기' : '작성하기';
?>

<style>
/* 게시글 작성/수정 페이지 스타일 */
.write-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    min-height: calc(100vh - 200px);
}

.write-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    text-align: center;
    margin-top: 60px;
    margin-bottom: 30px;
    border-radius: 12px;
}

.write-header h1 {
    font-size: 2rem;
    margin-bottom: 10px;
    font-weight: 700;
}

.write-header p {
    font-size: 1rem;
    opacity: 0.9;
    margin: 0;
}

.write-form {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
}

.form-group {
    margin-bottom: 25px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #2d3748;
    font-size: 14px;
}

.form-label.required::after {
    content: ' *';
    color: #e53e3e;
}

.form-input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 16px;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    box-sizing: border-box;
}

.form-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-textarea {
    resize: vertical;
    min-height: 300px;
    font-family: inherit;
    line-height: 1.6;
}

.char-counter {
    font-size: 12px;
    color: #718096;
    text-align: right;
    margin-top: 5px;
}

.char-counter.warning {
    color: #d69e2e;
}

.char-counter.error {
    color: #e53e3e;
}

.form-buttons {
    display: flex;
    gap: 12px;
    justify-content: space-between;
    align-items: center;
    margin-top: 30px;
    flex-wrap: wrap;
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
    min-width: 120px;
    justify-content: center;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.btn-secondary {
    background: #718096;
    color: white;
}

.btn-secondary:hover {
    background: #4a5568;
}

.btn-danger {
    background: #e53e3e;
    color: white;
}

.btn-danger:hover {
    background: #c53030;
}

.form-tips {
    background: #f3f4ff;
    border: 1px solid #a5b4fc;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
}

.form-tips h4 {
    color: #4c1d95;
    margin: 0 0 10px 0;
    font-size: 14px;
}

.form-tips ul {
    margin: 0;
    padding-left: 20px;
    color: #5b21b6;
    font-size: 13px;
}

.form-tips li {
    margin-bottom: 4px;
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.loading-content {
    background: white;
    padding: 30px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #e2e8f0;
    border-top: 4px solid #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 15px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* 모바일 반응형 */
@media (max-width: 768px) {
    .write-container {
        padding: 15px;
    }
    
    .write-header {
        padding: 25px 20px;
    }
    
    .write-header h1 {
        font-size: 1.5rem;
    }
    
    .write-form {
        padding: 20px;
    }
    
    .form-buttons {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
    }
    
    .btn {
        flex: 1;
        min-width: 80px;
        max-width: 120px;
        font-size: 14px;
        padding: 10px 8px;
    }
}

/* Quill.js 에디터 커스터마이징 */
.ql-editor {
    font-family: inherit;
    font-size: 16px;
    line-height: 1.6;
    padding: 20px;
    min-height: 280px;
    user-select: text !important;
    -webkit-user-select: text !important;
    -moz-user-select: text !important;
    -ms-user-select: text !important;
}

.ql-editor.ql-blank::before {
    color: #9ca3af;
    font-style: normal;
    left: 20px;
}

.ql-toolbar {
    border-top: 2px solid #e2e8f0;
    border-left: 2px solid #e2e8f0;
    border-right: 2px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;
    border-radius: 8px 8px 0 0;
    background: #f8fafc;
}

.ql-container {
    border-left: 2px solid #e2e8f0;
    border-right: 2px solid #e2e8f0;
    border-bottom: 2px solid #e2e8f0;
    border-radius: 0 0 8px 8px;
    font-family: inherit;
    user-select: text !important;
    -webkit-user-select: text !important;
    -moz-user-select: text !important;
    -ms-user-select: text !important;
}

.ql-toolbar:focus-within + .ql-container,
.ql-container:focus-within {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.ql-toolbar .ql-picker-label:hover,
.ql-toolbar .ql-picker-item:hover,
.ql-toolbar button:hover {
    color: #48bb78;
}

.ql-toolbar button.ql-active,
.ql-toolbar .ql-picker-label.ql-active,
.ql-toolbar .ql-picker-item.ql-selected {
    color: #48bb78;
}

.ql-snow .ql-tooltip {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.ql-snow .ql-tooltip input[type=text] {
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    padding: 8px;
}

.ql-snow .ql-tooltip a.ql-action::after {
    color: #48bb78;
}

/* 이미지 업로드 피드백 */
.ql-editor img {
    max-width: 100%;
    height: auto;
    margin: 10px 0;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* 텍스트 선택 강제 활성화 - 최우선 적용 */
*[class*="ql-"],
#quill-editor,
#quill-editor *,
.ql-editor,
.ql-editor *,
.ql-container,
.ql-container *,
.ql-editor p,
.ql-editor div,
.ql-editor span,
.ql-editor strong,
.ql-editor em,
.ql-editor u,
.ql-editor ol,
.ql-editor ul,
.ql-editor li,
.ql-editor h1,
.ql-editor h2,
.ql-editor h3 {
    user-select: text !important;
    -webkit-user-select: text !important;
    -moz-user-select: text !important;
    -ms-user-select: text !important;
    -webkit-touch-callout: default !important;
    -webkit-user-drag: text !important;
    pointer-events: auto !important;
    cursor: text !important;
}

/* 에디터 영역 커서 강제 설정 */
.ql-editor {
    cursor: text !important;
}

.ql-editor:hover {
    cursor: text !important;
}

/* 모든 텍스트 요소에 텍스트 커서 적용 */
.ql-editor *:not(img):not(button):not(input) {
    cursor: text !important;
}

/* 기본 커서 스타일 제거 */
.ql-editor, .ql-editor * {
    cursor: text !important;
}

/* 최우선 순위로 텍스트 커서 적용 */
#quill-editor .ql-editor,
#quill-editor .ql-editor *,
#quill-editor .ql-container,
#quill-editor .ql-container * {
    cursor: text !important;
    user-select: text !important;
    -webkit-user-select: text !important;
    -moz-user-select: text !important;
    -ms-user-select: text !important;
    pointer-events: auto !important;
}

/* 전역 스타일 덮어쓰기 */
body #quill-editor .ql-editor,
body #quill-editor .ql-editor *,
html #quill-editor .ql-editor,
html #quill-editor .ql-editor * {
    cursor: text !important;
    user-select: text !important;
    -webkit-user-select: text !important;
    -moz-user-select: text !important;
    -ms-user-select: text !important;
}

/* 드래그 선택 활성화 */
.ql-editor p,
.ql-editor span,
.ql-editor div,
.ql-editor strong,
.ql-editor em,
.ql-editor u {
    user-select: text !important;
    -webkit-user-select: text !important;
    -moz-user-select: text !important;
    -ms-user-select: text !important;
}

/* 다크모드 대응 */
@media (prefers-color-scheme: dark) {
    .write-form {
        background: #2d3748;
        border-color: #4a5568;
    }
    
    .form-label {
        color: #e2e8f0;
    }
    
    .form-input {
        background: #4a5568;
        border-color: #718096;
        color: #e2e8f0;
    }
    
    .form-input:focus {
        border-color: #667eea;
        background: #4a5568;
    }
    
    .ql-toolbar {
        background: #4a5568;
        border-color: #718096;
    }
    
    .ql-container {
        border-color: #718096;
    }
    
    .ql-editor {
        background: #4a5568;
        color: #e2e8f0;
    }
    
    .ql-snow .ql-tooltip {
        background: #4a5568;
        border-color: #718096;
        color: #e2e8f0;
    }
}
</style>

<!-- Quill.js CDN - 최신 안정 버전 2.0 (deprecated 이벤트 문제 완전 해결) -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

<!-- 공통 업로드 설정 (validateFileSize 함수 사용 전에 로드) -->
<?php include '/var/www/html/topmkt/src/views/includes/upload-config.js.php'; ?>

<div class="write-container">
    <!-- 헤더 섹션 -->
    <div class="write-header">
        <h1><?= $isEdit ? '📝 게시글 수정' : '✍️ 새 게시글 작성' ?></h1>
        <p><?= $isEdit ? '게시글을 수정해주세요' : '커뮤니티에 새로운 이야기를 공유해주세요' ?></p>
    </div>
    
    <!-- 작성 팁 -->
    <div class="form-tips">
        <h4>💡 게시글 작성 팁</h4>
        <ul>
            <li>제목은 간결하고 명확하게 작성해주세요 (200자 이내)</li>
            <li>내용은 구체적이고 유용한 정보를 포함해주세요 (10,000자 이내)</li>
            <li>예의를 지키고 타인을 배려하는 글을 작성해주세요</li>
            <li>스팸성 내용이나 광고는 삼가해주세요</li>
        </ul>
    </div>
    
    <!-- 게시글 작성 폼 -->
    <form id="writeForm" class="write-form">
        <!-- CSRF 토큰 -->
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        
        <!-- 제목 입력 -->
        <div class="form-group">
            <label for="title" class="form-label required">제목</label>
            <input type="text" 
                   id="title" 
                   name="title" 
                   class="form-input" 
                   placeholder="게시글 제목을 입력해주세요"
                   value="<?= isset($post) ? htmlspecialchars($post['title']) : '' ?>"
                   maxlength="200"
                   required>
            <div id="titleCounter" class="char-counter">0 / 200</div>
        </div>
        
        <!-- 내용 입력 (리치 텍스트 에디터) -->
        <div class="form-group">
            <label for="content" class="form-label required">내용</label>
            <div id="quill-editor" style="height: 300px; border: 2px solid #e2e8f0; border-radius: 8px; user-select: text; -webkit-user-select: text; -moz-user-select: text; -ms-user-select: text;"></div>
            <textarea id="content" 
                      name="content" 
                      style="display: none;"
                      required><?= isset($post) ? htmlspecialchars($post['content']) : '' ?></textarea>
            <div id="contentCounter" class="char-counter">0 / 10,000</div>
            <div id="imageCounter" class="char-counter" style="color: #2563eb; font-weight: 500;">📷 이미지: 0 / 20</div>
            <div class="editor-tips" style="margin-top: 8px; font-size: 12px; color: #718096;">
                💡 <strong>에디터 사용법:</strong> 
                텍스트 선택 후 포맷 적용 | 이미지 업로드 버튼 클릭 | Ctrl+Z로 실행 취소
            </div>
        </div>
        
        <!-- 버튼 영역 -->
        <div class="form-buttons">
            <?php if ($isEdit): ?>
                <button type="button" id="deleteBtn" class="btn btn-danger">
                    🗑️ 삭제
                </button>
            <?php endif; ?>
            <button type="button" id="cancelBtn" class="btn btn-secondary" 
                    data-version="v3.14.1-FINAL" 
                    data-timestamp="<?= time() ?>"
                    title="v3.14.1-FINAL Enhanced Cancel Button - 작성된 내용이 있을 때만 확인">
                ❌ 취소
            </button>
            <button type="submit" id="submitBtn" class="btn btn-primary">
                <span id="submitText"><?= $submitText ?></span>
            </button>
        </div>
    </form>
</div>

<!-- 로딩 오버레이 -->
<div id="loadingOverlay" class="loading-overlay">
    <div class="loading-content">
        <div class="loading-spinner"></div>
        <p><?= $isEdit ? '게시글을 수정하고 있습니다...' : '게시글을 작성하고 있습니다...' ?></p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('✍️ 게시글 작성 페이지 로드 완료');
    
    const form = document.getElementById('writeForm');
    const titleInput = document.getElementById('title');
    const contentTextarea = document.getElementById('content');
    const titleCounter = document.getElementById('titleCounter');
    const contentCounter = document.getElementById('contentCounter');
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const loadingOverlay = document.getElementById('loadingOverlay');
    const deleteBtn = document.getElementById('deleteBtn');
    
    const isEdit = <?= $isEdit ? 'true' : 'false' ?>;
    const postId = <?= isset($post) ? $post['id'] : 'null' ?>;
    
    // Quill.js 에디터 초기화
    let quill;
    
    // 이미지 업로드 핸들러
    function imageHandler() {
        const input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/jpeg,image/jpg,image/png,image/gif,image/webp');
        input.style.display = 'none';
        
        input.onchange = async function() {
            const file = input.files[0];
            if (!file) return;
            
            // 파일 크기 검증 (공통 설정 사용: 30MB)
            if (!window.validateFileSize || !window.validateFileSize(file.size)) {
                alert(window.getFileSizeErrorMessage ? window.getFileSizeErrorMessage() : '파일 크기가 너무 큽니다.');
                return;
            }
            
            // 이미지 개수 제한 검사 (20개)
            const currentImages = quill.container.querySelectorAll('img').length;
            if (currentImages >= 20) {
                alert(`최대 20개의 이미지만 업로드할 수 있습니다. (현재: ${currentImages}개)`);
                return;
            }
            
            let range = null;
            let loadingTextInserted = false;
            
            try {
                // Quill 에디터 상태 확인
                if (typeof quill === 'undefined' || !quill) {
                    throw new Error('Quill 에디터가 초기화되지 않았습니다.');
                }
                
                // 현재 선택 범위 가져오기
                range = quill.getSelection();
                if (!range) {
                    // 선택 범위가 없으면 에디터 끝으로 설정
                    range = { index: quill.getLength() };
                }
                
                // 로딩 표시
                quill.insertText(range.index, '이미지 업로드 중...', 'italic', true);
                loadingTextInserted = true;
                
                // CSRF 토큰 확인
                const csrfTokenElement = document.querySelector('input[name="csrf_token"]');
                if (!csrfTokenElement) {
                    throw new Error('CSRF 토큰을 찾을 수 없습니다. 페이지를 새로고침해주세요.');
                }
                
                // FormData 생성
                const formData = new FormData();
                formData.append('image', file);
                formData.append('csrf_token', csrfTokenElement.value);
                formData.append('upload_type', 'posts');
                
                console.log('🔄 이미지 업로드 시작:', file.name, 'Size:', file.size);
                
                // 업로드 요청
                const response = await fetch('/api/media/upload-image', {
                    method: 'POST',
                    body: formData
                });
                
                console.log('📡 서버 응답 상태:', response.status, response.statusText);
                
                // 응답 상태 코드 확인
                if (!response.ok) {
                    throw new Error(`서버 오류: ${response.status} ${response.statusText}`);
                }
                
                const result = await response.json();
                console.log('📦 응답 데이터:', result);
                
                // 업로드 중 텍스트 제거
                if (loadingTextInserted) {
                    quill.deleteText(range.index, '이미지 업로드 중...'.length);
                    loadingTextInserted = false;
                }
                
                if (result.success) {
                    // 이미지 삽입
                    quill.insertEmbed(range.index, 'image', result.data.url);
                    quill.setSelection(range.index + 1);
                    console.log('✅ 이미지 업로드 성공:', result.data.url);
                    
                    // 이미지 카운터 업데이트
                    updateImageCounter();
                } else {
                    throw new Error(result.message || '알 수 없는 오류가 발생했습니다.');
                }
                
            } catch (error) {
                console.error('❌ 이미지 업로드 오류 상세:', error);
                
                // 업로드 중 텍스트 제거 (오류 발생 시)
                if (loadingTextInserted && range && typeof quill !== 'undefined' && quill) {
                    try {
                        quill.deleteText(range.index, '이미지 업로드 중...'.length);
                    } catch (deleteError) {
                        console.error('로딩 텍스트 제거 실패:', deleteError);
                    }
                }
                
                // 구체적인 오류 메시지 표시
                let errorMessage = '이미지 업로드 중 오류가 발생했습니다.';
                if (error.message) {
                    errorMessage += '\n상세: ' + error.message;
                }
                
                alert(errorMessage);
            }
        };
        
        input.click();
    }
    
    // 이미지 개수 카운터 업데이트 함수
    function updateImageCounter() {
        const imageCounter = document.getElementById('imageCounter');
        if (!imageCounter) return;
        
        const currentImages = quill.container.querySelectorAll('img').length;
        const maxImages = 20;
        
        // 색상 및 스타일 설정
        let counterClass = '';
        let warningText = '';
        
        if (currentImages >= 18) { // 90% 이상
            counterClass = 'error'; // 빨간색
            warningText = ' ⚠️';
        } else if (currentImages >= 15) { // 75% 이상
            counterClass = 'warning'; // 주황색
            warningText = ' ⚠️';
        } else {
            counterClass = ''; // 기본 색상
        }
        
        imageCounter.className = `char-counter ${counterClass}`;
        imageCounter.innerHTML = `📷 이미지: ${currentImages} / ${maxImages}${warningText}`;
        
        console.log(`📊 이미지 카운터 업데이트: ${currentImages}/${maxImages}`);
    }
    
    // Quill 에디터 초기화
    quill = new Quill('#quill-editor', {
        theme: 'snow',
        placeholder: '게시글 내용을 입력해주세요...\n\n• 구체적이고 유용한 정보를 공유해주세요\n• 예의를 지키고 타인을 배려하는 글을 작성해주세요\n• 개인정보나 민감한 정보는 포함하지 마세요',
        modules: {
            toolbar: {
                container: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    [{ 'align': [] }],
                    ['link', 'image'],
                    ['clean']
                ],
                handlers: {
                    image: imageHandler
                }
            }
        },
        bounds: '#quill-editor'
    });
    
    // 🔥 Quill 에디터를 전역 접근 가능하도록 설정 (v3.14.1-FINAL)
    window.quill = quill;
    console.log('✅ window.quill 할당 완료:', !!window.quill);
    
    // 텍스트 변경 시 이미지 카운터 업데이트 및 제한 검사
    quill.on('text-change', function(delta, oldDelta, source) {
        // 이미지 개수 확인
        const currentImages = quill.container.querySelectorAll('img').length;
        
        // 20개 초과 시 초과분 제거
        if (currentImages > 20) {
            console.log(`⚠️ 이미지 개수 초과: ${currentImages}개 → 20개로 제한`);
            const images = quill.container.querySelectorAll('img');
            for (let i = 20; i < images.length; i++) {
                images[i].remove();
            }
            alert('최대 20개의 이미지만 허용됩니다. 초과된 이미지가 제거되었습니다.');
        }
        
        // 이미지 카운터 업데이트
        setTimeout(updateImageCounter, 100); // 약간의 지연으로 DOM 업데이트 후 실행
    });
    
    // 초기 이미지 카운터 설정
    setTimeout(updateImageCounter, 500);
    
    // Quill 에디터의 기본 이벤트를 덮어쓰기
    const editor = quill.root;
    
    // Quill의 selection 모듈에 접근해서 텍스트 선택 강제 활성화
    quill.on('selection-change', function(range, oldRange, source) {
        console.log('Selection changed:', range, source);
    });
    
    // 에디터에서 마우스 이벤트 처리
    let isMouseDown = false;
    let startPos = null;
    
    editor.addEventListener('mousedown', function(e) {
        console.log('마우스 다운:', e.target);
        isMouseDown = true;
        startPos = { x: e.clientX, y: e.clientY };
        
        // 기본 Quill 이벤트를 막지 않고 텍스트 선택 허용
        e.target.style.userSelect = 'text';
        e.target.style.webkitUserSelect = 'text';
    });
    
    editor.addEventListener('mousemove', function(e) {
        if (isMouseDown) {
            console.log('드래그 중');
            // 드래그 거리 계산
            const distance = Math.sqrt(
                Math.pow(e.clientX - startPos.x, 2) + 
                Math.pow(e.clientY - startPos.y, 2)
            );
            
            if (distance > 5) { // 5px 이상 드래그시 텍스트 선택 모드
                console.log('텍스트 선택 모드 활성화');
                document.body.style.userSelect = 'text';
                e.target.style.userSelect = 'text';
            }
        }
        
        // 커서 스타일 강제 적용
        e.target.style.cursor = 'text';
    });
    
    document.addEventListener('mouseup', function(e) {
        if (isMouseDown) {
            console.log('마우스 업');
            isMouseDown = false;
            startPos = null;
            document.body.style.userSelect = '';
        }
    });
    
    // DOM 요소에 직접 스타일 적용
    editor.style.cssText += 'cursor: text !important; user-select: text !important;';
    editor.setAttribute('contenteditable', 'true');
    
    console.log('Quill 에디터 초기화 완료, 에디터 요소:', editor);
    
    // 추가 입력 방지를 위한 키보드 이벤트 처리
    quill.root.addEventListener('keydown', function(e) {
        const currentLength = quill.getText().length;
        const maxLength = 10000;
        
        // 10,000자에 도달했을 때 추가 문자 입력 방지
        if (currentLength >= maxLength) {
            // 백스페이스, 삭제, 방향키, Ctrl 조합 등은 허용
            const allowedKeys = [
                'Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 
                'ArrowUp', 'ArrowDown', 'Home', 'End', 'Tab'
            ];
            
            // Ctrl이나 Cmd와 함께 사용하는 키 조합 허용 (복사, 붙여넣기 등)
            const isControlKey = e.ctrlKey || e.metaKey;
            const isNavigation = allowedKeys.includes(e.key);
            
            if (!isControlKey && !isNavigation && e.key.length === 1) {
                e.preventDefault();
                
                // 알림 표시 (너무 자주 표시되지 않도록 제한)
                if (!window.maxLengthKeyWarningShown) {
                    showMaxLengthWarning();
                    window.maxLengthKeyWarningShown = true;
                    setTimeout(() => {
                        window.maxLengthKeyWarningShown = false;
                    }, 2000);
                }
                
                console.log(`⚠️ 키 입력 차단: ${e.key} (현재 ${currentLength}/${maxLength}자)`);
            }
        }
    });
    
    // 복사-붙여넣기 시 글자 수 제한 처리
    quill.root.addEventListener('paste', function(e) {
        setTimeout(() => {
            const currentLength = quill.getText().length;
            const maxLength = 10000;
            
            if (currentLength > maxLength) {
                console.log(`⚠️ 붙여넣기 후 글자 수 초과: ${currentLength}/${maxLength}`);
                
                // 전체 텍스트를 10,000자로 제한
                const fullText = quill.getText();
                const limitedText = fullText.substring(0, maxLength);
                
                // 에디터 내용을 제한된 길이로 설정
                quill.setText(limitedText);
                
                showMaxLengthWarning();
            }
        }, 10);
    });
    
    // 텍스트 선택 강제 활성화
    setTimeout(function() {
        const editor = document.querySelector('.ql-editor');
        const container = document.querySelector('.ql-container');
        const quillDiv = document.querySelector('#quill-editor');
        
        if (editor) {
            editor.style.userSelect = 'text';
            editor.style.webkitUserSelect = 'text';
            editor.style.mozUserSelect = 'text';
            editor.style.msUserSelect = 'text';
            editor.style.cursor = 'text';
            editor.style.pointerEvents = 'auto';
            
            // 모든 자식 요소에도 적용
            const allElements = editor.querySelectorAll('*');
            allElements.forEach(el => {
                el.style.userSelect = 'text';
                el.style.webkitUserSelect = 'text';
                el.style.mozUserSelect = 'text';
                el.style.msUserSelect = 'text';
                el.style.cursor = 'text';
                el.style.pointerEvents = 'auto';
            });
            
            console.log('✅ 텍스트 선택 및 커서 활성화 완료');
        }
        
        // 전역 스타일 추가로 강제 적용 - 최대 우선순위
        const style = document.createElement('style');
        style.textContent = `
            html body div#quill-editor div.ql-editor,
            html body div#quill-editor div.ql-editor *,
            html body div#quill-editor div.ql-editor p,
            html body div#quill-editor div.ql-editor span,
            html body div#quill-editor div.ql-editor div,
            html body div#quill-editor div.ql-editor strong,
            html body div#quill-editor div.ql-editor em,
            html body div#quill-editor div.ql-editor u,
            html body div#quill-editor div.ql-editor h1,
            html body div#quill-editor div.ql-editor h2,
            html body div#quill-editor div.ql-editor h3,
            html body div#quill-editor div.ql-editor ol,
            html body div#quill-editor div.ql-editor ul,
            html body div#quill-editor div.ql-editor li {
                cursor: text !important;
                user-select: text !important;
                -webkit-user-select: text !important;
                -moz-user-select: text !important;
                -ms-user-select: text !important;
                pointer-events: auto !important;
            }
            
            /* 모든 것을 덮어쓰는 스타일 */
            [data-editor="true"] * {
                cursor: text !important;
                user-select: text !important;
                -webkit-user-select: text !important;
                -moz-user-select: text !important;
                -ms-user-select: text !important;
            }
        `;
        document.head.appendChild(style);
        
        // 에디터에 data 속성 추가
        setTimeout(() => {
            const editorElement = document.querySelector('#quill-editor');
            if (editorElement) {
                editorElement.setAttribute('data-editor', 'true');
                console.log('에디터에 data-editor 속성 추가됨');
            }
        }, 200);
        
        // 강력한 커서 스타일 적용 - 지속적으로 확인
        const forceCursorStyle = () => {
            const editorElements = document.querySelectorAll('#quill-editor, #quill-editor *, .ql-editor, .ql-editor *, .ql-container, .ql-container *');
            editorElements.forEach(el => {
                if (el) {
                    el.style.setProperty('cursor', 'text', 'important');
                    el.style.setProperty('user-select', 'text', 'important');
                    el.style.setProperty('-webkit-user-select', 'text', 'important');
                    el.style.setProperty('-moz-user-select', 'text', 'important');
                    el.style.setProperty('-ms-user-select', 'text', 'important');
                    el.style.setProperty('pointer-events', 'auto', 'important');
                }
            });
        };
        
        // 즉시 실행
        forceCursorStyle();
        
        // 100ms마다 재적용 (처음 5초간)
        let count = 0;
        const interval = setInterval(() => {
            forceCursorStyle();
            count++;
            if (count > 50) { // 5초 후 중단
                clearInterval(interval);
                console.log('✅ 커서 스타일 강제 적용 완료');
            }
        }, 100);
        
        // 디버깅용 전역 함수
        window.debugCursor = () => {
            const editor = document.querySelector('.ql-editor');
            if (editor) {
                console.log('에디터 찾음:', editor);
                console.log('현재 커서 스타일:', getComputedStyle(editor).cursor);
                console.log('현재 user-select:', getComputedStyle(editor).userSelect);
                
                // 강제로 스타일 재적용
                forceCursorStyle();
                
                console.log('재적용 후 커서 스타일:', getComputedStyle(editor).cursor);
            } else {
                console.log('에디터를 찾을 수 없음');
            }
        };
        
        console.log('👉 브라우저 콘솔에서 window.debugCursor() 함수를 실행해보세요');
        
        // 새로 추가되는 요소에도 텍스트 선택 활성화
        if (editor) {
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1) { // Element node
                            node.style.userSelect = 'text';
                            node.style.webkitUserSelect = 'text';
                            node.style.mozUserSelect = 'text';
                            node.style.msUserSelect = 'text';
                            node.style.cursor = 'text';
                            node.style.pointerEvents = 'auto';
                            
                            // 자식 요소들에도 적용
                            const children = node.querySelectorAll('*');
                            children.forEach(child => {
                                child.style.userSelect = 'text';
                                child.style.webkitUserSelect = 'text';
                                child.style.mozUserSelect = 'text';
                                child.style.msUserSelect = 'text';
                                child.style.cursor = 'text';
                                child.style.pointerEvents = 'auto';
                            });
                        }
                    });
                });
            });
            
            observer.observe(editor, {
                childList: true,
                subtree: true
            });
        }
    }, 100);
    
    // 에디터 내용이 변경될 때마다 히든 textarea 업데이트 및 글자 수 제한
    quill.on('text-change', function(delta, oldDelta, source) {
        const htmlContent = quill.root.innerHTML;
        const textContent = quill.getText();
        const currentLength = textContent.length;
        const maxLength = 10000;
        
        // 10,000자 초과 시 입력 방지
        if (currentLength > maxLength) {
            console.log(`⚠️ 글자 수 초과: ${currentLength}/${maxLength}`);
            
            // 이전 상태로 복원
            quill.history.undo();
            
            // 사용자에게 알림 (한 번만 표시하도록 제어)
            if (!window.maxLengthWarningShown) {
                showMaxLengthWarning();
                window.maxLengthWarningShown = true;
                
                // 5초 후 다시 알림 가능하도록 설정
                setTimeout(() => {
                    window.maxLengthWarningShown = false;
                }, 5000);
            }
            
            // 글자 수 카운터 업데이트 (제한된 길이로)
            const limitedText = quill.getText();
            updateContentCharCounter(limitedText.length);
            contentTextarea.value = quill.root.innerHTML;
        } else {
            // 정상 범위 내에서는 일반적인 업데이트
            contentTextarea.value = htmlContent;
            updateContentCharCounter(currentLength);
        }
    });
    
    // 기존 내용이 있으면 에디터에 설정
    if (contentTextarea.value.trim()) {
        quill.root.innerHTML = contentTextarea.value;
    }
    
    // 문자 수 카운터 업데이트 함수들
    function updateCharCounter(input, counter, maxLength) {
        const currentLength = input.value.length;
        counter.textContent = `${currentLength.toLocaleString()} / ${maxLength.toLocaleString()}`;
        
        // 경고 및 오류 상태 표시
        counter.className = 'char-counter';
        if (currentLength > maxLength * 0.9) {
            counter.classList.add('warning');
        }
        if (currentLength >= maxLength) {
            counter.classList.add('error');
        }
    }
    
    function updateContentCharCounter(currentLength) {
        const maxLength = 10000;
        contentCounter.textContent = `${currentLength.toLocaleString()} / ${maxLength.toLocaleString()}`;
        
        // 경고 및 오류 상태 표시
        contentCounter.className = 'char-counter';
        if (currentLength > maxLength * 0.9) {
            contentCounter.classList.add('warning');
        }
        if (currentLength >= maxLength) {
            contentCounter.classList.add('error');
        }
    }
    
    // 최대 글자 수 도달 알림 표시 함수
    function showMaxLengthWarning() {
        // 기존 알림이 있으면 제거
        const existingWarning = document.querySelector('.max-length-warning');
        if (existingWarning) {
            existingWarning.remove();
        }
        
        // 알림 요소 생성
        const warningDiv = document.createElement('div');
        warningDiv.className = 'max-length-warning';
        warningDiv.innerHTML = `
            <div class="warning-content">
                <div class="warning-icon">⚠️</div>
                <div class="warning-message">
                    <strong>글자 수 제한 도달</strong><br>
                    최대 10,000자까지만 입력할 수 있습니다.
                </div>
                <button class="warning-close" onclick="this.parentElement.parentElement.remove()">×</button>
            </div>
        `;
        
        // 스타일 적용
        warningDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #fff3cd;
            border: 2px solid #ffeaa7;
            border-radius: 8px;
            padding: 0;
            z-index: 10000;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            animation: slideInRight 0.3s ease-out;
            max-width: 350px;
            font-family: inherit;
        `;
        
        // 내부 콘텐츠 스타일
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            
            .max-length-warning .warning-content {
                display: flex;
                align-items: flex-start;
                padding: 16px;
                gap: 12px;
            }
            
            .max-length-warning .warning-icon {
                font-size: 24px;
                line-height: 1;
                flex-shrink: 0;
            }
            
            .max-length-warning .warning-message {
                flex: 1;
                font-size: 14px;
                line-height: 1.4;
                color: #856404;
            }
            
            .max-length-warning .warning-message strong {
                color: #533f03;
                font-weight: 600;
            }
            
            .max-length-warning .warning-close {
                background: none;
                border: none;
                font-size: 20px;
                font-weight: bold;
                color: #856404;
                cursor: pointer;
                padding: 0;
                width: 20px;
                height: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                line-height: 1;
                flex-shrink: 0;
            }
            
            .max-length-warning .warning-close:hover {
                color: #533f03;
                background: rgba(0, 0, 0, 0.1);
                border-radius: 50%;
            }
        `;
        
        if (!document.querySelector('style[data-max-length-warning]')) {
            style.setAttribute('data-max-length-warning', 'true');
            document.head.appendChild(style);
        }
        
        // 페이지에 추가
        document.body.appendChild(warningDiv);
        
        // 자동으로 5초 후 제거
        setTimeout(() => {
            if (warningDiv && warningDiv.parentElement) {
                warningDiv.style.animation = 'slideInRight 0.3s ease-in reverse';
                setTimeout(() => {
                    if (warningDiv && warningDiv.parentElement) {
                        warningDiv.remove();
                    }
                }, 300);
            }
        }, 5000);
        
        // 에디터에 포커스를 다시 맞춤 (사용자가 계속 작성할 수 있도록)
        setTimeout(() => {
            quill.focus();
        }, 100);
    }
    
    // 초기 문자 수 카운터 설정
    updateCharCounter(titleInput, titleCounter, 200);
    updateContentCharCounter(quill.getText().length);
    
    // 실시간 문자 수 업데이트 (제목만)
    titleInput.addEventListener('input', function() {
        updateCharCounter(this, titleCounter, 200);
    });
    
    // 폼 제출 처리
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const title = titleInput.value.trim();
        
        // 에디터 내용을 HTML로 가져오기
        const editorHtml = quill.root.innerHTML;
        const editorText = quill.getText().trim();
        
        // 히든 textarea에 HTML 내용 설정
        contentTextarea.value = editorHtml;
        
        // 유효성 검사
        if (!title) {
            alert('제목을 입력해주세요.');
            titleInput.focus();
            return;
        }
        
        if (title.length > 200) {
            alert('제목은 200자 이내로 입력해주세요.');
            titleInput.focus();
            return;
        }
        
        if (!editorText || editorText.length <= 1) {
            alert('내용을 입력해주세요.');
            quill.focus();
            return;
        }
        
        if (editorText.length > 10000) {
            alert('내용은 10,000자 이내로 입력해주세요.');
            quill.focus();
            return;
        }
        
        // 로딩 표시
        showLoading();
        
        // API 요청 준비
        const url = isEdit ? `/community/posts/${postId}` : '/community/posts';
        let fetchOptions = {};
        
        if (isEdit) {
            // 수정 시: PUT 메서드와 JSON 사용
            fetchOptions = {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    title: title,
                    content: editorHtml,
                    csrf_token: document.querySelector('input[name="csrf_token"]').value
                })
            };
        } else {
            // 생성 시: POST 메서드와 FormData 사용
            const formData = new FormData();
            formData.append('title', title);
            formData.append('content', editorHtml);
            formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
            
            fetchOptions = {
                method: 'POST',
                body: formData
            };
        }
        
        fetch(url, fetchOptions)
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.status === 'success') {
                // alert를 표시한 후 setTimeout을 사용하여 리다이렉트 보장
                alert(data.message);
                setTimeout(() => {
                    if (data.data && data.data.redirectUrl) {
                        window.location.href = data.data.redirectUrl;
                    } else {
                        window.location.href = '/community';
                    }
                }, 100); // 100ms 지연으로 alert 완료 후 리다이렉트
            } else {
                alert(data.message || '오류가 발생했습니다.');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            alert('네트워크 오류가 발생했습니다. 다시 시도해주세요.');
        });
    });
    
    // 🚨 페이지 로드 시 버전 확인 및 캐시 상태 출력 (v3.14.1+ FINAL)
    const currentTime = new Date().toISOString();
    console.log(`🕐 페이지 로드 시간: ${currentTime}`);
    console.log(`🔄 캐시 무효화 버전: v3.14.1-FINAL-${Date.now()}`);
    console.log('✅ 취소 버튼 confirm 시스템 v3.14.1+ 로드 완료');
    
    // 취소 버튼 처리 (최종 강화 버전 - v3.14.1-FINAL)
    const cancelBtn = document.getElementById('cancelBtn');
    if (cancelBtn) {
        // 모든 기존 이벤트 완전 제거
        const newCancelBtn = cancelBtn.cloneNode(true);
        cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);
        
        console.log('🔧 취소 버튼 이벤트 핸들러 완전 재생성 - v3.14.1-FINAL');
        
        // 최종 이벤트 리스너 등록
        newCancelBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('🚨 취소 버튼 클릭됨 - v3.14.1-FINAL ' + new Date().toLocaleTimeString());
            
            // 🔍 변수 상태 상세 디버깅
            console.log('🔍 변수 상태 확인:');
            console.log('  - titleInput:', titleInput);
            console.log('  - titleInput 존재:', !!titleInput);
            console.log('  - window.quill:', window.quill);
            console.log('  - quill 존재:', !!window.quill);
            
            // 직접 DOM에서 요소 찾기
            const titleElement = document.getElementById('title');
            const quillElement = document.querySelector('.ql-editor');
            console.log('  - titleElement:', titleElement);
            console.log('  - quillElement:', quillElement);
            
            // 작성된 내용이 있는지 확인 (더 안전한 방법)
            const titleValue = titleElement ? titleElement.value.trim() : '';
            const quillText = window.quill ? window.quill.getText().trim() : (quillElement ? quillElement.textContent.trim() : '');
            const hasContent = titleValue || quillText.length > 1;
            
            console.log(`📊 상세 내용 확인:`);
            console.log(`  - 제목 값: "${titleValue}"`);
            console.log(`  - 본문 텍스트: "${quillText}"`);
            console.log(`  - 본문 길이: ${quillText.length}`);
            console.log(`  - 내용 있음: ${hasContent}`);
            
            if (hasContent) {
                console.log('⚠️ 내용이 있어서 confirm 표시');
                const userConfirmed = confirm('작성 중인 내용이 사라집니다.\n정말로 취소하시겠습니까?');
                console.log(`👤 사용자 선택: ${userConfirmed}`);
                
                if (!userConfirmed) {
                    console.log('🚫 사용자가 취소를 선택함 - 페이지 유지');
                    return;
                }
                console.log('✅ 사용자가 확인 - 페이지 이동');
            } else {
                console.log('📝 내용 없음 - 즉시 이동');
            }
            
            // 🔥 beforeunload 이벤트 중복 방지 - 취소 버튼으로 이동할 때는 beforeunload 무시
            console.log('🔧 beforeunload 이벤트 일시적 무력화');
            isSubmitting = true;  // beforeunload 무시 플래그 설정
            
            console.log('✅ 커뮤니티 목록으로 이동');
            // 취소 확정 시 커뮤니티 목록으로 이동
            window.location.href = '/community';
        });
        
        console.log('✅ 취소 버튼 이벤트 핸들러 등록 완료 - v3.14.1+');
        
        // 추가 안전장치: 브라우저 캐시 문제 대응
        cancelBtn.setAttribute('data-version', 'v3.14.1-enhanced');
        cancelBtn.title = 'v3.14.1+ Enhanced Cancel Button - 작성된 내용이 있을 때만 확인';
        
    } else {
        console.error('❌ 취소 버튼을 찾을 수 없음');
    }

    // 삭제 버튼 처리 (수정 페이지에서만)
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            if (!confirm('정말로 이 게시글을 삭제하시겠습니까?\n삭제된 게시글은 복구할 수 없습니다.')) {
                return;
            }
            
            // 🔥 삭제 시 beforeunload 이벤트 무력화 - 삭제는 변경사항 저장과 다른 작업
            console.log('🗑️ 삭제 진행: beforeunload 이벤트 무력화');
            isSubmitting = true;  // beforeunload 무시 플래그 설정
            
            showLoading();
            
            fetch(`/community/posts/${postId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    csrf_token: document.querySelector('input[name="csrf_token"]').value
                })
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                
                if (data.status === 'success') {
                    alert(data.message);
                    setTimeout(() => {
                        window.location.href = data.data.redirectUrl || '/community';
                    }, 100); // 100ms 지연으로 alert 완료 후 리다이렉트
                } else {
                    alert(data.message || '삭제 중 오류가 발생했습니다.');
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error:', error);
                alert('네트워크 오류가 발생했습니다. 다시 시도해주세요.');
            });
        });
    }
    
    // 로딩 표시/숨김 함수
    function showLoading() {
        loadingOverlay.style.display = 'flex';
        submitBtn.disabled = true;
        if (deleteBtn) deleteBtn.disabled = true;
    }
    
    function hideLoading() {
        loadingOverlay.style.display = 'none';
        submitBtn.disabled = false;
        if (deleteBtn) deleteBtn.disabled = false;
    }
    
    // 페이지 이탈 시 경고 (내용이 입력된 경우)
    let isSubmitting = false;
    
    form.addEventListener('submit', function() {
        isSubmitting = true;
    });
    
    window.addEventListener('beforeunload', function(e) {
        if (!isSubmitting && (titleInput.value.trim() || quill.getText().trim().length > 1)) {
            const message = '작성 중인 내용이 있습니다. 페이지를 벗어나시겠습니까?';
            e.returnValue = message;
            return message;
        }
    });
    
    // 자동 저장 기능 (나중에 구현)
    // setInterval(function() {
    //     const title = titleInput.value.trim();
    //     const content = contentTextarea.value.trim();
    //     if (title || content) {
    //         // 임시 저장 로직
    //         console.log('자동 저장 중...');
    //     }
    // }, 30000); // 30초마다
});
</script>

<script>
</script>