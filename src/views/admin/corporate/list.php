<?php
/**
 * 관리자 > 기업회원 목록 페이지 - 새 템플릿 구조 적용
 */

// 페이지 정보 설정
$page_title = '기업회원 목록';
$page_description = '승인된 기업회원 목록 및 관리';
$current_page = 'corporate-list';

// 페이지별 추가 스타일
$additional_styles = '
<style>
/* 통계 요약 */
.summary-cards {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 30px;
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
    position: sticky;
    top: 0;
    z-index: 10;
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
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-align: center;
    min-width: 60px;
}

.status-approved {
    background: #c6f6d5;
    color: #22543d;
}

.status-suspended {
    background: #fed7d7;
    color: #c53030;
}

.status-rejected {
    background: #e2e8f0;
    color: #4a5568;
}

.member-stats {
    display: flex;
    gap: 12px;
    font-size: 12px;
    color: #718096;
}

.stat-item {
    background: #f7fafc;
    padding: 4px 8px;
    border-radius: 4px;
}

.member-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.btn-manage {
    padding: 6px 12px;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-manage:hover {
    background: #5a67d8;
}

.btn-suspend {
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

.btn-suspend:hover {
    background: #dd6b20;
}

.btn-activate {
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

.btn-activate:hover {
    background: #38a169;
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
    max-width: 700px;
    position: relative;
    max-height: calc(100vh - 40px);
    overflow-y: auto;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
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

.form-input {
    width: 100%;
    padding: 12px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.3s ease;
}

.form-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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

.form-select {
    width: 100%;
    padding: 12px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    background: white;
    transition: border-color 0.3s ease;
}

.form-select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
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

.btn-warning {
    padding: 10px 20px;
    background: #ed8936;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-warning:hover {
    background: #dd6b20;
}

/* 테이블 반응형 */
.table-wrapper {
    overflow-x: auto;
    margin: -15px -30px 0;
    padding: 15px 30px 0;
}

@media (max-width: 1600px) {
    .members-table {
        min-width: 1200px;
    }
}
</style>
';

// 콘텐츠 정의
$content = '
    <!-- 통계 요약 -->
    <div class="summary-cards">
        <div class="summary-card">
            <div class="summary-card-icon">✅</div>
            <div class="summary-card-number">' . number_format(count(array_filter($members, function($m) { return $m['status'] === 'approved'; }))) . '</div>
            <div class="summary-card-label">승인된 기업</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-icon">⏸️</div>
            <div class="summary-card-number">' . number_format(count(array_filter($members, function($m) { return $m['status'] === 'suspended'; }))) . '</div>
            <div class="summary-card-label">일시정지</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-icon">📝</div>
            <div class="summary-card-number">' . number_format(array_sum(array_column($members, 'post_count'))) . '</div>
            <div class="summary-card-label">총 게시글</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-icon">🎓</div>
            <div class="summary-card-number">' . number_format(array_sum(array_column($members, 'lecture_count'))) . '</div>
            <div class="summary-card-label">총 강의</div>
        </div>
    </div>
    
    <!-- 검색 및 필터 섹션 -->
    <div class="filter-section">
        <div class="filter-row">
            <div class="filter-group">
                <label class="filter-label">검색:</label>
                <input type="text" class="filter-input" id="searchInput" placeholder="회사명, 사업자번호, 회원명으로 검색..." onkeyup="filterMembers()">
            </div>
            <div class="filter-group">
                <label class="filter-label">상태:</label>
                <select class="filter-select" id="statusFilter" onchange="filterMembers()">
                    <option value="all">전체</option>
                    <option value="approved">승인됨</option>
                    <option value="suspended">일시정지</option>
                    <option value="rejected">거절됨</option>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label">가입일:</label>
                <select class="filter-select" id="joinDateFilter" onchange="filterMembers()">
                    <option value="all">전체</option>
                    <option value="today">오늘</option>
                    <option value="week">1주일 이내</option>
                    <option value="month">1개월 이내</option>
                    <option value="quarter">3개월 이내</option>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label">기업 유형:</label>
                <select class="filter-select" id="companyTypeFilter" onchange="filterMembers()">
                    <option value="all">전체</option>
                    <option value="domestic">국내 기업</option>
                    <option value="overseas">해외 기업</option>
                </select>
            </div>
        </div>
    </div>

    <!-- 기업회원 목록 -->
    <div class="members-section">
        <div class="section-header">
            <h3 class="section-title">🏢 기업회원 목록</h3>
            <div id="filteredCount" style="color: #718096; font-size: 14px;">
                총 ' . number_format(count($members)) . '개 기업
            </div>
        </div>
        
        ' . (empty($members) ? '
            <div class="empty-message">
                <div class="empty-icon">🏢</div>
                <div class="empty-title">등록된 기업회원이 없습니다</div>
                <div class="empty-description">기업인증이 완료되면 여기에 표시됩니다.</div>
            </div>
        ' : '
            <div class="table-wrapper">
                <table class="members-table">
                    <thead>
                        <tr>
                            <th>기업 정보</th>
                            <th>회원 정보</th>
                            <th>상태</th>
                            <th>승인일</th>
                            <th>활동</th>
                            <th>관리</th>
                        </tr>
                    </thead>
                    <tbody id="membersTableBody">
                        ' . implode('', array_map(function($member) {
                            $approvedDate = $member['corp_approved_at'] ? date('Y-m-d H:i', strtotime($member['corp_approved_at'])) : '-';
                            $joinDays = floor((time() - strtotime($member['created_at'])) / 86400);
                            
                            return '
                                <tr data-company-name="' . htmlspecialchars($member['company_name']) . '" 
                                    data-business-number="' . htmlspecialchars($member['business_number']) . '"
                                    data-nickname="' . htmlspecialchars($member['nickname']) . '"
                                    data-status="' . $member['status'] . '"
                                    data-is-overseas="' . ($member['is_overseas'] ? '1' : '0') . '"
                                    data-join-days="' . $joinDays . '">
                                    <td>
                                        <div class="company-info">
                                            <div class="company-name">' . htmlspecialchars($member['company_name']) . '</div>
                                            <div class="company-details">
                                                사업자번호: ' . htmlspecialchars($member['business_number']) . '<br>
                                                대표자: ' . htmlspecialchars($member['representative_name']) . 
                                                ($member['is_overseas'] ? ' <span style="color: #667eea; font-weight: 500;"> (해외기업)</span>' : '') . '
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-nickname">' . htmlspecialchars($member['nickname']) . '</div>
                                            <div class="user-contact">
                                                ' . htmlspecialchars($member['phone']) . '<br>
                                                ' . htmlspecialchars($member['email']) . '
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="status-badge status-' . $member['status'] . '">
                                            ' . ($member['status'] === 'approved' ? '승인됨' : 
                                                ($member['status'] === 'suspended' ? '일시정지' : '거절됨')) . '
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-size: 12px; color: #718096;">
                                            ' . $approvedDate . '
                                        </div>
                                    </td>
                                    <td>
                                        <div class="member-stats">
                                            <div class="stat-item">게시글 ' . number_format($member['post_count']) . '</div>
                                            <div class="stat-item">강의 ' . number_format($member['lecture_count']) . '</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="member-actions">
                                            <button class="btn-manage" onclick="manageMember(' . $member['id'] . ')">관리</button>
                                            ' . ($member['status'] === 'approved' ? 
                                                '<button class="btn-suspend" onclick="changeStatus(' . $member['id'] . ', \'suspended\')">정지</button>' :
                                                '<button class="btn-activate" onclick="changeStatus(' . $member['id'] . ', \'approved\')">활성화</button>'
                                            ) . '
                                        </div>
                                    </td>
                                </tr>';
                        }, $members)) . '
                    </tbody>
                </table>
            </div>
        ') . '
    </div>

