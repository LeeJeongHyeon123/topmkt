# 탑마케팅 프로젝트 - 작업 관리 (TASK)

**프로젝트**: 탑마케팅 (TOPMKT)
**경로**: `/var/www/html/topmkt`
**버전**: v5.0.0 (진행 중)
**작업명**: Font Awesome → Lucide Icons 전환
**최종 업데이트**: 2025-11-20 KST

---

## 📌 사용 규칙

### ✅ 이 파일의 역할
- **진행 중 작업만** 기록
- 실시간 체크박스 업데이트
- 작업 현황 한눈에 파악

### 📝 워크플로우
1. **작업 시작**: 새 섹션 추가
2. **작업 진행**: 체크박스 실시간 업데이트
3. **작업 완료**:
   - → `docs/12.개발노트.md`로 상세 내용 이동
   - → `CLAUDE.md`에 요약 추가
   - → **이 파일에서 완료 작업 삭제**

---

## 🚀 진행 중 작업: v5.0.0 - Font Awesome → Lucide Icons 전환

### 전체 진행률: 0% (0/30시간)

**목표**: 깔끔하고 세련된 홈페이지 구현 (Font Awesome 완전 제거)

---

## Phase 1: Lucide Icons 통합 준비 (0/2시간)

### Phase 1.1: Lucide CDN 추가
- [ ] header.php에 Lucide CDN 스크립트 추가
- [ ] Lucide 초기화 스크립트 추가
- [ ] 브라우저 콘솔에서 `lucide` 객체 확인
- [ ] `lucide.createIcons()` 정상 실행 확인

**파일**: `src/views/templates/header.php`

---

### Phase 1.2: Helper 함수 작성
- [ ] `lucide-helper.js.php` 파일 생성
- [ ] `replaceLucideIcon()` 함수 작성
- [ ] `createLucideIcon()` 함수 작성
- [ ] `getLucideIconName()` 함수 작성 (105개 매핑)
- [ ] `toggleLucideIcon()` 함수 작성
- [ ] `addLucideIconToButton()` 함수 작성
- [ ] footer.php에 Helper 함수 통합
- [ ] 브라우저에서 Helper 함수 단위 테스트

**파일**:
- `src/views/includes/lucide-helper.js.php` (신규)
- `src/views/templates/footer.php` (수정)

---

### Phase 1.3: CSS 애니메이션 추가
- [ ] `lucide-custom.css` 파일 생성
- [ ] 기본 아이콘 스타일 정의
- [ ] 로딩 스피너 애니메이션 (spin) 추가
- [ ] 아이콘 크기 유틸리티 클래스 추가
- [ ] 아이콘 색상 유틸리티 클래스 추가
- [ ] 반응형 아이콘 크기 정의
- [ ] header.php에 CSS 통합
- [ ] 스피너 회전 애니메이션 동작 확인

**파일**:
- `public/assets/css/lucide-custom.css` (신규)
- `src/views/templates/header.php` (수정)

---

### Phase 1 완료 조건
- [ ] ✅ Lucide CDN 정상 로드
- [ ] ✅ Helper 함수 5개 모두 작동
- [ ] ✅ CSS 애니메이션 정상 작동
- [ ] ✅ Font Awesome과 Lucide 병행 사용 가능

---

## Phase 2: 공통 컴포넌트 전환 (0/4시간)

### 2.1 PHP 컴포넌트 전환
- [ ] Button.php - FA 아이콘 → Lucide 전환
- [ ] SearchFilter.php - FA 아이콘 → Lucide 전환
- [ ] Card.php - FA 아이콘 → Lucide 전환

**파일**:
- `src/components/ui/Button.php`
- `src/components/ui/SearchFilter.php`
- `src/components/ui/Card.php`

---

### 2.2 템플릿 컴포넌트 전환
- [ ] header.php - 햄버거 메뉴, 사용자 아이콘 등 (34개 FA)
- [ ] footer.php - 하단 아이콘 (2개 FA)
- [ ] 404.php - 에러 페이지 아이콘 (6개 FA)
- [ ] 403.php - 에러 페이지 아이콘 (2개 FA)

