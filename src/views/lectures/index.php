<?php
/**
 * 강의 일정 메인 페이지 (캘린더 뷰)
 */

// 로그인 상태 확인
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
require_once SRC_PATH . '/helpers/HtmlSanitizerHelper.php';
$isLoggedIn = AuthMiddleware::isLoggedIn();
$currentUserId = AuthMiddleware::getCurrentUserId();

// 월/년도 네비게이션을 위한 날짜 계산
$prevMonth = $currentMonth == 1 ? 12 : $currentMonth - 1;
$prevYear = $currentMonth == 1 ? $currentYear - 1 : $currentYear;
$nextMonth = $currentMonth == 12 ? 1 : $currentMonth + 1;
$nextYear = $currentMonth == 12 ? $currentYear + 1 : $currentYear;

$monthNames = [
    1 => '1월', 2 => '2월', 3 => '3월', 4 => '4월', 5 => '5월', 6 => '6월',
    7 => '7월', 8 => '8월', 9 => '9월', 10 => '10월', 11 => '11월', 12 => '12월'
];
?>

<style>
/* 강의 일정 페이지 스타일 */
.lectures-container {
    max-width: 1600px;
    margin: 0 auto;
    padding: 30px 15px 20px 15px;
    min-height: calc(100vh - 200px);
    overflow-x: auto;
}

.lectures-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 40px 20px;
    text-align: center;
    margin-top: 60px;
    margin-bottom: 30px;
    border-radius: 12px;
    max-width: 1600px;
    margin-left: auto;
    margin-right: auto;
}

.lectures-header h1 {
    font-size: 2.5rem;
    margin-bottom: 10px;
    font-weight: 700;
}

.lectures-header p {
    font-size: 1.1rem;
    opacity: 0.9;
    margin: 0;
}

.calendar-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
    max-width: 1600px;
    margin-left: auto;
    margin-right: auto;
}

.month-navigation {
    display: flex;
    align-items: center;
    gap: 20px;
}

.month-nav-btn {
    padding: 8px 16px;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.month-nav-btn:hover {
    background: #5a67d8;
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
    padding: 8px 16px;
    background: transparent;
    border: none;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.3s ease;
    text-decoration: none;
    color: #4a5568;
}

.view-btn.active {
    background: #667eea;
    color: white;
}

.btn-create {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none !important;
    transition: all 0.3s ease;
}

.btn-create:link,
.btn-create:visited,
.btn-create:focus,
.btn-create:active {
    text-decoration: none !important;
}

.btn-create:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(72, 187, 120, 0.4);
    text-decoration: none !important;
}

/* 색상 범례 스타일 */
.color-legend {
    display: flex;
    justify-content: center;
    gap: 30px;
    margin: 20px 0;
    padding: 15px;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 500;
    color: #4a5568;
}

