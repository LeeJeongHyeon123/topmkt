<script>
/**
 * 날짜/시간 포맷 유틸리티
 *
 * 탑마케팅 전체 페이지에서 사용하는 날짜/시간 포맷 함수 통합
 * v3.62.0 - 날짜 포맷 유틸리티 컴포넌트화
 *
 * @author Claude (Anthropic)
 * @date 2025-10-06
 */

(function() {
    'use strict';

    /**
     * 날짜를 한국어 형식으로 포맷
     *
     * @param {string|Date} dateString - 날짜 문자열 또는 Date 객체
     * @param {object} options - 포맷 옵션
     * @param {boolean} options.includeTime - 시간 포함 여부 (기본: false)
     * @param {string} options.nullText - null/undefined 시 표시 텍스트 (기본: "없음")
     * @returns {string} 포맷된 날짜 문자열
     *
     * @example
     * formatDate('2025-10-06') // "2025. 10. 6."
     * formatDate('2025-10-06', { includeTime: true }) // "2025. 10. 6. 14:30"
     * formatDate(null) // "없음"
     */
    window.formatDate = function(dateString, options = {}) {
        const {
            includeTime = false,
            nullText = '없음'
        } = options;

        if (!dateString) return nullText;

        try {
            const date = new Date(dateString);

            // Invalid Date 체크
            if (isNaN(date.getTime())) {
                console.warn('⚠️ formatDate: Invalid date:', dateString);
                return nullText;
            }

            const dateStr = date.toLocaleDateString('ko-KR');

            if (includeTime) {
                const timeStr = date.toLocaleTimeString('ko-KR', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
                return `${dateStr} ${timeStr}`;
            }

            return dateStr;
        } catch (error) {
            console.error('❌ formatDate 오류:', error);
            return nullText;
        }
    };

    /**
     * 날짜와 시간을 한국어 형식으로 포맷 (상세)
     *
     * @param {string|Date} dateString - 날짜 문자열 또는 Date 객체
     * @param {object} options - 포맷 옵션
     * @param {string} options.nullText - null/undefined 시 표시 텍스트 (기본: "없음")
     * @param {boolean} options.includeSeconds - 초 포함 여부 (기본: false)
     * @returns {string} 포맷된 날짜/시간 문자열
     *
     * @example
     * formatDateTime('2025-10-06 14:30:45') // "2025. 10. 6. 오후 2:30"
     * formatDateTime('2025-10-06 14:30:45', { includeSeconds: true }) // "2025. 10. 6. 오후 2:30:45"
     */
    window.formatDateTime = function(dateString, options = {}) {
        const {
            nullText = '없음',
            includeSeconds = false
        } = options;

        if (!dateString) return nullText;

        try {
            const date = new Date(dateString);

            // Invalid Date 체크
            if (isNaN(date.getTime())) {
                console.warn('⚠️ formatDateTime: Invalid date:', dateString);
                return nullText;
            }

            const formatOptions = {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            };

            if (includeSeconds) {
                formatOptions.second = '2-digit';
            }

            return date.toLocaleString('ko-KR', formatOptions);
        } catch (error) {
            console.error('❌ formatDateTime 오류:', error);
            return nullText;
        }
    };

    /**
     * 날짜를 간단한 형식으로 포맷 (년-월-일)
     *
     * @param {string|Date} dateString - 날짜 문자열 또는 Date 객체
     * @returns {string} YYYY-MM-DD 형식 문자열
     *
     * @example
     * formatDateSimple('2025-10-06') // "2025-10-06"
     */
    window.formatDateSimple = function(dateString) {
        if (!dateString) return '';

        try {
            const date = new Date(dateString);

            if (isNaN(date.getTime())) {
                return '';
            }

            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');

            return `${year}-${month}-${day}`;
        } catch (error) {
            console.error('❌ formatDateSimple 오류:', error);
            return '';
        }
    };

    /**
     * 상대 시간 포맷 (방금 전, 3분 전, 2시간 전 등)
     *
     * @param {string|Date} dateString - 날짜 문자열 또는 Date 객체
     * @returns {string} 상대 시간 문자열
     *
     * @example
     * formatRelativeTime('2025-10-06 14:28:00') // "2분 전" (현재 14:30 기준)
     * formatRelativeTime('2025-10-05 14:30:00') // "1일 전"
     */
    window.formatRelativeTime = function(dateString) {
        if (!dateString) return '';

        try {
            const date = new Date(dateString);
            const now = new Date();

            if (isNaN(date.getTime())) {
                return formatDate(dateString);
            }

            const diffMs = now - date;
            const diffSeconds = Math.floor(diffMs / 1000);
            const diffMinutes = Math.floor(diffSeconds / 60);
            const diffHours = Math.floor(diffMinutes / 60);
            const diffDays = Math.floor(diffHours / 24);

            if (diffSeconds < 60) {
                return '방금 전';
            } else if (diffMinutes < 60) {
                return `${diffMinutes}분 전`;
            } else if (diffHours < 24) {
                return `${diffHours}시간 전`;
            } else if (diffDays < 7) {
                return `${diffDays}일 전`;
            } else {
                return formatDate(dateString);
            }
        } catch (error) {
            console.error('❌ formatRelativeTime 오류:', error);
            return formatDate(dateString);
        }
    };

    // 초기화 로그
    console.log('✅ DateUtils 유틸리티 로드 완료');

})();
</script>
