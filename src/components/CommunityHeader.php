<?php
/**
 * 커뮤니티 페이지 공통 헤더 컴포넌트
 *
 * @version 2.0.0 - GradientHeader 컴포넌트 사용
 * @created 2025-10-04
 */

require_once SRC_PATH . '/components/ui/GradientHeader.php';

echo renderGradientHeader([
    'title' => '<i data-lucide="message-square" width="32" height="32" style="display: inline; vertical-align: middle; margin-right: 10px;"></i>커뮤니티 게시판',
    'subtitle' => '탑마케팅 커뮤니티에서 정보를 공유하고 함께 성장하세요',
    'theme' => 'purple',
    'size' => 'md',
    'align' => 'center',
    'allowHtml' => true
]);
?>
