<?php
/**
 * 행사 등록/편집 페이지 (통합 버전)
 * 강의 등록과 동일한 기능 제공: 주소검색, 강사정보, 이미지업로드, 리치텍스트에디터 등
 */

// 로그인 상태 확인
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
require_once SRC_PATH . '/helpers/HtmlSanitizerHelper.php';
$isLoggedIn = AuthMiddleware::isLoggedIn();
$currentUserId = AuthMiddleware::getCurrentUserId();

if (!$isLoggedIn) {
    header('Location: /auth/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// 기업회원 권한 확인
require_once SRC_PATH . '/middleware/CorporateMiddleware.php';
$permission = CorporateMiddleware::checkLectureEventPermission();

if (!$permission['hasPermission']) {
    $_SESSION['error_message'] = $permission['message'];
    header('Location: /corp/info');
    exit;
}

// 수정 모드 확인 (URL에서 ID 파라미터가 있으면 수정 모드)
$isEditMode = false;
$event = null;
$eventId = null;
$instructors = [];
$images = [];

if (isset($data['event']) && !empty($data['event'])) {
    $isEditMode = true;
    $event = $data['event'];
    $eventId = $event['id'];
    $instructors = $data['instructors'] ?? [];
    $images = $data['images'] ?? [];
}

// CSRF 토큰 생성
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!-- Quill.js 에디터 CSS/JS -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

<!-- 카카오 주소 검색 API -->
<script src="https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>

<!-- 네이버 Maps API (강의 등록과 동일) -->
<script>
// 네이버 Maps API 로딩 함수 (강의 등록과 동일)
function loadNaverMapsAPI() {
    return new Promise((resolve, reject) => {
        if (window.naver && window.naver.maps) {
            resolve();
            return;
        }
        
        const script = document.createElement('script');
        script.type = 'text/javascript';
        script.src = 'https://oapi.map.naver.com/openapi/v3/maps.js?ncpKeyId=<?= htmlspecialchars(NAVER_MAPS_CLIENT_ID, ENT_QUOTES, 'UTF-8') ?>&submodules=geocoder';
        script.async = true;
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
    });
}

// 네이버 Maps API를 통한 정확한 좌표 설정 (강의 등록과 동일)
window.getCoordinates = function(address) {
    console.log('네이버 API로 주소 좌표 계산 시작:', address);
    
    if (!address) {
        document.getElementById('venue_latitude').value = '';
        document.getElementById('venue_longitude').value = '';
        updateCoordinateStatus('', false);
        return;
    }
    
    // 네이버 Maps API가 로드되어 있는지 확인
    if (typeof naver !== 'undefined' && naver.maps && naver.maps.Service) {
        // 네이버 Maps Geocoding 서비스 사용
        naver.maps.Service.geocode({
            query: address
        }, function(status, response) {
            if (status === naver.maps.Service.Status.OK) {
                const result = response.v2.addresses[0];
                if (result) {
                    const lat = parseFloat(result.y);
                    const lng = parseFloat(result.x);
                    
                    // 좌표 설정
                    document.getElementById('venue_latitude').value = lat;
                    document.getElementById('venue_longitude').value = lng;
                    
                    // 성공 시각적 피드백
                    const addressField = document.getElementById('venue_address');
                    if (addressField) {
                        addressField.style.backgroundColor = '#f0fdf4';
                        addressField.style.borderColor = '#22c55e';
                    }
                    
                    console.log('네이버 API 좌표 설정 완료:', {
                        address: address,
                        latitude: lat,
                        longitude: lng
                    });
                    
                    updateCoordinateStatus('✅ 주소 위치가 정상적으로 설정되었습니다', true);
                    return;
                }
            }
            
            // API 실패 시 fallback 좌표 시스템
            console.error('네이버 Geocoding API 실패, fallback 좌표 적용');
            Toast.info('정확한 좌표를 가져올 수 없어 근사 좌표를 사용합니다.');
            
            // 지역별 근사 좌표 fallback 시스템
            const regionCoordinates = getRegionCoordinates(address);
            if (regionCoordinates) {
                document.getElementById('venue_latitude').value = regionCoordinates.lat;
                document.getElementById('venue_longitude').value = regionCoordinates.lng;
                
                console.log('Fallback 좌표 적용:', {
                    address: address,
                    region: regionCoordinates.region,
                    latitude: regionCoordinates.lat,
                    longitude: regionCoordinates.lng
                });
                
                // 성공 시각적 피드백
                const addressField = document.getElementById('venue_address');
                if (addressField) {
                    addressField.style.backgroundColor = '#fff7ed';
                    addressField.style.borderColor = '#f97316';
                }
                
                updateCoordinateStatus('⚠️ 근사 위치로 설정되었습니다 (지역: ' + regionCoordinates.region + ')', true);
            } else {
                // Fallback도 실패한 경우
                document.getElementById('venue_latitude').value = '';
                document.getElementById('venue_longitude').value = '';
                
                // 실패 시각적 피드백
                const addressField = document.getElementById('venue_address');
                if (addressField) {
                    addressField.style.backgroundColor = '#fef2f2';
                    addressField.style.borderColor = '#ef4444';
                }
                
                updateCoordinateStatus('❌ 주소 위치를 찾을 수 없습니다', false);
                Toast.error('주소의 정확한 좌표를 찾을 수 없습니다.\n다른 주소를 시도해보세요.');
            }
        });
    } else {
        // 네이버 Maps API가 로드되지 않은 경우
        console.error('네이버 Maps API가 로드되지 않았습니다');
        updateCoordinateStatus('❌ 지도 API 로드 실패', false);
        Toast.error('지도 서비스를 로드할 수 없습니다. 페이지를 새로고침해주세요.');
    }
};

// 좌표 상태 업데이트 함수
function updateCoordinateStatus(status, isSuccess = false) {
    const statusElement = document.getElementById('coordinate_status');
    if (statusElement) {
        statusElement.textContent = status;
        statusElement.style.color = isSuccess ? '#28a745' : '#dc3545';
    }
}

// 페이지 로드 후 네이버 지도 API 로딩
document.addEventListener('DOMContentLoaded', function() {
    loadNaverMapsAPI().then(() => {
        console.log('네이버 Maps API 로딩 완료');
    }).catch(error => {
        console.error('네이버 Maps API 로딩 실패:', error);
        Toast.warning('지도 기능을 불러올 수 없습니다.\n주소 검색은 정상 작동합니다.');
        updateCoordinateStatus('❌ 지도 API 로딩 실패', false);
    });
});
</script>

<style>
/* 행사 등록 페이지 스타일 */
.event-create-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 80px 20px 40px;
    min-height: calc(100vh - 160px);
}

.event-create-header {
    background: linear-gradient(135deg, #4A90E2 0%, #2E86AB 100%);
    color: white;
    padding: 40px 30px;
    text-align: center;
    margin-bottom: 40px;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(74, 144, 226, 0.2);
}

.event-create-header h1 {
    font-size: 2.5rem;
    margin-bottom: 10px;
    font-weight: 700;
}

.event-create-header p {
    font-size: 1.1rem;
    opacity: 0.9;
}

/* 폼 섹션 스타일 */
.form-section {
    background: white;
    padding: 30px;
    margin-bottom: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
}

.form-section-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #f1f5f9;
    display: flex;
    align-items: center;
    gap: 10px;
}

.form-section-title i {
    color: #4A90E2;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
    font-size: 0.95rem;
}

.form-label.required::after {
    content: ' *';
    color: #ef4444;
}

.form-input, .form-select, .form-textarea {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    background: white;
}

.form-input:focus, .form-select:focus, .form-textarea:focus {
    outline: none;
    border-color: #4A90E2;
    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
}

.form-textarea {
    resize: vertical;
    min-height: 120px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.help-text {
    font-size: 0.85rem;
    color: #6b7280;
    margin-top: 6px;
}

/* 위치 정보 스타일 */
.location-toggle {
    display: flex;
    background: #f8fafc;
    border-radius: 8px;
    padding: 4px;
    margin-bottom: 20px;
}

.location-toggle input[type="radio"] {
    display: none;
}

.location-toggle label {
    flex: 1;
    text-align: center;
    padding: 12px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s ease;
    color: #6b7280;
}

.location-toggle input[type="radio"]:checked + label {
    background: #4A90E2;
    color: white;
    box-shadow: 0 2px 4px rgba(74, 144, 226, 0.3);
}

.location-fields {
    display: none;
}

.location-fields.active {
    display: block;
}

/* 참가 신청 설정 스타일 */
.registration-toggle {
    display: flex;
    background: #f8fafc;
    border-radius: 8px;
    padding: 4px;
    margin-bottom: 10px;
}

.registration-toggle input[type="radio"] {
    display: none;
}

.registration-toggle label {
    flex: 1;
    text-align: center;
    padding: 12px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s ease;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.registration-toggle input[type="radio"]:checked + label {
    background: #4A90E2;
    color: white;
    box-shadow: 0 2px 4px rgba(74, 144, 226, 0.3);
}

.registration-toggle label i {
    font-size: 0.9rem;
}

/* 주소 검색 버튼 스타일 */
.address-search-container {
    position: relative;
}

.address-search-btn {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: #4A90E2;
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.85rem;
    font-weight: 500;
    transition: background 0.2s ease;
}

.address-search-btn:hover {
    background: #3b82f6;
}

#venue_address {
    padding-right: 80px;
    cursor: pointer;
}

#venue_address:read-only {
    background-color: #f9fafb;
}

/* 이미지 업로드 스타일 */
.image-upload-area {
    border: 2px dashed #e2e8f0;
    border-radius: 8px;
    background: #f8fafc;
    transition: all 0.3s ease;
    cursor: pointer;
    min-height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.image-upload-area:hover {
    border-color: #4A90E2;
    background: #f1f5f9;
}

.upload-placeholder {
    text-align: center;
    padding: 20px;
    color: #64748b;
}

.upload-icon {
    font-size: 2rem;
    color: #94a3b8;
    margin-bottom: 10px;
}

.image-preview-container {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-top: 15px;
}

.event-image-item,
.image-preview-item {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    width: 180px;
    height: 140px;
    flex-shrink: 0;
}

.event-image-item img,
.image-preview-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.event-image-item:hover img,
.image-preview-item:hover img {
    transform: scale(1.05);
}

.remove-event-image,
.remove-image {
    position: absolute;
    top: 8px;
    right: 8px;
    background: rgba(239, 68, 68, 0.9);
    color: white;
    border: none;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    transition: all 0.3s ease;
    z-index: 10;
}

.remove-event-image:hover,
.remove-image:hover {
    background: rgba(220, 38, 38, 0.9);
    transform: scale(1.1);
}

/* 강사 정보 스타일 */
.instructors-container {
    margin-top: 20px;
}

.instructor-item {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    position: relative;
}

.instructor-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.instructor-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2d3748;
}

.remove-instructor {
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 6px;
    padding: 6px 12px;
    cursor: pointer;
    font-size: 0.85rem;
}

.instructor-image-upload {
    margin-bottom: 15px;
}

.instructor-image-container {
    position: relative;
    width: 100px;
    height: 100px;
    border-radius: 8px;
    overflow: hidden;
    background: #f3f4f6;
    border: 2px dashed #d1d5db;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.instructor-image-container:hover {
    border-color: #4A90E2;
    background: #f1f5f9;
}

.instructor-image-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.instructor-image-placeholder {
    text-align: center;
    color: #6b7280;
    font-size: 0.8rem;
}

/* 참가비 입력 스타일 */
.fee-input-container {
    position: relative;
}

.fee-input-container::after {
    content: '원';
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #6b7280;
    font-weight: 500;
}

#registration_fee_display {
    padding-right: 40px;
}

