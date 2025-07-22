#!/bin/bash
# 간단한 curl 테스트

echo "=== curl 테스트 ==="
curl -s -I "https://www.topmktx.com" | head -5
echo ""
echo "✅ curl 명령어 실행 완료!"