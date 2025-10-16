<?php
/**
 * 신청 취소 즉시 테스트
 */

// 상수 정의
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

echo "<h1>🔧 신청 취소 즉시 테스트</h1>";

// 모든 PHP 오류 표시
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 세션 시작
session_start();

try {
    // 기본 설정 로드
    require_once CONFIG_PATH . '/paths.php';
    require_once CONFIG_PATH . '/config.php';
    require_once CONFIG_PATH . '/database.php';
    
    echo "<h2>✅ 시스템 로드 완료</h2>";
    
    // 데이터베이스 연결
    $db = Database::getInstance();
    $connection = $db->getConnection();
    echo "✅ 데이터베이스 연결 성공<br>";
    
    // 현재 신청 정보 확인
    echo "<h2>📋 현재 신청 정보</h2>";
    $registration = $connection->query("SELECT * FROM lecture_registrations WHERE lecture_id = 167 AND user_id = 5 ORDER BY created_at DESC LIMIT 1")->fetch_assoc();
    
    if ($registration) {
        echo "신청 ID: " . $registration['id'] . "<br>";
        echo "현재 상태: " . $registration['status'] . "<br>";
        echo "사용자 ID: " . $registration['user_id'] . "<br>";
        echo "강의 ID: " . $registration['lecture_id'] . "<br>";
        
        echo "<h2>🚀 직접 취소 실행</h2>";
        
        // 직접 UPDATE 쿼리 실행
        $updateQuery = "UPDATE lecture_registrations SET status = 'cancelled', processed_at = NOW() WHERE id = ?";
        $stmt = $connection->prepare($updateQuery);
        
        if (!$stmt) {
            echo "❌ Prepare 실패: " . $connection->error . "<br>";
        } else {
            echo "✅ Prepare 성공<br>";
            
            $stmt->bind_param("i", $registration['id']);
            echo "✅ 파라미터 바인딩 완료 (ID: " . $registration['id'] . ")<br>";
            
            if ($stmt->execute()) {
                $affectedRows = $stmt->affected_rows;
                echo "✅ 쿼리 실행 성공<br>";
                echo "영향받은 행: " . $affectedRows . "<br>";
                
                if ($affectedRows > 0) {
                    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
                    echo "🎉 <strong>신청 취소 성공!</strong>";
                    echo "</div>";
                    
                    // 업데이트 후 상태 확인
                    echo "<h2>🔍 업데이트 후 상태</h2>";
                    $updatedReg = $connection->query("SELECT * FROM lecture_registrations WHERE id = " . $registration['id'])->fetch_assoc();
                    echo "상태: " . $updatedReg['status'] . "<br>";
                    echo "처리일시: " . ($updatedReg['processed_at'] ?? 'null') . "<br>";
                    
                } else {
                    echo "<div style='background: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
                    echo "⚠️ <strong>업데이트된 행이 없습니다.</strong> 이미 취소되었거나 조건에 맞지 않습니다.";
                    echo "</div>";
                }
            } else {
                echo "❌ 쿼리 실행 실패: " . $stmt->error . "<br>";
            }
        }
        
    } else {
        echo "❌ 신청 정보를 찾을 수 없습니다.<br>";
        
        // 모든 신청 내역 확인
        echo "<h3>모든 신청 내역:</h3>";
        $allRegs = $connection->query("SELECT * FROM lecture_registrations WHERE lecture_id = 167")->fetch_all(MYSQLI_ASSOC);
        echo "<pre>" . print_r($allRegs, true) . "</pre>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ 오류 발생</h2>";
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px;'>";
    echo "<strong>오류:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>파일:</strong> " . $e->getFile() . "<br>";
    echo "<strong>라인:</strong> " . $e->getLine() . "<br>";
    echo "</div>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1, h2 { color: #333; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style>