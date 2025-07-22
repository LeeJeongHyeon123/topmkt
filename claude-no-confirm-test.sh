#!/bin/bash
# Claude Code CLI 확인 설정 테스트

echo "=== Claude Code CLI 확인 설정 테스트 ==="
echo ""

echo "1. 현재 환경 변수:"
env | grep -i claude | sort

echo ""
echo "2. 글로벌 설정 파일:"
if [ -f "/root/.claude/settings.json" ]; then
    echo "   /root/.claude/settings.json:"
    cat /root/.claude/settings.json | jq . 2>/dev/null || cat /root/.claude/settings.json
else
    echo "   /root/.claude/settings.json: 파일 없음"
fi

echo ""
echo "3. 프로젝트 설정 파일:"
if [ -f "/var/www/html/topmkt/.claude-settings.json" ]; then
    echo "   /var/www/html/topmkt/.claude-settings.json:"
    cat /var/www/html/topmkt/.claude-settings.json | jq . 2>/dev/null || cat /var/www/html/topmkt/.claude-settings.json
else
    echo "   /var/www/html/topmkt/.claude-settings.json: 파일 없음"
fi

echo ""
echo "4. bashrc에서 Claude 관련 설정:"
grep -n CLAUDE /root/.bashrc

echo ""
echo "5. 권장 해결책:"
echo "   - 새 터미널 세션을 시작하거나"
echo "   - source /root/.bashrc 실행"
echo "   - ./scripts/claude-auto.sh 스크립트 사용"

echo ""
echo "테스트 완료."