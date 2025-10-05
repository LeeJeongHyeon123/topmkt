#!/bin/bash

##############################################
# 탑마케팅 컴포넌트 미사용 감지 스크립트
#
# 목적: 개발자가 컴포넌트를 사용하지 않고
#       직접 HTML/CSS를 작성했는지 자동 감지
#
# 검증 항목 (v3.42.0):
#   1. Button 컴포넌트 (renderButton)
#   2. Modal 컴포넌트 (renderModal, renderConfirmModal)
#   3. Alert/Toast 컴포넌트
#   4. GradientHeader 컴포넌트
#   5. CharacterCounter 클래스 (v3.29.0)
#   6. 컴포넌트 import 누락
#   7. Toast 알림 (v3.30.0)
#   8. Loading 인디케이터 (v3.31.0)
#   9. SearchFilter 컴포넌트 (v3.37.0)
#  10. Pagination 컴포넌트 (v3.39.0)
#  11. UploadConfig 시스템 (v3.39.0)
#  12. Modal.confirm() 확인 다이얼로그 (v3.40.0)
#  13. FormValidator 검증 클래스 (v3.41.0)
#  14. ApiClient HTTP 클라이언트 (v3.42.0) ⭐ NEW
#
# 사용법: ./scripts/check_component_violations.sh
# Git Hook: .git/hooks/pre-commit에서 자동 실행
#
# 참고: Card 컴포넌트는 상황에 따라 사용/미사용이 모두 적합할 수 있어
#       검증 항목에서 제외되었습니다. 자세한 내용은
#       /docs/Card_Component_Migration_Guidelines.md 참조
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

echo "${BLUE}[1/13]${NC} 버튼 직접 코딩 감지 중..."
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

echo "${BLUE}[2/13]${NC} 모달 직접 코딩 감지 중..."
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

echo "${BLUE}[3/13]${NC} 알림 직접 코딩 감지 중..."
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

echo "${BLUE}[4/13]${NC} 그라디언트 헤더 직접 코딩 감지 중..."
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

echo "${BLUE}[5/13]${NC} 글자 수 카운터 직접 코딩 감지 중..."
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

echo "${BLUE}[6/13]${NC} 컴포넌트 import 누락 감지 중..."
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

echo "${BLUE}[7/13]${NC} Toast 알림 직접 코딩 감지 중..."
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
# 8. Loading 인디케이터 직접 코딩 감지 (v3.31.0)
##############################################

echo "${BLUE}[8/13]${NC} Loading 인디케이터 직접 코딩 감지 중..."
echo ""

# setLoading, showLoading, hideLoading 함수 직접 정의 감지
# loading.js.php는 제외, 백업/테스트 파일 제외
# lectures/create.php, community/write.php의 기존 함수는 예외 (오버레이 관리 포함)
LOADING_FUNCTIONS=$(grep -rn 'function setLoading\|function showLoading\|function hideLoading' ${SRC_DIR}/ 2>/dev/null | \
    grep -v "loading.js.php" | \
    grep -v "v3.31.0.*제거" | \
    grep -v "lectures/create.php" | \
    grep -v "community/write.php" | \
    grep -vE "${EXCLUDE_PATTERN}")

if [ ! -z "$LOADING_FUNCTIONS" ]; then
    echo "${RED}❌ Loading 함수 직접 정의 발견:${NC}"
    echo "$LOADING_FUNCTIONS" | while read line; do
        echo "   $line"
    done
    echo ""
    echo "${YELLOW}💡 해결: Loading 클래스 사용${NC}"
    echo "   Loading.button(buttonElement, true, { text: '처리 중...' })"
    echo "   Loading.button(buttonElement, false)"
    echo "   Loading.buttons([btn1, btn2], true)"
    echo ""
    VIOLATIONS_FOUND=1
fi

# 버튼 innerHTML에 fa-spinner 직접 작성 감지 (Loading 클래스 사용 안한 경우)
BUTTON_SPINNER=$(grep -rn '\.innerHTML.*fa-spinner fa-spin' ${SRC_DIR}/ 2>/dev/null | \
    grep -v "loading.js.php" | \
    grep -v "v3.31.0" | \
    grep -v "Loading.button" | \
    grep -vE "${EXCLUDE_PATTERN}")

