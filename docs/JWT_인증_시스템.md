# JWT 인증 시스템 문서

## 개요

탑마케팅 플랫폼은 2025년 6월부터 기존 세션 기반 인증에서 **JWT(JSON Web Token) 기반 인증 시스템**으로 전면 전환되었습니다. 이를 통해 사용자들이 **30일간 자동 로그인 상태를 유지**할 수 있게 되었으며, 모바일 앱 사용자들이 며칠 후 접속해도 로그인이 풀리지 않는 문제를 해결했습니다.

## 주요 특징

### 🚀 핵심 기능
- **30일 장기 로그인**: "로그인 상태 유지" 체크시 30일간 자동 로그인
- **자동 토큰 갱신**: 만료 5분 전 백그라운드에서 자동 리프레시
- **모바일 친화적**: 컴퓨터 종료 후에도 로그인 상태 유지
- **보안 강화**: HMAC SHA256 서명, HTTP-only 쿠키 사용
- **확장성**: Stateless 인증으로 서버 분산 환경 지원

### 🔒 보안 기능
- **Access Token**: 1시간 유효, 짧은 수명으로 보안 강화
- **Refresh Token**: 30일 유효, 장기 로그인 지원
- **HTTP-only 쿠키**: XSS 공격 방지
- **토큰 서명**: HMAC SHA256으로 변조 방지
- **자동 무효화**: 로그아웃시 모든 토큰 즉시 무효화

## 시스템 아키텍처

### 토큰 구조
```
Access Token (1시간)
├── Header: {"typ":"JWT","alg":"HS256"}
├── Payload: {"user_id":123,"exp":1719123456,"iat":1719119856}
└── Signature: HMAC-SHA256(header + payload + secret)

Refresh Token (30일)
├── Header: {"typ":"JWT","alg":"HS256"}
├── Payload: {"user_id":123,"type":"refresh","exp":1721711856}
└── Signature: HMAC-SHA256(header + payload + secret)
```

### 인증 플로우
```
1. 사용자 로그인
   ↓
2. JWT 토큰 쌍 생성 (Access + Refresh)
   ↓
3. HTTP-only 쿠키에 토큰 저장
   ↓
4. 클라이언트에서 자동 토큰 관리 시작
   ↓
5. 55분 후 자동 토큰 갱신
   ↓
6. 30일간 반복 (Refresh Token 유효 기간)
```

## 구현 상세

### 1. JWT 헬퍼 클래스 (`/src/helpers/JWTHelper.php`)

```php
class JWTHelper {
    // JWT 토큰 생성
    public static function createToken($payload, $expiry = null)
    
    // JWT 토큰 검증
    public static function validateToken($token)
    
    // 토큰 쌍 생성 (Access + Refresh)
    public static function createTokenPair($user)
    
    // 토큰에서 사용자 정보 추출
    public static function getUserFromToken($token)
}
```

### 2. 인증 미들웨어 (`/src/middlewares/AuthMiddleware.php`)

```php
class AuthMiddleware {
    // JWT 기반 인증 확인
    public static function isAuthenticated()
    
    // 현재 사용자 정보 반환 (JWT 기반)
    public static function getCurrentUser()
    
    // JWT 토큰으로 사용자 인증
    private static function authenticateWithJWT()
}
```

### 3. 인증 컨트롤러 (`/src/controllers/AuthController.php`)

```php
class AuthController {
    // JWT 기반 로그인
    public function login()
    
    // JWT 토큰 갱신
    public function refreshToken()
    
    // 현재 사용자 정보 반환
    public function me()
    
    // JWT 세션 생성
    private function createJWTSession($user, $remember = false)
}
```

### 4. 프론트엔드 토큰 관리 (`/public/assets/js/jwt-auth.js`)

```javascript
class JWTAuth {
    // 토큰 상태 확인
    async checkTokenStatus()
    
    // 자동 토큰 갱신
    async refreshToken()
    
    // 하트비트 전송
    async sendHeartbeat()
    
    // 토큰 만료 처리
    async handleTokenExpiry()
}
```

## API 엔드포인트

### 인증 관련 API

| 메서드 | 엔드포인트 | 설명 | 응답 |
|--------|------------|------|------|
| `POST` | `/auth/login` | 로그인 및 JWT 토큰 발급 | `{"success":true,"redirect":"/"}` |
| `POST` | `/auth/logout` | 로그아웃 및 토큰 무효화 | `{"success":true}` |
| `POST` | `/auth/refresh` | JWT 토큰 갱신 | `{"success":true,"message":"토큰 갱신됨"}` |
| `GET` | `/auth/me` | 현재 사용자 정보 및 토큰 상태 | `{"success":true,"user":{...},"token_info":{...}}` |

