<script>
/* ========================================
   Lucide Icons Helper 함수
   v5.0.0 - Font Awesome → Lucide 전환
   ======================================== */

/**
 * Font Awesome → Lucide Icons 매핑 테이블 (105개)
 * 조사 결과: 95% 완벽 대응, 4% 유사 대응, 1% 대응 없음
 */
const FA_TO_LUCIDE_MAP = {
    // Top 30 아이콘 (사용 빈도 높음)
    'fas fa-times': 'x',
    'fas fa-check': 'check',
    'fas fa-user': 'user',
    'fas fa-info-circle': 'info',
    'fas fa-eye': 'eye',
    'fas fa-comments': 'message-square',
    'fas fa-check-circle': 'check-circle',
    'fas fa-lock': 'lock',
    'fas fa-users': 'users',
    'fas fa-search': 'search',
    'fas fa-rocket': 'rocket',
    'fas fa-plus': 'plus',
    'fas fa-spinner': 'loader-2',
    'fas fa-sign-in-alt': 'log-in',
    'fas fa-comment': 'message-circle',
    'fas fa-calendar-alt': 'calendar',
    'fas fa-arrow-left': 'arrow-left',
    'fas fa-graduation-cap': 'graduation-cap',
    'fas fa-edit': 'edit',
    'fas fa-clock': 'clock',
    'fas fa-user-circle': 'user-circle',
    'fas fa-trash': 'trash-2',
    'fas fa-shield-alt': 'shield',
    'fas fa-save': 'save',
    'fas fa-map-marker-alt': 'map-pin',
    'fas fa-exclamation-triangle': 'alert-triangle',
    'fas fa-envelope': 'mail',
    'fas fa-bell': 'bell',
    'fas fa-user-plus': 'user-plus',
    'fas fa-sign-out-alt': 'log-out',

    // 추가 아이콘 (31-105)
    'fas fa-paper-plane': 'send',
    'fas fa-mobile-alt': 'smartphone',
    'fas fa-images': 'images',
    'fas fa-home': 'home',
    'fas fa-bullhorn': 'megaphone',
    'fas fa-arrow-right': 'arrow-right',
    'fas fa-user-edit': 'user-cog',
    'fas fa-undo': 'undo',
    'fas fa-share-alt': 'share-2',
    'fas fa-key': 'key',
    'fas fa-heart': 'heart',
    'fas fa-grip-lines': 'grip-horizontal',
    'fas fa-globe': 'globe',
    'fas fa-eye-slash': 'eye-off',
    'fas fa-exclamation-circle': 'alert-circle',
    'fas fa-clipboard-list': 'clipboard-list',
    'fas fa-chevron-down': 'chevron-down',
    'fas fa-chart-line': 'trending-up',
    'fas fa-calendar': 'calendar',
    'fab fa-youtube': 'youtube',
    'fab fa-facebook': 'facebook',
    'fab fa-instagram': 'instagram',
    'fas fa-video': 'video',
    'fas fa-upload': 'upload',
    'fas fa-download': 'download',
    'fas fa-cog': 'settings',
    'fas fa-bars': 'menu',
    'fas fa-ellipsis-v': 'more-vertical',
    'fas fa-star': 'star',
    'fas fa-play': 'play',
    'fas fa-building': 'building',
    'fas fa-circle-notch': 'loader',
    'fas fa-chevron-up': 'chevron-up',
    'fas fa-chevron-right': 'chevron-right',
    'fas fa-chevron-left': 'chevron-left',
    'fas fa-times-circle': 'x-circle',
    'fas fa-plus-circle': 'plus-circle',
    'fas fa-minus-circle': 'minus-circle',
    'fas fa-question-circle': 'help-circle',
    'fas fa-file-alt': 'file-text',
    'fas fa-file': 'file',
    'fas fa-folder': 'folder',
    'fas fa-folder-open': 'folder-open',
    'fas fa-copy': 'copy',
    'fas fa-paste': 'clipboard',
    'fas fa-cut': 'scissors',
    'fas fa-link': 'link',
    'fas fa-external-link-alt': 'external-link',
    'fas fa-cloud-upload-alt': 'cloud-upload',
    'fas fa-cloud-download-alt': 'cloud-download',
    'fas fa-sync': 'refresh-cw',
    'fas fa-sync-alt': 'refresh-cw',
    'fas fa-redo': 'redo',
    'fas fa-filter': 'filter',
    'fas fa-sort': 'arrow-up-down',
    'fas fa-sort-up': 'arrow-up',
    'fas fa-sort-down': 'arrow-down',
    'fas fa-th': 'grid',
    'fas fa-th-list': 'list',
    'fas fa-th-large': 'layout-grid',
    'fas fa-tag': 'tag',
    'fas fa-tags': 'tags',
    'fas fa-bookmark': 'bookmark',
    'fas fa-flag': 'flag',
    'fas fa-thumbs-up': 'thumbs-up',
    'fas fa-thumbs-down': 'thumbs-down',
    'fas fa-share': 'share',
    'fas fa-reply': 'reply',
    'fas fa-forward': 'forward',
    'fas fa-print': 'printer',
    'fas fa-camera': 'camera',
    'fas fa-image': 'image',
    'fas fa-microphone': 'mic',
    'fas fa-volume-up': 'volume-2',
    'fas fa-volume-down': 'volume-1',
    'fas fa-volume-mute': 'volume-x',
    'fas fa-phone': 'phone',
    'fas fa-mobile': 'smartphone',
    'fas fa-envelope-open': 'mail-open',
    'fas fa-inbox': 'inbox',
    'fas fa-archive': 'archive',
    'fas fa-wifi': 'wifi',
    'fas fa-bluetooth': 'bluetooth',
    'fas fa-battery-full': 'battery',
    'fas fa-power-off': 'power',
    'fas fa-lightbulb': 'lightbulb',
    'fas fa-moon': 'moon',
    'fas fa-sun': 'sun',
    'fas fa-cloud': 'cloud',
    'fas fa-umbrella': 'umbrella',
    'fas fa-snowflake': 'snowflake',
    'fas fa-fire': 'flame',
    'fas fa-bolt': 'zap',
    'fas fa-bug': 'bug',
    'fas fa-code': 'code',
    'fas fa-terminal': 'terminal',
    'fas fa-database': 'database',
    'fas fa-server': 'server',
    'fas fa-sitemap': 'sitemap',
    'fas fa-chart-bar': 'bar-chart',
    'fas fa-chart-pie': 'pie-chart',
    'fas fa-chart-area': 'area-chart'
};

