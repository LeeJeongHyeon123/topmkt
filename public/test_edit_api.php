<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Edit Check API 테스트</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .test-section { margin: 30px 0; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .test-button { background: #667eea; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        .test-button:hover { background: #5a67d8; }
        .result { margin: 10px 0; padding: 15px; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .info { background: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>
    <h1>🧪 Edit Check API 테스트</h1>

    <div class="test-section">
        <h2>API 직접 테스트</h2>
        <p>서버 API가 정상 작동하는지 확인</p>

        <button class="test-button" onclick="testAPI('/api/events/202/check-editable', '행사 202 (지난 일정)')">
            행사 202 체크 (지난 일정)
        </button>

        <button class="test-button" onclick="testAPI('/api/events/205/check-editable', '행사 205 (미래 일정)')">
            행사 205 체크 (미래 일정)
        </button>

        <button class="test-button" onclick="testAPI('/api/lectures/3/check-editable', '강의 3 (지난 일정)')">
            강의 3 체크 (지난 일정)
        </button>

        <div id="api-result"></div>
    </div>

    <div class="test-section">
        <h2>JavaScript 함수 테스트</h2>
        <p>edit-check.js의 EditChecker 함수가 정상 작동하는지 확인</p>

        <button class="test-button" onclick="testEditChecker('events', 202)">
            EditChecker로 행사 202 체크
        </button>

        <button class="test-button" onclick="testEditChecker('events', 205)">
            EditChecker로 행사 205 체크
        </button>

        <button class="test-button" onclick="testEditChecker('lectures', 3)">
            EditChecker로 강의 3 체크
        </button>

        <div id="js-result"></div>
    </div>

    <div class="test-section">
        <h2>실제 수정 버튼 시뮬레이션</h2>
        <p>실제 수정 버튼과 같은 방식으로 테스트</p>

        <button class="test-button" data-event-id="202">
            행사 202 수정 (지난 일정 - Alert 예상)
        </button>

        <button class="test-button" data-event-id="205">
            행사 205 수정 (미래 일정 - 페이지 이동 예상)
        </button>

        <button class="test-button" data-lecture-id="3">
            강의 3 수정 (지난 일정 - Alert 예상)
        </button>

        <div id="simulation-result"></div>
    </div>

    <!-- edit-check.js 로드 -->
    <script src="/assets/js/edit-check.js"></script>

    <script>
        // API 직접 테스트
        function testAPI(url, description) {
            showResult('api-result', `🔄 ${description} 테스트 중...`, 'info');

            fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showResult('api-result', `✅ ${description}: ${data.message}`, 'success');
                } else {
                    showResult('api-result', `❌ ${description}: ${data.message} (${data.code})`, 'error');
                }
            })
            .catch(error => {
                showResult('api-result', `🚨 ${description}: 네트워크 오류 - ${error.message}`, 'error');
            });
        }

        // EditChecker 함수 테스트
        function testEditChecker(type, id) {
            showResult('js-result', `🔄 EditChecker ${type} ${id} 테스트 중...`, 'info');

            const description = `${type} ${id}`;

            if (type === 'events') {
                EditChecker.checkEventEditable(id, function(editUrl) {
                    showResult('js-result', `✅ ${description}: 수정 가능 - ${editUrl}`, 'success');
                });
            } else if (type === 'lectures') {
                EditChecker.checkLectureEditable(id, function(editUrl) {
                    showResult('js-result', `✅ ${description}: 수정 가능 - ${editUrl}`, 'success');
                });
            }
        }

        // 결과 표시 함수
        function showResult(elementId, message, type) {
            const resultDiv = document.getElementById(elementId);
            const resultElement = document.createElement('div');
            resultElement.className = `result ${type}`;
            resultElement.innerHTML = `<strong>${new Date().toLocaleTimeString()}</strong> - ${message}`;
            resultDiv.appendChild(resultElement);

            // 최대 5개 결과만 표시
            while (resultDiv.children.length > 5) {
                resultDiv.removeChild(resultDiv.firstChild);
            }
        }

        // EditChecker Alert 함수 오버라이드 (테스트용)
        const originalShowAlert = EditChecker.showAlert;
        EditChecker.showAlert = function(message, code, data) {
            // 원래 alert 대신 결과창에 표시
            showResult('js-result', `🚫 ${message} (${code})`, 'error');
        };

    </script>
</body>
</html>