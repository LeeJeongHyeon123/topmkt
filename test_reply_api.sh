#!/bin/bash
# 커뮤니티 답글 작성 API 테스트 스크립트

echo "=== 커뮤니티 답글 작성 API 테스트 ==="

# 1. 세션 쿠키를 담을 파일
COOKIE_JAR="/tmp/topmkt_cookies.txt"

# 2. 로그인 (사용자 4번으로 로그인)
echo "1️⃣ 로그인 시도..."
curl -c "$COOKIE_JAR" -X POST "http://localhost/auth/login" \
     -H "Content-Type: application/x-www-form-urlencoded" \
     -d "email=2jeonghyeon2@naver.com&password=your_password_here" \
     -s -I | head -5

# 3. 커뮤니티 페이지 접속해서 CSRF 토큰 확보
echo "2️⃣ 커뮤니티 페이지 접속..."
RESPONSE=$(curl -b "$COOKIE_JAR" -s "http://localhost/community/posts/3")
echo "페이지 로드 상태: $(echo "$RESPONSE" | head -1)"

# CSRF 토큰 추출 (meta 태그에서)
CSRF_TOKEN=$(echo "$RESPONSE" | grep -o 'name="csrf-token" content="[^"]*"' | cut -d'"' -f4)
echo "추출된 CSRF 토큰: $CSRF_TOKEN"

# 4. 답글 작성 API 호출
echo "3️⃣ 답글 작성 API 호출..."

# 부모 댓글 ID (방금 생성한 79090)
PARENT_ID=79090
POST_ID=3

# API 호출 데이터
API_DATA="{\"post_id\":$POST_ID,\"parent_id\":$PARENT_ID,\"content\":\"테스트 답글 - $(date '+%Y-%m-%d %H:%M:%S')\"}"

echo "전송할 데이터: $API_DATA"

# API 호출 실행
API_RESPONSE=$(curl -b "$COOKIE_JAR" \
     -X POST "http://localhost/api/comments" \
     -H "Content-Type: application/json" \
     -H "X-CSRF-Token: $CSRF_TOKEN" \
     -d "$API_DATA" \
     -w "HTTP_CODE:%{http_code}\n" \
     -s)

echo "4️⃣ API 응답 결과:"
echo "$API_RESPONSE"

# HTTP 상태 코드 분석
HTTP_CODE=$(echo "$API_RESPONSE" | grep "HTTP_CODE:" | cut -d: -f2)
RESPONSE_BODY=$(echo "$API_RESPONSE" | grep -v "HTTP_CODE:")

echo ""
echo "📊 결과 분석:"
echo "HTTP 상태 코드: $HTTP_CODE"

case $HTTP_CODE in
    200)
        echo "✅ 성공: 답글이 정상적으로 작성되었습니다."
        ;;
    403)
        echo "🚨 403 Forbidden: 권한 없음 에러 발생"
        echo "가능한 원인:"
        echo "- CSRF 토큰 검증 실패"
        echo "- 로그인 상태 확인 실패"
        echo "- 권한 부족"
        ;;
    400)
        echo "❌ 400 Bad Request: 잘못된 요청 데이터"
        ;;
    401)
        echo "🔐 401 Unauthorized: 인증 실패"
        ;;
    500)
        echo "💥 500 Internal Server Error: 서버 내부 오류"
        ;;
    *)
        echo "❓ 예상치 못한 응답 코드: $HTTP_CODE"
        ;;
esac

echo ""
echo "응답 본문:"
echo "$RESPONSE_BODY" | python3 -m json.tool 2>/dev/null || echo "$RESPONSE_BODY"

# 5. 정리
rm -f "$COOKIE_JAR"

echo ""
echo "🏁 테스트 완료"