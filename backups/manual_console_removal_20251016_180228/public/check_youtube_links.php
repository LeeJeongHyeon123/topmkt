<?php
/**
 * 유튜브 링크 및 상세 정보 확인
 */

session_start();

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 설정 파일 로드
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';

echo "<h1>행사 189 상세 정보 확인</h1>";

try {
    $db = Database::getInstance();
    
    // 1. 행사 189의 기본 정보 조회
    $event = $db->fetch("SELECT * FROM lectures WHERE id = 189 AND content_type = 'event'");
    
    echo "<h2>📋 행사 189 기본 정보</h2>";
    
    if ($event) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>필드</th><th>값</th></tr>";
        
        // 주요 필드들 확인
        $fields = [
            'id' => 'ID',
            'title' => '제목',
            'description' => '설명',
            'start_date' => '시작일',
            'end_date' => '종료일',
            'location_type' => '위치 타입',
            'venue_name' => '장소명',
            'venue_address' => '주소',
            'online_link' => '온라인 링크',
            'youtube_link' => '유튜브 링크',
            'max_participants' => '최대 참가자',
            'registration_fee' => '참가비',
            'category' => '카테고리',
            'status' => '상태'
        ];
        
        foreach ($fields as $field => $label) {
            $value = $event[$field] ?? '';
            echo "<tr>";
            echo "<td><strong>" . $label . "</strong></td>";
            echo "<td>" . ($value ? htmlspecialchars($value) : "<span style='color: #999;'>없음</span>") . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // 2. 강사 정보 상세 확인
        echo "<h2>👨‍🏫 강사 정보 상세</h2>";
        $instructors = $db->fetchAll("SELECT * FROM lecture_instructors WHERE lecture_id = 189");
        
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
                echo "<td>" . htmlspecialchars($instructor['instructor_info']) . "</td>";
                echo "<td>" . ($imagePath ? htmlspecialchars($imagePath) : "<span style='color: #999;'>없음</span>") . "</td>";
                echo "<td>" . ($exists ? "✅" : "❌") . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>강사 정보가 없습니다.</p>";
        }
        
        // 3. 데이터베이스 스키마 확인
        echo "<h2>🗃️ lectures 테이블 구조</h2>";
        $columns = $db->fetchAll("DESCRIBE lectures");
        
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
        
        // 4. 실제 강사 이미지 파일 확인
        echo "<h2>📁 실제 강사 이미지 파일 확인</h2>";
        $instructorDir = ROOT_PATH . '/public/assets/uploads/instructors/';
        
        if (is_dir($instructorDir)) {
            $files = scandir($instructorDir);
            $imageFiles = array_filter($files, function($file) {
                return preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file);
            });
            
            echo "<p>강사 이미지 디렉토리: " . $instructorDir . "</p>";
            echo "<p>총 이미지 파일 수: " . count($imageFiles) . "개</p>";
            
            if (count($imageFiles) > 0) {
                echo "<ul>";
                foreach ($imageFiles as $file) {
                    $filePath = $instructorDir . $file;
                    $size = filesize($filePath);
                    echo "<li>" . htmlspecialchars($file) . " (" . number_format($size) . " bytes)</li>";
                }
                echo "</ul>";
            }
        } else {
            echo "<p style='color: red;'>강사 이미지 디렉토리가 존재하지 않습니다: " . $instructorDir . "</p>";
        }
        
        // 5. 유튜브 링크 상태 요약
        echo "<h2>📺 유튜브 링크 상태 요약</h2>";
        $youtubeLink = $event['youtube_link'] ?? '';
        $onlineLink = $event['online_link'] ?? '';
        
        if ($youtubeLink) {
            echo "<p>✅ <strong>유튜브 링크 존재:</strong> " . htmlspecialchars($youtubeLink) . "</p>";
        } else {
            echo "<p>❌ <strong>유튜브 링크 없음</strong></p>";
        }
        
        if ($onlineLink) {
            echo "<p>✅ <strong>온라인 링크 존재:</strong> " . htmlspecialchars($onlineLink) . "</p>";
        } else {
            echo "<p>❌ <strong>온라인 링크 없음</strong></p>";
        }
        
    } else {
        echo "<p style='color: red;'>행사 189를 찾을 수 없습니다.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 오류 발생: " . $e->getMessage() . "</p>";
}
?>