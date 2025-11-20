<?php
/**
 * SearchFilter Component
 * v3.37.0 - 검색/필터 시스템 완전 컴포넌트화
 *
 * Ultra Think 모드로 33개 파일의 검색/필터 패턴 분석 후 통합
 *
 * 지원 패턴:
 * 1. 단순 폼 기반 검색 (GET method)
 * 2. 고급 필터 시스템 (다중 필터 그리드)
 * 3. 날짜 범위 필터
 * 4. 클라이언트 사이드 필터링 (JavaScript)
 *
 * 사용 예시:
 * ```php
 * echo SearchFilter::create([
 *     'action' => '/community',
 *     'method' => 'GET',
 *     'layout' => 'inline',
 *     'filters' => [
 *         ['type' => 'select', 'name' => 'filter', 'label' => '검색 필터', 'options' => [...]],
 *         ['type' => 'text', 'name' => 'search', 'placeholder' => '검색어...']
 *     ]
 * ]);
 * ```
 */
class SearchFilter
{
    /**
     * SearchFilter 컴포넌트 생성
     *
     * @param array $config 설정 배열
     * @return string HTML 문자열
     */
    public static function create(array $config): string
    {
        // 기본 설정
        $defaults = [
            'action' => '#',
            'method' => 'GET',  // GET, POST, JS (JavaScript 모드)
            'layout' => 'inline',  // inline, grid-2, grid-3, grid-4
            'filters' => [],
            'searchInput' => true,  // 검색 input 표시 여부
            'searchPlaceholder' => '검색어를 입력하세요...',
            'searchName' => 'search',
            'searchInputId' => '',  // 검색 input ID (선택사항)
            'searchValue' => '',
            'submitButton' => true,  // 검색 버튼 표시 여부
            'submitText' => '<i data-lucide="search" width="18" height="18"></i> 검색',
            'resetButton' => true,  // 초기화 버튼 표시 여부
            'resetText' => '<i data-lucide="undo" width="18" height="18"></i> 초기화',
            'collapsible' => false,  // 접기/펼치기 기능
            'collapsed' => false,  // 초기 접힘 상태
            'title' => '🔍 필터 및 검색',
            'cssClass' => '',  // 추가 CSS 클래스
            'onSubmit' => null,  // JavaScript 콜백 (method='JS' 시 필수)
            'onReset' => null,  // 초기화 콜백
            'preserveParams' => [],  // 유지할 URL 파라미터들
        ];

        $config = array_merge($defaults, $config);

        // HTML 생성 시작
        $html = self::renderContainer($config);

        return $html;
    }

    /**
     * 컨테이너 렌더링
     */
    private static function renderContainer(array $config): string
    {
        $cssClass = 'search-filter-section ' . $config['cssClass'];
        $id = uniqid('sf_');

        $html = '<div class="' . $cssClass . '" id="' . $id . '">';

        // 접기/펼치기 헤더
        if ($config['collapsible']) {
            $html .= self::renderHeader($config, $id);
        }

        // 필터 폼
        $contentClass = $config['collapsible'] ? 'search-filter-content' : '';
        $contentStyle = ($config['collapsible'] && $config['collapsed']) ? 'display: none;' : '';

        $html .= '<div class="' . $contentClass . '" id="' . $id . '-content" style="' . $contentStyle . '">';

        if ($config['method'] === 'JS') {
            // JavaScript 모드 (폼 없음)
            $html .= self::renderFiltersOnly($config, $id);
        } else {
            // GET/POST 폼 모드
            $html .= self::renderForm($config, $id);
        }

        $html .= '</div>';  // content
        $html .= '</div>';  // container

        // JavaScript 초기화
        $html .= self::renderScript($config, $id);

        return $html;
    }

    /**
     * 접기/펼치기 헤더 렌더링
     */
    private static function renderHeader(array $config, string $id): string
    {
        $toggleIcon = $config['collapsed'] ? 'chevron-down' : 'chevron-up';
        $toggleText = $config['collapsed'] ? '펼치기' : '간단히 보기';

        return '<div class="search-filter-header">
            <h3 class="search-filter-title">' . $config['title'] . '</h3>
            <button type="button" class="search-filter-toggle" onclick="SearchFilter.toggle(\'' . $id . '\')">
                <span class="toggle-text">' . $toggleText . '</span>
                <i data-lucide="' . $toggleIcon . '" width="18" height="18" class="toggle-icon"></i>
            </button>
        </div>';
    }

    /**
     * 폼 렌더링 (GET/POST 모드)
     */
    private static function renderForm(array $config, string $id): string
    {
        $html = '<form method="' . strtoupper($config['method']) . '" action="' . htmlspecialchars($config['action']) . '" class="search-filter-form">';

        // 유지할 파라미터들 (hidden input)
        foreach ($config['preserveParams'] as $param) {
            if (isset($_GET[$param])) {
                $html .= '<input type="hidden" name="' . htmlspecialchars($param) . '" value="' . htmlspecialchars($_GET[$param]) . '">';
            }
        }

        // 필터 그리드 (inline 레이아웃은 검색 행 포함)
        $html .= self::renderFilters($config);

        // 검색 input + 버튼 행 (inline 레이아웃이 아닐 때만)
        if ($config['layout'] !== 'inline' && ($config['searchInput'] || $config['submitButton'] || $config['resetButton'])) {
            $html .= self::renderSearchRow($config);
        }

        $html .= '</form>';

        return $html;
    }

