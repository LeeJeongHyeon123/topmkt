<?php
/**
 * 탑마케팅 로그인 페이지
 */
$page_title = '로그인';
$page_description = '탑마케팅에 로그인하여 네트워크 마케팅 커뮤니티에 참여하세요';
$current_page = 'login';

require_once SRC_PATH . '/views/templates/header.php';
?>

<!-- 로그인 페이지 -->
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
            <!-- 로그인 폼 컨테이너 -->
            <div class="auth-form-container">
                <!-- 로고 및 제목 -->
                <div class="auth-header">
                    <div class="auth-logo">
                        <div class="logo-icon">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <span class="logo-text"><?= APP_NAME ?? '탑마케팅' ?></span>
                    </div>
                    <h1 class="auth-title">다시 만나서 반갑습니다</h1>
                    <p class="auth-subtitle">계정에 로그인하여 커뮤니티 활동을 계속하세요</p>
                </div>

                <!-- 에러/성공 메시지 표시 -->
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?= htmlspecialchars($_SESSION['error']) ?></span>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span><?= htmlspecialchars($_SESSION['success']) ?></span>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <!-- 로그인 폼 -->
                <form class="auth-form" method="POST" action="/auth/login" id="login-form">
                    <div class="form-group">
                        <label for="phone" class="form-label">
                            <i class="fas fa-mobile-alt"></i>
                            휴대폰 번호
                        </label>
                        <input 
                            type="tel" 
                            id="phone" 
                            name="phone" 
                            class="form-input" 
                            placeholder="010-1234-5678"
                            value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                            required 
                            autocomplete="tel"
                            pattern="010-[0-9]{3,4}-[0-9]{4}"
                            maxlength="13"
                        >
                        <small class="form-help">회원가입 시 사용한 휴대폰 번호를 입력하세요</small>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i>
                            비밀번호
                        </label>
                        <div class="password-input-wrapper">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-input" 
                                placeholder="비밀번호를 입력하세요"
                                required 
                                autocomplete="current-password"
                            >
                            <button type="button" class="password-toggle" id="password-toggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember" value="1">
                            <span class="checkbox-custom"></span>
                            <span class="checkbox-text">로그인 상태 유지</span>
                        </label>
                        <a href="/auth/forgot-password" class="auth-link">비밀번호를 잊으셨나요?</a>
                    </div>

                    <!-- CSRF 토큰 -->
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    
                    <!-- 리다이렉트 URL -->
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($_GET['redirect'] ?? '') ?>">

                    <button type="submit" class="btn btn-primary-gradient btn-large btn-full">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>로그인</span>
                    </button>
                </form>

                <!-- 회원가입 링크 -->
                <div class="auth-footer">
                    <p class="auth-switch">
                        아직 계정이 없으신가요? 
                        <a href="/auth/signup" class="auth-link">
                            회원가입하기
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </p>
                </div>

                <!-- 관리자 테스트 계정 안내 (개발용) -->
                <?php if (defined('APP_DEBUG') && APP_DEBUG): ?>
                    <div class="dev-notice">
                        <h4>🔧 개발자 테스트 계정</h4>
                        <p><strong>휴대폰:</strong> 010-0000-0000</p>
                        <p><strong>비밀번호:</strong> admin123!</p>
                        <button type="button" class="btn btn-outline-secondary" onclick="fillTestAccount()">
                            테스트 계정으로 자동 입력
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- 사이드 정보 -->
            <div class="auth-side-info">
                <div class="side-info-content">
                    <div class="side-info-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h2>안전한 로그인</h2>
                    <p>최신 보안 기술로 여러분의 계정을 안전하게 보호합니다</p>
                    
                    <div class="security-features">
                        <div class="security-feature">
                            <i class="fas fa-shield-alt"></i>
                            <span>SSL 암호화</span>
                        </div>
                        <div class="security-feature">
                            <i class="fas fa-user-shield"></i>
                            <span>2단계 인증</span>
                        </div>
                        <div class="security-feature">
                            <i class="fas fa-history"></i>
                            <span>로그인 기록</span>
                        </div>
                    </div>

                    <div class="login-benefits">
                        <h3>로그인 후 이용 가능한 서비스</h3>
                        <ul>
                            <li><i class="fas fa-comments"></i> 커뮤니티 참여</li>
                            <li><i class="fas fa-bell"></i> 실시간 알림</li>
                            <li><i class="fas fa-chart-line"></i> 성과 분석 도구</li>
                            <li><i class="fas fa-graduation-cap"></i> 전문가 강의</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* 알림 메시지 스타일 */
