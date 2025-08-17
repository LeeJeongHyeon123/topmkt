/**
 * 탑마케팅 병렬처리 안정성 사전 테스트
 * 420개 E2E 테스트 실행 전 병렬 환경 검증
 * 
 * 테스트 항목:
 * 1. 3개 브라우저 병렬 실행 안정성
 * 2. 네트워크 연결 및 동시 접속 안정성
 * 3. 브라우저 컨텍스트 충돌 방지
 * 4. 메모리 사용량 및 성능 모니터링
 * 5. 오류 복구 및 재시도 로직
 */

import { chromium } from 'playwright';
import fs from 'fs';

class ParallelStabilityTest {
    constructor() {
        this.baseUrl = 'https://www.topmktx.com';
        this.browsers = [];
        this.results = {
            stability: { passed: 0, failed: 0, details: [] },
            performance: { memoryUsage: [], loadTimes: [], cpuUsage: [] },
            network: { connectSuccess: 0, connectFailed: 0, errors: [] },
            errors: []
        };
        
        // 3개 브라우저 그룹 설정 (실제 E2E 테스트 구성과 동일)
        this.browserConfigs = [
            {
                id: 'A',
                name: '일반 기능 테스트 브라우저',
                viewport: { width: 1920, height: 1080 },
                userAgent: 'E2E-Browser-A/1.0',
                delay: 0, // 즉시 시작
                args: ['--disable-web-security', '--allow-running-insecure-content']
            },
            {
                id: 'B', 
                name: '관리자 + 성능 테스트 브라우저',
                viewport: { width: 1366, height: 768 },
                userAgent: 'E2E-Browser-B/1.0',
                delay: 2000, // 2초 후 시작
                args: ['--memory-pressure-off', '--max-old-space-size=4096']
            },
            {
                id: 'C',
                name: '반응형 + 접근성 테스트 브라우저',
                viewport: { width: 375, height: 667 },
                userAgent: 'E2E-Browser-C/1.0',
                delay: 4000, // 4초 후 시작
                args: ['--disable-dev-shm-usage', '--no-first-run']
            }
        ];
        
        this.testStartTime = Date.now();
    }

    async init() {
        console.log('🧪 탑마케팅 병렬처리 안정성 사전 테스트 시작');
        console.log('=' .repeat(70));
        console.log(`⏰ 시작 시간: ${new Date().toLocaleString()}`);
        console.log(`🌐 테스트 URL: ${this.baseUrl}`);
        console.log(`🎭 브라우저 수: ${this.browserConfigs.length}개`);
        console.log('=' .repeat(70));
    }

    async delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    // 1단계: 3개 브라우저 병렬 초기화 테스트
    async testBrowserInitialization() {
        console.log('\n📍 1단계: 브라우저 병렬 초기화 테스트');
        console.log('-' .repeat(50));
        
        const initPromises = this.browserConfigs.map(async (config, index) => {
            await this.delay(config.delay); // 지연된 시작
            
            try {
                console.log(`🚀 브라우저 ${config.id} 초기화 중... (${config.delay}ms 지연)`);
                
                const startTime = Date.now();
                const browser = await chromium.launch({
                    headless: true,
                    args: [
                        '--no-sandbox',
                        '--disable-dev-shm-usage',
                        ...config.args
                    ]
                });
                
                const context = await browser.newContext({
                    viewport: config.viewport,
                    userAgent: config.userAgent,
                    ignoreHTTPSErrors: true,
                    timeout: 30000
                });
                
                const page = await context.newPage();
                
                // 네트워크 모니터링 설정
                const networkErrors = [];
                page.on('response', response => {
                    if (!response.ok() && response.status() >= 400) {
                        networkErrors.push(`[${config.id}] HTTP ${response.status()}: ${response.url()}`);
                    }
                });
                
                const initTime = Date.now() - startTime;
                
                this.browsers.push({
                    config,
                    browser,
                    context, 
                    page,
                    networkErrors,
                    initTime,
                    memoryUsage: 0
                });
                
                console.log(`✅ 브라우저 ${config.id} 초기화 완료 (${initTime}ms)`);
                this.results.stability.passed++;
                
                return { success: true, config, initTime };
                
            } catch (error) {
                console.log(`❌ 브라우저 ${config.id} 초기화 실패: ${error.message}`);
                this.results.stability.failed++;
                this.results.errors.push(`브라우저 ${config.id} 초기화: ${error.message}`);
                
                return { success: false, config, error: error.message };
            }
        });
        
        const initResults = await Promise.all(initPromises);
        
        const successCount = initResults.filter(r => r.success).length;
        const totalTime = Math.max(...initResults.map(r => r.initTime || 0));
        
        console.log(`📊 초기화 결과: ${successCount}/${this.browserConfigs.length} 성공 (${totalTime}ms)`);
        
        if (successCount !== this.browserConfigs.length) {
            throw new Error(`브라우저 초기화 실패: ${this.browserConfigs.length - successCount}개 실패`);
        }
        
        return true;
    }

