# 헤더 로고 위치 수정 QA 문서 v4.1

## 📋 작업 개요
**작업일**: 2025-10-16
**버전**: v3.83.0 (예정)
**작업자**: Claude (Anthropic)
**목적**: 모든 디스플레이 사이즈에서 헤더 로고가 좌측에 고정되도록 수정

## 🔍 문제점 분석

### 사용자 보고 문제
1. ❌ 로고 위치가 부적절함 (좌측에 위치해야 함)
2. ❌ 일부 디스플레이 사이즈에서 로고가 좌측 가장자리에서 너무 멀리 떨어짐
3. ❌ 일부 디스플레이 사이즈에서 로고가 갑자기 중앙으로 이동
4. ❌ **심각**: PC 사이즈에서 로고가 오른쪽에 위치
5. ❌ 디스플레이 사이즈별로 문제 발생 (반응형 디자인 문제)

### 근본 원인
1. **`justify-content: space-between`**: 요소들을 양쪽 끝에 배치하여 로고가 중앙이나 우측으로 이동
2. **`margin-right: auto`**: Flexbox에서 로고를 우측으로 밀어냄
3. **CSS/JavaScript/HTML 간 충돌**: 동일한 속성을 3곳에서 설정하여 우선순위 충돌 발생
4. **Inline 스타일 우선순위**: HTML inline 스타일이 CSS를 덮어씀

## ✅ 적용된 수정사항

### 1. CSS 수정 (5개 섹션)

#### 섹션 1: Lines 180-191 (초기 CSS 블록)
```css
/* 변경 전 */
justify-content: space-between !important;

/* 변경 후 */
justify-content: flex-start !important;
gap: 20px !important;
```

#### 섹션 2: Lines 220-256 (핵심 CSS 규칙)
```css
/* 변경 전 */
.header-content {
    justify-content: space-between !important;
}
.header-left {
    margin-right: auto !important;
}

/* 변경 후 */
.header-content {
    justify-content: flex-start !important;
    gap: 20px !important;
}
.header-left {
    margin-right: 0 !important;
}
```

#### 섹션 3: Lines 262-293 (미디어 쿼리 CSS)
- 모든 breakpoint에서 동일한 수정 적용
- `justify-content: flex-start`, `gap: 20px`, `margin-right: 0`

#### 섹션 4: Lines 732-743 (ULTRA FORCE CSS)
```css
/* 변경 전 */
justify-content: space-between !important; /* 로고 좌측 고정 - 중앙 이동 절대 방지 */

/* 변경 후 */
justify-content: flex-start !important; /* 로고 좌측 고정 - 중앙 이동 절대 방지 */
gap: 20px !important;
```

#### 섹션 5: Lines 751-778 (모바일 미디어 쿼리 @max-width: 900px)
```css
/* 변경 전 */
margin-right: auto !important;

/* 변경 후 */
margin-right: 0 !important;
```

### 2. JavaScript 수정 (Lines 1911-1942)

#### forceCorrectLayout() 함수
```javascript
// 변경 전
headerContent.style.setProperty('justify-content', 'space-between', 'important');
const logoStyles = {
    'margin-right': 'auto'
};

// 변경 후
headerContent.style.setProperty('justify-content', 'flex-start', 'important');
headerContent.style.setProperty('gap', '20px', 'important');
const logoStyles = {
    'margin-right': '0'
};
```

### 3. HTML Inline 스타일 수정 (Lines 400, 402)

#### header-content
```html
<!-- 변경 전 -->
<div class="header-content" style="... justify-content: space-between !important; ...">

<!-- 변경 후 -->
<div class="header-content" style="... justify-content: flex-start !important; gap: 20px !important; ...">
```

#### header-left
```html
<!-- 변경 전 -->
<div class="header-left" style="... margin-right: auto !important; ...">

<!-- 변경 후 -->
<div class="header-left" style="... margin-right: 0 !important; ...">
```

## 🧪 QA 테스트 시나리오

