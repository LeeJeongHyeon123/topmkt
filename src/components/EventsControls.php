<?php
/**
 * 이벤트 페이지 공통 컨트롤 컴포넌트
 * 월 네비게이션 + 뷰 전환 + 등록 버튼
 *
 * 필요한 변수들:
 * - $year: 현재 연도
 * - $month: 현재 월
 * - $prev_month: 이전 월 정보 ['year' => int, 'month' => int]
 * - $next_month: 다음 월 정보 ['year' => int, 'month' => int]
 * - $view: 현재 뷰 ('calendar' 또는 'list')
 * - $monthNames: 월 이름 배열
 * - $isLoggedIn: 로그인 상태 boolean
 *
 * @version 1.0.0
 * @created 2025-09-26
 */

// 필수 변수 검증
if (!isset($year, $month, $prev_month, $next_month, $view, $monthNames, $isLoggedIn)) {
    throw new Exception('EventsControls 컴포넌트에 필요한 변수가 전달되지 않았습니다.');
}
?>

<!-- 캘린더 컨트롤 영역 -->
<div class="calendar-controls">
    <!-- 월 네비게이션 -->
    <div class="month-navigation" style="justify-content: center !important; display: flex !important; align-items: center !important; gap: 20px !important;">
        <a href="?year=<?= $prev_month['year'] ?>&month=<?= $prev_month['month'] ?>&view=<?= $view ?>" class="month-nav-btn">
            ← 이전달
        </a>
        <div class="current-month">
            <?= $year ?>년 <?= $monthNames[$month] ?>
        </div>
        <a href="?year=<?= $next_month['year'] ?>&month=<?= $next_month['month'] ?>&view=<?= $view ?>" class="month-nav-btn">
            다음달 →
        </a>
    </div>

    <!-- 뷰 전환 및 액션 -->
    <div class="view-controls">
        <div class="view-toggle">
            <a href="?year=<?= $year ?>&month=<?= $month ?>&view=calendar"
               class="view-btn <?= $view === 'calendar' ? 'active' : '' ?>">
                📅 캘린더
            </a>
            <a href="?year=<?= $year ?>&month=<?= $month ?>&view=list"
               class="view-btn <?= $view === 'list' ? 'active' : '' ?>">
                📋 목록
            </a>
        </div>

        <?php if ($isLoggedIn): ?>
            <?php
            // 기업회원 권한 확인
            require_once SRC_PATH . '/middlewares/CorporateMiddleware.php';
            $permission = CorporateMiddleware::checkLectureEventPermission();

            if ($permission['hasPermission']): ?>
                <a href="/events/create" class="btn-create">
                    ➕ 행사 등록
                </a>
            <?php else: ?>
                <a href="/corp/info" class="btn-create" style="background: #a0aec0;"
                   title="<?= htmlspecialchars($permission['message']) ?>">
                    📝 행사 일정 등록
                </a>
            <?php endif; ?>
        <?php else: ?>
            <a href="/auth/login?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn-create">
                🔑 로그인 후 등록
            </a>
        <?php endif; ?>
    </div>
</div>