/* Quill 에디터 스타일 */
.quill-container {
    margin-top: 10px;
}

.ql-editor {
    min-height: 200px;
}

.ql-toolbar.ql-snow {
    border-top: 1px solid #d1d5db;
    border-left: 1px solid #d1d5db;
    border-right: 1px solid #d1d5db;
    border-radius: 8px 8px 0 0;
}

.ql-container.ql-snow {
    border-bottom: 1px solid #d1d5db;
    border-left: 1px solid #d1d5db;
    border-right: 1px solid #d1d5db;
    border-radius: 0 0 8px 8px;
}

/* 버튼 스타일 */
.form-buttons {
    text-align: center;
    padding: 30px 0;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
    margin: 0 10px;
}

.btn-primary {
    background: #4A90E2;
    color: white;
}

.btn-primary:hover {
    background: #3b82f6;
    transform: translateY(-1px);
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* 반응형 디자인 */
@media (max-width: 768px) {
    .event-create-container {
        padding: 60px 15px 20px;
    }
    
    .form-section {
        padding: 20px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .event-create-header {
        padding: 30px 20px;
    }
    
    .event-create-header h1 {
        font-size: 2rem;
    }
    
    .location-toggle {
        flex-direction: column;
        gap: 5px;
    }
    
    .image-preview-container {
        gap: 10px;
    }
    
    .event-image-item,
    .image-preview-item {
        width: 140px;
        height: 110px;
    }
    
    .remove-event-image,
    .remove-image {
        width: 24px;
        height: 24px;
        font-size: 0.7rem;
    }
}
</style>

<div class="event-create-container">
    <!-- 헤더 컴포넌트 -->
    <?php include_once SRC_PATH . '/components/EventCreateHeader.php'; ?>

    <!-- 행사 등록 폼 -->
    <form id="eventForm" method="POST" action="/events/store" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="content_type" value="event">
        <?php if ($isEditMode): ?>
        <input type="hidden" name="event_id" value="<?= htmlspecialchars($eventId, ENT_QUOTES, 'UTF-8') ?>">
        <?php endif; ?>
        
        <!-- 기본 정보 섹션 -->
        <div class="form-section">
            <h3 class="form-section-title">
                <i class="fas fa-info-circle"></i>
                기본 정보
            </h3>
            
            <div class="form-group">
                <label for="title" class="form-label required">행사 제목</label>
                <input type="text" id="title" name="title" class="form-input" 
                       placeholder="예: 2024 마케팅 트렌드 컨퍼런스" required maxlength="200"
                       value="<?= $isEditMode ? htmlspecialchars($event['title'] ?? '', ENT_QUOTES, 'UTF-8') : '' ?>">
                <div class="help-text">참가자의 관심을 끌 수 있는 명확하고 흥미로운 제목을 작성해주세요.</div>
            </div>

            <div class="form-group">
                <label for="category" class="form-label required">카테고리</label>
                <select id="category" name="category" class="form-select" required>
                    <option value="">카테고리를 선택하세요</option>
                    <option value="conference" <?= $isEditMode && ($event['category'] ?? '') === 'conference' ? 'selected' : '' ?>>컨퍼런스</option>
                    <option value="seminar" <?= $isEditMode && ($event['category'] ?? '') === 'seminar' ? 'selected' : '' ?>>세미나</option>
                    <option value="workshop" <?= $isEditMode && ($event['category'] ?? '') === 'workshop' ? 'selected' : '' ?>>워크샵</option>
                    <option value="networking" <?= $isEditMode && ($event['category'] ?? '') === 'networking' ? 'selected' : '' ?>>네트워킹</option>
                    <option value="exhibition" <?= $isEditMode && ($event['category'] ?? '') === 'exhibition' ? 'selected' : '' ?>>전시회</option>
                    <option value="training" <?= $isEditMode && ($event['category'] ?? '') === 'training' ? 'selected' : '' ?>>교육</option>
                    <option value="other" <?= $isEditMode && ($event['category'] ?? '') === 'other' ? 'selected' : '' ?>>기타</option>
                </select>
            </div>

            <div class="form-group">
                <label for="description" class="form-label required">행사 상세 설명</label>
                <div class="help-text">행사의 목적, 프로그램, 기대효과 등을 자세히 작성해주세요. 이미지와 다양한 서식을 활용할 수 있습니다.</div>
                <div class="quill-container">
                    <div id="quill-editor"></div>
                </div>
                <div id="imageCounter" class="char-counter" style="color: #2563eb; font-weight: 500;">📷 이미지: 0 / 20</div>
                <textarea name="description" id="description" style="display: none;"></textarea>
            </div>
        </div>

        <!-- 일정 정보 섹션 -->
        <div class="form-section">
            <h3 class="form-section-title">
                <i class="fas fa-calendar-alt"></i>
                일정 정보
            </h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="start_date" class="form-label required">시작일</label>
                    <input type="date" id="start_date" name="start_date" class="form-input" required
                           value="<?= $isEditMode ? htmlspecialchars($event['start_date'] ?? '', ENT_QUOTES, 'UTF-8') : '' ?>">
                </div>
                <div class="form-group">
                    <label for="start_time" class="form-label required">시작시간</label>
                    <input type="time" id="start_time" name="start_time" class="form-input" required
                           value="<?= $isEditMode && !empty($event['start_time']) ? htmlspecialchars(substr($event['start_time'], 0, 5), ENT_QUOTES, 'UTF-8') : '' ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="end_date" class="form-label">종료일</label>
                    <input type="date" id="end_date" name="end_date" class="form-input"
                           value="<?= $isEditMode ? htmlspecialchars($event['end_date'] ?? '', ENT_QUOTES, 'UTF-8') : '' ?>">
                    <div class="help-text">당일 행사인 경우 비워두세요.</div>
                </div>
                <div class="form-group">
                    <label for="end_time" class="form-label">종료시간</label>
                    <input type="time" id="end_time" name="end_time" class="form-input"
                           value="<?= $isEditMode && !empty($event['end_time']) ? htmlspecialchars(substr($event['end_time'], 0, 5), ENT_QUOTES, 'UTF-8') : '' ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="registration_deadline" class="form-label required">신청 마감일</label>
                    <input type="datetime-local" id="registration_deadline" name="registration_deadline" class="form-input" required
                           value="<?php 
                               if ($isEditMode && !empty($event['registration_deadline'])) {
                                   $timestamp = strtotime($event['registration_deadline']);
                                   echo $timestamp ? htmlspecialchars(date('Y-m-d\TH:i', $timestamp), ENT_QUOTES, 'UTF-8') : '';
                               } else {
                                   echo '';
                               }
                           ?>">
                    <div class="help-text">참가 신청 마감 일시를 설정하세요. 이 일시 이후로는 신청이 불가합니다.</div>
                </div>
            </div>
        </div>

        <!-- 장소 정보 섹션 -->
        <div class="form-section">
            <h3 class="form-section-title">
                <i class="fas fa-map-marker-alt"></i>
                장소 정보
            </h3>
            
            <div class="location-toggle">
                <input type="radio" id="offline" name="location_type" value="offline" checked>
                <label for="offline">🏢 오프라인 행사</label>
                
                <input type="radio" id="online" name="location_type" value="online">
                <label for="online">💻 온라인 행사</label>
            </div>

            <div id="offline-fields" class="location-fields active">
                <div class="form-group">
                    <label for="venue_name" class="form-label required">행사장명</label>
                    <input type="text" id="venue_name" name="venue_name" class="form-input" 
                           placeholder="예: 코엑스 컨퍼런스룸 A"
                           value="<?= $isEditMode ? htmlspecialchars($event['venue_name'] ?? '', ENT_QUOTES, 'UTF-8') : '' ?>">
                </div>
                <div class="form-group">
                    <label for="venue_address" class="form-label required">주소</label>
                    <div class="address-search-container">
                        <input type="text" id="venue_address" name="venue_address" class="form-input" 
                               placeholder="주소 검색 버튼을 클릭하세요" readonly
                               value="<?= $isEditMode ? htmlspecialchars($event['venue_address'] ?? '', ENT_QUOTES, 'UTF-8') : '' ?>">
                        <button type="button" id="address_search_btn" class="address-search-btn">
                            🔍 주소 검색
                        </button>
                    </div>
                    <div class="help-text">정확한 주소를 입력하면 참가자들이 쉽게 찾아올 수 있습니다.</div>
                    
                    <!-- 위치 설정 상태 표시 -->
                    <div style="margin-top: 8px;">
                        <span style="font-size: 0.9rem; font-weight: 600;" id="coordinate_status"></span>
                    </div>
                    
                    <!-- 위도, 경도 저장을 위한 숨김 필드 -->
                    <input type="hidden" id="venue_latitude" name="venue_latitude" 
                           value="<?= $isEditMode ? htmlspecialchars($event['venue_latitude'] ?? '', ENT_QUOTES, 'UTF-8') : '' ?>">
                    <input type="hidden" id="venue_longitude" name="venue_longitude" 
                           value="<?= $isEditMode ? htmlspecialchars($event['venue_longitude'] ?? '', ENT_QUOTES, 'UTF-8') : '' ?>">
                </div>
            </div>

            <div id="online-fields" class="location-fields">
                <div class="form-group">
                    <label for="online_link" class="form-label required">온라인 링크</label>
                    <input type="url" id="online_link" name="online_link" class="form-input" 
                           placeholder="https://zoom.us/j/1234567890"
                           value="<?= $isEditMode ? htmlspecialchars($event['online_link'] ?? '', ENT_QUOTES, 'UTF-8') : '' ?>">
                    <div class="help-text">Zoom, Teams, Meet 등의 온라인 회의 링크를 입력하세요.</div>
                </div>
            </div>
        </div>

        <!-- 행사 세부사항 섹션 -->
        <div class="form-section">
            <h3 class="form-section-title">
                <i class="fas fa-cogs"></i>
                행사 세부사항
            </h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="max_participants" class="form-label">최대 참가자 수</label>
                    <input type="number" id="max_participants" name="max_participants" class="form-input" 
                           placeholder="50" min="1"
                           value="<?= $isEditMode ? htmlspecialchars($event['max_participants'] ?? '', ENT_QUOTES, 'UTF-8') : '' ?>">
                    <div class="help-text">참가자 수 제한이 없으면 비워두세요.</div>
                </div>
                <div class="form-group">
                    <label for="registration_fee" class="form-label">참가비</label>
                    <div class="fee-input-container">
                        <input type="text" id="registration_fee_display" class="form-input" 
                               placeholder="50,000">
                        <input type="hidden" id="registration_fee" name="registration_fee" value="0">
                    </div>
                    <div class="help-text">무료 행사인 경우 0 또는 비워두세요.</div>
                </div>
            </div>
            
            <!-- 참가 신청 설정 -->
            <div class="form-group">
                <label class="form-label">온라인 참가 신청</label>
                <div class="registration-toggle">
                    <input type="radio" id="allow_registration_yes" name="allow_online_registration" value="1" <?= (!$isEditMode || ($isEditMode && $event['allow_online_registration'] == 1)) ? 'checked' : '' ?>>
                    <label for="allow_registration_yes">
                        <i class="fas fa-check-circle"></i>
                        온라인 참가 신청 접수
                    </label>
                    
                    <input type="radio" id="allow_registration_no" name="allow_online_registration" value="0" <?= ($isEditMode && $event['allow_online_registration'] == 0) ? 'checked' : '' ?>>
                    <label for="allow_registration_no">
                        <i class="fas fa-times-circle"></i>
                        참가 신청을 받지 않음
                    </label>
                </div>
                <div class="help-text">
                    <span id="registration-help-yes">참가자가 직접 온라인으로 신청할 수 있습니다.</span>
                    <span id="registration-help-no" style="display: none;">참가자는 온라인 신청 없이 주최자에게 별도 연락해야 합니다.</span>
                </div>
            </div>
        </div>

        <!-- 행사 이미지 섹션 -->
        <div class="form-section">
            <h3 class="form-section-title">
                <i class="fas fa-images"></i>
                행사 이미지
            </h3>
            
            <div class="form-group">
                <label class="form-label">행사 홍보 이미지</label>
                <div class="image-upload-area" onclick="document.getElementById('event_images').click()">
                    <div class="upload-placeholder">
                        <div class="upload-icon">📷</div>
                        <p>클릭하여 이미지 업로드</p>
                        <div class="upload-help">JPG, PNG, GIF, WebP (최대 30MB)</div>
                    </div>
                </div>
                <input type="file" id="event_images" name="event_images[]" multiple style="display: none;">
                <div class="image-preview-container" id="event-image-preview"></div>
            </div>
        </div>

        <!-- 강사 정보 섹션 -->
        <div class="form-section">
            <h3 class="form-section-title">
                <i class="fas fa-user-tie"></i>
                강사/연사 정보
            </h3>
            
            <div class="form-group">
                <button type="button" id="add-instructor" class="btn btn-secondary">
                    <i class="fas fa-plus"></i>
                    강사 추가 (최대 5명)
                </button>
                <div class="help-text">행사의 강사나 연사 정보를 추가할 수 있습니다.</div>
            </div>

            <div class="instructors-container" id="instructors-container">
                <!-- 강사 정보가 동적으로 추가됩니다 -->
            </div>
        </div>

        <!-- 추가 정보 섹션 -->
        <div class="form-section">
            <h3 class="form-section-title">
                <i class="fas fa-plus-circle"></i>
                추가 정보
            </h3>
            
            <div class="form-group">
                <label for="youtube_video" class="form-label">YouTube 동영상 URL</label>
                <input type="url" id="youtube_video" name="youtube_video" class="form-input" 
                       placeholder="https://www.youtube.com/watch?v=..."
                       value="<?= $isEditMode ? htmlspecialchars($event['youtube_video'] ?? '', ENT_QUOTES, 'UTF-8') : '' ?>">
                <div class="help-text">행사 소개 영상이나 관련 동영상 링크가 있으면 입력해주세요.</div>
            </div>
        </div>

        <!-- 등록 버튼 -->
        <div class="form-buttons">
            <a href="/events" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                취소
            </a>
            <button type="submit" class="btn btn-primary" id="submit-btn">
                <i class="fas fa-<?= $isEditMode ? 'save' : 'calendar-plus' ?>"></i>
                <?= $isEditMode ? '행사 수정' : '행사 등록' ?>
            </button>
        </div>
    </form>
</div>

<script>
// 전역 변수
let quill;
let instructorCount = 0;
let eventImageCount = 0;
let uploadedEventImages = [];

// 숫자 콤마 처리 함수
function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function removeCommas(str) {
    return str.replace(/,/g, '');
}

// DOM 로드 후 초기화
document.addEventListener('DOMContentLoaded', function() {
    initializeForm();
    initializeQuillEditor();
    initializeLocationToggle();
    initializeAddressSearch();
    initializeFeeInput();
    initializeImageUpload();
    initializeInstructorSystem();
    initializeRegistrationToggle();
    
    <?php if ($isEditMode): ?>
    // 편집 모드일 때 기존 데이터 로드
    loadEditData();
    <?php endif; ?>
});

// 폼 초기화
function initializeForm() {
    const form = document.getElementById('eventForm');
    const submitBtn = document.getElementById('submit-btn');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // 에디터 내용을 hidden textarea에 복사
        const editorContent = quill.root.innerHTML;
        document.getElementById('description').value = editorContent;
        
        // 폼을 FormData로 변환하여 파일 업로드 지원
        const formData = new FormData(form);
        
        // 기존 HTML input의 파일 데이터 제거 (중복 방지)
        formData.delete('event_images[]');
        
        // 업로드된 이벤트 이미지 파일들을 FormData에 추가 (배열 형태로)
        uploadedEventImages.forEach((imageData, index) => {
            if (imageData.file) {
                formData.append('event_images[]', imageData.file);
            }
        });
        
        // 디버깅을 위한 로그
        console.log('📤 FormData 전송:', {
            hasEventImages: uploadedEventImages.length > 0,
            eventImagesCount: uploadedEventImages.length,
            formAction: form.action
        });
        
        // 유효성 검사
        if (!validateForm()) {
            return;
        }
        
        // 🚀 v3.31.0: Loading 클래스 사용
        Loading.button(submitBtn, true, { text: '등록 중...' });

        // v3.42.0: ApiClient 사용 (FormData는 자동으로 multipart/form-data로 처리)
        ApiClient.post(form.action, formData, {
            headers: {}, // Content-Type 자동 설정을 위해 빈 객체 전달
            noLoading: true,
            noErrorToast: true
        })
        .then(data => {
            // 성공 시 리다이렉트 (data에 redirect URL이 있으면)
            if (data.success || data.data?.success) {
                const redirectUrl = data.redirect || data.data?.redirect || '/events';
                window.location.href = redirectUrl;
            } else {
                // 오류 응답 처리
                Toast.error(data.message || '등록 중 오류가 발생했습니다.');
                Loading.button(submitBtn, false);
            }
        })
        .catch(error => {
            console.error('Form submission error:', error);
            Toast.error('등록 중 오류가 발생했습니다.');
            Loading.button(submitBtn, false);
        });
    });
}
</script>

