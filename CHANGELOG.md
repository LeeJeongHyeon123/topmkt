# 변경 로그 (Changelog)

## [3.5.0] - 2025-07-17

### 🚀 76.8KB 파일 업로드 버그 완전 해결

#### 🐛 JavaScript 로드 순서 문제 해결
- **문제**: validateFileSize 함수가 정의되지 않아 대용량 파일 업로드 실패
- **해결**: upload-config.js.php include 위치를 `<head>` 섹션으로 최적화
- **영향**: 모든 뷰 파일에서 validateFileSize 함수 정상 작동 보장
- **결과**: 파일 크기 검증 로직 안정성 확보

#### ✅ 실제 대용량 파일 업로드 성공 검증
- **검증 완료**: 12MB+ 파일 업로드 성공 확인
- **제한 확인**: 30MB 제한 정상 작동 검증
- **상태**: 모든 업로드 기능 완전 정상화

#### 🎯 기술적 성과
- **완전한 버그 해결**: 대용량 파일 업로드 100% 정상 작동
- **안정성 확보**: 모든 뷰 파일에서 일관된 검증 로직
- **사용자 경험 개선**: 원활한 파일 업로드 프로세스

### ⚡ 프로필 페이지 성능 최적화 (99.8% 개선)

#### 🚀 서버 응답 시간 500배 개선
- **기존**: 10.5초 (심각한 성능 저하)
- **최적화 후**: 21ms (즉시 로딩 수준)
- **개선율**: 99.8% 성능 향상 달성

#### 🔧 N+1 쿼리 문제 해결
- **기존**: 7개 개별 쿼리 (비효율적)
- **최적화 후**: 3개 통합 쿼리
- **방법**: JOIN 쿼리 활용한 효율적 데이터 로딩
- **효과**: 데이터베이스 부하 57% 감소

#### 💾 대용량 데이터 캐시 시스템 구축
- **프로필 데이터 JSON 캐시 구현**
  - 사용자별 프로필 데이터 캐시 저장
  - 실시간 캐시 무효화 시스템
  - 메모리 사용량 최적화

#### 📊 데이터베이스 인덱스 최적화
- **핵심 컬럼 인덱스 생성**: 검색 성능 향상
- **쿼리 실행 계획 최적화**: 효율적 데이터 접근
- **복합 인덱스 활용**: 다중 조건 검색 최적화

#### 🎯 기술적 성과
- **극적인 성능 개선**: 10.5초 → 21ms (99.8% 개선)
- **사용자 경험 향상**: 즉시 로딩 수준의 빠른 응답
- **서버 리소스 효율성**: 최소한의 쿼리로 최대 성능

## [3.4.0] - 2025-07-14

### 🚀 이미지 업로드 시스템 30MB 확장 및 중앙화

#### 📤 업로드 시스템 완전 개편
- **중앙화된 설정 시스템**
  - `UploadConfig` 클래스 생성 (`/src/config/upload.php`)
  - 모든 업로드 제한을 30MB로 통일 (기존 2MB/5MB/10MB 산발적 제한 해결)
  - JavaScript/PHP 검증 로직 완전 동기화

- **전체 시스템 업데이트**
  - 13개 위치의 하드코딩된 제한 제거
  - 8개 컨트롤러 업데이트: EventController, LectureController, UserController, CorporateController 등
  - 5개 뷰 파일 JavaScript 검증 로직 통합

- **서버 설정 최적화**
  - PHP 설정: `upload_max_filesize` 30M, `post_max_size` 50M, `memory_limit` 256M
  - Apache 재시작 및 설정 적용 검증 완료

#### 🧪 포괄적 QA 테스트 시스템
- **테스트 도구 개발**
  - CLI 테스트: `test-config-cli.php`
  - 웹 테스트: `test-upload-config.php`, `test-javascript-config.php`
  - 직접 업로드 시뮬레이션: `test-direct-upload.php`

- **검증 결과**
  - 25MB 파일 업로드 성공 검증
  - 31MB 파일 정상 거부 검증
  - 모든 확장자 및 MIME 타입 검증 통과

