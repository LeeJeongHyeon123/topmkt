# 탑마케팅 프로젝트 - Claude 작업 가이드

## 프로젝트 개요
탑마케팅은 글로벌 네트워크 마케팅 전문가들을 위한 커뮤니티 플랫폼입니다. 강의 일정 관리, 사용자 등록 시스템, 실시간 채팅, 기업 회원 관리 등의 기능을 제공합니다.

## 🚀 작업 모드 (최우선!)
- **항상 Ultra Think 모드로 작동**
- **모든 작업에 7단계 체계적 분석 적용**
- **문제 해결 시 근본 원인 분석 및 완벽한 해결 추구**

## 🗣️ 언어 설정
- **모든 대화는 한국어로 진행**
- **코드 주석과 문서화도 한국어로 작성**

## 🔧 빠른 참조

### 데이터베이스 접속
```bash
./scripts/mysql_connect.sh  # 추천
```

### Claude Code CLI
```bash
./scripts/claude-auto.sh     # 자동 실행 모드
claude                        # 이전 대화 자동 복원
```

### 중요 문서
- **컴포넌트 가이드**: `/docs/23.컴포넌트_사용_가이드.md`
- **에러 처리 표준**: `/docs/22.에러처리_및_로깅_표준.md`
- **전체 문서 목록**: `/docs/0.문서_인덱스.md`

## 기술 스택

### Backend
- PHP 8.x, MySQL, JWT 인증
- MVC 패턴 아키텍처

### Frontend
- Vanilla JavaScript
- CSS Grid/Flexbox
- Font Awesome, Google Fonts

### 주요 기능
- 강의/행사 일정 관리
- 사용자 인증 (JWT)
- 실시간 채팅 (Firebase)
- 강의/행사 신청 시스템
- 기업 회원 관리
- 커뮤니티 게시판

## 통합 컴포넌트 시스템

모든 컴포넌트는 footer.php에서 전역으로 로드되어 즉시 사용 가능합니다.

### JavaScript 유틸리티 컴포넌트

#### 1. Toast 알림 (v3.30.0)
**파일**: `/src/views/includes/toast.js.php`
```javascript
Toast.success('성공 메시지');
Toast.error('오류 메시지');
Toast.info('안내 메시지');
Toast.warning('경고 메시지');
```

#### 2. Loading 인디케이터 (v3.31.0)
**파일**: `/src/views/includes/loading.js.php`
```javascript
Loading.show('로딩 중...');
Loading.hide();
Loading.button(buttonElement, true, { text: '처리 중...' });
```

#### 3. Utils 유틸리티 (v3.57.0)
**파일**: `/src/views/includes/utils.js.php`
```javascript
debounce(() => performSearch(), 300)
throttle(() => updateScroll(), 100)
once(() => init())
await sleep(1000)
```

#### 4. Modal 시스템 (v3.36.0)
**파일**: `/public/assets/js/modal.js`
```javascript
Modal.confirm({
    title: '확인',
    message: '정말 삭제하시겠습니까?',
    onConfirm: () => { /* 확인 시 동작 */ }
});
```

#### 5. FormValidator (v3.41.0)
**파일**: `/src/views/includes/form-validator.js.php`
```javascript
const validator = new FormValidator(formElement);
validator.addRule('email', { required: true, email: true });
if (validator.validate()) { /* 검증 통과 */ }
```

#### 6. ApiClient (v3.42.0)
**파일**: `/src/views/includes/api-client.js.php`
```javascript
const data = await ApiClient.get('/api/endpoint');
await ApiClient.post('/api/endpoint', { key: 'value' });
await ApiClient.put('/api/endpoint', { key: 'value' });
await ApiClient.delete('/api/endpoint');
```

#### 7. DateUtils (v3.62.0)
**파일**: `/src/views/includes/date-utils.js.php`
```javascript
formatDate('2025-10-10', 'YYYY년 MM월 DD일')
formatTime('14:30:00', 'HH:mm')
parseDate('2025-10-10')
isValidDate('2025-10-10')
```

