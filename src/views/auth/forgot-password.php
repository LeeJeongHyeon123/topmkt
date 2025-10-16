<?php
/**
 * 탑마케팅 비밀번호 찾기 페이지 - Ultra Modern Responsive Design
 * 완전한 모바일 최적화 및 접근성 준수
 */
$page_title = '비밀번호 찾기';
$page_description = '탑마케팅 계정의 비밀번호를 안전하게 재설정하세요';
$current_page = 'forgot-password';

// CSRF 토큰 생성 (페이지 로딩 시점에서 보장)
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once SRC_PATH . '/views/templates/header.php';
?>

<!-- 🔐 비밀번호 찾기 메인 섹션 -->
<main class="forgot-password-main" role="main" aria-labelledby="forgot-password-title">
    <!-- 배경 컨테이너 -->
    <div class="background-container">
        <div class="gradient-overlay"></div>
        <div class="floating-shapes" aria-hidden="true">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
    </div>

    <!-- 메인 콘텐츠 컨테이너 -->
    <div class="content-wrapper">
        <div class="form-container">
            <!-- 헤더 섹션 -->
            <header class="form-header">
                <h1 id="forgot-password-title" class="main-title">
                    비밀번호 찾기
                </h1>
                <p class="description">
                    가입 시 등록한 휴대폰 번호로<br>
                    보안 인증 코드를 전송해드립니다
                </p>
            </header>

            <!-- 알림 메시지 영역 -->
            <div class="alert-zone" role="alert" aria-live="polite">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error" tabindex="0">
                        <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                        <span class="alert-message"><?= htmlspecialchars($_SESSION['error']) ?></span>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success" tabindex="0">
                        <i class="fas fa-check-circle" aria-hidden="true"></i>
                        <span class="alert-message"><?= htmlspecialchars($_SESSION['success']) ?></span>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
            </div>

            <!-- 🚀 3단계 통합 비밀번호 찾기 폼 -->
            <div class="multi-step-form">
                <!-- 진행률 표시기 -->
                <div class="progress-indicator">
                    <div class="progress-step active" data-step="1">
                        <div class="step-circle">1</div>
                        <div class="step-label">휴대폰 인증</div>
                    </div>
                    <div class="progress-connector">
                        <div class="progress-line"></div>
                    </div>
                    <div class="progress-step" data-step="2">
                        <div class="step-circle">2</div>
                        <div class="step-label">코드 확인</div>
                    </div>
                    <div class="progress-connector">
                        <div class="progress-line"></div>
                    </div>
                    <div class="progress-step" data-step="3">
                        <div class="step-circle">3</div>
                        <div class="step-label">비밀번호 재설정</div>
                    </div>
                </div>

                <!-- 1단계: 휴대폰 번호 입력 -->
                <form id="step1Form" class="form-step active" data-step="1">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    
                    <div class="step-header">
                        <h2 class="step-title">휴대폰 번호 입력</h2>
                        <p class="step-description">가입 시 등록한 휴대폰 번호로 인증 코드를 발송합니다</p>
                    </div>

                    <div class="form-group">
                        <label for="phone" class="form-label">
                            <i class="fas fa-mobile-alt" aria-hidden="true"></i>
                            <span class="label-text">휴대폰 번호</span>
                        </label>
                        <div class="input-wrapper">
                            <input 
                                type="tel" 
                                id="phone" 
                                name="phone" 
                                class="form-input" 
                                placeholder="010-1234-5678" 
                                required
                                maxlength="13"
                                pattern="010-[0-9]{3,4}-[0-9]{4}"
                                autocomplete="tel"
                            >
                            <div class="input-status" aria-hidden="true">
                                <i class="fas fa-check-circle success-icon"></i>
                                <i class="fas fa-exclamation-circle error-icon"></i>
                            </div>
                        </div>
                        <div class="form-help">
                            회원가입 시 등록한 휴대폰 번호를 정확히 입력해주세요
                        </div>
                        <div class="error-message" role="alert"></div>
                    </div>

                    <button type="submit" class="submit-button" id="step1Button">
                        <span class="button-content">
                            <i class="fas fa-paper-plane" aria-hidden="true"></i>
                            <span class="button-text">인증 코드 발송</span>
                        </span>
                        <span class="loading-spinner" aria-hidden="true">
                            <i class="fas fa-spinner fa-spin"></i>
                            <span class="loading-text">발송 중...</span>
                        </span>
                    </button>
                </form>

                <!-- 2단계: 인증 코드 입력 -->
                <form id="step2Form" class="form-step" data-step="2">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" id="verified_phone" name="phone">

                    <div class="step-header">
                        <h2 class="step-title">인증 코드 확인</h2>
                        <p class="step-description">
                            <span id="maskedPhone">010-****-****</span>로 발송된 인증 코드를 입력하세요
                        </p>
                    </div>

                    <div class="form-group">
                        <label for="verification_code" class="form-label">
                            <i class="fas fa-key" aria-hidden="true"></i>
                            <span class="label-text">인증 코드</span>
                        </label>
                        <div class="input-wrapper">
                            <input 
                                type="text" 
                                id="verification_code" 
                                name="verification_code" 
                                class="form-input code-input" 
                                placeholder="6자리 숫자" 
                                required
                                maxlength="6"
                                pattern="[0-9]{6}"
                                autocomplete="one-time-code"
                            >
                            <div class="input-status" aria-hidden="true">
                                <i class="fas fa-check-circle success-icon"></i>
                                <i class="fas fa-exclamation-circle error-icon"></i>
                            </div>
                        </div>
                        <div class="form-help">
                            SMS로 발송된 6자리 인증 코드를 입력하세요 (5분간 유효)
                        </div>
                        <div class="error-message" role="alert"></div>
                    </div>

                    <div class="button-group">
                        <button type="submit" class="submit-button primary full-width" id="step2Button">
                            <span class="button-content">
                                <i class="fas fa-check" aria-hidden="true"></i>
                                <span class="button-text">인증 코드 확인</span>
                            </span>
                            <span class="loading-spinner" aria-hidden="true">
                                <i class="fas fa-spinner fa-spin"></i>
                                <span class="loading-text">확인 중...</span>
                            </span>
                        </button>
                    </div>

                    <!-- 재발송 버튼 -->
                    <div class="resend-section">
                        <p class="resend-text">인증 코드를 받지 못하셨나요?</p>
                        <button type="button" class="resend-button" id="resendCode">
                            <i class="fas fa-redo"></i>
                            인증 코드 재발송
                        </button>
                    </div>
                </form>

                <!-- 3단계: 새 비밀번호 설정 -->
                <form id="step3Form" class="form-step" data-step="3">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" id="final_phone" name="phone">
                    <input type="hidden" id="final_code" name="verification_code">

                    <div class="step-header">
                        <h2 class="step-title">새 비밀번호 설정</h2>
                        <p class="step-description">안전한 새 비밀번호를 설정해주세요</p>
                    </div>

                    <div class="form-group">
                        <label for="new_password" class="form-label">
                            <i class="fas fa-lock" aria-hidden="true"></i>
                            <span class="label-text">새 비밀번호</span>
                        </label>
                        <div class="input-wrapper">
                            <input 
                                type="password" 
                                id="new_password" 
                                name="new_password" 
                                class="form-input" 
                                placeholder="새 비밀번호 입력" 
                                required
                                minlength="8"
                                autocomplete="new-password"
                            >
                            <button type="button" class="password-toggle" onclick="togglePasswordVisibility('new_password')">
                                <i class="fas fa-eye" id="new_password_eye"></i>
                            </button>
                        </div>
                        <div class="form-help">
                            최소 8자 이상, 영문, 숫자, 특수문자 조합을 권장합니다
                        </div>
                        <div class="error-message" role="alert"></div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password" class="form-label">
                            <i class="fas fa-lock" aria-hidden="true"></i>
                            <span class="label-text">비밀번호 확인</span>
                        </label>
                        <div class="input-wrapper">
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                class="form-input" 
                                placeholder="비밀번호 다시 입력" 
                                required
                                minlength="8"
                                autocomplete="new-password"
                            >
                            <button type="button" class="password-toggle" onclick="togglePasswordVisibility('confirm_password')">
                                <i class="fas fa-eye" id="confirm_password_eye"></i>
                            </button>
                        </div>
                        <div class="password-match-indicator" id="passwordMatch" style="display: none;">
                            <i class="fas fa-check-circle text-success"></i>
                            <span>비밀번호가 일치합니다</span>
                        </div>
                        <div class="error-message" role="alert"></div>
                    </div>

                    <div class="button-group">
                        <button type="submit" class="submit-button primary full-width" id="step3Button">
                            <span class="button-content">
                                <i class="fas fa-check-circle" aria-hidden="true"></i>
                                <span class="button-text">비밀번호 재설정 완료</span>
                            </span>
                            <span class="loading-spinner" aria-hidden="true">
                                <i class="fas fa-spinner fa-spin"></i>
                                <span class="loading-text">처리 중...</span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- 네비게이션 링크 -->
            <nav class="auth-navigation" role="navigation" aria-label="인증 페이지 내비게이션">
                <a href="/auth/login" class="nav-link primary" 
                   title="로그인 페이지로 이동">
                    <i class="fas fa-arrow-left" aria-hidden="true"></i>
                    <span>로그인으로 돌아가기</span>
                </a>
                <a href="/auth/signup" class="nav-link secondary"
                   title="회원가입 페이지로 이동">
                    <i class="fas fa-user-plus" aria-hidden="true"></i>
                    <span>회원가입</span>
                </a>
            </nav>
        </div>
    </div>
