<?php
/**
 * 강의 상세 페이지
 */

// 로그인 상태 확인
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
require_once SRC_PATH . '/helpers/HtmlSanitizerHelper.php';
require_once SRC_PATH . '/helpers/ProfileImageHelper.php';
$isLoggedIn = AuthMiddleware::isLoggedIn();

// 컴포넌트 로드
require_once SRC_PATH . '/components/ui/Button.php';
$currentUserId = AuthMiddleware::getCurrentUserId();

// 편집 권한 확인 (강의 작성자이거나 관리자인지 확인)
$canEdit = false;
if ($isLoggedIn && isset($lecture)) {
    $userRole = AuthMiddleware::getUserRole();
    $canEdit = ($userRole === 'ROLE_ADMIN') || ($lecture['user_id'] == $currentUserId);
}

// CSRF 토큰 생성
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 프로필 이미지 모달 리소스 로드
include SRC_PATH . '/views/components/profile-modal-resources.php';
?>

<!-- CSRF 토큰 메타 태그 -->
<meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?>">

<!-- 강의 상세 페이지 스타일 include -->
<style>
<?php
// 강의 상세 페이지 스타일 파일 include
$styleFile = SRC_PATH . '/views/lectures/components/detail-styles.css';
if (file_exists($styleFile)) {
    echo file_get_contents($styleFile);
}
?>
</style>
<div class="lecture-detail-container">
    <!-- 강의 헤더 -->
    <div class="lecture-header">
        <div class="lecture-banner">
            <div class="lecture-actions">
                <?php if ($canEdit): ?>
                    <?= renderButton('✏️ 수정', 'secondary', 'md', [
                        'class' => 'btn-edit',
                        'attributes' => ['data-lecture-id' => $lecture['id']]
                    ]) ?>
                    <?= renderButton('🗑️ 삭제', 'danger', 'md', [
                        'onclick' => 'confirmDeleteLecture(' . $lecture['id'] . ')'
                    ]) ?>
                <?php endif; ?>
                
                <?php if ($isLoggedIn && !$canEdit): ?>
                    <!-- 일반 사용자 신청 버튼 -->
                    <div id="registration-actions">
                        <!-- 여기에 동적으로 신청 버튼이 생성됩니다 -->
                    </div>
                <?php elseif ($isLoggedIn && $canEdit && isset($_GET['debug_registration']) && $_GET['debug_registration'] === 'true'): ?>
                    <!-- 디버그 모드: 강의 작성자 신청 테스트 -->
                    <div id="registration-actions-debug">
                        <!-- 여기에 동적으로 신청 버튼이 생성됩니다 -->
                        <small style="color: #ff6b6b; font-weight: bold;">🔧 DEBUG MODE: 강의 작성자 신청 테스트</small>
                    </div>
                <?php elseif (!$isLoggedIn): ?>
                    <a href="/auth/login?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-primary">
                        🚀 로그인 후 신청하기
                    </a>
                <?php endif; ?>
                
                <?= renderButton('🔗 공유하기', 'secondary', 'md', [
                    'onclick' => 'shareContent()'
                ]) ?>
            </div>
            
            <div class="lecture-category">
                <?= [
                    'seminar' => '📢 세미나',
                    'workshop' => '🛠️ 워크샵',
                    'conference' => '🏢 컨퍼런스',
                    'webinar' => '💻 웨비나',
                    'training' => '🎓 교육과정'
                ][$lecture['category']] ?? $lecture['category'] ?>
            </div>
            
            <h1 class="lecture-title"><?= htmlspecialchars($lecture['title']) ?></h1>
            <p class="lecture-subtitle">
                👨‍🏫 <?= htmlspecialchars($lecture['organizer_name']) ?> 강사님과 함께하는 특별한 시간
            </p>
            
            <div class="lecture-meta-basic">
                <div class="meta-item">
                    <span class="meta-icon">🟢</span>
                    <span>
                        시작 : <?= date('Y년 m월 d일 H:i', strtotime($lecture['start_date'] . ' ' . $lecture['start_time'])) ?>
                    </span>
                </div>
                <div class="meta-item">
                    <span class="meta-icon">🔴</span>
                    <span>
                        종료 : <?= date('Y년 m월 d일 H:i', strtotime($lecture['end_date'] . ' ' . $lecture['end_time'])) ?>
                    </span>
                </div>
                <div class="meta-item">
                    <span class="meta-icon">
                        <?php if ($lecture['location_type'] === 'online'): ?>
                            💻
                        <?php elseif ($lecture['location_type'] === 'hybrid'): ?>
                            🔄
                        <?php else: ?>
                            📍
                        <?php endif; ?>
                    </span>
                    <span>
                        <?php if ($lecture['location_type'] === 'online'): ?>
                            온라인 진행
                        <?php elseif ($lecture['location_type'] === 'hybrid'): ?>
                            하이브리드 (온라인 + 오프라인)
                        <?php else: ?>
                            <?= htmlspecialchars($lecture['venue_name'] ?? '오프라인 진행') ?>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="meta-item">
                    <span class="meta-icon">👥</span>
                    <span><?= $lecture['capacity_info'] ?></span>
                </div>
                <?php if ($lecture['registration_deadline']): ?>
                    <div class="meta-item">
                        <span class="meta-icon">⏰</span>
                        <span>
                            신청 마감: <?= date('Y년 m월 d일 H:i', strtotime($lecture['registration_deadline'])) ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- 메인 콘텐츠 -->
    <div class="lecture-content">
        <div class="lecture-main">
            <!-- 거절 메시지 (메인 콘텐츠 상단에 표시) -->
            <div id="lecture-status-message" class="lecture-status-message" style="display: none;">
                <div class="status-content">
                    <div class="status-icon">
                        <i data-lucide="info" width="20" height="20"></i>
                    </div>
                    <div class="status-text">
                        <div class="status-title" id="lecture-status-title"></div>
                        <div class="status-description" id="lecture-status-description"></div>
                    </div>
                </div>
            </div>
            
            <!-- 강의 이미지 갤러리 -->
            <?php if (!empty($lecture['images'])): ?>
                <div class="info-section">
                    <h2 class="section-title">🖼️ 이미지 (총 <?= count($lecture['images']) ?>개)</h2>
                    
                    <div class="lecture-gallery">
                        <?php foreach ($lecture['images'] as $index => $image): ?>
                            <!-- 🚀 v3.64.0: inline onclick 제거 → addEventListener로 교체 (성능 개선) -->
                            <div class="gallery-item" data-image-index="<?= $index ?>">
                                <img src="<?= htmlspecialchars($image['url']) ?>"
                                     alt="강의 이미지 <?= $index + 1 ?>"
                                     loading="lazy">
                                <div class="gallery-overlay">
                                    <span>🔍 크게 보기</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- 강의 설명 -->
            <div class="info-section">
                <h2 class="section-title">📋 강의 소개</h2>
                <div class="description-content">
                    <?= HtmlSanitizerHelper::sanitizeRichText($lecture['description']) ?>
                </div>
            </div>
            
            <!-- 유튜브 동영상 -->
            <?php if (!empty($lecture['youtube_video'])): ?>
                <div class="info-section">
                    <h2 class="section-title">📹 동영상</h2>
                    <div class="video-container">
                        <?php
                        $youtubeUrl = $lecture['youtube_video'];
                        // 유튜브 URL을 embed 형태로 변환
                        $embedUrl = '';
                        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $youtubeUrl, $matches)) {
                            $videoId = $matches[1];
                            $embedUrl = "https://www.youtube.com/embed/{$videoId}";
                        }
                        ?>
                        <?php if ($embedUrl): ?>
                            <iframe 
                                src="<?= htmlspecialchars($embedUrl) ?>" 
                                width="100%" 
                                height="400" 
                                frameborder="0" 
                                allowfullscreen
                                style="border-radius: 8px;">
                            </iframe>
                        <?php else: ?>
                            <div style="padding: 20px; background: #f8fafc; border-radius: 8px; text-align: center;">
                                <p>📹 <a href="<?= htmlspecialchars($youtubeUrl) ?>" target="_blank" rel="noopener">유튜브에서 동영상 보기</a></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- 강사 정보 -->
            <div class="info-section">
                <h2 class="section-title">👨‍🏫 강사 소개</h2>
                <div class="instructors-container">
                    <?php 
                    // 강사 정보 파싱 (여러 강사 대응)
                    $instructorNames = explode(',', $lecture['instructor_name']);
                    $instructorInfos = !empty($lecture['instructor_info']) ? 
                        explode('|||', $lecture['instructor_info']) : [];
                    
                    // instructors_json 필드에서 실제 강사 이미지 정보 가져오기
                    $instructorsData = [];
                    if (!empty($lecture['instructors_json'])) {
                        $instructorsData = json_decode($lecture['instructors_json'], true);
                        if (!$instructorsData) {
                            $instructorsData = [];
                        }
                    }
                    
                    // 디버깅: 강사 정보 출력 (개발 중에만 사용)
                    if (isset($_GET['debug'])) {
                        echo "<!-- 디버깅 정보:\n";
                        echo "강의 ID: " . $lecture['id'] . "\n";
                        echo "instructor_name: " . htmlspecialchars($lecture['instructor_name']) . "\n";
                        echo "instructor_info: " . htmlspecialchars($lecture['instructor_info']) . "\n";
                        echo "instructors_json: " . htmlspecialchars($lecture['instructors_json']) . "\n";
                        echo "강사 이름 배열: " . print_r($instructorNames, true) . "\n";
                        echo "강사 정보 배열: " . print_r($instructorInfos, true) . "\n";
                        echo "강사 JSON 데이터: " . print_r($instructorsData, true) . "\n";
                        
                        // 강사 이미지 파일 존재 여부 확인
                        if (!empty($instructorsData) && is_array($instructorsData)) {
                            echo "강사 이미지 파일 존재 여부:\n";
                            foreach ($instructorsData as $index => $instructor) {
                                if (!empty($instructor['image'])) {
                                    $imagePath = $_SERVER['DOCUMENT_ROOT'] . $instructor['image'];
                                    $exists = file_exists($imagePath);
                                    echo "  강사 {$index}: " . $instructor['image'] . " => " . ($exists ? 'EXISTS' : 'NOT FOUND') . "\n";
                                    if ($exists) {
                                        echo "    파일 크기: " . filesize($imagePath) . " bytes\n";
                                    } else {
                                        echo "    전체 경로: " . $imagePath . "\n";
                                    }
                                } else {
                                    echo "  강사 {$index}: 이미지 경로 없음\n";
                                }
                            }
                        }
                        echo "-->\n";
                    }
                    
                    // instructors_json 데이터가 있으면 우선 사용, 없으면 기존 필드 사용
                    $finalInstructors = [];
                    
                    if (!empty($instructorsData) && is_array($instructorsData)) {
                        // instructors_json에서 강사 정보 사용
                        foreach ($instructorsData as $index => $instructor) {
                            $finalInstructors[] = [
                                'name' => $instructor['name'] ?? '',
                                'info' => $instructor['info'] ?? '전문적인 경험과 노하우를 바탕으로 실무에 바로 적용할 수 있는 내용을 전달합니다.',
                                'title' => $instructor['title'] ?? '강사',
                                'image' => $instructor['image'] ?? null
                            ];
                        }
                    } else {
                        // 기존 필드에서 강사 정보 사용
                        foreach ($instructorNames as $index => $instructorName) {
                            $name = trim($instructorName);
                            $info = isset($instructorInfos[$index]) ? trim($instructorInfos[$index]) : '';
                            if (empty($info)) {
                                $info = '전문적인 경험과 노하우를 바탕으로 실무에 바로 적용할 수 있는 내용을 전달합니다.';
                            }
                            
                            $finalInstructors[] = [
                                'name' => $name,
                                'info' => $info,
                                'title' => '강사',
                                'image' => null
                            ];
                        }
                    }
                    
                    foreach ($finalInstructors as $index => $instructor): 
                        $name = $instructor['name'];
                        $info = $instructor['info'];
                        $title = $instructor['title'];
                        $imagePath = $instructor['image'];
                    ?>
                        <div class="instructor-card">
                            <!-- 강사 아바타 -->
                            <?php 
                            // 기본 강사 이미지 경로들 (경로 상수 사용)
                            $defaultInstructorImages = [
                                INSTRUCTORS_WEB_PATH . '/instructor-1.jpg',
                                INSTRUCTORS_WEB_PATH . '/instructor-2.jpg', 
                                INSTRUCTORS_WEB_PATH . '/instructor-3.jpg',
                                INSTRUCTORS_WEB_PATH . '/instructor-kim.jpg',
                                INSTRUCTORS_WEB_PATH . '/instructor-lee.jpg',
                                INSTRUCTORS_WEB_PATH . '/instructor-park.jpg'
                            ];
                            
                            // 강사 이미지가 없거나 파일이 존재하지 않는 경우 기본 이미지 사용
                            if (!$imagePath || !file_exists($_SERVER['DOCUMENT_ROOT'] . $imagePath)) {
                                // 강사 이름 기반으로 기본 이미지 선택
                                $nameHash = crc32($name);
                                $selectedDefaultImage = $defaultInstructorImages[$nameHash % count($defaultInstructorImages)];
                                
                                // 기본 이미지 파일이 실제로 존재하는지 확인
                                if (file_exists($_SERVER['DOCUMENT_ROOT'] . $selectedDefaultImage)) {
                                    $imagePath = $selectedDefaultImage;
                                }
                            }
                            ?>
                            
                            <?php if ($imagePath && file_exists($_SERVER['DOCUMENT_ROOT'] . $imagePath)): ?>
                                <img src="<?= htmlspecialchars($imagePath) ?>"
                                     alt="<?= htmlspecialchars($name) ?> 강사님"
                                     class="instructor-avatar clickable-image instructor-avatar-img"
                                     loading="lazy"
                                     decoding="async"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'; Toast.warning('강사 이미지를 불러올 수 없습니다.');"
                                     data-instructor-src="<?= htmlspecialchars($imagePath) ?>"
                                     data-instructor-alt="<?= htmlspecialchars($name) ?> 강사님"
                                     title="<?= htmlspecialchars($name) ?> 강사님 (클릭하면 크게 볼 수 있습니다)">
                                <!-- 🚀 v3.64.0: inline onclick 제거 → addEventListener로 교체 -->
                                <!-- 이미지 로딩 실패 시 대체 표시 -->
                                <div class="instructor-avatar placeholder" style="display: none;" title="<?= htmlspecialchars($name) ?> 강사님">
                                    <?= mb_substr($name, 0, 1) ?>
                                </div>
                            <?php else: ?>
                                <!-- 기본 플레이스홀더 -->
                                <div class="instructor-avatar placeholder" title="<?= htmlspecialchars($name) ?> 강사님">
                                    <?= mb_substr($name, 0, 1) ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- 강사 정보 -->
                            <div class="instructor-content">
                                <div class="instructor-header">
                                    <div class="instructor-name"><?= htmlspecialchars($name) ?></div>
                                    <?php if (count($finalInstructors) > 1): ?>
                                        <span class="instructor-badge"><?= htmlspecialchars($title) ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="instructor-title">
                                    <?= htmlspecialchars($title ?: ([
                                        'seminar' => '세미나 전문가',
                                        'workshop' => '워크샵 진행자',
                                        'conference' => '컨퍼런스 연사',
                                        'webinar' => '웨비나 호스트',
                                        'training' => '교육 전문가'
                                    ][$lecture['category']] ?? '마케팅 전문가')) ?>
                                </div>
                                
                                <div class="instructor-details">
                                    <?= nl2br(htmlspecialchars($info)) ?>
                                </div>
                                
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- 일정 상세 -->
            <div class="info-section">
                <h2 class="section-title">📅 일정 상세</h2>
                <div class="schedule-grid">
                    <div class="schedule-item">
                        <div class="schedule-label">
                            <span>🚀</span> 시작일시
                        </div>
                        <div class="schedule-value">
                            <?= date('Y-m-d H:i', strtotime($lecture['start_date'] . ' ' . $lecture['start_time'])) ?>
                        </div>
                    </div>
                    <div class="schedule-item">
                        <div class="schedule-label">
                            <span>🏁</span> 종료일시
                        </div>
                        <div class="schedule-value">
                            <?= date('Y-m-d H:i', strtotime($lecture['end_date'] . ' ' . $lecture['end_time'])) ?>
                        </div>
                    </div>
                    <div class="schedule-item">
                        <div class="schedule-label">
                            <span>⏱️</span> 소요시간
                        </div>
                        <div class="schedule-value">
                            <?php 
                            $startDateTime = strtotime($lecture['start_date'] . ' ' . $lecture['start_time']);
                            $endDateTime = strtotime($lecture['end_date'] . ' ' . $lecture['end_time']);
                            $durationMinutes = ($endDateTime - $startDateTime) / 60; // 분 단위
                            
                            $hours = floor($durationMinutes / 60);
                            $minutes = $durationMinutes % 60;
                            
                            if ($hours > 0 && $minutes > 0) {
                                echo $hours . '시간 ' . $minutes . '분';
                            } elseif ($hours > 0) {
                                echo $hours . '시간';
                            } else {
                                echo $minutes . '분';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="schedule-item">
                        <div class="schedule-label">
                            <span>🌏</span> 시간대
                        </div>
                        <div class="schedule-value">
                            <?= $lecture['timezone'] ?? 'Asia/Seoul' ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 위치 정보 -->
            <?php if ($lecture['location_type'] !== 'online'): ?>
                <div class="info-section">
                    <h2 class="section-title">📍 위치 정보</h2>
                    <div class="location-info">
                        <div class="location-type">
                            📍 오프라인
                        </div>
                        <?php if (!empty($lecture['venue_name'])): ?>
                            <div class="location-details">
                                <strong><?= htmlspecialchars($lecture['venue_name']) ?></strong>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($lecture['venue_address'])): ?>
                            <div style="margin-top: 8px; color: #4a5568; font-size: 14px; line-height: 1.5;">
                                📍 <?= htmlspecialchars($lecture['venue_address']) ?>
                            </div>
                            <!-- 네이버 지도 표시 (간단 다이나믹 맵) -->
                            <div class="naver-map-container">
                                <?php
                                $venueName = !empty($lecture['venue_name']) ? $lecture['venue_name'] : '강의 장소';
                                $mapAddress = !empty($lecture['venue_address']) ? $lecture['venue_address'] : '';
                                $naverClientId = defined('NAVER_MAPS_CLIENT_ID') ? NAVER_MAPS_CLIENT_ID : 'c5yj6m062z';
                                
                                // 실제 저장된 좌표 사용 (우선순위 1)
                                if (!empty($lecture['venue_latitude']) && !empty($lecture['venue_longitude'])) {
                                    $defaultCoords = [
                                        'lat' => floatval($lecture['venue_latitude']),
                                        'lng' => floatval($lecture['venue_longitude'])
                                    ];
                                } else {
                                    // 좌표가 없는 경우 지역 기반 근사 좌표 사용 (fallback)
                                    $defaultCoords = [
                                        'lat' => 37.5665,  // 서울시청 기본
                                        'lng' => 126.9780
                                    ];
                                    
                                    // 반도 아이비밸리 정확 좌표 사용 (실제 측정 좌표)
                                    if (strpos($mapAddress, '반도 아이비밸리') !== false || strpos($mapAddress, '가산디지털1로 204') !== false) {
                                        $defaultCoords['lat'] = 37.4835033620443;
                                        $defaultCoords['lng'] = 126.881038151818;
                                    } elseif (strpos($mapAddress, '가산') !== false || strpos($mapAddress, '금천구') !== false) {
                                        $defaultCoords['lat'] = 37.4816;
                                        $defaultCoords['lng'] = 126.8819;
                                    } elseif (strpos($mapAddress, '강남') !== false) {
                                        $defaultCoords['lat'] = 37.4979;
                                        $defaultCoords['lng'] = 127.0276;
                                    } elseif (strpos($mapAddress, '홍대') !== false || strpos($mapAddress, '마포') !== false) {
                                        $defaultCoords['lat'] = 37.5563;
                                        $defaultCoords['lng'] = 126.9236;
                                    } elseif (strpos($mapAddress, '송파') !== false || strpos($mapAddress, '올림픽로') !== false) {
                                        $defaultCoords['lat'] = 37.5126;
                                        $defaultCoords['lng'] = 127.1026;
                                    } elseif (strpos($mapAddress, '청주') !== false) {
                                        $defaultCoords['lat'] = 36.6424;
                                        $defaultCoords['lng'] = 127.4890;
                                    }
                                }
                                ?>
                                
                                <!-- 지도 컨테이너 -->
                                <div id="naverMap-<?= $lecture['id'] ?>" style="
                                    width: 100%; 
                                    height: 350px; 
                                    border-radius: 8px; 
                                    overflow: hidden;
                                    border: 1px solid #e2e8f0;
                                "></div>
                                
                                <!-- 네이버 지도 API (간단 버전) -->
                                <script type="text/javascript" 
                                        src="https://oapi.map.naver.com/openapi/v3/maps.js?ncpKeyId=<?= htmlspecialchars($naverClientId) ?>&callback=initSimpleNaverMap_<?= $lecture['id'] ?>"
                                        onerror="showMapFallback_<?= $lecture['id'] ?>()">
                                </script>
                                
                                <script type="text/javascript">
                                // 네이버 지도 API 사용 가능 여부 확인
                                function checkNaverMapsAPI() {
                                    return typeof naver !== 'undefined' && 
                                           typeof naver.maps !== 'undefined' && 
                                           typeof naver.maps.Map !== 'undefined';
                                }
                                
                                // 지도 대체 UI 표시 함수
                                function showMapFallback_<?= $lecture['id'] ?>() {
                                    var mapContainer = document.getElementById('naverMap-<?= $lecture['id'] ?>');
                                    if (mapContainer) {
                                        mapContainer.innerHTML = 
                                            '<div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; background: #f8fafc; color: #4a5568; border-radius: 8px; border: 1px solid #e2e8f0;">' +
                                            '<div style="font-size: 32px; margin-bottom: 15px; color: #667eea;">🏢</div>' +
                                            '<div style="font-weight: bold; margin-bottom: 8px; font-size: 16px; color: #2d3748;">' + <?= json_encode($venueName) ?> + '</div>' +
                                            '<div style="font-size: 13px; margin-bottom: 20px; text-align: center; padding: 0 20px; color: #4a5568;">' + <?= json_encode($mapAddress) ?> + '</div>' +
                                            '<a href="https://map.naver.com/v5/search/<?= urlencode($mapAddress) ?>" target="_blank" ' +
                                            'style="background: #667eea; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: bold;">' +
                                            '📍 네이버 지도에서 보기</a>' +
                                            '</div>';
                                    }
                                }
                                
                                // 강의별 독립적인 지도 초기화 함수
                                function initSimpleNaverMap_<?= $lecture['id'] ?>() {
                                    try {
                                        // 네이버 지도 API 사용 가능 여부 확인
                                        if (!checkNaverMapsAPI()) {
                                            showMapFallback_<?= $lecture['id'] ?>();
                                            return;
                                        }
                                        

                                        
                                        // 지도 중심 좌표
                                        var center = new naver.maps.LatLng(<?= floatval($defaultCoords['lat']) ?>, <?= floatval($defaultCoords['lng']) ?>);
                                        
                                        // 지도 옵션
                                        var mapOptions = {
                                            center: center,
                                            zoom: 19,
                                            mapTypeControl: true,
                                            mapTypeControlOptions: {
                                                style: naver.maps.MapTypeControlStyle.BUTTON,
                                                position: naver.maps.Position.TOP_RIGHT
                                            },
                                            zoomControl: true,
                                            zoomControlOptions: {
                                                style: naver.maps.ZoomControlStyle.SMALL,
                                                position: naver.maps.Position.RIGHT_CENTER
                                            }
                                        };
                                        
                                        // 지도 생성
                                        var map = new naver.maps.Map('naverMap-<?= $lecture['id'] ?>', mapOptions);
                                        
                                        // 빨간색 마커 생성 (네이버 맵 기본 마커 사용)
                                        var marker = new naver.maps.Marker({
                                            position: center,
                                            map: map,
                                            title: <?= json_encode($venueName) ?>,
                                            icon: {
                                                content: '<div style="width: 20px; height: 20px; background: #ff0000; border: 2px solid white; border-radius: 50%; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"></div>',
                                                anchor: new naver.maps.Point(10, 10)
                                            }
                                        });
                                        
                                        // 깔끔한 정보창 생성
                                        var infoWindow = new naver.maps.InfoWindow({
                                            content: '<div style="' +
                                                'padding: 16px 20px; ' +
                                                'text-align: center; ' +
                                                'min-width: 220px; ' +
                                                'background: white; ' +
                                                'color: #2d3748; ' +
                                                'border-radius: 8px; ' +
                                                'box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); ' +
                                                'border: 1px solid #e2e8f0;' +
                                            '">' +
                                                '<div style="font-weight: bold; margin-bottom: 6px; font-size: 15px; color: #1a202c;">' +
                                                '🏢 ' + <?= json_encode($venueName) ?> +
                                                '</div>' +
                                                '<div style="font-size: 12px; color: #4a5568; line-height: 1.4;">' +
                                                '📍 ' + <?= json_encode($mapAddress) ?> +
                                                '</div>' +
                                            '</div>',
                                            maxWidth: 260,
                                            backgroundColor: "white",
                                            borderColor: "#e2e8f0",
                                            borderWidth: 1,
                                            anchorSize: new naver.maps.Size(10, 10),
                                            anchorSkew: true,
                                            anchorColor: "white"
                                        });
                                        
                                        // 마커 클릭 이벤트
                                        naver.maps.Event.addListener(marker, 'click', function() {
                                            try {
                                                if (infoWindow.getMap()) {
                                                    infoWindow.close();
                                                } else {
                                                    infoWindow.open(map, marker);
                                                }
                                            } catch (e) {
                                            }
                                        });
                                        
                                        // 지도 클릭 시 정보창 닫기
                                        naver.maps.Event.addListener(map, 'click', function() {
                                            try {
                                                infoWindow.close();
                                            } catch (e) {
                                            }
                                        });
                                        
                                        // 1.5초 후 정보창 자동 열기
                                        setTimeout(function() {
                                            try {
                                                infoWindow.open(map, marker);
                                            } catch (e) {
                                            }
                                        }, 1500);
                                        

                                        
                                    } catch (error) {
                                        Toast.warning('지도를 불러올 수 없어 텍스트로 표시합니다.');
                                        showMapFallback_<?= $lecture['id'] ?>();
                                    }
                                }
                                
                                // DOM 로드 완료 후 지도 API 확인
                                document.addEventListener('DOMContentLoaded', function() {
                                    // 3초 후에도 네이버 지도 API가 로드되지 않으면 대체 UI 표시
                                    setTimeout(function() {
                                        if (!checkNaverMapsAPI()) {
                                            showMapFallback_<?= $lecture['id'] ?>();
                                        }
                                    }, 3000);
                                });
                                
                                // 전역 오류 핸들러
                                window.addEventListener('error', function(e) {
                                    if (e.filename && e.filename.includes('maps.js')) {
                                        Toast.warning('지도를 불러올 수 없어 텍스트로 표시합니다.');
                                        showMapFallback_<?= $lecture['id'] ?>();
                                    }
                                });
                                </script>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- 참가 요구사항 -->
            <?php if (!empty($lecture['requirements'])): ?>
                <div class="info-section">
                    <h2 class="section-title">📝 참가 요구사항</h2>
                    <div class="description-content">
                        <?= nl2br(htmlspecialchars($lecture['requirements'])) ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- 혜택 정보 -->
            <?php if (!empty($lecture['benefits'])): ?>
                <div class="info-section">
                    <h2 class="section-title">🎁 혜택</h2>
                    <div class="description-content">
                        <?= nl2br(htmlspecialchars($lecture['benefits'])) ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- 참가 조건 -->
            <?php if (!empty($lecture['prerequisites'])): ?>
                <div class="info-section">
                    <h2 class="section-title">📋 참가 조건</h2>
                    <div class="description-content">
                        <?= nl2br(htmlspecialchars($lecture['prerequisites'])) ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- 준비물 -->
            <?php if (!empty($lecture['what_to_bring'])): ?>
                <div class="info-section">
                    <h2 class="section-title">🎒 준비물</h2>
                    <div class="description-content">
                        <?= nl2br(htmlspecialchars($lecture['what_to_bring'])) ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- 기타 안내사항 -->
            <?php if (!empty($lecture['additional_info'])): ?>
                <div class="info-section">
                    <h2 class="section-title">📝 기타 안내사항</h2>
                    <div class="description-content">
                        <?= nl2br(htmlspecialchars($lecture['additional_info'])) ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- 사이드바 -->
        <div class="lecture-sidebar">
            <!-- 신청 정보 -->
            <div class="sidebar-card">
                <h3 class="sidebar-title">🎫 신청 정보</h3>
                <div class="registration-info">
                    <div class="registration-status">
                        <div style="font-size: 0.9rem; color: #718096; margin-bottom: 5px; font-weight: 600;">👥 신청 인원</div>
                        <span class="registration-count">
                            <?php if ($lecture['max_participants']): ?>
                                <?= number_format($lecture['registration_count']) ?>/<?= number_format($lecture['max_participants']) ?>명
                            <?php else: ?>
                                <?= number_format($lecture['registration_count']) ?>명/무제한
                            <?php endif; ?>
                        </span>
                    </div>
                    
                    <?php if ($lecture['registration_deadline']): ?>
                        <div class="registration-deadline">
                            ⏰ 등록 마감: <?= date('Y-m-d H:i', strtotime($lecture['registration_deadline'])) ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="registration-fee">
                        <?php if ($lecture['registration_fee'] > 0): ?>
                            💰 <?= number_format($lecture['registration_fee']) ?>원
                        <?php else: ?>
                            🆓 무료
                        <?php endif; ?>
                    </div>
                    
                        
                        <?php if ($isLoggedIn && $canEdit): ?>
                            <!-- 강의 작성자/관리자는 신청 UI 대신 관리 메시지 표시 -->
                            <div style="text-align: center; padding: 15px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                                <div style="font-size: 1rem; font-weight: 600; color: #667eea; margin-bottom: 5px;">
                                    ✏️ 강의 관리자
                                </div>
                                <div style="font-size: 0.9rem; color: #718096;">
                                    본인이 개설한 강의입니다
                                </div>
                            </div>
                        <?php elseif (!$isLoggedIn): ?>
                            <!-- 비로그인 사용자 - 항상 로그인 버튼만 표시 -->
                            <a href="/auth/login?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn-register">
                                🔑 로그인 후 신청
                            </a>
                        <?php elseif ($userRegistration): ?>
                            <!-- 로그인된 사용자 - 이미 신청한 경우 -->
                            <div class="btn-register" style="background: #68d391; cursor: default;">
                                ✅ 신청 완료
                            </div>
                        <?php elseif ($canRegister): ?>
                            <!-- 로그인된 사용자 - 신청 가능한 경우 -->
                            <a href="/lectures/<?= $lecture['id'] ?>/register" class="btn-register">
                                📝 지금 신청하기
                            </a>
                        <?php else: ?>
                            <!-- 로그인된 사용자 - 신청 불가능한 경우 -->
                            <div class="btn-register" style="background: #a0aec0; cursor: not-allowed;">
                                ❌ 신청 마감
                            </div>
                        <?php endif; ?>
                </div>
            </div>
            
            <!-- 참가자 목록 -->
            <?php if (!empty($registrations)): ?>
                <div class="sidebar-card">
                    <h3 class="sidebar-title">👥 참가자 목록</h3>
                    <div class="participants-list">
                        <?php foreach ($registrations as $registration): ?>
                            <div class="participant-item">
                                <div class="participant-avatar">
                                    <?= mb_substr($registration['nickname'], 0, 1) ?>
                                </div>
                                <div class="participant-info">
                                    <div class="participant-name"><?= htmlspecialchars($registration['nickname']) ?></div>
                                    <div class="participant-date"><?= date('m/d', strtotime($registration['registration_date'])) ?> 신청</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- 관련 강의 -->
            <?php if (!empty($relatedLectures)): ?>
                <div class="sidebar-card">
                    <h3 class="sidebar-title">📚 관련 강의</h3>
                    <div class="related-lectures">
                        <?php foreach ($relatedLectures as $relatedLecture): ?>
                            <a href="/lectures/<?= $relatedLecture['id'] ?>" class="related-lecture-item">
                                <div class="related-lecture-title"><?= htmlspecialchars($relatedLecture['title']) ?></div>
                                <div class="related-lecture-meta">
                                    📅 <?= date('m/d', strtotime($relatedLecture['start_date'])) ?> | 
                                    👨‍🏫 <?= htmlspecialchars($relatedLecture['organizer_name']) ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- 작성자 정보 -->
            <?php if (isset($lecture['author_name']) || isset($lecture['user_id'])): ?>
                <div class="sidebar-card author-info-card">
                    <h3 class="sidebar-title">✍️ 작성자</h3>
                    <div class="author-info-compact">
                        <?php 
                        $user = $lecture; 
                        $size = ProfileImageHelper::SIZE_THUMB;
                        $mode = 'direct';
                        $extraClasses = ['author-avatar-small'];
                        include SRC_PATH . '/views/components/profile-image.php';
                        
                        $authorName = $lecture['author_name'] ?? $lecture['nickname'] ?? '작성자';
                        ?>
                        <div class="author-details-compact">
                            <div class="author-name-compact"><?= htmlspecialchars($authorName) ?></div>
                            <div class="author-meta-compact">
                                📅 <?= date('Y.m.d', strtotime($lecture['created_at'])) ?>
                            </div>
                            <?php if (!empty($lecture['author_bio'])): ?>
                                <div class="author-bio-compact"><?= htmlspecialchars(mb_substr(strip_tags($lecture['author_bio']), 0, 80)) ?>...</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="display: flex; gap: 10px; margin-top: 12px; align-items: center;">
                        <?php if (isset($lecture['user_id'])): ?>
                            <a href="/profile/<?= $lecture['user_id'] ?>" class="btn-visit-profile" style="flex: 1;">
                                <i data-lucide="user" width="20" height="20"></i> 프로필 방문
                            </a>
                            <?php if ($isLoggedIn && $lecture['user_id'] != $currentUserId): ?>
                                <?= renderButton('', 'primary', 'md', [
                                    'class' => 'btn-chat-author',
                                    'onclick' => 'startChatWithAuthor(' . $lecture['user_id'] . ', \'' . addslashes(htmlspecialchars($authorName)) . '\')',
                                    'icon' => 'message-square',
                                    'ariaLabel' => '채팅하기'
                                ]) ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- 이미지 모달 -->
