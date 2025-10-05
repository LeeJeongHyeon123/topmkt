# SearchFilter Component 사용 가이드

v3.37.0 - 검색/필터 시스템 완전 컴포넌트화

## 개요

33개 파일의 검색/필터 패턴을 분석하여 4가지 주요 패턴을 통합한 컴포넌트입니다.

## 설치

### 1. 파일 포함
```php
require_once SRC_PATH . '/components/ui/SearchFilter.php';
```

### 2. CSS 자동 로드
header.php에 자동으로 로드됩니다:
```html
<link rel="stylesheet" href="/assets/css/search-filter.css">
```

## 패턴 1: 단순 폼 기반 검색 (Community, Notices)

### 사용 예시
```php
<?php
// community/index.php
require_once SRC_PATH . '/components/ui/SearchFilter.php';

echo SearchFilter::create([
    'action' => '/community',
    'method' => 'GET',
    'layout' => 'inline',
    'filters' => [
        [
            'type' => 'select',
            'name' => 'filter',
            'label' => '검색 필터',
            'options' => [
                'all' => '전체',
                'title' => '제목만',
                'content' => '내용만',
                'author' => '작성자'
            ]
        ]
    ],
    'searchInput' => true,
    'searchName' => 'search',
    'searchPlaceholder' => '검색어를 입력하세요...',
    'searchValue' => $_GET['search'] ?? '',
    'submitButton' => true,
    'submitText' => '<i class="fas fa-search"></i> 검색',
    'resetButton' => true,
    'resetText' => '<i class="fas fa-undo"></i> 초기화',
    'preserveParams' => ['page']  // 페이지 번호 유지
]);
?>
```

### 출력 HTML
```html
<div class="search-filter-section">
    <form method="GET" action="/community" class="search-filter-form">
        <div class="search-filter-grid layout-inline">
            <div class="search-filter-group">
                <label class="search-filter-label">검색 필터</label>
                <select class="search-filter-input" name="filter">
                    <option value="all">전체</option>
                    <option value="title">제목만</option>
                    <option value="content">내용만</option>
                    <option value="author">작성자</option>
                </select>
            </div>
        </div>
        <div class="search-filter-row">
            <input type="text" name="search" class="search-filter-search-input" placeholder="검색어를 입력하세요...">
            <button type="submit" class="btn btn-primary search-filter-submit">
                <i class="fas fa-search"></i> 검색
            </button>
            <button type="button" class="btn btn-secondary search-filter-reset" onclick="SearchFilter.resetForm(this)">
                <i class="fas fa-undo"></i> 초기화
            </button>
        </div>
    </form>
</div>
```

### 백엔드 처리 (Controller)
```php
// NoticeController.php
public function index() {
    $filter = $_GET['filter'] ?? 'all';
    $search = $_GET['search'] ?? '';

    // 검색 쿼리 구축
    $query = "SELECT * FROM notices WHERE 1=1";
    $params = [];

    if ($search) {
        if ($filter === 'title') {
            $query .= " AND title LIKE ?";
            $params[] = "%$search%";
        } elseif ($filter === 'content') {
            $query .= " AND content LIKE ?";
            $params[] = "%$search%";
        } else {
            $query .= " AND (title LIKE ? OR content LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
    }

    $notices = $this->model->query($query, $params);
    // ...
}
```

---

## 패턴 2: 고급 필터 시스템 (Admin Users)

