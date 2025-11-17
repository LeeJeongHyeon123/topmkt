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

### 🎨 강의/행사 일정 페이지 모바일 UI 개선 (v4.2.3)

**시작일**: 2025-11-17
**담당**: Claude (Anthropic)
**작업 유형**: UI/UX 개선

#### 문제
1. **모바일 타이틀 크기**: 2.5rem → 너무 큼, 가독성 저하
2. **행사 일정 리스트뷰 간격**: gradient-header가 공통 헤더에 붙어 있음

#### Ultra Think 7단계 체크리스트

**✅ 1단계: 문제 정의**
- [x] 강의/행사 페이지 파일 위치 파악
- [x] 모바일 breakpoint 확인 (768px, 480px)
- [x] 현재 타이틀 크기 확인 (2.5rem)
- [x] 캘린더뷰 vs 리스트뷰 간격 차이 확인

**✅ 2단계: 데이터 수집**
- [x] 강의 일정 파일 읽기 (lectures/index.php)
- [x] 행사 일정 파일 읽기 (events/list.php, events/index.php)
- [x] GradientHeader 컴포넌트 확인
- [x] 모바일 CSS 미디어 쿼리 확인

**✅ 3단계: 근본 원인 파악**
- [x] GradientHeader.php 인라인 스타일 (font-size: 2.5rem, margin-top: 60px)
- [x] events/list.php에 768px h1 font-size 조정 누락
- [x] events/list.php에 margin-top 충돌 (15px vs 30px)

**✅ 4단계: 해결 전략 수립**
- [x] 각 페이지별 CSS 오버라이드 방식 선택
- [x] events/list.php: h1 1.6rem 추가, margin-top 15px 제거
- [x] event-index-styles.css: h1 1.6rem 추가

**✅ 5단계: 구현**
- [x] lectures/index.php: 이미 1.6rem 적용 확인 (수정 불필요)
- [x] events/list.php: 768px h1 1.6rem 추가 (2곳)
- [x] event-index-styles.css: 768px h1 1.6rem 추가
- [x] events/list.php: 충돌하는 margin-top 15px 제거

**✅ 6단계: 검증**
- [x] events/list.php h1 font-size: 1.6rem 2곳 확인
- [x] event-index-styles.css h1 font-size: 1.6rem 확인
- [x] lectures/index.php h1 font-size: 1.6rem 확인
- [x] events/list.php margin-top 15px 제거 확인
- [x] events/list.php margin-top 30px 유지 확인

**✅ 7단계: 문서화**
- [x] `docs/12.개발노트.md` 업데이트
- [x] TASK.md 업데이트 (모든 단계 완료)
- [x] Git 커밋 및 태그 (v4.2.3 완료)

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
