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

<style>
/* 🌟 ULTRA THINK 모드 - 간단하고 깔끔한 레이아웃 v4.0 */
/* 📅 행사 일정 페이지 - 파란색 테마 유지 */

/* 🏗️ 메인 컨테이너 - 강의 일정과 완전 동일 */
.events-container {
    max-width: 1600px;
    margin: 0 auto;
    padding: 30px 20px 20px 20px;
    min-height: calc(100vh - 200px);
}

/* 📱 레이아웃 구조 - 강의 일정과 완전 동일한 단순 구조 */
/* 💬 레이아웃 그리드는 events-common.css에서 관리됨 */

/* 화면 크기별 캘린더 너비 제한 - 강의 일정과 동일 */
@media (min-width: 1441px) {
    /* Grid 시스템에서 자동 조정 */
}

    .calendar-view {
        max-width: 100% !important;
        width: 100% !important;
        /* min-width 완전 제거 - 완전 유연한 크기 */
    }
}

@media (min-width: 1200px) and (max-width: 1440px) {
    /* Grid 시스템에서 자동 조정 */
}

    .calendar-view {
        max-width: 100% !important;
        width: 100% !important;
        min-width: auto !important; /* 7개 컬럼을 더 작은 공간에 맞춤 */
    }

    /* 캘린더 내부 요소들도 제한 */
    .events-layout .calendar-view,
    .events-main .calendar-view,
    div.calendar-view {
        max-width: 100% !important;
        width: 100% !important;
        min-width: auto !important;
    }
}

/* 1024px 이하에서 세로 배치 - 강의 일정과 동일 */
@media (max-width: 1024px) {
    .events-layout {
        grid-template-columns: 1fr;
        max-width: none;
        gap: 30px !important;
        padding-right: 0 !important;
    }

    .events-sidebar {
        order: 2;
        max-width: 100% !important;
        width: 100% !important;
        min-width: 100% !important;
        overflow: hidden;
        margin-top: 30px !important;
        margin-right: 0 !important;
        margin-left: 0 !important;
        padding: 20px !important;
        box-sizing: border-box !important;
    }

    .events-main {
        order: 1;
    }
}




/* 메인 콘텐츠 - Grid 1fr로 자동 크기 조정 */
.events-main {
    /* Grid 1fr로 자동 크기 조정되므로 별도 제한 불필요 */
    overflow: hidden;
}

/* 캘린더 뷰 너비 제한 - 강의 일정과 동일 */
.calendar-view {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
}

/* Grid 내에서 캘린더 추가 제한 */
.events-layout .calendar-view {
    max-width: 100%;
    width: 100%;
}

/* 사이드바 스타일 - 강의 일정과 동일 */
.events-sidebar {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
    height: fit-content;
    max-width: 100%;
    overflow: hidden;
    box-sizing: border-box;
}


/* 📅 캘린더 뷰 - 완전 중앙 정렬 (중복 제거됨) */

/* 📋 목록 뷰 */
.list-view {
    width: 100%;
    max-width: 100%;
    margin: 0 auto;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    box-sizing: border-box;
}

/* 🎛️ 캘린더 컨트롤 (월 네비게이션) */
.calendar-controls {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

/* 📊 사이드바 - 반응형 개선 */
html body .events-container .events-layout .events-sidebar,
body .events-container .events-layout .events-sidebar,
.events-container .events-layout .events-sidebar,
.events-layout .events-sidebar,
.events-sidebar {
    background: white !important;
    border-radius: 12px !important;
    padding: 20px !important;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05) !important;
    border: 1px solid #e2e8f0 !important;
    height: fit-content !important;
    width: 300px !important;
    min-width: 300px !important;
    overflow: hidden !important;
    box-sizing: border-box !important;
    margin-right: 20px !important;
}

/* 🔥 작은 화면에서 사이드바 강제 캘린더와 동일한 너비 */
@media (max-width: 1024px) {
    html body .events-container .events-layout .events-sidebar,
    body .events-container .events-layout .events-sidebar,
    .events-container .events-layout .events-sidebar,
    .events-layout .events-sidebar,
    .events-sidebar {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 100% !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        margin-top: 30px !important;
        flex: none !important;
        flex-grow: 0 !important;
        flex-shrink: 0 !important;
        flex-basis: 100% !important;
    }

    /* 캘린더도 동일한 너비로 강제 설정 */
    .events-main,
    .calendar-view {
        width: 100% !important;
        max-width: 100% !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }
}

/* 큰 화면에서 사이드바 고정 */
@media (min-width: 1440px) {
    .events-sidebar {
        max-width: 300px !important;
        width: 300px !important;
        margin-right: 20px !important;
    }
}

