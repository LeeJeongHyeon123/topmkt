<?php
/**
 * 🔍 debug_fixed.php HTML 출력 상태 확인
 * 실제로 어떤 HTML이 브라우저에 전달되는지 분석
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>🔍 HTML 출력 분석</title>
    <style>
        body { font-family: monospace; background: #000; color: #0f0; padding: 20px; margin: 0; }
        .error { color: #f00; }
        .success { color: #0f0; }
        .warning { color: #fa0; }
        .info { color: #4af; }
        pre { background: #111; padding: 15px; border-radius: 5px; overflow-x: auto; white-space: pre-wrap; max-height: 400px; overflow-y: auto; }
        h1 { color: #f60; text-align: center; }
        .section { border: 1px solid #333; margin: 20px 0; padding: 15px; border-radius: 5px; }
        .code { background: #222; color: #ccc; padding: 10px; border-radius: 3px; margin: 10px 0; }
    </style>
</head>
<body>

<h1>🔍 debug_fixed.php HTML 출력 분석</h1>

<div class="section">
    <h2>1️⃣ 실제 HTML 출력 캡처</h2>
    <pre>
<?php
echo "=== debug_fixed.php 실제 출력 캡처 ===\n";

// debug_fixed.php를 내부적으로 실행하고 출력 캡처
ob_start();
try {
    // 오류 출력도 캡처
    $old_error_handler = set_error_handler(function($errno, $errstr, $errfile, $errline) {
        echo "PHP_ERROR: [$errno] $errstr in $errfile:$errline\n";
    });
    
    include 'debug_fixed.php';
    
    restore_error_handler();
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
} catch (Error $e) {
    echo "FATAL_ERROR: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
}

$html_output = ob_get_contents();
ob_end_clean();

echo "<span class='success'>✅ HTML 출력 길이: " . strlen($html_output) . " 바이트</span>\n";

// HTML에서 탭 관련 요소들 찾기
echo "\n=== 탭 요소 분석 ===\n";

// div id 찾기
preg_match_all('/<div[^>]+id=["\']([^"\']+)["\'][^>]*>/', $html_output, $div_matches);
if (!empty($div_matches[1])) {
    echo "<span class='info'>📋 찾은 DIV ID들:</span>\n";
    $unique_ids = array_unique($div_matches[1]);
    foreach ($unique_ids as $id) {
        echo "   - $id\n";
    }
} else {
    echo "<span class='error'>❌ DIV ID를 찾을 수 없음</span>\n";
}

// 특정 탭 ID들 검색
$expected_tabs = ['console', 'server', 'php', 'database', 'system', 'actions'];
echo "\n<span class='info'>🔍 예상 탭 ID 검색:</span>\n";
foreach ($expected_tabs as $tab_id) {
    if (strpos($html_output, 'id="' . $tab_id . '"') !== false) {
        echo "<span class='success'>✅ $tab_id 탭 존재</span>\n";
    } else {
        echo "<span class='error'>❌ $tab_id 탭 없음</span>\n";
    }
}

// HTML 끝부분 확인 (완전히 렌더링되었는지)
echo "\n=== HTML 완성도 확인 ===\n";
if (strpos($html_output, '</html>') !== false) {
    echo "<span class='success'>✅ HTML 태그 완료됨</span>\n";
} else {
    echo "<span class='error'>❌ HTML 태그 미완성 (중간에 중단됨)</span>\n";
}

if (strpos($html_output, '</body>') !== false) {
    echo "<span class='success'>✅ BODY 태그 완료됨</span>\n";
} else {
    echo "<span class='error'>❌ BODY 태그 미완성</span>\n";
}

// JavaScript 코드 확인
if (strpos($html_output, 'switchTab') !== false) {
    echo "<span class='success'>✅ JavaScript switchTab 함수 포함됨</span>\n";
} else {
    echo "<span class='error'>❌ JavaScript switchTab 함수 없음</span>\n";
}

// PHP 오류 메시지 확인
if (strpos($html_output, 'PHP_ERROR:') !== false || strpos($html_output, 'EXCEPTION:') !== false || strpos($html_output, 'FATAL_ERROR:') !== false) {
    echo "<span class='error'>🔴 PHP 오류가 출력에 포함됨</span>\n";
    echo "오류 내용:\n";
    $lines = explode("\n", $html_output);
    foreach ($lines as $line) {
        if (strpos($line, 'PHP_ERROR:') !== false || strpos($line, 'EXCEPTION:') !== false || strpos($line, 'FATAL_ERROR:') !== false) {
            echo "<span class='error'>   $line</span>\n";
        }
    }
} else {
    echo "<span class='success'>✅ PHP 오류 없음</span>\n";
}
?>
    </pre>
</div>

<div class="section">
    <h2>2️⃣ HTML 출력 샘플 (처음 2000자)</h2>
    <div class="code">
<?php
$sample = substr($html_output, 0, 2000);
echo htmlspecialchars($sample);
if (strlen($html_output) > 2000) {
    echo "\n\n... (총 " . strlen($html_output) . " 바이트 중 처음 2000자만 표시)";
}
?>
    </div>
</div>

<div class="section">
    <h2>3️⃣ HTML 출력 끝부분 (마지막 1000자)</h2>
    <div class="code">
<?php
$end_sample = substr($html_output, -1000);
echo htmlspecialchars($end_sample);
?>
    </div>
</div>

<div class="section">
    <h2>4️⃣ 문제 진단 결과</h2>
    <pre>
<?php
echo "=== 진단 결과 요약 ===\n";

$issues = [];

// 출력 길이 확인
if (strlen($html_output) < 10000) {
    $issues[] = "HTML 출력이 너무 짧음 (예상: 20000+ 바이트, 실제: " . strlen($html_output) . " 바이트)";
}

// 탭 누락 확인
$missing_tabs = [];
foreach ($expected_tabs as $tab_id) {
    if (strpos($html_output, 'id="' . $tab_id . '"') === false) {
        $missing_tabs[] = $tab_id;
    }
}

if (!empty($missing_tabs)) {
    $issues[] = "누락된 탭: " . implode(', ', $missing_tabs);
}

// HTML 완성도 확인
if (strpos($html_output, '</html>') === false) {
    $issues[] = "HTML이 완전히 렌더링되지 않음 (</html> 태그 없음)";
}

if (empty($issues)) {
    echo "<span class='success'>🎉 모든 검사 통과 - HTML이 완전히 렌더링됨</span>\n";
    echo "브라우저에서 탭이 인식되지 않는 것은 JavaScript 문제일 가능성이 높습니다.\n";
} else {
    echo "<span class='error'>🔴 발견된 문제들:</span>\n";
    foreach ($issues as $issue) {
        echo "   - $issue\n";
    }
}
?>
    </pre>
</div>

<div class="section">
    <h2>5️⃣ 즉시 해결 방안</h2>
    <div style="color: #fff; padding: 15px;">
        <h3 style="color: #f60;">🔧 문제별 해결 방법:</h3>
        <ul>
            <li><strong>HTML 출력 짧음</strong> → PHP 오류로 중단, 오류 위치 찾아 수정</li>
            <li><strong>특정 탭 누락</strong> → 해당 탭 생성 부분의 PHP 코드 문제</li>
            <li><strong>HTML 미완성</strong> → 치명적 오류로 실행 중단, 오류 수정 필요</li>
            <li><strong>JavaScript 문제</strong> → DOM 로딩 순서나 함수 정의 문제</li>
        </ul>
    </div>
</div>

</body>
</html>