# 통합 테스트 (E2E Tests) 가이드

**작성일**: 2025-08-14  
**테스트 도구**: Playwright MCP + Claude Code CLI  
**테스트 대상**: 탑마케팅 웹 애플리케이션  

## 📁 폴더 구조

```
tests/integration/
├── README.md                           # 이 파일
├── notices-e2e-checklist.md           # 공지사항 E2E 테스트 체크리스트
├── notices/                            # 공지사항 테스트
│   ├── notices-e2e-test.js            # 공지사항 E2E 테스트 스크립트 (예정)
│   └── notices-test-data.json         # 테스트 데이터 (예정)
├── reports/                            # 테스트 결과 리포트
│   ├── notices-e2e-report-template.md # 리포트 템플릿
│   └── results/                       # 실행 결과 저장
│       ├── screenshots/               # 스크린샷 저장
│       └── videos/                    # 비디오 녹화 저장
├── config/                            # 테스트 설정
│   ├── playwright.config.js          # Playwright 설정 (예정)
│   └── test-environment.json         # 테스트 환경 설정 (예정)
└── utils/                             # 테스트 유틸리티
    ├── auth-helper.js                 # 인증 헬퍼 (예정)
    ├── data-helper.js                 # 데이터 처리 헬퍼 (예정)
    └── screenshot-helper.js           # 스크린샷 헬퍼 (예정)
```

## 🚀 테스트 실행 방법

### 1. Playwright MCP 환경 확인
```bash
cd /var/www/html/topmkt
node simple-playwright-test.js  # 기본 테스트 실행
```

### 2. Claude Code CLI에서 E2E 테스트 실행
```bash
./scripts/claude-auto.sh        # Claude Code 자동 실행
# Claude Code에서 Playwright MCP 도구들 사용하여 테스트 진행
```

### 3. 테스트 체크리스트 확인
- `notices-e2e-checklist.md` 파일의 120개 테스트 항목 순차 실행
- 각 항목별 통과/실패 여부 체크
- 발견된 이슈 즉시 기록

## 📋 테스트 유형

### 🔍 E2E (End-to-End) 테스트
- **목적**: 실제 사용자 워크플로우 시뮬레이션
- **범위**: 전체 애플리케이션 스택 (Frontend + Backend + Database)
- **도구**: Playwright MCP (헤드리스 브라우저)

### 📊 포함된 테스트 카테고리
1. **페이지 접근 및 기본 표시** (7개 항목)
2. **이미지 시스템** (17개 항목) - v3.10.0 개선 기능
3. **검색 및 필터링** (14개 항목)
4. **페이지네이션** (10개 항목)
5. **사용자 권한별 접근 제어** (15개 항목)
6. **CRUD 기능** (16개 항목)
7. **댓글 시스템** (14개 항목) - v3.9.0 개선 기능
8. **반응형 웹 디자인** (12개 항목)
9. **성능 및 최적화** (8개 항목)
10. **접근성 및 SEO** (9개 항목)
11. **보안** (10개 항목)
12. **에러 처리** (8개 항목)

**총 테스트 항목**: 120개

## ✅ 성공 기준

- [ ] 전체 테스트 항목 95% 이상 통과
- [ ] 치명적 오류 (Critical) 0건
- [ ] 주요 기능 100% 정상 작동
- [ ] 성능 기준 충족 (로딩 시간 3초 이내)
- [ ] 모든 디바이스에서 반응형 정상 작동

## 🛠️ 테스트 환경

### 기본 환경
- **브라우저**: Chromium (Playwright 헤드리스)
- **Node.js**: v20.14.0
- **Playwright**: v1.40.0
- **MCP SDK**: v0.4.0

### 테스트 해상도
- **데스크톱**: 1920x1080
- **태블릿**: 768x1024  
- **모바일**: 375x667

### 네트워크 조건
- **연결 속도**: 고속 (Local Network)
- **타임아웃**: 기본 5초, 페이지 로드 30초

## 📝 리포트 작성

### 자동 생성 항목
- [ ] 전체 테스트 실행 결과 요약
- [ ] 각 카테고리별 통과/실패 통계
- [ ] 발견된 이슈 상세 분석
- [ ] 성능 측정 결과 (로딩 시간, 렌더링 시간)
- [ ] 스크린샷 및 에러 로그

### 수동 분석 항목
- [ ] 이슈 우선순위 분류 (Critical/High/Medium/Low)
- [ ] 근본 원인 분석
- [ ] 수정 권장사항
- [ ] 재테스트 계획

## 🔗 관련 문서

- [Playwright MCP 연동 가이드](../../docs/Playwright_MCP_연동_가이드.md)
- [v3.10.0 공지사항 이미지표시 완전개선](../../docs/v3.10.0_공지사항_이미지표시_완전개선.md)
- [v3.9.0 댓글수정시스템 완전개선](../../docs/v3.9.0_댓글수정시스템_완전개선.md)
- [에러처리 및 로깅 표준](../../docs/22.에러처리_및_로깅_표준.md)

---

**📅 최종 수정일**: 2025-08-14  
**🤖 작성자**: Claude (Ultra Think 모드)  
**📋 상태**: 테스트 준비 완료