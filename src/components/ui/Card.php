<?php
/**
 * Card 컴포넌트 (v3.38.0)
 *
 * 재사용 가능한 카드 UI 컴포넌트 시스템
 *
 * 지원하는 카드 타입:
 * - feature: 기능 소개 카드 (홈페이지 기능 섹션)
 * - stat: 통계 카드 (대시보드, 통계 표시)
 * - sidebar: 사이드바 카드 (강의/행사 상세 페이지)
 * - profile: 프로필 정보 카드 (사용자 프로필)
 * - info: 정보 카드 (일반 정보 표시)
 * - instructor: 강사 카드 (강사 정보 표시)
 *
 * 사용 예시:
 * ```php
 * // Stat Card (통계 카드)
 * echo Card::stat([
 *     'icon' => '👥',
 *     'value' => '1,234',
 *     'label' => '총 회원 수',
 *     'variant' => 'primary'  // primary, success, warning, danger
 * ]);
 *
 * // Feature Card (기능 카드)
 * echo Card::feature([
 *     'icon' => 'fas fa-users',
 *     'iconBg' => 'blue',  // blue, green, purple, orange
 *     'title' => '커뮤니티 네트워킹',
 *     'description' => '전 세계 전문가들과 연결...',
 *     'link' => '/community',
 *     'linkText' => '시작하기'
 * ]);
 *
 * // Sidebar Card (사이드바 카드)
 * echo Card::sidebar([
 *     'title' => '🎫 신청 정보',
 *     'content' => '<div>...</div>'
 * ]);
 *
 * // Profile Card (프로필 카드)
 * echo Card::profile([
 *     'title' => '📝 자기소개',
 *     'content' => '안녕하세요...'
 * ]);
 * ```
 *
 * @package Components\UI
 * @version 3.38.0
 * @since 2025-10-05
 */

class Card
{
    /**
     * Stat Card 생성 (통계 카드)
     *
     * @param array $options 카드 옵션
     *   - icon: 아이콘 (이모지 또는 텍스트)
     *   - value: 통계 값
     *   - label: 레이블 텍스트
     *   - variant: 색상 테마 (primary, success, warning, danger)
     *   - change: 변화량 표시 (옵션)
     *   - changeType: 변화 타입 (positive, negative)
     * @return string HTML 문자열
     */
    public static function stat(array $options): string
    {
        $icon = $options['icon'] ?? '📊';
        $value = $options['value'] ?? '0';
        $label = $options['label'] ?? '';
        $variant = $options['variant'] ?? 'primary';
        $change = $options['change'] ?? null;
        $changeType = $options['changeType'] ?? 'positive';

        $changeHtml = '';
        if ($change) {
            $changeHtml = '<div class="stat-change ' . htmlspecialchars($changeType) . '">' . $change . '</div>';
        }

        return <<<HTML
        <div class="stat-card {$variant}">
            <div class="stat-header">
                <div class="stat-icon">{$icon}</div>
            </div>
            <div class="stat-value">{$value}</div>
            <div class="stat-label">{$label}</div>
            {$changeHtml}
        </div>
        HTML;
    }

    /**
     * Feature Card 생성 (기능 소개 카드)
     *
     * @param array $options 카드 옵션
     *   - icon: Lucide 아이콘명 (예: 'star', 'users') 또는 Font Awesome 클래스 (자동 변환)
     *   - iconSize: 아이콘 크기 (기본: 32)
     *   - iconBg: 아이콘 배경 색상 (blue, green, purple, orange)
     *   - title: 제목
     *   - description: 설명
     *   - link: 링크 URL
     *   - linkText: 링크 텍스트
     * @return string HTML 문자열
     */
    public static function feature(array $options): string
    {
        // 아이콘 처리 (Font Awesome → Lucide 자동 변환)
        $iconInput = $options['icon'] ?? 'star';
        $iconSize = $options['iconSize'] ?? 32;

        // Font Awesome 감지 및 변환
        $lucideIcon = $iconInput;
        if (strpos($iconInput, 'fa-') !== false || strpos($iconInput, 'fas ') !== false) {
            $faToLucideMap = [
                'fas fa-star' => 'star',
                'fas fa-users' => 'users',
                'fas fa-graduation-cap' => 'graduation-cap',
                'fas fa-calendar' => 'calendar',
                'fas fa-calendar-alt' => 'calendar',
                'fas fa-comments' => 'message-square',
                'fas fa-heart' => 'heart',
                'fas fa-rocket' => 'rocket',
                'fas fa-bullhorn' => 'megaphone'
            ];

            $lucideIcon = $faToLucideMap[$iconInput] ?? str_replace(['fas fa-', 'far fa-', 'fa-'], '', $iconInput);
        }

        $iconBg = $options['iconBg'] ?? 'blue';
        $title = $options['title'] ?? '';
        $description = $options['description'] ?? '';
        $link = $options['link'] ?? '#';
        $linkText = $options['linkText'] ?? '자세히 보기';

        return <<<HTML
        <div class="feature-card">
            <div class="feature-icon">
                <div class="icon-bg {$iconBg}">
                    <i data-lucide="{$lucideIcon}" width="{$iconSize}" height="{$iconSize}"></i>
                </div>
            </div>
            <h3>{$title}</h3>
            <p>{$description}</p>
            <a href="{$link}" class="feature-link">
                <span>{$linkText}</span>
                <i data-lucide="arrow-right" width="18" height="18"></i>
            </a>
        </div>
        HTML;
    }

