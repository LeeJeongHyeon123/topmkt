/**
 * 브라우저 확장 프로그램 관련 에러 억제
 * "A listener indicated an asynchronous response by returning true" 에러는 
 * 주로 브라우저 확장 프로그램에서 발생하는 정상적인 동작입니다.
 */

(function() {
    'use strict';
    
    // 원본 console.error 저장

    
    // 억제할 에러 패턴들
    const suppressedErrors = [
        /A listener indicated an asynchronous response by returning true/,
        /message channel closed before a response was received/,
        /Could not establish connection\. Receiving end does not exist/,
        /Extension context invalidated/
    ];
    
    // console.error 오버라이드
    console.error = function(...args) {
        const message = args.join(' ');
        
        // 억제할 에러인지 확인
        const shouldSuppress = suppressedErrors.some(pattern => 
            pattern.test(message)
        );
        
        if (!shouldSuppress) {
            // 일반 에러는 그대로 출력
            originalConsoleError.apply(console, args);
        } else {
            // 디버그 모드에서만 출력
            if (window.location.search.includes('debug=true')) {
                originalConsoleError.apply(console, ['[SUPPRESSED]', ...args]);
            }
        }
    };
    
    // 전역 에러 핸들러
    window.addEventListener('error', function(event) {
        const message = event.message || '';
        
        // 브라우저 확장 프로그램 관련 에러 억제
        const isExtensionError = suppressedErrors.some(pattern => 
            pattern.test(message)
        );
        
        if (isExtensionError) {
            event.preventDefault();
            return false;
        }
    }, true);
    
    // Promise rejection 핸들러
    window.addEventListener('unhandledrejection', function(event) {
        const reason = event.reason?.message || event.reason || '';
        
        // 브라우저 확장 프로그램 관련 에러 억제
        const isExtensionError = suppressedErrors.some(pattern => 
            pattern.test(reason)
        );
        
        if (isExtensionError) {
            event.preventDefault();
            return false;
        }
    });
    
})();