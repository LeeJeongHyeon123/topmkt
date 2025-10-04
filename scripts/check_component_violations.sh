#!/bin/bash

##############################################
# 탑마케팅 컴포넌트 미사용 감지 스크립트
# 
# 목적: 개발자가 컴포넌트를 사용하지 않고
#       직접 HTML/CSS를 작성했는지 자동 감지
#
# 사용법: ./scripts/check_component_violations.sh
##############################################

PROJECT_ROOT="/var/www/html/topmkt"
SRC_DIR="${PROJECT_ROOT}/src/views"
COMPONENT_DIR="${PROJECT_ROOT}/src/components"

# 검사 제외 패턴 (백업/테스트 파일)
EXCLUDE_PATTERN=".*_backup\.php|.*_fixed\.php|.*_direct\.php|.*_simple\.php|.*_test\.php"

# 색상 정의
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo ""
echo "========================================"
echo "🔍 탑마케팅 컴포넌트 미사용 감지 시작"
echo "========================================"
echo ""

VIOLATIONS_FOUND=0

##############################################
# 1. 버튼 직접 코딩 감지
##############################################

echo "${BLUE}[1/5]${NC} 버튼 직접 코딩 감지 중..."
echo ""

# btn-primary 직접 사용 감지 (Button.php 제외, 백업/테스트 파일 제외)
BTN_PRIMARY=$(grep -rn 'class="btn-primary"' ${SRC_DIR}/ 2>/dev/null | grep -v "Button.php" | grep -v "renderButton" | grep -vE "${EXCLUDE_PATTERN}")

if [ ! -z "$BTN_PRIMARY" ]; then
    echo "${RED}❌ btn-primary 직접 사용 발견:${NC}"
    echo "$BTN_PRIMARY" | while read line; do
        echo "   $line"
    done
    echo ""
    echo "${YELLOW}💡 해결: renderButton('텍스트', 'primary') 사용${NC}"
    echo ""
    VIOLATIONS_FOUND=1
fi

# btn-secondary 직접 사용 감지 (백업/테스트 파일 제외)
BTN_SECONDARY=$(grep -rn 'class="btn-secondary"' ${SRC_DIR}/ 2>/dev/null | grep -v "Button.php" | grep -v "renderButton" | grep -vE "${EXCLUDE_PATTERN}")

if [ ! -z "$BTN_SECONDARY" ]; then
    echo "${RED}❌ btn-secondary 직접 사용 발견:${NC}"
    echo "$BTN_SECONDARY" | while read line; do
        echo "   $line"
    done
    echo ""
    echo "${YELLOW}💡 해결: renderButton('텍스트', 'secondary') 사용${NC}"
    echo ""
    VIOLATIONS_FOUND=1
fi

# btn-danger 직접 사용 감지 (백업/테스트 파일 제외)
BTN_DANGER=$(grep -rn 'class="btn-danger"' ${SRC_DIR}/ 2>/dev/null | grep -v "Button.php" | grep -v "renderButton" | grep -vE "${EXCLUDE_PATTERN}")

if [ ! -z "$BTN_DANGER" ]; then
    echo "${RED}❌ btn-danger 직접 사용 발견:${NC}"
    echo "$BTN_DANGER" | while read line; do
        echo "   $line"
    done
    echo ""
    echo "${YELLOW}💡 해결: renderButton('텍스트', 'danger') 사용${NC}"
    echo ""
    VIOLATIONS_FOUND=1
fi

##############################################
# 2. 모달 직접 코딩 감지
##############################################

echo "${BLUE}[2/5]${NC} 모달 직접 코딩 감지 중..."
echo ""

# openModal, closeModal 함수 직접 정의 감지 (백업/테스트 파일 제외 + Modal 컴포넌트 구현 전까지 임시 제외)
MODAL_FUNCTIONS=$(grep -rn 'function openModal\|function closeModal' ${SRC_DIR}/ 2>/dev/null | grep -v "Modal.php" | grep -vE "${EXCLUDE_PATTERN}" | grep -v "admin/users/list.php" | grep -v "admin/corporate/pending.php")

