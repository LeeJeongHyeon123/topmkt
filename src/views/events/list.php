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
?>

<style>
/* 행사 일정 리스트 페이지 스타일 (파란색 테마) */
.events-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 30px 15px 20px 15px;
    min-height: calc(100vh - 200px);
}

.events-header {
    background: linear-gradient(135deg, #4A90E2 0%, #2E86AB 100%);
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

.events-header h1 {
    font-size: 2.5rem;
    margin-bottom: 10px;
    font-weight: 700;
}

.events-header p {
    font-size: 1.1rem;
    opacity: 0.9;
    margin: 0;
}

.events-controls {
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin-bottom: 30px;
    align-items: center;
}

.events-navigation {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
    justify-content: center;
}

.month-nav {
    display: flex;
    align-items: center;
    gap: 15px;
    background: white;
    padding: 10px 20px;
    border-radius: 50px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.nav-btn {
    background: #4A90E2;
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 50%;
    cursor: pointer;
    transition: background 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
}

.nav-btn:hover {
    background: #357ABD;
}

.current-month {
    font-size: 1.3rem;
    font-weight: 600;
    color: #2E86AB;
    min-width: 120px;
    text-align: center;
}

.view-toggle {
    display: flex;
    background: white;
    border-radius: 50px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.view-btn {
    padding: 10px 20px;
    border: none;
    background: white;
    color: #666;
    cursor: pointer;
    transition: all 0.3s;
    font-weight: 500;
}

.view-btn.active {
    background: #4A90E2;
    color: white;
}

.create-event-btn {
    background: linear-gradient(135deg, #4A90E2 0%, #2E86AB 100%);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    box-shadow: 0 4px 15px rgba(74, 144, 226, 0.3);
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.create-event-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(74, 144, 226, 0.4);
    color: white;
    text-decoration: none;
}

/* 리스트 스타일 */
.events-list {
    display: grid;
    gap: 20px;
    grid-template-columns: 1fr;
}

.event-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: all 0.3s;
    cursor: pointer;
}

.event-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
}

.event-card-header {
    padding: 20px;
    border-left: 4px solid #4A90E2;
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
    color: #4A90E2;
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
    color: #4A90E2;
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

/* 반응형 */
@media (max-width: 768px) {
    .events-container {
        padding: 20px 10px;
    }
    
    .events-header {
        margin-top: 30px;
        padding: 20px 10px;
        margin-left: 0;
        margin-right: 0;
    }
    
    .events-header h1 {
        font-size: 1.6rem;
    }
    
    .events-header p {
        font-size: 0.9rem;
    }
    
    .events-controls {
        flex-direction: column;
        gap: 15px;
    }
    
    .events-navigation {
        flex-direction: column;
        gap: 15px;
    }
    
    .event-card-header {
        padding: 15px;
    }
    
    .event-title {
        font-size: 1.1rem;
    }
    
    .event-meta {
        gap: 10px;
    }
    
    .event-footer {
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
    }
}

/* 작은 모바일 화면 최적화 */
@media (max-width: 480px) {
    .events-container {
        padding: 10px 8px;
    }
    
    .events-header {
        margin-top: 30px; /* 캘린더 뷰와 동일하게 */
        padding: 15px 8px;
    }
    
    .events-header h1 {
        font-size: 1.4rem;
    }
    
    .events-header p {
        font-size: 0.85rem;
    }
}
</style>

<div class="events-container">
    <!-- 헤더 -->
    <div class="events-header">
        <h1>🎉 행사 일정</h1>
        <p>다양한 마케팅 행사와 네트워킹 행사에 참여하세요</p>
    </div>

    <!-- 컨트롤 영역 -->
    <div class="events-controls">
        <div class="events-navigation">
            <!-- 월 네비게이션 -->
            <div class="month-nav">
                <button class="nav-btn" onclick="navigateMonth(<?= $prev_month['year'] ?>, <?= $prev_month['month'] ?>)">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="current-month">
                    <?= $year ?>년 <?= $monthNames[$month] ?>
                </div>
                <button class="nav-btn" onclick="navigateMonth(<?= $next_month['year'] ?>, <?= $next_month['month'] ?>)">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>

            <!-- 뷰 토글 -->
            <div class="view-toggle">
                <button class="view-btn <?= $view === 'calendar' ? 'active' : '' ?>" 
                        onclick="toggleView('calendar')">
                    <i class="fas fa-calendar-alt"></i> 캘린더
                </button>
                <button class="view-btn <?= $view === 'list' ? 'active' : '' ?>" 
                        onclick="toggleView('list')">
                    <i class="fas fa-list"></i> 목록
                </button>
            </div>
        </div>

        <!-- 행사 등록 버튼 -->
        <?php if ($isLoggedIn && in_array($current_user['role'] ?? '', ['PREMIUM', 'ADMIN', 'SUPER_ADMIN'])): ?>
        <a href="/events/create" class="create-event-btn">
            <i class="fas fa-plus"></i>
            새 행사 등록
        </a>
        <?php endif; ?>
    </div>

    <!-- 행사 리스트 -->
    <div class="events-list">
        <?php if (empty($events)): ?>
            <div class="no-events">
                <i class="fas fa-calendar-times"></i>
                <h3><?= $year ?>년 <?= $monthNames[$month] ?>에 등록된 행사가 없습니다</h3>
                <p>새로운 행사를 등록하거나 다른 달을 확인해보세요.</p>
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

<script>
// 월 네비게이션
function navigateMonth(year, month) {
    window.location.href = `/events?year=${year}&month=${month}&view=<?= $view ?>`;
}

// 뷰 전환
function toggleView(viewType) {
    window.location.href = `/events?year=<?= $year ?>&month=<?= $month ?>&view=${viewType}`;
}

// 행사 상세 보기
function showEventDetail(eventId) {
    window.location.href = `/events/detail?id=${eventId}`;
}
</script>