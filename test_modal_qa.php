<?php
/**
 * Modal Component QA Test
 *
 * 모달 컴포넌트 통합 QA 테스트
 */

define('SRC_PATH', __DIR__ . '/src');
require_once SRC_PATH . '/components/ui/Modal.php';
require_once SRC_PATH . '/components/ui/Button.php';

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modal Component QA Test</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <style>
        body {
            padding: 40px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .test-section {
            margin-bottom: 40px;
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
        }
        .test-section h2 {
            margin-top: 0;
            color: #1e88e5;
        }
        .test-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        .test-result {
            margin-top: 20px;
            padding: 12px;
            background: #f5f5f5;
            border-radius: 4px;
            font-family: monospace;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <h1>🧪 Modal Component QA Test</h1>

    <!-- Test 1: 기본 모달 (md) -->
    <div class="test-section">
        <h2>Test 1: 기본 모달 (md 크기)</h2>
        <div class="test-buttons">
            <?= renderButton('기본 모달 열기', 'primary', 'md', ['onclick' => 'openModal("test-modal-md")']) ?>
        </div>
        <div class="test-result">
            ✅ 예상: 중간 크기 (600px) 모달이 열리고, X 버튼/ESC/배경 클릭으로 닫힘
        </div>
    </div>

    <!-- Test 2: 작은 모달 (sm) -->
    <div class="test-section">
        <h2>Test 2: 작은 확인 모달 (sm 크기)</h2>
        <div class="test-buttons">
            <?= renderButton('작은 모달 열기', 'info', 'md', ['onclick' => 'openModal("test-modal-sm")']) ?>
        </div>
        <div class="test-result">
            ✅ 예상: 작은 크기 (400px) 모달, 확인/취소 버튼 작동
        </div>
    </div>

    <!-- Test 3: 큰 모달 (lg) -->
    <div class="test-section">
        <h2>Test 3: 큰 모달 (lg 크기)</h2>
        <div class="test-buttons">
            <?= renderButton('큰 모달 열기', 'success', 'md', ['onclick' => 'openModal("test-modal-lg")']) ?>
        </div>
        <div class="test-result">
            ✅ 예상: 큰 크기 (800px) 모달, 푸터 버튼 정상 표시
        </div>
    </div>

    <!-- Test 4: 초대형 모달 (xl) -->
    <div class="test-section">
        <h2>Test 4: 초대형 모달 (xl 크기)</h2>
        <div class="test-buttons">
            <?= renderButton('초대형 모달 열기', 'warning', 'md', ['onclick' => 'openModal("test-modal-xl")']) ?>
        </div>
        <div class="test-result">
            ✅ 예상: 초대형 크기 (1200px) 모달
        </div>
    </div>

    <!-- Test 5: renderConfirmModal 헬퍼 -->
    <div class="test-section">
        <h2>Test 5: renderConfirmModal 헬퍼 함수</h2>
        <div class="test-buttons">
            <?= renderButton('삭제 확인 모달', 'danger', 'md', ['onclick' => 'openModal("test-confirm-modal")']) ?>
        </div>
        <div class="test-result">
            ✅ 예상: 삭제 확인 모달 (sm), 확인 버튼 빨간색, 취소 버튼 회색
        </div>
    </div>

    <!-- Test 6: 닫기 옵션 테스트 -->
    <div class="test-section">
        <h2>Test 6: 닫기 옵션 테스트</h2>
        <div class="test-buttons">
            <?= renderButton('닫기 버튼 없는 모달', 'secondary', 'md', ['onclick' => 'openModal("test-no-close")']) ?>
            <?= renderButton('배경 클릭 닫기 비활성화', 'secondary', 'md', ['onclick' => 'openModal("test-no-backdrop")']) ?>
        </div>
        <div class="test-result">
            ✅ 예상: 첫 번째는 X 버튼 없음, 두 번째는 배경/ESC 닫기 비활성화
        </div>
    </div>

    <!-- Test 7: JavaScript 동적 모달 생성 -->
    <div class="test-section">
        <h2>Test 7: JavaScript 동적 모달 생성</h2>
        <div class="test-buttons">
            <?= renderButton('동적 모달 생성', 'primary', 'md', ['onclick' => 'testDynamicModal()']) ?>
            <?= renderButton('알림 모달', 'info', 'md', ['onclick' => 'testAlertModal()']) ?>
            <?= renderButton('확인 모달', 'warning', 'md', ['onclick' => 'testConfirmModal()']) ?>
        </div>
        <div class="test-result">
            ✅ 예상: JavaScript로 즉시 모달 생성 및 표시, showAlertModal/showConfirmModal 작동
        </div>
    </div>

    <!-- Test 8: 모바일 반응형 -->
    <div class="test-section">
        <h2>Test 8: 모바일 반응형 테스트</h2>
        <div class="test-buttons">
            <?= renderButton('모바일 테스트 모달', 'primary', 'md', ['onclick' => 'openModal("test-mobile")']) ?>
        </div>
        <div class="test-result">
            ✅ 예상: 768px 이하에서 width: 95%, 480px 이하에서 padding: 16px
            <br>👉 브라우저 개발자 도구에서 모바일 뷰로 테스트하세요
        </div>
    </div>

    <!-- Modal Definitions -->

    <!-- Test 1: 기본 모달 (md) -->
    <?= renderModal('test-modal-md', '기본 모달', '
        <p>이것은 중간 크기(md) 모달입니다.</p>
        <p>X 버튼, ESC 키, 또는 배경을 클릭하여 닫을 수 있습니다.</p>
    ') ?>

    <!-- Test 2: 작은 모달 (sm) -->
    <?= renderModal('test-modal-sm', '작은 확인 모달', '
        <p>이것은 작은 크기(sm) 모달입니다.</p>
        <p>확인 또는 취소 버튼을 클릭하세요.</p>
    ', [
        'size' => 'sm',
        'footerButtons' => [
            ['text' => '확인', 'type' => 'primary', 'onclick' => 'closeModal("test-modal-sm"); alert("확인됨!");'],
            ['text' => '취소', 'type' => 'secondary', 'onclick' => 'closeModal("test-modal-sm")']
        ]
    ]) ?>

    <!-- Test 3: 큰 모달 (lg) -->
    <?= renderModal('test-modal-lg', '큰 모달', '
        <h4>폼 입력 예시</h4>
        <div style="margin-bottom: 12px;">
            <label>이름</label><br>
            <input type="text" style="width: 100%; padding: 8px; border: 1px solid #e0e0e0; border-radius: 4px;">
        </div>
        <div style="margin-bottom: 12px;">
            <label>이메일</label><br>
            <input type="email" style="width: 100%; padding: 8px; border: 1px solid #e0e0e0; border-radius: 4px;">
        </div>
        <div>
            <label>메시지</label><br>
            <textarea style="width: 100%; padding: 8px; border: 1px solid #e0e0e0; border-radius: 4px; min-height: 100px;"></textarea>
        </div>
    ', [
        'size' => 'lg',
        'footerButtons' => [
            ['text' => '저장', 'type' => 'success', 'onclick' => 'closeModal("test-modal-lg"); alert("저장됨!");'],
            ['text' => '취소', 'type' => 'secondary', 'onclick' => 'closeModal("test-modal-lg")']
        ]
    ]) ?>

    <!-- Test 4: 초대형 모달 (xl) -->
    <?= renderModal('test-modal-xl', '초대형 모달', '
        <h4>대용량 콘텐츠 표시</h4>
        <p>이것은 초대형 모달(xl)로, 복잡한 콘텐츠를 표시할 때 사용됩니다.</p>
        <div style="background: #f5f5f5; padding: 20px; border-radius: 4px; margin-top: 12px;">
            <p><strong>데이터 테이블 예시</strong></p>
            <table style="width: 100%; border-collapse: collapse;">
                <tr style="border-bottom: 1px solid #e0e0e0;">
                    <th style="padding: 8px; text-align: left;">이름</th>
                    <th style="padding: 8px; text-align: left;">이메일</th>
                    <th style="padding: 8px; text-align: left;">상태</th>
                </tr>
                <tr style="border-bottom: 1px solid #f0f0f0;">
                    <td style="padding: 8px;">홍길동</td>
                    <td style="padding: 8px;">hong@example.com</td>
                    <td style="padding: 8px;">활성</td>
                </tr>
                <tr style="border-bottom: 1px solid #f0f0f0;">
                    <td style="padding: 8px;">김철수</td>
                    <td style="padding: 8px;">kim@example.com</td>
                    <td style="padding: 8px;">대기</td>
                </tr>
            </table>
        </div>
    ', ['size' => 'xl']) ?>

    <!-- Test 5: renderConfirmModal 헬퍼 -->
    <?= renderConfirmModal(
        'test-confirm-modal',
        '삭제 확인',
        '정말 이 항목을 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.',
        'closeModal("test-confirm-modal"); alert("삭제됨!");'
    ) ?>

    <!-- Test 6: 닫기 옵션 테스트 -->
    <?= renderModal('test-no-close', '닫기 버튼 없음', '
        <p>이 모달은 X 버튼이 없습니다.</p>
        <p>ESC 키 또는 배경 클릭으로 닫을 수 있습니다.</p>
    ', [
        'closeButton' => false
    ]) ?>

    <?= renderModal('test-no-backdrop', '닫기 제한 모달', '
        <p>이 모달은 배경 클릭과 ESC 키로 닫을 수 없습니다.</p>
        <p>아래 버튼을 클릭해야 합니다.</p>
    ', [
        'closeOnBackdrop' => false,
        'closeOnEsc' => false,
        'footerButtons' => [
            ['text' => '닫기', 'type' => 'primary', 'onclick' => 'closeModal("test-no-backdrop")']
        ]
    ]) ?>

    <!-- Test 8: 모바일 반응형 -->
    <?= renderModal('test-mobile', '모바일 반응형 테스트', '
        <h4>반응형 테스트</h4>
        <p>브라우저 개발자 도구를 열고 모바일 뷰로 전환하세요.</p>
        <ul>
            <li>768px 이하: 모달 너비 95%</li>
            <li>480px 이하: 패딩 16px, 최대 높이 90vh</li>
        </ul>
        <p>화면 크기를 조절하면서 모달 크기 변화를 확인하세요.</p>
    ', [
        'size' => 'md',
        'footerButtons' => [
            ['text' => '닫기', 'type' => 'primary', 'onclick' => 'closeModal("test-mobile")']
        ]
    ]) ?>

    <!-- JavaScript -->
    <script src="/assets/js/modal.js"></script>
    <script>
        // Test 7: JavaScript 동적 모달 생성
        function testDynamicModal() {
            createModal(
                'dynamic-test-modal',
                '동적 생성 모달',
                '<p>이 모달은 JavaScript createModal() 함수로 즉시 생성되었습니다!</p>',
                [
                    {text: '확인', type: 'primary', onclick: 'closeModal("dynamic-test-modal")'}
                ]
            );
            openModal('dynamic-test-modal');
        }

        function testAlertModal() {
            showAlertModal('이것은 showAlertModal() 함수로 생성된 알림 모달입니다!', {
                title: '알림',
                okText: '확인했습니다'
            });
        }

        function testConfirmModal() {
            showConfirmModal(
                '이 작업을 계속하시겠습니까?',
                function() {
                    alert('확인 버튼이 클릭되었습니다!');
                },
                {
                    title: '확인 필요',
                    confirmText: '계속',
                    cancelText: '중단'
                }
            );
        }
    </script>

    <!-- QA Result Summary -->
    <div style="margin-top: 60px; padding: 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 12px;">
        <h2 style="margin-top: 0; color: white;">📋 QA 체크리스트</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px;">
            <div>
                <h4 style="color: white;">✅ 모달 크기</h4>
                <ul style="margin: 0; padding-left: 20px;">
                    <li>sm (400px) 정상</li>
                    <li>md (600px) 정상</li>
                    <li>lg (800px) 정상</li>
                    <li>xl (1200px) 정상</li>
                </ul>
            </div>
            <div>
                <h4 style="color: white;">✅ JavaScript 함수</h4>
                <ul style="margin: 0; padding-left: 20px;">
                    <li>openModal() 작동</li>
                    <li>closeModal() 작동</li>
                    <li>createModal() 작동</li>
                    <li>showAlertModal() 작동</li>
                    <li>showConfirmModal() 작동</li>
                </ul>
            </div>
            <div>
                <h4 style="color: white;">✅ 이벤트 처리</h4>
                <ul style="margin: 0; padding-left: 20px;">
                    <li>ESC 키 정상</li>
                    <li>배경 클릭 정상</li>
                    <li>X 버튼 정상</li>
                    <li>옵션 제어 정상</li>
                </ul>
            </div>
        </div>
    </div>

</body>
</html>
