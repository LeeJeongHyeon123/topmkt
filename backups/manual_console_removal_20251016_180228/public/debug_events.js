// 이벤트 페이지 디버깅 스크립트
(function() {
    console.log('🎪 이벤트 페이지 디버깅 시작...');
    
    // 페이지 로드 상태 확인
    function checkPageLoad() {
        console.log('📄 페이지 로드 상태:');
        console.log(`   └─ readyState: ${document.readyState}`);
        console.log(`   └─ URL: ${window.location.href}`);
        console.log(`   └─ Title: ${document.title}`);
        
        // 에러 메시지 요소 확인
        const errorElements = document.querySelectorAll('[class*="error"], .alert, .error-message');
        console.log(`   └─ 에러 요소 수: ${errorElements.length}`);
        
        errorElements.forEach((el, i) => {
            console.log(`      └─ 에러 ${i + 1}: "${el.textContent.trim()}"`);
        });
        
        // 시스템 오류 메시지 확인
        const systemError = document.querySelector('h1');
        if (systemError && systemError.textContent.includes('시스템 오류')) {
            console.log('❌ 시스템 오류 페이지 감지됨!');
            console.log('   └─ 오류 메시지:', systemError.nextElementSibling?.textContent);
        }
    }
    
    // JavaScript 에러 모니터링
    function monitorErrors() {
        window.addEventListener('error', function(e) {
            console.log('🚨 JavaScript 에러 발생:');
            console.log(`   └─ 메시지: ${e.message}`);
            console.log(`   └─ 파일: ${e.filename}:${e.lineno}`);
            console.log(`   └─ 스택: ${e.error?.stack}`);
        });
        
        window.addEventListener('unhandledrejection', function(e) {
            console.log('🚨 처리되지 않은 Promise 거부:');
            console.log(`   └─ 이유: ${e.reason}`);
        });
    }
    
    // AJAX 요청 모니터링
    function monitorAjax() {
        const originalFetch = window.fetch;
        window.fetch = function(...args) {
            console.log('🌐 Fetch 요청:', args[0]);
            return originalFetch.apply(this, args)
                .then(response => {
                    console.log(`   └─ 응답: ${response.status} ${response.statusText}`);
                    if (!response.ok) {
                        console.log('   └─ ❌ 실패한 요청!');
                    }
                    return response;
                })
                .catch(error => {
                    console.log(`   └─ ❌ 네트워크 에러: ${error.message}`);
                    throw error;
                });
        };
        
        // XMLHttpRequest 모니터링
        const originalXHR = window.XMLHttpRequest;
        window.XMLHttpRequest = function() {
            const xhr = new originalXHR();
            const originalOpen = xhr.open;
            xhr.open = function(method, url) {
                console.log(`📡 XHR 요청: ${method} ${url}`);
                return originalOpen.apply(this, arguments);
            };
            
            xhr.addEventListener('load', function() {
                console.log(`   └─ XHR 응답: ${xhr.status} ${xhr.statusText}`);
                if (xhr.status >= 400) {
                    console.log('   └─ ❌ 실패한 XHR 요청!');
                    console.log(`   └─ 응답 내용: ${xhr.responseText.substring(0, 200)}...`);
                }
            });
            
            xhr.addEventListener('error', function() {
                console.log('   └─ ❌ XHR 네트워크 에러');
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
                                console.log('🚨 시스템 오류 요소 추가됨!');
                                console.log('   └─ 요소:', node);
                            }
                            
                            if (node.classList && node.classList.contains('error')) {
                                console.log('🚨 에러 클래스 요소 추가됨!');
                                console.log('   └─ 요소:', node);
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
        
        console.log('👀 DOM 변경 감시 시작');
    }
    
    // 페이지 리다이렉트 감지
    function monitorRedirects() {
        let currentUrl = window.location.href;
        
        const checkRedirect = setInterval(() => {
            if (window.location.href !== currentUrl) {
                console.log('🔄 페이지 리다이렉트 감지:');
                console.log(`   └─ 이전: ${currentUrl}`);
                console.log(`   └─ 이후: ${window.location.href}`);
                currentUrl = window.location.href;
            }
        }, 100);
        
        // 30초 후 모니터링 중지
        setTimeout(() => {
            clearInterval(checkRedirect);
            console.log('🔄 리다이렉트 모니터링 종료');
        }, 30000);
    }
    
    // 초기화
    function init() {
        console.log('\n🔍 ===== 이벤트 페이지 디버깅 시작 =====');
        checkPageLoad();
        monitorErrors();
        monitorAjax();
        monitorDOMChanges();
        monitorRedirects();
        
        console.log('\n💡 디버깅 팁:');
        console.log('1. 페이지 로드 후 몇 초 기다려보세요');
        console.log('2. JavaScript 에러가 있는지 확인하세요');
        console.log('3. 네트워크 요청이 실패하는지 확인하세요');
        console.log('4. 시스템 오류 메시지가 언제 나타나는지 확인하세요');
        console.log('5. 브라우저 캐시를 완전히 비우고 다시 시도해보세요');
        
        console.log('🔍 ===== 디버깅 준비 완료 =====\n');
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
