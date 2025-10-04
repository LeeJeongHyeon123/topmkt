<?php
/**
 * 행사 수정 페이지
 */

// 로그인 상태 확인
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
$isLoggedIn = AuthMiddleware::isLoggedIn();
$currentUserId = AuthMiddleware::getCurrentUserId();

// 수정 권한 확인 (컨트롤러에서 이미 확인했으므로 간소화)
$canEdit = true; // EventController에서 이미 권한과 날짜 체크를 완료함

// 디버깅을 위한 로그
if (class_exists('WebLogger')) {
    WebLogger::info('이벤트 수정 뷰 접근', [
        'event_id' => $event['id'] ?? 'N/A',
        'user_id' => $currentUserId,
        'is_logged_in' => $isLoggedIn
    ]);
}

// CSRF 토큰 생성
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!-- 한국어 인코딩 설정 -->
<meta charset="utf-8">

<!-- CSRF 토큰 메타 태그 -->
<meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?>">

<!-- Quill.js 에디터 CSS -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">

<style>
/* 행사 수정 페이지 스타일 */
.event-edit-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 30px 15px;
    min-height: calc(100vh - 200px);
    padding-top: 80px;
}

.page-header {
    text-align: center;
    margin-bottom: 40px;
}

.page-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 10px;
}

.page-subtitle {
    font-size: 1.1rem;
    color: #64748b;
    margin-bottom: 30px;
}

.form-section {
    background: white;
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
}

.form-label.required::after {
    content: ' *';
    color: #ef4444;
}

.form-input, .form-select, .form-textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s;
}

.form-input:focus, .form-select:focus, .form-textarea:focus {
    outline: none;
    border-color: #4A90E2;
    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
}

.form-textarea {
    min-height: 120px;
    resize: vertical;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.radio-group {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.radio-item {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    padding: 10px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    transition: all 0.3s;
}

.radio-item:hover {
    border-color: #4A90E2;
    background: #f8fafc;
}

.radio-item input[type="radio"] {
    margin: 0;
}

.radio-item input[type="radio"]:checked + .radio-label {
    color: #4A90E2;
    font-weight: 600;
}

.radio-item:has(input[type="radio"]:checked) {
    border-color: #4A90E2;
    background: #f0f9ff;
}

.instructor-section {
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    background: #f8fafc;
}

.instructor-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 15px;
}

.instructor-number {
    font-size: 1.2rem;
    font-weight: 600;
    color: #4A90E2;
}

.remove-instructor-btn {
    background: #ef4444;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: background 0.3s;
}

.remove-instructor-btn:hover {
    background: #dc2626;
}

.add-instructor-btn {
    background: #10b981;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 600;
    transition: background 0.3s;
    margin-bottom: 20px;
}

.add-instructor-btn:hover {
    background: #059669;
}

.instructor-image-container {
    width: 120px;
    height: 120px;
    border: 2px dashed #cbd5e0;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
    margin-bottom: 15px;
    background: white;
}

.instructor-image-container:hover {
    border-color: #4A90E2;
    background: #f0f9ff;
}

.instructor-image-placeholder {
    text-align: center;
    color: #64748b;
}

.instructor-image-preview {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 6px;
}

.image-upload-section {
    border: 2px dashed #cbd5e0;
    border-radius: 12px;
    padding: 40px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    margin-bottom: 20px;
}

.image-upload-section:hover {
    border-color: #4A90E2;
    background: #f0f9ff;
}

.image-upload-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}

.image-upload-icon {
    font-size: 3rem;
    color: #64748b;
}

.image-upload-text {
    font-size: 1.1rem;
    font-weight: 600;
    color: #374151;
}

.image-upload-subtext {
    font-size: 0.9rem;
    color: #64748b;
}

.image-preview-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 15px;
    margin-top: 20px;
}

.image-preview-item {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    border: 2px solid #e2e8f0;
}

.image-preview-item img {
    width: 100%;
    height: 120px;
    object-fit: cover;
}

