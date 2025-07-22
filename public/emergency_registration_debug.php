<?php
/**
 * 🚨 울트라씽크 모드 - 기업 대시보드 500 오류 긴급 진단
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=UTF-8');

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>🚨 울트라씽크 긴급 진단</title>";
echo "<style>body{font-family:monospace;background:#000;color:#0f0;padding:20px;} .error{color:#f00;} .success{color:#0f0;} .warning{color:#fa0;} .critical{color:#ff0;background:#800;padding:5px;} pre{background:#111;padding:15px;border-radius:5px;}</style>";
echo "</head><body>";

echo "<h1>🚨 울트라씽크 모드 - 기업 대시보드 긴급 진단</h1>";

// 경로 설정
define('ROOT_PATH', realpath(__DIR__ . '/..'));
define('SRC_PATH', ROOT_PATH . '/src');

echo "<h2>1️⃣ 시스템 기본 상태 확인</h2>";
echo "<pre>";

// PHP 버전 및 확장 확인
echo "<span class='success'>✅ PHP 버전: " . PHP_VERSION . "</span>\n";
echo "<span class='success'>✅ 메모리 한계: " . ini_get('memory_limit') . "</span>\n";

$extensions = ['mysqli', 'pdo', 'json'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<span class='success'>✅ $ext 확장 로드됨</span>\n";
    } else {
        echo "<span class='error'>❌ $ext 확장 누락</span>\n";
    }
}

echo "</pre>";

echo "<h2>2️⃣ 파일 시스템 및 경로 확인</h2>";
echo "<pre>";

$criticalFiles = [
    'routes.php' => SRC_PATH . '/config/routes.php',
    'RegistrationDashboardController.php' => SRC_PATH . '/controllers/RegistrationDashboardController.php',
    'BaseController.php' => SRC_PATH . '/controllers/BaseController.php',
    'AuthMiddleware.php' => SRC_PATH . '/middlewares/AuthMiddleware.php',
    'database.php' => SRC_PATH . '/config/database.php'
];

foreach ($criticalFiles as $name => $path) {
    if (file_exists($path)) {
        echo "<span class='success'>✅ $name 존재: $path</span>\n";
    } else {
        echo "<span class='critical'>🚨 $name 누락: $path</span>\n";
    }
}

echo "</pre>";

echo "<h2>3️⃣ 라우트 매칭 테스트</h2>";
echo "<pre>";

try {
    require_once SRC_PATH . '/config/routes.php';
    
    $router = new Router();
    echo "<span class='success'>✅ Router 클래스 인스턴스 생성 성공</span>\n";
    
    // Reflection으로 routes 배열 접근
    $reflection = new ReflectionClass($router);
    $routesProperty = $reflection->getProperty('routes');
    $routesProperty->setAccessible(true);
    $routes = $routesProperty->getValue($router);
    
    $targetRoute = 'GET:/registrations';
    if (isset($routes[$targetRoute])) {
        echo "<span class='success'>✅ 라우트 매칭: $targetRoute => [{$routes[$targetRoute][0]}, {$routes[$targetRoute][1]}]</span>\n";
    } else {
        echo "<span class='critical'>🚨 라우트 누락: $targetRoute</span>\n";
        echo "사용 가능한 registration 관련 라우트:\n";
        foreach ($routes as $route => $controller) {
            if (strpos($route, 'registration') !== false) {
                echo "  $route => [{$controller[0]}, {$controller[1]}]\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "<span class='critical'>🚨 라우터 로딩 실패: " . $e->getMessage() . "</span>\n";
}

echo "</pre>";

echo "<h2>4️⃣ 컨트롤러 로딩 테스트</h2>";
echo "<pre>";

try {
    require_once SRC_PATH . '/controllers/BaseController.php';
    echo "<span class='success'>✅ BaseController 로드 성공</span>\n";
    
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    echo "<span class='success'>✅ AuthMiddleware 로드 성공</span>\n";
    
    require_once SRC_PATH . '/controllers/RegistrationDashboardController.php';
    echo "<span class='success'>✅ RegistrationDashboardController 로드 성공</span>\n";
    
    if (class_exists('RegistrationDashboardController')) {
        echo "<span class='success'>✅ RegistrationDashboardController 클래스 존재</span>\n";
        
        if (method_exists('RegistrationDashboardController', 'index')) {
            echo "<span class='success'>✅ index 메소드 존재</span>\n";
        } else {
            echo "<span class='error'>❌ index 메소드 누락</span>\n";
        }
    } else {
        echo "<span class='error'>❌ RegistrationDashboardController 클래스 누락</span>\n";
    }
    
} catch (ParseError $e) {
    echo "<span class='critical'>🚨 PHP 구문 오류: " . $e->getMessage() . "</span>\n";
    echo "파일: " . $e->getFile() . " 라인: " . $e->getLine() . "\n";
} catch (Exception $e) {
    echo "<span class='critical'>🚨 컨트롤러 로딩 실패: " . $e->getMessage() . "</span>\n";
}

echo "</pre>";

echo "<h2>5️⃣ 데이터베이스 연결 및 테이블 확인</h2>";
echo "<pre>";

try {
    require_once SRC_PATH . '/config/database.php';
    $db = Database::getInstance();
    echo "<span class='success'>✅ 데이터베이스 연결 성공</span>\n";
    
    // 필수 테이블 확인
    $requiredTables = ['lectures', 'lecture_registrations', 'users'];
    
    foreach ($requiredTables as $table) {
        $result = $db->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            echo "<span class='success'>✅ $table 테이블 존재</span>\n";
            
            // 테이블 레코드 수 확인
            $countResult = $db->query("SELECT COUNT(*) as count FROM $table");
            if ($countResult) {
                $count = $countResult->fetch_assoc()['count'];
                echo "   → 레코드 수: $count\n";
            }
        } else {
            echo "<span class='critical'>🚨 $table 테이블 누락</span>\n";
        }
    }
    
} catch (Exception $e) {
    echo "<span class='critical'>🚨 데이터베이스 오류: " . $e->getMessage() . "</span>\n";
}

echo "</pre>";

echo "<h2>6️⃣ 인증 및 세션 상태 확인</h2>";
echo "<pre>";

session_start();

echo "세션 상태:\n";
foreach ($_SESSION as $key => $value) {
    if (is_string($value) || is_numeric($value)) {
        echo "  $key: $value\n";
    } else {
        echo "  $key: " . gettype($value) . "\n";
    }
}

// AuthMiddleware 테스트
try {
    if (class_exists('AuthMiddleware')) {
        $isLoggedIn = AuthMiddleware::isLoggedIn();
        echo "<span class='" . ($isLoggedIn ? 'success' : 'warning') . "'>" . 
             ($isLoggedIn ? '✅' : '⚠️') . " 로그인 상태: " . ($isLoggedIn ? '로그인됨' : '로그인 안됨') . "</span>\n";
        
        if ($isLoggedIn) {
            $userId = AuthMiddleware::getCurrentUserId();
            $userRole = AuthMiddleware::getUserRole();
            echo "<span class='success'>✅ 사용자 ID: $userId</span>\n";
            echo "<span class='success'>✅ 사용자 권한: $userRole</span>\n";
        }
    }
} catch (Exception $e) {
    echo "<span class='error'>❌ AuthMiddleware 테스트 실패: " . $e->getMessage() . "</span>\n";
}

echo "</pre>";

echo "<h2>7️⃣ 실제 컨트롤러 실행 시뮬레이션</h2>";
echo "<pre>";

try {
    // 실제 컨트롤러 인스턴스 생성 및 실행 테스트
    if (class_exists('RegistrationDashboardController')) {
        echo "컨트롤러 인스턴스 생성 시도...\n";
        
        // 출력 버퍼링으로 실제 실행 테스트
        ob_start();
        
        $controller = new RegistrationDashboardController();
        echo "<span class='success'>✅ 컨트롤러 인스턴스 생성 성공</span>\n";
        
        // index 메소드 호출 시뮬레이션 (실제로는 호출하지 않고 준비만)
        echo "<span class='success'>✅ index 메소드 호출 준비 완료</span>\n";
        
        ob_end_clean();
    }
    
} catch (Error $e) {
    echo "<span class='critical'>🚨 치명적 오류: " . $e->getMessage() . "</span>\n";
    echo "파일: " . $e->getFile() . " 라인: " . $e->getLine() . "\n";
} catch (Exception $e) {
    echo "<span class='critical'>🚨 실행 오류: " . $e->getMessage() . "</span>\n";
    echo "파일: " . $e->getFile() . " 라인: " . $e->getLine() . "\n";
}

echo "</pre>";

echo "<h2>8️⃣ 울트라씽크 해결 방안</h2>";
echo "<div style='color:#fff;padding:15px;background:#222;border-radius:5px;'>";
echo "<h3>🔧 즉시 실행할 단계:</h3>";
echo "<ol>";
echo "<li><strong>누락된 테이블 생성</strong>: fix_registration_system.sql 실행</li>";
echo "<li><strong>라우트 추가 확인</strong>: routes.php에 /registrations 라우트 존재 여부</li>";
echo "<li><strong>컨트롤러 구문 검사</strong>: PHP 파싱 오류 확인</li>";
echo "<li><strong>권한 시스템 점검</strong>: AuthMiddleware 작동 상태</li>";
echo "<li><strong>실제 호출 테스트</strong>: 단계별 디버깅</li>";
echo "</ol>";
echo "<h3>🚀 즉시 테스트:</h3>";
echo "<p><a href='/debug_registration_dashboard.php' style='color:#0f0;'>👉 추가 디버깅 도구 실행</a></p>";
echo "</div>";

echo "</body></html>";
?>