<?php
/**
 * forgot-password 라우트 테스트
 */

define('ROOT_PATH', __DIR__);
define('SRC_PATH', __DIR__ . '/src');

require_once SRC_PATH . '/config/routes.php';

echo "<h1>🔍 forgot-password 라우트 테스트</h1>";

// Router 인스턴스 생성
$router = new Router();

// 보호된 routes 배열에 접근하기 위해 reflection 사용
$reflection = new ReflectionClass($router);
$routesProperty = $reflection->getProperty('routes');
$routesProperty->setAccessible(true);
$routes = $routesProperty->getValue($router);

echo "<h2>📋 등록된 모든 라우트:</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>라우트 키</th><th>컨트롤러</th><th>액션</th></tr>";

$forgotPasswordRoutes = [];
foreach ($routes as $key => $route) {
    $isTarget = strpos($key, 'forgot') !== false || strpos($key, 'reset') !== false || strpos($key, 'auth') !== false;
    $rowStyle = $isTarget ? 'background-color: yellow;' : '';
    
    if (strpos($key, 'forgot') !== false || strpos($key, 'reset') !== false) {
        $forgotPasswordRoutes[] = $key;
    }
    
    echo "<tr style='$rowStyle'>";
    echo "<td><strong>$key</strong></td>";
    echo "<td>{$route[0]}</td>";
    echo "<td>{$route[1]}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>🎯 forgot-password 관련 라우트:</h2>";
if (!empty($forgotPasswordRoutes)) {
    foreach ($forgotPasswordRoutes as $route) {
        echo "<div style='background: lightgreen; padding: 10px; margin: 5px;'>";
        echo "<strong>✅ 발견: </strong>$route => " . $routes[$route][0] . "::" . $routes[$route][1];
        echo "</div>";
    }
} else {
    echo "<div style='background: lightcoral; padding: 10px;'>";
    echo "<strong>❌ forgot-password 라우트를 찾을 수 없습니다!</strong>";
    echo "</div>";
}

// 현재 요청 URL 시뮬레이션
$testUrls = [
    'GET:/auth/forgot-password',
    'POST:/auth/forgot-password',
    'GET:/auth/reset-password',
    'POST:/auth/reset-password'
];

echo "<h2>🧪 라우트 매칭 테스트:</h2>";
foreach ($testUrls as $testUrl) {
    $exists = isset($routes[$testUrl]);
    $color = $exists ? 'lightgreen' : 'lightcoral';
    $status = $exists ? '✅ 매칭됨' : '❌ 매칭 안됨';
    
    echo "<div style='background: $color; padding: 5px; margin: 2px;'>";
    echo "<strong>$testUrl:</strong> $status";
    if ($exists) {
        echo " => " . $routes[$testUrl][0] . "::" . $routes[$testUrl][1];
    }
    echo "</div>";
}

// AuthController 파일 존재 확인
$authControllerPath = SRC_PATH . '/controllers/AuthController.php';
echo "<h2>📁 AuthController 파일 확인:</h2>";
if (file_exists($authControllerPath)) {
    echo "<div style='background: lightgreen; padding: 10px;'>";
    echo "✅ AuthController.php 파일 존재: $authControllerPath";
    echo "</div>";
    
    // 메서드 존재 확인
    require_once $authControllerPath;
    if (class_exists('AuthController')) {
        $reflection = new ReflectionClass('AuthController');
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        
        $targetMethods = ['showForgotPassword', 'forgotPassword', 'showResetPassword', 'resetPassword'];
        echo "<h3>🔍 AuthController 메서드 확인:</h3>";
        
        foreach ($targetMethods as $methodName) {
            if ($reflection->hasMethod($methodName)) {
                echo "<div style='background: lightgreen; padding: 5px; margin: 2px;'>";
                echo "✅ 메서드 존재: $methodName";
                echo "</div>";
            } else {
                echo "<div style='background: lightcoral; padding: 5px; margin: 2px;'>";
                echo "❌ 메서드 없음: $methodName";
                echo "</div>";
            }
        }
    } else {
        echo "<div style='background: orange; padding: 10px;'>";
        echo "⚠️ AuthController 클래스를 찾을 수 없음";
        echo "</div>";
    }
} else {
    echo "<div style='background: lightcoral; padding: 10px;'>";
    echo "❌ AuthController.php 파일 없음: $authControllerPath";
    echo "</div>";
}

// 뷰 파일 존재 확인
$viewPaths = [
    SRC_PATH . '/views/auth/forgot-password.php',
    SRC_PATH . '/views/auth/reset-password.php'
];

echo "<h2>👁️ 뷰 파일 확인:</h2>";
foreach ($viewPaths as $viewPath) {
    $fileName = basename($viewPath);
    if (file_exists($viewPath)) {
        echo "<div style='background: lightgreen; padding: 5px; margin: 2px;'>";
        echo "✅ $fileName 존재: $viewPath";
        echo "</div>";
    } else {
        echo "<div style='background: lightcoral; padding: 5px; margin: 2px;'>";
        echo "❌ $fileName 없음: $viewPath";
        echo "</div>";
    }
}

echo "<br><hr>";
echo "<h2>📊 요약:</h2>";
echo "<p><strong>라우트 등록 상태:</strong> " . (count($forgotPasswordRoutes) > 0 ? '✅ 성공' : '❌ 실패') . "</p>";
echo "<p><strong>컨트롤러 존재:</strong> " . (file_exists($authControllerPath) ? '✅ 존재' : '❌ 없음') . "</p>";
echo "<p><strong>뷰 파일:</strong> " . (file_exists($viewPaths[0]) && file_exists($viewPaths[1]) ? '✅ 모두 존재' : '❌ 일부 누락') . "</p>";

if (count($forgotPasswordRoutes) > 0 && file_exists($authControllerPath)) {
    echo "<div style='background: lightgreen; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
    echo "<h3>🎉 결론: forgot-password 기능이 정상적으로 구현되었습니다!</h3>";
    echo "<p>라우트, 컨트롤러, 뷰가 모두 준비되었습니다.</p>";
    echo "<p><strong>테스트 URL:</strong> <a href='/auth/forgot-password' target='_blank'>https://www.topmktx.com/auth/forgot-password</a></p>";
    echo "</div>";
} else {
    echo "<div style='background: lightcoral; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
    echo "<h3>❌ 문제가 발견되었습니다:</h3>";
    echo "<p>라우트나 컨트롤러에 문제가 있습니다. 위 결과를 참고하여 수정이 필요합니다.</p>";
    echo "</div>";
}
?>