**파일**:
- `src/views/templates/header.php`
- `src/views/templates/footer.php`
- `src/views/templates/404.php`
- `src/views/templates/403.php`

---

### 2.3 JavaScript 컴포넌트 전환
- [ ] loading.js.php - `fas fa-spinner` → `Loader2`
- [ ] toast.js.php - 상태 아이콘 → Lucide

**파일**:
- `src/views/includes/loading.js.php`
- `src/views/includes/toast.js.php`

---

### Phase 2 완료 조건
- [ ] ✅ 모든 컴포넌트 아이콘 정상 표시
- [ ] ✅ 동적 아이콘 정상 작동
- [ ] ✅ 기존 페이지 영향 없음 확인

---

## Phase 3: POC 테스트 - community 페이지 (0/2시간)

### 3.1 페이지 전환
- [ ] community/index.php FA 아이콘 2개 → Lucide 전환

**파일**: `src/views/community/index.php`

---

### 3.2 QA 테스트
- [ ] 아이콘 정상 표시 확인
- [ ] 동적 아이콘 동작 확인 (있는 경우)
- [ ] 반응형 디자인 확인 (PC, Tablet, Mobile)
- [ ] 브라우저 호환성 확인 (Chrome, Safari, Firefox)
- [ ] 성능 측정 (번들 사이즈, 로딩 속도)

---

### 3.3 의사결정
- [ ] QA 통과 → Phase 4 진행
- [ ] 이슈 발견 → 전략 재수립

**문서**: POC QA 결과 보고서 작성

---

## Phase 4: 전체 페이지 순차 전환 (0/16시간)

> Phase 3 QA 통과 후 체크리스트 추가 예정

### 4.1 Low Risk 페이지 (0/2시간)
- [ ] registrations/dashboard.php
- [ ] notifications/settings.php

---

### 4.2 Medium Risk 페이지 (0/8시간)
- [ ] lectures/* (29개 FA)
- [ ] events/* (46개 FA)
- [ ] notices/* (29개 FA)
- [ ] chat/index.php (19개 FA)

---

### 4.3 High Risk 페이지 (0/6시간)
- [ ] auth/* (86개 FA, 동적 아이콘 많음)
- [ ] user/* (32개 FA)

---

## Phase 5: 이모지 → Lucide 전환 (선택, 0/4시간)

> Phase 4 완료 후 체크리스트 추가 예정

- [ ] 📅 → `<Calendar />`
- [ ] 🕒 → `<Clock />`
- [ ] 👨‍🏫 → `<GraduationCap />`
- [ ] 📍 → `<MapPin />`
- [ ] ⏰ → `<Clock />`

---

## Phase 6: Font Awesome 제거 및 정리 (0/2시간)

> Phase 5 완료 후 체크리스트 추가 예정

- [ ] Font Awesome CDN 제거 (header.php)
- [ ] Fallback CSS 제거
- [ ] `.fa-*` 클래스 의존성 제거
- [ ] Lucide 커스텀 스타일 통합
- [ ] 전체 페이지 크로스 브라우저 테스트
- [ ] 성능 측정 (Before/After)
- [ ] Git 커밋 (v5.0.0)
- [ ] 문서화 (개발노트.md, CLAUDE.md)
- [ ] PLAN.md, TASK.md 초기화

---

## 📊 전체 체크리스트 요약

### Phase 1: Lucide 통합 준비
- **진행률**: 0/17개 (0%)
- **예상 시간**: 2시간
- **상태**: 진행 중

### Phase 2: 공통 컴포넌트 전환
- **진행률**: 0/12개 (0%)
- **예상 시간**: 4시간
- **상태**: 대기 중

### Phase 3: POC 테스트
- **진행률**: 0/7개 (0%)
- **예상 시간**: 2시간
- **상태**: 대기 중

### Phase 4-6: 전체 전환 및 정리
- **예상 시간**: 22시간
- **상태**: 계획 중

---

**다음 작업**: Phase 1.1 - Lucide CDN 추가 (header.php)
**관련 문서**: `/var/www/html/topmkt/PLAN.md` (상세 구현 계획)
