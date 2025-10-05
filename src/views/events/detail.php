<?php
/**
 * 행사 상세 페이지
 */

// 로그인 상태 확인
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
require_once SRC_PATH . '/helpers/HtmlSanitizerHelper.php';
require_once SRC_PATH . '/helpers/ProfileImageHelper.php';

// 컴포넌트 로드
require_once SRC_PATH . '/components/ui/Button.php';
require_once SRC_PATH . '/components/ui/Modal.php';

$isLoggedIn = AuthMiddleware::isLoggedIn();
$currentUserId = AuthMiddleware::getCurrentUserId();

// 편집 권한 확인 (행사 작성자이거나 관리자인지 확인)
$canEdit = false;
if ($isLoggedIn && isset($event)) {
    $userRole = AuthMiddleware::getUserRole();
    $canEdit = ($userRole === 'ROLE_ADMIN') || ($event['user_id'] == $currentUserId);
}

// CSRF 토큰 생성
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 프로필 이미지 모달 리소스 로드
include SRC_PATH . '/views/components/profile-modal-resources.php';
?>

<!-- 한국어 인코딩 설정 -->
<meta charset="utf-8">

<!-- CSRF 토큰 메타 태그 -->
<meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?>">

<!-- Quill.js 에디터 CSS (리치 텍스트 표시용) -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">

<style>
/* 행사 상세 페이지 스타일 (파란색 테마) */
.event-detail-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 30px 15px;
    min-height: calc(100vh - 200px);
    /* 헤더 겹침 방지: 고정 헤더 높이만큼 상단 여백 추가 */
    margin-top: 80px; /* 데스크톱: 헤더 높이(66px) + 여유 공간(14px) */
    padding-top: 20px;
}

.event-hero {
    background: linear-gradient(135deg, #4A90E2 0%, #2E86AB 100%);
    color: white;
    padding: 60px 30px;
    border-radius: 16px;
    margin-bottom: 40px;
    margin-top: 0px;
    position: relative;
    overflow: hidden;
}

/* 히어로 섹션 상단 관리 버튼 (수정/삭제) */
.event-admin-actions {
    position: absolute;
    top: 20px;
    right: 20px;
    display: flex;
    gap: 10px;
    z-index: 2;
}

/* 기본 버튼 스타일 */
.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    min-height: 48px;
    min-width: 48px;
    justify-content: center;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* 수정 버튼 스타일 */
.btn-edit {
    background: rgba(255, 255, 255, 0.15);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(8px);
}

.btn-edit:hover {
    background: rgba(255, 255, 255, 0.25);
    border-color: rgba(255, 255, 255, 0.5);
    color: white;
    text-decoration: none;
}

/* 삭제 버튼 스타일 */
.btn-danger {
    background: rgba(229, 62, 62, 0.9);
    color: white;
    border: 1px solid rgba(197, 48, 48, 0.8);
    backdrop-filter: blur(8px);
}

.btn-danger:hover {
    background: rgba(197, 48, 48, 0.95);
    border-color: rgba(197, 48, 48, 1);
    color: white;
    text-decoration: none;
}

.event-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="white" opacity="0.1"/><circle cx="80" cy="80" r="3" fill="white" opacity="0.1"/><circle cx="40" cy="60" r="1" fill="white" opacity="0.1"/></svg>');
    pointer-events: none;
}

.event-hero-content {
    position: relative;
    z-index: 1;
    max-width: 800px;
    margin: 0 auto;
    text-align: center;
}

.event-category {
    display: inline-block;
    background: rgba(255, 255, 255, 0.2);
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 16px;
    margin-bottom: 20px;
    backdrop-filter: blur(10px);
}

.event-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 20px;
    line-height: 1.2;
}

.event-subtitle {
    font-size: 1.1rem;
    opacity: 0.9;
    margin-bottom: 30px;
}

.event-meta-row {
    display: flex;
    justify-content: center;
    gap: 30px;
    flex-wrap: wrap;
}

.event-meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 1rem;
}

.event-meta-item i {
    font-size: 1.1rem;
    opacity: 0.8;
}


.event-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 40px;
    margin-bottom: 40px;
}

.event-main {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    overflow: hidden;
}

.event-sidebar {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.content-section {
    padding: 30px;
}

.content-section h2 {
    color: #1e293b;
    font-size: 1.5rem;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e2e8f0;
}

.event-description {
    color: #64748b;
    line-height: 1.7;
    font-size: 1rem;
}

/* 행사 내용 내 이미지 크기 제한 */
.event-description img {
    max-width: 100% !important;
    height: auto !important;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    margin: 10px 0;
}

/* Quill 에디터 이미지 크기 제한 */
.ql-editor img {
    max-width: 100% !important;
    height: auto !important;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    margin: 10px 0;
}

/* 강사/연사 정보 스타일 */
.instructors-card {
    border-left: 3px solid #4A90E2;
}

.instructors-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.instructor-item {
    padding: 15px;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

.instructor-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 10px;
}

.instructor-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    overflow: hidden;
    position: relative;
    flex-shrink: 0;
}

.instructor-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.instructor-fallback {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #4A90E2 0%, #2E86AB 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 1.2rem;
}

.instructor-details {
    flex: 1;
}

.instructor-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 4px;
}

.instructor-title {
    font-size: 16px;
    color: #4A90E2;
    font-weight: 500;
}

.instructor-bio {
    color: #64748b;
    line-height: 1.5;
    font-size: 16px;
}

.info-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    padding: 25px;
}

.info-card h3 {
    color: #1e293b;
    font-size: 1.2rem;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.info-card h3 i {
    color: #4A90E2;
}

.info-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.info-list li {
    padding: 10px 0;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.info-list li:last-child {
    border-bottom: none;
}

.info-label {
    color: #64748b;
    font-size: 16px;
}

.info-value {
    color: #1e293b;
    font-weight: 500;
    text-align: right;
}

.register-card {
    background: linear-gradient(135deg, #4A90E2 0%, #2E86AB 100%);
    color: white;
    text-align: center;
}

.register-card h3 {
    color: white;
    margin-bottom: 15px;
}

.event-fee {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 20px;
}

.register-btn {
    background: white;
    color: #4A90E2;
    border: none;
    padding: 15px 30px;
    border-radius: 25px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    width: 100%;
    font-size: 1.1rem;
    min-height: 48px;
    padding: 12px 24px;
}

.register-btn:hover {
    background: #f8fafc;
    transform: translateY(-2px);
}

.register-btn:disabled {
    background: #cbd5e0;
    color: #9ca3af;
    cursor: not-allowed;
    transform: none;
}

/* 행사 신청 상태 메시지 스타일 */
.event-status-message {
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 15px;
    border: 1px solid;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    animation: fadeIn 0.3s ease-in-out;
}

.event-status-message .status-content {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.event-status-message .status-icon {
    font-size: 20px;
    margin-top: 2px;
    width: 24px;
    text-align: center;
}

.event-status-message .status-text {
    flex: 1;
}

.event-status-message .status-title {
    font-weight: 600;
    font-size: 16px;
    margin-bottom: 4px;
}

.event-status-message .status-description {
    font-size: 16px;
    opacity: 0.9;
    line-height: 1.4;
}

/* 승인 상태 스타일 */
.event-status-message.approved {
    background: #f0f9ff;
    border-color: #0ea5e9;
    color: #0c4a6e;
}

.event-status-message.approved .status-icon {
    color: #0ea5e9;
}

/* 거절 상태 스타일 */
.event-status-message.rejected {
    background: #fef2f2;
    border-color: #ef4444;
    color: #991b1b;
}

.event-status-message.rejected .status-icon {
    color: #ef4444;
}

/* 대기 상태 스타일 */
.event-status-message.pending {
    background: #fffbeb;
    border-color: #f59e0b;
    color: #92400e;
}

.event-status-message.pending .status-icon {
    color: #f59e0b;
}

/* 대기열 상태 스타일 */
.event-status-message.waiting {
    background: #f8fafc;
    border-color: #64748b;
    color: #475569;
}

.event-status-message.waiting .status-icon {
    color: #64748b;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.instructor-card {
    text-align: center;
}

.instructor-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4A90E2 0%, #2E86AB 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    font-size: 2rem;
    color: white;
}

.instructor-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 10px;
}

.instructor-bio {
    color: #64748b;
    font-size: 16px;
    line-height: 1.5;
}

.instructor-avatar-image {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    margin: 0 auto 15px;
    overflow: hidden;
    position: relative;
}

.instructor-avatar-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.instructor-avatar-fallback {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4A90E2 0%, #2E86AB 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
    position: absolute;
    top: 0;
    left: 0;
}

/* 이미지 갤러리 스타일 */
.event-gallery {
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
    background: #f8fafc;
}

.gallery-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(74, 144, 226, 0.15);
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
    background: rgba(74, 144, 226, 0.8);
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

/* YouTube 동영상 스타일 */
.youtube-container {
    position: relative;
    padding-bottom: 56.25%; /* 16:9 비율 */
    height: 0;
    overflow: hidden;
    border-radius: 12px;
    background: #000;
}

.youtube-container iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border-radius: 12px;
}

/* 이미지 모달 스타일 */
.image-modal {
    display: none;
    position: fixed;
    z-index: 9999;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.9);
    backdrop-filter: blur(5px);
}

.modal-content {
    position: relative;
    max-width: 90%;
    max-height: 90%;
    margin: auto;
    top: 50%;
    transform: translateY(-50%);
    text-align: center;
}

.modal-content img {
    max-width: 100%;
    max-height: 80vh;
    border-radius: 8px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
}

.modal-close {
    position: absolute;
    top: 15px;
    right: 25px;
    color: white;
    font-size: 35px;
    font-weight: bold;
    cursor: pointer;
    z-index: 10000;
}

.modal-close:hover {
    color: #4A90E2;
}

/* 심플한 모달 네비게이션 */
.modal-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 48px;
    height: 48px;
    background: rgba(255, 255, 255, 0.95);
    border: none;
    border-radius: 24px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
    z-index: 10001;
}

.modal-nav:hover {
    background: rgba(255, 255, 255, 1);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    transform: translateY(-50%) scale(1.05);
}

.modal-nav:focus {
    outline: none;
    background: rgba(255, 255, 255, 0.95);
}

.modal-nav:active {
    background: rgba(255, 255, 255, 0.95);
    transform: translateY(-50%) scale(0.98);
}

.modal-prev {
    left: 20px;
}

.modal-next {
    right: 20px;
}

.modal-nav svg {
    width: 20px;
    height: 20px;
    fill: #666;
    transition: fill 0.2s ease;
}

.modal-nav:hover svg {
    fill: #333;
}