if [ ! -z "$BUTTON_SPINNER" ]; then
    echo "${RED}❌ 버튼 로딩 innerHTML 직접 작성 발견:${NC}"
    echo "$BUTTON_SPINNER" | while read line; do
        echo "   $line"
    done
    echo ""
    echo "${YELLOW}💡 해결: Loading 클래스 사용${NC}"
    echo "   Loading.button(buttonElement, true, { text: '처리 중...' })"
    echo ""
    VIOLATIONS_FOUND=1
fi

##############################################
# 9. SearchFilter 컴포넌트 직접 코딩 감지 (v3.37.0)
##############################################

echo "${BLUE}[9/13]${NC} SearchFilter 직접 코딩 감지 중..."
echo ""

# 검색/필터 폼 직접 작성 패턴 감지 (SearchFilter::create 사용 안한 경우)
# 1. search-filter-grid 클래스를 수동으로 작성한 경우
SEARCH_FILTER_GRID=$(grep -rn '<div class="search-filter-grid' ${SRC_DIR}/ 2>/dev/null | \
    grep -v "SearchFilter.php" | \
    grep -v "SearchFilter::create" | \
    grep -vE "${EXCLUDE_PATTERN}")

if [ ! -z "$SEARCH_FILTER_GRID" ]; then
    echo "${RED}❌ search-filter-grid 직접 작성 발견:${NC}"
    echo "$SEARCH_FILTER_GRID" | while read line; do
        echo "   $line"
    done
    echo ""
    echo "${YELLOW}💡 해결: SearchFilter::create() 사용${NC}"
    echo "   SearchFilter::create(['layout' => 'grid-3', 'filters' => [...]])"
    echo ""
    VIOLATIONS_FOUND=1
fi

# 2. search-filter-inline-row 클래스를 수동으로 작성한 경우
SEARCH_FILTER_INLINE=$(grep -rn '<div class="search-filter-inline-row' ${SRC_DIR}/ 2>/dev/null | \
    grep -v "SearchFilter.php" | \
    grep -v "SearchFilter::create" | \
    grep -vE "${EXCLUDE_PATTERN}")

if [ ! -z "$SEARCH_FILTER_INLINE" ]; then
    echo "${RED}❌ search-filter-inline-row 직접 작성 발견:${NC}"
    echo "$SEARCH_FILTER_INLINE" | while read line; do
        echo "   $line"
    done
    echo ""
    echo "${YELLOW}💡 해결: SearchFilter::create() 사용${NC}"
    echo "   SearchFilter::create(['layout' => 'inline', 'filters' => [...]])"
    echo ""
    VIOLATIONS_FOUND=1
fi

# 3. 필터 폼에 중복된 CSS 인라인 스타일 (v3.37.0에서 제거된 패턴)
FILTER_INLINE_CSS=$(grep -rn 'display: grid.*grid-template-columns.*filter' ${SRC_DIR}/ 2>/dev/null | \
    grep -v "SearchFilter.php" | \
    grep -v "search-filter.css" | \
    grep -v "v3.37.0.*제거" | \
    grep -vE "${EXCLUDE_PATTERN}")

if [ ! -z "$FILTER_INLINE_CSS" ]; then
    echo "${YELLOW}⚠️  필터 인라인 CSS 발견 (중복 가능성):${NC}"
    echo "$FILTER_INLINE_CSS" | while read line; do
        echo "   $line"
    done
    echo ""
    echo "${YELLOW}💡 해결: search-filter.css 사용 (중앙 관리)${NC}"
    echo ""
    # 경고만 하고 차단하지는 않음
fi

# 4. SearchFilter::create 사용하지만 SearchFilter.php import 안한 파일
MISSING_SEARCHFILTER_IMPORT=$(grep -rl 'SearchFilter::create(' ${SRC_DIR}/ 2>/dev/null | grep -vE "${EXCLUDE_PATTERN}" | while read file; do
    if ! grep -q "require.*SearchFilter.php\|include.*SearchFilter.php" "$file" 2>/dev/null; then
        echo "$file"
    fi
done)

