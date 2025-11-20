<?php
/**
 * 탑마케팅 로그인 페이지
 *
 * 📦 사용 가능한 컴포넌트 목록
 *
 * UI 컴포넌트:
 * - Button.php           : renderButton($text, $type, $size, $options)
 * - Modal.php            : renderModal($id, $title, $content) (예정)
 * - Alert.php            : showAlert($message, $type) (예정)
 *
 * 📖 자세한 사용법: /docs/23.컴포넌트_사용_가이드.md
 */
$page_title = '로그인';
$page_description = '탑마케팅에 로그인하여 네트워크 마케팅 커뮤니티에 참여하세요';
$current_page = 'login';

require_once SRC_PATH . '/components/ui/Button.php';
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
                            <i data-lucide="rocket" width="32" height="32"></i>
                        </div>
                        <span class="logo-text"><?= APP_NAME ?? '탑마케팅' ?></span>
                    </div>
                    <h1 class="auth-title">다시 만나서 반갑습니다</h1>
                    <p class="auth-subtitle">계정에 로그인하여 커뮤니티 활동을 계속하세요</p>
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

                <!-- 로그인 폼 -->
                <form class="auth-form" method="POST" action="/auth/login" id="login-form">
                    <div class="form-group">
                        <label for="phone" class="form-label">
                            <i data-lucide="smartphone" width="20" height="20"></i>
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
                            <i data-lucide="lock" width="20" height="20"></i>
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
                                <i data-lucide="eye" width="20" height="20"></i>
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
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($_SESSION['login_redirect'] ?? '') ?>">

                    <?= renderButton('로그인', 'primary', 'lg', [
                        'buttonType' => 'submit',
                        'icon' => 'log-in',
                        'fullWidth' => true,
                        'class' => 'btn-primary-gradient'
                    ]) ?>
                </form>

                <!-- 회원가입 링크 -->
                <div class="auth-footer">
                    <p class="auth-switch">
                        아직 계정이 없으신가요?
                        <a href="/auth/signup" class="auth-link">
                            회원가입하기
                            <i data-lucide="arrow-right" width="18" height="18"></i>
                        </a>
                    </p>
                </div>

                <!-- 관리자 테스트 계정 안내 (개발용) -->
                <?php if (defined('APP_DEBUG') && APP_DEBUG): ?>
                    <div class="dev-notice">
                        <h4>🔧 개발자 테스트 계정</h4>
                        <p><strong>휴대폰:</strong> 010-0000-0000</p>
                        <p><strong>비밀번호:</strong> admin123!</p>
                        <?= renderButton('테스트 계정으로 자동 입력', 'outline', 'md', [
                            'onclick' => 'fillTestAccount()'
                        ]) ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- 사이드 정보 -->
            <div class="auth-side-info">
                <div class="side-info-content">
                    <div class="side-info-icon">
                        <i data-lucide="lock" width="64" height="64"></i>
                    </div>
                    <h2>안전한 로그인</h2>
                    <p>최신 보안 기술로 여러분의 계정을 안전하게 보호합니다</p>

                    <div class="security-features">
                        <div class="security-feature">
                            <i data-lucide="shield" width="24" height="24"></i>
                            <span>SSL 암호화</span>
                        </div>
                        <div class="security-feature">
                            <i data-lucide="shield-check" width="24" height="24"></i>
                            <span>2단계 인증</span>
                        </div>
                        <div class="security-feature">
                            <i data-lucide="history" width="24" height="24"></i>
                            <span>로그인 기록</span>
                        </div>
                    </div>

                    <div class="login-benefits">
                        <h3>로그인 후 이용 가능한 서비스</h3>
                        <ul>
                            <li><i data-lucide="message-square" width="20" height="20"></i> 커뮤니티 참여</li>
                            <li><i data-lucide="bell" width="20" height="20"></i> 실시간 알림</li>
                            <li><i data-lucide="trending-up" width="20" height="20"></i> 성과 분석 도구</li>
                            <li><i data-lucide="graduation-cap" width="20" height="20"></i> 전문가 강의</li>
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
    color: #ffffff;
    font-size: 14px;
    font-weight: 600;
}

.security-feature i {
    color: #10dc60;
    width: 20px;
}

/* 로그인 혜택 */
.login-benefits {
    margin-top: 30px;
}

.login-benefits h3 {
    font-size: 16px;
    margin-bottom: 15px;
    color: #ffffff;
    font-weight: 700;
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
    color: #f8fafc;
    font-size: 14px;
    font-weight: 600;
}