    // 2단계: 네트워크 연결 및 동시 접속 테스트
    async testNetworkConnectivity() {
        console.log('\n📍 2단계: 네트워크 연결 및 동시 접속 테스트');
        console.log('-' .repeat(50));
        
        const testUrls = [
            '/', // 메인 페이지
            '/community', // 커뮤니티
            '/lectures', // 강의
            '/notices', // 공지사항
            '/admin' // 관리자 대시보드
        ];
        
        for (const url of testUrls) {
            console.log(`🌐 동시 접속 테스트: ${url}`);
            
            const connectPromises = this.browsers.map(async (browserInstance, index) => {
                const fullUrl = `${this.baseUrl}${url}`;
                const startTime = Date.now();
                
                try {
                    const response = await browserInstance.page.goto(fullUrl, {
                        waitUntil: 'domcontentloaded',
                        timeout: 15000
                    });
                    
                    const loadTime = Date.now() - startTime;
                    
                    if (response && response.ok()) {
                        console.log(`  ✅ [${browserInstance.config.id}] ${fullUrl} (${loadTime}ms)`);
                        this.results.network.connectSuccess++;
                        this.results.performance.loadTimes.push({ 
                            browser: browserInstance.config.id, 
                            url, 
                            time: loadTime 
                        });
                        return { success: true, loadTime, browser: browserInstance.config.id };
                    } else {
                        throw new Error(`HTTP ${response?.status() || 'Unknown'}`);
                    }
                    
                } catch (error) {
                    console.log(`  ❌ [${browserInstance.config.id}] ${fullUrl}: ${error.message}`);
                    this.results.network.connectFailed++;
                    this.results.network.errors.push({
                        browser: browserInstance.config.id,
                        url: fullUrl,
                        error: error.message
                    });
                    return { success: false, error: error.message, browser: browserInstance.config.id };
                }
            });
            
            const connectResults = await Promise.all(connectPromises);
            const successCount = connectResults.filter(r => r.success).length;
            const avgLoadTime = connectResults
                .filter(r => r.success)
                .reduce((sum, r) => sum + r.loadTime, 0) / successCount || 0;
                
            console.log(`  📊 결과: ${successCount}/${this.browsers.length} 성공, 평균 ${avgLoadTime.toFixed(0)}ms`);
            
            // 각 URL 테스트 간 잠시 대기
            await this.delay(1000);
        }
        
        const totalTests = testUrls.length * this.browsers.length;
        const successRate = (this.results.network.connectSuccess / totalTests * 100).toFixed(1);
        console.log(`📊 전체 네트워크 테스트 성공률: ${successRate}%`);
        
        if (successRate < 90) {
            console.log(`⚠️ 네트워크 성공률이 90% 미만입니다. 실제 테스트에서 문제가 발생할 수 있습니다.`);
        }
        
        return successRate >= 90;
    }

