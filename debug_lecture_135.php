<?php
// 강의 135번 이미지 데이터 디버깅 스크립트
try {
    $mysqli = new mysqli('localhost', 'root', 'Dnlszkem1!', 'TOPMKT', 3306, '/var/lib/mysql/mysql.sock');
    
    if ($mysqli->connect_error) {
        throw new Exception('연결 실패: ' . $mysqli->connect_error);
    }
    
    $mysqli->set_charset('utf8mb4');
    $mysqli->query("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    echo "<h1>강의 135번 이미지 데이터 디버깅</h1>";
    
    // 강의 기본 정보 조회
    $result = $mysqli->query("SELECT id, title, lecture_images FROM lectures WHERE id = 135");
    $lecture = $result->fetch_assoc();
    
    echo "<h2>기본 정보</h2>";
    echo "<pre>";
    echo "ID: " . $lecture['id'] . "\n";
    echo "Title: " . $lecture['title'] . "\n";
    echo "lecture_images (raw): " . $lecture['lecture_images'] . "\n";
    echo "lecture_images length: " . strlen($lecture['lecture_images']) . "\n";
    echo "</pre>";
    
    // JSON 파싱
    echo "<h2>이미지 JSON 파싱</h2>";
    echo "<pre>";
    
    if (empty($lecture['lecture_images'])) {
        echo "❌ lecture_images 필드가 비어있습니다!\n";
    } else {
        $images = json_decode($lecture['lecture_images'], true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "❌ JSON 파싱 오류: " . json_last_error_msg() . "\n";
        } else {
            echo "✅ JSON 파싱 성공\n";
            echo "이미지 개수: " . count($images) . "\n\n";
            
            foreach ($images as $index => $image) {
                echo "=== 이미지 " . ($index + 1) . " ===\n";
                echo "original_name: " . ($image['original_name'] ?? 'NULL') . "\n";
                echo "file_name: " . ($image['file_name'] ?? 'NULL') . "\n";
                echo "file_path: " . ($image['file_path'] ?? 'NULL') . "\n";
                echo "file_size: " . ($image['file_size'] ?? 'NULL') . "\n";
                echo "upload_time: " . ($image['upload_time'] ?? 'NULL') . "\n";
                echo "display_order: " . ($image['display_order'] ?? 'NULL') . "\n";
                
                // 파일 실제 존재 여부 확인
                if (isset($image['file_path'])) {
                    $fullPath = $_SERVER['DOCUMENT_ROOT'] . $image['file_path'];
                    echo "실제 파일 존재: " . (file_exists($fullPath) ? "✅ YES" : "❌ NO") . "\n";
                    echo "전체 경로: " . $fullPath . "\n";
                }
                echo "\n";
            }
        }
    }
    echo "</pre>";
    
    // 최근 업로드된 이미지 파일들 확인
    echo "<h2>최근 업로드된 강의 이미지 파일들</h2>";
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/uploads/lectures/';
    
    if (is_dir($uploadDir)) {
        $files = scandir($uploadDir);
        $recentFiles = array();
        
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..' && is_file($uploadDir . $file)) {
                $recentFiles[$file] = filemtime($uploadDir . $file);
            }
        }
        
        // 시간순 정렬
        arsort($recentFiles);
        $recentFiles = array_slice($recentFiles, 0, 10, true);
        
        echo "<pre>";
        foreach ($recentFiles as $file => $time) {
            echo date('Y-m-d H:i:s', $time) . " - " . $file . "\n";
        }
        echo "</pre>";
    } else {
        echo "<p>❌ 업로드 디렉토리가 존재하지 않습니다: " . $uploadDir . "</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>오류</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>