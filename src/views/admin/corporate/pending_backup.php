<?php
/**
 * 관리자 > 기업인증 대기 목록 페이지
 */

// CSRF 토큰 생성
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<style>
/* ===== 기업인증 대기 목록 페이지 스타일 ===== */

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

/* 페이지 콘텐츠 */
.page-content {
    padding: 40px;
}

/* 통계 요약 */
.summary-cards {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 30px;
    margin-bottom: 40px;
}

/* 필터 섹션 */
.filter-section {
    background: white;
    border-radius: 16px;
    padding: 20px 30px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
    margin-bottom: 30px;
}

.filter-row {
    display: flex;
    align-items: center;
    gap: 30px;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    align-items: center;
    gap: 8px;
}

.filter-label {
    font-weight: 500;
    color: #4a5568;
    font-size: 14px;
    white-space: nowrap;
}

.filter-input {
    padding: 8px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 14px;
    width: 300px;
    transition: border-color 0.3s ease;
}

.filter-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.filter-select {
    padding: 8px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 14px;
    background: white;
    min-width: 150px;
    transition: border-color 0.3s ease;
}

.filter-select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.summary-card {
    background: white;
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
    text-align: center;
}

.summary-card-icon {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    margin: 0 auto 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
    background: linear-gradient(135deg, #667eea, #764ba2);
}

.summary-card-number {
    font-size: 36px;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 8px;
}

.summary-card-label {
    color: #718096;
    font-size: 14px;
    font-weight: 500;
}

/* 기업인증 목록 */
.applications-section {
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

.applications-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.applications-table th,
.applications-table td {
    padding: 16px;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
}

.applications-table th {
    background: #f7fafc;
    font-weight: 600;
    color: #4a5568;
    font-size: 14px;
}

.applications-table td {
    color: #2d3748;
    font-size: 14px;
}

.company-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.company-name {
    font-weight: 600;
    color: #1a202c;
}

.company-details {
    font-size: 12px;
    color: #718096;
}

.user-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.user-nickname {
    font-weight: 500;
    color: #1a202c;
}

.user-contact {
    font-size: 12px;
    color: #718096;
}

.application-date {
    font-size: 12px;
    color: #718096;
}

.application-actions {
    display: flex;
    gap: 8px;
}

.btn-view {
    padding: 6px 12px;
    background: #667eea;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-view:hover {
    background: #5a67d8;
    text-decoration: none;
    color: white;
}

.btn-approve {
    padding: 6px 12px;
    background: #48bb78;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-approve:hover {
    background: #38a169;
}

.btn-reject {
    padding: 6px 12px;
    background: #e53e3e;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-reject:hover {
    background: #c53030;
}

/* 빈 목록 메시지 */
.empty-message {
    text-align: center;
    padding: 60px 20px;
    color: #718096;
}

.empty-icon {
    font-size: 48px;
    color: #cbd5e0;
    margin-bottom: 16px;
}

.empty-title {
    font-size: 18px;
    font-weight: 600;
    color: #4a5568;
    margin-bottom: 8px;
}

.empty-description {
    font-size: 14px;
    color: #718096;
}

/* 모달 스타일 */
.modal {
    display: none;
    position: fixed;
    z-index: 10000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    overflow-y: auto;
    padding: 20px 0;
}

.modal-content {
    background: white;
    margin: 20px auto;
    padding: 30px;
    border-radius: 16px;
    width: 90%;
    max-width: 600px;
    position: relative;
    max-height: calc(100vh - 40px);
    overflow-y: auto;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

/* 상세보기 모달은 더 크게 */
#detailModal .modal-content {
    max-width: 900px;
    width: 95%;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e2e8f0;
}

.modal-title {
    font-size: 20px;
    font-weight: 700;
    color: #1a202c;
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    color: #718096;
    cursor: pointer;
    padding: 4px;
    line-height: 1;
}

.modal-close:hover {
    color: #1a202c;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #4a5568;
    font-size: 14px;
}

.form-textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-family: inherit;
    font-size: 14px;
    resize: vertical;
    min-height: 100px;
}

.form-textarea:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 20px;
}

