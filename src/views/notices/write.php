<?php
/**
 * 공지사항 작성 페이지
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

// 기업 사용자 권한 체크 (이미 컨트롤러에서 처리되지만 추가 안전장치)
if (!$canWrite) {
    header('Location: /notices');
    exit;
}

$isEdit = isset($action) && $action === 'edit';
$pageTitle = $isEdit ? '공지사항 수정' : '새 공지사항 작성';
$submitText = $isEdit ? '수정하기' : '작성하기';
?>

<style>
/* 공지사항 작성 페이지 스타일 */
.write-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    min-height: calc(100vh - 200px);
}

.write-header {
    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
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
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.form-select {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 16px;
    background: white;
    cursor: pointer;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    box-sizing: border-box;
}

.form-select:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.form-checkbox-wrapper {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px;
    background: #fef3c7;
    border-radius: 8px;
    border: 1px solid #f59e0b;
}

.form-checkbox {
    width: 20px;
    height: 20px;
    cursor: pointer;
}

.form-checkbox-label {
    cursor: pointer;
    font-weight: 600;
    color: #92400e;
    margin: 0;
}

.char-counter {
    font-size: 12px;
    color: #718096;
    text-align: right;
    margin-top: 5px;
}

.char-counter.warning {
    color: #d97706;
}

.char-counter.error {
    color: #dc2626;
}

.form-buttons {
    display: flex;
    gap: 12px;
    justify-content: center;
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
    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
    color: white;
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.4);
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
    background: #dc2626;
    color: white;
}

.btn-danger:hover {
    background: #b91c1c;
}

.form-tips {
    background: #f0f9ff;
    border: 1px solid #0ea5e9;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
}

.form-tips h4 {
    color: #0c4a6e;
    margin: 0 0 10px 0;
    font-size: 14px;
}

.form-tips ul {
    margin: 0;
    padding-left: 20px;
    color: #0369a1;
    font-size: 13px;
}

.form-tips li {
    margin-bottom: 4px;
}

/* Quill 에디터 커스터마이징 */
.ql-container {
    border-radius: 0 0 8px 8px;
    border: 2px solid #e2e8f0;
    border-top: none;
    min-height: 350px;
    font-family: inherit;
}

.ql-toolbar {
    border: 2px solid #e2e8f0;
    border-bottom: none;
    border-radius: 8px 8px 0 0;
}

.ql-editor {
    line-height: 1.6;
    font-size: 16px;
    min-height: 320px;
}

.ql-editor::before {
    color: #a0aec0;
    font-style: normal;
}

.ql-editor:focus {
    outline: none;
}

/* 이미지 업로드 영역 */
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
    border-color: #2563eb;
    background: #f0f9ff;
}

.upload-input {
    display: none;
}

