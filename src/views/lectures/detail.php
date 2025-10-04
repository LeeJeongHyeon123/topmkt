<?php
/**
 * 강의 상세 페이지
 */

// 로그인 상태 확인
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
require_once SRC_PATH . '/helpers/HtmlSanitizerHelper.php';
require_once SRC_PATH . '/helpers/ProfileImageHelper.php';
$isLoggedIn = AuthMiddleware::isLoggedIn();

// 컴포넌트 로드
require_once SRC_PATH . '/components/ui/Button.php';
$currentUserId = AuthMiddleware::getCurrentUserId();

// 편집 권한 확인 (강의 작성자이거나 관리자인지 확인)
$canEdit = false;
if ($isLoggedIn && isset($lecture)) {
    $userRole = AuthMiddleware::getUserRole();
    $canEdit = ($userRole === 'ROLE_ADMIN') || ($lecture['user_id'] == $currentUserId);
}

// CSRF 토큰 생성
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 프로필 이미지 모달 리소스 로드
include SRC_PATH . '/views/components/profile-modal-resources.php';
?>

<!-- CSRF 토큰 메타 태그 -->
<meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?>">

<style>
/* 강의 상세 페이지 스타일 */
.lecture-detail-container {
    max-width: 1600px;
    margin: 0 auto;
    padding: 20px;
    min-height: calc(100vh - 200px);
    /* 헤더 겹침 방지: 고정 헤더 높이만큼 상단 여백 추가 */
    margin-top: 30px; /* 데스크톱: 헤더와 적절한 간격 유지 */
    padding-top: 20px;
}

.lecture-header {
    background: transparent !important;
    border-radius: 0;
    overflow: visible;
    box-shadow: none;
    border: none;
    margin-bottom: 20px;
    padding: 0;
    position: relative;
}

.lecture-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 80px 40px 40px 40px;
    position: relative;
    border-radius: 12px;
    margin: 0;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

.lecture-category {
    display: inline-block;
    padding: 6px 12px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 15px;
}

.lecture-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 10px;
    line-height: 1.2;
}

.lecture-subtitle {
    font-size: 1.1rem;
    opacity: 0.9;
    margin-bottom: 20px;
}

.lecture-meta-basic {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 1rem;
    color: rgba(255, 255, 255, 0.95);
    font-weight: 500;
}

.meta-icon {
    font-size: 1.2rem;
}

/* 버튼 위치 수정 v2.0 - 2025-09-19 */
.lecture-banner .lecture-actions {
    position: absolute !important;
    top: 10px !important;
    right: 10px !important;
    display: flex !important;
    gap: 6px !important;
    z-index: 100 !important;
    background: rgba(0, 0, 0, 0.1) !important;
    padding: 4px !important;
    border-radius: 6px !important;
}

.lecture-actions .btn {
    padding: 6px 12px !important;
    font-size: 12px !important;
    min-height: 28px !important;
    max-height: 28px !important;
    border-radius: 4px;
    backdrop-filter: blur(10px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    line-height: 1 !important;
    white-space: nowrap !important;
    font-weight: 500 !important;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s ease;
    min-height: auto;
    line-height: 1.2;
}

.btn-primary {
    background: #48bb78;
    color: white;
}

.btn-primary:hover {
    background: #38a169;
    transform: translateY(-1px);
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.3);
}

.btn-edit {
    background: rgba(255, 255, 255, 0.25);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.4);
}

.btn-edit:hover {
    background: rgba(255, 255, 255, 0.35);
    transform: translateY(-1px);
}

/* 강의 액션 버튼들 전용 스타일 */
.lecture-actions .btn-edit {
    background: rgba(255, 255, 255, 0.9) !important;
    color: #4a5568 !important;
    border: 1px solid rgba(255, 255, 255, 0.9) !important;
}

.lecture-actions .btn-edit:hover {
    background: rgba(255, 255, 255, 1) !important;
    transform: translateY(-1px);
}

.lecture-actions .btn-danger {
    background: rgba(229, 62, 62, 0.9) !important;
    color: white !important;
    border: 1px solid rgba(229, 62, 62, 0.9) !important;
}

.lecture-actions .btn-danger:hover {
    background: rgba(229, 62, 62, 1) !important;
    transform: translateY(-1px);
}

.lecture-actions .btn-secondary {
    background: rgba(160, 174, 192, 0.9) !important;
    color: white !important;
    border: 1px solid rgba(160, 174, 192, 0.9) !important;
}

.lecture-actions .btn-secondary:hover {
    background: rgba(160, 174, 192, 1) !important;
    transform: translateY(-1px);
}

.btn-danger {
    background: #e53e3e;
    color: white;
    border: 1px solid #c53030;
}

.btn-success {
    background: #48bb78;
    color: white;
    border: 1px solid #38a169;
}

.btn-warning {
    background: #ed8936;
    color: white;
    border: 1px solid #dd6b20;
}

.btn-info {
    background: #4299e1;
    color: white;
    border: 1px solid #3182ce;
}

.btn-outline {
    background: transparent;
    border: 2px solid rgba(255, 255, 255, 0.3);
    color: rgba(255, 255, 255, 0.9);
    transition: all 0.2s ease;
}

.btn-outline:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.5);
    color: white;
}

/* 신청 액션 컨테이너 */
#registration-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
}

#registration-actions .btn {
    white-space: nowrap;
    min-width: 140px;
}

/* 신청 모달 스타일 */
.registration-modal {
    display: none;
    position: fixed;
    z-index: 10000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(5px);
}

.registration-modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border-radius: 16px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.registration-modal-header {
    padding: 24px 28px 20px;
    border-bottom: 1px solid #e2e8f0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 16px 16px 0 0;
}

.registration-modal-header h2 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 12px;
}

.registration-modal-close {
    position: absolute;
    top: 20px;
    right: 24px;
    background: none;
    border: none;
    font-size: 28px;
    color: rgba(255, 255, 255, 0.8);
    cursor: pointer;
    padding: 0;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s ease;
}

.registration-modal-close:hover {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.registration-modal-body {
    padding: 28px;
}

.form-section {
    margin-bottom: 24px;
}

.form-section:last-child {
    margin-bottom: 0;
}

.form-section-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 16px;
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #4a5568;
    font-size: 0.9rem;
}

.form-group .required::after {
    content: ' *';
    color: #e53e3e;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.2s ease;
    background: white;
    box-sizing: border-box;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
}

.form-group input.error,
.form-group textarea.error,
.form-group select.error {
    border-color: #e53e3e;
    background-color: #fed7d7;
}

.error-message {
    color: #e53e3e;
    font-size: 0.8rem;
    margin-top: 4px;
    display: block;
}

.error-message.general-error {
    background: #fed7d7;
    border: 1px solid #e53e3e;
    border-radius: 6px;
    padding: 12px 16px;
    margin-bottom: 20px;
}

.registration-modal-footer {
    padding: 20px 28px 28px;
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    border-top: 1px solid #e2e8f0;
}

.registration-modal-footer .btn {
    min-width: 120px;
    padding: 12px 24px;
    font-size: 14px;
    font-weight: 600;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
}

.registration-modal-footer .btn-primary {
    background: #667eea;
    color: white;
}

.registration-modal-footer .btn-primary:hover:not(:disabled) {
    background: #5a67d8;
    transform: translateY(-1px);
}

.registration-modal-footer .btn-secondary {
    background: #e2e8f0;
    color: #4a5568;
}

.registration-modal-footer .btn-secondary:hover {
    background: #cbd5e0;
}

.registration-modal-footer .btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* 글자 수 카운터 스타일 */
.char-counter {
    text-align: right;
    font-size: 0.875rem;
    color: #6b7280;
    margin-top: 4px;
    font-family: 'SF Pro Display', -apple-system, BlinkMacSystemFont, system-ui, sans-serif;
}

.char-counter span {
    font-weight: 600;
}

/* 텍스트에어리어 스타일 개선 */
.form-group textarea {
    resize: vertical;
    min-height: 80px;
    font-family: 'SF Pro Display', -apple-system, BlinkMacSystemFont, system-ui, sans-serif;
}

/* 반응형 디자인 */
@media (max-width: 768px) {
    .registration-modal-content {
        width: 95%;
        margin: 20px auto;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    
    .registration-modal-header {
        padding: 20px 24px 16px;
    }
    
    .registration-modal-body {
        padding: 24px 20px;
    }
    
    .registration-modal-footer {
        padding: 16px 20px 24px;
        flex-direction: column;
    }
    
    .registration-modal-footer .btn {
        width: 100%;
    }
}

.btn-danger:hover {
    background: #c53030;
    border-color: #9c1e1e;
    transform: translateY(-1px);
}

/* 콘텐츠 레이아웃 */
.lecture-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
}

/* 768px 이하에서 단일 컬럼 강제 적용 */
@media (max-width: 768px) {
    .lecture-detail-container .lecture-content {
        display: grid !important;
        grid-template-columns: 1fr !important;
        gap: 15px !important;
    }
    
    .lecture-detail-container .lecture-sidebar {
        order: 10 !important;
    }
    
    .lecture-detail-container .lecture-main {
        order: 1 !important;
    }
}

.lecture-main {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
}

.lecture-sidebar {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.sidebar-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
}

.sidebar-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 15px;
    padding-bottom: 8px;
    border-bottom: 2px solid #667eea;
}

/* 강의 정보 섹션 */
.info-section {
    margin-bottom: 30px;
}

.section-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.description-content {
    color: #4a5568;
    line-height: 1.7;
    font-size: 1rem;
}

/* 강사 정보 개선 */
.instructors-container {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.instructor-card {
    background: #f8fafc;
    padding: 25px;
    border-radius: 12px;
    border-left: 4px solid #667eea;
    display: flex;
    gap: 20px;
    align-items: flex-start;
    transition: all 0.3s ease;
}

.instructor-card:hover {
    background: #edf2f7;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
}

.instructor-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #667eea;
    flex-shrink: 0;
    box-shadow: 0 4px 8px rgba(102, 126, 234, 0.2);
    transition: all 0.3s ease;
    cursor: pointer;
    aspect-ratio: 1 / 1;
    display: block;
}

.instructor-avatar:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 16px rgba(102, 126, 234, 0.3);
}

.instructor-avatar.clickable-image {
    cursor: pointer;
}

.instructor-avatar.clickable-image:hover {
    transform: scale(1.08);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
}

.instructor-avatar.placeholder {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 1.5rem;
    position: relative;
    aspect-ratio: 1 / 1;
    width: 80px;
    height: 80px;
}

/* 강사 이미지 원형 유지 강화 */
.instructor-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    aspect-ratio: 1 / 1;
}