.btn-cancel {
    padding: 10px 20px;
    background: #e2e8f0;
    color: #4a5568;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-cancel:hover {
    background: #cbd5e0;
}

.btn-primary {
    padding: 10px 20px;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: #5a67d8;
}

.btn-success {
    padding: 10px 20px;
    background: #48bb78;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-success:hover {
    background: #38a169;
}

.btn-danger {
    padding: 10px 20px;
    background: #e53e3e;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-danger:hover {
    background: #c53030;
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
                    <a href="/admin" class="nav-item">
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
                    <a href="/admin/corporate/pending" class="nav-item active">
                        <i>🏢</i> 인증 대기
                        <?php if (count($applications) > 0): ?>
                            <span style="background: #e53e3e; color: white; padding: 2px 8px; border-radius: 10px; font-size: 12px; margin-left: auto;">
                                <?= number_format(count($applications)) ?>
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
                    <h1>기업인증 대기 목록</h1>
                    <p>승인 대기 중인 기업인증 신청을 검토하고 처리하세요</p>
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
            
            <!-- 페이지 콘텐츠 -->
            <div class="page-content">
                <!-- 통계 요약 -->
                <div class="summary-cards">
                    <div class="summary-card">
                        <div class="summary-card-icon">⏱️</div>
                        <div class="summary-card-number"><?= number_format(count($applications)) ?></div>
                        <div class="summary-card-label">대기 중인 신청</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-card-icon">📅</div>
                        <div class="summary-card-number">
                            <?= number_format(count(array_filter($applications, function($app) { 
                                return (time() - strtotime($app['created_at'])) > 86400; 
                            }))) ?>
                        </div>
                        <div class="summary-card-label">1일 이상 대기</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-card-icon">⚡</div>
                        <div class="summary-card-number">
                            <?= number_format(count(array_filter($applications, function($app) { 
                                return (time() - strtotime($app['created_at'])) > 259200; 
                            }))) ?>
                        </div>
                        <div class="summary-card-label">3일 이상 대기</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-card-icon">🔔</div>
                        <div class="summary-card-number">
                            <?= number_format(count(array_filter($applications, function($app) { 
                                return (time() - strtotime($app['created_at'])) > 604800; 
                            }))) ?>
                        </div>
                        <div class="summary-card-label">1주 이상 대기</div>
                    </div>
                </div>
                
                <!-- 검색 및 필터 섹션 -->
                <div class="filter-section">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label class="filter-label">검색:</label>
                            <input type="text" class="filter-input" id="searchInput" placeholder="회사명, 사업자번호, 신청자명으로 검색..." onkeyup="filterApplications()">
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">대기 기간:</label>
                            <select class="filter-select" id="waitTimeFilter" onchange="filterApplications()">
                                <option value="all">전체</option>
                                <option value="today">오늘 신청</option>
                                <option value="week">1주일 이내</option>
                                <option value="urgent">3일 이상 대기</option>
                                <option value="critical">1주일 이상 대기</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">기업 유형:</label>
                            <select class="filter-select" id="companyTypeFilter" onchange="filterApplications()">
                                <option value="all">전체</option>
                                <option value="domestic">국내 기업</option>
                                <option value="overseas">해외 기업</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 기업인증 목록 -->
                <div class="applications-section">
                    <div class="section-header">
                        <h3 class="section-title">🏢 기업인증 신청 목록</h3>
                        <div id="filteredCount" style="color: #718096; font-size: 14px;">
                            총 <?= number_format(count($applications)) ?>개 신청
                        </div>
                    </div>
                    
                    <?php if (empty($applications)): ?>
                        <div class="empty-message">
                            <div class="empty-icon">🏢</div>
                            <div class="empty-title">대기 중인 기업인증 신청이 없습니다</div>
                            <div class="empty-description">새로운 기업인증 신청이 들어오면 여기에 표시됩니다.</div>
                        </div>
                    <?php else: ?>
                        <table class="applications-table">
                            <thead>
                                <tr>
                                    <th>기업 정보</th>
                                    <th>신청자</th>
                                    <th>신청일</th>
                                    <th>대기시간</th>
                                    <th>관리</th>
                                </tr>
                            </thead>
                            <tbody id="applicationsTableBody">
                                <?php foreach ($applications as $app): ?>
                                    <tr data-company-name="<?= htmlspecialchars($app['company_name']) ?>" 
                                        data-business-number="<?= htmlspecialchars($app['business_number']) ?>"
                                        data-nickname="<?= htmlspecialchars($app['nickname']) ?>"
                                        data-is-overseas="<?= $app['is_overseas'] ? '1' : '0' ?>"
                                        data-created-at="<?= $app['created_at'] ?>"
                                        data-wait-days="<?= floor((time() - strtotime($app['created_at'])) / 86400) ?>">
                                        <td>
                                            <div class="company-info">
                                                <div class="company-name"><?= htmlspecialchars($app['company_name']) ?></div>
                                                <div class="company-details">
                                                    사업자번호: <?= htmlspecialchars($app['business_number']) ?><br>
                                                    대표자: <?= htmlspecialchars($app['representative_name']) ?>
                                                    <?php if ($app['is_overseas']): ?>
                                                        <span style="color: #667eea; font-weight: 500;"> (해외기업)</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="user-info">
                                                <div class="user-nickname"><?= htmlspecialchars($app['nickname']) ?></div>
                                                <div class="user-contact">
                                                    <?= htmlspecialchars($app['phone']) ?><br>
                                                    <?= htmlspecialchars($app['email']) ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="application-date">
                                                <?= date('Y-m-d H:i', strtotime($app['created_at'])) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php 
                                            $waitTime = time() - strtotime($app['created_at']);
                                            $days = floor($waitTime / 86400);
                                            $hours = floor(($waitTime % 86400) / 3600);
                                            ?>
                                            <div class="application-date" style="<?= $days >= 3 ? 'color: #e53e3e; font-weight: 600;' : '' ?>">
                                                <?php if ($days > 0): ?>
                                                    <?= $days ?>일 <?= $hours ?>시간
                                                <?php else: ?>
                                                    <?= $hours ?>시간
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="application-actions">
                                                <button class="btn-view" onclick="viewApplication(<?= $app['id'] ?>)">상세보기</button>
                                                <button class="btn-approve" onclick="processApplication(<?= $app['id'] ?>, 'approve')">승인</button>
                                                <button class="btn-reject" onclick="processApplication(<?= $app['id'] ?>, 'reject')">거절</button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <!-- 승인/거절 모달 -->
    <div id="processModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">기업인증 처리</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form id="processForm">
                <input type="hidden" id="applicationId" name="application_id">
                <input type="hidden" id="actionType" name="action">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                
                <div class="form-group">
                    <label class="form-label" for="adminNotes">관리자 메모</label>
                    <textarea 
                        class="form-textarea" 
                        id="adminNotes" 
                        name="admin_notes" 
                        placeholder="승인/거절 사유를 입력하세요..."
                        rows="4"
                    ></textarea>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal()">취소</button>
                    <button type="submit" id="submitBtn" class="btn-primary">처리</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- 상세보기 모달 -->
    <div id="detailModal" class="modal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3 class="modal-title">기업인증 신청 상세보기</h3>
                <button class="modal-close" onclick="closeDetailModal()">&times;</button>
            </div>
            <div id="detailContent">
                <!-- 상세 내용이 AJAX로 로드됩니다 -->
            </div>
        </div>
    </div>
</body>

<script>
// 전역 변수
let originalApplicationsData = [];

// 페이지 로드 시 초기화
document.addEventListener('DOMContentLoaded', function() {
    // 원본 데이터 저장 (필터링용)
    const rows = document.querySelectorAll('#applicationsTableBody tr');
    rows.forEach(row => {
        originalApplicationsData.push({
            element: row,
            companyName: row.dataset.companyName,
            businessNumber: row.dataset.businessNumber,
            nickname: row.dataset.nickname,
            isOverseas: row.dataset.isOverseas === '1',
            createdAt: row.dataset.createdAt,
            waitDays: parseInt(row.dataset.waitDays)
        });
    });
});

// 검색 및 필터링 함수
function filterApplications() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const waitTimeFilter = document.getElementById('waitTimeFilter').value;
    const companyTypeFilter = document.getElementById('companyTypeFilter').value;
    
    let visibleCount = 0;
    
    originalApplicationsData.forEach(data => {
        let show = true;
        
        // 검색어 필터링
        if (searchTerm) {
            const searchableText = (
                data.companyName + ' ' + 
                data.businessNumber + ' ' + 
                data.nickname
            ).toLowerCase();
            
            if (!searchableText.includes(searchTerm)) {
                show = false;
            }
        }
        
        // 대기 기간 필터링
        if (waitTimeFilter !== 'all') {
            const waitDays = data.waitDays;
            const today = new Date().toDateString();
            const createdDate = new Date(data.createdAt).toDateString();
            
            switch (waitTimeFilter) {
                case 'today':
                    if (today !== createdDate) show = false;
                    break;
                case 'week':
                    if (waitDays > 7) show = false;
                    break;
                case 'urgent':
                    if (waitDays < 3) show = false;
                    break;
                case 'critical':
                    if (waitDays < 7) show = false;
                    break;
            }
        }
        
        // 기업 유형 필터링
        if (companyTypeFilter !== 'all') {
            if (companyTypeFilter === 'overseas' && !data.isOverseas) {
                show = false;
            } else if (companyTypeFilter === 'domestic' && data.isOverseas) {
                show = false;
            }
        }
        
        // 결과 적용
        data.element.style.display = show ? '' : 'none';
        if (show) visibleCount++;
    });
    
    // 필터링 결과 카운트 업데이트
    document.getElementById('filteredCount').textContent = 
        `총 ${originalApplicationsData.length.toLocaleString()}개 중 ${visibleCount.toLocaleString()}개 표시`;
}

