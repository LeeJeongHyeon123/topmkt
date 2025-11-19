# 프로필 이미지 클릭 버그 수정 플랜

**작성일**: 2025-11-17 15:00 KST
**우선순위**: 중간
**예상 시간**: 30분
**담당**: Claude

---

## 🎯 목표

프로필 이미지를 설정하지 않은 사용자(기본 아바타 사용자)의 프로필 이미지 클릭 시에도 프로필 페이지로 정상 이동되도록 수정

## 📋 배경

### 현재 문제

**증상**:
- 프로필 이미지 설정 사용자: ✅ 클릭 → 프로필 페이지 이동
- 기본 아바타 사용자: ❌ 클릭 → 이동 안 됨
- 영향 범위: 커뮤니티, 채팅, 공지사항 등 전체

**사용자 신고**:
```
https://www.topmktx.com/community/posts/1000043
프로필 이미지를 설정하지 않은 유저의 프로필 이미지를 클릭하면
프로필 페이지로 이동되지 않음
```

### Ultra Think 7단계 분석 결과

#### 1단계: 문제 정의
- 기본 아바타 사용자의 프로필 접근성 저하
- 프로필 이미지 유무와 프로필 페이지 접근은 별개여야 함

#### 2단계: 데이터 수집 (에이전트 조사)
- ProfileImageHelper.php Line 138의 조건문 확인
- profile-image.php 컴포넌트 동작 확인
- community/detail.php, chat/index.php 사용 패턴 확인

#### 3단계: 근본 원인
**파일**: `/src/helpers/ProfileImageHelper.php`
**위치**: Line 138

```php
// 🐛 문제 코드
if ($userId && !empty($user['nickname'])) {
    $attributes['onclick'] = "...프로필 페이지 이동...";
}
```

**원인**:
- `!empty($user['nickname'])` 조건이 너무 엄격
- userId가 있어도 nickname이 NULL이거나 빈 문자열이면 onclick 미설정
- 채팅 페이지에서 동적 생성 시 nickname 필드 누락 가능
- `$userName` 변수는 이미 기본값 '사용자' 처리됨 (Lines 117-120)

#### 4단계: 해결 전략
nickname 체크 제거, **userId만으로 프로필 페이지 이동 허용**

#### 5단계: 구현 계획
ProfileImageHelper.php Line 138 조건 변경

#### 6단계: 검증 방법
5가지 테스트 시나리오 수동 검증

#### 7단계: 문서화
PLAN.md → TASK.md → 개발노트 → 초기화

---

## 🔧 구현 단계

### 1단계: ProfileImageHelper.php 조건 수정 ✅

**파일**: `/src/helpers/ProfileImageHelper.php`
**위치**: Line 138
**예상 소요**: 5분

**현재 코드**:
```php
// 유효한 사용자 ID가 있을 때만 클릭 이벤트 추가
if ($userId && !empty($user['nickname'])) {  // ← 🐛 문제: nickname 체크
    // v3.98.0: 프로필 이미지 클릭 → 프로필 페이지로 이동 (통일)
    $attributes['onclick'] = "event.stopPropagation(); if(window.TopMarketingLoading) { window.TopMarketingLoading.show(); window.TopMarketingLoading.setMessage('프로필을 불러오는 중...'); } window.location.href='/profile?user_id=" . $userId . "';";
    $attributes['title'] = htmlspecialchars($userName) . '님의 프로필 보기';
    $attributes['style'] = 'cursor: pointer;';
}
```

**수정 후 코드**:
```php
// 유효한 사용자 ID가 있을 때만 클릭 이벤트 추가
if ($userId && is_numeric($userId) && $userId > 0) {  // ✅ 수정: userId만 체크
    // v3.98.0: 프로필 이미지 클릭 → 프로필 페이지로 이동 (통일)
    $attributes['onclick'] = "event.stopPropagation(); if(window.TopMarketingLoading) { window.TopMarketingLoading.show(); window.TopMarketingLoading.setMessage('프로필을 불러오는 중...'); } window.location.href='/profile?user_id=" . $userId . "';";
    $attributes['title'] = htmlspecialchars($userName) . '님의 프로필 보기';
    $attributes['style'] = 'cursor: pointer;';
}
```

**변경 사항**:
```diff
- if ($userId && !empty($user['nickname'])) {
+ if ($userId && is_numeric($userId) && $userId > 0) {
```

**근거**:
1. `$userName`은 이미 Lines 117-120에서 기본값 '사용자' 처리
2. onclick 이벤트는 userId만으로 프로필 페이지 이동 가능
3. `is_numeric()` + `> 0` 체크로 유효한 사용자만 필터링
4. nickname 유무와 프로필 페이지 접근 권한은 별개

**완료 조건**:
- [ ] Line 138 조건문 수정
- [ ] 주석 추가 (수정 이유 명시)

---

### 2단계: 문법 오류 확인 ✅

**명령어**:
```bash
php -l /var/www/html/topmkt/src/helpers/ProfileImageHelper.php
```

**예상 결과**:
```
No syntax errors detected in ProfileImageHelper.php
```

**완료 조건**:
- [ ] PHP 문법 오류 없음 확인

---

