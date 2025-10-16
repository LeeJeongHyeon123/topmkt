<?php
/**
 * 이벤트 페이지 공통 헤더 컴포넌트
 * 캘린더 뷰와 목록 뷰에서 동일하게 사용
 *
 * @version 2.0.0 - GradientHeader 컴포넌트 사용
 * @created 2025-09-26
 * @updated 2025-10-04
 */

require_once SRC_PATH . '/components/ui/GradientHeader.php';

echo renderGradientHeader([
    'title' => '🎉 행사 일정',
    'subtitle' => '다양한 마케팅 행사와 네트워킹 행사에 참여하세요',
    'theme' => 'purple',
    'size' => 'md',
    'align' => 'center'
]);
?>