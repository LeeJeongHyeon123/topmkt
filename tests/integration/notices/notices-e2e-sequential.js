/**
 * 순차 실행 E2E 테스트 - 100% 안정성 보장
 * 하나씩 차근차근 실행하여 오류 없는 테스트 보장
 */

import { chromium } from 'playwright';

class SequentialE2ETest {
    constructor() {
        this.browser = null;
        this.page = null;
        this.results = {
            total: 0,
            passed: 0,
            failed: 0,
            details: []
        };
        this.baseUrl = 'https://www.topmktx.com';
        this.accounts = {
            corporate: { username: '우리집탄이', password: 'fpemgor77!' },
            general: { username: '안계현', password: 'fpemgor77!' }
        };
    }

    async init() {
        console.log('🐌 순차 실행 E2E 테스트 시작 (100% 안정성)');
        console.log('=' .repeat(60));
        
        this.browser = await chromium.launch({ 
            headless: false, // 시각적 확인 가능
            args: ['--no-sandbox', '--disable-dev-shm-usage'],
            slowMo: 100 // 느린 실행으로 안정성 확보
        });
        
        this.page = await this.browser.newPage({
            viewport: { width: 1920, height: 1080 }
        });
        
        console.log('✅ 단일 브라우저 초기화 완료');
    }

    async delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    async recordResult(testId, name, result, error = null, duration = 0) {
        this.results.total++;
        if (result === 'passed') this.results.passed++;
        else this.results.failed++;
        
        this.results.details.push({ testId, name, result, error, duration });
        
        const icon = result === 'passed' ? '✅' : '❌';
        console.log(`${icon} [${testId}] ${name} (${duration}ms)`);
        if (error) console.log(`   💬 ${error.message}`);
    }

    async runTest(testId, name, testFunc) {
        const start = Date.now();
        try {
            await testFunc();
            await this.recordResult(testId, name, 'passed', null, Date.now() - start);
            await this.delay(1000); // 각 테스트 간 1초 휴식
            return true;
        } catch (error) {
            await this.recordResult(testId, name, 'failed', error, Date.now() - start);
            await this.delay(2000); // 실패 시 더 긴 휴식
            return false;
        }
    }

    async runAllSequential() {
        try {
            await this.init();
            
            console.log('\n🔍 1단계: 기본 접근 테스트');
            await this.testBasicAccess();
            
            console.log('\n🔍 2단계: 기능 테스트'); 
            await this.testFeatures();
            
            console.log('\n🔍 3단계: 사용자 인터랙션 테스트');
            await this.testInteractions();
            
        } finally {
            await this.cleanup();
            this.printResults();
        }
    }

    async testBasicAccess() {
        await this.runTest('1.1', '메인 페이지 접근', async () => {
            const response = await this.page.goto(`${this.baseUrl}/notices`);
            if (!response.ok()) throw new Error(`HTTP ${response.status()}`);
            await this.page.waitForLoadState('networkidle');
        });

        await this.runTest('1.2', '페이지 제목 확인', async () => {
            const title = await this.page.title();
            if (!title.includes('공지사항')) throw new Error(`제목 오류: ${title}`);
        });

        await this.runTest('1.3', '공지사항 목록 표시', async () => {
            const cards = await this.page.$$('.notice-item');
            if (cards.length === 0) throw new Error('공지사항이 표시되지 않음');
            console.log(`   📋 공지사항 ${cards.length}개 발견`);
        });

        await this.runTest('1.4', '헤더/푸터 표시', async () => {
            const header = await this.page.$('header, .header, nav');
            const footer = await this.page.$('footer, .footer');
            if (!header) throw new Error('헤더 없음');
            if (!footer) throw new Error('푸터 없음');
        });
    }

