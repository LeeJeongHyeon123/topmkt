<?php
/**
 * 강의 86번 위치 정보 업데이트
 */

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

// 데이터베이스 설정 로드
require_once SRC_PATH . '/config/database.php';

echo "<h1>🎯 강의 86번 위치 정보 업데이트</h1>\n";

try {
    // 데이터베이스 연결
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 4px solid #48bb78;'>\n";
    echo "<p style='color: #2d5016; margin: 0;'>✅ 데이터베이스 연결 성공</p>\n";
    echo "</div>\n";
    
    // 현재 강의 86번 정보 조회
    $stmt = $pdo->prepare("SELECT id, title, venue_name, venue_address, location_type FROM lectures WHERE id = ?");
    $stmt->execute([86]);
    $lecture = $stmt->fetch();
    
    if (!$lecture) {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 4px solid #dc3545;'>\n";
        echo "<p style='color: #721c24; margin: 0;'>❌ 강의 86번을 찾을 수 없습니다.</p>\n";
        echo "</div>\n";
        exit;
    }
    
    echo "<h2>📋 현재 강의 정보</h2>\n";
    echo "<div style='background: #f8fafc; padding: 15px; border-radius: 8px; margin: 10px 0; border: 1px solid #e2e8f0;'>\n";
    echo "<p><strong>강의 제목:</strong> " . htmlspecialchars($lecture['title']) . "</p>\n";
    echo "<p><strong>현재 장소명:</strong> " . htmlspecialchars($lecture['venue_name'] ?? '없음') . "</p>\n";
    echo "<p><strong>현재 주소:</strong> " . htmlspecialchars($lecture['venue_address'] ?? '없음') . "</p>\n";
    echo "<p><strong>위치 타입:</strong> " . htmlspecialchars($lecture['location_type'] ?? '없음') . "</p>\n";
    echo "</div>\n";
    
    // 업데이트 실행
    $newVenueName = "반도 아이비밸리";
    $newVenueAddress = "서울시 금천구 가산디지털1로 204, 반도 아이비밸리";
    $newLocationType = "offline"; // 오프라인으로 설정
    
    $updateStmt = $pdo->prepare("
        UPDATE lectures 
        SET venue_name = ?, venue_address = ?, location_type = ?, updated_at = NOW() 
        WHERE id = ?
    ");
    
    $result = $updateStmt->execute([$newVenueName, $newVenueAddress, $newLocationType, 86]);
    
    if ($result) {
        echo "<h2>✅ 업데이트 완료</h2>\n";
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 4px solid #28a745;'>\n";
        echo "<p style='color: #155724; margin: 0;'><strong>새로운 장소명:</strong> " . htmlspecialchars($newVenueName) . "</p>\n";
        echo "<p style='color: #155724; margin: 5px 0 0 0;'><strong>새로운 주소:</strong> " . htmlspecialchars($newVenueAddress) . "</p>\n";
        echo "<p style='color: #155724; margin: 5px 0 0 0;'><strong>위치 타입:</strong> " . htmlspecialchars($newLocationType) . "</p>\n";
        echo "</div>\n";
        
        // 업데이트 후 확인
        $checkStmt = $pdo->prepare("SELECT venue_name, venue_address, location_type FROM lectures WHERE id = ?");
        $checkStmt->execute([86]);
        $updatedLecture = $checkStmt->fetch();
        
        echo "<h2>🔍 업데이트 후 확인</h2>\n";
        echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 4px solid #48bb78;'>\n";
        echo "<p style='color: #2d5016; margin: 0;'><strong>장소명:</strong> " . htmlspecialchars($updatedLecture['venue_name']) . "</p>\n";
        echo "<p style='color: #2d5016; margin: 5px 0 0 0;'><strong>주소:</strong> " . htmlspecialchars($updatedLecture['venue_address']) . "</p>\n";
        echo "<p style='color: #2d5016; margin: 5px 0 0 0;'><strong>위치 타입:</strong> " . htmlspecialchars($updatedLecture['location_type']) . "</p>\n";
        echo "</div>\n";
        
        echo "<div style='background: #cff4fc; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #0dcaf0;'>\n";
        echo "<p style='color: #055160; margin: 0;'>🎉 강의 위치 업데이트가 성공적으로 완료되었습니다!</p>\n";
        echo "<p style='color: #055160; margin: 10px 0 0 0;'>📍 이제 <a href='https://www.topmktx.com/lectures/86' target='_blank' style='color: #0dcaf0; font-weight: bold;'>강의 페이지</a>에서 새로운 위치를 확인할 수 있습니다.</p>\n";
        echo "</div>\n";
        
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 4px solid #dc3545;'>\n";
        echo "<p style='color: #721c24; margin: 0;'>❌ 업데이트 실패</p>\n";
        echo "</div>\n";
    }
    
} catch (PDOException $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 4px solid #dc3545;'>\n";
    echo "<p style='color: #721c24; margin: 0;'>❌ 데이터베이스 오류: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "</div>\n";
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 4px solid #dc3545;'>\n";
    echo "<p style='color: #721c24; margin: 0;'>❌ 오류: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "</div>\n";
}

echo "<br><a href='/lectures/86' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📍 강의 페이지에서 확인하기</a>\n";
echo "<br><br><a href='/' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;'>🏠 메인 페이지로 돌아가기</a>\n";
?>