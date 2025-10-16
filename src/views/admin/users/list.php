<?php
/**
 * 관리자 - 회원 목록 페이지
 * Ultra Think 6단계: 프론트엔드 뷰 및 JavaScript 구현
 */

// Modal, Pagination 컴포넌트 로드
require_once SRC_PATH . '/components/ui/Modal.php';
require_once SRC_PATH . '/components/ui/Pagination.php';

// 페이지 정보 설정
$page_title = '회원 목록';
$page_description = '등록된 회원들을 관리하고 모니터링하세요';
$current_page = 'users';

// 페이지별 추가 스타일
$additional_styles = '
<style>
/* 회원 목록 페이지 스타일 */
.users-content {
    max-width: 100%;
    margin: 0;
}

/* 헤더 섹션 */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e2e8f0;
}

.header-info h2 {
    font-size: 24px;
    font-weight: 700;
    color: #1a202c;
    margin: 0 0 8px 0;
}

.header-info p {
    color: #718096;
    margin: 0;
    font-size: 14px;
}

.header-actions {
    display: flex;
    gap: 12px;
    align-items: center;
}

.btn {
    padding: 10px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-secondary {
    background: #f8f9fa;
    color: #6c757d;
    border: 1px solid #dee2e6;
}

.btn-secondary:hover {
    background: #e9ecef;
}

/* 통계 섹션 */
.stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.stat-box {
    background: white;
    border-radius: 12px;
    padding: 20px;
    border: 1px solid #e2e8f0;
    text-align: center;
}

.stat-number {
    font-size: 28px;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 4px;
}

.stat-label {
    color: #718096;
    font-size: 14px;
}

/* 필터 섹션 */
/* 🚀 v3.37.0: 필터 CSS는 이제 /assets/css/search-filter.css에서 통합 관리 */
/* .filters-section, .filters-header 등은 SearchFilter 컴포넌트에서 자동 제공 */

/* 테이블 섹션 */
.table-section {
    background: white;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid #e2e8f0;
    background: #f8f9fa;
}

.table-title {
    font-size: 16px;
    font-weight: 600;
    color: #1a202c;
}

.bulk-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}

.bulk-select {
    display: none;
}

.bulk-select.show {
    display: flex;
    gap: 8px;
}

.users-table {
    width: 100%;
    border-collapse: collapse;
}

.users-table th {
    background: #f8f9fa;
    padding: 12px 16px;
    text-align: left;
    font-weight: 600;
    font-size: 13px;
    color: #4a5568;
    border-bottom: 1px solid #e2e8f0;
}

.users-table td {
    padding: 16px;
    border-bottom: 1px solid #f1f5f9;
}

.users-table tr:hover {
    background: #f8f9fa;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 12px;
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
    flex: 1;
}

.user-name {
    font-weight: 600;
    color: #1a202c;
    font-size: 14px;
    margin-bottom: 2px;
}

.user-email {
    color: #718096;
    font-size: 12px;
}