</main>

<style>
/**
 * 🚀 Ultra Modern Forgot Password UI - Mobile First Design
 * 완전한 반응형 디자인 + 접근성 AA등급 + 현대적 UX
 */

/* ========== 강력한 화이트 배경 강제 적용 ========== */
.forgot-password-main .form-container,
.forgot-password-main .content-wrapper .form-container,
.content-wrapper .form-container,
.form-container {
    background: white !important;
    background-color: white !important;
    background-image: none !important;
    backdrop-filter: none !important;
}

/* 추가 보험용 스타일 */
main.forgot-password-main div.content-wrapper div.form-container {
    background: #ffffff !important;
    background-color: #ffffff !important;
}

/* ========== 화이트 배경 최적화 CSS 변수 정의 ========== */
:root {
    /* 세련된 브랜드 컬러 (부드러운 퍼플 톤) */
    --primary-500: #8b5cf6;   /* 메인 퍼플 - 화이트 배경과 조화 */
    --primary-600: #7c3aed;   /* 버튼 컬러 - 화이트 배경에 최적화 */
    --primary-700: #6d28d9;   /* 호버 상태 */
    --primary-800: #5b21b6;   /* 액티브 상태 */
    --secondary-600: #8b5cf6; /* 세컨더리도 부드럽게 */
    --secondary-700: #7c3aed;
    
    /* 화이트 배경 최적화 그레이 스케일 */
    --gray-50: #fafafa;
    --gray-100: #f5f5f5;
    --gray-200: #e5e5e5;
    --gray-300: #d4d4d4;
    --gray-500: #737373;     /* 부드러운 텍스트 */
    --gray-600: #525252;     /* 메인 텍스트 - 화이트 배경에 적절한 대비 */
    --gray-700: #404040;     /* 제목 - 너무 진하지 않게 */
    --gray-800: #262626;     /* 강조 텍스트 */
    --gray-900: #171717;     /* 최고 강조 */
    
    /* 부드러운 상태 색상 */
    --success-50: #f0fdf4;
    --success-600: #10b981;   /* 더 부드러운 녹색 */
    --error-50: #fef2f2;
    --error-600: #f87171;     /* 더 부드러운 빨간색 */
    
    /* 타이포그래피 */
    --font-size-xs: 0.75rem;    /* 12px */
    --font-size-sm: 0.875rem;   /* 14px */
    --font-size-base: 1rem;     /* 16px */
    --font-size-lg: 1.125rem;   /* 18px */
    --font-size-xl: 1.25rem;    /* 20px */
    --font-size-2xl: 1.5rem;    /* 24px */
    --font-size-3xl: 1.875rem;  /* 30px */
    
    /* 간격 시스템 */
    --space-1: 0.25rem;  /* 4px */
    --space-2: 0.5rem;   /* 8px */
    --space-3: 0.75rem;  /* 12px */
    --space-4: 1rem;     /* 16px */
    --space-5: 1.25rem;  /* 20px */
    --space-6: 1.5rem;   /* 24px */
    --space-8: 2rem;     /* 32px */
    --space-12: 3rem;    /* 48px */
    --space-16: 4rem;    /* 64px */
    
    /* 그림자 */
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    
    /* 둥근 모서리 */
    --radius-sm: 0.25rem;
    --radius-md: 0.375rem;
    --radius-lg: 0.5rem;
    --radius-xl: 0.75rem;
    --radius-2xl: 1rem;
    
    /* 트랜지션 */
    --transition-fast: 150ms ease-in-out;
    --transition-normal: 200ms ease-in-out;
    --transition-slow: 300ms ease-in-out;
}