.instructor-avatar.placeholder::after {
    content: '';
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 20px;
    height: 20px;
    background: linear-gradient(45deg, #48bb78, #38a169);
    border-radius: 50%;
    border: 2px solid white;
    display: flex;
    align-items: center;
    justify-content: center;
}

.instructor-avatar.placeholder::before {
    content: '👨‍🏫';
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 20px;
    height: 20px;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* 이미지 로딩 상태 표시 */
.instructor-avatar.loading {
    position: relative;
    opacity: 0.7;
}

.instructor-avatar.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid rgba(102, 126, 234, 0.3);
    border-top: 2px solid #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* 이미지 오류 상태 표시 */
.instructor-avatar.error {
    background: linear-gradient(135deg, #fc8181 0%, #f56565 100%);
    position: relative;
}

.instructor-avatar.error::before {
    content: '⚠️';
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 20px;
    height: 20px;
    background: #fed7d7;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    border: 2px solid white;
}

.instructor-content {
    flex: 1;
}

.instructor-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.instructor-name {
    font-size: 1.3rem;
    font-weight: 700;
    color: #2d3748;
}

.instructor-badge {
    padding: 4px 8px;
    background: #667eea;
    color: white;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.instructor-title {
    font-size: 1rem;
    color: #4a5568;
    font-weight: 600;
    margin-bottom: 8px;
}

.instructor-details {
    color: #718096;
    line-height: 1.6;
    font-size: 0.95rem;
}

.instructor-experience {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #e2e8f0;
    font-size: 0.9rem;
    color: #4a5568;
}

/* 레거시 지원 */
.instructor-info {
    background: #f8fafc;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #667eea;
}

/* 일정 정보 */
.schedule-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.schedule-item {
    background: #f8fafc;
    padding: 20px;
    border-radius: 12px;
    text-align: center;
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
}

.schedule-item:hover {
    background: #edf2f7;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
}

.schedule-label {
    font-size: 0.9rem;
    color: #718096;
    font-weight: 600;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.schedule-value {
    font-size: 1.2rem;
    font-weight: 700;
    color: #2d3748;
    line-height: 1.3;
}

/* 위치 정보 */
.location-info {
    background: #f8fafc;
    padding: 20px;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    color: #2d3748;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.location-type {
    display: inline-block;
    padding: 6px 12px;
    background: #667eea;
    color: white;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 12px;
}

.location-details {
    color: #2d3748;
    font-weight: 600;
}

/* 신청 정보 */
.registration-info {
    text-align: center;
}

.registration-status {
    font-size: 1.2rem;
    font-weight: 700;
    margin-bottom: 10px;
}

.registration-count {
    color: #667eea;
}

.registration-deadline {
    color: #e53e3e;
    font-size: 0.9rem;
    margin-bottom: 15px;
}

.registration-fee {
    font-size: 1.5rem;
    font-weight: 700;
    color: #48bb78;
    margin-bottom: 20px;
}

.btn-register {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-size: 1.1rem;
    padding: 15px 30px;
    border-radius: 8px;
    font-weight: 700;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
    width: 100%;
    text-align: center;
}

.btn-register:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
}

.btn-register:disabled {
    background: #a0aec0;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

/* 강의 신청 상태 메시지 스타일 (행사 페이지와 동일) */
.lecture-status-message {
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 15px;
    border: 1px solid;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.lecture-status-message .status-content {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.lecture-status-message .status-icon {
    font-size: 20px;
    margin-top: 2px;
    width: 24px;
    text-align: center;
}

.lecture-status-message .status-text {
    flex: 1;
}

.lecture-status-message .status-title {
    font-weight: 600;
    font-size: 16px;
    margin-bottom: 4px;
}

.lecture-status-message .status-description {
    font-size: 14px;
    opacity: 0.9;
    line-height: 1.4;
}

/* 승인 상태 스타일 */
.lecture-status-message.approved {
    background: #f0f9ff;
    border-color: #0ea5e9;
    color: #0c4a6e;
}

.lecture-status-message.approved .status-icon {
    color: #0ea5e9;
}

/* 거절 상태 스타일 */
.lecture-status-message.rejected {
    background: #fef2f2;
    border-color: #ef4444;
    color: #991b1b;
}

.lecture-status-message.rejected .status-icon {
    color: #ef4444;
}

/* 대기 상태 스타일 */
.lecture-status-message.pending {
    background: #fffbeb;
    border-color: #f59e0b;
    color: #92400e;
}

.lecture-status-message.pending .status-icon {
    color: #f59e0b;
}

/* 대기열 상태 스타일 */
.lecture-status-message.waiting {
    background: #f8fafc;
    border-color: #64748b;
    color: #475569;
}

.lecture-status-message.waiting .status-icon {
    color: #64748b;
}

/* 참가자 목록 */
.participants-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.participant-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background: #f8fafc;
    border-radius: 6px;
}

.participant-avatar {
    width: 32px;
    height: 32px;
    background: #667eea;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 0.8rem;
}

.participant-info {
    flex: 1;
}

.participant-name {
    font-weight: 600;
    color: #2d3748;
    font-size: 0.9rem;
}

.participant-date {
    font-size: 0.8rem;
    color: #718096;
}

/* 관련 강의 */
.related-lectures {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.related-lecture-item {
    padding: 15px;
    background: #f8fafc;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    color: inherit;
    border-left: 4px solid #667eea;
}

.related-lecture-item:hover {
    background: #e2e8f0;
    transform: translateX(4px);
}

.related-lecture-title {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 5px;
    font-size: 0.9rem;
}

.related-lecture-meta {
    font-size: 0.8rem;
    color: #718096;
}

/* 모바일 반응형 */
@media (max-width: 1024px) {
    .lecture-content {
        grid-template-columns: 1fr !important;
    }
    
    .lecture-sidebar {
        order: 10 !important;
    }
}

/* 포괄적 모바일 최적화 - 768px 이하 태블릿/모바일 */
@media (max-width: 768px) {
    /* 기본 컨테이너 최적화 */
    .lecture-detail-container {
        padding: 12px;
        margin: 0;
        /* 모바일 헤더 겹침 방지: 모바일 헤더 높이에 맞춰 조정 */
        margin-top: 30px; /* 모바일: 헤더와 적절한 간격 유지 */
        padding-top: 15px;
    }
    
    /* 배너 영역 최적화 */
    .lecture-banner {
        padding: 51px 21px 30px 21px;
        margin: 19px -1px 0 -1px;
    }
    
    .lecture-title {
        font-size: 24px !important; /* 2rem에서 24px로 명시적 설정 */
        line-height: 1.3;
        margin-bottom: 12px;
    }
    
    .lecture-subtitle {
        font-size: 16px !important;
        line-height: 1.4;
    }
    
    .lecture-category {
        font-size: 14px !important;
        padding: 8px 16px;
    }
    
    /* 메타 정보 모바일 최적화 */
    .lecture-meta-basic {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    
    .meta-item {
        font-size: 16px !important;
        padding: 4px 0;
    }
    
    .meta-icon {
        font-size: 18px !important;
    }
    
    /* 콘텐츠 레이아웃 단일 컬럼으로 변경 */
    .lecture-content {
        grid-template-columns: 1fr !important;
        gap: 15px;
    }
    
    /* 메인 콘텐츠 최적화 */
    .lecture-main {
        padding: 20px 16px;
        margin-bottom: 15px;
    }
    
    /* 사이드바를 하단으로 이동 */
    .lecture-sidebar {
        order: 10 !important;
        margin-top: 15px;
        padding: 16px;
    }
    
    /* 사이드바 카드들 */
    .sidebar-card {
        padding: 16px;
        margin-bottom: 15px;
    }
    
    .sidebar-card h3 {
        font-size: 18px !important;
        margin-bottom: 12px;
    }
    
    /* 스케줄 그리드 */
    .schedule-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    
    .schedule-item {
        padding: 16px;
    }
    
    .schedule-date {
        font-size: 16px !important;
    }
    
    .schedule-time {
        font-size: 14px !important;
    }
    
    /* 강사 카드 모바일 최적화 */
    .instructor-card {
        flex-direction: column;
        text-align: center;
        gap: 15px;
        padding: 20px 16px;
    }
    
    .instructor-avatar {
        width: 80px;
        height: 80px;
        margin: 0 auto;
    }
    
    .instructor-name {
        font-size: 18px !important;
    }
    
    .instructor-title {
        font-size: 14px !important;
    }
    
    .instructor-header {
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .instructor-experience {
        text-align: left;
    }
    
    /* 액션 버튼들 최적화 */
    .lecture-banner .lecture-actions {
        position: absolute !important;
        top: 8px !important;
        right: 8px !important;
        justify-content: flex-end !important;
        margin-top: 0 !important;
        padding: 0 !important;
    }
    
    /* 모든 버튼 터치 타겟 44px+ 확보 (강의 액션 버튼 제외) */
    .btn:not(.lecture-actions .btn), button:not(.lecture-actions .btn),
    .btn-primary:not(.lecture-actions .btn), .btn-secondary:not(.lecture-actions .btn),
    .btn-register:not(.lecture-actions .btn), .btn-edit:not(.lecture-actions .btn),
    .btn-danger:not(.lecture-actions .btn), .btn-success:not(.lecture-actions .btn),
    a[class*="btn"]:not(.lecture-actions .btn), input[type="button"], input[type="submit"], [role="button"] {
        min-height: 48px !important;
        min-width: 48px !important;
        padding: 12px 20px !important;
        font-size: 16px !important;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        border-radius: 8px;
        touch-action: manipulation;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        box-sizing: border-box !important;
    }

    /* 강의 액션 버튼들은 모바일에서도 작게 유지 */
    .lecture-actions .btn {
        min-height: 28px !important;
        max-height: 28px !important;
        padding: 6px 12px !important;
        font-size: 12px !important;
        min-width: auto !important;
    }
    
    /* 숨겨진 버튼들 강제 표시 */
    .lecture-detail-container button[style*="display: none"],
    .lecture-detail-container .btn[style*="display: none"] {
        display: inline-flex !important;
        visibility: visible !important;
    }
    
    /* 모든 텍스트 최소 16px 확보 */
    .lecture-description, .sidebar-card p, .schedule-item, .instructor-info {
        font-size: 16px !important;
        line-height: 1.5;
    }
    
    /* 특정 작은 텍스트들 강제 확대 */
    .lecture-detail-container p, 
    .lecture-detail-container span,
    .lecture-detail-container div:not(.lecture-title):not(.lecture-subtitle) {
        font-size: 16px !important;
        line-height: 1.5 !important;
    }
    
    /* 이모지 영역 폰트 크기 강제 설정 */
    .status-indicator, .capacity-indicator, .venue-indicator {
        font-size: 16px !important;
    }
    
    /* 참가자 목록 최적화 */
    .participant-item {
        padding: 12px 16px !important;
        margin-bottom: 8px;
    }
    
    .participant-name {
        font-size: 16px !important;
    }
    
    /* 관련 강의 목록 최적화 */
    .related-lecture-item {
        padding: 16px !important;
        margin-bottom: 12px;
    }
    
    .related-lecture-title {
        font-size: 16px !important;
        line-height: 1.4;
    }
    
    /* 프로필 관련 버튼들 */
    .btn-visit-profile, .btn-chat-author {
        min-height: 44px !important;
        padding: 10px 16px !important;
        font-size: 14px !important;
    }
    
    /* 수평 스크롤 완전 제거 */
    * {
        max-width: 100%;
        box-sizing: border-box;
    }
    
    /* 이미지 반응형 처리 */
    img {
        max-width: 100% !important;
        height: auto !important;
    }
    
    /* 테이블 반응형 처리 */
    table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
}

/* 소형 모바일 최적화 - 480px 이하 */
@media (max-width: 480px) {
    /* 컨테이너 여백 최소화 */
    .lecture-detail-container {
        padding: 8px;
    }
    
    /* 배너 더 컴팩트하게 */
    .lecture-banner {
        padding: 46px 17px 24px 17px;
        margin: 9px -1px 0 -1px;
    }
    
    .lecture-title {
        font-size: 20px !important;
        line-height: 1.2;
    }
    
    .lecture-subtitle {
        font-size: 14px !important;
    }
    
    /* 메인 콘텐츠 패딩 축소 */
    .lecture-main {
        padding: 16px 12px;
    }
    
    .sidebar-card {
        padding: 12px;
    }
    
    /* 버튼 크기 조정 (여전히 44px+ 유지) */
    .btn, .btn-register {
        padding: 14px 16px !important;
        font-size: 15px !important;
        border-radius: 6px;
    }
    
    /* 강사 아바타 크기 조정 */
    .instructor-avatar {
        width: 70px;
        height: 70px;
    }
    
    /* 스케줄 아이템 패딩 축소 */
    .schedule-item {
        padding: 12px;
    }
    
    /* 텍스트 크기 세밀 조정 */
    .meta-item {
        font-size: 15px !important;
    }
    
    .sidebar-card h3 {
        font-size: 16px !important;
    }
    
    /* 액션 영역 패딩 축소 */
    .lecture-banner .lecture-actions {
        position: absolute !important;
        top: 6px !important;
        right: 6px !important;
        padding: 0 !important;
        margin-top: 0 !important;
    }
}

/* 강의 갤러리 스타일 */
.lecture-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.gallery-item {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s ease;
    aspect-ratio: 16/9;
}

.gallery-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.gallery-item:hover img {
    transform: scale(1.05);
}

.gallery-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
    color: white;
    font-weight: 600;
}

.gallery-item:hover .gallery-overlay {
    opacity: 1;
}

/* 이미지 모달 */
.image-modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.9);
    backdrop-filter: blur(4px);
}

.modal-image-content {
    position: relative;
    margin: auto;
    display: block;
    width: 90%;
    max-width: 1000px;
    max-height: 90vh;
    object-fit: contain;
    margin-top: 5vh;
    border-radius: 8px;
}

.modal-image-close {
    position: absolute;
    top: 20px;
    right: 35px;
    color: white;
    font-size: 40px;
    font-weight: bold;
    cursor: pointer;
    transition: color 0.3s ease;
}

.modal-image-close:hover {
    color: #ccc;
}

.modal-image-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0, 0, 0, 0.6);
    color: white;
    border: none;
    font-size: 18px;
    width: 50px;
    height: 50px;
    cursor: pointer;
    border-radius: 50%;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.2);
}

.modal-image-nav:hover {
    background: rgba(0, 0, 0, 0.8);
    border-color: rgba(255, 255, 255, 0.4);
    transform: translateY(-50%) scale(1.1);
}

.modal-nav-prev {
    left: 20px;
}

.modal-nav-next {
    right: 20px;
}

.modal-nav-prev::before {
    content: '‹';
    font-size: 24px;
    font-weight: bold;
    line-height: 1;
}

.modal-nav-next::before {
    content: '›';
    font-size: 24px;
    font-weight: bold;
    line-height: 1;
}

.modal-image-counter {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    color: white;
    background: rgba(0, 0, 0, 0.7);
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
}

/* 네이버 지도 스타일 */
.naver-map-container {
    margin-top: 15px;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border: 1px solid #e2e8f0;
}

/* 네이버 지도 컨테이너 호버 효과 */
.naver-map-container:hover {
    box-shadow: 0 4px 12px rgba(0, 199, 60, 0.15);
    transition: box-shadow 0.3s ease;
}

/* 기존 프로필 이미지 모달 CSS 제거됨 - profile-modal.css 통합 시스템 사용 */

/* 작성자 정보 카드 (컴팩트) */
.author-info-card {
    border-left: 3px solid #667eea;
}

.author-info-compact {
    display: flex;
    align-items: center;
    gap: 12px;
}

.author-avatar-small {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    flex-shrink: 0;
    transition: transform 0.2s ease;
    cursor: pointer;
}

.author-avatar-small:hover {
    transform: scale(1.1);
}

.author-details-compact {
    flex: 1;
    min-width: 0;
}

.author-name-compact {
    font-size: 1rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 2px;
}

.author-meta-compact {
    font-size: 0.8rem;
    color: #718096;
    margin-bottom: 4px;
}

.author-bio-compact {
    font-size: 0.8rem;
    color: #4a5568;
    line-height: 1.3;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

/* 프로필 방문 버튼 */
.btn-visit-profile {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: #667eea;
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 600;
    transition: all 0.3s ease;
    justify-content: center;
    height: 44px;
    margin: 0;
}

.btn-visit-profile:hover {
    background: #5a67d8;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    text-decoration: none;
}

.btn-visit-profile i {
    font-size: 0.875rem;
}

.btn-chat-author {
    background: #667eea;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 8px 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 44px;
    height: 44px;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 600;
    margin: 0;
    flex-shrink: 0;
}

.btn-chat-author:hover {
    background: #5a67d8;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    text-decoration: none;
    color: white;
}

.btn-chat-author i {
    font-size: 0.875rem;
}

/* PC와 모바일 모든 화면 크기에서 화이트 배경 일관성 유지 */

/* 글로벌 화이트 배경 강제 적용 */
body {
    background-color: white !important;
}

/* 모든 화면 크기에서 화이트 배경 유지 */
.lecture-detail-container {
    background: white !important;
}

.lecture-main, .sidebar-card {
    background: white !important;
    border-color: #e2e8f0 !important;
}

/* 강의 헤더는 투명 배경 유지 */
.lecture-header {
    background: transparent !important;
}

.schedule-item, .instructor-info, .participant-item, .related-lecture-item {
    background: white !important;
    border-color: #e2e8f0 !important;
    color: #333 !important;
}

.profile-image-modal .modal-content {
    background: white !important;
    color: #333 !important;
}

.profile-image-modal .modal-header {
    background: #f8fafc !important;
    border-color: #e2e8f0 !important;
}

.profile-image-modal .modal-header h3 {
    color: #2d3748 !important;
}

/* 모바일에서도 화이트 배경 강제 유지 */
@media (max-width: 768px) {
    body {
        background-color: white !important;
    }
    
    .lecture-detail-container {
        background: white !important;
    }
    
    .lecture-main, .sidebar-card {
        background: white !important;
        border-color: #e2e8f0 !important;
    }

    .lecture-header {
        background: transparent !important;
    }
    
    .schedule-item, .instructor-info, .participant-item, .related-lecture-item {
        background: white !important;
        border-color: #e2e8f0 !important;
        color: #333 !important;
    }
}

/* 다크모드 감지되어도 화이트 배경 강제 유지 */
@media (prefers-color-scheme: dark) {
    body {
        background-color: white !important;
    }
    
    .lecture-detail-container {
        background: white !important;
    }
    
    .lecture-main, .sidebar-card {
        background: white !important;
        border-color: #e2e8f0 !important;
        color: #333 !important;
    }

    .lecture-header {
        background: transparent !important;
    }
    
    .schedule-item, .instructor-info, .participant-item, .related-lecture-item {
        background: white !important;
        border-color: #e2e8f0 !important;
        color: #333 !important;
    }
    
    
    /* 텍스트 색상도 라이트 모드로 유지 */
    .lecture-title, .lecture-subtitle, .lecture-description,
    .schedule-date, .schedule-time, .instructor-name,
    .instructor-title, .participant-name, .related-lecture-title {
        color: #333 !important;
    }
    
    .lecture-meta, .schedule-meta, .instructor-info-text {
        color: #718096 !important;
    }
}
</style>

<div class="lecture-detail-container">
    <!-- 강의 헤더 -->
    <div class="lecture-header">
        <div class="lecture-banner">
            <div class="lecture-actions">
                <?php if ($canEdit): ?>
                    <?= renderButton('✏️ 수정', 'secondary', 'md', [
                        'class' => 'btn-edit',
                        'attributes' => ['data-lecture-id' => $lecture['id']]
                    ]) ?>
                    <?= renderButton('🗑️ 삭제', 'danger', 'md', [
                        'onclick' => 'confirmDeleteLecture(' . $lecture['id'] . ')'
                    ]) ?>
                <?php endif; ?>
                
                <?php if ($isLoggedIn && !$canEdit): ?>
                    <!-- 일반 사용자 신청 버튼 -->
                    <div id="registration-actions">
                        <!-- 여기에 동적으로 신청 버튼이 생성됩니다 -->
                    </div>
                <?php elseif ($isLoggedIn && $canEdit && isset($_GET['debug_registration']) && $_GET['debug_registration'] === 'true'): ?>
                    <!-- 디버그 모드: 강의 작성자 신청 테스트 -->
                    <div id="registration-actions-debug">
                        <!-- 여기에 동적으로 신청 버튼이 생성됩니다 -->
                        <small style="color: #ff6b6b; font-weight: bold;">🔧 DEBUG MODE: 강의 작성자 신청 테스트</small>
                    </div>
                <?php elseif (!$isLoggedIn): ?>
                    <a href="/auth/login?return_to=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-primary">
                        🚀 로그인 후 신청하기
                    </a>
                <?php endif; ?>
                
                <?= renderButton('🔗 공유하기', 'secondary', 'md', [
                    'onclick' => 'shareContent()'
                ]) ?>
            </div>
            
            <div class="lecture-category">
                <?= [
                    'seminar' => '📢 세미나',
                    'workshop' => '🛠️ 워크샵',
                    'conference' => '🏢 컨퍼런스',
                    'webinar' => '💻 웨비나',
                    'training' => '🎓 교육과정'
                ][$lecture['category']] ?? $lecture['category'] ?>
            </div>
            
            <h1 class="lecture-title"><?= htmlspecialchars($lecture['title']) ?></h1>
            <p class="lecture-subtitle">
                👨‍🏫 <?= htmlspecialchars($lecture['organizer_name']) ?> 강사님과 함께하는 특별한 시간
            </p>
            
            <div class="lecture-meta-basic">
                <div class="meta-item">
                    <span class="meta-icon">🟢</span>
                    <span>
                        시작 : <?= date('Y년 m월 d일 H:i', strtotime($lecture['start_date'] . ' ' . $lecture['start_time'])) ?>
                    </span>
                </div>
                <div class="meta-item">
                    <span class="meta-icon">🔴</span>
                    <span>
                        종료 : <?= date('Y년 m월 d일 H:i', strtotime($lecture['end_date'] . ' ' . $lecture['end_time'])) ?>
                    </span>
                </div>
                <div class="meta-item">
                    <span class="meta-icon">
                        <?php if ($lecture['location_type'] === 'online'): ?>
                            💻
                        <?php elseif ($lecture['location_type'] === 'hybrid'): ?>
                            🔄
                        <?php else: ?>
                            📍
                        <?php endif; ?>
                    </span>
                    <span>
                        <?php if ($lecture['location_type'] === 'online'): ?>
                            온라인 진행
                        <?php elseif ($lecture['location_type'] === 'hybrid'): ?>
                            하이브리드 (온라인 + 오프라인)
                        <?php else: ?>
                            <?= htmlspecialchars($lecture['venue_name'] ?? '오프라인 진행') ?>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="meta-item">
                    <span class="meta-icon">👥</span>
                    <span><?= $lecture['capacity_info'] ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 메인 콘텐츠 -->
    <div class="lecture-content">
        <div class="lecture-main">
            <!-- 거절 메시지 (메인 콘텐츠 상단에 표시) -->
            <div id="lecture-status-message" class="lecture-status-message" style="display: none;">
                <div class="status-content">
                    <div class="status-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="status-text">
                        <div class="status-title" id="lecture-status-title"></div>
                        <div class="status-description" id="lecture-status-description"></div>
                    </div>
                </div>
            </div>
            
            <!-- 강의 이미지 갤러리 -->
            <?php if (!empty($lecture['images'])): ?>
                <div class="info-section">
                    <h2 class="section-title">🖼️ 이미지 (총 <?= count($lecture['images']) ?>개)</h2>
                    
                    <div class="lecture-gallery">
                        <?php foreach ($lecture['images'] as $index => $image): ?>
                            <div class="gallery-item" onclick="openImageModal(<?= $index ?>)">
                                <img src="<?= htmlspecialchars($image['url']) ?>" 
                                     alt="강의 이미지 <?= $index + 1 ?>"
                                     loading="lazy">
                                <div class="gallery-overlay">
                                    <span>🔍 크게 보기</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- 강의 설명 -->
            <div class="info-section">
                <h2 class="section-title">📋 강의 소개</h2>
                <div class="description-content">
                    <?= nl2br(htmlspecialchars($lecture['description'])) ?>
                </div>
            </div>
            
            <!-- 유튜브 동영상 -->
            <?php if (!empty($lecture['youtube_video'])): ?>
                <div class="info-section">
                    <h2 class="section-title">📹 동영상</h2>
                    <div class="video-container">
                        <?php
                        $youtubeUrl = $lecture['youtube_video'];
                        // 유튜브 URL을 embed 형태로 변환
                        $embedUrl = '';
                        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $youtubeUrl, $matches)) {
                            $videoId = $matches[1];
                            $embedUrl = "https://www.youtube.com/embed/{$videoId}";
                        }
                        ?>
                        <?php if ($embedUrl): ?>
                            <iframe 
                                src="<?= htmlspecialchars($embedUrl) ?>" 
                                width="100%" 
                                height="400" 
                                frameborder="0" 
                                allowfullscreen
                                style="border-radius: 8px;">
                            </iframe>
                        <?php else: ?>
                            <div style="padding: 20px; background: #f8fafc; border-radius: 8px; text-align: center;">
                                <p>📹 <a href="<?= htmlspecialchars($youtubeUrl) ?>" target="_blank" rel="noopener">유튜브에서 동영상 보기</a></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- 강사 정보 -->
            <div class="info-section">
                <h2 class="section-title">👨‍🏫 강사 소개</h2>
                <div class="instructors-container">
                    <?php 
                    // 강사 정보 파싱 (여러 강사 대응)
                    $instructorNames = explode(',', $lecture['instructor_name']);
                    $instructorInfos = !empty($lecture['instructor_info']) ? 
                        explode('|||', $lecture['instructor_info']) : [];
                    
                    // instructors_json 필드에서 실제 강사 이미지 정보 가져오기
                    $instructorsData = [];
                    if (!empty($lecture['instructors_json'])) {
                        $instructorsData = json_decode($lecture['instructors_json'], true);
                        if (!$instructorsData) {
                            $instructorsData = [];
                        }
                    }
                    
                    // 디버깅: 강사 정보 출력 (개발 중에만 사용)
                    if (isset($_GET['debug'])) {
                        echo "<!-- 디버깅 정보:\n";
                        echo "강의 ID: " . $lecture['id'] . "\n";
                        echo "instructor_name: " . htmlspecialchars($lecture['instructor_name']) . "\n";
                        echo "instructor_info: " . htmlspecialchars($lecture['instructor_info']) . "\n";
                        echo "instructors_json: " . htmlspecialchars($lecture['instructors_json']) . "\n";
                        echo "강사 이름 배열: " . print_r($instructorNames, true) . "\n";
                        echo "강사 정보 배열: " . print_r($instructorInfos, true) . "\n";
                        echo "강사 JSON 데이터: " . print_r($instructorsData, true) . "\n";
                        
                        // 강사 이미지 파일 존재 여부 확인
                        if (!empty($instructorsData) && is_array($instructorsData)) {
                            echo "강사 이미지 파일 존재 여부:\n";
                            foreach ($instructorsData as $index => $instructor) {
                                if (!empty($instructor['image'])) {
                                    $imagePath = $_SERVER['DOCUMENT_ROOT'] . $instructor['image'];
                                    $exists = file_exists($imagePath);
                                    echo "  강사 {$index}: " . $instructor['image'] . " => " . ($exists ? 'EXISTS' : 'NOT FOUND') . "\n";
                                    if ($exists) {
                                        echo "    파일 크기: " . filesize($imagePath) . " bytes\n";
                                    } else {
                                        echo "    전체 경로: " . $imagePath . "\n";
                                    }
                                } else {
                                    echo "  강사 {$index}: 이미지 경로 없음\n";
                                }
                            }
                        }
                        echo "-->\n";
                    }
                    
                    // instructors_json 데이터가 있으면 우선 사용, 없으면 기존 필드 사용
                    $finalInstructors = [];
                    
                    if (!empty($instructorsData) && is_array($instructorsData)) {
                        // instructors_json에서 강사 정보 사용
                        foreach ($instructorsData as $index => $instructor) {
                            $finalInstructors[] = [
                                'name' => $instructor['name'] ?? '',
                                'info' => $instructor['info'] ?? '전문적인 경험과 노하우를 바탕으로 실무에 바로 적용할 수 있는 내용을 전달합니다.',
                                'title' => $instructor['title'] ?? '강사',
                                'image' => $instructor['image'] ?? null
                            ];
                        }
                    } else {
                        // 기존 필드에서 강사 정보 사용
                        foreach ($instructorNames as $index => $instructorName) {
                            $name = trim($instructorName);
                            $info = isset($instructorInfos[$index]) ? trim($instructorInfos[$index]) : '';
                            if (empty($info)) {
                                $info = '전문적인 경험과 노하우를 바탕으로 실무에 바로 적용할 수 있는 내용을 전달합니다.';
                            }
                            
                            $finalInstructors[] = [
                                'name' => $name,
                                'info' => $info,
                                'title' => '강사',
                                'image' => null
                            ];
                        }
                    }
                    
                    foreach ($finalInstructors as $index => $instructor): 
                        $name = $instructor['name'];
                        $info = $instructor['info'];
                        $title = $instructor['title'];
                        $imagePath = $instructor['image'];
                    ?>
                        <div class="instructor-card">
                            <!-- 강사 아바타 -->
                            <?php 
                            // 기본 강사 이미지 경로들 (경로 상수 사용)
                            $defaultInstructorImages = [
                                INSTRUCTORS_WEB_PATH . '/instructor-1.jpg',
                                INSTRUCTORS_WEB_PATH . '/instructor-2.jpg', 
                                INSTRUCTORS_WEB_PATH . '/instructor-3.jpg',
                                INSTRUCTORS_WEB_PATH . '/instructor-kim.jpg',
                                INSTRUCTORS_WEB_PATH . '/instructor-lee.jpg',
                                INSTRUCTORS_WEB_PATH . '/instructor-park.jpg'
                            ];
                            
                            // 강사 이미지가 없거나 파일이 존재하지 않는 경우 기본 이미지 사용
                            if (!$imagePath || !file_exists($_SERVER['DOCUMENT_ROOT'] . $imagePath)) {
                                // 강사 이름 기반으로 기본 이미지 선택
                                $nameHash = crc32($name);
                                $selectedDefaultImage = $defaultInstructorImages[$nameHash % count($defaultInstructorImages)];
                                
                                // 기본 이미지 파일이 실제로 존재하는지 확인
                                if (file_exists($_SERVER['DOCUMENT_ROOT'] . $selectedDefaultImage)) {
                                    $imagePath = $selectedDefaultImage;
                                }
                            }
                            ?>
                            
                            <?php if ($imagePath && file_exists($_SERVER['DOCUMENT_ROOT'] . $imagePath)): ?>
                                <img src="<?= htmlspecialchars($imagePath) ?>" 
                                     alt="<?= htmlspecialchars($name) ?> 강사님" 
                                     class="instructor-avatar clickable-image"
                                     loading="lazy"
                                     decoding="async"
                                     onerror="console.error('강사 이미지 로딩 실패:', this.src); this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                     onclick="openInstructorImageModal('<?= htmlspecialchars($imagePath) ?>', '<?= htmlspecialchars($name) ?> 강사님')"
                                     title="<?= htmlspecialchars($name) ?> 강사님 (클릭하면 크게 볼 수 있습니다)">
                                <!-- 이미지 로딩 실패 시 대체 표시 -->
                                <div class="instructor-avatar placeholder" style="display: none;" title="<?= htmlspecialchars($name) ?> 강사님">
                                    <?= mb_substr($name, 0, 1) ?>
                                </div>
                            <?php else: ?>
                                <!-- 기본 플레이스홀더 -->
                                <div class="instructor-avatar placeholder" title="<?= htmlspecialchars($name) ?> 강사님">
                                    <?= mb_substr($name, 0, 1) ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- 강사 정보 -->
                            <div class="instructor-content">
                                <div class="instructor-header">
                                    <div class="instructor-name"><?= htmlspecialchars($name) ?></div>
                                    <?php if (count($finalInstructors) > 1): ?>
                                        <span class="instructor-badge"><?= htmlspecialchars($title) ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="instructor-title">
                                    <?= htmlspecialchars($title ?: ([
                                        'seminar' => '세미나 전문가',
                                        'workshop' => '워크샵 진행자',
                                        'conference' => '컨퍼런스 연사',
                                        'webinar' => '웨비나 호스트',
                                        'training' => '교육 전문가'
                                    ][$lecture['category']] ?? '마케팅 전문가')) ?>
                                </div>
                                
                                <div class="instructor-details">
                                    <?= nl2br(htmlspecialchars($info)) ?>
                                </div>
                                
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- 일정 상세 -->
            <div class="info-section">
                <h2 class="section-title">📅 일정 상세</h2>
                <div class="schedule-grid">
                    <div class="schedule-item">
                        <div class="schedule-label">
                            <span>🚀</span> 시작일시
                        </div>
                        <div class="schedule-value">
                            <?= date('Y-m-d H:i', strtotime($lecture['start_date'] . ' ' . $lecture['start_time'])) ?>
                        </div>
                    </div>
                    <div class="schedule-item">
                        <div class="schedule-label">
                            <span>🏁</span> 종료일시
                        </div>
                        <div class="schedule-value">
                            <?= date('Y-m-d H:i', strtotime($lecture['end_date'] . ' ' . $lecture['end_time'])) ?>
                        </div>
                    </div>
                    <div class="schedule-item">
                        <div class="schedule-label">
                            <span>⏱️</span> 소요시간
                        </div>
                        <div class="schedule-value">
                            <?php 
                            $startDateTime = strtotime($lecture['start_date'] . ' ' . $lecture['start_time']);
                            $endDateTime = strtotime($lecture['end_date'] . ' ' . $lecture['end_time']);
                            $durationMinutes = ($endDateTime - $startDateTime) / 60; // 분 단위
                            
                            $hours = floor($durationMinutes / 60);
                            $minutes = $durationMinutes % 60;
                            
                            if ($hours > 0 && $minutes > 0) {
                                echo $hours . '시간 ' . $minutes . '분';
                            } elseif ($hours > 0) {
                                echo $hours . '시간';
                            } else {
                                echo $minutes . '분';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="schedule-item">
                        <div class="schedule-label">
                            <span>🌏</span> 시간대
                        </div>
                        <div class="schedule-value">
                            <?= $lecture['timezone'] ?? 'Asia/Seoul' ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 위치 정보 -->
            <?php if ($lecture['location_type'] !== 'online'): ?>
                <div class="info-section">
                    <h2 class="section-title">📍 위치 정보</h2>
                    <div class="location-info">
                        <div class="location-type">
                            📍 오프라인
                        </div>
                        <?php if (!empty($lecture['venue_name'])): ?>
                            <div class="location-details">
                                <strong><?= htmlspecialchars($lecture['venue_name']) ?></strong>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($lecture['venue_address'])): ?>
                            <div style="margin-top: 8px; color: #4a5568; font-size: 14px; line-height: 1.5;">
                                📍 <?= htmlspecialchars($lecture['venue_address']) ?>
                            </div>
                            <!-- 네이버 지도 표시 (간단 다이나믹 맵) -->
                            <div class="naver-map-container">
                                <?php
                                $venueName = !empty($lecture['venue_name']) ? $lecture['venue_name'] : '강의 장소';
                                $mapAddress = !empty($lecture['venue_address']) ? $lecture['venue_address'] : '';
                                $naverClientId = defined('NAVER_MAPS_CLIENT_ID') ? NAVER_MAPS_CLIENT_ID : 'c5yj6m062z';
                                
                                // 실제 저장된 좌표 사용 (우선순위 1)
                                if (!empty($lecture['venue_latitude']) && !empty($lecture['venue_longitude'])) {
                                    $defaultCoords = [
                                        'lat' => floatval($lecture['venue_latitude']),
                                        'lng' => floatval($lecture['venue_longitude'])
                                    ];
                                } else {
                                    // 좌표가 없는 경우 지역 기반 근사 좌표 사용 (fallback)
                                    $defaultCoords = [
                                        'lat' => 37.5665,  // 서울시청 기본
                                        'lng' => 126.9780
                                    ];
                                    
                                    // 반도 아이비밸리 정확 좌표 사용 (실제 측정 좌표)
                                    if (strpos($mapAddress, '반도 아이비밸리') !== false || strpos($mapAddress, '가산디지털1로 204') !== false) {
                                        $defaultCoords['lat'] = 37.4835033620443;
                                        $defaultCoords['lng'] = 126.881038151818;
                                    } elseif (strpos($mapAddress, '가산') !== false || strpos($mapAddress, '금천구') !== false) {
                                        $defaultCoords['lat'] = 37.4816;
                                        $defaultCoords['lng'] = 126.8819;
                                    } elseif (strpos($mapAddress, '강남') !== false) {
                                        $defaultCoords['lat'] = 37.4979;
                                        $defaultCoords['lng'] = 127.0276;
                                    } elseif (strpos($mapAddress, '홍대') !== false || strpos($mapAddress, '마포') !== false) {
                                        $defaultCoords['lat'] = 37.5563;
                                        $defaultCoords['lng'] = 126.9236;
                                    } elseif (strpos($mapAddress, '송파') !== false || strpos($mapAddress, '올림픽로') !== false) {
                                        $defaultCoords['lat'] = 37.5126;
                                        $defaultCoords['lng'] = 127.1026;
                                    } elseif (strpos($mapAddress, '청주') !== false) {
                                        $defaultCoords['lat'] = 36.6424;
                                        $defaultCoords['lng'] = 127.4890;
                                    }
                                }
                                ?>
                                
                                <!-- 지도 컨테이너 -->
                                <div id="naverMap-<?= $lecture['id'] ?>" style="
                                    width: 100%; 
                                    height: 350px; 
                                    border-radius: 8px; 
                                    overflow: hidden;
                                    border: 1px solid #e2e8f0;
                                "></div>
                                
                                <!-- 네이버 지도 API (간단 버전) -->
                                <script type="text/javascript" 
                                        src="https://oapi.map.naver.com/openapi/v3/maps.js?ncpKeyId=<?= htmlspecialchars($naverClientId) ?>&callback=initSimpleNaverMap_<?= $lecture['id'] ?>"
                                        onerror="showMapFallback_<?= $lecture['id'] ?>()">
                                </script>
                                
                                <script type="text/javascript">
                                // 네이버 지도 API 사용 가능 여부 확인
                                function checkNaverMapsAPI() {
                                    return typeof naver !== 'undefined' && 
                                           typeof naver.maps !== 'undefined' && 
                                           typeof naver.maps.Map !== 'undefined';
                                }
                                
                                // 지도 대체 UI 표시 함수
                                function showMapFallback_<?= $lecture['id'] ?>() {
                                    var mapContainer = document.getElementById('naverMap-<?= $lecture['id'] ?>');
                                    if (mapContainer) {
                                        mapContainer.innerHTML = 
                                            '<div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; background: #f8fafc; color: #4a5568; border-radius: 8px; border: 1px solid #e2e8f0;">' +
                                            '<div style="font-size: 32px; margin-bottom: 15px; color: #667eea;">🏢</div>' +
                                            '<div style="font-weight: bold; margin-bottom: 8px; font-size: 16px; color: #2d3748;">' + <?= json_encode($venueName) ?> + '</div>' +
                                            '<div style="font-size: 13px; margin-bottom: 20px; text-align: center; padding: 0 20px; color: #4a5568;">' + <?= json_encode($mapAddress) ?> + '</div>' +
                                            '<a href="https://map.naver.com/v5/search/<?= urlencode($mapAddress) ?>" target="_blank" ' +
                                            'style="background: #667eea; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: bold;">' +
                                            '📍 네이버 지도에서 보기</a>' +
                                            '</div>';
                                    }
                                }
                                
                                // 강의별 독립적인 지도 초기화 함수
                                function initSimpleNaverMap_<?= $lecture['id'] ?>() {
                                    try {
                                        // 네이버 지도 API 사용 가능 여부 확인
                                        if (!checkNaverMapsAPI()) {
                                            console.warn('🗺️ 네이버 지도 API를 사용할 수 없습니다.');
                                            showMapFallback_<?= $lecture['id'] ?>();
                                            return;
                                        }
                                        
                                        console.log('🗺️ 네이버 지도 (강의 <?= $lecture['id'] ?>) 초기화 시작');
                                        
                                        // 지도 중심 좌표
                                        var center = new naver.maps.LatLng(<?= floatval($defaultCoords['lat']) ?>, <?= floatval($defaultCoords['lng']) ?>);
                                        
                                        // 지도 옵션
                                        var mapOptions = {
                                            center: center,
                                            zoom: 19,
                                            mapTypeControl: true,
                                            mapTypeControlOptions: {
                                                style: naver.maps.MapTypeControlStyle.BUTTON,
                                                position: naver.maps.Position.TOP_RIGHT
                                            },
                                            zoomControl: true,
                                            zoomControlOptions: {
                                                style: naver.maps.ZoomControlStyle.SMALL,
                                                position: naver.maps.Position.RIGHT_CENTER
                                            }
                                        };
                                        
                                        // 지도 생성
                                        var map = new naver.maps.Map('naverMap-<?= $lecture['id'] ?>', mapOptions);
                                        
                                        // 빨간색 마커 생성 (네이버 맵 기본 마커 사용)
                                        var marker = new naver.maps.Marker({
                                            position: center,
                                            map: map,
                                            title: <?= json_encode($venueName) ?>,
                                            icon: {
                                                content: '<div style="width: 20px; height: 20px; background: #ff0000; border: 2px solid white; border-radius: 50%; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"></div>',
                                                anchor: new naver.maps.Point(10, 10)
                                            }
                                        });
                                        
                                        // 깔끔한 정보창 생성
                                        var infoWindow = new naver.maps.InfoWindow({
                                            content: '<div style="' +
                                                'padding: 16px 20px; ' +
                                                'text-align: center; ' +
                                                'min-width: 220px; ' +
                                                'background: white; ' +
                                                'color: #2d3748; ' +
                                                'border-radius: 8px; ' +
                                                'box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); ' +
                                                'border: 1px solid #e2e8f0;' +
                                            '">' +
                                                '<div style="font-weight: bold; margin-bottom: 6px; font-size: 15px; color: #1a202c;">' +
                                                '🏢 ' + <?= json_encode($venueName) ?> +
                                                '</div>' +
                                                '<div style="font-size: 12px; color: #4a5568; line-height: 1.4;">' +
                                                '📍 ' + <?= json_encode($mapAddress) ?> +
                                                '</div>' +
                                            '</div>',
                                            maxWidth: 260,
                                            backgroundColor: "white",
                                            borderColor: "#e2e8f0",
                                            borderWidth: 1,
                                            anchorSize: new naver.maps.Size(10, 10),
                                            anchorSkew: true,
                                            anchorColor: "white"
                                        });
                                        
                                        // 마커 클릭 이벤트
                                        naver.maps.Event.addListener(marker, 'click', function() {
                                            try {
                                                if (infoWindow.getMap()) {
                                                    infoWindow.close();
                                                } else {
                                                    infoWindow.open(map, marker);
                                                }
                                            } catch (e) {
                                                console.warn('마커 클릭 이벤트 오류:', e);
                                            }
                                        });
                                        
                                        // 지도 클릭 시 정보창 닫기
                                        naver.maps.Event.addListener(map, 'click', function() {
                                            try {
                                                infoWindow.close();
                                            } catch (e) {
                                                console.warn('지도 클릭 이벤트 오류:', e);
                                            }
                                        });
                                        
                                        // 1.5초 후 정보창 자동 열기
                                        setTimeout(function() {
                                            try {
                                                infoWindow.open(map, marker);
                                            } catch (e) {
                                                console.warn('정보창 자동 열기 오류:', e);
                                            }
                                        }, 1500);
                                        
                                        console.log('✅ 네이버 지도 (강의 <?= $lecture['id'] ?>) 초기화 완료');
                                        
                                    } catch (error) {
                                        console.error('❌ 네이버 지도 초기화 실패:', error);
                                        showMapFallback_<?= $lecture['id'] ?>();
                                    }
                                }
                                
                                // DOM 로드 완료 후 지도 API 확인
                                document.addEventListener('DOMContentLoaded', function() {
                                    // 3초 후에도 네이버 지도 API가 로드되지 않으면 대체 UI 표시
                                    setTimeout(function() {
                                        if (!checkNaverMapsAPI()) {
                                            console.warn('🗺️ 네이버 지도 API 로딩 타임아웃');
                                            showMapFallback_<?= $lecture['id'] ?>();
                                        }
                                    }, 3000);
                                });
                                
                                // 전역 오류 핸들러
                                window.addEventListener('error', function(e) {
                                    if (e.filename && e.filename.includes('maps.js')) {
                                        console.error('네이버 지도 스크립트 오류:', e.message);
                                        showMapFallback_<?= $lecture['id'] ?>();
                                    }
                                });
                                </script>
                            </div>
                            
                            <!-- 지도 하단 정보 -->
                            <div style="margin-top: 15px; padding: 12px; background: #f7fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                                <div style="display: flex; align-items: center; gap: 8px; font-size: 14px; color: #2d3748;">
                                    <span style="color: #667eea;">🏢</span>
                                    <strong><?= htmlspecialchars($lecture['venue_name'] ?? '강의 장소') ?></strong>
                                </div>
                                <div style="font-size: 13px; color: #4a5568; margin-top: 4px;">
                                    지도를 클릭하거나 확대하여 상세 위치를 확인하세요
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- 참가 요구사항 -->
            <?php if (!empty($lecture['requirements'])): ?>
                <div class="info-section">
                    <h2 class="section-title">📝 참가 요구사항</h2>
                    <div class="description-content">
                        <?= nl2br(htmlspecialchars($lecture['requirements'])) ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- 혜택 정보 -->
            <?php if (!empty($lecture['benefits'])): ?>
                <div class="info-section">
                    <h2 class="section-title">🎁 혜택</h2>
                    <div class="description-content">
                        <?= nl2br(htmlspecialchars($lecture['benefits'])) ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- 참가 조건 -->
            <?php if (!empty($lecture['prerequisites'])): ?>
                <div class="info-section">
                    <h2 class="section-title">📋 참가 조건</h2>
                    <div class="description-content">
                        <?= nl2br(htmlspecialchars($lecture['prerequisites'])) ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- 준비물 -->
            <?php if (!empty($lecture['what_to_bring'])): ?>
                <div class="info-section">
                    <h2 class="section-title">🎒 준비물</h2>
                    <div class="description-content">
                        <?= nl2br(htmlspecialchars($lecture['what_to_bring'])) ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- 기타 안내사항 -->
            <?php if (!empty($lecture['additional_info'])): ?>
                <div class="info-section">
                    <h2 class="section-title">📝 기타 안내사항</h2>
                    <div class="description-content">
                        <?= nl2br(htmlspecialchars($lecture['additional_info'])) ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- 사이드바 -->
        <div class="lecture-sidebar">
            <!-- 신청 정보 -->
            <div class="sidebar-card">
                <h3 class="sidebar-title">🎫 신청 정보</h3>
                <div class="registration-info">
                    <div class="registration-status">
                        <div style="font-size: 0.9rem; color: #718096; margin-bottom: 5px; font-weight: 600;">👥 신청 인원</div>
                        <span class="registration-count"><?= $lecture['capacity_info'] ?></span>
                    </div>
                    
                    <?php if ($lecture['registration_deadline']): ?>
                        <div class="registration-deadline">
                            ⏰ 등록 마감: <?= date('Y-m-d H:i', strtotime($lecture['registration_deadline'])) ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="registration-fee">
                        <?php if ($lecture['registration_fee'] > 0): ?>
                            💰 <?= number_format($lecture['registration_fee']) ?>원
                        <?php else: ?>
                            🆓 무료
                        <?php endif; ?>
                    </div>
                    
                        
                        <?php if ($isLoggedIn && $canEdit): ?>
                            <!-- 강의 작성자/관리자는 신청 UI 대신 관리 메시지 표시 -->
                            <div style="text-align: center; padding: 15px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                                <div style="font-size: 1rem; font-weight: 600; color: #667eea; margin-bottom: 5px;">
                                    ✏️ 강의 관리자
                                </div>
                                <div style="font-size: 0.9rem; color: #718096;">
                                    본인이 개설한 강의입니다
                                </div>
                            </div>
                        <?php elseif (!$isLoggedIn): ?>
                            <!-- 비로그인 사용자 - 항상 로그인 버튼만 표시 -->
                            <a href="/auth/login?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn-register">
                                🔑 로그인 후 신청
                            </a>
                        <?php elseif ($userRegistration): ?>
                            <!-- 로그인된 사용자 - 이미 신청한 경우 -->
                            <div class="btn-register" style="background: #68d391; cursor: default;">
                                ✅ 신청 완료
                            </div>
                        <?php elseif ($canRegister): ?>
                            <!-- 로그인된 사용자 - 신청 가능한 경우 -->
                            <a href="/lectures/<?= $lecture['id'] ?>/register" class="btn-register">
                                📝 지금 신청하기
                            </a>
                        <?php else: ?>
                            <!-- 로그인된 사용자 - 신청 불가능한 경우 -->
                            <div class="btn-register" style="background: #a0aec0; cursor: not-allowed;">
                                ❌ 신청 마감
                            </div>
                        <?php endif; ?>
                </div>
            </div>
            
            <!-- 참가자 목록 -->
            <?php if (!empty($registrations)): ?>
                <div class="sidebar-card">
                    <h3 class="sidebar-title">👥 참가자 목록</h3>
                    <div class="participants-list">
                        <?php foreach ($registrations as $registration): ?>
                            <div class="participant-item">
                                <div class="participant-avatar">
                                    <?= mb_substr($registration['nickname'], 0, 1) ?>
                                </div>
                                <div class="participant-info">
                                    <div class="participant-name"><?= htmlspecialchars($registration['nickname']) ?></div>
                                    <div class="participant-date"><?= date('m/d', strtotime($registration['registration_date'])) ?> 신청</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- 관련 강의 -->
            <?php if (!empty($relatedLectures)): ?>
                <div class="sidebar-card">
                    <h3 class="sidebar-title">📚 관련 강의</h3>
                    <div class="related-lectures">
                        <?php foreach ($relatedLectures as $relatedLecture): ?>
                            <a href="/lectures/<?= $relatedLecture['id'] ?>" class="related-lecture-item">
                                <div class="related-lecture-title"><?= htmlspecialchars($relatedLecture['title']) ?></div>
                                <div class="related-lecture-meta">
                                    📅 <?= date('m/d', strtotime($relatedLecture['start_date'])) ?> | 
                                    👨‍🏫 <?= htmlspecialchars($relatedLecture['organizer_name']) ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- 작성자 정보 -->
            <?php if (isset($lecture['author_name']) || isset($lecture['user_id'])): ?>
                <div class="sidebar-card author-info-card">
                    <h3 class="sidebar-title">✍️ 작성자</h3>
                    <div class="author-info-compact">
                        <?php 
                        $user = $lecture; 
                        $size = ProfileImageHelper::SIZE_THUMB;
                        $mode = 'direct';
                        $extraClasses = ['author-avatar-small'];
                        include SRC_PATH . '/views/components/profile-image.php';
                        
                        $authorName = $lecture['author_name'] ?? $lecture['nickname'] ?? '작성자';
                        ?>
                        <div class="author-details-compact">
                            <div class="author-name-compact"><?= htmlspecialchars($authorName) ?></div>
                            <div class="author-meta-compact">
                                📅 <?= date('Y.m.d', strtotime($lecture['created_at'])) ?>
                            </div>
                            <?php if (!empty($lecture['author_bio'])): ?>
                                <div class="author-bio-compact"><?= htmlspecialchars(mb_substr(strip_tags($lecture['author_bio']), 0, 80)) ?>...</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="display: flex; gap: 10px; margin-top: 12px; align-items: center;">
                        <?php if (isset($lecture['user_id'])): ?>
                            <a href="/profile/<?= $lecture['user_id'] ?>" class="btn-visit-profile" style="flex: 1;">
                                <i class="fas fa-user"></i> 프로필 방문
                            </a>
                            <?php if ($isLoggedIn && $lecture['user_id'] != $currentUserId): ?>
                                <?= renderButton('', 'primary', 'md', [
                                    'class' => 'btn-chat-author',
                                    'onclick' => 'startChatWithAuthor(' . $lecture['user_id'] . ', \'' . addslashes(htmlspecialchars($authorName)) . '\')',
                                    'icon' => 'fas fa-comment',
                                    'ariaLabel' => '채팅하기'
                                ]) ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- 이미지 모달 -->
<div id="imageModal" class="image-modal">
    <span class="modal-image-close" onclick="closeImageModal()">&times;</span>
    <img class="modal-image-content" id="modalImage">
    <button class="modal-image-nav modal-nav-prev" onclick="changeImage(-1)"></button>
    <button class="modal-image-nav modal-nav-next" onclick="changeImage(1)"></button>
    <div class="modal-image-counter" id="imageCounter">    </div>
</div>

<!-- 기존 프로필 이미지 모달 HTML 제거됨 - profile-modal.js 통합 시스템 사용 -->

<script>
// 전역 오류 핸들러 추가
window.addEventListener('error', function(event) {
    console.error('JavaScript 오류 감지:', {
        message: event.message,
        filename: event.filename,
        lineno: event.lineno,
        colno: event.colno,
        error: event.error
    });
});

// 안전한 함수 실행 헬퍼
function safeExecute(fn, context) {
    try {
        return fn.call(context);
    } catch (error) {
        console.warn('함수 실행 중 오류:', error);
        return null;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('📅 강의 상세 페이지 로드 완료');
    console.log('📊 강의 ID:', <?= $lecture['id'] ?>);
    console.log('👥 신청자 수:', <?= count($registrations ?? []) ?>);
    
    // 강의 상세 관련 전역 객체 정의
    if (typeof window.lectureDetail === 'undefined') {
        window.lectureDetail = {
            initialized: true,
            lectureId: <?= $lecture['id'] ?>,
            canRegister: <?= $canRegister ? 'true' : 'false' ?>,
            canEdit: <?= $canEdit ? 'true' : 'false' ?>,
            userRegistered: <?= $userRegistration ? 'true' : 'false' ?>,
            registrationCount: <?= count($registrations ?? []) ?>
        };
    }
    
    // 강사 이미지 로딩 개선
    initializeInstructorImages();
    
    // 강사 이미지 로딩 함수
    function initializeInstructorImages() {
        const instructorImages = document.querySelectorAll('.instructor-avatar img');
        
        instructorImages.forEach((img, index) => {
            // 로딩 상태 표시
            img.parentElement.classList.add('loading');
            
            img.addEventListener('load', function() {
                console.log('✅ 강사 이미지 ' + (index + 1) + ' 로딩 성공:', this.src);
                this.parentElement.classList.remove('loading');
                this.style.opacity = '1';
            });
            
            img.addEventListener('error', function() {
                console.warn('❌ 강사 이미지 ' + (index + 1) + ' 로딩 실패:', this.src);
                this.parentElement.classList.remove('loading');
                this.parentElement.classList.add('error');
                
                // 이미지 숨기고 placeholder 표시
                this.style.display = 'none';
                const placeholder = this.nextElementSibling;
                if (placeholder && placeholder.classList.contains('placeholder')) {
                    placeholder.style.display = 'flex';
                    placeholder.classList.add('error');
                }
            });
            
            // 이미지가 이미 로드된 경우 (캐시된 경우)
            if (img.complete && img.naturalHeight !== 0) {
                img.parentElement.classList.remove('loading');
                img.style.opacity = '1';
                console.log('✅ 강사 이미지 ' + (index + 1) + ' 캐시에서 로드됨:', img.src);
            }
        });
        
        // placeholder 이미지들에 호버 효과 추가
        const placeholders = document.querySelectorAll('.instructor-avatar.placeholder');
        placeholders.forEach(placeholder => {
            placeholder.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.05)';
                this.style.boxShadow = '0 6px 16px rgba(102, 126, 234, 0.3)';
            });
            
            placeholder.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
                this.style.boxShadow = '0 4px 8px rgba(102, 126, 234, 0.2)';
            });
        });
    }
    
    // 구식 신청 시스템 코드 제거됨 (모달 기반 신청 시스템 사용)
    
    // 일정 추가 버튼 이벤트
    const icalBtn = document.querySelector('a[download]');
    if (icalBtn) {
        icalBtn.addEventListener('click', function() {
            console.log('📅 iCal 파일 다운로드 시작');
        });
    }
    
    // 참가자 목록 애니메이션
    const participantItems = document.querySelectorAll('.participant-item');
    participantItems.forEach((item, index) => {
        item.style.animationDelay = (index * 0.1) + 's';
        item.style.animation = 'fadeInUp 0.5s ease forwards';
    });
    
    // 관련 강의 호버 효과
    const relatedItems = document.querySelectorAll('.related-lecture-item');
    relatedItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(8px)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(4px)';
        });
    });
    
    // 뒤로가기 단축키
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            window.history.back();
        }
    });
});

