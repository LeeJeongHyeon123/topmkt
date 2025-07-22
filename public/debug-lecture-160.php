<?php
/**
 * 강의 160번 강사 이미지 문제 디버그
 */

require_once '../src/config/database.php';

try {
    $db = Database::getInstance();
    
    // 강의 160번 데이터 조회
    $lecture = $db->fetch("SELECT * FROM lectures WHERE id = 160");
    
    if (!$lecture) {
        echo "강의 160번을 찾을 수 없습니다.";
        exit;
    }
    
    echo "<h1>강의 160번 강사 이미지 문제 진단</h1>";
    
    echo "<h2>1. 강의 기본 정보</h2>";
    echo "<p><strong>제목:</strong> " . htmlspecialchars($lecture['title']) . "</p>";
    echo "<p><strong>생성일:</strong> " . $lecture['created_at'] . "</p>";
    echo "<p><strong>수정일:</strong> " . $lecture['updated_at'] . "</p>";
    
    echo "<h2>2. 강사 JSON 데이터</h2>";
    echo "<pre>" . htmlspecialchars($lecture['instructors_json']) . "</pre>";
    
    // JSON 파싱
    if ($lecture['instructors_json']) {
        $instructors = json_decode($lecture['instructors_json'], true);
        if ($instructors) {
            echo "<h3>파싱된 강사 데이터:</h3>";
            foreach ($instructors as $i => $instructor) {
                echo "<h4>강사 " . ($i + 1) . ":</h4>";
                echo "<ul>";
                echo "<li><strong>이름:</strong> " . htmlspecialchars($instructor['name'] ?? 'N/A') . "</li>";
                echo "<li><strong>직책:</strong> " . htmlspecialchars($instructor['title'] ?? 'N/A') . "</li>";
                echo "<li><strong>정보:</strong> " . htmlspecialchars($instructor['info'] ?? 'N/A') . "</li>";
                echo "<li><strong>이미지:</strong> " . (isset($instructor['image']) ? htmlspecialchars($instructor['image']) : '<span style="color: red;">없음</span>') . "</li>";
                echo "</ul>";
                
                // 이미지 파일 존재 확인
                if (isset($instructor['image'])) {
                    $imagePath = '/workspace/public' . $instructor['image'];
                    $exists = file_exists($imagePath);
                    echo "<p>이미지 파일 존재: " . ($exists ? '✅' : '❌') . " ($imagePath)</p>";
                    if ($exists) {
                        $size = filesize($imagePath);
                        echo "<p>파일 크기: " . number_format($size) . " bytes</p>";
                    }
                }
            }
        } else {
            echo "<p style='color: red;'>JSON 파싱 실패</p>";
        }
    } else {
        echo "<p style='color: red;'>강사 JSON 데이터 없음</p>";
    }
    
    echo "<h2>3. 업로드된 강사 이미지 파일 확인</h2>";
    $instructorDir = '/workspace/public/assets/uploads/instructors/';
    $files = glob($instructorDir . 'instructor_*_' . '1751342657' . '_*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
    
    if ($files) {
        echo "<p>강의 160번과 연관된 이미지 파일들:</p>";
        echo "<ul>";
        foreach ($files as $file) {
            $webPath = str_replace('/workspace/public', '', $file);
            $size = filesize($file);
            $modified = date('Y-m-d H:i:s', filemtime($file));
            echo "<li>";
            echo "<strong>" . basename($file) . "</strong><br>";
            echo "경로: " . $webPath . "<br>";
            echo "크기: " . number_format($size) . " bytes<br>";
            echo "수정일: " . $modified . "<br>";
            echo "<img src='" . $webPath . "' style='max-width: 200px; max-height: 200px; border: 1px solid #ccc; margin: 5px 0;'>";
            echo "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: orange;'>강의 160번과 연관된 이미지 파일을 찾을 수 없습니다.</p>";
        
        // 최근 업로드된 강사 이미지들 확인
        $recentFiles = glob($instructorDir . 'instructor_*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
        if ($recentFiles) {
            // 파일을 수정시간 순으로 정렬
            usort($recentFiles, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            
            echo "<h3>최근 업로드된 강사 이미지들 (최신 5개):</h3>";
            echo "<ul>";
            for ($i = 0; $i < min(5, count($recentFiles)); $i++) {
                $file = $recentFiles[$i];
                $webPath = str_replace('/workspace/public', '', $file);
                $size = filesize($file);
                $modified = date('Y-m-d H:i:s', filemtime($file));
                echo "<li>";
                echo "<strong>" . basename($file) . "</strong><br>";
                echo "경로: " . $webPath . "<br>";
                echo "크기: " . number_format($size) . " bytes<br>";
                echo "수정일: " . $modified . "<br>";
                echo "<img src='" . $webPath . "' style='max-width: 150px; max-height: 150px; border: 1px solid #ccc; margin: 5px 0;'>";
                echo "</li>";
            }
            echo "</ul>";
        }
    }
    
    echo "<h2>4. 로그 파일 확인</h2>";
    
    $logFile = '/workspace/debug_instructor_images.log';
    if (file_exists($logFile)) {
        $logContent = file_get_contents($logFile);
        echo "<h3>강사 이미지 업로드 로그:</h3>";
        echo "<pre style='background: #f5f5f5; padding: 10px; max-height: 300px; overflow-y: auto;'>";
        echo htmlspecialchars($logContent);
        echo "</pre>";
    } else {
        echo "<p>강사 이미지 업로드 로그 파일이 없습니다.</p>";
    }
    
    echo "<h2>5. 해결 방안</h2>";
    echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107;'>";
    echo "<h4>진단 결과:</h4>";
    if (!$lecture['instructors_json'] || !json_decode($lecture['instructors_json'], true)) {
        echo "<p>❌ 강사 JSON 데이터가 없거나 잘못됨</p>";
    } else {
        $instructors = json_decode($lecture['instructors_json'], true);
        $hasImages = false;
        foreach ($instructors as $instructor) {
            if (isset($instructor['image'])) {
                $hasImages = true;
                break;
            }
        }
        if (!$hasImages) {
            echo "<p>❌ 강사 데이터는 있지만 이미지 경로가 없음</p>";
        } else {
            echo "<p>✅ 강사 이미지 데이터 정상</p>";
        }
    }
    echo "</div>";
    
} catch (Exception $e) {
    echo "<h1>오류 발생</h1>";
    echo "<p>오류: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>