#### 📋 완전 문서화
- **QA 리포트 생성**: `UPLOAD_SYSTEM_QA_REPORT.md`
  - 프로젝트 개요 및 달성 결과 상세 기록
  - 영향받은 파일 통계: 21개 파일 (8개 컨트롤러, 5개 뷰, 2개 설정, 4개 테스트)
  - 운영 가이드 및 향후 용량 변경 방법 제공

#### 🎯 기술적 성과
- **원클릭 용량 변경**: 한 곳에서 설정 변경 시 전체 시스템 반영
- **Zero Regression**: 기존 기능 영향 없음
- **100% 호환성**: 모든 업로드 기능 정상 작동
- **Future Proof**: 확장성 있는 아키텍처 구현

## [3.1.0] - 2025-07-01

### 🎯 주요 기능 추가

#### 📊 강의/행사 신청 관리 시스템
- **신청 관리 대시보드** (`/registrations`)
  - 강의별 신청 현황 통계 (전체/대기/승인/거절)
  - 최근 1개월 기준 강의 목록 표시
  - 날짜 필터 기능 (시작일/종료일 선택)
  - 인원수 천 단위 콤마 표시
  - 반응형 모바일 친화적 디자인

- **드롭다운 신청 관리 메뉴**
  - 헤더 사용자 드롭다운에 "신청 관리" 메뉴 추가
  - 모든 로그인된 사용자에게 접근 권한 제공
  - 역할 기반 메뉴 표시 최적화

#### 🗂️ 신청 관리 컨트롤러 시스템
- **RegistrationController**: 강의 신청 API
  - 신청 상태 조회 (`GET /api/lectures/{id}/registration-status`)
  - 신청 등록 (`POST /api/lectures/{id}/registration`)
  - 신청 취소 (`DELETE /api/lectures/{id}/registration`)
  - CSRF 토큰 보안 검증

- **RegistrationDashboardController**: 관리 대시보드
  - 대시보드 메인 페이지 (`GET /registrations`)
  - 강의별 신청자 상세 관리 (`GET /registrations/lectures/{id}`)
  - 신청 상태 변경 API (`POST /api/registrations/{id}/status`)

#### 📧 이메일 알림 시스템
- **EmailService 클래스**: 완전한 이메일 발송 시스템
  - 신청 승인 알림 (HTML 템플릿)
  - 신청 거절 알림 (사유 포함)
  - 신청 확인 알림 (접수 완료)
  - 전문적인 HTML 이메일 디자인

### 🔧 시스템 개선

#### 데이터베이스 최적화
- **날짜 기반 필터링**: 행사 시작일 기준 조회
- **집계 쿼리 최적화**: registration_statistics 테이블 없이 직접 집계
- **권한 시스템 확장**: ROLE_USER, ROLE_CORP, GENERAL 등 다양한 역할 지원

#### UI/UX 개선
- **색상 일관성**: 강의 카드 헤더 텍스트 흰색 처리
- **날짜 필터 UI**: 직관적인 시작일/종료일 선택
- **필터 버튼**: "필터 적용", "초기화" 기능
- **불필요한 UI 제거**: "📊 모든 강의 보기" 버튼 및 이모지 제거

### 🐛 긴급 시스템 복구

#### 강의 페이지 시스템 오류 해결
- **다중 디버깅 도구 생성**: 
  - `emergency_fix_lectures.php`: 종합 진단
  - `ultra_debug.php`: 완전한 디버깅 콘솔
  - `debug_fixed.php`: JavaScript 오류 수정
- **JavaScript 함수 정의 오류 해결**: switchTab 등 함수 순서 문제 수정
- **BaseController 누락 해결**: 500 에러 원인 제거

#### 스타일링 문제 해결
- **다크 모드 이슈**: PC/모바일 모든 화면에서 흰색 배경 일관성 유지
- **강의 색상 시스템 복원**: 오프라인/온라인 강의 색상 구분 복구
- **CSS 미디어 쿼리 최적화**: 반응형 디자인 개선

### 🔐 보안 강화
- **CSRF 토큰 검증**: 모든 신청 관리 API에 보안 검증
- **권한 체크 강화**: 사용자별 접근 권한 세밀 조정
- **SQL 인젝션 방지**: Prepared Statement 사용

