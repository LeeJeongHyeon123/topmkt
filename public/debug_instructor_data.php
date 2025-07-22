<?php
/**
 * 강사명 디버깅 스크립트
 */

// 경로 설정
define('ROOT_PATH', '/workspace/var/www/html/topmkt');
define('SRC_PATH', ROOT_PATH . '/src');

require_once SRC_PATH . '/config/database.php';

echo "<h1>🔍 강사명 데이터 디버깅 (이벤트 ID: 181)</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .debug-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; background: #f9f9f9; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>";

try {
    $db = Database::getInstance();
    
    echo "<div class='debug-section'>";
    echo "<h2>📊 이벤트 ID 181 강사 데이터 분석</h2>";
    
    $sql = "SELECT id, title, instructor_name, instructor_info, created_at FROM lectures WHERE id = 181 AND content_type = 'event'";
    $event = $db->fetch($sql);
    
    if ($event) {
        echo "<span class='success'>✅ 이벤트 발견</span><br><br>";
        
        echo "<table>";
        echo "<tr><th>필드</th><th>값</th><th>분석</th></tr>";
        echo "<tr><td><strong>ID</strong></td><td>{$event['id']}</td><td>-</td></tr>";
        echo "<tr><td><strong>제목</strong></td><td>" . htmlspecialchars($event['title']) . "</td><td>-</td></tr>";
        
        $instructorName = $event['instructor_name'];
        $analysis = '';
        if ($instructorName === '미정') {
            $analysis = '<span class="warning">⚠️ 기본값으로 저장됨</span>';
        } elseif (!empty($instructorName)) {
            $analysis = '<span class="success">✅ 정상적인 강사명</span>';
        } else {
            $analysis = '<span class="error">❌ NULL 또는 빈값</span>';
        }
        
        echo "<tr><td><strong>강사명</strong></td><td>'" . htmlspecialchars($instructorName) . "'</td><td>{$analysis}</td></tr>";
        echo "<tr><td><strong>강사 정보</strong></td><td>" . htmlspecialchars($event['instructor_info'] ?: '없음') . "</td><td>-</td></tr>";
        echo "<tr><td><strong>등록일시</strong></td><td>{$event['created_at']}</td><td>-</td></tr>";
        echo "</table>";
        
        echo "<h3>🎯 문제 분석</h3>";
        if ($instructorName === '미정') {
            echo "<p><span class='warning'>⚠️ 강사명이 '미정'으로 저장되어 있습니다.</span></p>";
            echo "<p><strong>가능한 원인:</strong></p>";
            echo "<ul>";
            echo "<li>폼에서 instructor_names[] 배열로 전송되었지만 백엔드에서 instructor_name으로 처리</li>";
            echo "<li>빈 문자열이 전송되어 기본값 '미정'이 적용됨</li>";
            echo "<li>JavaScript 폼 처리 과정에서 데이터 손실</li>";
            echo "</ul>";
            
            echo "<h3>🔧 해결책</h3>";
            echo "<p>EventController를 수정하여 instructor_names[] 배열을 올바르게 처리하도록 했습니다.</p>";
            echo "<p><strong>다음 테스트 방법:</strong></p>";
            echo "<ol>";
            echo "<li>새로운 이벤트 등록으로 수정사항 테스트</li>";
            echo "<li>기존 이벤트 수정 기능으로 강사명 업데이트</li>";
            echo "</ol>";
        }
        
    } else {
        echo "<span class='error'>❌ 이벤트 ID 181을 찾을 수 없습니다</span>";
    }
    echo "</div>";
    
    // 최근 이벤트들 확인
    echo "<div class='debug-section'>";
    echo "<h2>📈 최근 이벤트들의 강사명 현황</h2>";
    
    $recentSql = "SELECT id, title, instructor_name, created_at FROM lectures WHERE content_type = 'event' ORDER BY created_at DESC LIMIT 5";
    $recentEvents = $db->fetchAll($recentSql);
    
    if ($recentEvents) {
        echo "<table>";
        echo "<tr><th>ID</th><th>제목</th><th>강사명</th><th>등록일시</th></tr>";
        
        foreach ($recentEvents as $event) {
            $nameClass = ($event['instructor_name'] === '미정') ? 'warning' : 'success';
            echo "<tr>";
            echo "<td>{$event['id']}</td>";
            echo "<td>" . htmlspecialchars(mb_substr($event['title'], 0, 20)) . "...</td>";
            echo "<td class='{$nameClass}'>" . htmlspecialchars($event['instructor_name']) . "</td>";
            echo "<td>{$event['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='debug-section'>";
    echo "<span class='error'>❌ 오류: " . $e->getMessage() . "</span>";
    echo "</div>";
}
?>