<!-- 공통 업로드 설정 (Quill 에디터 초기화 전에 로드) -->
<?php include '/var/www/html/topmkt/src/views/includes/upload-config.js.php'; ?>

<script>
// Quill 에디터 초기화
function initializeQuillEditor() {
    quill = new Quill('#quill-editor', {
        theme: 'snow',
        placeholder: '행사의 상세한 설명을 입력해주세요...',
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
        }
    });
    
    // 전역 접근을 위해 window에 할당
    window.quill = quill;
    
    // 에디터 텍스트 선택 문제 해결
    const editorElement = document.querySelector('.ql-editor');
    if (editorElement) {
        editorElement.style.cssText += `
            -webkit-user-select: text !important;
            -moz-user-select: text !important;
            -ms-user-select: text !important;
            user-select: text !important;
        `;
    }
}

// 에디터 이미지 핸들러
function imageHandler() {
    const input = document.createElement('input');
    input.setAttribute('type', 'file');
    if (window.getImageAcceptAttribute) {
        input.setAttribute('accept', window.getImageAcceptAttribute());
    }
    input.style.display = 'none';
    
    input.onchange = async function() {
        const file = input.files[0];
        if (!file) return;
        
        // 파일 크기 검증 (공통 설정 사용: 30MB)
        if (!window.validateFileSize || !window.validateFileSize(file.size)) {
            Toast.error(window.getFileSizeErrorMessage ? window.getFileSizeErrorMessage() : '파일 크기가 너무 큽니다.');
            return;
        }
        
        // 이미지 개수 제한 검사 (20개)
        const currentImages = quill.container.querySelectorAll('img').length;
        if (currentImages >= 20) {
            Toast.error(`최대 20개의 이미지만 업로드할 수 있습니다. (현재: ${currentImages}개)`);
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
            formData.append('upload_type', 'events');
            
            console.log('🔄 이미지 업로드 시작:', file.name, 'Size:', file.size);

            // v3.42.0: ApiClient 사용 (FormData는 자동으로 multipart/form-data로 처리)
            const result = await ApiClient.post('/api/media/upload-image', formData, {
                headers: {}, // Content-Type 자동 설정을 위해 빈 객체 전달
                noLoading: true
            });

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
                window.updateImageCounter();
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
            
            Toast.error(errorMessage);
        }
    };
    
    input.click();
}

