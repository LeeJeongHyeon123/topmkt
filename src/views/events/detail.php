<?php
/**
 * 행사 상세 페이지
 */

// 로그인 상태 확인
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
require_once SRC_PATH . '/helpers/HtmlSanitizerHelper.php';
require_once SRC_PATH . '/helpers/ProfileImageHelper.php';

// 컴포넌트 로드
require_once SRC_PATH . '/components/ui/Button.php';
require_once SRC_PATH . '/components/ui/Modal.php';

$isLoggedIn = AuthMiddleware::isLoggedIn();
$currentUserId = AuthMiddleware::getCurrentUserId();

// 편집 권한 확인 (행사 작성자이거나 관리자인지 확인)
$canEdit = false;
if ($isLoggedIn && isset($event)) {
    $userRole = AuthMiddleware::getUserRole();
    $canEdit = ($userRole === 'ROLE_ADMIN') || ($event['user_id'] == $currentUserId);
}

// CSRF 토큰 생성
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 행사 상세 페이지 스타일 include - PHP 코드 바깥으로 이동
$styleFile = SRC_PATH . '/views/events/components/event-detail-styles.css';

// 프로필 이미지 모달 리소스 로드
include SRC_PATH . '/views/components/profile-modal-resources.php';
?>

<!-- 한국어 인코딩 설정 -->
<meta charset="utf-8">

<!-- CSRF 토큰 메타 태그 -->
<meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?>">

<!-- Quill.js 에디터 CSS (리치 텍스트 표시용) -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">

<!-- 행사 상세 페이지 스타일 include - 강의 페이지와 동일한 방식 -->
<style>
<?php
if (isset($styleFile) && file_exists($styleFile)) {
    echo file_get_contents($styleFile);
}
?>
</style>

