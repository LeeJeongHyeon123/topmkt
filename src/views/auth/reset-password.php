<?php
/**
 * 탑마케팅 비밀번호 재설정 페이지
 */
$page_title = '비밀번호 재설정';
$page_description = '인증 코드를 입력하여 새로운 비밀번호를 설정하세요';
$current_page = 'reset-password';

require_once SRC_PATH . '/views/templates/header.php';
?>

<!-- 비밀번호 재설정 페이지 -->
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
            <!-- 비밀번호 재설정 폼 컨테이너 -->
            <div class="auth-form-container">
                <!-- 로고 및 제목 -->
                <div class="auth-header">
                    <div class="auth-logo">
                        <div class="logo-icon">
                            <i data-lucide="shield" width="32" height="32"></i>
                        </div>
                        <span class="logo-text"><?= APP_NAME ?? '탑마케팅' ?></span>
                    </div>
                    <h1 class="auth-title">비밀번호 재설정</h1>
                    <p class="auth-subtitle">
                        <?= isset($_SESSION['password_reset_phone']) ? 
                            htmlspecialchars($_SESSION['password_reset_phone']) . '로 발송된 인증 코드와 새 비밀번호를 입력하세요' : 
                            '인증 코드와 새 비밀번호를 입력하세요' ?>
                    </p>
                </div>

                <!-- 에러/성공 메시지 표시 (Toast로 전환) -->
                <?php if (isset($_SESSION['error'])): ?>
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Toast.error('<?= addslashes(htmlspecialchars($_SESSION['error'])) ?>');
                    });
                    </script>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Toast.success('<?= addslashes(htmlspecialchars($_SESSION['success'])) ?>');
                    });
                    </script>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <!-- 비밀번호 재설정 폼 -->
                <form id="resetPasswordForm" method="POST" action="/auth/reset-password" class="auth-form">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    
                    <!-- 인증 코드 입력 -->
                    <div class="input-group">
                        <label for="verification_code" class="input-label">
                            <i data-lucide="key" width="20" height="20"></i>
                            인증 코드
                        </label>
                        <input 
                            type="text" 
                            id="verification_code" 
                            name="verification_code" 
                            class="input-field verification-code-input" 
                            placeholder="6자리 인증 코드 입력" 
                            required
                            maxlength="6"
                            pattern="[0-9]{6}"
                            autocomplete="one-time-code"
                        >
                        <div class="input-help">
                            SMS로 발송된 6자리 인증 코드를 입력하세요 (5분간 유효)
                        </div>
                    </div>

                    <!-- 새 비밀번호 입력 -->
                    <div class="input-group">
                        <label for="new_password" class="input-label">
                            <i data-lucide="lock" width="20" height="20"></i>
                            새 비밀번호
                        </label>
                        <div class="password-input-wrapper">
                            <input 
                                type="password" 
                                id="new_password" 
                                name="new_password" 
                                class="input-field password-input" 
                                placeholder="새 비밀번호 입력" 
                                required
                                minlength="8"
                                autocomplete="new-password"
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                                <i data-lucide="eye" width="20" height="20" id="new_password_eye"></i>
                            </button>
                        </div>
                        <div class="input-help">
                            최소 8자 이상의 안전한 비밀번호를 입력하세요
                        </div>
                    </div>

                    <!-- 비밀번호 확인 -->
                    <div class="input-group">
                        <label for="confirm_password" class="input-label">
                            <i data-lucide="lock" width="20" height="20"></i>
                            비밀번호 확인
                        </label>
                        <div class="password-input-wrapper">
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                class="input-field password-input" 
                                placeholder="비밀번호 다시 입력" 
                                required
                                minlength="8"
                                autocomplete="new-password"
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                <i data-lucide="eye" width="20" height="20" id="confirm_password_eye"></i>
                            </button>
                        </div>
                        <div class="password-match-indicator" id="passwordMatchIndicator" style="display: none;">
                            <i data-lucide="check-circle" width="20" height="20" class="text-success"></i>
                            <span>비밀번호가 일치합니다</span>
                        </div>
                    </div>

                    <!-- 제출 버튼 -->
                    <button type="submit" class="btn btn-primary btn-auth" id="submitButton">
                        <span class="btn-text">비밀번호 재설정</span>
                        <span class="btn-loading" style="display: none;">
                            <i data-lucide="loader-2" width="20" height="20" class="lucide-spin"></i>
                            처리 중...
                        </span>
                    </button>
                </form>

                <!-- 추가 링크 -->
                <div class="auth-links">
                    <a href="/auth/forgot-password" class="auth-link">
                        <i data-lucide="arrow-left" width="18" height="18"></i>
                        다시 인증 코드 발송
                    </a>
                    <a href="/auth/login" class="auth-link">
                        <i data-lucide="log-in" width="18" height="18"></i>
                        로그인 페이지로 돌아가기
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* 비밀번호 재설정 전용 스타일 */
.auth-section {
    min-height: 100vh;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.auth-background {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    overflow: hidden;
}

.auth-gradient-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.9) 0%, rgba(118, 75, 162, 0.9) 100%);
}

