<?php
/**
 * 관리자 대시보드 - 새 템플릿 구조 적용
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
    /* padding은 page-content에서 처리 */
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
    gap: 40px;
    margin-bottom: 40px;
}

.chart-section {
    background: white;
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
}

.urgent-section {
    background: white;
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e2e8f0;
}

.section-title {
    font-size: 20px;
    font-weight: 700;
    color: #1a202c;
}

.chart-container {
    height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f7fafc;
    border-radius: 12px;
    color: #718096;
    font-size: 16px;
    font-weight: 500;
}

.urgent-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    background: #f7fafc;
    border-radius: 12px;
    margin-bottom: 16px;
    transition: all 0.3s ease;
}

.urgent-item:hover {
    background: #edf2f7;
    transform: translateX(5px);
}

.urgent-item:last-child {
    margin-bottom: 0;
}

.urgent-info {
    flex: 1;
}

.urgent-title {
    font-weight: 600;
    color: #1a202c;
    font-size: 14px;
    margin-bottom: 4px;
}

.urgent-meta {
    color: #718096;
    font-size: 12px;
}

.urgent-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    background: #fed7d7;
    color: #c53030;
}

/* 하단 그리드 */
.bottom-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
}

.activity-section {
    background: white;
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 0;
    border-bottom: 1px solid #e2e8f0;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 16px;
}

.activity-content {
    flex: 1;
}

.activity-title {
    font-weight: 600;
    color: #1a202c;
    font-size: 14px;
    margin-bottom: 4px;
}

.activity-meta {
    color: #718096;
    font-size: 12px;
}
</style>
';

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
                ' . ($todayStats['pendingCorps'] > 0 ? '
                    <div class="stat-change negative">
                        <span>⚠️</span> 처리 필요
                    </div>
                ' : '
                    <div class="stat-change positive">
                        <span>✅</span> 모두 처리됨
                    </div>
                ') . '
            </div>
        </div>
        
        <!-- 메인 그리드 -->
        <div class="main-grid">
            <!-- 차트 섹션 -->
            <div class="chart-section">
                <div class="section-header">
                    <h3 class="section-title">📈 주간 가입자 추이</h3>
                    <span style="color: #718096; font-size: 14px;">최근 7일</span>
                </div>
                <div class="chart-container">
                    <div>
                        <div style="font-size: 24px; margin-bottom: 12px;">📊</div>
                        <div>차트가 여기에 표시됩니다</div>
                        <div style="font-size: 12px; margin-top: 8px; opacity: 0.7;">
                            Chart.js 또는 다른 라이브러리로 구현 예정
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 긴급 처리 -->
            <div class="urgent-section">
                <div class="section-header">
                    <h3 class="section-title">🚨 긴급 처리</h3>
                </div>
                
                ' . (isset($urgentTasks['pendingCorps']) && !empty($urgentTasks['pendingCorps']) ? 
                    implode('', array_map(function($corp) {
                        return '
                            <div class="urgent-item">
                                <div class="urgent-info">
                                    <div class="urgent-title">기업인증: ' . htmlspecialchars($corp['company_name']) . '</div>
                                    <div class="urgent-meta">
                                        신청자: ' . htmlspecialchars($corp['nickname']) . ' • 
                                        ' . date('m-d H:i', strtotime($corp['created_at'])) . '
                                    </div>
                                </div>
                                <div class="urgent-badge">대기</div>
                            </div>';
                    }, $urgentTasks['pendingCorps'])) : '
                        <div class="urgent-item">
                            <div class="urgent-info">
                                <div class="urgent-title">✅ 모든 작업 완료</div>
                                <div class="urgent-meta">처리할 긴급 사항이 없습니다.</div>
                            </div>
                        </div>
                    '
                ) . '
                
                <div class="urgent-item">
                    <div class="urgent-info">
                        <div class="urgent-title">시스템 상태</div>
                        <div class="urgent-meta">모든 시스템 정상 작동 중</div>
                    </div>
                    <div style="padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; background: #c6f6d5; color: #22543d;">
                        정상
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 하단 그리드 -->
        <div class="bottom-grid">
            <!-- 최근 게시글 -->
            <div class="activity-section">
                <div class="section-header">
                    <h3 class="section-title">📝 최근 게시글</h3>
                </div>
                
                ' . (isset($recentActivities['posts']) && !empty($recentActivities['posts']) ?
                    implode('', array_map(function($post) {
                        return '
                            <div class="activity-item">
                                <div class="activity-icon">📄</div>
                                <div class="activity-content">
                                    <div class="activity-title">
                                        ' . htmlspecialchars(mb_substr($post['title'], 0, 30)) .
                                        (mb_strlen($post['title']) > 30 ? '...' : '') . '
                                    </div>
                                    <div class="activity-meta">
                                        ' . htmlspecialchars($post['nickname']) . ' • 
                                        ' . date('m-d H:i', strtotime($post['created_at'])) . '
                                    </div>
                                </div>
                            </div>';
                    }, array_slice($recentActivities['posts'], 0, 5))) : '
                        <div class="activity-item">
                            <div class="activity-icon">📝</div>
                            <div class="activity-content">
                                <div class="activity-title">게시글이 없습니다</div>
                                <div class="activity-meta">아직 작성된 게시글이 없습니다.</div>
                            </div>
                        </div>
                    '
                ) . '
            </div>
            
            <!-- 최근 댓글 -->
            <div class="activity-section">
                <div class="section-header">
                    <h3 class="section-title">💬 최근 댓글</h3>
                </div>
                
                ' . (isset($recentActivities['comments']) && !empty($recentActivities['comments']) ?
                    implode('', array_map(function($comment) {
                        return '
                            <div class="activity-item">
                                <div class="activity-icon">💭</div>
                                <div class="activity-content">
                                    <div class="activity-title">
                                        ' . htmlspecialchars(mb_substr($comment['content'], 0, 30)) .
                                        (mb_strlen($comment['content']) > 30 ? '...' : '') . '
                                    </div>
                                    <div class="activity-meta">
                                        ' . htmlspecialchars($comment['nickname']) . ' • 
                                        ' . htmlspecialchars(mb_substr($comment['post_title'], 0, 20)) .
                                        (mb_strlen($comment['post_title']) > 20 ? '...' : '') . ' • 
                                        ' . date('m-d H:i', strtotime($comment['created_at'])) . '
                                    </div>
                                </div>
                            </div>';
                    }, array_slice($recentActivities['comments'], 0, 5))) : '
                        <div class="activity-item">
                            <div class="activity-icon">💬</div>
                            <div class="activity-content">
                                <div class="activity-title">댓글이 없습니다</div>
                                <div class="activity-meta">아직 작성된 댓글이 없습니다.</div>
                            </div>
                        </div>
                    '
                ) . '
            </div>
        </div>
    </div>
';

// 레이아웃 렌더링
include SRC_PATH . '/views/templates/admin_layout.php';
?>