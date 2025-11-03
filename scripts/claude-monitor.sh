#!/bin/bash
# Claude Code 메모리 모니터링 도구
# 현재 실행 중인 Claude 프로세스와 시스템 리소스를 모니터링합니다.

echo "📊 Claude Code 메모리 모니터링"
echo "================================"
echo ""

# 시스템 메모리 상태
echo "💾 시스템 메모리:"
free -h | awk 'NR==1{print "   "$0} NR==2{printf "   총: %s | 사용: %s | 여유: %s | 사용률: %.1f%%\n", $2, $3, $7, ($3/$2)*100}'

# 스왑 메모리 상태
SWAP_TOTAL=$(free -m | awk '/^Swap:/{print $2}')
SWAP_USED=$(free -m | awk '/^Swap:/{print $3}')
if [ "$SWAP_TOTAL" -gt 0 ]; then
    SWAP_PERCENT=$(awk "BEGIN {printf \"%.1f\", ($SWAP_USED/$SWAP_TOTAL)*100}")
    echo "   스왑: ${SWAP_USED}MB / ${SWAP_TOTAL}MB (${SWAP_PERCENT}% 사용)"
fi

echo ""

# Claude 프로세스 목록
echo "🤖 실행 중인 Claude 프로세스:"
CLAUDE_PROCESSES=$(ps aux | grep -E "claude$|claude " | grep -v grep)

if [ -z "$CLAUDE_PROCESSES" ]; then
    echo "   실행 중인 Claude 프로세스가 없습니다."
else
    CLAUDE_COUNT=$(echo "$CLAUDE_PROCESSES" | wc -l)
    TOTAL_MEM=$(echo "$CLAUDE_PROCESSES" | awk '{sum+=$6} END {printf "%.1f", sum/1024}')

    echo "   총 ${CLAUDE_COUNT}개 프로세스 (메모리 사용: ${TOTAL_MEM}MB)"
    echo ""
    echo "   PID      CPU%   MEM%   RSS(MB)  실행시간   터미널"
    echo "   --------------------------------------------------------"
    echo "$CLAUDE_PROCESSES" | awk '{printf "   %-8s %-6s %-6s %-8.1f %-10s %s\n", $2, $3"%", $4"%", $6/1024, $10, $7}'
fi

echo ""

# 메모리 경고
AVAILABLE_MEM=$(free -m | awk '/^Mem:/{print $7}')
if [ "$AVAILABLE_MEM" -lt 1000 ]; then
    echo "⚠️  경고: 사용 가능한 메모리가 ${AVAILABLE_MEM}MB로 부족합니다!"
    echo "   OOM Killer가 프로세스를 강제 종료할 수 있습니다."
    echo ""
    echo "   권장 조치:"
    echo "   1. 사용하지 않는 Claude 프로세스 종료"
    echo "   2. 또는 cleanup-claude-processes.sh 실행"
elif [ "$AVAILABLE_MEM" -lt 2000 ]; then
    echo "⚡ 주의: 사용 가능한 메모리가 ${AVAILABLE_MEM}MB입니다."
    echo "   추가 Claude 프로세스 실행 시 주의가 필요합니다."
else
    echo "✅ 메모리 상태 양호 (${AVAILABLE_MEM}MB 사용 가능)"
fi

echo ""

# 옵션: 지속 모니터링
if [ "$1" == "--watch" ] || [ "$1" == "-w" ]; then
    echo "🔄 5초마다 자동 갱신 중... (Ctrl+C로 종료)"
    echo ""
    while true; do
        sleep 5
        clear
        bash "$0"
    done
fi

# 도움말
if [ "$1" == "--help" ] || [ "$1" == "-h" ]; then
    echo "사용법:"
    echo "  $0           # 현재 상태 1회 표시"
    echo "  $0 --watch   # 5초마다 자동 갱신"
    echo "  $0 -w        # 5초마다 자동 갱신 (단축)"
fi
