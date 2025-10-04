<?php
/**
 * 관리자 > 기업회원 목록 페이지
 */
?>

<style>
/* ===== 기업회원 목록 페이지 스타일 ===== */

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
    grid-template-columns: repeat(5, 1fr);
    gap: 20px;
    margin-bottom: 40px;
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
}

.summary-card-icon.approved {
    background: linear-gradient(135deg, #48bb78, #38a169);
}

.summary-card-icon.rejected {
    background: linear-gradient(135deg, #e53e3e, #c53030);
}

.summary-card-icon.active {
    background: linear-gradient(135deg, #667eea, #764ba2);
}

.summary-card-icon.suspended {
    background: linear-gradient(135deg, #ed8936, #dd6b20);
}

.summary-card-icon.content {
    background: linear-gradient(135deg, #805ad5, #6b46c1);
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
    gap: 20px;
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
}

.filter-select {
    padding: 8px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 14px;
    background: white;
}

.filter-select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* 기업회원 목록 */
.members-section {
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

.members-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.members-table th,
.members-table td {
    padding: 16px;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
}

.members-table th {
    background: #f7fafc;
    font-weight: 600;
    color: #4a5568;
    font-size: 14px;
}

.members-table td {
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

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.status-approved {
    background: #c6f6d5;
    color: #22543d;
}

.status-rejected {
    background: #fed7d7;
    color: #c53030;
}

.status-suspended {
    background: #feebc8;
    color: #c05621;
}

.activity-stats {
    display: flex;
    flex-direction: column;
    gap: 2px;
    font-size: 12px;
    color: #718096;
}

.activity-number {
    font-weight: 600;
    color: #1a202c;
}

.approval-date {
    font-size: 12px;
    color: #718096;
}

.member-actions {
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

.btn-manage {
    padding: 6px 12px;
    background: #ed8936;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-manage:hover {
    background: #dd6b20;
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
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    overflow-y: auto;
    padding: 20px 0;
}

.modal-content {
    background-color: white;
    margin: 0 auto;
    border-radius: 16px;
    width: 90%;
    max-width: 900px;
    max-height: calc(100vh - 40px);
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-header {
    padding: 30px 40px 20px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 16px 16px 0 0;
}

.modal-header h2 {
    margin: 0;
    font-size: 24px;
    font-weight: 700;
}

.close {
    color: white;
    font-size: 32px;
    font-weight: bold;
    cursor: pointer;
    line-height: 1;
    transition: all 0.3s ease;
}

.close:hover {
    color: #f1f5f9;
    transform: scale(1.1);
}

.modal-body {
    padding: 40px;
}

/* 상세 정보 스타일 */
.detail-section {
    margin-bottom: 40px;
    background: #f8fafc;
    border-radius: 12px;
    padding: 30px;
    border: 1px solid #e2e8f0;
}

.detail-section-title {
    font-size: 18px;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.detail-label {
    font-size: 14px;
    font-weight: 600;
    color: #4a5568;
}

.detail-value {
    font-size: 16px;
    color: #1a202c;
    word-break: break-all;
}

.detail-item.full-width {
    grid-column: 1 / -1;
}

.status-badge-large {
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    display: inline-block;
}

.status-approved-large {
    background: #c6f6d5;
    color: #22543d;
}

.status-rejected-large {
    background: #fed7d7;
    color: #c53030;
}

.status-suspended-large {
    background: #feebc8;
    color: #c05621;
}

.activity-summary {
    display: flex;
    gap: 30px;
    margin-top: 10px;
}

.activity-item {
    text-align: center;
    padding: 15px;
    background: white;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    flex: 1;
}

.activity-number-large {
    font-size: 24px;
    font-weight: 700;
    color: #667eea;
    display: block;
    margin-bottom: 4px;
}

.activity-label {
    font-size: 12px;
    color: #718096;
    font-weight: 500;
}

.history-item {
    padding: 15px;
    background: white;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    margin-bottom: 12px;
}

.history-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.history-action {
    font-weight: 600;
    color: #1a202c;
}

.history-date {
    font-size: 12px;
    color: #718096;
}

.history-admin {
    font-size: 13px;
    color: #4a5568;
}

.history-notes {
    font-size: 14px;
    color: #2d3748;
    background: #f7fafc;
    padding: 10px;
    border-radius: 6px;
    margin-top: 8px;
    white-space: pre-wrap;
    word-break: break-word;
}

.document-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
    padding: 8px 12px;
    background: #edf2f7;
    border-radius: 6px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.document-link:hover {
    background: #e2e8f0;
    color: #5a67d8;
    text-decoration: none;
}

/* 관리 모달 스타일 */
.manage-tabs {
    display: flex;
    border-bottom: 2px solid #e2e8f0;
    margin-bottom: 30px;
}

.tab-btn {
    padding: 12px 24px;
    background: none;
    border: none;
    font-size: 14px;
    font-weight: 500;
    color: #718096;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    transition: all 0.3s ease;
}

.tab-btn.active {
    color: #667eea;
    border-bottom-color: #667eea;
}

.tab-btn:hover {
    color: #667eea;
    background: #f7fafc;
}

.manage-info {
    background: #f8fafc;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 30px;
    border: 1px solid #e2e8f0;
}

.manage-company-name {
    font-size: 18px;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 8px;
}

.manage-company-details {
    font-size: 14px;
    color: #718096;
    display: flex;
    gap: 20px;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.tab-content h3 {
    font-size: 18px;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #4a5568;
    margin-bottom: 8px;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
}

.btn-primary {
    padding: 12px 24px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-secondary {
    padding: 12px 24px;
    background: #e2e8f0;
    color: #4a5568;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-secondary:hover {
    background: #cbd5e0;
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
                    <a href="/admin/corporate/pending" class="nav-item">
                        <i>🏢</i> 인증 대기
                    </a>
                    <a href="/admin/corporate/list" class="nav-item active">
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
                    <h1>기업회원 목록</h1>
                    <p>승인된 기업회원과 거절된 신청을 관리하세요</p>
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
                        <div class="summary-card-icon approved">✅</div>
                        <div class="summary-card-number">
                            <?= number_format(count(array_filter($members, function($m) { return $m['status'] === 'approved'; }))) ?>
                        </div>
                        <div class="summary-card-label">승인된 기업회원</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-card-icon rejected">❌</div>
                        <div class="summary-card-number">
                            <?= number_format(count(array_filter($members, function($m) { return $m['status'] === 'rejected'; }))) ?>
                        </div>
                        <div class="summary-card-label">거절된 신청</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-card-icon suspended">⏸️</div>
                        <div class="summary-card-number">
                            <?= number_format(count(array_filter($members, function($m) { return $m['status'] === 'suspended'; }))) ?>
                        </div>
                        <div class="summary-card-label">일시정지된 기업</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-card-icon active">🚀</div>
                        <div class="summary-card-number">
                            <?php 
                            $activeMembers = array_filter($members, function($m) { 
                                return $m['status'] === 'approved' && ($m['post_count'] > 0 || $m['lecture_count'] > 0); 
                            });
                            echo number_format(count($activeMembers));
                            ?>
                        </div>
                        <div class="summary-card-label">활성 기업회원</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-card-icon content">📊</div>
                        <div class="summary-card-number">
                            <?= number_format(array_sum(array_column($members, 'post_count')) + array_sum(array_column($members, 'lecture_count'))) ?>
                        </div>
                        <div class="summary-card-label">총 콘텐츠 수</div>
                    </div>
                </div>
                
                <!-- 필터 섹션 -->
                <div class="filter-section">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label class="filter-label">상태:</label>
                            <select class="filter-select" id="statusFilter" onchange="filterTable()">
                                <option value="all">전체</option>
                                <option value="approved">승인됨</option>
                                <option value="rejected">거절됨</option>
                                <option value="suspended">일시정지</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">활동:</label>
                            <select class="filter-select" id="activityFilter" onchange="filterTable()">
                                <option value="all">전체</option>
                                <option value="active">활성 회원</option>
                                <option value="inactive">비활성 회원</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">처리 기간:</label>
                            <select class="filter-select" id="dateFilter" onchange="filterTable()">
                                <option value="all">전체 기간</option>
                                <option value="week">최근 1주일</option>
                                <option value="month">최근 1개월</option>
                                <option value="quarter">최근 3개월</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- 기업회원 목록 -->
                <div class="members-section">
                    <div class="section-header">
                        <h3 class="section-title">📋 기업회원 목록</h3>
                        <div style="color: #718096; font-size: 14px;">
                            총 <?= number_format(count($members)) ?>개 기업
                        </div>
                    </div>
                    
                    <?php if (empty($members)): ?>
                        <div class="empty-message">
                            <div class="empty-icon">🏢</div>
                            <div class="empty-title">기업회원이 없습니다</div>
                            <div class="empty-description">아직 처리된 기업인증 신청이 없습니다.</div>
                        </div>
                    <?php else: ?>
                        <table class="members-table" id="membersTable">
                            <thead>
                                <tr>
                                    <th>기업 정보</th>
                                    <th>회원 정보</th>
                                    <th>상태</th>
                                    <th>활동 내역</th>
                                    <th>승인/거절일</th>
                                    <th>관리</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($members as $member): ?>
                                    <tr data-status="<?= $member['status'] ?>" data-processed="<?= $member['processed_at'] ?>">
                                        <td>
                                            <div class="company-info">
                                                <div class="company-name"><?= htmlspecialchars($member['company_name']) ?></div>
                                                <div class="company-details">
                                                    사업자번호: <?= htmlspecialchars($member['business_number']) ?><br>
                                                    대표자: <?= htmlspecialchars($member['representative_name']) ?>
                                                    <?php if ($member['is_overseas']): ?>
                                                        <span style="color: #667eea; font-weight: 500;"> (해외기업)</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="user-info">
                                                <div class="user-nickname"><?= htmlspecialchars($member['nickname']) ?></div>
                                                <div class="user-contact">
                                                    <?= htmlspecialchars($member['phone']) ?><br>
                                                    <?= htmlspecialchars($member['email']) ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?= $member['status'] ?>">
                                                <?php 
                                                switch($member['status']) {
                                                    case 'approved': echo '승인됨'; break;
                                                    case 'rejected': echo '거절됨'; break;
                                                    case 'suspended': echo '일시정지'; break;
                                                    default: echo $member['status']; break;
                                                }
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="activity-stats">
                                                <div>게시글: <span class="activity-number"><?= number_format($member['post_count']) ?></span>개</div>
                                                <div>강의: <span class="activity-number"><?= number_format($member['lecture_count']) ?></span>개</div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="approval-date">
                                                <?php if ($member['processed_at']): ?>
                                                    <?= date('Y-m-d H:i', strtotime($member['processed_at'])) ?>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="member-actions">
                                                <button class="btn-view" onclick="viewMember(<?= $member['id'] ?>)">상세보기</button>
                                                <?php if (in_array($member['status'], ['approved', 'suspended'])): ?>
                                                    <button class="btn-manage" onclick="manageMember(<?= $member['id'] ?>)">관리</button>
                                                <?php endif; ?>
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

    <!-- 상세보기 모달 -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>📋 기업회원 상세 정보</h2>
                <span class="close" onclick="closeDetailModal()">&times;</span>
            </div>
            <div class="modal-body" id="detailModalBody">
                <!-- 상세 정보가 여기에 로드됩니다 -->
            </div>
        </div>
    </div>

    <!-- 기업회원 관리 모달 -->
    <div id="manageModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>⚙️ 기업회원 관리</h2>
                <span class="close" onclick="closeManageModal()">&times;</span>
            </div>
            <div class="modal-body">
                <!-- 관리 탭 네비게이션 -->
                <div class="manage-tabs">
                    <button class="tab-btn active" onclick="switchTab('status')">상태 관리</button>
                    <button class="tab-btn" onclick="switchTab('memo')">관리자 메모</button>
                    <button class="tab-btn" onclick="switchTab('contact')">연락처 관리</button>
                </div>

                <!-- 기업 기본 정보 표시 -->
                <div class="manage-info" id="manageInfo">
                    <!-- 선택된 기업 정보가 여기에 표시됩니다 -->
                </div>

                <!-- 상태 관리 탭 -->
                <div id="statusTab" class="tab-content active">
                    <h3>🔄 상태 변경</h3>
                    <form id="statusForm">
                        <input type="hidden" id="manageMemberId" name="member_id">
                        <input type="hidden" name="action" value="status_change">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        
                        <div class="form-group">
                            <label>새로운 상태:</label>
                            <select name="new_status" id="newStatus" required>
                                <option value="">선택하세요</option>
                                <option value="approved">승인</option>
                                <option value="suspended">일시정지</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>변경 사유:</label>
                            <textarea name="reason" placeholder="상태 변경 사유를 입력하세요..." rows="3"></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-primary">상태 변경</button>
                        </div>
                    </form>
                </div>

                <!-- 관리자 메모 탭 -->
                <div id="memoTab" class="tab-content">
                    <h3>📝 관리자 메모</h3>
                    <form id="memoForm">
                        <input type="hidden" name="member_id" id="memoMemberId">
                        <input type="hidden" name="action" value="update_memo">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        
                        <div class="form-group">
                            <label>관리자 전용 메모:</label>
                            <textarea name="admin_memo" id="adminMemo" placeholder="이 기업에 대한 관리자 메모를 작성하세요..." rows="5"></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-primary">메모 저장</button>
                        </div>
                    </form>
                </div>

                <!-- 연락처 관리 탭 -->
                <div id="contactTab" class="tab-content">
                    <h3>📞 연락처 관리</h3>
                    <form id="contactForm">
                        <input type="hidden" name="member_id" id="contactMemberId">
                        <input type="hidden" name="action" value="update_contact">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        
                        <div class="form-group">
                            <label>대표자명:</label>
                            <input type="text" name="representative_name" id="repName" required>
                        </div>
                        
                        <div class="form-group">
                            <label>대표자 연락처:</label>
                            <input type="tel" name="representative_phone" id="repPhone" required>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-primary">연락처 저장</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

<script>
// 테이블 필터링 함수
function filterTable() {
    const statusFilter = document.getElementById('statusFilter').value;
    const activityFilter = document.getElementById('activityFilter').value;
    const dateFilter = document.getElementById('dateFilter').value;
    const table = document.getElementById('membersTable');
    const rows = table.getElementsByTagName('tr');
    
    // 헤더 행은 제외하고 필터링
    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        const status = row.getAttribute('data-status');
        const processedAt = row.getAttribute('data-processed');
        
        let showRow = true;
        
        // 상태 필터
        if (statusFilter !== 'all' && status !== statusFilter) {
            showRow = false;
        }
        
        // 활동 필터
        if (activityFilter !== 'all') {
            const activityNumbers = row.querySelectorAll('.activity-number');
            const postCount = parseInt(activityNumbers[0].textContent);
            const lectureCount = parseInt(activityNumbers[1].textContent);
            const isActive = postCount > 0 || lectureCount > 0;
            
            if (activityFilter === 'active' && !isActive) {
                showRow = false;
            } else if (activityFilter === 'inactive' && isActive) {
                showRow = false;
            }
        }
        
        // 날짜 필터
        if (dateFilter !== 'all' && processedAt) {
            const processedDate = new Date(processedAt);
            const now = new Date();
            const diffTime = now - processedDate;
            const diffDays = diffTime / (1000 * 60 * 60 * 24);
            
            if (dateFilter === 'week' && diffDays > 7) {
                showRow = false;
            } else if (dateFilter === 'month' && diffDays > 30) {
                showRow = false;
            } else if (dateFilter === 'quarter' && diffDays > 90) {
                showRow = false;
            }
        }
        
        row.style.display = showRow ? '' : 'none';
    }
}

// 기업회원 상세보기
function viewMember(memberId) {
    fetch('/admin/corporate/detail', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `application_id=${memberId}&csrf_token=<?= $_SESSION['csrf_token'] ?>`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMemberDetail(data.data);
        } else {
            Toast.error('상세 정보를 불러올 수 없습니다: ' + (data.error || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Toast.error('상세 정보를 불러오는 중 오류가 발생했습니다.');
    });
}

// 상세보기 모달 표시
function showMemberDetail(member) {
    const modal = document.getElementById('detailModal');
    const modalBody = document.getElementById('detailModalBody');
    
    // 상태 배지 스타일 결정
    let statusClass, statusText;
    switch(member.status) {
        case 'approved':
            statusClass = 'status-approved-large';
            statusText = '승인됨';
            break;
        case 'rejected':
            statusClass = 'status-rejected-large';
            statusText = '거절됨';
            break;
        case 'suspended':
            statusClass = 'status-suspended-large';
            statusText = '일시정지';
            break;
        default:
            statusClass = 'status-rejected-large';
            statusText = member.status;
    }
    
    // 해외기업 여부
    const isOverseas = member.is_overseas == 1;
    
    // 모달 내용 생성
    modalBody.innerHTML = `
        <!-- 기업 기본 정보 -->
        <div class="detail-section">
            <div class="detail-section-title">🏢 기업 기본 정보</div>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">기업명</div>
                    <div class="detail-value">${member.company_name || '-'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">대표자명</div>
                    <div class="detail-value">${member.representative_name || '-'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">사업자등록번호</div>
                    <div class="detail-value">${member.business_number || '-'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">기업 유형</div>
                    <div class="detail-value">${isOverseas ? '해외기업' : '국내기업'}</div>
                </div>
                <div class="detail-item full-width">
                    <div class="detail-label">기업 주소</div>
                    <div class="detail-value">${member.company_address || '-'}</div>
                </div>
            </div>
        </div>

        <!-- 회원 정보 -->
        <div class="detail-section">
            <div class="detail-section-title">👤 회원 정보</div>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">닉네임</div>
                    <div class="detail-value">${member.nickname || '-'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">휴대폰</div>
                    <div class="detail-value">${member.phone || '-'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">이메일</div>
                    <div class="detail-value">${member.email || '-'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">가입일</div>
                    <div class="detail-value">${member.user_created_at ? new Date(member.user_created_at).toLocaleDateString('ko-KR') : '-'}</div>
                </div>
            </div>
        </div>

        <!-- 인증 상태 및 처리 정보 -->
        <div class="detail-section">
            <div class="detail-section-title">✅ 인증 상태</div>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">현재 상태</div>
                    <div class="detail-value">
                        <span class="status-badge-large ${statusClass}">${statusText}</span>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">신청일</div>
                    <div class="detail-value">${member.created_at ? new Date(member.created_at).toLocaleString('ko-KR') : '-'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">처리일</div>
                    <div class="detail-value">${member.processed_at ? new Date(member.processed_at).toLocaleString('ko-KR') : '-'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">처리자</div>
                    <div class="detail-value">${member.processed_by_name || '-'}</div>
                </div>
                ${member.admin_notes ? `
                <div class="detail-item full-width">
                    <div class="detail-label">승인/거절 사유</div>
                    <div class="detail-value">
                        <div class="history-notes">${member.admin_notes}</div>
                    </div>
                </div>
                ` : ''}
                ${member.admin_memo ? `
                <div class="detail-item full-width">
                    <div class="detail-label">관리자 전용 메모</div>
                    <div class="detail-value">
                        <div class="history-notes">${member.admin_memo}</div>
                    </div>
                </div>
                ` : ''}
            </div>
        </div>

        <!-- 활동 현황 -->
        <div class="detail-section">
            <div class="detail-section-title">📊 활동 현황</div>
            <div class="activity-summary">
                <div class="activity-item">
                    <span class="activity-number-large">${(member.post_count || 0).toLocaleString()}</span>
                    <div class="activity-label">게시글</div>
                </div>
                <div class="activity-item">
                    <span class="activity-number-large">${(member.lecture_count || 0).toLocaleString()}</span>
                    <div class="activity-label">강의</div>
                </div>
                <div class="activity-item">
                    <span class="activity-number-large">${((member.post_count || 0) + (member.lecture_count || 0)).toLocaleString()}</span>
                    <div class="activity-label">총 콘텐츠</div>
                </div>
            </div>
        </div>

        <!-- 첨부 문서 -->
        ${member.business_registration_file ? `
        <div class="detail-section">
            <div class="detail-section-title">📄 첨부 문서</div>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">사업자등록증</div>
                    <div class="detail-value">
                        <a href="/admin/document/view?file=${member.business_registration_file}" 
                           target="_blank" class="document-link">
                            📎 문서 보기
                        </a>
                    </div>
                </div>
            </div>
        </div>
        ` : ''}

        <!-- 처리 이력 -->
        ${member.history && member.history.length > 0 ? `
        <div class="detail-section">
            <div class="detail-section-title">📋 처리 이력</div>
            ${member.history.map(history => `
                <div class="history-item">
                    <div class="history-header">
                        <span class="history-action">
                            ${(() => {
                                switch(history.action_type) {
                                    case 'approve': return '✅ 승인';
                                    case 'reject': return '❌ 거절';
                                    case 'reapprove': return '🔄 재승인';
                                    case 'suspend': return '⏸️ 일시정지';
                                    case 'apply': return '📝 신청';
                                    case 'reapply': return '📝 재신청';
                                    case 'modify': return '✏️ 수정';
                                    default: return history.action_type;
                                }
                            })()}
                        </span>
                        <span class="history-date">${new Date(history.created_at).toLocaleString('ko-KR')}</span>
                    </div>
                    <div class="history-admin">처리자: ${history.created_by_name || '알 수 없음'}</div>
                    ${history.admin_notes ? `<div class="history-notes">${history.admin_notes}</div>` : ''}
                </div>
            `).join('')}
        </div>
        ` : ''}
    `;
    
    modal.style.display = 'block';
}

// 상세보기 모달 닫기
function closeDetailModal() {
    const modal = document.getElementById('detailModal');
    modal.style.display = 'none';
}

// 모달 외부 클릭 시 닫기
window.onclick = function(event) {
    const detailModal = document.getElementById('detailModal');
    const manageModal = document.getElementById('manageModal');
    
    if (event.target === detailModal) {
        closeDetailModal();
    }
    if (event.target === manageModal) {
        closeManageModal();
    }
}

// 기업회원 관리 모달 열기
function manageMember(memberId) {
    // 먼저 상세 정보를 가져와서 모달에 표시
    fetch('/admin/corporate/detail', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `application_id=${memberId}&csrf_token=<?= $_SESSION['csrf_token'] ?>`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showManageModal(data.data);
        } else {
            Toast.error('기업 정보를 불러올 수 없습니다: ' + (data.error || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Toast.error('기업 정보를 불러오는 중 오류가 발생했습니다.');
    });
}

// 관리 모달 표시
function showManageModal(member) {
    const modal = document.getElementById('manageModal');
    const manageInfo = document.getElementById('manageInfo');
    
    // 기업 정보 표시
    manageInfo.innerHTML = `
        <div class="manage-company-name">${member.company_name || '-'}</div>
        <div class="manage-company-details">
            <span>대표자: ${member.representative_name || '-'}</span>
            <span>연락처: ${member.representative_phone || '-'}</span>
            <span>상태: ${member.status === 'approved' ? '승인됨' : member.status === 'suspended' ? '일시정지' : '거절됨'}</span>
        </div>
    `;
    
    // 모든 폼의 member_id 설정
    document.getElementById('manageMemberId').value = member.id;
    document.getElementById('memoMemberId').value = member.id;
    document.getElementById('contactMemberId').value = member.id;
    
    // 현재 정보 폼에 미리 채우기
    document.getElementById('adminMemo').value = member.admin_memo || '';
    document.getElementById('repName').value = member.representative_name || '';
    document.getElementById('repPhone').value = member.representative_phone || '';
    
    // 상태 선택 기본값 설정
    const statusSelect = document.getElementById('newStatus');
    statusSelect.value = member.status === 'approved' ? 'suspended' : 'approved';
    
    modal.style.display = 'block';
}

// 관리 모달 닫기
function closeManageModal() {
    const modal = document.getElementById('manageModal');
    modal.style.display = 'none';
    
    // 폼 초기화
    document.getElementById('statusForm').reset();
    document.getElementById('memoForm').reset();
    document.getElementById('contactForm').reset();
}

// 탭 전환
function switchTab(tabName) {
    // 모든 탭 버튼과 콘텐츠에서 active 클래스 제거
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    
    // 선택된 탭 활성화
    event.target.classList.add('active');
    document.getElementById(tabName + 'Tab').classList.add('active');
}

// 상태 변경 폼 제출 (Modal.confirm() 사용)
document.getElementById('statusForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const newStatus = formData.get('new_status');
    const reason = formData.get('reason');

    if (!newStatus) {
        Toast.info('새로운 상태를 선택해주세요.');
        return;
    }

    const statusText = newStatus === 'approved' ? '승인' : '일시정지';
    if (!(await Modal.confirm(`정말 이 기업회원을 ${statusText} 상태로 변경하시겠습니까?`, {
        type: 'warning',
        confirmText: '변경',
        cancelText: '취소'
    }))) {
        return;
    }

    submitManageForm(this, '상태가 변경되었습니다.');
});

// 메모 폼 제출
document.getElementById('memoForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitManageForm(this, '관리자 메모가 저장되었습니다.');
});

// 연락처 폼 제출
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const repName = this.representative_name.value.trim();
    const repPhone = this.representative_phone.value.trim();
    
    if (!repName || !repPhone) {
        Toast.error('대표자명과 연락처를 모두 입력해주세요.');
        return;
    }
    
    submitManageForm(this, '연락처 정보가 업데이트되었습니다.');
});

// 관리 폼 제출 공통 함수
function submitManageForm(form, successMessage) {
    const formData = new FormData(form);
    
    fetch('/admin/corporate/manage', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Toast.success(successMessage);
            closeManageModal();
            // 페이지 새로고침하여 변경사항 반영
            location.reload();
        } else {
            Toast.error('오류가 발생했습니다: ' + (data.error || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Toast.error('처리 중 오류가 발생했습니다.');
    });
}
</script>