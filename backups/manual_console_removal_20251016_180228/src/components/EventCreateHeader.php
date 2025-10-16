<?php
/**
 * 행사 등록 페이지 공통 헤더 컴포넌트
 *
 * @version 2.0.0 - GradientHeader 컴포넌트 사용
 * @created 2025-10-04
 */

require_once SRC_PATH . '/components/ui/GradientHeader.php';

echo renderGradientHeader([
    'title' => '🎉 새로운 행사 등록',
    'subtitle' => '참가자들에게 의미있는 경험을 선사할 행사를 등록해보세요',
    'theme' => 'purple',
    'size' => 'md',
    'align' => 'center'
]);
?>
