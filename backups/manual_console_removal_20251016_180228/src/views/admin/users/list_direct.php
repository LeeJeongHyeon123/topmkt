<?php
/**
 * 관리자 사용자 목록 페이지 (직접 렌더링 버전)
 * renderView 호환성 문제 해결
 */

// 디버깅을 위한 에러 출력 활성화
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Pagination 컴포넌트 로드
require_once SRC_PATH . '/components/ui/Pagination.php';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? '회원 목록' ?> - 탑마케팅 관리자</title>
    <meta name="robots" content="noindex, nofollow">
    
    <?php 
    // CSRF 토큰 생성
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    ?>
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?>">
    
    <!-- 기본 CSS -->
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- 프로필 이미지 모달 CSS (정확한 중앙 정렬) -->
    <link rel="stylesheet" href="/assets/css/components/profile-modal.css">
    
    
    <!-- 관리자 공통 스타일 -->
    <?php include SRC_PATH . '/views/templates/admin_styles.php'; ?>

    <!-- 🚀 v3.63.0: 필수 JavaScript 컴포넌트 로드 (HEAD에서 먼저 로드) -->
    <?php require_once SRC_PATH . '/views/includes/toast.js.php'; ?>
    <?php require_once SRC_PATH . '/views/includes/loading.js.php'; ?>
    <?php require_once SRC_PATH . '/views/includes/api-client.js.php'; ?>
    <?php require_once SRC_PATH . '/views/includes/date-utils.js.php'; ?>
    <?php require_once SRC_PATH . '/views/includes/utils.js.php'; ?>

    <script>
    console.log('✅ [Admin Users Direct] 모든 컴포넌트 로드 완료:', {
        Toast: typeof window.Toast,
        Loading: typeof window.Loading,
        ApiClient: typeof window.ApiClient,
        DateUtils: typeof window.DateUtils,
        formatNumber: typeof window.formatNumber,
        formatPhone: typeof window.formatPhone
    });
    </script>
