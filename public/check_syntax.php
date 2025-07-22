<?php
/**
 * PHP 파일 구문 검사
 */

echo "<h1>🔍 PHP 파일 구문 검사</h1>";

$filesToCheck = [
    '/var/www/html/topmkt/src/controllers/RegistrationController.php',
    '/var/www/html/topmkt/src/helpers/ResponseHelper.php',
    '/var/www/html/topmkt/src/helpers/SmsHelper.php',
    '/var/www/html/topmkt/src/middlewares/AuthMiddleware.php'
];

foreach ($filesToCheck as $file) {
    echo "<h2>📄 " . basename($file) . "</h2>";
    
    if (file_exists($file)) {
        // PHP 구문 검사
        $output = shell_exec("php -l '$file' 2>&1");
        
        if (strpos($output, 'No syntax errors') !== false) {
            echo "✅ 구문 오류 없음<br>";
        } else {
            echo "❌ 구문 오류 발견:<br>";
            echo "<pre style='background: #ffe6e6; padding: 10px; border-radius: 5px;'>";
            echo htmlspecialchars($output);
            echo "</pre>";
        }
        
        // 파일 크기 확인
        $size = filesize($file);
        echo "📊 파일 크기: " . number_format($size) . " bytes<br>";
        
        // 마지막 수정 시간
        $mtime = filemtime($file);
        echo "🕒 마지막 수정: " . date('Y-m-d H:i:s', $mtime) . "<br>";
        
    } else {
        echo "❌ 파일이 존재하지 않음<br>";
    }
    
    echo "<br>";
}

// 추가로 include/require 테스트
echo "<h2>🧪 파일 로딩 테스트</h2>";

try {
    echo "1. paths.php 로드 테스트...<br>";
    $pathsFile = '/var/www/html/topmkt/src/config/paths.php';
    if (file_exists($pathsFile)) {
        include_once $pathsFile;
        echo "✅ paths.php 로드 성공<br>";
    }
    
    echo "2. config.php 로드 테스트...<br>";
    $configFile = '/var/www/html/topmkt/src/config/config.php';
    if (file_exists($configFile)) {
        include_once $configFile;
        echo "✅ config.php 로드 성공<br>";
    }
    
    echo "3. ResponseHelper.php 로드 테스트...<br>";
    $responseHelperFile = '/var/www/html/topmkt/src/helpers/ResponseHelper.php';
    if (file_exists($responseHelperFile)) {
        include_once $responseHelperFile;
        echo "✅ ResponseHelper.php 로드 성공<br>";
        
        if (class_exists('ResponseHelper')) {
            echo "✅ ResponseHelper 클래스 존재<br>";
        } else {
            echo "❌ ResponseHelper 클래스 존재하지 않음<br>";
        }
    }
    
    echo "4. AuthMiddleware.php 로드 테스트...<br>";
    $authFile = '/var/www/html/topmkt/src/middlewares/AuthMiddleware.php';
    if (file_exists($authFile)) {
        include_once $authFile;
        echo "✅ AuthMiddleware.php 로드 성공<br>";
        
        if (class_exists('AuthMiddleware')) {
            echo "✅ AuthMiddleware 클래스 존재<br>";
        } else {
            echo "❌ AuthMiddleware 클래스 존재하지 않음<br>";
        }
    }
    
} catch (ParseError $e) {
    echo "❌ 파싱 오류: " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "파일: " . $e->getFile() . ", 라인: " . $e->getLine() . "<br>";
} catch (Error $e) {
    echo "❌ Fatal Error: " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "파일: " . $e->getFile() . ", 라인: " . $e->getLine() . "<br>";
} catch (Exception $e) {
    echo "❌ Exception: " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "파일: " . $e->getFile() . ", 라인: " . $e->getLine() . "<br>";
}

?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1, h2 { color: #333; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
pre { border-radius: 5px; overflow-x: auto; }
</style>