.upload-button {
    background: #2563eb;
    color: white;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    display: inline-block;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.upload-button:hover {
    background: #1d4ed8;
}

.upload-progress {
    display: none;
    margin-top: 10px;
}

.progress-bar {
    background: #e5e7eb;
    height: 8px;
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill {
    background: linear-gradient(90deg, #2563eb, #1e40af);
    height: 100%;
    width: 0%;
    transition: width 0.3s ease;
}

.uploaded-images {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 15px;
}

.uploaded-image {
    position: relative;
    width: 100px;
    height: 100px;
    border-radius: 8px;
    overflow: hidden;
    border: 2px solid #e2e8f0;
}

.uploaded-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.remove-image {
    position: absolute;
    top: 4px;
    right: 4px;
    background: #dc2626;
    color: white;
    border: none;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    cursor: pointer;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.remove-image:hover {
    background: #b91c1c;
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
        font-size: 1.6rem;
    }
    
    .write-form {
        padding: 20px;
    }
    
    .form-buttons {
        flex-direction: column;
        align-items: stretch;
    }
    
    .btn {
        min-width: auto;
        width: 100%;
    }
    
    .ql-toolbar {
        border-radius: 8px 8px 0 0;
    }
    
    .ql-container {
        min-height: 300px;
    }
    
    .ql-editor {
        min-height: 270px;
        font-size: 14px;
    }
    
    .uploaded-images {
        justify-content: center;
    }
}
</style>

<div class="write-container">
    <!-- 헤더 컴포넌트 -->
    <?php include_once SRC_PATH . '/components/NoticeWriteHeader.php'; ?>

    <!-- 작성 안내 팁 -->
    <div class="form-tips">
        <h4>💡 공지사항 작성 가이드</h4>
        <ul>
            <li>명확하고 간결한 제목을 작성해주세요</li>
            <li>이미지는 최대 5개까지 첨부 가능합니다</li>
            <li>이미지 업로드 시 <?= number_format(UploadConfig::MAX_FILE_SIZE / (1024 * 1024)) ?>MB 이하의 파일만 가능합니다</li>
            <li>HTML 태그는 자동으로 정리되어 안전하게 저장됩니다</li>
        </ul>
    </div>
    
    <!-- 작성 폼 -->
    <form class="write-form" id="noticeForm" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <?php if ($isEdit && isset($notice)): ?>
            <input type="hidden" name="notice_id" value="<?= htmlspecialchars($notice['id']) ?>">
        <?php endif; ?>
        
        <!-- 제목 -->
        <div class="form-group">
            <label for="title" class="form-label required">제목</label>
            <input type="text" 
                   id="title" 
                   name="title" 
                   class="form-input"
                   placeholder="공지사항 제목을 입력하세요..."
                   value="<?= $isEdit ? htmlspecialchars($notice['title'] ?? '') : '' ?>"
                   maxlength="200"
                   required>
            <div class="char-counter" id="titleCounter">0 / 200</div>
        </div>
        
        
        <!-- 내용 -->
        <div class="form-group">
            <label for="content" class="form-label required">내용</label>
            <div id="editor-container">
                <!-- Quill 에디터가 여기에 생성됩니다 -->
            </div>
            <textarea id="content" 
                      name="content" 
                      style="display: none;" 
                      required><?= $isEdit ? htmlspecialchars($notice['content'] ?? '') : '' ?></textarea>
            <div id="imageCounter" class="char-counter" style="color: #2563eb; font-weight: 500;">📷 이미지: 0 / 20</div>
        </div>
        
        <!-- 이미지 업로드 -->
        <div class="form-group">
            <label class="form-label">이미지 첨부 (선택사항)</label>
            <div class="upload-area" id="uploadArea">
                <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: #cbd5e0; margin-bottom: 10px;"></i>
                <p>이미지를 드래그하여 올리거나 클릭하여 선택하세요</p>
                <div class="upload-button" onclick="document.getElementById('imageInput').click()">
                    <i class="fas fa-plus"></i> 이미지 선택
                </div>
                <input type="file" 
                       id="imageInput" 
                       name="images[]" 
                       class="upload-input"
                       accept="image/*" 
                       multiple>
                <p style="font-size: 12px; color: #718096; margin: 10px 0 0 0;">
                    지원 형식: JPG, PNG, GIF | 최대 <?= number_format(UploadConfig::MAX_FILE_SIZE / (1024 * 1024)) ?>MB | 최대 5개
                </p>
            </div>
            
            <!-- 업로드 진행률 -->
            <div class="upload-progress" id="uploadProgress">
                <div style="font-size: 14px; color: #4a5568; margin-bottom: 5px;">
                    업로드 중... <span id="progressPercent">0%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
            </div>
            
            <!-- 업로드된 이미지 미리보기 -->
            <div class="uploaded-images" id="uploadedImages">
                <?php if ($isEdit && !empty($notice['images'])): ?>
                    <?php foreach ($notice['images'] as $image): ?>
                        <div class="uploaded-image" data-image-id="<?= htmlspecialchars($image['id']) ?>">
                            <img src="<?= htmlspecialchars($image['file_path']) ?>" alt="첨부 이미지">
                            <button type="button" class="remove-image" onclick="removeImage(this, <?= htmlspecialchars($image['id']) ?>)">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- 버튼 그룹 -->
        <div class="form-buttons">
            <button type="submit" class="btn btn-primary" id="submitBtn">
                <i class="fas fa-save"></i> <?= $submitText ?>
            </button>
            <a href="/notices" class="btn btn-secondary" onclick="isFormSubmitted = true;">
                <i class="fas fa-times"></i> 취소
            </a>
            <?php if ($isEdit): ?>
                <button type="button" class="btn btn-danger" onclick="deleteNotice(<?= $notice['id'] ?>)">
                    <i class="fas fa-trash"></i> 삭제
                </button>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Quill.js 라이브러리 -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

<!-- 🚀 v3.27.0: 공통 업로드 설정 -->
<?php include '/var/www/html/topmkt/src/views/includes/upload-config.js.php'; ?>

<script>
// 공지사항 작성 페이지 JavaScript
let quill;
let uploadedFiles = [];
const maxImages = 20;
const maxFileSize = <?= UploadConfig::MAX_FILE_SIZE ?>;
let isFormSubmitted = false; // 폼 제출 상태 추적

document.addEventListener('DOMContentLoaded', function() {
    console.log('📢 공지사항 작성 페이지 초기화');
    
    // Quill 에디터 초기화
    initializeEditor();
    
    // 폼 이벤트 리스너 설정
    setupFormEvents();
    
    // 이미지 업로드 기능 설정
    setupImageUpload();
    
    // 기존 이미지가 있는 경우 카운트 업데이트
    updateImageCount();
});

// 🚀 Ultra Think v3.13.0: Quill 커스텀 이미지 핸들러 (MediaController 연동)
function quillImageHandler() {
    const input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('accept', 'image/*');
    input.addEventListener('change', function() {
        const file = input.files[0];
        if (!file) return;

        // 🚀 v3.27.0: 공통 업로드 설정 사용
        // 파일 크기 검증
        if (!window.validateFileSize(file.size)) {
            Toast.error(window.getFileSizeErrorMessage());
            return;
        }

        // 파일 확장자 검증
        if (!window.validateImageExtension(file.name)) {
            Toast.info('JPG, PNG, GIF, WebP 파일만 업로드 가능합니다.');
            return;
        }
        
        // 이미지 개수 제한 검사 (20개)
        const currentImages = document.getElementById('editor-container').querySelectorAll('img').length;
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
    
    fetch('/api/media/upload-image', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // 로딩 텍스트 제거
        quill.deleteText(range.index, '이미지 업로드 중...'.length);
        
        if (data.success) {
            // 성공시 이미지 삽입
            quill.insertEmbed(range.index, 'image', data.data.url, 'user');
            quill.setSelection(range.index + 1, 0);  // 커서를 이미지 다음으로 이동
            
            console.log('✅ Quill 이미지 업로드 성공:', data.data.url);
            
            // 이미지 카운터 업데이트
            updateImageCounter();
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

// 이미지 개수 카운터 업데이트 함수
function updateImageCounter() {
    const imageCounter = document.getElementById('imageCounter');
    if (!imageCounter) return;
    
    const currentImages = document.getElementById('editor-container').querySelectorAll('img').length;
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
function initializeEditor() {
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
        placeholder: '공지사항 내용을 입력하세요...\n\n• 중요한 정보는 굵게 표시하세요\n• 필요한 경우 이미지를 첨부하세요\n• 독자가 이해하기 쉽게 작성해주세요'
    });

    // 기존 내용이 있는 경우 에디터에 설정
    <?php if ($isEdit && !empty($notice['content'])): ?>
        quill.root.innerHTML = <?= json_encode($notice['content']) ?>;
    <?php endif; ?>

    // 에디터 내용 변경시 히든 필드 업데이트
    quill.on('text-change', function() {
        document.getElementById('content').value = quill.root.innerHTML;
        validateForm();
        // 이미지 개수 확인 및 제한
        const currentImages = document.getElementById('editor-container').querySelectorAll('img').length;
        
        // 20개 초과 시 초과분 제거
        if (currentImages > 20) {
            console.log(`⚠️ 이미지 개수 초과: ${currentImages}개 → 20개로 제한`);
            const images = document.getElementById('editor-container').querySelectorAll('img');
            for (let i = 20; i < images.length; i++) {
                images[i].remove();
            }
            Toast.error('최대 20개의 이미지만 허용됩니다. 초과된 이미지가 제거되었습니다.');
        }
        
        // 이미지 카운터 업데이트 (약간의 지연으로 DOM 업데이트 후 실행)
        setTimeout(updateImageCounter, 100);
    });

    // 초기 이미지 카운터 설정
    setTimeout(updateImageCounter, 500);

    console.log('✅ Quill 에디터 초기화 완료');
}

// 폼 이벤트 설정
function setupFormEvents() {
    const form = document.getElementById('noticeForm');
    const titleInput = document.getElementById('title');
    const submitBtn = document.getElementById('submitBtn');
    
    // 🚀 v3.29.0: 제목 글자수 카운터 (통합 CharacterCounter 클래스 사용)
    new CharacterCounter(
        titleInput,
        document.getElementById('titleCounter'),
        200,
        { warningThreshold: 0.8, errorThreshold: 0.9 }
    );

    // 제목 입력 이벤트 (유효성 검사)
    titleInput.addEventListener('input', validateForm);
    
    // 폼 제출 이벤트
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        submitForm();
    });
    
    // 초기 폼 유효성 검사
    validateForm();
    
    console.log('✅ 폼 이벤트 설정 완료');
}

// 이미지 업로드 기능 설정
function setupImageUpload() {
    const uploadArea = document.getElementById('uploadArea');
    const imageInput = document.getElementById('imageInput');
    
    // 드래그 앤 드롭 이벤트
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });
    
    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
    });
    
    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        
        const files = Array.from(e.dataTransfer.files).filter(file => 
            file.type.startsWith('image/')
        );
        
        handleImageFiles(files);
    });
    
    // 파일 선택 이벤트
    imageInput.addEventListener('change', function(e) {
        handleImageFiles(Array.from(e.target.files));
    });
    
    console.log('✅ 이미지 업로드 기능 설정 완료');
}

