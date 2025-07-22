<?php
/**
 * 강의 142번 드래그&드롭 순서 fix 테스트
 */

// cURL을 사용해서 실제 웹사이트에서 강의 142번 페이지를 가져와서 확인
$url = "http://www.topmktx.com/lectures/142";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h1>강의 142번 드래그&드롭 순서 fix 테스트</h1>\n";
echo "<p><strong>URL:</strong> $url</p>\n";
echo "<p><strong>HTTP 상태:</strong> $httpCode</p>\n";

if ($httpCode == 200 && $response) {
    // 디버깅 정보 섹션 추출
    $pattern = '/이미지 순서 디버깅 정보.*?<\/div>\s*<\/div>/s';
    if (preg_match($pattern, $response, $matches)) {
        echo "<h2>✅ 디버깅 정보 발견!</h2>\n";
        echo "<div style='border: 2px solid green; padding: 15px; margin: 15px 0;'>\n";
        echo $matches[0];
        echo "</div>\n";
        
        // display_order 값들 추출
        if (preg_match_all('/display_order:\s*(\d+|MISSING)/', $response, $orderMatches)) {
            echo "<h3>발견된 display_order 값들:</h3>\n";
            echo "<ul>\n";
            foreach ($orderMatches[1] as $idx => $order) {
                $status = ($order === 'MISSING') ? '❌ MISSING' : "✅ $order";
                echo "<li>이미지 " . ($idx + 1) . ": $status</li>\n";
            }
            echo "</ul>\n";
            
            // 성공 여부 판단
            $missingCount = count(array_filter($orderMatches[1], function($o) { return $o === 'MISSING'; }));
            if ($missingCount == 0) {
                echo "<h2 style='color: green;'>🎉 성공! 모든 이미지에 display_order가 있습니다!</h2>\n";
            } else {
                echo "<h2 style='color: red;'>❌ 실패: $missingCount 개의 이미지에 display_order가 없습니다.</h2>\n";
            }
        }
    } else {
        echo "<h2 style='color: orange;'>⚠️ 디버깅 정보를 찾을 수 없습니다.</h2>\n";
        echo "<p>페이지는 로드되었지만 디버깅 섹션이 없습니다.</p>\n";
    }
} else {
    echo "<h2 style='color: red;'>❌ 페이지 로드 실패</h2>\n";
    echo "<p>HTTP 상태: $httpCode</p>\n";
}
?>