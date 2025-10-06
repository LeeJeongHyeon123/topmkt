/**
 * 탑마케팅 유틸리티 함수 모음
 *
 * 성능 최적화 및 공통 기능을 위한 유틸리티 함수들
 * v3.57.0에서 생성
 */

(function() {
    'use strict';

    /**
     * Debounce 함수
     *
     * 연속된 이벤트를 그룹화하여 마지막 이벤트만 실행
     * 사용 예: 검색 입력, 윈도우 리사이즈, 자동완성
     *
     * @param {Function} func - 실행할 함수
     * @param {Number} wait - 대기 시간 (밀리초)
     * @returns {Function} 디바운스된 함수
     *
     * 예제:
     * searchInput.addEventListener('input', debounce(() => {
     *     performSearch();
     * }, 300));
     */
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func.apply(this, args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    /**
     * Throttle 함수
     *
     * 일정 시간 간격으로만 함수 실행을 허용
     * 사용 예: 스크롤 이벤트, 마우스 이동, 버튼 연타 방지
     *
     * @param {Function} func - 실행할 함수
     * @param {Number} limit - 실행 간격 (밀리초)
     * @returns {Function} 스로틀된 함수
     *
     * 예제:
     * window.addEventListener('scroll', throttle(() => {
     *     updateScrollPosition();
     * }, 100));
     */
    function throttle(func, limit) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }

    /**
     * Once 함수
     *
     * 함수를 단 한 번만 실행되도록 제한
     * 사용 예: 초기화 함수, 일회성 이벤트
     *
     * @param {Function} func - 실행할 함수
     * @returns {Function} 한 번만 실행되는 함수
     *
     * 예제:
     * const initialize = once(() => {
     *     console.log('초기화 완료');
     * });
     * initialize(); // "초기화 완료" 출력
     * initialize(); // 아무 일도 일어나지 않음
     */
    function once(func) {
        let ran = false;
        let result;
        return function(...args) {
            if (!ran) {
                ran = true;
                result = func.apply(this, args);
            }
            return result;
        };
    }

    /**
     * Sleep 함수
     *
     * 지정된 시간만큼 대기 (async/await와 함께 사용)
     *
     * @param {Number} ms - 대기 시간 (밀리초)
     * @returns {Promise}
     *
     * 예제:
     * async function example() {
     *     console.log('시작');
     *     await sleep(1000);
     *     console.log('1초 후');
     * }
     */
    function sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    // 전역 객체에 등록
    window.debounce = debounce;
    window.throttle = throttle;
    window.once = once;
    window.sleep = sleep;

    console.log('✅ 탑마케팅 유틸리티 함수 로드 완료 (debounce, throttle, once, sleep)');
})();