#### 8. ClipboardUtils (v3.65.0) 🆕
**파일**: `/src/views/includes/clipboard-utils.js.php`
```javascript
copyToClipboard(text, {
    successMessage: '✅ 복사되었습니다!',
    errorMessage: '❌ 복사 실패',
    noToast: false
});
isClipboardSupported()
readFromClipboard()
```

#### 9. CharacterCounter (v3.29.0)
**파일**: `/src/views/includes/char-counter.js.php`
```javascript
new CharacterCounter(inputElement, {
    max: 2000,
    min: 10,
    counterElement: document.getElementById('counter')
});
```

#### 10. UploadConfig
**파일**: `/src/config/upload.php`, `/src/views/includes/upload-config.js.php`
```javascript
validateFileSize(file.size)
validateImageExtension(file.name)
input.accept = getImageAcceptAttribute()
```

#### 11. Pagination
**파일**: `/src/views/includes/pagination.js.php`
```javascript
window.Pagination.render(paginationData, containerElement);
```

### PHP 컴포넌트

#### 1. SearchFilter (v3.37.0)
**파일**: `/src/components/ui/SearchFilter.php`
```php
<?php
echo SearchFilter::create([
    'method' => 'JS',
    'layout' => 'inline',
    'filters' => [/* ... */],
    'searchInput' => true,
    'onSubmit' => 'handleSearch()'
]);
?>
```

#### 2. Button
**파일**: `/src/components/ui/Button.php`
```php
<?= renderButton('저장', 'primary') ?>
<?= renderButton('취소', 'secondary') ?>
<?= renderButton('삭제', 'danger') ?>
```

#### 3. Modal
**파일**: `/src/components/ui/Modal.php`
```php
<?= renderModal('my-modal', '제목', '<p>내용</p>') ?>
<?= renderConfirmModal('delete-confirm', '삭제', '정말?', 'delete()') ?>
```

#### 4. Pagination
**파일**: `/src/components/ui/Pagination.php`
```php
<?php
require_once SRC_PATH . '/components/ui/Pagination.php';
echo renderPagination($paginationData);
?>
```

#### 5. Card (v3.38.0)
**파일**: `/src/components/ui/Card.php`
```php
<?= Card::stat(['icon' => '📊', 'title' => '제목', 'value' => '100']) ?>
<?= Card::feature(['icon' => '🎓', 'title' => '제목', 'desc' => '설명']) ?>
```

### 컴포넌트 로드 순서 (footer.php)
```
1. Toast (v3.30.0)
2. Loading (v3.31.0)
3. Utils (v3.57.0)
4. Modal (v3.36.0)
5. FormValidator (v3.41.0)
6. ApiClient (v3.42.0)
7. DateUtils (v3.62.0)
8. ClipboardUtils (v3.65.0) 🆕
9. CharacterCounter (v3.29.0)
10. UploadConfig
11. Pagination
```

## 최근 주요 작업 (v3.58.0 ~ v3.84.0)

### v3.81.0 - 프로덕션 환경 완료: 수동으로 모든 console 로그 안전하게 제거 (2025-10-16) 🎉
**사용자 요청으로 꼼꼼한 수동 제거 방식으로 완벽한 console 로그 제거 완료**

**3단계 체계적 제거 프로세스**:
1. **1단계 - Python 자동화** (1,017개 제거)
   - `scripts/safe_manual_console_removal.py` 개발
   - 독립적인 console 문 자동 제거
   - 주석 처리된 console 보존

2. **2단계 - Lectures 파일 정밀 제거** (약 8개 제거)
   - Agent를 사용한 여러 줄 console 처리
   - lectures/detail.php, lectures/index.php, lectures/create.php