<!-- 관리 모달 -->
<div id="manageModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">기업회원 관리</h3>
            <button class="modal-close" onclick="closeManageModal()">&times;</button>
        </div>
        <div id="manageContent">
            <!-- 관리 내용이 여기에 로드됩니다 -->
        </div>
    </div>
</div>

<!-- 상태 변경 모달 -->
<div id="statusModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="statusModalTitle">상태 변경</h3>
            <button class="modal-close" onclick="closeStatusModal()">&times;</button>
        </div>
        <form id="statusForm">
            <input type="hidden" id="memberId" name="member_id">
            <input type="hidden" id="newStatus" name="new_status">
            <input type="hidden" name="action" value="status_change">
            <input type="hidden" name="csrf_token" value="' . ($_SESSION['csrf_token'] ?? '') . '">
            
            <div class="form-group">
                <label class="form-label" for="statusReason">변경 사유</label>
                <textarea 
                    class="form-textarea" 
                    id="statusReason" 
                    name="reason" 
                    placeholder="상태 변경 사유를 입력하세요..."
                    rows="4"
                    required
                ></textarea>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeStatusModal()">취소</button>
                <button type="submit" id="statusSubmitBtn" class="btn-primary">변경</button>
            </div>
        </form>
    </div>
</div>

<script>
// 전역 변수
let originalMembersData = [];

// 페이지 로드 시 초기화
document.addEventListener("DOMContentLoaded", function() {
    // 원본 데이터 저장 (필터링용)
    const rows = document.querySelectorAll("#membersTableBody tr");
    rows.forEach(row => {
        originalMembersData.push({
            element: row,
            companyName: row.dataset.companyName,
            businessNumber: row.dataset.businessNumber,
            nickname: row.dataset.nickname,
            status: row.dataset.status,
            isOverseas: row.dataset.isOverseas === "1",
            joinDays: parseInt(row.dataset.joinDays)
        });
    });
});

