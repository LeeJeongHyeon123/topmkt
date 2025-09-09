#!/bin/bash
# Claude Code CLI 자동 실행 스크립트
# 확인 없이 모든 명령어를 자동으로 실행합니다.

echo "🚀 Claude Code CLI 자동 실행 모드로 시작 중..."

# Claude Code CLI 빠른 상태 체크
echo "🔄 Claude Code CLI 상태 체크 중..."
if ! command -v claude &> /dev/null; then
    echo "❌ Claude Code CLI가 설치되지 않았습니다."
    exit 1
fi

# 백그라운드에서 업데이트 체크 (성능 최적화)
echo "📦 Claude Code CLI 백그라운드 업데이트 체크..."
timeout 3s claude update --auto-confirm &> /dev/null || {
    echo "⚡ 업데이트 체크 건너뜀 (빠른 시작 모드)"
}

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

# 성능 최적화 환경 변수 (최고 속도 모드)
export CLAUDE_PARALLEL_TOOLS=true
export CLAUDE_BATCH_MODE=true
export CLAUDE_MAX_CONCURRENT=16
export CLAUDE_STREAMING=true
export CLAUDE_CACHE_ENABLED=true
export CLAUDE_AGGRESSIVE_CACHING=true
export CLAUDE_PREFETCH_ENABLED=true
export CLAUDE_COMPRESSION_ENABLED=true
export CLAUDE_FAST_MODE=true
export CLAUDE_OPTIMIZED_RENDERING=true
export CLAUDE_MINIMAL_OUTPUT=false
export NODE_OPTIONS="--max-old-space-size=12288 --max-semi-space-size=1024 --enable-source-maps=false"

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
echo "⚡ 극한 성능 모드: Sonnet 4 모델 + 병렬 16개 + 적극적 캐시 + 스트리밍 + 12GB 메모리"
claude --continue

echo ""
echo "🏁 Claude Code CLI 세션이 종료되었습니다."