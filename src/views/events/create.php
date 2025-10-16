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

<!-- 🚀 v3.68.1: Flatpickr datetime picker 라이브러리 (간소화) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ko.js"></script>

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
                    
                    
                    updateCoordinateStatus('✅ 주소 위치가 정상적으로 설정되었습니다', true);
                    return;
                }
            }
            
            // API 실패 시 fallback 좌표 시스템
            Toast.info('정확한 좌표를 가져올 수 없어 근사 좌표를 사용합니다.');
            
            // 지역별 근사 좌표 fallback 시스템
            const regionCoordinates = getRegionCoordinates(address);
            if (regionCoordinates) {
                document.getElementById('venue_latitude').value = regionCoordinates.lat;
                document.getElementById('venue_longitude').value = regionCoordinates.lng;
                
                
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
    }).catch(error => {
        Toast.warning('지도 기능을 불러올 수 없습니다.\n주소 검색은 정상 작동합니다.');
        updateCoordinateStatus('❌ 지도 API 로딩 실패', false);
    });
});
</script>

<!-- 행사 등록 페이지 스타일 include -->
<style>
<?php
// 행사 등록 페이지 스타일 파일 include
$styleFile = SRC_PATH . '/views/events/components/event-create-styles.css';
if (file_exists($styleFile)) {
    echo file_get_contents($styleFile);
}
?>
</style>