.user-phone {
    color: #718096;
    font-size: 12px;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

/* 🚀 v3.28.0: 상태 배지 스타일은 /assets/css/badges.css에서 통합 관리 */

.role-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

.role-admin { background: #e6fffa; color: #234e52; }
.role-moderator { background: #fef5e7; color: #b7791f; }
.role-user { background: #edf2f7; color: #4a5568; }
.role-corp { background: #e6f3ff; color: #2c5aa0; }

.action-buttons {
    display: flex;
    gap: 4px;
}

.action-btn {
    padding: 6px 8px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    transition: all 0.2s;
}

.action-btn:hover {
    transform: translateY(-1px);
}

.btn-view { background: #e6f3ff; color: #2c5aa0; }
.btn-edit { background: #fef5e7; color: #b7791f; }
.btn-delete { background: #fed7d7; color: #c53030; }

/* 페이지네이션 */
.pagination-section {
    padding: 20px 24px;
    background: #f8f9fa;
    display: flex;
    justify-content: between;
    align-items: center;
}

.pagination-info {
    color: #718096;
    font-size: 14px;
}

.pagination {
    display: flex;
    gap: 4px;
    margin-left: auto;
}

.page-btn {
    padding: 8px 12px;
    border: 1px solid #e2e8f0;
    background: white;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s;
}

.page-btn:hover {
    background: #f8f9fa;
}

.page-btn.active {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

.page-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* 모달 스타일 */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal.show {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 12px;
    padding: 24px;
    max-width: 500px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid #e2e8f0;
}

.modal-title {
    font-size: 18px;
    font-weight: 600;
    color: #1a202c;
}

.modal-close {
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    color: #718096;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-close:hover {
    background: #f8f9fa;
}

.modal-body {
    margin-bottom: 20px;
}

.modal-footer {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    padding-top: 16px;
    border-top: 1px solid #e2e8f0;
}

.form-group {
    margin-bottom: 16px;
}

.form-label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
    color: #4a5568;
    font-size: 14px;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 14px;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* 로딩 상태 */
.loading {
    text-align: center;
    padding: 40px;
    color: #718096;
}

.loading::before {
    content: "⏳";
    font-size: 24px;
    display: block;
    margin-bottom: 12px;
}

/* 빈 상태 */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #718096;
}

.empty-state::before {
    content: "📋";
    font-size: 48px;
    display: block;
    margin-bottom: 16px;
}

/* 반응형 */
@media (max-width: 1200px) {
    /* 🚀 v3.37.0: .filters-grid는 SearchFilter 컴포넌트에서 자동 관리 */
    /* .filters-grid { grid-template-columns: repeat(3, 1fr); } */

    .stats-row {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    /* 🚀 v3.37.0: .filters-grid, .search-row는 SearchFilter 컴포넌트에서 자동 관리 */
    /* .filters-grid { grid-template-columns: 1fr; } */
    /* .search-row { grid-template-columns: 1fr; } */

    .stats-row {
        grid-template-columns: 1fr;
    }
    
    .users-table {
        font-size: 12px;
    }
    
    .user-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
}
</style>
';

// 콘텐츠 정의
$content = '
    <div class="users-content">
        <!-- 페이지 헤더 -->
        <div class="page-header">
            <div class="header-info">
                <h2>회원 목록</h2>
                <p>등록된 회원들을 관리하고 모니터링하세요</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-secondary" onclick="exportUsers()">
                    <i class="fas fa-download"></i> 내보내기
                </button>
                <button class="btn btn-primary" onclick="refreshData()">
                    <i class="fas fa-refresh"></i> 새로고침
                </button>
            </div>
        </div>

        <!-- 통계 섹션 -->
        <div class="stats-row" id="user-stats">
            <div class="stat-box">
                <div class="stat-number" id="total-users">-</div>
                <div class="stat-label">전체 회원</div>
            </div>
            <div class="stat-box">
                <div class="stat-number" id="today-signups">-</div>
                <div class="stat-label">오늘 신규 가입</div>
            </div>
            <div class="stat-box">
                <div class="stat-number" id="active-users">-</div>
                <div class="stat-label">활성 회원</div>
            </div>
            <div class="stat-box">
                <div class="stat-number" id="pending-users">-</div>
                <div class="stat-label">승인 대기</div>
            </div>
        </div>

        <!-- 필터 섹션 (SearchFilter 컴포넌트) -->
';

// SearchFilter 컴포넌트 추가
require_once SRC_PATH . '/components/ui/SearchFilter.php';

$content .= SearchFilter::create([
            'method' => 'JS',
            'layout' => 'grid-4',
            'filters' => [
                [
                    'type' => 'select',
                    'name' => 'status',
                    'id' => 'filter-status',
                    'label' => '상태',
                    'options' => [
                        '' => '전체',
                        'active' => '활성',
                        'inactive' => '비활성',
                        'suspended' => '정지',
                        'pending' => '대기'
                    ]
                ],
                [
                    'type' => 'select',
                    'name' => 'role',
                    'id' => 'filter-role',
                    'label' => '권한',
                    'options' => [
                        '' => '전체',
                        'ROLE_USER' => '일반 회원',
                        'ROLE_CORP' => '기업 회원',
                        'ROLE_MODERATOR' => '운영자',
                        'ROLE_ADMIN' => '관리자'
                    ]
                ],
                [
                    'type' => 'select',
                    'name' => 'corp-status',
                    'id' => 'filter-corp-status',
                    'label' => '기업 상태',
                    'options' => [
                        '' => '전체',
                        'none' => '비기업',
                        'pending' => '인증 대기',
                        'approved' => '승인됨',
                        'rejected' => '거절됨'
                    ]
                ],
                [
                    'type' => 'select',
                    'name' => 'verified',
                    'id' => 'filter-verified',
                    'label' => '휴대폰 인증',
                    'options' => [
                        '' => '전체',
                        'phone_verified' => '인증 완료',
                        'phone_unverified' => '미인증'
                    ]
                ],
                [
                    'type' => 'select',
                    'name' => 'login-activity',
                    'id' => 'filter-login-activity',
                    'label' => '로그인 활동',
                    'options' => [
                        '' => '전체',
                        'recent_7days' => '최근 7일',
                        'recent_30days' => '최근 30일',
                        'inactive_30days' => '30일 이상 비활성'
                    ]
                ],
                [
                    'type' => 'date',
                    'name' => 'date-from',
                    'id' => 'filter-date-from',
                    'label' => '가입일 (시작)'
                ],
                [
                    'type' => 'date',
                    'name' => 'date-to',
                    'id' => 'filter-date-to',
                    'label' => '가입일 (종료)'
                ],
                [
                    'type' => 'select',
                    'name' => 'sort',
                    'id' => 'filter-sort',
                    'label' => '정렬 방식',
                    'options' => [
                        'created_at' => '가입일 (최신순)',
                        'nickname' => '닉네임 (가나다순)',
                        'email' => '이메일 (가나다순)',
                        'last_login' => '마지막 로그인',
                        'status' => '상태별'
                    ],
                    'value' => 'created_at'
                ]
            ],
            'searchInput' => true,
            'searchName' => 'search',
            'searchInputId' => 'search-input',
            'searchPlaceholder' => '닉네임, 이메일, 전화번호로 검색...',
            'searchValue' => '',
            'submitButton' => true,
            'submitText' => '<i class="fas fa-search"></i> 검색',
            'resetButton' => true,
            'resetText' => '<i class="fas fa-undo"></i> 초기화',
            'collapsible' => true,
            'collapsed' => false,
            'title' => '🔍 필터 및 검색',
            'onSubmit' => 'applyFilters()',
            'onReset' => 'resetFilters()',
            'cssClass' => 'admin-users-filter'
        ]);

$content .= <<<'HTML'

        <!-- 테이블 섹션 -->
        <div class="table-section">
            <div class="table-header">
                <h3 class="table-title">회원 목록</h3>
                <div class="bulk-actions">
                    <div class="bulk-select" id="bulk-actions-container">
                        <select id="bulk-action" class="filter-input">
                            <option value="">일괄 작업 선택</option>
                            <option value="activate">활성화</option>
                            <option value="deactivate">비활성화</option>
                            <option value="suspend">정지</option>
                            <option value="delete">삭제</option>
                            <option value="notify">알림 발송</option>
                        </select>
                        <button class="btn btn-primary" onclick="executeBulkAction()">실행</button>
                        <button class="btn btn-secondary" onclick="clearSelection()">선택 해제</button>
                    </div>
                </div>
            </div>
            
            <div id="table-container">
                <div class="loading">데이터를 불러오는 중...</div>
            </div>
            
            <div class="pagination-section" id="pagination-container" style="display: none;">
                <div class="pagination-info" id="pagination-info"></div>
                <div class="pagination" id="pagination"></div>
            </div>
        </div>
    </div>
HTML;

// 모달들 추가
$content .= renderModal(
    'user-detail-modal',
    '회원 상세 정보',
    '<div id="user-detail-content"><!-- 상세 정보가 여기에 로드됩니다 --></div>',
    [
        'footerButtons' => [
            ['text' => '닫기', 'type' => 'secondary', 'onclick' => 'closeModal("user-detail-modal")']
        ]
    ]
);

$content .= renderModal(
    'status-change-modal',
    '회원 상태 변경',
    '
    <div class="form-group">
        <label class="form-label">새로운 상태</label>
        <select class="form-control" id="new-status">
            <option value="active">활성</option>
            <option value="inactive">비활성</option>
            <option value="suspended">정지</option>
            <option value="pending">대기</option>
        </select>
    </div>
    <div class="form-group">
        <label class="form-label">변경 사유</label>
        <textarea class="form-control" id="status-reason" rows="3" placeholder="상태 변경 사유를 입력하세요..."></textarea>
    </div>
    ',
    [
        'footerButtons' => [
            ['text' => '취소', 'type' => 'secondary', 'onclick' => 'closeModal("status-change-modal")'],
            ['text' => '변경', 'type' => 'primary', 'onclick' => 'updateUserStatus()']
        ]
    ]
);

$content .= renderModal(
    'role-change-modal',
    '회원 권한 변경',
    '
    <div class="form-group">
        <label class="form-label">새로운 권한</label>
        <select class="form-control" id="new-role">
            <option value="ROLE_USER">일반 회원</option>
            <option value="ROLE_CORP">기업 회원</option>
            <option value="ROLE_MODERATOR">운영자</option>
            <option value="ROLE_ADMIN">관리자</option>
        </select>
    </div>
    <div class="form-group">
        <label class="form-label">변경 사유</label>
        <textarea class="form-control" id="role-reason" rows="3" placeholder="권한 변경 사유를 입력하세요..."></textarea>
    </div>
    ',
    [
        'footerButtons' => [
            ['text' => '취소', 'type' => 'secondary', 'onclick' => 'closeModal("role-change-modal")'],
            ['text' => '변경', 'type' => 'primary', 'onclick' => 'updateUserRole()']
        ]
    ]
);

$content .= renderModal(
    'notify-modal',
    '회원 알림 발송',
    '
    <div class="form-group">
        <label class="form-label">알림 메시지</label>
        <textarea class="form-control" id="notify-message" rows="4" placeholder="발송할 메시지를 입력하세요..."></textarea>
    </div>
    ',
    [
        'footerButtons' => [
            ['text' => '취소', 'type' => 'secondary', 'onclick' => 'closeModal("notify-modal")'],
            ['text' => '발송', 'type' => 'primary', 'onclick' => 'sendNotification()']
        ]
    ]
);

// 페이지별 추가 스크립트
$additional_scripts = <<<'SCRIPTS'
<script src="/assets/js/modal.js"></script>
<script>
// 전역 변수
let currentUserId = null;
let selectedUsers = new Set();
let currentPage = 1;
let currentFilters = {};
let isLoading = false;

// CSRF 토큰
const csrfToken = "' . ($_SESSION['csrf_token'] ?? '') . '";

// 페이지 로드 시 초기화
document.addEventListener("DOMContentLoaded", function() {
    loadUserStats();
    loadUsersData();
    setupEventListeners();
});

// 이벤트 리스너 설정
function setupEventListeners() {
    // 필터 변경 이벤트
    const filterElements = [
        "filter-status", "filter-role", "filter-corp-status", 
        "filter-verified", "filter-login-activity", "filter-date-from", 
        "filter-date-to", "filter-sort"
    ];
    
    filterElements.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener("change", debounce(applyFilters, 500));
        }
    });
    
    // 검색 입력 이벤트
    const searchInput = document.getElementById("search-input");
    if (searchInput) {
        searchInput.addEventListener("input", debounce(applyFilters, 500));
        searchInput.addEventListener("keypress", function(e) {
            if (e.key === "Enter") {
                applyFilters();
            }
        });
    }
    
    // 모달 외부 클릭 시 닫기
    document.addEventListener("click", function(e) {
        if (e.target.classList.contains("modal")) {
            closeModal(e.target.id);
        }
    });
}

// v3.57.0: 통합 utils.js.php의 debounce 함수 사용
// (전역 window.debounce 사용)

// 사용자 통계 로드
async function loadUserStats() {
    try {
        // 🚀 v3.42.0: ApiClient 사용 (fetch → ApiClient.get)
        const data = await ApiClient.get("/admin/users/stats", { noLoading: true, noErrorToast: true });

        if (data.success) {
            const stats = data.data;
            document.getElementById("total-users").textContent = formatNumber(stats.total_users || 0);
            document.getElementById("today-signups").textContent = formatNumber(stats.today_signups || 0);
            document.getElementById("active-users").textContent = formatNumber(stats.active_users || 0);
            document.getElementById("pending-users").textContent = formatNumber(stats.by_status?.pending || 0);
        }
    } catch (error) {
        Toast.error('통계 데이터를 불러올 수 없습니다.\n페이지를 새로고침해주세요.');
    }
}

// 사용자 데이터 로드
async function loadUsersData(page = 1) {
    if (isLoading) return;
    
    isLoading = true;
    currentPage = page;
    
    // 로딩 표시
    const tableContainer = document.getElementById("table-container");
    tableContainer.innerHTML = `<div class="loading">데이터를 불러오는 중...</div>`;
    
    try {
        // 필터 파라미터 구성
        const params = new URLSearchParams({
            page: page,
            limit: 20,
            ...currentFilters
        });
        
        // 🚀 v3.42.0: ApiClient 사용 (fetch → ApiClient.get)
        const data = await ApiClient.get(`/admin/users/data?${params}`, { noLoading: true });

        if (data.success) {
            renderUsersTable(data.data);
            renderPagination(data.data);
        } else {
            throw new Error(data.message || "데이터 로드 실패");
        }
    } catch (error) {
        Toast.error('사용자 데이터를 불러올 수 없습니다.\n잠시 후 다시 시도해주세요.');
        tableContainer.innerHTML = `
            <div class="empty-state">
                <div>❌ 데이터 로드에 실패했습니다</div>
                <div style="font-size: 14px; margin-top: 8px;">${error.message}</div>
                <button class="btn btn-primary" onclick="loadUsersData(${page})" style="margin-top: 16px;">다시 시도</button>
            </div>
        `;
    } finally {
        isLoading = false;
    }
}

// 사용자 테이블 렌더링
function renderUsersTable(data) {
    const { users, total, page, total_pages } = data;
    const tableContainer = document.getElementById("table-container");
    
    if (!users || users.length === 0) {
        tableContainer.innerHTML = `
            <div class="empty-state">
                <div>검색 결과가 없습니다</div>
                <div style="font-size: 14px; margin-top: 8px;">다른 검색 조건을 시도해보세요</div>
            </div>
        `;
        return;
    }
    
    const tableHTML = `
        <table class="users-table">
            <thead>
                <tr>
                    <th style="width: 40px;">
                        <input type="checkbox" id="select-all" onchange="toggleSelectAll(this)">
                    </th>
                    <th>회원 정보</th>
                    <th>연락처</th>
                    <th>상태</th>
                    <th>권한</th>
                    <th>기업 상태</th>
                    <th>활동 정보</th>
                    <th>가입일</th>
                    <th style="width: 120px;">작업</th>
                </tr>
            </thead>
            <tbody>
                ${users.map(user => `
                    <tr>
                        <td>
                            <input type="checkbox" value="${user.id}" onchange="toggleUserSelection(this, ${user.id})">
                        </td>
                        <td>
                            <div class="user-info">
                                <div class="user-avatar">
                                    ${user.nickname ? user.nickname.charAt(0).toUpperCase() : "U"}
                                </div>
                                <div class="user-details">
                                    <div class="user-name">${escapeHtml(user.nickname || "이름 없음")}</div>
                                    <div class="user-email">${escapeHtml(decryptIfNeeded(user.email) || "이메일 없음")}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="user-phone">${formatPhone(decryptIfNeeded(user.phone))}</div>
                            <div style="font-size: 11px; color: #718096;">
                                ${user.phone_verified ? "📱 인증됨" : "📱 미인증"}
                            </div>
                        </td>
                        <td>
                            <span class="status-badge status-${user.status}">
                                ${getStatusText(user.status)}
                            </span>
                        </td>
                        <td>
                            <span class="role-badge role-${user.role?.toLowerCase()}">
                                ${getRoleText(user.role)}
                            </span>
                        </td>
                        <td>
                            <span class="status-badge status-${user.corp_status || "none"}">
                                ${getCorpStatusText(user.corp_status)}
                            </span>
                        </td>
                        <td>
                            <div style="font-size: 12px;">
                                <div>게시글: ${formatNumber(user.post_count || 0)}개</div>
                                <div>댓글: ${formatNumber(user.comment_count || 0)}개</div>
                                <div style="color: #718096;">
                                    ${user.last_login ? "로그인: " + formatDate(user.last_login, { includeTime: true }) : "미로그인"}
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 12px; color: #718096;">
                                ${formatDate(user.created_at, { includeTime: true })}
                            </div>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="action-btn btn-view" onclick="viewUserDetail(${user.id})" title="상세보기">
                                    👁️
                                </button>
                                <button class="action-btn btn-edit" onclick="openStatusChangeModal(${user.id}, \"${user.status}\")" title="상태변경">
                                    ⚙️
                                </button>
                                <button class="action-btn btn-edit" onclick="openRoleChangeModal(${user.id}, \"${user.role}\")" title="권한변경">
                                    🔑
                                </button>
                                <button class="action-btn btn-view" onclick="openNotifyModal(${user.id})" title="알림발송">
                                    📨
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join("")}
            </tbody>
        </table>
    `;
    
    tableContainer.innerHTML = tableHTML;
    updateBulkActionsVisibility();
}

// 페이지네이션 렌더링
function renderPagination(data) {
    const { total, page, total_pages, limit } = data;
    const paginationContainer = document.getElementById("pagination-container");
    const paginationInfo = document.getElementById("pagination-info");
    const pagination = document.getElementById("pagination");
    
    if (total_pages <= 1) {
        paginationContainer.style.display = "none";
        return;
    }
    
    paginationContainer.style.display = "flex";
    
    // 페이지네이션 정보
    const start = (page - 1) * limit + 1;
    const end = Math.min(page * limit, total);
    paginationInfo.textContent = `${formatNumber(start)}-${formatNumber(end)} / 총 ${formatNumber(total)}명`;
    
    // 페이지네이션 버튼
    let paginationHTML = "";
    
    // 이전 버튼
    paginationHTML += `
        <button class="page-btn" ${page <= 1 ? "disabled" : ""} onclick="loadUsersData(${page - 1})">
            ◀ 이전
        </button>
    `;
    
    // 페이지 번호들
    const maxPages = 5;
    let startPage = Math.max(1, page - Math.floor(maxPages / 2));
    let endPage = Math.min(total_pages, startPage + maxPages - 1);
    
    if (endPage - startPage + 1 < maxPages) {
        startPage = Math.max(1, endPage - maxPages + 1);
    }
    
    for (let i = startPage; i <= endPage; i++) {
        paginationHTML += `
            <button class="page-btn ${i === page ? "active" : ""}" onclick="loadUsersData(${i})">
                ${i}
            </button>
        `;
    }
    
    // 다음 버튼
    paginationHTML += `
        <button class="page-btn" ${page >= total_pages ? "disabled" : ""} onclick="loadUsersData(${page + 1})">
            다음 ▶
        </button>
    `;
    
    pagination.innerHTML = paginationHTML;
}

// 필터 적용
function applyFilters() {
    // 현재 필터 값들 수집
    currentFilters = {
        status: document.getElementById("filter-status").value,
        role: document.getElementById("filter-role").value,
        corp_status: document.getElementById("filter-corp-status").value,
        verified_status: document.getElementById("filter-verified").value,
        login_activity: document.getElementById("filter-login-activity").value,
        date_from: document.getElementById("filter-date-from").value,
        date_to: document.getElementById("filter-date-to").value,
        sort: document.getElementById("filter-sort").value,
        search: document.getElementById("search-input").value.trim()
    };
    
    // 빈 값 제거
    Object.keys(currentFilters).forEach(key => {
        if (!currentFilters[key]) {
            delete currentFilters[key];
        }
    });
    
    // 첫 페이지부터 다시 로드
    loadUsersData(1);
}

// 필터 초기화
function resetFilters() {
    document.getElementById("filter-status").value = "";
    document.getElementById("filter-role").value = "";
    document.getElementById("filter-corp-status").value = "";
    document.getElementById("filter-verified").value = "";
    document.getElementById("filter-login-activity").value = "";
    document.getElementById("filter-date-from").value = "";
    document.getElementById("filter-date-to").value = "";
    document.getElementById("filter-sort").value = "created_at";
    document.getElementById("search-input").value = "";
    
    currentFilters = {};
    loadUsersData(1);
}

// 필터 토글
// 🚀 v3.37.0: toggleFilters()는 SearchFilter 컴포넌트의 SearchFilter.toggle()로 대체됨
// function toggleFilters() {
//     const content = document.getElementById("filters-content");
//     const toggleText = document.getElementById("filter-toggle-text");
//     const toggleIcon = document.getElementById("filter-toggle-icon");
//
//     if (content.style.display === "none") {
//         content.style.display = "block";
//         toggleText.textContent = "간단히 보기";
//         toggleIcon.className = "fas fa-chevron-up";
//     } else {
//         content.style.display = "none";
//         toggleText.textContent = "자세히 보기";
//         toggleIcon.className = "fas fa-chevron-down";
//     }
// }

// 전체 선택 토글
function toggleSelectAll(checkbox) {
    const userCheckboxes = document.querySelectorAll("input[type=checkbox][value]");
    userCheckboxes.forEach(cb => {
        cb.checked = checkbox.checked;
        if (checkbox.checked) {
            selectedUsers.add(parseInt(cb.value));
        } else {
            selectedUsers.delete(parseInt(cb.value));
        }
    });
    updateBulkActionsVisibility();
}

// 개별 사용자 선택 토글
function toggleUserSelection(checkbox, userId) {
    if (checkbox.checked) {
        selectedUsers.add(userId);
    } else {
        selectedUsers.delete(userId);
        document.getElementById("select-all").checked = false;
    }
    updateBulkActionsVisibility();
}

// 선택 해제
function clearSelection() {
    selectedUsers.clear();
    document.querySelectorAll("input[type=checkbox]").forEach(cb => cb.checked = false);
    updateBulkActionsVisibility();
}

// 벌크 액션 표시/숨김
function updateBulkActionsVisibility() {
    const bulkContainer = document.getElementById("bulk-actions-container");
    if (selectedUsers.size > 0) {
        bulkContainer.classList.add("show");
    } else {
        bulkContainer.classList.remove("show");
    }
}

// 벌크 액션 실행
async function executeBulkAction() {
    const action = document.getElementById("bulk-action").value;
    if (!action || selectedUsers.size === 0) {
        Toast.info('작업을 선택하고 대상 회원을 선택하세요.');
        return;
    }

    if (!(await Modal.confirm(`선택한 ${formatNumber(selectedUsers.size)}명의 회원에게 "${action}" 작업을 실행하시겠습니까?`, {
        type: 'warning',
        confirmText: '실행',
        cancelText: '취소'
    }))) {
        return;
    }
    
    try {
        // 🚀 v3.42.0: ApiClient 사용 (fetch → ApiClient.post)
        const data = await ApiClient.post("/admin/users/bulk-action", {
            action: action,
            user_ids: Array.from(selectedUsers)
        }, { noLoading: true });

        if (data.success) {
            Toast.success(`작업이 완료되었습니다. 성공: ${formatNumber(data.success_count)}개, 실패: ${formatNumber(data.fail_count)}개`);
            clearSelection();
            loadUsersData(currentPage);
        } else {
            throw new Error(data.message || "벌크 작업 실패");
        }
    } catch (error) {
        Toast.error("작업 실행에 실패했습니다: " + error.message);
    }
}

// 사용자 상세보기
async function viewUserDetail(userId) {
    try {
        // 🚀 v3.42.0: ApiClient 사용 (fetch → ApiClient.get)
        const data = await ApiClient.get(`/admin/users/${userId}/detail`, { noLoading: true });

        if (data.success) {
            const user = data.data;
            document.getElementById("user-detail-content").innerHTML = `
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div>
                        <h4>기본 정보</h4>
                        <p><strong>ID:</strong> ${user.id}</p>
                        <p><strong>닉네임:</strong> ${escapeHtml(user.nickname || "없음")}</p>
                        <p><strong>이메일:</strong> ${escapeHtml(decryptIfNeeded(user.email) || "없음")}</p>
                        <p><strong>전화번호:</strong> ${formatPhone(decryptIfNeeded(user.phone))}</p>
                        <p><strong>상태:</strong> ${getStatusText(user.status)}</p>
                        <p><strong>권한:</strong> ${getRoleText(user.role)}</p>
                    </div>
                    <div>
                        <h4>활동 정보</h4>
                        <p><strong>게시글:</strong> ${formatNumber(user.post_count || 0)}개</p>
                        <p><strong>댓글:</strong> ${formatNumber(user.comment_count || 0)}개</p>
                        <p><strong>받은 좋아요:</strong> ${formatNumber(user.total_likes || 0)}개</p>
                        <p><strong>가입일:</strong> ${formatDate(user.created_at, { includeTime: true })}</p>
                        <p><strong>마지막 로그인:</strong> ${user.last_login ? formatDate(user.last_login, { includeTime: true }) : "없음"}</p>
                        <p><strong>로그인 시도:</strong> ${formatNumber(user.login_attempts || 0)}회</p>
                    </div>
                </div>
                
                ${user.recent_activity && user.recent_activity.length > 0 ? `
                    <div style="margin-top: 20px;">
                        <h4>최근 활동</h4>
                        <div style="max-height: 200px; overflow-y: auto;">
                            ${user.recent_activity.slice(0, 10).map(activity => `
                                <div style="padding: 8px; border-bottom: 1px solid #f1f5f9; font-size: 13px;">
                                    <strong>${activity.action}</strong> - ${activity.description}
                                    <div style="color: #718096; font-size: 11px;">${formatDate(activity.created_at, { includeTime: true })}</div>
                                </div>
                            `).join("")}
                        </div>
                    </div>
                ` : ""}
            `;
            openModal("user-detail-modal");
        } else {
            throw new Error(data.message || "사용자 정보 로드 실패");
        }
    } catch (error) {
        Toast.error("사용자 정보를 불러올 수 없습니다: " + error.message);
    }
}

// 상태 변경 모달 열기
function openStatusChangeModal(userId, currentStatus) {
    currentUserId = userId;
    document.getElementById("new-status").value = currentStatus;
    document.getElementById("status-reason").value = "";
    openModal("status-change-modal");
}

// 사용자 상태 업데이트
async function updateUserStatus() {
    const newStatus = document.getElementById("new-status").value;
    const reason = document.getElementById("status-reason").value;
    
    if (!newStatus) {
        Toast.info('새로운 상태를 선택하세요.');
        return;
    }
    
    try {
        // 🚀 v3.42.0: ApiClient 사용 (fetch → ApiClient.post)
        const data = await ApiClient.post(`/admin/users/${currentUserId}/status`, {
            status: newStatus,
            reason: reason
        }, { noLoading: true });

        if (data.success) {
            Toast.success('사용자 상태가 성공적으로 변경되었습니다.');
            closeModal("status-change-modal");
            loadUsersData(currentPage);
        } else {
            throw new Error(data.message || "상태 변경 실패");
        }
    } catch (error) {
        Toast.error("상태 변경에 실패했습니다: " + error.message);
    }
}

// 권한 변경 모달 열기
function openRoleChangeModal(userId, currentRole) {
    currentUserId = userId;
    document.getElementById("new-role").value = currentRole;
    document.getElementById("role-reason").value = "";
    openModal("role-change-modal");
}

// 사용자 권한 업데이트
async function updateUserRole() {
    const newRole = document.getElementById("new-role").value;
    const reason = document.getElementById("role-reason").value;
    
    if (!newRole) {
        Toast.info('새로운 권한을 선택하세요.');
        return;
    }
    
    try {
        // 🚀 v3.42.0: ApiClient 사용 (fetch → ApiClient.post)
        const data = await ApiClient.post(`/admin/users/${currentUserId}/role`, {
            role: newRole,
            reason: reason
        }, { noLoading: true });

        if (data.success) {
            Toast.success('사용자 권한이 성공적으로 변경되었습니다.');
            closeModal("role-change-modal");
            loadUsersData(currentPage);
        } else {
            throw new Error(data.message || "권한 변경 실패");
        }
    } catch (error) {
        Toast.error("권한 변경에 실패했습니다: " + error.message);
    }
}

// 알림 발송 모달 열기
function openNotifyModal(userId) {
    currentUserId = userId;
    document.getElementById("notify-message").value = "";
    openModal("notify-modal");
}

// 알림 발송
async function sendNotification() {
    const message = document.getElementById("notify-message").value.trim();
    
    if (!message) {
        Toast.error('발송할 메시지를 입력하세요.');
        return;
    }
    
    try {
        // v3.42.0: ApiClient 사용
        const data = await ApiClient.post(`/admin/users/${currentUserId}/notify`, {
            message: message
        }, { noLoading: true });

        if (data.success) {
            Toast.success('알림이 성공적으로 발송되었습니다.');
            closeModal("notify-modal");
        } else {
            throw new Error(data.message || "알림 발송 실패");
        }
    } catch (error) {
        Toast.error("알림 발송에 실패했습니다: " + error.message);
    }
}

// 데이터 내보내기
async function exportUsers() {
    try {
        const params = new URLSearchParams(currentFilters);
        window.open(`/admin/users/export?${params}`, "_blank");
    } catch (error) {
        Toast.error("데이터 내보내기에 실패했습니다: " + error.message);
    }
}

// 데이터 새로고침
function refreshData() {
    loadUserStats();
    loadUsersData(currentPage);
}

// 모달 닫기 시 추가 정리 작업
const originalCloseModal = closeModal;
window.closeModal = function(modalId) {
    originalCloseModal(modalId);
    if (modalId === "user-detail-modal" || modalId === "status-change-modal" || modalId === "role-change-modal") {
        currentUserId = null;
    }
};

// 유틸리티 함수들
function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}

// 암호화된 데이터인지 확인하고 복호화가 필요한 경우 처리
function decryptIfNeeded(data) {
    if (!data || typeof data !== "string") return data;

    // Base64로 인코딩된 긴 문자열인지 확인 (암호화된 데이터의 특징)
    if (data.length > 50 && isBase64(data)) {
        // 암호화된 데이터로 판단되면 "암호화됨" 표시
        return "[암호화된 데이터]";
    }

    return data;
}

// Base64 문자열인지 확인
function isBase64(str) {
    try {
        return btoa(atob(str)) === str;
    } catch (err) {
        return false;
    }
}

// 🚀 v3.63.0: formatNumber, formatPhone은 utils.js.php 통합 시스템 사용 (admin_layout.php 전역 로드)
// 🚀 v3.62.0: formatDate는 date-utils.js.php 통합 시스템 사용 (footer.php 전역 로드)

function getStatusText(status) {
    const statusMap = {
        active: "활성",
        inactive: "비활성", 
        suspended: "정지",
        pending: "대기",
        deleted: "삭제됨"
    };
    return statusMap[status] || status;
}

function getRoleText(role) {
    const roleMap = {
        "ROLE_USER": "일반 회원",
        "ROLE_CORP": "기업 회원",
        "ROLE_MODERATOR": "운영자",
        "ROLE_ADMIN": "관리자",
        "SUPER_ADMIN": "최고 관리자"
    };
    return roleMap[role] || role;
}

function getCorpStatusText(corpStatus) {
    const corpStatusMap = {
        none: "비기업",
        pending: "인증 대기",
        approved: "승인됨",
        rejected: "거절됨"
    };
    return corpStatusMap[corpStatus] || corpStatus || "비기업";
}
</script>
SCRIPTS;

// 레이아웃 렌더링
include SRC_PATH . '/views/templates/admin_layout.php';
?>