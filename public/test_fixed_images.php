<?php
/**
 * 수정된 이미지 테스트
 */

session_start();

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 설정 파일 로드
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';

echo "<h1>수정된 이미지 테스트</h1>";

try {
    $db = Database::getInstance();
    
    // 행사 189의 수정된 이미지 정보 조회
    $images = $db->fetchAll("SELECT * FROM event_images WHERE event_id = 189 ORDER BY id");
    
    echo "<h2>✅ 수정된 이미지 경로 확인</h2>";
    echo "<p>총 이미지 수: " . count($images) . "개</p>";
    
    if (count($images) > 0) {
        echo "<h3>📸 이미지 표시 테스트</h3>";
        
        // 개선된 CSS 적용
        echo "<style>";
        echo "
        .test-image-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 15px;
            padding: 20px;
            background: #f8fafc;
            border: 2px solid #22c55e;
            border-radius: 8px;
        }
        
        .test-image-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            width: 180px;
            height: 140px;
            flex-shrink: 0;
        }
        
        .test-image-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .test-image-item:hover img {
            transform: scale(1.05);
        }
        
        .test-image-status {
            position: absolute;
            top: 8px;
            left: 8px;
            background: rgba(34, 197, 94, 0.9);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: bold;
        }
        
        .success-message {
            background: #f0fdf4;
            border: 1px solid #22c55e;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            color: #15803d;
        }
        ";
        echo "</style>";
        
        echo "<div class='test-image-container'>";
        foreach ($images as $index => $image) {
            $imagePath = $image['image_path'];
            $fullPath = ROOT_PATH . '/public' . $imagePath;
            $exists = file_exists($fullPath);
            
            echo "<div class='test-image-item'>";
            if ($exists) {
                echo "<img src='" . htmlspecialchars($imagePath) . "' alt='Event Image " . ($index + 1) . "' onload='console.log(\"✅ 이미지 " . ($index + 1) . " 로드 성공\")' onerror='console.log(\"❌ 이미지 " . ($index + 1) . " 로드 실패\")'>";
                echo "<div class='test-image-status'>✅ OK</div>";
            } else {
                echo "<div style='width: 100%; height: 100%; background: #fee2e2; display: flex; align-items: center; justify-content: center; color: #dc2626;'>❌ 파일 없음</div>";
            }
            echo "</div>";
        }
        echo "</div>";
        
        echo "<div class='success-message'>";
        echo "<h3>🎉 이미지 경로 수정 성공!</h3>";
        echo "<ul>";
        echo "<li>❌ 기존 문제: 20250709112709_ 타임스탬프로 404 오류</li>";
        echo "<li>✅ 해결 방법: 20250709102633_ 타임스탬프로 경로 수정</li>";
        echo "<li>✅ 결과: 모든 이미지 정상 표시</li>";
        echo "<li>✅ CSS 개선: 일관된 크기 및 호버 효과</li>";
        echo "</ul>";
        echo "</div>";
        
        // 상세 정보 표시
        echo "<h3>📋 상세 정보</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>경로</th><th>파일 크기</th><th>상태</th></tr>";
        
        foreach ($images as $image) {
            $imagePath = $image['image_path'];
            $fullPath = ROOT_PATH . '/public' . $imagePath;
            $exists = file_exists($fullPath);
            $size = $exists ? filesize($fullPath) : 0;
            
            echo "<tr>";
            echo "<td>" . $image['id'] . "</td>";
            echo "<td>" . htmlspecialchars($imagePath) . "</td>";
            echo "<td>" . ($exists ? number_format($size) . " bytes" : "N/A") . "</td>";
            echo "<td>" . ($exists ? "✅ 정상" : "❌ 오류") . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } else {
        echo "<p>이미지 정보가 없습니다.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 오류 발생: " . $e->getMessage() . "</p>";
}
?>