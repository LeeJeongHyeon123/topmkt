/**
 * Modal System - JavaScript Utilities
 * 탑마케팅 프로젝트 통합 모달 시스템
 *
 * @package TOPMKT
 * @version 1.0.0
 * @since 2025-10-04
 */

/**
 * 모달 열기
 * @param {string} modalId - 모달 ID
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) {
        return;
    }

    modal.classList.add('show');
    document.body.style.overflow = 'hidden';

    // ESC 키 이벤트 등록
    if (modal.dataset.closeEsc === 'true') {
        document.addEventListener('keydown', handleModalEscape);
    }

    // 배경 클릭 이벤트 등록
    if (modal.dataset.closeBackdrop === 'true') {
        modal.addEventListener('click', handleModalBackdropClick);
    }
}

/**
 * 모달 닫기
 * @param {string} modalId - 모달 ID
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) {
        return;
    }

    modal.classList.remove('show');
    document.body.style.overflow = '';

    // 이벤트 리스너 제거
    document.removeEventListener('keydown', handleModalEscape);
    modal.removeEventListener('click', handleModalBackdropClick);
}

/**
 * 모든 모달 닫기
 */
function closeAllModals() {
    const modals = document.querySelectorAll('.modal.show');
    modals.forEach(modal => {
        modal.classList.remove('show');
    });
    document.body.style.overflow = '';
    document.removeEventListener('keydown', handleModalEscape);
}

/**
 * ESC 키 처리
 * @param {KeyboardEvent} e
 */
function handleModalEscape(e) {
    if (e.key === 'Escape' || e.keyCode === 27) {
        const openModals = document.querySelectorAll('.modal.show');
        if (openModals.length > 0) {
            // 가장 마지막에 열린 모달 닫기
            const lastModal = openModals[openModals.length - 1];
            if (lastModal.dataset.closeEsc === 'true') {
                closeModal(lastModal.id);
            }
        }
    }
}

/**
 * 배경 클릭 처리
 * @param {MouseEvent} e
 */
function handleModalBackdropClick(e) {
    // 모달 배경(overlay)을 직접 클릭한 경우에만 닫기
    if (e.target.classList.contains('modal')) {
        const modal = e.target;
        if (modal.dataset.closeBackdrop === 'true') {
            closeModal(modal.id);
        }
    }
}

/**
 * 모달 토글 (열기/닫기)
 * @param {string} modalId - 모달 ID
 */
function toggleModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) {
        return;
    }

    if (modal.classList.contains('show')) {
        closeModal(modalId);
    } else {
        openModal(modalId);
    }
}

/**
 * 동적 모달 생성
 * @param {string} id - 모달 ID
 * @param {string} title - 모달 제목
 * @param {string} content - 모달 내용 (HTML)
 * @param {Array} buttons - 버튼 배열 [{text, type, onclick}]
 * @returns {HTMLElement} 생성된 모달 엘리먼트
 */
function createModal(id, title, content, buttons = []) {
    // 기존 모달 제거
    const existing = document.getElementById(id);
    if (existing) {
        existing.remove();
    }

    // 모달 엘리먼트 생성
    const modal = document.createElement('div');
    modal.id = id;
    modal.className = 'modal';
    modal.setAttribute('data-close-backdrop', 'true');
    modal.setAttribute('data-close-esc', 'true');

    // 모달 내용 생성
    let modalHTML = `
        <div class="modal-content modal-md">
            <div class="modal-header">
                <h3 class="modal-title">${title}</h3>
                <button class="modal-close" onclick="closeModal('${id}')" aria-label="닫기">&times;</button>
            </div>
            <div class="modal-body">
                ${content}
            </div>
    `;

    // 버튼이 있으면 푸터 추가
    if (buttons.length > 0) {
        modalHTML += '<div class="modal-footer">';
        buttons.forEach(btn => {
            const btnType = btn.type || 'secondary';
            const btnText = btn.text || '버튼';
            const btnOnclick = btn.onclick || '';
            modalHTML += `<button class="btn btn-${btnType}" onclick="${btnOnclick}">${btnText}</button>`;
        });
        modalHTML += '</div>';
    }

    modalHTML += `
        </div>
    `;

    modal.innerHTML = modalHTML;
    document.body.appendChild(modal);

    return modal;
}

/**
 * 확인 모달 표시 (간편 함수)
 * @param {string} message - 확인 메시지
 * @param {Function} onConfirm - 확인 버튼 클릭 시 콜백
 * @param {Object} options - 추가 옵션
 */
function showConfirmModal(message, onConfirm, options = {}) {
    const id = options.id || 'confirm-modal-' + Date.now();
    const title = options.title || '확인';

    const buttons = [
        {
            text: options.confirmText || '확인',
            type: options.confirmType || 'primary',
            onclick: `(function() { closeModal('${id}'); (${onConfirm.toString()})(); })()`
        },
        {
            text: options.cancelText || '취소',
            type: 'secondary',
            onclick: `closeModal('${id}')`
        }
    ];

    createModal(id, title, `<p>${message}</p>`, buttons);
    openModal(id);
}

