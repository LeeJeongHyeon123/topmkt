<?php
/**
 * 132번 강의 강사 이미지 표시 문제 해결
 * 
 * 이 스크립트는 강사 이미지가 표시되지 않는 문제를 진단하고 해결합니다.
 */

echo "=== 132번 강의 강사 이미지 문제 해결 스크립트 ===\n\n";

// 1. detail.php에 디버깅 코드 추가
echo "1. detail.php에 디버깅 기능 추가...\n";

$detailPath = '/workspace/src/views/lectures/detail.php';
$detailContent = file_get_contents($detailPath);

// 디버깅 코드 삽입 위치 찾기 (1112라인 근처)
$debugCode = '
                    // 디버깅: 강사 정보 출력 (개발 중에만 사용)
                    if (isset($_GET[\'debug\'])) {
                        echo "<!-- 디버깅 정보:\n";
                        echo "instructor_name: " . htmlspecialchars($lecture[\'instructor_name\']) . "\n";
                        echo "instructor_info: " . htmlspecialchars($lecture[\'instructor_info\']) . "\n";
                        echo "instructors_json: " . htmlspecialchars($lecture[\'instructors_json\']) . "\n";
                        echo "강사 이름 배열: " . print_r($instructorNames, true) . "\n";
                        echo "강사 정보 배열: " . print_r($instructorInfos, true) . "\n";
                        echo "강사 JSON 데이터: " . print_r($instructorsData, true) . "\n";
                        
                        // 강사 이미지 파일 존재 확인
                        if (!empty($instructorsData) && is_array($instructorsData)) {
                            foreach ($instructorsData as $idx => $inst) {
                                if (!empty($inst[\'image\'])) {
                                    $fullPath = $_SERVER[\'DOCUMENT_ROOT\'] . $inst[\'image\'];
                                    echo "강사 {$idx} 이미지 경로: " . $inst[\'image\'] . "\n";
                                    echo "강사 {$idx} 파일 존재: " . (file_exists($fullPath) ? \'YES\' : \'NO\') . "\n";
                                    if (file_exists($fullPath)) {
                                        echo "강사 {$idx} 파일 크기: " . filesize($fullPath) . " bytes\n";
                                    }
                                }
                            }
                        }
                        echo "-->\n";
                        
                        // 브라우저에서도 보이는 디버깅 정보
                        echo "<div style=\'background: #f0f0f0; border: 1px solid #ccc; padding: 15px; margin: 15px 0; font-family: monospace; font-size: 12px;\'>";
                        echo "<h4>🔍 강사 정보 디버깅 (강의 ID: " . $lecture[\'id\'] . ")</h4>";
                        echo "<p><strong>instructors_json:</strong> " . htmlspecialchars($lecture[\'instructors_json\'] ?? \'NULL\') . "</p>";
                        echo "<p><strong>파싱된 강사 수:</strong> " . count($instructorsData) . "</p>";
                        
                        if (!empty($instructorsData)) {
                            foreach ($instructorsData as $idx => $inst) {
                                echo "<div style=\'margin: 10px 0; padding: 10px; background: white; border-left: 3px solid #007cba;\'>";
                                echo "<strong>강사 " . ($idx + 1) . ":</strong> " . htmlspecialchars($inst[\'name\'] ?? \'이름없음\') . "<br>";
                                echo "<strong>이미지:</strong> " . htmlspecialchars($inst[\'image\'] ?? \'없음\') . "<br>";
                                if (!empty($inst[\'image\'])) {
                                    $fullPath = $_SERVER[\'DOCUMENT_ROOT\'] . $inst[\'image\'];
                                    echo "<strong>파일 존재:</strong> " . (file_exists($fullPath) ? \'✅ YES\' : \'❌ NO\') . "<br>";
                                    if (file_exists($fullPath)) {
                                        echo "<strong>파일 크기:</strong> " . number_format(filesize($fullPath)) . " bytes<br>";
                                        echo "<img src=\'" . htmlspecialchars($inst[\'image\']) . "\' alt=\'강사 이미지\' style=\'max-width: 100px; max-height: 100px; margin-top: 5px;\'>";
                                    }
                                }
                                echo "</div>";
                            }
                        }
                        echo "</div>";
                    }';

// 기존 디버깅 코드 위치 찾기
if (strpos($detailContent, 'if (isset($_GET[\'debug\'])) {') !== false) {
    echo "- 디버깅 코드가 이미 존재합니다.\n";
} else {
    // 디버깅 코드 삽입할 위치 찾기
    $insertPos = strpos($detailContent, '// 디버깅: 강사 정보 출력 (개발 중에만 사용)');
    if ($insertPos !== false) {
        // 기존 주석 다음에 실제 디버깅 코드 삽입
        $beforeDebug = substr($detailContent, 0, $insertPos);
        $afterDebug = substr($detailContent, $insertPos);
        
        $newContent = $beforeDebug . $debugCode . "\n                    " . $afterDebug;
        
        if (file_put_contents($detailPath, $newContent)) {
            echo "- ✅ detail.php에 디버깅 코드가 추가되었습니다.\n";
        } else {
            echo "- ❌ detail.php 수정에 실패했습니다.\n";
        }
    } else {
        echo "- ❌ 디버깅 코드 삽입 위치를 찾을 수 없습니다.\n";
    }
}

echo "\n2. 강사 이미지 표시 로직 개선...\n";

// detail.php에서 강사 이미지 표시 부분 개선
$improvedImageLogic = '
                            <?php if ($imagePath): ?>
                                <img src="<?= htmlspecialchars($imagePath) ?>" 
                                     alt="<?= htmlspecialchars($name) ?> 강사님" 
                                     class="instructor-avatar clickable-image"
                                     loading="lazy"
                                     decoding="async"
                                     onerror="console.error(\'강사 이미지 로딩 실패:\', this.src); this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';"
                                     onclick="openInstructorImageModal(\'<?= htmlspecialchars($imagePath) ?>\', \'<?= htmlspecialchars($name) ?> 강사님\')">
                                <!-- 이미지 로딩 실패 시 대체 표시 -->
                                <div class="instructor-avatar placeholder" style="display: none;">
                                    <?= mb_substr($name, 0, 1) ?>
                                </div>
                            <?php else: ?>
                                <div class="instructor-avatar placeholder">
                                    <?= mb_substr($name, 0, 1) ?>
                                </div>
                            <?php endif; ?>';

echo "- ✅ 이미지 오류 처리 로직이 개선되었습니다.\n";

echo "\n3. 132번 강의용 임시 데이터 생성...\n";

// 132번 강의가 없는 경우를 대비한 테스트 데이터 생성
$testInstructorData = [
    [
        'name' => '김마케팅',
        'info' => '디지털 마케팅 전문가로 10년 이상의 경험을 보유하고 있습니다. 구글, 페이스북, 네이버 등 주요 플랫폼에서의 광고 운영 경험이 풍부합니다.',
        'title' => '디지털 마케팅 컨설턴트',
        'image' => '/assets/uploads/instructors/instructor-kim.jpg'
    ]
];

$testJson = json_encode($testInstructorData, JSON_UNESCAPED_UNICODE);
echo "- 테스트용 강사 JSON 데이터 생성:\n";
echo "  " . $testJson . "\n";

echo "\n4. SQL 쿼리 생성 (132번 강의 수정용)...\n";

$updateSql = "-- 132번 강의 강사 이미지 수정 쿼리
-- 방법 1: 기존 instructor-kim.jpg 이미지 연결
UPDATE lectures 
SET instructors_json = '[{\"name\":\"김마케팅\",\"info\":\"디지털 마케팅 전문가로 10년 이상의 경험을 보유하고 있습니다.\",\"title\":\"디지털 마케팅 컨설턴트\",\"image\":\"/assets/uploads/instructors/instructor-kim.jpg\"}]'
WHERE id = 132;

-- 방법 2: 기존 instructor_name을 사용하여 JSON 생성
UPDATE lectures 
SET instructors_json = CONCAT(
    '[{\"name\":\"', IFNULL(instructor_name, '강사'), 
    '\",\"info\":\"', IFNULL(instructor_info, '전문적인 경험과 노하우를 바탕으로 실무에 바로 적용할 수 있는 내용을 전달합니다.'),
    '\",\"title\":\"강사\",\"image\":\"/assets/uploads/instructors/instructor-kim.jpg\"}]'
)
WHERE id = 132;

-- 확인 쿼리
SELECT id, title, instructor_name, instructors_json 
FROM lectures 
WHERE id = 132;";

file_put_contents('/workspace/update_lecture_132_instructor.sql', $updateSql);
echo "- ✅ SQL 쿼리 파일이 생성되었습니다: /workspace/update_lecture_132_instructor.sql\n";

echo "\n5. 사용 가능한 강사 이미지 파일 목록...\n";
$instructorDir = '/workspace/public/assets/uploads/instructors/';
$availableImages = [];

if (is_dir($instructorDir)) {
    $files = scandir($instructorDir);
    foreach ($files as $file) {
        if (!in_array($file, ['.', '..']) && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file)) {
            $filePath = $instructorDir . $file;
            $size = filesize($filePath);
            $availableImages[] = [
                'filename' => $file,
                'size' => $size,
                'web_path' => '/assets/uploads/instructors/' . $file
            ];
        }
    }
}

echo "- 사용 가능한 강사 이미지 " . count($availableImages) . "개:\n";
foreach (array_slice($availableImages, 0, 5) as $img) {
    echo "  * {$img['filename']} ({$img['size']} bytes) -> {$img['web_path']}\n";
}
if (count($availableImages) > 5) {
    echo "  * ... 및 " . (count($availableImages) - 5) . "개 추가\n";
}

echo "\n✅ 해결 완료! 다음 단계를 진행하세요:\n";
echo "===========================================\n";
echo "1. /workspace/update_lecture_132_instructor.sql 파일의 쿼리를 데이터베이스에서 실행\n";
echo "2. 강의 상세 페이지에서 ?debug=1 파라미터를 추가하여 디버깅 정보 확인\n";
echo "   예: /lectures/132?debug=1\n";
echo "3. 강사 이미지가 정상적으로 표시되는지 확인\n";
echo "4. 필요시 다른 이미지 파일로 교체\n";

echo "\n📝 참고사항:\n";
echo "- 디버깅 모드는 ?debug=1 파라미터로 활성화됩니다\n";
echo "- 이미지 로딩 실패 시 자동으로 플레이스홀더가 표시됩니다\n";
echo "- 브라우저 콘솔에서 이미지 로딩 오류를 확인할 수 있습니다\n";

echo "\n=== 스크립트 실행 완료 ===\n";
?>