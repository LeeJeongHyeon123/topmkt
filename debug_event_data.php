<?php
/**
 * 이벤트 데이터 디버깅 스크립트
 * 특정 이벤트의 강사 정보를 확인합니다.
 */

// 경로 설정
define('ROOT_PATH', '/workspace/var/www/html/topmkt');
define('SRC_PATH', ROOT_PATH . '/src');

require_once SRC_PATH . '/config/database.php';

echo "<h1>🔍 이벤트 데이터 디버깅</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .debug-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; background: #f9f9f9; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    pre { background: #f0f0f0; padding: 10px; border-radius: 4px; overflow-x: auto; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>";

try {
    $db = Database::getInstance();
    
    // 이벤트 ID 181의 데이터 조회
    $eventId = 181;
    
    echo "<div class='debug-section'>";
    echo "<h2>📊 이벤트 ID {$eventId} 데이터 분석</h2>";
    
    $sql = "SELECT 
                id, title, instructor_name, instructor_info, instructor_image,
                start_date, start_time, venue_name, venue_address,
                created_at, user_id
            FROM lectures 
            WHERE id = ? AND content_type = 'event'";
    
    $event = $db->fetch($sql, [$eventId]);
    
    if ($event) {
        echo "<span class='success'>✅ 이벤트 데이터 발견</span><br>";
        
        echo "<table>";
        echo "<tr><th>필드</th><th>값</th><th>상태</th></tr>";
        
        foreach ($event as $key => $value) {
            $status = '';
            if ($key === 'instructor_name') {
                if ($value === '미정') {
                    $status = '<span class="warning">⚠️ 기본값</span>';
                } elseif (!empty($value)) {
                    $status = '<span class="success">✅ 값 존재</span>';
                } else {
                    $status = '<span class="error">❌ 빈 값</span>';
                }
            }
            
            $displayValue = $value ?: '<em>NULL/비어있음</em>';
            echo "<tr><td><strong>{$key}</strong></td><td>{$displayValue}</td><td>{$status}</td></tr>";
        }
        echo "</table>";
        
        // 강사명 분석
        echo "<h3>🎯 강사명 상세 분석</h3>";
        $instructorName = $event['instructor_name'];
        
        echo "<ul>";
        echo "<li><strong>현재 값:</strong> '" . htmlspecialchars($instructorName) . "'</li>";
        echo "<li><strong>길이:</strong> " . strlen($instructorName) . " 문자</li>";
        echo "<li><strong>타입:</strong> " . gettype($instructorName) . "</li>";
        echo "<li><strong>empty() 체크:</strong> " . (empty($instructorName) ? 'true' : 'false') . "</li>";
        echo "<li><strong>is_null() 체크:</strong> " . (is_null($instructorName) ? 'true' : 'false') . "</li>";
        echo "<li><strong>비교 (== '미정'):</strong> " . ($instructorName == '미정' ? 'true' : 'false') . "</li>";
        echo "</ul>";
        
    } else {
        echo "<span class='error'>❌ 이벤트를 찾을 수 없습니다</span>";
    }
    echo "</div>";
    
    // 최근 이벤트들의 강사명 현황
    echo "<div class='debug-section'>";
    echo "<h2>📈 최근 이벤트들의 강사명 현황</h2>";
    
    $recentSql = "SELECT id, title, instructor_name, created_at 
                  FROM lectures 
                  WHERE content_type = 'event' 
                  ORDER BY created_at DESC 
                  LIMIT 10";
    
    $recentEvents = $db->fetchAll($recentSql);
    
    if ($recentEvents) {
        echo "<table>";
        echo "<tr><th>ID</th><th>제목</th><th>강사명</th><th>등록일시</th><th>상태</th></tr>";
        
        foreach ($recentEvents as $event) {
            $status = '';
            if ($event['instructor_name'] === '미정') {
                $status = '<span class="warning">⚠️ 기본값</span>';
            } elseif (!empty($event['instructor_name'])) {
                $status = '<span class="success">✅ 정상</span>';
            } else {
                $status = '<span class="error">❌ 빈 값</span>';
            }
            
            echo "<tr>";
            echo "<td>{$event['id']}</td>";
            echo "<td>" . htmlspecialchars(mb_substr($event['title'], 0, 30)) . "</td>";
            echo "<td>" . htmlspecialchars($event['instructor_name']) . "</td>";
            echo "<td>{$event['created_at']}</td>";
            echo "<td>{$status}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='debug-section'>";
    echo "<span class='error'>❌ 오류 발생: " . $e->getMessage() . "</span>";
    echo "</div>";
}

echo "<div class='debug-section'>";
echo "<h2>🔧 수정 방법</h2>";
echo "<p>만약 강사명이 잘못 저장되었다면 다음 방법으로 수정할 수 있습니다:</p>";
echo "<ol>";
echo "<li>직접 데이터베이스 UPDATE로 수정</li>";
echo "<li>이벤트 편집 기능을 통해 수정</li>";
echo "<li>새로운 이벤트로 다시 등록</li>";
echo "</ol>";
echo "</div>";
?>