<?php
/**
 * 테스트 페이지 1
 */

// 기본 설정
define('SRC_PATH', dirname(__DIR__) . '/src');
require_once SRC_PATH . '/config/database.php';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>테스트 페이지</title>
    <style>
        body {
            font-family: 'Noto Sans KR', Arial, sans-serif;
            margin: 0;
            padding: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .test-container {
            text-align: center;
            background: rgba(255, 255, 255, 0.1);
            padding: 60px 40px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        h1 {
            font-size: 3rem;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }
        p {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 30px;
        }
        .timestamp {
            font-size: 0.9rem;
            opacity: 0.7;
            margin-top: 30px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }
        .btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>🧪 테스트</h1>
        <p>test1 라우트가 정상적으로 작동하고 있습니다!</p>
        <p>이 페이지는 <code>/test1</code> 경로로 접근 가능합니다.</p>
        
        <div class="timestamp">
            현재 시간: <?= date('Y-m-d H:i:s') ?><br>
            서버 시간대: <?= date_default_timezone_get() ?>
        </div>
        
        <div style="margin-top: 30px;">
            <a href="/" class="btn">홈으로 돌아가기</a>
        </div>
    </div>
</body>
</html>