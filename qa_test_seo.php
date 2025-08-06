<?php
/**
 * QA 테스트: SEO 및 공유 기능
 */

define('SRC_PATH', __DIR__ . '/src');

echo "🧪 === SEO & 공유 기능 QA 테스트 ===\n";
echo str_repeat("=", 45) . "\n\n";

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

// 테스트 1: 공지사항 상세보기 SEO 메타태그
runTest("상세보기 SEO 메타태그", function() {
    $detailContent = file_get_contents(SRC_PATH . '/views/notices/detail.php');
    
    $seoTags = [
        'meta description' => 'name="description"',
        'meta keywords' => 'name="keywords"', 
        'meta author' => 'name="author"',
        'canonical URL' => 'rel="canonical"'
    ];
    
    $foundTags = 0;
    foreach ($seoTags as $name => $pattern) {
        if (strpos($detailContent, $pattern) !== false) {
            $foundTags++;
            echo "   - ✓ $name 존재\n";
        } else {
            echo "   - ✗ $name 누락\n";
        }
    }
    
    if ($foundTags < 3) {
        return "필수 SEO 메타태그가 부족함: $foundTags/4개";
    }
    
    return true;
});

// 테스트 2: Open Graph 메타태그
runTest("Open Graph 메타태그", function() {
    $detailContent = file_get_contents(SRC_PATH . '/views/notices/detail.php');
    
    $ogTags = [
        'og:type' => 'property="og:type"',
        'og:title' => 'property="og:title"',
        'og:description' => 'property="og:description"',
        'og:url' => 'property="og:url"',
        'og:image' => 'property="og:image"',
        'og:site_name' => 'property="og:site_name"'
    ];
    
    $foundOgTags = 0;
    foreach ($ogTags as $name => $pattern) {
        if (strpos($detailContent, $pattern) !== false) {
            $foundOgTags++;
            echo "   - ✓ $name 존재\n";
        } else {
            echo "   - ✗ $name 누락\n";
        }
    }
    
    if ($foundOgTags < 4) {
        return "Open Graph 태그가 부족함: $foundOgTags/" . count($ogTags) . "개";
    }
    
    return true;
});

// 테스트 3: Twitter Card 메타태그
runTest("Twitter Card 메타태그", function() {
    $detailContent = file_get_contents(SRC_PATH . '/views/notices/detail.php');
    
    $twitterTags = [
        'twitter:card' => 'name="twitter:card"',
        'twitter:title' => 'name="twitter:title"',
        'twitter:description' => 'name="twitter:description"',
        'twitter:image' => 'name="twitter:image"'
    ];
    
    $foundTwitterTags = 0;
    foreach ($twitterTags as $name => $pattern) {
        if (strpos($detailContent, $pattern) !== false) {
            $foundTwitterTags++;
            echo "   - ✓ $name 존재\n";
        } else {
            echo "   - ✗ $name 누락\n";
        }
    }
    
    if ($foundTwitterTags < 3) {
        return "Twitter Card 태그가 부족함: $foundTwitterTags/" . count($twitterTags) . "개";
    }
    
    return true;
});

// 테스트 4: 구조화 데이터 (JSON-LD)
runTest("구조화 데이터", function() {
    $detailContent = file_get_contents(SRC_PATH . '/views/notices/detail.php');
    
    // Article 구조화 데이터 확인
    $structuredDataElements = [
        'article:published_time' => 'property="article:published_time"',
        'article:modified_time' => 'property="article:modified_time"',
        'article:author' => 'property="article:author"',
        'article:section' => 'property="article:section"'
    ];
    
    $foundElements = 0;
    foreach ($structuredDataElements as $name => $pattern) {
        if (strpos($detailContent, $pattern) !== false) {
            $foundElements++;
            echo "   - ✓ $name 존재\n";
        }
    }
    
    if ($foundElements < 2) {
        return "구조화 데이터 요소가 부족함: $foundElements/" . count($structuredDataElements) . "개";
    }
    
    return true;
});

// 테스트 5: 소셜 공유 기능 구현
runTest("소셜 공유 기능", function() {
    $detailContent = file_get_contents(SRC_PATH . '/views/notices/detail.php');
    
    $shareFeatures = [
        '페이스북 공유' => 'shareToFacebook',
        '트위터 공유' => 'shareToTwitter',
        '카카오톡 공유' => 'shareToKakao',
        'URL 복사' => 'copyShareUrl',
        '공유 모달' => 'share-modal'
    ];
    
    $foundFeatures = 0;
    foreach ($shareFeatures as $name => $pattern) {
        if (strpos($detailContent, $pattern) !== false) {
            $foundFeatures++;
            echo "   - ✓ $name 구현됨\n";
        } else {
            echo "   - ✗ $name 누락\n";
        }
    }
    
    if ($foundFeatures < 4) {
        return "공유 기능이 부족함: $foundFeatures/" . count($shareFeatures) . "개";
    }
    
    return true;
});

