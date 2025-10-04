<?php
/**
 * 이벤트 페이지 공통 사이드바 컴포넌트
 * 오늘의 행사 및 다가오는 행사를 표시
 *
 * 필요한 변수들:
 * - $todayEvents: 오늘의 행사 배열 (EventController::getTodayEvents()에서 제공)
 * - $upcomingEvents: 다가오는 행사 배열 (EventController::getUpcomingEvents()에서 제공)
 *
 * @version 1.0.0
 * @created 2025-09-26
 */

// 필수 변수 검증
if (!isset($todayEvents, $upcomingEvents)) {
    throw new Exception('EventsSidebar 컴포넌트에 필요한 변수가 전달되지 않았습니다.');
}
?>

<!-- 이벤트 사이드바 -->
<div class="events-sidebar">
    <!-- 오늘의 행사 -->
    <div class="sidebar-section">
        <h3 class="sidebar-title">🚀 오늘의 행사</h3>
        <?php if (!empty($todayEvents)): ?>
            <div class="today-events">
                <?php foreach ($todayEvents as $event): ?>
                    <a href="/events/detail?id=<?= $event['id'] ?>" class="sidebar-event-item">
                        <div class="sidebar-event-title"><?= htmlspecialchars($event['title']) ?></div>
                        <div class="sidebar-event-meta">
                            <span>🕒 <?= date('H:i', strtotime($event['start_time'])) ?></span>
                            <span>👨‍🏫 <?= htmlspecialchars($event['organizer_name']) ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-sidebar">
                오늘 예정된 행사가 없습니다.
            </div>
        <?php endif; ?>
    </div>

    <!-- 다가오는 행사 -->
    <div class="sidebar-section">
        <h3 class="sidebar-title">📋 다가오는 행사</h3>
        <?php if (!empty($upcomingEvents)): ?>
            <div class="upcoming-events">
                <?php foreach ($upcomingEvents as $event): ?>
                    <a href="/events/detail?id=<?= $event['id'] ?>" class="sidebar-event-item">
                        <div class="sidebar-event-title"><?= htmlspecialchars($event['title']) ?></div>
                        <div class="sidebar-event-meta">
                            <span>📅 <?= date('m/d', strtotime($event['start_date'])) ?></span>
                            <span>🕒 <?= date('H:i', strtotime($event['start_time'])) ?></span>
                            <span>👨‍🏫 <?= htmlspecialchars($event['organizer_name']) ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-sidebar">
                예정된 행사가 없습니다.
            </div>
        <?php endif; ?>
    </div>
</div>