// 승인/거절 처리 모달
function processApplication(applicationId, action) {
    document.getElementById('applicationId').value = applicationId;
    document.getElementById('actionType').value = action;
    
    const modal = document.getElementById('processModal');
    const modalTitle = document.getElementById('modalTitle');
    const submitBtn = document.getElementById('submitBtn');
    const adminNotes = document.getElementById('adminNotes');
    
    if (action === 'approve') {
        modalTitle.textContent = '기업인증 승인';
        submitBtn.textContent = '승인';
        submitBtn.className = 'btn-success';
        adminNotes.placeholder = '승인 사유를 입력하세요... (선택사항)';
    } else {
        modalTitle.textContent = '기업인증 거절';
        submitBtn.textContent = '거절';
        submitBtn.className = 'btn-danger';
        adminNotes.placeholder = '거절 사유를 입력하세요...';
    }
    
    modal.style.display = 'block';
    adminNotes.focus();
}

function closeModal() {
    const modal = document.getElementById('processModal');
    modal.style.display = 'none';
    document.getElementById('processForm').reset();
}

// 상세보기 모달
async function viewApplication(applicationId) {
    const modal = document.getElementById('detailModal');
    const content = document.getElementById('detailContent');
    
    // 로딩 표시
    content.innerHTML = '<div style="text-align: center; padding: 40px;"><div style="font-size: 18px;">로딩 중...</div></div>';
    modal.style.display = 'block';
    
    try {
        const formData = new FormData();
        formData.append('application_id', applicationId);
        
        const response = await fetch('/admin/corporate/detail', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            renderApplicationDetail(result.data);
        } else {
            content.innerHTML = '<div style="text-align: center; padding: 40px; color: #e53e3e;">오류: ' + result.error + '</div>';
        }
    } catch (error) {
        content.innerHTML = '<div style="text-align: center; padding: 40px; color: #e53e3e;">상세 정보를 불러오는 중 오류가 발생했습니다.</div>';
    }
}

