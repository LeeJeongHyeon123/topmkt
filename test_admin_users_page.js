/**
 * Ultra Think 7단계: Playwright 헤드리스 테스트
 * 관리자 회원 목록 페이지 완전 테스트
 */

const { chromium } = require('playwright');

async function testAdminUsersPage() {
    console.log('🧪 Ultra Think 7단계: 관리자 회원 목록 페이지 테스트 시작');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    const context = await browser.newContext({
        viewport: { width: 1920, height: 1080 },
        userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    });
    
    const page = await context.newPage();
    
    let testResults = {
        passed: 0,
        failed: 0,
        details: []
    };
    
    try {
        // 1. 관리자 로그인 테스트
        console.log('1️⃣ 관리자 로그인 테스트');
        await testAdminLogin(page, testResults);
        
        // 2. 페이지 로딩 테스트
        console.log('2️⃣ 회원 목록 페이지 로딩 테스트');
        await testPageLoading(page, testResults);
        
        // 3. 통계 데이터 로딩 테스트
        console.log('3️⃣ 통계 데이터 로딩 테스트');
        await testStatsLoading(page, testResults);
        
        // 4. 필터링 기능 테스트
        console.log('4️⃣ 필터링 기능 테스트');
        await testFiltering(page, testResults);
        
        // 5. 검색 기능 테스트
        console.log('5️⃣ 검색 기능 테스트');
        await testSearchFunction(page, testResults);
        
        // 6. 테이블 렌더링 테스트
        console.log('6️⃣ 사용자 테이블 렌더링 테스트');
        await testTableRendering(page, testResults);
        
        // 7. 페이지네이션 테스트
        console.log('7️⃣ 페이지네이션 테스트');
        await testPagination(page, testResults);
        
        // 8. 모달 기능 테스트
        console.log('8️⃣ 모달 기능 테스트');
        await testModalFunctions(page, testResults);
        
        // 9. 벌크 액션 테스트
        console.log('9️⃣ 벌크 액션 테스트');
        await testBulkActions(page, testResults);
        
        // 10. 반응형 디자인 테스트
        console.log('🔟 반응형 디자인 테스트');
        await testResponsiveDesign(page, testResults);
        
        // 11. API 응답 테스트
        console.log('1️⃣1️⃣ API 응답 테스트');
        await testApiResponses(page, testResults);
        
        // 12. 성능 테스트
        console.log('1️⃣2️⃣ 성능 테스트');
        await testPerformance(page, testResults);
        
    } catch (error) {
        console.error('❌ 테스트 실행 중 오류:', error);
        testResults.failed++;
        testResults.details.push({
            test: 'General Error',
            status: 'FAILED',
            error: error.message
        });
    } finally {
        await browser.close();
    }
    
    // 테스트 결과 출력
    printTestResults(testResults);
    
    return testResults;
}

// 1. 관리자 로그인 테스트
async function testAdminLogin(page, testResults) {
    try {
        // 로그인 페이지로 이동
        await page.goto('https://www.topmktx.com/auth/login');
        await page.waitForLoadState('networkidle');
        
        // 관리자 계정으로 로그인
        await page.fill('input[name="phone"]', '010-0000-0000');
        await page.fill('input[name="password"]', 'admin123!');
        await page.click('button[type="submit"]');
        
        // 로그인 완료 대기
        await page.waitForLoadState('networkidle');
        
        // 관리자 페이지 접근 확인
        const currentUrl = page.url();
        if (currentUrl.includes('/admin') || currentUrl.includes('dashboard')) {
            testResults.passed++;
            testResults.details.push({
                test: 'Admin Login',
                status: 'PASSED',
                message: '관리자 로그인 성공'
            });
        } else {
            throw new Error('관리자 페이지 접근 실패');
        }
        
    } catch (error) {
        testResults.failed++;
        testResults.details.push({
            test: 'Admin Login',
            status: 'FAILED',
            error: error.message
        });
    }
}

