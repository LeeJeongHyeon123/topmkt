<?php
/**
 * 행사 일정 리스트 뷰
 */

// 로그인 상태 확인
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
require_once SRC_PATH . '/helpers/HtmlSanitizerHelper.php';
$isLoggedIn = AuthMiddleware::isLoggedIn();
$currentUserId = AuthMiddleware::getCurrentUserId();

// 월 이름 배열
$monthNames = [
    1 => '1월', 2 => '2월', 3 => '3월', 4 => '4월', 5 => '5월', 6 => '6월',
    7 => '7월', 8 => '8월', 9 => '9월', 10 => '10월', 11 => '11월', 12 => '12월'
];

// 🔥 CRITICAL: 캐시 무효화를 위한 타임스탬프
$cache_buster = time();
?>

<!-- 🔥 CACHE BUSTER: 브라우저 캐시 강제 새로고침 -->
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
<meta name="cache-version" content="v2.2-<?php echo time(); ?>">

<!-- 🎨 공통 CSS 파일 -->
<link rel="stylesheet" href="/assets/css/events-common.css?v=<?php echo time(); ?>">

<style>
/* 🔥 CACHE BUSTER v2.2 - <?php echo date('Y-m-d H:i:s'); ?> - ULTRA HIGH PRIORITY CSS */
/* 행사 일정 리스트 페이지 스타일 (파란색 테마) - 사이드바 추가 */

/* 🚨 ABSOLUTE CSS OVERRIDE - 최고 우선순위로 중앙정렬 강제 적용 */
html body .events-container .calendar-controls,
html body .calendar-controls,
.events-container .calendar-controls,
.calendar-controls {
    justify-content: center !important;
    display: flex !important;
    align-items: center !important;
}

html body .events-container .month-navigation,
html body .month-navigation,
.events-container .month-navigation,
.month-navigation {
    justify-content: center !important;
    display: flex !important;
    align-items: center !important;
}

/* 🔥 모바일 events-header 좌우 여백 완전 통일 */
@media (max-width: 768px) {
    html body .events-header,
    .events-container .events-header,
    .events-header {
        margin-left: 15px !important;
        margin-right: 15px !important;
        width: calc(100% - 30px) !important;
        max-width: calc(100% - 30px) !important;
        padding: 30px 20px !important;
        box-sizing: border-box !important;
    }
}

/* 🚨 CRITICAL: 목록 UI events-header 좌우 여백 강제 추가 */
/* 캘린더 UI와 완전 동일한 여백 적용 */
html body .events-header,
.events-container .events-header,
.events-header {
    margin-left: 15px !important;
    margin-right: 15px !important;
    width: calc(100% - 30px) !important;
    max-width: calc(100vw - 30px) !important;
    box-sizing: border-box !important;
    position: relative !important;
}

/* 🔥 데스크톱에서도 여백 보장 */
@media (min-width: 769px) {
    html body .events-header,
    .events-container .events-header,
    .events-header {
        margin-left: auto !important;
        margin-right: auto !important;
        width: 100% !important;
        max-width: 1600px !important;
        padding: 40px 20px !important;
        margin-top: 20px !important; /* 데스크톱에서는 더 좁게 */
    }
}

/* 🚨 모바일에서 헤더 간격 최적화 */
@media (max-width: 768px) {
    html body .events-header,
    .events-container .events-header,
    .events-header {
        padding: 30px 20px !important; /* 패딩도 줄임 */
    }

    /* 🔥 모바일 컨테이너 패딩 강제 통일 */
    html body .events-container,
    .events-container {
        padding: 20px 15px 15px 15px !important; /* 모바일에서 더 컴팩트 */
    }
}
/* 🚨 EVENTS CONTAINER 완전 통일 - 강제 패딩 적용 */
html body .events-container,
.events-container {
    max-width: 1600px !important;
    margin: 0 auto !important;
    padding: 30px 15px 20px 15px !important;
    min-height: calc(100vh - 200px) !important;
    overflow-x: auto !important;
    box-sizing: border-box !important;
}

