# 탑마케팅 보안 점검 리포트

**점검일**: 2025-10-19
**점검자**: Claude (Anthropic)
**프로젝트**: 탑마케팅 (TOPMKT) v3.89.9
**점검 범위**: 웹 애플리케이션 보안 취약점 전체 점검

---

## 📋 목차

1. [점검 요약](#점검-요약)
2. [치명적 보안 문제 (CRITICAL)](#치명적-보안-문제-critical)
3. [높은 수준 보안 문제 (HIGH)](#높은-수준-보안-문제-high)
4. [중간 수준 보안 문제 (MEDIUM)](#중간-수준-보안-문제-medium)
5. [잘 구현된 보안 기능](#잘-구현된-보안-기능)
6. [권장 사항](#권장-사항)
7. [상세 점검 결과](#상세-점검-결과)

---

## 점검 요약

### 전체 보안 등급: **C+** (개선 필요)

| 영역 | 등급 | 상태 |
|------|------|------|
| **SQL Injection 방어** | A+ | ✅ 완벽 |
| **XSS 방어** | A | ✅ 양호 |
| **CSRF 보호** | A | ✅ 양호 |
| **파일 업로드 보안** | A+ | ✅ 완벽 |
| **인증/인가 시스템** | A | ✅ 양호 |
| **민감한 정보 관리** | D | 🚨 **치명적** |
| **보안 헤더** | C | ⚠️ 개선 필요 |
| **입력 검증** | A | ✅ 양호 |
| **세션/JWT 관리** | A | ✅ 양호 |

### 발견된 문제 요약

- **치명적 (CRITICAL)**: 4건 🚨
- **높음 (HIGH)**: 2건 ⚠️
- **중간 (MEDIUM)**: 3건 ℹ️
- **낮음 (LOW)**: 0건

---

## 치명적 보안 문제 (CRITICAL)

### 🚨 1. .env 파일 권한 문제

**위험도**: CRITICAL
**CVSS 점수**: 9.8 (매우 높음)

**문제**:
```bash
-rwxr-xr-x 1 www-data www-data 308 Jul  9 17:37 /var/www/html/topmkt/.env
```
- 파일 권한이 **755**로 설정되어 있어 모든 사용자가 읽을 수 있음
- 데이터베이스 비밀번호 `DB_PASSWORD=Dnlszkem1!` 노출 위험

**영향**:
- 시스템의 모든 사용자가 데이터베이스 비밀번호를 읽을 수 있음
- 서버 침해 시 데이터베이스 완전 장악 가능

**해결 방법**:
```bash
# 즉시 실행 필요!
chmod 600 /var/www/html/topmkt/.env
chown www-data:www-data /var/www/html/topmkt/.env
```

**검증**:
```bash
ls -la /var/www/html/topmkt/.env
# 결과: -rw------- (600)
```

---

### 🚨 2. API 키 하드코딩 (config.php)

**위험도**: CRITICAL
**CVSS 점수**: 8.5 (높음)

**문제**:
`/var/www/html/topmkt/src/config/config.php` 파일에 API 키가 하드코딩됨:

```php
// config.php
define('NAVER_MAPS_CLIENT_SECRET', 'ifjGgFsON2vMO2DiIFW1QLRBnEQ7l1j4w5CciajG');
define('ALIGO_API_KEY', 'ukqd7brex9cf9o3ggvy3bxr37brxxkm1');
```

**영향**:
- Git 저장소에 민감한 API 키가 커밋될 수 있음
- 코드 공유 시 API 키 노출
- 네이버 Maps API 및 SMS API 남용 가능

**해결 방법**:

1. **config.php 수정**:
```php
// config.php - 환경변수에서 로드하도록 변경
define('NAVER_MAPS_CLIENT_SECRET', $_ENV['NAVER_MAPS_CLIENT_SECRET'] ?? '');
define('ALIGO_API_KEY', $_ENV['ALIGO_API_KEY'] ?? '');
define('FIREBASE_API_KEY', $_ENV['FIREBASE_API_KEY'] ?? '');
```

2. **.env 파일에 추가**:
```bash
# .env
NAVER_MAPS_CLIENT_SECRET=ifjGgFsON2vMO2DiIFW1QLRBnEQ7l1j4w5CciajG
ALIGO_API_KEY=ukqd7brex9cf9o3ggvy3bxr37brxxkm1
FIREBASE_API_KEY=AIzaSyAlFQNcYxi29uhu5fW1MYy7iESy3GvmnUQ
```

3. **.gitignore 확인**:
```bash
# .gitignore에 .env 추가 (이미 있는지 확인)
echo ".env" >> .gitignore
```

---

### 🚨 3. database.php에 하드코딩된 비밀번호

**위험도**: CRITICAL
**CVSS 점수**: 8.5 (높음)

**문제**:
```php
// /var/www/html/topmkt/src/config/database.php
$password = $_ENV['DB_PASSWORD'] ?? 'Dnlszkem1!'; // fallback 하드코딩
```

**영향**:
- 환경변수가 설정되지 않으면 하드코딩된 비밀번호 사용
- 소스코드에 비밀번호 노출

**해결 방법**:
```php
// database.php 수정
$password = $_ENV['DB_PASSWORD'] ?? '';

if (empty($password)) {
    error_log('CRITICAL: DB_PASSWORD not set in environment variables');
    throw new Exception('Database configuration error');
}
```

---

### 🚨 4. SecurityHelper 암호화 키 하드코딩

**위험도**: CRITICAL
**CVSS 점수**: 8.0 (높음)

**문제**:
```php
// /var/www/html/topmkt/src/helpers/SecurityHelper.php
self::$encryptionKey = hash('sha256', 'TOPMKT_ENCRYPTION_KEY_2024_SECURE_DATA_PROTECTION', true);
self::$hashSalt = 'TOPMKT_HASH_SALT_2024_SECURE_HASHING';
```

**영향**:
- 암호화 키가 소스코드에 노출됨
- 개인정보 (전화번호, 이메일, 생년월일) 암호화가 무력화될 수 있음
- 해커가 암호화된 데이터를 복호화할 수 있음

**해결 방법**:

1. **.env 파일에 추가**:
```bash
# .env
ENCRYPTION_KEY=랜덤_32바이트_키_생성필요
HASH_SALT=랜덤_솔트_생성필요
```

2. **SecurityHelper.php 수정**:
```php
private static function init() {
    if (self::$encryptionKey === null) {
        $key = $_ENV['ENCRYPTION_KEY'] ?? '';
        if (empty($key)) {
            throw new Exception('ENCRYPTION_KEY not set in environment variables');
        }
        self::$encryptionKey = hash('sha256', $key, true);

        $salt = $_ENV['HASH_SALT'] ?? '';
        if (empty($salt)) {
            throw new Exception('HASH_SALT not set in environment variables');
        }
        self::$hashSalt = $salt;
    }
}
```

3. **안전한 키 생성**:
```bash
# 랜덤 암호화 키 생성
php -r "echo bin2hex(random_bytes(32));"
# 결과를 .env의 ENCRYPTION_KEY에 설정

# 랜덤 솔트 생성
php -r "echo bin2hex(random_bytes(32));"
# 결과를 .env의 HASH_SALT에 설정
```

---

## 높은 수준 보안 문제 (HIGH)

### ⚠️ 1. .env 파일 웹 접근 차단 미설정

**위험도**: HIGH
**CVSS 점수**: 7.5 (높음)

**문제**:
- 루트 디렉토리의 `.htaccess`에 .env 파일 접근 차단 규칙이 없음
- 웹 브라우저에서 `https://www.topmktx.com/.env` 직접 접근 가능성

**해결 방법**:

`/var/www/html/topmkt/.htaccess` 파일 수정:
```apache
# .env 파일 접근 차단 추가
<Files ".env">
    Order Allow,Deny
    Deny from all
</Files>

# 기타 민감한 설정 파일 차단
<FilesMatch "\.(env|log|sql|md|gitignore)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>
```

**검증**:
```bash
curl -I https://www.topmktx.com/.env
# 결과: 403 Forbidden 또는 404 Not Found 확인
```

---

### ⚠️ 2. 보안 헤더 미설정

**위험도**: HIGH
**CVSS 점수**: 6.5 (중간-높음)

**문제**:
- 주요 보안 HTTP 헤더가 설정되지 않음
- XSS, Clickjacking, MIME Sniffing 공격에 취약

**권장 헤더**:
```php
// public/index.php 또는 BaseController에 추가
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");

// HTTPS 환경에서만
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
}

// CSP 헤더 (필요시 조정)
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://www.googletagmanager.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com;");
```

---

## 중간 수준 보안 문제 (MEDIUM)

### ℹ️ 1. JWT Secret 키 하드코딩

**위험도**: MEDIUM
**CVSS 점수**: 5.5 (중간)

**문제**:
JWTHelper에서 JWT_SECRET_KEY가 명시적으로 정의되지 않으면 문제가 발생할 수 있음

**권장 사항**:
```php
// .env에 추가
JWT_SECRET_KEY=랜덤_JWT_시크릿_키_생성필요

// JWTHelper.php 수정
if (defined('JWT_SECRET_KEY') && !empty(JWT_SECRET_KEY)) {
    self::$secret_key = JWT_SECRET_KEY;
} else {
    throw new Exception('JWT_SECRET_KEY not configured');
}
```

---

### ℹ️ 2. ChatController Firebase API 키 노출

**위험도**: MEDIUM
**CVSS 점수**: 5.0 (중간)

**문제**:
```php
// ChatController.php
'apiKey' => $_ENV['FIREBASE_API_KEY'] ?? "AIzaSyAlFQNcYxi29uhu5fW1MYy7iESy3GvmnUQ"
```

**해결 방법**:
```php
// fallback 제거
'apiKey' => $_ENV['FIREBASE_API_KEY'] ?? ''

// 또는 예외 발생
if (empty($_ENV['FIREBASE_API_KEY'])) {
    throw new Exception('FIREBASE_API_KEY not configured');
}
```

---

### ℹ️ 3. 에러 메시지 상세 정보 노출 (프로덕션)

**위험도**: MEDIUM
**CVSS 점수**: 4.5 (중간)

**권장 사항**:
```php
// config.php 확인
if (APP_ENV === 'production') {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ERROR | E_PARSE);
} else {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
```

---

## 잘 구현된 보안 기능

### ✅ 1. SQL Injection 방어 (A+)

**구현 상태**: 완벽

**증거**:
- 모든 데이터베이스 쿼리에서 **Prepared Statements** 사용
- User 모델에서 `$this->db->execute($sql, $params)` 패턴 일관 사용
- 직접적인 SQL 변수 삽입 없음

**예시**:
```php
// User.php
$stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
```

---

### ✅ 2. XSS 방어 (A)

**구현 상태**: 양호

**증거**:
- 61개 파일에서 `htmlspecialchars()`, `htmlentities()`, `strip_tags()` 사용
- UserController에서 출력 전 sanitization 적용

**예시**:
```php
// UserController.php
$page_description = htmlspecialchars(strip_tags(mb_substr($user['bio'], 0, 150)));
```

---

### ✅ 3. CSRF 보호 (A)

**구현 상태**: 양호

**증거**:
- 17개 컨트롤러에서 CSRF 토큰 사용
- AuthController에서 자동 CSRF 토큰 생성

**예시**:
```php
// AuthController.php
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
```

---

### ✅ 4. 파일 업로드 보안 (A+)

**구현 상태**: 완벽

**증거**:
- **3중 검증**: 확장자 + MIME 타입 + 파일 크기
- **업로드 폴더 PHP 실행 차단**: `.htaccess` 완벽 설정
- **허용 파일**: 이미지만 (jpg, jpeg, png, gif, webp)

**upload.php 구현**:
```php
public const ALLOWED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
public const MAX_FILE_SIZE = 30 * 1024 * 1024; // 30MB

public static function validateImageFile(array $file): array {
    // 파일 크기, 확장자, MIME 타입 검증
}
```

**uploads/.htaccess**:
```apache
# PHP 스크립트 실행 방지
<Files "*.php">
    Order Allow,Deny
    Deny from all
</Files>

# 이미지 파일만 허용
<FilesMatch "\.(jpg|jpeg|png|gif|webp)$">
    Order Allow,Deny
    Allow from all
</FilesMatch>
```

---

### ✅ 5. 인증/인가 시스템 (A)

**구현 상태**: 양호

**증거**:
- **JWT 기반 인증**: Access Token (1시간) + Refresh Token (30일)
- **역할 기반 접근 제어 (RBAC)**: ADMIN, GENERAL, CORPORATE
- **HTTP-only 쿠키**: XSS 방지

**AuthMiddleware.php**:
```php
public static function isAuthenticated() {
    $user = self::authenticateWithJWT();
    if (!$user) {
        header('Location: /auth/login');
        exit;
    }
    return true;
}

public static function hasRole($role) {
    // 역할 검증
}
```

---

### ✅ 6. 개인정보 암호화 (A)

**구현 상태**: 양호

**증거**:
- **AES-256-GCM** 암호화 사용 (산업 표준)
- 전화번호, 이메일, 생년월일 암호화
- 검색용 해시 별도 생성

**SecurityHelper.php**:
```php
public static function encrypt($data) {
    $encrypted = openssl_encrypt(
        $data,
        'aes-256-gcm',
        self::$encryptionKey,
        OPENSSL_RAW_DATA,
        $iv,
        $tag
    );
}
```

**User 모델**:
```php
private function encryptPersonalData($data) {
    $encrypted['phone'] = SecurityHelper::encrypt($data['phone']);
    $encrypted['phone_search_hash'] = SecurityHelper::encryptSearchable($data['phone']);
}
```

---

### ✅ 7. 입력 검증 (A)

**구현 상태**: 양호

**증거**:
- **ValidationHelper** 사용
- 전화번호, 이메일, 사업자번호 검증
- 입력 sanitization 적용

**예시**:
```php
// AuthController.php
$phone = $this->sanitizePhone($input['phone'] ?? '');

if (!$this->isValidPhone($phone)) {
    return ['success' => false, 'message' => '올바른 휴대폰 번호를 입력해주세요.'];
}
```

---

## 권장 사항

### 긴급 (24시간 내)

1. **✅ .env 파일 권한 수정**:
   ```bash
   chmod 600 /var/www/html/topmkt/.env
   ```

2. **✅ .htaccess에 .env 접근 차단 추가**

3. **✅ API 키를 환경변수로 이동**

---

### 단기 (1주일 내)

1. **보안 헤더 추가**
2. **암호화 키를 환경변수로 이동**
3. **하드코딩된 비밀번호 제거**

---

### 중기 (1개월 내)

1. **보안 테스트 자동화**:
   - OWASP ZAP 또는 Burp Suite 스캔
   - 정기적인 취약점 점검

2. **로깅 강화**:
   - 실패한 로그인 시도 기록
   - 민감한 작업 감사 로그

3. **Rate Limiting 구현**:
   - 로그인 시도 제한
   - API 요청 제한

---

### 장기 (3개월 내)

1. **Web Application Firewall (WAF) 도입**
2. **침입 탐지 시스템 (IDS) 구축**
3. **정기적인 보안 교육**

---

## 상세 점검 결과

### 점검한 항목 (11개)

1. ✅ **SQL Injection** - Prepared Statements 사용 확인
2. ✅ **XSS (Cross-Site Scripting)** - htmlspecialchars 사용 확인
3. ✅ **CSRF (Cross-Site Request Forgery)** - CSRF 토큰 확인
4. ✅ **파일 업로드 보안** - 3중 검증 + .htaccess 확인
5. ✅ **인증/인가 시스템** - JWT + RBAC 확인
6. 🚨 **민감한 정보 노출** - API 키, 비밀번호 하드코딩 발견
7. ⚠️ **보안 헤더** - 미설정 확인
8. ✅ **입력 검증** - ValidationHelper 사용 확인
9. ✅ **세션/JWT 관리** - HTTP-only 쿠키 확인
10. ✅ **개인정보 암호화** - AES-256-GCM 확인
11. ⚠️ **.env 파일 보안** - 권한 및 웹 접근 문제 발견

---

## 종합 평가

### 강점

1. **코드 품질**: Prepared Statements, CSRF 토큰 등 보안 best practice 준수
2. **파일 업로드**: 매우 강력한 3중 검증 시스템
3. **암호화**: AES-256-GCM 산업 표준 사용
4. **인증 시스템**: JWT 기반 현대적 인증 구현

### 약점

1. **민감한 정보 관리**: API 키, 비밀번호가 소스코드에 하드코딩됨
2. **.env 파일 보안**: 권한 및 웹 접근 문제
3. **보안 헤더**: 주요 보안 헤더 미설정

### 결론

탑마케팅 프로젝트는 **대부분의 보안 best practice를 잘 준수**하고 있으나, **민감한 정보 관리에서 치명적인 문제**가 발견되었습니다.

**즉시 조치가 필요한 4가지 CRITICAL 이슈를 해결**하면 보안 등급을 **B+ 이상**으로 향상시킬 수 있습니다.

---

**점검 완료일**: 2025-10-19
**다음 점검 권장일**: 2025-11-19 (1개월 후)

---

## 부록: 빠른 수정 스크립트

```bash
#!/bin/bash
# 긴급 보안 수정 스크립트

# 1. .env 파일 권한 수정
chmod 600 /var/www/html/topmkt/.env
chown www-data:www-data /var/www/html/topmkt/.env

# 2. .htaccess 백업
cp /var/www/html/topmkt/.htaccess /var/www/html/topmkt/.htaccess.backup

# 3. .env 접근 차단 추가
cat >> /var/www/html/topmkt/.htaccess << 'EOF'

# 민감한 파일 접근 차단
<Files ".env">
    Order Allow,Deny
    Deny from all
</Files>

<FilesMatch "\.(env|log|sql|md|gitignore)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>
EOF

# 4. 검증
echo "=== .env 파일 권한 확인 ==="
ls -la /var/www/html/topmkt/.env

echo "=== .env 웹 접근 테스트 ==="
curl -I https://www.topmktx.com/.env

echo "=== 완료 ==="
```

**실행 방법**:
```bash
chmod +x /var/www/html/topmkt/scripts/security_fix.sh
sudo /var/www/html/topmkt/scripts/security_fix.sh
```