### 📁 새로 추가된 파일
- `src/controllers/RegistrationController.php`: 신청 API 컨트롤러
- `src/controllers/RegistrationDashboardController.php`: 대시보드 컨트롤러
- `src/controllers/BaseController.php`: 기본 컨트롤러 클래스
- `src/services/EmailService.php`: 이메일 발송 서비스
- `src/views/registrations/dashboard.php`: 대시보드 UI
- `src/views/registrations/lecture-detail.php`: 상세 관리 UI
- `public/debug_dropdown_menu.php`: 드롭다운 메뉴 디버깅 도구

### 🚀 성능 최적화
- **10개 제한 해제**: 모든 강의 표시로 사용성 개선
- **무한 스크롤 준비**: 향후 확장을 위한 기반 구축
- **AJAX 기반 필터링**: 페이지 새로고침 없는 빠른 필터링
- **URL 파라미터 유지**: 필터 상태 브라우저 히스토리 보존

### 🛠️ 라우팅 시스템 확장
```php
'GET:/registrations' => ['RegistrationDashboardController', 'index'],
'GET:/registrations/lectures/{id}' => ['RegistrationDashboardController', 'lectureRegistrations'],
'GET:/api/lectures/{id}/registration-status' => ['RegistrationController', 'getRegistrationStatus'],
'POST:/api/lectures/{id}/registration' => ['RegistrationController', 'createRegistration'],
'DELETE:/api/lectures/{id}/registration' => ['RegistrationController', 'cancelRegistration'],
'POST:/api/registrations/{id}/status' => ['RegistrationDashboardController', 'updateRegistrationStatus'],
```

---

## [3.0.0] - 2025-06-16

### 🎯 주요 기능 추가

#### 📋 관리자 기업회원 관리 시스템
- **기업인증 대기 목록 페이지** (`/admin/corporate/pending`)
  - 승인 대기 중인 기업인증 신청 목록
  - 신청 상세보기 모달 (기업 정보, 사업자등록증 등)
  - 사업자등록증 문서 뷰어 (PDF, 이미지 지원)
  - 실시간 검색 및 필터링 기능
  - 승인/거절 처리 시스템
  - SMS 알림 자동 발송 (90바이트 이하 최적화)

- **기업회원 목록 페이지** (`/admin/corporate/list`)
  - 승인/거절/일시정지된 기업회원 통합 관리
  - 상세보기 모달 (활동 현황, 처리 이력 포함)
  - 기업회원 관리 모달 (3개 탭: 상태/메모/연락처)
  - 상태별, 활동별, 기간별 필터링
  - 5개 통계 카드 (승인/거절/일시정지/활성/콘텐츠)

#### 🔧 기업회원 관리 기능
- **상태 관리**: 승인 ↔ 일시정지 양방향 변경 가능
- **관리자 메모**: 기업별 관리자 전용 메모 (승인/거절 사유와 분리)
- **연락처 관리**: 대표자명, 연락처 실시간 수정
- **처리 이력**: 모든 상태 변경 이력 추적 및 표시
- **SMS 알림**: 상태 변경 시 자동 SMS 발송

### 📊 데이터베이스 변경사항

#### 새로운 상태 추가
- `company_profiles.status`: `suspended` 상태 추가
- `users.corp_status`: `suspended` 상태 추가
- `company_application_history.action_type`: `reapprove`, `suspend` 추가

#### 새로운 컬럼 추가
- `company_profiles.admin_memo`: 관리자 전용 메모 컬럼

### 🎨 UI/UX 개선

#### 관리자 페이지 완전 리뉴얼
- **탭 기반 관리 모달**: 기능별 탭으로 직관적 관리
- **처리 이력 시각화**: 액션별 이모지와 명확한 표시
- **상태별 색상 구분**: 승인(녹색), 거절(빨간색), 일시정지(주황색)
- **숫자 포맷팅**: 모든 통계 숫자에 천 단위 콤마 표시
- **반응형 디자인**: 1920px 기준 최적화, 다양한 화면 지원

#### 기능 개선
- **개행 처리**: 관리자 메모와 처리 이력에서 줄바꿈 정상 표시
- **실시간 필터링**: JavaScript 기반 즉시 필터링
- **모달 최적화**: 스크롤 가능한 모달로 긴 내용 지원

### 🔐 보안 강화
- **CSRF 토큰**: 모든 관리 작업에 CSRF 보호
- **권한 체크**: 관리자 권한 검증 및 활동 로깅
- **파일 보안**: 문서 뷰어 경로 조작 방지
- **에러 처리**: 상세한 에러 로깅 및 사용자 친화적 메시지

