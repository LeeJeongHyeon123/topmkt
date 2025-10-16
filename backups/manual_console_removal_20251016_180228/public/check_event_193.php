<?php
/**
 * 행사 193 상태 확인
 */

session_start();

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 설정 파일 로드
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';

echo "<h1>행사 193 상태 확인</h1>";

// 테스트용 로그인 상태 설정
$_SESSION['user_id'] = 4;
$_SESSION['user_role'] = 'ROLE_USER';
$_SESSION['is_logged_in'] = true;

try {
    $db = Database::getInstance();
    $eventId = 193;
    
    // 1. 행사 기본 정보 확인
    echo "<h2>📋 행사 193 기본 정보</h2>";
    $event = $db->fetch("SELECT * FROM lectures WHERE id = ? AND content_type = 'event'", [$eventId]);
    
    if ($event) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>필드</th><th>값</th></tr>";
        
        $fields = [
            'id' => 'ID',
            'title' => '제목',
            'description' => '설명',
            'start_date' => '시작일',
            'end_date' => '종료일',
            'location_type' => '위치 타입',
            'venue_name' => '장소명',
            'max_participants' => '최대 참가자',
            'registration_fee' => '참가비',
            'status' => '상태',
            'created_at' => '생성일',
            'updated_at' => '수정일'
        ];
        
        foreach ($fields as $field => $label) {
            $value = $event[$field] ?? '';
            echo "<tr>";
            echo "<td><strong>" . $label . "</strong></td>";
            echo "<td>" . ($value ? htmlspecialchars($value) : "<span style='color: #999;'>없음</span>") . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>행사 193을 찾을 수 없습니다.</p>";
        exit;
    }
    
    // 2. 행사 이미지 확인
    echo "<h2>🖼️ 행사 이미지 상태</h2>";
    $eventImages = $db->fetchAll("SELECT * FROM event_images WHERE event_id = ? ORDER BY id", [$eventId]);
    echo "<p>총 " . count($eventImages) . "개의 이미지</p>";
    
    if (count($eventImages) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>이미지 경로</th><th>파일 존재</th><th>파일 크기</th></tr>";
        
        foreach ($eventImages as $image) {
            $fullPath = ROOT_PATH . '/public' . $image['image_path'];
            $exists = file_exists($fullPath);
            $size = $exists ? filesize($fullPath) : 0;
            
            echo "<tr>";
            echo "<td>" . $image['id'] . "</td>";
            echo "<td>" . htmlspecialchars($image['image_path']) . "</td>";
            echo "<td>" . ($exists ? "✅ 존재" : "❌ 없음") . "</td>";
            echo "<td>" . ($exists ? number_format($size) . " bytes" : "N/A") . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // 이미지 미리보기
        echo "<h3>이미지 미리보기</h3>";
        echo "<div style='display: flex; flex-wrap: wrap; gap: 10px;'>";
        foreach ($eventImages as $image) {
            $fullPath = ROOT_PATH . '/public' . $image['image_path'];
            if (file_exists($fullPath)) {
                echo "<div style='border: 1px solid #ddd; padding: 5px;'>";
                echo "<img src='" . htmlspecialchars($image['image_path']) . "' style='width: 150px; height: 100px; object-fit: cover;'>";
                echo "<div style='font-size: 0.8em; text-align: center;'>ID: " . $image['id'] . "</div>";
                echo "</div>";
            }
        }
        echo "</div>";
    } else {
        echo "<p style='color: orange;'>행사 이미지가 없습니다.</p>";
    }
    
    // 3. 강사 정보 확인
    echo "<h2>👨‍🏫 강사 정보 상태</h2>";
    $instructors = $db->fetchAll("SELECT * FROM lecture_instructors WHERE lecture_id = ? ORDER BY id", [$eventId]);
    echo "<p>총 " . count($instructors) . "명의 강사</p>";
    
    if (count($instructors) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>강사명</th><th>강사 소개</th><th>이미지 경로</th><th>파일 존재</th></tr>";
        
        foreach ($instructors as $instructor) {
            $imagePath = $instructor['instructor_image'] ?? '';
            $fullPath = $imagePath ? ROOT_PATH . '/public' . $imagePath : '';
            $exists = $imagePath && file_exists($fullPath);
            
            echo "<tr>";
            echo "<td>" . $instructor['id'] . "</td>";
            echo "<td>" . htmlspecialchars($instructor['instructor_name']) . "</td>";
            echo "<td>" . htmlspecialchars(substr($instructor['instructor_info'], 0, 100)) . "...</td>";
            echo "<td>" . ($imagePath ? htmlspecialchars($imagePath) : "<span style='color: #999;'>없음</span>") . "</td>";
            echo "<td>" . ($exists ? "✅ 존재" : "❌ 없음") . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // 강사 이미지 미리보기
        echo "<h3>강사 이미지 미리보기</h3>";
        echo "<div style='display: flex; flex-wrap: wrap; gap: 10px;'>";
        foreach ($instructors as $instructor) {
            $imagePath = $instructor['instructor_image'] ?? '';
            $fullPath = $imagePath ? ROOT_PATH . '/public' . $imagePath : '';
            
            echo "<div style='border: 1px solid #ddd; padding: 10px; text-align: center;'>";
            if ($imagePath && file_exists($fullPath)) {
                echo "<img src='" . htmlspecialchars($imagePath) . "' style='width: 80px; height: 80px; object-fit: cover; border-radius: 50%;'>";
            } else {
                echo "<div style='width: 80px; height: 80px; background: #f0f0f0; border-radius: 50%; display: flex; align-items: center; justify-content: center;'>👤</div>";
            }
            echo "<div style='font-size: 0.9em; margin-top: 5px;'>" . htmlspecialchars($instructor['instructor_name']) . "</div>";
            echo "</div>";
        }
        echo "</div>";
    } else {
        echo "<p style='color: orange;'>강사 정보가 없습니다.</p>";
    }
    
    // 4. 로그 확인
    echo "<h2>📝 최근 로그 확인</h2>";
    $logFiles = [
        '/var/log/httpd/error_log',
        '/var/log/apache2/error.log',
        '/var/log/php_errors.log'
    ];
    
    foreach ($logFiles as $logFile) {
        if (file_exists($logFile)) {
            echo "<h3>로그 파일: " . $logFile . "</h3>";
            $logContent = shell_exec("tail -20 " . $logFile . " | grep -i 'event\|image\|instructor' || echo '관련 로그 없음'");
            echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 4px; max-height: 200px; overflow-y: auto;'>";
            echo htmlspecialchars($logContent);
            echo "</pre>";
            break;
        }
    }
    
    // 5. 요약
    echo "<h2>📊 요약</h2>";
    echo "<div style='background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<ul>";
    echo "<li>행사 ID: " . $eventId . "</li>";
    echo "<li>행사 제목: " . htmlspecialchars($event['title']) . "</li>";
    echo "<li>행사 이미지: " . count($eventImages) . "개</li>";
    echo "<li>강사 수: " . count($instructors) . "명</li>";
    echo "<li>강사 이미지: " . count(array_filter($instructors, function($i) { return !empty($i['instructor_image']); })) . "개</li>";
    echo "</ul>";
    echo "</div>";
    
    if (count($eventImages) > 0 || count($instructors) > 0) {
        echo "<div style='background: #fef3c7; border: 1px solid #f59e0b; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
        echo "<h3>⚠️ 테스트 준비</h3>";
        echo "<p>수정 테스트를 진행하기 전에 현재 상태를 기록해두세요:</p>";
        echo "<ul>";
        echo "<li>현재 이미지 개수를 확인했습니다</li>";
        echo "<li>수정 후 이미지가 사라지는지 확인하세요</li>";
        echo "<li>문제가 발생하면 즉시 보고해주세요</li>";
        echo "</ul>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 오류 발생: " . $e->getMessage() . "</p>";
}
?>