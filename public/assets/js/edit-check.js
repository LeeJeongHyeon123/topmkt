/**
 * 지난 일정 수정 차단을 위한 AJAX 체크 기능
 * 수정 버튼 클릭 시 서버에서 수정 가능 여부를 확인하고 Alert로 알림
 */

// 전역 설정
window.EditChecker = {
    // API 엔드포인트
    endpoints: {
        lectures: '/api/lectures/{id}/check-editable',
        events: '/api/events/{id}/check-editable'
    },

    /**
     * 강의 수정 가능 여부 체크
     * @param {number} lectureId - 강의 ID
     * @param {function} onSuccess - 수정 가능할 때 실행할 콜백
     */
    checkLectureEditable: function(lectureId, onSuccess) {
        this.checkEditable('lectures', lectureId, onSuccess);
    },

    /**
     * 행사 수정 가능 여부 체크
     * @param {number} eventId - 행사 ID
     * @param {function} onSuccess - 수정 가능할 때 실행할 콜백
     */
    checkEventEditable: function(eventId, onSuccess) {
        this.checkEditable('events', eventId, onSuccess);
    },

    /**
     * 공통 수정 가능 여부 체크 함수
     * @param {string} type - 'lectures' 또는 'events'
     * @param {number} id - 강의/행사 ID
     * @param {function} onSuccess - 수정 가능할 때 실행할 콜백
     */
    checkEditable: function(type, id, onSuccess) {
        // 로딩 표시
        this.showLoading();

        // API URL 생성
        const url = this.endpoints[type].replace('{id}', id);

        // AJAX 요청
        fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin' // 쿠키 포함
        })
        .then(response => {
            this.hideLoading();
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // 수정 가능 - 콜백 실행
                if (typeof onSuccess === 'function') {
                    onSuccess(data.data.edit_url);
                } else {
                    // 기본 동작: 수정 페이지로 이동
                    window.location.href = data.data.edit_url;
                }
            } else {
                // 수정 불가 - Alert 표시
                this.showAlert(data.message, data.code, data.data);
            }
        })
        .catch(error => {
            this.hideLoading();
            console.error('수정 가능 여부 체크 오류:', error);
            this.showAlert('서버와 통신 중 오류가 발생했습니다. 잠시 후 다시 시도해주세요.', 'NETWORK_ERROR');
        });
    },

    /**
     * Alert 메시지 표시
     * @param {string} message - 메시지
     * @param {string} code - 오류 코드
     * @param {object} data - 추가 데이터
     */
    showAlert: function(message, code, data) {
        // 기본 alert
        alert(message);

        // 디버깅 정보 (개발 환경에서만)
        if (console && data) {
            console.group('🚫 수정 차단 정보');
            console.log('메시지:', message);
            console.log('코드:', code);
            console.log('데이터:', data);
            console.groupEnd();
        }
    },

    /**
     * 로딩 표시
     */
    showLoading: function() {
        // 간단한 로딩 표시 (커서 변경)
        document.body.style.cursor = 'wait';
    },

    /**
     * 로딩 숨김
     */
    hideLoading: function() {
        document.body.style.cursor = 'default';
    },

    /**
     * 수정 버튼에 이벤트 리스너 자동 적용
     * data-lecture-id 또는 data-event-id 속성을 가진 버튼에 자동으로 적용
     */
    initializeEditButtons: function() {
        // 강의 수정 버튼들
        document.querySelectorAll('[data-lecture-id]').forEach(button => {
            // 이미 이벤트가 적용된 버튼은 건너뛰기
            if (button.hasAttribute('data-edit-check-applied')) return;

            const lectureId = button.getAttribute('data-lecture-id');
            if (lectureId) {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.checkLectureEditable(lectureId);
                });
                button.setAttribute('data-edit-check-applied', 'true');
            }
        });

        // 행사 수정 버튼들
        document.querySelectorAll('[data-event-id]').forEach(button => {
            // 이미 이벤트가 적용된 버튼은 건너뛰기
            if (button.hasAttribute('data-edit-check-applied')) return;

            const eventId = button.getAttribute('data-event-id');
            if (eventId) {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.checkEventEditable(eventId);
                });
                button.setAttribute('data-edit-check-applied', 'true');
            }
        });

        // 🚀 v3.64.0: 성능 최적화 - 디버그 모드에서만 로그 출력
        if (window.DEBUG_MODE) {
            console.log('✅ EditChecker: 수정 버튼 이벤트 리스너 적용 완료');
        }
    }
};

// DOM 로드 완료 시 자동 초기화
document.addEventListener('DOMContentLoaded', function() {
    EditChecker.initializeEditButtons();
});

// 🚀 v3.64.0: 성능 최적화 - MutationObserver 비활성화 (성능 문제로 인해)
// 동적 댓글 추가 시에는 수동으로 EditChecker.initializeEditButtons() 호출 필요
// 예: 댓글 추가 후 → EditChecker.initializeEditButtons();

// MutationObserver는 성능 문제로 비활성화됨
// 필요시 window.enableEditCheckObserver()로 활성화 가능
window.enableEditCheckObserver = function() {
    console.log('🔍 EditChecker MutationObserver 활성화');

    let editCheckTimeout;
    const observer = new MutationObserver(function(mutations) {
        clearTimeout(editCheckTimeout);
        editCheckTimeout = setTimeout(function() {
            let hasNewButtons = false;

            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) {
                        const hasEditButtons = node.classList && (
                            node.classList.contains('btn-edit') ||
                            node.classList.contains('edit-btn')
                        );

                        if (hasEditButtons) {
                            hasNewButtons = true;
                        }
                    }
                });
            });

            if (hasNewButtons) {
                EditChecker.initializeEditButtons();
            }
        }, 300);
    });

    const targetContainer = document.querySelector('.comment-list, .main-content') || document.body;
    observer.observe(targetContainer, {
        childList: true,
        subtree: false // subtree 제거로 성능 개선
    });
};

console.log('🚀 EditChecker 로드 완료');