.auth-shapes {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
}

.auth-shape {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
}

.auth-shape-1 {
    width: 300px;
    height: 300px;
    top: -150px;
    right: -150px;
    animation: float 6s ease-in-out infinite;
}

.auth-shape-2 {
    width: 200px;
    height: 200px;
    bottom: -100px;
    left: -100px;
    animation: float 8s ease-in-out infinite reverse;
}

.auth-shape-3 {
    width: 150px;
    height: 150px;
    top: 50%;
    left: 20%;
    animation: float 10s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(10deg); }
}

.auth-content {
    position: relative;
    z-index: 2;
    width: 100%;
    max-width: 450px;
    margin: 0 auto;
}

.auth-form-container {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 3rem;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.auth-header {
    text-align: center;
    margin-bottom: 2rem;
}

.auth-logo {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}

.logo-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.logo-text {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2d3748;
    letter-spacing: -0.025em;
}

.auth-title {
    font-size: 1.875rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 0.5rem;
}

.auth-subtitle {
    color: #718096;
    font-size: 0.875rem;
    line-height: 1.5;
}

.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
}

.alert-error {
    background-color: #fed7d7;
    color: #c53030;
    border: 1px solid #feb2b2;
}

.alert-success {
    background-color: #c6f6d5;
    color: #2f855a;
    border: 1px solid #9ae6b4;
}

.auth-form {
    margin-bottom: 2rem;
}

.input-group {
    margin-bottom: 1.5rem;
}

.input-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #4a5568;
    margin-bottom: 0.5rem;
}

.input-field {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.2s ease-in-out;
    background-color: #ffffff;
}

.input-field:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.verification-code-input {
    text-align: center;
    font-size: 1.25rem;
    letter-spacing: 0.25em;
    font-weight: 600;
}

.password-input-wrapper {
    position: relative;
}

.password-input {
    padding-right: 3rem;
}

.password-toggle {
    position: absolute;
    right: 0.875rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #718096;
    cursor: pointer;
    font-size: 1rem;
    transition: color 0.2s ease-in-out;
}

.password-toggle:hover {
    color: #4a5568;
}

.input-help {
    margin-top: 0.5rem;
    font-size: 0.75rem;
    color: #718096;
}