/* 🚨 EVENTS HEADER 완전 통일 - 간격 수정 */
html body .events-header,
.events-container .events-header,
.events-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: white !important;
    padding: 40px 20px !important;
    text-align: center !important;
    margin-top: 60px !important; /* 캘린더 뷰와 완전 동일 */
    margin-bottom: 30px !important;
    border-radius: 12px !important;
    max-width: 1600px !important;
    margin-left: auto !important;
    margin-right: auto !important;
    width: 100% !important;
    box-sizing: border-box !important;
    /* 🔥 모바일 좌우 여백 강제 통일 */
    margin-left: auto !important;
    margin-right: auto !important;
    left: 0 !important;
    right: 0 !important;
    position: relative !important;
}

.events-header h1 {
    font-size: 2.5rem !important;
    margin-bottom: 10px !important;
    font-weight: 700 !important;
    color: white !important;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3) !important;
}

.events-header p {
    font-size: 1.1rem !important;
    opacity: 0.9 !important;
    margin: 0 !important;
    color: white !important;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3) !important;
}

/* 🔥 CACHE BUSTER v2.1 - 강제 CSS 새로고침 */
.calendar-controls,
.events-container .calendar-controls,
body .calendar-controls {
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
    margin-bottom: 20px !important;
    flex-wrap: wrap !important;
    gap: 15px !important;
    max-width: 1600px !important;
    margin-left: auto !important;
    margin-right: auto !important;
    /* 캐시 방지를 위한 높은 특이성 적용 */
}

/* 🔥 ULTRA HIGH PRIORITY - 월 네비게이션 강제 중앙정렬 */
html body div.events-container div.calendar-controls div.month-navigation,
html body div.calendar-controls div.month-navigation,
html body .events-container .calendar-controls .month-navigation,
html body .calendar-controls .month-navigation,
body .events-container .calendar-controls .month-navigation,
body .calendar-controls .month-navigation,
.events-container .calendar-controls .month-navigation,
.calendar-controls .month-navigation,
.events-container .month-navigation,
.month-navigation {
    display: flex !important;
    align-items: center !important;
    gap: 20px !important;
    justify-content: center !important;
    /* 🚨 FORCE CENTER ALIGNMENT - NO SPACE-BETWEEN ALLOWED */
    flex-direction: row !important;
    text-align: center !important;
    margin: 0 auto !important;
    position: relative !important;
    width: 100% !important;
    max-width: 100% !important;
}

.month-nav-btn {
    padding: 12px 20px;
    background: #667eea;
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
    background: #764ba2;
    transform: translateY(-1px);
}

.current-month {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2d3748;
}

/* 뷰 컨트롤 - 캘린더 뷰와 완전 동일 */
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
    background: #667eea;
    color: white;
}

.btn-create {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    text-decoration: none !important;
}

/* 🧠 Ultra Think 해결책: List 뷰 전용 Flexbox (캘린더 뷰 영향 없음) */

<script>
// List 뷰에서만 Flexbox 레이아웃 적용
document.addEventListener('DOMContentLoaded', function() {
    // 현재 URL이 list 뷰인지 확인
    const urlParams = new URLSearchParams(window.location.search);
    const viewType = urlParams.get('view');

    if (viewType === 'list') {
        // List 뷰에서만 Flexbox CSS 동적 적용
        const style = document.createElement('style');
        style.textContent = `
            .events-layout {
                display: flex !important;
                flex-direction: column !important;
                gap: 20px !important;
                max-width: 1600px !important;
                margin: 0 auto !important;
                width: 100% !important;
                box-sizing: border-box !important;
                grid-template-columns: unset !important;
                grid-template-rows: unset !important;
            }
        `;
        document.head.appendChild(style);
    }
});

