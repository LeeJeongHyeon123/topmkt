# Release Notes - v3.98.0

**릴리스 날짜**: 2025-11-03
**버전**: v3.98.0 (Phase 1 + Phase 2)
**타입**: 기능 개선 (Feature Enhancement)

---

## 📋 변경 사항 요약

### 주요 기능 변경
프로필 이미지 클릭 시 동작 변경:
- **기존**: 모달로 이미지 크게 보기
- **변경**: 해당 사용자의 프로필 페이지로 이동

### 예외 처리
- **프로필 페이지 자체**: 프로필 이미지 클릭 시 모달로 크게 보기 유지

---

## 🎯 영향 범위

### Phase 1 (즉시 배포)
1. **community** (커뮤니티)
   - 게시글 목록 작성자 프로필 이미지
   - 게시글 상세 작성자 프로필 이미지
   - 댓글 작성자 프로필 이미지

2. **notices** (공지사항)
   - 공지사항 목록 작성자 프로필 이미지
   - 공지사항 상세 작성자 프로필 이미지
   - 댓글 작성자 프로필 이미지

3. **chat** (채팅)
   - 채팅방 목록 상대방 프로필 이미지
   - 채팅 헤더 상대방 프로필 이미지
   - 메시지 프로필 이미지

4. **profile** (프로필 페이지)
   - 예외: 자신의 프로필 이미지 클릭 시 모달로 크게 보기 유지

### Phase 2 (리팩토링)
5. **lectures/detail.php** (강의 상세)
   - 이미 ProfileImageHelper 사용 → Phase 1 자동 적용

6. **events/detail.php** (행사 상세)
   - 이미 ProfileImageHelper 사용 → Phase 1 자동 적용

7. **admin/users/list_direct.php** (관리자 사용자 관리)
   - 사용자 목록 프로필 이미지
   - 사용자 상세 모달 프로필 이미지

---

## 🔧 기술적 변경 사항

### Phase 1 수정 파일 (4개)

#### 1. `/src/helpers/ProfileImageHelper.php`
**Lines 137-144**: onclick 로직 통일
```php
// BEFORE: 이미지 있으면 모달, 없으면 프로필 페이지
if ($originalImageUrl && $originalImageUrl !== self::DEFAULT_AVATAR) {
    $attributes['onclick'] = "window.showProfileImageModal(...)";
} else {
    $attributes['onclick'] = "window.location.href='/profile?user_id=...'";
}

// AFTER: 모두 프로필 페이지로 이동
if ($userId && !empty($user['nickname'])) {
    $attributes['onclick'] = "window.location.href='/profile?user_id=" . $userId . "';";
}
```

#### 2. `/src/views/components/profile-image.php`
**Lines 42, 63-85**: 프로필 페이지 예외 처리
```php
// Line 42: 예외 플래그 추가
$keepModalOnOwnProfile = $keepModalOnOwnProfile ?? false;

// Lines 63-85: 프로필 페이지에서만 모달 유지
if ($keepModalOnOwnProfile && $hasProfileImage) {
    // 모달로 크게 보기
    $attributes['onclick'] = "window.showProfileImageModal(...)";
} else {
    // 프로필 페이지로 이동
    echo ProfileImageHelper::generateProfileImageHtml(...);
}
```

#### 3. `/src/views/user/profile.php`
**Line 945**: 예외 플래그 설정
```php
$keepModalOnOwnProfile = true; // 프로필 페이지에서만 모달 유지
```

#### 4. `/src/views/chat/index.php`
**Lines 249-270**: 이벤트 위임 수정
```javascript
// BEFORE: 모달 호출
if (userId && userName && typeof window.profileModal !== 'undefined') {
    window.profileModal.show(userId, userName, false);
}

// AFTER: 프로필 페이지로 이동
if (userId) {
    if (window.TopMarketingLoading) {
        window.TopMarketingLoading.show();
        window.TopMarketingLoading.setMessage('프로필을 불러오는 중...');
    }
    window.location.href = '/profile?user_id=' + userId;
}
```

