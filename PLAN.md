# 작업 플랜 (PLAN)

**프로젝트**: 탑마케팅 (TOPMKT)
**경로**: `/var/www/html/topmkt`
**버전**: v5.0.0 (예정)
**작업명**: Font Awesome → Lucide Icons 전환
**작업 일자**: 2025-11-20
**작업자**: Claude (Anthropic)

---

## 📌 Ultra Think 7단계 분석

### 1단계: 문제 정의
**현재 상황**:
- Font Awesome 6.4.0 사용 중 (338개 인스턴스, 105개 고유 아이콘)
- 사용자 피드백: "Font Awesome이 촌스럽다"
- 목표: 깔끔하고 세련된 홈페이지로 리뉴얼

**전환 목표**:
- Font Awesome → Lucide Icons 완전 전환
- 번들 사이즈 60KB 감소 (80KB → 20KB)
- 현대적이고 미니멀한 디자인 달성

### 2단계: 데이터 수집
**Font Awesome 사용 현황**:
- 총 사용: 338개 인스턴스
- 영향 파일: 63개 (71개 뷰 파일 중)
- Top 5 아이콘: `fa-times`(38), `fa-check`(17), `fa-user`(11), `fa-info-circle`(11), `fa-eye`(10)
- 동적 아이콘: 17개 파일 (주로 auth, user 페이지)
- 이모지 혼용: 343개 (아이콘과 별도)

**페이지별 분포**:
- 유저 페이지: 243개 (auth 86, events 46, lectures 29 등)
- 관리자 페이지: 6개
- 공통 컴포넌트: 31개 (header, footer)
- JavaScript 컴포넌트: 10개

**Lucide 매핑 분석**:
- ✅ 완벽 대응: 100개 (95%)
- ⚠️ 유사 대응: 4개 (4%)
- ❌ 대응 없음: 1개 (1%) - `fab fa-tiktok`

### 3단계: 근본 원인
**Font Awesome의 한계**:
- 번들 사이즈 큰 편 (80KB gzip)
- 디자인 스타일이 2010년대 초반 느낌
- 아이콘 일관성 부족 (solid, regular, light 혼재)

**Lucide Icons의 장점**:
- 현대적이고 미니멀한 디자인 (Feather Icons 기반)
- 가벼운 번들 사이즈 (필요한 아이콘만 로드 시 ~20KB)
- 일관된 24x24px 그리드 시스템
- Tree-shaking 지원
- React, Vue, Svelte, Angular 등 프레임워크 지원

### 4단계: 해결 전략
**사용자 선택 반영**:
1. **전환 범위**: POC 먼저 → 검증 → 전체 진행
2. **우선순위**: 공통 컴포넌트 → 테스트 페이지 → 전체 페이지
3. **동적 아이콘**: Helper 함수 작성 (replaceIcon 등)
4. **이모지 처리**: 중요 이모지 일부 Lucide 전환

**6단계 로드맵**:
- Phase 1: Lucide 통합 준비 (2시간)
- Phase 2: 공통 컴포넌트 전환 (4시간)
- Phase 3: POC 테스트 - community 페이지 (2시간)
- Phase 4: 전체 페이지 순차 전환 (16시간)
- Phase 5: 이모지 → Lucide 전환 (선택, 4시간)
- Phase 6: Font Awesome 제거 및 정리 (2시간)

**총 예상 시간**: 30시간

### 5단계: 구현 (상세 단계)
> 아래 "현재 작업" 섹션에서 Phase별 상세 구현

### 6단계: 검증
**각 Phase QA**:
- Phase 1: Lucide CDN 로드 확인, Helper 함수 단위 테스트
- Phase 2: 컴포넌트별 아이콘 정상 표시 확인
- Phase 3: POC 완전 QA (성능, 브라우저, 반응형)
- Phase 4: 페이지별 QA (100% 커버리지)
- Phase 5: 이모지 전환 QA
- Phase 6: 최종 통합 QA, 성능 측정 (Before/After)

**성공 기준**:
- ✅ 모든 아이콘 정상 표시
- ✅ 동적 아이콘 100% 작동
- ✅ 번들 사이즈 60KB 이상 감소
- ✅ 성능 저하 없음 (±5% 이내)
- ✅ QA 이슈 0건

### 7단계: 문서화
**완료 후 문서화**:
1. `PLAN.md` → `docs/12.개발노트.md` 상세 이동
2. `CLAUDE.md`에 v5.0.0 요약 추가
3. `PLAN.md`, `TASK.md` 초기화