/* 중간 화면에서 사이드바 고정 */
@media (min-width: 1200px) and (max-width: 1439px) {
    .events-sidebar {
        max-width: 280px !important;
        width: 280px !important;
        margin-right: 25px !important;
    }
}

/* 작은 화면에서 사이드바 전체 너비 */
@media (max-width: 1199px) {
    .events-sidebar {
        max-width: 100% !important;
        width: 100% !important;
        min-width: 100% !important;
        margin-right: 0 !important;
        margin-left: 0 !important;
        margin-top: 30px !important;
        padding: 20px !important;
        box-sizing: border-box !important;
    }
}

.sidebar-section {
    margin-bottom: 30px;
}

.sidebar-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 15px;
    padding-bottom: 8px;
    border-bottom: 2px solid #4299e1;
}

.today-events, .upcoming-events {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.sidebar-event-item {
    display: block;
    padding: 12px;
    background: #f8fafc;
    border-radius: 8px;
    text-decoration: none;
    color: inherit;
    transition: all 0.3s ease;
    border: 1px solid #e2e8f0;
}

.sidebar-event-item:hover {
    background: #ebf8ff;
    border-color: #4299e1;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(66, 153, 225, 0.15);
}

.sidebar-event-title {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 6px;
    line-height: 1.3;
    font-size: 0.9rem;
}

.sidebar-event-meta {
    display: flex;
    flex-direction: column;
    gap: 4px;
    font-size: 0.8rem;
    color: #4a5568;
}

.sidebar-event-meta span {
    display: flex;
    align-items: center;
    gap: 4px;
}

.empty-sidebar {
    text-align: center;
    color: #9ca3af;
    padding: 20px;
    font-style: italic;
}


/* 📱 모바일 반응형 최적화 */
@media (max-width: 768px) {
    .events-container {
        padding: 15px;
    }

    .events-layout {
        gap: 15px;
    }

    .calendar-view,
    .list-view {
        padding: 15px;
        border-radius: 8px;
    }

    .calendar-controls {
        gap: 15px;
        margin-bottom: 15px;
    }
}

/* 📱 소형 모바일 반응형 */
@media (max-width: 480px) {
    .events-container {
        padding: 10px;
    }

    .events-layout {
        gap: 10px;
    }

    .calendar-view,
    .list-view {
        padding: 10px;
        border-radius: 6px;
    }

    .month-navigation {
        gap: 10px;
    }
}

/* 📅 캘린더 테이블 스타일 */
.calendar-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.calendar-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.day-header {
    padding: 8px 2px;
    text-align: center;
    font-weight: 600;
    color: #4a5568;
    font-size: 0.9rem;
    border-right: none;
    box-sizing: border-box;
    overflow: hidden;
    white-space: nowrap;
    min-width: 0;
}

.day-header:last-child {
    border-right: none;
}

.calendar-body {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
}

.calendar-day {
    min-height: 120px;
    padding: 4px;
    border-right: 1px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;
    background: white;
    position: relative;
    box-sizing: border-box;
    font-size: 0.8rem;
}

.calendar-day:last-child {
    border-right: none;
}

.calendar-day.other-month {
    background: #f8fafc;
    color: #a0aec0;
}

.day-number {
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 8px;
    color: #2d3748;
}

.other-month .day-number {
    color: #a0aec0;
}

.event-item {
    background: linear-gradient(135deg, #4A90E2 0%, #2E86AB 100%);
    color: white;
    padding: 4px 6px;
    margin-bottom: 4px;
    border-radius: 4px;
    font-size: 0.75rem;
    cursor: pointer;
    transition: all 0.2s ease;
    overflow: hidden;
}

.event-item:hover {
    transform: scale(1.02);
    box-shadow: 0 2px 6px rgba(74, 144, 226, 0.4);
}

.event-time {
    font-weight: 600;
    display: block;
    font-size: 0.7rem;
    opacity: 0.9;
}

.event-title {
    font-weight: 500;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 0.75rem;
}

/* 🎯 뷰 전환 로직 - URL 파라미터 기반 */
body[data-view="calendar"] .list-view {
    display: none;
}

body[data-view="list"] .calendar-view {
    display: none;
}

body[data-view="calendar"] .calendar-view {
    display: block;
}
    }

/* 🎆 이벤트 헤더 - 강의 일정과 동일한 스타일 적용 (강력한 우선순위) */
body .events-container .events-header,
html body div.events-container div.events-header,
html body .events-header,
.events-container .events-header,
div.events-container div.events-header,
.events-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    background-color: #667eea !important;
    color: white !important;
    padding: 40px 20px !important;
    text-align: center !important;
    margin-top: 60px !important;
    margin-bottom: 30px !important;
    border-radius: 12px !important;
    max-width: none !important;
    margin-left: auto !important;
    margin-right: auto !important;
    display: block !important;
    width: auto !important;
    box-sizing: border-box !important;
}