### 토큰 상태 응답 예시
```json
{
  "success": true,
  "user": {
    "id": 123,
    "nickname": "사용자닉네임",
    "role": "GENERAL"
  },
  "token_info": {
    "expires_in": 3456,
    "issued_at": 1719119856,
    "type": "access"
  }
}
```

## 마이그레이션 가이드

### 세션 기반에서 JWT 기반으로 전환

#### 1. 기존 코드 패턴
```php
// 이전 방식 (세션 기반)
if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login');
    exit;
}
$userId = $_SESSION['user_id'];
```

#### 2. 새로운 코드 패턴
```php
// 새로운 방식 (JWT 기반)
AuthMiddleware::isAuthenticated();
$userId = AuthMiddleware::getCurrentUserId();
```

#### 3. 호환성 유지
- JWT 인증 성공시 세션에도 사용자 정보 설정
- 기존 `$_SESSION['user_id']` 접근 코드는 계속 작동
- 점진적 마이그레이션 가능

## 설정 방법

### 1. 환경 변수 설정 (`config/config.php`)
```php
// JWT 시크릿 키 (256비트 이상 권장)
define('JWT_SECRET_KEY', 'your-very-secure-secret-key-here');

// 토큰 만료 시간
define('JWT_ACCESS_TOKEN_EXPIRY', 3600);      // 1시간
define('JWT_REFRESH_TOKEN_EXPIRY', 2592000);  // 30일
```

### 2. 웹서버 설정 (쿠키 보안)
```apache
# Apache .htaccess
Header always edit Set-Cookie ^(.*)$ $1;Secure;SameSite=Strict
```

### 3. HTTPS 필수
- JWT 토큰은 반드시 HTTPS 환경에서 사용
- HTTP 환경에서는 쿠키 Secure 플래그 비활성화

## 모니터링 및 디버깅

### 1. 로그 확인
```bash
# JWT 관련 로그 확인
tail -f /var/log/php/error.log | grep JWT

# 토큰 갱신 로그
grep "Token refreshed" /var/log/application.log
```

### 2. 브라우저 개발자 도구
```javascript
// JWT 디버그 정보
console.log(window.jwtAuth.debug());

// 토큰 상태 확인
window.jwtAuth.checkTokenStatus();
```

### 3. 쿠키 확인
```javascript
// 브라우저 콘솔에서 토큰 쿠키 확인
document.cookie.split(';').filter(c => c.includes('token'));
```

## 보안 고려사항

### 1. 토큰 보안
- **시크릿 키**: 256비트 이상 랜덤 문자열 사용
- **토큰 수명**: Access Token은 짧게, Refresh Token은 적절하게
- **쿠키 설정**: HttpOnly, Secure, SameSite 플래그 적용

### 2. 브루트 포스 방지
- 로그인 실패 5회 이상시 계정 잠금
- JWT 토큰 무효화시 즉시 쿠키 삭제
- 의심스러운 접근시 모든 토큰 무효화

### 3. XSS 방지
- 모든 사용자 입력 새니타이징
- CSP(Content Security Policy) 헤더 적용
- JWT 토큰은 JavaScript에서 접근 불가

## 문제 해결

### 1. 자주 발생하는 문제

#### Q: 로그인 후 바로 로그아웃됨
A: 시간대 설정 확인, 서버 시간과 클라이언트 시간 동기화

#### Q: 토큰 갱신이 안됨
A: Refresh Token 쿠키 확인, HTTPS 설정 확인

#### Q: 30일 후에도 로그인 유지됨
A: 정상 동작, Refresh Token이 계속 갱신되어 무제한 로그인 유지

### 2. 응급 복구
```php
// JWT 시스템 비활성화 (응급시)
// AuthMiddleware.php에서 임시 세션 폴백
if (!$user && isset($_SESSION['user_id'])) {
    return self::getUserFromDatabase($_SESSION['user_id']);
}
```

## 성능 최적화

### 1. 토큰 검증 최적화
- 서명 검증을 위한 CPU 사용량 모니터링
- 필요시 Redis 캐싱 도입
- 토큰 블랙리스트 관리

### 2. 네트워크 최적화
- 토큰 갱신 요청 최소화
- 하트비트 간격 조정
- 불필요한 `/auth/me` 호출 방지

## 마무리

JWT 인증 시스템 전환으로 탑마케팅 사용자들은 이제:
- ✅ **컴퓨터를 꺼놔도 로그아웃되지 않음**
- ✅ **모바일에서 며칠 후 접속해도 로그인 유지**
- ✅ **30일간 자동 로그인 상태 유지**
- ✅ **백그라운드에서 자동 토큰 갱신**

이를 통해 사용자 경험이 크게 개선되었으며, 현대적이고 확장 가능한 인증 시스템을 구축했습니다.

---

**문서 버전**: 1.0  
**작성일**: 2025년 6월 25일  
**작성자**: Claude Code  
**검토자**: 탑마케팅 개발팀