// 이미지 파일 처리
function handleImageFiles(files) {
    const currentImageCount = document.querySelectorAll('.uploaded-image').length;

    if (currentImageCount + files.length > maxImages) {
        Toast.error(`최대 ${maxImages}개의 이미지만 업로드할 수 있습니다.`);
        return;
    }

    files.forEach(file => {
        // 🚀 v3.27.0: 공통 업로드 설정 사용
        // 파일 크기 검증
        if (!window.validateFileSize(file.size)) {
            Toast.error(`${file.name}: ${window.getFileSizeErrorMessage()}`);
            return;
        }

        // 파일 확장자 검증
        if (!window.validateImageExtension(file.name)) {
            Toast.error(`${file.name}: JPG, PNG, GIF, WebP 파일만 업로드 가능합니다.`);
            return;
        }

        uploadImage(file);
    });
}

// 이미지 업로드
function uploadImage(file) {
    const formData = new FormData();
    formData.append('image', file);
    formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
    formData.append('upload_type', 'notices'); // 공지사항 업로드 타입 명시
    
    const progressEl = document.getElementById('uploadProgress');
    const progressFill = document.getElementById('progressFill');
    const progressPercent = document.getElementById('progressPercent');
    
    progressEl.style.display = 'block';
    
    fetch('/api/media/upload-image', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        progressEl.style.display = 'none';
        
        if (data.success) {
            // MediaController 응답 형식에 맞게 수정
            const imageUrl = data.data.url;
            const fileName = data.data.filename;
            const tempId = Date.now(); // 임시 ID 생성
            
            addImagePreview(imageUrl, tempId);
            uploadedFiles.push({
                id: tempId,
                path: imageUrl,
                filename: fileName
            });
            updateImageCount();
        } else {
            Toast.error('이미지 업로드 실패: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        progressEl.style.display = 'none';
        console.error('업로드 오류:', error);
        Toast.error('이미지 업로드 중 오류가 발생했습니다.');
    });
}

// 이미지 미리보기 추가
function addImagePreview(imagePath, imageId) {
    const uploadedImages = document.getElementById('uploadedImages');
    
    const imageDiv = document.createElement('div');
    imageDiv.className = 'uploaded-image';
    imageDiv.setAttribute('data-image-id', imageId);
    
    imageDiv.innerHTML = `
        <img src="${imagePath}" alt="첨부 이미지" loading="lazy">
        <button type="button" class="remove-image" onclick="removeImage(this, ${imageId})">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    uploadedImages.appendChild(imageDiv);
}

// 이미지 제거
async function removeImage(button, imageId) {
    if (!(await Modal.confirm('이미지를 제거하시겠습니까?', { type: 'warning' }))) {
        return;
    }
    
    // DOM에서 제거
    const imageDiv = button.closest('.uploaded-image');
    imageDiv.remove();
    
    // 업로드된 파일 목록에서 제거
    uploadedFiles = uploadedFiles.filter(file => file.id !== imageId);
    
    // 이미지 개수 업데이트
    updateImageCount();
    
    // 참고: 서버에서 임시 파일 자동 정리됨 (DELETE 엔드포인트 없음)
    console.log('이미지 제거됨:', imageId);
}

// 이미지 개수 업데이트
function updateImageCount() {
    const imageCount = document.querySelectorAll('.uploaded-image').length;
    const uploadArea = document.getElementById('uploadArea');
    
    if (imageCount >= maxImages) {
        uploadArea.style.opacity = '0.5';
        uploadArea.style.pointerEvents = 'none';
    } else {
        uploadArea.style.opacity = '1';
        uploadArea.style.pointerEvents = 'auto';
    }
}

// 🚀 v3.29.0: 글자수 카운터 (통합 CharacterCounter 클래스 사용 - 아래에서 초기화)

// 폼 유효성 검사
function validateForm() {
    const title = document.getElementById('title').value.trim();
    const content = quill.getText().trim();
    const submitBtn = document.getElementById('submitBtn');
    
    const isValid = title.length >= 2 && 
                   title.length <= 200 && 
                   content.length >= 3 && 
                   content.length <= 10000;
    
    console.log('🔍 폼 검증:', { title: title.length, content: content.length, isValid });
    
    submitBtn.disabled = !isValid;
    
    return isValid;
}

// 폼 제출
function submitForm() {
    if (!validateForm()) {
        Toast.error('입력 내용을 확인해주세요.');
        return;
    }
    
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    
    // 버튼 비활성화 및 로딩 표시
    // 🚀 v3.31.0: Loading 클래스 사용
    Loading.button(submitBtn, true, { text: '저장 중...' });
    
    // 에디터 내용을 히든 필드에 설정
    document.getElementById('content').value = quill.root.innerHTML;
    
    // 폼 데이터 준비
    const formData = new FormData(document.getElementById('noticeForm'));
    
    // 업로드된 이미지 경로들 추가
    uploadedFiles.forEach(file => {
        formData.append('image_paths[]', file.path);
    });
    
    // AJAX로 폼 제출
    const url = <?= $isEdit ? "'/api/notices/' + " . ($notice['id'] ?? 'null') : "'/api/notices'" ?>;
    const method = <?= $isEdit ? "'PUT'" : "'POST'" ?>;
    
    console.log('🚀 폼 제출 정보:', { url, method, isEdit: <?= $isEdit ? 'true' : 'false' ?> });
    
    fetch(url, {
        method: method,
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 폼 제출 성공 상태로 설정
            isFormSubmitted = true;
            
            Toast.success(<?= $isEdit ? '공지사항이 성공적으로 수정되었습니다.' : '공지사항이 성공적으로 작성되었습니다.' ?>);
            window.location.href = '/notices/' + data.data.id;
        } else {
            throw new Error(data.message || '저장 중 오류가 발생했습니다.');
        }
    })
    .catch(error => {
        console.error('제출 오류:', error);
        Toast.error('저장 중 오류가 발생했습니다: ' + error.message);
    })
    .finally(() => {
        // 버튼 복원
        Loading.button(submitBtn, false);
    });
}

// 공지사항 삭제
async function deleteNotice(noticeId) {
    if (!(await Modal.confirm('정말로 이 공지사항을 삭제하시겠습니까?\n삭제된 공지사항은 복구할 수 없습니다.', { type: 'warning' }))) {
        return;
    }
    
    // 삭제 시도 시 폼 제출 상태로 설정 (beforeunload 방지)
    isFormSubmitted = true;
    
    fetch(`/api/notices/${noticeId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            csrf_token: document.querySelector('input[name="csrf_token"]').value
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Toast.success('공지사항이 삭제되었습니다.');
            window.location.href = '/notices';
        } else {
            throw new Error(data.message || '삭제 중 오류가 발생했습니다.');
        }
    })
    .catch(error => {
        console.error('삭제 오류:', error);
        Toast.error('삭제 중 오류가 발생했습니다: ' + error.message);
    });
}

