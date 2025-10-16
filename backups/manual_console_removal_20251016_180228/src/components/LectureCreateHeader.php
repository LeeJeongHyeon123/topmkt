<?php
/**
 * 강의 등록/수정 페이지 공통 헤더 컴포넌트
 *
 * @version 2.0.0 - GradientHeader 컴포넌트 사용
 * @created 2025-10-04
 */

require_once SRC_PATH . '/components/ui/GradientHeader.php';

$title = $isEditMode ? '✏️ 강의 수정' : '➕ 강의 등록';
$subtitle = $isEditMode ? '강의 정보를 수정하여 더 나은 내용을 제공하세요' : '새로운 강의나 세미나를 등록하여 많은 분들과 지식을 공유하세요';

echo renderGradientHeader([
    'title' => $title,
    'subtitle' => $subtitle,
    'theme' => 'purple',
    'size' => 'md',
    'align' => 'center'
]);
?>