<div class="event-detail-container">
    <!-- 행사 히어로 섹션 -->
    <div class="event-hero">
        <div class="event-admin-actions">
            <?php if ($canEdit): ?>
                <?= renderButton('✏️ 수정', 'secondary', 'md', [
                    'class' => 'btn-edit',
                    'attributes' => ['data-event-id' => $event['id']]
                ]) ?>
                <?= renderButton('🗑️ 삭제', 'danger', 'md', [
                    'onclick' => 'confirmDeleteEvent(' . $event['id'] . ')'
                ]) ?>
            <?php endif; ?>
        </div>
        <div class="event-hero-content">
            <div class="event-category">
                <?php
                $categoryNames = [
                    'seminar' => '세미나',
                    'workshop' => '워크샵', 
                    'conference' => '컨퍼런스',
                    'webinar' => '웨비나',
                    'training' => '교육'
                ];
                echo $categoryNames[$event['category']] ?? '행사';
                ?>
                <?php if ($event['event_scale']): ?>
                    <?php
                    $scaleNames = ['small' => '소규모', 'medium' => '중규모', 'large' => '대규모'];
                    ?>
                    <span class="event-scale-badge <?= $event['event_scale'] ?>">
                        <?= $scaleNames[$event['event_scale']] ?>
                    </span>
                <?php endif; ?>
            </div>
            
            <h1 class="event-title">
                <?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?>
            </h1>
            
            <p class="event-subtitle">
                <?= htmlspecialchars(mb_substr(strip_tags($event['description']), 0, 100), ENT_QUOTES, 'UTF-8') ?>...
            </p>
            
            <div class="event-meta-row">
                <div class="event-meta-item">
                    <i data-lucide="calendar" width="20" height="20"></i>
                    <span><?= date('Y년 n월 j일', strtotime($event['start_date'])) ?></span>
                </div>
                <div class="event-meta-item">
                    <i data-lucide="clock" width="20" height="20"></i>
                    <span>
                        <?php
                        // 시작 시간만 표시
                        if ($event['start_time']) {
                            echo date('H:i', strtotime($event['start_time'])) . ' 시작';
                        } else {
                            echo '시간 미정';
                        }
                        ?>
                    </span>
                </div>
                <div class="event-meta-item">
                    <i data-lucide="map-pin" width="20" height="20"></i>
                    <span>
                        <?php if ($event['location_type'] === 'online'): ?>
                            온라인
                        <?php elseif ($event['location_type'] === 'hybrid'): ?>
                            하이브리드
                        <?php else: ?>
                            <?= htmlspecialchars($event['venue_name'] ?? '오프라인') ?>
                        <?php endif; ?>
                    </span>
                </div>
                <?php if (!empty($event['registration_deadline'])): ?>
                <div class="event-meta-item">
                    <i data-lucide="hourglass" width="20" height="20"></i>
                    <span>
                        <?php
                        $deadline = new DateTime($event['registration_deadline']);
                        $now = new DateTime();
                        
                        if ($now > $deadline) {
                            echo '<span style="color: #ef4444;">신청 마감</span>';
                        } else {
                            echo '신청 마감: ' . $deadline->format('n월 j일 H:i');
                        }
                        ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="event-share-actions">
                <?= renderButton('🔗 공유하기', 'secondary', 'md', [
                    'class' => 'btn-share',
                    'onclick' => 'shareEventContent()'
                ]) ?>
            </div>
        </div>
    </div>

    <!-- 메인 콘텐츠 -->
    <div class="event-content">
        <!-- 메인 영역 -->
        <div class="event-main">
            <?php if (!empty($event['youtube_video'])): ?>
            <div class="content-section">
                <h2>🎬 관련 영상</h2>
                <div class="youtube-container">
                    <?php
                    // YouTube URL을 embed 형식으로 변환
                    $youtubeUrl = $event['youtube_video'];
                    $embedUrl = $youtubeUrl;
                    
                    // 일반 YouTube URL을 embed URL로 변환
                    if (strpos($youtubeUrl, 'youtube.com/watch?v=') !== false) {
                        $videoId = preg_replace('/.*[?&]v=([^&]*).*/', '$1', $youtubeUrl);
                        $embedUrl = "https://www.youtube.com/embed/" . $videoId;
                    } elseif (strpos($youtubeUrl, 'youtu.be/') !== false) {
                        $videoId = str_replace('https://youtu.be/', '', $youtubeUrl);
                        $embedUrl = "https://www.youtube.com/embed/" . $videoId;
                    }
                    ?>
                    <iframe 
                        src="<?= htmlspecialchars($embedUrl) ?>" 
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen>
                    </iframe>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="content-section">
                <h2>행사 소개</h2>
                <div class="event-description ql-editor">
                    <?= $event['description'] ?? '' ?>
                </div>
            </div>
            
            <?php if (!empty($event['images'])): ?>
            <div class="content-section">
                <h2>🖼️ 이미지</h2>
                <div class="event-gallery">
                    <?php foreach ($event['images'] as $index => $image): ?>
                        <div class="gallery-item" onclick="openImageModal(<?= $index ?>)">
                            <img src="<?= htmlspecialchars($image['url']) ?>" 
                                 alt="<?= htmlspecialchars($image['alt_text']) ?>"
                                 loading="lazy">
                            <div class="gallery-overlay">
                                <span>🔍 크게 보기</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
        </div>

        <!-- 사이드바 -->
        <div class="event-sidebar">
            <!-- 등록 정보 -->
            <div class="info-card register-card">
                <h3><i data-lucide="ticket" width="20" height="20"></i>
                    <?php if ($event['registration_fee'] && $event['registration_fee'] > 0): ?>
                        참가 신청 비용
                    <?php else: ?>
                        참가 신청
                    <?php endif; ?>
                </h3>
                <div class="event-fee">
                    <?php if ($event['registration_fee']): ?>
                        <?= number_format($event['registration_fee']) ?>원
                    <?php else: ?>
                        무료
                    <?php endif; ?>
                </div>
                <?php if ($isLoggedIn): ?>
                    <?php
                    // 🔥 Ultra Think: 완전한 마감 조건 체크 시스템
                    $now = new DateTime();

                    // 1. 등록 마감일 확인
                    $isDeadlinePassed = false;
                    if (!empty($event['registration_deadline'])) {
                        $deadline = new DateTime($event['registration_deadline']);
                        $isDeadlinePassed = $now > $deadline;
                    }

                    // 2. 본인 행사 체크
                    $isOwnEvent = ($event['user_id'] == $currentUserId);

                    // 3. 정원 초과 체크
                    $isCapacityFull = false;
                    if ($event['max_participants'] && $event['max_participants'] > 0) {
                        $isCapacityFull = ($event['current_registration_count'] >= $event['max_participants']);
                    }

                    // 4. 행사 시작일 지남 체크
                    $isEventStarted = false;
                    if ($event['start_date'] && $event['start_time']) {
                        $eventStart = new DateTime($event['start_date'] . ' ' . $event['start_time']);
                        $isEventStarted = $now > $eventStart;
                    }

                    // 마감 여부 종합 판단
                    $cannotRegister = $isDeadlinePassed || $isOwnEvent || $isCapacityFull || $isEventStarted;
                    ?>

                    <?php if ($isOwnEvent): ?>
                        <!-- 본인 행사 신청 방지 안내 -->
                        <div class="own-event-notice" style="
                            background: linear-gradient(135deg, #fff3cd 0%, #fdf5e6 100%);
                            border: 2px solid #ffc107;
                            border-radius: 12px;
                            padding: 20px;
                            text-align: center;
                            margin: 20px 0;
                            box-shadow: 0 4px 12px rgba(255, 193, 7, 0.15);
                        ">
                            <div style="margin-bottom: 12px;">
                                <i data-lucide="user-cog" width="20" height="20" style="
                                    font-size: 2rem;
                                    color: #ffc107;
                                    margin-bottom: 8px;
                                "></i>
                            </div>
                            <h3 style="
                                color: #856404;
                                font-size: 1.3rem;
                                font-weight: 700;
                                margin: 0 0 8px 0;
                            ">본인이 등록한 행사입니다</h3>
                            <p style="
                                color: #6c757d;
                                font-size: 1rem;
                                margin: 0;
                                line-height: 1.5;
                            ">자신이 등록한 행사에는 참가 신청할 수 없습니다</p>
                        </div>
                    <?php elseif ($isCapacityFull): ?>
                        <!-- 정원 초과 안내 -->
                        <div class="capacity-full-notice" style="
                            background: linear-gradient(135deg, #f8d7da 0%, #f1c6cb 100%);
                            border: 2px solid #dc3545;
                            border-radius: 12px;
                            padding: 20px;
                            text-align: center;
                            margin: 20px 0;
                            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.15);
                        ">
                            <div style="margin-bottom: 12px;">
                                <i data-lucide="users" width="20" height="20" style="
                                    font-size: 2rem;
                                    color: #dc3545;
                                    margin-bottom: 8px;
                                "></i>
                            </div>
                            <h3 style="
                                color: #dc3545;
                                font-size: 1.3rem;
                                font-weight: 700;
                                margin: 0 0 8px 0;
                            ">정원이 마감되었습니다</h3>
                            <p style="
                                color: #6c757d;
                                font-size: 1rem;
                                margin: 0 0 12px 0;
                                line-height: 1.5;
                            ">
                                현재 <strong style="color: #dc3545;"><?= number_format($event['current_registration_count']) ?>명</strong>이 신청했습니다<br>
                                <span style="font-size: 0.9rem;">( 정원: <?= number_format($event['max_participants']) ?>명 )</span>
                            </p>
                            <div style="
                                background: #fff;
                                border-radius: 8px;
                                padding: 12px;
                                border-left: 4px solid #17a2b8;
                                margin-top: 15px;
                            ">
                                <i data-lucide="clock" width="20" height="20" style="color: #17a2b8; margin-right: 8px;"></i>
                                <span style="color: #495057; font-size: 0.95rem;">
                                    취소 발생 시 선착순으로 신청 가능합니다
                                </span>
                            </div>
                        </div>
                    <?php elseif ($isEventStarted): ?>
                        <!-- 행사 시작됨 안내 -->
                        <div class="event-started-notice" style="
                            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
                            border: 2px solid #17a2b8;
                            border-radius: 12px;
                            padding: 20px;
                            text-align: center;
                            margin: 20px 0;
                            box-shadow: 0 4px 12px rgba(23, 162, 184, 0.15);
                        ">
                            <div style="margin-bottom: 12px;">
                                <i data-lucide="play-circle" width="20" height="20" style="
                                    font-size: 2rem;
                                    color: #17a2b8;
                                    margin-bottom: 8px;
                                "></i>
                            </div>
                            <h3 style="
                                color: #17a2b8;
                                font-size: 1.3rem;
                                font-weight: 700;
                                margin: 0 0 8px 0;
                            ">행사가 이미 시작되었습니다</h3>
                            <p style="
                                color: #6c757d;
                                font-size: 1rem;
                                margin: 0;
                                line-height: 1.5;
                            ">행사 시작 후에는 참가 신청할 수 없습니다</p>
                        </div>
                    <?php elseif ($isDeadlinePassed): ?>
                        <?php
                        // 마감 시간 관련 정보 계산
                        $now = new DateTime();
                        $deadline = new DateTime($event['registration_deadline']);
                        $interval = $now->diff($deadline);

                        // 마감 후 경과 시간 계산
                        if ($interval->days > 0) {
                            $timeAgo = $interval->days . '일 전';
                        } elseif ($interval->h > 0) {
                            $timeAgo = $interval->h . '시간 전';
                        } else {
                            $timeAgo = $interval->i . '분 전';
                        }

                        // 마감 날짜 포맷팅
                        $deadlineFormatted = $deadline->format('Y년 m월 d일 H:i');
                        ?>

                        <div class="registration-deadline-notice" style="
                            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                            border: 2px solid #dc3545;
                            border-radius: 12px;
                            padding: 20px;
                            text-align: center;
                            margin: 20px 0;
                            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.15);
                        ">
                            <div style="margin-bottom: 12px;">
                                <i data-lucide="clock" width="20" height="20" style="
                                    font-size: 2rem;
                                    color: #dc3545;
                                    margin-bottom: 8px;
                                "></i>
                            </div>
                            <h3 style="
                                color: #dc3545;
                                font-size: 1.3rem;
                                font-weight: 700;
                                margin: 0 0 8px 0;
                            ">신청이 마감되었습니다</h3>
                            <p style="
                                color: #6c757d;
                                font-size: 1rem;
                                margin: 0 0 12px 0;
                                line-height: 1.5;
                            ">
                                <strong><?= $deadlineFormatted ?></strong>에 마감<br>
                                <span style="font-size: 0.9rem;">( <?= $timeAgo ?> 마감 )</span>
                            </p>
                        </div>
                    <?php elseif (!$event['allow_online_registration'] || $event['allow_online_registration'] == 0): ?>
                        <div class="no-registration-notice" style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 8px; text-align: center; color: #64748b;">
                            <i data-lucide="info" width="20" height="20" style="margin-right: 8px;"></i>
                            온라인 참가 신청을 받지 않는 행사입니다<br>
                            <small style="color: #94a3b8;">참가 문의는 주최자에게 별도 연락하세요</small>
                        </div>
                    <?php else: ?>
                        <!-- 행사 신청 상태 메시지 영역 -->
                        <div id="event-status-message" class="event-status-message" style="display: none; margin-bottom: 15px;">
                            <div class="status-content">
                                <div class="status-icon">
                                    <i data-lucide="info" width="20" height="20"></i>
                                </div>
                                <div class="status-text">
                                    <div class="status-title" id="event-status-title"></div>
                                    <div class="status-description" id="event-status-description"></div>
                                </div>
                            </div>
                        </div>
                        
                        <?= renderButton('참가 신청하기', 'primary', 'lg', [
                            'id' => 'event-register-btn',
                            'class' => 'register-btn',
                            'onclick' => 'registerEvent()',
                            'fullWidth' => true
                        ]) ?>
                        <?= renderButton('신청 취소', 'danger', 'lg', [
                            'id' => 'event-cancel-btn',
                            'class' => 'register-btn',
                            'onclick' => 'cancelEventRegistration()',
                            'attributes' => ['style' => 'display: none !important;']
                        ]) ?>
                    <?php endif; ?>
                <?php else: ?>
                    <?php if ($isDeadlinePassed): ?>
                        <?php
                        // 마감 시간 관련 정보 계산
                        $now = new DateTime();
                        $deadline = new DateTime($event['registration_deadline']);
                        $interval = $now->diff($deadline);

                        // 마감 후 경과 시간 계산
                        if ($interval->days > 0) {
                            $timeAgo = $interval->days . '일 전';
                        } elseif ($interval->h > 0) {
                            $timeAgo = $interval->h . '시간 전';
                        } else {
                            $timeAgo = $interval->i . '분 전';
                        }

                        // 마감 날짜 포맷팅
                        $deadlineFormatted = $deadline->format('Y년 m월 d일 H:i');
                        ?>

                        <div class="registration-deadline-notice" style="
                            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                            border: 2px solid #dc3545;
                            border-radius: 12px;
                            padding: 20px;
                            text-align: center;
                            margin: 20px 0;
                            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.15);
                        ">
                            <div style="margin-bottom: 12px;">
                                <i data-lucide="clock" width="20" height="20" style="
                                    font-size: 2rem;
                                    color: #dc3545;
                                    margin-bottom: 8px;
                                "></i>
                            </div>
                            <h3 style="
                                color: #dc3545;
                                font-size: 1.3rem;
                                font-weight: 700;
                                margin: 0 0 8px 0;
                            ">신청이 마감되었습니다</h3>
                            <p style="
                                color: #6c757d;
                                font-size: 1rem;
                                margin: 0 0 12px 0;
                                line-height: 1.5;
                            ">
                                <strong><?= $deadlineFormatted ?></strong>에 마감<br>
                                <span style="font-size: 0.9rem;">( <?= $timeAgo ?> 마감 )</span>
                            </p>
                        </div>
                    <?php elseif (!$event['allow_online_registration'] || $event['allow_online_registration'] == 0): ?>
                        <div class="no-registration-notice" style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 8px; text-align: center; color: #64748b;">
                            <i data-lucide="info" width="20" height="20" style="margin-right: 8px;"></i>
                            온라인 참가 신청을 받지 않는 행사입니다<br>
                            <small style="color: #94a3b8;">참가 문의는 주최자에게 별도 연락하세요</small>
                        </div>
                    <?php else: ?>
                        <?= renderButton('로그인 후 신청하기', 'primary', 'lg', [
                            'class' => 'register-btn',
                            'onclick' => 'redirectToLogin()',
                            'fullWidth' => true
                        ]) ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- 행사 정보 -->
            <div class="info-card">
                <h3><i data-lucide="info" width="20" height="20"></i> 행사 정보</h3>
                <ul class="info-list">
                    <li>
                        <span class="info-label">시작</span>
                        <span class="info-value">
                            <?php
                            $startDateTime = date('Y년 n월 j일 H:i', strtotime($event['start_date'] . ' ' . $event['start_time']));
                            echo $startDateTime;
                            ?>
                        </span>
                    </li>
                    <?php if ($event['end_date'] || $event['end_time']): ?>
                    <li>
                        <span class="info-label">종료</span>
                        <span class="info-value">
                            <?php
                            $endDate = $event['end_date'] ?: $event['start_date'];
                            $endTime = $event['end_time'] ?: $event['start_time'];
                            $endDateTime = date('Y년 n월 j일 H:i', strtotime($endDate . ' ' . $endTime));
                            echo $endDateTime;
                            ?>
                        </span>
                    </li>
                    <?php endif; ?>
                    <li>
                        <span class="info-label">장소</span>
                        <span class="info-value">
                            <?php if ($event['location_type'] === 'online'): ?>
                                온라인
                            <?php elseif ($event['location_type'] === 'hybrid'): ?>
                                하이브리드
                            <?php else: ?>
                                오프라인
                            <?php endif; ?>
                        </span>
                    </li>
                    <?php if ($event['max_participants']): ?>
                    <li>
                        <span class="info-label">신청 현황</span>
                        <span class="info-value">
                            <strong style="color: #4A90E2;"><?= number_format($event['current_registration_count'] ?? 0) ?></strong>
                            /
                            <strong><?= number_format($event['max_participants']) ?></strong>명
                            <?php
                            // 신청률 계산
                            $registrationRate = $event['max_participants'] > 0
                                ? round(($event['current_registration_count'] ?? 0) / $event['max_participants'] * 100, 1)
                                : 0;
                            ?>
                            <span style="
                                font-size: 0.85em;
                                color: <?= $registrationRate >= 80 ? '#dc3545' : ($registrationRate >= 50 ? '#ffc107' : '#28a745') ?>;
                                margin-left: 8px;
                                font-weight: 600;
                            ">
                                (<?= $registrationRate ?>%)
                            </span>
                        </span>
                    </li>
                    <?php endif; ?>
                    <?php if (!empty($event['registration_deadline'])): ?>
                    <li>
                        <span class="info-label">신청 마감</span>
                        <span class="info-value">
                            <?php
                            $deadline = new DateTime($event['registration_deadline']);
                            $now = new DateTime();
                            
                            if ($now > $deadline) {
                                echo '<span style="color: #ef4444; font-weight: 600;">마감됨</span>';
                            } else {
                                echo $deadline->format('Y년 n월 j일 H:i');
                            }
                            ?>
                        </span>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- 강사/연사 정보 -->
            <?php 
            // 다중 강사 정보 중에서 유효한 강사가 있는지 확인
            $hasValidInstructors = false;
            if (!empty($event['instructors']) && is_array($event['instructors'])) {
                foreach ($event['instructors'] as $instructor) {
                    if ((!empty($instructor['name']) && $instructor['name'] !== '미정') || 
                        (!empty($instructor['info']) && trim($instructor['info']) !== '')) {
                        $hasValidInstructors = true;
                        break;
                    }
                }
            }
            ?>
            <?php if ($hasValidInstructors): ?>
            <div class="info-card instructors-card">
                <h3><i data-lucide="users" width="20" height="20"></i> 강사/연사 정보</h3>
                <div class="instructors-list">
                    <?php foreach ($event['instructors'] as $instructor): ?>
                    <?php if ((!empty($instructor['name']) && $instructor['name'] !== '미정') || (!empty($instructor['info']) && trim($instructor['info']) !== '')): ?>
                    <div class="instructor-item">
                        <div class="instructor-header">
                            <div class="instructor-avatar">
                                <?php if (!empty($instructor['image'])): ?>
                                    <img src="<?= htmlspecialchars($instructor['image']) ?>" 
                                         alt="<?= htmlspecialchars($instructor['name']) ?>" 
                                         onclick="openInstructorImageModal('<?= htmlspecialchars($instructor['image']) ?>', '<?= htmlspecialchars($instructor['name']) ?>')"
                                         style="cursor: pointer;"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <?php endif; ?>
                                <div class="instructor-fallback" <?= !empty($instructor['image']) ? 'style="display:none;"' : '' ?>>
                                    <?= mb_substr($instructor['name'], 0, 1) ?>
                                </div>
                            </div>
                            <div class="instructor-details">
                                <div class="instructor-name"><?= htmlspecialchars($instructor['name']) ?></div>
                                <?php if (!empty($instructor['title'])): ?>
                                <div class="instructor-title"><?= htmlspecialchars($instructor['title']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if (!empty($instructor['info'])): ?>
                        <div class="instructor-bio"><?= htmlspecialchars($instructor['info']) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php elseif ((!empty($event['instructor_name']) && $event['instructor_name'] !== '미정') || (!empty($event['instructor_info']) && trim($event['instructor_info']) !== '')): ?>
            <!-- 기본 강사 정보 표시 (instructor_name, instructor_info 필드 사용) -->
            <div class="info-card instructors-card">
                <h3><i data-lucide="user" width="20" height="20"></i> 강사 정보</h3>
                <div class="instructors-list">
                    <div class="instructor-item">
                        <div class="instructor-header">
                            <div class="instructor-avatar">
                                <?php if (!empty($event['instructor_image'])): ?>
                                    <img src="<?= htmlspecialchars($event['instructor_image']) ?>" 
                                         alt="<?= htmlspecialchars($event['instructor_name'] ?: '강사') ?>" 
                                         onclick="openInstructorImageModal('<?= htmlspecialchars($event['instructor_image']) ?>', '<?= htmlspecialchars($event['instructor_name'] ?: '강사') ?>')"
                                         style="cursor: pointer;"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <?php endif; ?>
                                <div class="instructor-fallback" <?= !empty($event['instructor_image']) ? 'style="display:none;"' : '' ?>>
                                    <?= mb_substr($event['instructor_name'] ?: '강사', 0, 1) ?>
                                </div>
                            </div>
                            <div class="instructor-details">
                                <div class="instructor-name"><?= htmlspecialchars($event['instructor_name'] ?: '미정', ENT_QUOTES, 'UTF-8') ?></div>
                            </div>
                        </div>
                        <?php if (!empty($event['instructor_info'])): ?>
                        <div class="instructor-bio"><?= htmlspecialchars($event['instructor_info'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>


            <?php if ($event['venue_address'] || $event['online_link']): ?>
            <!-- 장소 정보 -->
            <div class="info-card">
                <h3><i data-lucide="map-pin" width="20" height="20"></i> 장소 안내</h3>
                <ul class="info-list">
                    <?php if ($event['venue_name']): ?>
                    <li>
                        <span class="info-label">장소명</span>
                        <span class="info-value"><?= htmlspecialchars($event['venue_name']) ?></span>
                    </li>
                    <?php endif; ?>
                    <?php if ($event['venue_address']): ?>
                    <li>
                        <span class="info-label">주소</span>
                        <span class="info-value"><?= htmlspecialchars($event['venue_address']) ?></span>
                    </li>
                    <?php endif; ?>
                    <?php if ($event['online_link'] && in_array($event['location_type'], ['online', 'hybrid'])): ?>
                    <li>
                        <span class="info-label">온라인 링크</span>
                        <span class="info-value">
                            <a href="<?= htmlspecialchars($event['online_link']) ?>" target="_blank" style="color: #4A90E2;">
                                참가 링크
                            </a>
                        </span>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <!-- 행사장 지도 (오프라인/하이브리드 행사만 표시) -->
            <?php if (in_array($event['location_type'], ['offline', 'hybrid']) && $event['venue_address']): ?>
            <div class="info-card">
                <h3><i data-lucide="map" width="20" height="20"></i> 오시는 길</h3>
                <div id="eventVenueMap" style="width: 100%; height: 300px; border-radius: 8px; margin-top: 15px;"></div>
                <div style="text-align: center; margin-top: 10px; color: #64748b; font-size: 0.9rem;">
                    지도를 드래그하여 위치를 확인하세요
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>
            
            <!-- 작성자 정보 -->
            <?php if (isset($event['user_id'])): ?>
                <div class="info-card author-info-card">
                    <h3><i data-lucide="user-cog" width="20" height="20"></i> 작성자</h3>
                    <div class="author-info-compact">
                        <?php
                        // 작성자 정보만 추출 (행사 데이터가 아닌 사용자 데이터로 변환)
                        $user = [
                            'id' => $event['user_id'], // 실제 작성자 user_id 사용
                            'nickname' => $event['author_name'] ?? $event['nickname'] ?? '작성자',
                            'profile_image' => $event['profile_image'] ?? null,
                            'profile_image_original' => $event['profile_image_original'] ?? null,
                            'profile_image_profile' => $event['profile_image_profile'] ?? null,
                            'profile_image_thumb' => $event['profile_image_thumb'] ?? null
                        ];
                        $size = ProfileImageHelper::SIZE_THUMB;
                        $mode = 'direct';
                        $extraClasses = ['author-avatar-small'];
                        include SRC_PATH . '/views/components/profile-image.php';
                        
                        $authorName = $event['author_name'] ?? $event['nickname'] ?? '작성자';
                        ?>
                        <div class="author-details-compact">
                            <div class="author-name-compact"><?= htmlspecialchars($authorName) ?></div>
                            <div class="author-meta-compact">
                                📅 <?= date('Y.m.d', strtotime($event['created_at'])) ?>
                            </div>
                            <?php if (!empty($event['author_bio'])): ?>
                                <div class="author-bio-compact"><?= htmlspecialchars(mb_substr(strip_tags($event['author_bio']), 0, 80)) ?>...</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="display: flex; gap: 10px; margin-top: 12px; align-items: center;">
                        <?php if (isset($event['user_id'])): ?>
                            <a href="/profile/<?= $event['user_id'] ?>" class="btn-visit-profile" style="flex: 1;">
                                <i data-lucide="user" width="20" height="20"></i> 프로필 방문
                            </a>
                            <?php if ($isLoggedIn && $event['user_id'] != $currentUserId): ?>
                                <?= renderButton('', 'primary', 'md', [
                                    'class' => 'btn-chat-author',
                                    'onclick' => 'startChatWithAuthor(' . $event['user_id'] . ', \'' . addslashes(htmlspecialchars($authorName)) . '\')',
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

<!-- 행사 신청 모달 -->
<?php if ($isLoggedIn): ?>
<div id="eventRegistrationModal" class="event-registration-modal" style="display: none;">
    <div class="event-modal-content">
        <div class="event-modal-header">
            <h3 class="event-modal-title">📋 행사 신청</h3>
            <button class="event-modal-close" onclick="closeEventRegistrationModal()">&times;</button>
        </div>
        <div class="event-modal-body">
            <form id="eventRegistrationForm">
                <!-- 개인 정보 섹션 -->
                <div class="form-section">
                    <h4 class="form-section-title">
                        <i data-lucide="user" width="20" height="20"></i> 개인 정보 (필수)
                    </h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="event_participant_name">이름 *</label>
                            <input type="text" id="event_participant_name" name="participant_name" required>
                        </div>
                        <div class="form-group">
                            <label for="event_participant_phone">연락처 *</label>
                            <input type="tel" id="event_participant_phone" name="participant_phone" required placeholder="010-1234-5678">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="event_participant_email">이메일 *</label>
                        <input type="email" id="event_participant_email" name="participant_email" required>
                    </div>
                </div>
                
                <!-- 소속 정보 섹션 -->
                <div class="form-section">
                    <h4 class="form-section-title">
                        <i data-lucide="building" width="20" height="20"></i> 소속 정보 (선택)
                    </h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="event_company_name">회사명/소속</label>
                            <input type="text" id="event_company_name" name="company_name" placeholder="소속 회사나 기관명 (선택사항)">
                        </div>
                        <div class="form-group">
                            <label for="event_position">직책/직위</label>
                            <input type="text" id="event_position" name="position" placeholder="직책이나 직위 (선택사항)">
                        </div>
                    </div>
                </div>
                
                <!-- 추가 정보 섹션 -->
                <div class="form-section">
                    <h4 class="form-section-title">
                        <i data-lucide="clipboard-check" width="20" height="20"></i> 추가 정보 (선택)
                    </h4>
                    <div class="form-group">
                        <label for="event_motivation">참가 동기/목적</label>
                        <textarea id="event_motivation" name="motivation" placeholder="이 행사에 참가하시는 이유나 기대하시는 점을 간단히 적어주세요 (선택사항)"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="event_how_did_you_know">어떻게 알게 되셨나요?</label>
                        <select id="event_how_did_you_know" name="how_did_you_know">
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
                        <label for="event_special_requests">특별 요청사항</label>
                        <textarea id="event_special_requests" name="special_requests" placeholder="식이 제한, 접근성 요구사항 등이 있으시면 알려주세요."></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="event-modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeEventRegistrationModal()">
                취소
            </button>
            <button type="button" class="btn btn-primary" onclick="submitEventRegistration()">
                신청하기
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- 이미지 모달 -->
<?php if (!empty($event['images'])): ?>
<div id="imageModal" class="image-modal">
    <span class="modal-close" onclick="closeImageModal()">&times;</span>
    <div class="modal-content">
        <img id="modalImage" src="" alt="">
        <button class="modal-nav modal-prev" onclick="prevImage()">
            <svg viewBox="0 0 24 24">
                <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
            </svg>
        </button>
        <button class="modal-nav modal-next" onclick="nextImage()">
            <svg viewBox="0 0 24 24">
                <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
            </svg>
        </button>
        <div class="modal-counter">
            <span id="imageCounter">1 / <?= count($event['images']) ?></span>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- 강사 이미지 모달 -->
<div id="instructorImageModal" class="instructor-image-modal">
    <span class="modal-close" onclick="closeInstructorImageModal()">&times;</span>
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="instructorModalName">강사 이미지</h3>
        </div>
        <img id="instructorModalImage" src="" alt="">
    </div>
</div>

<!-- 네이버 지도 API (행사장 위치) -->
<?php if (in_array($event['location_type'], ['offline', 'hybrid']) && $event['venue_address']): ?>
<?php
// 행사장 정보 설정
$venueName = !empty($event['venue_name']) ? $event['venue_name'] : '행사장';
$mapAddress = !empty($event['venue_address']) ? $event['venue_address'] : '';
$naverClientId = defined('NAVER_MAPS_CLIENT_ID') ? NAVER_MAPS_CLIENT_ID : 'c5yj6m062z';

// 행사장 좌표 (데이터베이스에서 가져온 실제 좌표 우선 사용)
$eventCoords = [
    'lat' => 37.5665,  // 서울시청 기본
    'lng' => 126.9780
];

// 데이터베이스에 저장된 위경도가 있으면 우선 사용
if (!empty($event['venue_latitude']) && !empty($event['venue_longitude'])) {
    $eventCoords['lat'] = floatval($event['venue_latitude']);
    $eventCoords['lng'] = floatval($event['venue_longitude']);
} else {
    // 저장된 위경도가 없으면 주소 기반으로 추정
    if (strpos($mapAddress, '반도 아이비밸리') !== false || strpos($mapAddress, '가산디지털1로 204') !== false) {
        $eventCoords['lat'] = 37.4835033620443;
        $eventCoords['lng'] = 126.881038151818;
    } elseif (strpos($mapAddress, '가산') !== false || strpos($mapAddress, '금천구') !== false) {
        $eventCoords['lat'] = 37.4816;
        $eventCoords['lng'] = 126.8819;
    } elseif (strpos($mapAddress, '강남') !== false || strpos($mapAddress, '테헤란로') !== false) {
        $eventCoords['lat'] = 37.4979;
        $eventCoords['lng'] = 127.0276;
    } elseif (strpos($mapAddress, '홍대') !== false || strpos($mapAddress, '마포') !== false) {
        $eventCoords['lat'] = 37.5563;
        $eventCoords['lng'] = 126.9236;
    }
}
?>

<script type="text/javascript" src="https://oapi.map.naver.com/openapi/v3/maps.js?ncpKeyId=<?= htmlspecialchars($naverClientId) ?>&callback=initEventVenueMap"></script>
<script>
// 네이버 지도 API 사용 가능 여부 확인
function checkNaverMapsAPI() {
    return typeof naver !== 'undefined' && 
           typeof naver.maps !== 'undefined' && 
           typeof naver.maps.Map !== 'undefined';
}

// 지도 대체 UI 표시 함수
function showEventMapFallback() {
    var mapContainer = document.getElementById('eventVenueMap');
    if (mapContainer) {
        mapContainer.innerHTML = 
            '<div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; background: #f8fafc; color: #4a5568; border-radius: 8px; border: 1px solid #e2e8f0;">' +
            '<div style="font-size: 32px; margin-bottom: 15px; color: #4A90E2;">🏢</div>' +
            '<div style="font-weight: bold; margin-bottom: 8px; font-size: 16px; color: #2d3748;"><?= addslashes($venueName) ?></div>' +
            '<div style="font-size: 13px; margin-bottom: 20px; text-align: center; padding: 0 20px; color: #4a5568;"><?= addslashes($mapAddress) ?></div>' +
            '<a href="https://map.naver.com/v5/search/<?= urlencode($mapAddress) ?>" target="_blank" ' +
            'style="background: #4A90E2; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: bold;">' +
            '📍 네이버 지도에서 보기</a>' +
            '</div>';
    }
}

// 행사장 지도 초기화 함수 (글로벌 함수로 정의)
window.initEventVenueMap = function() {
    try {
        // 네이버 지도 API 사용 가능 여부 확인
        if (!checkNaverMapsAPI()) {
            showEventMapFallback();
            return;
        }
        
        
        // 지도 중심 좌표
        var center = new naver.maps.LatLng(<?= floatval($eventCoords['lat']) ?>, <?= floatval($eventCoords['lng']) ?>);
        
        // 지도 옵션 (일반/위성 버튼 제거)
        var mapOptions = {
            center: center,
            zoom: 16,
            mapTypeControl: false,  // 일반/위성 버튼 완전 제거
            zoomControl: true,
            zoomControlOptions: {
                style: naver.maps.ZoomControlStyle.SMALL,
                position: naver.maps.Position.RIGHT_CENTER
            }
        };
        
        // 지도 생성
        var map = new naver.maps.Map('eventVenueMap', mapOptions);
        
        // 행사장 마커 생성 (파란색 테마)
        var marker = new naver.maps.Marker({
            position: center,
            map: map,
            title: '<?= addslashes($venueName) ?>',
            icon: {
                content: '<div style="width: 20px; height: 20px; background: #4A90E2; border: 2px solid white; border-radius: 50%; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"></div>',
                anchor: new naver.maps.Point(10, 10)
            }
        });
        
        // 정보창 생성
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
                '🎉 <?= addslashes($venueName) ?>' +
                '</div>' +
                '<div style="font-size: 12px; color: #4a5568; line-height: 1.4;">' +
                '📍 <?= addslashes($mapAddress) ?>' +
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
            } catch (error) {
            }
        });
        
        // 초기에 정보창 표시
        setTimeout(function() {
            try {
                infoWindow.open(map, marker);
            } catch (error) {
            }
        }, 500);
        

    } catch (error) {
        Toast.warning('지도를 불러올 수 없어 텍스트로 표시합니다.');
        showEventMapFallback();
    }
};

// API 로드 실패시 fallback
window.addEventListener('error', function(e) {
    if (e.filename && e.filename.includes('maps.js')) {
        showEventMapFallback();
    }
});

// DOM 로드 후 지도 초기화 (callback 방식이므로 자동 호출됨)
document.addEventListener('DOMContentLoaded', function() {
    // API가 callback으로 자동 호출되므로 별도 초기화 불필요
});
</script>
<?php endif; ?>

<script>
// 행사 ID 전역 변수
const eventId = <?= $event['id'] ?>;

// 조건부 워딩을 위한 변수 설정
const registrationButtonText = <?php echo json_encode(
    ($event['registration_fee'] && $event['registration_fee'] > 0) ? '참가 신청하기' : '참가 신청하기'
); ?>;

// 🔥 Ultra Think: 마감 조건 상태를 JavaScript에서 사용할 수 있도록 전달
const eventRegistrationStatus = <?php
echo json_encode([
    'canRegister' => !$cannotRegister,
    'isOwnEvent' => $isOwnEvent,
    'isCapacityFull' => $isCapacityFull,
    'isEventStarted' => $isEventStarted,
    'isDeadlinePassed' => $isDeadlinePassed,
    'currentCount' => intval($event['current_registration_count'] ?? 0),
    'maxParticipants' => $event['max_participants'] ? intval($event['max_participants']) : null,
    'organizerId' => intval($event['user_id']),
    'eventStartDateTime' => $event['start_date'] . ' ' . $event['start_time'],
    'registrationDeadline' => $event['registration_deadline'] ?? null
], JSON_UNESCAPED_UNICODE);
?>;

// 페이지 로드 시 행사 신청 상태 확인
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($isLoggedIn): ?>
        checkEventRegistrationStatus();
    <?php endif; ?>
});