.modal-counter {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    color: white;
    background: rgba(0, 0, 0, 0.5);
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 16px;
}

.networking-notice {
    background: #e0f2fe;
    border: 1px solid #81d4fa;
    border-radius: 8px;
    padding: 15px;
    margin-top: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.networking-notice i {
    color: #0277bd;
    font-size: 1.2rem;
}

.networking-notice span {
    color: #01579b;
    font-weight: 500;
}

/* 히어로 섹션 하단 공유 버튼 */
.event-share-actions {
    margin-top: 30px;
    display: flex;
    justify-content: center;
    gap: 15px;
}

.btn-share {
    padding: 12px 24px;
    border: none;
    border-radius: 25px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 1rem;
    background: rgba(255, 255, 255, 0.15);
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(10px);
}

.btn-share:hover {
    background: rgba(255, 255, 255, 0.25);
    border-color: rgba(255, 255, 255, 0.5);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 255, 255, 0.3);
    color: white;
    text-decoration: none;
}


/* 반응형 */
@media (max-width: 768px) {
    .event-detail-container {
        padding: 20px 10px;
        /* 모바일 헤더 겹침 방지: 모바일 헤더 높이에 맞춰 조정 */
        margin-top: 85px; /* 모바일: 헤더 높이(70px) + 여유 공간(15px) */
        padding-top: 15px;
    }
    
    .event-hero {
        margin-top: 0px;
        padding: 40px 20px;
    }
    
    .event-title {
        font-size: 2rem;
    }
    
    .event-meta-row {
        gap: 15px;
    }
    
    .event-content {
        grid-template-columns: 1fr !important;
        gap: 30px;
        display: flex;
        flex-direction: column;
    }
    
    /* 신청 마감 알림 모바일 최적화 */
    .registration-deadline-notice {
        padding: 16px !important;
        margin: 15px 0 !important;
    }

    .registration-deadline-notice h3 {
        font-size: 1.1rem !important;
    }

    .registration-deadline-notice p {
        font-size: 0.9rem !important;
    }

    .registration-deadline-notice .fas.fa-clock {
        font-size: 1.5rem !important;
    }

    /* 신청 현황 모바일 최적화 */
    .info-value {
        word-break: break-word;
        line-height: 1.4;
    }

    .event-sidebar {
        order: 10 !important;
        margin-top: 20px;
    }
    
    .content-section {
        padding: 20px;
    }
    
    /* 모바일에서 관리 버튼 위치 조정 */
    .event-admin-actions {
        top: 15px;
        right: 15px;
        gap: 8px;
    }
    
    .event-admin-actions .btn {
        padding: 10px 16px;
        font-size: 16px;
        min-height: 48px;
        min-width: 48px;
    }
    
    /* 모바일에서 공유 버튼 크기 조정 */
    .btn-share {
        padding: 12px 24px;
        font-size: 16px;
        min-height: 48px;
    }

    /* 🔥 모바일 행사 관리 버튼 크기 최적화 (강의 상세와 완전 동일하게) */
    .event-admin-actions .btn {
        padding: 6px 12px !important;
        font-size: 12px !important;
        min-height: 28px !important;
        max-height: 28px !important;
        min-width: auto !important;
        border-radius: 4px !important;
        margin: 0 !important;
        backdrop-filter: blur(10px) !important;
        z-index: 15 !important;
    }

    /* 행사 관리 버튼 위치 조정 - 강의 상세와 동일한 위치로 */
    .event-admin-actions {
        position: absolute !important;
        top: 8px !important;
        right: 8px !important;
        display: flex !important;
        gap: 6px !important;
        z-index: 15 !important;
        backdrop-filter: blur(10px) !important;
        background: rgba(0, 0, 0, 0.1) !important;
        padding: 4px !important;
        border-radius: 6px !important;
    }

    /* 행사 액션 버튼들 개별 색상 */
    .event-admin-actions .btn-edit {
        background: rgba(255, 255, 255, 0.9) !important;
        color: #4a5568 !important;
        border: 1px solid rgba(255, 255, 255, 0.9) !important;
    }

    .event-admin-actions .btn-danger {
        background: rgba(229, 62, 62, 0.9) !important;
        color: white !important;
        border: 1px solid rgba(229, 62, 62, 0.9) !important;
    }
}

/* 🔥 작은 모바일 (480px 이하)에서 행사 관리 버튼 더 작게 */
@media (max-width: 480px) {
    .event-admin-actions {
        top: 6px !important;
        right: 6px !important;
        padding: 2px !important;
        border-radius: 4px !important;
    }

    .event-admin-actions .btn {
        padding: 4px 8px !important;
        font-size: 11px !important;
        min-height: 24px !important;
        max-height: 24px !important;
        gap: 4px !important;
    }
}

@media (max-width: 768px) {
    /* 모바일 수평 스크롤 방지 */
    .event-detail-container {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }
    
    /* 모든 버튼 터치 타겟 최적화 */
    .btn,
    button,
    .register-btn,
    .btn-visit-profile, .btn-chat-author,
    .close, .modal-close,
    a[href],
    input[type="submit"], input[type="button"] {
        min-height: 48px !important;
        min-width: 48px !important;
        font-size: 16px !important;
        padding: 12px 24px !important;
        touch-action: manipulation;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    /* 🗺️ 네이버 지도 컨테이너 기본 설정 */
    #eventVenueMap {
        width: 100%;
        height: 400px;
        border-radius: 8px;
        overflow: hidden;
        position: relative;
    }

    /* 🚫 신청 취소 버튼 기본 숨김 처리 */
    #event-cancel-btn {
        display: none !important;
    }



    /* 모달 버튼들도 터치 친화적으로 */
    .modal-footer .btn {
        min-height: 48px !important;
        padding: 12px 24px !important;
        font-size: 16px !important;
    }
    
    /* 텍스트 가독성 개선 */
    .content-section p,
    .instructor-bio,
    .info-label,
    .event-status-message .status-description {
        font-size: 16px !important;
        line-height: 1.6 !important;
    }
    
    /* 모바일 모달 네비게이션 */
    .modal-nav {
        width: 44px;
        height: 44px;
    }
    
    .modal-prev {
        left: 15px;
    }
    
    .modal-next {
        right: 15px;
    }
}

/* 작성자 정보 스타일 */
.author-info-card {
    border-left: 3px solid #4A90E2;
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
    background: #4A90E2;
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
    background: #357ABD;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(74, 144, 226, 0.3);
    text-decoration: none;
}

.btn-visit-profile i {
    font-size: 0.875rem;
}

/* 채팅 버튼 */
.btn-chat-author {
    background: #4A90E2;
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
    background: #357ABD;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(74, 144, 226, 0.3);
    text-decoration: none;
    color: white;
}

.btn-chat-author i {
    font-size: 0.875rem;
}