### 테스트 환경
- **브라우저**: Chrome, Firefox, Safari, Edge
- **디바이스**: PC, 태블릿, 모바일
- **테스트 페이지**: https://www.topmktx.com (모든 페이지)

### 테스트 시나리오

#### 시나리오 1: PC 사이즈 (1920px 이상)
**테스트 단계**:
1. 브라우저 창을 최대화 (1920px 이상)
2. 홈페이지 접속
3. 헤더 로고 위치 확인
4. 페이지 스크롤 후 로고 위치 재확인
5. 다른 페이지로 이동하여 재확인

**예상 결과**:
- ✅ 로고가 헤더 좌측 끝에 위치
- ✅ 로고와 헤더 좌측 가장자리 간격: 약 20-40px
- ✅ 로고가 중앙이나 우측으로 이동하지 않음
- ✅ 스크롤 시에도 위치 변동 없음

#### 시나리오 2: 큰 태블릿 사이즈 (1440px)
**테스트 단계**:
1. 브라우저 개발자 도구 열기 (F12)
2. 반응형 디자인 모드 활성화
3. 뷰포트 크기를 1440px × 900px로 설정
4. 페이지 새로고침
5. 헤더 로고 위치 확인

**예상 결과**:
- ✅ 로고가 헤더 좌측에 고정
- ✅ 로고가 좌측 가장자리에서 적절한 거리 유지 (30px)
- ✅ 페이지 로딩 시 로고가 중앙으로 이동하지 않음

#### 시나리오 3: 태블릿 사이즈 (1024px)
**테스트 단계**:
1. 뷰포트 크기를 1024px × 768px로 설정
2. 페이지 새로고침
3. 헤더 로고 위치 확인
4. 네비게이션 메뉴 상태 확인

**예상 결과**:
- ✅ 로고가 헤더 좌측에 고정
- ✅ 로고와 좌측 가장자리 간격: 약 25px
- ✅ 로고 우측에 네비게이션 메뉴가 자연스럽게 배치

#### 시나리오 4: 작은 태블릿 사이즈 (768px)
**테스트 단계**:
1. 뷰포트 크기를 768px × 1024px로 설정
2. 페이지 새로고침
3. 모바일 메뉴 상태 확인
4. 헤더 로고 위치 확인

**예상 결과**:
- ✅ 로고가 헤더 좌측에 고정
- ✅ 햄버거 메뉴가 우측에 표시
- ✅ 로고와 좌측 가장자리 간격: 약 20px
- ✅ 로고가 중앙으로 이동하지 않음

#### 시나리오 5: 큰 모바일 사이즈 (425px)
**테스트 단계**:
1. 뷰포트 크기를 425px × 667px로 설정 (iPhone 6/7/8 Plus)
2. 페이지 새로고침
3. 헤더 로고 위치 확인
4. 모바일 메뉴 버튼 위치 확인

**예상 결과**:
- ✅ 로고가 헤더 좌측에 고정
- ✅ 로고가 좌측 가장자리에서 적절한 거리 유지 (15px)
- ✅ 로고가 너무 멀리 떨어지지 않음
- ✅ 햄버거 메뉴가 우측에 표시

#### 시나리오 6: 모바일 사이즈 (375px)
**테스트 단계**:
1. 뷰포트 크기를 375px × 667px로 설정 (iPhone 6/7/8)
2. 페이지 새로고침
3. 헤더 로고 위치 확인

**예상 결과**:
- ✅ 로고가 헤더 좌측에 고정
- ✅ 로고와 좌측 가장자리 간격: 약 12px
- ✅ 로고 텍스트와 아이콘이 모두 잘 보임

#### 시나리오 7: 작은 모바일 사이즈 (320px)
**테스트 단계**:
1. 뷰포트 크기를 320px × 568px로 설정 (iPhone SE)
2. 페이지 새로고침
3. 헤더 로고 위치 확인
4. 레이아웃 깨짐 여부 확인

**예상 결과**:
- ✅ 로고가 헤더 좌측에 고정
- ✅ 로고와 좌측 가장자리 간격: 약 10px
- ✅ 레이아웃이 깨지지 않음
- ✅ 모든 요소가 화면 내에 표시