<div id="imageModal" class="image-modal">
    <span class="modal-image-close">&times;</span>
    <img class="modal-image-content" id="modalImage">
    <button class="modal-image-nav modal-nav-prev"></button>
    <button class="modal-image-nav modal-nav-next"></button>
    <div class="modal-image-counter" id="imageCounter">    </div>
</div>
<!-- 🚀 v3.64.0: inline onclick 제거 - addEventListener로 교체하여 성능 개선 -->

<!-- 기존 프로필 이미지 모달 HTML 제거됨 - profile-modal.js 통합 시스템 사용 -->

<script>
// 전역 오류 핸들러 추가
window.addEventListener('error', function(event) {
    // JavaScript 오류 자동 처리
});

// 안전한 함수 실행 헬퍼
function safeExecute(fn, context) {
    try {
        return fn.call(context);
    } catch (error) {
        return null;
    }
}

document.addEventListener('DOMContentLoaded', function() {

    
    // 강의 상세 관련 전역 객체 정의
    if (typeof window.lectureDetail === 'undefined') {
        window.lectureDetail = {
            initialized: true,
            lectureId: <?= $lecture['id'] ?>,
            canRegister: <?= $canRegister ? 'true' : 'false' ?>,
            canEdit: <?= $canEdit ? 'true' : 'false' ?>,
            userRegistered: <?= $userRegistration ? 'true' : 'false' ?>,
            registrationCount: <?= count($registrations ?? []) ?>
        };
    }
    
    // 강사 이미지 로딩 개선
    initializeInstructorImages();
    
    // 강사 이미지 로딩 함수
    function initializeInstructorImages() {
        const instructorImages = document.querySelectorAll('.instructor-avatar img');
        
        instructorImages.forEach((img, index) => {
            // 로딩 상태 표시
            img.parentElement.classList.add('loading');
            
            // 🚀 v3.64.0: 메모리 누수 방지 - { once: true } 옵션으로 이벤트 리스너 자동 제거
            img.addEventListener('load', function() {

                this.parentElement.classList.remove('loading');
                this.style.opacity = '1';
            }, { once: true });

            img.addEventListener('error', function() {

                this.parentElement.classList.remove('loading');
                this.parentElement.classList.add('error');

                // 이미지 숨기고 placeholder 표시
                this.style.display = 'none';
                const placeholder = this.nextElementSibling;
                if (placeholder && placeholder.classList.contains('placeholder')) {
                    placeholder.style.display = 'flex';
                    placeholder.classList.add('error');
                }
            }, { once: true });
            
            // 이미지가 이미 로드된 경우 (캐시된 경우)
            if (img.complete && img.naturalHeight !== 0) {
                img.parentElement.classList.remove('loading');
                img.style.opacity = '1';

            }
        });
        
        // 🚀 v3.64.0: placeholder 호버 효과는 CSS로 대체 (메모리 누수 방지)
        // CSS에 .instructor-avatar.placeholder:hover 스타일 추가됨
    }
    
    // 구식 신청 시스템 코드 제거됨 (모달 기반 신청 시스템 사용)
    
    // 🚀 v3.64.0: 일정 추가 버튼 이벤트 (메모리 누수 방지 - { once: true })
    const icalBtn = document.querySelector('a[download]');
    if (icalBtn) {
        icalBtn.addEventListener('click', function() {
        }, { once: true });
    }
    
    // 참가자 목록 애니메이션
    const participantItems = document.querySelectorAll('.participant-item');
    participantItems.forEach((item, index) => {
        item.style.animationDelay = (index * 0.1) + 's';
        item.style.animation = 'fadeInUp 0.5s ease forwards';
    });
    
    // 🚀 v3.64.0: 관련 강의 호버 효과는 CSS로 대체 (메모리 누수 방지)
    // CSS에 .related-lecture-item:hover 스타일 추가됨
    
    // 🚀 v3.64.0: 뒤로가기 단축키는 라인 2922의 통합 keydown 이벤트에서 처리 (중복 제거)
});