/* 행사 신청 모달 스타일 (강의 등록 스타일 적용) */
.modal {
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

.modal-content {
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

.modal-header {
    padding: 24px 28px 20px;
    border-bottom: 1px solid #e2e8f0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 16px 16px 0 0;
    position: relative;
}

.modal-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.modal-close {
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

.modal-close:hover {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.modal-body {
    padding: 28px;
    text-align: left;
}

.modal-footer {
    padding: 16px 28px 24px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}

/* 섹션별 폼 구성 */
.form-section {
    margin-bottom: 28px;
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
    padding-bottom: 8px;
    border-bottom: 2px solid #e2e8f0;
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
    color: #374151;
    font-size: 16px;
    text-align: left;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 16px;
    transition: all 0.2s ease;
    background: white;
    text-align: left;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    transform: translateY(-1px);
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 16px;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: #e2e8f0;
    color: #4a5568;
}

.btn-secondary:hover {
    background: #cbd5e0;
    transform: translateY(-1px);
}

/* 반응형 디자인 */
@media (max-width: 768px) {
    .modal-content {
        width: 95%;
        max-width: none;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .modal-header {
        padding: 20px 24px 16px;
    }
    
    .modal-title {
        font-size: 1.3rem;
    }
    
    .modal-body {
        padding: 24px;
    }
}

/* 행사 이미지 갤러리 스타일 */
.event-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 15px;
}

.gallery-item {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    background: #f8fafc;
}

.gallery-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(74, 144, 226, 0.15);
}

.gallery-item img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    display: block;
    transition: transform 0.3s ease;
}

.gallery-item:hover img {
    transform: scale(1.05);
}

.gallery-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(74, 144, 226, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
    color: white;
    font-weight: 600;
    font-size: 1rem;
}

.gallery-item:hover .gallery-overlay {
    opacity: 1;
}

/* 이미지 모달 */
.event-image-modal {
    display: none;
    position: fixed;
    z-index: 10000;
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
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.2);
    transition: all 0.2s ease;
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
    font-size: 16px;
}

/* 반응형 디자인 */
@media (max-width: 768px) {
    .event-gallery {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 15px;
    }
    
    .gallery-item img {
        height: 150px;
    }
    
    .modal-image-content {
        width: 95%;
        margin-top: 10vh;
    }
    
    .modal-image-nav {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }
    
    .modal-nav-prev {
        left: 10px;
    }
    
    .modal-nav-next {
        right: 10px;
    }
}

/* 강사 이미지 모달 스타일 - 완벽한 중앙정렬 */
.instructor-image-modal {
    display: none !important; /* 기본값: 숨김 */
    position: fixed !important;
    z-index: 10000 !important;
    left: 0 !important;
    top: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    background-color: rgba(0, 0, 0, 0.9) !important;
    backdrop-filter: blur(4px) !important;
    margin: 0 !important;
    padding: 0 !important;
}

/* 모달이 활성화될 때 - 완벽한 중앙정렬 */
.instructor-image-modal.show {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.instructor-image-modal .modal-content {
    position: relative !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    justify-content: flex-start !important;
    width: 90% !important;
    max-width: 600px !important;
    max-height: 90vh !important;
    background: white !important;
    border-radius: 12px !important;
    overflow: hidden !important;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3) !important;
    margin: 0 !important;
    padding: 0 !important;
    left: auto !important;
    right: auto !important;
    top: auto !important;
    bottom: auto !important;
    transform: none !important;
}

.instructor-image-modal .modal-header {
    width: 100%;
    background: linear-gradient(135deg, #4A90E2 0%, #2E86AB 100%);
    color: white;
    padding: 20px;
    text-align: center;
}

.instructor-image-modal .modal-header h3 {
    margin: 0;
    font-size: 1.3rem;
    font-weight: 600;
}

.instructor-image-modal img {
    width: 100%;
    max-width: 500px;
    max-height: 500px;
    object-fit: cover;
    display: block;
    padding: 20px;
    box-sizing: border-box;
}

.instructor-image-modal .modal-close {
    position: absolute;
    top: 15px;
    right: 20px;
    color: white;
    font-size: 30px;
    font-weight: bold;
    cursor: pointer;
    z-index: 1;
    background: rgba(0,0,0,0.3);
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.instructor-image-modal .modal-close:hover {
    background: rgba(255,255,255,0.2);
    transform: scale(1.1);
}

/* 중앙정렬 강화를 위한 추가 CSS */
.instructor-image-modal.show {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    flex-direction: row !important;
    text-align: center !important;
}

.instructor-image-modal.show .modal-content {
    margin: auto !important;
    position: static !important;
}

@media (max-width: 768px) {
    .instructor-image-modal .modal-content {
        width: 95% !important;
        max-width: none !important;
    }
    
    .instructor-image-modal .modal-header {
        padding: 15px !important;
    }
    
    .instructor-image-modal .modal-header h3 {
        font-size: 1.1rem !important;
    }
    
    .instructor-image-modal img {
        padding: 15px !important;
    }
}
</style>

<div class="event-detail-container">
    <!-- 행사 히어로 섹션 -->
    <div class="event-hero">
        <div class="event-admin-actions">
            <?php if ($canEdit): ?>
                <?= renderButton('✏️ 수정', 'secondary', 'md', [
                    'class' => 'btn-edit',
                    'attributes' => ['data-event-id' => $event['id']]
                ]) ?>
                <?= renderButton('🗑️ 삭제', 'danger', 'md', [
                    'onclick' => 'confirmDeleteEvent(' . $event['id'] . ')'
                ]) ?>
            <?php endif; ?>
        </div>
        <div class="event-hero-content">
            <div class="event-category">
                <?php
                $categoryNames = [
                    'seminar' => '세미나',
                    'workshop' => '워크샵', 
                    'conference' => '컨퍼런스',
                    'webinar' => '웨비나',
                    'training' => '교육'
                ];
                echo $categoryNames[$event['category']] ?? '행사';
                ?>
                <?php if ($event['event_scale']): ?>
                    <?php
                    $scaleNames = ['small' => '소규모', 'medium' => '중규모', 'large' => '대규모'];
                    ?>
                    <span class="event-scale-badge <?= $event['event_scale'] ?>">
                        <?= $scaleNames[$event['event_scale']] ?>
                    </span>
                <?php endif; ?>
            </div>
            
            <h1 class="event-title">
                <?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?>
            </h1>
            
            <p class="event-subtitle">
                <?= htmlspecialchars(mb_substr(strip_tags($event['description']), 0, 100), ENT_QUOTES, 'UTF-8') ?>...
            </p>
            
            <div class="event-meta-row">
                <div class="event-meta-item">
                    <i class="fas fa-calendar"></i>
                    <span><?= date('Y년 n월 j일', strtotime($event['start_date'])) ?></span>
                </div>
                <div class="event-meta-item">
                    <i class="fas fa-clock"></i>
                    <span>
                        <?php
                        // 시작 시간만 표시
                        if ($event['start_time']) {
                            echo date('H:i', strtotime($event['start_time'])) . ' 시작';
                        } else {
                            echo '시간 미정';
                        }
                        ?>
                    </span>
                </div>
                <div class="event-meta-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>
                        <?php if ($event['location_type'] === 'online'): ?>
                            온라인
                        <?php elseif ($event['location_type'] === 'hybrid'): ?>
                            하이브리드
                        <?php else: ?>
                            <?= htmlspecialchars($event['venue_name'] ?? '오프라인') ?>
                        <?php endif; ?>
                    </span>
                </div>
                <?php if (!empty($event['registration_deadline'])): ?>
                <div class="event-meta-item">
                    <i class="fas fa-hourglass-half"></i>
                    <span>
                        <?php
                        $deadline = new DateTime($event['registration_deadline']);
                        $now = new DateTime();
                        
                        if ($now > $deadline) {
                            echo '<span style="color: #ef4444;">신청 마감</span>';
                        } else {
                            echo '신청 마감: ' . $deadline->format('n월 j일 H:i');
                        }
                        ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="event-share-actions">
                <?= renderButton('🔗 공유하기', 'secondary', 'md', [
                    'class' => 'btn-share',
                    'onclick' => 'shareEventContent()'
                ]) ?>
            </div>
        </div>
    </div>

    <!-- 메인 콘텐츠 -->
    <div class="event-content">
        <!-- 메인 영역 -->
        <div class="event-main">
            <?php if (!empty($event['youtube_video'])): ?>
            <div class="content-section">
                <h2>🎬 관련 영상</h2>
                <div class="youtube-container">
                    <?php
                    // YouTube URL을 embed 형식으로 변환
                    $youtubeUrl = $event['youtube_video'];
                    $embedUrl = $youtubeUrl;
                    
                    // 일반 YouTube URL을 embed URL로 변환
                    if (strpos($youtubeUrl, 'youtube.com/watch?v=') !== false) {
                        $videoId = preg_replace('/.*[?&]v=([^&]*).*/', '$1', $youtubeUrl);
                        $embedUrl = "https://www.youtube.com/embed/" . $videoId;
                    } elseif (strpos($youtubeUrl, 'youtu.be/') !== false) {
                        $videoId = str_replace('https://youtu.be/', '', $youtubeUrl);
                        $embedUrl = "https://www.youtube.com/embed/" . $videoId;
                    }
                    ?>
                    <iframe 
                        src="<?= htmlspecialchars($embedUrl) ?>" 
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen>
                    </iframe>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="content-section">
                <h2>행사 소개</h2>
                <div class="event-description ql-editor">
                    <?= $event['description'] ?? '' ?>
                </div>
            </div>
            
            <?php if (!empty($event['images'])): ?>
            <div class="content-section">
                <h2>🖼️ 이미지</h2>
                <div class="event-gallery">
                    <?php foreach ($event['images'] as $index => $image): ?>
                        <div class="gallery-item" onclick="openImageModal(<?= $index ?>)">
                            <img src="<?= htmlspecialchars($image['url']) ?>" 
                                 alt="<?= htmlspecialchars($image['alt_text']) ?>"
                                 loading="lazy">
                            <div class="gallery-overlay">
                                <span>🔍 크게 보기</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
        </div>

        <!-- 사이드바 -->
        <div class="event-sidebar">
            <!-- 등록 정보 -->
            <div class="info-card register-card">
                <h3><i class="fas fa-ticket-alt"></i>
                    <?php if ($event['registration_fee'] && $event['registration_fee'] > 0): ?>
                        참가 신청 비용
                    <?php else: ?>
                        참가 신청
                    <?php endif; ?>
                </h3>
                <div class="event-fee">
                    <?php if ($event['registration_fee']): ?>
                        <?= number_format($event['registration_fee']) ?>원
                    <?php else: ?>
                        무료
                    <?php endif; ?>
                </div>
                <?php if ($isLoggedIn): ?>
                    <?php
                    // 🔥 Ultra Think: 완전한 마감 조건 체크 시스템
                    $now = new DateTime();

                    // 1. 등록 마감일 확인
                    $isDeadlinePassed = false;
                    if (!empty($event['registration_deadline'])) {
                        $deadline = new DateTime($event['registration_deadline']);
                        $isDeadlinePassed = $now > $deadline;
                    }

                    // 2. 본인 행사 체크
                    $isOwnEvent = ($event['user_id'] == $currentUserId);

                    // 3. 정원 초과 체크
                    $isCapacityFull = false;
                    if ($event['max_participants'] && $event['max_participants'] > 0) {
                        $isCapacityFull = ($event['current_registration_count'] >= $event['max_participants']);
                    }

                    // 4. 행사 시작일 지남 체크
                    $isEventStarted = false;
                    if ($event['start_date'] && $event['start_time']) {
                        $eventStart = new DateTime($event['start_date'] . ' ' . $event['start_time']);
                        $isEventStarted = $now > $eventStart;
                    }

                    // 마감 여부 종합 판단
                    $cannotRegister = $isDeadlinePassed || $isOwnEvent || $isCapacityFull || $isEventStarted;
                    ?>

                    <?php if ($isOwnEvent): ?>
                        <!-- 본인 행사 신청 방지 안내 -->
                        <div class="own-event-notice" style="
                            background: linear-gradient(135deg, #fff3cd 0%, #fdf5e6 100%);
                            border: 2px solid #ffc107;
                            border-radius: 12px;
                            padding: 20px;
                            text-align: center;
                            margin: 20px 0;
                            box-shadow: 0 4px 12px rgba(255, 193, 7, 0.15);
                        ">
                            <div style="margin-bottom: 12px;">
                                <i class="fas fa-user-edit" style="
                                    font-size: 2rem;
                                    color: #ffc107;
                                    margin-bottom: 8px;
                                "></i>
                            </div>
                            <h3 style="
                                color: #856404;
                                font-size: 1.3rem;
                                font-weight: 700;
                                margin: 0 0 8px 0;
                            ">본인이 등록한 행사입니다</h3>
                            <p style="
                                color: #6c757d;
                                font-size: 1rem;
                                margin: 0;
                                line-height: 1.5;
                            ">자신이 등록한 행사에는 참가 신청할 수 없습니다</p>
                        </div>
                    <?php elseif ($isCapacityFull): ?>
                        <!-- 정원 초과 안내 -->
                        <div class="capacity-full-notice" style="
                            background: linear-gradient(135deg, #f8d7da 0%, #f1c6cb 100%);
                            border: 2px solid #dc3545;
                            border-radius: 12px;
                            padding: 20px;
                            text-align: center;
                            margin: 20px 0;
                            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.15);
                        ">
                            <div style="margin-bottom: 12px;">
                                <i class="fas fa-users" style="
                                    font-size: 2rem;
                                    color: #dc3545;
                                    margin-bottom: 8px;
                                "></i>
                            </div>
                            <h3 style="
                                color: #dc3545;
                                font-size: 1.3rem;
                                font-weight: 700;
                                margin: 0 0 8px 0;
                            ">정원이 마감되었습니다</h3>
                            <p style="
                                color: #6c757d;
                                font-size: 1rem;
                                margin: 0 0 12px 0;
                                line-height: 1.5;
                            ">
                                현재 <strong style="color: #dc3545;"><?= number_format($event['current_registration_count']) ?>명</strong>이 신청했습니다<br>
                                <span style="font-size: 0.9rem;">( 정원: <?= number_format($event['max_participants']) ?>명 )</span>
                            </p>
                            <div style="
                                background: #fff;
                                border-radius: 8px;
                                padding: 12px;
                                border-left: 4px solid #17a2b8;
                                margin-top: 15px;
                            ">
                                <i class="fas fa-clock" style="color: #17a2b8; margin-right: 8px;"></i>
                                <span style="color: #495057; font-size: 0.95rem;">
                                    취소 발생 시 선착순으로 신청 가능합니다
                                </span>
                            </div>
                        </div>
                    <?php elseif ($isEventStarted): ?>
                        <!-- 행사 시작됨 안내 -->
                        <div class="event-started-notice" style="
                            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
                            border: 2px solid #17a2b8;
                            border-radius: 12px;
                            padding: 20px;
                            text-align: center;
                            margin: 20px 0;
                            box-shadow: 0 4px 12px rgba(23, 162, 184, 0.15);
                        ">
                            <div style="margin-bottom: 12px;">
                                <i class="fas fa-play-circle" style="
                                    font-size: 2rem;
                                    color: #17a2b8;
                                    margin-bottom: 8px;
                                "></i>
                            </div>
                            <h3 style="
                                color: #17a2b8;
                                font-size: 1.3rem;
                                font-weight: 700;
                                margin: 0 0 8px 0;
                            ">행사가 이미 시작되었습니다</h3>
                            <p style="
                                color: #6c757d;
                                font-size: 1rem;
                                margin: 0;
                                line-height: 1.5;
                            ">행사 시작 후에는 참가 신청할 수 없습니다</p>
                        </div>
                    <?php elseif ($isDeadlinePassed): ?>
                        <?php
                        // 마감 시간 관련 정보 계산
                        $now = new DateTime();
                        $deadline = new DateTime($event['registration_deadline']);
                        $interval = $now->diff($deadline);

                        // 마감 후 경과 시간 계산
                        if ($interval->days > 0) {
                            $timeAgo = $interval->days . '일 전';
                        } elseif ($interval->h > 0) {
                            $timeAgo = $interval->h . '시간 전';
                        } else {
                            $timeAgo = $interval->i . '분 전';
                        }

                        // 마감 날짜 포맷팅
                        $deadlineFormatted = $deadline->format('Y년 m월 d일 H:i');
                        ?>

                        <div class="registration-deadline-notice" style="
                            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                            border: 2px solid #dc3545;
                            border-radius: 12px;
                            padding: 20px;
                            text-align: center;
                            margin: 20px 0;
                            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.15);
                        ">
                            <div style="margin-bottom: 12px;">
                                <i class="fas fa-clock" style="
                                    font-size: 2rem;
                                    color: #dc3545;
                                    margin-bottom: 8px;
                                "></i>
                            </div>
                            <h3 style="
                                color: #dc3545;
                                font-size: 1.3rem;
                                font-weight: 700;
                                margin: 0 0 8px 0;
                            ">신청이 마감되었습니다</h3>
                            <p style="
                                color: #6c757d;
                                font-size: 1rem;
                                margin: 0 0 12px 0;
                                line-height: 1.5;
                            ">
                                <strong><?= $deadlineFormatted ?></strong>에 마감<br>
                                <span style="font-size: 0.9rem;">( <?= $timeAgo ?> 마감 )</span>
                            </p>
                        </div>
                    <?php elseif (!$event['allow_online_registration'] || $event['allow_online_registration'] == 0): ?>
                        <div class="no-registration-notice" style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 8px; text-align: center; color: #64748b;">
                            <i class="fas fa-info-circle" style="margin-right: 8px;"></i>
                            온라인 참가 신청을 받지 않는 행사입니다<br>
                            <small style="color: #94a3b8;">참가 문의는 주최자에게 별도 연락하세요</small>
                        </div>
                    <?php else: ?>
                        <!-- 행사 신청 상태 메시지 영역 -->
                        <div id="event-status-message" class="event-status-message" style="display: none; margin-bottom: 15px;">
                            <div class="status-content">
                                <div class="status-icon">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div class="status-text">
                                    <div class="status-title" id="event-status-title"></div>
                                    <div class="status-description" id="event-status-description"></div>
                                </div>
                            </div>
                        </div>
                        
                        <?= renderButton('참가 신청하기', 'primary', 'lg', [
                            'id' => 'event-register-btn',
                            'class' => 'register-btn',
                            'onclick' => 'registerEvent()',
                            'fullWidth' => true
                        ]) ?>
                        <?= renderButton('신청 취소', 'danger', 'lg', [
                            'id' => 'event-cancel-btn',
                            'class' => 'register-btn',
                            'onclick' => 'cancelEventRegistration()',
                            'attributes' => ['style' => 'display: none !important;']
                        ]) ?>
                    <?php endif; ?>
                <?php else: ?>
                    <?php if ($isDeadlinePassed): ?>
                        <?php
                        // 마감 시간 관련 정보 계산
                        $now = new DateTime();
                        $deadline = new DateTime($event['registration_deadline']);
                        $interval = $now->diff($deadline);

                        // 마감 후 경과 시간 계산
                        if ($interval->days > 0) {
                            $timeAgo = $interval->days . '일 전';
                        } elseif ($interval->h > 0) {
                            $timeAgo = $interval->h . '시간 전';
                        } else {
                            $timeAgo = $interval->i . '분 전';
                        }

                        // 마감 날짜 포맷팅
                        $deadlineFormatted = $deadline->format('Y년 m월 d일 H:i');
                        ?>

                        <div class="registration-deadline-notice" style="
                            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                            border: 2px solid #dc3545;
                            border-radius: 12px;
                            padding: 20px;
                            text-align: center;
                            margin: 20px 0;
                            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.15);
                        ">
                            <div style="margin-bottom: 12px;">
                                <i class="fas fa-clock" style="
                                    font-size: 2rem;
                                    color: #dc3545;
                                    margin-bottom: 8px;
                                "></i>
                            </div>
                            <h3 style="
                                color: #dc3545;
                                font-size: 1.3rem;
                                font-weight: 700;
                                margin: 0 0 8px 0;
                            ">신청이 마감되었습니다</h3>
                            <p style="
                                color: #6c757d;
                                font-size: 1rem;
                                margin: 0 0 12px 0;
                                line-height: 1.5;
                            ">
                                <strong><?= $deadlineFormatted ?></strong>에 마감<br>
                                <span style="font-size: 0.9rem;">( <?= $timeAgo ?> 마감 )</span>
                            </p>
                        </div>
                    <?php elseif (!$event['allow_online_registration'] || $event['allow_online_registration'] == 0): ?>
                        <div class="no-registration-notice" style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 8px; text-align: center; color: #64748b;">
                            <i class="fas fa-info-circle" style="margin-right: 8px;"></i>
                            온라인 참가 신청을 받지 않는 행사입니다<br>
                            <small style="color: #94a3b8;">참가 문의는 주최자에게 별도 연락하세요</small>
                        </div>
                    <?php else: ?>
                        <?= renderButton('로그인 후 신청하기', 'primary', 'lg', [
                            'class' => 'register-btn',
                            'onclick' => 'redirectToLogin()',
                            'fullWidth' => true
                        ]) ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- 행사 정보 -->
            <div class="info-card">
                <h3><i class="fas fa-info-circle"></i> 행사 정보</h3>
                <ul class="info-list">
                    <li>
                        <span class="info-label">시작</span>
                        <span class="info-value">
                            <?php
                            $startDateTime = date('Y년 n월 j일 H:i', strtotime($event['start_date'] . ' ' . $event['start_time']));
                            echo $startDateTime;
                            ?>
                        </span>
                    </li>
                    <?php if ($event['end_date'] || $event['end_time']): ?>
                    <li>
                        <span class="info-label">종료</span>
                        <span class="info-value">
                            <?php
                            $endDate = $event['end_date'] ?: $event['start_date'];
                            $endTime = $event['end_time'] ?: $event['start_time'];
                            $endDateTime = date('Y년 n월 j일 H:i', strtotime($endDate . ' ' . $endTime));
                            echo $endDateTime;
                            ?>
                        </span>
                    </li>
                    <?php endif; ?>
                    <li>
                        <span class="info-label">장소</span>
                        <span class="info-value">
                            <?php if ($event['location_type'] === 'online'): ?>
                                온라인
                            <?php elseif ($event['location_type'] === 'hybrid'): ?>
                                하이브리드
                            <?php else: ?>
                                오프라인
                            <?php endif; ?>
                        </span>
                    </li>
                    <?php if ($event['max_participants']): ?>
                    <li>
                        <span class="info-label">신청 현황</span>
                        <span class="info-value">
                            <strong style="color: #4A90E2;"><?= number_format($event['current_registration_count'] ?? 0) ?></strong>
                            /
                            <strong><?= number_format($event['max_participants']) ?></strong>명
                            <?php
                            // 신청률 계산
                            $registrationRate = $event['max_participants'] > 0
                                ? round(($event['current_registration_count'] ?? 0) / $event['max_participants'] * 100, 1)
                                : 0;
                            ?>
                            <span style="
                                font-size: 0.85em;
                                color: <?= $registrationRate >= 80 ? '#dc3545' : ($registrationRate >= 50 ? '#ffc107' : '#28a745') ?>;
                                margin-left: 8px;
                                font-weight: 600;
                            ">
                                (<?= $registrationRate ?>%)
                            </span>
                        </span>
                    </li>
                    <?php endif; ?>
                    <?php if (!empty($event['registration_deadline'])): ?>
                    <li>
                        <span class="info-label">신청 마감</span>
                        <span class="info-value">
                            <?php
                            $deadline = new DateTime($event['registration_deadline']);
                            $now = new DateTime();
                            
                            if ($now > $deadline) {
                                echo '<span style="color: #ef4444; font-weight: 600;">마감됨</span>';
                            } else {
                                echo $deadline->format('Y년 n월 j일 H:i');
                            }
                            ?>
                        </span>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- 강사/연사 정보 -->
            <?php 
            // 다중 강사 정보 중에서 유효한 강사가 있는지 확인
            $hasValidInstructors = false;
            if (!empty($event['instructors']) && is_array($event['instructors'])) {
                foreach ($event['instructors'] as $instructor) {
                    if ((!empty($instructor['name']) && $instructor['name'] !== '미정') || 
                        (!empty($instructor['info']) && trim($instructor['info']) !== '')) {
                        $hasValidInstructors = true;
                        break;
                    }
                }
            }
            ?>
            <?php if ($hasValidInstructors): ?>
            <div class="info-card instructors-card">
                <h3><i class="fas fa-users"></i> 강사/연사 정보</h3>
                <div class="instructors-list">
                    <?php foreach ($event['instructors'] as $instructor): ?>
                    <?php if ((!empty($instructor['name']) && $instructor['name'] !== '미정') || (!empty($instructor['info']) && trim($instructor['info']) !== '')): ?>
                    <div class="instructor-item">
                        <div class="instructor-header">
                            <div class="instructor-avatar">
                                <?php if (!empty($instructor['image'])): ?>
                                    <img src="<?= htmlspecialchars($instructor['image']) ?>" 
                                         alt="<?= htmlspecialchars($instructor['name']) ?>" 
                                         onclick="openInstructorImageModal('<?= htmlspecialchars($instructor['image']) ?>', '<?= htmlspecialchars($instructor['name']) ?>')"
                                         style="cursor: pointer;"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <?php endif; ?>
                                <div class="instructor-fallback" <?= !empty($instructor['image']) ? 'style="display:none;"' : '' ?>>
                                    <?= mb_substr($instructor['name'], 0, 1) ?>
                                </div>
                            </div>
                            <div class="instructor-details">
                                <div class="instructor-name"><?= htmlspecialchars($instructor['name']) ?></div>
                                <?php if (!empty($instructor['title'])): ?>
                                <div class="instructor-title"><?= htmlspecialchars($instructor['title']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if (!empty($instructor['info'])): ?>
                        <div class="instructor-bio"><?= htmlspecialchars($instructor['info']) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php elseif ((!empty($event['instructor_name']) && $event['instructor_name'] !== '미정') || (!empty($event['instructor_info']) && trim($event['instructor_info']) !== '')): ?>
            <!-- 기본 강사 정보 표시 (instructor_name, instructor_info 필드 사용) -->
            <div class="info-card instructors-card">
                <h3><i class="fas fa-user"></i> 강사 정보</h3>
                <div class="instructors-list">
                    <div class="instructor-item">
                        <div class="instructor-header">
                            <div class="instructor-avatar">
                                <?php if (!empty($event['instructor_image'])): ?>
                                    <img src="<?= htmlspecialchars($event['instructor_image']) ?>" 
                                         alt="<?= htmlspecialchars($event['instructor_name'] ?: '강사') ?>" 
                                         onclick="openInstructorImageModal('<?= htmlspecialchars($event['instructor_image']) ?>', '<?= htmlspecialchars($event['instructor_name'] ?: '강사') ?>')"
                                         style="cursor: pointer;"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <?php endif; ?>
                                <div class="instructor-fallback" <?= !empty($event['instructor_image']) ? 'style="display:none;"' : '' ?>>
                                    <?= mb_substr($event['instructor_name'] ?: '강사', 0, 1) ?>
                                </div>
                            </div>
                            <div class="instructor-details">
                                <div class="instructor-name"><?= htmlspecialchars($event['instructor_name'] ?: '미정', ENT_QUOTES, 'UTF-8') ?></div>
                            </div>
                        </div>
                        <?php if (!empty($event['instructor_info'])): ?>
                        <div class="instructor-bio"><?= htmlspecialchars($event['instructor_info'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>


            <?php if ($event['venue_address'] || $event['online_link']): ?>
            <!-- 장소 정보 -->
            <div class="info-card">
                <h3><i class="fas fa-map-marker-alt"></i> 장소 안내</h3>
                <ul class="info-list">
                    <?php if ($event['venue_name']): ?>
                    <li>
                        <span class="info-label">장소명</span>
                        <span class="info-value"><?= htmlspecialchars($event['venue_name']) ?></span>
                    </li>
                    <?php endif; ?>
                    <?php if ($event['venue_address']): ?>
                    <li>
                        <span class="info-label">주소</span>
                        <span class="info-value"><?= htmlspecialchars($event['venue_address']) ?></span>
                    </li>
                    <?php endif; ?>
                    <?php if ($event['online_link'] && in_array($event['location_type'], ['online', 'hybrid'])): ?>
                    <li>
                        <span class="info-label">온라인 링크</span>
                        <span class="info-value">
                            <a href="<?= htmlspecialchars($event['online_link']) ?>" target="_blank" style="color: #4A90E2;">
                                참가 링크
                            </a>
                        </span>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <!-- 행사장 지도 (오프라인/하이브리드 행사만 표시) -->
            <?php if (in_array($event['location_type'], ['offline', 'hybrid']) && $event['venue_address']): ?>
            <div class="info-card">
                <h3><i class="fas fa-map"></i> 오시는 길</h3>
                <div id="eventVenueMap" style="width: 100%; height: 300px; border-radius: 8px; margin-top: 15px;"></div>
                <div style="text-align: center; margin-top: 10px; color: #64748b; font-size: 0.9rem;">
                    지도를 드래그하여 위치를 확인하세요
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>
            
            <!-- 작성자 정보 -->
            <?php if (isset($event['user_id'])): ?>
                <div class="info-card author-info-card">
                    <h3><i class="fas fa-user-edit"></i> 작성자</h3>
                    <div class="author-info-compact">
                        <?php
                        // 작성자 정보만 추출 (행사 데이터가 아닌 사용자 데이터로 변환)
                        $user = [
                            'id' => $event['user_id'], // 실제 작성자 user_id 사용
                            'nickname' => $event['author_name'] ?? $event['nickname'] ?? '작성자',
                            'profile_image' => $event['profile_image'] ?? null,
                            'profile_image_original' => $event['profile_image_original'] ?? null,
                            'profile_image_profile' => $event['profile_image_profile'] ?? null,
                            'profile_image_thumb' => $event['profile_image_thumb'] ?? null
                        ];
                        $size = ProfileImageHelper::SIZE_THUMB;
                        $mode = 'direct';
                        $extraClasses = ['author-avatar-small'];
                        include SRC_PATH . '/views/components/profile-image.php';
                        
                        $authorName = $event['author_name'] ?? $event['nickname'] ?? '작성자';
                        ?>
                        <div class="author-details-compact">
                            <div class="author-name-compact"><?= htmlspecialchars($authorName) ?></div>
                            <div class="author-meta-compact">
                                📅 <?= date('Y.m.d', strtotime($event['created_at'])) ?>
                            </div>
                            <?php if (!empty($event['author_bio'])): ?>
                                <div class="author-bio-compact"><?= htmlspecialchars(mb_substr(strip_tags($event['author_bio']), 0, 80)) ?>...</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="display: flex; gap: 10px; margin-top: 12px; align-items: center;">
                        <?php if (isset($event['user_id'])): ?>
                            <a href="/profile/<?= $event['user_id'] ?>" class="btn-visit-profile" style="flex: 1;">
                                <i class="fas fa-user"></i> 프로필 방문
                            </a>
                            <?php if ($isLoggedIn && $event['user_id'] != $currentUserId): ?>
                                <?= renderButton('', 'primary', 'md', [
                                    'class' => 'btn-chat-author',
                                    'onclick' => 'startChatWithAuthor(' . $event['user_id'] . ', \'' . addslashes(htmlspecialchars($authorName)) . '\')',
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

<!-- 행사 신청 모달 -->
<?php if ($isLoggedIn): ?>
<div id="eventRegistrationModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">📋 행사 신청</h3>
            <button class="modal-close" onclick="closeEventRegistrationModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="eventRegistrationForm">
                <!-- 개인 정보 섹션 -->
                <div class="form-section">
                    <h4 class="form-section-title">
                        <i class="fas fa-user"></i> 개인 정보 (필수)
                    </h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="event_participant_name">이름 *</label>
                            <input type="text" id="event_participant_name" name="participant_name" required>
                        </div>
                        <div class="form-group">
                            <label for="event_participant_phone">연락처 *</label>
                            <input type="tel" id="event_participant_phone" name="participant_phone" required placeholder="010-1234-5678">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="event_participant_email">이메일 *</label>
                        <input type="email" id="event_participant_email" name="participant_email" required>
                    </div>
                </div>
                
                <!-- 소속 정보 섹션 -->
                <div class="form-section">
                    <h4 class="form-section-title">
                        <i class="fas fa-building"></i> 소속 정보 (선택)
                    </h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="event_company_name">회사명/소속</label>
                            <input type="text" id="event_company_name" name="company_name" placeholder="소속 회사나 기관명 (선택사항)">
                        </div>
                        <div class="form-group">
                            <label for="event_position">직책/직위</label>
                            <input type="text" id="event_position" name="position" placeholder="직책이나 직위 (선택사항)">
                        </div>
                    </div>
                </div>
                
                <!-- 추가 정보 섹션 -->
                <div class="form-section">
                    <h4 class="form-section-title">
                        <i class="fas fa-clipboard-check"></i> 추가 정보 (선택)
                    </h4>
                    <div class="form-group">
                        <label for="event_motivation">참가 동기/목적</label>
                        <textarea id="event_motivation" name="motivation" placeholder="이 행사에 참가하시는 이유나 기대하시는 점을 간단히 적어주세요 (선택사항)"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="event_how_did_you_know">어떻게 알게 되셨나요?</label>
                        <select id="event_how_did_you_know" name="how_did_you_know">
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
                        <label for="event_special_requests">특별 요청사항</label>
                        <textarea id="event_special_requests" name="special_requests" placeholder="식이 제한, 접근성 요구사항 등이 있으시면 알려주세요."></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeEventRegistrationModal()">
                취소
            </button>
            <button type="button" class="btn btn-primary" onclick="submitEventRegistration()">
                신청하기
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- 이미지 모달 -->
<?php if (!empty($event['images'])): ?>
<div id="imageModal" class="image-modal">
    <span class="modal-close" onclick="closeImageModal()">&times;</span>
    <div class="modal-content">
        <img id="modalImage" src="" alt="">
        <button class="modal-nav modal-prev" onclick="prevImage()">
            <svg viewBox="0 0 24 24">
                <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
            </svg>
        </button>
        <button class="modal-nav modal-next" onclick="nextImage()">
            <svg viewBox="0 0 24 24">
                <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
            </svg>
        </button>
        <div class="modal-counter">
            <span id="imageCounter">1 / <?= count($event['images']) ?></span>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- 강사 이미지 모달 -->
<div id="instructorImageModal" class="instructor-image-modal">
    <span class="modal-close" onclick="closeInstructorImageModal()">&times;</span>
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="instructorModalName">강사 이미지</h3>
        </div>
        <img id="instructorModalImage" src="" alt="">
    </div>
</div>

<!-- 네이버 지도 API (행사장 위치) -->
<?php if (in_array($event['location_type'], ['offline', 'hybrid']) && $event['venue_address']): ?>
<?php
// 행사장 정보 설정
$venueName = !empty($event['venue_name']) ? $event['venue_name'] : '행사장';
$mapAddress = !empty($event['venue_address']) ? $event['venue_address'] : '';
$naverClientId = defined('NAVER_MAPS_CLIENT_ID') ? NAVER_MAPS_CLIENT_ID : 'c5yj6m062z';

// 행사장 좌표 (데이터베이스에서 가져온 실제 좌표 우선 사용)
$eventCoords = [
    'lat' => 37.5665,  // 서울시청 기본
    'lng' => 126.9780
];

// 데이터베이스에 저장된 위경도가 있으면 우선 사용
if (!empty($event['venue_latitude']) && !empty($event['venue_longitude'])) {
    $eventCoords['lat'] = floatval($event['venue_latitude']);
    $eventCoords['lng'] = floatval($event['venue_longitude']);
} else {
    // 저장된 위경도가 없으면 주소 기반으로 추정
    if (strpos($mapAddress, '반도 아이비밸리') !== false || strpos($mapAddress, '가산디지털1로 204') !== false) {
        $eventCoords['lat'] = 37.4835033620443;
        $eventCoords['lng'] = 126.881038151818;
    } elseif (strpos($mapAddress, '가산') !== false || strpos($mapAddress, '금천구') !== false) {
        $eventCoords['lat'] = 37.4816;
        $eventCoords['lng'] = 126.8819;
    } elseif (strpos($mapAddress, '강남') !== false || strpos($mapAddress, '테헤란로') !== false) {
        $eventCoords['lat'] = 37.4979;
        $eventCoords['lng'] = 127.0276;
    } elseif (strpos($mapAddress, '홍대') !== false || strpos($mapAddress, '마포') !== false) {
        $eventCoords['lat'] = 37.5563;
        $eventCoords['lng'] = 126.9236;
    }
}
?>

<script type="text/javascript" src="https://oapi.map.naver.com/openapi/v3/maps.js?ncpKeyId=<?= htmlspecialchars($naverClientId) ?>&callback=initEventVenueMap"></script>
<script>
// 네이버 지도 API 사용 가능 여부 확인
function checkNaverMapsAPI() {
    return typeof naver !== 'undefined' && 
           typeof naver.maps !== 'undefined' && 
           typeof naver.maps.Map !== 'undefined';
}

// 지도 대체 UI 표시 함수
function showEventMapFallback() {
    var mapContainer = document.getElementById('eventVenueMap');
    if (mapContainer) {
        mapContainer.innerHTML = 
            '<div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; background: #f8fafc; color: #4a5568; border-radius: 8px; border: 1px solid #e2e8f0;">' +
            '<div style="font-size: 32px; margin-bottom: 15px; color: #4A90E2;">🏢</div>' +
            '<div style="font-weight: bold; margin-bottom: 8px; font-size: 16px; color: #2d3748;"><?= addslashes($venueName) ?></div>' +
            '<div style="font-size: 13px; margin-bottom: 20px; text-align: center; padding: 0 20px; color: #4a5568;"><?= addslashes($mapAddress) ?></div>' +
            '<a href="https://map.naver.com/v5/search/<?= urlencode($mapAddress) ?>" target="_blank" ' +
            'style="background: #4A90E2; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: bold;">' +
            '📍 네이버 지도에서 보기</a>' +
            '</div>';
    }
}

// 행사장 지도 초기화 함수 (글로벌 함수로 정의)
window.initEventVenueMap = function() {
    try {
        // 네이버 지도 API 사용 가능 여부 확인
        if (!checkNaverMapsAPI()) {
            console.warn('🗺️ 네이버 지도 API를 사용할 수 없습니다.');
            showEventMapFallback();
            return;
        }
        
        console.log('🗺️ 행사장 지도 초기화 시작');
        
        // 지도 중심 좌표
        var center = new naver.maps.LatLng(<?= floatval($eventCoords['lat']) ?>, <?= floatval($eventCoords['lng']) ?>);
        
        // 지도 옵션 (일반/위성 버튼 제거)
        var mapOptions = {
            center: center,
            zoom: 16,
            mapTypeControl: false,  // 일반/위성 버튼 완전 제거
            zoomControl: true,
            zoomControlOptions: {
                style: naver.maps.ZoomControlStyle.SMALL,
                position: naver.maps.Position.RIGHT_CENTER
            }
        };
        
        // 지도 생성
        var map = new naver.maps.Map('eventVenueMap', mapOptions);
        
        // 행사장 마커 생성 (파란색 테마)
        var marker = new naver.maps.Marker({
            position: center,
            map: map,
            title: '<?= addslashes($venueName) ?>',
            icon: {
                content: '<div style="width: 20px; height: 20px; background: #4A90E2; border: 2px solid white; border-radius: 50%; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"></div>',
                anchor: new naver.maps.Point(10, 10)
            }
        });
        
        // 정보창 생성
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
                '🎉 <?= addslashes($venueName) ?>' +
                '</div>' +
                '<div style="font-size: 12px; color: #4a5568; line-height: 1.4;">' +
                '📍 <?= addslashes($mapAddress) ?>' +
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
            } catch (error) {
                console.error('🗺️ 정보창 토글 오류:', error);
            }
        });
        
        // 초기에 정보창 표시
        setTimeout(function() {
            try {
                infoWindow.open(map, marker);
            } catch (error) {
                console.error('🗺️ 초기 정보창 표시 오류:', error);
            }
        }, 500);
        
        console.log('🗺️ 행사장 지도 초기화 완료');

    } catch (error) {
        console.error('🗺️ 행사장 지도 초기화 오류:', error);
        showEventMapFallback();
    }
};

// API 로드 실패시 fallback
window.addEventListener('error', function(e) {
    if (e.filename && e.filename.includes('maps.js')) {
        console.warn('🗺️ 네이버 지도 API 로드 실패:', e.message);
        showEventMapFallback();
    }
});

// DOM 로드 후 지도 초기화 (callback 방식이므로 자동 호출됨)
document.addEventListener('DOMContentLoaded', function() {
    // API가 callback으로 자동 호출되므로 별도 초기화 불필요
    console.log('🗺️ DOM 로드 완료 - API callback 대기 중');
});
</script>
<?php endif; ?>

<script>
// 행사 ID 전역 변수
const eventId = <?= $event['id'] ?>;

// 조건부 워딩을 위한 변수 설정
const registrationButtonText = <?php echo json_encode(
    ($event['registration_fee'] && $event['registration_fee'] > 0) ? '참가 신청하기' : '참가 신청하기'
); ?>;

// 🔥 Ultra Think: 마감 조건 상태를 JavaScript에서 사용할 수 있도록 전달
const eventRegistrationStatus = <?php
echo json_encode([
    'canRegister' => !$cannotRegister,
    'isOwnEvent' => $isOwnEvent,
    'isCapacityFull' => $isCapacityFull,
    'isEventStarted' => $isEventStarted,
    'isDeadlinePassed' => $isDeadlinePassed,
    'currentCount' => intval($event['current_registration_count'] ?? 0),
    'maxParticipants' => $event['max_participants'] ? intval($event['max_participants']) : null,
    'organizerId' => intval($event['user_id']),
    'eventStartDateTime' => $event['start_date'] . ' ' . $event['start_time'],
    'registrationDeadline' => $event['registration_deadline'] ?? null
], JSON_UNESCAPED_UNICODE);
?>;

// 페이지 로드 시 행사 신청 상태 확인
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($isLoggedIn): ?>
        checkEventRegistrationStatus();
    <?php endif; ?>
});


// JWT 인증 헤더 생성 함수
function getAuthHeaders() {
    const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
    const headers = {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    };
    
    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }
    
    return headers;
}

// 행사 신청 상태 확인
async function checkEventRegistrationStatus() {
    try {
        const response = await fetch(`/api/events/${eventId}/registration-status?event_id=${eventId}`, {
            method: 'GET',
            headers: getAuthHeaders()
        });
        
        const result = await response.json();
        
        if (response.ok && result.status === 'success' && result.data.registration) {
            const registration = result.data.registration;
            updateEventRegistrationUI(registration.status, registration);
        } else if (response.status === 401) {
            console.log('🔐 등록 상태 확인을 위해 로그인이 필요합니다.');
        } else {
            console.log('📊 등록 상태 정보 없음:', result.message || '알 수 없는 오류');
            // 신청 안함 상태로 UI 초기화
            updateEventRegistrationUI('none', null);
        }
    } catch (error) {
        console.error('행사 신청 상태 확인 오류:', error);
        // 오류 시에도 기본 상태로 초기화
        updateEventRegistrationUI('none', null);
    }
}

// 행사 신청 UI 업데이트
function updateEventRegistrationUI(status, registration) {
    const registerBtn = document.getElementById('event-register-btn');
    const cancelBtn = document.getElementById('event-cancel-btn');
    const statusMessage = document.getElementById('event-status-message');
    const statusTitle = document.getElementById('event-status-title');
    const statusDescription = document.getElementById('event-status-description');
    const statusIcon = statusMessage?.querySelector('.status-icon i');
    
    if (!registerBtn || !cancelBtn) return;
    
    // 상태 메시지 초기화
    if (statusMessage) {
        statusMessage.className = 'event-status-message';
        statusMessage.style.display = 'none';
    }
    
    switch (status) {
        case 'pending':
            registerBtn.style.display = 'none';
            cancelBtn.style.setProperty('display', 'block', 'important');
            cancelBtn.textContent = '신청 취소 (승인 대기중)';
            cancelBtn.style.background = '#dc3545';
            
            // 대기 상태 메시지 표시
            showStatusMessage('pending', '🕒', '신청 검토 중입니다', 
                '신청이 접수되었습니다. 승인 결과를 기다려주세요.');
            break;
            
        case 'approved':
            registerBtn.style.display = 'none';
            cancelBtn.style.setProperty('display', 'block', 'important');
            cancelBtn.textContent = '신청 취소 (승인됨)';
            cancelBtn.style.background = '#dc3545';
            
            // 승인 상태 메시지 표시
            const approvedMessage = registration?.admin_notes || '신청이 승인되었습니다. 행사에 참석해주세요.';
            showStatusMessage('approved', '✅', '신청이 승인되었습니다', approvedMessage);
            break;
            
        case 'waiting':
            registerBtn.style.display = 'none';
            cancelBtn.style.setProperty('display', 'block', 'important');
            cancelBtn.textContent = `신청 취소 (대기: ${registration.waiting_order}번)`;
            cancelBtn.style.background = '#dc3545';
            
            // 대기열 상태 메시지 표시
            showStatusMessage('waiting', '⏳', `대기열 ${registration.waiting_order}번입니다`, 
                '정원이 초과되어 대기열에 등록되었습니다. 승인 시 알림을 드리겠습니다.');
            break;
            
        case 'rejected':
            registerBtn.style.display = 'block';
            registerBtn.textContent = '다시 신청하기';
            cancelBtn.style.setProperty('display', 'none', 'important');
            
            // 거절 상태 메시지 표시
            const rejectedMessage = registration?.admin_notes || '신청이 거절되었습니다. 다시 신청하실 수 있습니다.';
            showStatusMessage('rejected', '❌', '신청이 거절되었습니다', rejectedMessage);
            break;
            
        case 'cancelled':
            registerBtn.style.display = 'block';
            registerBtn.textContent = '다시 신청하기';
            cancelBtn.style.setProperty('display', 'none', 'important');
            hideStatusMessage();
            break;

        case 'none':
            // 신청 안함 상태 (기본 상태)
            registerBtn.style.display = 'block';
            registerBtn.textContent = '참가 신청하기';
            cancelBtn.style.setProperty('display', 'none', 'important');
            hideStatusMessage();
            break;

        default:
            registerBtn.style.display = 'block';
            registerBtn.textContent = registrationButtonText;
            cancelBtn.style.setProperty('display', 'none', 'important');
            hideStatusMessage();
    }
    
    // 상태 메시지 표시 함수
    function showStatusMessage(statusClass, iconClass, title, description) {
        if (!statusMessage || !statusTitle || !statusDescription || !statusIcon) return;
        
        statusMessage.className = `event-status-message ${statusClass}`;
        statusMessage.style.display = 'block';
        statusIcon.className = `fas ${getIconClass(iconClass)}`;
        statusTitle.textContent = title;
        statusDescription.textContent = description;
    }
    
    // 상태 메시지 숨김 함수
    function hideStatusMessage() {
        if (statusMessage) {
            statusMessage.style.display = 'none';
        }
    }
    
    // 아이콘 클래스 매핑
    function getIconClass(iconText) {
        const iconMap = {
            '🕒': 'fa-clock',
            '✅': 'fa-check-circle',
            '⏳': 'fa-hourglass-half',
            '❌': 'fa-times-circle'
        };
        return iconMap[iconText] || 'fa-info-circle';
    }
}

// 🔥 Ultra Think: 마감 조건 실시간 검증 함수
function validateEventRegistrationConditions() {
    const now = new Date();

    // 1. 본인 행사 체크
    if (eventRegistrationStatus.isOwnEvent) {
        return {
            canRegister: false,
            message: '본인이 등록한 행사에는 참가 신청할 수 없습니다.',
            type: 'own_event'
        };
    }

    // 2. 정원 초과 체크
    if (eventRegistrationStatus.maxParticipants &&
        eventRegistrationStatus.currentCount >= eventRegistrationStatus.maxParticipants) {
        return {
            canRegister: false,
            message: `정원이 마감되었습니다. (${eventRegistrationStatus.currentCount}/${eventRegistrationStatus.maxParticipants}명)`,
            type: 'capacity_full'
        };
    }

    // 3. 행사 시작일 지남 체크
    const eventStart = new Date(eventRegistrationStatus.eventStartDateTime);
    if (now > eventStart) {
        return {
            canRegister: false,
            message: '행사가 이미 시작되어 참가 신청할 수 없습니다.',
            type: 'event_started'
        };
    }

    // 4. 등록 마감일 체크
    if (eventRegistrationStatus.registrationDeadline) {
        const deadline = new Date(eventRegistrationStatus.registrationDeadline);
        if (now > deadline) {
            return {
                canRegister: false,
                message: '등록 마감일이 지나 참가 신청할 수 없습니다.',
                type: 'deadline_passed'
            };
        }
    }

    return {
        canRegister: true,
        message: '참가 신청이 가능합니다.',
        type: 'available'
    };
}

// 행사 신청 버튼 클릭
async function registerEvent() {
    try {
        // 🔥 Ultra Think: 실시간 마감 조건 검증
        const validation = validateEventRegistrationConditions();

        if (!validation.canRegister) {
            // 마감 조건에 걸린 경우 사용자에게 안내
            const alertMessages = {
                'own_event': '⚠️ 본인이 등록한 행사입니다\n\n자신이 등록한 행사에는 참가 신청할 수 없습니다.',
                'capacity_full': '🈵 정원이 마감되었습니다\n\n취소가 발생하면 선착순으로 신청 가능합니다.',
                'event_started': '⏰ 행사가 이미 시작되었습니다\n\n다른 진행 예정인 행사를 확인해보세요.',
                'deadline_passed': '⏳ 등록 마감일이 지났습니다\n\n다른 진행 예정인 행사를 확인해보세요.'
            };

            Toast.error(alertMessages[validation.type] || validation.message);
            return;
        }

        // 이전 신청 데이터 조회 및 폼 자동 입력
        await loadEventUserInfo();

        // 모달 표시
        document.getElementById('eventRegistrationModal').style.display = 'block';
        document.body.style.overflow = 'hidden';

    } catch (error) {
        console.error('행사 신청 모달 열기 오류:', error);
        Toast.error('행사 신청 준비 중 오류가 발생했습니다.');
    }
}

// 로그인 페이지로 리다이렉트
async function redirectToLogin() {
    if (await Modal.confirm('로그인이 필요합니다. 로그인 페이지로 이동하시겠습니까?')) {
        window.location.href = '/auth/login?redirect=' + encodeURIComponent(window.location.pathname);
    }
}

// 사용자 정보 및 이전 신청 데이터 로드
async function loadEventUserInfo() {
    console.log('📝 행사 신청: 사용자 정보 로드 시작...');

    try {
        // 사용자 정보 가져오기
        const userResponse = await fetch('/auth/me', {
            method: 'GET',
            headers: getAuthHeaders()
        });

        if (userResponse.ok) {
            const userData = await userResponse.json();
            console.log('👤 행사 신청: API 응답 전체:', JSON.stringify(userData, null, 2));
            if (userData.success && userData.user) {
                console.log('✅ 행사 신청: 사용자 정보 자동 입력 시작');
                fillEventUserInfo(userData.user);
            } else {
                console.log('❌ 행사 신청: 사용자 정보 구조 오류', userData);
            }
        } else {
            console.log('❌ 행사 신청: API 요청 실패:', userResponse.status, userResponse.statusText);
        }
        
        // 이전 신청 데이터 가져오기
        const prevResponse = await fetch(`/api/events/${eventId}/previous-registration`, {
            method: 'GET',
            headers: getAuthHeaders()
        });
        
        // 응답 처리 - 401 오류도 정상적으로 처리
        const prevData = await prevResponse.json();
        
        if (prevResponse.ok && prevData.status === 'success' && prevData.data) {
            // 성공적인 응답: 이전 신청 데이터로 폼 채우기
            fillEventRegistrationForm(prevData.data);
        } else if (prevResponse.status === 401 || prevData.message === '로그인이 필요합니다.') {
            // 인증 필요: 정상적인 상황이므로 로그만 출력
            console.log('🔐 인증이 필요합니다. 로그인 후 이전 신청 데이터를 불러올 수 있습니다.');
        } else if (prevData.status === 'success' && prevData.data === null) {
            // 성공적인 응답이지만 데이터가 없는 경우 (정상 상황)
            console.log('📝 이전 신청 데이터 없음:', prevData.message);
        } else if (prevData.status === 'error') {
            // 기타 오류: 자세한 로그 출력
            console.log('📝 이전 신청 데이터 없음:', prevData.message);
        } else {
            // 예상치 못한 응답
            console.warn('⚠️ 예상치 못한 응답:', prevData);
        }
    } catch (error) {
        console.error('사용자 정보 로드 오류:', error);
    }
}

// 사용자 정보로 폼 채우기
function fillEventUserInfo(userData) {
    console.log('🔧 행사 신청: 폼 자동 채우기 실행', userData);

    const nameField = document.getElementById('event_participant_name');
    const emailField = document.getElementById('event_participant_email');
    const phoneField = document.getElementById('event_participant_phone');

    if (nameField) {
        nameField.value = userData.nickname || '';
        console.log('✅ 이름 필드 채움:', userData.nickname);
    } else {
        console.log('❌ 이름 필드 없음: event_participant_name');
    }

    if (emailField) {
        emailField.value = userData.email || '';
        console.log('✅ 이메일 필드 채움:', userData.email);
    } else {
        console.log('❌ 이메일 필드 없음: event_participant_email');
    }

    if (phoneField) {
        phoneField.value = userData.phone || '';
        console.log('✅ 전화번호 필드 채움:', userData.phone);
    } else {
        console.log('❌ 전화번호 필드 없음: event_participant_phone');
    }
}

// 이전 신청 데이터로 폼 채우기
function fillEventRegistrationForm(registrationData) {
    document.getElementById('event_participant_name').value = registrationData.participant_name || '';
    document.getElementById('event_participant_email').value = registrationData.participant_email || '';
    document.getElementById('event_participant_phone').value = registrationData.participant_phone || '';
    document.getElementById('event_company_name').value = registrationData.company_name || '';
    document.getElementById('event_position').value = registrationData.position || '';
    document.getElementById('event_motivation').value = registrationData.motivation || '';
    document.getElementById('event_special_requests').value = registrationData.special_requests || '';
    document.getElementById('event_how_did_you_know').value = registrationData.how_did_you_know || '';
}

// 행사 신청 모달 닫기
function closeEventRegistrationModal() {
    document.getElementById('eventRegistrationModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// 행사 신청 제출
async function submitEventRegistration() {
    try {
        // 🔥 Ultra Think: 제출 전 마감 조건 재검증
        const validation = validateEventRegistrationConditions();

        if (!validation.canRegister) {
            const alertMessages = {
                'own_event': '❌ 본인이 등록한 행사에는 신청할 수 없습니다.',
                'capacity_full': '❌ 정원이 마감되어 신청할 수 없습니다.',
                'event_started': '❌ 행사가 이미 시작되어 신청할 수 없습니다.',
                'deadline_passed': '❌ 등록 마감일이 지나 신청할 수 없습니다.'
            };

            Toast.error(alertMessages[validation.type] || validation.message);
            closeEventRegistrationModal();
            return;
        }

        const form = document.getElementById('eventRegistrationForm');
        const formData = new FormData(form);

        // CSRF 토큰 추가
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        formData.append('csrf_token', csrfToken);
        
        // FormData를 JSON으로 변환
        const data = {};
        formData.forEach((value, key) => {
            data[key] = value;
        });
        
        const response = await fetch(`/api/events/${eventId}/registration?event_id=${eventId}`, {
            method: 'POST',
            headers: getAuthHeaders(),
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            Toast.error('✅ ' + result.message);
            closeEventRegistrationModal();
            
            // UI 업데이트
            if (result.data) {
                updateEventRegistrationUI(result.data.status, result.data);
            }
        } else {
            if (result.data && result.data.errors) {
                let errorMsg = '입력 정보를 확인해주세요:\n';
                for (const field in result.data.errors) {
                    errorMsg += '- ' + result.data.errors[field] + '\n';
                }
                Toast.error(errorMsg);
            } else {
                Toast.error('❌ ' + (result.message || '신청 처리 중 오류가 발생했습니다.'));
            }
        }
    } catch (error) {
        console.error('행사 신청 제출 오류:', error);
        Toast.error('❌ 네트워크 오류가 발생했습니다.');
    }
}

// 행사 신청 취소
async function cancelEventRegistration() {
    if (!(await Modal.confirm('정말로 행사 신청을 취소하시겠습니까?', { type: 'warning' }))) {
        return;
    }
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        const headers = getAuthHeaders();
        headers['X-CSRF-TOKEN'] = csrfToken;
        
        const response = await fetch(`/api/events/${eventId}/registration?event_id=${eventId}`, {
            method: 'DELETE',
            headers: headers,
            body: JSON.stringify({
                csrf_token: csrfToken
            })
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            Toast.error('✅ ' + result.message);
            
            // UI 업데이트
            updateEventRegistrationUI('cancelled', null);
        } else {
            Toast.error('❌ ' + (result.message || '신청 취소 중 오류가 발생했습니다.'));
        }
    } catch (error) {
        console.error('행사 신청 취소 오류:', error);
        Toast.error('❌ 네트워크 오류가 발생했습니다.');
    }
}

// 모달 외부 클릭 시 닫기
document.addEventListener('click', function(e) {
    const modal = document.getElementById('eventRegistrationModal');
    if (e.target === modal) {
        closeEventRegistrationModal();
    }
});

// ESC 키로 모달 닫기
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeEventRegistrationModal();
    }
});

<?php if (!empty($event['images'])): ?>
// 이미지 갤러리 관련 변수
const eventImages = <?= json_encode($event['images']) ?>;
let currentImageIndex = 0;

// 이미지 모달 열기
function openImageModal(index) {
    currentImageIndex = index;
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    const counter = document.getElementById('imageCounter');
    
    modalImage.src = eventImages[currentImageIndex].url;
    modalImage.alt = eventImages[currentImageIndex].alt_text;
    counter.textContent = `${currentImageIndex + 1} / ${eventImages.length}`;
    
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden'; // 배경 스크롤 방지
}

// 이미지 모달 닫기
function closeImageModal() {
    const modal = document.getElementById('imageModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto'; // 스크롤 복원
}

// 이전 이미지
function prevImage() {
    currentImageIndex = (currentImageIndex - 1 + eventImages.length) % eventImages.length;
    updateModalImage();
}

// 다음 이미지
function nextImage() {
    currentImageIndex = (currentImageIndex + 1) % eventImages.length;
    updateModalImage();
}

// 모달 이미지 업데이트
function updateModalImage() {
    const modalImage = document.getElementById('modalImage');
    const counter = document.getElementById('imageCounter');
    
    modalImage.src = eventImages[currentImageIndex].url;
    modalImage.alt = eventImages[currentImageIndex].alt_text;
    counter.textContent = `${currentImageIndex + 1} / ${eventImages.length}`;
}

// 키보드 이벤트
document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('imageModal');
    if (modal.style.display === 'block') {
        switch(e.key) {
            case 'Escape':
                closeImageModal();
                break;
            case 'ArrowLeft':
                prevImage();
                break;
            case 'ArrowRight':
                nextImage();
                break;
        }
    }
});

