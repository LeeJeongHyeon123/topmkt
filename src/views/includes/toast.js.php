<script>
/**
 * 🚀 v3.30.0: 통합 Toast 알림 시스템
 *
 * 목적:
 * - 6개 중복된 showMessage 계열 함수 통합
 * - 70개 사용 위치의 일관된 사용자 경험 제공
 * - 인라인 스타일 제거, CSS 클래스 기반 스타일링
 *
 * 사용법:
 * Toast.success('성공 메시지');
 * Toast.error('오류 메시지');
 * Toast.warning('경고 메시지');
 * Toast.info('정보 메시지');
 *
 * 옵션:
 * Toast.show('메시지', 'success', { duration: 5000, position: 'top-center' });
 */

class Toast {
    /**
     * Toast 메시지 표시
     *
     * @param {string} message - 표시할 메시지
     * @param {string} type - 메시지 타입 (success, error, warning, info)
     * @param {Object} options - 추가 옵션
     * @param {number} options.duration - 표시 시간 (ms, 기본값: 3000)
     * @param {string} options.position - 위치 (top-right, top-center, top-left, bottom-right, bottom-center, bottom-left)
     * @param {boolean} options.dismissible - 닫기 버튼 표시 여부 (기본값: true)
     */
    static show(message, type = 'info', options = {}) {
        // 기본 옵션 설정
        const settings = {
            duration: options.duration || 3000,
            position: options.position || 'top-right',
            dismissible: options.dismissible !== false
        };

        // 기존 동일한 메시지 제거 (중복 방지)
        const existingToasts = document.querySelectorAll('.toast-message');
        existingToasts.forEach(toast => {
            if (toast.textContent.includes(message)) {
                toast.remove();
            }
        });

        // Toast 컨테이너 생성 또는 가져오기
        let container = document.querySelector(`.toast-container[data-position="${settings.position}"]`);
        if (!container) {
            container = document.createElement('div');
            container.className = `toast-container toast-${settings.position}`;
            container.setAttribute('data-position', settings.position);
            document.body.appendChild(container);
        }

        // Toast 메시지 엘리먼트 생성
        const toastEl = document.createElement('div');
        toastEl.className = `toast-message toast-${type}`;
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'polite');

        // 아이콘 결정
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };

        // Toast 내용 구성
        toastEl.innerHTML = `
            <div class="toast-content">
                <i class="fas ${icons[type] || icons.info} toast-icon"></i>
                <span class="toast-text">${message}</span>
                ${settings.dismissible ? '<button class="toast-close" aria-label="닫기">&times;</button>' : ''}
            </div>
        `;

        // 컨테이너에 추가
        container.appendChild(toastEl);

        // 애니메이션 트리거 (reflow 강제)
        void toastEl.offsetWidth;
        toastEl.classList.add('toast-show');

        // 닫기 버튼 이벤트
        if (settings.dismissible) {
            const closeBtn = toastEl.querySelector('.toast-close');
            closeBtn.addEventListener('click', () => {
                this.dismiss(toastEl);
            });
        }

        // 자동 제거
        if (settings.duration > 0) {
            setTimeout(() => {
                this.dismiss(toastEl);
            }, settings.duration);
        }

        return toastEl;
    }

    /**
     * Toast 메시지 제거
     *
     * @param {HTMLElement} toastEl - 제거할 Toast 엘리먼트
     */
    static dismiss(toastEl) {
        if (!toastEl) return;

        toastEl.classList.remove('toast-show');
        toastEl.classList.add('toast-hide');

        setTimeout(() => {
            if (toastEl.parentNode) {
                toastEl.parentNode.removeChild(toastEl);

                // 빈 컨테이너 제거
                const container = toastEl.closest('.toast-container');
                if (container && container.children.length === 0) {
                    container.remove();
                }
            }
        }, 300); // 애니메이션 시간과 일치
    }

    /**
     * 성공 메시지 표시
     *
     * @param {string} message - 메시지
     * @param {Object} options - 옵션
     */
    static success(message, options = {}) {
        return this.show(message, 'success', options);
    }

    /**
     * 오류 메시지 표시
     *
     * @param {string} message - 메시지
     * @param {Object} options - 옵션
     */
    static error(message, options = {}) {
        return this.show(message, 'error', options);
    }

    /**
     * 경고 메시지 표시
     *
     * @param {string} message - 메시지
     * @param {Object} options - 옵션
     */
    static warning(message, options = {}) {
        return this.show(message, 'warning', options);
    }

    /**
     * 정보 메시지 표시
     *
     * @param {string} message - 메시지
     * @param {Object} options - 옵션
     */
    static info(message, options = {}) {
        return this.show(message, 'info', options);
    }

    /**
     * 모든 Toast 메시지 제거
     */
    static dismissAll() {
        const allToasts = document.querySelectorAll('.toast-message');
        allToasts.forEach(toast => {
            this.dismiss(toast);
        });
    }
}

// 전역 객체로 등록
if (typeof window !== 'undefined') {
    window.Toast = Toast;
}
</script>
