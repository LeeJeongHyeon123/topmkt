<?php
/**
 * 모든 강의의 한글 파일명 문제를 일괄 수정하는 스크립트
 */

try {
    $mysqli = new mysqli('localhost', 'root', 'Dnlszkem1!', 'TOPMKT', 3306, '/var/lib/mysql/mysql.sock');
    
    if ($mysqli->connect_error) {
        throw new Exception('연결 실패: ' . $mysqli->connect_error);
    }
    
    $mysqli->set_charset('utf8mb4');
    $mysqli->query("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    echo "<h1>한글 파일명 문제 일괄 수정</h1>";
    
    // 문제가 있는 강의들 조회 (물음표가 포함된 lecture_images)
    $result = $mysqli->query("
        SELECT id, created_at, lecture_images 
        FROM lectures 
        WHERE lecture_images LIKE '%?%' 
        ORDER BY id DESC 
        LIMIT 20
    ");
    
    $uploadDir = '/workspace/public/assets/uploads/lectures/';
    $fixedCount = 0;
    
    while ($row = $result->fetch_assoc()) {
        $lectureId = $row['id'];
        $createdAt = $row['created_at'];
        $lectureImages = $row['lecture_images'];
        
        echo "<h2>강의 {$lectureId}번 수정 중...</h2>";
        echo "<p>생성일: {$createdAt}</p>";
        
        // 생성 시간을 타임스탬프로 변환
        $timestamp = strtotime($createdAt);
        
        // 해당 시간대에 업로드된 파일들 찾기
        $pattern = $timestamp . '_*';
        $matchingFiles = glob($uploadDir . $pattern);
        
        if (empty($matchingFiles)) {
            echo "<p style='color: orange;'>❌ 매칭되는 파일 없음</p>";
            continue;
        }
        
        echo "<p>발견된 파일들:</p><ul>";
        foreach ($matchingFiles as $file) {
            echo "<li>" . basename($file) . "</li>";
        }
        echo "</ul>";
        
        // JSON 디코드
        $images = json_decode($lectureImages, true);
        if (!is_array($images)) {
            echo "<p style='color: red;'>❌ JSON 파싱 실패</p>";
            continue;
        }
        
        // 새로운 이미지 데이터 생성
        $newImages = [];
        foreach ($images as $index => $image) {
            if (isset($matchingFiles[$index])) {
                $fileName = basename($matchingFiles[$index]);
                $newImages[] = [
                    'original_name' => $fileName,
                    'file_name' => $fileName,
                    'file_path' => '/assets/uploads/lectures/' . $fileName,
                    'file_size' => $image['file_size'] ?? filesize($matchingFiles[$index]),
                    'display_order' => $index + 1
                ];
            }
        }
        
        if (!empty($newImages)) {
            // 데이터베이스 업데이트
            $newImagesJson = json_encode($newImages, JSON_UNESCAPED_UNICODE);
            $escapedJson = $mysqli->real_escape_string($newImagesJson);
            
            $updateQuery = "UPDATE lectures SET lecture_images = '{$escapedJson}' WHERE id = {$lectureId}";
            
            if ($mysqli->query($updateQuery)) {
                echo "<p style='color: green;'>✅ 성공적으로 수정됨</p>";
                $fixedCount++;
                
                echo "<p>수정된 이미지들:</p><ul>";
                foreach ($newImages as $img) {
                    echo "<li>{$img['file_name']} -> {$img['file_path']}</li>";
                }
                echo "</ul>";
            } else {
                echo "<p style='color: red;'>❌ 데이터베이스 업데이트 실패: " . $mysqli->error . "</p>";
            }
        }
        
        echo "<hr>";
    }
    
    echo "<h2>작업 완료</h2>";
    echo "<p><strong>{$fixedCount}개 강의</strong>의 이미지 경로가 수정되었습니다.</p>";
    
} catch (Exception $e) {
    echo "<h2>오류</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}
?>