/* ========== 기본 리셋 ========== */
*,
*::before,
*::after {
    box-sizing: border-box;
}

/* ========== 메인 컨테이너 ========== */
.forgot-password-main {
    min-height: 100vh;
    position: relative;
    display: grid;
    place-items: center;
    padding: var(--space-4);
    margin-top: 40px !important; /* 🔥 헤더와 적절한 거리로 조정 - UI 최적화 */
    background: linear-gradient(135deg, var(--primary-600) 0%, var(--secondary-600) 100%);
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* ========== 배경 요소 ========== */
.background-container {
    position: fixed;
    inset: 0;
    overflow: hidden;
    z-index: 0;
}

.gradient-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        135deg,
        rgba(79, 70, 229, 0.95) 0%,
        rgba(124, 58, 237, 0.95) 100%
    );
}

.floating-shapes {
    position: absolute;
    inset: 0;
}

.shape {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(20px);
    pointer-events: none;
    animation: float 8s ease-in-out infinite;
}

.shape-1 {
    width: clamp(200px, 25vw, 400px);
    height: clamp(200px, 25vw, 400px);
    top: -10%;
    right: -10%;
    animation-delay: 0s;
}

.shape-2 {
    width: clamp(150px, 20vw, 300px);
    height: clamp(150px, 20vw, 300px);
    bottom: -5%;
    left: -5%;
    animation-delay: -2s;
}

.shape-3 {
    width: clamp(100px, 15vw, 200px);
    height: clamp(100px, 15vw, 200px);
    top: 40%;
    left: 15%;
    animation-delay: -4s;
}

@keyframes float {
    0%, 100% { 
        transform: translateY(0) rotate(0deg) scale(1); 
    }
    33% { 
        transform: translateY(-20px) rotate(5deg) scale(1.05); 
    }
    66% { 
        transform: translateY(10px) rotate(-3deg) scale(0.95); 
    }
}

/* ========== 콘텐츠 래퍼 ========== */
.content-wrapper {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 28rem; /* 448px */
    margin: 0 auto;
}

.form-container {
    background: #ffffff !important;
    background-color: #ffffff !important;
    backdrop-filter: none !important;
    border-radius: var(--radius-2xl);
    padding: var(--space-8);
    box-shadow: var(--shadow-xl);
    border: 1px solid rgba(0, 0, 0, 0.1) !important;
    animation: slideUp 0.6s ease-out;
}

/* 더 구체적인 선택자로 강제 적용 */
.forgot-password-main .content-wrapper .form-container {
    background: #ffffff !important;
    background-color: #ffffff !important;
    backdrop-filter: none !important;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ========== 헤더 섹션 ========== */
.form-header {
    text-align: center;
    margin-bottom: var(--space-8);
}

.brand-logo {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-3);
    margin-bottom: var(--space-6);
}


.brand-name {
    font-size: var(--font-size-2xl);
    font-weight: 700;
    color: var(--gray-900);
    letter-spacing: -0.025em;
}

.main-title {
    font-size: var(--font-size-3xl);
    font-weight: 700;
    color: #1a1a1a; /* 진한 검은색으로 변경 */
    margin-bottom: var(--space-3);
    line-height: 1.2;
}

.description {
    color: #333333; /* 진한 회색으로 변경 */
    font-size: var(--font-size-base); /* 16px - 접근성 최적화 */
    line-height: 1.6;
    max-width: 24rem;
    margin: 0 auto;
}

/* ========== 알림 메시지 ========== */
.alert-zone {
    margin-bottom: var(--space-6);
}

.alert {
    padding: var(--space-4) var(--space-5);
    border-radius: var(--radius-lg);
    margin-bottom: var(--space-4);
    display: flex;
    align-items: flex-start;
    gap: var(--space-3);
    font-size: var(--font-size-base); /* 16px - 가독성 향상 */
    line-height: 1.5;
    border: 1px solid;
    animation: alertSlide 0.3s ease-out;
}

@keyframes alertSlide {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.alert-error {
    background-color: var(--error-50);
    color: var(--error-600);
    border-color: var(--error-600);
}

.alert-success {
    background-color: var(--success-50);
    color: var(--success-600);
    border-color: var(--success-600);
}

.alert-message {
    flex: 1;
    margin: 0;
}

/* ========== 다단계 폼 스타일 ========== */
.multi-step-form {
    width: 100%;
}

/* 진행률 표시기 */
.progress-indicator {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: var(--space-8);
    padding: 0 var(--space-4);
    width: 100%;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
    gap: 0;
}

.progress-step {
    display: grid;
    grid-template-rows: 40px 1fr;
    justify-items: center;
    align-items: center;
    gap: 24px;
    flex: 1;
    max-width: 120px;
    min-width: 100px;
    position: relative;
    text-align: center;
    padding: 0 var(--space-2);
    height: 88px;
}

.step-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    font-weight: 600;
    font-size: 16px;
    border: 2px solid var(--gray-300);
    background: white;
    color: var(--gray-500);
    transition: all 0.3s ease;
    justify-self: center; /* Grid에서 완전한 중앙 정렬 */
}