.image-preview-remove {
    position: absolute;
    top: 5px;
    right: 5px;
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    cursor: pointer;
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.submit-section {
    text-align: center;
    margin-top: 40px;
}

.submit-btn {
    background: linear-gradient(135deg, #4A90E2 0%, #2E86AB 100%);
    color: white;
    border: none;
    padding: 16px 48px;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    margin-right: 20px;
}

.submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(74, 144, 226, 0.4);
}

.cancel-btn {
    background: #64748b;
    color: white;
    border: none;
    padding: 16px 48px;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-block;
}

.cancel-btn:hover {
    background: #475569;
}

.conditional-field {
    display: none;
}

.conditional-field.active {
    display: block;
}

/* 반응형 디자인 */
@media (max-width: 768px) {
    .event-edit-container {
        padding: 20px 10px;
        padding-top: 60px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .radio-group {
        flex-direction: column;
    }
    
    .image-preview-container {
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    }
    
    .submit-btn, .cancel-btn {
        width: 100%;
        margin: 10px 0;
    }
}
</style>

<div class="event-edit-container">
    <div class="page-header">
        <h1 class="page-title">행사 수정</h1>
        <p class="page-subtitle">행사 정보를 수정하세요</p>
    </div>

    <form id="eventEditForm" method="POST" enctype="multipart/form-data" action="/events/<?= $event['id'] ?>/update">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        
        <!-- 기본 정보 섹션 -->
        <div class="form-section">
            <h2 class="section-title">📋 기본 정보</h2>
            
            <div class="form-group">
                <label for="title" class="form-label required">행사 제목</label>
                <input type="text" id="title" name="title" class="form-input" value="<?= htmlspecialchars($event['title']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description" class="form-label required">행사 설명</label>
                <div id="description-editor" style="min-height: 200px;"></div>
                <div id="imageCounter" class="char-counter" style="color: #2563eb; font-weight: 500; margin-top: 8px;">📷 이미지: 0 / 20</div>
                <textarea id="description" name="description" class="form-textarea" style="display: none;" required><?= htmlspecialchars($event['description']) ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="category" class="form-label required">카테고리</label>
                <select id="category" name="category" class="form-select" required>
                    <option value="">카테고리를 선택하세요</option>
                    <option value="seminar" <?= $event['category'] == 'seminar' ? 'selected' : '' ?>>세미나</option>
                    <option value="workshop" <?= $event['category'] == 'workshop' ? 'selected' : '' ?>>워크샵</option>
                    <option value="conference" <?= $event['category'] == 'conference' ? 'selected' : '' ?>>컨퍼런스</option>
                    <option value="webinar" <?= $event['category'] == 'webinar' ? 'selected' : '' ?>>웨비나</option>
                    <option value="training" <?= $event['category'] == 'training' ? 'selected' : '' ?>>교육</option>
                </select>
            </div>
        </div>

        <!-- 일정 정보 섹션 -->
        <div class="form-section">
            <h2 class="section-title">📅 일정 정보</h2>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="start_date" class="form-label required">시작 날짜</label>
                    <input type="date" id="start_date" name="start_date" class="form-input" value="<?= $event['start_date'] ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="end_date" class="form-label">종료 날짜</label>
                    <input type="date" id="end_date" name="end_date" class="form-input" value="<?= $event['end_date'] ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="start_time" class="form-label required">시작 시간</label>
                    <input type="time" id="start_time" name="start_time" class="form-input" value="<?= $event['start_time'] ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="end_time" class="form-label">종료 시간</label>
                    <input type="time" id="end_time" name="end_time" class="form-input" value="<?= $event['end_time'] ?>">
                </div>
            </div>
        </div>

        <!-- 장소 정보 섹션 -->
        <div class="form-section">
            <h2 class="section-title">📍 장소 정보</h2>
            
            <div class="form-group">
                <label class="form-label required">행사 형태</label>
                <div class="radio-group">
                    <div class="radio-item">
                        <input type="radio" id="location_offline" name="location_type" value="offline" <?= $event['location_type'] == 'offline' ? 'checked' : '' ?>>
                        <label for="location_offline" class="radio-label">오프라인</label>
                    </div>
                    <div class="radio-item">
                        <input type="radio" id="location_online" name="location_type" value="online" <?= $event['location_type'] == 'online' ? 'checked' : '' ?>>
                        <label for="location_online" class="radio-label">온라인</label>
                    </div>
                    <div class="radio-item">
                        <input type="radio" id="location_hybrid" name="location_type" value="hybrid" <?= $event['location_type'] == 'hybrid' ? 'checked' : '' ?>>
                        <label for="location_hybrid" class="radio-label">하이브리드</label>
                    </div>
                </div>
            </div>
            
            <div id="offline-fields" class="conditional-field <?= in_array($event['location_type'], ['offline', 'hybrid']) ? 'active' : '' ?>">
                <div class="form-group">
                    <label for="venue_name" class="form-label">장소명</label>
                    <input type="text" id="venue_name" name="venue_name" class="form-input" value="<?= htmlspecialchars($event['venue_name']) ?>">
                </div>
                
                <div class="form-group">
                    <label for="venue_address" class="form-label">주소</label>
                    <input type="text" id="venue_address" name="venue_address" class="form-input" value="<?= htmlspecialchars($event['venue_address']) ?>">
                </div>
            </div>
            
            <div id="online-fields" class="conditional-field <?= in_array($event['location_type'], ['online', 'hybrid']) ? 'active' : '' ?>">
                <div class="form-group">
                    <label for="online_link" class="form-label">온라인 링크</label>
                    <input type="url" id="online_link" name="online_link" class="form-input" value="<?= htmlspecialchars($event['online_link']) ?>">
                </div>
            </div>
        </div>

        <!-- 참가 정보 섹션 -->
        <div class="form-section">
            <h2 class="section-title">👥 참가 정보</h2>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="max_participants" class="form-label">최대 참가자 수</label>
                    <input type="number" id="max_participants" name="max_participants" class="form-input" min="1" value="<?= $event['max_participants'] ?>">
                </div>
                
                <div class="form-group">
                    <label for="registration_fee" class="form-label">참가비 (원)</label>
                    <input type="number" id="registration_fee" name="registration_fee" class="form-input" min="0" step="1000" value="<?= $event['registration_fee'] ?>">
                </div>
            </div>
        </div>

        <!-- 강사 정보 섹션 -->
        <div class="form-section">
            <h2 class="section-title">👨‍🏫 강사 정보</h2>
            
            <div id="instructors-container">
                <?php if (!empty($instructors)): ?>
                    <?php foreach ($instructors as $index => $instructor): ?>
                        <div class="instructor-section" data-index="<?= $index ?>">
                            <div class="instructor-header">
                                <div class="instructor-number">강사 <?= $index + 1 ?></div>
                                <?php if ($index > 0): ?>
                                    <button type="button" class="remove-instructor-btn" onclick="removeInstructor(<?= $index ?>)">삭제</button>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <div class="instructor-image-container" onclick="document.getElementById('instructor_image_<?= $index ?>').click()">
                                    <?php if (!empty($instructor['image'])): ?>
                                        <img src="<?= htmlspecialchars($instructor['image']) ?>" alt="강사 이미지" class="instructor-image-preview">
                                    <?php else: ?>
                                        <div class="instructor-image-placeholder">
                                            <div style="font-size: 1.5rem; margin-bottom: 5px;">👤</div>
                                            <div>이미지 선택</div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <input type="file" id="instructor_image_<?= $index ?>" name="instructor_images[]" 
                                       accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" 
                                       style="display: none;" onchange="handleInstructorImage(<?= $index ?>, this)">
                            </div>
                            
                            <div class="form-group">
                                <label for="instructor_name_<?= $index ?>" class="form-label required">강사명</label>
                                <input type="text" id="instructor_name_<?= $index ?>" name="instructor_names[]" 
                                       class="form-input" value="<?= htmlspecialchars($instructor['name']) ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="instructor_info_<?= $index ?>" class="form-label">강사 소개</label>
                                <textarea id="instructor_info_<?= $index ?>" name="instructor_infos[]" 
                                          class="form-textarea" rows="3"><?= htmlspecialchars($instructor['info']) ?></textarea>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="instructor-section" data-index="0">
                        <div class="instructor-header">
                            <div class="instructor-number">강사 1</div>
                        </div>
                        
                        <div class="form-group">
                            <div class="instructor-image-container" onclick="document.getElementById('instructor_image_0').click()">
                                <div class="instructor-image-placeholder">
                                    <div style="font-size: 1.5rem; margin-bottom: 5px;">👤</div>
                                    <div>이미지 선택</div>
                                </div>
                            </div>
                            <input type="file" id="instructor_image_0" name="instructor_images[]" 
                                   accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" 
                                   style="display: none;" onchange="handleInstructorImage(0, this)">
                        </div>
                        
                        <div class="form-group">
                            <label for="instructor_name_0" class="form-label required">강사명</label>
                            <input type="text" id="instructor_name_0" name="instructor_names[]" 
                                   class="form-input" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="instructor_info_0" class="form-label">강사 소개</label>
                            <textarea id="instructor_info_0" name="instructor_infos[]" 
                                      class="form-textarea" rows="3"></textarea>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <button type="button" class="add-instructor-btn" onclick="addInstructor()">+ 강사 추가</button>
        </div>

        <!-- 행사 이미지 섹션 -->
        <div class="form-section">
            <h2 class="section-title">📸 행사 이미지</h2>
            
            <div class="image-upload-section" onclick="document.getElementById('event_images').click()">
                <div class="image-upload-content">
                    <div class="image-upload-icon">📷</div>
                    <div class="image-upload-text">이미지 업로드</div>
                    <div class="image-upload-subtext">여러 이미지를 선택할 수 있습니다 (JPG, PNG, GIF, WebP)</div>
                </div>
            </div>
            
            <input type="file" id="event_images" name="event_images[]" multiple 
                   accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" 
                   style="display: none;" onchange="handleEventImages(this)">
            
            <div id="event-images-preview" class="image-preview-container">
                <?php if (!empty($event_images)): ?>
                    <?php foreach ($event_images as $index => $image): ?>
                        <div class="image-preview-item" data-index="<?= $index ?>">
                            <img src="<?= htmlspecialchars($image['url']) ?>" alt="행사 이미지">
                            <button type="button" class="image-preview-remove" onclick="removeEventImage(<?= $index ?>)">×</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- 제출 섹션 -->
        <div class="submit-section">
            <button type="submit" class="submit-btn">행사 수정</button>
            <a href="/events/detail?id=<?= $event['id'] ?>" class="cancel-btn">취소</a>
        </div>
    </form>
</div>

<!-- Quill.js 에디터 JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

<!-- 🚀 v3.27.0: 공통 업로드 설정 -->
<?php include '/var/www/html/topmkt/src/views/includes/upload-config.js.php'; ?>

<script>
// Quill 에디터 초기화
const quill = new Quill('#description-editor', {
    theme: 'snow',
    placeholder: '행사에 대한 상세한 설명을 입력하세요...',
    modules: {
        toolbar: {
            container: [
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{'header': 1}, {'header': 2}],
                [{'list': 'ordered'}, {'list': 'bullet'}],
                [{'script': 'sub'}, {'script': 'super'}],
                [{'indent': '-1'}, {'indent': '+1'}],
                [{'direction': 'rtl'}],
                [{'size': ['small', false, 'large', 'huge']}],
                [{'header': [1, 2, 3, 4, 5, 6, false]}],
                [{'color': []}, {'background': []}],
                [{'font': []}],
                [{'align': []}],
                ['clean'],
                ['link', 'image', 'video']
            ],
            handlers: {
                'image': quillImageHandler
            }
        }
    }
});

// 커스텀 이미지 핸들러 함수 (20개 제한)
function quillImageHandler() {
    console.log('📷 이미지 업로드 버튼 클릭됨 (events/edit.php)');
    
    // 현재 이미지 개수 확인
    const currentImages = quill.container.querySelectorAll('img').length;
    if (currentImages >= 20) {
        alert(`최대 20개의 이미지만 업로드할 수 있습니다. (현재: ${currentImages}개)`);
        return;
    }
    
    const input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('accept', 'image/*');
    
    input.onchange = async () => {
        const file = input.files[0];
        if (!file) return;

        // 🚀 v3.27.0: 공통 업로드 설정 사용
        // 파일 크기 검증
        if (!window.validateFileSize(file.size)) {
            alert(window.getFileSizeErrorMessage());
            return;
        }

        // 파일 확장자 검증
        if (!window.validateImageExtension(file.name)) {
            alert('JPG, PNG, GIF, WebP 파일만 업로드 가능합니다.');
            return;
        }
        
        // 재차 이미지 개수 확인
        const currentImages = quill.container.querySelectorAll('img').length;
        if (currentImages >= 20) {
            alert(`최대 20개의 이미지만 업로드할 수 있습니다. (현재: ${currentImages}개)`);
            return;
        }
        
        const range = quill.getSelection();
        if (!range) {
            console.error('❌ Quill 에디터 선택 범위를 가져올 수 없습니다.');
            return;
        }
        
        // 업로드 중 표시
        console.log('📤 이미지 업로드 시작...');
        quill.insertText(range.index, '이미지 업로드 중...', 'italic', true);
        let loadingTextInserted = true;
        
        try {
            const formData = new FormData();
            formData.append('image', file);
            
            const response = await fetch('/upload-image', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
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
            console.error('❌ 이미지 업로드 오류:', error);
            
            // 업로드 중 텍스트 제거 (오류 발생 시)
            if (loadingTextInserted && range && typeof quill !== 'undefined' && quill) {
                try {
                    quill.deleteText(range.index, '이미지 업로드 중...'.length);
                } catch (deleteError) {
                    console.error('로딩 텍스트 제거 실패:', deleteError);
                }
            }
            
            alert('이미지 업로드 중 오류가 발생했습니다.\n상세: ' + error.message);
        }
    };
    
    input.click();
}

// 기존 내용 설정
quill.root.innerHTML = `<?= addslashes($event['description']) ?>`;

// 이미지 카운터 업데이트 함수
function updateImageCounter() {
    const imageCounter = document.getElementById('imageCounter');
    if (imageCounter && typeof quill !== 'undefined' && quill) {
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

// Quill 텍스트 변경 이벤트 모니터링 (20개 초과 시 자동 제거)
quill.on('text-change', function(delta, oldDelta, source) {
    const currentImages = quill.container.querySelectorAll('img').length;
    if (currentImages > 20) {
        console.log(`⚠️ 이미지 개수 초과: ${currentImages}개 → 20개로 제한`);
        const images = quill.container.querySelectorAll('img');
        for (let i = 20; i < images.length; i++) {
            images[i].remove();
        }
        alert('최대 20개의 이미지만 허용됩니다. 초과된 이미지가 제거되었습니다.');
    }
    // 이미지 카운터 업데이트 (약간의 지연을 두어 DOM 변경 완료 후 실행)
    setTimeout(updateImageCounter, 100);
});

// 초기 이미지 카운터 업데이트
setTimeout(updateImageCounter, 500);

// 폼 제출 시 에디터 내용을 hidden textarea에 복사
document.getElementById('eventEditForm').addEventListener('submit', function(e) {
    document.getElementById('description').value = quill.root.innerHTML;
});

// 강사 인덱스 추적
let instructorIndex = <?= count($instructors) ?>;

// 장소 타입 변경 처리
document.querySelectorAll('input[name="location_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const offlineFields = document.getElementById('offline-fields');
        const onlineFields = document.getElementById('online-fields');
        
        offlineFields.classList.remove('active');
        onlineFields.classList.remove('active');
        
        if (this.value === 'offline') {
            offlineFields.classList.add('active');
        } else if (this.value === 'online') {
            onlineFields.classList.add('active');
        } else if (this.value === 'hybrid') {
            offlineFields.classList.add('active');
            onlineFields.classList.add('active');
        }
    });
});

// 강사 추가
function addInstructor() {
    const container = document.getElementById('instructors-container');
    const newInstructor = document.createElement('div');
    newInstructor.className = 'instructor-section';
    newInstructor.setAttribute('data-index', instructorIndex);
    
    newInstructor.innerHTML = `
        <div class="instructor-header">
            <div class="instructor-number">강사 ${instructorIndex + 1}</div>
            <button type="button" class="remove-instructor-btn" onclick="removeInstructor(${instructorIndex})">삭제</button>
        </div>
        
        <div class="form-group">
            <div class="instructor-image-container" onclick="document.getElementById('instructor_image_${instructorIndex}').click()">
                <div class="instructor-image-placeholder">
                    <div style="font-size: 1.5rem; margin-bottom: 5px;">👤</div>
                    <div>이미지 선택</div>
                </div>
            </div>
            <input type="file" id="instructor_image_${instructorIndex}" name="instructor_images[]" 
                   accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" 
                   style="display: none;" onchange="handleInstructorImage(${instructorIndex}, this)">
        </div>
        
        <div class="form-group">
            <label for="instructor_name_${instructorIndex}" class="form-label required">강사명</label>
            <input type="text" id="instructor_name_${instructorIndex}" name="instructor_names[]" 
                   class="form-input" required>
        </div>
        
        <div class="form-group">
            <label for="instructor_info_${instructorIndex}" class="form-label">강사 소개</label>
            <textarea id="instructor_info_${instructorIndex}" name="instructor_infos[]" 
                      class="form-textarea" rows="3"></textarea>
        </div>
    `;
    
    container.appendChild(newInstructor);
    instructorIndex++;
}

// 강사 제거
function removeInstructor(index) {
    const instructor = document.querySelector(`[data-index="${index}"]`);
    if (instructor) {
        instructor.remove();
    }
}

// 강사 이미지 미리보기
function handleInstructorImage(index, input) {
    const file = input.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const container = document.querySelector(`[data-index="${index}"] .instructor-image-container`);
            container.innerHTML = `<img src="${e.target.result}" alt="강사 이미지" class="instructor-image-preview">`;
        };
        reader.readAsDataURL(file);
    }
}