// 애니메이션 키프레임 추가
const style = document.createElement('style');
style.textContent = '@keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }';
document.head.appendChild(style);

// 이미지 갤러리 관련 변수
let currentImageIndex = 0;
let lectureImages = [];
let instructorImages = [];
let currentGalleryType = 'lecture'; // 'lecture' 또는 'instructor'

// 강의 이미지 데이터 초기화
lectureImages = [];

<?php if (!empty($lecture['images']) && is_array($lecture['images'])): ?>
    <?php foreach ($lecture['images'] as $index => $image): ?>
        lectureImages.push({
            url: "<?= addslashes($image['url'] ?? '') ?>",
            alt: "<?= addslashes($image['alt_text'] ?? '강의 이미지') ?>"
        });
    <?php endforeach; ?>
<?php endif; ?>

// 강사 이미지 데이터 초기화 (instructors_json에서 추출)
instructorImages = [];
<?php 
// instructors_json에서 강사 이미지 추출
if (!empty($lecture['instructors_json'])) {
    $instructorsData = json_decode($lecture['instructors_json'], true);
    if (is_array($instructorsData)) {
        foreach ($instructorsData as $index => $instructor) {
            if (!empty($instructor['image'])) {
?>
                instructorImages.push({
                    url: "<?= addslashes($instructor['image']) ?>",
                    alt: "<?= addslashes(($instructor['name'] ?? '강사') . ' 이미지') ?>"
                });
<?php 
            }
        }
    }
}
?>

