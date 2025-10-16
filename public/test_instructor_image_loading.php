<?php
/**
 * 강사 이미지 로딩 테스트
 */

session_start();

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 설정 파일 로드
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';

echo "<h1>강사 이미지 로딩 테스트</h1>";

// 테스트용 로그인 상태 설정
$_SESSION['user_id'] = 4;
$_SESSION['user_role'] = 'ROLE_USER';
$_SESSION['is_logged_in'] = true;

try {
    $db = Database::getInstance();
    
    // 행사 189의 강사 정보 조회
    $instructors = $db->fetchAll("SELECT * FROM lecture_instructors WHERE lecture_id = 189");
    
    echo "<h2>💻 강사 이미지 로딩 시뮬레이션</h2>";
    echo "<p>총 강사 수: " . count($instructors) . "명</p>";
    
    if (count($instructors) > 0) {
        echo "<style>";
        echo "
        .instructor-container {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }
        
        .instructor-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            width: 300px;
            background: #f9f9f9;
        }
        
        .instructor-image-container {
            width: 100px;
            height: 100px;
            border-radius: 8px;
            overflow: hidden;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            border: 2px solid #d1d5db;
            cursor: pointer;
        }
        
        .instructor-image-placeholder {
            text-align: center;
            color: #6b7280;
        }
        
        .instructor-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .instructor-details {
            margin-top: 10px;
        }
        
        .instructor-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .instructor-info {
            color: #666;
            font-size: 0.9em;
        }
        
        .status-indicator {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
            margin-top: 10px;
        }
        
        .status-success {
            background: #dcfce7;
            color: #16a34a;
        }
        
        .status-error {
            background: #fee2e2;
            color: #dc2626;
        }
        ";
        echo "</style>";
        
        echo "<div class='instructor-container'>";
        foreach ($instructors as $index => $instructor) {
            $imagePath = $instructor['instructor_image'] ?? '';
            $fullPath = $imagePath ? ROOT_PATH . '/public' . $imagePath : '';
            $exists = $imagePath && file_exists($fullPath);
            
            echo "<div class='instructor-item'>";
            echo "<div class='instructor-image-container' onclick='alert(\"이미지 업로드 기능 테스트\")'>";
            
            if ($exists) {
                echo "<img src='" . htmlspecialchars($imagePath) . "' alt='강사 이미지' onload='' onerror=''>";
            } else {
                echo "<div class='instructor-image-placeholder'>";
                echo "<div style='font-size: 2rem; margin-bottom: 5px;'>👤</div>";
                echo "<div style='font-size: 0.8rem;'>이미지 선택</div>";
                echo "</div>";
            }
            
            echo "</div>";
            
            echo "<div class='instructor-details'>";
            echo "<div class='instructor-name'>" . htmlspecialchars($instructor['instructor_name']) . "</div>";
            echo "<div class='instructor-info'>" . htmlspecialchars($instructor['instructor_info']) . "</div>";
            echo "</div>";
            
            echo "<div class='status-indicator " . ($exists ? 'status-success' : 'status-error') . "'>";
            echo $exists ? "✅ 이미지 로드 성공" : "❌ 이미지 파일 없음";
            echo "</div>";
            
            if ($imagePath) {
                echo "<div style='margin-top: 10px; font-size: 0.8em; color: #666;'>";
                echo "경로: " . htmlspecialchars($imagePath);
                echo "</div>";
            }
            
            echo "</div>";
        }
        echo "</div>";
        
        echo "<h3>🔧 JavaScript 수정 사항</h3>";
        echo "<div style='background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
        echo "<h4>수정된 강사 이미지 로드 코드:</h4>";
        echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto;'>";
        echo htmlspecialchars("// 강사 이미지 로드
const instructorImage = instructor.instructor_image || instructor.image;
if (instructorImage) {
    const imageContainer = instructorContainer.querySelector('.instructor-image-container');
    if (imageContainer) {
        imageContainer.innerHTML = `<img src=\"\${instructorImage}\" alt=\"강사 이미지\" style=\"width: 100%; height: 100%; object-fit: cover; border-radius: 8px;\">`;
    }
}");
        echo "</pre>";
        echo "</div>";
        
        echo "<h3>🎯 테스트 결과</h3>";
        $successCount = 0;
        foreach ($instructors as $instructor) {
            $imagePath = $instructor['instructor_image'] ?? '';
            $fullPath = $imagePath ? ROOT_PATH . '/public' . $imagePath : '';
            if ($imagePath && file_exists($fullPath)) {
                $successCount++;
            }
        }
        
        echo "<ul>";
        echo "<li>총 강사 수: " . count($instructors) . "명</li>";
        echo "<li>이미지 로드 성공: " . $successCount . "명</li>";
        echo "<li>이미지 없음: " . (count($instructors) - $successCount) . "명</li>";
        echo "</ul>";
        
        if ($successCount == 0) {
            echo "<div style='background: #fef3c7; border: 1px solid #f59e0b; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
            echo "<h4>⚠️ 주의사항</h4>";
            echo "<p>모든 강사 이미지가 로드되지 않았습니다. 이는 정상적인 현상일 수 있습니다:</p>";
            echo "<ul>";
            echo "<li>강사 이미지가 실제로 등록되지 않은 경우</li>";
            echo "<li>이미지 파일이 서버에서 삭제된 경우</li>";
            echo "<li>이미지 경로가 변경된 경우</li>";
            echo "</ul>";
            echo "<p>이 경우 플레이스홀더가 표시되는 것이 정상입니다.</p>";
            echo "</div>";
        }
        
    } else {
        echo "<p>강사 정보가 없습니다.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 오류 발생: " . $e->getMessage() . "</p>";
}
?>