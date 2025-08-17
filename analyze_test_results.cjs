/**
 * 모바일 반응형 테스트 결과 분석 스크립트
 */

const fs = require('fs');

// 테스트 결과 로드
const testResults = JSON.parse(fs.readFileSync('/var/www/html/topmkt/mobile_responsive_test_results.json', 'utf8'));

console.log('📊 탑마케팅 모바일 반응형 테스트 결과 분석\n');

// 전체 요약
console.log('='.repeat(60));
console.log('전체 테스트 요약');
console.log('='.repeat(60));
console.log(`테스트 시간: ${new Date(testResults.timestamp).toLocaleString('ko-KR')}`);
console.log(`전체 점수: ${testResults.overallScore}/100`);
console.log(`총 테스트: ${testResults.summary.totalTests}개`);
console.log(`✅ 통과: ${testResults.summary.passedTests}개 (90점 이상)`);
console.log(`⚠️  경고: ${testResults.summary.warningTests}개 (70-89점)`);
console.log(`❌ 실패: ${testResults.summary.failedTests}개 (70점 미만)`);

// 페이지별 분석
console.log('\n' + '='.repeat(60));
console.log('페이지별 상세 분석');
console.log('='.repeat(60));

const pageNames = {
    'home': '홈페이지',
    'notices': '공지사항',
    'community': '커뮤니티',
    'lectures': '강의일정',
    'events': '행사일정',
    'chat': '채팅',
    'profile': '내프로필'
};

const viewportNames = {
    'iPhone_SE': 'iPhone SE (375px)',
    'iPad': 'iPad (768px)',
    'Small_Mobile': '소형모바일 (320px)'
};

// 각 페이지별 분석
for (const [pageKey, pageName] of Object.entries(pageNames)) {
    console.log(`\n📱 ${pageName}`);
    console.log('-'.repeat(40));
    
    const pageData = testResults.pages[pageKey];
    if (!pageData) {
        console.log('   데이터 없음');
        continue;
    }
    
    for (const [viewportKey, viewportName] of Object.entries(viewportNames)) {
        const testData = pageData[viewportKey];
        if (!testData || !testData.tests) {
            console.log(`   ${viewportName}: 데이터 없음`);
            continue;
        }
        
        const score = testData.score || 0;
        const tests = testData.tests;
        
        console.log(`   ${viewportName}: ${score}/100점`);
        
        // 터치 타겟 분석
        if (tests.touchTargets) {
            const tt = tests.touchTargets;
            console.log(`      터치 타겟: ${tt.passed}/${tt.total} 통과 (${tt.failed}개 실패)`);
            if (tt.details && tt.details.failed && tt.details.failed.length > 0) {
                console.log(`      실패 예시: ${tt.details.failed[0].text} (${Math.round(tt.details.failed[0].minDimension)}px)`);
            }
        }
        
        // 폰트 크기 분석
        if (tests.fontSizes) {
            const fs = tests.fontSizes;
            console.log(`      폰트 크기: 평균 ${Math.round(fs.averageSize)}px (작은 폰트 ${fs.smallFonts}개)`);
        }
        
        // 수평 스크롤 분석
        if (tests.horizontalScroll) {
            const hs = tests.horizontalScroll;
            if (hs.hasHorizontalScroll) {
                console.log(`      ⚠️ 수평 스크롤: ${hs.overflowAmount}px 넘침`);
            } else {
                console.log(`      ✅ 수평 스크롤: 문제없음`);
            }
        }
        
        // 버튼 텍스트 줄바꿈 분석
        if (tests.buttonWrapping) {
            const bw = tests.buttonWrapping;
            if (bw.wrapped > 0) {
                console.log(`      ⚠️ 버튼 줄바꿈: ${bw.wrapped}개 발견`);
            } else {
                console.log(`      ✅ 버튼 텍스트: 줄바꿈 없음`);
            }
        }
    }
}

// 문제점 요약
console.log('\n' + '='.repeat(60));
console.log('주요 문제점 요약');
console.log('='.repeat(60));

