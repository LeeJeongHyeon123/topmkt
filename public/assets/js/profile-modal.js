/**
 * 프로필 이미지 모달 통합 클래스
 * 
 * 기존 7개 파일에서 중복되던 프로필 이미지 모달 기능을 통합하여
 * 코드 중복을 제거하고 일관된 UX를 제공합니다.
 * 
 * 주요 기능:
 * - API 기반 프로필 이미지 로딩
 * - 직접 이미지 표시  
 * - 동적 모달 HTML 생성
 * - 로딩 상태 관리
 * - 에러 처리
 * - ESC 키 핸들링
 * 
 * @version 1.0.0
 * @author Claude (Anthropic)
 * @date 2025-08-11
 */
class ProfileImageModal {
    constructor() {
        this.modal = null;
        this.modalImage = null;
        this.modalUserName = null;
        this.initialized = false;
        this.escKeyHandler = null;
        
        // 초기화
        this.init();
    }

    /**
     * 모달 시스템 초기화
     */
    init() {
        if (this.initialized) return;
        
        this.createModalHTML();
        this.bindEvents();
        this.initialized = true;
        
    }

    /**
     * 모달 HTML을 동적으로 생성 (HTML 중복 제거)
     */
    createModalHTML() {
        // 이미 모달이 존재하면 제거
        const existingModal = document.getElementById('profileImageModal');
        if (existingModal) {
            existingModal.remove();
        }

        const modalHTML = `
        <div id="profileImageModal" class="profile-image-modal" onclick="window.profileModal.close()">
            <div class="modal-content" onclick="event.stopPropagation()">
                <div class="modal-header">
                    <h3 id="modalUserName">사용자 프로필</h3>
                    <button class="modal-close" onclick="window.profileModal.close()">&times;</button>
                </div>
                <div class="modal-body">
                    <img id="modalProfileImage" src="" alt="프로필 이미지" 
                         style="max-width: 100%; max-height: 80vh; border-radius: 8px; display: none;">
                    <div id="profileImageSpinner" class="spinner-container" style="display: none;">
                        <div class="spinner"></div>
                        <p>이미지를 불러오고 있습니다...</p>
                    </div>
                </div>
            </div>
        </div>`;

        // body에 추가
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // DOM 요소 캐싱
        this.modal = document.getElementById('profileImageModal');
        this.modalImage = document.getElementById('modalProfileImage');
        this.modalUserName = document.getElementById('modalUserName');
        this.spinner = document.getElementById('profileImageSpinner');
    }

    /**
     * 이벤트 바인딩
     */
    bindEvents() {
        // ESC 키 핸들러 생성
        this.escKeyHandler = (event) => {
            if (event.key === 'Escape') {
                this.close();
            }
        };
    }

    /**
     * 프로필 이미지 모달 표시 (통합 인터페이스)
     * 
     * @param {number|string} userIdOrImageSrc - 사용자 ID (API 호출용) 또는 직접 이미지 URL
     * @param {string} userName - 사용자 이름
     * @param {boolean} isDirect - 직접 이미지 URL 사용 여부 (기본값: false, API 호출)
     */
    async show(userIdOrImageSrc, userName, isDirect = false) {
        if (!userIdOrImageSrc || !userName) {
            return;
        }

        // 알려진 존재하지 않는 사용자 ID들 차단
        const invalidUserIds = []; // 데이터 수정 완료로 차단 목록 비움
        if (!isDirect && invalidUserIds.includes(parseInt(userIdOrImageSrc))) {
            alert('이 사용자의 프로필 정보를 찾을 수 없습니다.');
            return;
        }


        try {
            if (isDirect) {
                // 직접 이미지 표시 모드 (lectures, events)
                this.showDirectImage(userIdOrImageSrc, userName);
            } else {
                // API 호출 모드 (community, notices, chat)
                await this.fetchAndShowImage(userIdOrImageSrc, userName);
            }
        } catch (error) {
            this.showError('프로필 이미지를 불러올 수 없습니다.');
        }
    }

    /**
     * 직접 이미지 표시 (lectures, events 페이지용)
     * 
     * @param {string} imageSrc - 이미지 URL
     * @param {string} userName - 사용자 이름
     */
    showDirectImage(imageSrc, userName) {
        if (!imageSrc || imageSrc.trim() === '') {
            alert('원본 프로필 이미지를 찾을 수 없습니다.');
            return;
        }


        // 모달 열기 및 사용자 이름 설정
        this.modalUserName.textContent = userName + '의 프로필';
        this.openModal();

        // 이미지 미리 로딩 후 표시
        this.loadAndDisplayImage(imageSrc);
    }

