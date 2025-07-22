<?php
/**
 * 강의 143번 이미지 순서 확인
 */

define('SRC_PATH', '/workspace/src');
require_once SRC_PATH . '/config/database.php';

try {
    $db = Database::getInstance();
    
    // 강의 143번 데이터 조회
    $stmt = $db->prepare("SELECT id, title, lecture_images FROM lectures WHERE id = ?");
    $stmt->bind_param("i", $lectureId = 143);
    $stmt->execute();
    $result = $stmt->get_result();
    $lecture = $result->fetch_assoc();
    
    echo "<h1>강의 143번 이미지 순서 DB 확인</h1>\n";
    echo "<p><strong>강의 ID:</strong> " . $lecture['id'] . "</p>\n";
    echo "<p><strong>제목:</strong> " . $lecture['title'] . "</p>\n";
    
    // JSON 파싱
    $images = json_decode($lecture['lecture_images'], true);
    
    echo "<h2>1. 데이터베이스에 저장된 원본 데이터</h2>\n";
    echo "<pre>" . htmlspecialchars($lecture['lecture_images']) . "</pre>\n";
    
    if (is_array($images)) {
        echo "<h2>2. 파싱된 이미지 데이터</h2>\n";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
        echo "<tr style='background: #f0f0f0;'>\n";
        echo "<th>배열 인덱스</th><th>display_order</th><th>파일명</th><th>temp_index</th>\n";
        echo "</tr>\n";
        
        foreach ($images as $idx => $img) {
            $displayOrder = $img['display_order'] ?? '없음';
            $fileName = $img['file_name'] ?? '없음';
            $tempIndex = $img['temp_index'] ?? '없음';
            
            echo "<tr>\n";
            echo "<td style='text-align: center;'>$idx</td>\n";
            echo "<td style='text-align: center; font-weight: bold; color: blue;'>$displayOrder</td>\n";
            echo "<td style='font-family: monospace;'>$fileName</td>\n";
            echo "<td style='text-align: center; color: red;'>$tempIndex</td>\n";
            echo "</tr>\n";
        }
        echo "</table>\n";
        
        // display_order로 정렬 테스트
        echo "<h2>3. display_order로 정렬했을 때 순서</h2>\n";
        $sortedImages = $images;
        usort($sortedImages, function($a, $b) {
            $orderA = $a['display_order'] ?? 999;
            $orderB = $b['display_order'] ?? 999;
            return $orderA - $orderB;
        });
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
        echo "<tr style='background: #e6f3ff;'>\n";
        echo "<th>정렬 후 순서</th><th>display_order</th><th>파일명</th><th>원래 temp_index</th>\n";
        echo "</tr>\n";
        
        foreach ($sortedImages as $idx => $img) {
            $displayOrder = $img['display_order'] ?? '없음';
            $fileName = $img['file_name'] ?? '없음';
            $tempIndex = $img['temp_index'] ?? '없음';
            
            echo "<tr>\n";
            echo "<td style='text-align: center; font-weight: bold;'>" . ($idx + 1) . "번째</td>\n";
            echo "<td style='text-align: center; font-weight: bold; color: blue;'>$displayOrder</td>\n";
            echo "<td style='font-family: monospace;'>$fileName</td>\n";
            echo "<td style='text-align: center; color: red;'>$tempIndex</td>\n";
            echo "</tr>\n";
        }
        echo "</table>\n";
        
        echo "<h2>4. 결론</h2>\n";
        echo "<p><strong>예상 순서 (드래그&드롭 기준):</strong></p>\n";
        echo "<ol>\n";
        echo "<li>1순위: temp_index 1 (display_order 1)</li>\n";
        echo "<li>2순위: temp_index 0 (display_order 2)</li>\n";
        echo "<li>3순위: temp_index 2 (display_order 3)</li>\n";
        echo "</ol>\n";
        
        // display_order 필드 존재 여부 확인
        $hasDisplayOrder = true;
        foreach ($images as $img) {
            if (!isset($img['display_order'])) {
                $hasDisplayOrder = false;
                break;
            }
        }
        
        if ($hasDisplayOrder) {
            echo "<p style='color: green;'>✅ 모든 이미지에 display_order 필드가 있습니다.</p>\n";
        } else {
            echo "<p style='color: red;'>❌ 일부 이미지에 display_order 필드가 없습니다!</p>\n";
        }
        
    } else {
        echo "<p style='color: red;'>JSON 파싱 실패</p>\n";
    }
    
} catch (Exception $e) {
    echo "<h2>오류 발생</h2>\n";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>\n";
}
?>