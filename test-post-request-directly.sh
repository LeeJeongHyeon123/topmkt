#!/bin/bash

echo "🧪 비밀번호 찾기 POST 요청 직접 테스트"

# 1. 먼저 페이지를 GET으로 가져와서 CSRF 토큰 추출
echo "📝 CSRF 토큰 추출 중..."
CSRF_TOKEN=$(curl -s -c cookies.txt "https://www.topmktx.com/auth/forgot-password" | grep -o 'name="csrf_token" value="[^"]*"' | sed 's/name="csrf_token" value="//' | sed 's/"//')

if [ -z "$CSRF_TOKEN" ]; then
    echo "❌ CSRF 토큰을 찾을 수 없습니다"
    exit 1
fi

echo "🔐 CSRF 토큰: ${CSRF_TOKEN:0:10}..."

# 2. POST 요청으로 비밀번호 찾기 제출 (잘못된 전화번호)
echo "📤 POST 요청 전송 중..."

RESPONSE=$(curl -s -w "\n%{http_code}\n%{content_type}\n" \
    -b cookies.txt \
    -X POST \
    -H "Content-Type: application/x-www-form-urlencoded" \
    -H "Accept: application/json" \
    -H "X-Requested-With: XMLHttpRequest" \
    -d "phone=123456789&csrf_token=$CSRF_TOKEN" \
    "https://www.topmktx.com/auth/forgot-password")

# 응답 분석
BODY=$(echo "$RESPONSE" | head -n -2)
HTTP_CODE=$(echo "$RESPONSE" | tail -n 2 | head -n 1)
CONTENT_TYPE=$(echo "$RESPONSE" | tail -n 1)

echo "📊 응답 분석:"
echo "HTTP 상태 코드: $HTTP_CODE"
echo "Content-Type: $CONTENT_TYPE"
echo ""
echo "응답 본문 (처음 500자):"
echo "$BODY" | head -c 500
echo ""
echo "..."

# JSON인지 HTML인지 확인
if [[ "$BODY" == *"<!DOCTYPE"* ]]; then
    echo "❌ HTML 응답이 반환되었습니다."
    echo "🔍 HTML 제목 추출:"
    echo "$BODY" | grep -o '<title>[^<]*</title>' | sed 's/<title>//' | sed 's/<\/title>//'
    
    echo ""
    echo "🔍 에러 메시지 검색:"
    echo "$BODY" | grep -o 'error[^<]*' | head -3
    
elif [[ "$BODY" == *"{"* ]]; then
    echo "✅ JSON 응답이 반환되었습니다."
    echo "🔍 JSON 내용:"
    echo "$BODY" | head -c 200
else
    echo "❓ 알 수 없는 응답 형태입니다."
fi

# 정리
rm -f cookies.txt

echo ""
echo "✅ 직접 POST 요청 테스트 완료"