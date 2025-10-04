<?php
/**
 * 관리자 사용자 목록 페이지 (수정된 버전)
 */

// 페이지 정보 설정
$page_title = '회원 목록';
$page_description = '등록된 회원들을 관리하고 모니터링하세요';
$current_page = 'users';

// 콘텐츠 정의
ob_start();
?>

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

<?php
$content = ob_get_clean();

// 추가 CSS 정의
$additional_styles = '
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
}

.activity-log {
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 15px;
    background: #fafafa;
}

.activity-item {
    padding: 8px 0;
    border-bottom: 1px solid #e2e8f0;
    font-size: 0.9em;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-time {
    color: #718096;
    font-size: 0.8em;
}
</style>';

// 추가 JavaScript 정의
$additional_scripts = '
<script>
// 전역 변수
let currentPage = 1;
let currentFilters = {};
let isLoading = false;

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
    document.getElementById("searchInput").addEventListener("input", function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            loadUsersData();
        }, 500);
    });
    
    // 필터 변경 시 자동 검색
    document.getElementById("statusFilter").addEventListener("change", loadUsersData);
    document.getElementById("roleFilter").addEventListener("change", loadUsersData);
    
    // 전체 선택 체크박스
    document.getElementById("selectAll").addEventListener("change", function() {
        const checkboxes = document.querySelectorAll(".user-checkbox");
        checkboxes.forEach(cb => cb.checked = this.checked);
    });
}

