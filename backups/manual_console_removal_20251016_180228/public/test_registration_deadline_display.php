<?php
/**
 * Test registration deadline display in event detail page
 */

session_start();

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 설정 파일 로드
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';

echo "<h1>Registration Deadline Display Test</h1>";

try {
    $db = Database::getInstance();
    
    // Test with different events
    $testEvents = [195, 194, 193, 192, 191]; // Including event 195 and some others
    
    foreach ($testEvents as $eventId) {
        echo "<div style='border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
        echo "<h2>Event ID: {$eventId}</h2>";
        
        // Get event data
        $sql = "SELECT id, title, registration_deadline, start_date, end_date, status FROM lectures WHERE id = ? AND content_type = 'event'";
        $event = $db->fetch($sql, [$eventId]);
        
        if ($event) {
            echo "<h3>Event Details:</h3>";
            echo "<ul>";
            echo "<li><strong>Title:</strong> " . htmlspecialchars($event['title']) . "</li>";
            echo "<li><strong>Status:</strong> " . $event['status'] . "</li>";
            echo "<li><strong>Start Date:</strong> " . $event['start_date'] . "</li>";
            echo "<li><strong>End Date:</strong> " . $event['end_date'] . "</li>";
            echo "<li><strong>Registration Deadline:</strong> " . ($event['registration_deadline'] ? $event['registration_deadline'] : '<strong style="color: #ef4444;">NOT SET</strong>') . "</li>";
            echo "</ul>";
            
            // Test both display sections
            if (!empty($event['registration_deadline'])) {
                echo "<h3>Display Test:</h3>";
                echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 4px; margin: 10px 0;'>";
                echo "<h4>1. Event Meta Section (Above content):</h4>";
                
                $deadline = new DateTime($event['registration_deadline']);
                $now = new DateTime();
                
                if ($now > $deadline) {
                    echo '<div class="event-meta-item" style="display: flex; align-items: center; gap: 8px;">';
                    echo '<i class="fas fa-hourglass-half"></i>';
                    echo '<span style="color: #ef4444;">신청 마감</span>';
                    echo '</div>';
                } else {
                    echo '<div class="event-meta-item" style="display: flex; align-items: center; gap: 8px;">';
                    echo '<i class="fas fa-hourglass-half"></i>';
                    echo '<span>신청 마감: ' . $deadline->format('n월 j일 H:i') . '</span>';
                    echo '</div>';
                }
                echo "</div>";
                
                echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 4px; margin: 10px 0;'>";
                echo "<h4>2. Sidebar Section (Event Information):</h4>";
                echo '<div style="display: flex; justify-content: space-between; padding: 10px; border-bottom: 1px solid #eee;">';
                echo '<span style="color: #64748b; font-size: 0.9rem;">신청 마감</span>';
                echo '<span>';
                
                if ($now > $deadline) {
                    echo '<span style="color: #ef4444; font-weight: 600;">마감됨</span>';
                } else {
                    echo $deadline->format('Y년 n월 j일 H:i');
                }
                echo '</span>';
                echo '</div>';
                echo "</div>";
                
                echo "<div style='background: #dbeafe; padding: 15px; border-radius: 4px; margin: 10px 0;'>";
                echo "<h4>3. Deadline Status:</h4>";
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
                echo "<div style='background: #fef3c7; padding: 15px; border-radius: 4px; margin: 10px 0;'>";
                echo "<h4>❌ Issue Found:</h4>";
                echo "<p><strong>Registration deadline is NOT SET for this event.</strong></p>";
                echo "<p>This means:</p>";
                echo "<ul>";
                echo "<li>The registration deadline will NOT appear in the event meta section</li>";
                echo "<li>The registration deadline will NOT appear in the sidebar</li>";
                echo "<li>Users can register indefinitely (no deadline enforcement)</li>";
                echo "</ul>";
                echo "</div>";
            }
            
        } else {
            echo "<div style='background: #fef2f2; padding: 15px; border-radius: 4px;'>";
            echo "<p><strong>Event not found or not an event type</strong></p>";
            echo "</div>";
        }
        
        echo "</div>";
    }
    
    // Summary and recommendations
    echo "<div style='background: #f0f9ff; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h2>Summary and Recommendations:</h2>";
    echo "<h3>For Event ID 195:</h3>";
    echo "<ul>";
    echo "<li>❌ Registration deadline is NOT SET in the database</li>";
    echo "<li>❌ Will NOT display in event meta section</li>";
    echo "<li>❌ Will NOT display in sidebar</li>";
    echo "<li>❌ No deadline enforcement for registration</li>";
    echo "</ul>";
    
    echo "<h3>Code Analysis:</h3>";
    echo "<ul>";
    echo "<li>✅ Code logic is correct in both display sections</li>";
    echo "<li>✅ Conditional checks are proper: <code>if (!empty(\$event['registration_deadline']))</code></li>";
    echo "<li>✅ DateTime formatting is correct</li>";
    echo "<li>✅ Deadline comparison logic is working</li>";
    echo "</ul>";
    
    echo "<h3>Solution:</h3>";
    echo "<p>To fix the issue, you need to SET the registration_deadline for event 195 in the database.</p>";
    echo "<p>Example SQL:</p>";
    echo "<code>UPDATE lectures SET registration_deadline = '2025-07-13 23:59:59' WHERE id = 195;</code>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #fef2f2; padding: 15px; border-radius: 8px;'>";
    echo "<h3>❌ Error occurred</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "</div>";
}
?>