---

## 현재 작업: Phase 1 - Lucide Icons 통합 준비

### Phase 1.1: Lucide CDN 추가 (예상 30분)

**목표**: Lucide와 Font Awesome 병행 사용 환경 구축

**파일**: `/var/www/html/topmkt/src/views/templates/header.php`

**작업 내용**:
1. Font Awesome CDN 아래에 Lucide CDN 추가
2. Lucide 초기화 스크립트 추가

**코드 추가 위치**: Lines 76-77 (Font Awesome CDN 아래)

```html
<!-- Lucide Icons 6.0.0 (Phase 1: Font Awesome과 병행) -->
<script src="https://unpkg.com/lucide@latest"></script>
<script>
  // Lucide 아이콘 초기화 (DOM 로드 후)
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') {
      lucide.createIcons();
    }
  });
</script>
```

**검증 방법**:
- 브라우저 콘솔에서 `lucide` 객체 존재 확인
- `lucide.createIcons()` 정상 실행 확인

---

### Phase 1.2: Helper 함수 작성 (예상 1시간)

**목표**: 동적 아이콘 생성 및 교체를 위한 공통 함수 작성

**파일**: `/var/www/html/topmkt/src/views/includes/lucide-helper.js.php` (신규 생성)

**함수 목록**:

#### 1. `replaceLucideIcon(element, iconName, className = '')`
**용도**: 기존 요소의 아이콘을 Lucide로 교체
**파라미터**:
- `element`: DOM 요소
- `iconName`: Lucide 아이콘명 (예: 'Check', 'X', 'User')
- `className`: 추가 CSS 클래스 (선택)

**구현**:
```javascript
function replaceLucideIcon(element, iconName, className = '') {
    if (!element) return;

    element.innerHTML = '';
    const icon = document.createElement('i');
    icon.setAttribute('data-lucide', iconName);
    if (className) icon.className = className;
    element.appendChild(icon);

    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}
```

**사용 예시**:
```javascript
// 비밀번호 표시/숨김 토글
const eyeIcon = document.getElementById('togglePassword');
replaceLucideIcon(eyeIcon, isVisible ? 'Eye' : 'EyeOff');

// 검증 상태 표시
const statusIcon = document.getElementById('validationStatus');
replaceLucideIcon(statusIcon, 'Check', 'text-success');
```

---

#### 2. `createLucideIcon(iconName, className = '', size = 24)`
**용도**: 새로운 Lucide 아이콘 요소 생성 및 반환
**파라미터**:
- `iconName`: Lucide 아이콘명
- `className`: CSS 클래스
- `size`: 아이콘 크기 (기본 24px)

**구현**:
```javascript
function createLucideIcon(iconName, className = '', size = 24) {
    const icon = document.createElement('i');
    icon.setAttribute('data-lucide', iconName);
    icon.setAttribute('width', size);
    icon.setAttribute('height', size);
    if (className) icon.className = className;

    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    return icon;
}
```

**사용 예시**:
```javascript
// 버튼에 아이콘 추가
const saveButton = document.getElementById('saveBtn');
const saveIcon = createLucideIcon('Save', 'mr-2', 20);
saveButton.prepend(saveIcon);
```

---

#### 3. `getLucideIconName(fontAwesomeClass)`
**용도**: Font Awesome 클래스명을 Lucide 아이콘명으로 변환
**파라미터**:
- `fontAwesomeClass`: Font Awesome 클래스 (예: 'fas fa-user')

**반환**: Lucide 아이콘명 (예: 'User')