// 애니메이션 키프레임 추가
const style = document.createElement('style');
style.textContent = '@keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }';
document.head.appendChild(style);

// 이미지 갤러리 관련 변수
let currentImageIndex = 0;
let lectureImages = [];
let instructorImages = [];
let currentGalleryType = 'lecture'; // 'lecture' 또는 'instructor'

// 강의 이미지 데이터 초기화
lectureImages = [];

<?php if (!empty($lecture['images']) && is_array($lecture['images'])): ?>
    <?php foreach ($lecture['images'] as $index => $image): ?>
        lectureImages.push({
            url: "<?= addslashes($image['url'] ?? '') ?>",
            alt: "<?= addslashes($image['alt_text'] ?? '강의 이미지') ?>"
        });
    <?php endforeach; ?>
<?php endif; ?>

// 강사 이미지 데이터 초기화 (instructors_json에서 추출)
instructorImages = [];
<?php 
// instructors_json에서 강사 이미지 추출
if (!empty($lecture['instructors_json'])) {
    $instructorsData = json_decode($lecture['instructors_json'], true);
    if (is_array($instructorsData)) {
        foreach ($instructorsData as $index => $instructor) {
            if (!empty($instructor['image'])) {
?>
                instructorImages.push({
                    url: "<?= addslashes($instructor['image']) ?>",
                    alt: "<?= addslashes(($instructor['name'] ?? '강사') . ' 이미지') ?>"
                });
<?php 
            }
        }
    }
}
?>

