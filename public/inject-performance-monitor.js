// Performance Monitor
(function() {
    let clickStart = 0;
    
    document.addEventListener('click', function(e) {
        clickStart = performance.now();
    }, true);
    
    if (typeof window.openImageModal === 'function') {
        const originalOpen = window.openImageModal;
        window.openImageModal = function(...args) {
            const funcStart = performance.now();
            const delay = funcStart - clickStart;
            
            performance.mark('open-start');
            const result = originalOpen.apply(this, args);
            performance.mark('open-end');
            performance.measure('open-time', 'open-start', 'open-end');
            
            const openTime = performance.getEntriesByName('open-time')[0].duration;
            
            requestAnimationFrame(() => {
                const total = performance.now() - clickStart;
            });
            
            return result;
        };
    }
    
})();