**구현**:
```javascript
const FA_TO_LUCIDE_MAP = {
    // Top 30 아이콘 매핑
    'fas fa-times': 'X',
    'fas fa-check': 'Check',
    'fas fa-user': 'User',
    'fas fa-info-circle': 'Info',
    'fas fa-eye': 'Eye',
    'fas fa-comments': 'MessageSquare',
    'fas fa-check-circle': 'CheckCircle',
    'fas fa-lock': 'Lock',
    'fas fa-users': 'Users',
    'fas fa-search': 'Search',
    'fas fa-rocket': 'Rocket',
    'fas fa-plus': 'Plus',
    'fas fa-spinner': 'Loader2',
    'fas fa-sign-in-alt': 'LogIn',
    'fas fa-comment': 'MessageCircle',
    'fas fa-calendar-alt': 'Calendar',
    'fas fa-arrow-left': 'ArrowLeft',
    'fas fa-graduation-cap': 'GraduationCap',
    'fas fa-edit': 'Edit',
    'fas fa-clock': 'Clock',
    'fas fa-user-circle': 'UserCircle',
    'fas fa-trash': 'Trash2',
    'fas fa-shield-alt': 'Shield',
    'fas fa-save': 'Save',
    'fas fa-map-marker-alt': 'MapPin',
    'fas fa-exclamation-triangle': 'AlertTriangle',
    'fas fa-envelope': 'Mail',
    'fas fa-bell': 'Bell',
    'fas fa-user-plus': 'UserPlus',
    'fas fa-sign-out-alt': 'LogOut',

    // 추가 아이콘 (31-105)
    'fas fa-paper-plane': 'Send',
    'fas fa-mobile-alt': 'Smartphone',
    'fas fa-images': 'Images',
    'fas fa-home': 'Home',
    'fas fa-bullhorn': 'Megaphone',
    'fas fa-arrow-right': 'ArrowRight',
    'fas fa-user-edit': 'UserCog',
    'fas fa-undo': 'Undo',
    'fas fa-share-alt': 'Share2',
    'fas fa-key': 'Key',
    'fas fa-heart': 'Heart',
    'fas fa-grip-lines': 'GripHorizontal',
    'fas fa-globe': 'Globe',
    'fas fa-eye-slash': 'EyeOff',
    'fas fa-exclamation-circle': 'AlertCircle',
    'fas fa-clipboard-list': 'ClipboardList',
    'fas fa-chevron-down': 'ChevronDown',
    'fas fa-chart-line': 'TrendingUp',
    'fas fa-calendar': 'Calendar',
    'fab fa-youtube': 'Youtube',
    'fab fa-facebook': 'Facebook',
    'fab fa-instagram': 'Instagram',
    'fas fa-video': 'Video',
    'fas fa-upload': 'Upload',
    'fas fa-download': 'Download',
    'fas fa-cog': 'Settings',
    'fas fa-bars': 'Menu',
    'fas fa-ellipsis-v': 'MoreVertical',
    'fas fa-star': 'Star',
    'fas fa-play': 'Play',
    'fas fa-building': 'Building',
    'fas fa-circle-notch': 'Loader',
    'fas fa-chevron-up': 'ChevronUp',
    'fas fa-chevron-right': 'ChevronRight',
    'fas fa-chevron-left': 'ChevronLeft',
    'fas fa-times-circle': 'XCircle',
    'fas fa-plus-circle': 'PlusCircle',
    'fas fa-minus-circle': 'MinusCircle',
    'fas fa-question-circle': 'HelpCircle',
    'fas fa-file-alt': 'FileText',
    'fas fa-file': 'File',
    'fas fa-folder': 'Folder',
    'fas fa-folder-open': 'FolderOpen',
    'fas fa-copy': 'Copy',
    'fas fa-paste': 'Clipboard',
    'fas fa-cut': 'Scissors',
    'fas fa-link': 'Link',
    'fas fa-external-link-alt': 'ExternalLink',
    'fas fa-download': 'Download',
    'fas fa-cloud-upload-alt': 'CloudUpload',
    'fas fa-cloud-download-alt': 'CloudDownload',
    'fas fa-sync': 'RefreshCw',
    'fas fa-sync-alt': 'RefreshCw',
    'fas fa-redo': 'Redo',
    'fas fa-filter': 'Filter',
    'fas fa-sort': 'ArrowUpDown',
    'fas fa-sort-up': 'ArrowUp',
    'fas fa-sort-down': 'ArrowDown',
    'fas fa-th': 'Grid',
    'fas fa-th-list': 'List',
    'fas fa-th-large': 'LayoutGrid',
    'fas fa-tag': 'Tag',
    'fas fa-tags': 'Tags',
    'fas fa-bookmark': 'Bookmark',
    'fas fa-flag': 'Flag',
    'fas fa-thumbs-up': 'ThumbsUp',
    'fas fa-thumbs-down': 'ThumbsDown',
    'fas fa-share': 'Share',
    'fas fa-reply': 'Reply',
    'fas fa-forward': 'Forward',
    'fas fa-print': 'Printer',
    'fas fa-camera': 'Camera',
    'fas fa-image': 'Image',
    'fas fa-microphone': 'Mic',
    'fas fa-volume-up': 'Volume2',
    'fas fa-volume-down': 'Volume1',
    'fas fa-volume-mute': 'VolumeX',
    'fas fa-phone': 'Phone',
    'fas fa-mobile': 'Smartphone',
    'fas fa-envelope-open': 'MailOpen',
    'fas fa-inbox': 'Inbox',
    'fas fa-archive': 'Archive',
    'fas fa-wifi': 'Wifi',
    'fas fa-bluetooth': 'Bluetooth',
    'fas fa-battery-full': 'Battery',
    'fas fa-power-off': 'Power',
    'fas fa-lightbulb': 'Lightbulb',
    'fas fa-moon': 'Moon',
    'fas fa-sun': 'Sun',
    'fas fa-cloud': 'Cloud',
    'fas fa-umbrella': 'Umbrella',
    'fas fa-snowflake': 'Snowflake',
    'fas fa-fire': 'Flame',
    'fas fa-bolt': 'Zap',
    'fas fa-bug': 'Bug',
    'fas fa-code': 'Code',
    'fas fa-terminal': 'Terminal',
    'fas fa-database': 'Database',
    'fas fa-server': 'Server',
    'fas fa-sitemap': 'Sitemap',
    'fas fa-chart-bar': 'BarChart',
    'fas fa-chart-pie': 'PieChart',
    'fas fa-chart-area': 'AreaChart'
};

function getLucideIconName(fontAwesomeClass) {
    return FA_TO_LUCIDE_MAP[fontAwesomeClass] || null;
}
```