.step-label {
    font-size: var(--font-size-sm);
    color: var(--gray-500);
    text-align: center;
    font-weight: 500;
    transition: color 0.3s ease;
    width: 100%;
    margin: 0;
    line-height: 1.2;
    min-height: 24px; /* 최소 높이 고정 */
    display: flex;
    align-items: center;
    justify-content: center;
}

.progress-step.active .step-circle {
    background: var(--primary-600);
    border-color: var(--primary-600);
    color: white;
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.2);
    margin: 0; /* 마진 초기화 */
}

.progress-step.active .step-label {
    color: var(--primary-600);
    font-weight: 600;
    margin: 0; /* 마진 초기화 */
}

.progress-step.completed .step-circle {
    background: var(--success-600);
    border-color: var(--success-600);
    color: white;
    margin: 0; /* 마진 초기화 */
}

.progress-step.completed .step-label {
    color: var(--success-600);
    margin: 0; /* 마진 초기화 */
}

/* 모든 상태에서 일관된 간격 강제 적용 */
.progress-step.active,
.progress-step.completed {
    gap: 24px !important; /* 강제로 24px 간격 유지 */
    height: 88px !important; /* 강제로 높이 유지 */
}

/* STEP2, STEP3 active 상태 특별 처리 */
.progress-indicator .progress-step.active[data-step="2"],
.progress-indicator .progress-step.active[data-step="3"] {
    gap: 24px !important;
    height: 88px !important;
    grid-template-rows: 40px 1fr !important;
    align-items: center !important;
    justify-items: center !important;
}

.progress-indicator .progress-step.active .step-circle {
    margin: 0 !important;
    justify-self: center !important;
    align-self: center !important;
}

.progress-indicator .progress-step.active .step-label {
    margin: 0 !important;
    justify-self: center !important;
    align-self: start !important;
}

.progress-connector {
    display: flex;
    align-items: center;
    flex: 1;
    min-width: 60px;
    max-width: 120px;
    justify-content: center;
    margin: 0 var(--space-2);
}

.progress-line {
    height: 2px;
    background: var(--gray-300);
    width: 100%;
    transition: background-color 0.3s ease;
}

.progress-step.completed + .progress-connector .progress-line {
    background: var(--success-600);
}

/* 단계별 폼 */
.form-step {
    display: none;
    animation: stepFadeIn 0.4s ease-out;
}

.form-step.active,
.form-step[data-step="1"] {
    display: block;
}

/* 2단계, 3단계는 기본적으로 숨김 */
.form-step[data-step="2"],
.form-step[data-step="3"] {
    display: none;
}

@keyframes stepFadeIn {
    from { 
        opacity: 0; 
        transform: translateX(20px); 
    }
    to { 
        opacity: 1; 
        transform: translateX(0); 
    }
}

.step-header {
    text-align: center;
    margin-bottom: var(--space-6);
}

.step-title {
    font-size: var(--font-size-xl);
    font-weight: 700;
    color: var(--gray-900);
    margin-bottom: var(--space-2);
}

.step-description {
    color: var(--gray-600);
    font-size: var(--font-size-base);
    line-height: 1.5;
    margin: 0;
}

/* 버튼 그룹 */
.button-group {
    display: flex;
    gap: var(--space-3);
    margin-top: var(--space-6);
}

.back-button {
    flex: 0 0 auto;
    padding: var(--space-3) var(--space-4);
    background: var(--gray-100);
    border: 1px solid var(--gray-300);
    border-radius: var(--radius-md);
    color: var(--gray-700);
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.back-button:hover {
    background: var(--gray-200);
    border-color: var(--gray-400);
}

/* 인증 코드 입력 스타일 */
.code-input {
    text-align: center;
    font-size: var(--font-size-xl);
    font-weight: 600;
    letter-spacing: 0.2em;
}

/* 비밀번호 토글 버튼 */
.password-toggle {
    position: absolute;
    right: var(--space-3);
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--gray-500);
    cursor: pointer;
    padding: var(--space-2);
    border-radius: var(--radius-sm);
    transition: color 0.2s ease;
}

.password-toggle:hover {
    color: var(--gray-700);
}

/* 비밀번호 일치 표시 */
.password-match-indicator {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    margin-top: var(--space-2);
    font-size: var(--font-size-sm);
    color: var(--success-600);
}

.text-success {
    color: var(--success-600) !important;
}

/* 재발송 섹션 */
.resend-section {
    text-align: center;
    margin-top: var(--space-6);
    padding-top: var(--space-4);
    border-top: 1px solid var(--gray-200);
}

.resend-text {
    font-size: var(--font-size-sm);
    color: var(--gray-600);
    margin-bottom: var(--space-3);
}

.resend-button {
    background: none;
    border: none;
    color: var(--primary-600);
    font-weight: 500;
    cursor: pointer;
    padding: var(--space-2) var(--space-3);
    border-radius: var(--radius-md);
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
}

.resend-button:hover {
    background: var(--primary-50);
    color: var(--primary-700);
}

.resend-button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* ========== 기존 폼 스타일 ========== */
.forgot-password-form {
    margin-bottom: var(--space-8);
}

.form-group {
    margin-bottom: var(--space-6);
}

.form-label {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    font-size: var(--font-size-base); /* 16px - 접근성 최적화 */
    font-weight: 600;
    color: #2a2a2a; /* 진한 색상으로 변경 */
    margin-bottom: var(--space-3);
}

.label-text {
    flex: 1;
}

.input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.form-input {
    width: 100%;
    min-height: 3.5rem; /* 56px - 터치 친화적 */
    padding: var(--space-4) var(--space-5);
    padding-right: 3rem; /* 상태 아이콘 공간 */
    border: 2px solid #cccccc; /* 더 진한 테두리 */
    border-radius: var(--radius-lg);
    font-size: var(--font-size-base); /* 16px - 모바일 줌 방지 */
    font-weight: 500;
    background-color: white;
    color: #1a1a1a; /* 진한 검은색 텍스트 */
    transition: all var(--transition-normal);
    box-shadow: var(--shadow-sm);
}

