#!/bin/bash
# Claude Code credentials 자동 백업 스크립트

BACKUP_DIR="/var/www/html/topmkt/backups/claude"
DATE=$(date +%Y%m%d-%H%M%S)

# 백업 디렉토리 생성
mkdir -p "$BACKUP_DIR"

# credentials와 settings 백업
if [ -f /root/.claude/.credentials.json ]; then
    cp /root/.claude/.credentials.json "$BACKUP_DIR/credentials-$DATE.json"
    echo "✅ Credentials backed up: $BACKUP_DIR/credentials-$DATE.json"
fi

if [ -f /root/.claude/settings.json ]; then
    cp /root/.claude/settings.json "$BACKUP_DIR/settings-$DATE.json"
    echo "✅ Settings backed up: $BACKUP_DIR/settings-$DATE.json"
fi

# 🔥 NEW: history.jsonl 백업 (대화 내역 보호)
if [ -f /root/.claude/history.jsonl ]; then
    cp /root/.claude/history.jsonl "$BACKUP_DIR/history-$DATE.jsonl"
    echo "✅ History backed up: $BACKUP_DIR/history-$DATE.jsonl"
fi

# 🔥 NEW: 프로젝트별 대화 통계
if [ -f /root/.claude/history.jsonl ]; then
    TOPMKT_COUNT=$(grep -c "topmkt" /root/.claude/history.jsonl 2>/dev/null || echo 0)
    TOTAL_COUNT=$(wc -l < /root/.claude/history.jsonl)
    echo "📊 History stats: topmkt=$TOPMKT_COUNT, total=$TOTAL_COUNT"
fi

# 7일 이상 된 백업 파일 삭제
find "$BACKUP_DIR" -name "*.json*" -mtime +7 -delete
echo "✅ Old backups cleaned (7+ days)"
