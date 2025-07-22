<?php
/**
 * 탑마케팅 회원가입 페이지
 */
$page_title = '회원가입';
$page_description = '탑마케팅에 가입하여 글로벌 네트워크 마케팅 커뮤니티에 참여하세요';
$current_page = 'signup';

require_once SRC_PATH . '/views/templates/header.php';
?>

<!-- reCAPTCHA v3 스크립트 -->
<script src="https://www.google.com/recaptcha/api.js?render=6LfViDErAAAAAMcOf3D-JxEhisMDhzLhEDYEahZb"></script>

<!-- 회원가입 페이지 -->
<section class="auth-section">
    <div class="auth-background">
        <div class="auth-gradient-overlay"></div>
        <div class="auth-shapes">
            <div class="auth-shape auth-shape-1"></div>
            <div class="auth-shape auth-shape-2"></div>
            <div class="auth-shape auth-shape-3"></div>
        </div>
    </div>

    <div class="container">
        <div class="auth-content">
            <!-- 회원가입 폼 컨테이너 -->
            <div class="auth-form-container">
                <!-- 로고 및 제목 -->
                <div class="auth-header">
                    <div class="auth-logo">
                        <div class="logo-icon">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <span class="logo-text"><?= APP_NAME ?? '탑마케팅' ?></span>
                    </div>
                    <h1 class="auth-title">새로운 여정을 시작하세요</h1>
                    <p class="auth-subtitle">글로벌 네트워크 마케팅 커뮤니티에 가입하여 성공을 함께 만들어가세요</p>
                </div>

                <!-- 회원가입 폼 -->
                <form class="auth-form" method="POST" action="/auth/signup" id="signup-form">
                    <div class="form-group">
                        <label for="nickname" class="form-label">
                            <i class="fas fa-user"></i>
                            닉네임 <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="nickname" 
                            name="nickname" 
                            class="form-input" 
                            placeholder="닉네임을 입력하세요 (2-20자)"
                            value="<?= htmlspecialchars($_POST['nickname'] ?? '') ?>"
                            required 
                            autocomplete="username"
                            maxlength="20"
                            minlength="2"
                        >
                        <small class="form-help">한글, 영문, 숫자를 사용하여 2-20자로 입력하세요</small>
                    </div>

                    <div class="form-group">
                        <label for="phone" class="form-label">
                            <i class="fas fa-mobile-alt"></i>
                            휴대폰 번호 <span class="required">*</span>
                        </label>
                        <div class="phone-verification-group">
                            <input 
                                type="tel" 
                                id="phone" 
                                name="phone" 
                                class="form-input phone-input" 
                                placeholder="010-1234-5678"
                                value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                                required 
                                autocomplete="tel"
                                pattern="010-[0-9]{3,4}-[0-9]{4}"
                                maxlength="13"
                            >
                            <button type="button" id="send-verification-btn" class="btn btn-outline-primary">
                                인증번호 발송
                            </button>
                        </div>
                        <small class="form-help">010으로 시작하는 휴대폰 번호를 입력하세요 (로그인 시 사용됩니다)</small>
                    </div>

                    <!-- 인증번호 입력 필드 -->
                    <div class="form-group" id="verification-group" style="display: none;">
                        <label for="verification_code" class="form-label">
                            <i class="fas fa-shield-alt"></i>
                            인증번호 <span class="required">*</span>
                        </label>
                        <div class="verification-input-group">
                            <input 
                                type="text" 
                                id="verification_code" 
                                name="verification_code" 
                                class="form-input verification-input" 
                                placeholder="4자리 인증번호 입력"
                                maxlength="4"
                                pattern="[0-9]{4}"
                            >
                            <button type="button" id="verify-code-btn" class="btn btn-success">
                                확인
                            </button>
                            <div id="timer-display" class="timer-display">03:00</div>
                        </div>
                        <small class="form-help">
                            <span id="verification-help">휴대폰으로 전송된 4자리 인증번호를 입력하세요</span>
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope"></i>
                            이메일 <span class="required">*</span>
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-input" 
                            placeholder="example@email.com"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            required 
                            autocomplete="email"
                        >
                        <small class="form-help">계정 복구 및 중요한 알림을 받기 위해 사용됩니다 (필수)</small>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i>
                            비밀번호 <span class="required">*</span>
                        </label>
                        <div class="password-input-wrapper">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-input" 
                                placeholder="비밀번호를 입력하세요 (8자 이상)"
                                required 
                                autocomplete="new-password"
                                minlength="8"
                            >
                            <button type="button" class="password-toggle" id="password-toggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <small class="form-help">영문, 숫자, 특수문자를 포함하여 8자 이상 입력하세요</small>
                    </div>

                    <div class="form-group">
                        <label for="password_confirm" class="form-label">
                            <i class="fas fa-lock"></i>
                            비밀번호 확인 <span class="required">*</span>
                        </label>
                        <div class="password-input-wrapper">
                            <input 
                                type="password" 
                                id="password_confirm" 
                                name="password_confirm" 
                                class="form-input" 
                                placeholder="비밀번호를 다시 입력하세요"
                                required 
                                autocomplete="new-password"
                                minlength="8"
                            >
                            <button type="button" class="password-toggle" id="password-confirm-toggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" name="terms" value="1" required>
                            <span class="checkbox-custom"></span>
                            <span class="checkbox-text">
                                <a href="/terms" target="_blank">이용약관</a> 및 
                                <a href="/privacy" target="_blank">개인정보처리방침</a>에 동의합니다 <span class="required">*</span>
                            </span>
                        </label>
                    </div>

                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" name="marketing" value="1">
                            <span class="checkbox-custom"></span>
                            <span class="checkbox-text">마케팅 정보 수신에 동의합니다 (선택)</span>
                        </label>
                    </div>

                    <!-- 숨겨진 필드들 -->
                    <input type="hidden" id="phone_verified" name="phone_verified" value="0">
                    <input type="hidden" id="csrf_token" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" id="recaptcha_token" name="recaptcha_token" value="">

                    <button type="submit" class="btn btn-primary-gradient btn-large btn-full" id="signup-btn" disabled>
                        <i class="fas fa-user-plus"></i>
                        <span>회원가입</span>
                    </button>
                    
                    <div class="recaptcha-notice">
                        <i class="fas fa-shield-alt"></i>
                        이 사이트는 reCAPTCHA로 보호되며, Google의 
                        <a href="https://policies.google.com/privacy" target="_blank">개인정보처리방침</a>과 
                        <a href="https://policies.google.com/terms" target="_blank">서비스 약관</a>이 적용됩니다.
                    </div>
                </form>

                <!-- 로그인 링크 -->
                <div class="auth-footer">
                    <p class="auth-switch">
                        이미 계정이 있으신가요? 
                        <a href="/auth/login" class="auth-link">
                            로그인하기
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </p>
                </div>
            </div>

            <!-- 사이드 정보 -->
            <div class="auth-side-info">
                <div class="side-info-content">
                    <div class="side-info-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h2>성공의 시작</h2>
                    <p>전 세계 네트워크 마케팅 전문가들과 함께 새로운 기회를 발견하고 성장하세요</p>
                    
                    <div class="info-stats">
                        <div class="info-stat">
                            <div class="stat-number">10,000+</div>
                            <div class="stat-label">글로벌 멤버</div>
                        </div>
                        <div class="info-stat">
                            <div class="stat-number">24/7</div>
                            <div class="stat-label">언제든지 소통</div>
                        </div>
                        <div class="info-stat">
                            <div class="stat-number">100+</div>
                            <div class="stat-label">전문 콘텐츠</div>
                        </div>
                    </div>

                    <div class="signup-benefits">
                        <h3>가입 혜택</h3>
                        <ul>
                            <li><i class="fas fa-check"></i> 무료 커뮤니티 액세스</li>
                            <li><i class="fas fa-check"></i> 전문가 네트워킹 기회</li>
                            <li><i class="fas fa-check"></i> 독점 행사 및 강의 참여</li>
                            <li><i class="fas fa-check"></i> 실시간 마케팅 인사이트</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* 휴대폰 인증 관련 스타일 */