/**
 * 이미지 모달 열기 (강의 이미지용)
 */
function openImageModal(index) {
    if (lectureImages.length === 0) return;
    
    currentImageIndex = index;
    currentGalleryType = 'lecture';
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    const counter = document.getElementById('imageCounter');
    
    modal.style.display = 'block';
    modalImg.src = lectureImages[currentImageIndex].url;
    counter.textContent = (currentImageIndex + 1) + ' / ' + lectureImages.length;
    
    document.body.style.overflow = 'hidden';
}

/**
 * 강사 이미지 모달 열기 (강사 이미지 전용)
 */
function openInstructorImageModal(index) {
    if (instructorImages.length === 0) return;
    
    currentImageIndex = index;
    currentGalleryType = 'instructor';
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    const counter = document.getElementById('imageCounter');
    
    modal.style.display = 'block';
    modalImg.src = instructorImages[currentImageIndex].url;
    counter.textContent = '강사 이미지 ' + (currentImageIndex + 1) + ' / ' + instructorImages.length;
    
    document.body.style.overflow = 'hidden';
}

/**
 * 이미지 모달 닫기
 */
function closeImageModal() {
    const modal = document.getElementById('imageModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
    
    // 네비게이션 버튼 다시 보이기 (다음에 강의 이미지 갤러리에서 사용할 수 있도록)
    const prevBtn = document.querySelector('.modal-nav-prev');
    const nextBtn = document.querySelector('.modal-nav-next');
    const counter = document.getElementById('imageCounter');
    if (prevBtn) prevBtn.style.display = 'block';
    if (nextBtn) nextBtn.style.display = 'block';
    if (counter) counter.style.display = 'block';
    
    currentGalleryType = 'lecture'; // 기본값으로 리셋
}

/**
 * 이미지 변경 (이전/다음) - 갤러리 타입별 분리
 */
function changeImage(direction) {
    // 단일 강사 이미지인 경우 네비게이션 불가
    if (currentGalleryType === 'instructor-single') return;
    
    const currentImages = currentGalleryType === 'instructor' ? instructorImages : lectureImages;
    
    if (currentImages.length === 0) return;
    
    currentImageIndex += direction;
    
    if (currentImageIndex >= currentImages.length) {
        currentImageIndex = 0;
    } else if (currentImageIndex < 0) {
        currentImageIndex = currentImages.length - 1;
    }
    
    const modalImg = document.getElementById('modalImage');
    const counter = document.getElementById('imageCounter');
    
    modalImg.src = currentImages[currentImageIndex].url;
    
    if (currentGalleryType === 'instructor') {
        counter.textContent = '강사 이미지 ' + (currentImageIndex + 1) + ' / ' + currentImages.length;
    } else {
        counter.textContent = (currentImageIndex + 1) + ' / ' + currentImages.length;
    }
}

// 모달 외부 클릭 시 닫기 (오류 방지)
document.addEventListener('DOMContentLoaded', function() {
    const imageModal = document.getElementById('imageModal');
    if (imageModal) {
        imageModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeImageModal();
            }
        });
    }
});

