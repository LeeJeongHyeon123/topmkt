<?php
/**
 * 이미지 경로 확인
 */

session_start();

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 설정 파일 로드
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';

echo "<h1>이미지 경로 확인</h1>";

try {
    // 행사 189의 이미지 정보 조회
    $db = Database::getInstance();
    $images = $db->fetchAll("SELECT * FROM event_images WHERE event_id = 189");
    
    echo "<h2>📊 행사 189 이미지 경로 분석</h2>";
    echo "<p>총 이미지 수: " . count($images) . "개</p>";
    
    if (count($images) > 0) {
        echo "<h3>🔍 이미지 경로 상세 분석</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>경로</th><th>파일 존재</th><th>전체 경로</th><th>크기</th></tr>";
        
        foreach ($images as $image) {
            $imagePath = $image['image_path'];
            $fullPath = ROOT_PATH . '/public' . $imagePath;
            
            // 파일 존재 확인
            $exists = file_exists($fullPath);
            $size = $exists ? filesize($fullPath) : 0;
            
            echo "<tr>";
            echo "<td>" . $image['id'] . "</td>";
            echo "<td>" . htmlspecialchars($imagePath) . "</td>";
            echo "<td>" . ($exists ? "✅" : "❌") . "</td>";
            echo "<td>" . htmlspecialchars($fullPath) . "</td>";
            echo "<td>" . ($exists ? number_format($size) . " bytes" : "N/A") . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // 업로드 디렉토리 확인
        echo "<h3>📁 업로드 디렉토리 확인</h3>";
        $uploadDirs = [
            '/assets/uploads/events/2025/07/',
            '/uploads/events/2025/07/',
            '/assets/uploads/events/',
            '/uploads/events/'
        ];
        
        foreach ($uploadDirs as $dir) {
            $fullDirPath = ROOT_PATH . '/public' . $dir;
            $exists = is_dir($fullDirPath);
            echo "<p><strong>" . $dir . "</strong>: " . ($exists ? "✅ 존재" : "❌ 없음") . "</p>";
            
            if ($exists) {
                $files = scandir($fullDirPath);
                $imageFiles = array_filter($files, function($file) {
                    return preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file);
                });
                echo "<ul>";
                foreach ($imageFiles as $file) {
                    echo "<li>" . htmlspecialchars($file) . "</li>";
                }
                echo "</ul>";
            }
        }
        
        // 대체 경로 확인
        echo "<h3>🔍 대체 경로 확인</h3>";
        $alternativePaths = [
            ROOT_PATH . '/public/uploads/',
            ROOT_PATH . '/uploads/',
            ROOT_PATH . '/assets/',
            ROOT_PATH . '/public/assets/'
        ];
        
        foreach ($alternativePaths as $path) {
            $exists = is_dir($path);
            echo "<p><strong>" . $path . "</strong>: " . ($exists ? "✅ 존재" : "❌ 없음") . "</p>";
            
            if ($exists) {
                $files = scandir($path);
                $relevantFiles = array_filter($files, function($file) {
                    return !in_array($file, ['.', '..']) && (is_dir($path . '/' . $file) || preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file));
                });
                
                if (count($relevantFiles) > 0) {
                    echo "<ul>";
                    foreach (array_slice($relevantFiles, 0, 10) as $file) {
                        $isDir = is_dir($path . '/' . $file);
                        echo "<li>" . ($isDir ? "📁 " : "📄 ") . htmlspecialchars($file) . "</li>";
                    }
                    if (count($relevantFiles) > 10) {
                        echo "<li>... 및 " . (count($relevantFiles) - 10) . "개 더</li>";
                    }
                    echo "</ul>";
                }
            }
        }
        
    } else {
        echo "<p>이미지 정보가 없습니다.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 오류 발생: " . $e->getMessage() . "</p>";
}
?>