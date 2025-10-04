<?php
/**
 * 공지사항 작성/수정 페이지 공통 헤더 컴포넌트
 *
 * @version 2.0.0 - GradientHeader 컴포넌트 사용
 * @created 2025-10-04
 */

require_once SRC_PATH . '/components/ui/GradientHeader.php';

echo renderGradientHeader([
    'title' => '📢 ' . $pageTitle,
    'subtitle' => '중요한 소식을 공유해주세요',
    'theme' => 'purple',
    'size' => 'md',
    'align' => 'center'
]);
?>
