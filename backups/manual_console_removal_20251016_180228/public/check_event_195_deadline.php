<?php
/**
 * Check event 195 registration deadline
 */

session_start();

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 설정 파일 로드
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';

echo "<h1>Event 195 Registration Deadline Check</h1>";

try {
    $eventId = 195;
    
    echo "<h2>🔍 Event ID: {$eventId}</h2>";
    
    // 이벤트 조회
    $db = Database::getInstance();
    $sql = "SELECT id, title, registration_deadline, start_date, end_date, status, created_at FROM lectures WHERE id = ? AND content_type = 'event'";
    $event = $db->fetch($sql, [$eventId]);
    
    if ($event) {
        echo "<div style='background: #f0fdf4; padding: 15px; border-radius: 8px;'>";
        echo "<h3>✅ Event Found</h3>";
        echo "<ul>";
        echo "<li><strong>ID:</strong> " . $event['id'] . "</li>";
        echo "<li><strong>Title:</strong> " . htmlspecialchars($event['title']) . "</li>";
        echo "<li><strong>Status:</strong> " . $event['status'] . "</li>";
        echo "<li><strong>Start Date:</strong> " . $event['start_date'] . "</li>";
        echo "<li><strong>End Date:</strong> " . $event['end_date'] . "</li>";
        echo "<li><strong>Registration Deadline:</strong> " . ($event['registration_deadline'] ? $event['registration_deadline'] : 'NOT SET') . "</li>";
        echo "<li><strong>Created At:</strong> " . $event['created_at'] . "</li>";
        echo "</ul>";
        echo "</div>";
        
        // Check if registration deadline is set
        if (!empty($event['registration_deadline'])) {
            echo "<div style='background: #f0f9ff; padding: 15px; border-radius: 8px; margin-top: 15px;'>";
            echo "<h3>⏰ Registration Deadline Status</h3>";
            
            $now = new DateTime();
            $deadline = new DateTime($event['registration_deadline']);
            
            echo "<p><strong>Current Time:</strong> " . $now->format('Y-m-d H:i:s') . "</p>";
            echo "<p><strong>Deadline:</strong> " . $deadline->format('Y-m-d H:i:s') . "</p>";
            
            if ($now > $deadline) {
                echo "<p style='color: #ef4444; font-weight: 600;'>❌ Registration Deadline PASSED</p>";
            } else {
                echo "<p style='color: #22c55e; font-weight: 600;'>✅ Registration Still OPEN</p>";
                $diff = $deadline->diff($now);
                echo "<p>Time remaining: " . $diff->format('%d days, %h hours, %i minutes') . "</p>";
            }
            echo "</div>";
        } else {
            echo "<div style='background: #fef3c7; padding: 15px; border-radius: 8px; margin-top: 15px;'>";
            echo "<h3>⚠️ No Registration Deadline Set</h3>";
            echo "<p>This event does not have a registration deadline configured.</p>";
            echo "</div>";
        }
        
    } else {
        echo "<div style='background: #fef2f2; padding: 15px; border-radius: 8px;'>";
        echo "<h3>❌ Event Not Found</h3>";
        echo "<p>Event with ID {$eventId} does not exist or is not an event type.</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #fef2f2; padding: 15px; border-radius: 8px;'>";
    echo "<h3>❌ Error occurred</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "</div>";
}
?>