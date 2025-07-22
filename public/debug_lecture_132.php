<?php
// 132번 강의 디버깅 스크립트
require_once '../src/config/config.php';
require_once '../src/config/database.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== 132번 강의 강사 이미지 디버깅 ===\n\n";

try {
    $db = Database::getInstance();
    
    // 1. 132번 강의 정보 조회
    echo "1. 132번 강의 정보 조회:\n";
    $stmt = $db->prepare("SELECT id, title, instructor_name, instructor_info, instructors_json FROM lectures WHERE id = ?");
    $stmt->execute([132]);
    $lecture = $stmt->fetch();
    
    if ($lecture) {
        echo "✅ 강의 찾음:\n";
        echo "- ID: " . $lecture['id'] . "\n";
        echo "- 제목: " . ($lecture['title'] ?? 'NULL') . "\n";
        echo "- 강사명: " . ($lecture['instructor_name'] ?? 'NULL') . "\n";
        echo "- 강사정보: " . ($lecture['instructor_info'] ?? 'NULL') . "\n";
        echo "- 강사JSON: " . ($lecture['instructors_json'] ?? 'NULL') . "\n\n";
        
        // 2. JSON 데이터 파싱
        echo "2. JSON 데이터 파싱:\n";
        if (!empty($lecture['instructors_json'])) {
            $instructors = json_decode($lecture['instructors_json'], true);
            $jsonError = json_last_error();
            
            if ($jsonError === JSON_ERROR_NONE) {
                echo "✅ JSON 파싱 성공:\n";
                foreach ($instructors as $index => $instructor) {
                    echo "강사 {$index}:\n";
                    echo "  - 이름: " . ($instructor['name'] ?? 'NULL') . "\n";
                    echo "  - 이미지: " . ($instructor['image'] ?? 'NULL') . "\n";
                    echo "  - 정보: " . ($instructor['info'] ?? 'NULL') . "\n";
                    echo "  - 직책: " . ($instructor['title'] ?? 'NULL') . "\n";
                    
                    // 3. 이미지 파일 실제 존재 확인
                    if (!empty($instructor['image'])) {
                        $fullPath = '/workspace/public' . $instructor['image'];
                        $exists = file_exists($fullPath);
                        echo "  - 이미지 파일 존재: " . ($exists ? '✅ YES' : '❌ NO') . "\n";
                        if ($exists) {
                            echo "  - 파일 크기: " . filesize($fullPath) . " bytes\n";
                        } else {
                            echo "  - 파일 경로: " . $fullPath . "\n";
                        }
                    }
                    echo "\n";
                }
            } else {
                echo "❌ JSON 파싱 실패: " . json_last_error_msg() . "\n";
            }
        } else {
            echo "❌ instructors_json 데이터 없음\n";
        }
        
        // 4. 강사 이미지 디렉토리 확인
        echo "3. 강사 이미지 디렉토리 확인:\n";
        $instructorDir = '/workspace/public/assets/uploads/instructors/';
        if (is_dir($instructorDir)) {
            $files = scandir($instructorDir);
            $imageFiles = array_filter($files, function($file) {
                return !in_array($file, ['.', '..']) && 
                       preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file);
            });
            
            echo "전체 강사 이미지 파일 수: " . count($imageFiles) . "\n";
            
            // 132번 강의와 관련된 이미지 파일 찾기
            $lecture132Files = array_filter($imageFiles, function($file) {
                return strpos($file, '132') !== false || 
                       strpos($file, '1750') !== false; // 타임스탬프 기반
            });
            
            if (count($lecture132Files) > 0) {
                echo "132번 강의 관련 이미지 파일:\n";
                foreach ($lecture132Files as $file) {
                    echo "  - " . $file . "\n";
                }
            } else {
                echo "132번 강의 관련 이미지 파일 없음\n";
            }
        } else {
            echo "❌ 강사 이미지 디렉토리 없음\n";
        }
        
    } else {
        echo "❌ 132번 강의를 찾을 수 없음\n";
        
        // 비슷한 ID 강의들 확인
        $stmt = $db->prepare("SELECT id, title FROM lectures WHERE id BETWEEN 130 AND 135 ORDER BY id");
        $stmt->execute();
        $nearbyLectures = $stmt->fetchAll();
        
        if ($nearbyLectures) {
            echo "\n130-135번 사이의 강의들:\n";
            foreach ($nearbyLectures as $lecture) {
                echo "- ID: " . $lecture['id'] . " | " . $lecture['title'] . "\n";
            }
        }
    }
    
    echo "\n=== 해결 방안 제안 ===\n";
    echo "1. 강의 상세 페이지에 디버깅 정보 추가\n";
    echo "2. 이미지 fallback 처리 개선\n";
    echo "3. 기본 강사 이미지 연결\n";
    echo "4. JSON 데이터 검증 로직 추가\n";
    
} catch (Exception $e) {
    echo "❌ 오류 발생: " . $e->getMessage() . "\n";
    echo "스택 트레이스:\n" . $e->getTraceAsString() . "\n";
}
?>