    /**
     * API를 통한 프로필 이미지 가져오기 (community, notices, chat 페이지용)
     * 
     * @param {number} userId - 사용자 ID
     * @param {string} userName - 사용자 이름
     */
    async fetchAndShowImage(userId, userName) {

        // 모달 열기 및 로딩 상태 표시
        this.modalUserName.textContent = userName + '의 프로필';
        this.openModal();
        this.showSpinner();

        try {
            // fetch 함수 결정 (chat 페이지는 chatFetch, 나머지는 fetch)
            const fetchFunction = typeof chatFetch !== 'undefined' ? chatFetch : fetch;
            
            const response = await fetchFunction(`/api/users/${userId}/profile-image`);

            if (!response.ok) {
                if (response.status === 404) {
                    throw new Error('사용자 정보를 찾을 수 없습니다.');
                }
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            // 안전한 응답 파싱 (data.data || data 패턴)
            const imageData = this.parseApiResponse(data);

            const originalImage = imageData.original_image;
            if (!originalImage) {
                throw new Error('원본 이미지를 찾을 수 없습니다.');
            }

            // 이미지 로딩 및 표시
            this.hideSpinner();
            this.loadAndDisplayImage(originalImage);

        } catch (error) {
            this.hideSpinner();
            this.showError('프로필 이미지를 불러오는 중 오류가 발생했습니다.');
        }
    }

    /**
     * 안전한 API 응답 파싱 (data.data || data 패턴 통일)
     * 
     * @param {Object} data - API 응답 데이터
     * @returns {Object} - 파싱된 이미지 데이터
     */
    parseApiResponse(data) {
        // 중첩된 data 구조 처리 (data.data가 있으면 사용, 없으면 data 직접 사용)
        return data.data || data;
    }

    /**
     * 이미지 로딩 및 표시
     * 
     * @param {string} imageSrc - 이미지 URL
     */
    loadAndDisplayImage(imageSrc) {

        const img = new Image();
        
        img.onload = () => {
            this.modalImage.src = imageSrc;
            this.modalImage.style.display = 'block';
        };
        
        img.onerror = () => {
            this.showError('이미지를 로딩할 수 없습니다.');
        };
        
        img.src = imageSrc;
    }

    /**
     * 로딩 스피너 표시
     */
    showSpinner() {
        if (this.spinner) {
            this.spinner.style.display = 'block';
        }
        if (this.modalImage) {
            this.modalImage.style.display = 'none';
        }
    }

    /**
     * 로딩 스피너 숨김
     */
    hideSpinner() {
        if (this.spinner) {
            this.spinner.style.display = 'none';
        }
    }

    /**
     * 에러 메시지 표시
     * 
     * @param {string} message - 에러 메시지
     */
    showError(message) {
        this.hideSpinner();
        
        if (this.modalImage) {
            this.modalImage.style.display = 'none';
        }
        
        // 에러 메시지 표시
        const modalBody = this.modal.querySelector('.modal-body');
        modalBody.innerHTML = `
            <div style="text-align: center; padding: 40px; color: #e53e3e;">
                <i data-lucide="alert-triangle" style="width: 2rem; height: 2rem; margin-bottom: 10px;"></i>
                <p>${message}</p>
            </div>
        `;
        // Lucide 아이콘 렌더링
        if (window.lucide) {
            window.lucide.createIcons();
        }
    }

    /**
     * 모달 열기
     */
    openModal() {
        if (this.modal) {
            this.modal.style.display = 'block';
            
            // ESC 키 이벤트 추가
            document.addEventListener('keydown', this.escKeyHandler);
            
        }
    }

    /**
     * 모달 닫기
     */
    close() {
        if (this.modal) {
            this.modal.style.display = 'none';
            
            // ESC 키 이벤트 제거
            document.removeEventListener('keydown', this.escKeyHandler);
            
            // 상태 초기화
            this.hideSpinner();
            if (this.modalImage) {
                this.modalImage.style.display = 'none';
                this.modalImage.src = '';
            }
            
        }
    }

    /**
     * 기존 함수와의 호환성을 위한 래퍼 함수들
     * (기존 코드에서 직접 호출하던 함수들)
     */
    
    // 직접 이미지 표시 (기존 showProfileImageModal 호환)
    showProfileImageModal(imageSrc, userName) {
        return this.show(imageSrc, userName, true);
    }
    
    // API 통한 이미지 가져오기 (기존 fetchProfileImage 호환)
    fetchProfileImage(userId, userName) {
        return this.show(userId, userName, false);
    }
    
    // 모달 닫기 (기존 closeProfileImageModal 호환)
    closeProfileImageModal() {
        return this.close();
    }
}

// 전역 인스턴스 생성 (모든 페이지에서 사용 가능)
window.profileModal = new ProfileImageModal();

// 기존 함수들을 전역 스코프에서도 사용할 수 있도록 래퍼 생성
window.showProfileImageModal = (imageSrc, userName) => {
    return window.profileModal.showProfileImageModal(imageSrc, userName);
};

window.fetchProfileImage = (userId, userName) => {
    return window.profileModal.fetchProfileImage(userId, userName);
};

window.closeProfileImageModal = () => {
    return window.profileModal.closeProfileImageModal();
};

