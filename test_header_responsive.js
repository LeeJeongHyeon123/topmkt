/**
 * 헤더 반응형 테스트 스크립트
 * 812*858 사이즈에서 user-menu 짤림 문제 해결 검증
 */

console.log('🔍 헤더 반응형 테스트 시작');

// 현재 화면 크기 정보
const screenInfo = {
    width: window.innerWidth,
    height: window.innerHeight,
    isTarget: window.innerWidth === 812 && window.innerHeight === 858,
    isTabletRange: window.innerWidth >= 481 && window.innerWidth <= 900,
    isMobileRange: window.innerWidth <= 480,
    isDesktopRange: window.innerWidth >= 901
};

console.log('📐 화면 크기 정보:', screenInfo);

// 헤더 요소들 확인
const headerElements = {
    mobileHamburger: document.getElementById('mobile-hamburger'),
    userMenu: document.querySelector('.user-menu'),
    navAuth: document.querySelector('.nav-auth'),
    mainNav: document.querySelector('.main-nav'),
    headerContent: document.querySelector('.header-content')
};

console.log('🎯 헤더 요소 상태:');
Object.entries(headerElements).forEach(([key, element]) => {
    if (element) {
        const styles = window.getComputedStyle(element);
        console.log(`  ${key}:`, {
            display: styles.display,
            visibility: styles.visibility,
            opacity: styles.opacity,
            position: styles.position,
            zIndex: styles.zIndex
        });
    } else {
        console.log(`  ${key}: 요소 없음`);
    }
});

// 812*858 사이즈에서의 예상 동작 검증
if (screenInfo.isTarget) {
    console.log('🎯 812*858 타겟 크기 감지!');

    const expectedBehavior = {
        mobileHamburger: 'display: flex (표시되어야 함)',
        userMenu: 'display: none (숨겨져야 함)',
        navAuth: 'display: none (숨겨져야 함)',
        mainNav: 'display: none (숨겨져야 함)'
    };

    console.log('📋 예상 동작:', expectedBehavior);

    // 실제 동작 검증
    const results = {};

    if (headerElements.mobileHamburger) {
        const hamburgerStyle = window.getComputedStyle(headerElements.mobileHamburger);
        results.hamburger = hamburgerStyle.display === 'flex' ? '✅ 정상' : '❌ 비정상';
    }

    if (headerElements.userMenu) {
        const userMenuStyle = window.getComputedStyle(headerElements.userMenu);
        results.userMenu = userMenuStyle.display === 'none' ? '✅ 정상' : '❌ 비정상';
    }

    if (headerElements.navAuth) {
        const navAuthStyle = window.getComputedStyle(headerElements.navAuth);
        results.navAuth = navAuthStyle.display === 'none' ? '✅ 정상' : '❌ 비정상';
    }

    if (headerElements.mainNav) {
        const mainNavStyle = window.getComputedStyle(headerElements.mainNav);
        results.mainNav = mainNavStyle.display === 'none' ? '✅ 정상' : '❌ 비정상';
    }

    console.log('🔍 검증 결과:', results);

    // 종합 평가
    const allPass = Object.values(results).every(result => result.includes('✅'));
    console.log('🏆 종합 평가:', allPass ? '✅ 모든 테스트 통과' : '❌ 일부 테스트 실패');

} else if (screenInfo.isTabletRange) {
    console.log('📱 태블릿 범위 (481px~900px) - 햄버거 메뉴 모드');
} else if (screenInfo.isMobileRange) {
    console.log('📱 작은 모바일 범위 (480px 이하) - 컴팩트 모드');
} else if (screenInfo.isDesktopRange) {
    console.log('🖥️ 데스크톱 범위 (901px 이상) - user-menu 모드');
}

// 문제 해결 확인
const problemSolved = screenInfo.isTarget &&
    headerElements.mobileHamburger &&
    window.getComputedStyle(headerElements.mobileHamburger).display === 'flex' &&
    headerElements.userMenu &&
    window.getComputedStyle(headerElements.userMenu).display === 'none';

console.log('🎉 문제 해결 상태:', problemSolved ? '✅ 해결됨' : '❌ 미해결');

// CSS 미디어 쿼리 상태 확인
const mediaQueries = [
    { name: '태블릿/모바일 (max-width: 900px)', query: '(max-width: 900px)' },
    { name: '데스크톱 (min-width: 901px)', query: '(min-width: 901px)' },
    { name: '작은 모바일 (max-width: 480px)', query: '(max-width: 480px)' }
];

console.log('🎨 미디어 쿼리 상태:');
mediaQueries.forEach(({ name, query }) => {
    const matches = window.matchMedia(query).matches;
    console.log(`  ${name}: ${matches ? '✅ 활성' : '❌ 비활성'}`);
});

// 테스트 완료 메시지
console.log('✅ 헤더 반응형 테스트 완료');
console.log('📝 결과 요약: 812*858 사이즈에서', problemSolved ? 'user-menu 짤림 문제가 해결되었습니다!' : 'user-menu 짤림 문제가 아직 남아있습니다.');