/**
 * 알림 모달 표시 (간편 함수)
 * @param {string} message - 알림 메시지
 * @param {Object} options - 추가 옵션
 */
function showAlertModal(message, options = {}) {
    const id = options.id || 'alert-modal-' + Date.now();
    const title = options.title || '알림';

    const buttons = [
        {
            text: options.okText || '확인',
            type: options.okType || 'primary',
            onclick: `closeModal('${id}')`
        }
    ];

    createModal(id, title, `<p>${message}</p>`, buttons);
    openModal(id);
}

/**
 * Modal 클래스 (네임스페이스)
 */
const Modal = {
    /**
     * 확인 다이얼로그 표시 (Promise 기반)
     * @param {string} message - 확인 메시지
     * @param {Object} options - 추가 옵션
     * @returns {Promise<boolean>} 확인: true, 취소: false
     *
     * @example
     * // 기본 사용법
     * const result = await Modal.confirm('삭제하시겠습니까?');
     * if (result) {
     *     deleteItem();
     * }
     *
     * // 옵션 사용
     * await Modal.confirm('정말 삭제하시겠습니까?', {
     *     title: '삭제 확인',
     *     confirmText: '삭제',
     *     cancelText: '취소',
     *     type: 'danger'
     * });
     */
    confirm: function(message, options = {}) {
        return new Promise((resolve) => {
            const id = 'modal-confirm-' + Date.now();
            const title = options.title || '확인';
            const confirmText = options.confirmText || '확인';
            const cancelText = options.cancelText || '취소';
            const type = options.type || 'primary'; // primary, danger, warning, success

            // 타입별 아이콘
            const icons = {
                primary: '❓',
                danger: '⚠️',
                warning: '⚠️',
                success: '✅',
                info: 'ℹ️'
            };
            const icon = options.icon || icons[type] || '❓';

            // 모달 HTML 생성
            const modalHTML = `
                <div class="modal-content modal-confirm modal-confirm-${type}">
                    <div class="modal-confirm-icon">
                        ${icon}
                    </div>
                    <div class="modal-confirm-title">
                        ${title}
                    </div>
                    <div class="modal-confirm-message">
                        ${message}
                    </div>
                    <div class="modal-confirm-buttons">
                        <button class="btn btn-secondary modal-confirm-cancel" id="${id}-cancel">
                            ${cancelText}
                        </button>
                        <button class="btn btn-${type} modal-confirm-ok" id="${id}-confirm">
                            ${confirmText}
                        </button>
                    </div>
                </div>
            `;

            // 기존 모달 제거
            const existing = document.getElementById(id);
            if (existing) {
                existing.remove();
            }

            // 모달 엘리먼트 생성
            const modal = document.createElement('div');
            modal.id = id;
            modal.className = 'modal modal-confirm-wrapper';
            modal.innerHTML = modalHTML;
            document.body.appendChild(modal);

            // 버튼 이벤트 연결
            const confirmBtn = document.getElementById(`${id}-confirm`);
            const cancelBtn = document.getElementById(`${id}-cancel`);

            confirmBtn.addEventListener('click', () => {
                modal.classList.remove('show');
                setTimeout(() => {
                    modal.remove();
                    document.body.style.overflow = '';
                }, 300);
                resolve(true);
            });

            cancelBtn.addEventListener('click', () => {
                modal.classList.remove('show');
                setTimeout(() => {
                    modal.remove();
                    document.body.style.overflow = '';
                }, 300);
                resolve(false);
            });

            // ESC 키 처리
            const handleEscape = (e) => {
                if (e.key === 'Escape') {
                    modal.classList.remove('show');
                    setTimeout(() => {
                        modal.remove();
                        document.body.style.overflow = '';
                    }, 300);
                    document.removeEventListener('keydown', handleEscape);
                    resolve(false);
                }
            };
            document.addEventListener('keydown', handleEscape);

            // 배경 클릭 처리
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.remove('show');
                    setTimeout(() => {
                        modal.remove();
                        document.body.style.overflow = '';
                    }, 300);
                    resolve(false);
                }
            });

            // 모달 표시
            setTimeout(() => {
                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
                confirmBtn.focus(); // 확인 버튼에 포커스
            }, 10);
        });
    },

    /**
     * 모달 열기 (기존 함수 래핑)
     */
    open: openModal,

    /**
     * 모달 닫기 (기존 함수 래핑)
     */
    close: closeModal,

    /**
     * 모든 모달 닫기 (기존 함수 래핑)
     */
    closeAll: closeAllModals
};