// JWT 인증 헤더 생성 함수
function getAuthHeaders() {
    const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
    const headers = {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    };
    
    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }
    
    return headers;
}

// 행사 신청 상태 확인
async function checkEventRegistrationStatus() {
    try {
        // 🚀 v3.42.0: ApiClient 사용 (fetch → ApiClient.get)
        const result = await ApiClient.get(`/api/events/${eventId}/registration-status?event_id=${eventId}`, {
            noLoading: true,
            noErrorToast: true
        });

        if (result.success && result.data && result.data.registration) {
            const registration = result.data.registration;
            updateEventRegistrationUI(registration.status, registration);
        } else {
            // 신청 안함 상태로 UI 초기화
            updateEventRegistrationUI('none', null);
        }
    } catch (error) {

        // 오류 시에도 기본 상태로 초기화
        updateEventRegistrationUI('none', null);
    }
}

// 행사 신청 UI 업데이트
function updateEventRegistrationUI(status, registration) {
    const registerBtn = document.getElementById('event-register-btn');
    const cancelBtn = document.getElementById('event-cancel-btn');
    const statusMessage = document.getElementById('event-status-message');
    const statusTitle = document.getElementById('event-status-title');
    const statusDescription = document.getElementById('event-status-description');
    const statusIcon = statusMessage?.querySelector('.status-icon i');
    
    if (!registerBtn || !cancelBtn) return;
    
    // 상태 메시지 초기화
    if (statusMessage) {
        statusMessage.className = 'event-status-message';
        statusMessage.style.display = 'none';
    }
    
    switch (status) {
        case 'pending':
            registerBtn.style.display = 'none';
            cancelBtn.style.setProperty('display', 'block', 'important');
            cancelBtn.textContent = '신청 취소 (승인 대기중)';
            cancelBtn.style.background = '#dc3545';
            cancelBtn.style.color = 'white';

            // 대기 상태 메시지 표시
            showStatusMessage('pending', '🕒', '신청 검토 중입니다',
                '신청이 접수되었습니다. 승인 결과를 기다려주세요.');
            break;
            
        case 'approved':
            registerBtn.style.display = 'none';
            cancelBtn.style.setProperty('display', 'block', 'important');
            cancelBtn.textContent = '신청 취소 (승인됨)';
            cancelBtn.style.background = '#dc3545';
            cancelBtn.style.color = 'white';

            // 승인 상태 메시지 표시
            const approvedMessage = registration?.admin_notes || '신청이 승인되었습니다. 행사에 참석해주세요.';
            showStatusMessage('approved', '✅', '신청이 승인되었습니다', approvedMessage);
            break;
            
        case 'waiting':
            registerBtn.style.display = 'none';
            cancelBtn.style.setProperty('display', 'block', 'important');
            cancelBtn.textContent = `신청 취소 (대기: ${registration.waiting_order}번)`;
            cancelBtn.style.background = '#dc3545';
            cancelBtn.style.color = 'white';

            // 대기열 상태 메시지 표시
            showStatusMessage('waiting', '⏳', `대기열 ${registration.waiting_order}번입니다`,
                '정원이 초과되어 대기열에 등록되었습니다. 승인 시 알림을 드리겠습니다.');
            break;
            
        case 'rejected':
            registerBtn.style.display = 'block';
            registerBtn.textContent = '다시 신청하기';
            cancelBtn.style.setProperty('display', 'none', 'important');
            
            // 거절 상태 메시지 표시
            const rejectedMessage = registration?.admin_notes || '신청이 거절되었습니다. 다시 신청하실 수 있습니다.';
            showStatusMessage('rejected', '❌', '신청이 거절되었습니다', rejectedMessage);
            break;
            
        case 'cancelled':
            registerBtn.style.display = 'block';
            registerBtn.textContent = '다시 신청하기';
            cancelBtn.style.setProperty('display', 'none', 'important');
            hideStatusMessage();
            break;

        case 'none':
            // 신청 안함 상태 (기본 상태)
            registerBtn.style.display = 'block';
            registerBtn.textContent = '참가 신청하기';
            cancelBtn.style.setProperty('display', 'none', 'important');
            hideStatusMessage();
            break;

        default:
            registerBtn.style.display = 'block';
            registerBtn.textContent = registrationButtonText;
            cancelBtn.style.setProperty('display', 'none', 'important');
            hideStatusMessage();
    }
    
    // 상태 메시지 표시 함수
    function showStatusMessage(statusClass, iconClass, title, description) {
        if (!statusMessage || !statusTitle || !statusDescription || !statusIcon) return;
        
        statusMessage.className = `event-status-message ${statusClass}`;
        statusMessage.style.display = 'block';
        statusIcon.className = `fas ${getIconClass(iconClass)}`;
        statusTitle.textContent = title;
        statusDescription.textContent = description;
    }
    
    // 상태 메시지 숨김 함수
    function hideStatusMessage() {
        if (statusMessage) {
            statusMessage.style.display = 'none';
        }
    }
    
    // 아이콘 클래스 매핑
    function getIconClass(iconText) {
        const iconMap = {
            '🕒': 'fa-clock',
            '✅': 'fa-check-circle',
            '⏳': 'fa-hourglass-half',
            '❌': 'fa-times-circle'
        };
        return iconMap[iconText] || 'fa-info-circle';
    }
}