#### 시나리오 8: 동적 리사이징 테스트
**테스트 단계**:
1. 브라우저 창을 최대화 (1920px)
2. 브라우저 창 크기를 천천히 줄이기 (1920px → 320px)
3. 각 breakpoint에서 로고 위치 확인
4. 브라우저 창 크기를 다시 키우기 (320px → 1920px)
5. 로고 위치 재확인

**예상 결과**:
- ✅ 모든 사이즈에서 로고가 좌측에 고정
- ✅ 리사이징 시 로고가 갑자기 중앙으로 점프하지 않음
- ✅ 부드러운 전환 애니메이션 (있는 경우)
- ✅ 레이아웃이 깨지지 않음

## 📊 체크리스트

### 기본 체크리스트
- [ ] 1920px 이상: 로고 좌측 고정
- [ ] 1440px: 로고 좌측 고정
- [ ] 1024px: 로고 좌측 고정
- [ ] 768px: 로고 좌측 고정
- [ ] 425px: 로고 좌측 고정, 적절한 간격 유지
- [ ] 375px: 로고 좌측 고정
- [ ] 320px: 로고 좌측 고정

### 페이지별 체크리스트
- [ ] 홈페이지 (/)
- [ ] 강의 목록 (/lectures)
- [ ] 강의 상세 (/lectures/{id})
- [ ] 행사 목록 (/events)
- [ ] 행사 상세 (/events/{id})
- [ ] 커뮤니티 (/community)
- [ ] 로그인 페이지 (/login)

### 브라우저별 체크리스트
- [ ] Chrome
- [ ] Firefox
- [ ] Safari
- [ ] Edge
- [ ] 모바일 Safari (iOS)
- [ ] 모바일 Chrome (Android)

## 🚨 알려진 제한사항

### 모바일 레이아웃 (768px 이하)
- Line 1792의 `justify-content: space-between`은 의도적으로 유지됨
- 이유: 모바일에서 로고(좌측) + 햄버거 메뉴(우측) 레이아웃을 위함
- 로고는 여전히 좌측에 고정되며, 햄버거 메뉴는 우측에 표시됨

## 🔄 롤백 방법

### 문제 발생 시 복구 절차
1. 백업 파일 위치 확인:
   ```bash
   ls -la /var/www/html/topmkt/src/views/templates/header.php.backup_*
   ```

2. 최신 백업으로 복구:
   ```bash
   # 백업 파일명 확인 후
   cp /var/www/html/topmkt/src/views/templates/header.php.backup_YYYYMMDD_HHMMSS \
      /var/www/html/topmkt/src/views/templates/header.php
   ```

3. Git 복구 (커밋 후):
   ```bash
   git log --oneline -5  # 최근 커밋 확인
   git revert <commit-hash>  # 특정 커밋 되돌리기
   ```

## 📝 버그 리포트 템플릿

문제 발견 시 아래 템플릿 사용:

```markdown
### 버그 리포트

**디스플레이 사이즈**: (예: 1440px × 900px)
**브라우저**: (예: Chrome 120.0)
**OS**: (예: Windows 11 / macOS 14 / iOS 17)
**페이지 URL**: (예: https://www.topmktx.com/lectures)

**문제 설명**:
(로고 위치가 어떻게 잘못되었는지 상세히 설명)

**스크린샷**:
(가능하면 스크린샷 첨부)

**재현 단계**:
1.
2.
3.

**예상 동작**:
(로고가 어디에 위치해야 하는지)

**실제 동작**:
(로고가 실제로 어디에 위치하는지)

**개발자 도구 콘솔 로그**:
```
(F12 → Console 탭의 로그 복사)
```
```

## 📞 지원

**문제 발생 시 연락처**: 개발팀
**긴급 수정 필요 시**: 백업 파일로 즉시 롤백 후 보고

---

**작성일**: 2025-10-16
**최종 수정**: 2025-10-16
**다음 리뷰**: QA 완료 후
