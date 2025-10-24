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
                        <div class="input-wrapper">
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
                            <i class="input-status-icon" id="nickname-status-icon"></i>
                        </div>
                        <div class="field-status-message" id="nickname-status-message" style="display: none;">
                            <div class="status-indicator" id="nickname-indicator">
                                <i class="fas fa-times"></i>
                                <span id="nickname-message-text">닉네임을 확인하는 중...</span>
                            </div>
                        </div>
                        <small class="form-help">한글, 영문, 숫자를 사용하여 2-20자로 입력하세요</small>
                    </div>

                    <div class="form-group">
                        <label for="phone" class="form-label">
                            <i class="fas fa-mobile-alt"></i>
                            휴대폰 번호 <span class="required">*</span>
                        </label>
                        <div class="phone-verification-group">
                            <div class="input-wrapper phone-input-wrapper">
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
                                <i class="input-status-icon" id="phone-status-icon" title="전화번호 지우기"></i>
                            </div>
                            <button type="button" id="send-verification-btn" class="btn btn-outline-primary">
                                인증번호 발송
                            </button>
                        </div>
                        <div class="field-status-message" id="phone-status-message" style="display: none;">
                            <div class="status-indicator" id="phone-indicator">
                                <i class="fas fa-times"></i>
                                <span id="phone-message-text">휴대폰 번호를 확인하는 중...</span>
                            </div>
                        </div>
                        <small class="form-help" id="phone-form-help">010으로 시작하는 휴대폰 번호를 입력하세요 (로그인 시 사용됩니다)</small>
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
                                placeholder="인증번호입력"
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
                        <small class="form-help">계정 복구 및 중요한 알림을 받기 위해 사용됩니다 (중복 허용, 필수)</small>
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
                            <i class="input-status-icon" id="password-status-icon"></i>
                        </div>
                        <div class="password-requirements" id="password-requirements" style="display: none;">
                            <div class="requirement" id="req-length">
                                <i class="fas fa-times"></i>
                                <span>8자 이상</span>
                            </div>
                            <div class="requirement" id="req-letter">
                                <i class="fas fa-times"></i>
                                <span>영문 포함</span>
                            </div>
                            <div class="requirement" id="req-number">
                                <i class="fas fa-times"></i>
                                <span>숫자 포함</span>
                            </div>
                            <div class="requirement" id="req-special">
                                <i class="fas fa-times"></i>
                                <span>특수문자 포함</span>
                            </div>
                        </div>
                        <div class="password-strength" id="password-strength" style="display: none;">
                            <div class="strength-label">비밀번호 강도:</div>
                            <div class="strength-bar">
                                <div class="strength-fill" id="strength-fill"></div>
                            </div>
                            <span class="strength-text" id="strength-text">약함</span>
                        </div>
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
                            <i class="input-status-icon" id="password-confirm-status-icon"></i>
                        </div>
                        <div class="password-match-status" id="password-match-status" style="display: none;">
                            <div class="match-indicator" id="match-indicator">
                                <i class="fas fa-times"></i>
                                <span id="match-text">비밀번호가 일치하지 않습니다</span>
                            </div>
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