### 📱 SMS 알림 시스템
- **승인**: "기업인증 승인완료! 강의등록 가능합니다."
- **거절**: "기업인증이 거절되었습니다. 재신청 가능합니다."
- **재승인**: "기업인증이 재승인되었습니다. 서비스를 계속 이용하실 수 있습니다."
- **일시정지**: "기업회원 서비스가 일시정지되었습니다."

### 🛠️ 기술적 개선

#### 새로운 라우트
- `POST:/admin/corporate/process`: 승인/거절 처리
- `POST:/admin/corporate/detail`: 신청 상세보기
- `GET:/admin/document/view`: 사업자등록증 뷰어
- `POST:/admin/corporate/manage`: 기업회원 관리

#### 코드 구조
- **AdminController 확장**: 564라인 추가로 완전한 관리 시스템
- **MVC 패턴**: 컨트롤러-뷰 분리 및 모듈화
- **트랜잭션 처리**: 데이터 일관성 보장
- **SmsHelper 클래스화**: 기존 함수에서 클래스로 확장

### 📁 추가된 파일
- `src/views/admin/corporate/pending.php`: 인증 대기 목록 (1,200라인)
- `src/views/admin/corporate/list.php`: 기업회원 목록 (1,600라인)
- `add_suspended_status.sql`: 일시정지 상태 추가 SQL
- `add_admin_memo_column.sql`: 관리자 메모 컬럼 추가 SQL
- `restore_rejected_applications.sql`: 거절 신청 복원 SQL
- `reset_approved_to_pending.sql`: 승인 → 대기 변경 SQL

### 🚀 성능 최적화
- **숫자 포맷팅**: 모든 카운트에 `number_format()` 적용
- **AJAX 기반**: 페이지 새로고침 없는 실시간 처리
- **필터링 최적화**: 클라이언트 사이드 즉시 필터링

---

## [2.2.0] - 2025-01-11

### ✨ 신규 기능 (Added)
- **프로필 이미지 지연 로딩**: 커뮤니티 페이지 성능 최적화를 위한 지연 로딩 구현
- **프로필 이미지 API**: `/api/users/{id}/profile-image` 엔드포인트 추가
- **대용량 프로필 이미지 모달**: 95% 화면 크기 활용한 대형 이미지 뷰어

### 🔧 개선 사항 (Improved)
- **커뮤니티 페이지 성능**: 0.025초(25ms) 로딩 시간으로 대폭 개선
- **데이터베이스 쿼리 최적화**: 불필요한 프로필 이미지 필드 제거로 쿼리 경량화
- **프로필 이미지 모달 크기**: 실제 이미지 크기에 따른 자연스러운 표시
- **UI/UX 개선**: 게시글 상세 페이지 레이아웃 간소화

### 🎨 디자인 개선 (Design)
- **author-info 섹션 제거**: 별도 작성자 정보 영역 제거로 깔끔한 레이아웃
- **헤더 통합 프로필**: 게시글 헤더에 프로필 이미지 통합하여 일반적인 블로그 형태로 개선
- **색상 통일성**: 작성자 정보 영역 배경색을 흰색 계열로 통일

### 🚀 성능 최적화 (Performance)
- **프로필 이미지 최적화**: 목록에서는 썸네일만 로딩, 클릭 시 원본 이미지 AJAX 로딩
- **Post 모델 경량화**: 4개 주요 쿼리에서 원본 이미지 필드 제거
- **캐시 최적화**: 불필요한 이미지 데이터 사전 로딩 방지

### 🛠️ 기술적 개선 (Technical)
- **User 모델**: `getProfileImageInfo()` 메서드 추가
- **UserController**: `getProfileImage()` API 메서드 추가
- **지연 로딩 시스템**: JavaScript 기반 AJAX 이미지 로딩 구현
- **모달 시스템**: 로딩 스피너 및 오류 처리 개선

## [2.1.0] - 2025-01-03