.legend-color {
    width: 20px;
    height: 20px;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.legend-color.offline {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.legend-color.online {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
}

/* 캘린더 스타일 */
.calendar-view {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    overflow-x: auto;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
    min-width: 980px;
}

.calendar-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.day-header {
    padding: 18px 12px;
    text-align: center;
    font-weight: 700;
    color: #4a5568;
    border-right: 1px solid #e2e8f0;
    font-size: 1rem;
}

.day-header:last-child {
    border-right: none;
}

.calendar-body {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
}

.calendar-day {
    min-height: 140px;
    min-width: 140px;
    border-right: 1px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;
    padding: 8px;
    background: white;
    position: relative;
    overflow: hidden;
}

.calendar-day:nth-child(7n) {
    border-right: none;
}

.calendar-day.today {
    background: #fff5f5;
    border: 2px solid #667eea;
}

.day-number {
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 8px;
    font-size: 1.1rem;
}

.lecture-item {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

.lecture-item:hover {
    transform: scale(1.02);
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.4);
}

.lecture-item.online {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
}


.lecture-time {
    font-size: 0.75rem;
    opacity: 0.9;
    display: block;
    font-weight: 500;
}

.lecture-title {
    font-weight: 600;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 0.8rem;
}

/* 더보기 버튼 스타일 */
.more-lectures-btn {
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

.more-lectures-btn:hover {
    background: linear-gradient(135deg, #495057 0%, #343a40 100%);
    transform: scale(1.02);
    box-shadow: 0 2px 6px rgba(108, 117, 125, 0.4);
}

.more-text {
    font-weight: 600;
    font-size: 0.7rem;
}

/* 사이드바 */
.lectures-layout {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 20px;
    max-width: 1600px;
    margin: 0 auto;
}

.lectures-sidebar {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
    height: fit-content;
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
    border-bottom: 2px solid #667eea;
}

.today-lectures, .upcoming-lectures {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.sidebar-lecture-item {
    background: #f8fafc;
    padding: 12px;
    border-radius: 8px;
    border-left: 4px solid #667eea;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    color: inherit;
}

.sidebar-lecture-item:hover {
    background: #e2e8f0;
    transform: translateX(4px);
}

.sidebar-lecture-title {
    font-weight: 600;
    color: #2d3748;
    font-size: 0.9rem;
    margin-bottom: 4px;
}

.sidebar-lecture-meta {
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

/* 리스트 뷰 */
.list-view {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
}

.lecture-list-item {
    padding: 20px;
    border-bottom: 1px solid #e2e8f0;
    cursor: pointer;
    transition: background-color 0.2s ease;
    text-decoration: none;
    color: inherit;
    display: block;
}

.lecture-list-item:hover {
    background-color: #f8fafc;
}

.lecture-list-item:last-child {
    border-bottom: none;
}

.lecture-list-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 10px;
}

.lecture-list-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 5px;
}

.lecture-badge {
    padding: 4px 8px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    color: white;
}

.badge-seminar { background: #667eea; }
.badge-workshop { background: #48bb78; }
.badge-conference { background: #ed8936; }
.badge-webinar { background: #38b2ac; }
.badge-training { background: #9f7aea; }

.lecture-list-meta {
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

.lecture-list-description {
    color: #4a5568;
    font-size: 0.9rem;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* 모바일 반응형 */
@media (max-width: 1024px) {
    .lectures-layout {
        grid-template-columns: 1fr;
        max-width: none;
    }
    
    .lectures-sidebar {
        order: -1;
    }
    
    .calendar-view {
        min-width: 800px;
    }
    
    .lectures-header,
    .calendar-controls {
        max-width: none;
    }
}

@media (max-width: 768px) {
    .lectures-container {
        padding: 20px 10px 15px 10px;
    }
    
    .lectures-header {
        padding: 30px 15px;
        margin-top: 15px;
        margin-left: 0;
        margin-right: 0;
    }
    
    .lectures-header h1 {
        font-size: 1.8rem;
    }
    
    .calendar-controls {
        flex-direction: column;
        align-items: stretch;
        gap: 10px;
        margin-left: 0;
        margin-right: 0;
    }
    
    .month-navigation {
        justify-content: center;
    }
    
    .calendar-view {
        min-width: 700px;
        border-radius: 8px;
    }
    
    .calendar-day {
        min-height: 100px;
        min-width: 100px;
        padding: 5px;
    }
    
    .lecture-item {
        font-size: 0.7rem;
        padding: 3px 4px;
        margin-bottom: 2px;
    }
    
    .lecture-time {
        font-size: 0.65rem;
    }
    
    .lecture-title {
        font-size: 0.7rem;
    }
    
    .day-number {
        font-size: 1rem;
        margin-bottom: 5px;
    }
    
    .lecture-list-meta {
        grid-template-columns: 1fr;
    }
    
    /* 모바일에서 범례 세로 배치 */
    .color-legend {
        flex-direction: column;
        gap: 15px;
        align-items: center;
        margin: 15px 0;
        padding: 12px;
    }
}

/* 매우 작은 화면 (모바일 세로) */
@media (max-width: 480px) {
    .lectures-container {
        padding: 15px 5px;
    }
    
    .lectures-header {
        padding: 20px 10px;
        margin-top: 10px;
        margin-left: 0;
        margin-right: 0;
    }
    
    .lectures-header h1 {
        font-size: 1.5rem;
    }
    
    .calendar-view {
        min-width: 500px;
    }
    
    .calendar-day {
        min-height: 80px;
        min-width: 70px;
        padding: 3px;
    }
    
    .lecture-item {
        font-size: 0.6rem;
        padding: 2px 3px;
        margin-bottom: 1px;
    }
    
    .lecture-time {
        font-size: 0.55rem;
    }
    
    .lecture-title {
        font-size: 0.6rem;
    }
    
    .day-number {
        font-size: 0.9rem;
        margin-bottom: 3px;
    }
    
    .month-nav-btn {
        padding: 6px 12px;
        font-size: 0.9rem;
    }
    
    .current-month {
        font-size: 1.2rem;
    }
}

/* 캘린더 스크롤 힌트 */
.calendar-view::after {
    content: '← 좌우로 스크롤하세요 →';
    position: absolute;
    bottom: 10px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.7rem;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s ease;
}

@media (max-width: 768px) {
    .calendar-view {
        position: relative;
    }
    
    .calendar-view::after {
        opacity: 1;
        animation: scrollHint 3s ease-in-out infinite;
    }
}

@keyframes scrollHint {
    0%, 70%, 100% { opacity: 0; }
    10%, 60% { opacity: 1; }
}

/* 일정 상세 모달 */
.day-lectures-modal {
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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

.modal-lecture-item {
    background: #f8fafc;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 12px;
    border-left: 4px solid #667eea;
    transition: all 0.2s ease;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
    display: block;
}

.modal-lecture-item:hover {
    background: #e2e8f0;
    transform: translateX(4px);
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.15);
}

.modal-lecture-item.online {
    border-left-color: #48bb78;
}


.modal-lecture-time {
    font-size: 0.9rem;
    font-weight: 600;
    color: #667eea;
    margin-bottom: 5px;
}

.modal-lecture-title {
    font-size: 1rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 8px;
    line-height: 1.4;
}

.modal-lecture-meta {
    display: flex;
    gap: 15px;
    font-size: 0.8rem;
    color: #718096;
    flex-wrap: wrap;
}

.modal-lecture-meta span {
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
    
    .modal-lecture-item {
        padding: 12px;
    }
    
    .modal-lecture-meta {
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
.lectures-container {
    background: white !important;
}

.calendar-view, .lectures-sidebar, .list-view {
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
    background: #fff5f5 !important;
    border-color: #667eea !important;
}

.day-number {
    color: #2d3748 !important;
}

.color-legend {
    background: #f8fafc !important;
    border-color: #e2e8f0 !important;
}

.legend-item {
    color: #4a5568 !important;
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

/* 강의 아이템 색상은 원래 시스템 유지 - 오프라인(보라)/온라인(초록) 구분 */

/* 모바일에서도 화이트 배경 강제 유지 */
@media (max-width: 768px) {
    body {
        background-color: white !important;
    }
    
    .lectures-container {
        background: white !important;
        padding: 20px 10px 15px 10px;
    }
    
    .calendar-view, .lectures-sidebar, .list-view {
        background: white !important;
        border-color: #e2e8f0 !important;
    }
    
    .calendar-day {
        background: white !important;
        border-color: #e2e8f0 !important;
    }
    
    .calendar-day.today {
        background: #fff5f5 !important;
        border-color: #667eea !important;
    }
}

/* 다크모드 감지되어도 화이트 배경 강제 유지 */
@media (prefers-color-scheme: dark) {
    body {
        background-color: white !important;
    }
    
    .lectures-container {
        background: white !important;
    }
    
    .calendar-view, .lectures-sidebar, .list-view {
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
        background: #fff5f5 !important;
        border-color: #667eea !important;
    }
    
    .day-number {
        color: #2d3748 !important;
    }
    
    .color-legend {
        background: #f8fafc !important;
        border-color: #e2e8f0 !important;
    }
    
    .legend-item {
        color: #4a5568 !important;
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
    
    /* 강의 아이템 색상은 원래 시스템 유지 - 다크모드에서도 오프라인(보라)/온라인(초록) 구분 */
}
</style>

<div class="lectures-container">
    <!-- 헤더 섹션 -->
    <div class="lectures-header">
        <h1>📅 강의 일정</h1>
        <p>다양한 마케팅 강의와 세미나 일정을 확인하고 신청하세요</p>
    </div>
    
    <!-- 색상 범례 -->
    <div class="color-legend">
        <div class="legend-item">
            <div class="legend-color offline"></div>
            <span>🏢 오프라인 강의</span>
        </div>
        <div class="legend-item">
            <div class="legend-color online"></div>
            <span>💻 온라인 강의</span>
        </div>
    </div>
    
    <!-- 캘린더 컨트롤 영역 -->
    <div class="calendar-controls">
        <!-- 월 네비게이션 -->
        <div class="month-navigation">
            <a href="?year=<?= $prevYear ?>&month=<?= $prevMonth ?>&view=<?= $view ?>" class="month-nav-btn">
                ← 이전달
            </a>
            <div class="current-month">
                <?= $currentYear ?>년 <?= $monthNames[$currentMonth] ?>
            </div>
            <a href="?year=<?= $nextYear ?>&month=<?= $nextMonth ?>&view=<?= $view ?>" class="month-nav-btn">
                다음달 →
            </a>
        </div>
        
        <!-- 뷰 전환 및 액션 -->
        <div class="view-controls">
            <div class="view-toggle">
                <a href="?year=<?= $currentYear ?>&month=<?= $currentMonth ?>&view=calendar" 
                   class="view-btn <?= $view === 'calendar' ? 'active' : '' ?>">
                    📅 캘린더
                </a>
                <a href="?year=<?= $currentYear ?>&month=<?= $currentMonth ?>&view=list" 
                   class="view-btn <?= $view === 'list' ? 'active' : '' ?>">
                    📋 목록
                </a>
            </div>
            
            <?php if ($isLoggedIn): ?>
                <?php 
                // 기업회원 권한 확인
                require_once SRC_PATH . '/middleware/CorporateMiddleware.php';
                $permission = CorporateMiddleware::checkLectureEventPermission();
                
                if ($permission['hasPermission']): ?>
                    <a href="/lectures/create" class="btn-create">
                        ➕ 강의 등록
                    </a>
                <?php else: ?>
                    <a href="/corp/info" class="btn-create" style="background: #a0aec0;" 
                       title="<?= htmlspecialchars($permission['message']) ?>">
                        📝 강의 일정 등록
                    </a>
                <?php endif; ?>
            <?php else: ?>
                <a href="/auth/login?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn-create">
                    🔑 로그인 후 등록
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="lectures-layout">
        <!-- 메인 콘텐츠 -->
        <div class="lectures-main">
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
                        <?php foreach ($calendarData as $week): ?>
                            <?php foreach ($week as $day): ?>
                                <?php if ($day === null): ?>
                                    <div class="calendar-day"></div>
                                <?php else: ?>
                                    <div class="calendar-day <?= $day['isToday'] ? 'today' : '' ?>" 
                                         data-date="<?= sprintf('%04d-%02d-%02d', $currentYear, $currentMonth, $day['day']) ?>"
                                         data-lecture-count="<?= count($day['lectures']) ?>">
                                        <div class="day-number"><?= $day['day'] ?></div>
                                        <?php 
                                        $maxVisible = 3; // 최대 표시할 일정 수
                                        $visibleLectures = array_slice($day['lectures'], 0, $maxVisible);
                                        $remainingCount = count($day['lectures']) - $maxVisible;
                                        ?>
                                        
                                        <?php foreach ($visibleLectures as $lecture): ?>
                                            <a href="/lectures/<?= $lecture['id'] ?>" 
                                               class="lecture-item <?= $lecture['location_type'] ?>"
                                               title="<?= htmlspecialchars($lecture['title']) ?>">
                                                <span class="lecture-time"><?= date('H:i', strtotime($lecture['start_time'])) ?></span>
                                                <span class="lecture-title"><?= htmlspecialchars($lecture['title']) ?></span>
                                            </a>
                                        <?php endforeach; ?>
                                        
                                        <?php if ($remainingCount > 0): ?>
                                            <div class="more-lectures-btn" 
                                                 onclick="showDayLectures('<?= sprintf('%04d-%02d-%02d', $currentYear, $currentMonth, $day['day']) ?>', <?= $day['day'] ?>, <?= htmlspecialchars(json_encode($day['lectures']), ENT_QUOTES) ?>)">
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
                    <?php if (!empty($lectures)): ?>
                        <?php foreach ($lectures as $lecture): ?>
                            <a href="/lectures/<?= $lecture['id'] ?>" class="lecture-list-item">
                                <div class="lecture-list-header">
                                    <div>
                                        <div class="lecture-list-title"><?= htmlspecialchars($lecture['title']) ?></div>
                                        <div class="lecture-list-meta">
                                            <div class="meta-item">
                                                📅 <?= date('Y-m-d', strtotime($lecture['start_date'])) ?>
                                            </div>
                                            <div class="meta-item">
                                                🕒 <?= date('H:i', strtotime($lecture['start_time'])) ?> - <?= date('H:i', strtotime($lecture['end_time'])) ?>
                                            </div>
                                            <div class="meta-item">
                                                👨‍🏫 <?= htmlspecialchars($lecture['instructor_name']) ?>
                                            </div>
                                            <div class="meta-item">
                                                <?php if ($lecture['location_type'] === 'online'): ?>
                                                    💻 온라인
                                                <?php else: ?>
                                                    📍 <?= htmlspecialchars($lecture['venue_name'] ?? '오프라인') ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="lecture-badge badge-<?= $lecture['category'] ?>">
                                        <?= [
                                            'seminar' => '세미나',
                                            'workshop' => '워크샵',
                                            'conference' => '컨퍼런스',
                                            'webinar' => '웨비나',
                                            'training' => '교육과정'
                                        ][$lecture['category']] ?? $lecture['category'] ?>
                                    </span>
                                </div>
                                
                                <div class="lecture-list-description">
                                    <?= htmlspecialchars(HtmlSanitizerHelper::htmlToPlainText($lecture['description'], 200)) ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-sidebar">
                            <p>📅 이번 달에 예정된 강의가 없습니다.</p>
                            <?php if ($isLoggedIn && in_array($_SESSION['user_role'] ?? '', ['PREMIUM', 'ADMIN', 'SUPER_ADMIN'])): ?>
                                <a href="/lectures/create" class="btn-create" style="margin-top: 10px; display: inline-block;">
                                    ➕ 첫 번째 강의 등록하기
                                </a>
                            <?php elseif ($isLoggedIn): ?>
                                <p style="margin-top: 10px; color: #718096; font-size: 0.9rem;">
                                    🏢 기업회원만 강의를 등록할 수 있습니다
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- 사이드바 -->
        <div class="lectures-sidebar">
            <!-- 오늘의 강의 -->
            <div class="sidebar-section">
                <h3 class="sidebar-title">🚀 오늘의 강의</h3>
                <?php if (!empty($todayLectures)): ?>
                    <div class="today-lectures">
                        <?php foreach ($todayLectures as $lecture): ?>
                            <a href="/lectures/<?= $lecture['id'] ?>" class="sidebar-lecture-item">
                                <div class="sidebar-lecture-title"><?= htmlspecialchars($lecture['title']) ?></div>
                                <div class="sidebar-lecture-meta">
                                    <span>🕒 <?= date('H:i', strtotime($lecture['start_time'])) ?></span>
                                    <span>👨‍🏫 <?= htmlspecialchars($lecture['organizer_name']) ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-sidebar">
                        오늘 예정된 강의가 없습니다.
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- 다가오는 강의 -->
            <div class="sidebar-section">
                <h3 class="sidebar-title">📋 다가오는 강의</h3>
                <?php if (!empty($upcomingLectures)): ?>
                    <div class="upcoming-lectures">
                        <?php foreach ($upcomingLectures as $lecture): ?>
                            <a href="/lectures/<?= $lecture['id'] ?>" class="sidebar-lecture-item">
                                <div class="sidebar-lecture-title"><?= htmlspecialchars($lecture['title']) ?></div>
                                <div class="sidebar-lecture-meta">
                                    <span>📅 <?= date('m/d', strtotime($lecture['start_date'])) ?></span>
                                    <span>🕒 <?= date('H:i', strtotime($lecture['start_time'])) ?></span>
                                    <span>👨‍🏫 <?= htmlspecialchars($lecture['organizer_name']) ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-sidebar">
                        예정된 강의가 없습니다.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- 일정 상세 모달 -->
<div id="dayLecturesModal" class="day-lectures-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">일정 상세</h3>
            <p class="modal-subtitle" id="modalSubtitle">날짜별 일정 목록</p>
            <button class="modal-close" onclick="closeDayLecturesModal()">&times;</button>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- 일정 목록이 여기에 동적으로 삽입됩니다 -->
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('📅 강의 일정 페이지 로드 완료');
    console.log('📊 이번 달 강의 수:', <?= count($lectures ?? []) ?>);
    console.log('📄 현재 뷰:', '<?= $view ?>');
    
    // 캘린더 강의 아이템 호버 효과
    const lectureItems = document.querySelectorAll('.lecture-item');
    lectureItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1.02)';
        });
    });
    
    // 강의 일정 관련 전역 객체 정의
    if (typeof window.lectures === 'undefined') {
        window.lectures = {
            initialized: true,
            currentYear: <?= $currentYear ?>,
            currentMonth: <?= $currentMonth ?>,
            currentView: '<?= $view ?>',
            lectureCount: <?= count($lectures ?? []) ?>,
            todayCount: <?= count($todayLectures ?? []) ?>,
            upcomingCount: <?= count($upcomingLectures ?? []) ?>
        };
    }
    
    // 키보드 네비게이션
    document.addEventListener('keydown', function(e) {
        // 좌우 화살표로 월 네비게이션
        if (e.key === 'ArrowLeft' && !e.target.matches('input, textarea')) {
            e.preventDefault();
            window.location.href = '?year=<?= $prevYear ?>&month=<?= $prevMonth ?>&view=<?= $view ?>';
        } else if (e.key === 'ArrowRight' && !e.target.matches('input, textarea')) {
            e.preventDefault();
            window.location.href = '?year=<?= $nextYear ?>&month=<?= $nextMonth ?>&view=<?= $view ?>';
        }
        
        // 'c'키로 캘린더 뷰, 'l'키로 리스트 뷰
        if (e.key === 'c' && !e.target.matches('input, textarea')) {
            e.preventDefault();
            window.location.href = '?year=<?= $currentYear ?>&month=<?= $currentMonth ?>&view=calendar';
        } else if (e.key === 'l' && !e.target.matches('input, textarea')) {
            e.preventDefault();
            window.location.href = '?year=<?= $currentYear ?>&month=<?= $currentMonth ?>&view=list';
        }
    });
    
    // 전역 오류 핸들러
    window.addEventListener('error', function(event) {
        if (event.filename && event.filename.includes('lectures')) {
            console.warn('🚀 강의 일정 페이지 JavaScript 오류 감지:', {
                message: event.message,
                filename: event.filename,
                lineno: event.lineno,
                colno: event.colno
            });
            event.preventDefault();
        }
    });
});

/**
 * 날짜별 일정 상세 모달 표시
 */
function showDayLectures(date, day, lectures) {
    try {
        const modal = document.getElementById('dayLecturesModal');
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
        modalSubtitle.textContent = `${formattedDate} · 총 ${lectures.length}개 일정`;
        
        // 모달 바디 내용 생성
        let modalContent = '';
        
        if (lectures.length === 0) {
            modalContent = '<div class="modal-empty">📅 이 날에는 예정된 일정이 없습니다.</div>';
        } else {
            // 시간 순으로 정렬
            lectures.sort((a, b) => {
                return new Date(`2000-01-01T${a.start_time}`) - new Date(`2000-01-01T${b.start_time}`);
            });
            
            lectures.forEach(lecture => {
                const startTime = lecture.start_time.substring(0, 5); // HH:MM 형식
                const endTime = lecture.end_time.substring(0, 5);
                
                let locationInfo = '';
                if (lecture.location_type === 'online') {
                    locationInfo = '💻 온라인';
                } else if (lecture.location_type === 'hybrid') {
                    locationInfo = '🔄 하이브리드';
                } else {
                    locationInfo = '📍 ' + (lecture.venue_name || '오프라인');
                }
                
                const categoryMap = {
                    'seminar': '세미나',
                    'workshop': '워크샵', 
                    'conference': '컨퍼런스',
                    'webinar': '웨비나',
                    'training': '교육과정'
                };
                
                const categoryName = categoryMap[lecture.category] || lecture.category;
                
                modalContent += `
                    <a href="/lectures/${lecture.id}" class="modal-lecture-item ${lecture.location_type}">
                        <div class="modal-lecture-time">${startTime} - ${endTime}</div>
                        <div class="modal-lecture-title">${escapeHtml(lecture.title)}</div>
                        <div class="modal-lecture-meta">
                            <span>👨‍🏫 ${escapeHtml(lecture.instructor_name || '미정')}</span>
                            <span>${locationInfo}</span>
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
                closeDayLecturesModal();
            }
        };
        
        console.log(`📅 ${date} 일정 모달 표시 (${lectures.length}개)`);
        
    } catch (error) {
        console.error('일정 모달 표시 오류:', error);
        alert('일정을 불러오는 중 오류가 발생했습니다.');
    }
}

/**
 * 날짜별 일정 모달 닫기
 */
function closeDayLecturesModal() {
    const modal = document.getElementById('dayLecturesModal');
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
        closeDayLecturesModal();
    }
});
</script>