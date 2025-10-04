<?php
/**
 * 공지사항 페이지 공통 헤더 컴포넌트
 *
 * @version 2.0.0 - GradientHeader 컴포넌트 사용
 * @created 2025-10-04
 */

require_once SRC_PATH . '/components/ui/GradientHeader.php';

echo renderGradientHeader([
    'title' => '📢 공지사항',
    'subtitle' => '중요한 소식과 업데이트를 확인하세요',
    'theme' => 'purple',
    'size' => 'md',
    'align' => 'center'
]);
?>