// 🔥 Ultra Think: 마감 조건 실시간 검증 함수
function validateEventRegistrationConditions() {
    const now = new Date();

    // 1. 본인 행사 체크
    if (eventRegistrationStatus.isOwnEvent) {
        return {
            canRegister: false,
            message: '본인이 등록한 행사에는 참가 신청할 수 없습니다.',
            type: 'own_event'
        };
    }

    // 2. 정원 초과 체크
    if (eventRegistrationStatus.maxParticipants &&
        eventRegistrationStatus.currentCount >= eventRegistrationStatus.maxParticipants) {
        return {
            canRegister: false,
            message: `정원이 마감되었습니다. (${eventRegistrationStatus.currentCount}/${eventRegistrationStatus.maxParticipants}명)`,
            type: 'capacity_full'
        };
    }

    // 3. 행사 시작일 지남 체크
    const eventStart = new Date(eventRegistrationStatus.eventStartDateTime);
    if (now > eventStart) {
        return {
            canRegister: false,
            message: '행사가 이미 시작되어 참가 신청할 수 없습니다.',
            type: 'event_started'
        };
    }

    // 4. 등록 마감일 체크
    if (eventRegistrationStatus.registrationDeadline) {
        const deadline = new Date(eventRegistrationStatus.registrationDeadline);
        if (now > deadline) {
            return {
                canRegister: false,
                message: '등록 마감일이 지나 참가 신청할 수 없습니다.',
                type: 'deadline_passed'
            };
        }
    }

    return {
        canRegister: true,
        message: '참가 신청이 가능합니다.',
        type: 'available'
    };
}

