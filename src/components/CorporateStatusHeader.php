<?php
/**
 * 기업 인증 현황 페이지 공통 헤더 컴포넌트
 *
 * @version 2.0.0 - GradientHeader 컴포넌트 사용
 * @created 2025-10-04
 */

require_once SRC_PATH . '/components/ui/GradientHeader.php';

echo renderGradientHeader([
    'title' => '📊 기업 인증 현황',
    'subtitle' => '기업 인증 신청 내역과 상태를 확인하세요',
    'theme' => 'purple',
    'size' => 'md',
    'align' => 'center'
]);
?>
