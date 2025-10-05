<?php
/**
 * 공지사항 편집 페이지
 */

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

// 공지사항 데이터가 전달되었는지 확인
if (!isset($notice) || !$notice) {
    header('Location: /notices');
    exit;
}
?>

<style>
/* 공지사항 편집 페이지 스타일 */
.edit-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    min-height: calc(100vh - 200px);
}

.edit-header {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
    padding: 30px;
    text-align: center;
    margin-top: 60px;
    margin-bottom: 30px;
    border-radius: 12px;
}

.edit-header h1 {
    font-size: 2rem;
    margin-bottom: 10px;
    font-weight: 700;
}

.edit-header p {
    font-size: 1rem;
    opacity: 0.9;
    margin: 0;
}

.edit-form {
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
    color: #dc2626;
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
    border-color: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
}

.form-textarea {
    min-height: 300px;
    resize: vertical;
    font-family: inherit;
    line-height: 1.6;
}

/* Quill 에디터 커스터마이징 - 편집용 */
.ql-container {
    border-radius: 0 0 8px 8px;
    border: 2px solid #e2e8f0;
    border-top: none;
    min-height: 300px;
    font-family: inherit;
}

.ql-toolbar {
    border: 2px solid #e2e8f0;
    border-radius: 8px 8px 0 0;
    background: #f8fafc;
}

.ql-editor {
    line-height: 1.6;
    font-size: 16px;
    min-height: 270px;
}

.ql-editor::before {
    color: #a0aec0;
    font-style: normal;
}

.ql-editor:focus {
    outline: none;
    border-color: #f59e0b;
}


/* 이미지 업로드 영역 - 작성 페이지와 통일 */
.upload-area {
    border: 2px dashed #cbd5e0;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    background: #f7fafc;
    transition: all 0.3s ease;
    margin-top: 10px;
}

.upload-area:hover,
.upload-area.dragover {
    border-color: #f59e0b;
    background: #fffbeb;
}

.upload-input {
    display: none;
}

.upload-button {
    background: #f59e0b;
    color: white;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    display: inline-block;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
}

.upload-button:hover {
    background: #d97706;
}

/* 새로 업로드된 이미지 미리보기 */
.uploaded-images {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 15px;
}

/* 기존 이미지 표시 - 작성 페이지와 통일된 스타일 */
.existing-images {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 15px;
}

.existing-image-item {
    position: relative;
    width: 100px;
    height: 100px;
    border-radius: 8px;
    overflow: hidden;
    border: 2px solid #e2e8f0;
}

.existing-image-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-remove-btn {
    position: absolute;
    top: 4px;
    right: 4px;
    background: #dc2626;
    color: white;
    border: none;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    font-size: 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.image-remove-btn:hover {
    background: #b91c1c;
    transform: scale(1.1);
}

.upload-progress {
    margin-top: 20px;
    display: none;
}

.progress-bar {
    width: 100%;
    height: 20px;
    background: #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);
    width: 0%;
    transition: width 0.3s ease;
}

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
}

.btn {
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
}

.btn-primary:hover {
    box-shadow: 0 6px 16px rgba(245, 158, 11, 0.6);
    transform: translateY(-1px);
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
    transform: translateY(-1px);
}

.loading {
    opacity: 0.7;
    pointer-events: none;
}

