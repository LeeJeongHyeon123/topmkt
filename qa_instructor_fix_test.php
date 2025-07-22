<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>강사명 수정사항 QA 테스트</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background: #f5f5f5; 
        }
        .container { 
            max-width: 900px; 
            margin: 0 auto; 
            background: white; 
            padding: 30px; 
            border-radius: 12px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1); 
        }
        .test-section { 
            margin: 25px 0; 
            padding: 20px; 
            border-left: 5px solid #007cba; 
            background: #f8f9fa; 
            border-radius: 8px;
        }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .info { color: #17a2b8; font-weight: bold; }
        .btn { 
            background: #007cba; 
            color: white; 
            padding: 12px 25px; 
            border: none; 
            border-radius: 6px; 
            cursor: pointer; 
            margin: 8px; 
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover { background: #0056b3; }
        .btn-test { background: #28a745; }
        .btn-test:hover { background: #1e7e34; }
        .checklist { 
            background: white; 
            padding: 20px; 
            border-radius: 8px; 
            border: 1px solid #e9ecef; 
        }
        .checklist-item { 
            margin: 12px 0; 
            padding: 10px; 
            border-radius: 4px; 
            background: #f8f9fa; 
        }
        .step { 
            counter-increment: step-counter; 
            position: relative; 
            padding-left: 40px; 
        }
        .step::before { 
            content: counter(step-counter); 
            position: absolute; 
            left: 0; 
            top: 0; 
            background: #007cba; 
            color: white; 
            width: 25px; 
            height: 25px; 
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-weight: bold; 
        }
        .steps-container { 
            counter-reset: step-counter; 
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 강사명 "미정" 오류 수정사항 QA 테스트</h1>
        
        <div class="test-section">
            <h2>🎯 수정사항 요약</h2>
            <div class="success">✅ <strong>문제 원인 해결:</strong> 폼에서 `instructor_names[]` 배열로 전송되는 데이터를 백엔드에서 `instructor_name`으로 올바르게 처리하도록 수정</div>
            <br>
            <div class="info">📋 <strong>수정된 파일들:</strong></div>
            <ul>
                <li><code>/src/controllers/EventController.php</code> - instructor_names 배열 처리 로직 추가</li>
                <li><code>/src/views/events/detail.php</code> - 강사 정보 표시 로직 개선</li>
                <li><code>/src/config/database.php</code> - 한글 인코딩 문제 해결</li>
            </ul>
        </div>

        <div class="test-section">
            <h2>🧪 QA 테스트 시나리오</h2>
            <div class="steps-container">
                <div class="step">
                    <h3>새로운 이벤트 등록 테스트</h3>
                    <p>수정된 로직이 제대로 작동하는지 확인</p>
                    <a href="https://www.topmktx.com/events/create" class="btn btn-test" target="_blank">이벤트 등록 페이지 열기</a>
                </div>
                
                <div class="step">
                    <h3>강사명 입력 테스트</h3>
                    <p>강사명 필드에 실제 이름을 입력하고 등록</p>
                    <div class="checklist">
                        <div class="checklist-item">☐ 강사명: "테스트 강사" 입력</div>
                        <div class="checklist-item">☐ 다른 필수 정보 모두 입력</div>
                        <div class="checklist-item">☐ 이벤트 등록 버튼 클릭</div>
                        <div class="checklist-item">☐ 성공적으로 상세페이지로 이동</div>
                    </div>
                </div>
                
                <div class="step">
                    <h3>결과 확인 테스트</h3>
                    <p>등록된 이벤트에서 강사명이 올바르게 표시되는지 확인</p>
                    <div class="checklist">
                        <div class="checklist-item">☐ 강사 정보 섹션이 표시됨</div>
                        <div class="checklist-item">☐ 강사명이 "미정"이 아닌 입력한 이름으로 표시됨</div>
                        <div class="checklist-item">☐ 한글 인코딩이 올바르게 표시됨</div>
                        <div class="checklist-item">☐ 지도 위치가 올바르게 표시됨</div>
                    </div>
                </div>
                
                <div class="step">
                    <h3>기존 이벤트 확인</h3>
                    <p>기존 이벤트 ID 181의 강사 정보 표시 확인</p>
                    <a href="https://www.topmktx.com/events/detail?id=181" class="btn" target="_blank">이벤트 ID 181 확인</a>
                    <div class="checklist">
                        <div class="checklist-item">☐ 강사 정보 섹션이 표시됨 (fallback 로직)</div>
                        <div class="checklist-item">☐ 현재는 "미정"으로 표시되지만 섹션이 보임</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="test-section">
            <h2>🔍 상세 테스트 포인트</h2>
            
            <h3>✅ 테스트해야 할 항목들</h3>
            <div class="checklist">
                <div class="checklist-item">
                    <strong>폼 데이터 전송:</strong>
                    <ul>
                        <li>강사명이 instructor_names[] 배열로 전송되는지</li>
                        <li>백엔드에서 instructor_name으로 변환되는지</li>
                    </ul>
                </div>
                <div class="checklist-item">
                    <strong>데이터베이스 저장:</strong>
                    <ul>
                        <li>입력한 강사명이 정확히 저장되는지</li>
                        <li>"미정" 기본값이 올바른 경우에만 적용되는지</li>
                    </ul>
                </div>
                <div class="checklist-item">
                    <strong>화면 표시:</strong>
                    <ul>
                        <li>강사 정보 섹션이 표시되는지</li>
                        <li>한글 인코딩이 깨지지 않는지</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="test-section">
            <h2>📊 예상 결과</h2>
            
            <div class="success">
                <h3>✅ 성공 시 예상 결과:</h3>
                <ul>
                    <li>새로 등록한 이벤트에서 강사명이 입력한 값으로 정확히 표시</li>
                    <li>"미정"이 아닌 실제 강사명 표시</li>
                    <li>강사 정보 섹션이 올바르게 렌더링</li>
                    <li>한글 인코딩 문제 해결</li>
                </ul>
            </div>
            
            <div class="warning">
                <h3>⚠️ 만약 여전히 "미정"으로 표시된다면:</h3>
                <ul>
                    <li>브라우저 개발자 도구에서 네트워크 탭 확인</li>
                    <li>폼 데이터가 올바르게 전송되는지 확인</li>
                    <li>서버 로그 확인</li>
                </ul>
            </div>
        </div>

        <div class="test-section">
            <h2>🚀 테스트 시작</h2>
            <p><strong>지금 바로 테스트를 시작해보세요!</strong></p>
            
            <a href="https://www.topmktx.com/events/create" class="btn btn-test" target="_blank">
                🎯 새 이벤트 등록하여 테스트하기
            </a>
            
            <a href="https://www.topmktx.com/events" class="btn" target="_blank">
                📋 이벤트 목록 확인하기
            </a>
        </div>

        <div class="test-section">
            <h2>📝 테스트 결과 보고</h2>
            <p>테스트 완료 후 다음 정보를 확인해주세요:</p>
            <ul>
                <li>새로 등록한 이벤트 ID</li>
                <li>강사명이 올바르게 표시되는지 여부</li>
                <li>발견된 문제점이나 개선사항</li>
            </ul>
        </div>
    </div>

    <script>
        // 체크리스트 클릭 기능
        document.querySelectorAll('.checklist-item').forEach(item => {
            item.addEventListener('click', function() {
                const checkbox = this.textContent.charAt(0);
                if (checkbox === '☐') {
                    this.textContent = this.textContent.replace('☐', '✅');
                    this.style.background = '#d4edda';
                    this.style.color = '#155724';
                } else if (checkbox === '✅') {
                    this.textContent = this.textContent.replace('✅', '☐');
                    this.style.background = '#f8f9fa';
                    this.style.color = 'inherit';
                }
            });
        });
    </script>
</body>
</html>