if [ ! -z "$MISSING_SEARCHFILTER_IMPORT" ]; then
    echo "${RED}❌ SearchFilter.php import 누락:${NC}"
    echo "$MISSING_SEARCHFILTER_IMPORT" | while read line; do
        echo "   $line"
    done
    echo ""
    echo "${YELLOW}💡 해결: 파일 상단에 추가${NC}"
    echo "   <?php require_once SRC_PATH . '/components/ui/SearchFilter.php'; ?>"
    echo ""
    VIOLATIONS_FOUND=1
fi

# 5. 필터 검색 폼에서 오래된 패턴 감지 (filter-container, filter-group 등)
OLD_FILTER_PATTERN=$(grep -rn 'class="filter-container\|class="filter-group' ${SRC_DIR}/ 2>/dev/null | \
    grep -v "SearchFilter.php" | \
    grep -v "search-filter.css" | \
    grep -v "v3.37.0.*마이그레이션" | \
    grep -vE "${EXCLUDE_PATTERN}")

if [ ! -z "$OLD_FILTER_PATTERN" ]; then
    echo "${YELLOW}⚠️  오래된 필터 패턴 발견 (마이그레이션 권장):${NC}"
    echo "$OLD_FILTER_PATTERN" | while read line; do
        echo "   $line"
    done
    echo ""
    echo "${YELLOW}💡 마이그레이션: SearchFilter::create() 사용${NC}"
    echo "   - layout: inline, grid-2, grid-3, grid-4"
    echo "   - 15개 설정 옵션 지원"
    echo "   - 자동 반응형 및 접근성 지원"
    echo ""
    # 경고만 하고 차단하지는 않음
fi


##############################################
# 10. Pagination 컴포넌트 직접 코딩 감지 (v3.39.0)
##############################################

echo "${BLUE}[10/13]${NC} Pagination 컴포넌트 직접 코딩 감지 중..."
echo ""

# 1. PHP 서버사이드 수동 페이지네이션 패턴 감지
# (JavaScript 동적 페이지네이션은 제외 - admin/users/*.php는 AJAX 기반이므로 정상)
MANUAL_PAGINATION_PHP=$(grep -rn '<div class="pagination\|class="page-link' ${SRC_DIR}/ --include="*.php" 2>/dev/null | \
    grep -v "renderPagination" | \
    grep -v "Pagination.php" | \
    grep -v "admin/users/list" | \
    grep -v "pagination\.innerHTML" | \
    grep -vE "${EXCLUDE_PATTERN}")

if [ ! -z "$MANUAL_PAGINATION_PHP" ]; then
    echo "${RED}❌ PHP 수동 페이지네이션 발견 (renderPagination() 사용 권장):${NC}"
    echo "$MANUAL_PAGINATION_PHP" | while read line; do
        echo "   $line"
    done
    echo ""
    echo "${YELLOW}💡 해결: renderPagination(\$currentPage, \$totalPages) 사용${NC}"
    echo ""
    VIOLATIONS_FOUND=1
fi

# 2. renderPagination() 사용하지만 Pagination.php import 안한 파일
MISSING_PAGINATION_IMPORT=$(grep -rl 'renderPagination(' ${SRC_DIR}/ 2>/dev/null | grep -vE "${EXCLUDE_PATTERN}" | while read file; do
    if ! grep -q "require.*Pagination.php\|include.*Pagination.php" "$file" 2>/dev/null; then
        echo "$file"
    fi
done)

if [ ! -z "$MISSING_PAGINATION_IMPORT" ]; then
    echo "${RED}❌ Pagination 컴포넌트 사용하지만 import 누락:${NC}"
    echo "$MISSING_PAGINATION_IMPORT" | while read line; do
        echo "   $line"
    done
    echo ""
    echo "${YELLOW}💡 해결: require_once SRC_PATH . '/components/ui/Pagination.php';${NC}"
    echo ""
    VIOLATIONS_FOUND=1
fi

echo "${GREEN}✅ Pagination 컴포넌트 검증 완료${NC}"
echo ""

##############################################
# 11. UploadConfig 시스템 직접 코딩 감지 (v3.39.0)
##############################################