body .events-container .events-header h1,
html body .events-header h1,
.events-container .events-header h1,
.events-header h1 {
    font-size: 2.5rem !important;
    margin-bottom: 10px !important;
    font-weight: 700 !important;
    color: white !important;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3) !important;
}

body .events-container .events-header p,
html body .events-header p,
.events-container .events-header p,
.events-header p {
    font-size: 1.1rem !important;
    opacity: 0.9 !important;
    margin: 0 !important;
    color: white !important;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3) !important;
}

/* 📅 월 네비게이션 - 완전 중앙 정렬 */
.month-navigation {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    margin: 0 auto;
    width: 100%;
    box-sizing: border-box;
}

.month-nav-btn {
    padding: 12px 20px;
    background: #4A90E2;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    min-height: 44px;
    box-sizing: border-box;
    font-size: 16px;
}

.month-nav-btn:hover {
    background: #357ABD;
    transform: translateY(-1px);
}

.current-month {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2d3748;
}

.view-controls {
    display: flex;
    gap: 10px;
    align-items: center;
}

.view-toggle {
    display: flex;
    background: #f8fafc;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
}

.view-btn {
    padding: 12px 20px;
    background: transparent;
    border: none;
    cursor: pointer;
    font-size: 16px;
    transition: all 0.3s ease;
    text-decoration: none;
    color: #4a5568;
    min-height: 44px;
    box-sizing: border-box;
}

.view-btn.active {
    background: #4A90E2;
    color: white;
}

.btn-create {
    background: linear-gradient(135deg, #4A90E2 0%, #2E86AB 100%);
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none !important;
    transition: all 0.3s ease;
    min-height: 44px;
    box-sizing: border-box;
    font-size: 16px;
}

.btn-create:link,
.btn-create:visited,
.btn-create:focus,
.btn-create:active {
    text-decoration: none !important;
}

.btn-create:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(74, 144, 226, 0.4);
    text-decoration: none !important;
}

/* 캘린더 스타일 - 중복 제거됨 */

/* 중복 제거됨 - 첫 번째 정의 사용 */

/* 중복 제거됨 - 첫 번째 정의 사용 */

.day-header:last-child {
    border-right: none;
}

.calendar-body {
    /* 🚨 FORCE DISPLAY - 모든 상황에서 표시 보장 */
    display: grid !important;
    grid-template-columns: repeat(7, 1fr) !important;
    width: 100% !important;
    visibility: visible !important;
    opacity: 1 !important;
}

/* 🚨 CALENDAR DAY - 반응형 폭 적용 */
.calendar-day {
    min-height: 120px;
    width: 100% !important; /* 🔥 Grid 1fr 활용 */
    box-sizing: border-box !important;
    padding: 6px !important; /* 패딩 축소로 공간 절약 */
    border-right: 1px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;
    background: white;
    position: relative;
    overflow: hidden;
}

.calendar-day:nth-child(7n) {
    border-right: none;
}

.calendar-day.today {
    background: #f0f7ff;
    border: 2px solid #4A90E2;
}

.calendar-day.empty-day {
    background: transparent;
    border: 1px solid #e2e8f0; /* 테두리 유지하되 연한 회색으로 */
    pointer-events: none;
    opacity: 0.3; /* 시각적으로 비활성화 표시 */
}

.day-number {
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 8px;
    font-size: 1.1rem;
}