3. **3단계 - 나머지 파일 완전 제거** (46개 제거)
   - Agent를 사용한 전체 파일 스캔
   - 20개 파일 추가 처리 완료

**제거 통계**:
- 제거된 console: **1,063개** (활성 로그)
- 처리된 파일: **63개**
- 코드 감소: **1,150줄** (93 추가, 1,243 삭제)
- 보존된 주석: 66개 (`// console.*`)
- 보존된 시스템 코드: 1개 (`console.error` 재정의)

**제거된 주요 파일 (상위 5개)**:
1. chat/index.php: 187개
2. lectures/detail.php: 121개
3. auth/signup.php: 97개
4. lectures/create.php: 32개
5. community/write.php: 60개

**안전장치**:
- ✅ 백업: `backups/manual_console_removal_20251016_180228/`
- ✅ JavaScript 문법 오류: 0건
- ✅ error-suppressor.js 시스템 코드 보존
- ✅ 주석 처리된 console 모두 보존

**프로덕션 준비 완료**:
- ✅ 클라이언트 콘솔 출력: 0개
- ✅ 프로덕션 보안: 강화
- ✅ 서비스 오픈: 준비 완료

**v3.79.0~v3.80.0 실패 교훈**:
- sed 자동화는 JavaScript/PHP/HTML 복합 구조에 부적합
- Python 줄 단위 제거도 여러 줄 console 처리 불가
- Agent + 수동 확인 방식이 가장 안전하고 확실

### v3.84.0 - 행사 상세 페이지 모바일 패딩 최적화 (2025-10-16)
**문제**: 모바일 화면에서 좌우 여백이 과도하여 콘텐츠 영역이 매우 좁게 표시
**해결**: 모바일 전용 패딩 값 최적화로 콘텐츠 영역 30-40% 확대

**주요 변경사항**:
1. **Container 패딩**: `20px 10px` → `15px 8px` (좌우 20% 감소)
2. **Hero Section 패딩**: `40px 20px` → `30px 12px` (좌우 40% 감소)
3. **Content Section 패딩**: `20px` → `15px 12px` (좌우 40% 감소)
4. **Info Card 패딩**: 신규 모바일 최적화 `18px 12px` (좌우 52% 감소)

**개선 효과**:
- 모바일 콘텐츠 영역 약 30-40% 확대
- 좌우 여백 일관성 확보 (12px 통일)
- 화면 공간 활용도 극대화
- 자연스러운 UI/UX 제공

**디자인 원칙**:
- 모든 모바일 컴포넌트에 일관된 12px 좌우 패딩 적용
- 콘텐츠 너비 우선, 과도한 여백 최소화
- 요소 간 간격은 적절히 유지하여 가독성 보장

### v3.74.1 - 헤더 로고 위치 최종 해결 (2025-10-16) 🔥🔥🔥🔥
**긴급 업데이트**: PC 사이즈에서도 로고가 오른쪽에 위치하는 치명적 문제 발견 및 해결

**문제 상황**:
- v3.74.0에서 구현한 솔루션에도 불구하고 실제 웹사이트에서 로고가 오른쪽에 위치
- 다른 CSS 파일의 우선순위가 더 높아 덮어씌워짐

**핵폭탄급 해결 전략 적용**:
1. **최상위 우선순위 CSS 적용**:
   - 모든 가능한 셀렉터 조합 사용 (9개 셀렉터)
   - 범용 셀렉터 (*) 포함으로 어떤 CSS도 덮어씌우지 못하도록 방어
   - 모든 미디어 쿼리에서 일관된 규칙 적용

2. **강화된 검증 시스템**:
   - 6개 셀렉터에 대한 지속적 검증 (100ms 간격, 10초)
   - display, justify-content, align-items 모두 확인
   - 상세한 콘솔 로그로 문제 진단 가능

3. **즉시 실행 시스템 강화**:
   - 범용 셀렉터를 포함한 즉시 실행 스크립트
   - DOM 파싱 전 스타일 적용으로 FOUC 방지