.alert {
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    line-height: 1.4;
}

.alert-error {
    background-color: #fee;
    border: 1px solid #fcc;
    color: #c33;
}

.alert-success {
    background-color: #efe;
    border: 1px solid #cfc;
    color: #363;
}

.alert i {
    font-size: 16px;
}

/* 폼 옵션 스타일 */
.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 10px;
}

/* 보안 기능 표시 */
.security-features {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin: 20px 0;
}

.security-feature {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #64748b;
    font-size: 14px;
}

.security-feature i {
    color: #10b981;
    width: 20px;
}

/* 로그인 혜택 */
.login-benefits {
    margin-top: 30px;
}

.login-benefits h3 {
    font-size: 16px;
    margin-bottom: 15px;
    color: #1e293b;
}

.login-benefits ul {
    list-style: none;
    padding: 0;
}

.login-benefits li {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
    color: #64748b;
    font-size: 14px;
}

.login-benefits li i {
    color: #667eea;
    width: 16px;
}

/* 개발자 테스트 안내 */
.dev-notice {
    margin-top: 20px;
    padding: 15px;
    background: #f0f9ff;
    border: 1px solid #0ea5e9;
    border-radius: 6px;
    font-size: 13px;
}

.dev-notice h4 {
    margin: 0 0 10px 0;
    color: #0369a1;
}

.dev-notice p {
    margin: 5px 0;
    color: #0369a1;
}

/* 반응형 */
@media (max-width: 768px) {
    .form-options {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // DOM 요소들
    const phoneInput = document.getElementById('phone');
    const passwordInput = document.getElementById('password');
    const passwordToggle = document.getElementById('password-toggle');
    const loginForm = document.getElementById('login-form');

    // 휴대폰 번호 포맷팅
    phoneInput.addEventListener('input', function() {
        let value = this.value.replace(/[^0-9]/g, '');
        
        // 010으로 시작하지 않으면 에러 표시
        if (value.length > 0 && !value.startsWith('010')) {
            this.setCustomValidity('010으로 시작하는 휴대폰 번호만 입력할 수 있습니다.');
            this.classList.add('error');
        } else {
            this.setCustomValidity('');
            this.classList.remove('error');
        }
        
        if (value.length >= 3) {
            value = value.substring(0, 3) + '-' + value.substring(3);
        }
        if (value.length >= 8) {
            value = value.substring(0, 8) + '-' + value.substring(8, 12);
        }
        
        this.value = value;
    });

    // 비밀번호 표시/숨김 토글
    passwordToggle.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        const icon = this.querySelector('i');
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    });

    // 폼 제출 시 추가 검증
    loginForm.addEventListener('submit', function(e) {
        const phone = phoneInput.value.trim();
        const password = passwordInput.value.trim();
        
        if (!phone || !password) {
            e.preventDefault();
            showMessage('휴대폰 번호와 비밀번호를 모두 입력해주세요.', 'error');
            return;
        }
        
        if (!isValidPhoneFormat(phone)) {
            e.preventDefault();
            showMessage('010으로 시작하는 올바른 휴대폰 번호를 입력해주세요.', 'error');
            return;
        }
    });

    // 휴대폰 번호 형식 검증
    function isValidPhoneFormat(phone) {
        const pattern = /^010-[0-9]{3,4}-[0-9]{4}$/;
        return pattern.test(phone);
    }

    // 메시지 표시
    function showMessage(message, type) {
        // 기존 메시지 제거
        const existingAlert = document.querySelector('.alert-message');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-message`;
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

    // 개발용 테스트 계정 자동 입력
    window.fillTestAccount = function() {
        phoneInput.value = '010-0000-0000';
        passwordInput.value = 'admin123!';
        phoneInput.focus();
    };
});
</script>

<?php require_once SRC_PATH . '/views/templates/footer.php'; ?> 