**사용 예시**:
```javascript
// Font Awesome 클래스를 Lucide로 변환
const element = document.querySelector('.fas.fa-user');
const lucideName = getLucideIconName('fas fa-user'); // 'User'
replaceLucideIcon(element, lucideName);
```

---

#### 4. `toggleLucideIcon(element, iconName1, iconName2, condition)`
**용도**: 조건에 따라 두 아이콘 중 하나를 표시 (토글용)
**파라미터**:
- `element`: DOM 요소
- `iconName1`: 조건이 true일 때 아이콘
- `iconName2`: 조건이 false일 때 아이콘
- `condition`: boolean 조건

**구현**:
```javascript
function toggleLucideIcon(element, iconName1, iconName2, condition) {
    const iconName = condition ? iconName1 : iconName2;
    replaceLucideIcon(element, iconName);
}
```

**사용 예시**:
```javascript
// 비밀번호 표시/숨김 토글
const isVisible = passwordInput.type === 'text';
toggleLucideIcon(eyeIcon, 'EyeOff', 'Eye', isVisible);
```

---

#### 5. `addLucideIconToButton(button, iconName, position = 'before')`
**용도**: 버튼에 아이콘 추가
**파라미터**:
- `button`: 버튼 DOM 요소
- `iconName`: Lucide 아이콘명
- `position`: 'before' (앞) 또는 'after' (뒤)

**구현**:
```javascript
function addLucideIconToButton(button, iconName, position = 'before') {
    if (!button) return;

    const icon = createLucideIcon(iconName, '', 20);

    if (position === 'before') {
        button.prepend(icon);
        button.prepend(document.createTextNode(' ')); // 간격
    } else {
        button.appendChild(document.createTextNode(' '));
        button.appendChild(icon);
    }
}
```

**사용 예시**:
```javascript
// 저장 버튼에 아이콘 추가
const saveBtn = document.getElementById('saveBtn');
addLucideIconToButton(saveBtn, 'Save', 'before');
```

---

**footer.php 통합**: Lines ~1800 (컴포넌트 로드 섹션)
```php
<!-- Lucide Helper Functions (v5.0.0) -->
<?php require_once SRC_PATH . '/views/includes/lucide-helper.js.php'; ?>
```

---

### Phase 1.3: CSS 애니메이션 추가 (예상 30분)

**목표**: Lucide 아이콘 애니메이션 및 커스텀 스타일 정의

**파일**: `/var/www/html/topmkt/public/assets/css/lucide-custom.css` (신규 생성)

**CSS 내용**:

```css
/* ========================================
   Lucide Icons 커스텀 스타일
   v5.0.0 - Font Awesome → Lucide 전환
   ======================================== */

/* 1. 기본 아이콘 스타일 */
i[data-lucide] {
    display: inline-block;
    vertical-align: middle;
    width: 1em;
    height: 1em;
    stroke-width: 2;
}

/* 2. 로딩 스피너 애니메이션 (Loader2) */
@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.lucide-loader2,
i[data-lucide="loader-2"] {
    animation: spin 1s linear infinite;
}

/* 3. 아이콘 크기 변형 */
.lucide-sm {
    width: 0.875em;
    height: 0.875em;
}

.lucide-lg {
    width: 1.25em;
    height: 1.25em;
}

.lucide-xl {
    width: 1.5em;
    height: 1.5em;
}

.lucide-2x {
    width: 2em;
    height: 2em;
}

.lucide-3x {
    width: 3em;
    height: 3em;
}

/* 4. 아이콘 색상 유틸리티 */
.lucide-primary {
    color: #667eea;
}

.lucide-secondary {
    color: #718096;
}

.lucide-success {
    color: #48bb78;
}

.lucide-danger {
    color: #f56565;
}

.lucide-warning {
    color: #ed8936;
}

.lucide-info {
    color: #4299e1;
}

.lucide-light {
    color: #f7fafc;
}

.lucide-dark {
    color: #2d3748;
}

/* 5. 아이콘 회전 */
.lucide-rotate-90 {
    transform: rotate(90deg);
}

.lucide-rotate-180 {
    transform: rotate(180deg);
}

.lucide-rotate-270 {
    transform: rotate(270deg);
}

/* 6. 아이콘 뒤집기 */
.lucide-flip-horizontal {
    transform: scaleX(-1);
}

.lucide-flip-vertical {
    transform: scaleY(-1);
}

/* 7. 버튼 내 아이콘 정렬 */
button i[data-lucide],
a.btn i[data-lucide] {
    margin-right: 0.5rem;
}

button i[data-lucide]:last-child,
a.btn i[data-lucide]:last-child {
    margin-right: 0;
    margin-left: 0.5rem;
}

/* 8. 호버 효과 */
.lucide-hover:hover {
    opacity: 0.7;
    transition: opacity 0.2s ease;
}

/* 9. 펄스 애니메이션 (알림 등) */
@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.lucide-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* 10. Font Awesome 호환 클래스 (마이그레이션 중 임시) */
.fa-spin {
    animation: spin 1s linear infinite;
}

/* 11. 반응형 아이콘 크기 */
@media (max-width: 768px) {
    i[data-lucide] {
        width: 1.1em;
        height: 1.1em;
    }
}

@media (max-width: 425px) {
    i[data-lucide] {
        width: 1.2em;
        height: 1.2em;
    }
}
```

**header.php 통합**: Lines ~77 (Font Awesome CSS 아래)
```html
<!-- Lucide Custom CSS (v5.0.0) -->
<link rel="stylesheet" href="/assets/css/lucide-custom.css">
```

---

### Phase 1 완료 조건
- ✅ Lucide CDN 로드 확인 (브라우저 콘솔에서 `lucide` 객체 존재)
- ✅ Helper 함수 5개 모두 작동 확인 (단위 테스트)
- ✅ CSS 애니메이션 정상 작동 (스피너 회전)
- ✅ Font Awesome과 Lucide 병행 사용 가능

---

## Phase 2: 공통 컴포넌트 전환 (예상 4시간)

> Phase 1 완료 후 상세 작성 예정

---

## Phase 3: POC 테스트 - community 페이지 (예상 2시간)

> Phase 2 완료 후 상세 작성 예정

---

## Phase 4: 전체 페이지 순차 전환 (예상 16시간)

> Phase 3 QA 통과 후 상세 작성 예정

---

## Phase 5: 이모지 → Lucide 전환 (선택, 예상 4시간)

> Phase 4 완료 후 상세 작성 예정

---

## Phase 6: Font Awesome 제거 및 정리 (예상 2시간)

> Phase 5 완료 후 상세 작성 예정

---

## 참고 자료

### Lucide Icons 공식 문서
- 홈페이지: https://lucide.dev/
- CDN 사용법: https://lucide.dev/guide/installation#cdn
- 아이콘 목록: https://lucide.dev/icons/

### Font Awesome → Lucide 매핑 가이드
- 조사 보고서: (Task agent 결과 참조)
- 매핑률: 95% 완벽, 4% 유사, 1% 없음

### 프로젝트 문서
- 컴포넌트 가이드: `/docs/23.컴포넌트_사용_가이드.md`
- 에러처리 표준: `/docs/22.에러처리_및_로깅_표준.md`
- CLAUDE.md: 프로젝트 히스토리