<!-- 회원가입 페이지 스타일 include -->
<style>
<?php
// 회원가입 페이지 스타일 파일 include
$styleFile = SRC_PATH . '/views/auth/components/signup-styles.css';
if (file_exists($styleFile)) {
    echo file_get_contents($styleFile);
}
?>
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (isset($_SESSION['debug_info'])): ?>
    // 세션에서 디버깅 정보 확인
    Toast.error('🚨 디버깅 정보\n\n<?= addslashes($_SESSION["debug_info"]) ?>\n\n콘솔 로그도 확인하세요.');
    <?php unset($_SESSION['debug_info']); endif; ?>
    
    // 전역 변수
    let verificationTimer = null;
    let timeLeft = 0;
    let isPhoneVerified = false;
    let recaptchaLoaded = false;
    
    
    // reCAPTCHA 로드 확인
    grecaptcha.ready(function() {
        recaptchaLoaded = true;
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


    // 비밀번호 표시/숨김 토글
    function setupPasswordToggle(input, toggle) {
        toggle.addEventListener('click', function() {
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            
            const icon = toggle.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    }

    setupPasswordToggle(passwordInput, passwordToggle);
    setupPasswordToggle(passwordConfirmInput, passwordConfirmToggle);

    // 전화번호 상태 아이콘 클릭 시 삭제
    const phoneStatusIcon = document.getElementById('phone-status-icon');
    if (phoneStatusIcon) {
        phoneStatusIcon.addEventListener('click', function() {
            // 아이콘이 표시되어 있을 때만 동작
            if (!this.classList.contains('show')) return;

            phoneInput.value = '';
            phoneInput.focus();

            // 상태 초기화
            const statusMessage = document.getElementById('phone-status-message');
            if (statusMessage) statusMessage.style.display = 'none';
            this.className = 'input-status-icon';
            phoneInput.classList.remove('valid', 'invalid', 'error');

            // 인증 상태 초기화
            if (isPhoneVerified) {
                resetVerification();
            }

            updateSendButtonState();
        });
    }

    // 휴대폰 번호 포맷팅 및 010 검증 (백스페이스 완전 대응)
    let isDeleting = false;
    let previousValue = '';
    let previousCursorPosition = 0;
    
    phoneInput.addEventListener('keydown', function(e) {
        // 현재 커서 위치와 값 저장
        previousValue = this.value;
        previousCursorPosition = this.selectionStart;
        
        if (e.key === 'Backspace' || e.key === 'Delete') {
            isDeleting = true;
        } else {
            isDeleting = false;
        }
    });
    
    // 기존 상세 휴대폰 입력 핸들러를 통합된 버전으로 대체함

    // 인증번호 입력 필드 - 숫자만 입력
    verificationCodeInput.addEventListener('input', function() {
        const oldValue = this.value;
        this.value = this.value.replace(/[^0-9]/g, '');
        updateVerifyButtonState();
    });

    // reCAPTCHA 토큰 생성
    async function generateRecaptchaToken(action) {
        
        if (!recaptchaLoaded) {
            Toast.error('보안 검증 로드 중입니다.\n잠시 후 다시 시도해주세요.');
            throw new Error('reCAPTCHA가 아직 로드되지 않았습니다.');
        }
        
        try {
            const token = await grecaptcha.execute('6LfViDErAAAAAMcOf3D-JxEhisMDhzLhEDYEahZb', {
                action: action
            });

            return token;
        } catch (error) {
            Toast.error('보안 검증에 실패했습니다.\n페이지를 새로고침해주세요.');
            throw error;
        }
    }

    // 인증번호 발송 버튼
    sendVerificationBtn.addEventListener('click', async function() {
        const phone = phoneInput.value.trim();
        
        // 🚀 v3.44.0: FormValidator 직접 사용
        if (!FormValidator.isValidPhoneStrict(phone)) {
            Toast.error('010으로 시작하는 올바른 휴대폰 번호를 입력해주세요.');
            return;
        }

        // 010 번호 추가 검증
        if (!phone.startsWith('010-')) {
            Toast.error('010으로 시작하는 휴대폰 번호만 사용할 수 있습니다.');
            return;
        }

        try {
            // reCAPTCHA 토큰 생성
            const recaptchaToken = await generateRecaptchaToken('send_verification');
            await sendVerificationCode(phone, recaptchaToken);
        } catch (error) {
            Toast.error('보안 검증에 실패했습니다. 새로고침 후 다시 시도해주세요.');
        }
    });

    // 인증번호 확인 버튼
    verifyCodeBtn.addEventListener('click', function() {
        const code = verificationCodeInput.value.trim();
        const phone = phoneInput.value.trim();
        
        
        if (code.length !== 4) {
            Toast.error('4자리 인증번호를 입력해주세요.');
            return;
        }

        verifyCode(phone, code);
    });

    // 엔터키로 인증번호 확인
    verificationCodeInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            verifyCodeBtn.click();
        }
    });

    // 실시간 중복 검사 관련 변수
    let nicknameCheckTimeout;
    let phoneCheckTimeout;
    let isNicknameAvailable = false;
    let isPhoneAvailable = false;
    const DEBOUNCE_DELAY = 800; // 800ms 디바운싱

    // 닉네임 중복 검사 함수
    async function checkNicknameDuplication(nickname) {
        
        const statusMessage = document.getElementById('nickname-status-message');
        const statusIndicator = document.getElementById('nickname-indicator');
        const statusIcon = document.getElementById('nickname-status-icon');
        const messageText = document.getElementById('nickname-message-text');
        
        if (!nickname || nickname.length < 2) {
            statusMessage.style.display = 'none';
            statusIcon.className = 'input-status-icon';
            nicknameInput.classList.remove('valid', 'invalid');
            isNicknameAvailable = false;
            return;
        }
        
        // 로딩 상태 표시
        statusMessage.style.display = 'block';
        statusIndicator.className = 'status-indicator checking';
        statusIcon.className = 'input-status-icon show checking fas fa-spinner';
        messageText.textContent = '닉네임을 확인하는 중...';
        nicknameInput.classList.remove('valid', 'invalid');

        // 🚀 v3.42.0: ApiClient 사용 (fetch → ApiClient.post)
        try {
            const result = await ApiClient.post('/auth/check-nickname',
                { nickname },
                { noLoading: true, noErrorToast: true }
            );

            if (result.success) {
                if (result.data.available) {
                    // 사용 가능
                    statusIndicator.className = 'status-indicator available';
                    statusIcon.className = 'input-status-icon show valid fas fa-check';
                    messageText.textContent = result.message;
                    nicknameInput.classList.add('valid');
                    nicknameInput.classList.remove('invalid');
                    isNicknameAvailable = true;
                } else {
                    // 사용 불가능
                    statusIndicator.className = 'status-indicator';
                    statusIcon.className = 'input-status-icon show invalid fas fa-times';
                    messageText.textContent = result.message;
                    nicknameInput.classList.add('invalid');
                    nicknameInput.classList.remove('valid');
                    isNicknameAvailable = false;
                }
            } else {
                throw new Error(result.message || '중복 검사 중 오류가 발생했습니다.');
            }

        } catch (error) {
            Toast.error('닉네임 중복 검사 중 오류가 발생했습니다.\n잠시 후 다시 시도해주세요.');
            statusIndicator.className = 'status-indicator';
            statusIcon.className = 'input-status-icon show invalid fas fa-exclamation-triangle';
            messageText.textContent = '중복 검사 중 오류가 발생했습니다. 다시 시도해주세요.';
            nicknameInput.classList.add('invalid');
            nicknameInput.classList.remove('valid');
            isNicknameAvailable = false;
        }
        
        updateFormValidation();
    }
    
    // 전역 노출 (테스트 및 디버깅용)
    window.checkNicknameDuplication = checkNicknameDuplication;
    
    // 휴대폰 번호 중복 검사 함수
    async function checkPhoneDuplication(phone) {
        
        const statusMessage = document.getElementById('phone-status-message');
        const statusIndicator = document.getElementById('phone-indicator');
        const statusIcon = document.getElementById('phone-status-icon');
        const messageText = document.getElementById('phone-message-text');
        
        // 최소 01012345678 (11자) 이상이어야 함
        if (!phone || phone.length < 11) {
            statusMessage.style.display = 'none';
            statusIcon.className = 'input-status-icon';
            phoneInput.classList.remove('valid', 'invalid');
            isPhoneAvailable = false;
            return;
        }

        // 로딩 상태 표시
        statusMessage.style.display = 'block';
        statusIndicator.className = 'status-indicator checking';
        statusIcon.className = 'input-status-icon show checking fas fa-spinner';
        messageText.textContent = '휴대폰 번호를 확인하는 중...';
        phoneInput.classList.remove('valid', 'invalid');

        // 🚀 v3.42.0: ApiClient 사용 (fetch → ApiClient.post)
        try {
            const result = await ApiClient.post('/auth/check-phone',
                { phone },
                { noLoading: true, noErrorToast: true }
            );

            if (result.success) {
                if (result.data.available) {
                    // 사용 가능
                    statusIndicator.className = 'status-indicator available';
                    statusIcon.className = 'input-status-icon show valid fas fa-check';
                    messageText.textContent = result.message;
                    phoneInput.classList.add('valid');
                    phoneInput.classList.remove('invalid');
                    isPhoneAvailable = true;
                } else {
                    // 사용 불가능
                    statusIndicator.className = 'status-indicator';
                    statusIcon.className = 'input-status-icon show invalid fas fa-times';
                    messageText.textContent = result.message;
                    phoneInput.classList.add('invalid');
                    phoneInput.classList.remove('valid');
                    isPhoneAvailable = false;
                }
            } else {
                throw new Error(result.message || '중복 검사 중 오류가 발생했습니다.');
            }

        } catch (error) {
            Toast.error('휴대폰 중복 검사 중 오류가 발생했습니다.\n잠시 후 다시 시도해주세요.');
            statusIndicator.className = 'status-indicator';
            statusIcon.className = 'input-status-icon show invalid fas fa-exclamation-triangle';
            messageText.textContent = '중복 검사 중 오류가 발생했습니다. 다시 시도해주세요.';
            phoneInput.classList.add('invalid');
            phoneInput.classList.remove('valid');
            isPhoneAvailable = false;
        }
        
        updateFormValidation();
    }
    
    // 전역 노출 (테스트 및 디버깅용)
    window.checkPhoneDuplication = checkPhoneDuplication;
    window.DEBOUNCE_DELAY = DEBOUNCE_DELAY;
    window.isNicknameAvailable = isNicknameAvailable;
    window.isPhoneAvailable = isPhoneAvailable;
    window.nicknameCheckTimeout = nicknameCheckTimeout;
    window.phoneCheckTimeout = phoneCheckTimeout;
    
    // 닉네임 입력 이벤트 (디바운싱 적용)
    nicknameInput.addEventListener('input', function() {
        const nickname = this.value.trim();
        
        // 이전 타이머 취소
        clearTimeout(nicknameCheckTimeout);
        
        // 새 타이머 설정
        nicknameCheckTimeout = setTimeout(() => {
            checkNicknameDuplication(nickname);
        }, DEBOUNCE_DELAY);
    });
    
    // 휴대폰 번호 입력 이벤트 (포맷팅 + 검증 + 실시간 중복검사)
    phoneInput.addEventListener('input', function() {
        let value = this.value.replace(/[^0-9]/g, '');
        
        // 삭제 중인 경우 - 자연스러운 처리
        if (isDeleting) {
            
            // 010으로 시작하지 않으면 에러 표시
            if (value.length > 0 && !value.startsWith('010')) {
                this.setCustomValidity('010으로 시작하는 휴대폰 번호만 입력할 수 있습니다.');
                this.classList.add('error');
            } else {
                this.setCustomValidity('');
                this.classList.remove('error');
            }
            
            // 삭제 시에는 최소한의 포맷팅만 적용
            let formattedValue = value;
            if (value.length > 3 && value.length <= 7) {
                formattedValue = value.substring(0, 3) + '-' + value.substring(3);
            } else if (value.length > 7) {
                formattedValue = value.substring(0, 3) + '-' + value.substring(3, 7) + '-' + value.substring(7, 11);
            }
            
            this.value = formattedValue;
            isDeleting = false;
            
            // 전화번호 변경으로 인증 상태 초기화
            if (isPhoneVerified) {
                resetVerification();
            }
            
            updateSendButtonState();
            return;
        }
        
        // 010으로 시작하지 않으면 에러 표시
        if (value.length > 0 && !value.startsWith('010')) {
            this.setCustomValidity('010으로 시작하는 휴대폰 번호만 입력할 수 있습니다.');
            this.classList.add('error');
        } else {
            this.setCustomValidity('');
            this.classList.remove('error');
        }
        
        // 일반적인 포맷팅 (입력하는 경우)
        if (value.length >= 3) {
            value = value.substring(0, 3) + '-' + value.substring(3);
        }
        if (value.length >= 8) {
            value = value.substring(0, 8) + '-' + value.substring(8, 12);
        }
        
        this.value = value;
        
        // 전화번호가 변경되면 인증 상태 초기화
        if (isPhoneVerified) {
            resetVerification();
        }
        
        // 실시간 중복검사 (디바운싱) - 유효한 010 번호일 때만
        if (value.length > 0 && value.startsWith('010')) {
            clearTimeout(phoneCheckTimeout);
            phoneCheckTimeout = setTimeout(() => {
                // 하이픈 제거한 순수 번호로 중복검사
                const cleanPhone = value.replace(/-/g, '');
                if (cleanPhone.length >= 10) { // 최소 010-1234-567 형태일 때만 검사
                    checkPhoneDuplication(cleanPhone);
                }
            }, DEBOUNCE_DELAY);
        }
        
        updateSendButtonState();
    });
    
    // 폼 검증 업데이트 함수
    function updateFormValidation() {
        validateForm();
    }

    // 강화된 비밀번호 검증 함수
    // 🚀 v3.41.0: FormValidator 사용
    function validatePassword(password) {
        const requirementsContainer = document.getElementById('password-requirements');

        // 비밀번호가 비어있으면 요구사항 박스 숨기기
        if (password.length === 0) {
            requirementsContainer.style.display = 'none';
            passwordInput.classList.remove('valid', 'invalid');
            updatePasswordStrength(password);
            return false;
        }

        // 🚀 FormValidator로 비밀번호 검증
        const validation = FormValidator.validatePassword(password);
        const requirements = validation.requirements;
        const isValid = validation.isValid;

        // 비밀번호가 있으면 요구사항 박스 보이기
        requirementsContainer.style.display = 'block';

        // UI 업데이트
        const reqElements = {
            length: document.getElementById('req-length'),
            letter: document.getElementById('req-letter'),
            number: document.getElementById('req-number'),
            special: document.getElementById('req-special')
        };

        Object.keys(requirements).forEach(req => {
            if (requirements[req]) {
                reqElements[req].classList.add('valid');
            } else {
                reqElements[req].classList.remove('valid');
            }
        });

        // 입력 필드 스타일 업데이트
        const passwordStatusIcon = document.getElementById('password-status-icon');
        if (password.length > 0) {
            if (isValid) {
                passwordInput.classList.add('valid');
                passwordInput.classList.remove('invalid');
                passwordStatusIcon.className = 'input-status-icon show valid fas fa-check';
            } else {
                passwordInput.classList.add('invalid');
                passwordInput.classList.remove('valid');
                passwordStatusIcon.className = 'input-status-icon show invalid fas fa-times';
            }
        } else {
            passwordInput.classList.remove('valid', 'invalid');
            passwordStatusIcon.className = 'input-status-icon';
        }

        // 비밀번호 강도 업데이트
        updatePasswordStrength(password);

        return isValid;
    }
    
    // 비밀번호 강도 계산 함수
    // 🚀 v3.41.0: FormValidator 사용
    function calculatePasswordStrength(password) {
        return FormValidator.calculatePasswordStrength(password);
    }
    
    // 비밀번호 강도 UI 업데이트 함수
    function updatePasswordStrength(password) {
        const strengthContainer = document.getElementById('password-strength');
        const strengthFill = document.getElementById('strength-fill');
        const strengthText = document.getElementById('strength-text');
        
        if (password.length === 0) {
            strengthContainer.style.display = 'none';
            return;
        }
        
        strengthContainer.style.display = 'flex';
        
        const strength = calculatePasswordStrength(password);
        
        // 이전 클래스 제거
        strengthFill.className = 'strength-fill';
        strengthText.className = 'strength-text';
        
        // 새 클래스 추가
        if (strength.className) {
            strengthFill.classList.add(strength.className);
            strengthText.classList.add(strength.className);
        }
        
        strengthText.textContent = strength.label;
    }
    
    // 비밀번호 일치 검증 함수
    // 🚀 v3.41.0: FormValidator 사용
    function validatePasswordMatch(password, passwordConfirm) {
        const matchStatus = document.getElementById('password-match-status');
        const matchIndicator = document.getElementById('match-indicator');
        const matchText = document.getElementById('match-text');

        if (passwordConfirm.length === 0) {
            matchStatus.style.display = 'none';
            passwordConfirmInput.classList.remove('valid', 'invalid');
            return false;
        }

        matchStatus.style.display = 'block';

        const passwordConfirmStatusIcon = document.getElementById('password-confirm-status-icon');

        // 🚀 FormValidator로 비밀번호 일치 검증
        const isMatch = FormValidator.validatePasswordMatch(password, passwordConfirm);

        if (isMatch) {
            matchIndicator.classList.add('valid');
            matchText.textContent = '비밀번호가 일치합니다';
            passwordConfirmInput.classList.add('valid');
            passwordConfirmInput.classList.remove('invalid');
            passwordConfirmStatusIcon.className = 'input-status-icon show valid fas fa-check';
            return true;
        } else {
            matchIndicator.classList.remove('valid');
            matchText.textContent = '비밀번호가 일치하지 않습니다';
            passwordConfirmInput.classList.add('invalid');
            passwordConfirmInput.classList.remove('valid');
            passwordConfirmStatusIcon.className = 'input-status-icon show invalid fas fa-times';
            return false;
        }
    }

    // 폼 유효성 검사
    function validateForm() {
        
        const nickname = nicknameInput.value.trim();
        const phone = phoneInput.value.trim();
        const email = emailInput.value.trim();
        const password = passwordInput.value;
        const passwordConfirm = passwordConfirmInput.value;
        const termsChecked = document.querySelector('input[name="terms"]').checked;
        
        
        const isNicknameValid = nickname.length >= 2 && nickname.length <= 20 && isNicknameAvailable;
        // 🚀 v3.44.0: FormValidator 직접 사용
        const isPhoneValid = FormValidator.isValidPhoneStrict(phone) && isPhoneAvailable;
        const isEmailValid = FormValidator.isValidEmail(email);
        const isPasswordValid = validatePassword(password);
        const isPasswordMatch = validatePasswordMatch(password, passwordConfirm);
        
        
        const isFormValid = isNicknameValid && isPhoneValid && isEmailValid && 
                          isPasswordValid && isPasswordMatch && isPhoneVerified && termsChecked;
        
        signupBtn.disabled = !isFormValid;
        
        return isFormValid;
    }

    // 입력 필드 변경 시 폼 유효성 검사
    [nicknameInput, phoneInput, emailInput, passwordInput, passwordConfirmInput].forEach(input => {
        input.addEventListener('input', function() {

            validateForm();
        });
    });

    // 🔐 비밀번호 필드 키보드 정책 설정
    // password 필드: Ctrl+C (복사) 허용 - 브라우저 보안 우회
    passwordInput.addEventListener('keydown', function(e) {
        // Ctrl+C (복사) 허용 - 브라우저 password 타입 보안 우회
        if (e.ctrlKey && e.key === 'c') {
            // 현재 타입 저장
            const currentType = this.type;
            // 임시로 text 타입으로 변경하여 복사 허용
            this.type = 'text';
            
            // 복사 허용 후 잠시 후 다시 password로 변경
            setTimeout(() => {
                this.type = currentType;
            }, 10); // 10ms 후 복원
            
            return; // 기본 복사 동작 허용
        }
        // Ctrl+V (붙여넣기) 차단
        if (e.ctrlKey && e.key === 'v') {
            e.preventDefault();
            return false;
        }
    });

    // password_confirm 필드: Ctrl+V (붙여넣기) 차단, Ctrl+C (복사) 허용
    passwordConfirmInput.addEventListener('keydown', function(e) {
        // Ctrl+V (붙여넣기) 차단
        if (e.ctrlKey && e.key === 'v') {
            e.preventDefault();
            Toast.info('보안을 위해 비밀번호 확인 필드에는 붙여넣기가 제한됩니다. 직접 입력해주세요.');
            return false;
        }
        // Ctrl+C (복사) 허용 - 브라우저 password 타입 보안 우회
        if (e.ctrlKey && e.key === 'c') {
            // 현재 타입 저장
            const currentType = this.type;
            // 임시로 text 타입으로 변경하여 복사 허용
            this.type = 'text';
            
            // 복사 허용 후 잠시 후 다시 password로 변경
            setTimeout(() => {
                this.type = currentType;
            }, 10); // 10ms 후 복원
            
            return; // 기본 복사 동작 허용
        }
    });

    // 컨텍스트 메뉴(우클릭)를 통한 붙여넣기도 차단
    passwordConfirmInput.addEventListener('paste', function(e) {
        e.preventDefault();
        Toast.info('보안을 위해 비밀번호 확인 필드에는 붙여넣기가 제한됩니다. 직접 입력해주세요.');
        return false;
    });

    // 📋 복사 이벤트 직접 처리 - 추가적인 복사 지원
    passwordInput.addEventListener('copy', function(e) {
        // copy 이벤트가 발생하면 잠시 type을 text로 변경
        const currentType = this.type;
        this.type = 'text';
        
        setTimeout(() => {
            this.type = currentType;
        }, 10);
    });

    passwordConfirmInput.addEventListener('copy', function(e) {
        // copy 이벤트가 발생하면 잠시 type을 text로 변경
        const currentType = this.type;
        this.type = 'text';
        
        setTimeout(() => {
            this.type = currentType;
        }, 10);
    });

    // 🖱️ 마우스로 텍스트 선택 지원 - selectstart 이벤트 처리
    [passwordInput, passwordConfirmInput].forEach(input => {
        input.addEventListener('selectstart', function(e) {
            // 선택 중일 때는 text 타입으로 변경
            const currentType = this.type;
            this.type = 'text';
            
            // 선택이 끝나면 다시 password로 복원
            const restoreType = () => {
                setTimeout(() => {
                    this.type = currentType;
                }, 100); // 선택 완료 후 약간의 지연
            };
            
            // 마우스 업 시 복원
            input.addEventListener('mouseup', restoreType, { once: true });
            // 포커스 잃을 때도 복원
            input.addEventListener('blur', restoreType, { once: true });
        });
    });

    // 이용약관 체크박스 변경 시 폼 유효성 검사
    const termsCheckbox = document.querySelector('input[name="terms"]');
    const marketingCheckbox = document.querySelector('input[name="marketing"]');
    
    if (termsCheckbox) {
        termsCheckbox.addEventListener('change', function() {
            validateForm();
        });
    }
    
    // 마케팅 동의는 선택사항이므로 폼 유효성에 영향 없음 (로그만)
    if (marketingCheckbox) {
        marketingCheckbox.addEventListener('change', function() {
        });
    }

    // 🚀 v3.44.0: 중복 함수 제거 (FormValidator 직접 사용으로 전환)
    // - isValidPhoneFormat() 제거 → FormValidator.isValidPhoneStrict() 직접 호출
    // - isValidEmailFormat() 제거 → FormValidator.isValidEmail() 직접 호출

    // 인증번호 발송 (reCAPTCHA 토큰 포함)
    async function sendVerificationCode(phone, recaptchaToken) {
        
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
        
        // 🚀 v3.31.0: Loading 클래스 사용
        Loading.button(sendVerificationBtn, true, { text: '발송 중...' });

        try {
            
            // 로딩 단계 업데이트
            if (window.TopMarketingLoading) {
                window.TopMarketingLoading.setStage('서버 연결 중...');
                window.TopMarketingLoading.setProgress(30);
            }
            
            const requestData = { 
                phone: phone,
                recaptcha_token: recaptchaToken
            };
            
            if (window.TopMarketingLoading) {
                window.TopMarketingLoading.setStage('인증번호 생성 중...');
                window.TopMarketingLoading.setProgress(60);
            }

            // v3.56.0: ApiClient 사용
            const data = await ApiClient.post('/auth/send-verification', requestData, {
                noLoading: true // TopMarketingLoading을 사용하므로 기본 로딩 비활성화
            });


            if (window.TopMarketingLoading) {
                window.TopMarketingLoading.setStage('📱 SMS 발송 중...');
                window.TopMarketingLoading.setProgress(90);
            }
            
            if (data.success) {
                
                // 성공 시 로딩 완료
                if (window.TopMarketingLoading) {
                    window.TopMarketingLoading.setStage('발송 완료! 📨');
                    window.TopMarketingLoading.setProgress(100);
                    setTimeout(() => {
                        window.TopMarketingLoading.hide();
                    }, 1000);
                }
                
                Toast.success('인증번호가 발송되었습니다.');
                showVerificationGroup();
                startTimer(180); // 3분 = 180초
            } else {
                
                // 실패 시 로딩 숨김
                if (window.TopMarketingLoading) {
                    window.TopMarketingLoading.hide();
                }
                
                Toast.error(data.message || '인증번호 발송에 실패했습니다.');
                Loading.button(sendVerificationBtn, false);
            }
        } catch (error) {
            
            // 오류 시 로딩 숨김
            if (window.TopMarketingLoading) {
                window.TopMarketingLoading.hide();
            }
            
            Toast.error('인증번호 발송 중 오류가 발생했습니다.');
            Loading.button(sendVerificationBtn, false);
        }
    }

    // 인증번호 확인
    function verifyCode(phone, code) {
        
        // 🚀 v3.31.0: Loading 클래스 사용
        Loading.button(verifyCodeBtn, true, { text: '확인 중...' });

        const requestData = { phone: phone, code: code };

        // v3.56.0: ApiClient 사용 (Promise 체인 유지)
        ApiClient.post('/auth/verify-code', requestData, {
            noLoading: true // Loading.button을 사용하므로 기본 로딩 비활성화
        })
        .then(data => {

            if (data.success) {
                Toast.success('휴대폰 인증이 완료되었습니다.');
                completeVerification();
            } else {
                Toast.error(data.message || '인증번호가 일치하지 않습니다.');
                Loading.button(verifyCodeBtn, false);
            }
        })
        .catch(error => {
            Toast.error('인증 확인 중 오류가 발생했습니다.');
            Loading.button(verifyCodeBtn, false);
        });
    }

    // 인증 그룹 표시
    function showVerificationGroup() {
        verificationGroup.style.display = 'block';
        verificationCodeInput.focus();
        sendVerificationBtn.innerHTML = '재발송';
        Loading.button(sendVerificationBtn, false);
    }

    // 타이머 시작
    function startTimer(seconds) {
        timeLeft = seconds;
        updateTimerDisplay();
        
        verificationTimer = setInterval(function() {
            timeLeft--;
            updateTimerDisplay();
            
            if (timeLeft <= 0) {
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
        }
    }

    // 인증 완료
    function completeVerification() {
        
        if (verificationTimer) {
            clearInterval(verificationTimer);
        }
        
        isPhoneVerified = true;
        phoneVerifiedInput.value = '1';
        
        
        // UI 업데이트
        document.querySelector('.phone-verification-group').style.display = 'none';
        verificationGroup.style.display = 'none';
        
        const phoneGroup = phoneInput.closest('.form-group');
        phoneGroup.classList.add('verified');
        
        phoneInput.readOnly = true;
        
        // 휴대폰 번호 도움말 숨기기 (인증 완료 후에는 불필요)
        const phoneFormHelp = document.getElementById('phone-form-help');
        if (phoneFormHelp) {
            phoneFormHelp.style.display = 'none';
        }
        
        // 성공 메시지 표시
        const statusDiv = document.createElement('div');
        statusDiv.className = 'verification-status success';
        statusDiv.innerHTML = '<i class="fas fa-check-circle"></i> 휴대폰 인증이 완료되었습니다.';
        phoneGroup.appendChild(statusDiv);
        
        validateForm();
    }

    // 인증 만료
    function expireVerification() {
        timerDisplay.textContent = '00:00';
        timerDisplay.classList.add('expired');
        verifyCodeBtn.disabled = true;
        verifyCodeBtn.innerHTML = '시간 만료';
        
        Toast.error('인증 시간이 만료되었습니다. 다시 인증번호를 요청해주세요.');
        
        // 인증번호 입력 필드 비활성화
        verificationCodeInput.disabled = true;
        
        // 재발송 버튼 활성화
        setTimeout(() => {
            Loading.button(sendVerificationBtn, false);
        }, 1000);
    }

    // 인증 상태 초기화
    function resetVerification() {
        
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
        validateForm();
    }

    // 발송 버튼 상태 업데이트
    function updateSendButtonState() {
        const phone = phoneInput.value.trim();
        // 🚀 v3.44.0: FormValidator 직접 사용
        const isValidPhone = FormValidator.isValidPhoneStrict(phone) && phone.startsWith('010-');
        const shouldDisable = !isValidPhone || isPhoneVerified;
        
        
        sendVerificationBtn.disabled = shouldDisable;
    }

    // 확인 버튼 상태 업데이트
    function updateVerifyButtonState() {
        const code = verificationCodeInput.value.trim();
        const shouldDisable = code.length !== 4 || timeLeft <= 0;
        
        
        verifyCodeBtn.disabled = shouldDisable;
    }

    // 🚀 v3.30.0: showMessage 함수 제거 (Toast 클래스로 대체됨)

    // 폼 제출 시 최종 검증 및 reCAPTCHA 토큰 생성
    signupForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            Toast.error('모든 필드를 올바르게 입력하고 휴대폰 인증을 완료해주세요.');
            Toast.error('⚠️ 디버깅: 폼 유효성 검사 실패\n\n콘솔 로그를 확인하세요.\n확인을 누르면 계속됩니다.');
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
            
            
            // 제출 직전 단계
            if (window.TopMarketingLoading) {
                window.TopMarketingLoading.setStage('계정 생성 중...');
                window.TopMarketingLoading.setProgress(80);
            }
            
            
            // 폼 제출
            this.submit();
        } catch (error) {
            
            // 오류 시 로딩 숨김
            if (window.TopMarketingLoading) {
                window.TopMarketingLoading.hide();
            }
            
            Toast.error('보안 검증에 실패했습니다. 새로고침 후 다시 시도해주세요.');
            Toast.error('⚠️ 디버깅: reCAPTCHA 토큰 생성 실패\n\n' + error.message + '\n\n콘솔 로그를 확인하세요.\n확인을 누르면 계속됩니다.');
        }
    });

    // 초기화
    validateForm();
    updateSendButtonState();
});
</script>

<?php require_once SRC_PATH . '/views/templates/footer.php'; ?> 