// 사용자 통계 로드
async function loadUserStats() {
    try {
        console.log("📊 통계 로딩 시작...");
        const response = await fetch("/admin/getUserStats", {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            credentials: "same-origin"
        });
        
        console.log("📊 API 응답 상태:", response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        console.log("📊 받은 데이터:", data);
        
        if (data.success && data.stats) {
            document.getElementById("totalUsers").textContent = data.stats.total_users || 0;
            document.getElementById("todaySignups").textContent = data.stats.today_signups || 0;
            document.getElementById("activeUsers").textContent = data.stats.active_users || 0;
            console.log("✅ 통계 로딩 성공");
        } else {
            throw new Error(data.error || "통계 데이터가 없습니다");
        }
    } catch (error) {
        console.error("❌ 통계 로드 오류:", error);
        document.getElementById("totalUsers").textContent = "오류";
        document.getElementById("todaySignups").textContent = "오류";
        document.getElementById("activeUsers").textContent = "오류";
    }
}

// 사용자 데이터 로드
async function loadUsersData() {
    if (isLoading) return;
    
    isLoading = true;
    document.getElementById("loadingIndicator").style.display = "block";
    document.getElementById("usersTable").style.display = "none";
    document.getElementById("noDataMessage").style.display = "none";
    
    // 현재 필터 수집
    currentFilters = {
        status: document.getElementById("statusFilter").value,
        role: document.getElementById("roleFilter").value,
        search: document.getElementById("searchInput").value.trim(),
        page: currentPage
    };
    
    try {
        const params = new URLSearchParams(currentFilters);
        const response = await fetch(`/admin/users/data?${params}`);
        const data = await response.json();
        
        console.log("📊 사용자 데이터 응답:", data);
        
        if (data.success) {
            renderUsersTable(data.data.users);
            renderPagination(data.data);
        } else {
            console.error("데이터 로드 실패:", data.message);
            document.getElementById("noDataMessage").style.display = "block";
        }
    } catch (error) {
        console.error("사용자 데이터 로드 오류:", error);
        document.getElementById("noDataMessage").style.display = "block";
    } finally {
        isLoading = false;
        document.getElementById("loadingIndicator").style.display = "none";
    }
}

// 사용자 테이블 렌더링
function renderUsersTable(users) {
    const tbody = document.getElementById("usersTableBody");
    
    if (!users || users.length === 0) {
        document.getElementById("noDataMessage").style.display = "block";
        return;
    }
    
    tbody.innerHTML = users.map(user => `
        <tr>
            <td><input type="checkbox" class="user-checkbox" value="${user.id}"></td>
            <td>${user.id}</td>
            <td>${escapeHtml(user.nickname)}</td>
            <td>${escapeHtml(user.email)}</td>
            <td>${escapeHtml(user.phone || "")}</td>
            <td><span class="role-badge role-${user.role.toLowerCase().replace("role_", "")}">${getRoleText(user.role)}</span></td>
            <td><span class="status-badge status-${user.status}">${getStatusText(user.status)}</span></td>
            <td>${formatDate(user.created_at)}</td>
            <td>
                <div class="action-buttons">
                    <button class="action-btn btn-view" onclick="viewUserDetail(${user.id})" title="상세보기">👁️</button>
                    <button class="action-btn btn-edit" onclick="editUser(${user.id})" title="편집">✏️</button>
                </div>
            </td>
        </tr>
    `).join("");
    
    document.getElementById("usersTable").style.display = "table";
}

// 페이지네이션 렌더링
function renderPagination(data) {
    const info = document.getElementById("paginationInfo");
    const controls = document.getElementById("paginationControls");
    
    if (data.total === 0) {
        info.textContent = "";
        controls.innerHTML = "";
        return;
    }
    
    const start = (data.page - 1) * data.limit + 1;
    const end = Math.min(data.page * data.limit, data.total);
    
    info.textContent = `${start}-${end} / 총 ${data.total}개`;
    
    // 페이지 컨트롤 생성
    let paginationHTML = "";
    
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
        document.getElementById("userDetailModal").style.display = "flex";
        
        // 로딩 상태 표시
        document.getElementById("userDetailContent").innerHTML = `
            <div class="loading-indicator">
                <div class="spinner"></div>
                <p>사용자 정보를 불러오는 중...</p>
            </div>
        `;
        
        console.log(`👤 사용자 ID ${userId} 상세 정보 로딩 시작...`);
        
        // API 호출
        const response = await fetch(`/admin/users/${userId}/detail`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            credentials: "same-origin"
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        console.log("👤 받은 사용자 데이터:", data);
        
        if (data.error) {
            throw new Error(data.error);
        }
        
        // 사용자 상세 정보 렌더링 (data.data 사용)
        renderUserDetail(data.data || data);
        
    } catch (error) {
        console.error("❌ 사용자 상세 정보 로드 오류:", error);
        document.getElementById("userDetailContent").innerHTML = `
            <div class="no-data-message">
                <p>❌ 사용자 정보를 불러올 수 없습니다</p>
                <p><strong>오류:</strong> ${error.message}</p>
            </div>
        `;
    }
}

// 사용자 상세 정보 렌더링
function renderUserDetail(user) {
    const profileImage = user.profile_image_thumb || user.profile_image || "/assets/uploads/default-avatar.png";
    
    const html = '<div class="profile-image-container">' +
        '<img src="' + profileImage + '" alt="프로필 이미지" class="profile-image-large" ' +
             'onerror="this.src=\\'/assets/uploads/default-avatar.png\\'">' +
        '<h3>' + escapeHtml(user.nickname) + '</h3>'
            <div class="user-status-badges">
                <span class="role-badge role-${user.role.toLowerCase().replace("role_", "")}">${getRoleText(user.role)}</span>
                <span class="status-badge status-${user.status}">${getStatusText(user.status)}</span>
                ${user.phone_verified === "1" ? '<span class="status-badge status-active">전화 인증됨</span>' : '<span class="status-badge status-inactive">전화 미인증</span>'}
            </div>
        </div>
        
        <div class="user-detail-section">
            <h3>📋 기본 정보</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">사용자 ID</div>
                    <div class="detail-value">${user.id}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">닉네임</div>
                    <div class="detail-value">${escapeHtml(user.nickname)}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">이메일</div>
                    <div class="detail-value">${escapeHtml(user.email)}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">전화번호</div>
                    <div class="detail-value">${escapeHtml(user.phone || "등록되지 않음")}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">실명</div>
                    <div class="detail-value">${escapeHtml(user.real_name || "등록되지 않음")}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">생년월일</div>
                    <div class="detail-value">${escapeHtml(user.birth_date || "등록되지 않음")}</div>
                </div>
            </div>
        </div>
        
        <div class="user-detail-section">
            <h3>🏢 기업 정보</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">기업 상태</div>
                    <div class="detail-value">${getCorpStatusText(user.corp_status)}</div>
                </div>
                ${user.company_name ? `
                <div class="detail-item">
                    <div class="detail-label">회사명</div>
                    <div class="detail-value">${escapeHtml(user.company_name)}</div>
                </div>` : ""}
                ${user.business_number ? `
                <div class="detail-item">
                    <div class="detail-label">사업자번호</div>
                    <div class="detail-value">${escapeHtml(user.business_number)}</div>
                </div>` : ""}
                ${user.position ? `
                <div class="detail-item">
                    <div class="detail-label">직책</div>
                    <div class="detail-value">${escapeHtml(user.position)}</div>
                </div>` : ""}
            </div>
        </div>
        
        <div class="user-detail-section">
            <h3>📊 활동 통계</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">게시글 수</div>
                    <div class="detail-value">${user.post_count || 0}개</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">댓글 수</div>
                    <div class="detail-value">${user.comment_count || 0}개</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">로그인 시도</div>
                    <div class="detail-value">${user.login_attempts || 0}회</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">최근 로그인</div>
                    <div class="detail-value">${user.last_login ? formatDateTime(user.last_login) : "없음"}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">가입일</div>
                    <div class="detail-value">${formatDateTime(user.created_at)}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">계정 수정일</div>
                    <div class="detail-value">${user.updated_at ? formatDateTime(user.updated_at) : "없음"}</div>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById("userDetailContent").innerHTML = html;
}

// 모달 닫기
function closeUserDetailModal() {
    document.getElementById("userDetailModal").style.display = "none";
}

// ESC 키로 모달 닫기
document.addEventListener("keydown", function(e) {
    if (e.key === "Escape") {
        closeUserDetailModal();
    }
});

// 모달 배경 클릭으로 닫기
document.addEventListener("click", function(e) {
    if (e.target.id === "userDetailModal") {
        closeUserDetailModal();
    }
});

// 사용자 편집
function editUser(userId) {
    Toast.info(`사용자 ID ${userId} 편집 (준비 중)`);
}

// 데이터 내보내기
function exportUsers() {
    const params = new URLSearchParams(currentFilters);
    window.open(`/admin/users/export?${params}`, "_blank");
}

// 유틸리티 함수들
function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}

function getRoleText(role) {
    const roleMap = {
        "ROLE_USER": "일반회원",
        "ROLE_CORPORATE": "기업회원", 
        "ROLE_ADMIN": "관리자",
        "ROLE_SUPER_ADMIN": "슈퍼관리자"
    };
    return roleMap[role] || role;
}

function getStatusText(status) {
    const statusMap = {
        "active": "활성",
        "inactive": "비활성",
        "suspended": "정지",
        "pending": "대기"
    };
    return statusMap[status] || status;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString("ko-KR");
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString("ko-KR");
}

function getCorpStatusText(status) {
    const statusMap = {
        "none": "없음",
        "pending": "신청 대기",
        "approved": "승인됨",
        "rejected": "거절됨"
    };
    return statusMap[status] || status;
}
</script>';

// 관리자 레이아웃 렌더링
require_once SRC_PATH . '/views/templates/admin_layout.php';
?>