function closeDetailModal() {
    const modal = document.getElementById('detailModal');
    modal.style.display = 'none';
}

// 상세보기 모달 내용 렌더링
function renderApplicationDetail(data) {
    const content = document.getElementById('detailContent');
    const createdAt = new Date(data.created_at).toLocaleString('ko-KR');
    const userCreatedAt = new Date(data.user_created_at).toLocaleString('ko-KR');
    
    let html = `
        <div style="padding: 20px;">
            <!-- 기업 정보 -->
            <div style="background: #f7fafc; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                <h4 style="color: #1a202c; margin-bottom: 15px;">🏢 기업 정보</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><strong>기업명:</strong> ${data.company_name}</div>
                    <div><strong>사업자번호:</strong> ${data.business_number}</div>
                    <div><strong>대표자:</strong> ${data.representative_name}</div>
                    <div><strong>대표자 연락처:</strong> ${data.representative_phone}</div>
                    <div style="grid-column: span 2;"><strong>기업 주소:</strong> ${data.company_address}</div>
                    <div><strong>기업 유형:</strong> ${data.is_overseas ? '해외 기업' : '국내 기업'}</div>
                </div>
            </div>
            
            <!-- 신청자 정보 -->
            <div style="background: #edf2f7; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                <h4 style="color: #1a202c; margin-bottom: 15px;">👤 신청자 정보</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><strong>닉네임:</strong> ${data.nickname}</div>
                    <div><strong>연락처:</strong> ${data.phone}</div>
                    <div><strong>이메일:</strong> ${data.email}</div>
                    <div><strong>가입일:</strong> ${userCreatedAt}</div>
                </div>
            </div>
            
            <!-- 신청 정보 -->
            <div style="background: #e6fffa; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                <h4 style="color: #1a202c; margin-bottom: 15px;">📋 신청 정보</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><strong>신청일:</strong> ${createdAt}</div>
                    <div><strong>상태:</strong> <span style="color: #ed8936; font-weight: bold;">대기 중</span></div>
                </div>
            </div>
            
            <!-- 사업자등록증 -->
            <div style="background: #fff5f5; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                <h4 style="color: #1a202c; margin-bottom: 15px;">📄 첨부 서류</h4>
                <div>
                    <strong>사업자등록증:</strong>
                    <a href="/admin/document/view?file=${encodeURIComponent(data.business_registration_file)}" 
                       target="_blank" 
                       style="color: #667eea; text-decoration: none; margin-left: 10px;">
                        📎 파일 보기
                    </a>
                </div>
            </div>
    `;
    
    // 처리 이력이 있으면 표시
    if (data.history && data.history.length > 0) {
        html += `
            <div style="background: #f0f4f8; padding: 20px; border-radius: 10px;">
                <h4 style="color: #1a202c; margin-bottom: 15px;">📜 처리 이력</h4>
                <div style="max-height: 200px; overflow-y: auto;">
        `;
        
        data.history.forEach(item => {
            const historyDate = new Date(item.created_at).toLocaleString('ko-KR');
            html += `
                <div style="border-bottom: 1px solid #e2e8f0; padding: 10px 0;">
                    <div><strong>${item.action_type}:</strong> ${historyDate}</div>
                    <div>처리자: ${item.created_by_name || '시스템'}</div>
                    ${item.admin_notes ? '<div style="color: #718096;">메모: ' + item.admin_notes + '</div>' : ''}
                </div>
            `;
        });
        
        html += '</div></div>';
    }
    
    html += `
            <div style="margin-top: 20px; text-align: right;">
                <button onclick="closeDetailModal()" style="padding: 10px 20px; background: #e2e8f0; color: #4a5568; border: none; border-radius: 6px; cursor: pointer;">닫기</button>
            </div>
        </div>
    `;
    
    content.innerHTML = html;
}

// 폼 제출 처리
document.getElementById('processForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = '처리 중...';
    
    try {
        const response = await fetch('/admin/corporate/process', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message + '\n\nSMS 알림이 발송되었습니다.');
            location.reload();
        } else {
            alert('오류: ' + result.error);
        }
    } catch (error) {
        alert('처리 중 오류가 발생했습니다: ' + error.message);
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
        closeModal();
    }
});

// 모달 외부 클릭 시 닫기
window.onclick = function(event) {
    const processModal = document.getElementById('processModal');
    const detailModal = document.getElementById('detailModal');
    
    if (event.target === processModal) {
        closeModal();
    }
    if (event.target === detailModal) {
        closeDetailModal();
    }
}
</script>