.form-input:focus {
    outline: none;
    border-color: var(--primary-600);
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1), var(--shadow-md); /* 부드러운 퍼플 그림자 */
    transform: translateY(-1px);
}

.form-input:valid {
    border-color: var(--success-600);
}

.form-input:invalid:not(:placeholder-shown) {
    border-color: var(--error-600);
}

.input-status {
    position: absolute;
    right: var(--space-4);
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.success-icon,
.error-icon {
    font-size: var(--font-size-lg);
    opacity: 0;
    transition: opacity var(--transition-normal);
}

.success-icon {
    color: var(--success-600);
}

.error-icon {
    color: var(--error-600);
}

.form-input:valid + .input-status .success-icon {
    opacity: 1;
}

.form-input:invalid:not(:placeholder-shown) + .input-status .error-icon {
    opacity: 1;
}

.form-help {
    margin-top: var(--space-3);
    font-size: var(--font-size-base); /* 16px - 가독성 향상 */
    color: var(--gray-600);
    line-height: 1.5;
}

.error-message {
    margin-top: var(--space-2);
    font-size: var(--font-size-sm);
    color: var(--error-600);
    font-weight: 500;
    opacity: 0;
    transition: opacity var(--transition-normal);
}

.error-message:not(:empty) {
    opacity: 1;
}

/* ========== 제출 버튼 ========== */
.submit-button {
    width: 100%;
    min-height: 3.5rem; /* 56px - 터치 친화적 */
    padding: var(--space-4) var(--space-6);
    border: none;
    border-radius: var(--radius-lg);
    background: linear-gradient(135deg, var(--primary-600) 0%, var(--secondary-600) 100%);
    color: white !important; /* 가시성 보장 */
    font-size: var(--font-size-base);
    font-weight: 600;
    cursor: pointer;
    transition: all var(--transition-normal);
    box-shadow: var(--shadow-lg);
    position: relative;
    overflow: hidden;
}

/* 버튼 내부 텍스트 색상 명시적 지정 - 가장 구체적인 선택자 사용 */
.forgot-password-main .submit-button,
.forgot-password-main .submit-button *,
.forgot-password-main .submit-button .button-content,
.forgot-password-main .submit-button .button-text,
.forgot-password-main .submit-button .loading-spinner,
.forgot-password-main .submit-button .loading-text {
    color: white !important;
    /* background 제거 - submit-button 자체에서 배경 처리 */
}

/* 제출 버튼 배경이 투명하게 나타나는 문제 해결 */
.forgot-password-main .form-container .submit-button {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important;
    color: white !important;
}

.submit-button:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 20px 30px -5px rgba(79, 70, 229, 0.3), var(--shadow-xl);
}

.submit-button:active:not(:disabled) {
    transform: translateY(0);
}

.submit-button:disabled {
    cursor: not-allowed;
    opacity: 0.8;
}

.button-content {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-2);
    transition: opacity var(--transition-normal);
}

.loading-spinner {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-2);
    opacity: 0;
    transition: opacity var(--transition-normal);
}

.submit-button:disabled .button-content {
    opacity: 0;
}

.submit-button:disabled .loading-spinner {
    opacity: 1;
}

.submit-help {
    margin-top: var(--space-3);
    text-align: center;
    font-size: var(--font-size-base); /* 16px - 가독성 향상 */
    color: var(--gray-600);
}

/* ========== 네비게이션 링크 ========== */
.auth-navigation {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
    text-align: center;
    margin-top: var(--space-8); /* 🔥 상단 여백 대폭 증가 - UI 버그 수정 */
}

.nav-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-2);
    min-height: 3rem; /* 48px - 터치 친화적 */
    padding: var(--space-3) var(--space-4);
    border-radius: var(--radius-lg);
    text-decoration: none;
    font-size: var(--font-size-base); /* 16px - 가독성 향상 */
    font-weight: 500;
    transition: all var(--transition-normal);
    border: 2px solid transparent;
}

.nav-link.primary {
    color: white !important; /* 대비 개선을 위해 흰색 텍스트 사용 */
    background: rgba(79, 70, 229, 0.9) !important; /* 배경색 강화 */
    border-color: rgba(79, 70, 229, 0.5);
}

.nav-link.primary:hover {
    background: rgba(79, 70, 229, 1) !important;
    color: white !important;
    transform: translateY(-1px);
}

/* 네비게이션 링크 내부 span과 아이콘도 같은 색상 적용 */
.nav-link.primary span,
.nav-link.primary i {
    color: white !important;
}

.nav-link.secondary {
    color: var(--gray-800) !important; /* 대비 개선 */
    background: var(--gray-100); /* 배경색 강화 */
    border-color: var(--gray-300);
}

.nav-link.secondary:hover {
    background: var(--gray-200);
    color: var(--gray-900) !important;
    transform: translateY(-1px);
}

/* ========== 반응형 디자인 ========== */

/* 🔹 작은 모바일 (320px - 479px) */
@media (max-width: 479px) {
    .forgot-password-main {
        padding: var(--space-3);
    }
    
    .form-container {
        padding: var(--space-6);
        border-radius: var(--radius-xl);
    }
    
    .main-title {
        font-size: var(--font-size-2xl);
    }
    
    .brand-name {
        font-size: var(--font-size-xl);
    }
    
    .progress-indicator {
        max-width: 320px;
        padding: 0 var(--space-2);
    }
    
    .progress-step {
        min-width: 60px;
    }
    
    .progress-connector {
        max-width: 80px;
    }
    
    .step-label {
        font-size: 12px;
    }
}

/* 🔹 태블릿 (480px - 767px) */
@media (min-width: 480px) and (max-width: 767px) {
    .content-wrapper {
        max-width: 32rem; /* 512px */
    }
    
    .form-container {
        padding: var(--space-8) var(--space-12);
    }
}