// 페이지 떠나기 전 확인
window.addEventListener('beforeunload', function(e) {
    // 폼이 성공적으로 제출된 경우 경고 표시하지 않음
    if (isFormSubmitted) {
        return;
    }
    
    const title = document.getElementById('title').value.trim();
    const content = quill ? quill.getText().trim() : '';
    
    // 제목이나 내용이 입력된 경우에만 경고 표시
    if (title || content) {
        e.preventDefault();
        e.returnValue = '변경사항이 저장되지 않을 수 있습니다. 정말로 페이지를 떠나시겠습니까?';
        return '변경사항이 저장되지 않을 수 있습니다. 정말로 페이지를 떠나시겠습니까?';
    }
});

// 🚀 Ultra Think: beforeunload confirm 취소 시 로딩 UI 정리
window.addEventListener('focus', function() {
    // 사용자가 confirm에서 취소하고 페이지로 돌아온 경우 로딩 UI 숨김
    if (window.topMarketingLoader && window.topMarketingLoader.isLoading) {
        console.log('🔄 페이지 포커스 복구 - 로딩 UI 정리');
        window.topMarketingLoader.hide();
    }
});

// 🚀 Ultra Think: 페이지 가시성 변경 시에도 로딩 UI 정리
document.addEventListener('visibilitychange', function() {
    if (!document.hidden && window.topMarketingLoader && window.topMarketingLoader.isLoading) {
        console.log('🔄 페이지 가시성 복구 - 로딩 UI 정리');
        window.topMarketingLoader.hide();
    }
});
</script>