</head>
<body class="admin-page">
    <div class="admin-container">
        <!-- 사이드바 -->
        <?php include SRC_PATH . '/views/templates/admin_sidebar.php'; ?>
        
        <!-- 메인 콘텐츠 -->
        <main class="admin-main">
            <!-- 헤더 -->
            <header class="main-header">
                <div class="header-left">
                    <h1><?= $page_title ?? '회원 관리' ?></h1>
                    <p><?= $page_description ?? '등록된 회원들을 관리하고 모니터링하세요' ?></p>
                </div>
                <div class="header-right">
                    <a href="/" class="main-site-btn">🏠 메인페이지</a>
                    <div class="admin-user-info">
                        <?php 
                        $currentUser = AuthMiddleware::getCurrentUser();
                        $userInitial = $currentUser ? strtoupper(mb_substr($currentUser['nickname'], 0, 1)) : 'A';
                        $userName = $currentUser['nickname'] ?? '관리자';
                        $userRole = $currentUser['role'] === 'ROLE_ADMIN' ? '시스템 관리자' : 
                                   ($currentUser['role'] === 'SUPER_ADMIN' ? '최고 관리자' : '관리자');
                        ?>
                        <div class="user-avatar"><?= $userInitial ?></div>
                        <div class="user-details">
                            <div class="user-name"><?= htmlspecialchars($userName) ?></div>
                            <div class="user-role"><?= htmlspecialchars($userRole) ?></div>
                        </div>
                    </div>
                    <a href="/auth/logout" class="logout-btn">🚪 로그아웃</a>
                </div>
            </header>
            
            <!-- 페이지 콘텐츠 -->
            <div class="page-content">
                <div class="admin-content">
                    <div class="page-header">
                        <h1>👥 회원 관리</h1>
                        <p>등록된 회원들을 관리하고 모니터링하세요</p>
                    </div>

                    <div class="stats-cards">
                        <div class="stat-card">
                            <h3>총 회원 수</h3>
                            <div class="stat-number" id="totalUsers">로딩 중...</div>
                        </div>
                        <div class="stat-card">
                            <h3>오늘 신규 가입</h3>
                            <div class="stat-number" id="todaySignups">로딩 중...</div>
                        </div>
                        <div class="stat-card">
                            <h3>활성 회원</h3>
                            <div class="stat-number" id="activeUsers">로딩 중...</div>
                        </div>
                    </div>

                    <div class="filters-section">
                        <div class="filter-row">
                            <select id="statusFilter" class="filter-select">
                                <option value="all">모든 상태</option>
                                <option value="active">활성</option>
                                <option value="inactive">비활성</option>
                                <option value="suspended">정지</option>
                            </select>
                            
                            <select id="roleFilter" class="filter-select">
                                <option value="all">모든 권한</option>
                                <option value="ROLE_USER">일반회원</option>
                                <option value="ROLE_CORPORATE">기업회원</option>
                                <option value="ROLE_ADMIN">관리자</option>
                            </select>
                            
                            <input type="text" id="searchInput" placeholder="닉네임, 이메일, 전화번호 검색..." class="search-input">
                            
                            <button onclick="loadUsersData()" class="btn-primary">검색</button>
                            <button onclick="exportUsers()" class="btn-secondary">내보내기</button>
                        </div>
                    </div>

                    <div class="users-table-container">
                        <div id="loadingIndicator" class="loading-indicator">
                            <div class="spinner"></div>
                            <p>데이터를 불러오는 중...</p>
                        </div>
                        
                        <table id="usersTable" class="users-table" style="display: none;">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAll"></th>
                                    <th>ID</th>
                                    <th>닉네임</th>
                                    <th>이메일</th>
                                    <th>전화번호</th>
                                    <th>권한</th>
                                    <th>상태</th>
                                    <th>가입일</th>
                                    <th>작업</th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody">
                                <!-- 사용자 데이터가 여기에 로드됩니다 -->
                            </tbody>
                        </table>
                        
                        <div id="noDataMessage" class="no-data-message" style="display: none;">
                            <p>조건에 맞는 사용자가 없습니다.</p>
                        </div>
                    </div>

                    <div class="pagination-container">
                        <div id="paginationInfo" class="pagination-info"></div>
                        <div id="paginationControls" class="pagination-controls"></div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- 사용자 상세보기 모달 -->
    <div id="userDetailModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>👤 사용자 상세 정보</h2>
                <button class="modal-close" onclick="closeUserDetailModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="userDetailContent">
                    <div class="loading-indicator">
                        <div class="spinner"></div>
                        <p>사용자 정보를 불러오는 중...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    .admin-content {
        max-width: 100%;
        margin: 0;
        padding: 20px;
    }

    .page-header {
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 1px solid #e2e8f0;
    }

    .page-header h1 {
        color: #2d3748;
        margin: 0 0 10px 0;
        font-size: 2em;
    }

    .stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
    }

    .stat-card h3 {
        margin: 0 0 10px 0;
        font-size: 0.9em;
        opacity: 0.9;
    }

    .stat-number {
        font-size: 2em;
        font-weight: bold;
    }

    .filters-section {
        background: #f8fafc;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .filter-row {
        display: flex;
        gap: 15px;
        align-items: center;
        flex-wrap: wrap;
    }

    .filter-select, .search-input {
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        font-size: 14px;
    }

    .search-input {
        flex: 1;
        min-width: 200px;
    }

    .btn-primary, .btn-secondary {
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        transition: background-color 0.2s;
    }

    .btn-primary {
        background: #667eea;
        color: white;
    }

    .btn-primary:hover {
        background: #5a67d8;
    }

    .btn-secondary {
        background: #e2e8f0;
        color: #4a5568;
    }

    .btn-secondary:hover {
        background: #cbd5e0;
    }

    .users-table-container {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .users-table {
        width: 100%;
        border-collapse: collapse;
    }

    .users-table th,
    .users-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #e2e8f0;
    }

    .users-table th {
        background: #f8fafc;
        font-weight: 600;
        color: #4a5568;
    }

    .users-table tbody tr:hover {
        background: #f8fafc;
    }

    .loading-indicator {
        text-align: center;
        padding: 40px;
    }

    .spinner {
        border: 3px solid #f3f3f3;
        border-top: 3px solid #667eea;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        animation: spin 1s linear infinite;
        margin: 0 auto 10px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .no-data-message {
        text-align: center;
        padding: 40px;
        color: #718096;
    }

    .pagination-container {
        margin-top: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .pagination-info {
        color: #718096;
        font-size: 14px;
    }

    .status-badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }

    .status-active { background: #c6f6d5; color: #22543d; }
    .status-inactive { background: #fed7d7; color: #742a2a; }
    .status-suspended { background: #feebc8; color: #744210; }

    .role-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }

    .role-user { background: #e2e8f0; color: #4a5568; }
    .role-corporate { background: #bee3f8; color: #2a69ac; }
    .role-admin { background: #fbb6ce; color: #97266d; }

    .action-buttons {
        display: flex;
        gap: 5px;
    }

    .action-btn {
        padding: 4px 8px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        transition: opacity 0.2s;
    }

    .action-btn:hover {
        opacity: 0.8;
    }

    .btn-view { background: #e2e8f0; }
    .btn-profile { background: #c6f6d5; }
    .btn-edit { background: #bee3f8; }
    .btn-delete { background: #fed7d7; }

    /* 모달 스타일 */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 10000;
    }

    .modal-content {
        background: white;
        border-radius: 12px;
        max-width: 800px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }

    .modal-header {
        padding: 20px 30px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px 12px 0 0;
    }

    .modal-header h2 {
        margin: 0;
        font-size: 1.5em;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 24px;
        color: white;
        cursor: pointer;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        justify-content: center;
        align-items: center;
        border-radius: 50%;
        transition: background-color 0.2s;
    }

    .modal-close:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .modal-body {
        padding: 30px;
    }

    .user-detail-section {
        margin-bottom: 30px;
    }

    .user-detail-section h3 {
        color: #2d3748;
        margin-bottom: 15px;
        padding-bottom: 8px;
        border-bottom: 2px solid #e2e8f0;
        font-size: 1.2em;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .detail-item {
        padding: 15px;
        background: #f8fafc;
        border-radius: 8px;
        border-left: 4px solid #667eea;
    }

    .detail-label {
        font-weight: 600;
        color: #4a5568;
        font-size: 0.9em;
        margin-bottom: 5px;
    }

    .detail-value {
        color: #2d3748;
        font-size: 1em;
        word-break: break-word;
    }

    .profile-image-container {
        text-align: center;
        margin-bottom: 20px;
    }

    .profile-image-large {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #e2e8f0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .user-status-badges {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 10px;
        justify-content: center;
    }
    
    /* 편집 모달 스타일 */
    #editUserModal .modal-content {
        max-width: 600px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
    }
    
    .form-section {
        margin-bottom: 30px;
        padding: 20px;
        background: #f8fafc;
        border-radius: 8px;
        border-left: 4px solid #667eea;
    }
    
    .form-section h3 {
        margin: 0 0 20px 0;
        color: #2d3748;
        font-size: 1.1em;
        font-weight: 600;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
        color: #4a5568;
        font-size: 0.9em;
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 14px;
        transition: border-color 0.2s ease;
        box-sizing: border-box;
    }
    
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        border-color: #667eea;
        outline: none;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .form-group textarea {
        resize: vertical;
        min-height: 80px;
    }
    
    .char-count {
        display: block;
        text-align: right;
        margin-top: 5px;
        font-size: 12px;
        color: #7f8c8d;
    }
    
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #e2e8f0;
    }
    
    .form-actions .btn {
        padding: 12px 24px;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .form-actions .btn-primary {
        background: #667eea;
        color: white;
    }
    
    .form-actions .btn-primary:hover:not(:disabled) {
        background: #5a67d8;
        transform: translateY(-1px);
    }
    
    .form-actions .btn-primary:disabled {
        background: #a0aec0;
        cursor: not-allowed;
        transform: none;
    }
    
    .form-actions .btn-secondary {
        background: #e2e8f0;
        color: #4a5568;
    }
    
    .form-actions .btn-secondary:hover {
        background: #cbd5e0;
        transform: translateY(-1px);
    }
    
    /* 반응형 편집 모달 */
    @media (max-width: 768px) {
        #editUserModal .modal-content {
            width: 95%;
            margin: 20px;
            max-height: calc(100vh - 40px);
        }
        
        .form-section {
            padding: 15px;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .form-actions .btn {
            width: 100%;
            justify-content: center;
        }
    }
    </style>

    <script>
    // 전역 변수
    let currentPage = 1;
    let currentFilters = {};
    let isLoading = false;
    
    // CSRF 토큰 설정
    window.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // 페이지 로드 시 초기화
    document.addEventListener("DOMContentLoaded", function() {
        loadUserStats();
        loadUsersData();
        setupEventListeners();
    });

    // 이벤트 리스너 설정
    function setupEventListeners() {
        // 검색 입력 디바운싱
        let searchTimeout;
        document.getElementById('searchInput').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                loadUsersData();
            }, 500);
        });
        
        // 필터 변경 시 자동 검색
        document.getElementById('statusFilter').addEventListener('change', loadUsersData);
        document.getElementById('roleFilter').addEventListener('change', loadUsersData);
        
        // 전체 선택 체크박스
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.user-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    }

    // 사용자 통계 로드
    async function loadUserStats() {
        try {
            console.log('📊 통계 로딩 시작...');

            // v3.42.0: ApiClient 사용
            const data = await ApiClient.get('/admin/getUserStats', { noLoading: true });

            console.log('📊 받은 데이터:', data);

            // ApiClient가 정규화한 응답 구조: {success, data, message}
            if (data.success && data.data) {
                const stats = data.data;
                document.getElementById('totalUsers').textContent = stats.total_users || 0;
                document.getElementById('todaySignups').textContent = stats.today_signups || 0;
                document.getElementById('activeUsers').textContent = stats.active_users || 0;
                console.log('✅ 통계 로딩 성공:', stats);
            } else {
                throw new Error(data.message || '통계 데이터가 없습니다');
            }
        } catch (error) {
            console.error('❌ 통계 로드 오류:', error);
            document.getElementById('totalUsers').textContent = '오류';
            document.getElementById('todaySignups').textContent = '오류';
            document.getElementById('activeUsers').textContent = '오류';
        }
    }

    // 사용자 데이터 로드
    async function loadUsersData() {
        if (isLoading) return;
        
        isLoading = true;
        document.getElementById('loadingIndicator').style.display = 'block';
        document.getElementById('usersTable').style.display = 'none';
        document.getElementById('noDataMessage').style.display = 'none';
        
        // 현재 필터 수집
        currentFilters = {
            status: document.getElementById('statusFilter').value,
            role: document.getElementById('roleFilter').value,
            search: document.getElementById('searchInput').value.trim(),
            page: currentPage
        };
        
        try {
            const params = new URLSearchParams(currentFilters);

            // v3.42.0: ApiClient 사용
            const data = await ApiClient.get(`/admin/users/data?${params}`, { noLoading: true });

            if (data.success) {
                renderUsersTable(data.data.users);
                renderPagination(data.data);
            } else {
                console.error('데이터 로드 실패:', data.message);
                document.getElementById('noDataMessage').style.display = 'block';
            }
        } catch (error) {
            console.error('사용자 데이터 로드 오류:', error);
            document.getElementById('noDataMessage').style.display = 'block';
        } finally {
            isLoading = false;
            document.getElementById('loadingIndicator').style.display = 'none';
        }
    }

    // 사용자 테이블 렌더링
    function renderUsersTable(users) {
        const tbody = document.getElementById('usersTableBody');
        
        if (!users || users.length === 0) {
            document.getElementById('noDataMessage').style.display = 'block';
            return;
        }
        
        tbody.innerHTML = users.map(user => `
            <tr>
                <td><input type="checkbox" class="user-checkbox" value="${user.id}"></td>
                <td>${user.id}</td>
                <td>${escapeHtml(user.nickname)}</td>
                <td>${escapeHtml(user.email)}</td>
                <td>${escapeHtml(user.phone || '')}</td>
                <td><span class="role-badge role-${user.role.toLowerCase().replace('role_', '')}">${getRoleText(user.role)}</span></td>
                <td><span class="status-badge status-${user.status}">${getStatusText(user.status)}</span></td>
                <td>${formatDate(user.created_at)}</td>
                <td>
                    <div class="action-buttons">
                        <button class="action-btn btn-view" onclick="viewUserDetail(${user.id})" title="상세보기">👁️</button>
                        <button class="action-btn btn-profile" onclick="viewUserProfile(${user.id})" title="프로필 보기">👤</button>
                        <button class="action-btn btn-edit" onclick="editUser(${user.id})" title="편집">✏️</button>
                    </div>
                </td>
            </tr>
        `).join('');
        
        document.getElementById('usersTable').style.display = 'table';
    }

    // 페이지네이션 렌더링
    function renderPagination(data) {
        const info = document.getElementById('paginationInfo');
        const controls = document.getElementById('paginationControls');
        
        if (data.total === 0) {
            info.textContent = '';
            controls.innerHTML = '';
            return;
        }
        
        const start = (data.page - 1) * data.limit + 1;
        const end = Math.min(data.page * data.limit, data.total);
        
        info.textContent = `${start}-${end} / 총 ${data.total}개`;
        
        // 페이지 컨트롤 생성
        let paginationHTML = '';
        
        if (data.page > 1) {
            paginationHTML += `<button onclick="changePage(${data.page - 1})" class="btn-secondary">이전</button>`;
        }
        
        paginationHTML += `<span style="margin: 0 10px;">페이지 ${data.page} / ${data.total_pages}</span>`;
        
        if (data.page < data.total_pages) {
            paginationHTML += `<button onclick="changePage(${data.page + 1})" class="btn-secondary">다음</button>`;
        }
        
        controls.innerHTML = paginationHTML;
    }

    // 페이지 변경
    function changePage(page) {
        currentPage = page;
        loadUsersData();
    }

    // 사용자 상세보기
    async function viewUserDetail(userId) {
        try {
            // 모달 표시
            document.getElementById('userDetailModal').style.display = 'flex';
            document.body.style.overflow = 'hidden'; // 배경 스크롤 방지
            
            // 로딩 상태 표시
            document.getElementById('userDetailContent').innerHTML = 
                '<div class="loading-indicator"><div class="spinner"></div><p>사용자 정보를 불러오는 중...</p></div>';
            
            console.log('👤 사용자 ID ' + userId + ' 상세 정보 로딩 시작...');

            // v3.42.0: ApiClient 사용
            const data = await ApiClient.get('/admin/users/' + userId + '/detail', { noLoading: true });

            console.log('👤 받은 사용자 데이터:', data);
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            // 사용자 상세 정보 렌더링
            renderUserDetail(data.data || data);
            
        } catch (error) {
            console.error('❌ 사용자 상세 정보 로드 오류:', error);
            document.getElementById('userDetailContent').innerHTML = 
                '<div class="no-data-message"><p>❌ 사용자 정보를 불러올 수 없습니다</p><p><strong>오류:</strong> ' + error.message + '</p></div>';
        }
    }

    // 사용자 상세 정보 렌더링
    function renderUserDetail(user) {
        const profileImage = user.profile_image_thumb || user.profile_image || '/assets/uploads/default-avatar.png';
        
        const html = 
            '<div class="profile-image-container">' +
                '<img src="' + profileImage + '" alt="프로필 이미지" class="profile-image-large profile-image-clickable" ' +
                'title="클릭하면 큰 이미지로 볼 수 있습니다" ' +
                'onclick="openProfileImageModal(\'' + (user.profile_image_original || user.profile_image || profileImage) + '\', \'' + escapeHtml(user.nickname) + '\')" ' +
                'onerror="this.src=\'/assets/uploads/default-avatar.png\'">' +
                '<h3>' + escapeHtml(user.nickname) + '</h3>' +
                '<div class="user-status-badges">' +
                    '<span class="role-badge role-' + user.role.toLowerCase().replace('role_', '') + '">' + getRoleText(user.role) + '</span>' +
                    '<span class="status-badge status-' + user.status + '">' + getStatusText(user.status) + '</span>' +
                    (user.phone_verified == '1' || user.phone_verified === 1 || user.phone_verified === true ? '<span class="status-badge status-active">전화 인증됨</span>' : '<span class="status-badge status-inactive">전화 미인증</span>') +
                '</div>' +
            '</div>' +
            
            '<div class="user-detail-section">' +
                '<h3>📋 기본 정보</h3>' +
                '<div class="detail-grid">' +
                    '<div class="detail-item"><div class="detail-label">사용자 ID</div><div class="detail-value">' + user.id + '</div></div>' +
                    '<div class="detail-item"><div class="detail-label">닉네임</div><div class="detail-value">' + escapeHtml(user.nickname) + '</div></div>' +
                    '<div class="detail-item"><div class="detail-label">이메일</div><div class="detail-value">' + escapeHtml(user.email) + '</div></div>' +
                    '<div class="detail-item"><div class="detail-label">전화번호</div><div class="detail-value">' + escapeHtml(user.phone || '등록되지 않음') + '</div></div>' +
                    (user.real_name ? '<div class="detail-item"><div class="detail-label">실명</div><div class="detail-value">' + escapeHtml(user.real_name) + '</div></div>' : '') +
                    (user.birth_date ? '<div class="detail-item"><div class="detail-label">생년월일</div><div class="detail-value">' + escapeHtml(user.birth_date) + '</div></div>' : '') +
                '</div>' +
            '</div>' +
            
            '<div class="user-detail-section">' +
                '<h3>🏢 기업 정보</h3>' +
                '<div class="detail-grid">' +
                    '<div class="detail-item"><div class="detail-label">기업 상태</div><div class="detail-value">' + getCorpStatusText(user.corp_status) + '</div></div>' +
                    (user.company_name ? '<div class="detail-item"><div class="detail-label">회사명</div><div class="detail-value">' + escapeHtml(user.company_name) + '</div></div>' : '') +
                    (user.business_number ? '<div class="detail-item"><div class="detail-label">사업자번호</div><div class="detail-value">' + escapeHtml(user.business_number) + '</div></div>' : '') +
                    (user.representative_name ? '<div class="detail-item"><div class="detail-label">대표자명</div><div class="detail-value">' + escapeHtml(user.representative_name) + '</div></div>' : '') +
                    (user.representative_phone ? '<div class="detail-item"><div class="detail-label">대표자 연락처</div><div class="detail-value">' + escapeHtml(user.representative_phone) + '</div></div>' : '') +
                    (user.company_address ? '<div class="detail-item"><div class="detail-label">회사 주소</div><div class="detail-value">' + escapeHtml(user.company_address) + '</div></div>' : '') +
                '</div>' +
            '</div>' +
            
            '<div class="user-detail-section">' +
                '<h3>📊 활동 통계</h3>' +
                '<div class="detail-grid">' +
                    '<div class="detail-item"><div class="detail-label">게시글 수</div><div class="detail-value">' + (user.post_count || 0) + '개</div></div>' +
                    '<div class="detail-item"><div class="detail-label">댓글 수</div><div class="detail-value">' + (user.comment_count || 0) + '개</div></div>' +
                    '<div class="detail-item"><div class="detail-label">로그인 시도</div><div class="detail-value">' + (user.login_attempts || 0) + '회</div></div>' +
                    '<div class="detail-item"><div class="detail-label">최근 로그인</div><div class="detail-value">' + (user.last_login ? formatDateTime(user.last_login) : '없음') + '</div></div>' +
                    '<div class="detail-item"><div class="detail-label">가입일</div><div class="detail-value">' + formatDateTime(user.created_at) + '</div></div>' +
                    '<div class="detail-item"><div class="detail-label">계정 수정일</div><div class="detail-value">' + (user.updated_at ? formatDateTime(user.updated_at) : '없음') + '</div></div>' +
                '</div>' +
            '</div>';
        
        document.getElementById('userDetailContent').innerHTML = html;
    }

    // 모달 닫기
    function closeUserDetailModal() {
        document.getElementById('userDetailModal').style.display = 'none';
        document.body.style.overflow = ''; // 스크롤 복원
    }

    // 모달 외부 클릭 시 닫기
    document.addEventListener('click', function(event) {
        const modal = document.getElementById('userDetailModal');
        if (event.target === modal) {
            closeUserDetailModal();
        }
    });

    // ESC 키로 모달 닫기
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const modal = document.getElementById('userDetailModal');
            if (modal && modal.style.display !== 'none') {
                closeUserDetailModal();
            }
        }
    });

    // 사용자 편집 모달 열기
    function editUser(userId) {
        console.log('🖊️ 사용자 편집 모달 열기:', userId);

        // v3.42.0: ApiClient 사용
        ApiClient.get(`/admin/users/${userId}/detail`, { noLoading: true })
            .then(data => {
                console.log('📊 API 응답 데이터:', data); // 디버깅용
                if (data.success) {
                    // getUserDetail API는 data.data 형태로 사용자 정보를 반환
                    showEditUserModal(data.data);
                } else {
                    Toast.error('사용자 정보를 불러올 수 없습니다: ' + (data.error || '알 수 없는 오류'));
                }
            })
            .catch(error => {
                console.error('사용자 정보 조회 오류:', error);
                Toast.error('사용자 정보를 불러오는 중 오류가 발생했습니다.');
            });
    }
    
    // 사용자 편집 모달 표시
    function showEditUserModal(user) {
        // 기존 모달이 있으면 제거
        const existingModal = document.getElementById('editUserModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        const modalHTML = `
        <div id="editUserModal" class="modal-overlay" onclick="closeEditUserModal()">
            <div class="modal-content" onclick="event.stopPropagation()">
                <div class="modal-header">
                    <h2>👤 사용자 편집: ${escapeHtml(user.nickname)}</h2>
                    <button class="modal-close" onclick="closeEditUserModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm" onsubmit="submitUserEdit(event, ${user.id})">
                        <div class="form-section">
                            <h3>📝 기본 정보</h3>
                            
                            <div class="form-group">
                                <label for="edit_nickname">닉네임 *</label>
                                <input type="text" id="edit_nickname" name="nickname" 
                                       value="${escapeHtml(user.nickname)}" 
                                       required minlength="2" maxlength="20">
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_email">이메일</label>
                                <input type="email" id="edit_email" name="email" 
                                       value="${escapeHtml(user.email || '')}">
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_phone">전화번호 *</label>
                                <input type="tel" id="edit_phone" name="phone" 
                                       value="${escapeHtml(user.phone || '')}"
                                       placeholder="010-1234-5678"
                                       pattern="[0-9]{2,3}-[0-9]{3,4}-[0-9]{4}"
                                       required>
                                <small>형식: 010-1234-5678</small>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3>⚙️ 계정 설정</h3>
                            
                            <div class="form-group">
                                <label for="edit_status">계정 상태</label>
                                <select id="edit_status" name="status">
                                    <option value="active" ${user.status === 'active' ? 'selected' : ''}>활성</option>
                                    <option value="inactive" ${user.status === 'inactive' ? 'selected' : ''}>비활성</option>
                                    <option value="suspended" ${user.status === 'suspended' ? 'selected' : ''}>정지</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_role">권한</label>
                                <select id="edit_role" name="role">
                                    <option value="ROLE_USER" ${user.role === 'ROLE_USER' ? 'selected' : ''}>일반 사용자</option>
                                    <option value="ROLE_CORPORATE" ${user.role === 'ROLE_CORPORATE' ? 'selected' : ''}>기업 회원</option>
                                    <option value="ROLE_ADMIN" ${user.role === 'ROLE_ADMIN' ? 'selected' : ''}>관리자</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="btn btn-secondary" onclick="closeEditUserModal()">취소</button>
                            <button type="submit" class="btn btn-primary" id="saveEditBtn">💾 저장</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        document.body.style.overflow = 'hidden';

        // 편집 모달 초기화 완료

        // 전화번호 자동 포맷팅 설정
        const phoneInput = document.getElementById('edit_phone');
        if (phoneInput) {
            // 하이픈 자동 삽입 (input 이벤트)
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value;
                let cursorPosition = e.target.selectionStart;

                // 숫자만 추출
                const numbers = value.replace(/[^\d]/g, '');

                // 입력이 비어있으면 빈 문자열 반환
                if (numbers.length === 0) {
                    e.target.value = '';
                    return;
                }

                // 전화번호 포맷팅 (010 형식만 지원)
                let formatted = '';

                if (numbers.length <= 3) {
                    formatted = numbers;
                } else if (numbers.length <= 7) {
                    formatted = numbers.substring(0, 3) + '-' + numbers.substring(3);
                } else {
                    formatted = numbers.substring(0, 3) + '-' + numbers.substring(3, 7) + '-' + numbers.substring(7, 11);
                }

                // 포맷팅된 값 적용
                e.target.value = formatted;

                // 커서 위치 조정 (하이픈이 추가된 경우 커서 위치 보정)
                const diff = formatted.length - value.length;
                if (diff > 0) {
                    e.target.setSelectionRange(cursorPosition + diff, cursorPosition + diff);
                } else {
                    e.target.setSelectionRange(cursorPosition, cursorPosition);
                }
            });

            // 010-으로 시작하는지 검증 (blur 이벤트)
            phoneInput.addEventListener('blur', function(e) {
                const value = e.target.value.trim();

                // 값이 있고, 010-으로 시작하지 않으면 에러 표시
                if (value && !value.startsWith('010-')) {
                    Toast.error('❌ 전화번호는 010-으로 시작해야 합니다');
                    e.target.focus();
                }
            });

            console.log('✅ 전화번호 자동 포맷팅 및 검증 설정 완료');
        }

        console.log('✅ 편집 모달 생성 완료');
    }
    
    // 사용자 편집 폼 제출
    function submitUserEdit(event, userId) {
        event.preventDefault();
        
        const form = event.target;
        const saveBtn = document.getElementById('saveEditBtn');
        const originalText = saveBtn.textContent;
        
        // 버튼 상태 변경
        saveBtn.disabled = true;
        saveBtn.textContent = '💾 저장 중...';
        
        // 폼 데이터 수집
        const formData = new FormData(form);
        
        // CSRF 토큰 추가
        if (window.csrfToken) {
            formData.append('csrf_token', window.csrfToken);
        }
        
        
        console.log('📤 사용자 편집 요청 전송:', userId);

        // v3.42.0: ApiClient 사용 (FormData는 자동으로 multipart/form-data로 처리)
        ApiClient.post(`/admin/users/${userId}/edit`, formData, {
            headers: {}, // Content-Type 자동 설정을 위해 빈 객체 전달
            noLoading: true
        })
        .then(data => {
            if (data.success) {
                Toast.success('✅ ' + data.message);
                
                // 변경 사항이 있다면 표시
                if (data.changes && data.changes.length > 0) {
                    console.log('📝 변경 사항:', data.changes.join(', '));
                }
                
                closeEditUserModal();

                // 사용자 목록 새로고침
                if (typeof loadUsersData === 'function') {
                    loadUsersData();
                }
            } else {
                Toast.error('❌ 편집 실패: ' + data.error);
            }
        })
        .catch(error => {
            console.error('편집 요청 오류:', error);
            Toast.error('편집 중 오류가 발생했습니다.');
        })
        .finally(() => {
            // 버튼 상태 복원
            saveBtn.disabled = false;
            saveBtn.textContent = originalText;
        });
    }
    
    // 사용자 편집 모달 닫기
    function closeEditUserModal() {
        const modal = document.getElementById('editUserModal');
        if (modal) {
            modal.remove();
            document.body.style.overflow = '';
            console.log('✅ 편집 모달 닫기 완료');
        }
    }

    // 데이터 내보내기
    function exportUsers() {
        const params = new URLSearchParams(currentFilters);
        window.open(`/admin/users/export?${params}`, '_blank');
    }

    // 유틸리티 함수들
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function getRoleText(role) {
        const roleMap = {
            'ROLE_USER': '일반회원',
            'ROLE_CORPORATE': '기업회원',
            'ROLE_ADMIN': '관리자',
            'ROLE_SUPER_ADMIN': '슈퍼관리자'
        };
        return roleMap[role] || role;
    }

    function getStatusText(status) {
        const statusMap = {
            'active': '활성',
            'inactive': '비활성',
            'suspended': '정지',
            'pending': '대기'
        };
        return statusMap[status] || status;
    }

    // 🚀 Phase 7: formatDate/formatDateTime 중복 제거
    // DateUtils (date-utils.js.php) 전역 함수 사용
    // window.formatDate(), window.formatDateTime() 자동 사용

    // 기업 상태 텍스트 변환 함수
    function getCorpStatusText(status) {
        const statusMap = {
            'approved': '승인됨',
            'pending': '승인 대기',
            'rejected': '거절됨',
            'none': '미신청',
            null: '미신청',
            undefined: '미신청'
        };
        return statusMap[status] || '알 수 없음';
    }

    // 프로필 이미지 모달 안전한 열기 함수
    function openProfileImageModal(imageSrc, userName) {
        console.log('🖼️ 프로필 이미지 모달 열기 시도:', imageSrc, userName);
        
        if (typeof window.profileModal !== 'undefined' && window.profileModal.show) {
            // ProfileImageModal이 정상 로드된 경우
            console.log('✅ ProfileImageModal 사용');
            window.profileModal.show(imageSrc, userName, true);
        } else {
            // ProfileImageModal이 로드되지 않은 경우 fallback
            console.log('⚠️ ProfileImageModal 없음, fallback 모달 생성');
            createFallbackProfileModal(imageSrc, userName);
        }
    }

    // 완전 CSS 클래스 기반 Fallback 프로필 모달 생성 (인라인 스타일 완전 제거)
    function createFallbackProfileModal(imageSrc, userName) {
        // 기존 모달이 있으면 제거
        const existingModal = document.getElementById('fallbackProfileModal');
        if (existingModal) {
            existingModal.remove();
        }

        // profile-modal.css의 정확한 클래스 구조 사용 (모든 인라인 스타일 제거)
        const modalHTML = `
        <div id="fallbackProfileModal" class="profile-image-modal show" onclick="closeFallbackProfileModal()">
            <div class="modal-content" onclick="event.stopPropagation()">
                <div class="modal-header">
                    <h3>${userName}의 프로필</h3>
                    <button class="modal-close" onclick="closeFallbackProfileModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <img src="${imageSrc}" alt="${userName}님의 프로필 이미지" 
                         onerror="this.src='/assets/uploads/default-avatar.png'"
                         onload="console.log('✅ 완전 CSS 클래스 기반 이미지 로드 완료')">
                </div>
            </div>
        </div>
        
        <style>
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideIn {
            from { 
                opacity: 0; 
                transform: translateY(-30px) scale(0.9); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0) scale(1); 
            }
        }
        </style>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // ESC 키 이벤트 추가
        document.addEventListener('keydown', fallbackModalEscHandler);
        
        // 스크롤 방지
        document.body.style.overflow = 'hidden';
        
        console.log('✅ 정상 스타일 Fallback 프로필 모달 생성 완료');
    }

    // Fallback 모달 닫기
    function closeFallbackProfileModal() {
        const modal = document.getElementById('fallbackProfileModal');
        if (modal) {
            modal.style.animation = 'fadeOut 0.3s ease';
            setTimeout(() => {
                modal.remove();
                document.body.style.overflow = '';
                document.removeEventListener('keydown', fallbackModalEscHandler);
            }, 300);
        }
    }

    // Fallback 모달 ESC 키 핸들러
    function fallbackModalEscHandler(event) {
        if (event.key === 'Escape') {
            closeFallbackProfileModal();
        }
    }

    // 사용자 프로필 페이지로 이동
    function viewUserProfile(userId) {
        console.log('👤 사용자 프로필 페이지로 이동:', userId);
        
        // 프로필 페이지 URL 생성
        const profileUrl = `/profile/${userId}`;
        
        // 새 탭에서 프로필 페이지 열기
        window.open(profileUrl, '_blank');
    }

    // CSS 추가
    const fallbackStyle = document.createElement('style');
    fallbackStyle.textContent = `
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
    `;
    document.head.appendChild(fallbackStyle);
    </script>

    <!-- 프로필 이미지 모달 JavaScript -->
    <script src="/assets/js/profile-modal.js"></script>
</body>
</html>