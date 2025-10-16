<?php
// 강의 등록자 확인
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

require_once CONFIG_PATH . '/paths.php';
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';

echo "<h1>🔍 강의 등록자 확인</h1>";

try {
    $db = Database::getInstance();
    
    // 강의 167 정보 조회
    $lecture = $db->fetch('SELECT id, title, user_id as organizer_id FROM lectures WHERE id = 167');
    
    if ($lecture) {
        echo "<h2>📋 강의 정보</h2>";
        echo "강의 ID: " . $lecture['id'] . "<br>";
        echo "강의명: " . htmlspecialchars($lecture['title']) . "<br>";
        echo "등록자 ID: " . $lecture['organizer_id'] . "<br>";
        
        // 등록자 정보 조회
        $organizer = $db->fetch('SELECT id, nickname, email FROM users WHERE id = ?', [$lecture['organizer_id']]);
        
        if ($organizer) {
            echo "<h2>👤 등록자 정보</h2>";
            echo "사용자 ID: " . $organizer['id'] . "<br>";
            echo "닉네임: " . htmlspecialchars($organizer['nickname']) . "<br>";
            echo "이메일: " . htmlspecialchars($organizer['email']) . "<br>";
        }
        
        // 현재 테스트 사용자 ID와 비교
        echo "<h2>⚖️ 비교</h2>";
        echo "테스트 사용자 ID: 5<br>";
        echo "강의 등록자 ID: " . $lecture['organizer_id'] . "<br>";
        
        if ($lecture['organizer_id'] == 5) {
            echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 10px; margin: 10px 0;'>";
            echo "❌ <strong>문제 확인!</strong> 테스트 사용자가 강의 등록자와 동일합니다.";
            echo "</div>";
            
            echo "<h2>🔧 해결 방안</h2>";
            echo "<ol>";
            echo "<li><strong>다른 사용자 ID로 테스트</strong> (예: user_id = 1, 2, 3, 4, 6...)</li>";
            echo "<li><strong>다른 강의로 테스트</strong> (등록자가 다른 강의)</li>";
            echo "</ol>";
        } else {
            echo "<div style='background: #e8f5e8; border: 1px solid #4caf50; padding: 10px; margin: 10px 0;'>";
            echo "✅ <strong>정상!</strong> 테스트 사용자와 강의 등록자가 다릅니다.";
            echo "</div>";
        }
        
    } else {
        echo "❌ 강의 167을 찾을 수 없습니다.";
    }
    
    // 다른 강의들 확인
    echo "<h2>📚 다른 강의들</h2>";
    $lectures = $db->fetchAll('SELECT id, title, user_id as organizer_id FROM lectures WHERE status = "published" ORDER BY id DESC LIMIT 10');
    
    if ($lectures) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>강의 ID</th><th>강의명</th><th>등록자 ID</th><th>테스트 가능</th></tr>";
        
        foreach ($lectures as $lec) {
            $canTest = ($lec['organizer_id'] != 5) ? '✅ 가능' : '❌ 불가';
            echo "<tr>";
            echo "<td>" . $lec['id'] . "</td>";
            echo "<td>" . htmlspecialchars($lec['title']) . "</td>";
            echo "<td>" . $lec['organizer_id'] . "</td>";
            echo "<td>" . $canTest . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "오류: " . $e->getMessage();
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1, h2, h3 { color: #333; }
table { margin: 20px 0; }
th, td { padding: 8px 12px; text-align: left; }
th { background: #f5f5f5; }
</style>