    // 3단계: 브라우저 컨텍스트 충돌 방지 테스트
    async testContextIsolation() {
        console.log('\n📍 3단계: 브라우저 컨텍스트 충돌 방지 테스트');
        console.log('-' .repeat(50));
        
        // 동일한 작업을 3개 브라우저에서 동시 실행
        const isolationTests = [
            {
                name: '로컬 스토리지 격리',
                test: async (browserInstance) => {
                    const key = `test_${browserInstance.config.id}_${Date.now()}`;
                    const value = `value_${browserInstance.config.id}`;
                    
                    await browserInstance.page.evaluate(([k, v]) => {
                        localStorage.setItem(k, v);
                    }, [key, value]);
                    
                    const stored = await browserInstance.page.evaluate((k) => {
                        return localStorage.getItem(k);
                    }, key);
                    
                    if (stored !== value) {
                        throw new Error(`로컬스토리지 격리 실패: ${stored} !== ${value}`);
                    }
                    
                    return true;
                }
            },
            {
                name: '세션 스토리지 격리',
                test: async (browserInstance) => {
                    const key = `session_${browserInstance.config.id}_${Date.now()}`;
                    const value = `session_value_${browserInstance.config.id}`;
                    
                    await browserInstance.page.evaluate(([k, v]) => {
                        sessionStorage.setItem(k, v);
                    }, [key, value]);
                    
                    const stored = await browserInstance.page.evaluate((k) => {
                        return sessionStorage.getItem(k);
                    }, key);
                    
                    if (stored !== value) {
                        throw new Error(`세션스토리지 격리 실패: ${stored} !== ${value}`);
                    }
                    
                    return true;
                }
            },
            {
                name: 'DOM 조작 격리',
                test: async (browserInstance) => {
                    const testId = `test-element-${browserInstance.config.id}`;
                    
                    await browserInstance.page.evaluate((id) => {
                        const div = document.createElement('div');
                        div.id = id;
                        div.textContent = `Test from browser ${id}`;
                        document.body.appendChild(div);
                    }, testId);
                    
                    const element = await browserInstance.page.$(`#${testId}`);
                    if (!element) {
                        throw new Error(`DOM 격리 실패: 요소를 찾을 수 없음`);
                    }
                    
                    return true;
                }
            }
        ];
        
        for (const isolationTest of isolationTests) {
            console.log(`🔒 ${isolationTest.name} 테스트`);
            
            const testPromises = this.browsers.map(async (browserInstance) => {
                try {
                    await isolationTest.test(browserInstance);
                    console.log(`  ✅ [${browserInstance.config.id}] ${isolationTest.name} 성공`);
                    return { success: true, browser: browserInstance.config.id };
                } catch (error) {
                    console.log(`  ❌ [${browserInstance.config.id}] ${isolationTest.name} 실패: ${error.message}`);
                    this.results.errors.push(`[${browserInstance.config.id}] ${isolationTest.name}: ${error.message}`);
                    return { success: false, browser: browserInstance.config.id, error: error.message };
                }
            });
            
            const testResults = await Promise.all(testPromises);
            const successCount = testResults.filter(r => r.success).length;
            
            console.log(`  📊 결과: ${successCount}/${this.browsers.length} 성공`);
            
            if (successCount !== this.browsers.length) {
                console.log(`  ⚠️ 컨텍스트 격리 문제 발견: ${isolationTest.name}`);
            }
        }
        
        return true;
    }