// 모달 배경 클릭시 닫기
document.getElementById('imageModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeImageModal();
    }
});
<?php endif; ?>

// 행사 이미지 갤러리 함수 별칭 (HTML에서 호출되는 함수명과 일치)
function openEventImageModal(index) {
    openImageModal(index);
}

// 강사 이미지 모달 열기
function openInstructorImageModal(imageSrc, instructorName) {
    console.log('강사 이미지 모달 열기:', imageSrc, instructorName);
    
    const modal = document.getElementById('instructorImageModal');
    const modalImage = document.getElementById('instructorModalImage');
    const modalName = document.getElementById('instructorModalName');
    
    if (!modal || !modalImage || !modalName) {
        console.error('강사 이미지 모달 요소를 찾을 수 없습니다.');
        return;
    }
    
    modalName.textContent = instructorName + ' 강사';
    modalImage.src = imageSrc;
    modalImage.alt = instructorName + ' 강사 이미지';
    
    // 완벽한 중앙정렬을 위한 클래스 적용
    modal.classList.add('show');
    modal.style.setProperty('display', 'flex', 'important');
    modal.style.setProperty('align-items', 'center', 'important');
    modal.style.setProperty('justify-content', 'center', 'important');
    document.body.style.overflow = 'hidden'; // 스크롤 방지
}