echo "${BLUE}[11/13]${NC} UploadConfig 시스템 직접 코딩 감지 중..."
echo ""

# 1. 하드코딩된 파일 크기 제한 감지 (upload-config.js.php 사용 안한 경우)
HARDCODED_FILE_SIZE=$(grep -rn 'file\.size.*1024.*1024\|maxSize.*MB' ${SRC_DIR}/ --include="*.php" 2>/dev/null | \
    grep -v "upload-config.js.php" | \
    grep -v "validateFileSize" | \
    grep -v "// " | \
    grep -vE "${EXCLUDE_PATTERN}")

if [ ! -z "$HARDCODED_FILE_SIZE" ]; then
    echo "${YELLOW}⚠️  하드코딩된 파일 크기 제한 발견:${NC}"
    echo "$HARDCODED_FILE_SIZE" | while read line; do
        echo "   $line"
    done | head -5
    echo ""
    echo "${YELLOW}💡 권장: window.validateFileSize() 사용 (upload-config.js.php)${NC}"
    echo ""
    # 경고만 하고 차단하지는 않음
fi

# 2. 하드코딩된 MIME 타입 검증 감지
HARDCODED_MIME=$(grep -rn 'image/jpeg.*image/png.*image/gif\|allowedTypes.*=.*\[' ${SRC_DIR}/ --include="*.php" 2>/dev/null | \
    grep -v "upload-config.js.php" | \
    grep -v "validateImageExtension" | \
    grep -vE "${EXCLUDE_PATTERN}")

if [ ! -z "$HARDCODED_MIME" ]; then
    echo "${YELLOW}⚠️  하드코딩된 MIME 타입 검증 발견:${NC}"
    echo "$HARDCODED_MIME" | while read line; do
        echo "   $line"
    done | head -5
    echo ""
    echo "${YELLOW}💡 권장: window.validateImageExtension() 사용 (upload-config.js.php)${NC}"
    echo ""
    # 경고만 하고 차단하지는 않음
fi

echo "${GREEN}✅ UploadConfig 시스템 검증 완료${NC}"
echo ""

##############################################
# 12. Modal.confirm() 확인 다이얼로그 감지 (v3.40.0)
##############################################

echo "${BLUE}[12/13]${NC} Modal.confirm() 확인 다이얼로그 감지 중..."
echo ""

# 1. 네이티브 confirm() 사용 감지 (Modal.confirm() 사용 안한 경우)
# - window.confirm( 패턴
# - if (confirm( 패턴
# - const result = confirm( 패턴
# 제외: confirmLogout, confirmPassword 등 변수명, 주석
NATIVE_CONFIRM=$(grep -rn '\bconfirm(' ${SRC_DIR}/ --include="*.php" 2>/dev/null | \
    grep -v "Modal.confirm" | \
    grep -v "confirmLogout\|confirmPassword\|confirmed" | \
    grep -v "// confirm\|<!-- confirm" | \
    grep -vE "${EXCLUDE_PATTERN}")

if [ ! -z "$NATIVE_CONFIRM" ]; then
    echo "${RED}❌ 네이티브 confirm() 사용 발견:${NC}"
    echo "$NATIVE_CONFIRM" | while read line; do
        echo "   $line"
    done | head -10
    echo ""
    echo "${YELLOW}💡 해결: Modal.confirm('메시지', { type: 'warning' }) 사용${NC}"
    echo ""
    VIOLATIONS_FOUND=1
fi

# 2. Modal.confirm() 사용하지만 footer.php import 누락 확인 (선택 사항)
# footer.php에 Modal.confirm() 스크립트가 포함되어 있으므로
# footer.php include 확인은 생략 (모든 페이지가 footer 포함)

echo "${GREEN}✅ Modal.confirm() 검증 완료${NC}"
echo ""

##############################################
# 13. FormValidator 검증 클래스 감지 (v3.41.0)
##############################################

echo "${BLUE}[13/13]${NC} FormValidator 검증 클래스 감지 중..."
echo ""