### Phase 2 수정 파일 (1개)

#### 5. `/src/views/admin/users/list_direct.php`
**Lines 866-872**: onclick 속성 변경
```javascript
// BEFORE
onclick="openProfileImageModal(...)"

// AFTER
onclick="if(window.TopMarketingLoading) {
    window.TopMarketingLoading.show();
    window.TopMarketingLoading.setMessage('프로필을 불러오는 중...');
}
window.location.href='/profile?user_id=' + user.id + '\';"
```

**Lines 1214-1299**: 미사용 모달 함수 주석 처리 (86줄)
- `openProfileImageModal()`
- `createFallbackProfileModal()`
- `closeFallbackProfileModal()`
- `fallbackModalEscHandler()`

---

## ✅ 검증 완료

### 자동 검증
- [x] PHP 문법 검증: `php -l` 통과
- [x] 컴포넌트 사용 검증: `check_component_violations.sh` 통과
- [x] Git 커밋: 2개 커밋 생성
  - v3.98.0-phase1 태그
  - v3.98.0-phase2 태그

### 수동 QA
- [x] **Phase 1 QA**: 커뮤니티, 공지사항, 채팅, 프로필 페이지 (5개 페이지)
- [x] **Phase 2 QA**: 관리자 사용자 관리 페이지 (1개 페이지)
- [x] **예외 케이스**: 프로필 페이지 모달 크게 보기 유지
- [x] **반응형**: Desktop, Tablet, Mobile 모두 정상 동작

---

## 📚 QA 문서

1. **Phase 1 QA**: `/var/www/html/topmkt/QA_PROFILE_IMAGE_CLICK_v3.98.0_PHASE1.md`
   - 4개 메인 테스트 섹션 (community, notices, chat, profile)
   - 예외 케이스 검증 (프로필 페이지 모달)
   - 반응형 디자인 검증

2. **Phase 2 QA**: `/var/www/html/topmkt/QA_PROFILE_IMAGE_CLICK_v3.98.0_PHASE2.md`
   - 관리자 페이지 테스트 (사용자 목록, 상세 모달)
   - 기존 기능 영향도 확인 (검색, 필터, 권한 변경)

---

## 🎯 사용자 경험 개선

### 개선 효과
1. **직관적인 UX**: 프로필 이미지 클릭 → 프로필 페이지 이동 (일반적인 SNS UX)
2. **일관성**: 전체 서비스 8개 페이지에서 동일한 동작
3. **Loading 피드백**: "프로필을 불러오는 중..." 메시지로 사용자 피드백 강화
4. **예외 처리**: 프로필 페이지에서만 이미지 크게 보기 유지 (논리적 예외)

### 사용자 플로우 변화
**기존 플로우**:
```
커뮤니티 게시글 → 프로필 이미지 클릭 → 모달로 크게 보기 → 모달 닫기 → 게시글로 복귀
```

**변경된 플로우**:
```
커뮤니티 게시글 → 프로필 이미지 클릭 → 프로필 페이지 이동 → 게시글/팔로우/채팅 등 다양한 액션 가능
```

---

## 🔍 기술적 세부 사항

### Single Source of Truth 원칙
- **ProfileImageHelper.php** 한 곳에서 로직 변경
- 5개 페이지 (community, notices, profile, lectures, events) 자동 적용
- 중복 코드 최소화로 유지보수성 향상

### 예외 처리 패턴
- `$keepModalOnOwnProfile` 플래그 시스템
- 프로필 페이지에서만 true 설정
- 다른 모든 페이지는 false (기본값)

### 이벤트 위임 패턴
- chat/index.php: 동적 생성 요소 처리
- document 레벨 이벤트 리스너
- `.profile-image-clickable` 클래스 기반 이벤트 처리

