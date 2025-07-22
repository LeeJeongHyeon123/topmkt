<?php
/**
 * 관리자 대시보드 메인 페이지
 */
?>

<style>
/* ===== 관리자 대시보드 전용 스타일 ===== */

/* 기본 설정 */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body.admin-page {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    color: #1a202c;
}

/* 관리자 컨테이너 */
.admin-container {
    width: 1920px;
    min-width: 1920px;
    margin: 0 auto;
    display: flex;
    min-height: 100vh;
}

/* 사이드바 */
.admin-sidebar {
    width: 280px;
    background: linear-gradient(180deg, #2d3748 0%, #1a202c 100%);
    box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
    position: fixed;
    height: 100vh;
    z-index: 1000;
    overflow-y: auto;
}

.sidebar-header {
    padding: 30px 25px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    text-align: center;
}

.sidebar-logo {
    color: white;
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 8px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.sidebar-subtitle {
    color: #a0aec0;
    font-size: 14px;
    font-weight: 500;
}

.sidebar-nav {
    padding: 20px 0;
}

.nav-section {
    margin-bottom: 30px;
}

.nav-section-title {
    color: #718096;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 0 25px 12px;
}

.nav-item {
    display: block;
    color: #e2e8f0;
    text-decoration: none;
    padding: 12px 25px;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
}

.nav-item:hover,
.nav-item.active {
    background: rgba(255, 255, 255, 0.1);
    border-left-color: #667eea;
    color: white;
    text-decoration: none;
}

.nav-item i {
    width: 20px;
    margin-right: 12px;
    font-size: 16px;
}

/* 메인 콘텐츠 */
.admin-main {
    margin-left: 280px;
    flex: 1;
    padding: 0;
    background: #f7fafc;
}

.main-header {
    background: white;
    padding: 25px 40px;
    border-bottom: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-left h1 {
    font-size: 32px;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 4px;
}

.header-left p {
    color: #718096;
    font-size: 16px;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 20px;
}

.admin-user-info {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 16px;
    background: #f7fafc;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 16px;
}

.user-details {
    display: flex;
    flex-direction: column;
}

.user-name {
    font-weight: 600;
    color: #1a202c;
    font-size: 14px;
}

.user-role {
    color: #718096;
    font-size: 12px;
}

.logout-btn {
    padding: 10px 20px;
    background: #e53e3e;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.3s ease;
}

.logout-btn:hover {
    background: #c53030;
    text-decoration: none;
    color: white;
    transform: translateY(-1px);
}

/* 메인페이지 버튼 스타일 */
.main-site-btn {
    padding: 10px 20px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    margin-right: 15px;
}

.main-site-btn:hover {
    background: linear-gradient(135deg, #5a67d8, #6b46c1);
    text-decoration: none;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

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
    content: '';
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

/* 차트 섹션 */
.chart-section {
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

/* 긴급 처리 섹션 */
.urgent-section {
    background: white;
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
}

.urgent-item {
    display: flex;
    justify-content: between;
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


/* 스크롤바 커스터마이징 */
.admin-sidebar::-webkit-scrollbar {
    width: 6px;
}

.admin-sidebar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
}

.admin-sidebar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 3px;
}
</style>

<body class="admin-page">
    <div class="admin-container">
        <!-- 사이드바 -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">🚀 탑마케팅</div>
                <div class="sidebar-subtitle">관리자 패널</div>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">대시보드</div>
                    <a href="/admin" class="nav-item active">
                        <i>📊</i> 메인 대시보드
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">회원 관리</div>
                    <a href="/admin/users" class="nav-item">
                        <i>👥</i> 회원 목록
                    </a>
                    <a href="/admin/users/roles" class="nav-item">
                        <i>🎭</i> 권한 관리
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">기업회원</div>
                    <a href="/admin/corporate/pending" class="nav-item">
                        <i>🏢</i> 인증 대기 
                        <?php if ($todayStats['pendingCorps'] > 0): ?>
                            <span style="background: #e53e3e; color: white; padding: 2px 8px; border-radius: 10px; font-size: 12px; margin-left: auto;">
                                <?= number_format($todayStats['pendingCorps']) ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <a href="/admin/corporate/list" class="nav-item">
                        <i>📋</i> 기업회원 목록
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">콘텐츠 관리</div>
                    <a href="/admin/posts" class="nav-item">
                        <i>📝</i> 게시글 관리
                    </a>
                    <a href="/admin/comments" class="nav-item">
                        <i>💬</i> 댓글 관리
                    </a>
                    <a href="/admin/reports" class="nav-item">
                        <i>🚨</i> 신고 관리
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">시스템</div>
                    <a href="/admin/settings" class="nav-item">
                        <i>⚙️</i> 사이트 설정
                    </a>
                    <a href="/admin/logs" class="nav-item">
                        <i>📋</i> 시스템 로그
                    </a>
                    <a href="/admin/backup" class="nav-item">
                        <i>💾</i> 백업 관리
                    </a>
                </div>
            </nav>
        </aside>
        
        <!-- 메인 콘텐츠 -->
        <main class="admin-main">
            <!-- 헤더 -->
            <header class="main-header">
                <div class="header-left">
                    <h1>관리자 대시보드</h1>
                    <p>탑마케팅 플랫폼 전체 현황을 확인하세요</p>
                </div>
                <div class="header-right">
                    <a href="/" class="main-site-btn">🏠 메인페이지</a>
                    <div class="admin-user-info">
                        <div class="user-avatar">A</div>
                        <div class="user-details">
                            <div class="user-name">관리자</div>
                            <div class="user-role">시스템 관리자</div>
                        </div>
                    </div>
                    <a href="/auth/logout" class="logout-btn">🚪 로그아웃</a>
                </div>
            </header>
            
            <!-- 대시보드 콘텐츠 -->
            <div class="dashboard-content">
                <!-- 통계 카드 -->
                <div class="stats-grid">
                    <div class="stat-card primary">
                        <div class="stat-header">
                            <div class="stat-icon">👥</div>
                        </div>
                        <div class="stat-number"><?= number_format($todayStats['signups']) ?></div>
                        <div class="stat-label">오늘 신규 가입</div>
                        <div class="stat-change positive">
                            <span>↗️</span> 전일 대비 +12%
                        </div>
                    </div>
                    
                    <div class="stat-card success">
                        <div class="stat-header">
                            <div class="stat-icon">📝</div>
                        </div>
                        <div class="stat-number"><?= number_format($todayStats['posts']) ?></div>
                        <div class="stat-label">오늘 게시글</div>
                        <div class="stat-change positive">
                            <span>↗️</span> 전일 대비 +8%
                        </div>
                    </div>
                    
                    <div class="stat-card warning">
                        <div class="stat-header">
                            <div class="stat-icon">🟢</div>
                        </div>
                        <div class="stat-number"><?= number_format($todayStats['activeUsers']) ?></div>
                        <div class="stat-label">현재 활성 사용자</div>
                        <div class="stat-change positive">
                            <span>↗️</span> 실시간 접속 중
                        </div>
                    </div>
                    
                    <div class="stat-card danger">
                        <div class="stat-header">
                            <div class="stat-icon">🏢</div>
                        </div>
                        <div class="stat-number"><?= number_format($todayStats['pendingCorps']) ?></div>
                        <div class="stat-label">기업인증 대기</div>
                        <?php if ($todayStats['pendingCorps'] > 0): ?>
                            <div class="stat-change negative">
                                <span>⚠️</span> 처리 필요
                            </div>
                        <?php else: ?>
                            <div class="stat-change positive">
                                <span>✅</span> 모두 처리됨
                            </div>
                        <?php endif; ?>
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
                        
                        <?php if (!empty($urgentTasks['pendingCorps'])): ?>
                            <?php foreach ($urgentTasks['pendingCorps'] as $corp): ?>
                                <div class="urgent-item">
                                    <div class="urgent-info">
                                        <div class="urgent-title">기업인증: <?= htmlspecialchars($corp['company_name']) ?></div>
                                        <div class="urgent-meta">
                                            신청자: <?= htmlspecialchars($corp['nickname']) ?> • 
                                            <?= date('m-d H:i', strtotime($corp['created_at'])) ?>
                                        </div>
                                    </div>
                                    <div class="urgent-badge">대기</div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="urgent-item">
                                <div class="urgent-info">
                                    <div class="urgent-title">✅ 모든 작업 완료</div>
                                    <div class="urgent-meta">처리할 긴급 사항이 없습니다.</div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
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
                        
                        <?php if (!empty($recentActivities['posts'])): ?>
                            <?php foreach (array_slice($recentActivities['posts'], 0, 5) as $post): ?>
                                <div class="activity-item">
                                    <div class="activity-icon">📄</div>
                                    <div class="activity-content">
                                        <div class="activity-title">
                                            <?= htmlspecialchars(mb_substr($post['title'], 0, 30)) ?>
                                            <?= mb_strlen($post['title']) > 30 ? '...' : '' ?>
                                        </div>
                                        <div class="activity-meta">
                                            <?= htmlspecialchars($post['nickname']) ?> • 
                                            <?= date('m-d H:i', strtotime($post['created_at'])) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="activity-item">
                                <div class="activity-icon">📝</div>
                                <div class="activity-content">
                                    <div class="activity-title">게시글이 없습니다</div>
                                    <div class="activity-meta">아직 작성된 게시글이 없습니다.</div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- 최근 댓글 -->
                    <div class="activity-section">
                        <div class="section-header">
                            <h3 class="section-title">💬 최근 댓글</h3>
                        </div>
                        
                        <?php if (!empty($recentActivities['comments'])): ?>
                            <?php foreach (array_slice($recentActivities['comments'], 0, 5) as $comment): ?>
                                <div class="activity-item">
                                    <div class="activity-icon">💭</div>
                                    <div class="activity-content">
                                        <div class="activity-title">
                                            <?= htmlspecialchars(mb_substr($comment['content'], 0, 30)) ?>
                                            <?= mb_strlen($comment['content']) > 30 ? '...' : '' ?>
                                        </div>
                                        <div class="activity-meta">
                                            <?= htmlspecialchars($comment['nickname']) ?> • 
                                            <?= htmlspecialchars(mb_substr($comment['post_title'], 0, 20)) ?>
                                            <?= mb_strlen($comment['post_title']) > 20 ? '...' : '' ?> • 
                                            <?= date('m-d H:i', strtotime($comment['created_at'])) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="activity-item">
                                <div class="activity-icon">💬</div>
                                <div class="activity-content">
                                    <div class="activity-title">댓글이 없습니다</div>
                                    <div class="activity-meta">아직 작성된 댓글이 없습니다.</div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>