// 768px에서 강제 오버플로우 수정 (Ultra 강화 버전)
if (window.innerWidth <= 768) {
    function forceNoOverflow() {
        let fixCount = 0;

        // 모든 요소 검사
        document.querySelectorAll('*').forEach(el => {
            const rect = el.getBoundingClientRect();
            if (rect.right > window.innerWidth) {
                // 오버플로우 요소 발견 시 강제 수정
                el.style.setProperty('max-width', '100%', 'important');
                el.style.setProperty('width', '100%', 'important');
                el.style.setProperty('box-sizing', 'border-box', 'important');
                el.style.setProperty('overflow-x', 'hidden', 'important');

                // 특별한 경우 추가 처리
                if (el.style.minWidth) {
                    el.style.setProperty('min-width', 'auto', 'important');
                }

                fixCount++;
            }
        });

        // 전체 페이지 강제 제한
        document.documentElement.style.setProperty('max-width', '100vw', 'important');
        document.documentElement.style.setProperty('overflow-x', 'hidden', 'important');
        document.body.style.setProperty('max-width', '100vw', 'important');
        document.body.style.setProperty('overflow-x', 'hidden', 'important');

    }

    // 페이지 로드 후 여러 번 실행 (더 자주)
    setTimeout(forceNoOverflow, 50);
    setTimeout(forceNoOverflow, 200);
    setTimeout(forceNoOverflow, 500);
    setTimeout(forceNoOverflow, 1000);
    setTimeout(forceNoOverflow, 2000);

    // 리사이즈 이벤트에서도 실행
    window.addEventListener('resize', function() {
        if (window.innerWidth <= 768) {
            setTimeout(forceNoOverflow, 100);
        }
    });

    // MutationObserver로 DOM 변경 감지 시에도 실행
    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver(function(mutations) {
            let shouldCheck = false;
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' || mutation.type === 'attributes') {
                    shouldCheck = true;
                }
            });
            if (shouldCheck) {
                setTimeout(forceNoOverflow, 100);
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['style', 'class']
        });
    }
}
</script>

/* 데스크톱에서만 Row 방향 */
@media (min-width: 1025px) {
    .events-layout {
        flex-direction: row !important;
    }

    .events-main {
        flex: 1 !important;
        max-width: calc(100% - 340px) !important;
    }

    .events-sidebar {
        flex: 0 0 320px !important;
        max-width: 320px !important;
    }
}

/* 💬 사이드바 스타일은 events-common.css에서 관리됨 */

.sidebar-section {
    margin-bottom: 30px;
}

.sidebar-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 15px;
    padding-bottom: 8px;
    border-bottom: 2px solid #667eea;
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
    border-left: 4px solid #667eea;
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

/* 리스트 스타일 - 여백 제거하여 events-main과 정렬 맞추기 */
.events-list {
    display: grid;
    gap: 20px;
    grid-template-columns: 1fr;
    margin: 0;
    padding: 0;
    width: 100%;
    box-sizing: border-box;
}

.event-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: all 0.3s;
    cursor: pointer;
    margin-bottom: 20px;
}

.event-card:hover {
    /* 호버 효과 제거 - 사용자 요청 */
}

.event-card-header {
    padding: 20px;
    border-left: 4px solid #667eea;
}

.event-card-header.scale-large {
    border-left-color: #FF6B6B;
}

.event-card-header.scale-medium {
    border-left-color: #FFA726;
}

.event-card-header.scale-small {
    border-left-color: #66BB6A;
}

.event-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
}


.event-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 15px;
}

.event-meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #64748b;
    font-size: 0.9rem;
}

.event-meta-item i {
    color: #667eea;
    width: 16px;
}

.event-description {
    color: #64748b;
    line-height: 1.6;
    margin-bottom: 15px;
}

.event-footer {
    padding: 15px 20px;
    background: #f8fafc;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.event-instructor {
    color: #1e293b;
    font-weight: 500;
    font-size: 0.9rem;
}

.event-fee {
    color: #667eea;
    font-weight: 600;
    font-size: 1rem;
}

.no-events {
    text-align: center;
    padding: 60px 20px;
    color: #64748b;
}

