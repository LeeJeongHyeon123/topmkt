<?php
/**
 * Gradient Header Component
 *
 * 보라색 그라디언트 배경을 가진 페이지 헤더 컴포넌트
 *
 * @package Components\UI
 * @version 1.0.0
 * @since 2025-10-04
 */

/**
 * 그라디언트 헤더 렌더링
 *
 * @param array $options 헤더 옵션
 *   - title: string (필수) 헤더 제목
 *   - subtitle: string (선택) 부제목
 *   - badge: string (선택) 배지 텍스트
 *   - badgeIcon: string (선택) 배지 아이콘 (이모지)
 *   - theme: string (선택) 색상 테마 (purple|blue|green|orange) 기본: purple
 *   - size: string (선택) 크기 (sm|md|lg|xl) 기본: md
 *   - align: string (선택) 정렬 (left|center|right) 기본: left
 *   - className: string (선택) 추가 CSS 클래스
 *   - style: string (선택) 추가 인라인 스타일
 *
 * @return string 렌더링된 HTML
 *
 * @example
 * <?= renderGradientHeader([
 *     'title' => '2025년 최신 마케팅 전략',
 *     'subtitle' => '글로벌 마케팅 전문가가 알려주는 실전 전략',
 *     'badge' => '온라인',
 *     'badgeIcon' => '📚'
 * ]) ?>
 */
function renderGradientHeader($options = []) {
    // 필수 옵션 검증
    if (empty($options['title'])) {
        trigger_error('GradientHeader: title is required', E_USER_WARNING);
        return '';
    }

    // 기본값 설정
    $defaults = [
        'title' => '',
        'subtitle' => '',
        'badge' => '',
        'badgeIcon' => '',
        'theme' => 'purple',
        'size' => 'md',
        'align' => 'left',
        'className' => '',
        'style' => ''
    ];

    $options = array_merge($defaults, $options);

    // 테마별 그라디언트 색상
    $themes = [
        'purple' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        'blue' => 'linear-gradient(135deg, #4299e1 0%, #3182ce 100%)',
        'green' => 'linear-gradient(135deg, #48bb78 0%, #38a169 100%)',
        'orange' => 'linear-gradient(135deg, #ed8936 0%, #dd6b20 100%)',
        'red' => 'linear-gradient(135deg, #f56565 0%, #e53e3e 100%)',
        'pink' => 'linear-gradient(135deg, #ed64a6 0%, #d53f8c 100%)'
    ];

    $gradient = $themes[$options['theme']] ?? $themes['purple'];

    // 크기별 패딩 설정
    $sizes = [
        'sm' => 'padding: 40px 30px;',
        'md' => 'padding: 60px 40px;',
        'lg' => 'padding: 80px 40px;',
        'xl' => 'padding: 100px 50px;'
    ];

    $sizePadding = $sizes[$options['size']] ?? $sizes['md'];

    // 정렬 스타일
    $alignStyles = [
        'left' => 'text-align: left;',
        'center' => 'text-align: center;',
        'right' => 'text-align: right;'
    ];

    $alignStyle = $alignStyles[$options['align']] ?? $alignStyles['left'];

    // HTML 이스케이프
    $title = htmlspecialchars($options['title'], ENT_QUOTES, 'UTF-8');
    $subtitle = htmlspecialchars($options['subtitle'], ENT_QUOTES, 'UTF-8');
    $badge = htmlspecialchars($options['badge'], ENT_QUOTES, 'UTF-8');
    $badgeIcon = $options['badgeIcon']; // 이모지는 이스케이프 불필요

    // 클래스 구성
    $classes = ['gradient-header'];
    if (!empty($options['className'])) {
        $classes[] = $options['className'];
    }
    $classString = implode(' ', $classes);

    // 스타일 구성
    $styles = [
        "background: {$gradient}",
        'color: white',
        $sizePadding,
        'border-radius: 12px',
        'box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05)',
        'border: 1px solid rgba(255, 255, 255, 0.1)',
        'position: relative',
        'overflow: hidden',
        $alignStyle
    ];

    if (!empty($options['style'])) {
        $styles[] = $options['style'];
    }

    $styleString = implode('; ', $styles);

    // HTML 생성
    $html = "<div class=\"{$classString}\" style=\"{$styleString}\">\n";

    // 배지 렌더링
    if (!empty($badge)) {
        $badgeStyle = 'display: inline-block; padding: 6px 12px; background: rgba(255, 255, 255, 0.2); border-radius: 20px; font-size: 0.85rem; font-weight: 600; margin-bottom: 15px;';
        $html .= "    <div class=\"gradient-header-badge\" style=\"{$badgeStyle}\">";
        if (!empty($badgeIcon)) {
            $html .= "{$badgeIcon} ";
        }
        $html .= "{$badge}</div>\n";
    }

    // 제목 렌더링
    $titleStyle = 'margin: 0 0 10px 0; font-size: 2.5rem; font-weight: 700; line-height: 1.2;';
    if ($options['size'] === 'sm') {
        $titleStyle = 'margin: 0 0 8px 0; font-size: 1.8rem; font-weight: 700; line-height: 1.2;';
    } elseif ($options['size'] === 'lg') {
        $titleStyle = 'margin: 0 0 12px 0; font-size: 3rem; font-weight: 700; line-height: 1.2;';
    } elseif ($options['size'] === 'xl') {
        $titleStyle = 'margin: 0 0 15px 0; font-size: 3.5rem; font-weight: 700; line-height: 1.2;';
    }

    $html .= "    <h1 class=\"gradient-header-title\" style=\"{$titleStyle}\">{$title}</h1>\n";

    // 부제목 렌더링
    if (!empty($subtitle)) {
        $subtitleStyle = 'margin: 0; font-size: 1.1rem; opacity: 0.9; line-height: 1.5;';
        if ($options['size'] === 'sm') {
            $subtitleStyle = 'margin: 0; font-size: 0.95rem; opacity: 0.9; line-height: 1.5;';
        } elseif ($options['size'] === 'lg' || $options['size'] === 'xl') {
            $subtitleStyle = 'margin: 0; font-size: 1.25rem; opacity: 0.9; line-height: 1.5;';
        }

        $html .= "    <p class=\"gradient-header-subtitle\" style=\"{$subtitleStyle}\">{$subtitle}</p>\n";
    }

    $html .= "</div>\n";

    return $html;
}

/**
 * 간단한 그라디언트 헤더 렌더링 (제목만)
 *
 * @param string $title 헤더 제목
 * @param string $size 크기 (sm|md|lg|xl) 기본: md
 * @return string 렌더링된 HTML
 *
 * @example
 * <?= renderSimpleGradientHeader('페이지 제목') ?>
 */
function renderSimpleGradientHeader($title, $size = 'md') {
    return renderGradientHeader([
        'title' => $title,
        'size' => $size
    ]);
}

/**
 * 그라디언트 섹션 헤더 렌더링 (작은 크기의 섹션 헤더)
 *
 * @param string $title 섹션 제목
 * @param string $subtitle 섹션 부제목 (선택)
 * @return string 렌더링된 HTML
 *
 * @example
 * <?= renderGradientSectionHeader('통계 요약', '최근 30일간의 데이터') ?>
 */
function renderGradientSectionHeader($title, $subtitle = '') {
    return renderGradientHeader([
        'title' => $title,
        'subtitle' => $subtitle,
        'size' => 'sm',
        'align' => 'center'
    ]);
}
?>
