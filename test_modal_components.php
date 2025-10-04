<?php
/**
 * Modal Component Integration Test
 *
 * 변환된 페이지들의 컴포넌트 로딩 및 모달 렌더링 테스트
 */

define('SRC_PATH', __DIR__ . '/src');
session_start();

// CSRF 토큰 생성 (테스트용)
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

echo "🧪 Modal Component Integration Test\n";
echo "========================================\n\n";

// Test 1: Button Component 로딩
echo "[1/4] Button Component 로딩 테스트...\n";
try {
    require_once SRC_PATH . '/components/ui/Button.php';
    $testButton = renderButton('테스트', 'primary', 'md');
    if (strpos($testButton, 'btn-primary') !== false) {
        echo "✅ Button Component 로딩 성공\n\n";
    } else {
        echo "❌ Button Component 렌더링 실패\n\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "❌ Button Component 로딩 실패: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 2: Modal Component 로딩
echo "[2/4] Modal Component 로딩 테스트...\n";
try {
    require_once SRC_PATH . '/components/ui/Modal.php';
    $testModal = renderModal('test-id', '테스트 제목', '<p>테스트 내용</p>');
    if (strpos($testModal, 'test-id') !== false && strpos($testModal, 'modal') !== false) {
        echo "✅ Modal Component 로딩 성공\n\n";
    } else {
        echo "❌ Modal Component 렌더링 실패\n\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "❌ Modal Component 로딩 실패: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 3: renderConfirmModal 헬퍼 테스트
echo "[3/4] renderConfirmModal 헬퍼 테스트...\n";
try {
    $confirmModal = renderConfirmModal('confirm-test', '확인', '정말 삭제하시겠습니까?', 'deleteItem()');
    if (strpos($confirmModal, 'confirm-test') !== false &&
        strpos($confirmModal, 'deleteItem()') !== false) {
        echo "✅ renderConfirmModal 정상 작동\n\n";
    } else {
        echo "❌ renderConfirmModal 렌더링 실패\n\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "❌ renderConfirmModal 테스트 실패: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 4: 복잡한 모달 (footer buttons) 테스트
echo "[4/4] Footer Buttons 모달 테스트...\n";
try {
    $complexModal = renderModal(
        'complex-modal',
        '복잡한 모달',
        '<div>내용</div>',
        [
            'size' => 'lg',
            'footerButtons' => [
                ['text' => '취소', 'type' => 'secondary', 'onclick' => 'closeModal("complex-modal")'],
                ['text' => '저장', 'type' => 'primary', 'onclick' => 'saveData()']
            ]
        ]
    );
    if (strpos($complexModal, 'complex-modal') !== false &&
        strpos($complexModal, 'saveData()') !== false &&
        strpos($complexModal, '취소') !== false) {
        echo "✅ Footer Buttons 모달 정상 작동\n\n";
    } else {
        echo "❌ Footer Buttons 모달 렌더링 실패\n\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "❌ Footer Buttons 모달 테스트 실패: " . $e->getMessage() . "\n\n";
    exit(1);
}

echo "========================================\n";
echo "✅ 모든 테스트 통과! (4/4)\n";
echo "========================================\n\n";

echo "📊 컴포넌트 통합 상태:\n";
echo "  - Button Component: ✅ 정상\n";
echo "  - Modal Component: ✅ 정상\n";
echo "  - renderModal(): ✅ 정상\n";
echo "  - renderConfirmModal(): ✅ 정상\n";
echo "  - Footer Buttons: ✅ 정상\n\n";

echo "🎉 Modal Component 시스템 완전 통합 완료!\n";
?>
