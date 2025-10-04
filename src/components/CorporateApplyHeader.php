<?php
/**
 * 기업 인증 신청 페이지 공통 헤더 컴포넌트
 *
 * @version 2.0.0 - GradientHeader 컴포넌트 사용
 * @created 2025-10-04
 */

require_once SRC_PATH . '/components/ui/GradientHeader.php';

$title = $isReapply ? '🔄 기업 인증 재신청' : '📝 기업 인증 신청';
$subtitle = $isReapply ? '거절 사유를 보완하여 다시 신청해주세요.' : '강의와 행사를 등록하기 위해 기업 인증을 신청하세요.';

echo renderGradientHeader([
    'title' => $title,
    'subtitle' => $subtitle,
    'theme' => 'green',
    'size' => 'md',
    'align' => 'center'
]);
?>