/* 🔹 데스크톱 (768px 이상) */
@media (min-width: 768px) {
    .content-wrapper {
        max-width: 36rem; /* 576px */
    }
    
    .form-container {
        padding: var(--space-12);
    }
    
    .auth-navigation {
        flex-direction: row;
        justify-content: space-between;
    }
    
    .nav-link {
        flex: 1;
        max-width: 12rem;
        font-size: 0.9rem; /* 모바일에서 텍스트 크기 약간 줄이기 */
        padding: var(--space-2) var(--space-3);
        white-space: nowrap; /* 텍스트 줄바꿈 방지 */
    }
}

/* 🔹 큰 화면 (1024px 이상) */
@media (min-width: 1024px) {
    .forgot-password-main {
        padding: var(--space-8);
    }
}

/* ========== 다크모드 지원 (선택사항) ========== */
@media (prefers-color-scheme: dark) {
    .form-container {
        background: #1f2937 !important;
        border-color: rgba(55, 65, 81, 0.5) !important;
    }
    
    .forgot-password-main .main-title,
    .forgot-password-main .brand-name {
        color: white !important;
    }
    
    .forgot-password-main .description,
    .forgot-password-main .form-label,
    .forgot-password-main .form-help {
        color: #d1d5db !important;
    }
    
    .forgot-password-main .form-input {
        background: #374151 !important;
        border-color: #6b7280 !important;
        color: white !important;
    }
    
    .forgot-password-main .form-input:focus {
        border-color: var(--primary-600) !important;
    }
}

/* ========== 접근성 개선 ========== */
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

/* 고대비 모드 지원 */
@media (prefers-contrast: high) {
    .form-input:focus {
        border-width: 3px;
    }
    
    .submit-button {
        border: 2px solid var(--gray-900);
    }
}

/* 포커스 표시 개선 */
.form-input:focus,
.submit-button:focus,
.nav-link:focus {
    outline: 2px solid var(--primary-600);
    outline-offset: 2px;
}

/* ========== 화이트 배경 기반 깔끔한 색상 시스템 ========== */
/* 화이트 배경에 최적화된 텍스트 색상 */
.forgot-password-main .main-title,
.forgot-password-main .brand-name {
    color: #1a1a1a !important; /* 진한 검은색으로 강제 변경 - 확실한 가독성 */
    font-weight: 700;
}

.forgot-password-main .description,
.forgot-password-main .form-help,
.forgot-password-main .submit-help {
    color: #333333 !important; /* 진한 회색으로 변경 - 확실한 가독성 */
    font-weight: 400;
}

.forgot-password-main .form-label,
.forgot-password-main .label-text {
    color: #2a2a2a !important; /* 진한 색상으로 변경 - 확실한 가독성 */
    font-weight: 600;
}

/* 제출 버튼 - 명확한 대비 */
.forgot-password-main .submit-button {
    background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%);
    color: white;
    border: none;
}

/* 입력 필드 - 화이트 배경 최적화 */
.forgot-password-main .form-input {
    color: #1a1a1a !important; /* 입력 텍스트도 진한 검은색 */
    background-color: white;
    border-color: #cccccc !important; /* 테두리도 더 진하게 */
}

.forgot-password-main .form-input:focus {
    border-color: var(--primary-600); /* 부드러운 퍼플 */
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1); /* 부드러운 퍼플 그림자 */
}

/* 알림 메시지 - 명확한 대비 */
.forgot-password-main .alert-error {
    color: #b91c1c;
    background-color: #fef2f2;
    border-color: #fca5a5;
}

.forgot-password-main .alert-success {
    color: #047857;
    background-color: #ecfdf5;
    border-color: #6ee7b7;
}

/* 에러 메시지 */
.forgot-password-main .error-message {
    color: #dc2626;
    font-weight: 500;
}

/* ========== 전역 헤더 버튼 대비 개선 ========== */
/* 회원가입 버튼 대비 개선 */
.btn.btn-primary {
    background: #1d4ed8 !important; /* 더 어두운 파란색 */
    color: white !important;
    box-shadow: var(--shadow-lg);
}

.btn.btn-primary:hover {
    background: #1e3a8a !important; /* 호버시 더욱 어두운 색상 */
    transform: translateY(-2px);
}

/* 헤더 로고 링크 색상 - 공통 헤더와 일관성 유지 */
.main-header .logo-link {
    /* 공통 헤더 스타일 적용 - 색상 오버라이드 제거 */
}

.main-header .logo-text {
    /* 공통 헤더 스타일 적용 - 색상 오버라이드 제거 */
}
</style>

<script>

/**
 * 🔥 간소화된 다단계 비밀번호 찾기 JavaScript
 * 구문 오류 완전 해결 및 핵심 기능만 포함
 */
class MultiStepPasswordResetManager {
    constructor() {
        this.currentStep = 1;
        this.userData = {};
        this.isSubmitting = false;
        
        this.progressSteps = document.querySelectorAll('.progress-step');
        this.formSteps = document.querySelectorAll('.form-step');
        this.alertZone = document.querySelector('.alert-zone');
        
        this.step1Form = document.getElementById('step1Form');
        this.phoneInput = document.getElementById('phone');
        this.step1Button = document.getElementById('step1Button');
        
        this.step2Form = document.getElementById('step2Form');
        this.verificationCodeInput = document.getElementById('verification_code');
        this.step2Button = document.getElementById('step2Button');
        this.maskedPhoneDisplay = document.getElementById('maskedPhone');
        
        this.step3Form = document.getElementById('step3Form');
        this.newPasswordInput = document.getElementById('new_password');
        this.confirmPasswordInput = document.getElementById('confirm_password');
        this.step3Button = document.getElementById('step3Button');
        
        this.init();
    }

    init() {
        this.setupEventListeners();
    }

