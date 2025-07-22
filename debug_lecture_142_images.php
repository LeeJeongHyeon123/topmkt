<?php
/**
 * 강의 142번 이미지 순서 디버깅
 */

define('SRC_PATH', '/workspace/src');
require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/models/User.php';
require_once SRC_PATH . '/helpers/ResponseHelper.php';
require_once SRC_PATH . '/helpers/ValidationHelper.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
require_once SRC_PATH . '/controllers/LectureController.php';

try {
    $controller = new LectureController();
    
    // 강의 142번 데이터 조회
    $lectureId = 142;
    
    echo "<h1>강의 142번 이미지 순서 디버깅</h1>";
    
    // 1. 데이터베이스에서 원본 데이터 확인
    $db = Database::getInstance();
    $stmt = $db->prepare("SELECT id, title, lecture_images FROM lectures WHERE id = ?");
    $stmt->bind_param("i", $lectureId);
    $stmt->execute();
    $result = $stmt->get_result();
    $rawData = $result->fetch_assoc();
    
    echo "<h2>1. 데이터베이스 원본 데이터</h2>";
    echo "<p><strong>강의 ID:</strong> " . $rawData['id'] . "</p>";
    echo "<p><strong>제목:</strong> " . $rawData['title'] . "</p>";
    echo "<p><strong>lecture_images JSON:</strong></p>";
    echo "<pre>" . htmlspecialchars($rawData['lecture_images']) . "</pre>";
    
    // 2. JSON 파싱 결과
    $imageData = json_decode($rawData['lecture_images'], true);
    echo "<h2>2. JSON 파싱 결과</h2>";
    if (is_array($imageData)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Index</th><th>File Name</th><th>Display Order</th><th>File Path</th><th>File Size</th></tr>";
        foreach ($imageData as $idx => $img) {
            echo "<tr>";
            echo "<td>$idx</td>";
            echo "<td>" . ($img['file_name'] ?? 'MISSING') . "</td>";
            echo "<td>" . ($img['display_order'] ?? 'MISSING') . "</td>";
            echo "<td>" . ($img['file_path'] ?? 'MISSING') . "</td>";
            echo "<td>" . ($img['file_size'] ?? 'MISSING') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>JSON 파싱 실패</p>";
    }
    
    // 3. LectureController의 getLectureImages 메서드 결과
    echo "<h2>3. LectureController->getLectureImages() 결과</h2>";
    
    // getLectureImages 메서드를 직접 호출하기 위해 reflection 사용
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('getLectureImages');
    $method->setAccessible(true);
    
    $processedImages = $method->invoke($controller, $rawData['lecture_images']);
    
    if (is_array($processedImages)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Index</th><th>URL</th><th>Display Order</th><th>File Name</th></tr>";
        foreach ($processedImages as $idx => $img) {
            echo "<tr>";
            echo "<td>$idx</td>";
            echo "<td>" . ($img['url'] ?? 'MISSING') . "</td>";
            echo "<td>" . ($img['display_order'] ?? 'MISSING') . "</td>";
            echo "<td>" . ($img['file_name'] ?? 'MISSING') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>getLectureImages 호출 실패</p>";
    }
    
    // 4. 파일 존재 여부 확인
    echo "<h2>4. 실제 파일 존재 여부</h2>";
    if (is_array($imageData)) {
        foreach ($imageData as $idx => $img) {
            $fileName = $img['file_name'] ?? '';
            $filePath = "/workspace/public/assets/uploads/lectures/$fileName";
            $exists = file_exists($filePath);
            $color = $exists ? 'green' : 'red';
            $status = $exists ? '✅ 존재' : '❌ 없음';
            echo "<p style='color: $color;'><strong>$fileName:</strong> $status</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<h2>오류 발생</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>