# 🚨 헤더 로고 중앙 이동 문제 해결 계획

## 🔍 현재 문제 상황

### 증상
- 초기 로딩 시 로고가 정상적으로 좌측에 위치
- JavaScript 로딩 완료 후 갑자기 로고가 중앙으로 이동
- 모든 페이지(강의, 이벤트, 커뮤니티 등)에서 동일 현상 발생

### 현재까지 시도한 해결책
- ✅ CSS에서 `!important` 규칙 적용
- ✅ JavaScript에서 위치 강제 설정
- ✅ 모바일 반응형에서 로고 위치 보호
- ❌ 하지만 여전히 로딩 후 로고가 중앙으로 이동

## 🎯 근본 원인 분석

### 1. 다른 JavaScript 코드 간섭
- 페이지의 다른 스크립트가 헤더 레이아웃을 변경할 가능성
- 특히 `forceCorrectLayout()` 함수가 헤더 구조에 영향

### 2. 초기화 타이밍 문제
- 로고 위치 설정이 JavaScript 실행 시점에 제대로 적용되지 않음
- 다른 코드가 나중에 실행되면서 덮어씌우기 가능성

### 3. 브라우저별 차이
- 특정 브라우저에서만 발생하는 문제일 수 있음
- CSS 우선순위나 렌더링 타이밍 차이

## 💡 해결 계획

### 단계 1: 더 강력한 CSS 보호 규칙 적용

**현재보다 강화된 CSS 규칙**
```css
/* 최상위 우선순위로 로고 위치 강제 보호 */
html body .header-left,
html body header .header-left,
html body .header-content .header-left,
.header-left,
header .header-left,
.header-content .header-left {
    flex: 0 0 auto !important;
    order: 1 !important;
    position: relative !important;
    transform: none !important;
    left: auto !important;
    right: auto !important;
    margin-left: 0 !important;
    margin-right: auto !important;
}
```

### 단계 2: JavaScript 초기화 타이밍 강화

**즉시 실행 함수 추가**
```javascript
// 즉시 실행하여 로고 위치 보호
(function() {
    'use strict';

    function protectLogoPosition() {
        const headerLeft = document.querySelector('.header-left');
        const headerContent = document.querySelector('.header-content');

        if (headerLeft) {
            // 모든 가능한 스타일 속성 강제 설정
            const styles = {
                'flex': '0 0 auto',
                'order': '1',
                'position': 'relative',
                'transform': 'none',
                'left': 'auto',
                'right': 'auto',
                'margin-left': '0',
                'margin-right': 'auto'
            };

            Object.entries(styles).forEach(([property, value]) => {
                headerLeft.style.setProperty(property, value, 'important');
            });
        }

        if (headerContent) {
            headerContent.style.setProperty('display', 'flex', 'important');
            headerContent.style.setProperty('justify-content', 'space-between', 'important');
            headerContent.style.setProperty('align-items', 'center', 'important');
        }
    }

    // 즉시 실행
    protectLogoPosition();

    // DOM 로드 완료 후 다시 실행
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', protectLogoPosition);
    } else {
        protectLogoPosition();
    }

    // 페이지 완전 로드 후 다시 실행
    window.addEventListener('load', protectLogoPosition);

})();
```

### 단계 3: 지속적 모니터링 시스템 구현