.loading::after {
    content: '';
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid transparent;
    border-top: 2px solid currentColor;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-left: 8px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.error-message {
    background: #fee2e2;
    color: #dc2626;
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-left: 4px solid #dc2626;
}

.success-message {
    background: #d1fae5;
    color: #065f46;
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-left: 4px solid #10b981;
}

/* 모바일 반응형 */
@media (max-width: 768px) {
    .edit-container {
        padding: 15px;
    }
    
    .edit-header {
        margin-top: 20px;
        padding: 20px;
    }
    
    .edit-header h1 {
        font-size: 1.5rem;
    }
    
    .edit-form {
        padding: 20px;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        justify-content: center;
    }
    
    .existing-image-item img {
        width: 120px;
        height: 80px;
    }
}
</style>

<div class="edit-container">
    <!-- 헤더 -->
    <div class="edit-header">
        <h1>✏️ 공지사항 수정</h1>
        <p><?= htmlspecialchars($notice['title']) ?></p>
    </div>

    <!-- 에러/성공 메시지 영역 -->
    <div id="message-area"></div>

    <!-- 편집 폼 -->
    <form id="editNoticeForm" class="edit-form">
        <input type="hidden" name="notice_id" value="<?= $notice['id'] ?>">
        
        <!-- 제목 -->
        <div class="form-group">
            <label for="title" class="form-label required">제목</label>
            <input type="text" 
                   id="title" 
                   name="title" 
                   class="form-input" 
                   placeholder="공지사항 제목을 입력하세요"
                   value="<?= htmlspecialchars($notice['title']) ?>"
                   required
                   maxlength="200">
        </div>


        <!-- 내용 -->
        <div class="form-group">
            <label for="content" class="form-label required">내용</label>
            <div id="editor-container" style="min-height: 300px; background: white; border: 2px solid #e2e8f0; border-radius: 8px;">
                <!-- Quill 에디터가 여기에 생성됩니다 -->
            </div>
            <div id="imageCounter" class="char-counter" style="color: #2563eb; font-weight: 500; margin-top: 8px;">📷 이미지: 0 / 20</div>
            <textarea id="content" 
                      name="content" 
                      style="display: none;" 
                      required><?= htmlspecialchars($notice['content']) ?></textarea>
        </div>

        <!-- 기존 이미지 -->
        <?php if (!empty($notice['images'])): ?>
        <div class="form-group">
            <label class="form-label">기존 첨부 이미지</label>
            <div class="existing-images" id="existingImages">
                <?php foreach ($notice['images'] as $image): ?>
                <div class="existing-image-item" data-image-id="<?= $image['id'] ?>">
                    <img src="<?= htmlspecialchars($image['file_path']) ?>" alt="<?= htmlspecialchars($image['filename']) ?>">
                    <button type="button" class="image-remove-btn" onclick="removeExistingImage(<?= $image['id'] ?>)">✕</button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- 새 이미지 업로드 -->
        <div class="form-group">
            <label class="form-label">이미지 첨부 (선택사항)</label>
            <div class="upload-area" id="uploadArea">
                <div class="upload-button" onclick="document.getElementById('images').click()">
                    <i class="fas fa-plus"></i> 이미지 선택
                </div>
                <input type="file" 
                       id="images" 
                       name="images[]" 
                       class="upload-input" 
                       accept="image/*" 
                       multiple>
                <p style="font-size: 12px; color: #718096; margin: 10px 0 0 0;">
                    지원 형식: JPG, PNG, GIF | 최대 <?= UploadConfig::MAX_FILE_SIZE_MB ?>MB | 최대 5개
                </p>
            </div>
            
            <!-- 업로드 진행률 -->
            <div class="upload-progress" id="uploadProgress">
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
            </div>
            
            <!-- 새로 선택된 이미지 미리보기 -->
            <div class="uploaded-images" id="newUploadedImages"></div>
        </div>

        <!-- 버튼 -->
        <div class="form-actions">
            <a href="/notices/<?= $notice['id'] ?>" class="btn btn-secondary">
                ❌ 취소
            </a>
            <button type="submit" class="btn btn-primary" id="submitBtn">
                ✅ 수정하기
            </button>
        </div>
    </form>
</div>

<!-- Quill.js 라이브러리 -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

<!-- 🚀 v3.27.0: 공통 업로드 설정 -->
<?php include '/var/www/html/topmkt/src/views/includes/upload-config.js.php'; ?>

<script>
// 편집 폼 JavaScript
let quill; // Quill 에디터 인스턴스

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editNoticeForm');
    const fileInput = document.getElementById('images');
    const submitBtn = document.getElementById('submitBtn');
    const messageArea = document.getElementById('message-area');
    
    let removedImages = [];
    
    // 🚀 Ultra Think: Quill 에디터 초기화
    initializeQuillEditor();
    
    // 파일 선택 이벤트
    fileInput.addEventListener('change', handleFileSelection);
    
    // 드래그 앤 드롭 지원
    const uploadArea = document.getElementById('uploadArea');
    if (uploadArea) {
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            fileInput.files = e.dataTransfer.files;
            handleFileSelection();
        });
    }
    
    function handleFileSelection() {
        const files = fileInput.files;
        
        // 🚀 Ultra Think: 기존 이미지 + 새 이미지 = 총 5개 제한
        const existingImages = document.querySelectorAll('#existingImages .existing-image-item:not([style*="display: none"])');
        const existingImageCount = existingImages.length;
        const totalAfterUpload = existingImageCount + files.length;
        
        if (totalAfterUpload > 20) {
            Toast.error(`총 이미지 개수가 20개를 초과합니다. 현재 ${existingImageCount}개 + 추가 ${files.length}개 = ${totalAfterUpload}개`);
            return;
        }
        
        // 파일 크기 검증
        for (let file of files) {
            if (!validateFileSize(file.size)) {
                Toast.error(`파일 ${file.name}이 너무 큽니다. 최대 <?= UploadConfig::MAX_FILE_SIZE_MB ?>MB까지 가능합니다.`);
                return;
            }
        }
        
        // 새 이미지 미리보기 표시
        const previewArea = document.getElementById('newUploadedImages');
        previewArea.innerHTML = ''; // 기존 미리보기 초기화
        
        Array.from(files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const imageDiv = document.createElement('div');
                imageDiv.className = 'existing-image-item';
                imageDiv.innerHTML = `
                    <img src="${e.target.result}" alt="새 이미지 ${index + 1}">
                    <button type="button" class="image-remove-btn" onclick="removeNewImage(this, ${index})">✕</button>
                `;
                previewArea.appendChild(imageDiv);
            };
            reader.readAsDataURL(file);
        });
        
        if (files.length > 0) {
            Toast.success(`${files.length}개의 새 이미지가 선택되었습니다.`);
        }
    }
    
    // 새 이미지 제거
    window.removeNewImage = async function(button, index) {
        if (!(await Modal.confirm('선택한 이미지를 제거하시겠습니까?', { type: 'warning' }))) return;
        
        button.closest('.existing-image-item').remove();
        
        // 파일 입력 초기화 (복잡한 파일 배열 조작 대신)
        fileInput.value = '';
        document.getElementById('newUploadedImages').innerHTML = '';
        Toast.success('이미지가 제거되었습니다. 필요하면 다시 선택해주세요.');
    };
    
    // 기존 이미지 제거
    window.removeExistingImage = async function(imageId) {
        if (!(await Modal.confirm('정말 이 이미지를 제거하시겠습니까? 제거된 이미지는 복구할 수 없습니다.', { type: 'warning' }))) {
            return;
        }
        
        const imageItem = document.querySelector(`[data-image-id="${imageId}"]`);
        if (imageItem) {
            imageItem.style.display = 'none';
            removedImages.push(imageId);
            Toast.success('이미지가 제거 목록에 추가되었습니다. 수정하기를 클릭하면 완전히 제거됩니다.');
        }
    };
    
    // 폼 제출
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData();
        const titleValue = document.getElementById('title').value;
        let contentValue = quill ? quill.root.innerHTML : document.getElementById('content').value;
        
        // 빈 HTML 태그 정리 (빈 img, p 태그 등)
        contentValue = contentValue
            .replace(/<img(?![^>]*src\s*=\s*["'][^"']+["'])[^>]*>/g, '') // src 없는 빈 img 태그만 제거
            .replace(/<p><\/p>/g, '') // 빈 p 태그 제거
            .replace(/<p>\s*<\/p>/g, '') // 공백만 있는 p 태그 제거
            .replace(/\n\s*\n/g, '\n') // 연속된 줄바꿈 정리
            .trim();
        
        const isFeaturedValue = '0';
        
        // 🔍 디버깅: 전송될 데이터 확인
        console.log('📝 폼 제출 데이터:', {
            title: titleValue,
            titleLength: titleValue.trim().length,
            content: contentValue,
            contentLength: contentValue.trim().length,
            contentTextOnly: quill ? quill.getText().trim() : contentValue.trim(),
            is_featured: isFeaturedValue,
            removedImages: removedImages
        });
        
        // ✅ 클라이언트 사이드 검증
        const trimmedTitle = titleValue.trim();
        const trimmedContent = quill ? quill.getText().trim() : contentValue.trim();
        
        if (!trimmedTitle) {
            Toast.error('제목을 입력해주세요.');
            return;
        }
        
        if (!trimmedContent) {
            Toast.error('내용을 입력해주세요.');
            return;
        }
        
        if (trimmedTitle.length > 200) {
            Toast.error('제목은 200자를 초과할 수 없습니다.');
            return;
        }
        
        if (trimmedContent.length > 10000) {
            Toast.error(`내용은 10,000자를 초과할 수 없습니다. (현재: ${trimmedContent.length}자)`);
            return;
        }
        
        formData.append('notice_id', document.querySelector('[name="notice_id"]').value);
        formData.append('title', titleValue);
        formData.append('content', contentValue);
        formData.append('is_featured', isFeaturedValue);
        formData.append('removed_images', JSON.stringify(removedImages));
        
        // 새 이미지 파일들 추가
        for (let file of fileInput.files) {
            formData.append('new_images[]', file);
        }
        
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
        
        try {
            // PUT 요청은 FormData와 호환성 문제가 있어 POST로 변경
            formData.append('_method', 'PUT'); // Laravel 스타일 메서드 오버라이드

            // v3.42.0: ApiClient 사용 (FormData는 자동으로 multipart/form-data로 처리)
            const result = await ApiClient.post('/api/notices/<?= $notice['id'] ?>', formData, {
                headers: {}, // Content-Type 자동 설정을 위해 빈 객체 전달
                noLoading: true
            });

            if (result.success) {
                Toast.success('공지사항이 성공적으로 수정되었습니다.');
                setTimeout(() => {
                    window.location.href = '/notices/' + result.notice_id;
                }, 1500);
            } else {
                Toast.error(result.message || '수정 중 오류가 발생했습니다.');
            }
        } catch (error) {
            console.error('수정 오류:', error);
            Toast.error('네트워크 오류가 발생했습니다.');
        } finally {
            submitBtn.classList.remove('loading');
            submitBtn.disabled = false;
        }
    });

    // 🚀 v3.30.0: showMessage 함수 제거 (Toast 클래스로 대체됨)
});