// 위치 토글 함수 (전역 스코프)
function toggleLocationFields() {
    const offlineRadio = document.getElementById('offline');
    const onlineRadio = document.getElementById('online');
    const offlineFields = document.getElementById('offline-fields');
    const onlineFields = document.getElementById('online-fields');
    
    if (offlineRadio.checked) {
        offlineFields.classList.add('active');
        onlineFields.classList.remove('active');
        
        // 필수 필드 설정
        document.getElementById('venue_name').required = true;
        document.getElementById('venue_address').required = true;
        document.getElementById('online_link').required = false;
    } else {
        offlineFields.classList.remove('active');
        onlineFields.classList.add('active');
        
        // 필수 필드 설정
        document.getElementById('venue_name').required = false;
        document.getElementById('venue_address').required = false;
        document.getElementById('online_link').required = true;
    }
}

// 위치 토글 초기화
function initializeLocationToggle() {
    const offlineRadio = document.getElementById('offline');
    const onlineRadio = document.getElementById('online');
    
    offlineRadio.addEventListener('change', toggleLocationFields);
    onlineRadio.addEventListener('change', toggleLocationFields);
    
    // 초기 상태 설정
    toggleLocationFields();
}

// 주소 검색 초기화
function initializeAddressSearch() {
    const addressSearchBtn = document.getElementById('address_search_btn');
    const addressField = document.getElementById('venue_address');
    
    function openAddressSearch() {
        new daum.Postcode({
            oncomplete: function(data) {
                let addr = '';
                let extraAddr = '';
                
                if (data.userSelectedType === 'R') {
                    addr = data.roadAddress;
                } else {
                    addr = data.jibunAddress;
                }
                
                if(data.userSelectedType === 'R'){
                    if(data.bname !== '' && /[동|로|가]$/g.test(data.bname)){
                        extraAddr += data.bname;
                    }
                    if(data.buildingName !== '' && data.apartment === 'Y'){
                        extraAddr += (extraAddr !== '' ? ', ' + data.buildingName : data.buildingName);
                    }
                    if(extraAddr !== ''){
                        extraAddr = ' (' + extraAddr + ')';
                    }
                }
                
                addressField.value = addr + extraAddr;
                
                // 주소 선택 후 좌표 계산 (이제 함수가 미리 정의되어 있음)
                window.getCoordinates(addr + extraAddr);
            }
        }).open();
    }
    
    addressSearchBtn.addEventListener('click', openAddressSearch);
    addressField.addEventListener('click', openAddressSearch);
}