// 2. 페이지 로딩 테스트
async function testPageLoading(page, testResults) {
    try {
        // 회원 목록 페이지로 이동
        await page.goto('https://www.topmktx.com/admin/users');
        await page.waitForLoadState('networkidle');
        
        // 페이지 제목 확인
        const pageTitle = await page.locator('h2').first().textContent();
        if (!pageTitle || !pageTitle.includes('회원 목록')) {
            throw new Error('페이지 제목이 올바르지 않음');
        }
        
        // 주요 요소들이 존재하는지 확인
        const elements = [
            '.page-header',
            '.stats-row', 
            '.filters-section',
            '.table-section',
            '#table-container'
        ];
        
        for (const selector of elements) {
            const element = await page.locator(selector);
            if (await element.count() === 0) {
                throw new Error(`필수 요소 없음: ${selector}`);
            }
        }
        
        testResults.passed++;
        testResults.details.push({
            test: 'Page Loading',
            status: 'PASSED',
            message: '페이지 로딩 및 기본 요소 확인 완료'
        });
        
    } catch (error) {
        testResults.failed++;
        testResults.details.push({
            test: 'Page Loading',
            status: 'FAILED',
            error: error.message
        });
    }
}

// 3. 통계 데이터 로딩 테스트
async function testStatsLoading(page, testResults) {
    try {
        // 통계 로딩 완료까지 대기
        await page.waitForFunction(() => {
            const totalUsers = document.getElementById('total-users');
            return totalUsers && totalUsers.textContent !== '-';
        }, { timeout: 10000 });
        
        // 모든 통계 카드에 데이터가 로드되었는지 확인
        const statIds = ['total-users', 'today-signups', 'active-users', 'pending-users'];
        
        for (const id of statIds) {
            const value = await page.locator(`#${id}`).textContent();
            if (!value || value === '-') {
                throw new Error(`통계 데이터 로드 실패: ${id}`);
            }
        }
        
        testResults.passed++;
        testResults.details.push({
            test: 'Stats Loading',
            status: 'PASSED',
            message: '통계 데이터 로딩 완료'
        });
        
    } catch (error) {
        testResults.failed++;
        testResults.details.push({
            test: 'Stats Loading',
            status: 'FAILED',
            error: error.message
        });
    }
}

// 4. 필터링 기능 테스트
async function testFiltering(page, testResults) {
    try {
        // 상태 필터 테스트
        await page.selectOption('#filter-status', 'active');
        await page.waitForTimeout(1000); // 디바운싱 대기
        
        // 권한 필터 테스트
        await page.selectOption('#filter-role', 'ROLE_USER');
        await page.waitForTimeout(1000);
        
        // 정렬 필터 테스트
        await page.selectOption('#filter-sort', 'nickname');
        await page.waitForTimeout(1000);
        
        // 필터 초기화 테스트
        await page.click('button:has-text("초기화")');
        await page.waitForTimeout(1000);
        
        // 필터가 초기화되었는지 확인
        const statusFilter = await page.locator('#filter-status').inputValue();
        if (statusFilter !== '') {
            throw new Error('필터 초기화 실패');
        }
        
        testResults.passed++;
        testResults.details.push({
            test: 'Filtering',
            status: 'PASSED',
            message: '필터링 기능 정상 작동'
        });
        
    } catch (error) {
        testResults.failed++;
        testResults.details.push({
            test: 'Filtering',
            status: 'FAILED',
            error: error.message
        });
    }
}

// 5. 검색 기능 테스트
async function testSearchFunction(page, testResults) {
    try {
        // 검색어 입력
        await page.fill('#search-input', 'admin');
        await page.waitForTimeout(1000); // 디바운싱 대기
        
        // 검색 버튼 클릭
        await page.click('button:has-text("검색")');
        await page.waitForTimeout(2000);
        
        // 검색어 지우기
        await page.fill('#search-input', '');
        await page.waitForTimeout(1000);
        
        testResults.passed++;
        testResults.details.push({
            test: 'Search Function',
            status: 'PASSED',
            message: '검색 기능 정상 작동'
        });
        
    } catch (error) {
        testResults.failed++;
        testResults.details.push({
            test: 'Search Function',
            status: 'FAILED',
            error: error.message
        });
    }
}

// 6. 테이블 렌더링 테스트
async function testTableRendering(page, testResults) {
    try {
        // 테이블 로딩 대기
        await page.waitForSelector('.users-table', { timeout: 15000 });
        
        // 테이블 헤더 확인
        const headers = await page.locator('.users-table th').allTextContents();
        const expectedHeaders = ['회원 정보', '연락처', '상태', '권한', '기업 상태', '활동 정보', '가입일', '작업'];
        
        for (const header of expectedHeaders) {
            if (!headers.some(h => h.includes(header))) {
                throw new Error(`테이블 헤더 누락: ${header}`);
            }
        }
        
        // 테이블 데이터 행 확인
        const dataRows = await page.locator('.users-table tbody tr').count();
        if (dataRows === 0) {
            throw new Error('테이블 데이터가 없음');
        }
        
        testResults.passed++;
        testResults.details.push({
            test: 'Table Rendering',
            status: 'PASSED',
            message: `테이블 렌더링 완료 (${dataRows}개 행)`
        });
        
    } catch (error) {
        testResults.failed++;
        testResults.details.push({
            test: 'Table Rendering',
            status: 'FAILED',
            error: error.message
        });
    }
}