# 1. 중복 검증 함수 감지 (FormValidator 사용 안한 경우)
# - function isValidEmail( 패턴
# - function isValidPhone( 패턴
# - function validatePassword( 패턴 (UI 업데이트 포함된 로컬 함수는 제외)
# 제외: form-validator.js.php 자체, 주석

# isValidEmail 중복 함수
DUPLICATE_IS_VALID_EMAIL=$(grep -rn 'function isValidEmail\|function isValidEmailFormat' ${SRC_DIR}/ --include="*.php" 2>/dev/null | \
    grep -v "form-validator.js.php" | \
    grep -v "// function\|<!-- function" | \
    grep -v "v3.41.0.*FormValidator" | \
    grep -vE "${EXCLUDE_PATTERN}")

if [ ! -z "$DUPLICATE_IS_VALID_EMAIL" ]; then
    echo "${YELLOW}⚠️  중복 이메일 검증 함수 발견:${NC}"
    echo "$DUPLICATE_IS_VALID_EMAIL" | while read line; do
        echo "   $line"
    done | head -5
    echo ""
    echo "${YELLOW}💡 권장: FormValidator.isValidEmail() 사용${NC}"
    echo ""
    # 경고만 하고 차단하지는 않음
fi

# isValidPhone 중복 함수
DUPLICATE_IS_VALID_PHONE=$(grep -rn 'function isValidPhone\|function isValidPhoneFormat' ${SRC_DIR}/ --include="*.php" 2>/dev/null | \
    grep -v "form-validator.js.php" | \
    grep -v "// function\|<!-- function" | \
    grep -v "v3.41.0.*FormValidator" | \
    grep -vE "${EXCLUDE_PATTERN}")

if [ ! -z "$DUPLICATE_IS_VALID_PHONE" ]; then
    echo "${YELLOW}⚠️  중복 전화번호 검증 함수 발견:${NC}"
    echo "$DUPLICATE_IS_VALID_PHONE" | while read line; do
        echo "   $line"
    done | head -5
    echo ""
    echo "${YELLOW}💡 권장: FormValidator.isValidPhone() 또는 FormValidator.isValidPhoneStrict() 사용${NC}"
    echo ""
    # 경고만 하고 차단하지는 않음
fi

echo "${GREEN}✅ FormValidator 검증 완료${NC}"
echo ""

##############################################
# 14. ApiClient HTTP 클라이언트 감지 (v3.42.0)
##############################################

echo "${BLUE}[14/14]${NC} ApiClient HTTP 클라이언트 감지 중..."

# fetch() 직접 사용 감지 (ApiClient 사용 권장)
DIRECT_FETCH=$(grep -rn 'fetch(' ${SRC_DIR}/ --include="*.php" 2>/dev/null | \
    grep -v "api-client.js.php" | \
    grep -v "// fetch\|<!-- fetch\|* fetch" | \
    grep -v "v3.42.0.*ApiClient" | \
    grep -vE "${EXCLUDE_PATTERN}")

if [ ! -z "$DIRECT_FETCH" ]; then
    echo "${YELLOW}⚠️  fetch() 직접 사용 발견:${NC}"
    echo "$DIRECT_FETCH" | while read line; do
        echo "   $line"
    done | head -10
    echo "${YELLOW}💡 권장: ApiClient.get() / ApiClient.post() / ApiClient.put() / ApiClient.delete() 사용${NC}"
fi

# XMLHttpRequest 직접 사용 감지
DIRECT_XHR=$(grep -rn 'new XMLHttpRequest\|XMLHttpRequest()' ${SRC_DIR}/ --include="*.php" 2>/dev/null | \
    grep -v "api-client.js.php" | \
    grep -v "// XMLHttpRequest\|<!-- XMLHttpRequest" | \
    grep -vE "${EXCLUDE_PATTERN}")

if [ ! -z "$DIRECT_XHR" ]; then
    echo "${YELLOW}⚠️  XMLHttpRequest 직접 사용 발견:${NC}"
    echo "$DIRECT_XHR" | while read line; do
        echo "   $line"
    done | head -5
    echo "${YELLOW}💡 권장: ApiClient 사용 (자동 에러 처리, 토큰 주입)${NC}"
fi

echo "${GREEN}✅ ApiClient 검증 완료${NC}"
echo ""

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
