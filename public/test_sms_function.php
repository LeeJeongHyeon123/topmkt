<?php
/**
 * SMS 함수 테스트
 */

// 오류 표시 활성화
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>📱 SMS 함수 테스트</h1>";

// 상수 정의
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

try {
    echo "<h2>📂 SmsHelper 로드</h2>";
    
    if (file_exists(SRC_PATH . '/helpers/SmsHelper.php')) {
        require_once SRC_PATH . '/helpers/SmsHelper.php';
        echo "✅ SmsHelper.php 로드 완료<br>";
        
        // sendLectureApplicationSms 함수 존재 확인
        if (function_exists('sendLectureApplicationSms')) {
            echo "✅ sendLectureApplicationSms 함수 존재<br>";
            
            echo "<h3>🧪 함수 테스트</h3>";
            echo "테스트 전화번호: 010-1234-5678<br>";
            
            // 실제 SMS 발송은 하지 않고 함수 호출만 테스트
            try {
                // SMS 서비스가 없어도 함수 정의가 있는지만 확인
                echo "함수 호출 테스트 시작...<br>";
                
                // 함수 시그니처 확인
                $reflection = new ReflectionFunction('sendLectureApplicationSms');
                $params = $reflection->getParameters();
                
                echo "📋 함수 매개변수:<br>";
                foreach ($params as $param) {
                    echo "- " . $param->getName() . "<br>";
                }
                
                echo "✅ 함수 정의가 올바름<br>";
                
            } catch (Exception $e) {
                echo "❌ 함수 호출 오류: " . htmlspecialchars($e->getMessage()) . "<br>";
            }
            
        } else {
            echo "❌ sendLectureApplicationSms 함수가 존재하지 않음<br>";
        }
        
        // 다른 SMS 함수들도 확인
        echo "<h3>📋 다른 SMS 함수 확인</h3>";
        $smsFunctions = [
            'sendSms',
            'sendLectureApprovalSms',
            'sendLectureRejectionSms',
            'sendAuthCodeSms',
            'formatPhone',
            'isValidPhone'
        ];
        
        foreach ($smsFunctions as $func) {
            if (function_exists($func)) {
                echo "✅ $func<br>";
            } else {
                echo "❌ $func<br>";
            }
        }
        
    } else {
        echo "❌ SmsHelper.php 파일을 찾을 수 없음<br>";
    }
    
    echo "<h2>🔧 RegistrationController 로드 테스트</h2>";
    
    // RegistrationController가 SmsHelper를 로드할 수 있는지 확인
    if (file_exists(SRC_PATH . '/controllers/RegistrationController.php')) {
        echo "✅ RegistrationController.php 존재<br>";
        
        // 파일 내용에서 SMS 관련 코드 확인
        $content = file_get_contents(SRC_PATH . '/controllers/RegistrationController.php');
        
        if (strpos($content, 'sendLectureApplicationSms') !== false) {
            echo "✅ RegistrationController에서 sendLectureApplicationSms 호출 확인<br>";
        } else {
            echo "❌ RegistrationController에서 sendLectureApplicationSms 호출 없음<br>";
        }
        
        if (strpos($content, "require_once SRC_PATH . '/helpers/SmsHelper.php'") !== false) {
            echo "✅ RegistrationController에서 SmsHelper 로드 확인<br>";
        } else {
            echo "❌ RegistrationController에서 SmsHelper 로드 없음<br>";
        }
    }
    
} catch (Exception $e) {
    echo "<h2>❌ 테스트 실패</h2>";
    echo "예외: " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "파일: " . $e->getFile() . "<br>";
    echo "라인: " . $e->getLine() . "<br>";
} catch (Error $e) {
    echo "<h2>❌ Fatal Error</h2>";
    echo "Fatal Error: " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "파일: " . $e->getFile() . "<br>";
    echo "라인: " . $e->getLine() . "<br>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1, h2, h3 { color: #333; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
</style>