// 참가비 입력 초기화
function initializeFeeInput() {
    const feeDisplayInput = document.getElementById('registration_fee_display');
    const feeHiddenInput = document.getElementById('registration_fee');
    
    feeDisplayInput.addEventListener('input', function(e) {
        let value = e.target.value;
        
        // 숫자와 콤마만 허용
        value = value.replace(/[^\d,]/g, '');
        
        // 콤마 제거 후 숫자로 변환
        let numericValue = removeCommas(value);
        
        if (numericValue === '') {
            e.target.value = '';
            feeHiddenInput.value = '0';
            return;
        }
        
        let num = parseInt(numericValue);
        if (isNaN(num)) {
            num = 0;
        }
        
        // 표시용: 콤마 추가
        e.target.value = numberWithCommas(num);
        
        // 실제 값: 숫자만
        feeHiddenInput.value = num;
    });
    
    // 초기값 설정
    feeDisplayInput.value = '0';
    feeHiddenInput.value = '0';
}

// 이미지 업로드 초기화
function initializeImageUpload() {
    const imageInput = document.getElementById('event_images');
    const previewContainer = document.getElementById('event-image-preview');
    
    imageInput.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        let hasInvalidFile = false;
        
        files.forEach(file => {
            if (!validateImageFile(file)) {
                hasInvalidFile = true;
                return;
            }
            
            // 파일 미리보기 생성
            const reader = new FileReader();
            reader.onload = function(e) {
                const imageId = addImagePreview(e.target.result, file.name);
                
                // 실제 파일을 uploadedEventImages에 추가
                uploadedEventImages.push({
                    id: imageId,
                    filename: file.name,
                    url: e.target.result,
                    file: file // 실제 파일 객체 저장
                });
                
                console.log('📷 이미지 추가됨:', {
                    filename: file.name,
                    size: file.size,
                    type: file.type,
                    totalCount: uploadedEventImages.length
                });
            };
            reader.readAsDataURL(file);
        });
        
        // 유효하지 않은 파일이 있으면 input 초기화
        if (hasInvalidFile) {
            e.target.value = '';
        }
        
        // 파일 처리 완료 후 항상 input 초기화 (중복 전송 방지)
        // JavaScript uploadedEventImages 배열에서 파일을 관리하므로 HTML input은 비움
        setTimeout(() => {
            e.target.value = '';
        }, 100);
    });
}

// 더 이상 필요하지 않은 업로드 함수들은 제거

// 🚀 v3.27.0: 이미지 파일 검증 (공통 업로드 설정 사용)
function validateImageFile(file) {
    // 파일 확장자 검증
    if (!window.validateImageExtension(file.name)) {
        Toast.info('JPG, PNG, GIF, WebP 파일만 업로드 가능합니다.');
        return false;
    }

    // 파일 크기 검증
    if (!window.validateFileSize(file.size)) {
        Toast.error(window.getFileSizeErrorMessage());
        return false;
    }
    
    return true;
}

