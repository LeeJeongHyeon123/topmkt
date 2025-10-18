# Playwright MCP 연동 가이드

## 📋 개요
이 문서는 탑마케팅 TOPMKT 프로젝트에서 Playwright MCP(Model Context Protocol)를 헤드리스 모드로 Claude Code와 연동하는 방법을 설명합니다.

### 🎯 목표
- Playwright를 MCP 서버로 구성하여 Claude Code에서 헤드리스 브라우저 자동화 기능 사용
- 웹 페이지 테스트, 스크린샷 촬영, UI 자동화 등을 Claude Code CLI를 통해 실행

### ✅ 완료된 기능
- ✅ Playwright MCP 서버 구축
- ✅ Claude Code 설정 통합
- ✅ 헤드리스 브라우저 자동화
- ✅ 9가지 브라우저 도구 제공
- ✅ JSON-RPC 통신 검증

## 🔧 설치 및 설정

### 1. 환경 요구사항
```bash
Node.js: v18+ (현재 v20.14.0)
npm: 패키지 관리자
Claude Code CLI: MCP 지원 버전
```

### 2. 패키지 설치
```bash
cd /var/www/html/topmkt

# 핵심 패키지 설치
npm install playwright@^1.40.0 @playwright/test@^1.40.0 @modelcontextprotocol/sdk@^0.4.0

# 브라우저 설치 (269개 의존성 포함)
npx playwright install --with-deps
```

### 3. package.json 설정
```json
{
  "name": "topmkt",
  "type": "module",
  "dependencies": {
    "playwright": "^1.40.0",
    "@playwright/test": "^1.40.0",
    "@modelcontextprotocol/sdk": "^0.4.0"
  }
}
```

### 4. MCP 서버 설정 (`.mcp/mcp.json`)
```json
{
  "mcpServers": {
    "playwright": {
      "command": "node",
      "args": ["/var/www/html/topmkt/playwright-mcp-server.js"],
      "env": {
        "PLAYWRIGHT_SKIP_BROWSER_DOWNLOAD": "false",
        "DISPLAY": ":99"
      }
    }
  }
}
```

### 5. Claude Code 설정 (`.claude-settings.json`)
```json
{
  "model": "sonnet",
  "autoExecute": true,
  "confirmTools": false,
  "allowedTools": [
    "mcp__launch_browser",
    "mcp__navigate",
    "mcp__screenshot",
    "mcp__click",
    "mcp__type",
    "mcp__wait_for_selector",
    "mcp__evaluate",
    "mcp__get_page_content",
    "mcp__close_browser"
  ]
}
```

## 🛠️ 사용 가능한 도구들

### 1. launch_browser
**기능**: 헤드리스 브라우저 실행
```json
{
  "browser": "chromium|firefox|webkit",
  "headless": true,
  "viewport": {"width": 1920, "height": 1080}
}
```

### 2. navigate  
**기능**: 지정된 URL로 이동
```json
{
  "url": "https://www.topmktx.com",
  "waitUntil": "load|domcontentloaded|networkidle"
}
```

### 3. screenshot
**기능**: 현재 페이지 스크린샷 촬영
```json
{
  "path": "/var/www/html/topmkt/screenshot.png",
  "fullPage": false,
  "format": "png|jpeg"
}
```

### 4. click
**기능**: 지정된 요소 클릭
```json
{
  "selector": "button.login-btn",
  "timeout": 5000
}
```

### 5. type
**기능**: 지정된 요소에 텍스트 입력
```json
{
  "selector": "input#username",
  "text": "testuser",
  "timeout": 5000
}
```

### 6. wait_for_selector
**기능**: 선택자가 나타날 때까지 대기
```json
{
  "selector": ".loading-complete",
  "timeout": 10000,
  "state": "visible|hidden|attached|detached"
}
```

### 7. evaluate
**기능**: 페이지에서 JavaScript 코드 실행
```json
{
  "script": "document.title"
}
```

### 8. get_page_content
**기능**: 현재 페이지의 HTML 내용 가져오기
```json
{}
```

### 9. close_browser
**기능**: 브라우저 닫기
```json
{}
```

## 📋 테스트 결과

### 기본 Playwright 테스트
```bash
$ node simple-playwright-test.js

🎭 Playwright 기본 테스트 시작...
✅ 브라우저 실행 성공
✅ 새 페이지 생성 성공
✅ 페이지 이동 성공
📄 페이지 제목: 홈 - 탑마케팅
📸 스크린샷 저장 성공: playwright-test.png
✅ 브라우저 종료 완료
🎉 모든 테스트 통과!
```

