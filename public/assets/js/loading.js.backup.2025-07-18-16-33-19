/**
 * 🚀 탑마케팅 로딩 UI 시스템
 * 실제 로딩 상태를 표시하는 로딩 애니메이션
 */

class TopMarketingLoader {
    constructor() {
        // 싱글톤 패턴으로 단일 인스턴스만 유지
        if (window.topMarketingLoaderInstance) {
            return window.topMarketingLoaderInstance;
        }
        
        this.progress = 0;
        this.isLoading = false;
        this.activeRequests = 0; // 진행 중인 요청 수
        this.loadingMessages = [
            '데이터를 불러오는 중...',
            '서버와 연결 중...',
            '콘텐츠 준비 중...',
            '거의 완료되었습니다...'
        ];
        this.currentMessage = 0;
        this.loadingOverlay = null;
        this.progressBar = null;
        this.loadingText = null;
        this.loadingStageElement = null;
        this.minLoadingTime = 300; // 최소 로딩 시간 (밀리초)
        this.loadingStartTime = null;
        
        window.topMarketingLoaderInstance = this;
        this.init();
    }
    
    init() {
        this.createLoadingHTML();
        this.bindEvents();
    }
    
    createLoadingHTML() {
        // 로딩 오버레이가 이미 존재하면 재사용
        const existingOverlay = document.getElementById('topMarketing-loading-overlay');
        if (existingOverlay) {
            this.loadingOverlay = existingOverlay;
            this.progressBar = document.getElementById('loading-progress-bar');
            this.loadingStageElement = document.getElementById('loading-stage');
            return;
        }
        
        // body 요소가 없으면 DOM 준비까지 대기
        if (!document.body) {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.createLoadingHTML());
            } else {
                // body 요소가 곧 생성될 것이므로 짧은 지연 후 재시도
                setTimeout(() => this.createLoadingHTML(), 10);
            }
            return;
        }
        
        const loadingHTML = `
            <div id="topMarketing-loading-overlay" class="loading-overlay" style="display: none;">
                <!-- 간단한 로딩 컨테이너 -->
                <div class="loading-container">
                    <!-- 로켓 아이콘 -->
                    <div class="loading-icon">
                        <div class="rocket-main">🚀</div>
                        <div class="loading-spinner"></div>
                    </div>
                    
                    <!-- 로딩 메시지 -->
                    <div class="loading-stage" id="loading-stage">데이터를 불러오는 중...</div>
                    
                    <!-- 심플한 진행률 바 -->
                    <div class="progress-container">
                        <div class="progress-bar" id="loading-progress-bar"></div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', loadingHTML);
        
        // 요소 참조 저장
        this.loadingOverlay = document.getElementById('topMarketing-loading-overlay');
        this.progressBar = document.getElementById('loading-progress-bar');
        this.loadingStageElement = document.getElementById('loading-stage');
    }
    
    bindEvents() {
        // 페이지 로드 시작 시 로딩 표시
        this.startPageLoading();
        
        // 페이지 로드 완료 시 로딩 숨김
        if (document.readyState === 'complete') {
            // 이미 로드 완료된 경우
            setTimeout(() => this.endPageLoading(), 100);
        } else {
            window.addEventListener('load', () => {
                this.endPageLoading();
            });
        }
    }
    
    startPageLoading() {
        this.show();
        this.setMessage('페이지를 불러오는 중...');
    }
    
    endPageLoading() {
        this.hide();
    }
    
    /**
     * 로딩 시작
     */
    startLoading() {
        this.activeRequests++;
        
        if (!this.isLoading) {
            this.show();
        }
    }
    
    /**
     * 로딩 종료
     */
    endLoading() {
        this.activeRequests--;
        
        if (this.activeRequests <= 0) {
            this.activeRequests = 0;
            this.hide();
        }
    }
    
    show() {
        this.isLoading = true;
        this.loadingStartTime = Date.now();
        
        if (this.loadingOverlay) {
            this.loadingOverlay.style.display = 'flex';
            this.loadingOverlay.classList.remove('hide');
            document.body.style.overflow = 'hidden';
            
            // 진행률 초기화 및 애니메이션
            this.updateProgress(20); // 시작 시 20%
            this.startProgressAnimation();
            
            // 메시지 순환
            this.startMessageRotation();
        }
    }
    
    startProgressAnimation() {
        // 자동 진행률 증가 애니메이션
        let progress = 20;
        this.progressInterval = setInterval(() => {
            if (progress < 90 && this.isLoading) {
                progress += Math.random() * 15;
                progress = Math.min(progress, 90);
                this.updateProgress(progress);
            }
        }, 300);
    }
    
    hide() {
        
        // 최소 로딩 시간 체크
        const elapsedTime = Date.now() - this.loadingStartTime;
        const remainingTime = Math.max(0, this.minLoadingTime - elapsedTime);
        
        // 진행률 애니메이션 중지
        if (this.progressInterval) {
            clearInterval(this.progressInterval);
            this.progressInterval = null;
        }
        
        setTimeout(() => {
            this.isLoading = false;
            this.stopMessageRotation();
            
            if (this.loadingOverlay) {
                // 100% 완료 표시
                this.updateProgress(100);
                
                // 부드럽게 사라지는 효과
                this.loadingOverlay.classList.add('hide');
                
                setTimeout(() => {
                    this.loadingOverlay.style.display = 'none';
                    document.body.style.overflow = '';
                    // 진행률 초기화를 조용히 처리 (로그 출력 없이)
                    this.progress = 0;
                    if (this.progressBar) {
                        this.progressBar.style.width = '0%';
                    }
                }, 300);
            }
        }, remainingTime);
    }
    
    updateProgress(percent) {
        this.progress = Math.min(100, Math.max(0, percent));
        
        if (this.progressBar) {
            this.progressBar.style.width = this.progress + '%';
        }
        
    }
    
    startMessageRotation() {
        if (this.messageInterval) {
            clearInterval(this.messageInterval);
        }
        
        this.currentMessage = 0;
        this.updateMessage();
        
        // 1초마다 메시지 변경
        this.messageInterval = setInterval(() => {
            this.currentMessage = (this.currentMessage + 1) % this.loadingMessages.length;
            this.updateMessage();
        }, 1000);
    }
    
    stopMessageRotation() {
        if (this.messageInterval) {
            clearInterval(this.messageInterval);
            this.messageInterval = null;
        }
    }
    
    updateMessage() {
        if (this.loadingStageElement && this.loadingMessages[this.currentMessage]) {
            this.loadingStageElement.textContent = this.loadingMessages[this.currentMessage];
        }
    }
    
    // 수동 진행률 설정
    setProgress(percent) {
        this.updateProgress(percent);
    }
    
    // 수동 메시지 설정
    setMessage(message) {
        if (this.loadingStageElement) {
            this.loadingStageElement.textContent = message;
        }
    }
    
    // 스테이지 설정 (setStage 별칭)
    setStage(stage) {
        this.setMessage(stage);
    }
    
    // 커스텀 로딩 (단계별 로딩)
    custom(options = {}) {
        const {
            stages = ['처리 중...'],
            duration = 3000,
            autoHide = true
        } = options;
        
        this.show();
        
        if (stages.length > 0) {
            const stageInterval = duration / stages.length;
            let currentStageIndex = 0;
            
            // 첫 번째 스테이지 표시
            this.setMessage(stages[0]);
            this.setProgress(10);
            
            const stageTimer = setInterval(() => {
                currentStageIndex++;
                if (currentStageIndex < stages.length) {
                    this.setMessage(stages[currentStageIndex]);
                    const progress = ((currentStageIndex + 1) / stages.length) * 90 + 10;
                    this.setProgress(progress);
                } else {
                    clearInterval(stageTimer);
                    if (autoHide) {
                        this.setProgress(100);
                        setTimeout(() => {
                            this.hide();
                        }, 500);
                    }
                }
            }, stageInterval);
        }
    }
    
}

// 전역 인스턴스 생성
let topMarketingLoader;

// 페이지 로드 시작 시 즉시 로딩 표시를 위해 스크립트 실행 즉시 초기화
(function() {
    topMarketingLoader = new TopMarketingLoader();
})();

// DOM 로드 완료 시 추가 설정
document.addEventListener('DOMContentLoaded', function() {
    
    // 모든 링크에 클릭 이벤트 추가
    document.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        if (link && link.href && !link.target && !link.href.startsWith('#') && !link.href.startsWith('javascript:')) {
            // 같은 도메인 내의 일반 링크 클릭 시
            const currentDomain = window.location.hostname;
            const linkDomain = new URL(link.href).hostname;
            
            if (currentDomain === linkDomain) {
                topMarketingLoader.show();
                topMarketingLoader.setMessage('페이지를 이동하는 중...');
            }
        }
    });
    
    // 폼 제출 시에도 로딩 표시
    document.addEventListener('submit', function(e) {
        if (!e.defaultPrevented) {
            topMarketingLoader.show();
            topMarketingLoader.setMessage('처리 중...');
        }
    });
});

// 페이지 로드 이벤트 처리
window.addEventListener('load', function() {
});

// 브라우저 히스토리 변경 감지 (뒤로가기, 앞으로가기)
window.addEventListener('beforeunload', function(e) {
    if (topMarketingLoader) {
        topMarketingLoader.show();
        topMarketingLoader.setMessage('페이지를 이동하는 중...');
    }
});

// pageshow 이벤트로 캐시된 페이지 로드 감지
window.addEventListener('pageshow', function(e) {
    if (e.persisted) {
        if (topMarketingLoader) {
            topMarketingLoader.hide();
        }
    }
});

// AJAX 요청 시 로딩 표시를 위한 헬퍼 함수들
window.TopMarketingLoading = {
    show: () => {
        if (topMarketingLoader) {
            topMarketingLoader.startLoading();
        }
    },
    hide: () => {
        if (topMarketingLoader) {
            topMarketingLoader.endLoading();
        }
    },
    setProgress: (percent) => {
        if (topMarketingLoader) {
            topMarketingLoader.setProgress(percent);
        }
    },
    setMessage: (message) => {
        if (topMarketingLoader) {
            topMarketingLoader.setMessage(message);
        }
    },
    setStage: (stage) => {
        if (topMarketingLoader) {
            topMarketingLoader.setStage(stage);
        }
    },
    custom: (options) => {
        if (topMarketingLoader) {
            topMarketingLoader.custom(options);
        }
    }
};

// AJAX 요청 인터셉터 (jQuery가 있는 경우)
if (typeof $ !== 'undefined') {
    $(document).ajaxStart(function() {
        window.TopMarketingLoading.show();
    });
    
    $(document).ajaxStop(function() {
        setTimeout(() => {
            window.TopMarketingLoading.hide();
        }, 500);
    });
}

// Fetch API 인터셉터
if (window.fetch) {
    const originalFetch = window.fetch;
    window.originalFetch = originalFetch; // 전역에 원본 fetch 저장
    let activeRequests = 0;
    
    window.fetch = function(...args) {
        activeRequests++;
        if (activeRequests === 1) {
            window.TopMarketingLoading.show();
        }
        
        return originalFetch.apply(this, args)
            .finally(() => {
                activeRequests--;
                if (activeRequests === 0) {
                    setTimeout(() => {
                        window.TopMarketingLoading.hide();
                    }, 500);
                }
            });
    };
}