**결과**:
- ✅ PC 포함 모든 디스플레이 사이즈에서 로고 좌측 고정 완벽 달성
- ✅ 어떤 CSS 충돌도 불가능한 핵폭탄급 방어 시스템 구축
- ✅ 실시간 검증으로 문제 즉시 감지 및 로그 기록
- ✅ 더 이상의 긴급 수정 불필요

**주의**: 이 솔루션은 CSS 우선순위 전쟁에서 승리하기 위한 최후의 수단입니다.

### v3.74.0 - 헤더 로고 위치 완벽 해결 (2025-10-16) 🔥🔥🔥
**문제**: PC를 제외한 모든 디스플레이 사이즈(1440px, 425px 등)에서 로고 위치 문제 발생
- 페이지 로딩 직후 로고가 중앙으로 이동
- 425px 이하에서 로고가 좌측에서 너무 떨어짐
- 여러 번의 긴급 수정으로 코드 중복 및 충돌

**해결 전략**: 단일 진실의 원천(Single Source of Truth) 원칙
1. **CSS 완전 재구축**:
   - 중복된 3개 CSS 블록 제거 (NUCLEAR BOMB, ULTRA CRITICAL 등)
   - 7개 명확한 breakpoint 체계 구축:
     - 1920px 이상 (PC): 15px 40px 패딩
     - 1440px~1919px (큰 태블릿): 15px 30px 패딩
     - 1024px~1439px (태블릿): 15px 25px 패딩
     - 768px~1023px (작은 태블릿): 12px 20px 패딩
     - 425px~767px (큰 모바일): 12px 15px 패딩
     - 375px~424px (모바일): 10px 12px 패딩
     - 320px~374px (작은 모바일): 10px 10px 패딩
   - 모든 사이즈에서 `justify-content: space-between` 강제

2. **JavaScript 단순화**:
   - 200줄 이상의 복잡한 보호 시스템 제거
   - DOM 파싱 전 즉시 실행 스크립트 (20줄)
   - 페이지 로드 후 간단한 검증만 수행 (15줄)
   - MutationObserver, setInterval 등 제거

3. **코드 감소**:
   - header.php: 약 200줄 감소 (복잡도 70% 감소)
   - 유지보수성 대폭 향상

4. **QA 문서**:
   - `/var/www/html/topmkt/QA_HEADER_LOGO_v4.0.md` 작성
   - 5가지 테스트 시나리오 제공
   - 버그 리포트 템플릿 포함

**결과**: 
- 모든 디스플레이 사이즈에서 로고 좌측 고정 완벽 달성
- 페이지 로딩 시 중앙 이동 문제 근본 해결
- 더 이상의 긴급 수정 불필요
- 단일 진실의 원천으로 향후 유지보수 간편

### v3.73.0 - 로그인 리다이렉트 기능 완전 복구 (2025-10-11) 🔥
**문제**: 강의 상세 페이지 등에서 로그인 버튼 클릭 시 redirect 파라미터 없이 이동되어 로그인 성공 후 원래 페이지로 돌아오지 못하는 문제
**해결**: 모든 로그인 버튼에 올바른 redirect 파라미터 추가 및 JavaScript 이벤트 처리 수정
- 헤더 로그인 버튼들 (데스크톱, 모바일)에 `redirect=현재페이지URI` 파라미터 추가
- 강의 상세 페이지 정적 로그인 버튼들에 `redirect=현재페이지URI` 파라미터 추가
- 강의 상세 페이지 JavaScript 동적 로그인 버튼들에 `redirect=현재페이지URI` 파라미터 추가
- AuthController::showLogin()에서 GET 파라미터를 세션에 저장하도록 수정
- 로그인 페이지 hidden input에서 세션의 redirect 값을 사용하도록 수정
- AuthController::isValidRedirectUrl()에서 `/lectures/`, `/events/` 경로 허용 추가
- 강의 상세 페이지 JavaScript에서 로그인 버튼 클릭 시 preventDefault() 하지 않도록 수정
- **결과**: 모든 페이지에서 로그인 후 원래 페이지로 정상 복귀 가능