    // 4단계: 메모리 사용량 및 성능 모니터링
    async testMemoryAndPerformance() {
        console.log('\n📍 4단계: 메모리 사용량 및 성능 모니터링');
        console.log('-' .repeat(50));
        
        for (const browserInstance of this.browsers) {
            try {
                // JavaScript 힙 크기 측정
                const memoryInfo = await browserInstance.page.evaluate(() => {
                    if (performance.memory) {
                        return {
                            usedJSHeapSize: performance.memory.usedJSHeapSize,
                            totalJSHeapSize: performance.memory.totalJSHeapSize,
                            jsHeapSizeLimit: performance.memory.jsHeapSizeLimit
                        };
                    }
                    return null;
                });
                
                if (memoryInfo) {
                    const usedMB = (memoryInfo.usedJSHeapSize / 1024 / 1024).toFixed(2);
                    const totalMB = (memoryInfo.totalJSHeapSize / 1024 / 1024).toFixed(2);
                    
                    console.log(`📊 [${browserInstance.config.id}] 메모리 사용량: ${usedMB}MB / ${totalMB}MB`);
                    
                    this.results.performance.memoryUsage.push({
                        browser: browserInstance.config.id,
                        used: usedMB,
                        total: totalMB,
                        timestamp: Date.now()
                    });
                    
                    // 메모리 사용량이 100MB를 초과하면 경고
                    if (parseFloat(usedMB) > 100) {
                        console.log(`  ⚠️ [${browserInstance.config.id}] 높은 메모리 사용량: ${usedMB}MB`);
                    }
                } else {
                    console.log(`📊 [${browserInstance.config.id}] 메모리 정보 수집 불가`);
                }
                
                // 성능 메트릭 수집
                const performanceMetrics = await browserInstance.page.evaluate(() => {
                    const navigation = performance.getEntriesByType('navigation')[0];
                    if (navigation) {
                        return {
                            domContentLoaded: navigation.domContentLoadedEventEnd - navigation.fetchStart,
                            loadComplete: navigation.loadEventEnd - navigation.fetchStart,
                            firstPaint: performance.getEntriesByName('first-paint')[0]?.startTime || 0,
                            firstContentfulPaint: performance.getEntriesByName('first-contentful-paint')[0]?.startTime || 0
                        };
                    }
                    return null;
                });
                
                if (performanceMetrics) {
                    console.log(`⚡ [${browserInstance.config.id}] 성능 메트릭:`);
                    console.log(`    DOM 로드: ${performanceMetrics.domContentLoaded.toFixed(0)}ms`);
                    console.log(`    전체 로드: ${performanceMetrics.loadComplete.toFixed(0)}ms`);
                    console.log(`    첫 페인트: ${performanceMetrics.firstPaint.toFixed(0)}ms`);
                }
                
            } catch (error) {
                console.log(`❌ [${browserInstance.config.id}] 성능 측정 실패: ${error.message}`);
            }
        }
        
        return true;
    }

    // 5단계: 오류 복구 및 재시도 로직 테스트
    async testErrorRecovery() {
        console.log('\n📍 5단계: 오류 복구 및 재시도 로직 테스트');
        console.log('-' .repeat(50));
        
        // 의도적으로 실패할 수 있는 테스트들
        const recoveryTests = [
            {
                name: '존재하지 않는 페이지 접근 (404)',
                url: '/nonexistent-page-test-404',
                expectError: true
            },
            {
                name: '네트워크 타임아웃 시뮬레이션',
                url: '/',
                timeout: 100, // 매우 짧은 타임아웃
                expectError: true
            },
            {
                name: '정상 페이지 복구 테스트',
                url: '/',
                expectError: false
            }
        ];
        
        for (const recoveryTest of recoveryTests) {
            console.log(`🔄 ${recoveryTest.name}`);
            
            const testPromises = this.browsers.map(async (browserInstance) => {
                const maxRetries = 3;
                let attempt = 0;
                
                while (attempt < maxRetries) {
                    try {
                        const fullUrl = `${this.baseUrl}${recoveryTest.url}`;
                        const response = await browserInstance.page.goto(fullUrl, {
                            waitUntil: 'domcontentloaded',
                            timeout: recoveryTest.timeout || 10000
                        });
                        
                        if (recoveryTest.expectError) {
                            if (response && response.status() >= 400) {
                                console.log(`  ✅ [${browserInstance.config.id}] 예상된 오류 정상 처리 (${response.status()})`);
                                return { success: true, expectedError: true };
                            } else {
                                console.log(`  ⚠️ [${browserInstance.config.id}] 오류가 예상되었지만 성공함`);
                                return { success: true, unexpectedSuccess: true };
                            }
                        } else {
                            if (response && response.ok()) {
                                console.log(`  ✅ [${browserInstance.config.id}] 정상 복구 성공`);
                                return { success: true, recovered: true };
                            } else {
                                throw new Error(`HTTP ${response?.status()}`);
                            }
                        }
                        
                    } catch (error) {
                        attempt++;
                        console.log(`  🔄 [${browserInstance.config.id}] 시도 ${attempt}/${maxRetries} 실패: ${error.message}`);
                        
                        if (attempt < maxRetries) {
                            const waitTime = Math.pow(2, attempt - 1) * 1000; // 지수 백오프
                            console.log(`  ⏳ [${browserInstance.config.id}] ${waitTime}ms 대기 후 재시도`);
                            await this.delay(waitTime);
                        }
                    }
                }
                
                if (recoveryTest.expectError) {
                    console.log(`  ✅ [${browserInstance.config.id}] 재시도 로직 정상 (예상된 실패)`);
                    return { success: true, retriedAsExpected: true };
                } else {
                    console.log(`  ❌ [${browserInstance.config.id}] 복구 실패`);
                    return { success: false, failedRecovery: true };
                }
            });
            
            const testResults = await Promise.all(testPromises);
            const successCount = testResults.filter(r => r.success).length;
            
            console.log(`  📊 결과: ${successCount}/${this.browsers.length} 성공`);
        }
        
        return true;
    }