// 키보드 이벤트 수정 (ESC는 이미지 모달 우선, 그 다음 뒤로가기)
document.addEventListener('keydown', function(e) {
    const imageModal = document.getElementById('imageModal');
    
    if (imageModal && imageModal.style.display === 'block') {
        // 이미지 모달이 열려있을 때
        if (e.key === 'Escape') {
            closeImageModal();
        } else if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
            // 단일 강사 이미지가 아닌 경우에만 키보드 네비게이션 허용
            if (currentGalleryType !== 'instructor-single') {
                if (e.key === 'ArrowLeft') {
                    changeImage(-1);
                } else if (e.key === 'ArrowRight') {
                    changeImage(1);
                }
            }
        }
    } else {
        // 이미지 모달이 없거나 닫혀있을 때
        if (e.key === 'Escape') {
            window.history.back();
        }
    }
});

/**
 * 공유하기 기능
 */
// 작성자와 채팅 시작
function startChatWithAuthor(authorId, authorName) {
    if (!authorId) {
        Toast.error('작성자 정보를 찾을 수 없습니다.');
        return;
    }
    
    // 채팅 페이지로 이동하면서 해당 사용자와 채팅 시작
    window.location.href = '/chat#user-' + authorId;
}

function shareContent() {
    try {
        const lectureTitle = <?= json_encode($lecture['title']) ?>;
        const lectureUrl = window.location.href;
        const lectureDescription = <?php
            $description = '';
            if (isset($lecture['description']) && trim($lecture['description']) !== '') {
                $description = substr(strip_tags($lecture['description']), 0, 100) . '...';
            } else {
                $description = (isset($lecture['title']) ? $lecture['title'] . ' 강의에 참여해보세요!' : '탑마케팅 강의에 참여해보세요!');
            }
            
            // UTF-8 검증 및 정리
            if (!mb_check_encoding($description, 'UTF-8')) {
                $description = mb_convert_encoding($description, 'UTF-8', 'auto');
            }
            
            $jsonResult = json_encode($description, JSON_UNESCAPED_UNICODE);
            if ($jsonResult === false) {
                // JSON 인코딩 실패 시 안전한 기본값 사용
                echo '"강의에 참여해보세요!"';
            } else {
                echo $jsonResult;
            }
        ?>;
        
        // Web Share API 지원 확인
        if (navigator.share) {
            navigator.share({
                title: lectureTitle,
                text: lectureDescription,
                url: lectureUrl
            }).then(() => {
                console.log('공유 성공');
            }).catch((error) => {
                console.log('공유 실패:', error);
                fallbackShare(lectureTitle, lectureUrl);
            });
        } else {
            // 폴백: 클립보드 복사 또는 공유 옵션 표시
            fallbackShare(lectureTitle, lectureUrl);
        }
    } catch (error) {
        console.error('공유 기능 오류:', error);
        Toast.error('공유 기능에 오류가 발생했습니다.');
    }
}