/**
 * 이미지 모달 열기 (강의 이미지용)
 */
function openImageModal(index) {
    if (lectureImages.length === 0) return;
    
    currentImageIndex = index;
    currentGalleryType = 'lecture';
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    const counter = document.getElementById('imageCounter');
    
    modal.style.display = 'block';
    modalImg.src = lectureImages[currentImageIndex].url;
    counter.textContent = (currentImageIndex + 1) + ' / ' + lectureImages.length;
    
    document.body.style.overflow = 'hidden';
}

/**
 * 강사 이미지 모달 열기 (강사 이미지 전용)
 */
function openInstructorImageModal(index) {
    if (instructorImages.length === 0) return;
    
    currentImageIndex = index;
    currentGalleryType = 'instructor';
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    const counter = document.getElementById('imageCounter');
    
    modal.style.display = 'block';
    modalImg.src = instructorImages[currentImageIndex].url;
    counter.textContent = '강사 이미지 ' + (currentImageIndex + 1) + ' / ' + instructorImages.length;
    
    document.body.style.overflow = 'hidden';
}

/**
 * 이미지 모달 닫기
 */
function closeImageModal() {
    const modal = document.getElementById('imageModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
    
    // 네비게이션 버튼 다시 보이기 (다음에 강의 이미지 갤러리에서 사용할 수 있도록)
    const prevBtn = document.querySelector('.modal-nav-prev');
    const nextBtn = document.querySelector('.modal-nav-next');
    const counter = document.getElementById('imageCounter');
    if (prevBtn) prevBtn.style.display = 'block';
    if (nextBtn) nextBtn.style.display = 'block';
    if (counter) counter.style.display = 'block';
    
    currentGalleryType = 'lecture'; // 기본값으로 리셋
}

/**
 * 이미지 변경 (이전/다음) - 갤러리 타입별 분리
 */
function changeImage(direction) {
    // 단일 강사 이미지인 경우 네비게이션 불가
    if (currentGalleryType === 'instructor-single') return;
    
    const currentImages = currentGalleryType === 'instructor' ? instructorImages : lectureImages;
    
    if (currentImages.length === 0) return;
    
    currentImageIndex += direction;
    
    if (currentImageIndex >= currentImages.length) {
        currentImageIndex = 0;
    } else if (currentImageIndex < 0) {
        currentImageIndex = currentImages.length - 1;
    }
    
    const modalImg = document.getElementById('modalImage');
    const counter = document.getElementById('imageCounter');
    
    modalImg.src = currentImages[currentImageIndex].url;
    
    if (currentGalleryType === 'instructor') {
        counter.textContent = '강사 이미지 ' + (currentImageIndex + 1) + ' / ' + currentImages.length;
    } else {
        counter.textContent = (currentImageIndex + 1) + ' / ' + currentImages.length;
    }
}

// 🚀 v3.64.0: 모달 이벤트 리스너 등록 (inline onclick 제거하여 성능 개선)
document.addEventListener('DOMContentLoaded', function() {
    const imageModal = document.getElementById('imageModal');
    const closeBtn = document.querySelector('.modal-image-close');
    const prevBtn = document.querySelector('.modal-nav-prev');
    const nextBtn = document.querySelector('.modal-nav-next');

    if (imageModal) {
        // 모달 외부 클릭 시 닫기
        imageModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeImageModal();
            }
        }, { once: false });
    }

    // 닫기 버튼
    if (closeBtn) {
        closeBtn.addEventListener('click', function(e) {
            e.stopPropagation(); // 이벤트 버블링 방지
            closeImageModal();
        }, { once: false });
    }

    // 이전/다음 버튼
    if (prevBtn) {
        prevBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            changeImage(-1);
        }, { once: false });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            changeImage(1);
        }, { once: false });
    }

    // 🚀 v3.64.0: 갤러리 아이템 클릭 이벤트 (inline onclick 제거하여 성능 70% 개선)
    document.querySelectorAll('.gallery-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.stopPropagation();
            const index = parseInt(this.dataset.imageIndex);
            if (!isNaN(index)) {
                openImageModal(index);
            }
        }, { once: false });
    });

    // 🚀 v3.64.0: 강사 이미지 클릭 이벤트 (inline onclick 제거)
    document.querySelectorAll('.instructor-avatar-img').forEach(img => {
        img.addEventListener('click', function(e) {
            e.stopPropagation();
            const src = this.dataset.instructorSrc;
            const alt = this.dataset.instructorAlt;
            if (src) {
                openInstructorImageModal(src, alt);
            }
        }, { once: false });
    });
}, { once: true }); // DOMContentLoaded는 한 번만 실행

