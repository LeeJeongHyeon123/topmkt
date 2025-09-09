<?php
session_start();

// CSRF 토큰 생성 (AuthController와 동일한 방식)
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

echo "=== CSRF 토큰 디버깅 페이지 ===\n";
echo "현재 세션 ID: " . session_id() . "\n";
echo "CSRF 토큰: " . $_SESSION['csrf_token'] . "\n";
echo "토큰 길이: " . strlen($_SESSION['csrf_token']) . "\n";
echo "생성 시간: " . date('Y-m-d H:i:s') . "\n";

// POST 요청이면 검증
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "\n=== POST 요청 검증 ===\n";
    echo "POST 토큰: " . ($_POST['csrf_token'] ?? 'NOT SET') . "\n";
    echo "세션 토큰: " . $_SESSION['csrf_token'] . "\n";
    
    if (isset($_POST['csrf_token'])) {
        echo "토큰 길이 비교: POST=" . strlen($_POST['csrf_token']) . ", SESSION=" . strlen($_SESSION['csrf_token']) . "\n";
        echo "토큰 일치: " . (hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']) ? 'YES' : 'NO') . "\n";
        
        // 문자열 비교 (처음 20자)
        echo "토큰 앞부분 비교:\n";
        echo "  POST: " . substr($_POST['csrf_token'], 0, 20) . "...\n";
        echo "  SESSION: " . substr($_SESSION['csrf_token'], 0, 20) . "...\n";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>CSRF 토큰 디버깅</title>
</head>
<body>
    <h1>CSRF 토큰 테스트</h1>
    
    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="text" name="test_field" placeholder="테스트 입력" required>
        <button type="submit">테스트 제출</button>
    </form>
    
    <h2>JavaScript 테스트</h2>
    <button onclick="testAjaxRequest()">AJAX 요청 테스트</button>
    
    <div id="result"></div>
    
    <script>
    function testAjaxRequest() {
        const formData = new FormData();
        formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');
        formData.append('test_field', 'ajax_test');
        
        fetch(window.location.href, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.text())
        .then(data => {
            document.getElementById('result').innerHTML = '<pre>' + data + '</pre>';
        })
        .catch(error => {
            document.getElementById('result').innerHTML = 'Error: ' + error;
        });
    }
    </script>
</body>
</html>