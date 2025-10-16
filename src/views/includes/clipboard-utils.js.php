<script>
/**
 * Clipboard Utilities (클립보드 유틸리티)
 *
 * 클립보드 복사 기능을 위한 통합 유틸리티 시스템
 *
 * @version 3.65.0
 * @author Claude (Anthropic)
 * @created 2025-10-10
 *
 * 주요 기능:
 * - navigator.clipboard.writeText() 우선 사용 (최신 브라우저)
 * - document.execCommand('copy') 폴백 (구형 브라우저)
 * - Toast 피드백 자동 통합
 * - 커스터마이징 가능한 성공/실패 메시지
 * - 비동기 Promise 기반 API
 *
 * 사용법:
 *   // 기본 사용
 *   copyToClipboard('복사할 텍스트');
 *
 *   // 커스텀 성공 메시지
 *   copyToClipboard('복사할 텍스트', { successMessage: '복사 완료!' });
 *
 *   // Toast 비활성화
 *   copyToClipboard('복사할 텍스트', { noToast: true });
 *
 *   // Promise 체이닝
 *   copyToClipboard('복사할 텍스트')
 *     .then(() => console.log('복사 성공'))
 *     .catch(err => console.error('복사 실패:', err));
 */

(function() {
    'use strict';

    /**
     * 클립보드에 텍스트 복사
     *
     * @param {string} text - 복사할 텍스트
     * @param {Object} options - 옵션 객체
     * @param {string} options.successMessage - 성공 시 Toast 메시지 (기본: '✅ 복사되었습니다!')
     * @param {string} options.errorMessage - 실패 시 Toast 메시지 (기본: '❌ 복사에 실패했습니다')
     * @param {boolean} options.noToast - Toast 표시 비활성화 (기본: false)
     * @returns {Promise<void>} 복사 성공/실패 Promise
     */
    window.copyToClipboard = function(text, options = {}) {
        // 옵션 기본값 설정
        const config = {
            successMessage: options.successMessage || '✅ 복사되었습니다!',
            errorMessage: options.errorMessage || '❌ 복사에 실패했습니다',
            noToast: options.noToast || false
        };

        // 텍스트 검증
        if (!text || typeof text !== 'string') {
            const error = new Error('복사할 텍스트가 유효하지 않습니다');
            if (!config.noToast && window.Toast) {
                Toast.error(config.errorMessage);
            }
            return Promise.reject(error);
        }

        // 최신 브라우저: navigator.clipboard API 사용
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(text)
                .then(() => {
                    // 성공 Toast 표시
                    if (!config.noToast && window.Toast) {
                        Toast.success(config.successMessage);
                    }

                })
                .catch((err) => {
                    // Clipboard API 실패 시 폴백으로 시도
                    return fallbackCopy(text, config);
                });
        }

        // 구형 브라우저: 폴백 메서드 사용
        return fallbackCopy(text, config);
    };

    /**
     * 폴백 복사 메서드 (구형 브라우저 지원)
     * document.execCommand('copy') 사용
     *
     * @param {string} text - 복사할 텍스트
     * @param {Object} config - 설정 객체
     * @returns {Promise<void>}
     */
    function fallbackCopy(text, config) {
        return new Promise((resolve, reject) => {
            try {
                // 임시 textarea 생성
                const textArea = document.createElement('textarea');
                textArea.value = text;

                // 화면에 보이지 않도록 스타일 설정
                textArea.style.position = 'fixed';
                textArea.style.top = '0';
                textArea.style.left = '0';
                textArea.style.width = '2em';
                textArea.style.height = '2em';
                textArea.style.padding = '0';
                textArea.style.border = 'none';
                textArea.style.outline = 'none';
                textArea.style.boxShadow = 'none';
                textArea.style.background = 'transparent';
                textArea.style.opacity = '0';
                textArea.style.zIndex = '-1';

                // DOM에 추가
                document.body.appendChild(textArea);

                // iOS 지원을 위한 특수 처리
                if (navigator.userAgent.match(/ipad|iphone/i)) {
                    const range = document.createRange();
                    range.selectNodeContents(textArea);
                    const selection = window.getSelection();
                    selection.removeAllRanges();
                    selection.addRange(range);
                    textArea.setSelectionRange(0, 999999);
                } else {
                    textArea.select();
                }

                // 복사 실행
                const successful = document.execCommand('copy');

                // textarea 제거
                document.body.removeChild(textArea);

                if (successful) {
                    // 성공 Toast 표시
                    if (!config.noToast && window.Toast) {
                        Toast.success(config.successMessage);
                    }

                    resolve();
                } else {
                    throw new Error('execCommand 복사 실패');
                }
            } catch (err) {
                // 실패 Toast 표시
                if (!config.noToast && window.Toast) {
                    Toast.error(config.errorMessage);
                }
                reject(err);
            }
        });
    }

    /**
     * 클립보드 API 지원 여부 확인
     *
     * @returns {boolean} Clipboard API 지원 여부
     */
    window.isClipboardSupported = function() {
        return !!(navigator.clipboard && navigator.clipboard.writeText);
    };

    /**
     * 클립보드에서 텍스트 읽기 (읽기 권한 필요)
     *
     * @returns {Promise<string>} 클립보드의 텍스트
     */
    window.readFromClipboard = function() {
        if (navigator.clipboard && navigator.clipboard.readText) {
            return navigator.clipboard.readText()
                .then((text) => {

                    return text;
                })
                .catch((err) => {
                    if (window.Toast) {
                        Toast.error('클립보드 읽기 권한이 필요합니다');
                    }
                    throw err;
                });
        } else {
            const error = new Error('Clipboard API 미지원 (읽기 불가)');
            if (window.Toast) {
                Toast.error('이 브라우저는 클립보드 읽기를 지원하지 않습니다');
            }
            return Promise.reject(error);
        }
    };

    // 전역 객체에 ClipboardUtils 네임스페이스 추가 (선택적)
    window.ClipboardUtils = {
        copy: window.copyToClipboard,
        read: window.readFromClipboard,
        isSupported: window.isClipboardSupported
    };


})();
</script>
