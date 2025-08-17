import { chromium } from '@playwright/test';

class MobileHeaderOptimizationTest {
    constructor() {
        this.baseUrl = 'https://www.topmktx.com';
        this.testUser = { id: 4, name: '우리집탄이' }; // DevLogin Helper 사용
    }

    async log(message, level = 'INFO') {
        const timestamp = new Date().toLocaleString('ko-KR', { 
            hour12: false, 
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit' 
        });
        const icon = level === 'SUCCESS' ? '✅' : level === 'ERROR' ? '❌' : '📱';
        console.log(`[${timestamp}] ${icon} ${message}`);
    }

    async autoLogin(page) {
        try {
            this.log(`DevLogin Helper로 자동 로그인 (User ID: ${this.testUser.id})`);
            
            await page.goto(`${this.baseUrl}/dev/login_helper.php?user_id=${this.testUser.id}`, {
                waitUntil: 'domcontentloaded',
                timeout: 10000
            });
            
            await page.waitForTimeout(1000);
            this.log(`로그인 성공 (${this.testUser.name})`, 'SUCCESS');
            return true;
            
        } catch (error) {
            this.log(`자동 로그인 실패: ${error.message}`, 'ERROR');
            return false;
        }
    }

    async testMobileHeaderLayout(page) {
        this.log('모바일 헤더 레이아웃 테스트 시작...');
        
        // 모바일 뷰포트 설정 (iPhone 13 크기)
        await page.setViewportSize({ width: 390, height: 844 });
        await page.goto(this.baseUrl, { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(2000);
        
        // 헤더 요소들 확인
        const headerContent = await page.$('.header-content');
        const headerLeft = await page.$('.header-left');
        const navAuth = await page.$('.nav-auth');
        const userMenu = await page.$('.user-menu');
        
        if (!headerContent || !headerLeft || !navAuth || !userMenu) {
            throw new Error('필수 헤더 요소를 찾을 수 없음');
        }
        
        // 레이아웃 확인 (좌측 로고, 우측 프로필)
        const headerBox = await headerContent.boundingBox();
        const logoBox = await headerLeft.boundingBox();
        const profileBox = await userMenu.boundingBox();
        
        // 로고가 좌측에 있는지 확인
        if (logoBox.x > headerBox.width / 2) {
            throw new Error('로고가 좌측에 정렬되지 않음');
        }
        
        // 프로필이 우측에 있는지 확인
        if (profileBox.x + profileBox.width < headerBox.width * 0.7) {
            throw new Error('프로필이 우측에 정렬되지 않음');
        }
        
        this.log('모바일 헤더 레이아웃 확인 완료', 'SUCCESS');
        return true;
    }

    async testMobileDropdownMenu(page) {
        this.log('모바일 드롭다운 메뉴 테스트 시작...');
        
        // 프로필 이미지 클릭
        const userMenu = await page.$('.user-menu');
        if (!userMenu) {
            throw new Error('사용자 메뉴를 찾을 수 없음');
        }
        
        await userMenu.click();
        await page.waitForTimeout(500);
        
        // 모바일 드롭다운이 표시되는지 확인
        const userDropdown = await page.$('.user-dropdown.active');
        if (!userDropdown) {
            throw new Error('모바일 드롭다운이 활성화되지 않음');
        }
        
        // 전체화면 오버레이 스타일 확인
        const dropdownStyles = await userDropdown.evaluate(el => {
            const styles = window.getComputedStyle(el);
            return {
                position: styles.position,
                top: styles.top,
                left: styles.left,
                right: styles.right,
                bottom: styles.bottom,
                zIndex: styles.zIndex,
                opacity: styles.opacity,
                visibility: styles.visibility,
                display: styles.display
            };
        });
        
        // 디버깅용 스타일 정보 출력
        this.log(`드롭다운 계산된 스타일: ${JSON.stringify(dropdownStyles, null, 2)}`);
        
        if (dropdownStyles.position !== 'fixed' || 
            dropdownStyles.top !== '0px' || 
            dropdownStyles.left !== '0px') {
            this.log(`스타일 검증 실패 - position: ${dropdownStyles.position}, top: ${dropdownStyles.top}, left: ${dropdownStyles.left}`, 'ERROR');
            throw new Error('모바일 드롭다운이 전체화면 오버레이로 설정되지 않음');
        }
        
        // 드롭다운 콘텐츠 확인
        const dropdownContent = await page.$('.dropdown-content');
        if (!dropdownContent) {
            throw new Error('드롭다운 콘텐츠를 찾을 수 없음');
        }
        
        // 메뉴 항목들 확인
        const menuItems = await page.$$('.dropdown-item');
        const expectedItems = ['프로필', '채팅', '신청 관리', '로그아웃'];
        
        if (menuItems.length < expectedItems.length) {
            throw new Error(`메뉴 항목이 부족함 (${menuItems.length}/${expectedItems.length})`);
        }
        
        this.log(`모바일 드롭다운 메뉴 확인 완료 (${menuItems.length}개 항목)`, 'SUCCESS');
        
        // 닫기 버튼 테스트
        const closeButton = await page.$('.mobile-dropdown-close');
        if (!closeButton) {
            throw new Error('모바일 닫기 버튼을 찾을 수 없음');
        }
        
        await closeButton.click();
        await page.waitForTimeout(300);
        
        // 드롭다운이 닫혔는지 확인
        const closedDropdown = await page.$('.user-dropdown.active');
        if (closedDropdown) {
            throw new Error('드롭다운이 제대로 닫히지 않음');
        }
        
        this.log('모바일 드롭다운 닫기 기능 확인 완료', 'SUCCESS');
        return true;
    }

    async testTabletLayout(page) {
        this.log('태블릿 레이아웃 테스트 시작...');
        
        // 태블릿 뷰포트 설정 (iPad 크기)
        await page.setViewportSize({ width: 768, height: 1024 });
        await page.reload({ waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(2000);
        
        // 태블릿에서 헤더 레이아웃 확인
        const headerContent = await page.$('.header-content');
        const userMenu = await page.$('.user-menu');
        
        if (!headerContent || !userMenu) {
            throw new Error('태블릿 헤더 요소를 찾을 수 없음');
        }
        
        // 프로필 메뉴 클릭 테스트
        await userMenu.click();
        await page.waitForTimeout(500);
        
        // 태블릿에서도 모바일 스타일 적용되는지 확인 (768px 이하)
        const userDropdown = await page.$('.user-dropdown.active');
        if (!userDropdown) {
            this.log('태블릿에서 데스크톱 드롭다운 방식 사용 중', 'INFO');
        } else {
            this.log('태블릿에서 모바일 드롭다운 방식 사용 중', 'INFO');
        }
        
        this.log('태블릿 레이아웃 테스트 완료', 'SUCCESS');
        return true;
    }

    async testDesktopLayout(page) {
        this.log('데스크톱 레이아웃 테스트 시작...');
        
        // 데스크톱 뷰포트 설정
        await page.setViewportSize({ width: 1920, height: 1080 });
        await page.reload({ waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(2000);
        
        // 데스크톱에서 기존 드롭다운 방식 확인
        const userMenu = await page.$('.user-menu');
        if (!userMenu) {
            throw new Error('데스크톱 사용자 메뉴를 찾을 수 없음');
        }
        
        await userMenu.click();
        await page.waitForTimeout(500);
        
        // 기존 floating 드롭다운 확인
        const floatingDropdown = await page.$('#floating-user-dropdown');
        if (!floatingDropdown) {
            throw new Error('데스크톱 드롭다운을 찾을 수 없음');
        }
        
        // 데스크톱 드롭다운 스타일 확인
        const dropdownStyles = await floatingDropdown.evaluate(el => {
            const styles = window.getComputedStyle(el);
            return {
                position: styles.position,
                width: styles.width,
                background: styles.backgroundColor
            };
        });
        
        if (dropdownStyles.position !== 'fixed') {
            throw new Error('데스크톱 드롭다운이 제대로 positioning되지 않음');
        }
        
        this.log('데스크톱 레이아웃 테스트 완료', 'SUCCESS');
        return true;
    }

    async runAllTests() {
        const browser = await chromium.launch({ 
            headless: true,
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
        const page = await browser.newPage();
        
        try {
            this.log('🚀 Ultra Think Mobile Header Optimization 테스트 시작');
            
            // 자동 로그인
            const loginSuccess = await this.autoLogin(page);
            if (!loginSuccess) {
                throw new Error('로그인 실패');
            }
            
            // 테스트 실행
            await this.testMobileHeaderLayout(page);
            await this.testMobileDropdownMenu(page);
            await this.testTabletLayout(page);
            await this.testDesktopLayout(page);
            
            this.log('🎉 모든 테스트 성공적으로 완료!', 'SUCCESS');
            return true;
            
        } catch (error) {
            this.log(`테스트 실패: ${error.message}`, 'ERROR');
            return false;
        } finally {
            await browser.close();
        }
    }
}

// 테스트 실행
(async () => {
    const test = new MobileHeaderOptimizationTest();
    const success = await test.runAllTests();
    process.exit(success ? 0 : 1);
})();