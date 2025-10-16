// 이벤트 페이지 디버깅 스크립트
(function() {
    
    // 페이지 로드 상태 확인
    function checkPageLoad() {
        
        // 에러 메시지 요소 확인
        const errorElements = document.querySelectorAll('[class*="error"], .alert, .error-message');
        
        errorElements.forEach((el, i) => {
        });
        
        // 시스템 오류 메시지 확인
        const systemError = document.querySelector('h1');
        if (systemError && systemError.textContent.includes('시스템 오류')) {
        }
    }
    
    // JavaScript 에러 모니터링
    function monitorErrors() {
        window.addEventListener('error', function(e) {
        });
        
        window.addEventListener('unhandledrejection', function(e) {
        });
    }
    
    // AJAX 요청 모니터링
    function monitorAjax() {
        const originalFetch = window.fetch;
        window.fetch = function(...args) {
            return originalFetch.apply(this, args)
                .then(response => {
                    if (!response.ok) {
                    }
                    return response;
                })
                .catch(error => {
                    throw error;
                });
        };
        
        // XMLHttpRequest 모니터링
        const originalXHR = window.XMLHttpRequest;
        window.XMLHttpRequest = function() {
            const xhr = new originalXHR();
            const originalOpen = xhr.open;
            xhr.open = function(method, url) {
                return originalOpen.apply(this, arguments);
            };
            
            xhr.addEventListener('load', function() {
                if (xhr.status >= 400) {
                }
            });
            
            xhr.addEventListener('error', function() {
            });
            
            return xhr;
        };
    }
    
    // DOM 변경 모니터링
    function monitorDOMChanges() {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(node => {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            if (node.textContent && node.textContent.includes('시스템 오류')) {
                            }
                            
                            if (node.classList && node.classList.contains('error')) {
                            }
                        }
                    });
                }
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
    }
    
    // 페이지 리다이렉트 감지
    function monitorRedirects() {
        let currentUrl = window.location.href;
        
        const checkRedirect = setInterval(() => {
            if (window.location.href !== currentUrl) {
                currentUrl = window.location.href;
            }
        }, 100);
        
        // 30초 후 모니터링 중지
        setTimeout(() => {
            clearInterval(checkRedirect);
        }, 30000);
    }
    
    // 초기화
    function init() {
        checkPageLoad();
        monitorErrors();
        monitorAjax();
        monitorDOMChanges();
        monitorRedirects();
        
        
    }
    
    // 페이지 로드 시 시작
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // 글로벌 함수로 노출
    window.debugEventsPage = init;
})();
