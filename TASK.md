# 탑마케팅 프로젝트 - 작업 관리 (TASK)

**프로젝트**: 탑마케팅 (TOPMKT)
**경로**: `/var/www/html/topmkt`
**최종 업데이트**: 2025-11-16 21:30 KST

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

### 🔗 관련 문서
- **완료 이력**: [docs/12.개발노트.md](./docs/12.개발노트.md)
- **프로젝트 가이드**: [CLAUDE.md](./CLAUDE.md)
- **에러 처리 표준**: [docs/22.에러처리_및_로깅_표준.md](./docs/22.에러처리_및_로깅_표준.md)

---

## 🚀 진행 중 작업

### 🎨 강의 일정 더보기 모달 헤더 UI 개선 (v4.2.4)

**시작일**: 2025-11-17
**담당**: Claude (Anthropic)
**작업 유형**: UI/UX 개선
**우선순위**: 중간

#### 문제
- **현재 상황**: 강의 일정 캘린더에서 4개 이상 일정이 있는 날짜 클릭 시 모달 팝업의 헤더가 다음과 같이 구성됨:
  ```
  10일 일정     2025년 6월 10일 화요일 · 총 7개 일정     [X]
  ```
- **문제점**: 닫기 버튼(X)과 날짜/요일 정보가 한 줄에 배치되어 **겹쳐 보임**
- **사용자 요구**: 다음 형식으로 2줄 구성 필요:
  ```
  [n일 일정]                                        [X]
  yyyy년 mm월 dd일 ㅇ요일 총 n개 일정
  ```

#### Ultra Think 7단계 체크리스트

**✅ 1단계: 문제 정의**
- [x] 스크린샷 확인 (screenshot_20251117_101706.png)
- [x] 모달 HTML 구조 파악 (lines 1742-1753)
- [x] 현재 헤더 레이아웃 분석 (title + subtitle + close button)

**✅ 2단계: 데이터 수집**
- [x] lectures/index.php 모달 관련 코드 읽기
- [x] showDayLectures() 함수 분석 (lines 1815-1895)
- [x] 모달 CSS 스타일 확인 (lines 1183-1332)
- [x] 현재 close button positioning: absolute (top: 50%)

**🔄 3단계: 근본 원인 파악**
- [x] `.modal-close` 버튼이 `position: absolute; top: 50%`로 헤더 중앙에 위치
- [x] `modalTitle`과 `modalSubtitle`이 세로로 쌓여 있어 높이 증가
- [x] 닫기 버튼이 subtitle과 겹치는 문제 발생
- [ ] 원하는 레이아웃: title + close 한 줄, subtitle 다음 줄

**🔄 4단계: 해결 전략 수립**
- [ ] HTML 구조 변경: title과 close를 감싸는 wrapper div 추가
- [ ] CSS flexbox 레이아웃: title-wrapper (space-between), subtitle (다음 줄)
- [ ] close button을 absolute → relative 포지셔닝으로 변경
- [ ] subtitle 상단 마진 추가 (5px → 10px)

**⏳ 5단계: 구현**
- [ ] HTML: `.modal-header` 내부 구조 변경
  - [ ] `.modal-title-wrapper` 추가 (flex container)
  - [ ] `modalTitle` + `modalClose` 포함
  - [ ] `modalSubtitle` 별도 배치
- [ ] CSS: `.modal-title-wrapper` 스타일 추가
  - [ ] `display: flex; justify-content: space-between; align-items: center;`
- [ ] CSS: `.modal-close` 포지셔닝 변경
  - [ ] `position: relative` (absolute 제거)
  - [ ] `top`, `right`, `transform` 제거
- [ ] CSS: `.modal-subtitle` 마진 조정
  - [ ] `margin: 10px 0 0 0;`

**⏳ 6단계: 검증**
- [ ] Playwright 스크립트 작성 (모달 열기 + 헤더 레이아웃 검증)
- [ ] 768px viewport: title과 close 한 줄 확인
- [ ] 425px viewport: 모바일 반응형 확인
- [ ] 닫기 버튼 클릭 동작 확인
- [ ] subtitle 텍스트 겹침 없음 확인

**⏳ 7단계: 문서화**
- [ ] `docs/12.개발노트.md`에 v4.2.4 추가
- [ ] TASK.md 완료 처리
- [ ] Git 커밋 및 태그 (v4.2.4)

#### 수정 파일
- `/var/www/html/topmkt/src/views/lectures/index.php`
  - HTML: lines 1742-1753 (모달 구조)
  - CSS: lines 1219-1256 (modal-header, modal-title, modal-subtitle, modal-close)

#### 예상 소요 시간
- 구현: 15분
- 검증: 10분
- 문서화: 5분
- **총 30분**

---

## 📝 다음 작업 대기

(없음)

---

**최근 완료 작업**: v4.2.3 - 강의/행사 일정 페이지 모바일 UI 개선 (2025-11-17)
**문서화 위치**: `/docs/12.개발노트.md`
**Git 태그**: `v4.2.3`
**커밋 해시**: `8742503a`

---

## ⚠️ 보류/블로킹 작업

(없음)

---

**최근 완료 작업**: v4.2.2 - CorporateMiddleware Fatal Error 긴급 수정 (2025-11-17)
**문서화 위치**: `/docs/12.개발노트.md`, `/CLAUDE.md`
**Git 태그**: `v4.2.2`
**다음 업데이트**: 신규 작업 시작 시