// 이미지 미리보기 추가
function addImagePreview(src, filename, customId = null, isLoading = false) {
    const previewContainer = document.getElementById('event-image-preview');
    const imageId = customId || 'event-image-' + (++eventImageCount);
    
    const imageDiv = document.createElement('div');
    imageDiv.className = 'event-image-item';
    imageDiv.id = imageId;
    
    if (isLoading) {
        imageDiv.innerHTML = `
            <div class="image-loading">
                <div class="loading-spinner"></div>
                <p>업로드 중...</p>
            </div>
        `;
    } else {
        imageDiv.innerHTML = `
            <img src="${src}" alt="${filename}">
            <button type="button" class="remove-event-image" onclick="removeEventImage('${imageId}')">
                ×
            </button>
        `;
    }
    
    previewContainer.appendChild(imageDiv);
    return imageId;
}

// 이미지 미리보기 업데이트
function updateImagePreview(imageId, src, filename) {
    const imageDiv = document.getElementById(imageId);
    if (imageDiv) {
        imageDiv.innerHTML = `
            <img src="${src}" alt="${filename}">
            <button type="button" class="remove-event-image" onclick="removeEventImage('${imageId}')">
                ×
            </button>
        `;
    }
}

// 이미지 미리보기 제거
function removeImagePreview(imageId) {
    const imageDiv = document.getElementById(imageId);
    if (imageDiv) {
        imageDiv.remove();
    }
}

// 이미지 제거
function removeEventImage(imageId) {
    const imageElement = document.getElementById(imageId);
    if (imageElement) {
        imageElement.remove();
        uploadedEventImages = uploadedEventImages.filter(img => img.id !== imageId);
        
        // 파일 input은 항상 비어있어야 함 (중복 전송 방지)
        const fileInput = document.getElementById('event_images');
        if (fileInput) {
            fileInput.value = '';
        }
    }
}

// 강사 시스템 초기화
function initializeInstructorSystem() {
    const addInstructorBtn = document.getElementById('add-instructor');
    
    addInstructorBtn.addEventListener('click', function() {
        if (instructorCount >= 5) {
            Toast.info('최대 5명까지 강사를 추가할 수 있습니다.');
            return;
        }
        
        addInstructor();
    });
}

// 강사 추가
function addInstructor() {
    const container = document.getElementById('instructors-container');
    const instructorIndex = instructorCount++;
    
    const instructorDiv = document.createElement('div');
    instructorDiv.className = 'instructor-item';
    instructorDiv.id = 'instructor-' + instructorIndex;
    
    instructorDiv.innerHTML = `
        <div class="instructor-header">
            <h4 class="instructor-title">강사 ${instructorIndex + 1}</h4>
            <button type="button" class="remove-instructor" onclick="removeInstructor(${instructorIndex})">
                <i class="fas fa-trash"></i> 제거
            </button>
        </div>
        
        <div class="instructor-image-upload">
            <label class="form-label">강사 프로필 이미지</label>
            <div class="instructor-image-container" onclick="document.getElementById('instructor_image_${instructorIndex}').click()">
                <div class="instructor-image-placeholder">
                    <div style="font-size: 1.5rem; margin-bottom: 5px;">👤</div>
                    <div>이미지 선택</div>
                </div>
            </div>
            <input type="file" id="instructor_image_${instructorIndex}" name="instructor_images[]"
                   style="display: none;" onchange="handleInstructorImage(${instructorIndex}, this)">
        </div>
        
        <div class="form-group">
            <label for="instructor_name_${instructorIndex}" class="form-label required">강사명</label>
            <input type="text" id="instructor_name_${instructorIndex}" name="instructor_names[]" 
                   class="form-input" placeholder="홍길동" required>
        </div>
        
        <div class="form-group">
            <label for="instructor_info_${instructorIndex}" class="form-label">강사 소개</label>
            <textarea id="instructor_info_${instructorIndex}" name="instructor_infos[]" 
                      class="form-textarea" rows="3"
                      placeholder="강사의 경력, 전문 분야 등을 간단히 소개해주세요..."></textarea>
        </div>
    `;
    
    container.appendChild(instructorDiv);

    // 🔧 v3.53.0: 동적으로 추가된 input에 accept 속성 설정
    const newInput = document.getElementById(`instructor_image_${instructorIndex}`);
    if (newInput && window.getImageAcceptAttribute) {
        newInput.accept = window.getImageAcceptAttribute();
    }

    // 버튼 텍스트 업데이트
    updateAddInstructorButton();
}

// 강사 제거
function removeInstructor(index) {
    const instructorElement = document.getElementById('instructor-' + index);
    if (instructorElement) {
        instructorElement.remove();
        instructorCount--;
        updateAddInstructorButton();
    }
}

// 강사 추가 버튼 업데이트
function updateAddInstructorButton() {
    const btn = document.getElementById('add-instructor');
    const remainingSlots = 5 - instructorCount;
    
    if (remainingSlots <= 0) {
        btn.style.display = 'none';
    } else {
        btn.style.display = 'inline-flex';
        btn.innerHTML = `<i class="fas fa-plus"></i> 강사 추가 (${remainingSlots}명 추가 가능)`;
    }
}

// 강사 이미지 핸들링
function handleInstructorImage(index, input) {
    const file = input.files[0];
    if (!file) return;
    
    if (!validateImageFile(file)) {
        input.value = '';
        return;
    }
    
    const reader = new FileReader();
    reader.onload = function(e) {
        const container = input.parentElement.querySelector('.instructor-image-container');
        container.innerHTML = `<img src="${e.target.result}" alt="강사 이미지">`;
    };
    reader.readAsDataURL(file);
}

// 참가 신청 설정 토글 초기화
function initializeRegistrationToggle() {
    const yesRadio = document.getElementById('allow_registration_yes');
    const noRadio = document.getElementById('allow_registration_no');
    const yesHelp = document.getElementById('registration-help-yes');
    const noHelp = document.getElementById('registration-help-no');
    
    function updateHelpText() {
        if (yesRadio.checked) {
            yesHelp.style.display = 'inline';
            noHelp.style.display = 'none';
        } else {
            yesHelp.style.display = 'none';
            noHelp.style.display = 'inline';
        }
    }
    
    yesRadio.addEventListener('change', updateHelpText);
    noRadio.addEventListener('change', updateHelpText);
    
    // 초기 상태 설정
    updateHelpText();
}

// 폼 유효성 검사
function validateForm() {
    const title = document.getElementById('title').value.trim();
    const category = document.getElementById('category').value;
    const startDate = document.getElementById('start_date').value;
    const startTime = document.getElementById('start_time').value;
    const description = quill.getText().trim();
    
    if (!title) {
        Toast.error('행사 제목을 입력해주세요.');
        return false;
    }
    
    if (!category) {
        Toast.info('카테고리를 선택해주세요.');
        return false;
    }
    
    if (!startDate || !startTime) {
        Toast.error('시작 날짜와 시간을 입력해주세요.');
        return false;
    }
    
    if (!description || description.length < 10) {
        Toast.error('행사 설명을 10자 이상 입력해주세요.');
        return false;
    }
    
    if (description.length > 10000) {
        Toast.error(`행사 설명은 10,000자를 초과할 수 없습니다. (현재: ${description.length}자)`);
        return false;
    }
    
    // 위치별 필수 필드 검사
    const locationType = document.querySelector('input[name="location_type"]:checked').value;
    
    if (locationType === 'offline') {
        const venueName = document.getElementById('venue_name').value.trim();
        const venueAddress = document.getElementById('venue_address').value.trim();
        const venueLatitude = document.getElementById('venue_latitude').value.trim();
        const venueLongitude = document.getElementById('venue_longitude').value.trim();
        
        if (!venueName || !venueAddress) {
            Toast.error('오프라인 행사는 행사장명과 주소를 입력해주세요.');
            return false;
        }
        
        // 좌표 필수 검증
        if (!venueLatitude || !venueLongitude) {
            Toast.error('주소 검색을 통해 정확한 위치 좌표를 설정해주세요.\n네이버 API 인증 문제가 있는 경우 관리자에게 문의하세요.');
            return false;
        }
    } else {
        const onlineLink = document.getElementById('online_link').value.trim();
        
        if (!onlineLink) {
            Toast.error('온라인 행사는 접속 링크를 입력해주세요.');
            return false;
        }
    }
    
    return true;
}

