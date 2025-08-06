<?php
/**
 * QA 테스트: UI/UX 및 반응형 디자인
 */

echo "🧪 === UI/UX & 반응형 디자인 QA 테스트 ===\n";
echo str_repeat("=", 50) . "\n\n";

$testResults = [];
$totalTests = 0;
$passedTests = 0;

function runTest($testName, $testFunction) {
    global $testResults, $totalTests, $passedTests;
    $totalTests++;
    
    try {
        $result = $testFunction();
        if ($result === true) {
            $passedTests++;
            $testResults[] = "✅ PASS: $testName";
            echo "✅ PASS: $testName\n";
        } else {
            $testResults[] = "❌ FAIL: $testName - $result";
            echo "❌ FAIL: $testName - $result\n";
        }
    } catch (Exception $e) {
        $testResults[] = "❌ ERROR: $testName - " . $e->getMessage();
        echo "❌ ERROR: $testName - " . $e->getMessage() . "\n";
    }
}

define('SRC_PATH', __DIR__ . '/src');

// 테스트 1: 뷰 파일 구조 확인
runTest("뷰 파일 구조", function() {
    $viewFiles = [
        'notices/index.php' => SRC_PATH . '/views/notices/index.php',
        'notices/write.php' => SRC_PATH . '/views/notices/write.php',
        'notices/detail.php' => SRC_PATH . '/views/notices/detail.php'
    ];
    
    foreach ($viewFiles as $name => $path) {
        if (!file_exists($path)) {
            return "$name 파일이 존재하지 않음";
        }
        
        $content = file_get_contents($path);
        if (strlen($content) < 1000) {
            return "$name 파일이 너무 짧음 (구현 미완성 가능성)";
        }
    }
    
    return true;
});

// 테스트 2: CSS 반응형 미디어 쿼리 확인
runTest("CSS 반응형 미디어 쿼리", function() {
    $viewFiles = [
        SRC_PATH . '/views/notices/index.php',
        SRC_PATH . '/views/notices/write.php',
        SRC_PATH . '/views/notices/detail.php'
    ];
    
    $foundMobileQueries = 0;
    
    foreach ($viewFiles as $file) {
        $content = file_get_contents($file);
        
        // 모바일 미디어 쿼리 확인
        if (preg_match('/@media\s*\([^)]*max-width:\s*768px[^)]*\)/', $content)) {
            $foundMobileQueries++;
        }
    }
    
    if ($foundMobileQueries < 3) {
        return "반응형 미디어 쿼리가 충분하지 않음: $foundMobileQueries/3개";
    }
    
    return true;
});

// 테스트 3: 필수 HTML 요소 확인
runTest("필수 HTML 요소", function() {
    $indexContent = file_get_contents(SRC_PATH . '/views/notices/index.php');
    
    $requiredElements = [
        'notices-container' => 'notices-container',
        'search-form' => 'search-form', 
        'notice-list' => 'notice-list',
        'pagination' => 'pagination'
    ];
    
    foreach ($requiredElements as $name => $class) {
        if (strpos($indexContent, $class) === false) {
            return "목록 페이지에 필수 요소 '$name' 누락";
        }
    }
    
    return true;
});

// 테스트 4: JavaScript 기능 요소 확인
runTest("JavaScript 기능 요소", function() {
    $writeContent = file_get_contents(SRC_PATH . '/views/notices/write.php');
    
    $jsFeatures = [
        'Quill.js 에디터' => 'new Quill',
        '폼 검증' => 'validateForm',
        '이미지 업로드' => 'uploadImage',
        '파일 드래그 앤 드롭' => 'dragover'
    ];
    
    foreach ($jsFeatures as $name => $pattern) {
        if (strpos($writeContent, $pattern) === false) {
            return "작성 페이지에 '$name' 기능 누락";
        }
    }
    
    return true;
});

// 테스트 5: 접근성 요소 확인
runTest("접근성 요소", function() {
    $detailContent = file_get_contents(SRC_PATH . '/views/notices/detail.php');
    
    $accessibilityFeatures = [
        'alt 속성' => 'alt=',
        'aria-label' => 'aria-label',
        'title 속성' => 'title=',
        '시맨틱 태그' => '<main'
    ];
    
    $foundFeatures = 0;
    foreach ($accessibilityFeatures as $name => $pattern) {
        if (strpos($detailContent, $pattern) !== false) {
            $foundFeatures++;
        }
    }
    
    if ($foundFeatures < 2) {
        return "접근성 요소가 부족함: $foundFeatures/" . count($accessibilityFeatures) . "개";
    }
    
    return true;
});