    /**
     * Sidebar Card 생성 (사이드바 카드)
     *
     * @param array $options 카드 옵션
     *   - title: 카드 제목 (이모지 포함 가능)
     *   - content: 카드 내용 (HTML)
     *   - class: 추가 CSS 클래스
     * @return string HTML 문자열
     */
    public static function sidebar(array $options): string
    {
        $title = $options['title'] ?? '';
        $content = $options['content'] ?? '';
        $class = $options['class'] ?? '';

        return <<<HTML
        <div class="sidebar-card {$class}">
            <h3 class="sidebar-title">{$title}</h3>
            {$content}
        </div>
        HTML;
    }

    /**
     * Profile Card 생성 (프로필 정보 카드)
     *
     * @param array $options 카드 옵션
     *   - title: 카드 제목
     *   - content: 카드 내용 (HTML)
     *   - icon: 제목 아이콘 (옵션)
     * @return string HTML 문자열
     */
    public static function profile(array $options): string
    {
        $title = $options['title'] ?? '';
        $content = $options['content'] ?? '';
        $icon = $options['icon'] ?? '';

        $titleWithIcon = $icon ? "{$icon} {$title}" : $title;

        return <<<HTML
        <div class="profile-card">
            <div class="card-title">{$titleWithIcon}</div>
            {$content}
        </div>
        HTML;
    }

    /**
     * Info Card 생성 (정보 카드)
     *
     * @param array $options 카드 옵션
     *   - title: 카드 제목
     *   - content: 카드 내용 (HTML)
     *   - variant: 색상 테마 (옵션)
     * @return string HTML 문자열
     */
    public static function info(array $options): string
    {
        $title = $options['title'] ?? '';
        $content = $options['content'] ?? '';
        $variant = $options['variant'] ?? '';

        return <<<HTML
        <div class="info-card {$variant}">
            <h3 class="info-title">{$title}</h3>
            <div class="info-content">{$content}</div>
        </div>
        HTML;
    }

    /**
     * Instructor Card 생성 (강사 카드)
     *
     * @param array $options 카드 옵션
     *   - name: 강사 이름
     *   - title: 강사 직함
     *   - image: 프로필 이미지 경로
     *   - bio: 강사 소개 (옵션)
     * @return string HTML 문자열
     */
    public static function instructor(array $options): string
    {
        $name = $options['name'] ?? '';
        $title = $options['title'] ?? '';
        $image = $options['image'] ?? '/assets/images/default-avatar.png';
        $bio = $options['bio'] ?? '';

        $bioHtml = $bio ? "<p class=\"instructor-bio\">{$bio}</p>" : '';

        return <<<HTML
        <div class="instructor-card">
            <div class="instructor-avatar">
                <img src="{$image}" alt="{$name}" loading="lazy">
            </div>
            <div class="instructor-info">
                <h4 class="instructor-name">{$name}</h4>
                <p class="instructor-title">{$title}</p>
                {$bioHtml}
            </div>
        </div>
        HTML;
    }

    /**
     * Generic Card 생성 (범용 카드)
     *
     * @param array $options 카드 옵션
     *   - content: 카드 내용 (HTML)
     *   - class: 추가 CSS 클래스
     *   - style: 인라인 스타일 (옵션)
     * @return string HTML 문자열
     */
    public static function generic(array $options): string
    {
        $content = $options['content'] ?? '';
        $class = $options['class'] ?? '';
        $style = $options['style'] ?? '';

        $styleAttr = $style ? " style=\"{$style}\"" : '';

        return <<<HTML
        <div class="card {$class}"{$styleAttr}>
            {$content}
        </div>
        HTML;
    }
}