### MCP 서버 테스트
```bash
$ node test-playwright-mcp.js

🧪 Playwright MCP 서버 테스트 시작...
📤 도구 목록 요청...
✅ 9개 도구 정상 반환

📤 브라우저 실행...
✅ chromium 브라우저가 성공적으로 시작되었습니다 (헤드리스: true)

📤 페이지 이동...
✅ https://www.topmktx.com로 이동 완료
제목: 홈 - 탑마케팅

📤 스크린샷 촬영...
✅ 스크린샷이 /var/www/html/topmkt/test-screenshot.png에 저장되었습니다

📤 브라우저 닫기...
✅ 브라우저가 성공적으로 닫혔습니다

✅ 테스트 완료!
```

## 💡 사용 예시

### Claude Code CLI에서 사용
```bash
# Claude Code 실행 (자동으로 MCP 서버 연결)
claude --continue

# 또는 새 세션
claude-new
```

### 기본 브라우저 자동화 시나리오
```
1. mcp__launch_browser 도구로 브라우저 시작
2. mcp__navigate 도구로 웹사이트 이동  
3. mcp__screenshot 도구로 스크린샷 촬영
4. mcp__click 도구로 버튼 클릭
5. mcp__type 도구로 텍스트 입력
6. mcp__close_browser 도구로 브라우저 종료
```

## 🔍 문제 해결

### 1. 브라우저 설치 오류
```bash
# 브라우저 재설치
npx playwright install --with-deps

# 시스템 권한 문제 시
sudo npx playwright install --with-deps
```

### 2. MCP 서버 연결 실패  
```bash
# MCP 설정 확인
cat .mcp/mcp.json

# Claude Code 설정 확인
cat .claude-settings.json
```

### 3. 헤드리스 모드 문제
```bash
# 디스플레이 서버 확인
export DISPLAY=:99

# X11 포워딩 활성화 (필요시)
sudo apt-get install xvfb
```

### 4. 권한 문제
```bash
# 파일 권한 확인
ls -la playwright-mcp-server.js

# 실행 권한 추가
chmod +x playwright-mcp-server.js
```

## 🚀 고급 활용법

### 1. 자동화 스크립트 작성
```javascript
// 커스텀 테스트 스크립트 예시
const testFlow = [
  { tool: 'launch_browser', args: { browser: 'chromium', headless: true } },
  { tool: 'navigate', args: { url: 'https://www.topmktx.com' } },
  { tool: 'wait_for_selector', args: { selector: '.main-content' } },
  { tool: 'screenshot', args: { path: './results.png' } },
  { tool: 'close_browser', args: {} }
];
```

### 2. 페이지 성능 테스트
```javascript
// 성능 측정 스크립트
const performanceTest = {
  script: `
    performance.getEntriesByType('navigation')[0].loadEventEnd - 
    performance.getEntriesByType('navigation')[0].navigationStart
  `
};
```

### 3. UI 요소 검증
```javascript
// UI 요소 존재 확인
const uiValidation = {
  script: `
    document.querySelector('.login-btn') ? 'exists' : 'missing'
  `
};
```

## 📚 참고 자료

### 공식 문서
- [Playwright 공식 문서](https://playwright.dev/)
- [Model Context Protocol 문서](https://modelcontextprotocol.io/)
- [Claude Code 문서](https://docs.anthropic.com/claude/code)

### 프로젝트 파일
- `playwright-mcp-server.js`: MCP 서버 구현체
- `simple-playwright-test.js`: 기본 테스트 스크립트
- `test-playwright-mcp.js`: MCP 서버 테스트 스크립트
- `.mcp/mcp.json`: MCP 서버 설정
- `.claude-settings.json`: Claude Code 설정

## 🎉 결론

Playwright MCP 연동이 성공적으로 완료되었습니다. 이제 Claude Code CLI를 통해 강력한 헤드리스 브라우저 자동화 기능을 사용할 수 있습니다.

### 주요 성과
- ✅ **완전 자동화**: Claude Code에서 직접 브라우저 제어 가능
- ✅ **헤드리스 모드**: 서버 환경에서 GUI 없이 안정적 실행
- ✅ **다양한 도구**: 9가지 브라우저 자동화 도구 제공
- ✅ **실시간 테스트**: JSON-RPC 통신으로 즉시 결과 확인
- ✅ **확장성**: 커스텀 도구 추가 및 스크립트 확장 가능

---

**문서 생성일**: 2025-08-14  
**작성자**: Claude (Ultra Think 모드)  
**버전**: v1.0  
**프로젝트**: 탑마케팅 TOPMKT