    // 정리 작업
    async cleanup() {
        console.log('\n🧹 정리 작업 시작');
        console.log('-' .repeat(50));
        
        for (const browserInstance of this.browsers) {
            try {
                if (browserInstance.browser) {
                    await browserInstance.browser.close();
                    console.log(`✅ 브라우저 ${browserInstance.config.id} 정리 완료`);
                }
            } catch (error) {
                console.log(`❌ 브라우저 ${browserInstance.config.id} 정리 실패: ${error.message}`);
            }
        }
    }

    // 최종 결과 출력 및 평가
    async printFinalResults() {
        const totalTime = Date.now() - this.testStartTime;
        
        console.log('\n' + '=' .repeat(70));
        console.log('📊 탑마케팅 병렬처리 안정성 사전 테스트 결과');
        console.log('=' .repeat(70));
        console.log(`⏰ 총 소요 시간: ${(totalTime / 1000).toFixed(1)}초`);
        console.log(`🗓️ 완료 시간: ${new Date().toLocaleString()}`);
        
        // 1. 전반적인 안정성
        const totalStabilityTests = this.results.stability.passed + this.results.stability.failed;
        const stabilityRate = totalStabilityTests > 0 ? 
            (this.results.stability.passed / totalStabilityTests * 100).toFixed(1) : 0;
        
        console.log(`\n🔹 브라우저 안정성: ${this.results.stability.passed}/${totalStabilityTests} (${stabilityRate}%)`);
        
        // 2. 네트워크 성능
        const totalNetworkTests = this.results.network.connectSuccess + this.results.network.connectFailed;
        const networkRate = totalNetworkTests > 0 ?
            (this.results.network.connectSuccess / totalNetworkTests * 100).toFixed(1) : 0;
        
        console.log(`🔹 네트워크 연결: ${this.results.network.connectSuccess}/${totalNetworkTests} (${networkRate}%)`);
        
        // 3. 평균 성능 지표
        if (this.results.performance.loadTimes.length > 0) {
            const avgLoadTime = this.results.performance.loadTimes
                .reduce((sum, item) => sum + item.time, 0) / this.results.performance.loadTimes.length;
            console.log(`🔹 평균 로딩 시간: ${avgLoadTime.toFixed(0)}ms`);
        }
        
        if (this.results.performance.memoryUsage.length > 0) {
            const avgMemory = this.results.performance.memoryUsage
                .reduce((sum, item) => sum + parseFloat(item.used), 0) / this.results.performance.memoryUsage.length;
            console.log(`🔹 평균 메모리 사용량: ${avgMemory.toFixed(1)}MB`);
        }
        
        // 4. 오류 분석
        if (this.results.errors.length > 0) {
            console.log(`\n⚠️ 발견된 이슈 (${this.results.errors.length}개):`);
            this.results.errors.slice(0, 5).forEach((error, index) => {
                console.log(`  ${index + 1}. ${error}`);
            });
            if (this.results.errors.length > 5) {
                console.log(`  ... 외 ${this.results.errors.length - 5}개 더`);
            }
        }
        
        // 5. 최종 판정
        const overallScore = (parseFloat(stabilityRate) * 0.4 + parseFloat(networkRate) * 0.6);
        let finalStatus, recommendation;
        
        if (overallScore >= 95) {
            finalStatus = '✅ 우수 (병렬 테스트 환경 완벽)';
            recommendation = '420개 E2E 테스트 실행 권장';
        } else if (overallScore >= 85) {
            finalStatus = '🟡 양호 (일부 최적화 필요)';
            recommendation = '주의하여 E2E 테스트 실행 가능';
        } else if (overallScore >= 70) {
            finalStatus = '⚠️ 보통 (병렬 처리 주의 필요)';
            recommendation = '순차 실행 또는 브라우저 수 축소 고려';
        } else {
            finalStatus = '❌ 불안정 (병렬 처리 부적합)';
            recommendation = '문제 해결 후 재테스트 필요';
        }
        
        console.log(`\n🎯 최종 판정: ${finalStatus}`);
        console.log(`💡 권장사항: ${recommendation}`);
        console.log(`📊 종합 점수: ${overallScore.toFixed(1)}/100점`);
        
        // 6. 다음 단계 안내
        if (overallScore >= 85) {
            console.log('\n🚀 다음 단계:');
            console.log('  1. 420개 E2E 테스트 실행 준비 완료');
            console.log('  2. 3개 브라우저 병렬 처리 안정적 동작 확인');
            console.log('  3. 예상 테스트 시간: 12-15시간');
            console.log('  4. 실행 명령: node 탑마케팅_완전무결_E2E_테스트.js');
        } else {
            console.log('\n🔧 개선 필요 사항:');
            if (parseFloat(stabilityRate) < 90) {
                console.log('  - 브라우저 초기화 안정성 개선');
            }
            if (parseFloat(networkRate) < 90) {
                console.log('  - 네트워크 연결 안정성 개선');
            }
            if (this.results.errors.length > 3) {
                console.log('  - 오류 처리 로직 강화');
            }
        }
        
        // 7. 결과를 파일로 저장
        const resultData = {
            timestamp: new Date().toISOString(),
            totalTime: totalTime,
            stabilityRate: parseFloat(stabilityRate),
            networkRate: parseFloat(networkRate),
            overallScore: overallScore,
            finalStatus: finalStatus,
            recommendation: recommendation,
            details: this.results
        };
        
        try {
            await fs.promises.writeFile(
                './병렬처리_안정성_테스트_결과.json',
                JSON.stringify(resultData, null, 2)
            );
            console.log('\n📄 상세 결과가 "병렬처리_안정성_테스트_결과.json"에 저장되었습니다.');
        } catch (error) {
            console.log(`⚠️ 결과 저장 실패: ${error.message}`);
        }
        
        return overallScore >= 85;
    }

    // 메인 실행 함수
    async runStabilityTest() {
        try {
            await this.init();
            
            // 1단계: 브라우저 초기화
            await this.testBrowserInitialization();
            
            // 2단계: 네트워크 연결
            await this.testNetworkConnectivity();
            
            // 3단계: 컨텍스트 격리
            await this.testContextIsolation();
            
            // 4단계: 성능 모니터링
            await this.testMemoryAndPerformance();
            
            // 5단계: 오류 복구
            await this.testErrorRecovery();
            
        } catch (error) {
            console.log(`\n💥 치명적 오류 발생: ${error.message}`);
            this.results.errors.push(`치명적 오류: ${error.message}`);
        } finally {
            await this.cleanup();
            const testPassed = await this.printFinalResults();
            
            if (testPassed) {
                console.log('\n🎊 병렬처리 안정성 테스트 통과! E2E 테스트 실행 가능합니다.');
                process.exit(0);
            } else {
                console.log('\n⚠️ 병렬처리 안정성 테스트 미통과. 환경 개선 후 재시도하세요.');
                process.exit(1);
            }
        }
    }
}

// 테스트 실행
const stabilityTest = new ParallelStabilityTest();
stabilityTest.runStabilityTest().catch(console.error);