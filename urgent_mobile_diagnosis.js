import { spawn } from 'child_process';
import fs from 'fs';

// MCP 서버와 통신하는 함수
function callMCP(method, params) {
    return new Promise((resolve, reject) => {
        const message = {
            jsonrpc: "2.0",
            id: Date.now(),
            method: method,
            params: params
        };

        const child = spawn('node', ['playwright-mcp-server.js'], {
            stdio: ['pipe', 'pipe', 'pipe']
        });

        child.stdin.write(JSON.stringify(message) + '\n');
        child.stdin.end();

        let output = '';
        child.stdout.on('data', (data) => {
            output += data.toString();
        });

        child.on('close', (code) => {
            try {
                const lines = output.trim().split('\n');
                const lastLine = lines[lines.length - 1];
                if (lastLine.includes('"result"')) {
                    const response = JSON.parse(lastLine);
                    resolve(response.result);
                } else {
                    reject(new Error('Invalid response'));
                }
            } catch (e) {
                reject(e);
            }
        });
    });
}

async function diagnoseMobileUI() {
    console.log('🚨 강의 상세 페이지 모바일 UI 긴급 진단 시작...\n');

    try {
        // 1. 브라우저 실행 (모바일 사이즈)
        console.log('📱 모바일 뷰포트로 브라우저 실행...');
        await callMCP('launch_browser', {
            browser: 'chromium',
            headless: true,
            viewport: { width: 375, height: 667 } // iPhone SE 크기
        });

        // 2. 강의 상세 페이지로 이동
        console.log('🔄 강의 상세 페이지 이동...');
        await callMCP('navigate', {
            url: 'https://www.topmktx.com/lectures/3?view=list',
            waitUntil: 'networkidle'
        });

        // 3. 페이지 로딩 대기
        await new Promise(resolve => setTimeout(resolve, 3000));

        // 4. 모바일 스크린샷 촬영
        console.log('📸 모바일 스크린샷 촬영...');
        await callMCP('screenshot', {
            path: '/var/www/html/topmkt/lectures_detail_mobile_broken.png',
            fullPage: true,
            format: 'png'
        });

        // 5. 소형 모바일 뷰포트로 변경
        console.log('📱 소형 모바일 뷰포트로 변경...');
        await callMCP('evaluate', {
            script: `
                // 뷰포트 크기 변경
                window.resizeTo(320, 568);
                // 페이지 다시 로드
                location.reload();
            `
        });

        await new Promise(resolve => setTimeout(resolve, 3000));

        // 6. 소형 모바일 스크린샷 촬영
        console.log('📸 소형 모바일 스크린샷 촬영...');
        await callMCP('screenshot', {
            path: '/var/www/html/topmkt/lectures_detail_small_broken.png',
            fullPage: true,
            format: 'png'
        });

        // 7. 페이지 구조 분석
        console.log('🔍 페이지 구조 분석...');
        const pageContent = await callMCP('get_page_content');
        
        // 8. JavaScript 에러 확인
        const jsErrors = await callMCP('evaluate', {
            script: `
                // 콘솔 에러 수집
                window.errors = window.errors || [];
                window.errors;
            `
        });

        // 9. 모바일 특화 문제점 분석
        const mobileIssues = await callMCP('evaluate', {
            script: `
                const issues = [];
                
                // 1. 수평 스크롤 확인
                if (document.body.scrollWidth > window.innerWidth) {
                    issues.push('수평 스크롤 발생: ' + document.body.scrollWidth + 'px > ' + window.innerWidth + 'px');
                }
                
                // 2. 폰트 크기 확인
                const smallTexts = Array.from(document.querySelectorAll('*')).filter(el => {
                    const style = window.getComputedStyle(el);
                    const fontSize = parseFloat(style.fontSize);
                    return fontSize < 14 && el.textContent.trim().length > 0;
                });
                if (smallTexts.length > 0) {
                    issues.push('작은 폰트 크기 요소: ' + smallTexts.length + '개');
                }
                
                // 3. 터치 타겟 크기 확인
                const smallButtons = Array.from(document.querySelectorAll('button, a, input[type="button"]')).filter(el => {
                    const rect = el.getBoundingClientRect();
                    return rect.width < 44 || rect.height < 44;
                });
                if (smallButtons.length > 0) {
                    issues.push('작은 터치 타겟: ' + smallButtons.length + '개');
                }
                
                // 4. 고정된 너비 요소 확인
                const fixedWidthElements = Array.from(document.querySelectorAll('*')).filter(el => {
                    const style = window.getComputedStyle(el);
                    return style.width && style.width.includes('px') && parseInt(style.width) > window.innerWidth;
                });
                if (fixedWidthElements.length > 0) {
                    issues.push('고정 너비 초과 요소: ' + fixedWidthElements.length + '개');
                }
                
                // 5. 레이아웃 구조 확인
                const mainContent = document.querySelector('.lecture-content');
                if (mainContent) {
                    const style = window.getComputedStyle(mainContent);
                    issues.push('메인 콘텐츠 그리드: ' + style.gridTemplateColumns);
                }
                
                return issues;
            `
        });

        // 10. 브라우저 닫기
        await callMCP('close_browser');

        // 결과 보고서 생성
        const report = {
            timestamp: new Date().toISOString(),
            viewport: '375x667 (iPhone SE)',
            issues: mobileIssues.content?.[0]?.text || 'Unable to analyze',
            jsErrors: jsErrors.content?.[0]?.text || 'No errors detected',
            screenshots: [
                '/var/www/html/topmkt/lectures_detail_mobile_broken.png',
                '/var/www/html/topmkt/lectures_detail_small_broken.png'
            ]
        };

        console.log('\n📋 진단 완료! 결과:');
        console.log('=====================================');
        console.log('🔍 발견된 문제점들:');
        console.log(report.issues);
        console.log('\n📸 생성된 스크린샷:');
        report.screenshots.forEach(path => console.log('  - ' + path));
        
        // 보고서 파일 저장
        fs.writeFileSync('/var/www/html/topmkt/mobile_ui_diagnosis_report.json', JSON.stringify(report, null, 2));
        console.log('\n💾 상세 보고서 저장됨: mobile_ui_diagnosis_report.json');

    } catch (error) {
        console.error('❌ 진단 중 오류 발생:', error);
    }
}

diagnoseMobileUI();