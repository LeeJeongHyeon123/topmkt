<?php
/**
 * 강사 이미지 등록 상태 확인
 */

session_start();

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 설정 파일 로드
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';

echo "<h1>강사 이미지 등록 상태 확인</h1>";

try {
    $db = Database::getInstance();
    
    // 1. 행사 189의 강사 정보 조회
    $instructors = $db->fetchAll("SELECT * FROM lecture_instructors WHERE lecture_id = 189");
    
    echo "<h2>📋 행사 189 강사 정보</h2>";
    echo "<p>총 강사 수: " . count($instructors) . "명</p>";
    
    if (count($instructors) > 0) {
        echo "<h3>👨‍🏫 강사 상세 정보</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>강사명</th><th>강사 소개</th><th>이미지 경로</th><th>파일 존재</th></tr>";
        
        foreach ($instructors as $instructor) {
            $imagePath = $instructor['instructor_image'] ?? '';
            $fullPath = $imagePath ? ROOT_PATH . '/public' . $imagePath : '';
            $exists = $imagePath && file_exists($fullPath);
            
            echo "<tr>";
            echo "<td>" . $instructor['id'] . "</td>";
            echo "<td>" . htmlspecialchars($instructor['instructor_name']) . "</td>";
            echo "<td>" . htmlspecialchars(substr($instructor['instructor_info'], 0, 50)) . "...</td>";
            echo "<td>" . ($imagePath ? htmlspecialchars($imagePath) : "없음") . "</td>";
            echo "<td>" . ($exists ? "✅" : "❌") . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // 2. 강사 이미지 시각적 확인
        echo "<h3>🖼️ 강사 이미지 미리보기</h3>";
        echo "<style>";
        echo "
        .instructor-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 15px;
        }
        
        .instructor-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            width: 250px;
            background: #f9f9f9;
        }
        
        .instructor-image {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ddd;
            margin-bottom: 10px;
        }
        
        .instructor-placeholder {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: #6b7280;
            margin-bottom: 10px;
        }
        
        .instructor-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .instructor-info {
            font-size: 0.9em;
            color: #666;
            line-height: 1.4;
        }
        ";
        echo "</style>";
        
        echo "<div class='instructor-preview'>";
        foreach ($instructors as $instructor) {
            $imagePath = $instructor['instructor_image'] ?? '';
            $fullPath = $imagePath ? ROOT_PATH . '/public' . $imagePath : '';
            $exists = $imagePath && file_exists($fullPath);
            
            echo "<div class='instructor-card'>";
            
            if ($exists) {
                echo "<img src='" . htmlspecialchars($imagePath) . "' alt='강사 이미지' class='instructor-image'>";
            } else {
                echo "<div class='instructor-placeholder'>👤</div>";
            }
            
            echo "<div class='instructor-name'>" . htmlspecialchars($instructor['instructor_name']) . "</div>";
            echo "<div class='instructor-info'>" . htmlspecialchars($instructor['instructor_info']) . "</div>";
            echo "</div>";
        }
        echo "</div>";
        
    } else {
        echo "<p>강사 정보가 없습니다.</p>";
    }
    
    // 3. 강사 이미지 업로드 디렉토리 확인
    echo "<h2>📁 강사 이미지 업로드 디렉토리 확인</h2>";
    
    $instructorImageDirs = [
        '/assets/uploads/instructors/',
        '/uploads/instructors/',
        '/assets/uploads/events/',
        '/uploads/events/'
    ];
    
    foreach ($instructorImageDirs as $dir) {
        $fullDirPath = ROOT_PATH . '/public' . $dir;
        $exists = is_dir($fullDirPath);
        echo "<p><strong>" . $dir . "</strong>: " . ($exists ? "✅ 존재" : "❌ 없음") . "</p>";
        
        if ($exists) {
            $files = scandir($fullDirPath);
            $imageFiles = array_filter($files, function($file) {
                return preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file) && strpos($file, 'instructor') !== false;
            });
            
            if (count($imageFiles) > 0) {
                echo "<ul>";
                foreach ($imageFiles as $file) {
                    echo "<li>" . htmlspecialchars($file) . "</li>";
                }
                echo "</ul>";
            } else {
                echo "<p style='margin-left: 20px; color: #666;'>강사 이미지 파일 없음</p>";
            }
        }
    }
    
    // 4. 데이터베이스 테이블 구조 확인
    echo "<h2>🗃️ 데이터베이스 테이블 구조 확인</h2>";
    
    $columns = $db->fetchAll("DESCRIBE lecture_instructors");
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>컬럼명</th><th>타입</th><th>NULL 허용</th><th>기본값</th></tr>";
    
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 오류 발생: " . $e->getMessage() . "</p>";
}
?>