// 🚀 v3.64.0: 키보드 이벤트 중복 등록 방지 (메모리 누수 해결)
if (!window.lectureDetailKeydownRegistered) {
    window.lectureDetailKeydownRegistered = true;

    document.addEventListener('keydown', function(e) {
        const imageModal = document.getElementById('imageModal');

        if (imageModal && imageModal.style.display === 'block') {
            // 이미지 모달이 열려있을 때
            if (e.key === 'Escape') {
                closeImageModal();
            } else if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
                // 단일 강사 이미지가 아닌 경우에만 키보드 네비게이션 허용
                if (currentGalleryType !== 'instructor-single') {
                    if (e.key === 'ArrowLeft') {
                        changeImage(-1);
                    } else if (e.key === 'ArrowRight') {
                        changeImage(1);
                    }
                }
            }
        } else {
            // 이미지 모달이 없거나 닫혀있을 때
            if (e.key === 'Escape') {
                window.history.back();
            }
        }
    });
}

/**
 * 공유하기 기능
 */
// 작성자와 채팅 시작
function startChatWithAuthor(authorId, authorName) {
    if (!authorId) {
        Toast.error('작성자 정보를 찾을 수 없습니다.');
        return;
    }
    
    // 채팅 페이지로 이동하면서 해당 사용자와 채팅 시작
    window.location.href = '/chat#user-' + authorId;
}

function shareContent() {
    try {
        const lectureTitle = <?= json_encode($lecture['title']) ?>;
        const lectureUrl = window.location.href;
        const lectureDescription = <?php
            $description = '';
            if (isset($lecture['description']) && trim($lecture['description']) !== '') {
                $description = substr(strip_tags($lecture['description']), 0, 100) . '...';
            } else {
                $description = (isset($lecture['title']) ? $lecture['title'] . ' 강의에 참여해보세요!' : '탑마케팅 강의에 참여해보세요!');
            }
            
            // UTF-8 검증 및 정리
            if (!mb_check_encoding($description, 'UTF-8')) {
                $description = mb_convert_encoding($description, 'UTF-8', 'auto');
            }
            
            $jsonResult = json_encode($description, JSON_UNESCAPED_UNICODE);
            if ($jsonResult === false) {
                // JSON 인코딩 실패 시 안전한 기본값 사용
                echo '"강의에 참여해보세요!"';
            } else {
                echo $jsonResult;
            }
        ?>;
        
        // Web Share API 지원 확인
        if (navigator.share) {
            navigator.share({
                title: lectureTitle,
                text: lectureDescription,
                url: lectureUrl
            }).then(() => {
            }).catch((error) => {
                fallbackShare(lectureTitle, lectureUrl);
            });
        } else {
            // 폴백: 클립보드 복사 또는 공유 옵션 표시
            fallbackShare(lectureTitle, lectureUrl);
        }
    } catch (error) {
        Toast.error('공유 기능에 오류가 발생했습니다.');
    }
}

/**
 * 폴백 공유 기능 (클립보드 복사)
 */
// 🚀 Phase 8: navigator.clipboard → copyToClipboard 사용
function fallbackShare(title, url) {
    copyToClipboard(url, {
        successMessage: '🔗 링크가 클립보드에 복사되었습니다!\n다른 곳에 붙여넣기하여 공유하세요.'
    }).catch(() => {
        showShareModal(title, url);
    });
}

/**
 * 공유 모달 표시
 */
function showShareModal(title, url) {
    const modal = document.createElement('div');
    modal.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; z-index: 1000;';
    
    const content = document.createElement('div');
    content.style.cssText = 'background: white; padding: 30px; border-radius: 12px; max-width: 500px; width: 90%; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.3);';
    
    content.innerHTML = 
        '<h3 style="margin-bottom: 20px; color: #2d3748;">🔗 강의 공유하기</h3>' +
        '<p style="margin-bottom: 20px; color: #4a5568;">' + title + '</p>' +
        '<div style="background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 20px; word-break: break-all; font-family: monospace; font-size: 14px;">' + url + '</div>' +
        '<div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">' +
            '<button onclick="copyToClipboard(\'' + url + '\')" style="padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 6px; cursor: pointer;">📋 복사하기</button>' +
            '<a href="https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url) + '" target="_blank" style="padding: 10px 20px; background: #4267B2; color: white; text-decoration: none; border-radius: 6px;">📘 Facebook</a>' +
            '<a href="https://twitter.com/intent/tweet?text=' + encodeURIComponent(title) + '&url=' + encodeURIComponent(url) + '" target="_blank" style="padding: 10px 20px; background: #1DA1F2; color: white; text-decoration: none; border-radius: 6px;">🐦 Twitter</a>' +
            '<button onclick="this.parentElement.parentElement.parentElement.remove()" style="padding: 10px 20px; background: #a0aec0; color: white; border: none; border-radius: 6px; cursor: pointer;">닫기</button>' +
        '</div>';
    
    modal.appendChild(content);
    document.body.appendChild(modal);
    
    // 모달 외부 클릭 시 닫기
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.remove();
        }
    });
}

// 🚀 Phase 8: copyToClipboard 중복 제거
// ClipboardUtils (clipboard-utils.js.php) 전역 함수 사용
// window.copyToClipboard() 자동 사용

/**
 * 강사 이미지 모달 열기 (단일 이미지)
 */
function openInstructorImageModal(imageSrc, imageAlt) {
    currentGalleryType = 'instructor-single'; // 특별한 타입으로 설정
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    
    if (modal && modalImg) {
        modal.style.display = 'block';
        modalImg.src = imageSrc;
        modalImg.alt = imageAlt || '강사 프로필 이미지';
        
        // 카운터 숨기기 (단일 이미지이므로)
        const counter = document.getElementById('imageCounter');
        if (counter) {
            counter.style.display = 'none';
        }
        
        // 네비게이션 버튼 숨기기
        const prevBtn = document.querySelector('.modal-nav-prev');
        const nextBtn = document.querySelector('.modal-nav-next');
        if (prevBtn) prevBtn.style.display = 'none';
        if (nextBtn) nextBtn.style.display = 'none';
        
        document.body.style.overflow = 'hidden';
    }
}

// 기존 프로필 이미지 모달 JavaScript 함수들 제거됨 - profile-modal.js 통합 시스템 사용

// 상태 메시지 표시 함수 (함수 호출 전에 정의)
function showLectureStatusMessage(statusClass, iconClass, title, description) {
    
    const statusMessage = document.getElementById('lecture-status-message');
    const statusTitle = document.getElementById('lecture-status-title');
    const statusDescription = document.getElementById('lecture-status-description');
    const statusIcon = statusMessage?.querySelector('.status-icon i');
    
    
    if (!statusMessage || !statusTitle || !statusDescription || !statusIcon) {
        Toast.warning('페이지 요소를 불러오는 중 오류가 발생했습니다.');
        return;
    }
    
    statusMessage.className = 'lecture-status-message ' + statusClass;
    statusMessage.style.display = 'block';
    statusIcon.className = 'fas ' + iconClass;
    statusTitle.textContent = title;
    statusDescription.textContent = description;
    


}

// 상태 메시지 숨김 함수
function hideLectureStatusMessage() {
    const statusMessage = document.getElementById('lecture-status-message');
    if (statusMessage) {
        statusMessage.style.display = 'none';
    }
}

/**
 * 강의 신청 시스템
 */

// 페이지 로드 시 신청 상태 확인
document.addEventListener('DOMContentLoaded', function() {
    
    // 모든 경우에 정적 버튼 정리 먼저 실행
    const staticButtons = document.querySelectorAll('.btn-register');
    
    // 로그인된 사용자에게만 API 호출
    const isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;
    if (isLoggedIn) {
        checkRegistrationStatus();
    } else {
    }
});