/**
 * 폴백 공유 기능 (클립보드 복사)
 */
function fallbackShare(title, url) {
    // 클립보드에 URL 복사
    if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(() => {
            Toast.success('🔗 링크가 클립보드에 복사되었습니다!\n다른 곳에 붙여넣기하여 공유하세요.');
        }).catch(() => {
            showShareModal(title, url);
        });
    } else {
        showShareModal(title, url);
    }
}

/**
 * 공유 모달 표시
 */
function showShareModal(title, url) {
    const modal = document.createElement('div');
    modal.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; z-index: 1000;';
    
    const content = document.createElement('div');
    content.style.cssText = 'background: white; padding: 30px; border-radius: 12px; max-width: 500px; width: 90%; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.3);';
    
    content.innerHTML = 
        '<h3 style="margin-bottom: 20px; color: #2d3748;">🔗 강의 공유하기</h3>' +
        '<p style="margin-bottom: 20px; color: #4a5568;">' + title + '</p>' +
        '<div style="background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 20px; word-break: break-all; font-family: monospace; font-size: 14px;">' + url + '</div>' +
        '<div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">' +
            '<button onclick="copyToClipboard(\'' + url + '\')" style="padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 6px; cursor: pointer;">📋 복사하기</button>' +
            '<a href="https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url) + '" target="_blank" style="padding: 10px 20px; background: #4267B2; color: white; text-decoration: none; border-radius: 6px;">📘 Facebook</a>' +
            '<a href="https://twitter.com/intent/tweet?text=' + encodeURIComponent(title) + '&url=' + encodeURIComponent(url) + '" target="_blank" style="padding: 10px 20px; background: #1DA1F2; color: white; text-decoration: none; border-radius: 6px;">🐦 Twitter</a>' +
            '<button onclick="this.parentElement.parentElement.parentElement.remove()" style="padding: 10px 20px; background: #a0aec0; color: white; border: none; border-radius: 6px; cursor: pointer;">닫기</button>' +
        '</div>';
    
    modal.appendChild(content);
    document.body.appendChild(modal);
    
    // 모달 외부 클릭 시 닫기
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.remove();
        }
    });
}

/**
 * 클립보드 복사
 */
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            Toast.success('✅ 링크가 복사되었습니다!');
        });
    } else {
        // 폴백 방법
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        Toast.success('✅ 링크가 복사되었습니다!');
    }
}

/**
 * 강사 이미지 모달 열기 (단일 이미지)
 */
function openInstructorImageModal(imageSrc, imageAlt) {
    currentGalleryType = 'instructor-single'; // 특별한 타입으로 설정
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    
    if (modal && modalImg) {
        modal.style.display = 'block';
        modalImg.src = imageSrc;
        modalImg.alt = imageAlt || '강사 프로필 이미지';
        
        // 카운터 숨기기 (단일 이미지이므로)
        const counter = document.getElementById('imageCounter');
        if (counter) {
            counter.style.display = 'none';
        }
        
        // 네비게이션 버튼 숨기기
        const prevBtn = document.querySelector('.modal-nav-prev');
        const nextBtn = document.querySelector('.modal-nav-next');
        if (prevBtn) prevBtn.style.display = 'none';
        if (nextBtn) nextBtn.style.display = 'none';
        
        document.body.style.overflow = 'hidden';
    }
}

// 기존 프로필 이미지 모달 JavaScript 함수들 제거됨 - profile-modal.js 통합 시스템 사용

// 상태 메시지 표시 함수 (함수 호출 전에 정의)
function showLectureStatusMessage(statusClass, iconClass, title, description) {
    console.log('🎯 showLectureStatusMessage 호출됨');
    console.log('📊 파라미터:', { statusClass, iconClass, title, description });
    
    const statusMessage = document.getElementById('lecture-status-message');
    const statusTitle = document.getElementById('lecture-status-title');
    const statusDescription = document.getElementById('lecture-status-description');
    const statusIcon = statusMessage?.querySelector('.status-icon i');
    
    console.log('🔍 showLectureStatusMessage DOM 요소:');
    console.log('- statusMessage:', statusMessage);
    console.log('- statusTitle:', statusTitle);
    console.log('- statusDescription:', statusDescription);
    console.log('- statusIcon:', statusIcon);
    
    if (!statusMessage || !statusTitle || !statusDescription || !statusIcon) {
        console.error('❌ showLectureStatusMessage: 필수 DOM 요소 누락!');
        return;
    }
    
    console.log('🎨 스타일 적용 시작...');
    statusMessage.className = 'lecture-status-message ' + statusClass;
    statusMessage.style.display = 'block';
    statusIcon.className = 'fas ' + iconClass;
    statusTitle.textContent = title;
    statusDescription.textContent = description;
    
    console.log('✅ 스타일 적용 완료:');
    console.log('- className:', statusMessage.className);
    console.log('- display:', statusMessage.style.display);
    console.log('- 최종 표시 여부:', getComputedStyle(statusMessage).display);
    console.log('- 위치 정보:', statusMessage.getBoundingClientRect());
}

// 상태 메시지 숨김 함수
function hideLectureStatusMessage() {
    const statusMessage = document.getElementById('lecture-status-message');
    if (statusMessage) {
        statusMessage.style.display = 'none';
    }
}

/**
 * 강의 신청 시스템
 */

// 페이지 로드 시 신청 상태 확인
document.addEventListener('DOMContentLoaded', function() {
    console.log('📄 페이지 로드 완료');
    console.log('👤 로그인 상태:', <?= $isLoggedIn ? 'true' : 'false' ?>);
    console.log('✏️ 편집 권한:', <?= $canEdit ? 'true' : 'false' ?>);
    
    // 모든 경우에 정적 버튼 정리 먼저 실행
    const staticButtons = document.querySelectorAll('.btn-register');
    console.log('🧹 정적 버튼 정리:', staticButtons.length + '개 발견');
    
    // 로그인된 사용자에게만 API 호출
    const isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;
    if (isLoggedIn) {
        console.log('✅ 로그인 사용자 - 신청 상태 확인 API 호출');
        checkRegistrationStatus();
    } else {
        console.log('🔑 비로그인 사용자 - API 호출 생략');
    }
});