### 사용 예시
```php
<?php
// admin/users/list.php
require_once SRC_PATH . '/components/ui/SearchFilter.php';

echo SearchFilter::create([
    'action' => '#',
    'method' => 'JS',  // JavaScript 모드
    'layout' => 'grid-4',  // 4열 그리드
    'collapsible' => true,  // 접기/펼치기 활성화
    'collapsed' => false,
    'title' => '🔍 필터 및 검색',
    'filters' => [
        [
            'type' => 'select',
            'name' => 'status',
            'label' => '상태',
            'id' => 'filter-status',
            'options' => [
                '' => '전체',
                'active' => '활성',
                'inactive' => '비활성',
                'suspended' => '정지',
                'pending' => '대기'
            ]
        ],
        [
            'type' => 'select',
            'name' => 'role',
            'label' => '권한',
            'id' => 'filter-role',
            'options' => [
                '' => '전체',
                'ROLE_USER' => '일반 회원',
                'ROLE_CORP' => '기업 회원',
                'ROLE_MODERATOR' => '운영자',
                'ROLE_ADMIN' => '관리자'
            ]
        ],
        [
            'type' => 'select',
            'name' => 'corp-status',
            'label' => '기업 상태',
            'id' => 'filter-corp-status',
            'options' => [
                '' => '전체',
                'none' => '비기업',
                'pending' => '인증 대기',
                'approved' => '승인됨',
                'rejected' => '거절됨'
            ]
        ],
        [
            'type' => 'select',
            'name' => 'verified',
            'label' => '휴대폰 인증',
            'id' => 'filter-verified',
            'options' => [
                '' => '전체',
                'phone_verified' => '인증 완료',
                'phone_unverified' => '미인증'
            ]
        ],
        [
            'type' => 'select',
            'name' => 'login-activity',
            'label' => '로그인 활동',
            'id' => 'filter-login-activity',
            'options' => [
                '' => '전체',
                'recent_7days' => '최근 7일',
                'recent_30days' => '최근 30일',
                'inactive_30days' => '30일 이상 비활성'
            ]
        ],
        [
            'type' => 'date',
            'name' => 'date-from',
            'label' => '가입일 (시작)',
            'id' => 'filter-date-from'
        ],
        [
            'type' => 'date',
            'name' => 'date-to',
            'label' => '가입일 (종료)',
            'id' => 'filter-date-to'
        ],
        [
            'type' => 'select',
            'name' => 'sort',
            'label' => '정렬 방식',
            'id' => 'filter-sort',
            'options' => [
                'created_at' => '가입일 (최신순)',
                'nickname' => '닉네임 (가나다순)',
                'email' => '이메일 (가나다순)',
                'last_login' => '마지막 로그인',
                'status' => '상태별'
            ]
        ]
    ],
    'searchInput' => true,
    'searchPlaceholder' => '닉네임, 이메일, 전화번호로 검색...',
    'submitButton' => true,
    'submitText' => '<i class="fas fa-search"></i> 검색',
    'resetButton' => true,
    'resetText' => '<i class="fas fa-undo"></i> 초기화',
    'onSubmit' => 'applyFilters()',  // JavaScript 콜백
    'onReset' => 'resetFilters()'
]);
?>
```

### JavaScript 구현
```javascript
function applyFilters() {
    const status = document.getElementById('filter-status').value;
    const role = document.getElementById('filter-role').value;
    const corpStatus = document.getElementById('filter-corp-status').value;
    const verified = document.getElementById('filter-verified').value;
    const loginActivity = document.getElementById('filter-login-activity').value;
    const dateFrom = document.getElementById('filter-date-from').value;
    const dateTo = document.getElementById('filter-date-to').value;
    const sort = document.getElementById('filter-sort').value;
    const search = document.querySelector('.search-filter-search-input').value;

    // URL 파라미터 구축
    const url = new URL(window.location.href);
    url.searchParams.set('status', status);
    url.searchParams.set('role', role);
    url.searchParams.set('corp_status', corpStatus);
    url.searchParams.set('verified', verified);
    url.searchParams.set('login_activity', loginActivity);
    url.searchParams.set('date_from', dateFrom);
    url.searchParams.set('date_to', dateTo);
    url.searchParams.set('sort', sort);
    url.searchParams.set('search', search);

    window.location.href = url.toString();
}

function resetFilters() {
    window.location.href = window.location.pathname;
}
```

---

## 패턴 3: 날짜 범위 필터 (Registrations Dashboard)