// 테스트 6: 메타태그 동적 생성
runTest("동적 메타태그 생성", function() {
    $detailContent = file_get_contents(SRC_PATH . '/views/notices/detail.php');
    
    // PHP 변수를 사용한 동적 메타태그 확인
    $dynamicElements = [
        '동적 제목' => '$pageTitle',
        '동적 설명' => '$pageDescription',
        '동적 URL' => '$pageUrl',
        '동적 이미지' => '$pageImage'
    ];
    
    $foundDynamic = 0;
    foreach ($dynamicElements as $name => $pattern) {
        if (strpos($detailContent, $pattern) !== false) {
            $foundDynamic++;
            echo "   - ✓ $name 변수 사용됨\n";
        }
    }
    
    if ($foundDynamic < 3) {
        return "동적 메타태그 생성이 부족함: $foundDynamic/" . count($dynamicElements) . "개";
    }
    
    return true;
});

// 테스트 7: URL 구조 SEO 최적화
runTest("URL 구조 SEO 최적화", function() {
    // 라우팅 설정 확인
    require_once SRC_PATH . '/config/routes.php';
    $router = new Router();
    
    $reflection = new ReflectionClass($router);
    $routesProperty = $reflection->getProperty('routes');
    $routesProperty->setAccessible(true);
    $routes = $routesProperty->getValue($router);
    
    $seoFriendlyRoutes = [
        'GET:/notices' => '목록 페이지',
        'GET:/notices/{id}' => '상세 페이지',
        'GET:/notices/write' => '작성 페이지'
    ];
    
    foreach ($seoFriendlyRoutes as $route => $desc) {
        if (isset($routes[$route])) {
            echo "   - ✓ $desc: $route\n";
        } else {
            return "$desc URL이 SEO 친화적이지 않음: $route";
        }
    }
    
    return true;
});

// 테스트 8: 페이지 성능 메타태그
runTest("성능 관련 메타태그", function() {
    $detailContent = file_get_contents(SRC_PATH . '/views/notices/detail.php');
    
    $performanceTags = [
        'viewport' => 'name="viewport"',
        'preconnect' => 'rel="preconnect"',
        'dns-prefetch' => 'rel="dns-prefetch"',
        'theme-color' => 'name="theme-color"'
    ];
    
    $foundPerf = 0;
    foreach ($performanceTags as $name => $pattern) {
        if (strpos($detailContent, $pattern) !== false) {
            $foundPerf++;
            echo "   - ✓ $name 태그 존재\n";
        }
    }
    
    if ($foundPerf < 2) {
        return "성능 관련 메타태그가 부족함: $foundPerf/" . count($performanceTags) . "개";
    }
    
    return true;
});

// 테스트 9: 공유 URL 생성
runTest("공유 URL 생성", function() {
    $detailContent = file_get_contents(SRC_PATH . '/views/notices/detail.php');
    
    // 절대 URL 생성 확인
    if (strpos($detailContent, 'https://www.topmktx.com') === false) {
        return "절대 URL이 생성되지 않음";
    }
    
    // URL 인코딩 확인
    if (strpos($detailContent, 'encodeURIComponent') === false) {
        return "URL 인코딩이 없음";
    }
    
    // 공유 URL 검증
    if (strpos($detailContent, 'pageUrl') === false) {
        return "페이지 URL 변수가 없음";
    }
    
    echo "   - ✓ 절대 URL 생성됨\n";
    echo "   - ✓ URL 인코딩 적용됨\n";
    echo "   - ✓ 페이지 URL 변수 존재\n";
    
    return true;
});

// 테스트 10: 검색 엔진 최적화 헤더
runTest("검색 엔진 최적화 헤더", function() {
    // header.php 파일의 SEO 요소 확인
    $headerContent = '';
    $headerPath = SRC_PATH . '/views/templates/header.php';
    
    if (file_exists($headerPath)) {
        $headerContent = file_get_contents($headerPath);
    } else {
        return "header.php 파일을 찾을 수 없음";
    }
    
    $seoHeaders = [
        'robots 메타태그' => 'name="robots"',
        '언어 설정' => 'lang="ko"',
        'charset 설정' => 'charset="UTF-8"',
        '사이트맵 힌트' => 'sitemap',
        '구조화 데이터' => 'application/ld+json'
    ];
    
    $foundHeaders = 0;
    foreach ($seoHeaders as $name => $pattern) {
        if (strpos($headerContent, $pattern) !== false) {
            $foundHeaders++;
            echo "   - ✓ $name 존재\n";
        }
    }
    
    if ($foundHeaders < 3) {
        return "SEO 헤더 요소가 부족함: $foundHeaders/" . count($seoHeaders) . "개";
    }
    
    return true;
});

echo "\n" . str_repeat("=", 45) . "\n";
echo "🏁 SEO & 공유 기능 QA 테스트 완료\n";
echo "📊 결과: $passedTests/$totalTests 통과 (" . round(($passedTests/$totalTests)*100, 1) . "%)\n";

if ($passedTests === $totalTests) {
    echo "✅ 모든 SEO & 공유 기능 테스트 통과!\n";
} else {
    echo "❌ " . ($totalTests - $passedTests) . "개 테스트 실패\n";
    echo "\n실패한 테스트들:\n";
    foreach ($testResults as $result) {
        if (strpos($result, '❌') === 0) {
            echo $result . "\n";
        }
    }
}

echo "\n" . str_repeat("=", 45) . "\n";
?>