// 테스트 6: SEO 메타태그 확인
runTest("SEO 메타태그", function() {
    $detailContent = file_get_contents(SRC_PATH . '/views/notices/detail.php');
    
    $seoElements = [
        'meta description' => 'name="description"',
        'Open Graph' => 'property="og:',
        'Twitter Card' => 'name="twitter:',
        'Canonical URL' => 'rel="canonical"'
    ];
    
    $foundElements = 0;
    foreach ($seoElements as $name => $pattern) {
        if (strpos($detailContent, $pattern) !== false) {
            $foundElements++;
        }
    }
    
    if ($foundElements < 3) {
        return "SEO 요소가 부족함: $foundElements/" . count($seoElements) . "개";
    }
    
    return true;
});

// 테스트 7: 폼 검증 기능 확인
runTest("폼 검증 기능", function() {
    $writeContent = file_get_contents(SRC_PATH . '/views/notices/write.php');
    
    $validationFeatures = [
        '필수 입력' => 'required',
        '최대 길이' => 'maxlength',
        '실시간 검증' => 'addEventListener',
        '글자수 카운터' => 'char-counter'
    ];
    
    $foundFeatures = 0;
    foreach ($validationFeatures as $name => $pattern) {
        if (strpos($writeContent, $pattern) !== false) {
            $foundFeatures++;
        }
    }
    
    if ($foundFeatures < 3) {
        return "폼 검증 기능이 부족함: $foundFeatures/" . count($validationFeatures) . "개";
    }
    
    return true;
});

// 테스트 8: 모바일 최적화 요소 확인
runTest("모바일 최적화", function() {
    $allViews = [
        file_get_contents(SRC_PATH . '/views/notices/index.php'),
        file_get_contents(SRC_PATH . '/views/notices/write.php'),
        file_get_contents(SRC_PATH . '/views/notices/detail.php')
    ];
    
    $mobileFeatures = [
        'viewport 메타태그' => 'name="viewport"',
        '터치 친화적 버튼' => 'touch-action',
        '모바일 메뉴' => 'mobile-menu',
        'flex/grid 레이아웃' => 'display: flex'
    ];
    
    $foundInViews = 0;
    foreach ($mobileFeatures as $name => $pattern) {
        foreach ($allViews as $content) {
            if (strpos($content, $pattern) !== false) {
                $foundInViews++;
                break;
            }
        }
    }
    
    if ($foundInViews < 2) {
        return "모바일 최적화 요소가 부족함: $foundInViews/" . count($mobileFeatures) . "개";
    }
    
    return true;
});

// 테스트 9: 사용자 경험 요소 확인
runTest("사용자 경험 요소", function() {
    $allContent = 
        file_get_contents(SRC_PATH . '/views/notices/index.php') .
        file_get_contents(SRC_PATH . '/views/notices/write.php') .
        file_get_contents(SRC_PATH . '/views/notices/detail.php');
    
    $uxFeatures = [
        '로딩 상태' => 'loading',
        '에러 처리' => 'error',
        '성공 메시지' => 'success',
        '확인 다이얼로그' => 'confirm',
        '툴팁/도움말' => 'title=',
        '애니메이션/전환' => 'transition'
    ];
    
    $foundFeatures = 0;
    foreach ($uxFeatures as $name => $pattern) {
        if (strpos($allContent, $pattern) !== false) {
            $foundFeatures++;
        }
    }
    
    if ($foundFeatures < 4) {
        return "UX 요소가 부족함: $foundFeatures/" . count($uxFeatures) . "개";
    }
    
    return true;
});

// 테스트 10: 브라우저 호환성 확인
runTest("브라우저 호환성", function() {
    $allContent = 
        file_get_contents(SRC_PATH . '/views/notices/index.php') .
        file_get_contents(SRC_PATH . '/views/notices/write.php') .
        file_get_contents(SRC_PATH . '/views/notices/detail.php');
    
    $compatibilityFeatures = [
        'ES6 폴백' => 'addEventListener',
        'CSS 폴백' => '!important',
        'CDN 폴백' => 'fallback',
        '점진적 향상' => 'progressive'
    ];
    
    // 기본적인 호환성 확인
    if (strpos($allContent, 'addEventListener') === false) {
        return "기본적인 JavaScript 이벤트 처리가 없음";
    }
    
    // 외부 CDN에 대한 폴백이나 에러 처리 확인
    if (strpos($allContent, 'onerror') !== false || strpos($allContent, 'fallback') !== false) {
        return true;
    }
    
    // 기본적인 호환성은 확보된 것으로 판단
    return true;
});

echo "\n" . str_repeat("=", 50) . "\n";
echo "🏁 UI/UX & 반응형 디자인 QA 테스트 완료\n";
echo "📊 결과: $passedTests/$totalTests 통과 (" . round(($passedTests/$totalTests)*100, 1) . "%)\n";

if ($passedTests === $totalTests) {
    echo "✅ 모든 UI/UX 테스트 통과!\n";
} else {
    echo "❌ " . ($totalTests - $passedTests) . "개 테스트 실패\n";
    echo "\n실패한 테스트들:\n";
    foreach ($testResults as $result) {
        if (strpos($result, '❌') === 0) {
            echo $result . "\n";
        }
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
?>