### 사용 예시
```php
<?php
// registrations/dashboard.php
require_once SRC_PATH . '/components/ui/SearchFilter.php';

echo SearchFilter::create([
    'action' => '#',
    'method' => 'JS',
    'layout' => 'grid-2',
    'filters' => [
        [
            'type' => 'date',
            'name' => 'start_date',
            'label' => '시작일',
            'id' => 'start-date',
            'value' => $_GET['start_date'] ?? ''
        ],
        [
            'type' => 'date',
            'name' => 'end_date',
            'label' => '종료일',
            'id' => 'end-date',
            'value' => $_GET['end_date'] ?? ''
        ]
    ],
    'searchInput' => false,  // 검색 input 비활성화
    'submitButton' => true,
    'submitText' => '<i class="fas fa-filter"></i> 필터 적용',
    'resetButton' => true,
    'resetText' => '<i class="fas fa-undo"></i> 초기화',
    'onSubmit' => 'applyDateFilter()',
    'onReset' => 'resetDateFilter()'
]);
?>
```

### JavaScript 구현
```javascript
function applyDateFilter() {
    const startDate = document.getElementById('start-date').value;
    const endDate = document.getElementById('end-date').value;

    const url = new URL(window.location.href);
    if (startDate) url.searchParams.set('start_date', startDate);
    if (endDate) url.searchParams.set('end_date', endDate);

    window.location.href = url.toString();
}

function resetDateFilter() {
    const url = new URL(window.location.href);
    url.searchParams.delete('start_date');
    url.searchParams.delete('end_date');
    window.location.href = url.toString();
}
```

---

## 패턴 4: 클라이언트 사이드 필터링 (Admin Corporate)

### 사용 예시
```php
<?php
// admin/corporate/list.php
require_once SRC_PATH . '/components/ui/SearchFilter.php';

echo SearchFilter::create([
    'action' => '#',
    'method' => 'JS',
    'layout' => 'grid-3',
    'filters' => [
        [
            'type' => 'select',
            'name' => 'status',
            'label' => '상태',
            'id' => 'statusFilter',
            'options' => [
                '' => '전체',
                'approved' => '승인됨',
                'pending' => '대기중',
                'rejected' => '거절됨',
                'suspended' => '정지됨'
            ]
        ],
        [
            'type' => 'select',
            'name' => 'join_date',
            'label' => '가입일',
            'id' => 'joinDateFilter',
            'options' => [
                '' => '전체',
                'recent_7days' => '최근 7일',
                'recent_30days' => '최근 30일',
                'recent_90days' => '최근 90일'
            ]
        ],
        [
            'type' => 'select',
            'name' => 'company_type',
            'label' => '기업 유형',
            'id' => 'companyTypeFilter',
            'options' => [
                '' => '전체',
                '대기업' => '대기업',
                '중견기업' => '중견기업',
                '중소기업' => '중소기업',
                '스타트업' => '스타트업'
            ]
        ]
    ],
    'searchInput' => true,
    'searchPlaceholder' => '회사명, 사업자번호, 회원명으로 검색...',
    'searchName' => 'searchInput',
    'searchId' => 'searchInput',
    'submitButton' => false,  // 실시간 필터링이므로 검색 버튼 불필요
    'resetButton' => true,
    'resetText' => '<i class="fas fa-undo"></i> 초기화',
    'onReset' => 'resetClientFilter()'
]);
?>
```

