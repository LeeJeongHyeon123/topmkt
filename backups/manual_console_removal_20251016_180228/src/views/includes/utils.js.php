<script>
/**
 * 통합 유틸리티 함수
 *
 * formatNumber, formatPhone 등 공통 유틸리티 함수 통합
 * v3.63.0 - 유틸리티 함수 컴포넌트화
 *
 * @author Claude (Anthropic)
 * @date 2025-10-06
 */

(function() {
    'use strict';

    /**
     * 숫자를 한국 로케일 형식으로 포맷 (천 단위 구분자)
     *
     * @param {number|string|null|undefined} number - 포맷할 숫자
     * @returns {string} 포맷된 숫자 문자열
     *
     * @example
     * formatNumber(1234567) // "1,234,567"
     * formatNumber(null) // "0"
     * formatNumber(undefined) // "0"
     */
    window.formatNumber = function(number) {
        if (number === null || number === undefined) return "0";
        return Number(number).toLocaleString("ko-KR");
    };

    /**
     * 전화번호를 하이픈 포함 형식으로 포맷
     *
     * @param {string|null|undefined} phone - 포맷할 전화번호
     * @returns {string} 포맷된 전화번호 문자열
     *
     * @example
     * formatPhone('01012345678') // "010-1234-5678"
     * formatPhone('') // "없음"
     * formatPhone(null) // "없음"
     */
    window.formatPhone = function(phone) {
        if (!phone) return "없음";
        return phone.replace(/(\d{3})(\d{4})(\d{4})/, "$1-$2-$3");
    };

    // 초기화 로그
    console.log('✅ Utils 유틸리티 로드 완료 (formatNumber, formatPhone)');

})();
</script>