    /**
     * 필터만 렌더링 (JavaScript 모드)
     */
    private static function renderFiltersOnly(array $config, string $id): string
    {
        $html = '<div class="search-filter-wrapper">';

        // 필터 그리드
        $html .= self::renderFilters($config);

        // 검색 input + 버튼 행 (inline 레이아웃이 아닐 때만 - inline은 이미 renderInlineLayout에서 검색창 포함)
        if ($config['layout'] !== 'inline' && ($config['searchInput'] || $config['submitButton'] || $config['resetButton'])) {
            $html .= self::renderSearchRow($config);
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * 필터 그리드 렌더링
     */
    private static function renderFilters(array $config): string
    {
        if (empty($config['filters'])) {
            return '';
        }

        $gridClass = 'search-filter-grid layout-' . $config['layout'];

        // Inline 레이아웃이고 검색 input이 있으면 완전 한 줄로 통합
        if ($config['layout'] === 'inline' && $config['searchInput']) {
            return self::renderInlineLayout($config);
        }

        $html = '<div class="' . $gridClass . '">';

        foreach ($config['filters'] as $filter) {
            $html .= self::renderFilter($filter);
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Inline 레이아웃 전용 렌더링 (필터 + 검색 완전 한 줄)
     */
    private static function renderInlineLayout(array $config): string
    {
        $html = '<div class="search-filter-inline-row">';

        // 필터들
        foreach ($config['filters'] as $filter) {
            $html .= self::renderInput($filter);
        }

        // 검색 input
        if ($config['searchInput']) {
            $idAttr = $config['searchInputId'] ? ' id="' . htmlspecialchars($config['searchInputId']) . '"' : '';
            $html .= '<input type="text" name="' . htmlspecialchars($config['searchName']) . '" class="search-filter-search-input"' . $idAttr . ' placeholder="' . htmlspecialchars($config['searchPlaceholder']) . '" value="' . htmlspecialchars($config['searchValue']) . '" autocomplete="off">';
        }

        // 버튼들
        if ($config['submitButton']) {
            if ($config['method'] === 'JS') {
                $onclick = $config['onSubmit'] ? 'onclick="' . $config['onSubmit'] . '"' : '';
                $html .= '<button type="button" class="btn btn-primary search-filter-submit" ' . $onclick . '>' . $config['submitText'] . '</button>';
            } else {
                $html .= '<button type="submit" class="btn btn-primary search-filter-submit">' . $config['submitText'] . '</button>';
            }
        }

        if ($config['resetButton']) {
            if ($config['method'] === 'JS') {
                $onclick = $config['onReset'] ? 'onclick="' . $config['onReset'] . '"' : 'onclick="SearchFilter.reset(this)"';
                $html .= '<button type="button" class="btn btn-secondary search-filter-reset" ' . $onclick . '>' . $config['resetText'] . '</button>';
            } else {
                $html .= '<button type="button" class="btn btn-secondary search-filter-reset" onclick="SearchFilter.resetForm(this)">' . $config['resetText'] . '</button>';
            }
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * 개별 필터 렌더링
     */
    private static function renderFilter(array $filter): string
    {
        $defaults = [
            'type' => 'text',
            'name' => '',
            'label' => '',
            'placeholder' => '',
            'value' => $_GET[$filter['name'] ?? ''] ?? ($filter['value'] ?? ''),
            'options' => [],
            'class' => '',
            'id' => uniqid('filter_'),
            'required' => false,
            'min' => null,
            'max' => null,
            'step' => null,
        ];

        $filter = array_merge($defaults, $filter);

        $html = '<div class="search-filter-group">';

        // 라벨
        if ($filter['label']) {
            $html .= '<label class="search-filter-label" for="' . $filter['id'] . '">' . htmlspecialchars($filter['label']) . '</label>';
        }

        // 입력 필드
        $html .= self::renderInput($filter);

        $html .= '</div>';

        return $html;
    }

    /**
     * 입력 필드 렌더링
     */
    private static function renderInput(array $filter): string
    {
        $commonAttrs = 'class="search-filter-input ' . $filter['class'] . '" id="' . $filter['id'] . '" name="' . $filter['name'] . '"';

        if ($filter['required']) {
            $commonAttrs .= ' required';
        }

        switch ($filter['type']) {
            case 'select':
                return self::renderSelect($filter, $commonAttrs);

            case 'date':
                return '<input type="date" ' . $commonAttrs . ' value="' . htmlspecialchars($filter['value']) . '"' .
                       ($filter['min'] ? ' min="' . $filter['min'] . '"' : '') .
                       ($filter['max'] ? ' max="' . $filter['max'] . '"' : '') . '>';

            case 'number':
                return '<input type="number" ' . $commonAttrs . ' value="' . htmlspecialchars($filter['value']) . '" placeholder="' . htmlspecialchars($filter['placeholder']) . '"' .
                       ($filter['min'] !== null ? ' min="' . $filter['min'] . '"' : '') .
                       ($filter['max'] !== null ? ' max="' . $filter['max'] . '"' : '') .
                       ($filter['step'] ? ' step="' . $filter['step'] . '"' : '') . '>';

            case 'text':
            default:
                return '<input type="text" ' . $commonAttrs . ' value="' . htmlspecialchars($filter['value']) . '" placeholder="' . htmlspecialchars($filter['placeholder']) . '" autocomplete="off">';
        }
    }

    /**
     * Select 필드 렌더링
     */
    private static function renderSelect(array $filter, string $commonAttrs): string
    {
        $html = '<select ' . $commonAttrs . '>';

        foreach ($filter['options'] as $value => $label) {
            $selected = ($filter['value'] == $value) ? ' selected' : '';
            $html .= '<option value="' . htmlspecialchars($value) . '"' . $selected . '>' . htmlspecialchars($label) . '</option>';
        }

        $html .= '</select>';

        return $html;
    }

    /**
     * 검색 행 렌더링
     */
    private static function renderSearchRow(array $config): string
    {
        $html = '<div class="search-filter-row">';

        // 검색 input
        if ($config['searchInput']) {
            $idAttr = $config['searchInputId'] ? ' id="' . htmlspecialchars($config['searchInputId']) . '"' : '';
            $html .= '<input type="text" name="' . htmlspecialchars($config['searchName']) . '" class="search-filter-search-input"' . $idAttr . ' placeholder="' . htmlspecialchars($config['searchPlaceholder']) . '" value="' . htmlspecialchars($config['searchValue']) . '" autocomplete="off">';
        }

        // 검색 버튼
        if ($config['submitButton']) {
            if ($config['method'] === 'JS') {
                $onclick = $config['onSubmit'] ? 'onclick="' . $config['onSubmit'] . '"' : '';
                $html .= '<button type="button" class="btn btn-primary search-filter-submit" ' . $onclick . '>' . $config['submitText'] . '</button>';
            } else {
                $html .= '<button type="submit" class="btn btn-primary search-filter-submit">' . $config['submitText'] . '</button>';
            }
        }

        // 초기화 버튼
        if ($config['resetButton']) {
            if ($config['method'] === 'JS') {
                $onclick = $config['onReset'] ? 'onclick="' . $config['onReset'] . '"' : 'onclick="SearchFilter.reset(this)"';
                $html .= '<button type="button" class="btn btn-secondary search-filter-reset" ' . $onclick . '>' . $config['resetText'] . '</button>';
            } else {
                $html .= '<button type="button" class="btn btn-secondary search-filter-reset" onclick="SearchFilter.resetForm(this)">' . $config['resetText'] . '</button>';
            }
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * JavaScript 초기화 스크립트
     */
    private static function renderScript(array $config, string $id): string
    {
        return '<script>
if (typeof SearchFilter === "undefined") {
    window.SearchFilter = {
        toggle: function(id) {
            const content = document.getElementById(id + "-content");
            const icon = document.querySelector("#" + id + " .toggle-icon");
            const text = document.querySelector("#" + id + " .toggle-text");

            if (content.style.display === "none") {
                content.style.display = "block";
                icon.setAttribute("data-lucide", "chevron-up");
                text.textContent = "간단히 보기";
                if (typeof lucide !== "undefined") lucide.createIcons();
            } else {
                content.style.display = "none";
                icon.setAttribute("data-lucide", "chevron-down");
                text.textContent = "펼치기";
                if (typeof lucide !== "undefined") lucide.createIcons();
            }
        },

        resetForm: function(button) {
            const form = button.closest("form");
            if (form) {
                // 모든 input, select 초기화
                form.querySelectorAll("input[type=text], input[type=search], input[type=number], input[type=date]").forEach(input => {
                    input.value = "";
                });
                form.querySelectorAll("select").forEach(select => {
                    select.selectedIndex = 0;
                });

                // 폼 제출 (GET 메서드이므로 action URL로 이동)
                if (form.method.toUpperCase() === "GET") {
                    window.location.href = form.action;
                } else {
                    form.submit();
                }
            }
        },

        reset: function(button) {
            // JavaScript 모드용 초기화
            const wrapper = button.closest(".search-filter-wrapper, .search-filter-content");
            if (wrapper) {
                wrapper.querySelectorAll("input[type=text], input[type=search], input[type=number], input[type=date]").forEach(input => {
                    input.value = "";
                });
                wrapper.querySelectorAll("select").forEach(select => {
                    select.selectedIndex = 0;
                });
            }
        }
    };
}
</script>';
    }
}
