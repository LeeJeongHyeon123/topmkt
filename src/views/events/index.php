<?php
/**
 * 행사 일정 메인 페이지 (캘린더 뷰) - 강의 일정과 동일한 구조
 */

// 로그인 상태 확인
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
require_once SRC_PATH . '/helpers/HtmlSanitizerHelper.php';
$isLoggedIn = AuthMiddleware::isLoggedIn();
$currentUserId = AuthMiddleware::getCurrentUserId();

// Controller에서 전달되는 변수 사용: $year, $month, $prev_month, $next_month

$monthNames = [
    1 => '1월', 2 => '2월', 3 => '3월', 4 => '4월', 5 => '5월', 6 => '6월',
    7 => '7월', 8 => '8월', 9 => '9월', 10 => '10월', 11 => '11월', 12 => '12월'
];

// 🔥 CRITICAL: 캐시 무효화를 위한 타임스탬프 (헤더 수정 후 강제 업데이트)
$cache_buster = time() + rand(1000, 9999) + 999999;
?>

<!-- 🔥 ULTIMATE CACHE KILLER: 개발 중 모든 캐시 완전 차단 -->
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate, max-age=0">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
<meta name="cache-version" content="dev-<?php echo microtime(true); ?>">
<!-- 🔥 추가 캐시 무효화 헤더들 -->
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="robots" content="noindex, nofollow">
<meta name="cache-control" content="private, no-cache, no-store, proxy-revalidate, no-transform">
<meta name="Last-Modified" content="<?php echo gmdate('D, d M Y H:i:s') . ' GMT'; ?>">
<meta name="ETag" content="<?php echo md5(microtime(true)); ?>">

<!-- 🎨 공통 CSS 파일 -->
<link rel="stylesheet" href="/assets/css/events-common.css?v=<?php echo time(); ?>">

<!-- 행사 인덱스 페이지 스타일 include -->
<style>
<?php
// 행사 인덱스 페이지 스타일 파일 include
$styleFile = SRC_PATH . '/views/events/components/event-index-styles.css';
if (file_exists($styleFile)) {
    echo file_get_contents($styleFile);
}
?>
</style>


<div class="events-container">
    <!-- 헤더 컴포넌트 -->
    <?php include_once SRC_PATH . '/components/EventsHeader.php'; ?>
    
    <!-- 캘린더 컨트롤 컴포넌트 -->
    <?php include_once SRC_PATH . '/components/EventsControls.php'; ?>
    
    <div class="events-layout">
        <!-- 메인 콘텐츠 -->
        <div class="events-main">
            <?php if ($view === 'calendar'): ?>
                <!-- 캘린더 뷰 -->
                <div class="calendar-view">
                    <div class="calendar-header">
                        <div class="day-header">일</div>
                        <div class="day-header">월</div>
                        <div class="day-header">화</div>
                        <div class="day-header">수</div>
                        <div class="day-header">목</div>
                        <div class="day-header">금</div>
                        <div class="day-header">토</div>
                    </div>
                    
                    <div class="calendar-body">
                        <?php foreach ($calendar_data as $week): ?>
                            <?php foreach ($week as $day): ?>
                                <?php if ($day === null): ?>
                                    <div class="calendar-day empty-day"></div>
                                <?php else: ?>
                                    <div class="calendar-day <?= $day['class'] ?>" 
                                         data-date="<?= $day['date'] ?>"
                                         data-event-count="<?= count($day['events'] ?? []) ?>">
                                        <div class="day-number"><?= $day['day'] ?></div>
                                        <?php
                                        $dayEvents = $day['events'] ?? [];
                                        ?>

                                        <?php foreach ($dayEvents as $event): ?>
                                            <a href="/events/detail?id=<?= $event['id'] ?>"
                                               class="event-item"
                                               title="<?= htmlspecialchars($event['title']) ?>">
                                                <span class="event-time"><?= date('H:i', strtotime($event['start_time'])) ?></span>
                                                <span class="event-title"><?= htmlspecialchars($event['title']) ?></span>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                
            <?php else: ?>
                <!-- 리스트 뷰 -->
                <div class="list-view">
                    <?php if (!empty($events)): ?>
                        <?php foreach ($events as $event): ?>
                            <a href="/events/detail?id=<?= $event['id'] ?>" class="event-list-item">
                                <div class="event-list-header">
                                    <div>
                                        <div class="event-list-title"><?= htmlspecialchars($event['title']) ?></div>
                                        <div class="event-list-meta">
                                            <div class="meta-item">
                                                📅 <?= date('Y-m-d', strtotime($event['start_date'])) ?>
                                            </div>
                                            <div class="meta-item">
                                                🕒 <?= date('H:i', strtotime($event['start_time'])) ?> - <?= date('H:i', strtotime($event['end_time'])) ?>
                                            </div>
                                            <div class="meta-item">
                                                👨‍🏫 <?= htmlspecialchars($event['organizer_name']) ?>
                                            </div>
                                            <div class="meta-item">
                                                📍 <?= htmlspecialchars($event['venue_name'] ?? '오프라인') ?>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="event-badge badge-<?= $event['category'] ?>">
                                        <?= [
                                            'conference' => '컨퍼런스',
                                            'seminar' => '세미나',
                                            'workshop' => '워크샵',
                                            'networking' => '네트워킹',
                                            'exhibition' => '전시회'
                                        ][$event['category']] ?? $event['category'] ?>
                                    </span>
                                </div>
                                
                                <div class="event-list-description">
                                    <?= htmlspecialchars(HtmlSanitizerHelper::htmlToPlainText($event['description'], 200)) ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-sidebar">
                            <p>📅 이번 달에 예정된 행사가 없습니다.</p>
                            <?php if ($isLoggedIn && in_array($_SESSION['user_role'] ?? '', ['ROLE_CORPORATE', 'ADMIN', 'SUPER_ADMIN'])): ?>
                                <a href="/events/create" class="btn-create" style="margin-top: 10px; display: inline-block;">
                                    ➕ 첫 번째 행사 등록하기
                                </a>
                            <?php elseif ($isLoggedIn): ?>
                                <p style="margin-top: 10px; color: #718096; font-size: 0.9rem;">
                                    🏢 기업회원만 행사를 등록할 수 있습니다
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- 사이드바 -->
        <?php include_once SRC_PATH . '/components/EventsSidebar.php'; ?>
    </div>