.event-item {
    background: linear-gradient(135deg, #4A90E2 0%, #2E86AB 100%);
    color: white;
    padding: 4px 6px;
    border-radius: 4px;
    font-size: 0.8rem;
    margin-bottom: 3px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: block;
    text-decoration: none;
    line-height: 1.3;
    overflow: hidden;
}

.event-item:hover {
    transform: scale(1.02);
    box-shadow: 0 2px 8px rgba(74, 144, 226, 0.4);
}

.event-time {
    font-size: 0.75rem;
    opacity: 0.9;
    display: block;
    font-weight: 500;
}

.event-title {
    font-weight: 600;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 0.8rem;
}

/* 더보기 버튼 스타일 */
.more-events-btn {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    color: white;
    padding: 3px 6px;
    border-radius: 4px;
    font-size: 0.7rem;
    margin-top: 2px;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: center;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.more-events-btn:hover {
    background: linear-gradient(135deg, #495057 0%, #343a40 100%);
    transform: scale(1.02);
    box-shadow: 0 2px 6px rgba(108, 117, 125, 0.4);
}

.more-text {
    font-weight: 600;
    font-size: 0.7rem;
}

/* 중복 사이드바 CSS 제거됨 - 위에서 이미 정의됨 */

.sidebar-section {
    margin-bottom: 30px;
}

.sidebar-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 15px;
    padding-bottom: 8px;
    border-bottom: 2px solid #4A90E2;
}

.today-events, .upcoming-events {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.sidebar-event-item {
    background: #f8fafc;
    padding: 12px;
    border-radius: 8px;
    border-left: 4px solid #4A90E2;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    color: inherit;
}

.sidebar-event-item:hover {
    background: #e2e8f0;
    transform: translateX(4px);
}

.sidebar-event-title {
    font-weight: 600;
    color: #2d3748;
    font-size: 0.9rem;
    margin-bottom: 4px;
}

.sidebar-event-meta {
    font-size: 0.8rem;
    color: #718096;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.empty-sidebar {
    text-align: center;
    color: #a0aec0;
    font-size: 0.9rem;
    padding: 20px 0;
}

/* 리스트 뷰 - 카드형 디자인 */
.list-view {
    background: transparent;
    border-radius: 0;
    overflow: visible;
    box-shadow: none;
    border: none;
    padding: 10px 0;
}

.event-list-item {
    background: white;
    padding: 20px;
    margin-bottom: 16px;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    color: inherit;
    display: block;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border: 1px solid #e2e8f0;
    position: relative;
    overflow: hidden;
}

.event-list-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(135deg, #4A90E2 0%, #2E86AB 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.event-list-item:hover {
    background-color: #fbfcfe;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    border-color: #b3d1f7;
}

.event-list-item:hover::before {
    opacity: 1;
}

.event-list-item:last-child {
    margin-bottom: 0;
}

.event-list-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 10px;
}

.event-list-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 5px;
}

.event-badge {
    padding: 4px 8px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    color: white;
}

/* 🚀 v3.28.0: 배지 스타일은 /assets/css/badges.css에서 통합 관리 */

.event-list-meta {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 10px;
    font-size: 0.9rem;
    color: #718096;
    margin-bottom: 10px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
}

.event-list-description {
    color: #4a5568;
    font-size: 0.9rem;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* 🔧 모바일 터치 타겟 및 폰트 크기 개선 */
/* 모든 인터랙티브 요소 44px+ 터치 타겟 보장 */
.month-nav-btn {
    min-height: 44px;      
    min-width: 44px;
    padding: 10px 16px;    
    font-size: 14px;       
    box-sizing: border-box;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1.2;
}

.view-btn {
    min-height: 44px;      
    min-width: 44px;
    padding: 10px 16px;    
    font-size: 14px;       
    box-sizing: border-box;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1.2;
}

.btn-create {
    min-height: 44px;      
    padding: 10px 20px;    
    font-size: 14px;       
    box-sizing: border-box;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1.2;
}

/* 터치 영역 확대를 위한 추가 패딩 */
.sidebar-event-item {
    min-height: 44px;      
    padding: 12px;         
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.event-list-item {
    min-height: 44px;      
    padding: 18px;         
    box-sizing: border-box;
}

.sidebar-event-title {
    font-size: 15px;       
    line-height: 1.3;
    font-weight: 600;
}

.sidebar-event-meta {
    font-size: 13px;       
    line-height: 1.3;
    color: #718096;
    margin-top: 4px;
}

.event-list-title {
    font-size: 16px;       
    line-height: 1.3;
    font-weight: 600;
}

.event-list-meta {
    font-size: 14px;       
    line-height: 1.4;
}

.event-list-description {
    font-size: 14px;       
    line-height: 1.5;
}

/* 모달 관련 터치 타겟 개선 */
.modal-close {
    min-height: 44px;      
    min-width: 44px;       
    padding: 10px;         
    font-size: 18px;       
    box-sizing: border-box;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-event-item {
    min-height: 48px;      
    padding: 16px;         
    box-sizing: border-box;
}

/* 캘린더 셀 내 행사 아이템 터치 개선 */
.event-item {
    min-height: 28px;      
    padding: 6px 8px;      
    margin-bottom: 4px;    
    font-size: 12px;       
    line-height: 1.2;      
    box-sizing: border-box;
    display: block;
}

.event-time {
    font-size: 11px;       
    line-height: 1.2;      
}

.event-title {
    font-size: 12px;       
    line-height: 1.2;      
}


/* 📱 모바일 반응형 */
@media (max-width: 768px) {
    .events-header h1 {
        font-size: 1.6rem;
    }

    .events-header p {
        font-size: 0.9rem;
    }

    .calendar-controls {
        flex-direction: column;
        gap: 10px;
    }

    .month-navigation {
        gap: 15px;
    }
}
    
    .month-navigation {
        justify-content: center;
        gap: 15px;
    }
    
    .month-nav-btn {
        padding: 8px 12px; 
        font-size: 14px; 
        min-height: 36px; 
        min-width: 36px;  
        line-height: 1.2; 
    }
    
    .current-month {
        font-size: 1.2rem;
    }
    
    .view-controls {
        justify-content: center;
        flex-wrap: wrap;
        gap: 8px;
    }
    
    .view-btn {
        padding: 8px 12px; 
        font-size: 14px; 
        min-height: 36px; 
        min-width: 36px;  
        line-height: 1.2; 
    }
    
    .btn-create {
        padding: 8px 16px; 
        font-size: 14px; 
        min-height: 36px; 
        min-width: 90px; 
        line-height: 1.2; 
    }
    
    /* 사이드바 모바일 최적화 */
    .events-sidebar {
        max-width: 100% !important;
        width: 100% !important;
        min-width: 100% !important;
        overflow: hidden;
        margin-left: 0 !important;
        margin-right: 0 !important;
        margin-top: 30px !important;
        box-sizing: border-box;
        padding: 15px !important;
    }
    
    .sidebar-section {
        margin-bottom: 20px;
    }
    
    .sidebar-title {
        font-size: 1rem;
    }
    
    /* 🚨 EMERGENCY FIX: 달력 모바일 최적화 */
    .calendar-view {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        width: 100% !important;
        max-width: 100% !important;
        overflow: hidden !important;
        border-radius: 8px;
        margin: 0 auto !important;
        box-sizing: border-box;
        background: white !important;
        border: 1px solid #e2e8f0 !important;
        position: relative !important;
        z-index: 1 !important;
    }
    
    .calendar-header,
    .calendar-body {
        display: grid !important;
        grid-template-columns: repeat(7, 1fr) !important;
        min-width: unset !important;
        width: 100% !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    .day-header {
        padding: 8px 2px;
        font-size: 0.8rem;
        box-sizing: border-box;
    }

    .calendar-day {
        min-height: 100px;
        padding: 4px;
        box-sizing: border-box;
    }
    
    .event-item {
        font-size: 0.7rem;
        padding: 3px 4px;
        margin-bottom: 2px;
    }
    
    .event-time {
        font-size: 0.65rem;
    }
    
    .event-title {
        font-size: 0.7rem;
    }
    
    .day-number {
        font-size: 1rem;
        margin-bottom: 5px;
    }
    
    .event-list-meta {
        grid-template-columns: 1fr !important;
        gap: 12px !important; 
        font-size: 16px !important; 
        line-height: 1.4 !important; 
    }
    
    /* 작은 모바일에서 목록형 뷰 추가 최적화 */
    .list-view {
        max-width: calc(100vw - 30px) !important;
        width: calc(100vw - 30px) !important;
        margin-left: 15px !important;
        margin-right: 15px !important;
    }
    
    .event-list-item {
        padding: 16px !important; 
        margin-bottom: 12px !important; 
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06) !important;
        min-height: 48px !important; 
    }
    
    .event-list-item:hover {
        transform: translateY(-0.5px) !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
    }
    
    .event-list-title {
        font-size: 20px !important; 
        line-height: 1.3 !important; 
        font-weight: 600 !important; 
        margin-bottom: 8px !important; 
    }
    
    .event-list-header {
        margin-bottom: 8px !important;
    }
}

/* 매우 작은 화면 (모바일 세로) */
@media (max-width: 480px) {
    .events-container {
        padding: 10px 5px;
    }
    
    .events-header {
        padding: 15px 8px;
        margin-top: 30px; /* 개선: 작은 화면에서 적절한 간격 */
        margin-left: 0 !important;
        margin-right: 0 !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }
    
    .events-header h1 {
        font-size: 1.4rem;
    }
    
    .events-header p {
        font-size: 0.85rem;
    }
    
    /* 캘린더 컨트롤 더 컴팩트하게 */
    .month-navigation {
        gap: 10px;
    }
    
    .month-nav-btn {
        padding: 8px 12px !important; 
        font-size: 14px !important; 
        min-height: 36px !important; 
        min-width: 36px !important; 
        line-height: 1.2 !important; 
    }
    
    .current-month {
        font-size: 1.1rem;
    }
    
    .view-btn {
        padding: 8px 12px !important; 
        font-size: 14px !important; 
        min-height: 36px !important; 
        min-width: 36px !important; 
        line-height: 1.2 !important; 
    }
    
    .btn-create {
        padding: 8px 14px !important; 
        font-size: 14px !important; 
        min-height: 36px !important; 
        min-width: 80px !important; 
        line-height: 1.2 !important; 
    }
    
    /* 사이드바 더 컴팩트하게 */
    .events-sidebar {
        max-width: 100% !important;
        width: 100% !important;
        min-width: 100% !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        margin-top: 30px !important;
        padding: 12px !important;
        box-sizing: border-box;
    }
    
    .sidebar-title {
        font-size: 18px !important; 
        margin-bottom: 12px !important; 
        font-weight: 700 !important; 
        line-height: 1.3 !important; 
    }
    
    .sidebar-event-item {
        padding: 12px !important; 
        min-height: 48px !important; 
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    
    .sidebar-event-title {
        font-size: 16px !important; 
        font-weight: 600 !important;
        line-height: 1.3 !important; 
    }
    
    .sidebar-event-meta {
        font-size: 16px !important; 
        line-height: 1.3 !important; 
        margin-top: 4px !important; 
    }
    
    
    .event-item {
        font-size: 0.6rem;
        padding: 2px 3px;
        margin-bottom: 1px;
    }
    
    .event-time {
        font-size: 0.55rem;
    }
    
    .event-title {
        font-size: 0.6rem;
    }
    
    .day-number {
        font-size: 0.9rem;
        margin-bottom: 3px;
    }
}


/* 일정 상세 모달 */
.day-events-modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 0;
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    max-height: 80vh;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.modal-header {
    background: linear-gradient(135deg, #4A90E2 0%, #2E86AB 100%);
    color: white;
    padding: 20px 25px;
    border-radius: 12px 12px 0 0;
    position: relative;
}

.modal-title {
    font-size: 1.3rem;
    font-weight: 700;
    margin: 0;
}

.modal-subtitle {
    font-size: 0.9rem;
    opacity: 0.9;
    margin: 5px 0 0 0;
}

.modal-close {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 5px;
    border-radius: 50%;
    transition: background-color 0.2s ease;
}

.modal-close:hover {
    background-color: rgba(255, 255, 255, 0.2);
}

.modal-body {
    padding: 20px 25px;
    max-height: 50vh;
    overflow-y: auto;
}

.modal-event-item {
    background: #f8fafc;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 12px;
    border-left: 4px solid #4A90E2;
    transition: all 0.2s ease;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
    display: block;
}

.modal-event-item:hover {
    background: #e2e8f0;
    transform: translateX(4px);
    box-shadow: 0 2px 8px rgba(74, 144, 226, 0.15);
}

.modal-event-time {
    font-size: 0.9rem;
    font-weight: 600;
    color: #4A90E2;
    margin-bottom: 5px;
}

.modal-event-title {
    font-size: 1rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 8px;
    line-height: 1.4;
}

.modal-event-meta {
    display: flex;
    gap: 15px;
    font-size: 0.8rem;
    color: #718096;
    flex-wrap: wrap;
}

.modal-event-meta span {
    display: flex;
    align-items: center;
    gap: 4px;
}

.modal-empty {
    text-align: center;
    color: #a0aec0;
    font-size: 0.9rem;
    padding: 40px 20px;
}

/* 모바일 모달 반응형 */
@media (max-width: 768px) {
    .modal-content {
        width: 95%;
        margin: 10% auto;
        max-height: 85vh;
    }
    
    .modal-header {
        padding: 15px 20px;
    }
    
    .modal-title {
        font-size: 1.1rem;
    }
    
    .modal-body {
        padding: 15px 20px;
    }
    
    .modal-event-item {
        padding: 12px;
    }
    
    .modal-event-meta {
        flex-direction: column;
        gap: 5px;
    }
}

/* PC와 모바일 모든 화면 크기에서 화이트 배경 일관성 유지 */

/* 글로벌 화이트 배경 강제 적용 */
body {
    background-color: white !important;
}

/* 모든 화면 크기에서 화이트 배경 유지 */
.events-container {
    background: white !important;
}

.calendar-view, .events-sidebar, .list-view {
    background: white !important;
    border-color: #e2e8f0 !important;
}

.calendar-header {
    background: #f8fafc !important;
    border-color: #e2e8f0 !important;
}

.day-header {
    color: #4a5568 !important;
    border-color: #e2e8f0 !important;
}

.calendar-day {
    background: white !important;
    border-color: #e2e8f0 !important;
}

.calendar-day.today {
    background: #f0f7ff !important;
    border-color: #4A90E2 !important;
}

.day-number {
    color: #2d3748 !important;
}

.calendar-controls {
    background: white !important;
    border-color: #e2e8f0 !important;
}

.month-navigation button {
    background: white !important;
    color: #4a5568 !important;
    border-color: #e2e8f0 !important;
}

/* 행사 아이템 색상은 파란색 시스템 유지 */

/* 모바일에서도 화이트 배경 강제 유지 */
@media (max-width: 768px) {
    body {
        background-color: white !important;
    }
    
    .events-container {
        background: white !important;
        padding: 20px 10px 15px 10px;
    }
    
    .calendar-view, .events-sidebar, .list-view {
        background: white !important;
        border-color: #e2e8f0 !important;
    }
    
    .calendar-day {
        background: white !important;
        border-color: #e2e8f0 !important;
    }
    
    .calendar-day.today {
        background: #f0f7ff !important;
        border-color: #4A90E2 !important;
    }
}

/* 다크모드 감지되어도 화이트 배경 강제 유지 */
@media (prefers-color-scheme: dark) {
    body {
        background-color: white !important;
    }
    
    .events-container {
        background: white !important;
    }
    
    .calendar-view, .events-sidebar, .list-view {
        background: white !important;
        border-color: #e2e8f0 !important;
    }
    
    .calendar-header {
        background: #f8fafc !important;
        border-color: #e2e8f0 !important;
    }
    
    .day-header {
        color: #4a5568 !important;
        border-color: #e2e8f0 !important;
    }
    
    .calendar-day {
        background: white !important;
        border-color: #e2e8f0 !important;
    }
    
    .calendar-day.today {
        background: #f0f7ff !important;
        border-color: #4A90E2 !important;
    }
    
    .day-number {
        color: #2d3748 !important;
    }
    
    .calendar-controls {
        background: white !important;
        border-color: #e2e8f0 !important;
    }
    
    .month-navigation button {
        background: white !important;
        color: #4a5568 !important;
        border-color: #e2e8f0 !important;
    }
    
    /* 행사 아이템 색상은 파란색 시스템 유지 - 다크모드에서도 */
}

/* 🚀 ULTRA THINK 해결책: 완전 개선된 반응형 시스템 */

/* 캐시 무효화를 위한 CSS 버전 */
.events-layout {
    --css-version: 20250922-v6-ultimate-sidebar-width-fix;
}

/* 추가 안정성 강화 */
@media (min-width: 1200px) and (max-width: 1439px) {
    .events-container {
        padding: 30px 25px !important;
        max-width: calc(100vw - 50px) !important;
    }

    .events-layout {
        padding-right: 25px !important;
    }

    .events-main {
        min-width: auto !important;
        max-width: calc(100vw - 360px) !important;
    }
}

/* 작은 화면에서 스크롤 방지 및 강제 세로 배치 */
@media (max-width: 1199px) {
    .events-container {
        overflow-x: visible !important;
        max-width: 100vw !important;
        padding: 20px 15px !important;
    }

    .events-main, .events-sidebar {
        overflow-x: visible !important;
        max-width: 100% !important;
        width: 100% !important;
        min-width: 100% !important;
        margin: 0 !important;
        box-sizing: border-box !important;
    }

    .events-sidebar {
        padding: 20px !important;
    }

    .calendar-view {
        overflow: hidden !important;
        width: 100% !important;
        max-width: 100% !important;
    }

    /* 🔥 핵심 수정: 768px에서 강제 1컬럼 Grid */
    .events-layout {
        display: grid !important;
        grid-template-columns: 1fr !important;
        grid-template-rows: auto !important;
        gap: 20px !important;
        max-width: none !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        box-sizing: border-box !important;
    }

    .events-sidebar {
        margin-top: 30px !important;
        margin-bottom: 0 !important;
        display: block !important;
        width: 100% !important;
        max-width: 100% !important;
    }

    .events-main {
        display: block !important;
        margin-bottom: 0 !important;
    }
}

/* 모바일 최적화 강화 */
@media (max-width: 768px) {
    .events-container {
        padding: 15px 10px !important;
    }

    .sidebar-title {
        font-size: 16px !important;
        margin-bottom: 12px !important;
    }

    .sidebar-event-item {
        padding: 10px !important;
        font-size: 14px !important;
    }

    .calendar-day {
        min-height: 80px !important;
        font-size: 11px !important;
        padding: 4px !important;
    }

    .event-item {
        font-size: 9px !important;
        padding: 2px 3px !important;
        margin: 1px 0 !important;
    }
}
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
                                        $maxVisible = 3; // 최대 표시할 일정 수
                                        $visibleEvents = array_slice($dayEvents, 0, $maxVisible);
                                        $remainingCount = count($dayEvents) - $maxVisible;
                                        ?>
                                        
                                        <?php foreach ($visibleEvents as $event): ?>
                                            <a href="/events/detail?id=<?= $event['id'] ?>" 
                                               class="event-item"
                                               title="<?= htmlspecialchars($event['title']) ?>">
                                                <span class="event-time"><?= date('H:i', strtotime($event['start_time'])) ?></span>
                                                <span class="event-title"><?= htmlspecialchars($event['title']) ?></span>
                                            </a>
                                        <?php endforeach; ?>
                                        
                                        <?php if ($remainingCount > 0): ?>
                                            <div class="more-events-btn" 
                                                 onclick="showDayEvents('<?= $day['date'] ?>', <?= $day['day'] ?>, <?= htmlspecialchars(json_encode($dayEvents), ENT_QUOTES) ?>)">
                                                <span class="more-text">+<?= $remainingCount ?>개 더보기</span>
                                            </div>
                                        <?php endif; ?>
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

<!-- 일정 상세 모달 -->
<div id="dayEventsModal" class="day-events-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">일정 상세</h3>
            <p class="modal-subtitle" id="modalSubtitle">날짜별 일정 목록</p>
            <button class="modal-close" onclick="closeDayEventsModal()">&times;</button>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- 일정 목록이 여기에 동적으로 삽입됩니다 -->
        </div>
    </div>
</div>

<script>
// 🌟 ULTRA THINK 모드 - 간단한 JavaScript v4.0
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎉 행사 일정 페이지 로드 완료');
    console.log('📊 이번 달 행사 수:', <?= count($events ?? []) ?>);
    console.log('📄 현재 뷰:', '<?= $view ?>');

    // ✅ CSS로 완전 해결됨 - JavaScript 강제 수정 불필요

    // 🎯 뷰 전환 설정
    document.body.setAttribute('data-view', '<?= $view ?>');
    console.log('🔧 뷰 설정 완료:', '<?= $view ?>');

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

/**
 * 날짜별 일정 상세 모달 표시
 */
function showDayEvents(date, day, events) {
    try {
        const modal = document.getElementById('dayEventsModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalSubtitle = document.getElementById('modalSubtitle');
        const modalBody = document.getElementById('modalBody');
        
        if (!modal || !modalTitle || !modalSubtitle || !modalBody) {
            console.error('모달 요소를 찾을 수 없습니다');
            return;
        }
        
        // 날짜 포맷팅
        const dateObj = new Date(date + 'T00:00:00');
        const options = { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            weekday: 'long'
        };
        const formattedDate = dateObj.toLocaleDateString('ko-KR', options);
        
        // 모달 헤더 설정
        modalTitle.textContent = `${day}일 일정`;
        modalSubtitle.textContent = `${formattedDate} · 총 ${events.length}개 일정`;
        
        // 모달 바디 내용 생성
        let modalContent = '';
        
        if (events.length === 0) {
            modalContent = '<div class="modal-empty">📅 이 날에는 예정된 일정이 없습니다.</div>';
        } else {
            // 시간 순으로 정렬
            events.sort((a, b) => {
                return new Date(`2000-01-01T${a.start_time}`) - new Date(`2000-01-01T${b.start_time}`);
            });
            
            events.forEach(event => {
                const startTime = event.start_time.substring(0, 5); // HH:MM 형식
                const endTime = event.end_time.substring(0, 5);
                
                const categoryMap = {
                    'conference': '컨퍼런스',
                    'seminar': '세미나', 
                    'workshop': '워크샵',
                    'networking': '네트워킹',
                    'exhibition': '전시회'
                };
                
                const categoryName = categoryMap[event.category] || event.category;
                
                modalContent += `
                    <a href="/events/detail?id=${event.id}" class="modal-event-item">
                        <div class="modal-event-time">${startTime} - ${endTime}</div>
                        <div class="modal-event-title">${escapeHtml(event.title)}</div>
                        <div class="modal-event-meta">
                            <span>👨‍🏫 ${escapeHtml(event.organizer_name || '미정')}</span>
                            <span>📍 ${escapeHtml(event.venue_name || '오프라인')}</span>
                            <span>🏷️ ${categoryName}</span>
                        </div>
                    </a>
                `;
            });
        }
        
        modalBody.innerHTML = modalContent;
        
        // 모달 표시
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden'; // 배경 스크롤 방지
        
        // 모달 외부 클릭 시 닫기
        modal.onclick = function(event) {
            if (event.target === modal) {
                closeDayEventsModal();
            }
        };
        
        console.log(`📅 ${date} 일정 모달 표시 (${events.length}개)`);
        
    } catch (error) {
        console.error('일정 모달 표시 오류:', error);
        Toast.error('일정을 불러오는 중 오류가 발생했습니다.');
    }
}

/**
 * 날짜별 일정 모달 닫기
 */
function closeDayEventsModal() {
    const modal = document.getElementById('dayEventsModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto'; // 배경 스크롤 복원
    }
}

/**
 * HTML 이스케이프 함수
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ESC 키로 모달 닫기
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeDayEventsModal();
    }
});
</script>