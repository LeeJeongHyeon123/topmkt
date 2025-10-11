<?php
/**
 * 인증 관련 컨트롤러
 */

require_once SRC_PATH . '/helpers/SmsHelper.php';
require_once SRC_PATH . '/helpers/JWTHelper.php';
require_once SRC_PATH . '/helpers/ResponseHelper.php';
require_once SRC_PATH . '/models/User.php';

class AuthController {
    
    private $userModel;
    private $db;
    
    public function __construct() {
        // 세션이 시작되지 않았으면 시작
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // CSRF 토큰 생성
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        // 데이터베이스 연결 초기화 (싱글톤 패턴 사용)
        require_once SRC_PATH . '/config/database.php';
        $this->db = Database::getInstance();
        
        // User 모델 초기화
        $this->userModel = new User();
    }
    
    /**
     * 로그인 페이지 표시
     */
    public function showLogin() {
        // 리다이렉트 URL을 세션에 저장 (로그인 성공 후 사용)
        if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
            $_SESSION['login_redirect'] = $_GET['redirect'];
        }

        include SRC_PATH . '/views/auth/login.php';
    }
    
    /**
     * 로그인 처리
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            return;
        }
        
        // JSON 요청인지 확인
        $isJsonRequest = $this->isJsonRequest();
        
        if ($isJsonRequest) {
            // JSON API 요청 처리
            $this->handleJsonLogin();
        } else {
            // 일반 Form 요청 처리
            $this->handleFormLogin();
        }
    }
    
    /**
     * JSON API 로그인 요청 처리
     */
    private function handleJsonLogin() {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => '잘못된 JSON 형식입니다.']);
            return;
        }
        
        $phone = $this->sanitizePhone($input['phone'] ?? '');
        $password = $input['password'] ?? '';
        
        if (!$phone || empty($password)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => '휴대폰 번호와 비밀번호를 모두 입력해주세요.']);
            return;
        }
        
        // 휴대폰 번호 유효성 검사
        if (!$this->isValidPhone($phone)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => '010으로 시작하는 올바른 휴대폰 번호를 입력해주세요.']);
            return;
        }
        
        try {
            // 사용자 인증
            $user = $this->userModel->login($phone, $password);

            // JWT 기반 로그인 세션 생성
            $tokens = $this->createJWTSession($user, $input['remember'] ?? false);

            echo json_encode([
                'success' => true,
                'message' => $user['nickname'] . '님, 환영합니다!',
                'user' => [
                    'id' => $user['id'],
                    'phone' => $user['phone'],
                    'nickname' => $user['nickname'],
                    'role' => $user['role'] ?? 'GENERAL'
                ],
                'tokens' => [
                    'access_token' => $tokens['access_token'],
                    'expires_in' => 3600, // 1시간
                    'has_refresh_token' => isset($_COOKIE['refresh_token'])
                ]
            ]);

        } catch (Exception $e) {
            // 로그인 실패 원인에 따라 HTTP 상태 코드 구분
            $message = $e->getMessage();

            if (strpos($message, '등록되지 않은') !== false) {
                http_response_code(404);
            } elseif (strpos($message, '계정이 잠겨있습니다') !== false) {
                http_response_code(423); // Locked
            } elseif (strpos($message, '비밀번호가 일치하지 않습니다') !== false) {
                http_response_code(401);
            } elseif (strpos($message, '정지되었습니다') !== false || strpos($message, '비활성화') !== false) {
                http_response_code(403); // Forbidden
            } else {
                http_response_code(400);
            }

            echo json_encode(['success' => false, 'message' => $message]);
        }
    }
    
    /**
     * 일반 Form 로그인 요청 처리
     */
    private function handleFormLogin() {
        // CSRF 토큰 검증
        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = '보안 토큰이 일치하지 않습니다. 다시 시도해주세요.';
            header('Location: /auth/login');
            return;
        }
        
        $phone = $this->sanitizePhone($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $redirect = $_POST['redirect'] ?? '';
        $remember = isset($_POST['remember']) && $_POST['remember'] === '1';
        
        if (!$phone || empty($password)) {
            $_SESSION['error'] = '휴대폰 번호와 비밀번호를 모두 입력해주세요.';
            $redirectUrl = !empty($redirect) ? '/auth/login?redirect=' . urlencode($redirect) : '/auth/login';
            header('Location: ' . $redirectUrl);
            return;
        }
        
        // 휴대폰 번호 유효성 검사
        if (!$this->isValidPhone($phone)) {
            $_SESSION['error'] = '010으로 시작하는 올바른 휴대폰 번호를 입력해주세요.';
            $redirectUrl = !empty($redirect) ? '/auth/login?redirect=' . urlencode($redirect) : '/auth/login';
            header('Location: ' . $redirectUrl);
            return;
        }
        
        try {
            // 사용자 인증
            $user = $this->userModel->login($phone, $password);

            // JWT 기반 로그인 세션 생성
            $this->createJWTSession($user, $remember);

            $_SESSION['success'] = $user['nickname'] . '님, 환영합니다!';

            // 리다이렉트 URL이 있으면 해당 페이지로, 없으면 메인 페이지로
            if (!empty($redirect) && $this->isValidRedirectUrl($redirect)) {
                header('Location: ' . $redirect);
            } else {
                header('Location: /');
            }
            exit;

        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $redirectUrl = !empty($redirect) ? '/auth/login?redirect=' . urlencode($redirect) : '/auth/login';
            header('Location: ' . $redirectUrl);
            return;
        }
    }
    
    /**
     * 회원가입 페이지 표시
     */
    public function showSignup() {
        include SRC_PATH . '/views/auth/signup.php';
    }
    
    /**
     * 인증번호 발송
     */
    public function sendVerification() {
        // AJAX 요청만 처리
        if (!$this->isAjaxRequest()) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $phone = $this->sanitizePhone($input['phone'] ?? '');
        $recaptchaToken = $input['recaptcha_token'] ?? '';
        
        // reCAPTCHA 검증
        if (!$this->verifyRecaptcha($recaptchaToken, 'send_verification')) {
            echo json_encode([
                'success' => false, 
                'message' => '보안 검증에 실패했습니다. 다시 시도해주세요.'
            ]);
            return;
        }
        
        // 입력 검증
        if (!$this->isValidPhone($phone)) {
            echo json_encode([
                'success' => false, 
                'message' => '010으로 시작하는 올바른 휴대폰 번호를 입력해주세요.'
            ]);
            return;
        }
        
        // 010 번호 추가 검증
        if (!$this->isValidKoreanMobile($phone)) {
            echo json_encode([
                'success' => false, 
                'message' => '010으로 시작하는 한국 휴대폰 번호만 사용할 수 있습니다.'
            ]);
            return;
        }
        
        // SMS 발송 제한 확인 (스팸 방지)
        if ($this->isSmsRateLimited($phone)) {
            echo json_encode([
                'success' => false, 
                'message' => '너무 많은 요청입니다. 1분 후 다시 시도해주세요.'
            ]);
            return;
        }
        
        // TODO: 휴대폰 번호 중복 검사
        // if ($this->isPhoneExists($phone)) {
        //     echo json_encode(['success' => false, 'message' => '이미 가입된 휴대폰 번호입니다.']);
        //     return;
        // }
        
        // 인증번호 생성 (4자리)
        $verificationCode = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        
        // 세션에 인증번호와 만료시간 저장
        $_SESSION['verification_code'] = $verificationCode;
        $_SESSION['verification_phone'] = $phone;
        $_SESSION['verification_expires'] = time() + 180; // 3분 후 만료
        
        // SMS 발송 제한 기록
        $this->recordSmsRequest($phone);
        
        // SMS 발송
        $result = sendAuthCodeSms($phone, $verificationCode);
        
        if ($result['success']) {
            echo json_encode([
                'success' => true,
                'message' => '인증번호가 발송되었습니다.',
                'expires_in' => 180
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'SMS 발송에 실패했습니다. 잠시 후 다시 시도해주세요.'
            ]);
        }
    }
    
    /**
     * 인증번호 확인
     */
    public function verifyCode() {
        // AJAX 요청만 처리
        if (!$this->isAjaxRequest()) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $phone = $this->sanitizePhone($input['phone'] ?? '');
        $code = $input['code'] ?? '';
        
        // 입력 검증
        if (!$this->isValidPhone($phone) || !preg_match('/^\d{4}$/', $code)) {
            echo json_encode([
                'success' => false,
                'message' => '입력값이 올바르지 않습니다.'
            ]);
            return;
        }
        
        // 세션에서 인증 정보 확인
        $sessionCode = $_SESSION['verification_code'] ?? '';
        $sessionPhone = $_SESSION['verification_phone'] ?? '';
        $expiresAt = $_SESSION['verification_expires'] ?? 0;
        
        // 인증번호 만료 확인
        if (time() > $expiresAt) {
            unset($_SESSION['verification_code'], $_SESSION['verification_phone'], $_SESSION['verification_expires']);
            echo json_encode([
                'success' => false,
                'message' => '인증 시간이 만료되었습니다. 다시 인증번호를 요청해주세요.'
            ]);
            return;
        }
        
        // 휴대폰 번호와 인증번호 일치 확인
        if ($phone !== $sessionPhone || $code !== $sessionCode) {
            echo json_encode([
                'success' => false,
                'message' => '인증번호가 일치하지 않습니다.'
            ]);
            return;
        }
        
        // 인증 성공 - 세션에 인증 완료 표시
        $_SESSION['phone_verified'] = $phone;
        $_SESSION['phone_verified_at'] = time();
        
        // 사용한 인증번호 정보 삭제
        unset($_SESSION['verification_code'], $_SESSION['verification_phone'], $_SESSION['verification_expires']);
        
        echo json_encode([
            'success' => true,
            'message' => '휴대폰 인증이 완료되었습니다.'
        ]);
    }
    
    /**
     * 회원가입 처리
     */
    public function signup() {
        error_log('🚀 회원가입 처리 시작');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log('❌ 잘못된 HTTP 메서드: ' . $_SERVER['REQUEST_METHOD']);
            header('HTTP/1.1 405 Method Not Allowed');
            return;
        }
        
        // JSON 요청인지 확인
        $isJsonRequest = $this->isJsonRequest();
        
        if ($isJsonRequest) {
            // JSON API 요청 처리
            $this->handleJsonSignup();
        } else {
            // 일반 Form 요청 처리
            $this->handleFormSignup();
        }
    }
    
    /**
     * JSON API 회원가입 요청 처리
     */
    private function handleJsonSignup() {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => '잘못된 JSON 형식입니다.']);
            return;
        }
        
        $phone = $this->sanitizePhone($input['phone'] ?? '');
        $nickname = $this->sanitizeInput($input['nickname'] ?? '');
        $email = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = $input['password'] ?? '';
        $passwordConfirm = $input['password_confirm'] ?? '';
        
        // 입력 검증
        $errors = [];
        
        if (empty($nickname)) {
            $errors[] = '닉네임을 입력해주세요.';
        } elseif (mb_strlen($nickname, 'UTF-8') < 2 || mb_strlen($nickname, 'UTF-8') > 20) {
            $errors[] = '닉네임은 2자 이상 20자 이하로 입력해주세요.';
        } elseif (!preg_match('/^[가-힣a-zA-Z0-9_]+$/', $nickname)) {
            $errors[] = '닉네임은 한글, 영문, 숫자, 언더스코어만 사용할 수 있습니다.';
        }
        
        if (!$this->isValidPhone($phone)) {
            $errors[] = '010으로 시작하는 올바른 휴대폰 번호를 입력해주세요.';
        }
        
        if (empty($password)) {
            $errors[] = '비밀번호를 입력해주세요.';
        } elseif (strlen($password) < 8) {
            $errors[] = '비밀번호는 8자 이상이어야 합니다.';
        }
        
        if ($password !== $passwordConfirm) {
            $errors[] = '비밀번호가 일치하지 않습니다.';
        }
        
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
            return;
        }
        
        try {
            // 회원가입 처리
            $result = $this->userModel->create([
                'phone' => $phone,
                'nickname' => $nickname,
                'email' => $email,
                'password' => $password,
                'role' => 'GENERAL',
                'terms_accepted' => true,
                'marketing_accepted' => false
            ]);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => '회원가입이 완료되었습니다.',
                    'redirect' => '/auth/login'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => '회원가입 처리 중 오류가 발생했습니다.']);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    /**
     * 일반 Form 회원가입 요청 처리
     */
    private function handleFormSignup() {
        error_log('📥 POST 데이터 수신: ' . json_encode(array_keys($_POST)));
        
        // CSRF 토큰 검증
        $csrfToken = $_POST['csrf_token'] ?? '';
        error_log('🛡️ CSRF 토큰 검증: ' . substr($csrfToken, 0, 10) . '...');
        
        if (!$this->verifyCsrfToken($csrfToken)) {
            error_log('❌ CSRF 토큰 검증 실패');
            error_log('🔍 세션 CSRF: ' . ($_SESSION['csrf_token'] ?? 'NULL'));
            error_log('🔍 POST CSRF: ' . $csrfToken);
            $_SESSION['error'] = '보안 토큰이 일치하지 않습니다. 다시 시도해주세요.';
            $_SESSION['debug_info'] = 'CSRF 토큰 불일치 - 페이지를 새로고침하고 다시 시도하세요.';
            error_log('🚨 디버깅: 3초 후 리다이렉트됩니다. 로그를 확인하세요.');
            sleep(3); // 디버깅을 위한 지연
            header('Location: /auth/signup');
            return;
        }
        
        error_log('✅ CSRF 토큰 검증 성공');
        
        // reCAPTCHA 검증
        $recaptchaToken = $_POST['recaptcha_token'] ?? '';
        error_log('🛡️ reCAPTCHA 토큰 검증: ' . substr($recaptchaToken, 0, 10) . '...');
        
        if (!$this->verifyRecaptcha($recaptchaToken, 'signup')) {
            error_log('❌ reCAPTCHA 검증 실패');
            $_SESSION['error'] = '보안 검증에 실패했습니다. 다시 시도해주세요.';
            header('Location: /auth/signup');
            return;
        }
        
        error_log('✅ reCAPTCHA 검증 성공');
        
        $phone = $this->sanitizePhone($_POST['phone'] ?? '');
        $nickname = $this->sanitizeInput($_POST['nickname'] ?? '');
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        $termsAccepted = isset($_POST['terms']) && $_POST['terms'] === '1';
        $marketingAccepted = isset($_POST['marketing']) && $_POST['marketing'] === '1';
        $phoneVerified = $_POST['phone_verified'] ?? '0';
        
        error_log('📊 입력 데이터 파싱 완료: ' . json_encode([
            'phone' => $phone,
            'nickname' => $nickname,
            'email' => $email,
            'passwordLength' => strlen($password),
            'passwordConfirmLength' => strlen($passwordConfirm),
            'termsAccepted' => $termsAccepted,
            'marketingAccepted' => $marketingAccepted,
            'phoneVerified' => $phoneVerified
        ]));
        
        // 입력 검증
        $errors = [];
        
        // 닉네임 검증
        error_log('🔍 닉네임 검증 시작: ' . $nickname);
        if (empty($nickname)) {
            $errors[] = '닉네임을 입력해주세요.';
            error_log('❌ 닉네임 비어있음');
        } elseif (mb_strlen($nickname, 'UTF-8') < 2 || mb_strlen($nickname, 'UTF-8') > 20) {
            $errors[] = '닉네임은 2자 이상 20자 이하로 입력해주세요.';
            error_log('❌ 닉네임 길이 오류: ' . strlen($nickname));
        } elseif (!preg_match('/^[가-힣a-zA-Z0-9_]+$/', $nickname)) {
            $errors[] = '닉네임은 한글, 영문, 숫자, 언더스코어만 사용할 수 있습니다.';
            error_log('❌ 닉네임 형식 오류');
        } else {
            error_log('✅ 닉네임 검증 통과');
        }
        
        // 휴대폰 번호 검증 (010 전용)
        error_log('🔍 휴대폰 번호 검증 시작: ' . $phone);
        if (!$this->isValidPhone($phone)) {
            $errors[] = '010으로 시작하는 올바른 휴대폰 번호를 입력해주세요.';
            error_log('❌ 휴대폰 번호 형식 오류');
        } elseif (!$this->isValidKoreanMobile($phone)) {
            $errors[] = '010으로 시작하는 한국 휴대폰 번호만 사용할 수 있습니다.';
            error_log('❌ 010 시작 검증 실패');
        } else {
            error_log('✅ 휴대폰 번호 검증 통과');
        }
        
        // 휴대폰 인증 확인 (필수)
        error_log('🔍 휴대폰 인증 상태 확인');
        error_log('📱 phoneVerified: ' . $phoneVerified);
        error_log('📱 세션 phone_verified: ' . ($_SESSION['phone_verified'] ?? 'null'));
        error_log('📱 세션 phone_verified_at: ' . ($_SESSION['phone_verified_at'] ?? 'null'));
        
        if ($phoneVerified !== '1' || 
            !isset($_SESSION['phone_verified']) || 
            $_SESSION['phone_verified'] !== $phone ||
            (time() - ($_SESSION['phone_verified_at'] ?? 0)) > 1800) { // 30분 이내
            $errors[] = '휴대폰 인증을 완료해주세요.';
            error_log('❌ 휴대폰 인증 확인 실패');
            error_log('📱 인증 상태 세부사항: ' . json_encode([
                'phoneVerified' => $phoneVerified,
                'session_phone_verified' => $_SESSION['phone_verified'] ?? null,
                'phone_match' => ($_SESSION['phone_verified'] ?? null) === $phone,
                'time_diff' => time() - ($_SESSION['phone_verified_at'] ?? 0)
            ]));
        } else {
            error_log('✅ 휴대폰 인증 확인 통과');
        }
        
        // 이메일 검증 (필수)
        error_log('🔍 이메일 검증 시작: ' . $email);
        if (empty($email)) {
            $errors[] = '이메일을 입력해주세요. (필수)';
            error_log('❌ 이메일 비어있음');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = '올바른 이메일 형식을 입력해주세요.';
            error_log('❌ 이메일 형식 오류');
        } elseif (strlen($email) > 100) {
            $errors[] = '이메일 주소가 너무 깁니다. (최대 100자)';
            error_log('❌ 이메일 길이 오류: ' . strlen($email));
        } else {
            error_log('✅ 이메일 검증 통과');
        }
        
        // 비밀번호 검증
        error_log('🔍 비밀번호 검증 시작 (길이: ' . strlen($password) . ')');
        if (strlen($password) < 8) {
            $errors[] = '비밀번호는 최소 8자 이상이어야 합니다.';
            error_log('❌ 비밀번호 길이 부족');
        } elseif (strlen($password) > 100) {
            $errors[] = '비밀번호가 너무 깁니다. (최대 100자)';
            error_log('❌ 비밀번호 길이 초과');
        } elseif (!preg_match('/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/', $password)) {
            $errors[] = '비밀번호는 영문, 숫자, 특수문자를 포함해야 합니다.';
            error_log('❌ 비밀번호 복잡성 검증 실패');
        } else {
            error_log('✅ 비밀번호 검증 통과');
        }
        
        if ($password !== $passwordConfirm) {
            $errors[] = '비밀번호와 비밀번호 확인이 일치하지 않습니다.';
            error_log('❌ 비밀번호 확인 불일치');
        } else {
            error_log('✅ 비밀번호 확인 일치');
        }
        
        if (!$termsAccepted) {
            $errors[] = '이용약관에 동의해주세요.';
            error_log('❌ 이용약관 미동의');
        } else {
            error_log('✅ 이용약관 동의 확인');
        }
        
        // 중복 검사
        error_log('🔍 중복 검사 시작');
        try {
            if ($this->userModel->isNicknameExists($nickname)) {
                $errors[] = '이미 사용 중인 닉네임입니다.';
                error_log('❌ 닉네임 중복: ' . $nickname);
            } else {
                error_log('✅ 닉네임 사용 가능');
            }
            
            if ($this->userModel->isPhoneExists($phone)) {
                $errors[] = '이미 가입된 휴대폰 번호입니다.';
                error_log('❌ 휴대폰 번호 중복: ' . $phone);
            } else {
                error_log('✅ 휴대폰 번호 사용 가능');
            }
            
            // 이메일 중복 허용 정책 - 휴대폰 번호 기반 로그인 시스템으로 이메일 중복 허용
            error_log('✅ 이메일 중복 허용 정책 적용: ' . $email);
        } catch (Exception $e) {
            error_log('❌ 중복 검사 중 데이터베이스 오류: ' . $e->getMessage());
            $errors[] = '회원가입 처리 중 오류가 발생했습니다. 잠시 후 다시 시도해주세요.';
        }
        
        error_log('📋 검증 결과 - 오류 개수: ' . count($errors));
        if (!empty($errors)) {
            error_log('❌ 검증 실패 - 오류 목록: ' . json_encode($errors));
            $_SESSION['error'] = implode(' ', $errors);
            header('Location: /auth/signup');
            return;
        }
        
        error_log('✅ 모든 검증 통과 - 회원 정보 저장 시작');
        
        // 회원 정보 저장
        try {
            $userData = [
                'phone' => $phone,
                'nickname' => $nickname,
                'email' => $email,
                'password' => $password,
                'terms_agreed' => $termsAccepted,
                'marketing_agreed' => $marketingAccepted
            ];
            
            error_log('💾 사용자 데이터 준비 완료: ' . json_encode([
                'phone' => $phone,
                'nickname' => $nickname,
                'email' => $email,
                'terms_agreed' => $termsAccepted,
                'marketing_agreed' => $marketingAccepted
            ]));
            
            error_log('🔧 User 모델 create 메서드 호출 시작');
            $userId = $this->userModel->create($userData);
            error_log('💾 사용자 생성 결과 - 사용자 ID: ' . ($userId ?: 'false'));
            
            if ($userId) {
                error_log('✅ 회원가입 성공 - 사용자 ID: ' . $userId);
                
                // 회원가입 성공 - 자동 로그인 처리
                $newUser = $this->userModel->findById($userId);
                error_log('👤 생성된 사용자 정보 조회 완료');
                
                $this->createUserSession($newUser);
                error_log('🔐 사용자 세션 생성 완료');
                
                // 인증 세션 정보 정리
                unset($_SESSION['phone_verified'], $_SESSION['phone_verified_at']);
                error_log('🧹 인증 세션 정보 정리 완료');
                
                // 환영 SMS 발송 (선택적)
                try {
                    error_log('📤 환영 SMS 발송 시도: ' . $phone);
                    sendWelcomeSms($phone, $nickname);
                    error_log('✅ 환영 SMS 발송 성공');
                } catch (Exception $e) {
                    // SMS 발송 실패는 회원가입 성공에 영향을 주지 않음
                    error_log('❌ 환영 SMS 발송 실패: ' . $e->getMessage());
                }
                
                // 성공 메시지 설정
                $_SESSION['success'] = $nickname . '님, 가입을 환영합니다! 탑마케팅과 함께 성공적인 마케팅 여정을 시작하세요.';
                error_log('💬 성공 메시지 설정 완료');
                
                // 메인 페이지로 리다이렉트 (자동 로그인 완료)
                error_log('🚀 메인 페이지로 리다이렉트');
                header('Location: /');
                exit;
            } else {
                error_log('❌ 사용자 생성 실패 - userModel->create 반환값: false');
                $_SESSION['error'] = '회원가입 처리 중 오류가 발생했습니다. 다시 시도해주세요.';
                header('Location: /auth/signup');
                return;
            }
            
        } catch (Exception $e) {
            error_log('💥 회원가입 처리 중 예외 발생: ' . $e->getMessage());
            error_log('📍 스택 트레이스: ' . $e->getTraceAsString());
            $_SESSION['error'] = '회원가입 처리 중 오류가 발생했습니다: ' . $e->getMessage();
            $_SESSION['debug_info'] = '예외 발생: ' . $e->getMessage() . ' (파일: ' . $e->getFile() . ', 라인: ' . $e->getLine() . ')';
            error_log('🚨 디버깅: 5초 후 리다이렉트됩니다. 로그를 확인하세요.');
            sleep(5); // 디버깅을 위한 더 긴 지연
            header('Location: /auth/signup');
            return;
        }
    }
    
    /**
     * 로그아웃 처리
     */
    public function logout() {
        try {
            // 사용자 정보 로깅 (선택적)
            if (isset($_SESSION['user_id'])) {
                error_log('User ' . $_SESSION['user_id'] . ' logged out');
            }
            
            // JWT 토큰 쿠키 삭제
            if (isset($_COOKIE['access_token'])) {
                setcookie('access_token', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
            }
            if (isset($_COOKIE['refresh_token'])) {
                setcookie('refresh_token', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
            }
            
            // 기존 Remember Me 쿠키 삭제 (마이그레이션 호환성)
            if (isset($_COOKIE['remember_token'])) {
                setcookie('remember_token', '', time() - 3600, '/', '', false, true);
            }
            
            // 세션 변수 초기화
            $_SESSION = [];
            
            // 세션 쿠키 삭제
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }
            
            // 세션 삭제
            session_destroy();
            
            // 성공 메시지를 위한 새 세션 시작
            session_start();
            $_SESSION['success'] = '로그아웃이 완료되었습니다.';
            
            // 메인 페이지로 리다이렉트
            header('Location: /');
            exit;
            
        } catch (Exception $e) {
            // 에러 발생 시 디버깅 정보 표시
            echo "<h1>로그아웃 에러 발생</h1>";
            echo "<div style='background: #ffebee; padding: 20px; margin: 20px; border: 1px solid #f44336;'>";
            echo "<h2>에러 메시지:</h2>";
            echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<h2>에러 위치:</h2>";
            echo "<p>" . htmlspecialchars($e->getFile()) . " : " . $e->getLine() . "</p>";
            echo "<h2>스택 트레이스:</h2>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
            echo "</div>";
            echo "<a href='/' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none;'>메인으로 돌아가기</a>";
            exit;
        } catch (Error $e) {
            // Fatal Error 발생 시 디버깅 정보 표시
            echo "<h1>로그아웃 Fatal Error 발생</h1>";
            echo "<div style='background: #ffebee; padding: 20px; margin: 20px; border: 1px solid #f44336;'>";
            echo "<h2>에러 메시지:</h2>";
            echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<h2>에러 위치:</h2>";
            echo "<p>" . htmlspecialchars($e->getFile()) . " : " . $e->getLine() . "</p>";
            echo "<h2>스택 트레이스:</h2>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
            echo "</div>";
            echo "<a href='/' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none;'>메인으로 돌아가기</a>";
            exit;
        }
    }
    
    /**
     * JWT 토큰 갱신 (리프레시)
     */
    public function refreshToken() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'POST 요청만 허용됩니다.']);
            return;
        }
        
        try {
            // 리프레시 토큰 확인
            $refreshToken = $_COOKIE['refresh_token'] ?? null;
            if (!$refreshToken) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => '리프레시 토큰이 없습니다.']);
                return;
            }
            
            // 리프레시 토큰 검증
            $payload = JWTHelper::validateToken($refreshToken);
            if (!$payload || ($payload['type'] ?? '') !== 'refresh') {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => '유효하지 않은 리프레시 토큰입니다.']);
                return;
            }
            
            // 사용자 정보 조회
            $user = $this->userModel->findById($payload['user_id']);
            if (!$user) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => '사용자를 찾을 수 없습니다.']);
                return;
            }
            
            // 새로운 액세스 토큰 생성
            $newTokens = JWTHelper::createTokenPair($user);
            
            // 새 액세스 토큰을 쿠키에 저장
            setcookie(
                'access_token',
                $newTokens['access_token'],
                time() + 3600, // 1시간
                '/',
                '',
                isset($_SERVER['HTTPS']),
                true
            );
            
            // 응답
            echo json_encode([
                'success' => true,
                'message' => '토큰이 갱신되었습니다.',
                'access_token' => $newTokens['access_token'],
                'expires_in' => 3600
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => '토큰 갱신 중 오류가 발생했습니다.']);
            error_log('JWT 토큰 갱신 오류: ' . $e->getMessage());
        }
    }
    
    /**
     * 현재 사용자 정보 조회 (JWT 기반)
     */
    public function me() {
        header('Content-Type: application/json');
        
        try {
            // JWT에서 사용자 정보 추출
            $accessToken = $_COOKIE['access_token'] ?? null;
            if (!$accessToken) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => '인증 토큰이 없습니다.']);
                return;
            }
            
            $userData = JWTHelper::getUserFromToken($accessToken);
            if (!$userData) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => '유효하지 않은 토큰입니다.']);
                return;
            }
            
            // 사용자 정보 조회
            $user = $this->userModel->findById($userData['user_id']);
            if (!$user) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => '사용자를 찾을 수 없습니다.']);
                return;
            }
            
            echo json_encode([
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'nickname' => $user['nickname'],
                    'email' => $user['email'],
                    'phone' => $user['phone'],
                    'role' => $user['role'],
                    'profile_image' => $user['profile_image_thumb'] ?? null,
                    'created_at' => $user['created_at']
                ],
                'token_info' => [
                    'expires_in' => JWTHelper::getTokenTimeLeft($accessToken),
                    'has_refresh_token' => isset($_COOKIE['refresh_token'])
                ]
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => '사용자 정보 조회 중 오류가 발생했습니다.']);
            error_log('JWT 사용자 정보 조회 오류: ' . $e->getMessage());
        }
    }
    
    /**
     * 닉네임 실시간 중복 검사 API (보안 강화)
     */
    public function checkNickname() {
        header('Content-Type: application/json');
        
        // HTTPS 강제 (프로덕션 환경에서)
        if (!$this->isSecureConnection() && $_SERVER['HTTP_HOST'] !== 'localhost') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => '보안 연결만 허용됩니다.']);
            return;
        }
        
        // Rate Limiting 체크
        if (!$this->checkRateLimit('nickname_check', 30, 300)) { // 5분당 30회 제한
            http_response_code(429);
            echo json_encode(['success' => false, 'message' => '요청이 너무 많습니다. 잠시 후 다시 시도해주세요.']);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'POST 메서드만 허용됩니다.']);
            return;
        }
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => '잘못된 JSON 형식입니다.']);
                return;
            }
            
            $nickname = $this->sanitizeInput($input['nickname'] ?? '');
            
            // 강화된 입력 검증
            if (empty($nickname)) {
                echo json_encode([
                    'success' => true,
                    'available' => false,
                    'message' => '닉네임을 입력해주세요.',
                    'field' => 'nickname'
                ]);
                return;
            }
            
            // 추가 보안 검증
            if (!$this->validateDuplicationRequest($nickname, 'nickname')) {
                echo json_encode([
                    'success' => false,
                    'message' => '닉네임 형식이 올바르지 않습니다.',
                    'field' => 'nickname'
                ]);
                return;
            }
            
            // 길이 검증 (유니코드 문자 기준)
            $nicknameLength = mb_strlen($nickname, 'UTF-8');
            if ($nicknameLength < 2 || $nicknameLength > 20) {
                echo json_encode([
                    'success' => true,
                    'available' => false,
                    'message' => "닉네임은 2자 이상 20자 이하로 입력해주세요. (현재: {$nicknameLength}자)",
                    'field' => 'nickname'
                ]);
                return;
            }
            
            // 형식 검증
            if (!preg_match('/^[가-힣a-zA-Z0-9_]+$/', $nickname)) {
                echo json_encode([
                    'success' => true,
                    'available' => false,
                    'message' => '닉네임은 한글, 영문, 숫자, 언더스코어만 사용할 수 있습니다.',
                    'field' => 'nickname'
                ]);
                return;
            }
            
            // 중복 검사
            $exists = $this->userModel->isNicknameExists($nickname);
            
            if ($exists) {
                echo json_encode([
                    'success' => true,
                    'available' => false,
                    'message' => '이미 사용 중인 닉네임입니다.',
                    'field' => 'nickname'
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'available' => true,
                    'message' => '사용 가능한 닉네임입니다.',
                    'field' => 'nickname'
                ]);
            }
            
            error_log("닉네임 중복검사: $nickname -> " . ($exists ? '중복' : '사용가능'));
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => '중복 검사 중 오류가 발생했습니다.',
                'field' => 'nickname'
            ]);
            error_log('닉네임 중복검사 오류: ' . $e->getMessage());
        }
    }
    
    /**
     * 휴대폰 번호 실시간 중복 검사 API (보안 강화)
     */
    public function checkPhone() {
        header('Content-Type: application/json');
        
        // HTTPS 강제 (프로덕션 환경에서)
        if (!$this->isSecureConnection() && $_SERVER['HTTP_HOST'] !== 'localhost') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => '보안 연결만 허용됩니다.']);
            return;
        }
        
        // Rate Limiting 체크
        if (!$this->checkRateLimit('phone_check', 30, 300)) { // 5분당 30회 제한
            http_response_code(429);
            echo json_encode(['success' => false, 'message' => '요청이 너무 많습니다. 잠시 후 다시 시도해주세요.']);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'POST 메서드만 허용됩니다.']);
            return;
        }
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => '잘못된 JSON 형식입니다.']);
                return;
            }
            
            $phone = $this->sanitizePhone($input['phone'] ?? '');
            
            // 강화된 입력 검증
            if (empty($phone)) {
                echo json_encode([
                    'success' => true,
                    'available' => false,
                    'message' => '휴대폰 번호를 입력해주세요.',
                    'field' => 'phone'
                ]);
                return;
            }
            
            // 추가 보안 검증
            if (!$this->validateDuplicationRequest($phone, 'phone')) {
                echo json_encode([
                    'success' => false,
                    'message' => '휴대폰 번호 형식이 올바르지 않습니다.',
                    'field' => 'phone'
                ]);
                return;
            }
            
            // 휴대폰 번호 형식 검증
            if (!$this->isValidPhone($phone)) {
                echo json_encode([
                    'success' => true,
                    'available' => false,
                    'message' => '010으로 시작하는 올바른 휴대폰 번호를 입력해주세요.',
                    'field' => 'phone'
                ]);
                return;
            }
            
            // 중복 검사
            $exists = $this->userModel->isPhoneExists($phone);
            
            if ($exists) {
                echo json_encode([
                    'success' => true,
                    'available' => false,
                    'message' => '이미 가입된 휴대폰 번호입니다.',
                    'field' => 'phone'
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'available' => true,
                    'message' => '사용 가능한 휴대폰 번호입니다.',
                    'field' => 'phone'
                ]);
            }
            
            error_log("휴대폰 중복검사: $phone -> " . ($exists ? '중복' : '사용가능'));
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => '중복 검사 중 오류가 발생했습니다.',
                'field' => 'phone'
            ]);
            error_log('휴대폰 중복검사 오류: ' . $e->getMessage());
        }
    }
    
    /**
     * 보안 연결 확인 (HTTPS)
     */
    private function isSecureConnection() {
        return (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            $_SERVER['SERVER_PORT'] == 443 ||
            (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        );
    }
    
    /**
     * Rate Limiting 체크 (세션 기반)
     * @param string $key 제한 키
     * @param int $maxRequests 최대 요청 수
     * @param int $windowSeconds 제한 시간 (초)
     * @return bool 허용 여부
     */
    private function checkRateLimit($key, $maxRequests, $windowSeconds) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $rateLimitKey = "rate_limit_{$key}";
        $now = time();
        
        // 세션에서 기존 기록 조회
        $requests = $_SESSION[$rateLimitKey] ?? [];
        
        // 만료된 요청 기록 제거
        $requests = array_filter($requests, function($timestamp) use ($now, $windowSeconds) {
            return ($now - $timestamp) < $windowSeconds;
        });
        
        // 현재 요청 수가 제한을 초과하는지 확인
        if (count($requests) >= $maxRequests) {
            return false;
        }
        
        // 현재 요청 기록 추가
        $requests[] = $now;
        $_SESSION[$rateLimitKey] = $requests;
        
        return true;
    }
    
    /**
     * 추가 입력 검증 강화
     */
    private function validateDuplicationRequest($input, $type) {
        // 입력값 길이 제한
        $maxLength = ($type === 'nickname') ? 30 : 15;
        if (strlen($input) > $maxLength) {
            return false;
        }
        
        // 특수 문자 검증 강화
        if ($type === 'nickname') {
            // 닉네임: 한글, 영문, 숫자, 일부 특수문자만 허용
            if (!preg_match('/^[가-힣a-zA-Z0-9_.-]+$/', $input)) {
                return false;
            }
        } elseif ($type === 'phone') {
            // 휴대폰: 숫자와 하이픈만 허용
            if (!preg_match('/^[0-9-]+$/', $input)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * 휴대폰 번호 유효성 검사
     */
    private function isValidPhone($phone) {
        // 010으로 시작하는 한국 휴대폰 번호만 허용
        $pattern = '/^010-[0-9]{3,4}-[0-9]{4}$/';
        return preg_match($pattern, $phone);
    }
    
    /**
     * 한국 휴대폰 번호 010 검증
     */
    private function isValidKoreanMobile($phone) {
        // 010으로 시작하는지 확인
        return strpos($phone, '010-') === 0;
    }
    
    /**
     * SMS 발송 제한 확인 (Rate Limiting)
     */
    private function isSmsRateLimited($phone) {
        $sessionKey = 'sms_requests_' . md5($phone);
        $currentTime = time();
        
        if (!isset($_SESSION[$sessionKey])) {
            $_SESSION[$sessionKey] = [];
        }
        
        // 1분 이전의 요청들은 제거
        $_SESSION[$sessionKey] = array_filter($_SESSION[$sessionKey], function($timestamp) use ($currentTime) {
            return ($currentTime - $timestamp) < 60;
        });
        
        // 1분 내에 3회 이상 요청하면 제한
        return count($_SESSION[$sessionKey]) >= 3;
    }
    
    /**
     * SMS 요청 기록
     */
    private function recordSmsRequest($phone) {
        $sessionKey = 'sms_requests_' . md5($phone);
        
        if (!isset($_SESSION[$sessionKey])) {
            $_SESSION[$sessionKey] = [];
        }
        
        $_SESSION[$sessionKey][] = time();
    }
    
    /**
     * reCAPTCHA v3 토큰 검증
     */
    private function verifyRecaptcha($token, $action) {
        if (empty($token)) {
            return false;
        }
        
        // 실제 reCAPTCHA 비밀 키 적용
        $secretKey = '6LfViDErAAAAAJYZ6zqP3I6q124NuaUlAGcUWeB5';
        
        $data = [
            'secret' => $secretKey,
            'response' => $token,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ];
        
        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];
        
        $context = stream_context_create($options);
        $result = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
        
        if ($result === false) {
            error_log('reCAPTCHA API 호출 실패 - URL: https://www.google.com/recaptcha/api/siteverify');
            return false;
        }
        
        error_log('reCAPTCHA API 응답: ' . $result);
        
        $resultArray = json_decode($result, true);
        
        if (!$resultArray) {
            error_log('reCAPTCHA 응답 파싱 실패 - Raw response: ' . $result);
            return false;
        }
        
        // 성공 여부 및 점수 확인
        $isSuccess = $resultArray['success'] ?? false;
        $score = $resultArray['score'] ?? 0;
        $receivedAction = $resultArray['action'] ?? '';
        
        error_log('reCAPTCHA 검증 결과 - Success: ' . ($isSuccess ? 'true' : 'false') . ', Action: ' . $receivedAction . ', Expected: ' . $action . ', Score: ' . $score);
        
        // 액션이 일치하고, 성공했으며, 점수가 0.5 이상인 경우 통과
        if ($isSuccess && $receivedAction === $action && $score >= 0.5) {
            return true;
        }
        
        // 실패 로그 기록
        $errorCodes = $resultArray['error-codes'] ?? [];
        error_log('reCAPTCHA 검증 실패 - Action: ' . $action . ', Score: ' . $score . ', Errors: ' . implode(', ', $errorCodes) . ', Full response: ' . json_encode($resultArray));
        
        return false;
    }
    
    /**
     * 휴대폰 번호 정제 (숫자만 추출 후 하이픈 추가)
     */
    private function sanitizePhone($phone) {
        // 숫자만 추출
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // 11자리인 경우 하이픈 추가
        if (strlen($phone) === 11 && substr($phone, 0, 2) === '01') {
            $phone = substr($phone, 0, 3) . '-' . substr($phone, 3, 4) . '-' . substr($phone, 7, 4);
        }
        
        return $phone;
    }
    
    /**
     * 입력값 정제
     */
    private function sanitizeInput($input) {
        return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
    }
    
    /**
     * AJAX 요청 확인
     */
    private function isAjaxRequest() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * CSRF 토큰 검증
     */
    private function verifyCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && 
               hash_equals($_SESSION['csrf_token'], $token);
    }
    
    private function createUserSession($user, $remember = false) {
        // 세션에 사용자 정보 저장
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['nickname'];
        $_SESSION['phone'] = $user['phone'];
        $_SESSION['user_role'] = $user['role'] ?? 'GENERAL';
        $_SESSION['profile_image'] = $user['profile_image'] ?? null;
        $_SESSION['last_activity'] = time(); // 활동 시간 초기화
        
        // 로그인 상태 유지 설정
        if ($remember) {
            // 30일 동안 세션 유지
            $lifetime = 30 * 24 * 60 * 60; // 30일
            ini_set('session.gc_maxlifetime', $lifetime);
            
            // 세션 쿠키 수명 설정
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                session_id(),
                time() + $lifetime,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
            
            // JWT 기반에서는 Remember Token 시스템 대신 Refresh Token 사용
            // 별도 Remember Token 저장 불필요
        } else {
            // 로그인 상태 유지를 선택하지 않은 경우 기본 세션 수명 (4시간으로 연장)
            ini_set('session.gc_maxlifetime', 14400); // 4시간
        }
        
        // 세션 ID 재생성 (세션 고정 공격 방지)
        session_regenerate_id(true);
    }
    
    /**
     * JWT 기반 사용자 세션 생성
     * 
     * @param array $user 사용자 정보
     * @param bool $remember 로그인 상태 유지 여부
     * @return array JWT 토큰 정보
     */
    private function createJWTSession($user, $remember = false) {
        // JWT 토큰 생성
        $tokens = JWTHelper::createTokenPair($user);
        
        // 액세스 토큰을 HTTP-Only 쿠키에 저장
        $accessTokenExpiry = time() + 3600; // 1시간
        setcookie(
            'access_token',
            $tokens['access_token'],
            $accessTokenExpiry,
            '/',
            '',
            isset($_SERVER['HTTPS']), // HTTPS에서만 secure
            true // httponly
        );
        
        // 로그인 상태 유지 선택시 리프레시 토큰도 쿠키에 저장
        if ($remember) {
            $refreshTokenExpiry = time() + (30 * 24 * 60 * 60); // 30일
            setcookie(
                'refresh_token',
                $tokens['refresh_token'],
                $refreshTokenExpiry,
                '/',
                '',
                isset($_SERVER['HTTPS']), // HTTPS에서만 secure  
                true // httponly
            );
        }
        
        // 호환성을 위해 세션에도 기본 정보 저장
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['nickname'];
        $_SESSION['phone'] = $user['phone'];
        $_SESSION['user_role'] = $user['role'] ?? 'GENERAL';
        $_SESSION['profile_image'] = $user['profile_image'] ?? null;
        $_SESSION['auth_method'] = 'jwt'; // JWT 인증 표시
        $_SESSION['last_activity'] = time();
        
        // 세션 ID 재생성
        session_regenerate_id(true);
        
        return $tokens;
    }
    
    /**
     * JSON 요청인지 확인
     */
    private function isJsonRequest() {
        // Content-Type이 JSON인 경우
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $isJsonContentType = strpos($contentType, 'application/json') === 0;
        
        return $isJsonContentType;
    }
    
    /**
     * 리다이렉트 URL 유효성 검증
     */
    private function isValidRedirectUrl($url) {
        // 보안상 내부 URL만 허용
        if (empty($url)) {
            return false;
        }
        
        // 상대 경로만 허용 (절대 URL 차단)
        if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
            return false;
        }
        
        // 허용된 경로 패턴
        $allowedPatterns = [
            '/^\/community/',
            '/^\/user/',
            '/^\/post/',
            '/^\/lectures/',
            '/^\/events/',
            '/^\/home/',
            '/^\/legal/',
            '/^\/$/'
        ];
        
        foreach ($allowedPatterns as $pattern) {
            if (preg_match($pattern, $url)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 비밀번호 찾기 페이지 표시
     */
    public function showForgotPassword() {
        include SRC_PATH . '/views/auth/forgot-password.php';
    }
    
    /**
     * 비밀번호 찾기 처리 (SMS 인증 코드 발송)
     */
    public function forgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            return;
        }
        
        // 요청 타입 확인
        $isJsonRequest = $this->isJsonRequest();
        $isAjaxRequest = $this->isAjaxRequest();
        
        try {
            $input = $isJsonRequest ? json_decode(file_get_contents('php://input'), true) : $_POST;
            
            // CSRF 토큰 검증
            if (!isset($input['csrf_token']) || !$this->verifyCsrfToken($input['csrf_token'])) {
                if ($isAjaxRequest) {
                    header('Content-Type: application/json');
                    http_response_code(403);
                    echo json_encode(['success' => false, 'error' => 'CSRF 토큰이 유효하지 않습니다.']);
                    return;
                } else {
                    $_SESSION['error'] = 'CSRF 토큰이 유효하지 않습니다.';
                    header('Location: /auth/forgot-password');
                }
                return;
            }
            
            $phone = $input['phone'] ?? '';
            
            if (empty($phone)) {
                if ($isAjaxRequest) {
                    header('Content-Type: application/json');
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => '휴대폰 번호를 입력해주세요.']);
                    return;
                } else {
                    $_SESSION['error'] = '휴대폰 번호를 입력해주세요.';
                    header('Location: /auth/forgot-password');
                }
                return;
            }
            
            // 휴대폰 번호 정제
            $phone = $this->sanitizePhone($phone);
            
            // 휴대폰 번호 유효성 검사
            if (!$this->isValidPhone($phone)) {
                if ($isAjaxRequest) {
                    header('Content-Type: application/json');
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => '올바른 휴대폰 번호 형식이 아닙니다.']);
                    return;
                } else {
                    $_SESSION['error'] = '올바른 휴대폰 번호 형식이 아닙니다.';
                    header('Location: /auth/forgot-password');
                }
                return;
            }
            
            // 해당 휴대폰 번호로 가입된 사용자 확인
            $user = $this->userModel->findByPhone($phone);
            if (!$user) {
                if ($isAjaxRequest) {
                    header('Content-Type: application/json');
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => '해당 휴대폰 번호로 가입된 계정이 없습니다.']);
                    return;
                } else {
                    $_SESSION['error'] = '해당 휴대폰 번호로 가입된 계정이 없습니다.';
                    header('Location: /auth/forgot-password');
                }
                return;
            }
            
            // SMS 발송 제한 확인
            if ($this->isSmsRateLimited($phone)) {
                if ($isAjaxRequest) {
                    header('Content-Type: application/json');
                    http_response_code(429);
                    echo json_encode(['success' => false, 'error' => '너무 많은 요청입니다. 1분 후 다시 시도해주세요.']);
                    return;
                } else {
                    $_SESSION['error'] = '너무 많은 요청입니다. 1분 후 다시 시도해주세요.';
                    header('Location: /auth/forgot-password');
                }
                return;
            }
            
            // 6자리 인증 코드 생성
            $verificationCode = sprintf('%06d', mt_rand(100000, 999999));
            
            // 세션에 인증 코드와 사용자 정보 저장
            $_SESSION['password_reset_code'] = $verificationCode;
            $_SESSION['password_reset_phone'] = $phone;
            $_SESSION['password_reset_user_id'] = $user['id'];
            $_SESSION['password_reset_expires'] = time() + 300; // 5분 후 만료
            
            // SMS 발송
            $smsMessage = "[탑마케팅] 비밀번호 재설정 인증 코드: {$verificationCode} (5분간 유효)";
            $smsResult = sendAuthCodeSms($phone, $verificationCode);
            
            if ($smsResult['success']) {
                // SMS 요청 기록
                $this->recordSmsRequest($phone);
                
                if ($isAjaxRequest) {
                    header('Content-Type: application/json');
                    http_response_code(200);
                    echo json_encode([
                        'success' => true,
                        'message' => '인증 코드가 발송되었습니다. 5분 내에 입력해주세요.',
                        'phone' => $phone
                    ]);
                    return;
                } else {
                    $_SESSION['success'] = '인증 코드가 발송되었습니다. 5분 내에 입력해주세요.';
                    header('Location: /auth/forgot-password');
                }
            } else {
                if ($isAjaxRequest) {
                    header('Content-Type: application/json');
                    http_response_code(500);
                    echo json_encode(['success' => false, 'error' => 'SMS 발송에 실패했습니다. 다시 시도해주세요.']);
                    return;
                } else {
                    $_SESSION['error'] = 'SMS 발송에 실패했습니다. 다시 시도해주세요.';
                    header('Location: /auth/forgot-password');
                }
            }
            
        } catch (Exception $e) {
            error_log('비밀번호 찾기 오류: ' . $e->getMessage());
            if ($isAjaxRequest) {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => '서버 오류가 발생했습니다.']);
                return;
            } else {
                $_SESSION['error'] = '서버 오류가 발생했습니다.';
                header('Location: /auth/forgot-password');
            }
        }
    }
    
    /**
     * 인증 코드 검증 (다단계 플로우용)
     */
    public function verifyResetCode() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            return;
        }
        
        $isAjaxRequest = $this->isAjaxRequest();
        
        try {
            // JSON 데이터 파싱
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Invalid JSON data');
            }
            
            // CSRF 토큰 검증
            if (!isset($input['csrf_token']) || !$this->verifyCsrfToken($input['csrf_token'])) {
                if ($isAjaxRequest) {
                    header('Content-Type: application/json');
                    http_response_code(403);
                    echo json_encode(['success' => false, 'error' => 'CSRF 토큰이 유효하지 않습니다.']);
                    return;
                } else {
                    $_SESSION['error'] = 'CSRF 토큰이 유효하지 않습니다.';
                    header('Location: /auth/forgot-password');
                }
                return;
            }
            
            $phone = $input['phone'] ?? '';
            $code = $input['verification_code'] ?? '';
            
            // 입력값 검증
            if (empty($phone) || empty($code)) {
                if ($isAjaxRequest) {
                    header('Content-Type: application/json');
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => '필수 정보가 누락되었습니다.']);
                    return;
                }
            }
            
            // 세션에서 저장된 인증 코드 확인
            if (!isset($_SESSION['password_reset_code']) || 
                !isset($_SESSION['password_reset_phone']) ||
                !isset($_SESSION['password_reset_expires'])) {
                
                if ($isAjaxRequest) {
                    header('Content-Type: application/json');
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => '인증 세션이 만료되었습니다. 다시 시작해주세요.']);
                    return;
                }
            }
            
            // 만료 시간 확인
            if (time() > $_SESSION['password_reset_expires']) {
                // 만료된 세션 정리
                unset($_SESSION['password_reset_code']);
                unset($_SESSION['password_reset_phone']);
                unset($_SESSION['password_reset_expires']);
                
                if ($isAjaxRequest) {
                    header('Content-Type: application/json');
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => '인증 코드가 만료되었습니다. 다시 발송받아주세요.']);
                    return;
                }
            }
            
            // 휴대폰 번호 일치 확인
            if ($_SESSION['password_reset_phone'] !== $phone) {
                if ($isAjaxRequest) {
                    header('Content-Type: application/json');
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => '휴대폰 번호가 일치하지 않습니다.']);
                    return;
                }
            }
            
            // 인증 코드 일치 확인
            if ($_SESSION['password_reset_code'] !== $code) {
                if ($isAjaxRequest) {
                    header('Content-Type: application/json');
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => '인증 코드가 올바르지 않습니다.']);
                    return;
                }
            }
            
            // 인증 성공
            if ($isAjaxRequest) {
                header('Content-Type: application/json');
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => '인증이 완료되었습니다.'
                ]);
                return;
            }
            
        } catch (Exception $e) {
            error_log('인증 코드 검증 오류: ' . $e->getMessage());
            if ($isAjaxRequest) {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => '서버 오류가 발생했습니다.']);
                return;
            } else {
                $_SESSION['error'] = '서버 오류가 발생했습니다.';
                header('Location: /auth/forgot-password');
            }
        }
    }
    
    /**
     * 비밀번호 재설정 페이지 표시
     */
    public function showResetPassword() {
        // 인증 코드 세션 확인
        if (!isset($_SESSION['password_reset_code']) || !isset($_SESSION['password_reset_phone'])) {
            $_SESSION['error'] = '잘못된 접근입니다. 비밀번호 찾기를 다시 시도해주세요.';
            header('Location: /auth/forgot-password');
            return;
        }
        
        // 인증 코드 만료 확인
        if (time() > $_SESSION['password_reset_expires']) {
            unset($_SESSION['password_reset_code'], $_SESSION['password_reset_phone'], $_SESSION['password_reset_user_id'], $_SESSION['password_reset_expires']);
            $_SESSION['error'] = '인증 코드가 만료되었습니다. 다시 시도해주세요.';
            header('Location: /auth/forgot-password');
            return;
        }
        
        include SRC_PATH . '/views/auth/reset-password.php';
    }
    
    /**
     * 비밀번호 재설정 처리
     */
    public function resetPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            return;
        }
        
        // 요청 타입 확인
        $isJsonRequest = $this->isJsonRequest();
        $isAjaxRequest = $this->isAjaxRequest();
        
        try {
            $input = $isJsonRequest ? json_decode(file_get_contents('php://input'), true) : $_POST;
            
            // CSRF 토큰 검증
            if (!isset($input['csrf_token']) || !$this->verifyCsrfToken($input['csrf_token'])) {
                if ($isAjaxRequest) {
                    header('Content-Type: application/json');
                    http_response_code(403);
                    echo json_encode(['success' => false, 'error' => 'CSRF 토큰이 유효하지 않습니다.']);
                } else {
                    $_SESSION['error'] = 'CSRF 토큰이 유효하지 않습니다.';
                    header('Location: /auth/forgot-password');
                }
                return;
            }
            
            // 다단계 플로우 파라미터
            $phone = $input['phone'] ?? '';
            $verificationCode = $input['verification_code'] ?? '';
            $newPassword = $input['new_password'] ?? '';
            $confirmPassword = $input['confirm_password'] ?? '';
            
            // 입력값 검증
            if (empty($phone) || empty($verificationCode) || empty($newPassword) || empty($confirmPassword)) {
                if ($isAjaxRequest) {
                    header('Content-Type: application/json');
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => '모든 필드를 입력해주세요.']);
                } else {
                    $_SESSION['error'] = '모든 필드를 입력해주세요.';
                    header('Location: /auth/reset-password');
                }
                return;
            }
            
            // 세션 확인
            if (!isset($_SESSION['password_reset_code']) || !isset($_SESSION['password_reset_user_id'])) {
                if ($isAjaxRequest) {
                    header('Content-Type: application/json');
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => '잘못된 접근입니다.']);
                } else {
                    $_SESSION['error'] = '잘못된 접근입니다.';
                    header('Location: /auth/forgot-password');
                }
                return;
            }
            
            // 인증 코드 만료 확인
            if (time() > $_SESSION['password_reset_expires']) {
                unset($_SESSION['password_reset_code'], $_SESSION['password_reset_phone'], $_SESSION['password_reset_user_id'], $_SESSION['password_reset_expires']);
                if ($isAjaxRequest) {
                    header('Content-Type: application/json');
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => '인증 코드가 만료되었습니다.']);
                } else {
                    $_SESSION['error'] = '인증 코드가 만료되었습니다.';
                    header('Location: /auth/forgot-password');
                }
                return;
            }
            
            // 인증 코드 확인
            if ($verificationCode !== $_SESSION['password_reset_code']) {
                if ($isAjaxRequest) {
                    header('Content-Type: application/json');
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => '인증 코드가 올바르지 않습니다.']);
                } else {
                    $_SESSION['error'] = '인증 코드가 올바르지 않습니다.';
                    header('Location: /auth/reset-password');
                }
                return;
            }
            
            // 비밀번호 확인
            if ($newPassword !== $confirmPassword) {
                if ($isAjaxRequest) {
                    header('Content-Type: application/json');
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => '비밀번호가 일치하지 않습니다.']);
                } else {
                    $_SESSION['error'] = '비밀번호가 일치하지 않습니다.';
                    header('Location: /auth/reset-password');
                }
                return;
            }
            
            // 비밀번호 강도 검증
            if (strlen($newPassword) < 8) {
                if ($isAjaxRequest) {
                    header('Content-Type: application/json');
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => '비밀번호는 8자 이상이어야 합니다.']);
                } else {
                    $_SESSION['error'] = '비밀번호는 8자 이상이어야 합니다.';
                    header('Location: /auth/reset-password');
                }
                return;
            }
            
            // 비밀번호 업데이트
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $userId = $_SESSION['password_reset_user_id'];
            
            $result = $this->db->execute(
                "UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?",
                [$hashedPassword, $userId]
            );
            
            if ($result) {
                // 세션 정리
                unset($_SESSION['password_reset_code'], $_SESSION['password_reset_phone'], $_SESSION['password_reset_user_id'], $_SESSION['password_reset_expires']);
                
                if ($isAjaxRequest) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'message' => '비밀번호가 성공적으로 변경되었습니다. 새 비밀번호로 로그인해주세요.'
                    ]);
                } else {
                    $_SESSION['success'] = '비밀번호가 성공적으로 변경되었습니다. 새 비밀번호로 로그인해주세요.';
                    header('Location: /auth/login');
                }
            } else {
                if ($isAjaxRequest) {
                    header('Content-Type: application/json');
                    http_response_code(500);
                    echo json_encode(['success' => false, 'error' => '비밀번호 변경에 실패했습니다.']);
                } else {
                    $_SESSION['error'] = '비밀번호 변경에 실패했습니다.';
                    header('Location: /auth/reset-password');
                }
            }
            
        } catch (Exception $e) {
            error_log('비밀번호 재설정 오류: ' . $e->getMessage());
            if ($isAjaxRequest) {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => '서버 오류가 발생했습니다.']);
            } else {
                $_SESSION['error'] = '서버 오류가 발생했습니다.';
                header('Location: /auth/reset-password');
            }
        }
    }
} 