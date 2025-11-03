<?php
session_start();
define('BASE_PATH', dirname(__DIR__));
define('SRC_PATH', BASE_PATH . '/src');

require_once SRC_PATH . '/views/templates/header.php';
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toast 디자인 테스트</title>
    <style>
        .test-container {
            max-width: 1200px;
            margin: 80px auto;
            padding: 40px;
        }

        .test-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .test-header h1 {
            font-size: 32px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 10px;
        }

        .test-header p {
            font-size: 16px;
            color: #6b7280;
        }

        .button-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .test-button {
            padding: 16px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .test-button:hover {
            transform: translateY(-2px);
        }

        .test-button:active {
            transform: translateY(0);
        }

        .btn-success {
            background: #10b981;
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-error {
            background: #ef4444;
            color: white;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .btn-warning {
            background: #f59e0b;
            color: white;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }

        .btn-info {
            background: #3b82f6;
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .description {
            background: #f9fafb;
            border-left: 4px solid #3b82f6;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .description h3 {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 10px;
        }

        .description ul {
            list-style: none;
            padding-left: 0;
        }

        .description li {
            padding: 8px 0;
            color: #4b5563;
            font-size: 14px;
        }

        .description li::before {
            content: "✓ ";
            color: #10b981;
            font-weight: bold;
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <div class="test-header">
            <h1>🎨 Toast 디자인 개선 테스트</h1>
            <p>세련되고 가시성 높은 Toast 알림 디자인</p>
        </div>

        <div class="description">
            <h3>개선 사항</h3>
            <ul>
                <li>미묘한 그라디언트 배경 (타입별 색상 tint)</li>
                <li>강화된 그림자 (8px + 4px 레이어드 섀도우)</li>
                <li>Frosted glass 효과 (backdrop-filter: blur)</li>
                <li>둥근 모서리 강화 (8px → 12px)</li>
                <li>헤더와 명확히 구분되는 elevation</li>
            </ul>
        </div>

        <div class="button-grid">
            <button class="test-button btn-success" onclick="Toast.success('회원가입이 완료되었습니다! 🎉')">
                Success Toast
            </button>

            <button class="test-button btn-error" onclick="Toast.error('등록되지 않은 휴대폰 번호입니다.')">
                Error Toast
            </button>

            <button class="test-button btn-warning" onclick="Toast.warning('비밀번호가 곧 만료됩니다.')">
                Warning Toast
            </button>

            <button class="test-button btn-info" onclick="Toast.info('새로운 메시지가 도착했습니다.')">
                Info Toast
            </button>
        </div>

        <div class="button-grid">
            <button class="test-button btn-success" onclick="Toast.success('파일 업로드 성공!')">
                짧은 Success
            </button>

            <button class="test-button btn-error" onclick="Toast.error('서버 연결에 실패했습니다. 네트워크 연결을 확인하고 다시 시도해주세요.')">
                긴 Error
            </button>

            <button class="test-button btn-warning" onclick="multipleToasts()">
                Multiple Toasts
            </button>

            <button class="test-button btn-info" onclick="Toast.info('프로필이 업데이트되었습니다.', { duration: 5000 })">
                5초 Info
            </button>
        </div>
    </div>

    <script>
        function multipleToasts() {
            Toast.success('첫 번째 알림');
            setTimeout(() => Toast.info('두 번째 알림'), 500);
            setTimeout(() => Toast.warning('세 번째 알림'), 1000);
        }
    </script>
</body>
</html>

<?php require_once SRC_PATH . '/views/templates/footer.php'; ?>
