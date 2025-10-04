<?php
/**
 * 페이지네이션 컴포넌트
 *
 * 통일된 페이지네이션 UI를 제공하는 재사용 가능한 컴포넌트
 * 다양한 페이지에서 일관된 페이지네이션 경험 제공
 */

if (!function_exists('renderPagination')) {
    /**
     * 페이지네이션 렌더링
     *
     * @param int $currentPage 현재 페이지 번호
     * @param int $totalPages 전체 페이지 수
     * @param array $options 옵션 배열
     *   - pageParam: 페이지 파라미터 이름 (기본: 'page')
     *   - preserveParams: 보존할 쿼리 파라미터 (배열 또는 true=전체)
     *   - anchor: 앵커 링크 (#comments-section)
     *   - baseUrl: 베이스 URL (기본: 현재 URL)
     *   - range: 표시할 페이지 범위 (기본: 2, 즉 현재±2 = 5페이지)
     *   - containerClass: 컨테이너 CSS 클래스 (기본: 'pagination')
     *   - linkClass: 링크 CSS 클래스 (기본: 'page-link')
     *   - activeClass: 활성 페이지 클래스 (기본: 'active')
     *   - disabledClass: 비활성 클래스 (기본: 'disabled')
     *   - prevText: 이전 버튼 텍스트 (기본: '← 이전')
     *   - nextText: 다음 버튼 텍스트 (기본: '다음 →')
     *   - showFirstLast: 첫/마지막 페이지 표시 (기본: true)
     *   - showEllipsis: 엘립시스(...) 표시 (기본: true)
     *
     * @return string 렌더링된 HTML
     */
    function renderPagination($currentPage, $totalPages, $options = []) {
        // 기본 옵션
        $defaults = [
            'pageParam' => 'page',
            'preserveParams' => true, // true = 모든 파라미터 보존, 배열 = 특정 파라미터만
            'anchor' => null,
            'baseUrl' => null,
            'range' => 2, // 현재 페이지 ±2 (총 5페이지 표시)
            'containerClass' => 'pagination',
            'linkClass' => 'page-link',
            'activeClass' => 'active',
            'disabledClass' => 'disabled',
            'prevText' => '← 이전',
            'nextText' => '다음 →',
            'showFirstLast' => true,
            'showEllipsis' => true
        ];

        $options = array_merge($defaults, $options);

        // 페이지 수가 1개 이하면 페이지네이션 표시 안 함
        if ($totalPages <= 1) {
            return '';
        }

        // 현재 페이지 범위 검증
        $currentPage = max(1, min($currentPage, $totalPages));

        // URL 빌더 헬퍼 함수
        $buildUrl = function($pageNum) use ($options) {
            // 베이스 URL 결정
            if ($options['baseUrl'] !== null) {
                $baseUrl = $options['baseUrl'];
            } else {
                $baseUrl = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
            }

            // 쿼리 파라미터 구성
            $params = [];

            // 기존 쿼리 파라미터 보존
            if ($options['preserveParams'] === true) {
                // 모든 파라미터 보존
                $params = $_GET;
                // 페이지 파라미터 제거 (새로 추가할 것이므로)
                unset($params[$options['pageParam']]);
            } elseif (is_array($options['preserveParams'])) {
                // 지정된 파라미터만 보존
                foreach ($options['preserveParams'] as $key) {
                    if (isset($_GET[$key])) {
                        $params[$key] = $_GET[$key];
                    }
                }
            }

            // 페이지 번호 추가
            $params[$options['pageParam']] = $pageNum;

            // URL 생성
            $url = $baseUrl;
            if (!empty($params)) {
                $url .= '?' . http_build_query($params);
            }

            // 앵커 추가
            if ($options['anchor']) {
                $url .= $options['anchor'];
            }

            return $url;
        };

        // 페이지 범위 계산
        $startPage = max(1, $currentPage - $options['range']);
        $endPage = min($totalPages, $currentPage + $options['range']);

        // HTML 생성 시작
        $html = '<div class="' . htmlspecialchars($options['containerClass']) . '">';

        // 이전 버튼
        if ($currentPage > 1) {
            $html .= '<a href="' . htmlspecialchars($buildUrl($currentPage - 1)) . '" ' .
                     'class="' . htmlspecialchars($options['linkClass']) . '">' .
                     htmlspecialchars($options['prevText']) .
                     '</a>';
        } else {
            $html .= '<span class="' . htmlspecialchars($options['linkClass']) . ' ' .
                     htmlspecialchars($options['disabledClass']) . '">' .
                     htmlspecialchars($options['prevText']) .
                     '</span>';
        }

        // 첫 페이지 (범위 밖일 때만)
        if ($options['showFirstLast'] && $startPage > 1) {
            $html .= '<a href="' . htmlspecialchars($buildUrl(1)) . '" ' .
                     'class="' . htmlspecialchars($options['linkClass']) . '">1</a>';

            // 엘립시스
            if ($options['showEllipsis'] && $startPage > 2) {
                $html .= '<span class="' . htmlspecialchars($options['linkClass']) . ' ' .
                         htmlspecialchars($options['disabledClass']) . '">...</span>';
            }
        }

        // 페이지 번호들
        for ($i = $startPage; $i <= $endPage; $i++) {
            if ($i === $currentPage) {
                // 현재 페이지 (활성)
                $html .= '<span class="' . htmlspecialchars($options['linkClass']) . ' ' .
                         htmlspecialchars($options['activeClass']) . '">' . $i . '</span>';
            } else {
                // 다른 페이지
                $html .= '<a href="' . htmlspecialchars($buildUrl($i)) . '" ' .
                         'class="' . htmlspecialchars($options['linkClass']) . '">' . $i . '</a>';
            }
        }

        // 마지막 페이지 (범위 밖일 때만)
        if ($options['showFirstLast'] && $endPage < $totalPages) {
            // 엘립시스
            if ($options['showEllipsis'] && $endPage < $totalPages - 1) {
                $html .= '<span class="' . htmlspecialchars($options['linkClass']) . ' ' .
                         htmlspecialchars($options['disabledClass']) . '">...</span>';
            }

            if ($totalPages > 0) {
                $html .= '<a href="' . htmlspecialchars($buildUrl($totalPages)) . '" ' .
                         'class="' . htmlspecialchars($options['linkClass']) . '">' .
                         number_format($totalPages) . '</a>';
            }
        }

        // 다음 버튼
        if ($currentPage < $totalPages) {
            $html .= '<a href="' . htmlspecialchars($buildUrl($currentPage + 1)) . '" ' .
                     'class="' . htmlspecialchars($options['linkClass']) . '">' .
                     htmlspecialchars($options['nextText']) .
                     '</a>';
        } else {
            $html .= '<span class="' . htmlspecialchars($options['linkClass']) . ' ' .
                     htmlspecialchars($options['disabledClass']) . '">' .
                     htmlspecialchars($options['nextText']) .
                     '</span>';
        }

        $html .= '</div>';

        return $html;
    }
}

/**
 * 페이지네이션 기본 스타일 (선택적 포함)
 *
 * @return string CSS 스타일
 */
if (!function_exists('getPaginationDefaultStyles')) {
    function getPaginationDefaultStyles() {
        return '
<style>
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    margin-top: 30px;
}

.page-link {
    padding: 8px 12px;
    border: 1px solid #e2e8f0;
    background: white;
    color: #4a5568;
    text-decoration: none;
    border-radius: 6px;
    font-size: 14px;
    transition: all 0.2s ease;
}

.page-link:hover:not(.disabled):not(.active) {
    background: #f8fafc;
    border-color: #667eea;
    color: #667eea;
}

.page-link.active {
    background: #667eea;
    border-color: #667eea;
    color: white;
    font-weight: 600;
}

.page-link.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}

/* 모바일 반응형 */
@media (max-width: 768px) {
    .pagination {
        flex-wrap: wrap;
        gap: 8px;
        justify-content: center;
    }

    .page-link {
        min-width: 44px;
        min-height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
}
</style>
';
    }
}