if [ ! -z "$MODAL_FUNCTIONS" ]; then
    echo "${RED}❌ 모달 함수 직접 정의 발견:${NC}"
    echo "$MODAL_FUNCTIONS" | while read line; do
        echo "   $line"
    done
    echo ""
    echo "${YELLOW}💡 해결: Modal.php 컴포넌트 사용 (예정)${NC}"
    echo ""
    VIOLATIONS_FOUND=1
fi

##############################################
# 3. 알림 직접 코딩 감지
##############################################

echo "${BLUE}[3/5]${NC} 알림 직접 코딩 감지 중..."
echo ""

# alert-success, alert-error 직접 사용 감지 (백업/테스트 파일 제외)
ALERT_CLASSES=$(grep -rn 'class="alert-success\|class="alert-error\|class="alert-warning' ${SRC_DIR}/ 2>/dev/null | grep -v "Alert.php" | grep -vE "${EXCLUDE_PATTERN}")

if [ ! -z "$ALERT_CLASSES" ]; then
    echo "${RED}❌ 알림 클래스 직접 사용 발견:${NC}"
    echo "$ALERT_CLASSES" | while read line; do
        echo "   $line"
    done
    echo ""
    echo "${YELLOW}💡 해결: Alert.php 컴포넌트 사용 (예정)${NC}"
    echo ""
    VIOLATIONS_FOUND=1
fi

##############################################
# 4. 그라디언트 헤더 직접 코딩 감지
##############################################

echo "${BLUE}[4/5]${NC} 그라디언트 헤더 직접 코딩 감지 중..."
echo ""

# linear-gradient 인라인 스타일 감지 (백업/테스트 파일 제외 + GradientHeader 컴포넌트 구현 전까지 임시 제외)
GRADIENT_INLINE=$(grep -rn 'style=.*linear-gradient.*667eea.*764ba2' ${SRC_DIR}/ 2>/dev/null | grep -v "GradientHeader.php" | grep -vE "${EXCLUDE_PATTERN}" | grep -v "chat/index.php")

if [ ! -z "$GRADIENT_INLINE" ]; then
    echo "${RED}❌ 그라디언트 인라인 스타일 발견:${NC}"
    echo "$GRADIENT_INLINE" | while read line; do
        echo "   $line"
    done
    echo ""
    echo "${YELLOW}💡 해결: GradientHeader.php 컴포넌트 사용 (예정)${NC}"
    echo ""
    VIOLATIONS_FOUND=1
fi

##############################################
# 5. 컴포넌트 import 누락 감지
##############################################

echo "${BLUE}[5/5]${NC} 컴포넌트 import 누락 감지 중..."
echo ""

# renderButton 사용하지만 Button.php import 안한 파일 (백업/테스트 파일 제외)
MISSING_IMPORT=$(grep -rl 'renderButton(' ${SRC_DIR}/ 2>/dev/null | grep -vE "${EXCLUDE_PATTERN}" | while read file; do
    if ! grep -q "require.*Button.php" "$file" 2>/dev/null; then
        echo "$file"
    fi
done)

if [ ! -z "$MISSING_IMPORT" ]; then
    echo "${RED}❌ Button.php import 누락:${NC}"
    echo "$MISSING_IMPORT" | while read line; do
        echo "   $line"
    done
    echo ""
    echo "${YELLOW}💡 해결: 파일 상단에 추가${NC}"
    echo "   <?php require_once SRC_PATH . '/components/ui/Button.php'; ?>"
    echo ""
    VIOLATIONS_FOUND=1
fi

##############################################
# 최종 결과
##############################################

echo "========================================"

if [ $VIOLATIONS_FOUND -eq 0 ]; then
    echo "${GREEN}✅ 컴포넌트 미사용 없음! 완벽합니다!${NC}"
else
    echo "${RED}⚠️  컴포넌트 미사용 발견됨${NC}"
    echo ""
    echo "위에 나온 파일들을 수정하세요:"
    echo "  1. 직접 작성한 버튼/모달/알림을 컴포넌트로 교체"
    echo "  2. 컴포넌트 import 추가"
    echo "  3. 인라인 스타일 제거"
    echo ""
    echo "📖 자세한 사용법: /docs/23.컴포넌트_사용_가이드.md"
fi

echo "========================================"
echo ""

exit $VIOLATIONS_FOUND
