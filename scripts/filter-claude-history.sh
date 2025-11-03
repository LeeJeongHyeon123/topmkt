#!/bin/bash
# Claude Code history 프로젝트별 필터링 스크립트

BACKUP_DIR="/var/www/html/topmkt/backups/claude"
CLAUDE_DIR="/root/.claude"

if [ "$1" == "-h" ] || [ "$1" == "--help" ]; then
    echo "사용법: $0 [프로젝트명] [옵션]"
    echo ""
    echo "옵션:"
    echo "  -e, --extract PROJECT    프로젝트별 대화만 추출"
    echo "  -c, --clean             현재 프로젝트 외 대화 제거"
    echo "  -h, --help              도움말 표시"
    echo ""
    echo "예제:"
    echo "  $0 --extract topmkt     # topmkt 대화만 추출"
    echo "  $0 --clean              # topmkt 외 대화 제거"
    exit 0
fi

# 프로젝트별 추출
if [ "$1" == "-e" ] || [ "$1" == "--extract" ]; then
    PROJECT="$2"
    if [ -z "$PROJECT" ]; then
        echo "❌ 프로젝트명을 입력하세요."
        exit 1
    fi

    OUTPUT="$BACKUP_DIR/history-${PROJECT}-$(date +%Y%m%d-%H%M%S).jsonl"
    grep "\"project\":\"/var/www/html/${PROJECT}\"" "$CLAUDE_DIR/history.jsonl" > "$OUTPUT"

    COUNT=$(wc -l < "$OUTPUT")
    echo "✅ $PROJECT 대화 추출 완료: $COUNT lines"
    echo "📁 저장 위치: $OUTPUT"
    exit 0
fi

# 현재 프로젝트 외 대화 제거
if [ "$1" == "-c" ] || [ "$1" == "--clean" ]; then
    PROJECT="topmkt"

    # 백업
    BACKUP_NAME="history-before-clean-$(date +%Y%m%d-%H%M%S).jsonl"
    cp "$CLAUDE_DIR/history.jsonl" "$BACKUP_DIR/$BACKUP_NAME"
    echo "✅ 백업 완료: $BACKUP_NAME"

    # 현재 프로젝트만 필터링
    TEMP_FILE="/tmp/history-temp-$$.jsonl"
    grep "\"project\":\"/var/www/html/${PROJECT}\"" "$CLAUDE_DIR/history.jsonl" > "$TEMP_FILE"
    mv "$TEMP_FILE" "$CLAUDE_DIR/history.jsonl"

    BEFORE=$(wc -l < "$BACKUP_DIR/$BACKUP_NAME")
    AFTER=$(wc -l < "$CLAUDE_DIR/history.jsonl")
    REMOVED=$((BEFORE - AFTER))

    echo "📊 정리 완료:"
    echo "   이전: $BEFORE lines"
    echo "   이후: $AFTER lines"
    echo "   제거: $REMOVED lines"
    exit 0
fi

echo "❌ 잘못된 옵션입니다. --help를 참고하세요."
exit 1
