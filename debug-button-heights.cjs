/**
 * 버튼 높이 문제 정확한 디버깅
 */

const { chromium } = require('playwright');

async function debugButtonHeights() {
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox'] 
    });
    
    try {
        const page = await browser.newPage();
        
        console.log('🔐 DevLoginHelper로 로그인 중...');
        await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
        await page.waitForTimeout(2000);
        
        console.log('📋 커뮤니티 페이지 접근...');
        await page.goto('https://www.topmktx.com/community');
        await page.waitForTimeout(3000);
        
        // 모든 버튼의 높이 측정
        const buttonInfo = await page.evaluate(() => {
            const searchBtn = document.querySelector('.search-btn');
            const writeBtn = document.querySelector('a[href="/community/write"]');
            const searchFilter = document.querySelector('#searchFilter');
            const searchInput = document.querySelector('#searchInput');
            
            const getElementInfo = (element, name) => {
                if (!element) return { name, exists: false };
                
                const rect = element.getBoundingClientRect();
                const styles = window.getComputedStyle(element);
                
                return {
                    name,
                    exists: true,
                    height: Math.round(rect.height),
                    width: Math.round(rect.width),
                    top: Math.round(rect.top),
                    styles: {
                        minHeight: styles.minHeight,
                        padding: styles.padding,
                        fontSize: styles.fontSize,
                        lineHeight: styles.lineHeight,
                        boxSizing: styles.boxSizing
                    }
                };
            };
            
            return {
                searchBtn: getElementInfo(searchBtn, '검색 버튼'),
                writeBtn: getElementInfo(writeBtn, '글쓰기 버튼'),
                searchFilter: getElementInfo(searchFilter, '검색 필터'),
                searchInput: getElementInfo(searchInput, '검색 입력')
            };
        });
        
        console.log('🎯 상단 버튼들 높이 분석:');
        Object.values(buttonInfo).forEach(btn => {
            if (btn.exists) {
                console.log(`\n   ${btn.name}:`);
                console.log(`     높이: ${btn.height}px`);
                console.log(`     위치: top=${btn.top}px`);
                console.log(`     min-height: ${btn.styles.minHeight}`);
                console.log(`     padding: ${btn.styles.padding}`);
                console.log(`     font-size: ${btn.styles.fontSize}`);
            } else {
                console.log(`\n   ${btn.name}: 존재하지 않음`);
            }
        });
        
        // 검색 후에도 확인
        console.log('\n🔍 검색 후 버튼 높이 확인...');
        await page.fill('#searchInput', 'ㅁㄴㅇㅁㄴ');
        await page.click('.search-btn');
        await page.waitForTimeout(2000);
        
        const afterSearchInfo = await page.evaluate(() => {
            const writeBtn = document.querySelector('a[href="/community/write"]');
            const clearBtn = document.querySelector('a:has-text("검색 해제")');
            const searchBtn = document.querySelector('.search-btn');
            
            const getElementInfo = (element, name) => {
                if (!element) return { name, exists: false };
                
                const rect = element.getBoundingClientRect();
                const styles = window.getComputedStyle(element);
                
                return {
                    name,
                    exists: true,
                    height: Math.round(rect.height),
                    width: Math.round(rect.width),
                    top: Math.round(rect.top),
                    styles: {
                        minHeight: styles.minHeight,
                        padding: styles.padding,
                        fontSize: styles.fontSize
                    }
                };
            };
            
            return {
                writeBtn: getElementInfo(writeBtn, '상단 글쓰기 버튼'),
                clearBtn: getElementInfo(clearBtn, '검색 해제 버튼'),
                searchBtn: getElementInfo(searchBtn, '검색 버튼'),
                centerWriteBtn: (() => {
                    const centerBtn = document.querySelector('.empty-state a[href="/community/write"]');
                    return getElementInfo(centerBtn, '중앙 글쓰기 버튼');
                })()
            };
        });
        
        console.log('\n📊 검색 후 버튼 높이:');
        Object.values(afterSearchInfo).forEach(btn => {
            if (btn.exists) {
                console.log(`\n   ${btn.name}:`);
                console.log(`     높이: ${btn.height}px`);
                console.log(`     위치: top=${btn.top}px`);
                console.log(`     min-height: ${btn.styles.minHeight}`);
                console.log(`     padding: ${btn.styles.padding}`);
            }
        });
        
        // 높이 차이 계산
        if (afterSearchInfo.writeBtn.exists && afterSearchInfo.searchBtn.exists) {
            const heightDiff = Math.abs(afterSearchInfo.writeBtn.height - afterSearchInfo.searchBtn.height);
            console.log(`\n🔍 글쓰기 vs 검색 버튼 높이 차이: ${heightDiff}px`);
            console.log(`   문제 여부: ${heightDiff > 5 ? '❌ 높이 불일치' : '✅ 높이 일치'}`);
        }
        
        await page.screenshot({ 
            path: `/var/www/html/topmkt/button-heights-debug.png`,
            fullPage: true
        });
        
        return { buttonInfo, afterSearchInfo };
        
    } catch (error) {
        console.error('❌ 디버그 중 오류:', error);
        return { error: error.message };
    } finally {
        await browser.close();
    }
}

debugButtonHeights().then(result => {
    console.log('\n📷 디버그 스크린샷: button-heights-debug.png');
});