// 행사 신청 버튼 클릭
async function registerEvent() {
    try {
        // 🔥 Ultra Think: 실시간 마감 조건 검증
        const validation = validateEventRegistrationConditions();

        if (!validation.canRegister) {
            // 마감 조건에 걸린 경우 사용자에게 안내
            const alertMessages = {
                'own_event': '⚠️ 본인이 등록한 행사입니다\n\n자신이 등록한 행사에는 참가 신청할 수 없습니다.',
                'capacity_full': '🈵 정원이 마감되었습니다\n\n취소가 발생하면 선착순으로 신청 가능합니다.',
                'event_started': '⏰ 행사가 이미 시작되었습니다\n\n다른 진행 예정인 행사를 확인해보세요.',
                'deadline_passed': '⏳ 등록 마감일이 지났습니다\n\n다른 진행 예정인 행사를 확인해보세요.'
            };

            Toast.error(alertMessages[validation.type] || validation.message);
            return;
        }

        // 이전 신청 데이터 조회 및 폼 자동 입력
        await loadEventUserInfo();

        // 모달 표시
        document.getElementById('eventRegistrationModal').style.display = 'block';
        document.body.style.overflow = 'hidden';

    } catch (error) {
        Toast.error('행사 신청 준비 중 오류가 발생했습니다.');
    }
}

