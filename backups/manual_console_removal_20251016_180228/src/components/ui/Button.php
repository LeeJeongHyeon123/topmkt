<?php
/**
 * 버튼 컴포넌트
 * 
 * 탑마케팅 프로젝트의 모든 버튼을 통합 관리하는 재사용 가능한 컴포넌트
 * 
 * 사용 가능한 컴포넌트 목록:
 * - Button.php           : renderButton($text, $type, $size, $options)
 * - Modal.php            : renderModal($id, $title, $content)
 * - Alert.php            : showAlert($message, $type)
 * - GradientHeader.php   : renderHeader($title, $color)
 * 
 * 📖 자세한 사용법: /docs/23.컴포넌트_사용_가이드.md
 * 
 * @package TOPMKT
 * @subpackage Components\UI
 * @version 1.0.0
 * @since 2025-10-04
 */

/**
 * 버튼 렌더링 함수
 * 
 * @param string $text 버튼 텍스트
 * @param string $type 버튼 타입 (primary|secondary|danger|success|warning|info|outline)
 * @param string $size 버튼 크기 (sm|md|lg)
 * @param array $options 추가 옵션
 *   - id: 버튼 ID
 *   - class: 추가 CSS 클래스
 *   - onclick: JavaScript 클릭 이벤트
 *   - href: 링크 URL (a 태그로 렌더링)
 *   - icon: 아이콘 클래스 (예: 'fas fa-save')
 *   - iconPosition: 아이콘 위치 ('left'|'right')
 *   - fullWidth: 전체 너비 (true|false)
 *   - disabled: 비활성화 (true|false)
 *   - ariaLabel: 접근성 레이블
 *   - buttonType: HTML button type (submit|button|reset)
 *   - attributes: 기타 HTML 속성 배열
 * 
 * @return string 렌더링된 HTML
 * 
 * @example
 * // 기본 사용
 * <?= renderButton('저장', 'primary') ?>
 * 
 * // 크기 지정
 * <?= renderButton('취소', 'secondary', 'lg') ?>
 * 
 * // 아이콘 포함
 * <?= renderButton('삭제', 'danger', 'md', ['icon' => 'fas fa-trash']) ?>
 * 
 * // 링크 버튼
 * <?= renderButton('목록', 'secondary', 'md', ['href' => '/lectures']) ?>
 * 
 * // 전체 너비
 * <?= renderButton('로그인', 'primary', 'lg', ['fullWidth' => true]) ?>
 */
function renderButton($text, $type = 'primary', $size = 'md', $options = []) {
    // 기본값 설정
    $defaults = [
        'id' => '',
        'class' => '',
        'onclick' => '',
        'href' => '',
        'icon' => '',
        'iconPosition' => 'left',
        'fullWidth' => false,
        'disabled' => false,
        'ariaLabel' => '',
        'buttonType' => 'button',
        'attributes' => []
    ];
    
    $opts = array_merge($defaults, $options);
    
    // 버튼 타입 검증
    $validTypes = ['primary', 'secondary', 'danger', 'success', 'warning', 'info', 'outline'];
    if (!in_array($type, $validTypes)) {
        $type = 'primary';
    }
    
    // 버튼 크기 검증
    $validSizes = ['sm', 'md', 'lg'];
    if (!in_array($size, $validSizes)) {
        $size = 'md';
    }
    
    // CSS 클래스 생성
    $classes = ['btn', "btn-{$type}", "btn-{$size}"];
    
    if ($opts['fullWidth']) {
        $classes[] = 'btn-full';
    }
    
    if ($opts['class']) {
        $classes[] = $opts['class'];
    }
    
    $classString = implode(' ', $classes);
    
    // HTML 속성 생성
    $attributes = [];
    
    if ($opts['id']) {
        $attributes[] = 'id="' . htmlspecialchars($opts['id']) . '"';
    }
    
    $attributes[] = 'class="' . htmlspecialchars($classString) . '"';
    
    if ($opts['onclick']) {
        $attributes[] = 'onclick="' . htmlspecialchars($opts['onclick']) . '"';
    }
    
    if ($opts['disabled']) {
        $attributes[] = 'disabled';
    }
    
    if ($opts['ariaLabel']) {
        $attributes[] = 'aria-label="' . htmlspecialchars($opts['ariaLabel']) . '"';
    }
    
    // 추가 속성
    foreach ($opts['attributes'] as $key => $value) {
        if (is_bool($value)) {
            if ($value) {
                $attributes[] = htmlspecialchars($key);
            }
        } else {
            $attributes[] = htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
        }
    }
    
    $attrString = implode(' ', $attributes);
    
    // 아이콘 HTML 생성
    $iconHtml = '';
    if ($opts['icon']) {
        $iconHtml = '<i class="' . htmlspecialchars($opts['icon']) . '"></i>';
    }
    
    // 버튼 내용 생성
    $content = '';
    if ($iconHtml && $opts['iconPosition'] === 'left') {
        $content = $iconHtml . ' ' . htmlspecialchars($text);
    } elseif ($iconHtml && $opts['iconPosition'] === 'right') {
        $content = htmlspecialchars($text) . ' ' . $iconHtml;
    } else {
        $content = htmlspecialchars($text);
    }
    
    // 링크 버튼 vs 일반 버튼
    if ($opts['href']) {
        return "<a href=\"{$opts['href']}\" {$attrString}>{$content}</a>";
    } else {
        $buttonTypeAttr = 'type="' . htmlspecialchars($opts['buttonType']) . '"';
        return "<button {$buttonTypeAttr} {$attrString}>{$content}</button>";
    }
}

/**
 * 버튼 그룹 렌더링 함수
 * 
 * @param array $buttons 버튼 배열 (각 요소는 renderButton 파라미터)
 * @param array $options 그룹 옵션
 *   - align: 정렬 (left|center|right|space-between)
 *   - gap: 버튼 간격 (기본: 10px)
 *   - class: 추가 CSS 클래스
 * 
 * @return string 렌더링된 HTML
 * 
 * @example
 * <?= renderButtonGroup([
 *     ['저장', 'primary', 'md', ['buttonType' => 'submit']],
 *     ['취소', 'secondary', 'md', ['onclick' => 'history.back()']]
 * ], ['align' => 'center']) ?>
 */
function renderButtonGroup($buttons, $options = []) {
    $defaults = [
        'align' => 'left',
        'gap' => '10px',
        'class' => ''
    ];
    
    $opts = array_merge($defaults, $options);
    
    // 정렬 스타일
    $alignStyles = [
        'left' => 'justify-content: flex-start;',
        'center' => 'justify-content: center;',
        'right' => 'justify-content: flex-end;',
        'space-between' => 'justify-content: space-between;'
    ];
    
    $alignStyle = $alignStyles[$opts['align']] ?? $alignStyles['left'];
    
    $html = '<div class="btn-group ' . htmlspecialchars($opts['class']) . '" style="display: flex; gap: ' . htmlspecialchars($opts['gap']) . '; ' . $alignStyle . '">';
    
    foreach ($buttons as $btn) {
        $text = $btn[0] ?? '';
        $type = $btn[1] ?? 'primary';
        $size = $btn[2] ?? 'md';
        $btnOptions = $btn[3] ?? [];
        
        $html .= renderButton($text, $type, $size, $btnOptions);
    }
    
    $html .= '</div>';
    
    return $html;
}
