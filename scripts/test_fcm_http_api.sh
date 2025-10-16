#!/bin/bash
#
# FCM RESTful API HTTP 엔드포인트 테스트
# 실제 curl로 API 호출하여 검증
#
# 사용법: ./scripts/test_fcm_http_api.sh [SESSION_COOKIE]
#

# 색상 정의
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 기본 URL
BASE_URL="https://www.topmktx.com"
API_BASE="${BASE_URL}/api/fcm"

# 세션 쿠키 (파라미터로 받거나 환경 변수)
SESSION_COOKIE="${1:-$FCM_TEST_SESSION}"

echo ""
echo "========================================"
echo "FCM RESTful API HTTP 테스트"
echo "========================================"
echo ""

# 테스트 통계
TOTAL_TESTS=0
PASSED_TESTS=0
FAILED_TESTS=0

# 테스트 결과 출력 함수
print_test_result() {
    local test_name="$1"
    local passed="$2"
    local message="$3"

    TOTAL_TESTS=$((TOTAL_TESTS + 1))

    if [ "$passed" = "true" ]; then
        echo -e "${GREEN}✅ ${test_name}${NC}"
        PASSED_TESTS=$((PASSED_TESTS + 1))
    else
        echo -e "${RED}❌ ${test_name}${NC}"
        FAILED_TESTS=$((FAILED_TESTS + 1))
    fi

    if [ -n "$message" ]; then
        echo "   └─ $message"
    fi
}

# JSON 파싱 함수 (jq 없이)
extract_json_value() {
    local json="$1"
    local key="$2"
    echo "$json" | grep -o "\"$key\":[^,}]*" | sed 's/.*://; s/"//g'
}

# 세션 쿠키 확인
if [ -z "$SESSION_COOKIE" ]; then
    echo -e "${YELLOW}⚠️  세션 쿠키가 제공되지 않았습니다.${NC}"
    echo ""
    echo "사용법:"
    echo "  1. 브라우저에서 로그인 후 개발자 도구 > Application > Cookies에서 PHPSESSID 확인"
    echo "  2. 다음과 같이 실행:"
    echo "     ./scripts/test_fcm_http_api.sh \"PHPSESSID=your_session_id\""
    echo ""
    echo "또는:"
    echo "  export FCM_TEST_SESSION=\"PHPSESSID=your_session_id\""
    echo "  ./scripts/test_fcm_http_api.sh"
    echo ""
    echo -e "${BLUE}📝 세션 없이 진행 가능한 테스트만 실행합니다...${NC}"
    echo ""
fi

# CSRF 토큰 가져오기
echo "1️⃣  CSRF 토큰 가져오기"
echo "========================================"
echo ""

if [ -n "$SESSION_COOKIE" ]; then
    CSRF_RESPONSE=$(curl -s -b "$SESSION_COOKIE" "${BASE_URL}/api/csrf-token")
    CSRF_TOKEN=$(extract_json_value "$CSRF_RESPONSE" "token")

    if [ -n "$CSRF_TOKEN" ] && [ "$CSRF_TOKEN" != "null" ]; then
        print_test_result "CSRF 토큰 획득" "true" "토큰: ${CSRF_TOKEN:0:20}..."
    else
        print_test_result "CSRF 토큰 획득" "false" "응답: $CSRF_RESPONSE"
        echo ""
        echo -e "${RED}❌ CSRF 토큰을 가져올 수 없어 테스트를 중단합니다.${NC}"
        exit 1
    fi
else
    echo -e "${YELLOW}⚠️  세션 쿠키가 없어 CSRF 토큰을 가져올 수 없습니다.${NC}"
    echo ""
fi

echo ""
echo "2️⃣  POST /api/fcm/tokens (토큰 등록)"
echo "========================================"
echo ""

# 테스트용 FCM 토큰 생성
TEST_TOKEN="http_test_fcm_token_$(date +%s)"