// 로그인 페이지로 리다이렉트
async function redirectToLogin() {
    if (await Modal.confirm('로그인이 필요합니다. 로그인 페이지로 이동하시겠습니까?')) {
        window.location.href = '/auth/login?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
    }
}

// 사용자 정보 및 이전 신청 데이터 로드
async function loadEventUserInfo() {

    try {
        // 🚀 v3.42.0: ApiClient 사용 (fetch → ApiClient.get)
        // 사용자 정보 가져오기
        let userInfo = null;
        try {
            const userData = await ApiClient.get('/auth/me', { noLoading: true, noErrorToast: true });
            if (userData.success && userData.user) {
                fillEventUserInfo(userData.user);
                userInfo = userData.user;
            } else {
            }
        } catch (error) {
        }

        // 🚀 v3.42.0: ApiClient 사용 (fetch → ApiClient.get)
        // 이전 신청 데이터 가져오기
        let previousRegistration = null;
        try {
            const prevData = await ApiClient.get(`/api/events/${eventId}/previous-registration`, {
                noLoading: true,
                noErrorToast: true
            });

            if (prevData.success && prevData.data) {
                // 성공적인 응답: 이전 신청 데이터로 폼 채우기
                fillEventRegistrationForm(prevData.data);
                previousRegistration = prevData.data;
            } else if (prevData.data === null) {
            }
        } catch (error) {
        }
    } catch (error) {
        Toast.error('사용자 정보를 불러올 수 없습니다.\n수동으로 입력해주세요.');
    }
}