/**
 * 1. replaceLucideIcon - 기존 요소의 아이콘을 Lucide로 교체
 *
 * @param {HTMLElement} element - 아이콘을 교체할 DOM 요소
 * @param {string} iconName - Lucide 아이콘명 (예: 'Check', 'X', 'User')
 * @param {string} className - 추가 CSS 클래스 (선택)
 *
 * @example
 * // 비밀번호 표시/숨김 토글
 * const eyeIcon = document.getElementById('togglePassword');
 * replaceLucideIcon(eyeIcon, isVisible ? 'eye' : 'eye-off');
 */
window.replaceLucideIcon = function(element, iconName, className = '') {
    if (!element) {
        return;
    }

    element.innerHTML = '';
    const icon = document.createElement('i');
    icon.setAttribute('data-lucide', iconName);
    if (className) {
        icon.className = className;
    }
    element.appendChild(icon);

    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
};

/**
 * 2. createLucideIcon - 새로운 Lucide 아이콘 요소 생성 및 반환
 *
 * @param {string} iconName - Lucide 아이콘명
 * @param {string} className - CSS 클래스
 * @param {number} size - 아이콘 크기 (기본 24px)
 * @returns {HTMLElement} 생성된 아이콘 요소
 *
 * @example
 * // 버튼에 아이콘 추가
 * const saveButton = document.getElementById('saveBtn');
 * const saveIcon = createLucideIcon('save', 'mr-2', 20);
 * saveButton.prepend(saveIcon);
 */
window.createLucideIcon = function(iconName, className = '', size = 24) {
    const icon = document.createElement('i');
    icon.setAttribute('data-lucide', iconName);
    icon.setAttribute('width', size);
    icon.setAttribute('height', size);
    if (className) {
        icon.className = className;
    }

    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    return icon;
};

/**
 * 3. getLucideIconName - Font Awesome 클래스명을 Lucide 아이콘명으로 변환
 *
 * @param {string} fontAwesomeClass - Font Awesome 클래스 (예: 'fas fa-user')
 * @returns {string|null} Lucide 아이콘명 (예: 'user') 또는 null
 *
 * @example
 * // Font Awesome 클래스를 Lucide로 변환
 * const element = document.querySelector('.fas.fa-user');
 * const lucideName = getLucideIconName('fas fa-user'); // 'user'
 * replaceLucideIcon(element, lucideName);
 */
window.getLucideIconName = function(fontAwesomeClass) {
    return FA_TO_LUCIDE_MAP[fontAwesomeClass] || null;
};

/**
 * 4. toggleLucideIcon - 조건에 따라 두 아이콘 중 하나를 표시 (토글용)
 *
 * @param {HTMLElement} element - DOM 요소
 * @param {string} iconName1 - 조건이 true일 때 아이콘
 * @param {string} iconName2 - 조건이 false일 때 아이콘
 * @param {boolean} condition - boolean 조건
 *
 * @example
 * // 비밀번호 표시/숨김 토글
 * const isVisible = passwordInput.type === 'text';
 * toggleLucideIcon(eyeIcon, 'eye-off', 'eye', isVisible);
 */
window.toggleLucideIcon = function(element, iconName1, iconName2, condition) {
    const iconName = condition ? iconName1 : iconName2;
    window.replaceLucideIcon(element, iconName);
};

/**
 * 5. addLucideIconToButton - 버튼에 아이콘 추가
 *
 * @param {HTMLElement} button - 버튼 DOM 요소
 * @param {string} iconName - Lucide 아이콘명
 * @param {string} position - 'before' (앞) 또는 'after' (뒤)
 *
 * @example
 * // 저장 버튼에 아이콘 추가
 * const saveBtn = document.getElementById('saveBtn');
 * addLucideIconToButton(saveBtn, 'save', 'before');
 */
window.addLucideIconToButton = function(button, iconName, position = 'before') {
    if (!button) {
        return;
    }

    const icon = window.createLucideIcon(iconName, '', 20);

    if (position === 'before') {
        button.prepend(icon);
        button.prepend(document.createTextNode(' ')); // 간격
    } else {
        button.appendChild(document.createTextNode(' '));
        button.appendChild(icon);
    }
};

</script>