.no-events i {
    font-size: 3rem;
    color: #cbd5e0;
    margin-bottom: 20px;
}

.no-events h3 {
    color: #1e293b;
    margin-bottom: 10px;
}

/* 모바일 반응형 - 캘린더 뷰와 동일 */
@media (max-width: 1024px) {
    .events-layout {
        display: flex !important;
        flex-direction: column !important;
        gap: 20px !important;
        max-width: none !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        box-sizing: border-box !important;
        /* Grid 속성 제거 */
        grid-template-columns: unset !important;
        grid-template-rows: unset !important;
    }

    .events-sidebar {
        order: -1 !important; /* 모바일/태블릿에서는 목록 앞에 */
        max-width: 100% !important;
        overflow: hidden !important;
        width: 100% !important;
        margin: 0 !important;
        box-sizing: border-box !important;
    }

    .events-main {
        order: 1 !important; /* 모바일/태블릿에서는 사이드바 뒤에 */
    }
}

/* 768px 이하 모바일 반응형 - 패딩 제거로 오버플로우 해결 */
@media (max-width: 768px) {
    /* 🔥 긴급 해결: html, body 가로 스크롤 완전 차단 */
    html, body {
        overflow-x: hidden !important;
        max-width: 100% !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    /* 🔥 main-content도 동일하게 제한 */
    main.main-content {
        overflow-x: hidden !important;
        max-width: 100% !important;
        width: 100% !important;
        box-sizing: border-box !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* 🔥 모든 하위 요소 강제 제한 */
    .events-header {
        max-width: 100% !important;
        width: 100% !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        box-sizing: border-box !important;
    }

    .calendar-controls {
        max-width: 100% !important;
        width: 100% !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        box-sizing: border-box !important;
    }

    /* 모든 요소 768px 초과 방지 */
    * {
        max-width: 100% !important;
        box-sizing: border-box !important;
    }
    /* 🔥 핵심 해결: events-container 패딩 완전 제거 - 최대 우선순위 */
    html body main.main-content div.events-container,
    body main.main-content div.events-container,
    main.main-content div.events-container,
    div.events-container,
    .events-container {
        padding: 0 !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        margin: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        overflow-x: hidden !important;
        max-width: 100% !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    /* 🔥 핵심 해결: events-layout gap 제거로 20px 오버플로우 해결 */
    html body div.events-container div.events-layout,
    body div.events-container div.events-layout,
    div.events-container div.events-layout,
    .events-container .events-layout,
    .events-layout {
        gap: 0 !important;
        margin-bottom: 20px !important;
    }

    /* 🔥 핵심 해결: events-sidebar 마진으로 여백, 패딩은 유지 */
    html body div.events-container div.events-sidebar,
    body div.events-container div.events-sidebar,
    div.events-container div.events-sidebar,
    .events-container .events-sidebar,
    .events-sidebar {
        margin: 0 20px 20px 20px !important;
        /* 패딩은 건드리지 않음 - 기본 20px 유지 */
        width: calc(100% - 40px) !important;
        max-width: calc(100% - 40px) !important;
        box-sizing: border-box !important;
    }

    /* 🔥 핵심 해결: events-main 영역도 동일한 마진 적용 */
    html body div.events-container div.events-main,
    body div.events-container div.events-main,
    div.events-container div.events-main,
    .events-container .events-main,
    .events-main {
        margin: 0 20px !important;
        width: calc(100% - 40px) !important;
        max-width: calc(100% - 40px) !important;
        box-sizing: border-box !important;
    }

    .events-header {
        margin-top: 30px !important; /* 캘린더 뷰와 동일 */
        padding: 40px 20px !important; /* 캘린더 뷰와 완전 동일 (inline style과 동일) */
        margin-left: 0 !important;
        margin-right: 0 !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    /* 모바일 타이틀 크기 조정 (v4.2.3) - 중복 방지 주석 */
    .events-header h1 {
        font-size: 1.6rem !important;
    }

    /* 사이드바 모바일 최적화 */
    .events-sidebar {
        max-width: calc(100vw - 40px) !important;
        width: calc(100vw - 40px) !important;
        overflow: hidden;
        margin-left: 20px !important;
        margin-right: 20px !important;
        box-sizing: border-box;
        padding: 15px !important;
    }

    .sidebar-section {
        margin-bottom: 20px;
    }

    .sidebar-title {
        font-size: 1rem;
    }

    /* 뷰 컨트롤 모바일 최적화 - 캘린더와 동일 */
    .view-controls {
        justify-content: center;
        flex-wrap: wrap;
        gap: 8px;
    }

    .view-btn {
        padding: 8px 12px !important;
        font-size: 14px !important;
        min-height: 36px !important;
        min-width: 36px !important;
        line-height: 1.2 !important;
    }

    .btn-create {
        padding: 8px 16px !important;
        font-size: 14px !important;
        min-height: 36px !important;
        min-width: 90px !important;
        line-height: 1.2 !important;
    }
    
    .events-header h1 {
        font-size: 1.6rem !important;
    }

    .events-header p {
        font-size: 0.9rem !important;
    }

    /* 캘린더 컨트롤 모바일 최적화 - 캘린더 뷰와 동일 */
    .calendar-controls {
        flex-direction: column;
        align-items: stretch;
        gap: 8px;
        margin: 10px 0;
        padding: 0 5px;
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

    /* 리스트 아이템 모바일 최적화 - 여백 제거 (events-main이 처리) */
    .events-list {
        max-width: 100% !important;
        width: 100% !important;
        overflow: hidden;
        margin: 0 !important;
        padding: 0 !important;
        box-sizing: border-box;
    }

    .event-card {
        max-width: 100% !important;
        width: 100% !important;
        padding: 0;
        box-sizing: border-box;
        overflow: hidden;
    }

    .event-card-header {
        padding: 15px !important;
        box-sizing: border-box;
    }

    .event-title {
        font-size: 1.1rem;
        line-height: 1.3;
    }

    .event-meta {
        grid-template-columns: 1fr 1fr !important;
        gap: 8px !important;
        font-size: 0.8rem !important;
    }

    .event-meta-item {
        font-size: 0.8rem;
        gap: 4px;
    }

    .event-description {
        max-width: 100% !important;
        width: 100% !important;
        box-sizing: border-box;
        overflow: hidden;
        word-wrap: break-word;
        font-size: 0.85rem;
        line-height: 1.4;
    }

    .event-footer {
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
        padding: 12px 15px !important;
    }

    .event-instructor,
    .event-fee {
        font-size: 0.9rem;
    }
}

/* 작은 모바일 화면 최적화 */
@media (max-width: 480px) {
    .events-container {
        padding: 10px 0;
        overflow-x: hidden;
    }

    .events-header {
        margin-top: 30px !important; /* 캘린더 뷰와 동일하게 */
        padding: 15px 8px !important; /* 캘린더 뷰와 동일하게 */
        margin-left: 0 !important;
        margin-right: 0 !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    .events-header h1 {
        font-size: 1.4rem !important;
    }

    .events-header p {
        font-size: 0.85rem !important;
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
        max-width: calc(100vw - 32px) !important;
        width: calc(100vw - 32px) !important;
        margin-left: 16px !important;
        margin-right: 16px !important;
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

    /* 캘린더 컨트롤 더 컴팩트하게 - 캘린더 뷰와 동일 */
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

    /* 리스트 아이템 작은 모바일 최적화 - 여백 제거 (events-main이 처리) */
    .events-list {
        max-width: 100% !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        box-sizing: border-box;
    }

    .event-card {
        margin-bottom: 12px !important;
        margin-left: 5px !important;
        margin-right: 5px !important;
        max-width: calc(100% - 10px) !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08) !important;
        box-sizing: border-box !important;
    }

    .event-card:hover {
        /* 모바일 호버 효과 제거 - 사용자 요청 */
    }

    .event-card-header {
        padding: 12px !important;
    }

    .event-title {
        font-size: 20px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        margin-bottom: 8px !important;
    }

    .event-meta {
        grid-template-columns: 1fr !important;
        gap: 12px !important;
        font-size: 16px !important;
        line-height: 1.4 !important;
    }

    .event-meta-item {
        font-size: 16px !important;
        line-height: 1.4 !important;
    }

    .event-description {
        font-size: 16px !important;
        line-height: 1.5 !important;
        margin-bottom: 8px !important;
    }

    .event-footer {
        padding: 12px !important;
        gap: 8px !important;
    }

    .event-instructor,
    .event-fee {
        font-size: 16px !important;
        line-height: 1.3 !important;
    }
}

/* 🎯 세련된 모던 호버 효과 */
html body .events-list .event-card,
html body .event-card,
.events-list .event-card,
.event-card,
div.event-card {
    cursor: pointer !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    position: relative !important;
    overflow: visible !important;
    border: 1px solid transparent !important;
}

/* 모든 자식 요소에도 pointer 커서 강제 적용 */
.event-card *,
.event-card > *,
.event-card-header,
.event-card-header *,
.event-title,
.event-meta,
.event-description {
    cursor: pointer !important;
}

/* 미묘한 호버 효과 - 그림자만 강화 */
.event-card:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12),
                0 4px 8px rgba(0, 0, 0, 0.08) !important;
    border-color: rgba(102, 126, 234, 0.2) !important;
}

/* 클릭 시 살짝 눌리는 효과 */
.event-card:active {
    transform: translateY(0px) !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
    transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

/* 제목에만 색상 변화 */
.event-card:hover .event-title {
    color: #667eea !important;
    transition: color 0.3s ease !important;
}
</style>

<div class="events-container">
    <!-- 헤더 컴포넌트 -->
    <?php include_once SRC_PATH . '/components/EventsHeader.php'; ?>

    <!-- 캘린더 컨트롤 컴포넌트 -->
    <?php include_once SRC_PATH . '/components/EventsControls.php'; ?>

    <!-- 레이아웃 그리드 - 메인 콘텐츠 + 사이드바 -->
    <div class="events-layout">
        <!-- 메인 콘텐츠 -->
        <div class="events-main">
            <!-- 행사 리스트 -->
            <div class="events-list">
                <?php if (empty($events)): ?>
                    <div class="no-events">
                        <i class="fas fa-calendar-times"></i>
                        <h3><?= $year ?>년 <?= $monthNames[$month] ?>에 등록된 행사가 없습니다</h3>
                        <p>새로운 행사를 등록하거나 다른 달을 확인해보세요.</p>
                        <?php if ($isLoggedIn && isset($permission) && $permission['hasPermission']): ?>
                            <a href="/events/create" class="btn-create" style="margin-top: 10px; display: inline-block;">
                                ➕ 첫 번째 행사 등록하기
                            </a>
                        <?php elseif ($isLoggedIn): ?>
                            <p style="margin-top: 10px; color: #718096; font-size: 0.9rem;">
                                🏢 기업회원만 행사를 등록할 수 있습니다
                            </p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($events as $event): ?>
                        <div class="event-card" onclick="showEventDetail(<?= $event['id'] ?>)">
                            <div class="event-card-header">
                                <div class="event-title">
                                    <?= htmlspecialchars($event['title']) ?>
                                </div>

                                <div class="event-meta">
                                    <div class="event-meta-item">
                                        <i class="fas fa-calendar"></i>
                                        <span><?= date('n월 j일', strtotime($event['start_date'])) ?></span>
                                    </div>
                                    <div class="event-meta-item">
                                        <i class="fas fa-clock"></i>
                                        <span><?= date('H:i', strtotime($event['start_time'])) ?></span>
                                    </div>
                                    <div class="event-meta-item">
                                        <i class="fas fa-map-marker-alt"></i>
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
                                    <?php if ($event['max_participants']): ?>
                                    <div class="event-meta-item">
                                        <i class="fas fa-users"></i>
                                        <span>최대 <?= number_format($event['max_participants']) ?>명</span>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <div class="event-description">
                                    <?= htmlspecialchars(HtmlSanitizerHelper::htmlToPlainText($event['description'], 150)) ?>
                                </div>
                            </div>

                            <div class="event-footer">
                                <div class="event-instructor">
                                    <i class="fas fa-user"></i>
                                    <?= htmlspecialchars($event['instructor_name']) ?>
                                </div>
                                <div class="event-fee">
                                    <?php if ($event['registration_fee']): ?>
                                        <?= number_format($event['registration_fee']) ?>원
                                    <?php else: ?>
                                        무료
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- 사이드바 -->
        <?php include_once SRC_PATH . '/components/EventsSidebar.php'; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // JavaScript 임시 제거 - CSS로 근본 해결 필요


    // 🚨 FORCE CENTER ALIGNMENT - JavaScript 강제 적용
    const monthNavigation = document.querySelector('.month-navigation');
    if (monthNavigation) {
        monthNavigation.style.setProperty('justify-content', 'center', 'important');
        monthNavigation.style.setProperty('display', 'flex', 'important');
        monthNavigation.style.setProperty('align-items', 'center', 'important');
        monthNavigation.style.setProperty('gap', '20px', 'important');
    }

    // 행사 일정 관련 전역 객체 정의
    if (typeof window.events === 'undefined') {
        window.events = {
            initialized: true,
            currentYear: <?= json_encode($year ?? date('Y')) ?>,
            currentMonth: <?= json_encode($month ?? date('n')) ?>,
            currentView: <?= json_encode($view ?? 'list') ?>,
            eventCount: <?= json_encode(count($events ?? [])) ?>,
            todayCount: <?= json_encode(count($todayEvents ?? [])) ?>,
            upcomingCount: <?= json_encode(count($upcomingEvents ?? [])) ?>
        };
    }

    // 키보드 네비게이션 - 캘린더 뷰와 동일
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

// 월 네비게이션
function navigateMonth(year, month) {
    window.location.href = `/events?year=${year}&month=${month}&view=<?= $view ?>`;
}

// 행사 상세 보기
function showEventDetail(eventId) {
    window.location.href = `/events/detail?id=${eventId}`;
}

// 768px 해상도에서 사이드바 너비 강제 제한
if (window.innerWidth <= 768) {
    document.body.style.overflowX = 'hidden';

    function preventOverflowOnly() {
        // JavaScript에서는 오버플로우 방지만 처리하고 정렬은 CSS에 맡김
        document.body.style.overflowX = 'hidden';

    }

    // 오버플로우 방지만 적용 (정렬은 CSS가 처리)
    preventOverflowOnly();

}

// 🎯 event-card 경계선 강제 추가 - 즉시 적용
function addEventCardBorders() {

    // 모든 event-card 찾아서 경계선 추가
    const eventCards = document.querySelectorAll('.event-card');

    eventCards.forEach((card, index) => {
        // 강력한 경계선 스타일 적용 + 우측 라인 보호
        card.style.setProperty('border', '2px solid #d1d5db', 'important');
        card.style.setProperty('border-radius', '12px', 'important');
        card.style.setProperty('margin-bottom', '20px', 'important');
        card.style.setProperty('background', 'white', 'important');
        card.style.setProperty('box-shadow', '0 2px 8px rgba(0,0,0,0.1)', 'important');

        // 우측 라인 보호는 CSS가 처리하므로 JavaScript에서 제거

        // 호버 효과 제거 - 사용자 요청

    });

}

// DOM 로드 완료 후 즉시 실행
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', addEventCardBorders);
} else {
    addEventCardBorders();
}

// 추가 안전망: 페이지 로드 후에도 실행
setTimeout(addEventCardBorders, 100);
setTimeout(addEventCardBorders, 500);
setTimeout(addEventCardBorders, 1000);
</script>