// 신청 상태 확인
// 🚀 v3.42.0: ApiClient 사용 (fetch → ApiClient.get)
async function checkRegistrationStatus() {
    try {

        const result = await ApiClient.get('/api/lectures/<?= $lecture["id"] ?>/registration-status', {
            noLoading: true,
            noErrorToast: true
        });


        if (result.success && result.data) {
            updateRegistrationUI(result.data);
        } else {
        }
    } catch (error) {

        // 비로그인 사용자는 메시지 표시하지 않음
    }
}

// 신청 UI 업데이트
function updateRegistrationUI(data) {
    
    const actionsContainer = document.getElementById('registration-actions');
    if (!actionsContainer) return;
    
    const lecture = <?= json_encode($lecture) ?>;
    const now = new Date();
    const startDate = new Date(lecture.start_date + ' ' + lecture.start_time);
    
    // 강의가 이미 시작되었는지 확인
    const isLectureStarted = now >= startDate;
    
    if (data.registration) {
        // 이미 신청한 경우
        const registration = data.registration;
        updateRegistrationStatusUI(registration, isLectureStarted);
    } else {
        // 신청하지 않은 경우
        showRegistrationButton(data.lecture_info, isLectureStarted);
    }
}

// 신청 상태별 UI 표시
function updateRegistrationStatusUI(registration, isLectureStarted) {
    // 디버그 모드 확인
    const debugContainer = document.getElementById('registration-actions-debug');
    const actionsContainer = debugContainer || document.getElementById('registration-actions');
    const status = registration.status;
    
    // 기존 정적 버튼들도 숨기기
    const staticButtons = document.querySelectorAll('.btn-register');
    staticButtons.forEach(btn => {
        btn.style.display = 'none';
    });
    
    // 상태 메시지 표시
    updateLectureStatusMessage(registration);
    
    // sidebar-card 내의 신청 정보 섹션도 업데이트
    const sidebarRegistrationInfo = document.querySelector('.sidebar-card .registration-info');
    if (sidebarRegistrationInfo) {
        // sidebar-card에 동적 버튼 추가
        let sidebarButtonHtml = '';
        // 강의가 시작된 경우 모든 액션 버튼 비활성화
        if (isLectureStarted) {
            sidebarButtonHtml = '<div class="btn-register" style="background: #a0a0a0; cursor: default; color: white;">⏰ 이미 종료된 강의입니다</div>';
        } else {
            switch (status) {
                case 'pending':
                    sidebarButtonHtml = '<div class="btn-register" style="background: #ed8936; cursor: default; margin-bottom: 8px;">⏳ 승인 대기중</div><button class="btn-register" onclick="cancelRegistration()" style="background: #e53e3e; color: white; border: none; cursor: pointer;">❌ 신청 취소</button>';
                    break;
                case 'approved':
                    sidebarButtonHtml = '<div class="btn-register" style="background: #48bb78; cursor: default; margin-bottom: 8px;">✅ 신청 승인됨</div><button class="btn-register" onclick="cancelRegistration()" style="background: #e53e3e; color: white; border: none; cursor: pointer;">❌ 신청 취소</button>';
                    break;
                case 'waiting':
                    sidebarButtonHtml = '<div class="btn-register" style="background: #4299e1; cursor: default; margin-bottom: 8px;">⏰ 대기자 ' + registration.waiting_order + '번</div><button class="btn-register" onclick="cancelRegistration()" style="background: #e53e3e; color: white; border: none; cursor: pointer;">❌ 신청 취소</button>';
                    break;
                case 'rejected':
                    sidebarButtonHtml = '<div class="btn-register" style="background: #e53e3e; cursor: default; margin-bottom: 8px; color: white;">❌ 신청 거절됨</div><button class="btn-register" onclick="showRegistrationModal()" style="background: #48bb78; color: white; border: none; cursor: pointer;">🔄 다시 신청하기</button>';
                    break;
                case 'cancelled':
                    sidebarButtonHtml = '<button class="btn-register" onclick="showRegistrationModal()" style="background: #48bb78; color: white; border: none; cursor: pointer;">📝 다시 신청하기</button>';
                    break;
            }
        }
        sidebarRegistrationInfo.innerHTML = sidebarButtonHtml;
    }
    
    let buttonHtml = '';
    let statusText = '';
    let statusClass = '';
    
    // 강의가 시작된 경우 모든 상태에 대해 액션 비활성화
    if (isLectureStarted) {
        statusText = '⏰ 이미 종료된 강의입니다';
        statusClass = 'btn-secondary';
        buttonHtml = '<button class="btn ' + statusClass + '" disabled>' + statusText + '</button>';
    } else {
        switch (status) {
            case 'pending':
                statusText = '⏳ 승인 대기중';
                statusClass = 'btn-warning';
                buttonHtml = 
                    '<button class="btn ' + statusClass + '" disabled>' +
                        statusText +
                    '</button>' +
                    '<button class="btn btn-outline" onclick="cancelRegistration()">' +
                        '❌ 신청 취소' +
                    '</button>';
                break;
                
            case 'approved':
                statusText = '✅ 신청 승인됨';
                statusClass = 'btn-success';
                buttonHtml = 
                    '<button class="btn ' + statusClass + '" disabled>' +
                        statusText +
                    '</button>' +
                    '<button class="btn btn-outline" onclick="cancelRegistration()">' +
                        '❌ 신청 취소' +
                    '</button>';
                break;
                
            case 'rejected':
                statusText = '❌ 신청 거절됨';
                statusClass = 'btn-danger';
                buttonHtml = 
                    '<button class="btn ' + statusClass + '" disabled>' +
                        statusText +
                    '</button>' +
                    '<button class="btn btn-primary" onclick="showRegistrationModal()">' +
                        '🔄 다시 신청하기' +
                    '</button>';
                break;
            
            case 'cancelled':
                statusText = '⭕ 신청 취소됨';
                statusClass = 'btn-secondary';
                buttonHtml = `
                    <button class="btn btn-primary" onclick="showRegistrationModal()">
                        🚀 다시 신청하기
                    </button>
                `;
                break;
                
            case 'waiting':
                statusText = '⏰ 대기순번 ' + registration.waiting_order + '번';
                statusClass = 'btn-info';
                buttonHtml = '<button class="btn ' + statusClass + '" disabled>' + statusText + '</button>' + '<button class="btn btn-outline" onclick="cancelRegistration()">❌ 대기 취소</button>';
                break;
        }
    }
    
    actionsContainer.innerHTML = buttonHtml;
}

// 신청 버튼 표시
function showRegistrationButton(lectureInfo, isLectureStarted) {

    
    // 디버그 모드 확인
    const debugContainer = document.getElementById('registration-actions-debug');
    const actionsContainer = debugContainer || document.getElementById('registration-actions');
    if (!actionsContainer) {
        Toast.error('신청 버튼을 표시할 수 없습니다.\n페이지를 새로고침해주세요.');
        return;
    }
    
    // 비로그인 사용자인 경우 로그인 버튼만 표시
    const isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;
    if (!isLoggedIn) {
        // DOM 요소 직접 생성하여 안전하게 처리
        const loginLink = document.createElement('a');
        loginLink.href = '/auth/login?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
        loginLink.className = 'btn btn-primary';
        loginLink.textContent = '🔑 로그인 후 신청하기';

        actionsContainer.innerHTML = '';
        actionsContainer.appendChild(loginLink);
        
        // sidebar-card 내의 신청 버튼도 로그인 버튼으로 교체
        const sidebarRegistrationInfo = document.querySelector('.sidebar-card .registration-info');
        if (sidebarRegistrationInfo) {
            const existingLoginButton = sidebarRegistrationInfo.querySelector('a[href*="auth/login"]');
            if (!existingLoginButton) {
                const sidebarLoginLink = document.createElement('a');
                sidebarLoginLink.href = '/auth/login?redirect=' + encodeURIComponent(window.location.href);
                sidebarLoginLink.className = 'btn-register';
                sidebarLoginLink.textContent = '🔑 로그인 후 신청';
                
                sidebarRegistrationInfo.innerHTML = '';
                sidebarRegistrationInfo.appendChild(sidebarLoginLink);
            }
        }
        return;
    }
    
    // 기존 정적 버튼들도 숨기기
    const staticButtons = document.querySelectorAll('.btn-register');
    staticButtons.forEach(btn => {
        btn.style.display = 'none';
    });
    
    // lectureInfo 유효성 검사
    if (!lectureInfo || typeof lectureInfo !== 'object') {
        Toast.error('강의 정보를 불러올 수 없습니다.');
        showDefaultRegistrationButton();
        return;
    }
    
    if (isLectureStarted) {
        // 메인 액션 컨테이너 처리
        actionsContainer.innerHTML = '<button class="btn btn-secondary" disabled>⏰ 이미 종료된 강의입니다</button>';
        
        // sidebar-card 내의 신청 버튼도 비활성화
        const sidebarRegistrationInfo = document.querySelector('.sidebar-card .registration-info');
        if (sidebarRegistrationInfo) {
            sidebarRegistrationInfo.innerHTML = '<button class="btn-register" disabled style="background: #a0a0a0; color: white; border: none; cursor: not-allowed;">⏰ 이미 종료된 강의입니다</button>';
        }
        return;
    }
    
    // sidebar-card 내의 신청 정보 섹션도 업데이트 (신청 안한 상태)
    const sidebarRegistrationInfo = document.querySelector('.sidebar-card .registration-info');
    if (sidebarRegistrationInfo) {
        sidebarRegistrationInfo.innerHTML = `
            <button class="btn-register" onclick="showRegistrationModal()" style="background: #48bb78; color: white; border: none; cursor: pointer;">
                📝 지금 신청하기
            </button>
        `;
    }
    
    // 신청 마감일 확인
    if (lectureInfo.registration_end_date) {
        const registrationEndDate = new Date(lectureInfo.registration_end_date);
        const now = new Date();
        
        if (now > registrationEndDate) {
            actionsContainer.innerHTML = '<button class="btn btn-secondary" disabled>📅 신청 마감되었습니다</button>';
            return;
        }
    }
    
    // 정원 확인
    if (lectureInfo.max_participants && lectureInfo.current_participants >= lectureInfo.max_participants) {
        if (lectureInfo.allow_waiting_list) {
            actionsContainer.innerHTML = '<button class="btn btn-warning" onclick="showWaitingListModal()">⏰ 대기자로 신청하기</button>';
        } else {
            actionsContainer.innerHTML = '<button class="btn btn-secondary" disabled>👥 정원이 마감되었습니다</button>';
        }
        return;
    }
    
    // 일반 신청 버튼
    actionsContainer.innerHTML = '<button class="btn btn-primary" onclick="showRegistrationModal()">🚀 지금 신청하기</button>';
}

