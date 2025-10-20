# 권한 검증(Access Control) 보안 점검 상세 계획

**작성일**: 2025-10-20
**작성자**: Claude (Anthropic)
**프로젝트**: 탑마케팅 (TOPMKT)
**목적**: 권한 검증 취약점 발견 및 수정

---

## 📋 목차

1. [개요](#개요)
2. [검증 범위](#검증-범위)
3. [검증 방법론](#검증-방법론)
4. [1단계: 수직적 권한 상승 검증](#1단계-수직적-권한-상승-검증)
5. [2단계: 수평적 권한 상승 검증 (IDOR)](#2단계-수평적-권한-상승-검증-idor)
6. [3단계: API 엔드포인트 권한 검증](#3단계-api-엔드포인트-권한-검증)
7. [4단계: 파일 접근 권한 검증](#4단계-파일-접근-권한-검증)
8. [5단계: 세션 및 인증 검증](#5단계-세션-및-인증-검증)
9. [테스트 계정 생성 계획](#테스트-계정-생성-계획)
10. [예상 소요 시간](#예상-소요-시간)
11. [산출물](#산출물)

---

## 개요

### 목적
탑마케팅 플랫폼의 모든 권한 검증 로직을 체계적으로 점검하여 다음 취약점을 발견하고 수정합니다:

- **수직적 권한 상승**: 일반 사용자가 관리자 기능 접근
- **수평적 권한 상승(IDOR)**: 사용자 A가 사용자 B의 데이터 접근
- **API 권한 우회**: 인증 없이 API 엔드포인트 호출
- **파일 접근 제어**: 다른 사용자의 파일 무단 접근
- **세션 탈취**: 세션 관리 취약점

### 위험도 평가 기준
- **치명적(Critical)**: 즉시 수정 필요, 프로덕션 배포 차단
- **높음(High)**: 1주일 내 수정
- **중간(Medium)**: 1개월 내 수정
- **낮음(Low)**: 3개월 내 수정

---

## 검증 범위

### 사용자 역할 (Role-Based Access Control)

탑마케팅 플랫폼의 사용자 역할:

1. **관리자(Admin)** - `role = 'admin'`
   - 모든 기능 접근 가능
   - 사용자 관리, 기업 관리, 강의/행사 관리, 공지사항 관리
   - 통계 및 리포트 조회

2. **기업 회원(Corporate)** - `role = 'corporate'`
   - 소속 기업 관련 기능만 접근
   - 기업 공지사항 관리
   - 소속 직원 관리
   - 기업 정보 수정

3. **일반 사용자(User)** - `role = 'user'`
   - 자신의 데이터만 접근 가능
   - 강의/행사 신청, 커뮤니티 참여
   - 프로필 수정

4. **비로그인 사용자(Guest)**
   - 공개 페이지만 접근
   - 로그인 없이 목록 조회

### 검증 대상 페이지 분류

#### 관리자 전용 페이지 (14개)
```
/admin/dashboard
/admin/users
/admin/users/{id}/edit
/admin/users/{id}/delete
/admin/lectures
/admin/lectures/create
/admin/lectures/{id}/edit
/admin/lectures/{id}/delete
/admin/events
/admin/events/create
/admin/events/{id}/edit
/admin/events/{id}/delete
/admin/notices
/admin/statistics
```

#### 기업 회원 전용 페이지 (8개)
```
/corporate/dashboard
/corporate/notices
/corporate/notices/create
/corporate/notices/{id}/edit
/corporate/notices/{id}/delete
/corporate/employees
/corporate/profile/edit
/corporate/statistics
```

#### 사용자 페이지 (자신의 데이터만) (12개)
```
/user/profile
/user/edit
/user/delete
/user/registrations (자신의 신청 내역)
/user/posts (자신이 작성한 글)
/community/{id}/edit (자신의 글만 수정)
/community/{id}/delete (자신의 글만 삭제)
/lectures/{id}/registration (자신의 신청)
/events/{id}/registration (자신의 신청)
/chat (자신이 참여한 채팅)
/notifications/settings (자신의 알림 설정)
/api/users/{id}/update (자신의 정보만 수정)
```

#### 공개 페이지 (10개)
```
/
/auth/login
/auth/signup
/auth/forgot-password
/lectures (목록)
/lectures/{id} (상세)
/events (목록)
/events/{id} (상세)
/community (목록)
/community/{id} (상세)
```

### 검증 대상 API 엔드포인트 (약 50개)

#### 인증 필요 API
```
POST /api/users/{id}/update
DELETE /api/users/{id}
POST /api/lectures/{id}/registration
POST /api/events/{id}/registration
POST /api/community/posts
PUT /api/community/posts/{id}
DELETE /api/community/posts/{id}
POST /api/comments
PUT /api/comments/{id}
DELETE /api/comments/{id}
POST /api/likes
POST /api/chat/send-notification
GET /api/notifications/settings
PUT /api/notifications/settings
```

#### 관리자 전용 API
```
POST /api/admin/users
PUT /api/admin/users/{id}
DELETE /api/admin/users/{id}
PUT /api/admin/lectures/{id}/status
PUT /api/admin/registrations/{id}/approve
PUT /api/admin/registrations/{id}/reject
```

#### 기업 회원 전용 API
```
POST /api/corporate/notices
PUT /api/corporate/notices/{id}
DELETE /api/corporate/notices/{id}
GET /api/corporate/employees
```

---

## 검증 방법론

### 1. 화이트박스 테스트 (White-box Testing)

**방법**: 소스코드 직접 검토

**검토 대상**:
- AuthMiddleware 구현 (`/src/middlewares/AuthMiddleware.php`)
- AdminMiddleware 구현 (있는 경우)
- CorporateMiddleware 구현 (있는 경우)
- 각 Controller의 권한 검증 로직
- 라우트 설정 (`/src/config/routes.php`)

**검토 항목**:
```php
// 1. 미들웨어 적용 확인
$router->get('/admin/users', 'AdminController@index', [AuthMiddleware::class]);

// 2. 역할 검증 확인
if ($_SESSION['user']['role'] !== 'admin') {
    throw new Exception('관리자만 접근 가능합니다.');
}

// 3. 소유권 검증 확인 (IDOR 방지)
if ($post['user_id'] !== $_SESSION['user']['id']) {
    throw new Exception('권한이 없습니다.');
}
```

**체크리스트**:
- [ ] 모든 관리자 페이지에 AuthMiddleware + 역할 검증 적용?
- [ ] 모든 기업 회원 페이지에 기업 권한 검증 적용?
- [ ] 사용자 데이터 수정/삭제 시 소유권 검증?
- [ ] API 엔드포인트에 인증 미들웨어 적용?
- [ ] CSRF 토큰 검증 적용?

---

### 2. 블랙박스 테스트 (Black-box Testing)

**방법**: 실제 HTTP 요청으로 테스트

**도구**:
- curl (명령줄)
- Postman (API 테스트)
- Burp Suite Community Edition (프록시)
- Chrome DevTools (Network 탭)

**테스트 시나리오**:
1. 비로그인 상태에서 관리자 페이지 접근 시도
2. 일반 사용자로 관리자 페이지 접근 시도
3. 사용자 A로 로그인 후 사용자 B의 데이터 수정 시도
4. 세션 쿠키 조작 시도
5. API 엔드포인트 직접 호출 시도

---

### 3. 그레이박스 테스트 (Grey-box Testing)

**방법**: 소스코드 참조 + 실제 요청 테스트

**장점**: 코드 로직을 알고 있으므로 취약점 우회 시도 가능

---

## 1단계: 수직적 권한 상승 검증

### 개요
**수직적 권한 상승(Vertical Privilege Escalation)**: 낮은 권한 사용자가 높은 권한 기능에 접근하는 취약점

### 검증 시나리오

#### 시나리오 1-1: 일반 사용자 → 관리자 기능 접근

**목표**: 일반 사용자가 관리자 페이지에 접근할 수 없는지 확인

**테스트 계정**:
- 일반 사용자: `user_test@example.com` (role = 'user')

**테스트 방법**:
```bash
# 1. 일반 사용자로 로그인
curl -X POST https://www.topmktx.com/auth/login \
  -d "phone=01012345678&password=test1234"

# 2. 세션 쿠키 저장
COOKIE="PHPSESSID=abcd1234..."

# 3. 관리자 페이지 접근 시도
curl -b "$COOKIE" https://www.topmktx.com/admin/dashboard
curl -b "$COOKIE" https://www.topmktx.com/admin/users
curl -b "$COOKIE" https://www.topmktx.com/admin/lectures/create
```

**예상 결과**:
- ✅ **정상**: HTTP 403 Forbidden 또는 리다이렉트(/auth/login)
- ❌ **취약**: HTTP 200 OK (관리자 페이지 접근 성공)

**검증 코드 위치**:
```
/src/controllers/AdminController.php
/src/middlewares/AuthMiddleware.php
/src/config/routes.php
```

**체크리스트**:
- [ ] `/admin/dashboard` - 접근 차단?
- [ ] `/admin/users` - 접근 차단?
- [ ] `/admin/users/{id}/edit` - 접근 차단?
- [ ] `/admin/users/{id}/delete` - 접근 차단?
- [ ] `/admin/lectures` - 접근 차단?
- [ ] `/admin/lectures/create` - 접근 차단?
- [ ] `/admin/lectures/{id}/edit` - 접근 차단?
- [ ] `/admin/lectures/{id}/delete` - 접근 차단?
- [ ] `/admin/events` - 접근 차단?
- [ ] `/admin/events/create` - 접근 차단?
- [ ] `/admin/events/{id}/edit` - 접근 차단?
- [ ] `/admin/events/{id}/delete` - 접근 차단?
- [ ] `/admin/notices` - 접근 차단?
- [ ] `/admin/statistics` - 접근 차단?

---

#### 시나리오 1-2: 일반 사용자 → 관리자 API 호출

**목표**: 일반 사용자가 관리자 API를 호출할 수 없는지 확인

**테스트 방법**:
```bash
# 일반 사용자로 로그인 후
# 관리자 API 호출 시도

# 1. 사용자 삭제 API
curl -X DELETE -b "$COOKIE" \
  https://www.topmktx.com/api/admin/users/999

# 2. 강의 상태 변경 API
curl -X PUT -b "$COOKIE" \
  https://www.topmktx.com/api/admin/lectures/1/status \
  -d "status=published"

# 3. 신청 승인 API
curl -X PUT -b "$COOKIE" \
  https://www.topmktx.com/api/admin/registrations/1/approve
```

**예상 결과**:
- ✅ **정상**: HTTP 403 Forbidden + JSON {"error": "권한이 없습니다"}
- ❌ **취약**: HTTP 200 OK (API 호출 성공)

**체크리스트**:
- [ ] `DELETE /api/admin/users/{id}` - 차단?
- [ ] `PUT /api/admin/lectures/{id}/status` - 차단?
- [ ] `PUT /api/admin/registrations/{id}/approve` - 차단?
- [ ] `PUT /api/admin/registrations/{id}/reject` - 차단?
- [ ] `POST /api/admin/notices` - 차단?

---

#### 시나리오 1-3: 일반 사용자 → 기업 회원 기능 접근

**목표**: 일반 사용자가 기업 회원 페이지에 접근할 수 없는지 확인

**테스트 방법**:
```bash
# 일반 사용자로 로그인 후
curl -b "$COOKIE" https://www.topmktx.com/corporate/dashboard
curl -b "$COOKIE" https://www.topmktx.com/corporate/notices/create
curl -b "$COOKIE" https://www.topmktx.com/corporate/employees
```

**예상 결과**:
- ✅ **정상**: HTTP 403 Forbidden
- ❌ **취약**: HTTP 200 OK

**체크리스트**:
- [ ] `/corporate/dashboard` - 차단?
- [ ] `/corporate/notices` - 차단?
- [ ] `/corporate/notices/create` - 차단?
- [ ] `/corporate/employees` - 차단?

---

#### 시나리오 1-4: 비로그인 사용자 → 로그인 필요 페이지 접근

**목표**: 비로그인 사용자가 인증 필요 페이지에 접근할 수 없는지 확인

**테스트 방법**:
```bash
# 로그인 없이 접근 시도
curl https://www.topmktx.com/user/profile
curl https://www.topmktx.com/user/edit
curl https://www.topmktx.com/user/registrations
```

**예상 결과**:
- ✅ **정상**: HTTP 302 Redirect → /auth/login
- ❌ **취약**: HTTP 200 OK (프로필 페이지 노출)

**체크리스트**:
- [ ] `/user/profile` - 리다이렉트?
- [ ] `/user/edit` - 리다이렉트?
- [ ] `/user/registrations` - 리다이렉트?
- [ ] `/chat` - 리다이렉트?
- [ ] `/notifications/settings` - 리다이렉트?

---

### 예상 취약점

#### 취약점 1-A: 미들웨어 미적용
```php
// 취약한 코드 예시
$router->get('/admin/users', 'AdminController@index');
// ❌ AuthMiddleware가 적용되지 않음!
```

**수정 방법**:
```php
// 올바른 코드
$router->get('/admin/users', 'AdminController@index', [AuthMiddleware::class]);
```

---

#### 취약점 1-B: 역할 검증 누락
```php
// 취약한 코드 예시
public function index() {
    // ❌ 역할 검증 없이 바로 관리자 페이지 렌더링
    return view('admin/users/index');
}
```

**수정 방법**:
```php
// 올바른 코드
public function index() {
    // ✅ 역할 검증 추가
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        throw new Exception('관리자만 접근 가능합니다.');
    }

    return view('admin/users/index');
}
```

---

#### 취약점 1-C: 클라이언트 측 권한 검증만 존재
```javascript
// 취약한 코드 예시 (JavaScript)
if (user.role !== 'admin') {
    // ❌ 클라이언트 측에서만 관리자 버튼 숨김
    document.getElementById('admin-menu').style.display = 'none';
}
```

**문제**: 브라우저 DevTools로 HTML 수정하면 우회 가능

**수정 방법**: 서버 측에서 반드시 권한 검증

---

## 2단계: 수평적 권한 상승 검증 (IDOR)

### 개요
**IDOR(Insecure Direct Object References)**: 사용자 A가 사용자 B의 데이터에 접근하는 취약점

### 검증 시나리오

#### 시나리오 2-1: 다른 사용자 프로필 수정 시도

**목표**: 사용자 A가 사용자 B의 프로필을 수정할 수 없는지 확인

**테스트 계정**:
- 사용자 A: `user_a@example.com` (id = 100)
- 사용자 B: `user_b@example.com` (id = 101)

**테스트 방법**:
```bash
# 1. 사용자 A로 로그인
curl -X POST https://www.topmktx.com/auth/login \
  -d "phone=01011111111&password=test1234"

COOKIE_A="PHPSESSID=xxx..."

# 2. 사용자 B의 프로필 수정 시도
curl -X POST -b "$COOKIE_A" \
  https://www.topmktx.com/api/users/101/update \
  -d "name=해킹당함&email=hacked@evil.com"

# 3. 사용자 B의 프로필 페이지 접근 시도
curl -b "$COOKIE_A" https://www.topmktx.com/user/profile?user_id=101
```

**예상 결과**:
- ✅ **정상**: HTTP 403 Forbidden + JSON {"error": "권한이 없습니다"}
- ❌ **취약**: HTTP 200 OK + {"success": true} (수정 성공!)

**검증 코드 위치**:
```
/src/controllers/UserController.php (update 메서드)
/src/models/User.php (update 메서드)
```

**체크리스트**:
- [ ] `POST /api/users/{id}/update` - 자신의 ID만 수정 가능?
- [ ] `DELETE /api/users/{id}` - 자신의 계정만 삭제 가능?
- [ ] `GET /user/profile?user_id={id}` - 자신의 프로필만 조회?

---

#### 시나리오 2-2: 다른 사용자 게시글 수정/삭제 시도

**목표**: 사용자 A가 사용자 B의 게시글을 수정/삭제할 수 없는지 확인

**테스트 방법**:
```bash
# 사용자 A로 로그인 후
# 사용자 B가 작성한 게시글(id=999) 수정 시도

curl -X PUT -b "$COOKIE_A" \
  https://www.topmktx.com/api/community/posts/999 \
  -d "title=해킹됨&content=해킹당한 글입니다"

# 삭제 시도
curl -X DELETE -b "$COOKIE_A" \
  https://www.topmktx.com/api/community/posts/999
```

**예상 결과**:
- ✅ **정상**: HTTP 403 Forbidden
- ❌ **취약**: HTTP 200 OK (수정/삭제 성공)

**검증 코드**:
```php
// 올바른 검증 로직 예시
public function update($id) {
    $post = $this->postModel->findById($id);

    // ✅ 작성자 확인
    if ($post['user_id'] !== $_SESSION['user']['id']) {
        http_response_code(403);
        return json_encode(['error' => '권한이 없습니다.']);
    }

    // 수정 로직...
}
```

**체크리스트**:
- [ ] `PUT /api/community/posts/{id}` - 작성자만 수정?
- [ ] `DELETE /api/community/posts/{id}` - 작성자만 삭제?
- [ ] `PUT /api/comments/{id}` - 작성자만 수정?
- [ ] `DELETE /api/comments/{id}` - 작성자만 삭제?

---

#### 시나리오 2-3: 다른 사용자 강의 신청 내역 조회

**목표**: 사용자 A가 사용자 B의 강의 신청 내역을 조회할 수 없는지 확인

**테스트 방법**:
```bash
# 사용자 A로 로그인 후
# 사용자 B의 신청 내역 조회 시도

curl -b "$COOKIE_A" \
  https://www.topmktx.com/api/users/101/registrations

curl -b "$COOKIE_A" \
  https://www.topmktx.com/user/registrations?user_id=101
```

**예상 결과**:
- ✅ **정상**: HTTP 403 Forbidden 또는 빈 배열 []
- ❌ **취약**: 사용자 B의 신청 내역 노출

**체크리스트**:
- [ ] `GET /api/users/{id}/registrations` - 자신의 내역만 조회?
- [ ] `GET /user/registrations?user_id={id}` - 파라미터 무시하고 세션 ID 사용?

---

#### 시나리오 2-4: 다른 사용자 파일 다운로드

**목표**: 사용자 A가 사용자 B가 업로드한 파일을 다운로드할 수 없는지 확인

**테스트 방법**:
```bash
# 사용자 A로 로그인 후
# 사용자 B의 프로필 이미지 다운로드 시도

curl -b "$COOKIE_A" -o hacked_profile.jpg \
  https://www.topmktx.com/assets/uploads/profiles/user_101_profile.jpg
```

**예상 결과**:
- ✅ **정상**: HTTP 403 Forbidden (프로필 이미지는 공개이므로 이 경우 200 OK도 정상)
- ❌ **취약**: 민감한 개인 파일 다운로드 성공

**참고**: 프로필 이미지는 공개 파일이므로 다운로드 가능해야 함. 하지만 민감한 문서(사업자등록증 등)는 차단해야 함.

**체크리스트**:
- [ ] 프로필 이미지 - 공개 (200 OK)
- [ ] 사업자등록증 이미지 - 비공개 (403 Forbidden)
- [ ] 기타 개인 문서 - 비공개 (403 Forbidden)

---

#### 시나리오 2-5: 기업 회원 간 데이터 접근

**목표**: 기업 A 회원이 기업 B의 데이터를 조회/수정할 수 없는지 확인

**테스트 계정**:
- 기업 A 회원: `corporate_a@example.com` (company_id = 1)
- 기업 B 회원: `corporate_b@example.com` (company_id = 2)

**테스트 방법**:
```bash
# 기업 A 회원으로 로그인 후
# 기업 B의 공지사항 수정 시도

curl -X PUT -b "$COOKIE_CORP_A" \
  https://www.topmktx.com/api/corporate/notices/999 \
  -d "title=해킹됨&content=..."

# 기업 B의 직원 목록 조회 시도
curl -b "$COOKIE_CORP_A" \
  https://www.topmktx.com/api/corporate/employees?company_id=2
```

**예상 결과**:
- ✅ **정상**: HTTP 403 Forbidden
- ❌ **취약**: 기업 B의 데이터 노출/수정

**검증 코드**:
```php
// 올바른 검증 로직 예시
public function updateNotice($id) {
    $notice = $this->noticeModel->findById($id);

    // ✅ 기업 소속 확인
    if ($notice['company_id'] !== $_SESSION['user']['company_id']) {
        http_response_code(403);
        return json_encode(['error': '권한이 없습니다.']);
    }

    // 수정 로직...
}
```

**체크리스트**:
- [ ] `PUT /api/corporate/notices/{id}` - 자신의 기업 공지만 수정?
- [ ] `DELETE /api/corporate/notices/{id}` - 자신의 기업 공지만 삭제?
- [ ] `GET /api/corporate/employees` - 자신의 기업 직원만 조회?

---

### 예상 취약점

#### 취약점 2-A: ID 파라미터 검증 누락
```php
// 취약한 코드 예시
public function update($id) {
    // ❌ ID 검증 없이 바로 업데이트
    $data = $_POST;
    $this->userModel->update($id, $data);
    return json_encode(['success' => true]);
}
```

**수정 방법**:
```php
// 올바른 코드
public function update($id) {
    // ✅ 세션 사용자 ID와 파라미터 ID 비교
    if ((int)$id !== $_SESSION['user']['id']) {
        http_response_code(403);
        return json_encode(['error' => '권한이 없습니다.']);
    }

    $data = $_POST;
    $this->userModel->update($id, $data);
    return json_encode(['success' => true]);
}
```

---

#### 취약점 2-B: 데이터베이스 조회 시 소유권 미확인
```php
// 취약한 코드 예시
public function getRegistrations($userId) {
    // ❌ 요청한 사용자가 본인인지 확인 안 함
    $sql = "SELECT * FROM registrations WHERE user_id = ?";
    return $this->db->fetchAll($sql, [$userId]);
}
```

**수정 방법**:
```php
// 올바른 코드
public function getRegistrations($userId) {
    // ✅ 세션 사용자 ID 강제 사용
    $currentUserId = $_SESSION['user']['id'];
    $sql = "SELECT * FROM registrations WHERE user_id = ?";
    return $this->db->fetchAll($sql, [$currentUserId]);
}
```

---

#### 취약점 2-C: URL 파라미터 신뢰
```php
// 취약한 코드 예시
public function profile() {
    // ❌ URL 파라미터를 그대로 신뢰
    $userId = $_GET['user_id'] ?? $_SESSION['user']['id'];
    $user = $this->userModel->findById($userId);
    return view('user/profile', ['user' => $user]);
}
```

**수정 방법**:
```php
// 올바른 코드
public function profile() {
    // ✅ URL 파라미터 무시, 세션만 사용
    $userId = $_SESSION['user']['id'];
    $user = $this->userModel->findById($userId);
    return view('user/profile', ['user' => $user]);
}
```

---

## 3단계: API 엔드포인트 권한 검증

### 개요
API 엔드포인트가 인증 없이 호출 가능한지 확인

### 검증 시나리오

#### 시나리오 3-1: 인증 없이 API 호출

**목표**: 인증 토큰 없이 API를 호출할 수 없는지 확인

**테스트 방법**:
```bash
# 로그인 없이 API 직접 호출

# 1. 프로필 수정 API
curl -X POST https://www.topmktx.com/api/users/100/update \
  -d "name=해킹"

# 2. 게시글 작성 API
curl -X POST https://www.topmktx.com/api/community/posts \
  -d "title=해킹&content=해킹"

# 3. 좋아요 API
curl -X POST https://www.topmktx.com/api/likes \
  -d "post_id=1"
```

**예상 결과**:
- ✅ **정상**: HTTP 401 Unauthorized + JSON {"error": "로그인이 필요합니다"}
- ❌ **취약**: HTTP 200 OK (API 호출 성공)

**체크리스트**:
- [ ] `POST /api/users/{id}/update` - 인증 필요?
- [ ] `POST /api/community/posts` - 인증 필요?
- [ ] `PUT /api/community/posts/{id}` - 인증 필요?
- [ ] `DELETE /api/community/posts/{id}` - 인증 필요?
- [ ] `POST /api/comments` - 인증 필요?
- [ ] `POST /api/likes` - 인증 필요?
- [ ] `POST /api/lectures/{id}/registration` - 인증 필요?
- [ ] `POST /api/events/{id}/registration` - 인증 필요?

---

#### 시나리오 3-2: CSRF 토큰 없이 API 호출

**목표**: CSRF 토큰 없이 상태 변경 API를 호출할 수 없는지 확인

**테스트 방법**:
```bash
# 로그인 후 세션 쿠키만 사용 (CSRF 토큰 제외)

curl -X POST -b "$COOKIE" \
  https://www.topmktx.com/api/users/100/update \
  -d "name=해킹"
  # ❌ CSRF 토큰 없음!
```

**예상 결과**:
- ✅ **정상**: HTTP 403 Forbidden + JSON {"error": "CSRF 토큰이 유효하지 않습니다"}
- ❌ **취약**: HTTP 200 OK (CSRF 우회 성공)

**체크리스트**:
- [ ] 모든 POST/PUT/DELETE API - CSRF 토큰 검증?

---

#### 시나리오 3-3: Content-Type 변경 우회

**목표**: Content-Type을 변경하여 CSRF 검증 우회 불가능한지 확인

**테스트 방법**:
```bash
# Content-Type: application/json으로 변경 시도
curl -X POST -b "$COOKIE" \
  -H "Content-Type: application/json" \
  https://www.topmktx.com/api/users/100/update \
  -d '{"name": "해킹"}'
```

**예상 결과**:
- ✅ **정상**: JSON 요청도 CSRF 검증 적용
- ❌ **취약**: JSON 요청은 CSRF 우회 가능

---

## 4단계: 파일 접근 권한 검증

### 개요
업로드된 파일에 대한 접근 권한 확인

### 검증 시나리오

#### 시나리오 4-1: 직접 URL로 파일 접근

**목표**: 민감한 파일이 직접 URL로 접근 불가능한지 확인

**테스트 방법**:
```bash
# 파일 직접 URL 접근 시도
curl https://www.topmktx.com/assets/uploads/profiles/user_101_profile.jpg
curl https://www.topmktx.com/assets/uploads/documents/company_1_business_license.pdf
```

**예상 결과**:
- 프로필 이미지: ✅ 200 OK (공개 파일)
- 사업자등록증: ✅ 403 Forbidden (비공개 파일)

**검증 방법**: .htaccess 또는 PHP 스크립트로 파일 접근 제어

---

#### 시나리오 4-2: Path Traversal 공격

**목표**: 경로 탐색 공격으로 다른 파일 접근 불가능한지 확인

**테스트 방법**:
```bash
# ../를 사용한 경로 탐색 시도
curl https://www.topmktx.com/api/files/download?file=../../.env
curl https://www.topmktx.com/api/files/download?file=../../../etc/passwd
```

**예상 결과**:
- ✅ **정상**: HTTP 403 Forbidden (경로 검증)
- ❌ **취약**: 파일 다운로드 성공

**검증 코드**:
```php
// 올바른 파일 경로 검증
$filename = $_GET['file'];

// ✅ ../ 차단
if (strpos($filename, '..') !== false) {
    http_response_code(403);
    die('Invalid file path');
}

// ✅ 허용된 디렉토리만 접근
$allowedDir = '/var/www/html/topmkt/public/assets/uploads/';
$realPath = realpath($allowedDir . $filename);

if (strpos($realPath, $allowedDir) !== 0) {
    http_response_code(403);
    die('Access denied');
}
```

---

## 5단계: 세션 및 인증 검증

### 개요
세션 관리 및 인증 메커니즘의 보안성 확인

### 검증 시나리오

#### 시나리오 5-1: 세션 고정 공격 (Session Fixation)

**목표**: 로그인 후 세션 ID가 재생성되는지 확인

**테스트 방법**:
```bash
# 1. 로그인 전 세션 ID 확인
curl -I https://www.topmktx.com/auth/login
# Set-Cookie: PHPSESSID=old_session_id

# 2. 로그인 후 세션 ID 확인
curl -X POST https://www.topmktx.com/auth/login \
  -d "phone=01012345678&password=test1234" \
  -c cookies.txt

cat cookies.txt
# PHPSESSID=new_session_id (변경됨?)
```

**예상 결과**:
- ✅ **정상**: 세션 ID 변경됨 (old_session_id ≠ new_session_id)
- ❌ **취약**: 세션 ID 그대로 유지

**검증 코드**:
```php
// 로그인 성공 시 세션 재생성
public function login($phone, $password) {
    // 인증 로직...

    if ($authenticated) {
        // ✅ 세션 재생성 (Session Fixation 방지)
        session_regenerate_id(true);

        $_SESSION['user'] = $user;
    }
}
```

---

#### 시나리오 5-2: 세션 타임아웃

**목표**: 일정 시간 후 세션이 만료되는지 확인

**테스트 방법**:
```bash
# 1. 로그인
curl -X POST https://www.topmktx.com/auth/login \
  -d "phone=01012345678&password=test1234" \
  -c cookies.txt

# 2. 30분 대기 (또는 sleep 30분)
sleep 1800

# 3. 인증 필요 페이지 접근
curl -b cookies.txt https://www.topmktx.com/user/profile
```

**예상 결과**:
- ✅ **정상**: HTTP 302 Redirect → /auth/login (세션 만료)
- ❌ **취약**: HTTP 200 OK (세션 유지)

**설정 확인**:
```php
// php.ini 또는 코드
ini_set('session.gc_maxlifetime', 1800); // 30분
ini_set('session.cookie_lifetime', 0); // 브라우저 종료 시
```

---

#### 시나리오 5-3: Remember Me 토큰 보안

**목표**: Remember Me 토큰이 안전하게 관리되는지 확인

**테스트 방법**:
```bash
# 1. "로그인 상태 유지" 체크하고 로그인
curl -X POST https://www.topmktx.com/auth/login \
  -d "phone=01012345678&password=test1234&remember=1" \
  -c cookies.txt

# 2. remember_token 쿠키 확인
cat cookies.txt
# remember_token=xxxxxxxxxxxxxxxx

# 3. 토큰 탈취 시도 (다른 브라우저에서)
curl -b "remember_token=xxxxxxxxxxxxxxxx" \
  https://www.topmktx.com/user/profile
```

**예상 결과**:
- ✅ **정상**: Remember 토큰 + IP/User-Agent 검증
- ❌ **취약**: Remember 토큰만으로 로그인 성공

---

## 테스트 계정 생성 계획

### 필요한 테스트 계정

#### 1. 관리자 계정
```
이메일: admin_test@topmktx.com
전화번호: 010-9999-0001
비밀번호: AdminTest1234!
역할: admin
```

#### 2. 일반 사용자 A
```
이메일: user_a@example.com
전화번호: 010-9999-1001
비밀번호: UserA1234!
역할: user
ID: 1000 (테스트용 고정 ID)
```

#### 3. 일반 사용자 B
```
이메일: user_b@example.com
전화번호: 010-9999-1002
비밀번호: UserB1234!
역할: user
ID: 1001 (테스트용 고정 ID)
```

#### 4. 기업 회원 A (기업 A 소속)
```
이메일: corporate_a@example.com
전화번호: 010-9999-2001
비밀번호: CorpA1234!
역할: corporate
company_id: 100 (테스트용 기업 A)
```

#### 5. 기업 회원 B (기업 B 소속)
```
이메일: corporate_b@example.com
전화번호: 010-9999-2002
비밀번호: CorpB1234!
역할: corporate
company_id: 101 (테스트용 기업 B)
```

### 테스트 데이터 생성 스크립트

```sql
-- 테스트 계정 생성 (실제 실행 전 백업 필수!)
-- 주의: 프로덕션 DB에서는 실행하지 말 것!

-- 1. 관리자 계정
INSERT INTO users (name, email, phone, password, role, status, created_at) VALUES
('관리자테스트', 'admin_test@topmktx.com', '암호화된값', '해시된비밀번호', 'admin', 'active', NOW());

-- 2. 일반 사용자 A
INSERT INTO users (id, name, email, phone, password, role, status, created_at) VALUES
(1000, '사용자A', 'user_a@example.com', '암호화된값', '해시된비밀번호', 'user', 'active', NOW());

-- 3. 일반 사용자 B
INSERT INTO users (id, name, email, phone, password, role, status, created_at) VALUES
(1001, '사용자B', 'user_b@example.com', '암호화된값', '해시된비밀번호', 'user', 'active', NOW());

-- 4. 테스트 기업 A
INSERT INTO companies (id, name, business_number, status, created_at) VALUES
(100, '테스트기업A', '123-45-67890', 'active', NOW());

-- 5. 기업 회원 A
INSERT INTO users (name, email, phone, password, role, company_id, status, created_at) VALUES
('기업회원A', 'corporate_a@example.com', '암호화된값', '해시된비밀번호', 'corporate', 100, 'active', NOW());

-- 6. 테스트 기업 B
INSERT INTO companies (id, name, business_number, status, created_at) VALUES
(101, '테스트기업B', '123-45-67891', 'active', NOW());

-- 7. 기업 회원 B
INSERT INTO users (name, email, phone, password, role, company_id, status, created_at) VALUES
('기업회원B', 'corporate_b@example.com', '암호화된값', '해시된비밀번호', 'corporate', 101, 'active', NOW());

-- 8. 사용자 A의 테스트 게시글 (IDOR 테스트용)
INSERT INTO posts (id, user_id, title, content, status, created_at) VALUES
(9000, 1000, '사용자A의 게시글', '이 글은 사용자A만 수정/삭제 가능해야 함', 'published', NOW());

-- 9. 사용자 B의 테스트 게시글 (IDOR 테스트용)
INSERT INTO posts (id, user_id, title, content, status, created_at) VALUES
(9001, 1001, '사용자B의 게시글', '이 글은 사용자B만 수정/삭제 가능해야 함', 'published', NOW());
```

---

## 예상 소요 시간

### 1단계: 수직적 권한 상승 검증
- 소스코드 검토: 4시간
- 블랙박스 테스트: 2시간
- 취약점 문서화: 1시간
- **소계: 7시간**

### 2단계: 수평적 권한 상승 검증 (IDOR)
- 소스코드 검토: 6시간
- 블랙박스 테스트: 4시간
- 취약점 문서화: 2시간
- **소계: 12시간**

### 3단계: API 엔드포인트 권한 검증
- API 목록 정리: 1시간
- 인증 검증 테스트: 3시간
- CSRF 검증 테스트: 2시간
- **소계: 6시간**

### 4단계: 파일 접근 권한 검증
- 파일 업로드/다운로드 로직 검토: 2시간
- Path Traversal 테스트: 2시간
- **소계: 4시간**

### 5단계: 세션 및 인증 검증
- 세션 관리 로직 검토: 2시간
- 세션 공격 테스트: 2시간
- **소계: 4시간**

### 테스트 계정 생성 및 정리
- 테스트 계정 생성: 1시간
- 테스트 데이터 준비: 1시간
- **소계: 2시간**

### **총 예상 소요 시간: 35시간 (약 5일)**

---

## 산출물

### 1. 권한 검증 취약점 리포트
**파일명**: `SECURITY_ACCESS_CONTROL_VULNERABILITIES_REPORT.md`

**내용**:
- 발견된 취약점 목록 (심각도별 분류)
- 각 취약점의 상세 설명
- 재현 방법 (PoC - Proof of Concept)
- 예상 피해 범위
- 수정 권장사항

**예시**:
```markdown
## 발견된 취약점

### [CRITICAL] 관리자 페이지 권한 검증 누락
**위치**: `/src/controllers/AdminController.php`
**라인**: 25
**취약점**: AuthMiddleware 미적용으로 일반 사용자가 관리자 페이지 접근 가능

**재현 방법**:
1. 일반 사용자로 로그인 (user_a@example.com)
2. https://www.topmktx.com/admin/users 접근
3. 결과: HTTP 200 OK (관리자 페이지 접근 성공!)

**예상 피해**:
- 모든 사용자 개인정보 노출
- 사용자 계정 삭제 가능
- 강의/행사 데이터 조작 가능

**수정 방법**:
`$router->get('/admin/users', 'AdminController@index', [AuthMiddleware::class]);`
```

---

### 2. 수정된 코드 목록
**파일명**: `SECURITY_ACCESS_CONTROL_FIXES.md`

**내용**:
- 수정된 파일 목록
- Before/After 코드 비교
- 수정 사항 설명

---

### 3. 권한 검증 테스트 체크리스트
**파일명**: `SECURITY_ACCESS_CONTROL_CHECKLIST.xlsx`

**내용**:
- 페이지별 권한 검증 체크리스트
- API별 권한 검증 체크리스트
- 테스트 결과 (통과/실패)

---

### 4. 보안 강화 가이드
**파일명**: `SECURITY_ACCESS_CONTROL_BEST_PRACTICES.md`

**내용**:
- 권한 검증 모범 사례
- 코드 예시 (Good vs Bad)
- 개발자 교육 자료

---

## 다음 단계

### 1. 계획 검토 및 승인
- [ ] 사용자 검토
- [ ] 계획 수정
- [ ] 최종 승인

### 2. 테스트 환경 준비
- [ ] 테스트 계정 생성
- [ ] 테스트 데이터 준비
- [ ] 백업 생성

### 3. 권한 검증 실행
- [ ] 1단계: 수직적 권한 상승 검증
- [ ] 2단계: 수평적 권한 상승 검증
- [ ] 3단계: API 엔드포인트 권한 검증
- [ ] 4단계: 파일 접근 권한 검증
- [ ] 5단계: 세션 및 인증 검증

### 4. 취약점 수정
- [ ] 취약점 우선순위 결정 (심각도 기반)
- [ ] 코드 수정
- [ ] 재테스트
- [ ] QA 확인

### 5. 문서화 및 보고
- [ ] 취약점 리포트 작성
- [ ] 수정 내역 문서화
- [ ] 보안 강화 가이드 작성

---

## 주의사항

### ⚠️ 프로덕션 환경 테스트 금지
- 절대로 프로덕션 환경에서 직접 테스트하지 마세요!
- 테스트 환경 또는 개발 환경에서만 실행
- 프로덕션 DB 백업 후 복원하여 테스트

### ⚠️ 테스트 계정 관리
- 테스트 계정은 테스트 후 즉시 삭제
- 테스트 계정 비밀번호는 복잡하게 설정
- 테스트 데이터는 명확히 표시 (id 1000번대 사용)

### ⚠️ SQL Injection 금지
- 권한 검증 테스트 중 SQL Injection 공격 시도 금지
- 허가된 테스트 시나리오만 실행

### ⚠️ 로그 모니터링
- 테스트 중 에러 로그 지속 모니터링
- 예상치 못한 오류 발생 시 즉시 중단

---

**작성자**: Claude (Anthropic)
**최종 업데이트**: 2025-10-20
**버전**: v1.0
**상태**: 계획 단계 (미실행)
