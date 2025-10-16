<?php
/**
 * 기업회원 시스템 안내 페이지 공통 헤더 컴포넌트
 *
 * @version 2.0.0 - GradientHeader 컴포넌트 사용
 * @created 2025-10-04
 */

require_once SRC_PATH . '/components/ui/GradientHeader.php';

echo renderGradientHeader([
    'title' => '🏢 기업회원 시스템',
    'subtitle' => '강의와 행사를 등록하고 참가자를 관리하세요',
    'theme' => 'purple',
    'size' => 'md',
    'align' => 'center'
]);
?>
