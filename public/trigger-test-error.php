<?php
/**
 * 테스트 에러 발생 (로깅 시스템 테스트용)
 */

// 로깅 시스템 초기화
require_once '../src/helpers/WebLogger.php';
require_once '../src/helpers/ResponseHelper.php';
require_once '../src/helpers/GlobalErrorHandler.php';

session_start();

// 글로벌 에러 핸들러 등록
GlobalErrorHandler::register();
WebLogger::init();

echo "<h1>테스트 에러 발생기</h1>";
echo "<p>다양한 종류의 에러를 의도적으로 발생시켜 로깅 시스템을 테스트합니다.</p>";

// AJAX 요청인지 확인
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (isset($_GET['type'])) {
    $errorType = $_GET['type'];
    
    WebLogger::info('Test error trigger', ['error_type' => $errorType]);
    
    switch ($errorType) {
        case 'validation':
            throw new ValidationException([
                'email' => '이메일 형식이 올바르지 않습니다.',
                'password' => '비밀번호는 8자 이상이어야 합니다.'
            ]);
            
        case 'database':
            throw new DatabaseException('테스트용 데이터베이스 오류', 'SELECT * FROM non_existing_table');
            
        case 'authorization':
            throw new AuthorizationException('테스트용 권한 오류');
            
        case 'notfound':
            throw new NotFoundException('테스트용 리소스 없음 오류');
            
        case 'ratelimit':
            throw new RateLimitException('테스트용 요청 제한 오류');
            
        case 'fatal':
            // Fatal Error 발생
            call_undefined_function();
            
        case 'warning':
            // Warning 발생
            WebLogger::warning('테스트 경고', ['test' => true]);
            echo "경고가 로그에 기록되었습니다.";
            break;
            
        case 'critical':
            WebLogger::critical('테스트 심각한 오류', ['test' => true]);
            echo "심각한 오류가 로그에 기록되었습니다.";
            break;
            
        default:
            echo "알 수 없는 에러 타입: " . htmlspecialchars($errorType);
    }
} else {
    // 테스트 버튼들
    echo '<div style="margin: 20px 0;">';
    
    $errorTypes = [
        'validation' => 'Validation Error',
        'database' => 'Database Error', 
        'authorization' => 'Authorization Error',
        'notfound' => 'Not Found Error',
        'ratelimit' => 'Rate Limit Error',
        'warning' => 'Warning Log',
        'critical' => 'Critical Log',
        'fatal' => 'Fatal Error'
    ];
    
    foreach ($errorTypes as $type => $label) {
        echo "<button onclick=\"triggerError('{$type}')\" style=\"margin: 5px; padding: 8px 15px;\">{$label}</button><br>";
    }
    
    echo '</div>';
    
    echo '<div id="result" style="margin-top: 20px; padding: 10px; background: #f5f5f5; min-height: 50px;"></div>';
    
    echo '<script>
    function triggerError(type) {
        document.getElementById("result").innerHTML = "에러 발생 중...";
        
        fetch("?type=" + type, {
            method: "GET",
            headers: {
                "X-Requested-With": "XMLHttpRequest"
            }
        })
        .then(response => {
            console.log("응답 상태:", response.status);
            return response.json().catch(() => response.text());
        })
        .then(data => {
            console.log("응답 데이터:", data);
            if (typeof data === "object") {
                document.getElementById("result").innerHTML = "<pre>" + JSON.stringify(data, null, 2) + "</pre>";
            } else {
                document.getElementById("result").innerHTML = "<pre>" + data + "</pre>";
            }
        })
        .catch(error => {
            console.error("오류:", error);
            document.getElementById("result").innerHTML = "<p style=\"color: red;\">오류: " + error.message + "</p>";
        });
    }
    </script>';
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
button { cursor: pointer; }
button:hover { background: #e0e0e0; }
</style>