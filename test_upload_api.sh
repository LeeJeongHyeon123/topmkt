#!/bin/bash

echo "🚀 Quill 이미지 업로드 API 실제 테스트"
echo "====================================="

# 테스트 이미지 생성 (1x1 PNG)
TEST_IMAGE="/tmp/test_upload.png"
echo -n "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==" | base64 -d > $TEST_IMAGE

echo "📸 테스트 이미지 생성: $TEST_IMAGE"
echo "파일 크기: $(ls -lh $TEST_IMAGE | awk '{print $5}')"
echo ""

# 세션 쿠키 파일
COOKIE_FILE="/tmp/test_cookies.txt"

echo "🔐 로그인 세션 생성 중..."
# 관리자 계정으로 로그인 (세션 생성)
curl -s -c $COOKIE_FILE \
    -X POST \
    -d "email=admin@topmktx.com&password=Dnlszkem1!" \
    "https://www.topmktx.com/api/auth/login" > /dev/null

echo "📤 이미지 업로드 API 테스트..."

# 실제 업로드 API 호출
RESPONSE=$(curl -s -b $COOKIE_FILE \
    -X POST \
    -F "image=@$TEST_IMAGE" \
    -F "upload_type=notices" \
    -F "is_quill_upload=true" \
    "https://www.topmktx.com/api/media/upload-image")

echo "📋 API 응답:"
echo "$RESPONSE" | jq . 2>/dev/null || echo "$RESPONSE"
echo ""

# 응답 분석
if echo "$RESPONSE" | grep -q '"success":true'; then
    echo "✅ 업로드 성공!"
    URL=$(echo "$RESPONSE" | jq -r '.data.url' 2>/dev/null)
    if [ "$URL" != "null" ] && [ -n "$URL" ]; then
        echo "🔗 이미지 URL: $URL"
        
        # URL 접근 테스트
        echo "🌐 URL 접근 테스트..."
        HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" "https://www.topmktx.com$URL")
        echo "HTTP 상태 코드: $HTTP_STATUS"
        
        if [ "$HTTP_STATUS" = "200" ]; then
            echo "✅ 이미지 접근 가능"
        else
            echo "❌ 이미지 접근 불가"
        fi
    fi
else
    echo "❌ 업로드 실패"
    echo "오류 메시지: $(echo "$RESPONSE" | jq -r '.message' 2>/dev/null)"
fi

# 정리
rm -f $TEST_IMAGE $COOKIE_FILE

echo ""
echo "🏁 테스트 완료"