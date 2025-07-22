<?php
/**
 * 강의 143번 DB 데이터 직접 확인
 */

$servername = "localhost";
$username = "root"; 
$password = "Dnlszkem1!";
$dbname = "TOPMKT";
$socket = "/var/lib/mysql/mysql.sock";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;unix_socket=$socket;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 강의 143번 데이터 조회
    $stmt = $pdo->prepare("SELECT id, title, lecture_images FROM lectures WHERE id = ?");
    $stmt->execute([143]);
    $lecture = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h1>강의 143번 DB 데이터 확인</h1>\n";
    echo "<p><strong>강의 ID:</strong> " . $lecture['id'] . "</p>\n";
    echo "<p><strong>제목:</strong> " . $lecture['title'] . "</p>\n";
    
    echo "<h2>원본 JSON 데이터</h2>\n";
    echo "<textarea style='width: 100%; height: 200px;'>" . htmlspecialchars($lecture['lecture_images']) . "</textarea>\n";
    
    // JSON 파싱
    $images = json_decode($lecture['lecture_images'], true);
    
    if (is_array($images)) {
        echo "<h2>파싱된 이미지 데이터</h2>\n";
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>\n";
        echo "<tr style='background: #f0f0f0;'>\n";
        echo "<th>배열 순서</th><th>display_order</th><th>파일명</th><th>temp_index</th><th>file_path</th>\n";
        echo "</tr>\n";
        
        foreach ($images as $idx => $img) {
            $displayOrder = $img['display_order'] ?? '❌ 없음';
            $fileName = $img['file_name'] ?? '❌ 없음';
            $tempIndex = $img['temp_index'] ?? '❌ 없음';
            $filePath = $img['file_path'] ?? '❌ 없음';
            
            echo "<tr>\n";
            echo "<td style='text-align: center; font-weight: bold;'>$idx</td>\n";
            echo "<td style='text-align: center; font-weight: bold; color: blue;'>$displayOrder</td>\n";
            echo "<td style='font-family: monospace; font-size: 11px;'>$fileName</td>\n";
            echo "<td style='text-align: center; color: red;'>$tempIndex</td>\n";
            echo "<td style='font-family: monospace; font-size: 10px;'>$filePath</td>\n";
            echo "</tr>\n";
        }
        echo "</table>\n";
        
        // display_order로 정렬 후 순서
        echo "<h2>display_order 기준 정렬 후 순서</h2>\n";
        $sortedImages = $images;
        usort($sortedImages, function($a, $b) {
            $orderA = $a['display_order'] ?? 999;
            $orderB = $b['display_order'] ?? 999;
            return $orderA - $orderB;
        });
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>\n";
        echo "<tr style='background: #e6f3ff;'>\n";
        echo "<th>표시 순서</th><th>display_order</th><th>파일명</th><th>원래 temp_index</th>\n";
        echo "</tr>\n";
        
        foreach ($sortedImages as $idx => $img) {
            $displayOrder = $img['display_order'] ?? '❌ 없음';
            $fileName = $img['file_name'] ?? '❌ 없음';
            $tempIndex = $img['temp_index'] ?? '❌ 없음';
            
            echo "<tr>\n";
            echo "<td style='text-align: center; font-weight: bold; color: green;'>" . ($idx + 1) . "번째</td>\n";
            echo "<td style='text-align: center; font-weight: bold; color: blue;'>$displayOrder</td>\n";
            echo "<td style='font-family: monospace; font-size: 11px;'>$fileName</td>\n";
            echo "<td style='text-align: center; color: red;'>$tempIndex</td>\n";
            echo "</tr>\n";
        }
        echo "</table>\n";
        
        echo "<h2>드래그&드롭 순서 분석</h2>\n";
        echo "<p><strong>콘솔에서 확인된 설정 순서:</strong></p>\n";
        echo "<ul>\n";
        echo "<li>1순위: temp_index 1 → display_order 1</li>\n";
        echo "<li>2순위: temp_index 0 → display_order 2</li>\n";
        echo "<li>3순위: temp_index 2 → display_order 3</li>\n";
        echo "</ul>\n";
        
        echo "<p><strong>실제 저장된 데이터:</strong></p>\n";
        foreach ($images as $idx => $img) {
            $displayOrder = $img['display_order'] ?? '없음';
            $tempIndex = $img['temp_index'] ?? '없음';
            echo "<li>배열[$idx]: temp_index $tempIndex → display_order $displayOrder</li>\n";
        }
        
    } else {
        echo "<p style='color: red;'>JSON 파싱 실패</p>\n";
    }
    
} catch (Exception $e) {
    echo "<h2>오류 발생</h2>\n";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>\n";
}
?>