// 🚀 Ultra Think v3.13.0: Quill 커스텀 이미지 핸들러 (MediaController 연동) + 20개 제한
function quillImageHandler() {
    console.log('📷 이미지 업로드 버튼 클릭됨 (notices/edit.php)');
    
    // 현재 이미지 개수 확인 (20개 제한)
    const currentImages = quill.container.querySelectorAll('img').length;
    if (currentImages >= 20) {
        Toast.error(`최대 20개의 이미지만 업로드할 수 있습니다. (현재: ${currentImages}개)`);
        return;
    }
    
    const input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('accept', 'image/*');
    input.addEventListener('change', function() {
        const file = input.files[0];
        if (!file) return;
        
        // 🚀 v3.45.0: UploadConfig 시스템 사용
        if (!window.validateFileSize(file.size)) {
            Toast.error(window.getFileSizeErrorMessage());
            return;
        }

        // 파일 형식 검증
        if (!window.validateImageExtension(file.name)) {
            Toast.warning('허용되지 않는 파일 형식입니다. (jpg, jpeg, png, gif, webp만 가능)');
            return;
        }
        
        // 재차 이미지 개수 확인 (업로드 직전)
        const currentImages = quill.container.querySelectorAll('img').length;
        if (currentImages >= 20) {
            Toast.error(`최대 20개의 이미지만 업로드할 수 있습니다. (현재: ${currentImages}개)`);
            return;
        }
        
        // 업로드 진행
        uploadImageToQuill(file);
    });
    input.click();
}