</div>

<script>
// 🌟 ULTRA THINK 모드 - 간단한 JavaScript v4.0
document.addEventListener('DOMContentLoaded', function() {


    // ✅ CSS로 완전 해결됨 - JavaScript 강제 수정 불필요

    // 🎯 뷰 전환 설정
    document.body.setAttribute('data-view', '<?= $view ?>');

    // Grid 레이아웃은 CSS에서 자동 처리됨

    // 행사 일정 관련 전역 객체 정의
    if (typeof window.events === 'undefined') {
        window.events = {
            initialized: true,
            currentYear: <?= json_encode($year ?? date('Y')) ?>,
            currentMonth: <?= json_encode($month ?? date('n')) ?>,
            currentView: <?= json_encode($view ?? 'calendar') ?>,
            eventCount: <?= json_encode(count($events ?? [])) ?>,
            todayCount: <?= json_encode(count($todayEvents ?? [])) ?>,
            upcomingCount: <?= json_encode(count($upcomingEvents ?? [])) ?>
        };
    }

    // 키보드 네비게이션
    document.addEventListener('keydown', function(e) {
        // 좌우 화살표로 월 네비게이션
        if (e.key === 'ArrowLeft' && !e.target.matches('input, textarea')) {
            e.preventDefault();
            window.location.href = '?year=<?= $prev_month['year'] ?>&month=<?= $prev_month['month'] ?>&view=<?= $view ?>';
        } else if (e.key === 'ArrowRight' && !e.target.matches('input, textarea')) {
            e.preventDefault();
            window.location.href = '?year=<?= $next_month['year'] ?>&month=<?= $next_month['month'] ?>&view=<?= $view ?>';
        }

        // 'c'키로 캘린더 뷰, 'l'키로 리스트 뷰
        if (e.key === 'c' && !e.target.matches('input, textarea')) {
            e.preventDefault();
            window.location.href = '?year=<?= $year ?>&month=<?= $month ?>&view=calendar';
        } else if (e.key === 'l' && !e.target.matches('input, textarea')) {
            e.preventDefault();
            window.location.href = '?year=<?= $year ?>&month=<?= $month ?>&view=list';
        }
    });
});
</script>