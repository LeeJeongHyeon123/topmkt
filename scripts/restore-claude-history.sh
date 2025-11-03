#!/bin/bash
# Claude Code history 복원 스크립트

BACKUP_DIR="/var/www/html/topmkt/backups/claude"
CLAUDE_DIR="/root/.claude"

# 사용법
if [ "$1" == "-h" ] || [ "$1" == "--help" ]; then
    echo "사용법: $0 [백업파일] [옵션]"
    echo ""
    echo "옵션:"
    echo "  -l, --list           사용 가능한 백업 목록 표시"
    echo "  -s, --stats FILE     백업 파일 통계 표시"
    echo "  -r, --restore FILE   지정한 백업 파일로 복원"
    echo "  -h, --help           도움말 표시"
    echo ""
    echo "예제:"
    echo "  $0 --list"
    echo "  $0 --stats history-20251021-115030.jsonl"
    echo "  $0 --restore history-20251021-115030.jsonl"
    exit 0
fi

# 백업 목록 표시
if [ "$1" == "-l" ] || [ "$1" == "--list" ]; then
    echo "📦 사용 가능한 history 백업:"
    ls -lht "$BACKUP_DIR"/*.jsonl 2>/dev/null | awk '{print $9, "("$6, $7, $8")"}'
    exit 0
fi

# 통계 표시
if [ "$1" == "-s" ] || [ "$1" == "--stats" ]; then
    FILE="$BACKUP_DIR/$2"
    if [ ! -f "$FILE" ]; then
        echo "❌ 파일을 찾을 수 없습니다: $FILE"
        exit 1
    fi

    TOTAL=$(wc -l < "$FILE")
    TOPMKT=$(grep -c "topmkt" "$FILE" 2>/dev/null || echo 0)
    EATPLE=$(grep -c "eatple" "$FILE" 2>/dev/null || echo 0)

    echo "📊 백업 파일 통계: $2"
    echo "   Total: $TOTAL lines"
    echo "   topmkt: $TOPMKT lines"
    echo "   eatple: $EATPLE lines"
    exit 0
fi

# 복원
if [ "$1" == "-r" ] || [ "$1" == "--restore" ]; then
    FILE="$BACKUP_DIR/$2"
    if [ ! -f "$FILE" ]; then
        echo "❌ 파일을 찾을 수 없습니다: $FILE"
        exit 1
    fi

    # 현재 파일 백업
    if [ -f "$CLAUDE_DIR/history.jsonl" ]; then
        BACKUP_NAME="history-before-restore-$(date +%Y%m%d-%H%M%S).jsonl"
        cp "$CLAUDE_DIR/history.jsonl" "$BACKUP_DIR/$BACKUP_NAME"
        echo "✅ 현재 history 백업: $BACKUP_NAME"
    fi

    # 복원
    cp "$FILE" "$CLAUDE_DIR/history.jsonl"
    echo "✅ History 복원 완료: $2"

    # 통계 표시
    TOTAL=$(wc -l < "$CLAUDE_DIR/history.jsonl")
    TOPMKT=$(grep -c "topmkt" "$CLAUDE_DIR/history.jsonl" 2>/dev/null || echo 0)
    echo "📊 복원 후: total=$TOTAL, topmkt=$TOPMKT"
    exit 0
fi

echo "❌ 잘못된 옵션입니다. --help를 참고하세요."
exit 1