// 신청 상태 확인
async function checkRegistrationStatus() {
    try {
        console.log('🔍 신청 상태 확인 시작...');
        
        const response = await fetch('/api/lectures/<?= $lecture["id"] ?>/registration-status', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        console.log('📡 API 응답 상태:', response.status);
        
        const result = await response.json();
        console.log('📋 신청 상태 데이터:', result);
        console.log('📋 응답 데이터 구조:', JSON.stringify(result, null, 2));
        
        // API 응답 구조 수정: result.data가 실제 데이터
        if (result.status === 'success' && result.data) {
            console.log('✅ API 성공 응답, data 사용');
            updateRegistrationUI(result.data);
        } else {
            console.log('ℹ️ API 응답 오류 또는 비로그인 상태:', result);
            // 비로그인 사용자는 메시지 표시하지 않음
        }
    } catch (error) {
        console.log('ℹ️ 신청 상태 확인 실패 (정상):', error.message);
        // 비로그인 사용자는 메시지 표시하지 않음
    }
}

// 신청 UI 업데이트
function updateRegistrationUI(data) {
    console.log('🔄 updateRegistrationUI 호출됨');
    console.log('📦 전체 data:', data);
    console.log('📦 data.lecture_info:', data.lecture_info);
    console.log('📦 data.registration:', data.registration);
    
    const actionsContainer = document.getElementById('registration-actions');
    if (!actionsContainer) return;
    
    const lecture = <?= json_encode($lecture) ?>;
    const now = new Date();
    const startDate = new Date(lecture.start_date + ' ' + lecture.start_time);
    
    // 강의가 이미 시작되었는지 확인
    const isLectureStarted = now >= startDate;
    
    if (data.registration) {
        // 이미 신청한 경우
        console.log('👤 이미 신청한 사용자');
        const registration = data.registration;
        updateRegistrationStatusUI(registration, isLectureStarted);
    } else {
        // 신청하지 않은 경우
        console.log('🆕 신청하지 않은 사용자');
        console.log('🔗 data.lecture_info를 showRegistrationButton에 전달');
        showRegistrationButton(data.lecture_info, isLectureStarted);
    }
}

// 신청 상태별 UI 표시
function updateRegistrationStatusUI(registration, isLectureStarted) {
    // 디버그 모드 확인
    const debugContainer = document.getElementById('registration-actions-debug');
    const actionsContainer = debugContainer || document.getElementById('registration-actions');
    const status = registration.status;
    
    // 기존 정적 버튼들도 숨기기
    const staticButtons = document.querySelectorAll('.btn-register');
    staticButtons.forEach(btn => {
        btn.style.display = 'none';
    });
    
    // 상태 메시지 표시
    updateLectureStatusMessage(registration);
    
    // sidebar-card 내의 신청 정보 섹션도 업데이트
    const sidebarRegistrationInfo = document.querySelector('.sidebar-card .registration-info');
    if (sidebarRegistrationInfo) {
        // sidebar-card에 동적 버튼 추가
        let sidebarButtonHtml = '';
        // 강의가 시작된 경우 모든 액션 버튼 비활성화
        if (isLectureStarted) {
            sidebarButtonHtml = '<div class="btn-register" style="background: #a0a0a0; cursor: default; color: white;">⏰ 이미 종료된 강의입니다</div>';
        } else {
            switch (status) {
                case 'pending':
                    sidebarButtonHtml = '<div class="btn-register" style="background: #ed8936; cursor: default; margin-bottom: 8px;">⏳ 승인 대기중</div><button class="btn-register" onclick="cancelRegistration()" style="background: #e53e3e; color: white; border: none; cursor: pointer;">❌ 신청 취소</button>';
                    break;
                case 'approved':
                    sidebarButtonHtml = '<div class="btn-register" style="background: #48bb78; cursor: default; margin-bottom: 8px;">✅ 신청 승인됨</div><button class="btn-register" onclick="cancelRegistration()" style="background: #e53e3e; color: white; border: none; cursor: pointer;">❌ 신청 취소</button>';
                    break;
                case 'waiting':
                    sidebarButtonHtml = '<div class="btn-register" style="background: #4299e1; cursor: default; margin-bottom: 8px;">⏰ 대기자 ' + registration.waiting_order + '번</div><button class="btn-register" onclick="cancelRegistration()" style="background: #e53e3e; color: white; border: none; cursor: pointer;">❌ 신청 취소</button>';
                    break;
                case 'rejected':
                    sidebarButtonHtml = '<div class="btn-register" style="background: #e53e3e; cursor: default; margin-bottom: 8px; color: white;">❌ 신청 거절됨</div><button class="btn-register" onclick="showRegistrationModal()" style="background: #48bb78; color: white; border: none; cursor: pointer;">🔄 다시 신청하기</button>';
                    break;
                case 'cancelled':
                    sidebarButtonHtml = '<button class="btn-register" onclick="showRegistrationModal()" style="background: #48bb78; color: white; border: none; cursor: pointer;">📝 다시 신청하기</button>';
                    break;
            }
        }
        sidebarRegistrationInfo.innerHTML = sidebarButtonHtml;
    }
    
    let buttonHtml = '';
    let statusText = '';
    let statusClass = '';
    
    // 강의가 시작된 경우 모든 상태에 대해 액션 비활성화
    if (isLectureStarted) {
        statusText = '⏰ 이미 종료된 강의입니다';
        statusClass = 'btn-secondary';
        buttonHtml = '<button class="btn ' + statusClass + '" disabled>' + statusText + '</button>';
    } else {
        switch (status) {
            case 'pending':
                statusText = '⏳ 승인 대기중';
                statusClass = 'btn-warning';
                buttonHtml = 
                    '<button class="btn ' + statusClass + '" disabled>' +
                        statusText +
                    '</button>' +
                    '<button class="btn btn-outline" onclick="cancelRegistration()">' +
                        '❌ 신청 취소' +
                    '</button>';
                break;
                
            case 'approved':
                statusText = '✅ 신청 승인됨';
                statusClass = 'btn-success';
                buttonHtml = 
                    '<button class="btn ' + statusClass + '" disabled>' +
                        statusText +
                    '</button>' +
                    '<button class="btn btn-outline" onclick="cancelRegistration()">' +
                        '❌ 신청 취소' +
                    '</button>';
                break;
                
            case 'rejected':
                statusText = '❌ 신청 거절됨';
                statusClass = 'btn-danger';
                buttonHtml = 
                    '<button class="btn ' + statusClass + '" disabled>' +
                        statusText +
                    '</button>' +
                    '<button class="btn btn-primary" onclick="showRegistrationModal()">' +
                        '🔄 다시 신청하기' +
                    '</button>';
                break;
            
            case 'cancelled':
                statusText = '⭕ 신청 취소됨';
                statusClass = 'btn-secondary';
                buttonHtml = `
                    <button class="btn btn-primary" onclick="showRegistrationModal()">
                        🚀 다시 신청하기
                    </button>
                `;
                break;
                
            case 'waiting':
                statusText = '⏰ 대기순번 ' + registration.waiting_order + '번';
                statusClass = 'btn-info';
                buttonHtml = '<button class="btn ' + statusClass + '" disabled>' + statusText + '</button>' + '<button class="btn btn-outline" onclick="cancelRegistration()">❌ 대기 취소</button>';
                break;
        }
    }
    
    actionsContainer.innerHTML = buttonHtml;
}

// 신청 버튼 표시
function showRegistrationButton(lectureInfo, isLectureStarted) {
    console.log('🎯 showRegistrationButton 호출됨');
    console.log('📋 lectureInfo:', lectureInfo);
    console.log('📋 lectureInfo 타입:', typeof lectureInfo);
    console.log('📋 lectureInfo.registration_end_date:', lectureInfo?.registration_end_date);
    console.log('📋 lectureInfo의 모든 키:', lectureInfo ? Object.keys(lectureInfo) : 'null');
    console.log('⏰ isLectureStarted:', isLectureStarted);
    
    // 디버그 모드 확인
    const debugContainer = document.getElementById('registration-actions-debug');
    const actionsContainer = debugContainer || document.getElementById('registration-actions');
    if (!actionsContainer) {
        console.error('❌ registration-actions 컨테이너를 찾을 수 없습니다');
        return;
    }
    
    // 비로그인 사용자인 경우 로그인 버튼만 표시
    const isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;
    if (!isLoggedIn) {
        // DOM 요소 직접 생성하여 안전하게 처리
        const loginLink = document.createElement('a');
        loginLink.href = '/auth/login?return_to=' + encodeURIComponent(window.location.pathname + window.location.search);
        loginLink.className = 'btn btn-primary';
        loginLink.textContent = '🔑 로그인 후 신청하기';
        
        actionsContainer.innerHTML = '';
        actionsContainer.appendChild(loginLink);
        
        // sidebar-card 내의 신청 버튼도 로그인 버튼으로 교체
        const sidebarRegistrationInfo = document.querySelector('.sidebar-card .registration-info');
        if (sidebarRegistrationInfo) {
            const existingLoginButton = sidebarRegistrationInfo.querySelector('a[href*="auth/login"]');
            if (!existingLoginButton) {
                const sidebarLoginLink = document.createElement('a');
                sidebarLoginLink.href = '/auth/login?redirect=' + encodeURIComponent(window.location.href);
                sidebarLoginLink.className = 'btn-register';
                sidebarLoginLink.textContent = '🔑 로그인 후 신청';
                
                sidebarRegistrationInfo.innerHTML = '';
                sidebarRegistrationInfo.appendChild(sidebarLoginLink);
            }
        }
        return;
    }
    
    // 기존 정적 버튼들도 숨기기
    const staticButtons = document.querySelectorAll('.btn-register');
    staticButtons.forEach(btn => {
        btn.style.display = 'none';
    });
    
    // lectureInfo 유효성 검사
    if (!lectureInfo || typeof lectureInfo !== 'object') {
        console.error('❌ lectureInfo가 유효하지 않습니다:', lectureInfo);
        showDefaultRegistrationButton();
        return;
    }
    
    if (isLectureStarted) {
        // 메인 액션 컨테이너 처리
        actionsContainer.innerHTML = '<button class="btn btn-secondary" disabled>⏰ 이미 종료된 강의입니다</button>';
        
        // sidebar-card 내의 신청 버튼도 비활성화
        const sidebarRegistrationInfo = document.querySelector('.sidebar-card .registration-info');
        if (sidebarRegistrationInfo) {
            sidebarRegistrationInfo.innerHTML = '<button class="btn-register" disabled style="background: #a0a0a0; color: white; border: none; cursor: not-allowed;">⏰ 이미 종료된 강의입니다</button>';
        }
        return;
    }
    
    // sidebar-card 내의 신청 정보 섹션도 업데이트 (신청 안한 상태)
    const sidebarRegistrationInfo = document.querySelector('.sidebar-card .registration-info');
    if (sidebarRegistrationInfo) {
        sidebarRegistrationInfo.innerHTML = `
            <button class="btn-register" onclick="showRegistrationModal()" style="background: #48bb78; color: white; border: none; cursor: pointer;">
                📝 지금 신청하기
            </button>
        `;
    }
    
    // 신청 마감일 확인
    if (lectureInfo.registration_end_date) {
        const registrationEndDate = new Date(lectureInfo.registration_end_date);
        const now = new Date();
        
        if (now > registrationEndDate) {
            actionsContainer.innerHTML = '<button class="btn btn-secondary" disabled>📅 신청 마감되었습니다</button>';
            return;
        }
    }
    
    // 정원 확인
    if (lectureInfo.max_participants && lectureInfo.current_participants >= lectureInfo.max_participants) {
        if (lectureInfo.allow_waiting_list) {
            actionsContainer.innerHTML = '<button class="btn btn-warning" onclick="showWaitingListModal()">⏰ 대기자로 신청하기</button>';
        } else {
            actionsContainer.innerHTML = '<button class="btn btn-secondary" disabled>👥 정원이 마감되었습니다</button>';
        }
        return;
    }
    
    // 일반 신청 버튼
    actionsContainer.innerHTML = '<button class="btn btn-primary" onclick="showRegistrationModal()">🚀 지금 신청하기</button>';
}

// 기본 신청 버튼 표시 (오류 시)
function showDefaultRegistrationButton() {
    console.log('🔄 기본 신청 버튼 표시 중...');
    
    // 편집 권한이 있는 사용자는 신청 버튼이 필요하지 않음
    const canEdit = <?= $canEdit ? 'true' : 'false' ?>;
    if (canEdit) {
        console.log('ℹ️ 편집 권한이 있는 사용자이므로 신청 버튼을 표시하지 않습니다');
        return;
    }
    
    // 디버그 모드 확인
    const debugContainer = document.getElementById('registration-actions-debug');
    const actionsContainer = debugContainer || document.getElementById('registration-actions');
    if (!actionsContainer) {
        console.error('❌ registration-actions 컨테이너를 찾을 수 없습니다');
        console.log('🔍 로그인 상태:', <?= $isLoggedIn ? 'true' : 'false' ?>);
        console.log('✏️ 편집 권한:', canEdit);
        return;
    }
    
    actionsContainer.innerHTML = '<button class="btn btn-primary" onclick="showRegistrationModal()">🚀 지금 신청하기</button>';
    
    console.log('✅ 기본 신청 버튼 표시 완료');
}

// 신청 모달 표시
function showRegistrationModal() {
    console.log('🚀 showRegistrationModal() 호출됨');
    
    const modal = document.getElementById('registrationModal');
    if (!modal) {
        console.error('❌ 신청 모달 요소를 찾을 수 없습니다');
        Toast.error('신청 모달을 로드할 수 없습니다. 페이지를 새로고침해주세요.');
        return;
    }
    
    console.log('✅ 모달 요소 발견, 표시 중...');
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    // 폼 초기화
    resetRegistrationForm();
    
    // 글자 수 카운터 초기화
    initCharacterCounters();
    
    // 사용자 정보 자동 입력 (비동기, 오류가 있어도 모달은 표시)
    loadUserInfo().catch(error => {
        console.warn('⚠️ 사용자 정보 로드 실패:', error);
    });
    
    console.log('✅ 모달 표시 완료');
}

// 신청 모달 닫기
function closeRegistrationModal() {
    const modal = document.getElementById('registrationModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
        
        // 로딩 상태 해제
        const submitButton = document.getElementById('submitRegistrationBtn');
        if (submitButton) {
            submitButton.innerHTML = '🚀 신청하기';
            submitButton.disabled = false;
        }
        
        // 폼 초기화
        resetRegistrationForm();
    }
}

// 신청 폼 초기화
function resetRegistrationForm() {
    const form = document.getElementById('registrationForm');
    if (form) {
        form.reset();
        
        // 에러 메시지 제거
        const errorElements = form.querySelectorAll('.error-message');
        errorElements.forEach(el => el.remove());
        
        // 입력 필드 스타일 초기화
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.classList.remove('error');
        });
    }
}

// 사용자 정보 자동 입력
async function loadUserInfo() {
    console.log('📝 사용자 정보 및 이전 신청 내역 로드 시작...');
    
    try {
        // 사용자 기본 정보 로드
        const userResponse = await fetch('/auth/me', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        let userInfo = null;
        if (userResponse.ok) {
            const userData = await userResponse.json();
            userInfo = userData.user;
            console.log('👤 로드된 사용자 정보 전체:', JSON.stringify(userInfo, null, 2));
        } else {
            console.log('❌ 사용자 정보 로드 실패:', userResponse.status, userResponse.statusText);
        }
        
        // 이전 신청 내역 로드 (취소된 것 포함)
        const registrationResponse = await fetch('/api/lectures/<?= $lecture["id"] ?>/previous-registration', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        let previousRegistration = null;
        if (registrationResponse.ok) {
            const regData = await registrationResponse.json();
            if (regData.status === 'success' && regData.data) {
                previousRegistration = regData.data;
                console.log('📋 이전 신청 내역 발견:', previousRegistration);
            }
        }
        
        // 폼 필드 자동 채우기
        fillRegistrationForm(userInfo, previousRegistration);
        
    } catch (error) {
        console.error('정보 로드 오류:', error);
    }
}

// 신청 폼 자동 채우기
function fillRegistrationForm(userInfo, previousRegistration) {
    console.log('📝 폼 자동 채우기 시작...');
    
    // 폼 요소들 가져오기
    const participantName = document.getElementById('participant_name');
    const participantEmail = document.getElementById('participant_email');
    const participantPhone = document.getElementById('participant_phone');
    const companyName = document.getElementById('company_name');
    const position = document.getElementById('position');
    const motivation = document.getElementById('motivation');
    const howDidYouKnow = document.getElementById('how_did_you_know');
    const specialRequests = document.getElementById('special_requests');
    
    
    // 1단계: 사용자 계정 기본 정보로 채우기
    if (userInfo) {
        console.log('✅ 사용자 기본 정보로 채우기:', userInfo.nickname);
        console.log('📧 사용자 이메일 데이터:', userInfo.email);
        console.log('📱 사용자 전화번호 데이터:', userInfo.phone);
        console.log('🔍 이메일 필드 요소:', participantEmail);
        
        if (participantName && userInfo.nickname) {
            participantName.value = userInfo.nickname;
            console.log('✅ 이름 자동 입력 완료:', userInfo.nickname);
        }
        if (participantEmail && userInfo.email) {
            participantEmail.value = userInfo.email;
            console.log('✅ 이메일 자동 입력 완료:', userInfo.email);
        } else {
            console.log('❌ 이메일 자동 입력 실패 - participantEmail:', !!participantEmail, 'userInfo.email:', userInfo.email);
        }
        if (participantPhone && userInfo.phone) {
            participantPhone.value = userInfo.phone;
            console.log('✅ 전화번호 자동 입력 완료:', userInfo.phone);
        }
    }
    
    // 2단계: 이전 신청 내역으로 덮어쓰기 (더 상세한 정보)
    if (previousRegistration) {
        console.log('📋 이전 신청 내역으로 폼 자동 채우기');
        
        if (previousRegistration.participant_name && participantName) {
            participantName.value = previousRegistration.participant_name;
        }
        if (previousRegistration.participant_email && participantEmail) {
            participantEmail.value = previousRegistration.participant_email;
        }
        if (previousRegistration.participant_phone && participantPhone) {
            participantPhone.value = previousRegistration.participant_phone;
        }
        if (previousRegistration.company_name && companyName) {
            companyName.value = previousRegistration.company_name;
        }
        if (previousRegistration.position && position) {
            position.value = previousRegistration.position;
        }
        if (previousRegistration.motivation && motivation) {
            motivation.value = previousRegistration.motivation;
        }
        if (previousRegistration.how_did_you_know && howDidYouKnow) {
            howDidYouKnow.value = previousRegistration.how_did_you_know;
        }
        if (previousRegistration.special_requests && specialRequests) {
            specialRequests.value = previousRegistration.special_requests;
        }
        
        console.log('✅ 이전 신청 내역으로 폼 자동 채우기 완료');
    }
}

// 신청 폼 제출
async function submitRegistration() {
    const form = document.getElementById('registrationForm');
    const submitButton = document.getElementById('submitRegistrationBtn');
    
    if (!form || !submitButton) return;
    
    // 클라이언트 사이드 검증
    if (!validateRegistrationForm()) {
        return;
    }
    
    // 버튼 비활성화
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '🔄 신청 중...';
    submitButton.disabled = true;
    
    try {
        // 폼 데이터 수집
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        
        // 데이터 정리
        Object.keys(data).forEach(key => {
            if (typeof data[key] === 'string') {
                data[key] = data[key].trim();
            }
        });
        
        // CSRF 토큰 추가
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        data.csrf_token = csrfToken;
        
        // 신청 요청
        const response = await fetch('/api/lectures/<?= $lecture["id"] ?>/registration', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            Toast.success('✅ ' + result.message);
            closeRegistrationModal();
            checkRegistrationStatus(); // 상태 새로고침
        } else {
            // 에러 메시지 표시
            showFormErrors(result.errors || { general: result.message });
        }
        
    } catch (error) {
        console.error('신청 제출 오류:', error);
        Toast.error('❌ 신청 처리 중 오류가 발생했습니다.');
    } finally {
        // 버튼 복구
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    }
}

// 신청 폼 클라이언트 사이드 검증
function validateRegistrationForm() {
    const errors = {};
    
    // 필수 필드 검증
    const participantName = document.getElementById('participant_name').value.trim();
    const participantEmail = document.getElementById('participant_email').value.trim();
    const participantPhone = document.getElementById('participant_phone').value.trim();
    
    if (!participantName) {
        errors.participant_name = '이름을 입력해주세요.';
    } else if (participantName.length < 2) {
        errors.participant_name = '이름은 2글자 이상 입력해주세요.';
    }
    
    if (!participantEmail) {
        errors.participant_email = '이메일을 입력해주세요.';
    } else if (!isValidEmail(participantEmail)) {
        errors.participant_email = '올바른 이메일 형식을 입력해주세요.';
    }
    
    if (!participantPhone) {
        errors.participant_phone = '연락처를 입력해주세요.';
    } else if (!isValidPhone(participantPhone)) {
        errors.participant_phone = '올바른 연락처 형식을 입력해주세요. (예: 010-1234-5678)';
    }
    
    // 에러가 있으면 표시하고 false 반환
    if (Object.keys(errors).length > 0) {
        showFormErrors(errors);
        return false;
    }
    
    // 기존 에러 메시지 제거
    clearFormErrors();
    return true;
}

// 이메일 유효성 검사
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// 전화번호 유효성 검사
function isValidPhone(phone) {
    // 한국 휴대폰 번호 형식 (010-1234-5678, 01012345678 등)
    const phoneRegex = /^(010|011|016|017|018|019)[-]?\d{3,4}[-]?\d{4}$/;
    return phoneRegex.test(phone.replace(/\s/g, ''));
}

// 폼 에러 메시지 제거
function clearFormErrors() {
    const existingErrors = document.querySelectorAll('.error-message');
    existingErrors.forEach(el => el.remove());
    
    const inputs = document.querySelectorAll('#registrationForm input, #registrationForm textarea, #registrationForm select');
    inputs.forEach(input => input.classList.remove('error'));
}

// 폼 에러 표시
function showFormErrors(errors) {
    // 기존 에러 메시지 제거
    const existingErrors = document.querySelectorAll('.error-message');
    existingErrors.forEach(el => el.remove());
    
    // 입력 필드 스타일 초기화
    const inputs = document.querySelectorAll('#registrationForm input, #registrationForm textarea, #registrationForm select');
    inputs.forEach(input => input.classList.remove('error'));
    
    // 새로운 에러 메시지 표시
    for (const [field, message] of Object.entries(errors)) {
        if (field === 'general') {
            // 일반 에러는 폼 상단에 표시
            const form = document.getElementById('registrationForm');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message general-error';
            errorDiv.textContent = message;
            form.insertBefore(errorDiv, form.firstChild);
        } else {
            // 필드별 에러는 해당 필드 아래에 표시
            const input = document.getElementById(field);
            if (input) {
                input.classList.add('error');
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message field-error';
                errorDiv.textContent = message;
                input.parentNode.insertBefore(errorDiv, input.nextSibling);
            }
        }
    }
}

// 대기자 신청 모달
async function showWaitingListModal() {
    if (await Modal.confirm('정원이 마감되어 대기자로 신청됩니다.\n\n대기자로 신청하시겠습니까?')) {
        showRegistrationModal();
    }
}

// 신청 취소
async function cancelRegistration() {
    if (!(await Modal.confirm('정말로 신청을 취소하시겠습니까?', { type: 'danger', confirmText: '취소', cancelText: '아니오' }))) {
        return;
    }
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        const response = await fetch('/api/lectures/<?= $lecture["id"] ?>/registration', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                csrf_token: csrfToken
            })
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            Toast.success('✅ 신청이 취소되었습니다.');
            checkRegistrationStatus(); // 상태 새로고침
        } else {
            Toast.error('❌ 신청 취소에 실패했습니다.

' + (data.message || '알 수 없는 오류'));
        }
    } catch (error) {
        console.error('신청 취소 오류:', error);
        Toast.error('❌ 신청 취소 중 오류가 발생했습니다.');
    }
}

