<?php
/**
 * Investigate lecture 157 instructor images issue
 * Using basic MySQLi connection
 */

// Database connection details
$host = 'localhost';
$port = 3306;
$socket = '/var/lib/mysql/mysql.sock';
$database = 'TOPMKT';
$username = 'root';
$password = 'Dnlszkem1!';

echo "=== Investigating Lecture 157 Instructor Images ===\n\n";

try {
    // Try connecting with socket first
    $conn = new mysqli($host, $username, $password, $database, $port, $socket);
    
    if ($conn->connect_error) {
        // Try without socket
        $conn = new mysqli($host, $username, $password, $database, $port);
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
    }
    
    // Set charset
    $conn->set_charset("utf8mb4");
    
    echo "✅ Database connected successfully\n\n";
    
    // 1. Check if lecture 157 exists
    echo "1. Checking if lecture 157 exists:\n";
    $result = $conn->query("SELECT COUNT(*) as count FROM lectures WHERE id = 157");
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        echo "❌ Lecture 157 does not exist\n";
        echo "   Checking latest lecture IDs...\n";
        $latest = $conn->query("SELECT id, title FROM lectures ORDER BY id DESC LIMIT 10");
        while ($lec = $latest->fetch_assoc()) {
            echo "   - Lecture {$lec['id']}: {$lec['title']}\n";
        }
        exit;
    }
    
    echo "✅ Lecture 157 exists\n\n";
    
    // 2. Get full lecture 157 data
    echo "2. Lecture 157 full data:\n";
    $stmt = $conn->prepare("SELECT * FROM lectures WHERE id = 157");
    $stmt->execute();
    $result = $stmt->get_result();
    $lecture = $result->fetch_assoc();
    
    echo "   - ID: {$lecture['id']}\n";
    echo "   - Title: {$lecture['title']}\n";
    echo "   - Instructor Name: {$lecture['instructor_name']}\n";
    echo "   - Instructor Image: " . ($lecture['instructor_image'] ?? 'NULL') . "\n";
    
    // Check if instructors_json field exists
    $columns = $conn->query("SHOW COLUMNS FROM lectures LIKE 'instructors_json'");
    if ($columns->num_rows > 0) {
        echo "   - Instructors JSON: " . ($lecture['instructors_json'] ?? 'NULL') . "\n";
        
        if (!empty($lecture['instructors_json'])) {
            echo "   - JSON Data:\n";
            $instructors = json_decode($lecture['instructors_json'], true);
            if ($instructors) {
                foreach ($instructors as $i => $instructor) {
                    echo "     Instructor " . ($i + 1) . ":\n";
                    echo "       - Name: " . ($instructor['name'] ?? 'N/A') . "\n";
                    echo "       - Image: " . ($instructor['image'] ?? 'N/A') . "\n";
                    echo "       - Bio: " . (substr($instructor['bio'] ?? 'N/A', 0, 50)) . "...\n";
                }
            } else {
                echo "     ❌ Failed to parse JSON\n";
            }
        }
    } else {
        echo "   ❌ instructors_json field does not exist\n";
    }
    
    echo "   - Created: {$lecture['created_at']}\n";
    echo "   - Updated: {$lecture['updated_at']}\n\n";
    
    // 3. Check instructor images in file system
    echo "3. Checking instructor image files:\n";
    $instructor_dir = '/workspace/public/assets/uploads/instructors/';
    
    if (!empty($lecture['instructor_image'])) {
        $image_path = $instructor_dir . $lecture['instructor_image'];
        echo "   - Single instructor image: {$lecture['instructor_image']}\n";
        echo "   - File path: {$image_path}\n";
        echo "   - File exists: " . (file_exists($image_path) ? "✅ Yes" : "❌ No") . "\n";
        if (file_exists($image_path)) {
            echo "   - File size: " . filesize($image_path) . " bytes\n";
            echo "   - Modified: " . date('Y-m-d H:i:s', filemtime($image_path)) . "\n";
        }
    } else {
        echo "   - No single instructor image set\n";
    }
    
    // Check if there are multiple instructor images from JSON
    if (!empty($lecture['instructors_json'])) {
        $instructors = json_decode($lecture['instructors_json'], true);
        if ($instructors) {
            echo "   - Multiple instructor images from JSON:\n";
            foreach ($instructors as $i => $instructor) {
                if (!empty($instructor['image'])) {
                    $image_path = $instructor_dir . $instructor['image'];
                    echo "     Instructor " . ($i + 1) . " image: {$instructor['image']}\n";
                    echo "     File exists: " . (file_exists($image_path) ? "✅ Yes" : "❌ No") . "\n";
                    if (file_exists($image_path)) {
                        echo "     File size: " . filesize($image_path) . " bytes\n";
                        echo "     Modified: " . date('Y-m-d H:i:s', filemtime($image_path)) . "\n";
                    }
                }
            }
        }
    }
    
    // 4. Check recent instructor images in directory
    echo "\n4. Recent instructor images in directory:\n";
    if (is_dir($instructor_dir)) {
        $files = array_diff(scandir($instructor_dir), array('.', '..'));
        $recent_files = [];
        
        foreach ($files as $file) {
            if (is_file($instructor_dir . $file)) {
                $recent_files[] = [
                    'name' => $file,
                    'time' => filemtime($instructor_dir . $file),
                    'size' => filesize($instructor_dir . $file)
                ];
            }
        }
        
        // Sort by modification time (newest first)
        usort($recent_files, function($a, $b) {
            return $b['time'] - $a['time'];
        });
        
        echo "   Recent files (last 10):\n";
        for ($i = 0; $i < min(10, count($recent_files)); $i++) {
            $file = $recent_files[$i];
            echo "   - {$file['name']} ({$file['size']} bytes, " . date('Y-m-d H:i:s', $file['time']) . ")\n";
        }
    } else {
        echo "   ❌ Instructor directory does not exist\n";
    }
    
    // 5. Compare with nearby lectures
    echo "\n5. Comparing with nearby lectures (150-165):\n";
    $nearby = $conn->query("
        SELECT id, title, instructor_name, instructor_image, 
               CASE WHEN instructors_json IS NOT NULL AND instructors_json != '' THEN 'YES' ELSE 'NO' END as has_json
        FROM lectures 
        WHERE id BETWEEN 150 AND 165 
        ORDER BY id
    ");
    
    while ($lec = $nearby->fetch_assoc()) {
        $marker = $lec['id'] == 157 ? '👉' : '  ';
        echo "   {$marker} Lecture {$lec['id']}: {$lec['instructor_name']} | Image: " . 
             ($lec['instructor_image'] ?? 'NULL') . " | JSON: {$lec['has_json']}\n";
    }
    
    // 6. Check debug logs
    echo "\n6. Checking debug logs:\n";
    $log_files = [
        '/workspace/debug_instructor_images.log',
        '/workspace/debug_instructor_validation.log',
        '/workspace/debug_lecture_images_test.log',
        '/workspace/logs/php_errors.log'
    ];
    
    foreach ($log_files as $log_file) {
        if (file_exists($log_file)) {
            echo "   - Found log: {$log_file}\n";
            $content = file_get_contents($log_file);
            if (strpos($content, '157') !== false) {
                echo "     ✅ Contains lecture 157 references\n";
                // Extract relevant lines
                $lines = explode("\n", $content);
                $relevant_lines = array_filter($lines, function($line) {
                    return strpos($line, '157') !== false;
                });
                
                if (!empty($relevant_lines)) {
                    echo "     Recent entries:\n";
                    foreach (array_slice($relevant_lines, -5) as $line) {
                        echo "       " . trim($line) . "\n";
                    }
                }
            } else {
                echo "     ❌ No lecture 157 references found\n";
            }
        } else {
            echo "   - Log not found: {$log_file}\n";
        }
    }
    
    $conn->close();
    
    echo "\n=== Investigation Complete ===\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>