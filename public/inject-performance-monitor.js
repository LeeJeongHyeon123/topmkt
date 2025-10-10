// Performance Monitor
(function() {
    console.log('Performance Monitor Start');
    let clickStart = 0;
    
    document.addEventListener('click', function(e) {
        clickStart = performance.now();
        console.log('Click:', e.target.className, clickStart);
    }, true);
    
    if (typeof window.openImageModal === 'function') {
        const originalOpen = window.openImageModal;
        window.openImageModal = function(...args) {
            const funcStart = performance.now();
            const delay = funcStart - clickStart;
            console.log('openImageModal called. Delay:', delay + 'ms');
            
            performance.mark('open-start');
            const result = originalOpen.apply(this, args);
            performance.mark('open-end');
            performance.measure('open-time', 'open-start', 'open-end');
            
            const openTime = performance.getEntriesByName('open-time')[0].duration;
            console.log('Function time:', openTime + 'ms');
            
            requestAnimationFrame(() => {
                const total = performance.now() - clickStart;
                console.log('Total time:', total + 'ms');
            });
            
            return result;
        };
    }
    
    console.log('Monitor ready. Click an image.');
})();
