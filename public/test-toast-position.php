<?php
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

require_once SRC_PATH . '/views/templates/header.php';
?>

<div style="padding: 50px; text-align: center;">
    <h1>Toast 위치 테스트</h1>
    <p>아래 버튼을 클릭하여 Toast가 어디에 표시되는지 확인하세요.</p>

    <button onclick="Toast.success('성공 메시지 테스트')" style="padding: 10px 20px; margin: 10px; font-size: 16px;">
        성공 Toast
    </button>

    <button onclick="Toast.error('오류 메시지 테스트')" style="padding: 10px 20px; margin: 10px; font-size: 16px;">
        오류 Toast
    </button>

    <button onclick="Toast.info('정보 메시지 테스트')" style="padding: 10px 20px; margin: 10px; font-size: 16px;">
        정보 Toast
    </button>

    <button onclick="Toast.warning('경고 메시지 테스트')" style="padding: 10px 20px; margin: 10px; font-size: 16px;">
        경고 Toast
    </button>

    <hr style="margin: 30px 0;">

    <h2>현재 Toast 설정 확인</h2>
    <button onclick="checkToastSettings()" style="padding: 10px 20px; font-size: 16px;">
        Toast 설정 확인
    </button>

    <pre id="settings" style="background: #f5f5f5; padding: 20px; margin-top: 20px; text-align: left; border-radius: 5px;"></pre>
</div>

<script>
function checkToastSettings() {
    const settingsEl = document.getElementById('settings');

    // Toast 클래스 코드 확인
    const toastCode = Toast.show.toString();
    const match = toastCode.match(/position:\s*options\.position\s*\|\|\s*'([^']+)'/);
    const defaultPosition = match ? match[1] : '찾을 수 없음';

    const info = `
Toast 기본 설정:
==================
기본 위치 (Default Position): ${defaultPosition}
Toast 클래스 존재: ${typeof Toast !== 'undefined' ? 'O' : 'X'}
Toast.show 메서드 존재: ${typeof Toast.show === 'function' ? 'O' : 'X'}

Toast.show 함수 내용 (일부):
${toastCode.substring(0, 500)}...
    `.trim();

    settingsEl.textContent = info;
}
</script>

<?php require_once SRC_PATH . '/views/templates/footer.php'; ?>
