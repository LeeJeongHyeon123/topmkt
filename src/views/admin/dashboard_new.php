<?php
/**
 * 새로운 관리자 대시보드 - 공통 템플릿 사용
 */

// 페이지 정보 설정
$page_title = '관리자 대시보드';
$page_description = '탑마케팅 플랫폼 전체 현황을 확인하세요';
$current_page = 'dashboard';

// 페이지별 추가 스타일
$additional_styles = '
<style>
/* 대시보드 콘텐츠 */
.dashboard-content {
    padding: 40px;
}

/* 통계 카드 */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 30px;
    margin-bottom: 40px;
}

.stat-card {
    background: white;
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--card-color);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
}

.stat-card.primary { --card-color: linear-gradient(135deg, #667eea, #764ba2); }
.stat-card.success { --card-color: linear-gradient(135deg, #48bb78, #38a169); }
.stat-card.warning { --card-color: linear-gradient(135deg, #ed8936, #dd6b20); }
.stat-card.danger { --card-color: linear-gradient(135deg, #e53e3e, #c53030); }

.stat-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
    background: var(--card-color);
}

.stat-number {
    font-size: 36px;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 8px;
    line-height: 1;
}

.stat-label {
    color: #718096;
    font-size: 16px;
    font-weight: 500;
}

.stat-change {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    font-weight: 500;
    margin-top: 12px;
}

.stat-change.positive {
    color: #38a169;
}

.stat-change.negative {
    color: #e53e3e;
}

/* 메인 그리드 */
.main-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
}

.dashboard-section {
    background: white;
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
}

.section-title {
    font-size: 20px;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}
</style>
';

// 대시보드 데이터 (실제로는 컨트롤러에서 전달받아야 함)
$todayStats = [
    'signups' => 12,
    'posts' => 45,
    'activeUsers' => 234,
    'pendingCorps' => 3
];

// 콘텐츠 정의
$content = '
    <!-- 대시보드 콘텐츠 -->
    <div class="dashboard-content">
        <!-- 통계 카드 -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-header">
                    <div class="stat-icon">👥</div>
                </div>
                <div class="stat-number">' . number_format($todayStats['signups']) . '</div>
                <div class="stat-label">오늘 신규 가입</div>
                <div class="stat-change positive">
                    <span>↗️</span> 전일 대비 +12%
                </div>
            </div>
            
            <div class="stat-card success">
                <div class="stat-header">
                    <div class="stat-icon">📝</div>
                </div>
                <div class="stat-number">' . number_format($todayStats['posts']) . '</div>
                <div class="stat-label">오늘 게시글</div>
                <div class="stat-change positive">
                    <span>↗️</span> 전일 대비 +8%
                </div>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-header">
                    <div class="stat-icon">🟢</div>
                </div>
                <div class="stat-number">' . number_format($todayStats['activeUsers']) . '</div>
                <div class="stat-label">현재 활성 사용자</div>
                <div class="stat-change positive">
                    <span>↗️</span> 실시간 접속 중
                </div>
            </div>
            
            <div class="stat-card danger">
                <div class="stat-header">
                    <div class="stat-icon">🏢</div>
                </div>
                <div class="stat-number">' . number_format($todayStats['pendingCorps']) . '</div>
                <div class="stat-label">기업인증 대기</div>
                <div class="stat-change negative">
                    <span>⚠️</span> 긴급 처리 필요
                </div>
            </div>
        </div>
        
        <!-- 메인 그리드 -->
        <div class="main-grid">
            <div class="dashboard-section">
                <h3 class="section-title">📊 최근 활동</h3>
                <p>최근 플랫폼 활동 내역이 여기에 표시됩니다.</p>
            </div>
            
            <div class="dashboard-section">
                <h3 class="section-title">🔔 알림</h3>
                <p>중요한 알림 사항이 여기에 표시됩니다.</p>
            </div>
        </div>
    </div>
';

// 레이아웃 렌더링
include SRC_PATH . '/views/templates/admin_layout.php';
?>