// 강사 이미지 모달 닫기
function closeInstructorImageModal() {
    const modal = document.getElementById('instructorImageModal');
    if (modal) {
        modal.classList.remove('show');
        modal.style.setProperty('display', 'none', 'important');
        document.body.style.overflow = 'auto'; // 스크롤 복원
    }
}

// 강사 이미지 모달 이벤트 리스너 (페이지 로드 후 실행)
document.addEventListener('DOMContentLoaded', function() {
    const instructorModal = document.getElementById('instructorImageModal');
    
    if (instructorModal) {
        // 배경 클릭시 닫기
        instructorModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeInstructorImageModal();
            }
        });
        
        // ESC 키로 닫기
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && instructorModal.classList.contains('show')) {
                closeInstructorImageModal();
            }
        });
    }
});

function closeEventImageModal() {
    closeImageModal();
}

function prevEventImage() {
    prevImage();
}

function nextEventImage() {
    nextImage();
}

/**
 * 행사 공유하기 기능
 */
function shareEventContent() {
    try {
        const eventTitle = "<?= addslashes(htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8')) ?>";
        const eventUrl = window.location.href;
        const eventDescription = "<?= addslashes(htmlspecialchars(substr(strip_tags($event['description'] ?? ''), 0, 100))) ?>...";
        
        // Web Share API 지원 확인
        if (navigator.share) {
            navigator.share({
                title: eventTitle,
                text: eventDescription,
                url: eventUrl
            }).then(() => {
                console.log('공유 성공');
            }).catch((error) => {
                console.log('공유 실패:', error);
                fallbackShare(eventTitle, eventUrl);
            });
        } else {
            // 폴백: 클립보드 복사 또는 공유 옵션 표시
            fallbackShare(eventTitle, eventUrl);
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
            Toast.error('🔗 링크가 클립보드에 복사되었습니다!\n다른 곳에 붙여넣기하여 공유하세요.');
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
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    `;
    
    const content = document.createElement('div');
    content.style.cssText = `
        background: white;
        padding: 30px;
        border-radius: 12px;
        max-width: 500px;
        width: 90%;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    `;
    
    content.innerHTML = `
        <h3 style="margin-bottom: 20px; color: #2d3748;">🔗 행사 공유하기</h3>
        <p style="margin-bottom: 20px; color: #4a5568;">${title}</p>
        <div style="background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 20px; word-break: break-all; font-family: monospace; font-size: 14px;">
            ${url}
        </div>
        <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
            <button onclick="copyToClipboard('${url}')" style="padding: 10px 20px; background: #4A90E2; color: white; border: none; border-radius: 6px; cursor: pointer;">
                📋 복사하기
            </button>
            <a href="https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}" target="_blank" style="padding: 10px 20px; background: #4267B2; color: white; text-decoration: none; border-radius: 6px;">
                📘 Facebook
            </a>
            <a href="https://twitter.com/intent/tweet?text=${encodeURIComponent(title)}&url=${encodeURIComponent(url)}" target="_blank" style="padding: 10px 20px; background: #1DA1F2; color: white; text-decoration: none; border-radius: 6px;">
                🐦 Twitter
            </a>
            <a href="https://t.me/share/url?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}" target="_blank" style="padding: 10px 20px; background: #0088CC; color: white; text-decoration: none; border-radius: 6px;">
                📤 Telegram
            </a>
            <button onclick="this.parentElement.parentElement.parentElement.remove()" style="padding: 10px 20px; background: #a0aec0; color: white; border: none; border-radius: 6px; cursor: pointer;">
                닫기
            </button>
        </div>
    `;
    
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
            Toast.error('✅ 링크가 복사되었습니다!');
        });
    } else {
        // 폴백 방법
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        Toast.error('✅ 링크가 복사되었습니다!');
    }
}

// 기존 프로필 이미지 모달 JavaScript 함수들 제거됨 - profile-modal.js 통합 시스템 사용

// 작성자와 채팅 시작
function startChatWithAuthor(authorId, authorName) {
    if (!authorId) {
        Toast.error('작성자 정보를 찾을 수 없습니다.');
        return;
    }
    
    // 채팅 페이지로 이동하면서 해당 사용자와 채팅 시작
    window.location.href = `/chat#user-${authorId}`;
}

// 이벤트 삭제 확인 함수
async function confirmDeleteEvent(eventId) {
    if (!eventId) {
        Toast.error('잘못된 행사 ID입니다.');
        return;
    }

    // 삭제 확인
    const confirmed = await Modal.confirm('⚠️ 정말로 이 행사를 삭제하시겠습니까?\n\n삭제된 행사는 복구할 수 없습니다.', { type: 'danger' });
    
    if (!confirmed) {
        return;
    }

    // 두 번째 확인
    const doubleConfirmed = await Modal.confirm('⚠️ 마지막 확인입니다!\n\n행사 제목: "<?= htmlspecialchars($event['title']) ?>"\n\n정말로 삭제하시겠습니까?', {
        type: 'danger',
        title: '최종 확인',
        confirmText: '삭제',
        cancelText: '취소'
    });

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
    console.log('=== 행사 삭제 디버깅 시작 ===');
    console.log('행사 ID:', eventId);
    console.log('CSRF 토큰:', csrfToken);
    console.log('요청 URL:', `/events/${eventId}/delete`);
    console.log('요청 데이터:', {
        csrf_token: csrfToken,
        confirm_delete: true
    });

    // 삭제 요청
    fetch(`/events/${eventId}/delete`, {
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
        
        if (isSuccess) {
            Toast.success('✅ 행사가 성공적으로 삭제되었습니다.');
            // 이전 페이지로 돌아가기 (또는 행사 목록으로)
            if (document.referrer && document.referrer !== window.location.href) {
                window.location.href = document.referrer;
            } else {
                window.location.href = '/events';
            }
        } else {
            console.error('행사 삭제 실패:', message);
            Toast.error('❌ 행사 삭제에 실패했습니다: ' + message);
            
            // 버튼 상태 복원
            deleteBtn.innerHTML = originalText;
            deleteBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('네트워크 오류:', error);
        Toast.error('❌ 네트워크 오류가 발생했습니다: ' + error.message);
        
        // 버튼 상태 복원
        deleteBtn.innerHTML = originalText;
        deleteBtn.disabled = false;
    });
}
</script>

<!-- edit-check.js 로드 -->
<script src="/assets/js/edit-check.js"></script>