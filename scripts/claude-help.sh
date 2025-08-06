#!/bin/bash
# Claude Code 대화 복원 사용법 안내

echo "🤖 Claude Code 대화 복원 완전 가이드"
echo "==============================================="
echo ""

echo "💡 문제 해결: 이전 대화가 자동으로 불러와지지 않는 이유"
echo "   → Claude Code는 기본적으로 새 세션을 시작합니다"
echo "   → 이전 대화를 불러오려면 특별한 옵션이 필요합니다"
echo ""

echo "🔧 해결된 방법들:"
echo ""
echo "1. 🚀 자동 복원 명령어 (권장)"
echo "   claude --continue          # 가장 최근 대화 계속하기"
echo "   claude -c                  # 위와 동일 (짧은 버전)"
echo ""

echo "2. 📋 세션 선택하여 복원"
echo "   claude --resume            # 대화형으로 세션 선택"
echo "   claude -r                  # 위와 동일 (짧은 버전)"
echo ""

echo "3. 🎯 특정 세션 ID로 복원"
echo "   claude --session-id <UUID> # 특정 세션 복원"
echo ""

echo "4. 🆕 새 세션 시작 (기본값)"
echo "   claude-new                 # 항상 새 세션 시작"
echo ""

echo "✅ 설정된 자동화:"
echo "   → /root/.bashrc에 alias 추가됨"
echo "   → 이제 'claude' 명령어만 입력하면 자동으로 이전 대화 복원"
echo "   → 새 세션이 필요한 경우 'claude-new' 사용"
echo ""

echo "🎨 편리한 스크립트들:"
echo "   ./scripts/claude-auto.sh   # 자동 복원 + 모든 확인 비활성화"
echo "   ./scripts/claude-help.sh   # 이 도움말"
echo ""

echo "🔍 현재 저장된 세션들:"
if [ -d "/root/.claude/projects/-var-www-html-topmkt" ]; then
    ls -la /root/.claude/projects/-var-www-html-topmkt/*.jsonl 2>/dev/null | head -5
    echo "   → 총 $(ls /root/.claude/projects/-var-www-html-topmkt/*.jsonl 2>/dev/null | wc -l)개 세션 저장됨"
else
    echo "   → 아직 저장된 세션이 없습니다"
fi
echo ""

echo "🎯 추천 사용법:"
echo "   1. 일반적인 작업: claude (자동으로 이전 대화 복원)"
echo "   2. 새 프로젝트 시작: claude-new"
echo "   3. 특정 세션 찾기: claude-resume"
echo ""

echo "✨ 이제 Claude Code에서 대화가 끊기지 않습니다!"