### JavaScript 구현 (클라이언트 사이드)
```javascript
// 원본 데이터 저장
let allMembers = <?= json_encode($members) ?>;

// 필터 이벤트 리스너 설정
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('searchInput').addEventListener('keyup', filterMembers);
    document.getElementById('statusFilter').addEventListener('change', filterMembers);
    document.getElementById('joinDateFilter').addEventListener('change', filterMembers);
    document.getElementById('companyTypeFilter').addEventListener('change', filterMembers);
});

// 실시간 필터링 함수
function filterMembers() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const joinDateFilter = document.getElementById('joinDateFilter').value;
    const companyTypeFilter = document.getElementById('companyTypeFilter').value;

    let filtered = allMembers.filter(member => {
        // 검색어 필터
        const searchMatch = !searchTerm ||
            member.company_name.toLowerCase().includes(searchTerm) ||
            member.business_number.includes(searchTerm) ||
            member.user_name.toLowerCase().includes(searchTerm);

        // 상태 필터
        const statusMatch = !statusFilter || member.status === statusFilter;

        // 가입일 필터
        let dateMatch = true;
        if (joinDateFilter) {
            const memberDate = new Date(member.created_at);
            const today = new Date();
            const diffDays = Math.floor((today - memberDate) / (1000 * 60 * 60 * 24));

            if (joinDateFilter === 'recent_7days') dateMatch = diffDays <= 7;
            else if (joinDateFilter === 'recent_30days') dateMatch = diffDays <= 30;
            else if (joinDateFilter === 'recent_90days') dateMatch = diffDays <= 90;
        }

        // 기업 유형 필터
        const typeMatch = !companyTypeFilter || member.company_type === companyTypeFilter;

        return searchMatch && statusMatch && dateMatch && typeMatch;
    });

    // 결과 렌더링
    renderFilteredMembers(filtered);
    updateFilteredCount(filtered.length, allMembers.length);
}

function resetClientFilter() {
    document.getElementById('searchInput').value = '';
    document.getElementById('statusFilter').selectedIndex = 0;
    document.getElementById('joinDateFilter').selectedIndex = 0;
    document.getElementById('companyTypeFilter').selectedIndex = 0;
    filterMembers();
}

function renderFilteredMembers(members) {
    const tbody = document.querySelector('.members-table tbody');
    if (members.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">검색 결과가 없습니다.</td></tr>';
        return;
    }

    tbody.innerHTML = members.map(member => `
        <tr>
            <td>${member.company_name}</td>
            <td>${member.business_number}</td>
            <td>${member.user_name}</td>
            <td><span class="status-badge status-${member.status}">${member.status}</span></td>
            <td>${member.created_at}</td>
            <td>${member.company_type}</td>
            <td><button onclick="viewMember(${member.id})">상세</button></td>
        </tr>
    `).join('');
}

function updateFilteredCount(filtered, total) {
    document.getElementById('filteredCount').textContent =
        `전체 ${total}개 중 ${filtered}개 표시`;
}
```

---

## 고급 기능

### 1. 여러 레이아웃 지원
```php
'layout' => 'inline',   // 1열 (기본)
'layout' => 'grid-2',   // 2열
'layout' => 'grid-3',   // 3열
'layout' => 'grid-4',   // 4열 (admin용)
```

### 2. 접기/펼치기 기능
```php
'collapsible' => true,   // 활성화
'collapsed' => false,    // 초기 펼침 상태
'title' => '🔍 필터 및 검색',  // 헤더 제목
```

### 3. URL 파라미터 유지
```php
'preserveParams' => ['page', 'limit', 'type']  // 페이지네이션 등 유지
```

### 4. 커스텀 CSS 클래스
```php
'cssClass' => 'my-custom-filter'
```

---

## 반응형 지원

### 자동 반응형 레이아웃
- **768px 이하 (태블릿)**: grid-3, grid-4 → grid-2 자동 축소
- **480px 이하 (모바일)**: 모든 grid → 1열 자동 축소
- 검색 행 버튼: 모바일에서 전체 너비

---

## 브라우저 호환성
- ✅ Chrome 60+
- ✅ Firefox 60+
- ✅ Safari 12+
- ✅ Edge 79+
- ✅ iOS Safari 12+
- ✅ Chrome for Android

---

## 성능 최적화

### 1. CSS 최소화
```bash
# 프로덕션 환경
npx cssnano /assets/css/search-filter.css -o /assets/css/search-filter.min.css
```

### 2. JavaScript 지연 로딩
```html
<!-- 검색 기능이 없는 페이지에서는 컴포넌트 미사용 -->
<?php if ($has_search): ?>
    <?= SearchFilter::create([...]) ?>
<?php endif; ?>
```

---

## 문제 해결

### Q: 필터가 적용되지 않습니다
A: JavaScript 모드(`method: 'JS'`)에서는 `onSubmit` 콜백이 필수입니다.

### Q: 스타일이 깨집니다
A: header.php에 CSS가 로드되었는지 확인하세요.

### Q: 모바일에서 레이아웃이 이상합니다
A: 자동 반응형이 적용되므로 별도 CSS 불필요합니다.

---

## 라이선스
© 2025 (주)윈카드. All Rights Reserved.
