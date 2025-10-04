/**
 * 🎯 812×858 사이즈 레이아웃 최종 검증 스크립트
 * 문제: 햄버거 버튼은 보이지만 레이아웃이 깨지고 난리 났던 문제
 * 해결: CSS 우선순위 강화 + !important 적용 + JavaScript 보완 로직
 */

console.log('🎯 812×858 사이즈 레이아웃 최종 검증 시작');

// 현재 화면 크기 확인
const currentWidth = window.innerWidth;
const currentHeight = window.innerHeight;
const isTargetSize = currentWidth === 812 && currentHeight === 858;
const isTabletRange = currentWidth <= 900;

console.log(`📐 현재 화면 크기: ${currentWidth}×${currentHeight}`);
console.log(`🎯 타겟 크기 (812×858): ${isTargetSize ? '✅ 일치' : '❌ 불일치'}`);
console.log(`📱 태블릿 범위 (≤900px): ${isTabletRange ? '✅ 해당' : '❌ 미해당'}`);

// 테스트할 요소들 정의
const testElements = [
    {
        name: '햄버거 메뉴',
        selector: '.mobile-hamburger',
        expectedVisible: isTabletRange,
        expectedStyles: {
            display: 'flex',
            position: 'fixed',
            visibility: 'visible'
        }
    },
    {
        name: '메인 네비게이션',
        selector: '.main-nav',
        expectedVisible: !isTabletRange,
        expectedStyles: {
            display: 'none',
            visibility: 'hidden',
            opacity: '0'
        }
    },
    {
        name: '사용자 메뉴',
        selector: '.user-menu',
        expectedVisible: !isTabletRange,
        expectedStyles: {
            display: 'none',
            visibility: 'hidden',
            opacity: '0'
        }
    },
    {
        name: 'nav-auth 영역',
        selector: '.nav-auth',
        expectedVisible: !isTabletRange,
        expectedStyles: {
            display: 'none',
            visibility: 'hidden',
            opacity: '0'
        }
    }
];

// 각 요소 테스트 실행
let testResults = [];

testElements.forEach(test => {
    const element = document.querySelector(test.selector);

    if (!element) {
        console.log(`❌ ${test.name}: 요소를 찾을 수 없음 (${test.selector})`);
        testResults.push({
            name: test.name,
            status: 'ELEMENT_NOT_FOUND',
            element: null
        });
        return;
    }

    const computedStyle = window.getComputedStyle(element);
    const results = {
        name: test.name,
        selector: test.selector,
        element: element,
        actualStyles: {},
        expectedStyles: test.expectedStyles,
        visible: test.expectedVisible,
        status: 'PASS'
    };

    // 실제 스타일 값 확인
    Object.keys(test.expectedStyles).forEach(property => {
        const actualValue = computedStyle.getPropertyValue(property);
        const expectedValue = test.expectedStyles[property];
        results.actualStyles[property] = actualValue;

        if (isTabletRange && test.expectedVisible) {
            // 태블릿에서 보여야 하는 요소 (햄버거 메뉴)
            if (property === 'display' && actualValue !== expectedValue) {
                results.status = 'FAIL';
            }
        } else if (isTabletRange && !test.expectedVisible) {
            // 태블릿에서 숨겨져야 하는 요소들
            if (property === 'display' && actualValue !== 'none') {
                results.status = 'FAIL';
            }
        }
    });

    // 결과 출력
    const statusIcon = results.status === 'PASS' ? '✅' : '❌';
    console.log(`${statusIcon} ${test.name}:`);
    console.log(`   요소: ${element.tagName}.${element.className}`);

    Object.keys(test.expectedStyles).forEach(property => {
        const actual = results.actualStyles[property];
        const expected = test.expectedStyles[property];
        const match = actual === expected ? '✅' : '❌';
        console.log(`   ${property}: ${actual} (예상: ${expected}) ${match}`);
    });

    testResults.push(results);
});

// 최종 결과 분석
const totalTests = testResults.length;
const passedTests = testResults.filter(r => r.status === 'PASS').length;
const failedTests = testResults.filter(r => r.status === 'FAIL').length;
const notFoundTests = testResults.filter(r => r.status === 'ELEMENT_NOT_FOUND').length;

console.log('\n📊 최종 테스트 결과:');
console.log(`   총 테스트: ${totalTests}개`);
console.log(`   ✅ 성공: ${passedTests}개`);
console.log(`   ❌ 실패: ${failedTests}개`);
console.log(`   🔍 요소 없음: ${notFoundTests}개`);

// 812×858 사이즈 특별 검증
if (isTargetSize) {
    console.log('\n🎯 812×858 타겟 크기 특별 검증:');

    // 햄버거 메뉴가 보이는지 확인
    const hamburger = document.querySelector('.mobile-hamburger');
    if (hamburger) {
        const hamburgerVisible = window.getComputedStyle(hamburger).display === 'flex';
        console.log(`   햄버거 메뉴 표시: ${hamburgerVisible ? '✅ 정상' : '❌ 비정상'}`);
    }

    // 문제 요소들이 완전히 숨겨졌는지 확인
    const problemElements = ['.main-nav', '.user-menu', '.nav-auth'];
    let allHidden = true;

    problemElements.forEach(selector => {
        const element = document.querySelector(selector);
        if (element) {
            const isHidden = window.getComputedStyle(element).display === 'none';
            console.log(`   ${selector} 숨김: ${isHidden ? '✅ 정상' : '❌ 비정상'}`);
            if (!isHidden) allHidden = false;
        }
    });

    const layoutFixed = hamburger && window.getComputedStyle(hamburger).display === 'flex' && allHidden;
    console.log(`\n🏆 812×858 레이아웃 상태: ${layoutFixed ? '✅ 완전 수정됨' : '❌ 아직 문제 있음'}`);

    if (layoutFixed) {
        console.log('🎉 축하합니다! 812×858 사이즈에서 레이아웃이 완벽하게 수정되었습니다!');
        console.log('   - 햄버거 메뉴가 올바르게 표시됩니다');
        console.log('   - 문제 요소들이 완전히 숨겨졌습니다');
        console.log('   - 레이아웃이 더 이상 깨지지 않습니다');
    }
}

// CSS 미디어 쿼리 상태 확인
console.log('\n🎨 CSS 미디어 쿼리 상태:');
const mediaQueries = [
    { name: '태블릿/모바일 (max-width: 900px)', query: '(max-width: 900px)' },
    { name: '데스크톱 (min-width: 901px)', query: '(min-width: 901px)' },
    { name: '작은 모바일 (max-width: 480px)', query: '(max-width: 480px)' },
    { name: '812×858 타겟', query: '(width: 812px) and (height: 858px)' }
];

mediaQueries.forEach(({ name, query }) => {
    const matches = window.matchMedia(query).matches;
    console.log(`   ${name}: ${matches ? '✅ 활성' : '❌ 비활성'}`);
});

console.log('\n✅ 812×858 사이즈 레이아웃 최종 검증 완료');