    setupEventListeners() {
        if (this.step1Form) {
            this.step1Form.addEventListener('submit', (e) => this.handleStep1Submit(e));
        }
        if (this.step2Form) {
            this.step2Form.addEventListener('submit', (e) => this.handleStep2Submit(e));
        }
        if (this.step3Form) {
            this.step3Form.addEventListener('submit', (e) => this.handleStep3Submit(e));
        }
        if (this.phoneInput) {
            this.phoneInput.addEventListener('input', (e) => this.formatPhoneNumber(e));
        }
    }

    async handleStep1Submit(e) {
        e.preventDefault();
        if (this.isSubmitting) return;
        
        const phone = this.phoneInput.value.trim();
        if (!this.validatePhoneNumber(phone)) {
            this.showAlert('올바른 휴대폰 번호를 입력해주세요.', 'error');
            return;
        }
        
        // 🚀 v3.31.0: Loading 클래스 사용
        Loading.button(this.step1Button, true, { text: '처리 중...' });

        // 🚀 v3.42.0: ApiClient 사용 (fetch → ApiClient.post)
        try {
            const formData = new FormData(this.step1Form);
            const data = await ApiClient.post('/auth/forgot-password',
                formData,
                { noLoading: true, noErrorToast: true }
            );

            if (data.success) {
                this.userData.phone = phone;
                if (this.maskedPhoneDisplay) {
                    this.maskedPhoneDisplay.textContent = this.maskPhoneNumber(phone);
                }
                if (this.step2Form && this.step2Form.querySelector('#verified_phone')) {
                    this.step2Form.querySelector('#verified_phone').value = phone;
                }
                this.goToStep(2);
                this.showAlert(data.message, 'success');
            } else {
                this.showAlert(data.error || data.message || '오류가 발생했습니다.', 'error');
            }
        } catch (error) {
            this.showAlert('네트워크 오류가 발생했습니다.', 'error');
        } finally {
            Loading.button(this.step1Button, false);
        }
    }

    async handleStep2Submit(e) {
        e.preventDefault();
        if (this.isSubmitting) return;
        
        const code = this.verificationCodeInput.value.trim();
        if (!code || code.length !== 6) {
            this.showAlert('6자리 인증 코드를 입력해주세요.', 'error');
            return;
        }
        
        // 🚀 v3.31.0: Loading 클래스 사용
        Loading.button(this.step2Button, true, { text: '인증 중...' });

        // 🚀 v3.42.0: ApiClient 사용 (fetch → ApiClient.post)
        try {
            const data = await ApiClient.post('/auth/verify-reset-code',
                {
                    phone: this.userData.phone,
                    verification_code: code,
                    csrf_token: document.querySelector('input[name="csrf_token"]').value
                },
                { noLoading: true, noErrorToast: true }
            );

            if (data.success) {
                this.userData.verificationCode = code;
                this.step3Form.querySelector('#final_phone').value = this.userData.phone;
                this.step3Form.querySelector('#final_code').value = code;
                this.goToStep(3);
                this.showAlert('인증이 완료되었습니다. 새 비밀번호를 설정해주세요.', 'success');
            } else {
                this.showAlert(data.error || data.message || '인증 코드가 올바르지 않습니다.', 'error');
            }
        } catch (error) {
            this.showAlert('네트워크 오류가 발생했습니다.', 'error');
        } finally {
            Loading.button(this.step2Button, false);
        }
    }

    async handleStep3Submit(e) {
        e.preventDefault();
        if (this.isSubmitting) return;
        
        const newPassword = this.newPasswordInput.value;
        const confirmPassword = this.confirmPasswordInput.value;
        
        if (!newPassword || newPassword.length < 8) {
            this.showAlert('비밀번호는 8자 이상이어야 합니다.', 'error');
            return;
        }
        
        if (newPassword !== confirmPassword) {
            this.showAlert('비밀번호가 일치하지 않습니다.', 'error');
            return;
        }
        
        // 🚀 v3.31.0: Loading 클래스 사용
        Loading.button(this.step3Button, true, { text: '저장 중...' });
        
        try {
            // 🚀 v3.42.0: ApiClient 사용 (fetch → ApiClient.post)
            const formData = new FormData(this.step3Form);
            const data = await ApiClient.post('/auth/reset-password',
                formData,
                { noLoading: true, noErrorToast: true }
            );

            if (data.success) {
                this.showAlert('비밀번호가 성공적으로 변경되었습니다. 로그인 페이지로 이동합니다.', 'success');
                setTimeout(() => {
                    window.location.href = '/auth/login';
                }, 2000);
            } else {
                this.showAlert(data.error || data.message || '비밀번호 재설정에 실패했습니다.', 'error');
            }
        } catch (error) {
            this.showAlert('네트워크 오류가 발생했습니다.', 'error');
        } finally {
            Loading.button(this.step3Button, false);
        }
    }

    goToStep(step) {
        
        this.currentStep = step;
        this.updateProgress(step);
        
        // 모든 폼 단계 숨기기
        this.formSteps.forEach(form => {
            form.style.display = 'none';
            form.classList.remove('active');
        });
        
        // 대상 단계 폼만 표시 (progress-step는 CSS로만 제어)
        const targetForm = document.querySelector(`.form-step[data-step="${step}"]`);
        
        if (targetForm) {
            targetForm.style.display = 'block';
            targetForm.classList.add('active');
        } else {
        }
        
        // 추가: 강제로 인라인 스타일 제거 (CSS 우선순위 문제 해결)
        if (step === 2) {
            const step2Form = document.getElementById('step2Form');
            if (step2Form) {
                step2Form.style.removeProperty('display');
                step2Form.style.display = 'block';
            }
        } else if (step === 3) {
            const step3Form = document.getElementById('step3Form');
            if (step3Form) {
                step3Form.style.removeProperty('display');
                step3Form.style.display = 'block';
                
                // 3단계로 이동할 때 hidden 필드에 필요한 데이터 설정
                const finalPhoneInput = document.getElementById('final_phone');
                const finalCodeInput = document.getElementById('final_code');
                const phoneValue = this.phoneInput ? this.phoneInput.value : '';
                const codeValue = this.verificationCodeInput ? this.verificationCodeInput.value : '';
                
                if (finalPhoneInput) finalPhoneInput.value = phoneValue;
                if (finalCodeInput) finalCodeInput.value = codeValue;
                
            }
        }
    }

