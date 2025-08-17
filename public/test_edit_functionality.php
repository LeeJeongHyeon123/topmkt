<?php
/**
 * 🚀 Ultra Think 모드: 편집 페이지 기능 검증 테스트
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

echo "<h1>🚀 Ultra Think 편집 기능 검증</h1>";
echo "<h2>1. 이미지 자동 검색 시스템 테스트</h2>";

try {
    require_once SRC_PATH . '/models/Notice.php';
    
    $notice = new Notice();
    $result = $notice->getById(10);
    
    if ($result) {
        echo "<p><strong>✅ 공지사항 10번 로드 성공</strong></p>";
        echo "<ul>";
        echo "<li>제목: " . htmlspecialchars($result['title']) . "</li>";
        echo "<li>내용 길이: " . strlen($result['content']) . "자</li>";
        echo "<li>원본 이미지 경로: " . ($result['image_path'] ?? 'N/A') . "</li>";
        echo "<li><strong>자동 검색된 이미지 수: " . count($result['images'] ?? []) . "개</strong></li>";
        echo "</ul>";
        
        if (!empty($result['images'])) {
            echo "<h3>📸 검색된 이미지 목록</h3>";
            echo "<ol>";
            foreach ($result['images'] as $image) {
                $fullPath = '/var/www/html/topmkt/public' . $image['file_path'];
                $exists = file_exists($fullPath);
                $size = $exists ? filesize($fullPath) : 0;
                
                echo "<li>";
                echo "<strong>" . htmlspecialchars($image['filename']) . "</strong><br>";
                echo "경로: " . htmlspecialchars($image['file_path']) . "<br>";
                echo "파일 존재: " . ($exists ? "✅" : "❌") . "<br>";
                if ($exists) {
                    echo "파일 크기: " . number_format($size) . " bytes<br>";
                }
                echo "</li><br>";
            }
            echo "</ol>";
        } else {
            echo "<p>❌ 이미지가 검색되지 않았습니다.</p>";
        }
        
    } else {
        echo "<p>❌ 공지사항 10번을 찾을 수 없습니다.</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ 테스트 오류: " . $e->getMessage() . "</p>";
    echo "<p>파일: " . $e->getFile() . ":" . $e->getLine() . "</p>";
}

echo "<hr>";
echo "<h2>2. HTML 내용 표시 테스트</h2>";

if (isset($result) && $result) {
    $content = $result['content'];
    echo "<h3>원본 HTML 내용:</h3>";
    echo "<pre>" . htmlspecialchars($content) . "</pre>";
    
    echo "<h3>시각적 렌더링:</h3>";
    echo "<div style='border: 1px solid #ccc; padding: 15px; background: #f9f9f9;'>";
    echo $content; // HTML로 렌더링
    echo "</div>";
    
    echo "<h3>Quill 에디터용 처리 결과:</h3>";
    echo "<p>텍스트 길이: " . strlen(strip_tags($content)) . "자</p>";
    echo "<p>HTML 태그 수: " . (strlen($content) - strlen(strip_tags($content))) . "자</p>";
}

echo "<hr>";
echo "<h2>3. 종합 결과</h2>";

$tests = [
    '이미지 자동 검색' => isset($result['images']) && count($result['images']) > 0,
    '제목 로드' => isset($result['title']) && !empty($result['title']),
    '내용 로드' => isset($result['content']) && !empty($result['content']),
    '파일 시스템 접근' => file_exists('/var/www/html/topmkt/public/assets/uploads/notices/2025/08/')
];

$passedCount = 0;
$totalCount = count($tests);

foreach ($tests as $testName => $passed) {
    $status = $passed ? "✅ 통과" : "❌ 실패";
    echo "<p><strong>$testName</strong>: $status</p>";
    if ($passed) $passedCount++;
}

$percentage = round(($passedCount / $totalCount) * 100);
echo "<h3>🎯 종합 점수: $passedCount/$totalCount ($percentage%)</h3>";

if ($percentage >= 80) {
    echo "<p style='color: green; font-weight: bold;'>🎉 편집 기능이 성공적으로 구현되었습니다!</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>⚠️ 일부 기능에 문제가 있습니다. 추가 수정이 필요합니다.</p>";
}

echo "<p>테스트 완료 시간: " . date('Y-m-d H:i:s') . "</p>";
?>