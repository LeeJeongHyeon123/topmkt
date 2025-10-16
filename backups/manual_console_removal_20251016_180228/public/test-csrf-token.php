<?php
/**
 * CSRF 토큰 테스트
 */

session_start();

// CSRF 토큰 생성
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_token'];

echo "<h1>CSRF 토큰 테스트</h1>";
echo "<p><strong>현재 CSRF 토큰:</strong> " . substr($csrfToken, 0, 20) . "...</p>";

?>

<script>
// 강의 162번 업데이트 테스트
function testLectureUpdate() {
    const csrfToken = '<?= $csrfToken ?>';
    
    // 테스트 데이터
    const testData = {
        csrf_token: csrfToken,
        title: '테스트 강의 제목',
        description: '테스트 설명',
        start_date: '2025-07-15',
        end_date: '2025-07-15',
        start_time: '14:00',
        end_time: '16:00',
        location_type: 'offline',
        venue_name: '테스트 장소',
        instructor_name: '테스트 강사',
        max_participants: 20,
        registration_fee: 0,
        category: 'marketing'
    };
    
    console.log('전송할 데이터:', testData);
    
    fetch('/lectures/162/update', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(testData)
    })
    .then(response => {
        console.log('응답 상태:', response.status);
        console.log('응답 헤더:', response.headers);
        return response.json();
    })
    .then(data => {
        console.log('응답 데이터:', data);
        document.getElementById('result').innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
    })
    .catch(error => {
        console.error('오류:', error);
        document.getElementById('result').innerHTML = '<p style="color: red;">오류: ' + error.message + '</p>';
    });
}

// FormData 방식 테스트
function testFormDataUpdate() {
    const csrfToken = '<?= $csrfToken ?>';
    
    const formData = new FormData();
    formData.append('_method', 'PUT');
    formData.append('csrf_token', csrfToken);
    formData.append('title', '테스트 강의 제목 (FormData)');
    formData.append('description', '테스트 설명');
    formData.append('start_date', '2025-07-15');
    formData.append('end_date', '2025-07-15');
    formData.append('start_time', '14:00');
    formData.append('end_time', '16:00');
    formData.append('location_type', 'offline');
    formData.append('venue_name', '테스트 장소');
    formData.append('instructor_name', '테스트 강사');
    formData.append('max_participants', '20');
    formData.append('registration_fee', '0');
    formData.append('category', 'marketing');
    
    console.log('FormData 전송');
    
    fetch('/lectures/162/update', {
        method: 'POST', // FormData는 POST로 보내고 _method로 PUT 지정
        body: formData
    })
    .then(response => {
        console.log('응답 상태:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('응답 데이터:', data);
        document.getElementById('result2').innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
    })
    .catch(error => {
        console.error('오류:', error);
        document.getElementById('result2').innerHTML = '<p style="color: red;">오류: ' + error.message + '</p>';
    });
}
</script>

<div style="margin: 20px 0;">
    <button onclick="testLectureUpdate()" style="padding: 10px 20px; margin-right: 10px;">JSON 방식 테스트</button>
    <button onclick="testFormDataUpdate()" style="padding: 10px 20px;">FormData 방식 테스트</button>
</div>

<h2>JSON 방식 결과:</h2>
<div id="result" style="background: #f5f5f5; padding: 10px; border-radius: 4px; min-height: 50px;"></div>

<h2>FormData 방식 결과:</h2>
<div id="result2" style="background: #f5f5f5; padding: 10px; border-radius: 4px; min-height: 50px;"></div>

<p><strong>참고:</strong> 개발자 도구 콘솔에서 더 자세한 로그를 확인할 수 있습니다.</p>