// Quill 에디터 이미지 업로드 함수
function uploadImageToQuill(file) {
    const formData = new FormData();
    formData.append('image', file);
    formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
    formData.append('upload_type', 'notices');
    formData.append('is_quill_upload', 'true'); // 🚀 Ultra Think v3.13.0: Quill 업로드 구분
    
    // 로딩 상태 표시
    const range = quill.getSelection(true);
    quill.insertText(range.index, '이미지 업로드 중...', 'italic', true);

    // v3.42.0: ApiClient 사용 (FormData는 자동으로 multipart/form-data로 처리)
    ApiClient.post('/api/media/upload-image', formData, {
        headers: {}, // Content-Type 자동 설정을 위해 빈 객체 전달
        noLoading: true
    })
    .then(data => {
        // 로딩 텍스트 제거
        quill.deleteText(range.index, '이미지 업로드 중...'.length);

        if (data.success) {
            // 성공시 이미지 삽입
            quill.insertEmbed(range.index, 'image', data.data.url, 'user');
            quill.setSelection(range.index + 1, 0);  // 커서를 이미지 다음으로 이동
            
            // 이미지 카운터 업데이트
            updateImageCounter();
            
            console.log('✅ Quill 이미지 업로드 성공:', data.data.url);
        } else {
            Toast.error('이미지 업로드 실패: ' + data.message);
            console.error('❌ Quill 이미지 업로드 실패:', data.message);
        }
    })
    .catch(error => {
        // 로딩 텍스트 제거
        quill.deleteText(range.index, '이미지 업로드 중...'.length);
        
        Toast.error('이미지 업로드 중 오류가 발생했습니다.');
        console.error('❌ Quill 이미지 업로드 오류:', error);
    });
}