if [ -n "$SESSION_COOKIE" ] && [ -n "$CSRF_TOKEN" ]; then
    # 테스트 2-1: Android 토큰 등록
    echo "테스트 2-1: Android 토큰 등록"
    REGISTER_RESPONSE=$(curl -s -X POST \
        -b "$SESSION_COOKIE" \
        -H "Content-Type: application/json" \
        "${API_BASE}/tokens" \
        -d "{
            \"csrf_token\": \"$CSRF_TOKEN\",
            \"fcm_token\": \"${TEST_TOKEN}\",
            \"device_type\": \"android\",
            \"device_name\": \"Test Device (curl)\",
            \"app_version\": \"1.0.0\"
        }")

    STATUS=$(extract_json_value "$REGISTER_RESPONSE" "status")
    MESSAGE=$(extract_json_value "$REGISTER_RESPONSE" "message")

    if [ "$STATUS" = "success" ]; then
        print_test_result "POST /api/fcm/tokens (Android)" "true" "메시지: $MESSAGE"
    else
        print_test_result "POST /api/fcm/tokens (Android)" "false" "응답: $REGISTER_RESPONSE"
    fi

    echo ""

    # 테스트 2-2: iOS 토큰 등록
    echo "테스트 2-2: iOS 토큰 등록"
    TEST_TOKEN_IOS="${TEST_TOKEN}_ios"
    REGISTER_IOS_RESPONSE=$(curl -s -X POST \
        -b "$SESSION_COOKIE" \
        -H "Content-Type: application/json" \
        "${API_BASE}/tokens" \
        -d "{
            \"csrf_token\": \"$CSRF_TOKEN\",
            \"fcm_token\": \"${TEST_TOKEN_IOS}\",
            \"device_type\": \"ios\",
            \"device_name\": \"iPhone Test (curl)\",
            \"app_version\": \"1.0.0\"
        }")

    STATUS=$(extract_json_value "$REGISTER_IOS_RESPONSE" "status")

    if [ "$STATUS" = "success" ]; then
        print_test_result "POST /api/fcm/tokens (iOS)" "true" "iOS 토큰 등록 성공"
    else
        print_test_result "POST /api/fcm/tokens (iOS)" "false" "응답: $REGISTER_IOS_RESPONSE"
    fi

    echo ""

    # 테스트 2-3: 잘못된 device_type
    echo "테스트 2-3: 잘못된 device_type (에러 예상)"
    INVALID_RESPONSE=$(curl -s -X POST \
        -b "$SESSION_COOKIE" \
        -H "Content-Type: application/json" \
        "${API_BASE}/tokens" \
        -d "{
            \"csrf_token\": \"$CSRF_TOKEN\",
            \"fcm_token\": \"invalid_${TEST_TOKEN}\",
            \"device_type\": \"windows\",
            \"device_name\": \"Windows PC\",
            \"app_version\": \"1.0.0\"
        }")

    STATUS=$(extract_json_value "$INVALID_RESPONSE" "status")

    if [ "$STATUS" = "error" ]; then
        print_test_result "잘못된 device_type 거부" "true" "올바르게 거부됨"
    else
        print_test_result "잘못된 device_type 거부" "false" "응답: $INVALID_RESPONSE"
    fi
else
    echo -e "${YELLOW}⚠️  세션/CSRF 토큰이 없어 건너뜁니다.${NC}"
fi

echo ""
echo "3️⃣  GET /api/fcm/tokens (토큰 목록 조회)"
echo "========================================"
echo ""

if [ -n "$SESSION_COOKIE" ]; then
    GET_RESPONSE=$(curl -s -X GET \
        -b "$SESSION_COOKIE" \
        "${API_BASE}/tokens")

    STATUS=$(extract_json_value "$GET_RESPONSE" "status")
    COUNT=$(extract_json_value "$GET_RESPONSE" "count")

    if [ "$STATUS" = "success" ]; then
        print_test_result "GET /api/fcm/tokens" "true" "조회된 토큰 수: $COUNT개"
    else
        print_test_result "GET /api/fcm/tokens" "false" "응답: $GET_RESPONSE"
    fi
else
    echo -e "${YELLOW}⚠️  세션 쿠키가 없어 건너뜁니다.${NC}"
fi

echo ""
echo "4️⃣  DELETE /api/fcm/tokens (토큰 삭제)"
echo "========================================"
echo ""

if [ -n "$SESSION_COOKIE" ] && [ -n "$CSRF_TOKEN" ]; then
    # Android 토큰 삭제
    echo "테스트 4-1: Android 토큰 삭제"
    DELETE_RESPONSE=$(curl -s -X DELETE \
        -b "$SESSION_COOKIE" \
        -H "Content-Type: application/json" \
        "${API_BASE}/tokens" \
        -d "{
            \"csrf_token\": \"$CSRF_TOKEN\",
            \"fcm_token\": \"${TEST_TOKEN}\"
        }")

    STATUS=$(extract_json_value "$DELETE_RESPONSE" "status")

    if [ "$STATUS" = "success" ]; then
        print_test_result "DELETE /api/fcm/tokens (Android)" "true" "토큰 삭제 성공"
    else
        print_test_result "DELETE /api/fcm/tokens (Android)" "false" "응답: $DELETE_RESPONSE"
    fi

    echo ""

    # iOS 토큰 삭제
    echo "테스트 4-2: iOS 토큰 삭제"
    DELETE_IOS_RESPONSE=$(curl -s -X DELETE \
        -b "$SESSION_COOKIE" \
        -H "Content-Type: application/json" \
        "${API_BASE}/tokens" \
        -d "{
            \"csrf_token\": \"$CSRF_TOKEN\",
            \"fcm_token\": \"${TEST_TOKEN_IOS}\"
        }")

    STATUS=$(extract_json_value "$DELETE_IOS_RESPONSE" "status")

    if [ "$STATUS" = "success" ]; then
        print_test_result "DELETE /api/fcm/tokens (iOS)" "true" "iOS 토큰 삭제 성공"
    else
        print_test_result "DELETE /api/fcm/tokens (iOS)" "false" "응답: $DELETE_IOS_RESPONSE"
    fi

    echo ""

    # 존재하지 않는 토큰 삭제
    echo "테스트 4-3: 존재하지 않는 토큰 삭제"
    DELETE_NONEXIST_RESPONSE=$(curl -s -X DELETE \
        -b "$SESSION_COOKIE" \
        -H "Content-Type: application/json" \
        "${API_BASE}/tokens" \
        -d "{
            \"csrf_token\": \"$CSRF_TOKEN\",
            \"fcm_token\": \"nonexistent_token_$(date +%s)\"
        }")

    STATUS=$(extract_json_value "$DELETE_NONEXIST_RESPONSE" "status")

    # 존재하지 않는 토큰 삭제는 에러를 반환할 수 있음
    if [ "$STATUS" = "error" ] || [ "$STATUS" = "success" ]; then
        print_test_result "존재하지 않는 토큰 삭제" "true" "올바르게 처리됨 (status: $STATUS)"
    else
        print_test_result "존재하지 않는 토큰 삭제" "false" "응답: $DELETE_NONEXIST_RESPONSE"
    fi