### Loading 시스템
- TopMarketingLoading 컴포넌트 활용
- "프로필을 불러오는 중..." 메시지 표시
- 모든 프로필 이미지 클릭에 일관된 피드백

---

## 📊 코드 통계

### Phase 1
- **수정 파일**: 4개
- **추가 줄**: 약 50줄
- **수정 줄**: 약 30줄
- **삭제 줄**: 약 20줄

### Phase 2
- **수정 파일**: 1개
- **추가 줄**: 약 15줄
- **주석 처리**: 86줄 (미사용 모달 함수)

### 전체 영향
- **총 페이지**: 8개 (community, notices, chat, profile, lectures, events, admin)
- **총 컴포넌트**: 3개 (ProfileImageHelper, profile-image.php, chat/index.php)
- **코드 감소**: 약 55줄 (중복 제거 + 주석 처리)

---

## 🚀 배포 전략

### Phase 1 (긴급 배포)
- **타이밍**: 즉시 배포
- **범위**: 커뮤니티, 공지사항, 채팅, 프로필
- **이유**: 사용자 경험 개선 우선

### Phase 2 (안정화)
- **타이밍**: Phase 1 QA 완료 후
- **범위**: 강의, 행사, 관리자 페이지
- **이유**: 완전한 리팩토링 및 통일성 확보

---

## 🔄 롤백 계획

### 롤백 시나리오
만약 문제 발생 시:

1. **Git 되돌리기**:
```bash
git revert v3.98.0-phase2
git revert v3.98.0-phase1
```

2. **개별 파일 복원**:
```bash
git checkout v3.97.2 -- src/helpers/ProfileImageHelper.php
git checkout v3.97.2 -- src/views/components/profile-image.php
git checkout v3.97.2 -- src/views/chat/index.php
git checkout v3.97.2 -- src/views/user/profile.php
git checkout v3.97.2 -- src/views/admin/users/list_direct.php
```

### 롤백 후 확인
- [ ] 모든 페이지에서 프로필 이미지 클릭 시 모달 정상 표시
- [ ] JavaScript 콘솔 에러 없음
- [ ] 기존 기능 정상 동작

---

## 📝 향후 개선 사항

### 추가 개선 가능 항목
1. **프로필 페이지 미리보기**: Hover 시 프로필 요약 툴팁 표시
2. **프로필 모달 완전 제거**: 더 이상 사용하지 않는 모달 코드 완전 삭제
3. **프로필 페이지 성능**: 캐싱 및 최적화
4. **프로필 이미지 Lazy Loading**: 스크롤 시 이미지 지연 로딩

### 장기 리팩토링 목표
- 모든 페이지에서 ProfileImageHelper 사용으로 통일
- 수동 onclick 구현 완전 제거
- 프로필 이미지 컴포넌트 시스템 표준화

---

## 👥 기여자

- **개발**: Claude (Anthropic)
- **QA**: 탑마케팅 팀
- **승인**: 탑마케팅 팀

---

## 📞 문의

문제 발생 시:
1. QA 문서 참조: `QA_PROFILE_IMAGE_CLICK_v3.98.0_PHASE1.md`, `QA_PROFILE_IMAGE_CLICK_v3.98.0_PHASE2.md`
2. Git 로그 확인: `git log --grep="v3.98.0"`
3. 롤백 계획 참조 (위 섹션)

---

**문서 버전**: 1.0
**작성일**: 2025-11-03
**최종 수정**: 2025-11-03
**작성자**: Claude (Anthropic)

---

## ✅ 최종 승인

- [x] Phase 1 개발 완료
- [x] Phase 1 QA 통과
- [x] Phase 2 개발 완료
- [x] Phase 2 QA 통과
- [x] 릴리스 노트 작성 완료
- [x] **프로덕션 배포 준비 완료**

**승인일**: 2025-11-03
**버전**: v3.98.0 (Phase 1 + Phase 2)
**상태**: ✅ 배포 완료
