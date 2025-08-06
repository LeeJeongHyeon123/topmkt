<?php
/**
 * QA 테스트: 라우팅 및 컨트롤러
 */

define('SRC_PATH', __DIR__ . '/src');
require_once SRC_PATH . '/config/routes.php';

echo "🧪 === 라우팅 & 컨트롤러 QA 테스트 ===\n";
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

// 테스트 1: 라우터 인스턴스 생성
runTest("라우터 인스턴스 생성", function() {
    $router = new Router();
    return ($router instanceof Router) ? true : "라우터 인스턴스 생성 실패";
});

// 테스트 2: 공지사항 라우트 등록 확인
runTest("공지사항 라우트 등록", function() {
    $router = new Router();
    
    // 리플렉션으로 routes 배열 접근
    $reflection = new ReflectionClass($router);
    $routesProperty = $reflection->getProperty('routes');
    $routesProperty->setAccessible(true);
    $routes = $routesProperty->getValue($router);
    
    $noticeRoutes = array_filter($routes, function($route, $key) {
        return strpos($key, 'notices') !== false;
    }, ARRAY_FILTER_USE_BOTH);
    
    if (count($noticeRoutes) < 5) {
        return "공지사항 관련 라우트가 충분하지 않음: " . count($noticeRoutes) . "개";
    }
    
    return true;
});

// 테스트 3: 필수 라우트 패턴 확인
runTest("필수 라우트 패턴 확인", function() {
    $router = new Router();
    $reflection = new ReflectionClass($router);
    $routesProperty = $reflection->getProperty('routes');
    $routesProperty->setAccessible(true);
    $routes = $routesProperty->getValue($router);
    
    $requiredRoutes = [
        'GET:/notices',
        'GET:/notices/write', 
        'POST:/api/notices',
        'GET:/notices/{id}',
        'PUT:/api/notices/{id}',
        'DELETE:/api/notices/{id}'
    ];
    
    foreach ($requiredRoutes as $route) {
        if (!isset($routes[$route])) {
            return "필수 라우트 누락: $route";
        }
    }
    
    return true;
});

// 테스트 4: 컨트롤러 파일 존재 확인
runTest("컨트롤러 파일 존재", function() {
    $controllers = [
        'NoticeController' => SRC_PATH . '/controllers/NoticeController.php',
        'NoticeCommentController' => SRC_PATH . '/controllers/NoticeCommentController.php'
    ];
    
    foreach ($controllers as $name => $path) {
        if (!file_exists($path)) {
            return "$name 파일이 존재하지 않음: $path";
        }
    }
    
    return true;
});

// 테스트 5: 컨트롤러 클래스 로딩
runTest("컨트롤러 클래스 로딩", function() {
    require_once SRC_PATH . '/controllers/NoticeController.php';
    require_once SRC_PATH . '/controllers/NoticeCommentController.php';
    
    if (!class_exists('NoticeController')) {
        return "NoticeController 클래스를 찾을 수 없음";
    }
    
    if (!class_exists('NoticeCommentController')) {
        return "NoticeCommentController 클래스를 찾을 수 없음";
    }
    
    return true;
});

// 테스트 6: 컨트롤러 인스턴스 생성
runTest("컨트롤러 인스턴스 생성", function() {
    $noticeController = new NoticeController();
    $commentController = new NoticeCommentController();
    
    if (!($noticeController instanceof NoticeController)) {
        return "NoticeController 인스턴스 생성 실패";
    }
    
    if (!($commentController instanceof NoticeCommentController)) {
        return "NoticeCommentController 인스턴스 생성 실패";
    }
    
    return true;
});

// 테스트 7: 컨트롤러 메서드 존재 확인
runTest("컨트롤러 메서드 확인", function() {
    $noticeController = new NoticeController();
    
    $requiredMethods = ['index', 'show', 'showWrite', 'create', 'update', 'delete'];
    foreach ($requiredMethods as $method) {
        if (!method_exists($noticeController, $method)) {
            return "NoticeController::$method 메서드가 존재하지 않음";
        }
    }
    
    $commentController = new NoticeCommentController();
    $commentMethods = ['store', 'update', 'delete', 'list'];
    foreach ($commentMethods as $method) {
        if (!method_exists($commentController, $method)) {
            return "NoticeCommentController::$method 메서드가 존재하지 않음";
        }
    }
    
    return true;
});

// 테스트 8: 라우트-컨트롤러 매핑 확인
runTest("라우트-컨트롤러 매핑", function() {
    $router = new Router();
    $reflection = new ReflectionClass($router);
    $routesProperty = $reflection->getProperty('routes');
    $routesProperty->setAccessible(true);
    $routes = $routesProperty->getValue($router);
    
    $mappings = [
        'GET:/notices' => ['NoticeController', 'index'],
        'POST:/api/notices' => ['NoticeController', 'create'],
        'GET:/notices/{id}' => ['NoticeController', 'show']
    ];
    
    foreach ($mappings as $route => $expected) {
        if (!isset($routes[$route])) {
            return "라우트 '$route'가 존재하지 않음";
        }
        
        $actual = $routes[$route];
        if ($actual[0] !== $expected[0] || $actual[1] !== $expected[1]) {
            return "라우트 '$route'의 매핑이 잘못됨: [{$actual[0]}, {$actual[1]}] != [{$expected[0]}, {$expected[1]}]";
        }
    }
    
    return true;
});

// 테스트 9: 뷰 파일 존재 확인
runTest("뷰 파일 존재 확인", function() {
    $viewFiles = [
        'index' => SRC_PATH . '/views/notices/index.php',
        'write' => SRC_PATH . '/views/notices/write.php',
        'detail' => SRC_PATH . '/views/notices/detail.php'
    ];
    
    foreach ($viewFiles as $name => $path) {
        if (!file_exists($path)) {
            return "$name 뷰 파일이 존재하지 않음: $path";
        }
    }
    
    return true;
});

// 테스트 10: 라우팅 충돌 검사
runTest("라우팅 충돌 검사", function() {
    $router = new Router();
    $reflection = new ReflectionClass($router);
    $routesProperty = $reflection->getProperty('routes');
    $routesProperty->setAccessible(true);
    $routes = $routesProperty->getValue($router);
    
    // 기존 community 라우트와 충돌하는지 확인
    $communityRoutes = array_filter(array_keys($routes), function($route) {
        return strpos($route, '/community') !== false;
    });
    
    $noticeRoutes = array_filter(array_keys($routes), function($route) {
        return strpos($route, '/notices') !== false;
    });
    
    // 패턴 충돌 검사
    foreach ($communityRoutes as $communityRoute) {
        foreach ($noticeRoutes as $noticeRoute) {
            $communityPath = preg_replace('/^[A-Z]+:/', '', $communityRoute);
            $noticePath = preg_replace('/^[A-Z]+:/', '', $noticeRoute);
            
            // 기본적으로 /community와 /notices는 충돌하지 않아야 함
            if ($communityPath === $noticePath) {
                return "라우트 패턴 충돌: $communityRoute <-> $noticeRoute";
            }
        }
    }
    
    return true;
});

echo "\n" . str_repeat("=", 50) . "\n";
echo "🏁 라우팅 & 컨트롤러 QA 테스트 완료\n";
echo "📊 결과: $passedTests/$totalTests 통과 (" . round(($passedTests/$totalTests)*100, 1) . "%)\n";

if ($passedTests === $totalTests) {
    echo "✅ 모든 라우팅 & 컨트롤러 테스트 통과!\n";
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