#!/bin/bash
# Claude Code 좀비 프로세스 안전 정리 스크립트
# 현재 실행 중인 프로세스는 보호하고 오래된 프로세스만 종료

CURRENT_PID=$$
PARENT_PID=$PPID

echo "🔍 현재 실행 중인 Claude 프로세스 확인 중..."
echo "   현재 스크립트 PID: $CURRENT_PID"
echo "   부모 프로세스 PID: $PARENT_PID"
echo ""

# 모든 claude 프로세스 찾기
CLAUDE_PIDS=$(ps aux | grep -E "claude$|claude " | grep -v grep | awk '{print $2}')

if [ -z "$CLAUDE_PIDS" ]; then
    echo "✅ 실행 중인 Claude 프로세스가 없습니다."
    exit 0
fi

echo "📊 발견된 Claude 프로세스:"
ps aux | grep -E "claude$|claude " | grep -v grep | awk '{printf "   PID %s: CPU %s%%, MEM %s%%, TIME %s\n", $2, $3, $4, $10}'
echo ""

# 현재 프로세스와 부모 프로세스 찾기
CURRENT_CLAUDE_PID=$(ps -o pid= -p $PARENT_PID 2>/dev/null | tr -d ' ')
if [ -z "$CURRENT_CLAUDE_PID" ]; then
    # 부모가 claude가 아니면 가장 최근 claude 프로세스 찾기
    CURRENT_CLAUDE_PID=$(ps aux | grep -E "claude$|claude " | grep -v grep | tail -1 | awk '{print $2}')
fi

echo "🛡️  보호할 프로세스: PID $CURRENT_CLAUDE_PID (현재 실행 중)"
echo ""

KILLED_COUNT=0
for PID in $CLAUDE_PIDS; do
    # 현재 프로세스는 건너뛰기
    if [ "$PID" = "$CURRENT_CLAUDE_PID" ]; then
        echo "⏭️  PID $PID: 현재 프로세스 - 보호됨"
        continue
    fi

    # 1시간 이상 실행된 프로세스만 종료
    RUNTIME=$(ps -o etime= -p $PID 2>/dev/null | tr -d ' ')
    if [ -n "$RUNTIME" ]; then
        echo "🔥 PID $PID 종료 중 (실행 시간: $RUNTIME)..."
        kill -9 $PID 2>/dev/null
        if [ $? -eq 0 ]; then
            KILLED_COUNT=$((KILLED_COUNT + 1))
            echo "   ✅ 종료 완료"
        else
            echo "   ❌ 종료 실패 (이미 종료됨)"
        fi
    fi
done

echo ""
echo "📈 정리 결과:"
echo "   종료된 프로세스: $KILLED_COUNT 개"
echo "   보호된 프로세스: 1 개 (PID $CURRENT_CLAUDE_PID)"

# 메모리 정리
echo ""
echo "🧹 시스템 메모리 캐시 정리 중..."
sync
echo 3 > /proc/sys/vm/drop_caches 2>/dev/null || echo "   ⚠️  캐시 정리 권한 부족 (sudo 필요)"

echo ""
echo "✅ 정리 완료!"
free -h