**변경 감지 및 자동 복구**
```javascript
// 로고 위치 변경 감지 및 자동 복구 시스템
(function() {
    'use strict';

    let logoProtectionInterval;
    const originalFlex = '0 0 auto';
    const originalOrder = '1';

    function checkAndFixLogoPosition() {
        const headerLeft = document.querySelector('.header-left');
        if (!headerLeft) return;

        const currentFlex = window.getComputedStyle(headerLeft).flex;
        const currentOrder = window.getComputedStyle(headerLeft).order;

        // 위치가 변경되었는지 확인
        if (currentFlex !== originalFlex || currentOrder !== originalOrder) {
            console.log('🔧 로고 위치 변경 감지 - 복구 실행');

            // 강제 복구
            headerLeft.style.setProperty('flex', originalFlex, 'important');
            headerLeft.style.setProperty('order', originalOrder, 'important');
            headerLeft.style.setProperty('position', 'relative', 'important');
        }
    }

    function startLogoProtection() {
        // 100ms마다 위치 확인
        logoProtectionInterval = setInterval(checkAndFixLogoPosition, 100);

        // 30초 후 자동 중단 (성능 최적화)
        setTimeout(() => {
            if (logoProtectionInterval) {
                clearInterval(logoProtectionInterval);
                console.log('✅ 로고 위치 보호 시스템 종료');
            }
        }, 30000);
    }

    // 즉시 시작
    startLogoProtection();

    // DOM 변경 이벤트 감지
    const observer = new MutationObserver(checkAndFixLogoPosition);
    observer.observe(document.body, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['style', 'class']
    });

})();
```

### 단계 4: 모든 페이지에 적용

**공통 헤더에 보호 코드 추가**
- 모든 페이지의 헤더에 동일한 보호 로직 적용
- 특히 이벤트 페이지와 강의 페이지 모두 보호

## 🎯 구현 우선순위

### Phase 1: 즉시 적용 (핵심 문제 해결)
1. **강화된 CSS 규칙** 적용
2. **즉시 실행 JavaScript** 추가

### Phase 2: 지속적 보호 (장기적 해결)
1. **모니터링 시스템** 구현
2. **이벤트 감지** 및 자동 복구

### Phase 3: 테스트 및 검증
1. **모든 페이지**에서 로고 위치 확인
2. **다양한 브라우저**에서 테스트
3. **시간 경과 후**에도 위치 유지 확인

## 🚀 예상 해결 효과

### Before (현재 문제)
- 초기: 로고 좌측 정상
- 로딩 완료 후: 로고 중앙 이동
- 예측 불가능한 위치 변경

### After (해결 후)
- **항상 좌측 고정**: 초기부터 끝까지 ✅
- **실시간 보호**: 변경 시 자동 복구 ✅
- **모든 환경 호환**: 브라우저/디바이스 관계없이 ✅

## 📋 구현 체크리스트

### CSS 강화
- [ ] 최상위 우선순위 CSS 규칙 추가
- [ ] 모든 미디어 쿼리에서 보호 규칙 적용
- [ ] 기존 CSS와 충돌하지 않도록 확인

### JavaScript 보호
- [ ] 즉시 실행 함수 구현
- [ ] DOM 로드 이벤트 리스너 추가
- [ ] 페이지 로드 이벤트 리스너 추가

### 모니터링 시스템
- [ ] 위치 변경 감지 함수 구현
- [ ] 자동 복구 시스템 구현
- [ ] 성능 최적화 (타이머 제한)

### 테스트 및 검증
- [ ] 모든 페이지에서 로고 위치 확인
- [ ] 다양한 브라우저 테스트
- [ ] 모바일/태블릿 환경 테스트

## ⚠️ 주의사항

### 성능 고려
- 모니터링 시스템이 성능에 영향을 주지 않도록 구현
- 불필요한 DOM 쿼리 최소화
- 적절한 타임아웃 설정으로 자동 종료

### 브라우저 호환성
- MutationObserver 지원 확인
- CSS 우선순위가 모든 브라우저에서 동일하게 작동하는지 확인

### 디버깅 용이성
- 콘솔 로그로 위치 변경 감지 및 복구 과정 추적
- 개발자 도구에서 쉽게 디버깅할 수 있도록 구현

## 🎉 성공 기준

- ✅ 초기 로딩부터 로고가 좌측에 위치
- ✅ JavaScript 로딩 후에도 위치 유지
- ✅ 모든 페이지(강의, 이벤트, 커뮤니티 등)에서 동일하게 작동
- ✅ 모든 브라우저와 디바이스에서 일관된 동작
- ✅ 성능 저하 없이 정상 작동

이 계획을 실행하면 헤더 로고 위치 문제가 완전히 해결될 것입니다!