// 사용자 정보로 폼 채우기
function fillEventUserInfo(userData) {

    const nameField = document.getElementById('event_participant_name');
    const emailField = document.getElementById('event_participant_email');
    const phoneField = document.getElementById('event_participant_phone');

    if (nameField) {
        nameField.value = userData.nickname || '';
    } else {
    }

    if (emailField) {
        emailField.value = userData.email || '';
    } else {
    }

    if (phoneField) {
        phoneField.value = userData.phone || '';
    } else {
    }
}

// 이전 신청 데이터로 폼 채우기
function fillEventRegistrationForm(registrationData) {
    document.getElementById('event_participant_name').value = registrationData.participant_name || '';
    document.getElementById('event_participant_email').value = registrationData.participant_email || '';
    document.getElementById('event_participant_phone').value = registrationData.participant_phone || '';
    document.getElementById('event_company_name').value = registrationData.company_name || '';
    document.getElementById('event_position').value = registrationData.position || '';
    document.getElementById('event_motivation').value = registrationData.motivation || '';
    document.getElementById('event_special_requests').value = registrationData.special_requests || '';
    document.getElementById('event_how_did_you_know').value = registrationData.how_did_you_know || '';
}

// 행사 신청 모달 닫기
function closeEventRegistrationModal() {
    document.getElementById('eventRegistrationModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// 행사 신청 제출
async function submitEventRegistration() {
    try {
        // 🔥 Ultra Think: 제출 전 마감 조건 재검증
        const validation = validateEventRegistrationConditions();

        if (!validation.canRegister) {
            const alertMessages = {
                'own_event': '❌ 본인이 등록한 행사에는 신청할 수 없습니다.',
                'capacity_full': '❌ 정원이 마감되어 신청할 수 없습니다.',
                'event_started': '❌ 행사가 이미 시작되어 신청할 수 없습니다.',
                'deadline_passed': '❌ 등록 마감일이 지나 신청할 수 없습니다.'
            };

            Toast.error(alertMessages[validation.type] || validation.message);
            closeEventRegistrationModal();
            return;
        }

        const form = document.getElementById('eventRegistrationForm');
        const formData = new FormData(form);

        // CSRF 토큰 추가
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        formData.append('csrf_token', csrfToken);
        
        // FormData를 JSON으로 변환
        const data = {};
        formData.forEach((value, key) => {
            data[key] = value;
        });

        // 🚀 v3.42.0: ApiClient 사용 (fetch → ApiClient.post)
        const result = await ApiClient.post(`/api/events/${eventId}/registration?event_id=${eventId}`,
            data,
            { noLoading: true } // 버튼 상태로 로딩 표시
        );

        if (result.success) {
            Toast.success('✅ ' + result.message);
            closeEventRegistrationModal();

            // UI 업데이트
            if (result.data) {
                updateEventRegistrationUI(result.data.status, result.data);
            }
        } else {
            if (result.errors) {
                let errorMsg = '입력 정보를 확인해주세요:\n';
                for (const field in result.errors) {
                    errorMsg += '- ' + result.errors[field] + '\n';
                }
                Toast.error(errorMsg);
            } else {
                Toast.error('❌ ' + (result.message || '신청 처리 중 오류가 발생했습니다.'));
            }
        }
    } catch (error) {
        Toast.error('행사 신청 중 오류가 발생했습니다.\n잠시 후 다시 시도해주세요.');
    }
}

// 행사 신청 취소
async function cancelEventRegistration() {
    if (!(await Modal.confirm('정말로 행사 신청을 취소하시겠습니까?', { type: 'warning' }))) {
        return;
    }
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        // 🚀 v3.42.0: ApiClient 사용 (fetch → ApiClient.delete)
        const result = await ApiClient.delete(`/api/events/${eventId}/registration?event_id=${eventId}`, {
            noLoading: true,
            body: { csrf_token: csrfToken }
        });

        if (result.success) {
            Toast.success('✅ ' + result.message);

            // UI 업데이트
            updateEventRegistrationUI('cancelled', null);
        } else {
            Toast.error('❌ ' + (result.message || '신청 취소 중 오류가 발생했습니다.'));
        }
    } catch (error) {
        Toast.error('신청 취소 중 오류가 발생했습니다.\n잠시 후 다시 시도해주세요.');
    }
}

// 모달 외부 클릭 시 닫기
document.addEventListener('click', function(e) {
    const modal = document.getElementById('eventRegistrationModal');
    if (e.target === modal) {
        closeEventRegistrationModal();
    }
});

// ESC 키로 모달 닫기
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeEventRegistrationModal();
    }
});

<?php if (!empty($event['images'])): ?>
// 이미지 갤러리 관련 변수
const eventImages = <?= json_encode($event['images']) ?>;
let currentImageIndex = 0;

// 이미지 모달 열기
function openImageModal(index) {
    currentImageIndex = index;
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    const counter = document.getElementById('imageCounter');
    
    modalImage.src = eventImages[currentImageIndex].url;
    modalImage.alt = eventImages[currentImageIndex].alt_text;
    counter.textContent = `${currentImageIndex + 1} / ${eventImages.length}`;
    
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden'; // 배경 스크롤 방지
}

// 이미지 모달 닫기
function closeImageModal() {
    const modal = document.getElementById('imageModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto'; // 스크롤 복원
}

// 이전 이미지
function prevImage() {
    currentImageIndex = (currentImageIndex - 1 + eventImages.length) % eventImages.length;
    updateModalImage();
}

// 다음 이미지
function nextImage() {
    currentImageIndex = (currentImageIndex + 1) % eventImages.length;
    updateModalImage();
}

// 모달 이미지 업데이트
function updateModalImage() {
    const modalImage = document.getElementById('modalImage');
    const counter = document.getElementById('imageCounter');
    
    modalImage.src = eventImages[currentImageIndex].url;
    modalImage.alt = eventImages[currentImageIndex].alt_text;
    counter.textContent = `${currentImageIndex + 1} / ${eventImages.length}`;
}

// 키보드 이벤트
document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('imageModal');
    if (modal.style.display === 'block') {
        switch(e.key) {
            case 'Escape':
                closeImageModal();
                break;
            case 'ArrowLeft':
                prevImage();
                break;
            case 'ArrowRight':
                nextImage();
                break;
        }
    }
});