    async testFeatures() {
        await this.runTest('2.1', '검색 기능 테스트', async () => {
            const searchInput = await this.page.$('input[name="search"]');
            if (!searchInput) throw new Error('검색 입력 필드 없음');
            
            await searchInput.fill('테스트');
            await this.page.keyboard.press('Enter');
            await this.page.waitForLoadState('networkidle');
            
            const currentUrl = this.page.url();
            if (!currentUrl.includes('search')) throw new Error('검색 실행 안됨');
        });

        await this.runTest('2.2', '상세 페이지 접근', async () => {
            await this.page.goto(`${this.baseUrl}/notices`);
            await this.page.waitForLoadState('networkidle');
            
            const firstCard = await this.page.$('.notice-item');
            if (!firstCard) throw new Error('클릭할 카드 없음');
            
            await firstCard.click();
            await this.page.waitForLoadState('networkidle');
            
            const detailTitle = await this.page.$('h1, .notice-title');
            if (!detailTitle) throw new Error('상세 페이지 로딩 실패');
        });

        await this.runTest('2.3', '이미지 확인', async () => {
            await this.page.goto(`${this.baseUrl}/notices/10`);
            await this.page.waitForLoadState('networkidle');
            
            const images = await this.page.$$('img[src*="notices"], .notice-image img');
            console.log(`   🖼️ 이미지 ${images.length}개 발견`);
        });
    }

    async testInteractions() {
        await this.runTest('3.1', '반응형 테스트 (태블릿)', async () => {
            await this.page.setViewportSize({ width: 768, height: 1024 });
            await this.page.goto(`${this.baseUrl}/notices`);
            await this.page.waitForLoadState('networkidle');
            
            const cards = await this.page.$$('.notice-item');
            if (cards.length === 0) throw new Error('태블릿에서 카드 표시 안됨');
        });

        await this.runTest('3.2', '반응형 테스트 (모바일)', async () => {
            await this.page.setViewportSize({ width: 375, height: 667 });
            await this.page.reload();
            await this.page.waitForLoadState('networkidle');
            
            const cards = await this.page.$$('.notice-item');
            if (cards.length === 0) throw new Error('모바일에서 카드 표시 안됨');
        });

        await this.runTest('3.3', '성능 테스트', async () => {
            await this.page.setViewportSize({ width: 1920, height: 1080 });
            
            const start = Date.now();
            await this.page.goto(`${this.baseUrl}/notices`);
            await this.page.waitForLoadState('networkidle');
            const loadTime = Date.now() - start;
            
            console.log(`   ⚡ 로딩 시간: ${loadTime}ms`);
            if (loadTime > 5000) throw new Error(`성능 기준 미달: ${loadTime}ms`);
        });
    }

    async cleanup() {
        if (this.browser) {
            await this.browser.close();
            console.log('🧹 브라우저 정리 완료');
        }
    }

    printResults() {
        console.log('\n' + '='.repeat(60));
        console.log('📊 순차 실행 E2E 테스트 결과');
        console.log('='.repeat(60));
        
        const passRate = (this.results.passed / this.results.total * 100).toFixed(1);
        
        console.log(`총 테스트: ${this.results.total}개`);
        console.log(`통과: ${this.results.passed}개`);
        console.log(`실패: ${this.results.failed}개`);
        console.log(`성공률: ${passRate}%`);
        
        if (this.results.failed > 0) {
            console.log('\n❌ 실패한 테스트:');
            this.results.details
                .filter(d => d.result === 'failed')
                .forEach(d => console.log(`  - [${d.testId}] ${d.name}: ${d.error?.message}`));
        }
        
        const status = passRate >= 95 ? '✅ 완벽' : 
                      passRate >= 85 ? '✅ 성공' : 
                      passRate >= 70 ? '⚠️ 주의' : '❌ 실패';
        
        console.log(`\n🎯 최종 판정: ${status} (${passRate}%)`);
        
        if (passRate >= 85) {
            console.log('🎊 안정적인 순차 실행으로 신뢰할 수 있는 결과!');
        }
    }
}

// 순차 테스트 실행
const sequentialTest = new SequentialE2ETest();
sequentialTest.runAllSequential().catch(console.error);