### 3단계: 테스트 시나리오 검증 ✅

**테스트 환경**: https://www.topmktx.com

#### 시나리오 1: 기본 아바타 사용자 클릭
**테스트**:
1. 커뮤니티 게시글에서 기본 아바타 사용자 찾기
2. 프로필 이미지 클릭
3. 프로필 페이지로 이동 확인

**예상 결과**: ✅ `/profile?user_id=XX`로 이동

#### 시나리오 2: 채팅 페이지 프로필 이미지
**테스트**:
1. 채팅 페이지 접속
2. 채팅방 목록에서 프로필 이미지 클릭
3. 프로필 페이지로 이동 확인

**예상 결과**: ✅ `/profile?user_id=XX`로 이동

#### 시나리오 3: 일반 사용자 정상 동작 유지
**테스트**:
1. 프로필 이미지 설정한 일반 사용자 프로필 이미지 클릭
2. 프로필 페이지로 이동 확인

**예상 결과**: ✅ 기존과 동일하게 정상 동작

#### 시나리오 4: 프로필 페이지 자체에서 모달 동작
**테스트**:
1. 자신의 프로필 페이지 접속
2. 프로필 이미지 클릭
3. 모달로 크게 보기 동작 확인

**예상 결과**: ✅ 모달 정상 동작 (keepModalOnOwnProfile)

#### 시나리오 5: 공지사항 페이지 프로필 이미지
**테스트**:
1. 공지사항 페이지에서 작성자 프로필 이미지 클릭
2. 프로필 페이지로 이동 확인

**예상 결과**: ✅ `/profile?user_id=XX`로 이동

**완료 조건**:
- [ ] 5가지 시나리오 모두 통과

---

## ✅ 최종 체크리스트

### 코드 수정
- [ ] ProfileImageHelper.php Line 138 조건 변경
- [ ] 주석 추가 (수정 이유)
- [ ] PHP 문법 오류 없음 확인

### 테스트
- [ ] 시나리오 1: 기본 아바타 클릭 정상 동작
- [ ] 시나리오 2: 채팅 페이지 정상 동작
- [ ] 시나리오 3: 일반 사용자 정상 동작
- [ ] 시나리오 4: 프로필 페이지 모달 정상 동작
- [ ] 시나리오 5: 공지사항 정상 동작

### 문서화
- [ ] Git 커밋 (버그 수정 내용 명시)
- [ ] 개발노트 기록 (근본 원인, 해결 방법)
- [ ] PLAN.md, TASK.md 초기화

---

## 📊 예상 결과

### Before Fix (수정 전)

| 사용자 타입 | 프로필 이미지 클릭 | 결과 |
|------------|-----------------|------|
| 일반 사용자 (이미지 O, nickname O) | ✅ 클릭 가능 | `/profile?user_id=XX` 이동 |
| 기본 아바타 (이미지 X, nickname O) | ❌ 클릭 불가 | onclick 없음 |
| nickname NULL (이미지 O/X) | ❌ 클릭 불가 | onclick 없음 |

### After Fix (수정 후)

| 사용자 타입 | 프로필 이미지 클릭 | 결과 |
|------------|-----------------|------|
| 일반 사용자 (이미지 O, nickname O) | ✅ 클릭 가능 | `/profile?user_id=XX` 이동 |
| 기본 아바타 (이미지 X, nickname O) | ✅ 클릭 가능 | `/profile?user_id=XX` 이동 |
| nickname NULL (이미지 O/X) | ✅ 클릭 가능 | `/profile?user_id=XX` 이동 |
| 모든 userId > 0 사용자 | ✅ 클릭 가능 | `/profile?user_id=XX` 이동 |

**개선 효과**:
- ✅ 기본 아바타 사용자 프로필 접근성 100% 향상
- ✅ nickname NULL 사용자도 정상 동작
- ✅ 채팅 페이지 동적 생성 프로필 이미지 정상 동작
- ✅ 기존 정상 사용자 동작 유지

---

## 📚 참고 문서

- `/var/www/html/topmkt/CLAUDE.md` - v3.98.0 프로필 이미지 클릭 동작 변경 이력
- `/src/helpers/ProfileImageHelper.php` - 프로필 이미지 헬퍼 클래스
- `/src/views/includes/profile-image.php` - 프로필 이미지 컴포넌트
- `/docs/23.컴포넌트_사용_가이드.md` - 컴포넌트 사용 가이드

---

## 🔗 관련 작업

- v3.98.0 - 프로필 이미지 클릭 동작 변경 (모달 → 프로필 페이지)
- v3.98.0 Phase 1+2 - 8개 페이지 일괄 수정

---

## ⚠️ 주의사항

1. **프로필 페이지 예외**: 자신의 프로필 페이지에서는 `keepModalOnOwnProfile` 플래그로 모달 동작 유지
2. **userId 검증**: `is_numeric()` + `> 0`으로 유효한 사용자만 필터링
3. **기존 동작 유지**: 일반 사용자의 정상 동작이 깨지지 않도록 주의
4. **회귀 테스트**: 커뮤니티, 채팅, 공지사항 모두 테스트 필수

---

**마지막 업데이트**: 2025-11-17 15:00
**상태**: 진행 중