else
    echo -e "${YELLOW}⚠️  세션/CSRF 토큰이 없어 건너뜁니다.${NC}"
fi

echo ""
echo "5️⃣  POST /api/fcm/push/test (테스트 푸시)"
echo "========================================"
echo ""

if [ -n "$SESSION_COOKIE" ] && [ -n "$CSRF_TOKEN" ]; then
    # 먼저 다시 토큰 등록 (이전에 삭제했으므로)
    curl -s -X POST \
        -b "$SESSION_COOKIE" \
        -H "Content-Type: application/json" \
        "${API_BASE}/tokens" \
        -d "{
            \"csrf_token\": \"$CSRF_TOKEN\",
            \"fcm_token\": \"${TEST_TOKEN}\",
            \"device_type\": \"android\",
            \"device_name\": \"Test Device (curl)\",
            \"app_version\": \"1.0.0\"
        }" > /dev/null

    echo "테스트 5-1: 테스트 푸시 전송"
    PUSH_RESPONSE=$(curl -s -X POST \
        -b "$SESSION_COOKIE" \
        -H "Content-Type: application/json" \
        "${API_BASE}/push/test" \
        -d "{
            \"csrf_token\": \"$CSRF_TOKEN\",
            \"title\": \"curl 테스트 알림\",
            \"body\": \"FCM API 테스트 중입니다.\"
        }")

    STATUS=$(extract_json_value "$PUSH_RESPONSE" "status")

    if [ "$STATUS" = "success" ]; then
        print_test_result "POST /api/fcm/push/test" "true" "테스트 푸시 전송 성공"
    elif [ "$STATUS" = "error" ]; then
        MESSAGE=$(extract_json_value "$PUSH_RESPONSE" "message")
        # APP_DEBUG가 false면 403 에러가 정상
        if [[ "$MESSAGE" == *"개발 환경"* ]]; then
            print_test_result "POST /api/fcm/push/test" "true" "운영 환경에서 올바르게 거부됨"
        else
            print_test_result "POST /api/fcm/push/test" "false" "응답: $PUSH_RESPONSE"
        fi
    else
        print_test_result "POST /api/fcm/push/test" "false" "응답: $PUSH_RESPONSE"
    fi

    # 테스트 토큰 정리
    curl -s -X DELETE \
        -b "$SESSION_COOKIE" \
        -H "Content-Type: application/json" \
        "${API_BASE}/tokens" \
        -d "{
            \"csrf_token\": \"$CSRF_TOKEN\",
            \"fcm_token\": \"${TEST_TOKEN}\"
        }" > /dev/null
else
    echo -e "${YELLOW}⚠️  세션/CSRF 토큰이 없어 건너뜁니다.${NC}"
fi

echo ""
echo "========================================"
echo "📊 테스트 결과 요약"
echo "========================================"
echo ""
echo "총 테스트: ${TOTAL_TESTS}개"
echo -e "${GREEN}✅ 통과: ${PASSED_TESTS}개${NC}"
if [ $FAILED_TESTS -gt 0 ]; then
    echo -e "${RED}❌ 실패: ${FAILED_TESTS}개${NC}"
fi
echo ""

if [ $TOTAL_TESTS -gt 0 ]; then
    SUCCESS_RATE=$(awk "BEGIN {printf \"%.1f\", ($PASSED_TESTS / $TOTAL_TESTS) * 100}")
    echo "성공률: ${SUCCESS_RATE}%"
else
    echo -e "${YELLOW}⚠️  실행된 테스트가 없습니다. 세션 쿠키를 제공하고 다시 실행하세요.${NC}"
fi

echo ""
echo "========================================"
echo "FCM HTTP API 테스트 완료"
echo "========================================"
echo ""

if [ $FAILED_TESTS -gt 0 ]; then
    exit 1
else
    exit 0
fi