/**
 * 강의 삭제 확인 및 실행
 */
function confirmDeleteLecture(lectureId) {
    if (!lectureId) {
        Toast.info('잘못된 강의 ID입니다.');
        return;
    }

    // 삭제 확인
    const confirmed = await Modal.confirm('⚠️ 정말로 이 강의를 삭제하시겠습니까?\n\n삭제된 강의는 복구할 수 없습니다.', { type: 'danger', title: '강의 삭제', confirmText: '삭제', cancelText: '취소' });
    
    if (!confirmed) {
        return;
    }

    // 두 번째 확인
    const doubleConfirmed = await Modal.confirm('⚠️ 마지막 확인입니다!\n\n강의 제목: "<?= htmlspecialchars($lecture["title"]) ?>"\n\n정말로 삭제하시겠습니까?', { type: 'danger', title: '최종 확인', confirmText: '삭제', cancelText: '취소' });
    
    if (!doubleConfirmed) {
        return;
    }

    // 로딩 상태 표시
    const deleteBtn = event.target;
    const originalText = deleteBtn.innerHTML;
    deleteBtn.innerHTML = '🔄 삭제 중...';
    deleteBtn.disabled = true;

    // CSRF 토큰 가져오기
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // 디버깅 정보 출력
    console.log('=== 강의 삭제 디버깅 시작 ===');
    console.log('강의 ID:', lectureId);
    console.log('CSRF 토큰:', csrfToken);
    console.log('요청 URL:', '/lectures/' + lectureId + '/delete');
    console.log('요청 데이터:', {
        csrf_token: csrfToken,
        confirm_delete: true
    });

    // 삭제 요청
    fetch('/lectures/' + lectureId + '/delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            csrf_token: csrfToken,
            confirm_delete: true
        })
    })
    .then(response => {
        console.log('=== 응답 정보 ===');
        console.log('응답 상태:', response.status);
        console.log('응답 상태 텍스트:', response.statusText);
        console.log('응답 헤더:', response.headers);
        console.log('응답 OK 여부:', response.ok);
        
        // 응답이 JSON이 아닐 수 있으므로 텍스트로 먼저 읽어보기
        return response.text().then(text => {
            console.log('응답 원문:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('JSON 파싱 오류:', e);
                throw new Error('서버 응답이 유효한 JSON이 아닙니다: ' + text);
            }
        });
    })
    .then(data => {
        console.log('=== 파싱된 응답 데이터 ===');
        console.log('응답 데이터:', data);
        console.log('응답 상태:', data.status);
        console.log('응답 데이터 객체:', data.data);
        
        // ResponseHelper 형식 처리
        const isSuccess = data.status === 'success' && data.data && data.data.success === true;
        const message = data.data ? data.data.message : (data.message || '알 수 없는 오류');
        
        console.log('최종 성공 여부:', isSuccess);
        console.log('최종 메시지:', message);
        
        if (isSuccess) {
            console.log('✅ 강의 삭제 성공');
            Toast.success('✅ 강의가 성공적으로 삭제되었습니다.');
            // 이전 페이지로 돌아가기 (또는 강의 목록으로)
            if (document.referrer && document.referrer !== window.location.href) {
                window.location.href = document.referrer;
            } else {
                window.location.href = '/lectures';
            }
        } else {
            console.error('❌ 강의 삭제 실패:', data);
            Toast.error('❌ 강의 삭제에 실패했습니다.

오류: ' + message);
            // 버튼 복구
            deleteBtn.innerHTML = originalText;
            deleteBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('=== 강의 삭제 오류 ===');
        console.error('오류 객체:', error);
        console.error('오류 메시지:', error.message);
        console.error('오류 스택:', error.stack);
        Toast.error('❌ 강의 삭제 중 오류가 발생했습니다.

네트워크를 확인하고 다시 시도해주세요.

오류: ' + error.message);
        // 버튼 복구
        deleteBtn.innerHTML = originalText;
        deleteBtn.disabled = false;
    });
}
</script>

<!-- 신청 모달 -->
<div id="registrationModal" class="registration-modal">
    <div class="registration-modal-content">
        <div class="registration-modal-header">
            <h2>🚀 강의 신청하기</h2>
            <button class="registration-modal-close" onclick="confirmCloseRegistrationModal()">&times;</button>
        </div>
        
        <form id="registrationForm" novalidate>
            <div class="registration-modal-body">
                <!-- 기본 정보 섹션 -->
                <div class="form-section">
                    <div class="form-section-title">
                        👤 신청자 정보
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="participant_name" class="required">이름</label>
                            <input type="text" id="participant_name" name="participant_name" required 
                                   placeholder="실명을 입력해주세요">
                        </div>
                        <div class="form-group">
                            <label for="participant_phone" class="required">연락처</label>
                            <input type="tel" id="participant_phone" name="participant_phone" required 
                                   placeholder="010-1234-5678">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="participant_email" class="required">이메일</label>
                        <input type="email" id="participant_email" name="participant_email" required 
                               placeholder="example@email.com">
                    </div>
                </div>
                
                <!-- 소속 정보 섹션 -->
                <div class="form-section">
                    <div class="form-section-title">
                        🏢 소속 정보
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="company_name">회사명/소속</label>
                            <input type="text" id="company_name" name="company_name" 
                                   placeholder="소속 회사나 기관명 (선택사항)">
                        </div>
                        <div class="form-group">
                            <label for="position">직책/직위</label>
                            <input type="text" id="position" name="position" 
                                   placeholder="직책이나 직위 (선택사항)">
                        </div>
                    </div>
                </div>
                
                <!-- 추가 정보 섹션 -->
                <div class="form-section">
                    <div class="form-section-title">
                        📝 추가 정보
                    </div>
                    
                    <div class="form-group">
                        <label for="motivation">참가 동기/목적</label>
                        <textarea id="motivation" name="motivation" maxlength="2000"
                                  placeholder="이 강의에 참가하시는 이유나 기대하시는 점을 간단히 적어주세요 (선택사항)"></textarea>
                        <div class="char-counter">
                            <span id="motivation-counter">0</span>/2,000자
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="how_did_you_know">어떻게 알게 되셨나요?</label>
                        <select id="how_did_you_know" name="how_did_you_know">
                            <option value="">선택해주세요 (선택사항)</option>
                            <option value="website">웹사이트에서</option>
                            <option value="social_media">소셜미디어</option>
                            <option value="friend_referral">지인 추천</option>
                            <option value="company_notice">회사 공지</option>
                            <option value="email">이메일</option>
                            <option value="search_engine">검색엔진</option>
                            <option value="advertisement">광고</option>
                            <option value="other">기타</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="special_requests">특별 요청사항</label>
                        <textarea id="special_requests" name="special_requests" maxlength="2000"
                                  placeholder="식단 제한, 접근성 지원 등 특별한 요청사항이 있으시면 적어주세요 (선택사항)"></textarea>
                        <div class="char-counter">
                            <span id="special-requests-counter">0</span>/2,000자
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="registration-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="confirmCloseRegistrationModal()">
                    취소
                </button>
                <button type="button" id="submitRegistrationBtn" class="btn btn-primary" onclick="submitRegistration()">
                    🚀 신청하기
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 모달 외부 클릭 시 닫기 -->
<script>
document.getElementById('registrationModal').addEventListener('click', function(e) {
    if (e.target === this) {
        confirmCloseRegistrationModal();
    }
});

// 모달 닫기 확인 함수
function confirmCloseRegistrationModal() {
    // 폼에 입력된 내용이 있는지 확인
    const form = document.getElementById('registrationForm');
    if (!form) {
        closeRegistrationModal();
        return;
    }
    
    const inputs = form.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"], textarea, select');
    let hasContent = false;
    
    // 입력된 내용 확인
    inputs.forEach(input => {
        if (input.value && input.value.trim() !== '') {
            hasContent = true;
        }
    });
    
    // 내용이 있으면 확인 다이얼로그 표시
    if (hasContent) {
        const shouldClose = await Modal.confirm(
            '📝 작성 중인 내용이 있습니다.\n' +
            '정말로 창을 닫으시겠습니까?\n\n' +
            '⚠️ 작성한 내용이 모두 삭제됩니다.'
        );
        
        if (shouldClose) {
            closeRegistrationModal();
        }
    } else {
        // 내용이 없으면 바로 닫기
        closeRegistrationModal();
    }
}

// ESC 키로 모달 닫기
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('registrationModal');
        if (modal && modal.style.display === 'block') {
            confirmCloseRegistrationModal();
        }
    }
});

// 강의 신청 상태 메시지 업데이트 함수
function updateLectureStatusMessage(registration) {
    console.log('🚨 updateLectureStatusMessage 호출됨');
    console.log('📋 registration 데이터:', registration);
    
    const statusMessage = document.getElementById('lecture-status-message');
    const statusTitle = document.getElementById('lecture-status-title');
    const statusDescription = document.getElementById('lecture-status-description');
    const statusIcon = statusMessage?.querySelector('.status-icon i');
    
    console.log('🔍 DOM 요소 확인:');
    console.log('- statusMessage:', statusMessage);
    console.log('- statusTitle:', statusTitle);
    console.log('- statusDescription:', statusDescription);
    console.log('- statusIcon:', statusIcon);
    
    if (!statusMessage || !statusTitle || !statusDescription || !statusIcon) {
        console.error('❌ 필수 DOM 요소가 누락됨!');
        return;
    }
    
    // 상태 메시지 초기화
    statusMessage.className = 'lecture-status-message';
    statusMessage.style.display = 'none';
    
    if (!registration) {
        console.log('⚠️ registration이 null이므로 메시지 숨김');
        hideLectureStatusMessage();
        return;
    }
    
    console.log('📊 registration.status:', registration.status);
    console.log('📝 registration.admin_notes:', registration.admin_notes);
    
    switch (registration.status) {
        case 'pending':
            console.log('⏳ pending 상태 처리');
            showLectureStatusMessage('pending', 'fa-clock', '신청 검토 중입니다', 
                '신청이 접수되었습니다. 승인 결과를 기다려주세요.');
            break;
            
        case 'approved':
            console.log('✅ approved 상태 처리');
            const approvedMessage = registration.admin_notes || '신청이 승인되었습니다. 강의에 참석해주세요.';
            showLectureStatusMessage('approved', 'fa-check-circle', '신청이 승인되었습니다', approvedMessage);
            break;
            
        case 'waiting':
            console.log('⏰ waiting 상태 처리');
            showLectureStatusMessage('waiting', 'fa-hourglass-half', '대기열 ' + registration.waiting_order + '번입니다', 
                '정원이 초과되어 대기열에 등록되었습니다. 승인 시 알림을 드리겠습니다.');
            break;
            
        case 'rejected':
            console.log('❌ rejected 상태 처리 시작');
            const rejectedMessage = registration.admin_notes || '신청이 거절되었습니다. 다시 신청하실 수 있습니다.';
            console.log('📝 거절 메시지:', rejectedMessage);
            showLectureStatusMessage('rejected', 'fa-times-circle', '신청이 거절되었습니다', rejectedMessage);
            break;
            
        case 'cancelled':
            console.log('⭕ cancelled 상태 처리');
            hideLectureStatusMessage();
            break;
            
        default:
            console.log('❓ 알 수 없는 상태:', registration.status);
            hideLectureStatusMessage();
    }
}

// btn-register 클릭 이벤트 추가
document.addEventListener('DOMContentLoaded', function() {
    const registerButtons = document.querySelectorAll('.btn-register');
    registerButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            showRegistrationModal();
        });
    });
});

// 🚀 v3.29.0: 글자 수 카운터 기능 (통합 CharacterCounter 클래스 사용)
function initCharacterCounters() {
    // 참가 동기 글자 수 카운터 (2000자, 90%부터 경고)
    new CharacterCounter(
        document.getElementById("motivation"),
        document.getElementById("motivation-counter"),
        2000,
        { errorThreshold: 1.0, warningThreshold: 0.9 }
    );
    
    // 특별 요청사항 글자 수 카운터 (2000자, 90%부터 경고)
    new CharacterCounter(
        document.getElementById("special_requests"),
        document.getElementById("special-requests-counter"),
        2000,
        { errorThreshold: 1.0, warningThreshold: 0.9 }
    );
}
</script>

<!-- edit-check.js 로드 -->
<script src="/assets/js/edit-check.js"></script>

<?php include SRC_PATH . '/views/templates/footer.php'; ?><\!-- Cache Buster: 1756642384 -->
