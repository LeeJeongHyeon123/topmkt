<?php
// 157번 강의 강사 이미지 문제 디버깅 스크립트
require_once 'src/config/config.php';
require_once 'src/config/database.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== 157번 강의 강사 이미지 문제 디버깅 ===\n\n";

try {
    $db = Database::getInstance();
    
    // 1. 157번 강의 정보 조회
    echo "1. 157번 강의 정보 조회:\n";
    $stmt = $db->prepare("SELECT * FROM lectures WHERE id = ?");
    $stmt->execute([157]);
    $lecture = $stmt->fetch();
    
    if ($lecture) {
        echo "✅ 강의 찾음:\n";
        echo "- ID: " . $lecture['id'] . "\n";
        echo "- 제목: " . ($lecture['title'] ?? 'NULL') . "\n";
        echo "- 강사명: " . ($lecture['instructor_name'] ?? 'NULL') . "\n";
        echo "- 강사 이미지 (단일): " . ($lecture['instructor_image'] ?? 'NULL') . "\n";
        echo "- 강사 정보: " . ($lecture['instructor_info'] ?? 'NULL') . "\n";
        echo "- 강사 JSON: " . ($lecture['instructors_json'] ?? 'NULL') . "\n";
        echo "- 생성일: " . ($lecture['created_at'] ?? 'NULL') . "\n";
        echo "- 수정일: " . ($lecture['updated_at'] ?? 'NULL') . "\n\n";
        
        // 2. 단일 강사 이미지 확인
        echo "2. 단일 강사 이미지 확인:\n";
        if (!empty($lecture['instructor_image'])) {
            $singleImagePath = '/workspace/public/assets/uploads/instructors/' . $lecture['instructor_image'];
            $webPath = '/assets/uploads/instructors/' . $lecture['instructor_image'];
            
            echo "- 이미지 파일명: " . $lecture['instructor_image'] . "\n";
            echo "- 서버 경로: " . $singleImagePath . "\n";
            echo "- 웹 경로: " . $webPath . "\n";
            echo "- 파일 존재: " . (file_exists($singleImagePath) ? '✅ YES' : '❌ NO') . "\n";
            
            if (file_exists($singleImagePath)) {
                echo "- 파일 크기: " . filesize($singleImagePath) . " bytes\n";
                echo "- 수정 시간: " . date('Y-m-d H:i:s', filemtime($singleImagePath)) . "\n";
            }
        } else {
            echo "❌ 단일 강사 이미지가 설정되지 않음\n";
        }
        echo "\n";
        
        // 3. JSON 데이터 파싱
        echo "3. JSON 강사 데이터 파싱:\n";
        if (!empty($lecture['instructors_json'])) {
            $instructors = json_decode($lecture['instructors_json'], true);
            $jsonError = json_last_error();
            
            if ($jsonError === JSON_ERROR_NONE) {
                echo "✅ JSON 파싱 성공 - " . count($instructors) . "명의 강사:\n";
                foreach ($instructors as $index => $instructor) {
                    echo "강사 " . ($index + 1) . ":\n";
                    echo "  - 이름: " . ($instructor['name'] ?? 'NULL') . "\n";
                    echo "  - 이미지: " . ($instructor['image'] ?? 'NULL') . "\n";
                    echo "  - 정보: " . substr($instructor['info'] ?? 'NULL', 0, 100) . "...\n";
                    echo "  - 직책: " . ($instructor['title'] ?? 'NULL') . "\n";
                    
                    // 이미지 파일 실제 존재 확인
                    if (!empty($instructor['image'])) {
                        $fullPath = '/workspace/public/assets/uploads/instructors/' . $instructor['image'];
                        $exists = file_exists($fullPath);
                        echo "  - 이미지 파일 존재: " . ($exists ? '✅ YES' : '❌ NO') . "\n";
                        if ($exists) {
                            echo "  - 파일 크기: " . filesize($fullPath) . " bytes\n";
                            echo "  - 수정 시간: " . date('Y-m-d H:i:s', filemtime($fullPath)) . "\n";
                        } else {
                            echo "  - 파일 경로: " . $fullPath . "\n";
                        }
                    } else {
                        echo "  - 이미지 파일 없음\n";
                    }
                    echo "\n";
                }
            } else {
                echo "❌ JSON 파싱 실패: " . json_last_error_msg() . "\n";
                echo "Raw JSON: " . $lecture['instructors_json'] . "\n\n";
            }
        } else {
            echo "❌ JSON 강사 데이터가 없음\n\n";
        }
        
        // 4. 주변 강의들과 비교
        echo "4. 주변 강의들과 비교 (150-165):\n";
        $stmt = $db->prepare("
            SELECT id, title, instructor_name, instructor_image, 
                   CASE WHEN instructors_json IS NOT NULL AND instructors_json != '' THEN 'YES' ELSE 'NO' END as has_json,
                   created_at
            FROM lectures 
            WHERE id BETWEEN 150 AND 165 
            ORDER BY id
        ");
        $stmt->execute();
        $nearbyLectures = $stmt->fetchAll();
        
        foreach ($nearbyLectures as $lec) {
            $marker = $lec['id'] == 157 ? '👉' : '  ';
            echo "{$marker} 강의 {$lec['id']}: {$lec['instructor_name']} | 단일이미지: " . 
                 ($lec['instructor_image'] ?? 'NULL') . " | JSON: {$lec['has_json']} | 생성: {$lec['created_at']}\n";
        }
        echo "\n";
        
        // 5. 강사 이미지 폴더 최근 파일들
        echo "5. 강사 이미지 폴더 최근 파일들:\n";
        $instructorDir = '/workspace/public/assets/uploads/instructors/';
        if (is_dir($instructorDir)) {
            $files = array_diff(scandir($instructorDir), array('.', '..'));
            $fileData = [];
            
            foreach ($files as $file) {
                if (is_file($instructorDir . $file)) {
                    $fileData[] = [
                        'name' => $file,
                        'time' => filemtime($instructorDir . $file),
                        'size' => filesize($instructorDir . $file)
                    ];
                }
            }
            
            // 최근 파일 순으로 정렬
            usort($fileData, function($a, $b) {
                return $b['time'] - $a['time'];
            });
            
            echo "최근 15개 파일:\n";
            for ($i = 0; $i < min(15, count($fileData)); $i++) {
                $file = $fileData[$i];
                echo "  - {$file['name']} ({$file['size']} bytes, " . date('Y-m-d H:i:s', $file['time']) . ")\n";
            }
        } else {
            echo "❌ 강사 이미지 폴더가 존재하지 않음\n";
        }
        echo "\n";
        
        // 6. 가능한 이미지 충돌 확인
        echo "6. 가능한 이미지 충돌 확인:\n";
        if (!empty($lecture['instructors_json'])) {
            $instructors = json_decode($lecture['instructors_json'], true);
            if ($instructors) {
                $imageFiles = [];
                foreach ($instructors as $instructor) {
                    if (!empty($instructor['image'])) {
                        $imageFiles[] = $instructor['image'];
                    }
                }
                
                if (!empty($imageFiles)) {
                    echo "사용 중인 이미지 파일들:\n";
                    foreach ($imageFiles as $image) {
                        echo "  - " . $image . "\n";
                        
                        // 다른 강의에서도 사용하는지 확인
                        $stmt = $db->prepare("
                            SELECT id, title FROM lectures 
                            WHERE id != ? AND (
                                instructor_image = ? OR 
                                instructors_json LIKE ?
                            )
                        ");
                        $stmt->execute([157, $image, '%' . $image . '%']);
                        $conflicts = $stmt->fetchAll();
                        
                        if (!empty($conflicts)) {
                            echo "    ⚠️  다른 강의에서도 사용 중:\n";
                            foreach ($conflicts as $conflict) {
                                echo "      - 강의 {$conflict['id']}: {$conflict['title']}\n";
                            }
                        } else {
                            echo "    ✅ 고유 사용\n";
                        }
                    }
                } else {
                    echo "❌ JSON에 이미지 파일이 없음\n";
                }
            }
        } else {
            echo "❌ JSON 데이터가 없어 충돌 확인 불가\n";
        }
        echo "\n";
        
        // 7. 추천 해결책
        echo "7. 추천 해결책:\n";
        
        if (empty($lecture['instructors_json'])) {
            echo "❌ 문제: instructors_json 필드가 비어있음\n";
            echo "✅ 해결책: 기본 강사 데이터 생성 필요\n";
        } else {
            $instructors = json_decode($lecture['instructors_json'], true);
            if ($instructors) {
                $hasValidImages = false;
                foreach ($instructors as $instructor) {
                    if (!empty($instructor['image'])) {
                        $imagePath = '/workspace/public/assets/uploads/instructors/' . $instructor['image'];
                        if (file_exists($imagePath)) {
                            $hasValidImages = true;
                            break;
                        }
                    }
                }
                
                if (!$hasValidImages) {
                    echo "❌ 문제: 유효한 강사 이미지가 없음\n";
                    echo "✅ 해결책: 기본 이미지 자동 할당 또는 새 이미지 업로드\n";
                } else {
                    echo "✅ 강사 이미지 데이터가 정상적으로 존재함\n";
                    echo "❓ 추가 조사: 프론트엔드 표시 로직 확인 필요\n";
                }
            } else {
                echo "❌ 문제: JSON 파싱 실패\n";
                echo "✅ 해결책: JSON 데이터 재생성 필요\n";
            }
        }
        
    } else {
        echo "❌ 강의 ID 157을 찾을 수 없음\n";
        
        // 최근 강의 ID들 확인
        echo "\n최근 강의 ID들:\n";
        $stmt = $db->prepare("SELECT id, title FROM lectures ORDER BY id DESC LIMIT 10");
        $stmt->execute();
        $recent = $stmt->fetchAll();
        
        foreach ($recent as $lec) {
            echo "- 강의 {$lec['id']}: {$lec['title']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ 오류 발생: " . $e->getMessage() . "\n";
    echo "스택 트레이스:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== 디버깅 완료 ===\n";
?>