.password-match-indicator {
    margin-top: 0.5rem;
    font-size: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.text-success {
    color: #2f855a;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.875rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
    position: relative;
    overflow: hidden;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 14px 0 rgba(102, 126, 234, 0.4);
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px 0 rgba(102, 126, 234, 0.5);
}

.btn-primary:active {
    transform: translateY(0);
}

.btn-primary:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.btn-auth {
    width: 100%;
    margin-bottom: 1rem;
}

.btn-loading {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.auth-links {
    text-align: center;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.auth-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    color: #667eea;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    transition: color 0.2s ease-in-out;
}

.auth-link:hover {
    color: #764ba2;
}

/* 모바일 반응형 */
@media (max-width: 480px) {
    .auth-form-container {
        padding: 2rem 1.5rem;
        margin: 1rem;
        border-radius: 16px;
    }

    .auth-title {
        font-size: 1.5rem;
    }

    .auth-subtitle {
        font-size: 0.8rem;
    }
}

/* Lucide spinner animation */
@keyframes lucide-spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.lucide-spin {
    animation: lucide-spin 1s linear infinite;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    const form = document.getElementById('resetPasswordForm');
    const verificationCodeInput = document.getElementById('verification_code');
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const submitButton = document.getElementById('submitButton');
    const btnText = submitButton.querySelector('.btn-text');
    const btnLoading = submitButton.querySelector('.btn-loading');
    const passwordMatchIndicator = document.getElementById('passwordMatchIndicator');
    
    // 인증 코드 입력 시 숫자만 허용
    verificationCodeInput.addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/[^0-9]/g, '').slice(0, 6);
    });
    
    // 비밀번호 일치 확인
    function checkPasswordMatch() {
        const newPassword = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        if (confirmPassword.length > 0) {
            if (newPassword === confirmPassword) {
                passwordMatchIndicator.style.display = 'flex';
                confirmPasswordInput.style.borderColor = '#2f855a';
                return true;
            } else {
                passwordMatchIndicator.style.display = 'none';
                confirmPasswordInput.style.borderColor = '#e53e3e';
                return false;
            }
        } else {
            passwordMatchIndicator.style.display = 'none';
            confirmPasswordInput.style.borderColor = '#e2e8f0';
            return false;
        }
    }
    
    confirmPasswordInput.addEventListener('input', checkPasswordMatch);
    newPasswordInput.addEventListener('input', checkPasswordMatch);
    
    // 폼 제출 처리
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // 유효성 검사
        if (!verificationCodeInput.value || verificationCodeInput.value.length !== 6) {
            Toast.error('6자리 인증 코드를 입력해주세요.');
            return;
        }
        
        if (!newPasswordInput.value || newPasswordInput.value.length < 8) {
            Toast.error('비밀번호는 8자 이상이어야 합니다.');
            return;
        }
        
        if (newPasswordInput.value !== confirmPasswordInput.value) {
            Toast.error('비밀번호가 일치하지 않습니다.');
            return;
        }
        
        // 버튼 로딩 상태
        submitButton.disabled = true;
        btnText.style.display = 'none';
        btnLoading.style.display = 'inline-flex';
        
        // FormData 생성
        const formData = new FormData(form);

        // v3.42.0: ApiClient 사용 (FormData는 자동으로 multipart/form-data로 처리)
        ApiClient.post('/auth/reset-password', formData, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            noLoading: true
        })
        .then(data => {
            if (data.success) {
                Toast.success(data.message || '비밀번호가 성공적으로 변경되었습니다.');
                setTimeout(() => {
                    window.location.href = '/auth/login';
                }, 2000);
            } else {
                Toast.error(data.error || '오류가 발생했습니다.');
            }
        })
        .catch(error => {
            Toast.error('네트워크 오류가 발생했습니다.');
        })
        .finally(() => {
            // 버튼 상태 복원
            submitButton.disabled = false;
            btnText.style.display = 'inline';
            btnLoading.style.display = 'none';
        });
    });

    // 🚀 v3.30.0: showAlert 함수 제거 (Toast 클래스로 대체됨)
});

// 비밀번호 토글 함수
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const eyeIcon = document.getElementById(inputId + '_eye');

    if (input.type === 'password') {
        input.type = 'text';
        eyeIcon.setAttribute('data-lucide', 'eye-off');
        eyeIcon.setAttribute('width', '20');
        eyeIcon.setAttribute('height', '20');
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    } else {
        input.type = 'password';
        eyeIcon.setAttribute('data-lucide', 'eye');
        eyeIcon.setAttribute('width', '20');
        eyeIcon.setAttribute('height', '20');
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }
}
</script>

<?php require_once SRC_PATH . '/views/templates/footer.php'; ?>