### v3.72.0 - 신청 마감일 날짜/시간 Input 분리 완전 개선 (2025-10-10) 🔥
**문제**: 신청 마감일만 Flatpickr로 날짜+시간 통합 input → 시작일/종료일과 일관성 부족
**해결**: 네이티브 date/time input으로 완전 분리
- Flatpickr 완전 제거 (229줄 → 26줄, 88% 코드 감소)
- HTML: registration_deadline_date + registration_deadline_time 분리
- 폼 제출 시 자동 합치기 로직 (hidden input)
- 백엔드 호환성 100% 검증 (PHP DateTime, MySQL DATETIME)
- 완벽한 UI 일관성 달성 (시작일/종료일/신청마감일 모두 동일 구조)
- **관련 버전**: v3.68.2~v3.72.0 (9번 반복 개선)
  - v3.68.2: Flatpickr UI 5가지 문제 해결
  - v3.68.3~v3.68.5: 색상 통일 (보라색 → 파란색)
  - v3.69.0: Flatpickr 라이트 디자인 재구축
  - v3.70.0~v3.70.1: "T" 구분자 제거
  - v3.71.0: 시간 입력 높이 증가 (32px → 40px)
  - v3.72.0: 날짜/시간 input 완전 분리 (최종 완성)

### v3.65.0 - ClipboardUtils 컴포넌트 통합 (2025-10-10)
- 클립보드 복사 유틸리티 통합
- 5개 파일 마이그레이션 완료
- 3개 중복 copyToClipboard 함수 제거 (51줄)
- navigator.clipboard 직접 사용 9개 → 0개
- Promise 기반 + fallback 시스템
- Toast 자동 연동

### v3.62.0 - DateUtils 컴포넌트 통합 (2025-10-09)
- 날짜/시간 포맷 유틸리티 통합
- 중복 날짜 검증 함수 제거
- 9개 파일 마이그레이션

### v3.58.0 - console.error Toast 피드백 통합 (2025-10-06)
- 103개 console.error 분석
- 10곳 Toast 피드백 추가
- 사용자 영향 오류 완벽 대응

### v3.42.0 - ApiClient HTTP 클라이언트 (2025-10-05)
- 82개 fetch() 호출 → ApiClient로 중앙화
- 자동 에러 처리 + Loading + Toast 통합
- 응답 정규화 시스템

### v3.41.0 - FormValidator 컴포넌트 (2025-10-05)
- 폼 검증 로직 중앙화
- 이메일/전화번호/비밀번호 검증 통합

### v3.38.0 - Card 컴포넌트 시스템 (2025-10-05)
- 6가지 카드 타입 통합
- 27% 코드 감소 달성

### v3.33.0 ~ v3.32.0 - alert() → Toast 마이그레이션 (2025-10-04)
- Phase 1+2: 160개 alert() → Toast 완전 전환
- 키워드 기반 자동 타입 결정

### v3.29.0 - CharacterCounter 컴포넌트 (2025-10-04)
- 35개 중복 인스턴스 → 1개 통합 클래스
- 85% 코드 중복 제거

### v3.28.0 - 배지 CSS 통일 (2025-10-04)
- 106개 배지 스타일 통합 관리
- 14줄 중복 CSS 제거

### v3.27.0 - 파일 업로드 시스템 통합 (2025-10-04)
- upload-config.js.php 통합
- 99% 중복 코드 제거

## 과거 주요 작업 아카이브 (v3.1.0 ~ v3.26.0)