// 🚀 Ultra Think: Quill 에디터 초기화 함수
function initializeQuillEditor() {
    console.log('📝 Quill 에디터 초기화 중...');
    
    const toolbarOptions = [
        [{ 'header': [1, 2, 3, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        [{ 'color': [] }, { 'background': [] }],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        [{ 'align': [] }],
        ['link', 'image'],
        ['clean']
    ];

    quill = new Quill('#editor-container', {
        modules: {
            toolbar: {
                container: toolbarOptions,
                handlers: {
                    image: quillImageHandler  // 🚀 Ultra Think v3.13.0: 커스텀 이미지 핸들러 추가
                }
            }
        },
        theme: 'snow',
        placeholder: '공지사항 내용을 입력하세요...'
    });

    // 🚀 기존 내용을 Quill 에디터에 로드
    const existingContent = document.getElementById('content').value;
    if (existingContent) {
        // HTML 디코딩 후 Quill에 설정
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = existingContent;
        quill.root.innerHTML = tempDiv.innerHTML;
        
        console.log('✅ 기존 내용 로드 완료:', tempDiv.textContent.substring(0, 50) + '...');
    }

    // 이미지 카운터 업데이트 함수
    function updateImageCounter() {
        const imageCounter = document.getElementById('imageCounter');
        if (imageCounter && quill) {
            const currentImages = quill.container.querySelectorAll('img').length;
            
            // 카운터 텍스트 업데이트
            imageCounter.innerHTML = `📷 이미지: ${currentImages} / 20`;
            
            // 카운터 색상 변경 (경고 표시)
            if (currentImages >= 18) {
                imageCounter.style.color = '#dc2626'; // 빨간색 (위험)
                imageCounter.style.fontWeight = '700';
            } else if (currentImages >= 15) {
                imageCounter.style.color = '#ea580c'; // 오렌지색 (주의)
                imageCounter.style.fontWeight = '600';
            } else {
                imageCounter.style.color = '#2563eb'; // 파란색 (정상)
                imageCounter.style.fontWeight = '500';
            }
            
            console.log(`📷 이미지 카운터 업데이트: ${currentImages}/20`);
        }
    }

    // 에디터 내용 변경시 히든 필드 업데이트 + 이미지 제한 모니터링
    quill.on('text-change', function() {
        document.getElementById('content').value = quill.root.innerHTML;
        
        // 이미지 개수 확인 및 초과분 제거
        const currentImages = quill.container.querySelectorAll('img').length;
        if (currentImages > 20) {
            console.log(`⚠️ 이미지 개수 초과: ${currentImages}개 → 20개로 제한`);
            const images = quill.container.querySelectorAll('img');
            for (let i = 20; i < images.length; i++) {
                images[i].remove();
            }
            Toast.error('최대 20개의 이미지만 허용됩니다. 초과된 이미지가 제거되었습니다.');
        }
        
        // 이미지 카운터 업데이트 (약간의 지연을 두어 DOM 변경 완료 후 실행)
        setTimeout(updateImageCounter, 100);
    });

    // 초기 이미지 카운터 업데이트
    setTimeout(updateImageCounter, 500);

    console.log('✅ Quill 에디터 초기화 완료 (이미지 제한 시스템 포함)');
}

// 🚀 v3.27.0: 공통 업로드 설정 사용 (upload-config.js.php에서 제공)
// - window.validateFileSize()
// - window.formatFileSize()
// - window.getFileSizeErrorMessage()
// - window.validateImageExtension()

console.log('✅ 공통 업로드 설정 로드 완료 - 최대 파일 크기: ' + window.TOPMKT_UPLOAD_CONFIG.maxFileSizeMB + 'MB');
</script>