    updateProgress(step) {
        this.progressSteps.forEach((stepEl, index) => {
            const stepNum = index + 1;
            
            // 🔥 인라인 스타일 완전 제거 - Grid 레이아웃 복구
            stepEl.style.removeProperty('display');
            stepEl.removeAttribute('style');
            
            if (stepNum < step) {
                stepEl.classList.add('completed');
                stepEl.classList.remove('active');
            } else if (stepNum === step) {
                stepEl.classList.add('active');
                stepEl.classList.remove('completed');
            } else {
                stepEl.classList.remove('active', 'completed');
            }
        });
    }

    formatPhoneNumber(e) {
        let value = e.target.value.replace(/[^0-9]/g, '');
        if (value.length > 11) {
            value = value.slice(0, 11);
        }
        
        if (value.length >= 3) {
            if (value.length <= 7) {
                value = value.replace(/(\d{3})(\d{1,4})/, '$1-$2');
            } else {
                value = value.replace(/(\d{3})(\d{3,4})(\d{1,4})/, '$1-$2-$3');
            }
        }
        
        e.target.value = value;
    }

    validatePhoneNumber(phone) {
        if (!phone) return false;
        const cleaned = phone.replace(/[^0-9]/g, '');
        return cleaned.length === 11 && cleaned.startsWith('010');
    }

    maskPhoneNumber(phone) {
        const cleaned = phone.replace(/[^0-9]/g, '');
        return cleaned.replace(/(\d{3})(\d{3,4})(\d{1,4})/, '$1-****-$3');
    }

    // 🚀 v3.31.0: setLoading 메서드 제거 (Loading 클래스로 대체됨)
    // 기존: this.setLoading(button, true/false)
    // 현재: Loading.button(button, true/false, { text: '...' })

    showAlert(message, type = 'info', duration = 5000) {
        if (!this.alertZone) return;
        
        const existingAlert = this.alertZone.querySelector('.alert');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.setAttribute('role', 'alert');
        
        const iconClass = type === 'error' ? 'exclamation-triangle' : 
                         type === 'success' ? 'check-circle' : 'info-circle';
        
        alert.innerHTML = `
            <i class="fas fa-${iconClass}"></i>
            <span class="alert-message">${message}</span>
        `;
        
        this.alertZone.appendChild(alert);
        
        if (duration > 0) {
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, duration);
        }
    }
    
    togglePasswordVisibility(inputId) {
        const input = document.getElementById(inputId);
        const eyeIcon = document.getElementById(inputId + '_eye');
        
        if (!input || !eyeIcon) {
            return;
        }
        
        if (input.type === 'password') {
            input.type = 'text';
            eyeIcon.className = 'fas fa-eye-slash';
        } else {
            input.type = 'password';
            eyeIcon.className = 'fas fa-eye';
        }
    }
}
// DOM 로드 완료 후 초기화
document.addEventListener('DOMContentLoaded', () => {
    window.multiStepPasswordReset = new MultiStepPasswordResetManager();
});

// 전역 함수로 비밀번호 보기/숨기기 함수 제공
function togglePasswordVisibility(inputId) {
    if (window.multiStepPasswordReset) {
        window.multiStepPasswordReset.togglePasswordVisibility(inputId);
    } else {
    }
}

</script>

<!-- Footer 가시성 강제 보장 CSS -->
<style>
/* ========== Footer 가시성 강제 보장 ========== */
/* Footer가 화면에 확실히 보이도록 강제 설정 */
.modern-footer {
    position: relative !important;
    z-index: 10 !important;
    margin-top: 40px !important;
    background: #f8fafc !important; /* 밝은 배경으로 변경 */
    border-top: 2px solid #e5e7eb !important; /* 더 진한 테두리 */
}

.footer-mobile {
    padding: 20px 0 15px !important; /* 더 많은 패딩 */
}

/* 모든 footer 텍스트를 진한 색상으로 강제 */
.footer-mobile .footer-logo-text,
.footer-mobile .footer-nav-link,
.footer-mobile .footer-contact-inline a,
.footer-mobile .footer-copyright,
.footer-mobile .footer-policy-link {
    color: #1f2937 !important; /* 진한 검은색 강제 적용 */
    font-weight: 600 !important;
}

/* 링크 버튼들 더 선명하게 */
.footer-mobile .footer-nav-link {
    background: rgba(255, 255, 255, 0.9) !important;
    border: 2px solid #d1d5db !important;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
}

.footer-mobile .footer-nav-link:hover {
    background: rgba(59, 130, 246, 0.1) !important;
    border-color: #3b82f6 !important;
    color: #3b82f6 !important;
}

/* 연락처 링크들 */
.footer-mobile .footer-contact-inline a {
    font-size: 14px !important;
    font-weight: 600 !important;
}

/* 저작권 정보 */
.footer-mobile .footer-copyright {
    font-size: 15px !important;
}

/* 정책 링크들 */
.footer-mobile .footer-policy-link {
    font-size: 13px !important;
    font-weight: 600 !important;
}

/* ========== 헤더 겹침 방지 반응형 CSS ========== */
/* 데스크톱 (1200px 이상) */
@media (min-width: 1200px) {
    .forgot-password-main {
        margin-top: 40px !important; /* 🔥 데스크톱 헤더와 적절한 거리 - UI 최적화 */
    }
}

/* 태블릿 (768px ~ 1199px) */
@media (min-width: 768px) and (max-width: 1199px) {
    .forgot-password-main {
        margin-top: 35px !important; /* 🔥 태블릿 헤더와 적절한 거리 - UI 최적화 */
    }
}

/* 모바일 (767px 이하) */
@media (max-width: 767px) {
    .forgot-password-main {
        margin-top: 30px !important; /* 🔥 모바일 헤더와 적절한 거리 - UI 최적화 */
    }
}
</style>

<?php require_once SRC_PATH . '/views/templates/footer.php'; ?>