// 모달 배경 클릭시 닫기
document.getElementById('imageModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeImageModal();
    }
});
<?php endif; ?>

// 행사 이미지 갤러리 함수 별칭 (HTML에서 호출되는 함수명과 일치)
function openEventImageModal(index) {
    openImageModal(index);
}

// 강사 이미지 모달 열기
function openInstructorImageModal(imageSrc, instructorName) {
    
    const modal = document.getElementById('instructorImageModal');
    const modalImage = document.getElementById('instructorModalImage');
    const modalName = document.getElementById('instructorModalName');
    
    if (!modal || !modalImage || !modalName) {
        Toast.warning('이미지 모달을 표시할 수 없습니다.');
        return;
    }
    
    modalName.textContent = instructorName + ' 강사';
    modalImage.src = imageSrc;
    modalImage.alt = instructorName + ' 강사 이미지';
    
    // 완벽한 중앙정렬을 위한 클래스 적용
    modal.classList.add('show');
    modal.style.setProperty('display', 'flex', 'important');
    modal.style.setProperty('align-items', 'center', 'important');
    modal.style.setProperty('justify-content', 'center', 'important');
    document.body.style.overflow = 'hidden'; // 스크롤 방지
}

// 강사 이미지 모달 닫기
function closeInstructorImageModal() {
    const modal = document.getElementById('instructorImageModal');
    if (modal) {
        modal.classList.remove('show');
        modal.style.setProperty('display', 'none', 'important');
        document.body.style.overflow = 'auto'; // 스크롤 복원
    }
}

// 강사 이미지 모달 이벤트 리스너 (페이지 로드 후 실행)
document.addEventListener('DOMContentLoaded', function() {
    const instructorModal = document.getElementById('instructorImageModal');
    
    if (instructorModal) {
        // 배경 클릭시 닫기
        instructorModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeInstructorImageModal();
            }
        });
        
        // ESC 키로 닫기
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && instructorModal.classList.contains('show')) {
                closeInstructorImageModal();
            }
        });
    }
});

function closeEventImageModal() {
    closeImageModal();
}

function prevEventImage() {
    prevImage();
}

function nextEventImage() {
    nextImage();
}

/**
 * 행사 공유하기 기능
 */
function shareEventContent() {
    try {
        const eventTitle = "<?= addslashes(htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8')) ?>";
        const eventUrl = window.location.href;
        const eventDescription = "<?= addslashes(htmlspecialchars(substr(strip_tags($event['description'] ?? ''), 0, 100))) ?>...";
        
        // Web Share API 지원 확인
        if (navigator.share) {
            navigator.share({
                title: eventTitle,
                text: eventDescription,
                url: eventUrl
            }).then(() => {
            }).catch((error) => {
                fallbackShare(eventTitle, eventUrl);
            });
        } else {
            // 폴백: 클립보드 복사 또는 공유 옵션 표시
            fallbackShare(eventTitle, eventUrl);
        }
    } catch (error) {
        Toast.error('공유 기능에 오류가 발생했습니다.');
    }
}

/**
 * 폴백 공유 기능 (클립보드 복사)
 */
// 🚀 Phase 8: navigator.clipboard → copyToClipboard 사용 (Toast.error 버그도 수정)
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
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    `;
    
    const content = document.createElement('div');
    content.style.cssText = `
        background: white;
        padding: 30px;
        border-radius: 12px;
        max-width: 500px;
        width: 90%;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    `;
    
    content.innerHTML = `
        <h3 style="margin-bottom: 20px; color: #2d3748;">🔗 행사 공유하기</h3>
        <p style="margin-bottom: 20px; color: #4a5568;">${title}</p>
        <div style="background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 20px; word-break: break-all; font-family: monospace; font-size: 14px;">
            ${url}
        </div>
        <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
            <button onclick="copyToClipboard('${url}')" style="padding: 10px 20px; background: #4A90E2; color: white; border: none; border-radius: 6px; cursor: pointer;">
                📋 복사하기
            </button>
            <a href="https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}" target="_blank" style="padding: 10px 20px; background: #4267B2; color: white; text-decoration: none; border-radius: 6px;">
                📘 Facebook
            </a>
            <a href="https://twitter.com/intent/tweet?text=${encodeURIComponent(title)}&url=${encodeURIComponent(url)}" target="_blank" style="padding: 10px 20px; background: #1DA1F2; color: white; text-decoration: none; border-radius: 6px;">
                🐦 Twitter
            </a>
            <a href="https://t.me/share/url?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}" target="_blank" style="padding: 10px 20px; background: #0088CC; color: white; text-decoration: none; border-radius: 6px;">
                📤 Telegram
            </a>
            <button onclick="this.parentElement.parentElement.parentElement.remove()" style="padding: 10px 20px; background: #a0aec0; color: white; border: none; border-radius: 6px; cursor: pointer;">
                닫기
            </button>
        </div>
    `;
    
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
// window.copyToClipboard() 자동 사용 (Toast.error 버그도 수정됨)

// 기존 프로필 이미지 모달 JavaScript 함수들 제거됨 - profile-modal.js 통합 시스템 사용

// 작성자와 채팅 시작
function startChatWithAuthor(authorId, authorName) {
    if (!authorId) {
        Toast.error('작성자 정보를 찾을 수 없습니다.');
        return;
    }
    
    // 채팅 페이지로 이동하면서 해당 사용자와 채팅 시작
    window.location.href = `/chat#user-${authorId}`;
}

// 이벤트 삭제 확인 함수
async function confirmDeleteEvent(eventId) {
    if (!eventId) {
        Toast.error('잘못된 행사 ID입니다.');
        return;
    }

    // 삭제 확인
    const confirmed = await Modal.confirm('⚠️ 정말로 이 행사를 삭제하시겠습니까?\n\n삭제된 행사는 복구할 수 없습니다.', { type: 'danger' });
    
    if (!confirmed) {
        return;
    }

    // 두 번째 확인
    const doubleConfirmed = await Modal.confirm('⚠️ 마지막 확인입니다!\n\n행사 제목: "<?= htmlspecialchars($event['title']) ?>"\n\n정말로 삭제하시겠습니까?', {
        type: 'danger',
        title: '최종 확인',
        confirmText: '삭제',
        cancelText: '취소'
    });

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

    // 디버깅 정보 출력

    // 🚀 v3.42.0: ApiClient 사용 (fetch → ApiClient.post)
    ApiClient.post(`/events/${eventId}/delete`,
        {
            csrf_token: csrfToken,
            confirm_delete: true
        },
        { noLoading: true } // 버튼 상태로 로딩 표시
    )
    .then(result => {

        if (result.success) {
            Toast.success('✅ 행사가 성공적으로 삭제되었습니다.');
            // 이전 페이지로 돌아가기 (또는 행사 목록으로)
            if (document.referrer && document.referrer !== window.location.href) {
                window.location.href = document.referrer;
            } else {
                window.location.href = '/events';
            }
        } else {
            Toast.error('❌ 행사 삭제에 실패했습니다: ' + result.message);

            // 버튼 상태 복원
            deleteBtn.innerHTML = originalText;
            deleteBtn.disabled = false;
        }
    })
    .catch(error => {
        Toast.error('행사 삭제 중 네트워크 오류가 발생했습니다.\n잠시 후 다시 시도해주세요.');

        // 버튼 상태 복원
        deleteBtn.innerHTML = originalText;
        deleteBtn.disabled = false;
    });
}
</script>

<!-- edit-check.js 로드 -->
<script src="/assets/js/edit-check.js"></script>

<!-- 이벤트 페이지 디버깅 스크립트 -->
<script src="/debug_events.js"></script>