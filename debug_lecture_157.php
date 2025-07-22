<?php
/**
 * Debug script for lecture ID 157 instructor image issues
 */

// Include necessary files
require_once __DIR__ . '/src/config/database.php';

try {
    echo "=== 탑마케팅 강의 157 디버깅 시작 ===\n\n";
    
    // 데이터베이스 연결
    $db = Database::getInstance();
    
    // 1. 강의 157의 기본 정보 조회
    echo "1. 강의 157 기본 정보:\n";
    $lecture = $db->fetch("SELECT * FROM lectures WHERE id = 157");
    
    if (!$lecture) {
        echo "❌ 강의 ID 157을 찾을 수 없습니다.\n";
        exit;
    }
    
    echo "   - 제목: " . $lecture['title'] . "\n";
    echo "   - 강사명: " . $lecture['instructor_name'] . "\n";
    echo "   - 강사 이미지: " . ($lecture['instructor_image'] ?? 'NULL') . "\n";
    echo "   - 생성일: " . $lecture['created_at'] . "\n";
    echo "   - 수정일: " . $lecture['updated_at'] . "\n";
    
    // 강의 테이블 구조 확인 (instructors_json 필드가 있는지 확인)
    echo "\n2. 강의 테이블 구조 확인:\n";
    $columns = $db->fetchAll("SHOW COLUMNS FROM lectures");
    $has_instructors_json = false;
    
    foreach ($columns as $column) {
        if ($column['Field'] === 'instructors_json') {
            $has_instructors_json = true;
            echo "   ✅ instructors_json 필드 발견: " . $column['Type'] . "\n";
            break;
        }
    }
    
    if (!$has_instructors_json) {
        echo "   ❌ instructors_json 필드가 존재하지 않습니다.\n";
        echo "   📋 현재 강의 테이블의 필드들:\n";
        foreach ($columns as $column) {
            if (strpos($column['Field'], 'instructor') !== false) {
                echo "      - " . $column['Field'] . " (" . $column['Type'] . ")\n";
            }
        }
    } else {
        // instructors_json 필드가 있다면 그 값 확인
        echo "\n3. instructors_json 내용 확인:\n";
        $instructors_data = $lecture['instructors_json'] ?? null;
        if ($instructors_data) {
            echo "   Raw JSON: " . $instructors_data . "\n";
            $decoded = json_decode($instructors_data, true);
            if ($decoded) {
                echo "   Decoded Data:\n";
                print_r($decoded);
            } else {
                echo "   ❌ JSON 파싱 실패\n";
            }
        } else {
            echo "   ❌ instructors_json 데이터가 없습니다.\n";
        }
    }
    
    // 4. 강사 이미지 파일 경로 확인
    echo "\n4. 강사 이미지 파일 확인:\n";
    if ($lecture['instructor_image']) {
        $image_path = '/workspace/public/assets/uploads/instructors/' . $lecture['instructor_image'];
        $web_path = '/assets/uploads/instructors/' . $lecture['instructor_image'];
        
        echo "   - 저장된 파일명: " . $lecture['instructor_image'] . "\n";
        echo "   - 실제 파일 경로: " . $image_path . "\n";
        echo "   - 웹 경로: " . $web_path . "\n";
        
        if (file_exists($image_path)) {
            echo "   ✅ 파일이 존재합니다.\n";
            echo "   - 파일 크기: " . filesize($image_path) . " bytes\n";
            echo "   - 수정 시간: " . date('Y-m-d H:i:s', filemtime($image_path)) . "\n";
        } else {
            echo "   ❌ 파일이 존재하지 않습니다.\n";
        }
    } else {
        echo "   ❌ 강사 이미지가 설정되지 않았습니다.\n";
    }
    
    // 5. 다른 강의들과 이미지 경로 비교
    echo "\n5. 최근 강의들의 강사 이미지 비교:\n";
    $recent_lectures = $db->fetchAll("
        SELECT id, title, instructor_name, instructor_image, created_at 
        FROM lectures 
        WHERE id BETWEEN 150 AND 165 
        ORDER BY id
    ");
    
    foreach ($recent_lectures as $lec) {
        $status = $lec['instructor_image'] ? '✅' : '❌';
        echo "   강의 {$lec['id']}: {$status} " . ($lec['instructor_image'] ?? 'NULL') . "\n";
    }
    
    // 6. instructors 폴더의 최근 파일들 확인
    echo "\n6. instructors 폴더의 최근 파일들:\n";
    $instructors_dir = '/workspace/public/assets/uploads/instructors/';
    if (is_dir($instructors_dir)) {
        $files = array_diff(scandir($instructors_dir), array('.', '..'));
        $recent_files = [];
        
        foreach ($files as $file) {
            $file_path = $instructors_dir . $file;
            if (is_file($file_path)) {
                $recent_files[] = [
                    'name' => $file,
                    'size' => filesize($file_path),
                    'time' => filemtime($file_path)
                ];
            }
        }
        
        // 수정 시간순으로 정렬
        usort($recent_files, function($a, $b) {
            return $b['time'] - $a['time'];
        });
        
        // 최근 10개 파일 표시
        $count = 0;
        foreach ($recent_files as $file) {
            if ($count >= 10) break;
            echo "   - " . $file['name'] . " (" . $file['size'] . " bytes, " . date('Y-m-d H:i:s', $file['time']) . ")\n";
            $count++;
        }
    } else {
        echo "   ❌ instructors 폴더가 존재하지 않습니다.\n";
    }
    
    echo "\n=== 디버깅 완료 ===\n";
    
} catch (Exception $e) {
    echo "❌ 오류 발생: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>