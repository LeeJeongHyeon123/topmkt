<script>
/**
 * 🚀 v3.31.0: 통합 Loading 인디케이터 시스템
 *
 * 목적: 버튼 로딩 상태 및 오버레이 로딩을 중앙화된 클래스로 관리
 *
 * 주요 기능:
 * - 버튼 로딩 상태 자동 관리 (disabled, 원본 텍스트 저장/복원)
 * - 스피너 아이콘 자동 추가/제거
 * - 여러 버튼 동시 제어 지원
 * - 전체 화면 오버레이 로딩 지원
 *
 * 사용법:
 * Loading.button(submitBtn, true, { text: '등록 중...' });
 * Loading.button(submitBtn, false); // 로딩 종료
 * Loading.buttons([btn1, btn2], true);
 * Loading.overlay(true, { message: '처리 중...' });
 */

class Loading {
    /**
     * 버튼 로딩 상태 관리
     *
     * @param {HTMLElement} buttonElement - 로딩 상태를 적용할 버튼 요소
     * @param {boolean} loading - true: 로딩 시작, false: 로딩 종료
     * @param {Object} options - 옵션 객체
     * @param {string} options.text - 로딩 중 표시할 텍스트 (기본: '처리 중...')
     * @param {string} options.icon - Lucide 아이콘명 (기본: 'loader-2')
     * @returns {HTMLElement} 버튼 요소
     */
    static button(buttonElement, loading = true, options = {}) {
        if (!buttonElement) {
            return null;
        }

        const settings = {
            text: options.text || '처리 중...',
            icon: options.icon || 'loader-2'
        };

        if (loading) {
            // 로딩 시작
            // 1. 원본 텍스트 저장 (data 속성에 저장)
            if (!buttonElement.hasAttribute('data-original-text')) {
                buttonElement.setAttribute('data-original-text', buttonElement.innerHTML);
            }

            // 2. 버튼 비활성화
            buttonElement.disabled = true;

            // 3. 로딩 텍스트 및 스피너 아이콘 설정 (v5.0.0: Lucide Icons)
            buttonElement.innerHTML = `<i data-lucide="${settings.icon}" width="18" height="18"></i> ${settings.text}`;

            // 4. Lucide 아이콘 초기화
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // 5. 로딩 상태 클래스 추가
            buttonElement.classList.add('btn-loading');
        } else {
            // 로딩 종료
            // 1. 원본 텍스트 복원
            const originalText = buttonElement.getAttribute('data-original-text');
            if (originalText) {
                buttonElement.innerHTML = originalText;
                buttonElement.removeAttribute('data-original-text');
            }

            // 2. 버튼 활성화
            buttonElement.disabled = false;

            // 3. 로딩 상태 클래스 제거
            buttonElement.classList.remove('btn-loading');
        }

        return buttonElement;
    }

    /**
     * 여러 버튼 동시 로딩 상태 관리
     *
     * @param {Array|NodeList} buttonElements - 버튼 요소 배열 또는 NodeList
     * @param {boolean} loading - true: 로딩 시작, false: 로딩 종료
     * @param {Object} options - 옵션 객체 (button 메서드와 동일)
     * @returns {Array} 버튼 요소 배열
     */
    static buttons(buttonElements, loading = true, options = {}) {
        if (!buttonElements) {
            return [];
        }

        // NodeList를 배열로 변환
        const elements = Array.isArray(buttonElements)
            ? buttonElements
            : Array.from(buttonElements);

        // 각 버튼에 대해 loading 적용
        elements.forEach(btn => {
            this.button(btn, loading, options);
        });

        return elements;
    }

    /**
     * 전체 화면 오버레이 로딩 표시/숨김
     *
     * @param {boolean} show - true: 표시, false: 숨김
     * @param {Object} options - 옵션 객체
     * @param {string} options.message - 로딩 메시지 (기본: '처리 중입니다...')
     * @param {boolean} options.blocking - 클릭 차단 여부 (기본: true)
     * @param {string} options.spinner - 스피너 스타일 (기본: 'default')
     * @returns {HTMLElement|null} 오버레이 요소
     */
    static overlay(show = true, options = {}) {
        const settings = {
            message: options.message || '처리 중입니다...',
            blocking: options.blocking !== false,
            spinner: options.spinner || 'default'
        };

        let overlay = document.getElementById('loading-overlay');

        if (show) {
            // 오버레이가 없으면 생성
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.id = 'loading-overlay';
                overlay.className = 'loading-overlay';
                overlay.innerHTML = `
                    <div class="loading-overlay-content">
                        <div class="loading-spinner">
                            <i data-lucide="loader-2" width="48" height="48"></i>
                        </div>
                        <div class="loading-overlay-message">${settings.message}</div>
                    </div>
                `;
                document.body.appendChild(overlay);

                // Lucide 아이콘 초기화 (v5.0.0)
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            } else {
                // 메시지 업데이트
                const messageEl = overlay.querySelector('.loading-overlay-message');
                if (messageEl) {
                    messageEl.textContent = settings.message;
                }
            }

            // 표시
            overlay.style.display = 'flex';

            // 스크롤 방지
            if (settings.blocking) {
                document.body.style.overflow = 'hidden';
            }

            return overlay;
        } else {
            // 숨김
            if (overlay) {
                overlay.style.display = 'none';
                document.body.style.overflow = '';
            }

            return null;
        }
    }

    /**
     * 오버레이 로딩 메시지 업데이트
     *
     * @param {string} message - 새로운 메시지
     */
    static updateOverlayMessage(message) {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            const messageEl = overlay.querySelector('.loading-overlay-message');
            if (messageEl) {
                messageEl.textContent = message;
            }
        }
    }

    /**
     * 모든 로딩 상태 초기화 (긴급 복구용)
     */
    static resetAll() {
        // 모든 버튼 로딩 해제
        const loadingButtons = document.querySelectorAll('.btn-loading');
        loadingButtons.forEach(btn => {
            this.button(btn, false);
        });

        // 오버레이 숨김
        this.overlay(false);

    }
}

// 전역 접근 가능하도록 window 객체에 등록
window.Loading = Loading;


</script>
