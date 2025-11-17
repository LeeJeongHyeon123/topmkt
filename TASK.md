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

### 🔥 CorporateMiddleware 클래스 중복 선언 Fatal Error 해결 (v4.2.2)

**시작일**: 2025-11-17
**담당**: Claude (Anthropic)
**작업 유형**: 긴급 버그 수정
**심각도**: Critical (서비스 중단)

#### 문제
- **URL**: https://www.topmktx.com/lectures?view=list
- **에러**: `Fatal error: Cannot declare class CorporateMiddleware, because the name is already in use`
- **위치**: `/var/www/html/topmkt/src/middleware/CorporateMiddleware.php` line 9
- **영향**: 강의 목록 페이지 완전 중단

#### Ultra Think 7단계 체크리스트

**⏳ 1단계: 문제 정의**
- [ ] Fatal Error 원인 파악: 클래스 중복 선언
- [ ] 영향 범위 확인: 어떤 페이지들이 영향 받는지
- [ ] 발생 시점 추정: 언제부터 발생했는지

**⏳ 2단계: 데이터 수집**
- [ ] CorporateMiddleware.php 파일 읽기
- [ ] 중복 선언 위치 찾기 (같은 파일 내 or 다른 파일)
- [ ] 최근 Git 커밋 히스토리 확인
- [ ] 파일 시스템 검색: `find` + `grep`으로 중복 파일 찾기

**⏳ 3단계: 근본 원인 파악**
- [ ] 왜 갑자기 발생했는지 분석
- [ ] 최근 변경사항과 연관성 확인
- [ ] 파일 구조 문제 or 코드 문제 판별

**⏳ 4단계: 해결 전략 수립**
- [ ] Option A: 중복 클래스 선언 제거
- [ ] Option B: 중복 파일 제거
- [ ] Option C: require/include 중복 호출 제거
- [ ] 최적 해결 방법 선택

**⏳ 5단계: 구현**
- [ ] 중복 제거 또는 파일 수정
- [ ] PHP 문법 검증
- [ ] 관련 파일들 확인 및 수정

**⏳ 6단계: 검증**
- [ ] 웹사이트 접속 테스트: https://www.topmktx.com/lectures?view=list
- [ ] Fatal Error 해결 확인
- [ ] 다른 페이지 정상 작동 확인
- [ ] 로그 확인

**⏳ 7단계: 문서화**
- [ ] 근본 원인 분석 결과 정리
- [ ] 재발 방지 대책 수립
- [ ] `docs/12.개발노트.md` 업데이트
- [ ] Git 커밋 및 태그

---

## 📝 다음 작업 대기

(없음)

---

## ⚠️ 보류/블로킹 작업

(없음)

---

**최근 완료 작업**: v4.2.1 - 공지사항 목록 HTML 엔티티 표시 오류 수정 (2025-11-17)
**문서화 위치**: `/docs/12.개발노트.md`, `/CLAUDE.md`
**다음 업데이트**: 신규 작업 시작 시
