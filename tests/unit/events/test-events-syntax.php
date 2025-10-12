<?php
// 이벤트 페이지 구문 오류 테스트

// 세션 시작
session_start();

// 기본 변수 설정 (구문 오류 방지)
$isEditMode = false;
$event = null;
$instructors = [];
$images = [];
$eventId = null;

// 실제 이벤트 페이지와 동일한 조건으로 렌더링
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>구문 테스트</title>
</head>
<body>
    <h1>JavaScript 구문 테스트</h1>
    
    <?php 
    // upload-config.js.php include
    include '/var/www/html/topmkt/src/views/includes/upload-config.js.php'; 
    ?>
    
    <script>
    console.log('메인 스크립트 시작');
    
    // 파일 크기 검증 테스트
    function testFileValidation() {
        if (typeof window.validateFileSize === 'function') {
            console.log('validateFileSize 함수 존재');
            console.log('76.8KB 테스트:', window.validateFileSize(78657));
        } else {
            console.error('validateFileSize 함수 없음');
        }
    }
    
    testFileValidation();
    </script>
    
    <?php if ($isEditMode): ?>
    <script>
    console.log('편집 모드 스크립트');
    
    function loadEditData() {
        const eventData = <?= json_encode($event) ?>;
        const instructorsData = <?= json_encode($instructors) ?>;
        const imagesData = <?= json_encode($images) ?>;
        console.log('편집 데이터 로드됨');
    }
    </script>
    <?php endif; ?>
    
    <script>
    console.log('모든 스크립트 로드 완료');
    </script>
</body>
</html>
<?php
$html = ob_get_clean();

// HTML을 파일로 저장
file_put_contents('/var/www/html/topmkt/test-syntax.html', $html);

echo "구문 테스트 HTML 생성 완료!\n";
echo "브라우저에서 https://www.topmktx.com/test-syntax.html 접속하여 Console 확인\n";
?>