const issues = [];

// 모든 페이지의 문제점 수집
for (const [pageKey, pageName] of Object.entries(pageNames)) {
    const pageData = testResults.pages[pageKey];
    if (!pageData) continue;
    
    for (const [viewportKey, viewportName] of Object.entries(viewportNames)) {
        const testData = pageData[viewportKey];
        if (!testData || !testData.tests) continue;
        
        const tests = testData.tests;
        
        // 터치 타겟 문제
        if (tests.touchTargets && tests.touchTargets.failed > 0) {
            issues.push({
                page: pageName,
                viewport: viewportName,
                type: '터치 타겟',
                count: tests.touchTargets.failed,
                detail: `${tests.touchTargets.failed}개 요소가 44px 미만`
            });
        }
        
        // 폰트 크기 문제
        if (tests.fontSizes && tests.fontSizes.tinyFonts > 0) {
            issues.push({
                page: pageName,
                viewport: viewportName,
                type: '폰트 크기',
                count: tests.fontSizes.tinyFonts,
                detail: `${tests.fontSizes.tinyFonts}개 요소가 14px 미만`
            });
        }
        
        // 수평 스크롤 문제
        if (tests.horizontalScroll && tests.horizontalScroll.hasHorizontalScroll) {
            issues.push({
                page: pageName,
                viewport: viewportName,
                type: '수평 스크롤',
                count: 1,
                detail: `${tests.horizontalScroll.overflowAmount}px 넘침`
            });
        }
        
        // 버튼 줄바꿈 문제
        if (tests.buttonWrapping && tests.buttonWrapping.wrapped > 0) {
            issues.push({
                page: pageName,
                viewport: viewportName,
                type: '버튼 줄바꿈',
                count: tests.buttonWrapping.wrapped,
                detail: `${tests.buttonWrapping.wrapped}개 버튼에서 줄바꿈`
            });
        }
    }
}

// 문제점 통계
const issueStats = {};
issues.forEach(issue => {
    if (!issueStats[issue.type]) {
        issueStats[issue.type] = { total: 0, pages: new Set() };
    }
    issueStats[issue.type].total += issue.count;
    issueStats[issue.type].pages.add(issue.page);
});

console.log('문제점 통계:');
for (const [type, stats] of Object.entries(issueStats)) {
    console.log(`  ${type}: ${stats.total}개 (${stats.pages.size}개 페이지)`);
}

console.log('\n상위 문제점:');
issues
    .sort((a, b) => b.count - a.count)
    .slice(0, 10)
    .forEach((issue, index) => {
        console.log(`  ${index + 1}. ${issue.page} - ${issue.viewport}: ${issue.type} (${issue.detail})`);
    });

// 권장사항
console.log('\n' + '='.repeat(60));
console.log('개선 권장사항');
console.log('='.repeat(60));

const recommendations = [];

if (issueStats['터치 타겟']) {
    recommendations.push('🎯 터치 타겟 크기를 최소 44px로 확대 (헤더 네비게이션, 링크 버튼 등)');
}

if (issueStats['폰트 크기']) {
    recommendations.push('📝 본문 텍스트 폰트 크기를 최소 16px로 설정');
}

if (issueStats['수평 스크롤']) {
    recommendations.push('📱 모바일에서 수평 스크롤 방지를 위한 CSS 개선');
}

if (issueStats['버튼 줄바꿈']) {
    recommendations.push('🔘 버튼 텍스트 줄바꿈 방지를 위한 여백 조정');
}

if (recommendations.length === 0) {
    console.log('✅ 주요 문제점이 발견되지 않았습니다!');
} else {
    recommendations.forEach((rec, index) => {
        console.log(`${index + 1}. ${rec}`);
    });
}

console.log('\n🎉 분석 완료!');
console.log(`📸 생성된 스크린샷: ${Object.keys(pageNames).length * Object.keys(viewportNames).length}개`);
console.log('📁 스크린샷 위치: /var/www/html/topmkt/screenshots/');