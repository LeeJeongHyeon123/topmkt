#!/bin/bash

echo "🔍 HTTP 403 에러 직접 분석"

# 1. CSRF 토큰 추출
echo "📝 CSRF 토큰 추출 중..."
CSRF_TOKEN=$(curl -s -c /tmp/cookies.txt "https://www.topmktx.com/auth/forgot-password" | grep -o 'name="csrf_token" value="[^"]*"' | sed 's/name="csrf_token" value="//' | sed 's/"//')

if [ -z "$CSRF_TOKEN" ]; then
    echo "❌ CSRF 토큰을 찾을 수 없습니다"
    exit 1
fi

echo "🔐 CSRF 토큰: ${CSRF_TOKEN:0:10}..."

# 2. 한국 전화번호 형식으로 POST 요청
echo "📤 POST 요청 전송 중 (한국 전화번호 형식)..."

RESPONSE=$(curl -s -w "\n%{http_code}\n%{content_type}\n" \
    -b /tmp/cookies.txt \
    -X POST \
    -H "Content-Type: application/x-www-form-urlencoded" \
    -H "Accept: application/json" \
    -H "X-Requested-With: XMLHttpRequest" \
    -d "phone=010-1234-5678&csrf_token=$CSRF_TOKEN" \
    "https://www.topmktx.com/auth/forgot-password")

# 응답 분석
BODY=$(echo "$RESPONSE" | head -n -2)
HTTP_CODE=$(echo "$RESPONSE" | tail -n 2 | head -n 1)
CONTENT_TYPE=$(echo "$RESPONSE" | tail -n 1)

echo ""
echo "📊 응답 분석:"
echo "HTTP 상태 코드: $HTTP_CODE"
echo "Content-Type: $CONTENT_TYPE"
echo ""
echo "📄 응답 본문:"
echo "$BODY"

# JSON 파싱 시도
if [[ "$BODY" == *"{"* ]]; then
    echo ""
    echo "✅ JSON 응답 감지"
    echo "🔍 에러 메시지 추출:"
    ERROR_MSG=$(echo "$BODY" | grep -o '"error":"[^"]*"' | sed 's/"error":"//' | sed 's/"//')
    if [ ! -z "$ERROR_MSG" ]; then
        echo "   에러: $ERROR_MSG"
    fi
else
    echo ""
    echo "❌ JSON이 아닌 응답"
fi

# 3. 다른 번호 형식도 테스트
echo ""
echo "🧪 다른 번호 형식으로 재테스트 (01012345678)..."

RESPONSE2=$(curl -s -w "\n%{http_code}\n" \
    -b /tmp/cookies.txt \
    -X POST \
    -H "Content-Type: application/x-www-form-urlencoded" \
    -H "Accept: application/json" \
    -H "X-Requested-With: XMLHttpRequest" \
    -d "phone=01012345678&csrf_token=$CSRF_TOKEN" \
    "https://www.topmktx.com/auth/forgot-password")

BODY2=$(echo "$RESPONSE2" | head -n -1)
HTTP_CODE2=$(echo "$RESPONSE2" | tail -n 1)

echo "HTTP 상태: $HTTP_CODE2"
echo "응답: $BODY2"

# 정리
rm -f /tmp/cookies.txt

echo ""
echo "✅ HTTP 403 에러 분석 완료"