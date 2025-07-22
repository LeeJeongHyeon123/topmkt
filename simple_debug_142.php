<?php
/**
 * 간단한 강의 142번 이미지 순서 확인
 */

try {
    $mysqli = new mysqli('localhost', 'root', 'Dnlszkem1!', 'TOPMKT', 3306, '/var/lib/mysql/mysql.sock');
    
    if ($mysqli->connect_error) {
        throw new Exception('연결 실패: ' . $mysqli->connect_error);
    }
    
    $mysqli->set_charset('utf8mb4');
    
    // 강의 142번 이미지 데이터 조회
    $result = $mysqli->query("SELECT id, title, lecture_images FROM lectures WHERE id = 142");
    $lecture = $result->fetch_assoc();
    
    echo "<h1>강의 142번 이미지 순서 분석</h1>";
    echo "<p><strong>강의 ID:</strong> " . $lecture['id'] . "</p>";
    echo "<p><strong>제목:</strong> " . $lecture['title'] . "</p>";
    
    // JSON 파싱
    $images = json_decode($lecture['lecture_images'], true);
    
    echo "<h2>데이터베이스에 저장된 순서</h2>";
    if (is_array($images)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th style='padding: 10px;'>DB Index</th>";
        echo "<th style='padding: 10px;'>Display Order</th>";
        echo "<th style='padding: 10px;'>File Name</th>";
        echo "<th style='padding: 10px;'>File Path</th>";
        echo "<th style='padding: 10px;'>파일 존재</th>";
        echo "</tr>";
        
        foreach ($images as $idx => $img) {
            $fileName = $img['file_name'] ?? '';
            $displayOrder = $img['display_order'] ?? 'MISSING';
            $filePath = $img['file_path'] ?? '';
            $fullPath = "/workspace/public" . $filePath;
            $exists = file_exists($fullPath);
            
            echo "<tr>";
            echo "<td style='padding: 10px; text-align: center;'>$idx</td>";
            echo "<td style='padding: 10px; text-align: center; font-weight: bold; color: blue;'>$displayOrder</td>";
            echo "<td style='padding: 10px; font-family: monospace;'>$fileName</td>";
            echo "<td style='padding: 10px; font-family: monospace;'>$filePath</td>";
            $existsText = $exists ? "✅ 존재" : "❌ 없음";
            $existsColor = $exists ? "green" : "red";
            echo "<td style='padding: 10px; text-align: center; color: $existsColor;'>$existsText</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // display_order로 정렬했을 때의 순서
        echo "<h2>display_order로 정렬한 순서 (실제 표시 순서)</h2>";
        usort($images, function($a, $b) {
            $orderA = $a['display_order'] ?? 999;
            $orderB = $b['display_order'] ?? 999;
            return $orderA - $orderB;
        });
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
        echo "<tr style='background: #e6f3ff;'>";
        echo "<th style='padding: 10px;'>표시 순서</th>";
        echo "<th style='padding: 10px;'>Display Order</th>";
        echo "<th style='padding: 10px;'>File Name</th>";
        echo "<th style='padding: 10px;'>URL</th>";
        echo "</tr>";
        
        foreach ($images as $idx => $img) {
            $fileName = $img['file_name'] ?? '';
            $displayOrder = $img['display_order'] ?? 'MISSING';
            $filePath = $img['file_path'] ?? '';
            
            echo "<tr>";
            echo "<td style='padding: 10px; text-align: center; font-weight: bold; color: red;'>" . ($idx + 1) . "</td>";
            echo "<td style='padding: 10px; text-align: center; font-weight: bold; color: blue;'>$displayOrder</td>";
            echo "<td style='padding: 10px; font-family: monospace;'>$fileName</td>";
            echo "<td style='padding: 10px; font-family: monospace;'>$filePath</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h2>결론</h2>";
        echo "<p><strong>드래그&드롭으로 설정된 순서:</strong></p>";
        echo "<ul>";
        foreach ($images as $idx => $img) {
            $displayOrder = $img['display_order'] ?? 'MISSING';
            echo "<li>표시 순서 " . ($idx + 1) . ": display_order = $displayOrder (파일: " . ($img['file_name'] ?? 'MISSING') . ")</li>";
        }
        echo "</ul>";
        
    } else {
        echo "<p style='color: red;'>JSON 파싱 실패</p>";
    }
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>오류 발생</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>