### ✨ 신규 기능 (Added)
- **프로필 공유 시스템**: Web Share API를 활용한 네이티브 공유 기능
- **실제 좋아요 수 계산**: 프로필 페이지에서 사용자가 받은 실제 좋아요 수 표시
- **한국어 URL 지원**: 한글 닉네임을 포함한 프로필 URL 정상 처리
- **커스텀 로딩 UI**: 단계별 로딩 애니메이션 시스템 구축
- **향상된 SMS 인증**: Aligo API를 통한 안정적인 SMS 인증 시스템

### 🔧 개선 사항 (Improved)
- **JavaScript 오류 수정**: 회원가입 페이지의 TopMarketingLoading 함수 오류 해결
- **프로필 UI 통일**: 프로필 편집 버튼과 공유 버튼의 디자인 일관성 확보
- **로딩 시스템 안정화**: `custom()` 및 `setStage()` 함수 추가로 로딩 경험 개선
- **CSS 최적화**: 프로필 페이지 stat-item 폰트 크기 조정 (1.3rem → 1.2rem)

### 🐛 버그 수정 (Fixed)
- **한국어 프로필 URL 500 오류**: URL 디코딩 문제로 인한 서버 오류 해결
- **JavaScript 구문 오류**: 잘못된 PHP 변수 인코딩으로 인한 JSON 파싱 오류 수정
- **프로필 공유 URL**: 자신의 프로필 공유 시 공개 URL로 변경
- **좋아요 수 표시**: 프로필에서 하드코딩된 0 대신 실제 좋아요 수 계산

### 🔐 보안 강화 (Security)
- **reCAPTCHA v3 통합**: 회원가입 및 SMS 발송 시 보안 검증 강화
- **세션 보안**: 사용자 세션 관리 및 보안 강화
- **입력 검증**: 사용자 입력 데이터 검증 및 새니타이징

### 📱 사용자 경험 (UX)
- **반응형 디자인**: 모바일/태블릿 환경 최적화
- **직관적인 UI**: 버튼 텍스트 및 아이콘 개선
- **실시간 피드백**: 로딩 상태 및 성공/오류 메시지 개선

## [2.0.0] - 2025-01-01

### ✨ 신규 기능 (Added)
- **커뮤니티 게시판**: 마케팅 정보 공유 플랫폼
- **실시간 댓글 시스템**: AJAX 기반 동적 댓글 기능
- **좋아요 시스템**: 게시글 및 댓글 좋아요 기능
- **검색 시스템**: 고성능 게시글 검색 기능
- **프로필 시스템**: 개인화된 사용자 프로필 페이지

### 🔧 개선 사항 (Improved)
- **데이터베이스 최적화**: 인덱싱 및 쿼리 성능 개선
- **캐싱 시스템**: Redis 기반 성능 최적화
- **UI/UX 개선**: 현대적이고 직관적인 사용자 인터페이스

### 🐛 버그 수정 (Fixed)
- **초기 설정 오류**: 데이터베이스 연결 및 설정 문제 해결
- **권한 관리**: 사용자 권한 및 접근 제어 시스템 구축

## [1.0.0] - 2024-12-01

### ✨ 신규 기능 (Added)
- **사용자 인증 시스템**: 회원가입, 로그인, 로그아웃
- **SMS 인증**: 휴대폰 번호 인증 시스템
- **기본 프로필**: 사용자 기본 정보 관리
- **GitHub Actions**: CI/CD 파이프라인 구축

### 🏗️ 인프라 (Infrastructure)
- **서버 환경**: Apache + PHP 8.0.30 + MariaDB 10.6.5
- **보안 설정**: SSL 인증서 및 기본 보안 구성
- **모니터링**: 기본 로그 시스템 구축

---

## 버전 규칙 (Versioning)

이 프로젝트는 [Semantic Versioning](https://semver.org/)을 따릅니다:
- **MAJOR** (주 버전): 호환되지 않는 API 변경
- **MINOR** (부 버전): 이전 버전과 호환되는 기능 추가
- **PATCH** (패치 버전): 이전 버전과 호환되는 버그 수정

## 기여 가이드

프로젝트에 기여하시려면:
1. 이슈를 먼저 등록해주세요
2. Feature branch를 생성해주세요
3. 변경사항을 커밋해주세요
4. Pull Request를 생성해주세요

## 연락처

문의사항이나 제안사항이 있으시면:
- **이메일**: jh@wincard.kr
- **GitHub Issues**: https://github.com/LeeJeongHyeon123/topmkt/issues 