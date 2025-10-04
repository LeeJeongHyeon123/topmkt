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

# openModal, closeModal 함수 직접 정의 감지 (백업/테스트 파일 제외)
MODAL_FUNCTIONS=$(grep -rn 'function openModal\|function closeModal' ${SRC_DIR}/ 2>/dev/null | grep -v "Modal.php" | grep -vE "${EXCLUDE_PATTERN}")

if [ ! -z "$MODAL_FUNCTIONS" ]; then
    echo "${RED}❌ 모달 함수 직접 정의 발견:${NC}"
    echo "$MODAL_FUNCTIONS" | while read line; do
        echo "   $line"
    done
    echo ""
    echo "${YELLOW}💡 해결: Modal.php 컴포넌트 및 modal.js 사용${NC}"
    echo ""
    VIOLATIONS_FOUND=1
fi

# Modal HTML 직접 작성 감지 (renderModal 없이 <div class="modal" 사용)
MODAL_HTML=$(grep -rn '<div class="modal"' ${SRC_DIR}/ 2>/dev/null | grep -v "renderModal" | grep -v "Modal.php" | grep -vE "${EXCLUDE_PATTERN}")

if [ ! -z "$MODAL_HTML" ]; then
    echo "${RED}❌ 모달 HTML 직접 작성 발견:${NC}"
    echo "$MODAL_HTML" | while read line; do
        echo "   $line"
    done
    echo ""
    echo "${YELLOW}💡 해결: renderModal('id', '제목', '내용') 또는 renderConfirmModal() 사용${NC}"
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
# 5. 글자 수 카운터 직접 코딩 감지 (v3.29.0)
##############################################

echo "${BLUE}[5/7]${NC} 글자 수 카운터 직접 코딩 감지 중..."
echo ""

# updateCharCounter, updateCharacterCount 함수 직접 정의 감지 (CharacterCounter 클래스 제외, 백업/테스트 파일 제외)
# Quill 전용 (user/edit.php, community/write.php), 폼 검증 통합 (lectures/create.php)은 예외 처리
CHAR_COUNTER_FUNCTIONS=$(grep -rn 'function updateCharCounter\|function updateCharacterCount' ${SRC_DIR}/ 2>/dev/null | \
    grep -v "char-counter.js.php" | \
    grep -v "user/edit.php" | \
    grep -v "community/write.php:.*updateContentCharCounter" | \
    grep -v "lectures/create.php" | \
    grep -vE "${EXCLUDE_PATTERN}")

if [ ! -z "$CHAR_COUNTER_FUNCTIONS" ]; then
    echo "${RED}❌ 글자 수 카운터 함수 직접 정의 발견:${NC}"
    echo "$CHAR_COUNTER_FUNCTIONS" | while read line; do
        echo "   $line"
    done
    echo ""
    echo "${YELLOW}💡 해결: CharacterCounter 클래스 사용${NC}"
    echo "   new CharacterCounter(inputElement, counterElement, maxLength, options)"
    echo "   (Quill 전용/폼검증 통합은 예외)"
    echo ""
    VIOLATIONS_FOUND=1
fi

# .length, .textContent 직접 사용하는 수동 카운터 패턴 감지 (단, 합법적인 경우 제외)
MANUAL_COUNTER=$(grep -rn 'textContent.*\.length\|\.length.*textContent' ${SRC_DIR}/ 2>/dev/null | \
    grep -v "char-counter.js.php" | \
    grep -v "user/edit.php" | \
    grep -v "community/write.php" | \
    grep -v "lectures/create.php" | \
    grep -v "imageCounter" | \
    grep -v "Image" | \
    grep -v "length}개" | \
    grep -v "총.*length" | \
    grep -vE "${EXCLUDE_PATTERN}" | \
    head -5)  # 처음 5개만 표시 (너무 많을 수 있음)

if [ ! -z "$MANUAL_COUNTER" ]; then
    echo "${YELLOW}⚠️  수동 글자 수 카운터 패턴 발견 (확인 필요):${NC}"
    echo "$MANUAL_COUNTER" | while read line; do
        echo "   $line"
    done
    echo ""
    echo "${YELLOW}💡 확인: CharacterCounter 클래스로 교체 가능한지 검토${NC}"
    echo ""
    # 경고만 하고 차단하지는 않음 (false positive 가능)
fi

##############################################
# 6. 컴포넌트 import 누락 감지
##############################################

echo "${BLUE}[6/7]${NC} 컴포넌트 import 누락 감지 중..."
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

# renderModal 사용하지만 Modal.php import 안한 파일 (백업/테스트 파일 제외)
# 주석에 있는 것은 제외하고 실제 함수 호출만 감지
MISSING_MODAL_IMPORT=$(grep -rl 'renderModal(' ${SRC_DIR}/ 2>/dev/null | grep -vE "${EXCLUDE_PATTERN}" | while read file; do
    # 주석이 아닌 실제 사용만 확인
    if grep 'renderModal(' "$file" | grep -qv "^[[:space:]]*\*" && ! grep -q "require.*Modal.php" "$file" 2>/dev/null; then
        echo "$file"
    fi
done)

if [ ! -z "$MISSING_MODAL_IMPORT" ]; then
    echo "${RED}❌ Modal.php import 누락:${NC}"
    echo "$MISSING_MODAL_IMPORT" | while read line; do
        echo "   $line"
    done
    echo ""
    echo "${YELLOW}💡 해결: 파일 상단에 추가${NC}"
    echo "   <?php require_once SRC_PATH . '/components/ui/Modal.php'; ?>"
    echo "   <script src=\"/assets/js/modal.js\"></script>"
    echo ""
    VIOLATIONS_FOUND=1
fi

##############################################
# 7. Toast 알림 직접 코딩 감지 (v3.30.0)
##############################################

echo "${BLUE}[7/7]${NC} Toast 알림 직접 코딩 감지 중..."
echo ""

# showMessage, showAlert, showSuccessMessage, showErrorMessage 함수 직접 정의 감지
# toast.js.php는 제외, 백업/테스트 파일 제외
TOAST_FUNCTIONS=$(grep -rn 'function showMessage\|function showAlert\|function showSuccessMessage\|function showErrorMessage' ${SRC_DIR}/ 2>/dev/null | \
    grep -v "toast.js.php" | \
    grep -v "v3.30.0.*제거" | \
    grep -vE "${EXCLUDE_PATTERN}")

if [ ! -z "$TOAST_FUNCTIONS" ]; then
    echo "${RED}❌ Toast 메시지 함수 직접 정의 발견:${NC}"
    echo "$TOAST_FUNCTIONS" | while read line; do
        echo "   $line"
    done
    echo ""
    echo "${YELLOW}💡 해결: Toast 클래스 사용${NC}"
    echo "   Toast.success('메시지')"
    echo "   Toast.error('메시지')"
    echo "   Toast.warning('메시지')"
    echo "   Toast.info('메시지')"
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