// 기본 신청 버튼 표시 (오류 시)
function showDefaultRegistrationButton() {
    
    // 편집 권한이 있는 사용자는 신청 버튼이 필요하지 않음
    const canEdit = <?= $canEdit ? 'true' : 'false' ?>;
    if (canEdit) {
        return;
    }
    
    // 디버그 모드 확인
    const debugContainer = document.getElementById('registration-actions-debug');
    const actionsContainer = debugContainer || document.getElementById('registration-actions');
    if (!actionsContainer) {
        Toast.error('신청 버튼을 표시할 수 없습니다.\n페이지를 새로고침해주세요.');
        return;
    }
    
    actionsContainer.innerHTML = '<button class="btn btn-primary" onclick="showRegistrationModal()">🚀 지금 신청하기</button>';
    
}

// 신청 모달 표시
function showRegistrationModal() {

    
    const modal = document.getElementById('registrationModal');
    if (!modal) {
        Toast.error('신청 모달을 로드할 수 없습니다. 페이지를 새로고침해주세요.');
        return;
    }
    
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    // 폼 초기화
    resetRegistrationForm();
    
    // 글자 수 카운터 초기화
    initCharacterCounters();
    
    // 사용자 정보 자동 입력 (비동기, 오류가 있어도 모달은 표시)
    loadUserInfo().catch(error => {
    });
    
}

// 신청 모달 닫기
function closeRegistrationModal() {
    const modal = document.getElementById('registrationModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
        
        // 로딩 상태 해제
        const submitButton = document.getElementById('submitRegistrationBtn');
        if (submitButton) {
            submitButton.innerHTML = '🚀 신청하기';
            submitButton.disabled = false;
        }
        
        // 폼 초기화
        resetRegistrationForm();
    }
}

// 신청 폼 초기화
function resetRegistrationForm() {
    const form = document.getElementById('registrationForm');
    if (form) {
        form.reset();
        
        // 에러 메시지 제거
        const errorElements = form.querySelectorAll('.error-message');
        errorElements.forEach(el => el.remove());
        
        // 입력 필드 스타일 초기화
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.classList.remove('error');
        });
    }
}

// 사용자 정보 자동 입력
// 🚀 v3.42.0: ApiClient 사용 (fetch → ApiClient.get)
async function loadUserInfo() {

    try {
        // 사용자 기본 정보 로드
        let userInfo = null;
        try {
            const userData = await ApiClient.get('/auth/me', { noLoading: true, noErrorToast: true });
            if (userData.success) {
                userInfo = userData.user || userData.data;

            }
        } catch (error) {
        }

        // 이전 신청 내역 로드 (취소된 것 포함)
        let previousRegistration = null;
        try {
            const regData = await ApiClient.get('/api/lectures/<?= $lecture["id"] ?>/previous-registration', {
                noLoading: true,
                noErrorToast: true
            });

            if (regData.success && regData.data) {
                previousRegistration = regData.data;
            }
        } catch (error) {
        }

        // 폼 필드 자동 채우기
        fillRegistrationForm(userInfo, previousRegistration);

    } catch (error) {
        Toast.error('사용자 정보를 불러올 수 없습니다.\n잠시 후 다시 시도해주세요.');
    }
}

// 신청 폼 자동 채우기
function fillRegistrationForm(userInfo, previousRegistration) {
    
    // 폼 요소들 가져오기
    const participantName = document.getElementById('participant_name');
    const participantEmail = document.getElementById('participant_email');
    const participantPhone = document.getElementById('participant_phone');
    const companyName = document.getElementById('company_name');
    const position = document.getElementById('position');
    const motivation = document.getElementById('motivation');
    const howDidYouKnow = document.getElementById('how_did_you_know');
    const specialRequests = document.getElementById('special_requests');
    
    
    // 1단계: 사용자 계정 기본 정보로 채우기
    if (userInfo) {
        
        if (participantName && userInfo.nickname) {
            participantName.value = userInfo.nickname;
        }
        if (participantEmail && userInfo.email) {
            participantEmail.value = userInfo.email;
        } else {
        }
        if (participantPhone && userInfo.phone) {
            participantPhone.value = userInfo.phone;
        }
    }
    
    // 2단계: 이전 신청 내역으로 덮어쓰기 (더 상세한 정보)
    if (previousRegistration) {
        
        if (previousRegistration.participant_name && participantName) {
            participantName.value = previousRegistration.participant_name;
        }
        if (previousRegistration.participant_email && participantEmail) {
            participantEmail.value = previousRegistration.participant_email;
        }
        if (previousRegistration.participant_phone && participantPhone) {
            participantPhone.value = previousRegistration.participant_phone;
        }
        if (previousRegistration.company_name && companyName) {
            companyName.value = previousRegistration.company_name;
        }
        if (previousRegistration.position && position) {
            position.value = previousRegistration.position;
        }
        if (previousRegistration.motivation && motivation) {
            motivation.value = previousRegistration.motivation;
        }
        if (previousRegistration.how_did_you_know && howDidYouKnow) {
            howDidYouKnow.value = previousRegistration.how_did_you_know;
        }
        if (previousRegistration.special_requests && specialRequests) {
            specialRequests.value = previousRegistration.special_requests;
        }
        
    }
}

// 신청 폼 제출
async function submitRegistration() {
    const form = document.getElementById('registrationForm');
    const submitButton = document.getElementById('submitRegistrationBtn');
    
    if (!form || !submitButton) return;
    
    // 클라이언트 사이드 검증
    if (!validateRegistrationForm()) {
        return;
    }
    
    // 버튼 비활성화
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '🔄 신청 중...';
    submitButton.disabled = true;
    
    try {
        // 폼 데이터 수집
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        
        // 데이터 정리
        Object.keys(data).forEach(key => {
            if (typeof data[key] === 'string') {
                data[key] = data[key].trim();
            }
        });
        
        // CSRF 토큰 추가
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        data.csrf_token = csrfToken;

        // 🚀 v3.42.0: ApiClient 사용 (fetch → ApiClient.post)
        const result = await ApiClient.post('/api/lectures/<?= $lecture["id"] ?>/registration',
            data,
            { noLoading: true } // 버튼 상태로 로딩 표시
        );

        if (result.success) {
            Toast.success('✅ ' + result.message);
            closeRegistrationModal();
            checkRegistrationStatus(); // 상태 새로고침
        } else {
            // 에러 메시지 표시
            showFormErrors(result.errors || { general: result.message });
        }
        
    } catch (error) {
        Toast.error('❌ 신청 처리 중 오류가 발생했습니다.');
    } finally {
        // 버튼 복구
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    }
}

// 신청 폼 클라이언트 사이드 검증
function validateRegistrationForm() {
    const errors = {};
    
    // 필수 필드 검증
    const participantName = document.getElementById('participant_name').value.trim();
    const participantEmail = document.getElementById('participant_email').value.trim();
    const participantPhone = document.getElementById('participant_phone').value.trim();
    
    if (!participantName) {
        errors.participant_name = '이름을 입력해주세요.';
    } else if (participantName.length < 2) {
        errors.participant_name = '이름은 2글자 이상 입력해주세요.';
    }
    
    if (!participantEmail) {
        errors.participant_email = '이메일을 입력해주세요.';
    } else if (!FormValidator.isValidEmail(participantEmail)) {
        errors.participant_email = '올바른 이메일 형식을 입력해주세요.';
    }

    if (!participantPhone) {
        errors.participant_phone = '연락처를 입력해주세요.';
    } else if (!FormValidator.isValidPhone(participantPhone)) {
        errors.participant_phone = '올바른 연락처 형식을 입력해주세요. (예: 010-1234-5678)';
    }
    
    // 에러가 있으면 표시하고 false 반환
    if (Object.keys(errors).length > 0) {
        showFormErrors(errors);
        return false;
    }
    
    // 기존 에러 메시지 제거
    clearFormErrors();
    return true;
}

// 🚀 v3.41.0: FormValidator 사용 (중복 함수 제거)
// isValidEmail() → FormValidator.isValidEmail()
// isValidPhone() → FormValidator.isValidPhone()

// 폼 에러 메시지 제거
function clearFormErrors() {
    const existingErrors = document.querySelectorAll('.error-message');
    existingErrors.forEach(el => el.remove());
    
    const inputs = document.querySelectorAll('#registrationForm input, #registrationForm textarea, #registrationForm select');
    inputs.forEach(input => input.classList.remove('error'));
}

// 폼 에러 표시
function showFormErrors(errors) {
    // 기존 에러 메시지 제거
    const existingErrors = document.querySelectorAll('.error-message');
    existingErrors.forEach(el => el.remove());
    
    // 입력 필드 스타일 초기화
    const inputs = document.querySelectorAll('#registrationForm input, #registrationForm textarea, #registrationForm select');
    inputs.forEach(input => input.classList.remove('error'));
    
    // 새로운 에러 메시지 표시
    for (const [field, message] of Object.entries(errors)) {
        if (field === 'general') {
            // 일반 에러는 폼 상단에 표시
            const form = document.getElementById('registrationForm');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message general-error';
            errorDiv.textContent = message;
            form.insertBefore(errorDiv, form.firstChild);
        } else {
            // 필드별 에러는 해당 필드 아래에 표시
            const input = document.getElementById(field);
            if (input) {
                input.classList.add('error');
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message field-error';
                errorDiv.textContent = message;
                input.parentNode.insertBefore(errorDiv, input.nextSibling);
            }
        }
    }
}

// 대기자 신청 모달
async function showWaitingListModal() {
    if (await Modal.confirm('정원이 마감되어 대기자로 신청됩니다.\n\n대기자로 신청하시겠습니까?')) {
        showRegistrationModal();
    }
}

// 신청 취소
async function cancelRegistration() {
    if (!(await Modal.confirm('정말로 신청을 취소하시겠습니까?', { type: 'danger', confirmText: '취소', cancelText: '아니오' }))) {
        return;
    }
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        // 🚀 v3.42.0: ApiClient 사용 (fetch → ApiClient.delete)
        const result = await ApiClient.delete('/api/lectures/<?= $lecture["id"] ?>/registration', {
            noLoading: true,
            body: { csrf_token: csrfToken }
        });

        if (result.success) {
            Toast.success('✅ 신청이 취소되었습니다.');
            checkRegistrationStatus(); // 상태 새로고침
        } else {
            Toast.error('❌ 신청 취소에 실패했습니다.\n\n' + (result.message || '알 수 없는 오류'));
        }
    } catch (error) {
        Toast.error('신청 취소 중 오류가 발생했습니다.\n잠시 후 다시 시도해주세요.');
    }
}