// 검색 및 필터링 함수
function filterMembers() {
    const searchTerm = document.getElementById("searchInput").value.toLowerCase();
    const statusFilter = document.getElementById("statusFilter").value;
    const joinDateFilter = document.getElementById("joinDateFilter").value;
    const companyTypeFilter = document.getElementById("companyTypeFilter").value;
    
    let visibleCount = 0;
    
    originalMembersData.forEach(data => {
        let show = true;
        
        // 검색어 필터링
        if (searchTerm) {
            const searchableText = (
                data.companyName + " " + 
                data.businessNumber + " " + 
                data.nickname
            ).toLowerCase();
            
            if (!searchableText.includes(searchTerm)) {
                show = false;
            }
        }
        
        // 상태 필터링
        if (statusFilter !== "all" && data.status !== statusFilter) {
            show = false;
        }
        
        // 가입일 필터링
        if (joinDateFilter !== "all") {
            const joinDays = data.joinDays;
            
            switch (joinDateFilter) {
                case "today":
                    if (joinDays > 0) show = false;
                    break;
                case "week":
                    if (joinDays > 7) show = false;
                    break;
                case "month":
                    if (joinDays > 30) show = false;
                    break;
                case "quarter":
                    if (joinDays > 90) show = false;
                    break;
            }
        }
        
        // 기업 유형 필터링
        if (companyTypeFilter !== "all") {
            if (companyTypeFilter === "overseas" && !data.isOverseas) {
                show = false;
            } else if (companyTypeFilter === "domestic" && data.isOverseas) {
                show = false;
            }
        }
        
        // 결과 적용
        data.element.style.display = show ? "" : "none";
        if (show) visibleCount++;
    });
    
    // 필터링 결과 카운트 업데이트
    document.getElementById("filteredCount").textContent = 
        `총 ${originalMembersData.length.toLocaleString()}개 중 ${visibleCount.toLocaleString()}개 표시`;
}

// 회원 관리 모달
async function manageMember(memberId) {
    const modal = document.getElementById("manageModal");
    const content = document.getElementById("manageContent");
    
    // 로딩 표시
    content.innerHTML = \'<div style="text-align: center; padding: 40px;"><div style="font-size: 18px;">로딩 중...</div></div>\';
    modal.style.display = "block";
    
    try {
        // 회원 정보 로드 (실제로는 AJAX로 구현)
        content.innerHTML = `
            <div style="padding: 20px;">
                <h4 style="margin-bottom: 20px;">기업회원 상세 관리</h4>
                <p style="color: #718096;">상세 관리 기능은 추후 구현 예정입니다.</p>
                <div style="margin-top: 20px; text-align: right;">
                    <button onclick="closeManageModal()" style="padding: 10px 20px; background: #e2e8f0; color: #4a5568; border: none; border-radius: 6px; cursor: pointer;">닫기</button>
                </div>
            </div>
        `;
    } catch (error) {
        content.innerHTML = \'<div style="text-align: center; padding: 40px; color: #e53e3e;">정보를 불러오는 중 오류가 발생했습니다.</div>\';
    }
}

function closeManageModal() {
    const modal = document.getElementById("manageModal");
    modal.style.display = "none";
}

// 상태 변경 모달
function changeStatus(memberId, newStatus) {
    document.getElementById("memberId").value = memberId;
    document.getElementById("newStatus").value = newStatus;
    
    const modal = document.getElementById("statusModal");
    const modalTitle = document.getElementById("statusModalTitle");
    const submitBtn = document.getElementById("statusSubmitBtn");
    const statusReason = document.getElementById("statusReason");
    
    if (newStatus === "approved") {
        modalTitle.textContent = "기업회원 활성화";
        submitBtn.textContent = "활성화";
        submitBtn.className = "btn-success";
        statusReason.placeholder = "활성화 사유를 입력하세요...";
    } else {
        modalTitle.textContent = "기업회원 일시정지";
        submitBtn.textContent = "일시정지";
        submitBtn.className = "btn-warning";
        statusReason.placeholder = "일시정지 사유를 입력하세요...";
    }
    
    modal.style.display = "block";
    statusReason.focus();
}

function closeStatusModal() {
    const modal = document.getElementById("statusModal");
    modal.style.display = "none";
    document.getElementById("statusForm").reset();
}

// 상태 변경 폼 제출 처리
document.getElementById("statusForm").addEventListener("submit", async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = document.getElementById("statusSubmitBtn");
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = "처리 중...";
    
    try {
        const response = await fetch("/admin/corporate/manage", {
            method: "POST",
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert("오류: " + result.error);
        }
    } catch (error) {
        alert("처리 중 오류가 발생했습니다: " + error.message);
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
        closeStatusModal();
    }
});

// 모달 외부 클릭 시 닫기
window.onclick = function(event) {
    const manageModal = document.getElementById("manageModal");
    const statusModal = document.getElementById("statusModal");
    
    if (event.target === manageModal) {
        closeManageModal();
    }
    if (event.target === statusModal) {
        closeStatusModal();
    }
}
</script>
';

// 레이아웃 렌더링
include SRC_PATH . '/views/templates/admin_layout.php';
?>