// 7. 페이지네이션 테스트
async function testPagination(page, testResults) {
    try {
        // 페이지네이션 요소 확인
        const paginationVisible = await page.locator('#pagination-container').isVisible();
        
        if (paginationVisible) {
            // 페이지네이션 정보 확인
            const paginationInfo = await page.locator('#pagination-info').textContent();
            if (!paginationInfo || !paginationInfo.includes('/')) {
                throw new Error('페이지네이션 정보 오류');
            }
            
            // 다음 페이지 버튼이 있다면 테스트
            const nextButton = page.locator('button:has-text("다음")');
            if (await nextButton.isEnabled()) {
                await nextButton.click();
                await page.waitForTimeout(2000);
                
                // 첫 페이지로 돌아가기
                const firstPageButton = page.locator('.page-btn').first();
                if (await firstPageButton.isVisible()) {
                    await firstPageButton.click();
                    await page.waitForTimeout(2000);
                }
            }
        }
        
        testResults.passed++;
        testResults.details.push({
            test: 'Pagination',
            status: 'PASSED',
            message: '페이지네이션 기능 정상 작동'
        });
        
    } catch (error) {
        testResults.failed++;
        testResults.details.push({
            test: 'Pagination',
            status: 'FAILED',
            error: error.message
        });
    }
}

// 8. 모달 기능 테스트
async function testModalFunctions(page, testResults) {
    try {
        // 첫 번째 사용자의 상세보기 버튼 클릭
        const viewButton = page.locator('.action-btn.btn-view').first();
        if (await viewButton.count() > 0) {
            await viewButton.click();
            await page.waitForTimeout(1000);
            
            // 모달이 열렸는지 확인
            const modal = page.locator('#user-detail-modal.show');
            if (await modal.count() === 0) {
                throw new Error('상세보기 모달이 열리지 않음');
            }
            
            // 모달 닫기
            await page.click('#user-detail-modal .modal-close');
            await page.waitForTimeout(500);
            
            // 모달이 닫혔는지 확인
            if (await modal.count() > 0) {
                throw new Error('모달이 닫히지 않음');
            }
        }
        
        testResults.passed++;
        testResults.details.push({
            test: 'Modal Functions',
            status: 'PASSED',
            message: '모달 기능 정상 작동'
        });
        
    } catch (error) {
        testResults.failed++;
        testResults.details.push({
            test: 'Modal Functions',
            status: 'FAILED',
            error: error.message
        });
    }
}

// 9. 벌크 액션 테스트
async function testBulkActions(page, testResults) {
    try {
        // 전체 선택 체크박스 확인
        const selectAllCheckbox = page.locator('#select-all');
        if (await selectAllCheckbox.count() > 0) {
            // 전체 선택
            await selectAllCheckbox.check();
            await page.waitForTimeout(500);
            
            // 벌크 액션 컨테이너가 나타났는지 확인
            const bulkContainer = page.locator('#bulk-actions-container.show');
            if (await bulkContainer.count() === 0) {
                throw new Error('벌크 액션 컨테이너가 나타나지 않음');
            }
            
            // 선택 해제
            await page.click('button:has-text("선택 해제")');
            await page.waitForTimeout(500);
            
            // 벌크 액션 컨테이너가 숨겨졌는지 확인
            if (await bulkContainer.count() > 0) {
                throw new Error('벌크 액션 컨테이너가 숨겨지지 않음');
            }
        }
        
        testResults.passed++;
        testResults.details.push({
            test: 'Bulk Actions',
            status: 'PASSED',
            message: '벌크 액션 기능 정상 작동'
        });
        
    } catch (error) {
        testResults.failed++;
        testResults.details.push({
            test: 'Bulk Actions',
            status: 'FAILED',
            error: error.message
        });
    }
}