.phone-verification-group {
    display: flex;
    gap: 10px;
    align-items: stretch;
}

.phone-input {
    flex: 1;
}

.verification-input-group {
    display: flex;
    gap: 10px;
    align-items: center;
}

.verification-input {
    flex: 1;
    text-align: center;
    font-size: 1.2em;
    font-weight: bold;
    letter-spacing: 0.5em;
}

.timer-display {
    background: #ff4757;
    color: white;
    padding: 8px 12px;
    border-radius: 4px;
    font-weight: bold;
    font-family: 'Courier New', monospace;
    min-width: 60px;
    text-align: center;
}

.timer-display.expired {
    background: #636e72;
}

.form-group.verified .form-input {
    border-color: #2ed573;
    background-color: #f8fff9;
}

.form-group.verified .form-label::after {
    content: " ✓";
    color: #2ed573;
    font-weight: bold;
}

#signup-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.verification-status {
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 0.9em;
    margin-top: 5px;
}

.verification-status.success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.verification-status.error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* 필수 필드 표시 */
.required {
    color: #dc3545;
    font-weight: bold;
}

/* 에러 스타일 */
.form-input.error {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.form-input.error:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

/* 폼 도움말 개선 */
.form-help {
    font-size: 0.85rem;
    color: #6c757d;
    margin-top: 6px;
    line-height: 1.4;
}

/* reCAPTCHA 관련 안내 */
.recaptcha-notice {
    font-size: 0.8rem;
    color: #94a3b8;
    text-align: center;
    margin-top: 16px;
    padding: 8px;
    background: #f8fafc;
    border-radius: 4px;
    border-left: 3px solid #667eea;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 회원가입 페이지 로드 완료');
    
    // 세션에서 디버깅 정보 확인
    <?php if (isset($_SESSION['debug_info'])): ?>
    console.error('🚨 서버 디버깅 정보:', <?= json_encode($_SESSION['debug_info']) ?>);
    alert('🚨 디버깅 정보\n\n<?= addslashes($_SESSION['debug_info']) ?>\n\n콘솔 로그도 확인하세요.');
    <?php unset($_SESSION['debug_info']); ?>
    <?php endif; ?>
    
    // 전역 변수
    let verificationTimer = null;
    let timeLeft = 0;
    let isPhoneVerified = false;
    let recaptchaLoaded = false;
    
    console.log('📊 초기 상태:', {
        verificationTimer,
        timeLeft,
        isPhoneVerified,
        recaptchaLoaded
    });
    
    // reCAPTCHA 로드 확인
    grecaptcha.ready(function() {
        recaptchaLoaded = true;
        console.log('✅ reCAPTCHA v3 로드 성공');
    });
    
    // DOM 요소들
    const phoneInput = document.getElementById('phone');
    const sendVerificationBtn = document.getElementById('send-verification-btn');
    const verificationGroup = document.getElementById('verification-group');
    const verificationCodeInput = document.getElementById('verification_code');
    const verifyCodeBtn = document.getElementById('verify-code-btn');
    const timerDisplay = document.getElementById('timer-display');
    const signupBtn = document.getElementById('signup-btn');
    const phoneVerifiedInput = document.getElementById('phone_verified');
    const passwordInput = document.getElementById('password');
    const passwordToggle = document.getElementById('password-toggle');
    const passwordConfirmInput = document.getElementById('password_confirm');
    const passwordConfirmToggle = document.getElementById('password-confirm-toggle');
    const nicknameInput = document.getElementById('nickname');
    const emailInput = document.getElementById('email');
    const signupForm = document.getElementById('signup-form');
    const recaptchaTokenInput = document.getElementById('recaptcha_token');

    console.log('📋 DOM 요소 확인:', {
        phoneInput: !!phoneInput,
        sendVerificationBtn: !!sendVerificationBtn,
        verificationGroup: !!verificationGroup,
        verificationCodeInput: !!verificationCodeInput,
        verifyCodeBtn: !!verifyCodeBtn,
        timerDisplay: !!timerDisplay,
        signupBtn: !!signupBtn,
        phoneVerifiedInput: !!phoneVerifiedInput,
        signupForm: !!signupForm,
        recaptchaTokenInput: !!recaptchaTokenInput
    });

    // 비밀번호 표시/숨김 토글
    function setupPasswordToggle(input, toggle) {
        console.log('🔒 비밀번호 토글 설정:', input.id);
        toggle.addEventListener('click', function() {
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            
            const icon = toggle.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
            console.log('👁️ 비밀번호 표시 토글:', type);
        });
    }

    setupPasswordToggle(passwordInput, passwordToggle);
    setupPasswordToggle(passwordConfirmInput, passwordConfirmToggle);

    // 휴대폰 번호 포맷팅 및 010 검증
    phoneInput.addEventListener('input', function() {
        console.log('📱 휴대폰 번호 입력:', this.value);
        let value = this.value.replace(/[^0-9]/g, '');
        console.log('📱 숫자만 추출:', value);
        
        // 010으로 시작하지 않으면 에러 표시
        if (value.length > 0 && !value.startsWith('010')) {
            console.warn('❌ 010으로 시작하지 않는 번호:', value);
            this.setCustomValidity('010으로 시작하는 휴대폰 번호만 입력할 수 있습니다.');
            this.classList.add('error');
        } else {
            console.log('✅ 유효한 010 번호');
            this.setCustomValidity('');
            this.classList.remove('error');
        }
        
        if (value.length >= 3) {
            value = value.substring(0, 3) + '-' + value.substring(3);
        }
        if (value.length >= 8) {
            value = value.substring(0, 8) + '-' + value.substring(8, 12);
        }
        
        console.log('📱 포맷팅된 번호:', value);
        this.value = value;
        
        // 전화번호가 변경되면 인증 상태 초기화
        if (isPhoneVerified) {
            console.log('🔄 전화번호 변경으로 인증 상태 초기화');
            resetVerification();
        }
        
        updateSendButtonState();
    });

    // 인증번호 입력 필드 - 숫자만 입력
    verificationCodeInput.addEventListener('input', function() {
        const oldValue = this.value;
        this.value = this.value.replace(/[^0-9]/g, '');
        console.log('🔢 인증번호 입력:', oldValue, '→', this.value);
        updateVerifyButtonState();
    });

    // reCAPTCHA 토큰 생성
    async function generateRecaptchaToken(action) {
        console.log('🛡️ reCAPTCHA 토큰 생성 시작 - 액션:', action);
        
        if (!recaptchaLoaded) {
            console.error('❌ reCAPTCHA가 아직 로드되지 않음');
            throw new Error('reCAPTCHA가 아직 로드되지 않았습니다.');
        }
        
        try {
            console.log('🛡️ grecaptcha.execute 호출 중...');
            const token = await grecaptcha.execute('6LfViDErAAAAAMcOf3D-JxEhisMDhzLhEDYEahZb', {
                action: action
            });
            console.log('✅ reCAPTCHA 토큰 생성 성공:', token.substring(0, 20) + '...');
            return token;
        } catch (error) {
            console.error('❌ reCAPTCHA 토큰 생성 실패:', error);
            throw error;
        }
    }

    // 인증번호 발송 버튼
    sendVerificationBtn.addEventListener('click', async function() {
        console.log('📤 인증번호 발송 버튼 클릭');
        const phone = phoneInput.value.trim();
        console.log('📱 발송 대상 번호:', phone);
        
        if (!isValidPhoneFormat(phone)) {
            console.warn('❌ 잘못된 휴대폰 번호 형식:', phone);
            showMessage('010으로 시작하는 올바른 휴대폰 번호를 입력해주세요.', 'error');
            return;
        }

        // 010 번호 추가 검증
        if (!phone.startsWith('010-')) {
            console.warn('❌ 010으로 시작하지 않는 번호:', phone);
            showMessage('010으로 시작하는 휴대폰 번호만 사용할 수 있습니다.', 'error');
            return;
        }

        try {
            console.log('🛡️ reCAPTCHA 토큰 생성 중...');
            // reCAPTCHA 토큰 생성
            const recaptchaToken = await generateRecaptchaToken('send_verification');
            console.log('📤 SMS 발송 요청 시작');
            await sendVerificationCode(phone, recaptchaToken);
        } catch (error) {
            console.error('❌ 인증번호 발송 중 오류:', error);
            showMessage('보안 검증에 실패했습니다. 새로고침 후 다시 시도해주세요.', 'error');
        }
    });

    // 인증번호 확인 버튼
    verifyCodeBtn.addEventListener('click', function() {
        console.log('✅ 인증번호 확인 버튼 클릭');
        const code = verificationCodeInput.value.trim();
        const phone = phoneInput.value.trim();
        
        console.log('🔢 입력된 인증번호:', code);
        console.log('📱 인증할 휴대폰 번호:', phone);
        
        if (code.length !== 4) {
            console.warn('❌ 잘못된 인증번호 길이:', code.length);
            showMessage('4자리 인증번호를 입력해주세요.', 'error');
            return;
        }

        verifyCode(phone, code);
    });

    // 엔터키로 인증번호 확인
    verificationCodeInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            console.log('⌨️ 엔터키로 인증번호 확인');
            e.preventDefault();
            verifyCodeBtn.click();
        }
    });

    // 폼 유효성 검사
    function validateForm() {
        console.log('🔍 폼 유효성 검사 시작');
        
        const nickname = nicknameInput.value.trim();
        const phone = phoneInput.value.trim();
        const email = emailInput.value.trim();
        const password = passwordInput.value;
        const passwordConfirm = passwordConfirmInput.value;
        
        console.log('📊 입력값 확인:', {
            nickname: nickname,
            phone: phone,
            email: email,
            passwordLength: password.length,
            passwordConfirmLength: passwordConfirm.length,
            isPhoneVerified: isPhoneVerified
        });
        
        const isNicknameValid = nickname.length >= 2 && nickname.length <= 20;
        const isPhoneValid = isValidPhoneFormat(phone);
        const isEmailValid = isValidEmailFormat(email);
        const isPasswordValid = password.length >= 8;
        const isPasswordMatch = password === passwordConfirm;
        
        console.log('✅ 유효성 검사 결과:', {
            isNicknameValid,
            isPhoneValid,
            isEmailValid,
            isPasswordValid,
            isPasswordMatch,
            isPhoneVerified
        });
        
        const isFormValid = isNicknameValid && isPhoneValid && isEmailValid && 
                          isPasswordValid && isPasswordMatch && isPhoneVerified;
        
        console.log('📝 전체 폼 유효성:', isFormValid);
        signupBtn.disabled = !isFormValid;
        
        return isFormValid;
    }

    // 입력 필드 변경 시 폼 유효성 검사
    [nicknameInput, phoneInput, emailInput, passwordInput, passwordConfirmInput].forEach(input => {
        input.addEventListener('input', function() {
            console.log('📝 입력 필드 변경:', input.id, '→', input.value.substring(0, 10) + (input.value.length > 10 ? '...' : ''));
            validateForm();
        });
    });

    // 휴대폰 번호 형식 검증 (010으로 시작하는지 확인)
    function isValidPhoneFormat(phone) {
        const pattern = /^010-[0-9]{3,4}-[0-9]{4}$/;
        const isValid = pattern.test(phone);
        console.log('📱 휴대폰 번호 형식 검증:', phone, '→', isValid);
        return isValid;
    }

    // 이메일 형식 검증
    function isValidEmailFormat(email) {
        const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const isValid = pattern.test(email);
        console.log('📧 이메일 형식 검증:', email, '→', isValid);
        return isValid;
    }

    // 인증번호 발송 (reCAPTCHA 토큰 포함)
    async function sendVerificationCode(phone, recaptchaToken) {
        console.log('📤 SMS 발송 함수 시작');
        console.log('📱 발송 번호:', phone);
        console.log('🛡️ reCAPTCHA 토큰 길이:', recaptchaToken.length);
        
        // 🚀 SMS 발송용 로딩 UI 표시
        if (window.TopMarketingLoading) {
            window.TopMarketingLoading.custom({
                stages: [
                    '보안 검증 중...',
                    '알리고 SMS 서비스 연결 중...',
                    '인증번호 생성 중...',
                    '📱 메시지 발송 중...',
                    '발송 완료! 📨'
                ],
                duration: 3000,
                autoHide: false
            });
        }
        
        sendVerificationBtn.disabled = true;
        sendVerificationBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 발송 중...';

        try {
            console.log('🌐 AJAX 요청 시작 - /auth/send-verification');
            
            // 로딩 단계 업데이트
            if (window.TopMarketingLoading) {
                window.TopMarketingLoading.setStage('서버 연결 중...');
                window.TopMarketingLoading.setProgress(30);
            }
            
            const requestData = { 
                phone: phone,
                recaptcha_token: recaptchaToken
            };
            console.log('📤 요청 데이터:', requestData);
            
            if (window.TopMarketingLoading) {
                window.TopMarketingLoading.setStage('인증번호 생성 중...');
                window.TopMarketingLoading.setProgress(60);
            }
            
            const response = await fetch('/auth/send-verification', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(requestData)
            });

            console.log('📡 응답 상태:', response.status, response.statusText);
            console.log('📡 응답 헤더:', [...response.headers.entries()]);
            
            if (window.TopMarketingLoading) {
                window.TopMarketingLoading.setStage('📱 SMS 발송 중...');
                window.TopMarketingLoading.setProgress(90);
            }
            
            const data = await response.json();
            console.log('📥 응답 데이터:', data);
            
            if (data.success) {
                console.log('✅ SMS 발송 성공');
                
                // 성공 시 로딩 완료
                if (window.TopMarketingLoading) {
                    window.TopMarketingLoading.setStage('발송 완료! 📨');
                    window.TopMarketingLoading.setProgress(100);
                    setTimeout(() => {
                        window.TopMarketingLoading.hide();
                    }, 1000);
                }
                
                showMessage('인증번호가 발송되었습니다.', 'success');
                showVerificationGroup();
                startTimer(180); // 3분 = 180초
            } else {
                console.error('❌ SMS 발송 실패:', data.message);
                
                // 실패 시 로딩 숨김
                if (window.TopMarketingLoading) {
                    window.TopMarketingLoading.hide();
                }
                
                showMessage(data.message || '인증번호 발송에 실패했습니다.', 'error');
                sendVerificationBtn.disabled = false;
                sendVerificationBtn.innerHTML = '인증번호 발송';
            }
        } catch (error) {
            console.error('❌ AJAX 요청 오류:', error);
            
            // 오류 시 로딩 숨김
            if (window.TopMarketingLoading) {
                window.TopMarketingLoading.hide();
            }
            
            showMessage('인증번호 발송 중 오류가 발생했습니다.', 'error');
            sendVerificationBtn.disabled = false;
            sendVerificationBtn.innerHTML = '인증번호 발송';
        }
    }

    // 인증번호 확인
    function verifyCode(phone, code) {
        console.log('🔢 인증번호 확인 함수 시작');
        console.log('📱 인증 번호:', phone);
        console.log('🔢 입력 코드:', code);
        
        verifyCodeBtn.disabled = true;
        verifyCodeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 확인 중...';

        const requestData = { phone: phone, code: code };
        console.log('📤 인증 확인 요청 데이터:', requestData);

        fetch('/auth/verify-code', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(requestData)
        })
        .then(response => {
            console.log('📡 인증 확인 응답 상태:', response.status, response.statusText);
            return response.json();
        })
        .then(data => {
            console.log('📥 인증 확인 응답 데이터:', data);
            
            if (data.success) {
                console.log('✅ 휴대폰 인증 성공');
                showMessage('휴대폰 인증이 완료되었습니다.', 'success');
                completeVerification();
            } else {
                console.error('❌ 인증 실패:', data.message);
                showMessage(data.message || '인증번호가 일치하지 않습니다.', 'error');
                verifyCodeBtn.disabled = false;
                verifyCodeBtn.innerHTML = '확인';
            }
        })
        .catch(error => {
            console.error('❌ 인증 확인 AJAX 오류:', error);
            showMessage('인증 확인 중 오류가 발생했습니다.', 'error');
            verifyCodeBtn.disabled = false;
            verifyCodeBtn.innerHTML = '확인';
        });
    }

    // 인증 그룹 표시
    function showVerificationGroup() {
        console.log('👁️ 인증번호 입력 그룹 표시');
        verificationGroup.style.display = 'block';
        verificationCodeInput.focus();
        sendVerificationBtn.innerHTML = '재발송';
        sendVerificationBtn.disabled = false;
    }

    // 타이머 시작
    function startTimer(seconds) {
        console.log('⏰ 타이머 시작:', seconds + '초');
        timeLeft = seconds;
        updateTimerDisplay();
        
        verificationTimer = setInterval(function() {
            timeLeft--;
            updateTimerDisplay();
            
            if (timeLeft <= 0) {
                console.log('⏰ 타이머 만료');
                clearInterval(verificationTimer);
                expireVerification();
            }
        }, 1000);
    }

    // 타이머 표시 업데이트
    function updateTimerDisplay() {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        const display = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        timerDisplay.textContent = display;
        
        if (timeLeft <= 30) {
            timerDisplay.classList.add('expired');
            console.log('⚠️ 타이머 30초 이하:', display);
        }
    }

    // 인증 완료
    function completeVerification() {
        console.log('🎉 휴대폰 인증 완료 처리 시작');
        
        if (verificationTimer) {
            clearInterval(verificationTimer);
            console.log('⏰ 타이머 정지');
        }
        
        isPhoneVerified = true;
        phoneVerifiedInput.value = '1';
        
        console.log('✅ 인증 상태 업데이트:', {
            isPhoneVerified,
            phoneVerifiedInputValue: phoneVerifiedInput.value
        });
        
        // UI 업데이트
        document.querySelector('.phone-verification-group').style.display = 'none';
        verificationGroup.style.display = 'none';
        
        const phoneGroup = phoneInput.closest('.form-group');
        phoneGroup.classList.add('verified');
        
        phoneInput.readOnly = true;
        
        // 성공 메시지 표시
        const statusDiv = document.createElement('div');
        statusDiv.className = 'verification-status success';
        statusDiv.innerHTML = '<i class="fas fa-check-circle"></i> 휴대폰 인증이 완료되었습니다.';
        phoneGroup.appendChild(statusDiv);
        
        console.log('🎨 UI 업데이트 완료');
        validateForm();
    }

    // 인증 만료
    function expireVerification() {
        console.log('❌ 인증 시간 만료');
        timerDisplay.textContent = '00:00';
        timerDisplay.classList.add('expired');
        verifyCodeBtn.disabled = true;
        verifyCodeBtn.innerHTML = '시간 만료';
        
        showMessage('인증 시간이 만료되었습니다. 다시 인증번호를 요청해주세요.', 'error');
        
        // 인증번호 입력 필드 비활성화
        verificationCodeInput.disabled = true;
        
        // 재발송 버튼 활성화
        setTimeout(() => {
            sendVerificationBtn.disabled = false;
            sendVerificationBtn.innerHTML = '재발송';
            console.log('🔄 재발송 버튼 활성화');
        }, 1000);
    }

    // 인증 상태 초기화
    function resetVerification() {
        console.log('🔄 인증 상태 초기화');
        
        if (verificationTimer) {
            clearInterval(verificationTimer);
        }
        
        isPhoneVerified = false;
        phoneVerifiedInput.value = '0';
        
        verificationGroup.style.display = 'none';
        verificationCodeInput.value = '';
        verificationCodeInput.disabled = false;
        
        const phoneGroup = phoneInput.closest('.form-group');
        phoneGroup.classList.remove('verified');
        phoneInput.readOnly = false;
        
        // 기존 상태 메시지 제거
        const existingStatus = phoneGroup.querySelector('.verification-status');
        if (existingStatus) {
            existingStatus.remove();
        }
        
        sendVerificationBtn.innerHTML = '인증번호 발송';
        console.log('🔄 인증 상태 초기화 완료');
        validateForm();
    }

    // 발송 버튼 상태 업데이트
    function updateSendButtonState() {
        const phone = phoneInput.value.trim();
        const isValidPhone = isValidPhoneFormat(phone) && phone.startsWith('010-');
        const shouldDisable = !isValidPhone || isPhoneVerified;
        
        console.log('🔘 발송 버튼 상태 업데이트:', {
            phone,
            isValidPhone,
            isPhoneVerified,
            shouldDisable
        });
        
        sendVerificationBtn.disabled = shouldDisable;
    }

    // 확인 버튼 상태 업데이트
    function updateVerifyButtonState() {
        const code = verificationCodeInput.value.trim();
        const shouldDisable = code.length !== 4 || timeLeft <= 0;
        
        console.log('🔘 확인 버튼 상태 업데이트:', {
            codeLength: code.length,
            timeLeft,
            shouldDisable
        });
        
        verifyCodeBtn.disabled = shouldDisable;
    }

    // 메시지 표시
    function showMessage(message, type) {
        console.log('💬 메시지 표시:', type, message);
        
        // 기존 메시지 제거
        const existingAlert = document.querySelector('.alert-message');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert-message alert-${type}`;
        alertDiv.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        `;
        
        const form = document.querySelector('.auth-form');
        form.insertBefore(alertDiv, form.firstChild);
        
        // 3초 후 자동 제거
        setTimeout(() => {
            alertDiv.remove();
        }, 3000);
    }

    // 폼 제출 시 최종 검증 및 reCAPTCHA 토큰 생성
    signupForm.addEventListener('submit', async function(e) {
        console.log('📝 회원가입 폼 제출 시작');
        e.preventDefault();
        
        console.log('🔍 최종 폼 유효성 검사');
        if (!validateForm()) {
            console.error('❌ 폼 유효성 검사 실패');
            showMessage('모든 필드를 올바르게 입력하고 휴대폰 인증을 완료해주세요.', 'error');
            alert('⚠️ 디버깅: 폼 유효성 검사 실패\n\n콘솔 로그를 확인하세요.\n확인을 누르면 계속됩니다.');
            return;
        }

        try {
            // 🚀 로딩 UI 표시
            if (window.TopMarketingLoading) {
                window.TopMarketingLoading.custom({
                    stages: [
                        '보안 검증 준비 중...',
                        'reCAPTCHA 토큰 생성 중...',
                        '회원 정보 암호화 중...',
                        '데이터베이스 연결 중...',
                        '계정 생성 중...',
                        '환영합니다! 🎉'
                    ],
                    duration: 5000,
                    autoHide: false
                });
            }
            
            console.log('🛡️ 회원가입용 reCAPTCHA 토큰 생성 중...');
            
            // reCAPTCHA 토큰 생성 중 로딩 단계 업데이트
            if (window.TopMarketingLoading) {
                window.TopMarketingLoading.setStage('reCAPTCHA 보안 검증 중...');
                window.TopMarketingLoading.setProgress(20);
            }
            
            // 회원가입용 reCAPTCHA 토큰 생성
            const recaptchaToken = await generateRecaptchaToken('signup');
            recaptchaTokenInput.value = recaptchaToken;
            
            // 데이터 준비 단계
            if (window.TopMarketingLoading) {
                window.TopMarketingLoading.setStage('회원 정보 검증 중...');
                window.TopMarketingLoading.setProgress(50);
            }
            
            console.log('📤 회원가입 폼 실제 제출');
            console.log('📊 제출할 데이터:', {
                nickname: nicknameInput.value,
                phone: phoneInput.value,
                email: emailInput.value,
                passwordLength: passwordInput.value.length,
                phoneVerified: phoneVerifiedInput.value,
                hasRecaptchaToken: !!recaptchaToken
            });
            
            // 제출 직전 단계
            if (window.TopMarketingLoading) {
                window.TopMarketingLoading.setStage('계정 생성 중...');
                window.TopMarketingLoading.setProgress(80);
            }
            
            console.log('🚨 디버깅 모드: 폼이 제출됩니다. 오류 발생 시 콘솔 로그를 확인하세요!');
            
            // 폼 제출
            this.submit();
        } catch (error) {
            console.error('❌ reCAPTCHA 토큰 생성 실패:', error);
            
            // 오류 시 로딩 숨김
            if (window.TopMarketingLoading) {
                window.TopMarketingLoading.hide();
            }
            
            showMessage('보안 검증에 실패했습니다. 새로고침 후 다시 시도해주세요.', 'error');
            alert('⚠️ 디버깅: reCAPTCHA 토큰 생성 실패\n\n' + error.message + '\n\n콘솔 로그를 확인하세요.\n확인을 누르면 계속됩니다.');
        }
    });

    // 초기화
    console.log('🏁 초기화 시작');
    validateForm();
    updateSendButtonState();
    console.log('✅ 회원가입 페이지 초기화 완료');
});
</script>

<?php require_once SRC_PATH . '/views/templates/footer.php'; ?> 