.login-benefits li i {
    color: #3b82f6;
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

/* 추가 가독성 개선 */
.auth-side-info h2 {
    color: #ffffff !important;
    font-weight: 700;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.auth-side-info > .side-info-content > p {
    color: #f8fafc !important;
    font-weight: 600;
    line-height: 1.6;
}

/* 반응형 */
@media (max-width: 768px) {
    .form-options {
        flex-direction: column;
        align-items: flex-start;
    }
    
    /* 모바일에서 더 높은 대비 */
    .security-feature {
        color: #f8fafc;
        font-weight: 600;
    }
    
    .login-benefits li {
        color: #e2e8f0;
        font-weight: 600;
    }
    
    .login-benefits h3 {
        color: #ffffff;
        font-weight: 700;
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
    let isDeleting = false;

    // beforeinput으로 삭제 감지
    phoneInput.addEventListener('beforeinput', function(e) {
        if (e.inputType === 'deleteContentBackward' || e.inputType === 'deleteContentForward') {
            isDeleting = true;
        } else {
            isDeleting = false;
        }
    });

    phoneInput.addEventListener('input', function(e) {
        let value = this.value.replace(/[^0-9]/g, '');

        // 빈 값이면 그대로 허용
        if (value.length === 0) {
            this.value = '';
            this.setCustomValidity('');
            this.classList.remove('error');
            return;
        }

        // 삭제 중이고 끝 하이픈 문제 방지
        if (isDeleting) {
            // "010-2659-" → "010-2659" (7자리) → 하이픈 추가 방지
            if (value.length === 7) {
                this.value = value.substring(0, 3) + '-' + value.substring(3);
                isDeleting = false;
                return;
            }
            // "010-" → "010" (3자리) → 하이픈 추가 방지
            if (value.length === 3) {
                this.value = value;
                isDeleting = false;
                return;
            }
        }

        // 010으로 시작하지 않으면 에러 표시
        if (value.length > 0 && !value.startsWith('010')) {
            this.setCustomValidity('010으로 시작하는 휴대폰 번호만 입력할 수 있습니다.');
            this.classList.add('error');
        } else {
            this.setCustomValidity('');
            this.classList.remove('error');
        }

        // 자동 하이픈 추가
        let formatted = value;
        if (value.length >= 3 && value.length < 7) {
            formatted = value.substring(0, 3) + '-' + value.substring(3);
        } else if (value.length >= 7) {
            formatted = value.substring(0, 3) + '-' + value.substring(3, 7) + '-' + value.substring(7, 11);
        }

        this.value = formatted;
        isDeleting = false;
    });

    // 비밀번호 표시/숨김 토글
    passwordToggle.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);

        // Lucide 아이콘 토글
        const icon = this.querySelector('i');
        const newIcon = type === 'password' ? 'eye' : 'eye-off';
        icon.setAttribute('data-lucide', newIcon);

        // Lucide 아이콘 재초기화
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });

    // 폼 제출 시 추가 검증
    loginForm.addEventListener('submit', function(e) {
        const phone = phoneInput.value.trim();
        const password = passwordInput.value.trim();

        if (!phone || !password) {
            e.preventDefault();
            // 🚀 v3.30.0: Toast 알림 시스템 사용
            Toast.error('휴대폰 번호와 비밀번호를 모두 입력해주세요.');
            return;
        }

        // 🚀 v3.44.0: FormValidator 직접 사용
        if (!FormValidator.isValidPhoneStrict(phone)) {
            e.preventDefault();
            // 🚀 v3.30.0: Toast 알림 시스템 사용
            Toast.error('010으로 시작하는 올바른 휴대폰 번호를 입력해주세요.');
            return;
        }
    });

    // 🚀 v3.44.0: 중복 함수 제거 (FormValidator 직접 사용으로 전환)
    // - isValidPhoneFormat() 제거 → FormValidator.isValidPhoneStrict() 직접 호출

    // 🚀 v3.30.0: showMessage 함수 제거 (Toast 클래스로 대체됨)

    // 개발용 테스트 계정 자동 입력
    window.fillTestAccount = function() {
        phoneInput.value = '010-0000-0000';
        passwordInput.value = 'admin123!';
        phoneInput.focus();
    };

    // Lucide 아이콘 초기화 (v5.0.0)
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});
</script>

<?php require_once SRC_PATH . '/views/templates/footer.php'; ?> 