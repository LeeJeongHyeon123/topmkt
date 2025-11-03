<?php
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

require_once SRC_PATH . '/views/templates/header.php';
?>

<div style="padding: 50px; text-align: center;">
    <h1>로그인 페이지 Toast 테스트</h1>
    <p>로그인 폼 검증 시 표시되는 Toast를 확인합니다.</p>

    <div style="max-width: 500px; margin: 30px auto; background: #f5f5f5; padding: 30px; border-radius: 10px;">
        <h2 style="margin-bottom: 20px;">테스트 시나리오</h2>

        <button onclick="testCase1()" style="padding: 15px 30px; margin: 10px; font-size: 16px; width: 90%;">
            1. 빈 폼 제출 (Toast.error)
        </button>

        <button onclick="testCase2()" style="padding: 15px 30px; margin: 10px; font-size: 16px; width: 90%;">
            2. 잘못된 휴대폰 번호 (Toast.error)
        </button>

        <button onclick="testCase3()" style="padding: 15px 30px; margin: 10px; font-size: 16px; width: 90%;">
            3. 일반 Toast.success
        </button>

        <button onclick="testCase4()" style="padding: 15px 30px; margin: 10px; font-size: 16px; width: 90%;">
            4. position 명시 (top-right)
        </button>

        <hr style="margin: 30px 0;">

        <h3>현재 Toast 위치 확인</h3>
        <div id="position-info" style="background: white; padding: 15px; border-radius: 5px; margin-top: 15px; text-align: left;">
            Toast 트리거 후 나타납니다
        </div>
    </div>
</div>

<script>
function testCase1() {
    // 로그인 페이지 line 395와 동일한 호출
    Toast.error('휴대폰 번호와 비밀번호를 모두 입력해주세요.');
    updatePositionInfo();
}

function testCase2() {
    // 로그인 페이지 line 403과 동일한 호출
    Toast.error('010으로 시작하는 올바른 휴대폰 번호를 입력해주세요.');
    updatePositionInfo();
}

function testCase3() {
    Toast.success('이것은 일반 성공 메시지입니다');
    updatePositionInfo();
}

function testCase4() {
    // 명시적으로 top-right 지정
    Toast.show('명시적 top-right 위치 테스트', 'warning', { position: 'top-right' });
    updatePositionInfo();
}

function updatePositionInfo() {
    setTimeout(() => {
        const container = document.querySelector('.toast-container');
        if (container) {
            const position = container.getAttribute('data-position');
            const className = container.className;
            const styles = window.getComputedStyle(container);

            document.getElementById('position-info').innerHTML = `
                <strong>Toast 컨테이너 정보:</strong><br>
                data-position: ${position}<br>
                className: ${className}<br>
                computed top: ${styles.top}<br>
                computed left: ${styles.left}<br>
                computed right: ${styles.right}<br>
                computed transform: ${styles.transform}
            `;
        }
    }, 100);
}
</script>

<?php require_once SRC_PATH . '/views/templates/footer.php'; ?>
