<?php
/**
 * 이미지 경로 수정
 */

session_start();

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 설정 파일 로드
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';

echo "<h1>이미지 경로 수정</h1>";

try {
    $db = Database::getInstance();
    
    // 행사 189의 이미지 정보 조회
    $images = $db->fetchAll("SELECT * FROM event_images WHERE event_id = 189 ORDER BY id");
    
    echo "<h2>📊 행사 189 이미지 경로 분석 및 수정</h2>";
    echo "<p>총 이미지 수: " . count($images) . "개</p>";
    
    // 실제 파일 목록 가져오기
    $uploadDir = ROOT_PATH . '/public/assets/uploads/events/2025/07/';
    $actualFiles = scandir($uploadDir);
    $imageFiles = array_filter($actualFiles, function($file) {
        return preg_match('/\.jpg$/', $file);
    });
    
    // 파일 크기별로 매칭
    $fileSizes = [];
    foreach ($imageFiles as $file) {
        $filePath = $uploadDir . $file;
        $size = filesize($filePath);
        $fileSizes[$size] = $file;
    }
    
    echo "<h3>📁 실제 파일 목록 (크기별)</h3>";
    echo "<ul>";
    foreach ($fileSizes as $size => $filename) {
        echo "<li>" . htmlspecialchars($filename) . " (" . number_format($size) . " bytes)</li>";
    }
    echo "</ul>";
    
    // 타임스탬프 분석
    echo "<h3>🕐 타임스탬프 분석</h3>";
    
    // 20250709102004_ 와 20250709102633_ 타임스탬프 그룹 확인
    $group1 = array_filter($imageFiles, function($file) {
        return strpos($file, '20250709102004_') === 0;
    });
    
    $group2 = array_filter($imageFiles, function($file) {
        return strpos($file, '20250709102633_') === 0;
    });
    
    echo "<p><strong>20250709102004_ 그룹:</strong> " . count($group1) . "개</p>";
    echo "<p><strong>20250709102633_ 그룹:</strong> " . count($group2) . "개</p>";
    
    // 가장 많은 파일을 가진 그룹 선택 (20250709102633_가 10개)
    $correctTimestamp = '20250709102633_';
    $correctFiles = $group2;
    
    if (count($correctFiles) == 10 && count($images) == 10) {
        echo "<h3>✅ 올바른 파일 매칭 발견!</h3>";
        echo "<p>20250709102633_ 타임스탬프 그룹에 정확히 10개 파일이 있습니다.</p>";
        
        // 파일 매칭 및 업데이트
        $sortedFiles = array_values($correctFiles);
        sort($sortedFiles);
        
        echo "<h3>🔄 데이터베이스 업데이트</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>기존 경로</th><th>새 경로</th><th>상태</th></tr>";
        
        foreach ($images as $index => $image) {
            if (isset($sortedFiles[$index])) {
                $oldPath = $image['image_path'];
                $newPath = '/assets/uploads/events/2025/07/' . $sortedFiles[$index];
                
                // 파일 존재 확인
                $newFilePath = ROOT_PATH . '/public' . $newPath;
                $exists = file_exists($newFilePath);
                
                echo "<tr>";
                echo "<td>" . $image['id'] . "</td>";
                echo "<td>" . htmlspecialchars($oldPath) . "</td>";
                echo "<td>" . htmlspecialchars($newPath) . "</td>";
                echo "<td>" . ($exists ? "✅ 존재" : "❌ 없음") . "</td>";
                echo "</tr>";
                
                if ($exists) {
                    // 데이터베이스 업데이트
                    $db->execute("UPDATE event_images SET image_path = ? WHERE id = ?", [$newPath, $image['id']]);
                }
            }
        }
        echo "</table>";
        
        echo "<h3>🎉 업데이트 완료!</h3>";
        echo "<p>행사 189의 이미지 경로가 정상적으로 수정되었습니다.</p>";
        
    } else {
        echo "<h3>❌ 자동 매칭 실패</h3>";
        echo "<p>파일 수가 일치하지 않습니다. 수동으로 확인이 필요합니다.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 오류 발생: " . $e->getMessage() . "</p>";
}
?>