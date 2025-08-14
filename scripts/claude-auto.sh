#!/bin/bash
# Claude Code CLI 자동 실행 스크립트
# 확인 없이 모든 명령어를 자동으로 실행합니다.

echo "🚀 Claude Code CLI 자동 실행 모드로 시작 중..."

# 모든 확인 관련 환경 변수 설정
export CLAUDE_AUTO_EXECUTE=true
export CLAUDE_CONFIRM_TOOLS=false
export CLAUDE_CONFIRM_BEFORE_TOOL_USE=false
export CLAUDE_BASH_CONFIRMATION=false
export CLAUDE_TOOL_CONFIRMATION=false
export CLAUDE_INTERACTIVE_MODE=false
export ANTHROPIC_CONFIRM_TOOLS=false

# MCP 관련 환경 변수 설정
export MCP_ENABLED=true
export PLAYWRIGHT_SKIP_BROWSER_DOWNLOAD=false
export DISPLAY=:99

# 작업 디렉토리를 탑마케팅 프로젝트로 설정
cd /var/www/html/topmkt

echo "📁 작업 디렉토리: $(pwd)"
echo "⚙️  자동 실행 모드: 활성화"
echo "❌ 도구 확인: 비활성화"
echo "🔧 환경 변수:"
echo "   CLAUDE_AUTO_EXECUTE=$CLAUDE_AUTO_EXECUTE"
echo "   CLAUDE_CONFIRM_TOOLS=$CLAUDE_CONFIRM_TOOLS"
echo "   CLAUDE_CONFIRM_BEFORE_TOOL_USE=$CLAUDE_CONFIRM_BEFORE_TOOL_USE"
echo "   MCP_ENABLED=$MCP_ENABLED"
echo "   PLAYWRIGHT_SKIP_BROWSER_DOWNLOAD=$PLAYWRIGHT_SKIP_BROWSER_DOWNLOAD"
echo "🎭 Playwright MCP 지원: 활성화"
echo ""

# Claude Code CLI 시작 (이전 대화 자동 복원)
echo "🔄 이전 대화 자동 복원 중..."
claude --continue

echo ""
echo "🏁 Claude Code CLI 세션이 종료되었습니다."