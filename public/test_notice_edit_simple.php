<?php
/**
 * 간단한 공지사항 편집 접근 테스트
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 공지사항 편집 기능 간단 테스트</h1>";

// 기본 설정 확인
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

echo "<h2>1️⃣ 파일 시스템 확인</h2>";
$editViewPath = SRC_PATH . '/views/notices/edit.php';
echo "<p>- edit.php 파일: " . (file_exists($editViewPath) ? '✅ 존재' : '❌ 없음') . "</p>";
echo "<p>- 파일 경로: $editViewPath</p>";

if (file_exists($editViewPath)) {
    $fileSize = filesize($editViewPath);
    echo "<p>- 파일 크기: " . number_format($fileSize) . " bytes</p>";
    
    $firstLines = file_get_contents($editViewPath, false, null, 0, 500);
    echo "<p>- 파일 시작 부분:</p>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
    echo htmlspecialchars($firstLines);
    echo "</pre>";
}

echo "<h2>2️⃣ 라우팅 테스트</h2>";
echo "<p><a href='/notices/10/edit' target='_blank'>🔗 공지사항 10번 편집 페이지 직접 접근 테스트</a></p>";
echo "<p><a href='/notices/10' target='_blank'>🔗 공지사항 10번 상세 페이지 (비교용)</a></p>";

echo "<h2>3️⃣ 컨트롤러 확인</h2>";
$controllerPath = SRC_PATH . '/controllers/NoticeController.php';
if (file_exists($controllerPath)) {
    echo "<p>✅ NoticeController.php 존재</p>";
    
    // showEdit 메서드 존재 확인
    $controllerContent = file_get_contents($controllerPath);
    if (strpos($controllerContent, 'showEdit') !== false) {
        echo "<p>✅ showEdit 메서드 존재</p>";
    } else {
        echo "<p>❌ showEdit 메서드 없음</p>";
    }
    
    // update 메서드 확인
    if (strpos($controllerContent, 'public function update') !== false) {
        echo "<p>✅ update 메서드 존재</p>";
    } else {
        echo "<p>❌ update 메서드 없음</p>";
    }
} else {
    echo "<p>❌ NoticeController.php 없음</p>";
}

echo "<h2>4️⃣ 네트워크 테스트</h2>";
echo "<script>
fetch('/notices/10/edit')
    .then(response => {
        console.log('편집 페이지 응답 상태:', response.status);
        document.getElementById('fetch-result').innerHTML = 
            '<p>HTTP 상태: ' + response.status + ' ' + response.statusText + '</p>';
        
        if (response.status === 200) {
            document.getElementById('fetch-result').innerHTML += '<p style=\"color: green;\">✅ 접근 성공!</p>';
        } else if (response.status === 404) {
            document.getElementById('fetch-result').innerHTML += '<p style=\"color: red;\">❌ 404 Not Found</p>';
        } else if (response.status === 403) {
            document.getElementById('fetch-result').innerHTML += '<p style=\"color: orange;\">⚠️ 403 Forbidden (권한 없음)</p>';
        } else if (response.status === 302) {
            document.getElementById('fetch-result').innerHTML += '<p style=\"color: blue;\">🔄 302 Redirect (로그인 필요)</p>';
        } else {
            document.getElementById('fetch-result').innerHTML += '<p style=\"color: red;\">❌ 오류: ' + response.status + '</p>';
        }
    })
    .catch(error => {
        console.error('네트워크 오류:', error);
        document.getElementById('fetch-result').innerHTML = '<p style=\"color: red;\">❌ 네트워크 오류: ' + error.message + '</p>';
    });
</script>";

echo "<div id='fetch-result'><p>⏳ 네트워크 테스트 진행 중...</p></div>";

echo "<h2>5️⃣ 현재 상태</h2>";
echo "<p>현재 시간: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>서버: " . $_SERVER['SERVER_NAME'] . "</p>";
echo "<p>요청 URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "</p>";

echo "<hr><p><em>이 테스트를 통해 공지사항 편집 기능의 접근 상태를 확인할 수 있습니다.</em></p>";
?>