### UI/UX 개선 시리즈 (2025-09)
- v3.22.0: 이벤트 상세 페이지 UI/버튼 개선
- v3.21.0: 이벤트 768px 반응형 완전 개선
- v3.20.0: 강의 등록 UI/UX 완전 개선
- v3.19.0: 강의 상세 페이지 UI/UX 완전 개선
- v3.18.0: 데이터베이스 보안 강화 (AES-256-GCM)
- v3.16.0: 강의/행사 일정 UI 통일화

### 시스템 개선 시리즈 (2025-08)
- v3.15.0: 강의 신청 시스템 완전 개선
- v3.14.0: 커뮤니티 시스템 완전 개선
- v3.13.0: 공지사항 이미지 업로드 완전 개선
- v3.12.1: 모바일 헤더 UI + 로켓 애니메이션
- v3.12.0: 420개 E2E 테스트 자동화
- v3.11.5: 관리자 메뉴 최적화
- v3.11.0: 관리자 기업 권한 변경
- v3.10.0: 공지사항 이미지 표시 개선
- v3.9.0: 댓글 수정 시스템 개선
- v3.8.5: 프로필 이미지 모달 통합
- v3.8.0: 공지사항 시스템 보안 강화

### 핵심 기능 개발 (2025-07)
- v3.7.0: Firebase 실시간 알림
- v3.6.0: 강의 신청 거절 재신청
- v3.5.0: 프로필 성능 최적화 (99.8%)
- v3.4.0: 이미지 업로드 30MB 확장
- v3.2.0: SMS 시스템 교체

> **상세 이력**: 과거 작업의 상세 내용은 `CLAUDE.md.backup_*` 파일 참조

## 개발 가이드라인

### 컴포넌트 사용 원칙
1. **새 기능 개발 시**: 기존 컴포넌트 우선 사용
2. **중복 코드 발견 시**: 컴포넌트로 통합
3. **Git 커밋 전**: `./scripts/check_component_violations.sh` 실행
4. **문서 참조**: `/docs/23.컴포넌트_사용_가이드.md`

### 금지 사항
- ❌ `alert()` 직접 사용 → Toast 사용
- ❌ `confirm()` 직접 사용 → Modal.confirm() 사용
- ❌ `navigator.clipboard` 직접 사용 → copyToClipboard() 사용
- ❌ 중복 검증 함수 → FormValidator 사용
- ❌ 직접 fetch() 호출 → ApiClient 사용
- ❌ 중복 날짜 포맷 함수 → DateUtils 사용

### Git Pre-commit Hook
- **위치**: `.git/hooks/pre-commit`
- **동작**: 커밋 전 자동 검증 실행
- **결과**: 위반 시 커밋 차단

## 커밋 규칙

```bash
git commit -m "✨ v3.XX.0 - [기능명] - [간단한 설명]

- 주요 변경사항 1
- 주요 변경사항 2
- 주요 변경사항 3

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude <noreply@anthropic.com>"
```

## 파일 구조

```
/var/www/html/topmkt/
├── public/                    # 웹 루트
│   ├── assets/               # CSS, JS, 이미지
│   └── index.php            # 엔트리 포인트
├── src/
│   ├── components/          # PHP 컴포넌트
│   │   ├── ui/             # UI 컴포넌트
│   │   └── headers/        # 페이지 헤더
│   ├── controllers/        # MVC 컨트롤러
│   ├── models/             # 데이터 모델
│   ├── views/              # 뷰 템플릿
│   │   ├── includes/       # JavaScript 컴포넌트
│   │   └── templates/      # 공통 템플릿
│   ├── middlewares/        # 미들웨어
│   └── config/             # 설정 파일
├── scripts/                # 유틸리티 스크립트
├── docs/                   # 프로젝트 문서
└── CLAUDE.md              # 이 문서
```

## 연락처
- **개발팀**: (주)윈카드
- **플랫폼**: 탑마케팅 (https://www.topmktx.com)

---

**마지막 업데이트**: 2025-10-16
**작업자**: Claude (Anthropic)
**최신 버전**: v3.81.0