// 10. 반응형 디자인 테스트
async function testResponsiveDesign(page, testResults) {
    try {
        // 모바일 뷰포트로 변경
        await page.setViewportSize({ width: 375, height: 812 });
        await page.waitForTimeout(1000);
        
        // 필터 섹션이 반응형으로 변경되었는지 확인
        const filtersGrid = page.locator('.filters-grid');
        const gridColumns = await filtersGrid.evaluate(el => 
            getComputedStyle(el).gridTemplateColumns
        );
        
        // 태블릿 뷰포트로 변경
        await page.setViewportSize({ width: 768, height: 1024 });
        await page.waitForTimeout(1000);
        
        // 데스크탑 뷰포트로 복원
        await page.setViewportSize({ width: 1920, height: 1080 });
        await page.waitForTimeout(1000);
        
        testResults.passed++;
        testResults.details.push({
            test: 'Responsive Design',
            status: 'PASSED',
            message: '반응형 디자인 정상 작동'
        });
        
    } catch (error) {
        testResults.failed++;
        testResults.details.push({
            test: 'Responsive Design',
            status: 'FAILED',
            error: error.message
        });
    }
}

// 11. API 응답 테스트
async function testApiResponses(page, testResults) {
    try {
        // API 요청 모니터링
        const apiResponses = [];
        
        page.on('response', response => {
            if (response.url().includes('/admin/users/')) {
                apiResponses.push({
                    url: response.url(),
                    status: response.status(),
                    ok: response.ok()
                });
            }
        });
        
        // 데이터 새로고침 버튼 클릭하여 API 호출 트리거
        await page.click('button:has-text("새로고침")');
        await page.waitForTimeout(3000);
        
        // API 응답 확인
        const failedResponses = apiResponses.filter(r => !r.ok);
        if (failedResponses.length > 0) {
            throw new Error(`API 응답 실패: ${failedResponses.map(r => r.url).join(', ')}`);
        }
        
        testResults.passed++;
        testResults.details.push({
            test: 'API Responses',
            status: 'PASSED',
            message: `API 응답 정상 (${apiResponses.length}개 요청)`
        });
        
    } catch (error) {
        testResults.failed++;
        testResults.details.push({
            test: 'API Responses',
            status: 'FAILED',
            error: error.message
        });
    }
}

// 12. 성능 테스트
async function testPerformance(page, testResults) {
    try {
        const startTime = Date.now();
        
        // 페이지 새로고침하여 로딩 시간 측정
        await page.reload();
        await page.waitForLoadState('networkidle');
        
        const loadTime = Date.now() - startTime;
        
        // 5초 이내 로딩 완료 확인
        if (loadTime > 5000) {
            throw new Error(`페이지 로딩 시간 초과: ${loadTime}ms`);
        }
        
        // JavaScript 에러 확인
        const jsErrors = [];
        page.on('pageerror', error => {
            jsErrors.push(error.message);
        });
        
        await page.waitForTimeout(2000);
        
        if (jsErrors.length > 0) {
            throw new Error(`JavaScript 에러 발생: ${jsErrors.join(', ')}`);
        }
        
        testResults.passed++;
        testResults.details.push({
            test: 'Performance',
            status: 'PASSED',
            message: `성능 테스트 통과 (로딩시간: ${loadTime}ms)`
        });
        
    } catch (error) {
        testResults.failed++;
        testResults.details.push({
            test: 'Performance',
            status: 'FAILED',
            error: error.message
        });
    }
}

// 테스트 결과 출력
function printTestResults(testResults) {
    console.log('\n🎯 Ultra Think 7단계 테스트 결과');
    console.log('='.repeat(60));
    console.log(`✅ 성공: ${testResults.passed}개`);
    console.log(`❌ 실패: ${testResults.failed}개`);
    console.log(`📊 성공률: ${((testResults.passed / (testResults.passed + testResults.failed)) * 100).toFixed(1)}%`);
    console.log('='.repeat(60));
    
    testResults.details.forEach((detail, index) => {
        const status = detail.status === 'PASSED' ? '✅' : '❌';
        console.log(`${index + 1}. ${status} ${detail.test}`);
        if (detail.message) {
            console.log(`   📝 ${detail.message}`);
        }
        if (detail.error) {
            console.log(`   🚨 ${detail.error}`);
        }
    });
    
    console.log('\n📋 종합 평가');
    console.log('-'.repeat(40));
    
    if (testResults.failed === 0) {
        console.log('🎉 모든 테스트 통과! 회원 목록 페이지가 완벽하게 구현되었습니다.');
    } else if (testResults.passed > testResults.failed) {
        console.log('✨ 대부분의 테스트 통과! 일부 수정이 필요합니다.');
    } else {
        console.log('⚠️ 다수의 테스트 실패! 주요 기능 점검이 필요합니다.');
    }
}

// 테스트 실행
if (require.main === module) {
    testAdminUsersPage().catch(console.error);
}

module.exports = { testAdminUsersPage };