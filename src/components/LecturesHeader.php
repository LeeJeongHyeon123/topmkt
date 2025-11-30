<?php
/**
 * 강의 페이지 공통 헤더 컴포넌트
 * 캘린더 뷰와 목록 뷰에서 동일하게 사용
 *
 * @version 2.0.0 - GradientHeader 컴포넌트 사용
 * @created 2025-10-04
 */

require_once SRC_PATH . '/components/ui/GradientHeader.php';

echo renderGradientHeader([
    'title' => '<i data-lucide="calendar" width="32" height="32" style="display: inline; vertical-align: middle; margin-right: 10px;"></i>강의 일정',
    'subtitle' => '다양한 마케팅 강의와 세미나 일정을 확인하고 신청하세요',
    'theme' => 'purple',
    'size' => 'md',
    'align' => 'center',
    'allowHtml' => true
]);
?>