// 전역 이미지 카운터 업데이트 함수
window.updateImageCounter = function() {
    const imageCounter = document.getElementById('imageCounter');
    if (imageCounter && window.quill) {
        const currentImages = window.quill.container.querySelectorAll('img').length;
        
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
};

// Quill 텍스트 변경 이벤트 모니터링 (20개 초과 시 자동 제거)
setTimeout(() => {
    if (window.quill) {
        window.quill.on('text-change', function(delta, oldDelta, source) {
            const currentImages = window.quill.container.querySelectorAll('img').length;
            if (currentImages > 20) {
                console.log(`⚠️ 이미지 개수 초과: ${currentImages}개 → 20개로 제한`);
                const images = window.quill.container.querySelectorAll('img');
                for (let i = 20; i < images.length; i++) {
                    images[i].remove();
                }
                Toast.error('최대 20개의 이미지만 허용됩니다. 초과된 이미지가 제거되었습니다.');
            }
            // 이미지 카운터 업데이트 (약간의 지연을 두어 DOM 변경 완료 후 실행)
            setTimeout(window.updateImageCounter, 100);
        });
        
        // 초기 이미지 카운터 업데이트
        window.updateImageCounter();
        
        console.log('✅ 이벤트 생성 페이지 - 이미지 제한 시스템 초기화 완료');
    } else {
        console.error('❌ Quill 에디터가 초기화되지 않았습니다.');
        Toast.error('에디터 초기화에 실패했습니다.\n페이지를 새로고침해주세요.');
    }
}, 1500);
</script>

<?php if ($isEditMode): ?>
<script>
// 편집 모드 데이터 로드 함수
function loadEditData() {
    const eventData = <?= json_encode($event ?? []) ?>;
    const instructorsData = <?= json_encode($instructors ?? []) ?>;
    const imagesData = <?= json_encode($images ?? []) ?>;
    
    // 기본 정보 로드
    if (eventData.description) {
        quill.root.innerHTML = eventData.description;
    }
    
    // 날짜 및 시간 필드 로드
    if (eventData.start_date) document.getElementById('start_date').value = eventData.start_date;
    if (eventData.end_date) document.getElementById('end_date').value = eventData.end_date;
    if (eventData.start_time) document.getElementById('start_time').value = eventData.start_time.slice(0, 5);
    if (eventData.end_time) document.getElementById('end_time').value = eventData.end_time.slice(0, 5);
    
    // 위치 타입 설정
    if (eventData.location_type) {
        const locationRadio = document.querySelector(`input[name="location_type"][value="${eventData.location_type}"]`);
        if (locationRadio) {
            locationRadio.checked = true;
            toggleLocationFields(); // 위치 필드 표시/숨김 업데이트
        }
    }
    
    // 장소 정보 로드
    if (eventData.venue_name) document.getElementById('venue_name').value = eventData.venue_name;
    if (eventData.venue_address) document.getElementById('venue_address').value = eventData.venue_address;
    if (eventData.online_link) document.getElementById('online_link').value = eventData.online_link;
    
    // 유튜브 링크 로드
    if (eventData.youtube_video) document.getElementById('youtube_video').value = eventData.youtube_video;
    
    // 정원 및 참가비 로드
    if (eventData.max_participants) document.getElementById('max_participants').value = eventData.max_participants;
    if (eventData.registration_fee) {
        const fee = eventData.registration_fee.toString();
        document.getElementById('registration_fee').value = fee;
        document.getElementById('registration_fee_display').value = numberWithCommas(fee);
    }
    
    // 참가 신청 허용 여부 로드
    if (eventData.allow_online_registration !== undefined) {
        const allowRegistration = eventData.allow_online_registration == 1;
        document.getElementById('allow_registration_yes').checked = allowRegistration;
        document.getElementById('allow_registration_no').checked = !allowRegistration;
        
        // 도움말 텍스트 업데이트
        const yesHelp = document.getElementById('registration-help-yes');
        const noHelp = document.getElementById('registration-help-no');
        if (allowRegistration) {
            yesHelp.style.display = 'inline';
            noHelp.style.display = 'none';
        } else {
            yesHelp.style.display = 'none';
            noHelp.style.display = 'inline';
        }
    }
    
    // 기존 강사 정보 로드
    if (instructorsData && instructorsData.length > 0) {
        instructorsData.forEach((instructor, index) => {
            addInstructor();
            const instructorContainer = document.querySelectorAll('.instructor-item')[index];
            if (instructorContainer) {
                const nameInput = instructorContainer.querySelector(`#instructor_name_${index}`);
                const infoInput = instructorContainer.querySelector(`#instructor_info_${index}`);
                if (nameInput) nameInput.value = instructor.name || instructor.instructor_name || '';
                if (infoInput) infoInput.value = instructor.info || instructor.instructor_info || '';
                
                // 강사 이미지 로드
                const instructorImage = instructor.instructor_image || instructor.image;
                if (instructorImage) {
                    const imageContainer = instructorContainer.querySelector('.instructor-image-container');
                    if (imageContainer) {
                        imageContainer.innerHTML = `<img src="${instructorImage}" alt="강사 이미지" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">`;
                    }
                }
            }
        });
    }
    
    // 기존 이미지 정보 표시
    if (imagesData && imagesData.length > 0) {
        const previewContainer = document.getElementById('event-image-preview');
        imagesData.forEach((image, index) => {
            const previewItem = document.createElement('div');
            previewItem.className = 'image-preview-item';
            previewItem.innerHTML = `
                <img src="${image.image_path || image.url}" alt="${image.alt_text || ''}">
                <button type="button" class="remove-image" onclick="removeExistingImage(this, ${image.id})">
                    <i class="fas fa-times"></i>
                </button>
                <input type="hidden" name="existing_images[]" value="${image.id}">
            `;
            previewContainer.appendChild(previewItem);
        });
    }
}

// 기존 이미지 삭제 함수
async function removeExistingImage(button, imageId) {
    if (await Modal.confirm('이 이미지를 삭제하시겠습니까?', { type: 'danger' })) {
        const parentElement = button.parentElement;
        
        // 삭제할 이미지 ID를 추가 (서버에서 처리용)
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'remove_images[]';
        hiddenInput.value = imageId;
        document.getElementById('eventForm').appendChild(hiddenInput);
        
        // existing_images[]에서 해당 ID 제거
        const existingInput = parentElement.querySelector(`input[name="existing_images[]"][value="${imageId}"]`);
        if (existingInput) {
            existingInput.remove();
        }
        
        // UI에서 이미지 제거
        parentElement.remove();
        
        console.log('🗑️ 기존 이미지 삭제 요청:', {
            imageId: imageId,
            removeInputAdded: true,
            existingInputRemoved: !!existingInput
        });
    }
}

/**
 * 지역별 근사 좌표 fallback 함수
 */
function getRegionCoordinates(address) {
    // 주소에서 지역명 추출하여 근사 좌표 반환
    const regionMap = {
        // 서울 주요 구역
        '강남': { lat: 37.5173, lng: 127.0473, region: '강남구' },
        '서초': { lat: 37.4836, lng: 127.0327, region: '서초구' },
        '송파': { lat: 37.5145, lng: 127.1060, region: '송파구' },
        '강동': { lat: 37.5301, lng: 127.1238, region: '강동구' },
        '광진': { lat: 37.5384, lng: 127.0822, region: '광진구' },
        '성동': { lat: 37.5636, lng: 127.0286, region: '성동구' },
        '용산': { lat: 37.5326, lng: 126.9900, region: '용산구' },
        '중구': { lat: 37.5638, lng: 126.9976, region: '중구' },
        '종로': { lat: 37.5735, lng: 126.9788, region: '종로구' },
        '마포': { lat: 37.5663, lng: 126.9019, region: '마포구' },
        '서대문': { lat: 37.5791, lng: 126.9368, region: '서대문구' },
        '은평': { lat: 37.6027, lng: 126.9291, region: '은평구' },
        '노원': { lat: 37.6542, lng: 127.0568, region: '노원구' },
        '도봉': { lat: 37.6688, lng: 127.0471, region: '도봉구' },
        '강북': { lat: 37.6398, lng: 127.0257, region: '강북구' },
        '성북': { lat: 37.5894, lng: 127.0167, region: '성북구' },
        '동대문': { lat: 37.5744, lng: 127.0396, region: '동대문구' },
        '중랑': { lat: 37.6063, lng: 127.0925, region: '중랑구' },
        '강서': { lat: 37.5509, lng: 126.8495, region: '강서구' },
        '양천': { lat: 37.5169, lng: 126.8664, region: '양천구' },
        '구로': { lat: 37.4954, lng: 126.8874, region: '구로구' },
        '금천': { lat: 37.4568, lng: 126.8956, region: '금천구' },
        '영등포': { lat: 37.5264, lng: 126.8962, region: '영등포구' },
        '동작': { lat: 37.5124, lng: 126.9393, region: '동작구' },
        '관악': { lat: 37.4781, lng: 126.9514, region: '관악구' },
        
        // 기타 주요 도시
        '부산': { lat: 35.1796, lng: 129.0756, region: '부산광역시' },
        '대구': { lat: 35.8714, lng: 128.6014, region: '대구광역시' },
        '인천': { lat: 37.4563, lng: 126.7052, region: '인천광역시' },
        '광주': { lat: 35.1595, lng: 126.8526, region: '광주광역시' },
        '대전': { lat: 36.3504, lng: 127.3845, region: '대전광역시' },
        '울산': { lat: 35.5384, lng: 129.3114, region: '울산광역시' },
        '세종': { lat: 36.4800, lng: 127.2890, region: '세종특별자치시' },
        
        // 경기도 주요 지역
        '수원': { lat: 37.2636, lng: 127.0286, region: '수원시' },
        '성남': { lat: 37.4201, lng: 127.1262, region: '성남시' },
        '고양': { lat: 37.6584, lng: 126.8320, region: '고양시' },
        '용인': { lat: 37.2411, lng: 127.1776, region: '용인시' },
        '부천': { lat: 37.5036, lng: 126.7660, region: '부천시' },
        '안산': { lat: 37.3219, lng: 126.8309, region: '안산시' },
        '안양': { lat: 37.3943, lng: 126.9568, region: '안양시' },
        '남양주': { lat: 37.6369, lng: 127.2166, region: '남양주시' },
        '화성': { lat: 37.1999, lng: 126.8310, region: '화성시' },
        '평택': { lat: 36.9921, lng: 127.1128, region: '평택시' },
        '의정부': { lat: 37.7381, lng: 127.0337, region: '의정부시' },
        '시흥': { lat: 37.3799, lng: 126.8030, region: '시흥시' },
        '김포': { lat: 37.6150, lng: 126.7158, region: '김포시' },
        '광명': { lat: 37.4786, lng: 126.8644, region: '광명시' },
        '광주': { lat: 37.4291, lng: 127.2550, region: '광주시' },
        '군포': { lat: 37.3616, lng: 126.9352, region: '군포시' },
        '하남': { lat: 37.5391, lng: 127.2148, region: '하남시' },
        '오산': { lat: 37.1499, lng: 127.0770, region: '오산시' },
        '이천': { lat: 37.2720, lng: 127.4350, region: '이천시' },
        '안성': { lat: 37.0078, lng: 127.2797, region: '안성시' }
    };
    
    // 주소 문자열에서 지역명 검색
    for (const [keyword, coords] of Object.entries(regionMap)) {
        if (address.includes(keyword)) {
            return coords;
        }
    }
    
    // 서울로 시작하면 서울시청 좌표 반환
    if (address.includes('서울')) {
        return { lat: 37.5665, lng: 126.9780, region: '서울특별시' };
    }
    
    // 기본값 (서울시청)
    return { lat: 37.5665, lng: 126.9780, region: '서울특별시' };
}

// 편집 모드 관련 함수들 완료
</script>
<?php endif; ?>

<!-- 디버깅용 스크립트 -->
<script>
// 🔧 v3.53.0: 동적 accept 속성 설정 (UploadConfig 시스템 사용)
if (window.getImageAcceptAttribute) {
    const eventImagesInput = document.getElementById('event_images');
    if (eventImagesInput) {
        eventImagesInput.accept = window.getImageAcceptAttribute();
        console.log('✅ 이미지 input accept 속성 동적 설정 완료:', eventImagesInput.accept);
    }
}

// 페이지 로드 시 전역 변수 및 오류 상태 확인
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔍 이벤트 생성 페이지 디버깅 정보:');
    console.log('- 편집 모드:', <?= $isEditMode ? 'true' : 'false' ?>);
    console.log('- 네이버 지도 API 키:', '<?= htmlspecialchars(NAVER_MAPS_CLIENT_ID, ENT_QUOTES, 'UTF-8') ?>');
    console.log('- CSRF 토큰 길이:', '<?= strlen($_SESSION['csrf_token']) ?>');
    
    // 중요한 DOM 요소들 존재 확인
    const requiredElements = [
        'eventForm', 'quill-editor', 'event_images', 'submit-btn',
        'venue_address', 'address_search_btn', 'coordinate_status'
    ];
    
    requiredElements.forEach(id => {
        const element = document.getElementById(id);
        if (!element) {
            console.error('❌ 필수 요소 누락:', id);
        } else {
            console.log('✅ 요소 확인됨:', id);
        }
    });
    
    // 업로드 설정 확인
    if (window.TOPMKT_UPLOAD_CONFIG) {
        console.log('✅ 업로드 설정 로드됨');
    } else {
        console.error('❌ 업로드 설정 로드 실패');
    }
    
    // Quill 에디터 상태 확인
    setTimeout(() => {
        if (typeof quill !== 'undefined' && quill) {
            console.log('✅ Quill 에디터 초기화됨');
        } else {
            console.error('❌ Quill 에디터 초기화 실패');
        }
    }, 1000);
});

// 전역 오류 핸들러
window.addEventListener('error', function(e) {
    console.error('🚨 JavaScript 오류 감지:', {
        message: e.message,
        filename: e.filename,
        line: e.lineno,
        column: e.colno,
        stack: e.error ? e.error.stack : 'Stack not available'
    });
});

// Promise rejection 핸들러
window.addEventListener('unhandledrejection', function(e) {
    console.error('🚨 Promise 거부 감지:', e.reason);
});
</script>