// 행사 이미지 처리
function handleEventImages(input) {
    const files = input.files;
    const previewContainer = document.getElementById('event-images-preview');
    
    // 기존 미리보기 제거
    previewContainer.innerHTML = '';
    
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const previewItem = document.createElement('div');
            previewItem.className = 'image-preview-item';
            previewItem.setAttribute('data-index', i);
            
            previewItem.innerHTML = `
                <img src="${e.target.result}" alt="행사 이미지">
                <button type="button" class="image-preview-remove" onclick="removeEventImage(${i})">×</button>
            `;
            
            previewContainer.appendChild(previewItem);
        };
        
        reader.readAsDataURL(file);
    }
}

// 행사 이미지 제거
function removeEventImage(index) {
    const item = document.querySelector(`#event-images-preview [data-index="${index}"]`);
    if (item) {
        item.remove();
    }
}

// 폼 제출 처리
document.getElementById('eventEditForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // 로딩 상태 표시
    const submitBtn = document.querySelector('.submit-btn');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = '수정 중...';
    submitBtn.disabled = true;
    
    // 폼 데이터 수집
    const formData = new FormData(this);
    
    // 유효성 검사
    const title = document.getElementById('title').value.trim();
    const description = quill.getText().trim();
    
    if (!title) {
        alert('행사 제목을 입력해주세요.');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
        return;
    }
    
    if (!description || description.length < 10) {
        alert('행사 설명을 10자 이상 입력해주세요.');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
        return;
    }
    
    if (description.length > 10000) {
        alert(`행사 설명은 10,000자를 초과할 수 없습니다. (현재: ${description.length}자)`);
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
        return;
    }
    
    // 에디터 내용 추가
    formData.set('description', quill.root.innerHTML);
    
    // 서버로 전송
    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (response.ok) {
            // 성공 시 상세 페이지로 이동
            window.location.href = '/events/detail?id=<?= $event['id'] ?>';
        } else {
            throw new Error('서버 오류가 발생했습니다.');
        }
    })
    .catch(error => {
        console.error('오류:', error);
        alert('행사 수정 중 오류가 발생했습니다.');
        
        // 버튼 상태 복원
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
});
</script>