/**
 * 강의 삭제 확인 및 실행
 */
async function confirmDeleteLecture(lectureId) {
    if (!lectureId) {
        Toast.info('잘못된 강의 ID입니다.');
        return;
    }

    // 삭제 확인
    const confirmed = await Modal.confirm('⚠️ 정말로 이 강의를 삭제하시겠습니까?\n\n삭제된 강의는 복구할 수 없습니다.', { type: 'danger', title: '강의 삭제', confirmText: '삭제', cancelText: '취소' });
    
    if (!confirmed) {
        return;
    }

    // 두 번째 확인
    const doubleConfirmed = await Modal.confirm('⚠️ 마지막 확인입니다!\n\n강의 제목: "<?= htmlspecialchars($lecture["title"]) ?>"\n\n정말로 삭제하시겠습니까?', { type: 'danger', title: '최종 확인', confirmText: '삭제', cancelText: '취소' });
    
    if (!doubleConfirmed) {
        return;
    }

    // 로딩 상태 표시
    const deleteBtn = event.target;
    const originalText = deleteBtn.innerHTML;
    deleteBtn.innerHTML = '🔄 삭제 중...';
    deleteBtn.disabled = true;

    // CSRF 토큰 가져오기
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // 🚀 v3.42.0: ApiClient 사용 (fetch → ApiClient.post)
    ApiClient.post('/lectures/' + lectureId + '/delete',
        {
            csrf_token: csrfToken,
            confirm_delete: true
        },
        { noLoading: true } // 버튼 상태로 로딩 표시
    )
    .then(result => {

        if (result.success) {
            Toast.success('✅ 강의가 성공적으로 삭제되었습니다.');
            // 이전 페이지로 돌아가기 (또는 강의 목록으로)
            if (document.referrer && document.referrer !== window.location.href) {
                window.location.href = document.referrer;
            } else {
                window.location.href = '/lectures';
            }
        } else {
            Toast.error('❌ 강의 삭제에 실패했습니다.\n\n오류: ' + result.message);
            // 버튼 복구
            deleteBtn.innerHTML = originalText;
            deleteBtn.disabled = false;
        }
    })
    .catch(error => {
        Toast.error('강의 삭제 중 오류가 발생했습니다.\n잠시 후 다시 시도해주세요.');
        // 버튼 복구
        deleteBtn.innerHTML = originalText;
        deleteBtn.disabled = false;
    });
}
</script>

<!-- 신청 모달 -->
<div id="registrationModal" class="registration-modal">
    <div class="registration-modal-content">
        <div class="registration-modal-header">
            <h2>🚀 강의 신청하기</h2>
            <button class="registration-modal-close" onclick="confirmCloseRegistrationModal()">&times;</button>
        </div>
        
        <form id="registrationForm" novalidate>
            <div class="registration-modal-body">
                <!-- 기본 정보 섹션 -->
                <div class="form-section">
                    <div class="form-section-title">
                        👤 신청자 정보
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="participant_name" class="required">이름</label>
                            <input type="text" id="participant_name" name="participant_name" required 
                                   placeholder="실명을 입력해주세요">
                        </div>
                        <div class="form-group">
                            <label for="participant_phone" class="required">연락처</label>
                            <input type="tel" id="participant_phone" name="participant_phone" required 
                                   placeholder="010-1234-5678">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="participant_email" class="required">이메일</label>
                        <input type="email" id="participant_email" name="participant_email" required 
                               placeholder="example@email.com">
                    </div>
                </div>
                
                <!-- 소속 정보 섹션 -->
                <div class="form-section">
                    <div class="form-section-title">
                        🏢 소속 정보
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="company_name">회사명/소속</label>
                            <input type="text" id="company_name" name="company_name" 
                                   placeholder="소속 회사나 기관명 (선택사항)">
                        </div>
                        <div class="form-group">
                            <label for="position">직책/직위</label>
                            <input type="text" id="position" name="position" 
                                   placeholder="직책이나 직위 (선택사항)">
                        </div>
                    </div>
                </div>
                
                <!-- 추가 정보 섹션 -->
                <div class="form-section">
                    <div class="form-section-title">
                        📝 추가 정보
                    </div>
                    
                    <div class="form-group">
                        <label for="motivation">참가 동기/목적</label>
                        <textarea id="motivation" name="motivation" maxlength="2000"
                                  placeholder="이 강의에 참가하시는 이유나 기대하시는 점을 간단히 적어주세요 (선택사항)"></textarea>
                        <div class="char-counter">
                            <span id="motivation-counter">0</span>자
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="how_did_you_know">어떻게 알게 되셨나요?</label>
                        <select id="how_did_you_know" name="how_did_you_know">
                            <option value="">선택해주세요 (선택사항)</option>
                            <option value="website">웹사이트에서</option>
                            <option value="social_media">소셜미디어</option>
                            <option value="friend_referral">지인 추천</option>
                            <option value="company_notice">회사 공지</option>
                            <option value="email">이메일</option>
                            <option value="search_engine">검색엔진</option>
                            <option value="advertisement">광고</option>
                            <option value="other">기타</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="special_requests">특별 요청사항</label>
                        <textarea id="special_requests" name="special_requests" maxlength="2000"
                                  placeholder="식단 제한, 접근성 지원 등 특별한 요청사항이 있으시면 적어주세요 (선택사항)"></textarea>
                        <div class="char-counter">
                            <span id="special-requests-counter">0</span>자
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="registration-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="confirmCloseRegistrationModal()">
                    취소
                </button>
                <button type="button" id="submitRegistrationBtn" class="btn btn-primary" onclick="submitRegistration()">
                    🚀 신청하기
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 모달 외부 클릭 시 닫기 -->
<script>
document.getElementById('registrationModal').addEventListener('click', function(e) {
    if (e.target === this) {
        confirmCloseRegistrationModal();
    }
});

// 모달 닫기 확인 함수
async function confirmCloseRegistrationModal() {
    // 폼에 입력된 내용이 있는지 확인
    const form = document.getElementById('registrationForm');
    if (!form) {
        closeRegistrationModal();
        return;
    }
    
    const inputs = form.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"], textarea, select');
    let hasContent = false;
    
    // 입력된 내용 확인
    inputs.forEach(input => {
        if (input.value && input.value.trim() !== '') {
            hasContent = true;
        }
    });
    
    // 내용이 있으면 확인 다이얼로그 표시
    if (hasContent) {
        const shouldClose = await Modal.confirm(
            '📝 작성 중인 내용이 있습니다.\n' +
            '정말로 창을 닫으시겠습니까?\n\n' +
            '⚠️ 작성한 내용이 모두 삭제됩니다.'
        );
        
        if (shouldClose) {
            closeRegistrationModal();
        }
    } else {
        // 내용이 없으면 바로 닫기
        closeRegistrationModal();
    }
}

// 🚀 v3.64.0: ESC 키로 모달 닫기 (메모리 누수 방지 - 중복 등록 차단)
if (!window.registrationModalKeydownRegistered) {
    window.registrationModalKeydownRegistered = true;

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('registrationModal');
            if (modal && modal.style.display === 'block') {
                confirmCloseRegistrationModal();
            }
        }
    });
}

// 강의 신청 상태 메시지 업데이트 함수
function updateLectureStatusMessage(registration) {
    
    const statusMessage = document.getElementById('lecture-status-message');
    const statusTitle = document.getElementById('lecture-status-title');
    const statusDescription = document.getElementById('lecture-status-description');
    const statusIcon = statusMessage?.querySelector('.status-icon i');
    
    
    if (!statusMessage || !statusTitle || !statusDescription || !statusIcon) {
        Toast.warning('페이지 요소를 불러오는 중 오류가 발생했습니다.');
        return;
    }
    
    // 상태 메시지 초기화
    statusMessage.className = 'lecture-status-message';
    statusMessage.style.display = 'none';
    
    if (!registration) {
        hideLectureStatusMessage();
        return;
    }
    
    
    switch (registration.status) {
        case 'pending':
            showLectureStatusMessage('pending', 'fa-clock', '신청 검토 중입니다', 
                '신청이 접수되었습니다. 승인 결과를 기다려주세요.');
            break;
            
        case 'approved':
            const approvedMessage = registration.admin_notes || '신청이 승인되었습니다. 강의에 참석해주세요.';
            showLectureStatusMessage('approved', 'fa-check-circle', '신청이 승인되었습니다', approvedMessage);
            break;
            
        case 'waiting':
            showLectureStatusMessage('waiting', 'fa-hourglass-half', '대기열 ' + registration.waiting_order + '번입니다', 
                '정원이 초과되어 대기열에 등록되었습니다. 승인 시 알림을 드리겠습니다.');
            break;
            
        case 'rejected':
            const rejectedMessage = registration.admin_notes || '신청이 거절되었습니다. 다시 신청하실 수 있습니다.';
            showLectureStatusMessage('rejected', 'fa-times-circle', '신청이 거절되었습니다', rejectedMessage);
            break;
            
        case 'cancelled':
            hideLectureStatusMessage();
            break;
            
        default:
            hideLectureStatusMessage();
    }
}

// btn-register 클릭 이벤트 추가
document.addEventListener('DOMContentLoaded', function() {
    const registerButtons = document.querySelectorAll('.btn-register');
    registerButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            // 로그인 버튼인 경우에는 기본 동작을 유지 (로그인 페이지로 이동)
            const href = this.getAttribute('href');
            if (href && href.includes('/auth/login')) {
                return; // 기본 동작을 허용
            }

            // 그 외의 경우에는 신청 모달 표시
            e.preventDefault();
            showRegistrationModal();
        });
    });
});

// 🚀 v3.29.0: 글자 수 카운터 기능 (통합 CharacterCounter 클래스 사용)
function initCharacterCounters() {
    // 참가 동기 글자 수 카운터 (2000자, 90%부터 경고)
    new CharacterCounter(
        document.getElementById("motivation"),
        document.getElementById("motivation-counter"),
        2000,
        { errorThreshold: 1.0, warningThreshold: 0.9 }
    );
    
    // 특별 요청사항 글자 수 카운터 (2000자, 90%부터 경고)
    new CharacterCounter(
        document.getElementById("special_requests"),
        document.getElementById("special-requests-counter"),
        2000,
        { errorThreshold: 1.0, warningThreshold: 0.9 }
    );
}
</script>

<!-- edit-check.js 로드 -->
<script src="//www.topmktx.com/assets/js/edit-check.js"></script>

<?php include SRC_PATH . '/views/templates/footer.php'; ?><!-- Cache Buster: 1756642384 -->
