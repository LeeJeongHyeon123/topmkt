<?php
/**
 * Modal 컴포넌트
 *
 * 탑마케팅 프로젝트의 모든 모달을 통합 관리하는 재사용 가능한 컴포넌트
 *
 * 사용 가능한 컴포넌트 목록:
 * - Modal.php            : renderModal($id, $title, $bodyContent, $options)
 * - Button.php           : renderButton($text, $type, $size, $options)
 *
 * 📖 자세한 사용법: /docs/23.컴포넌트_사용_가이드.md
 *
 * @package TOPMKT
 * @subpackage Components\UI
 * @version 1.0.0
 * @since 2025-10-04
 */

/**
 * 모달 렌더링 함수
 *
 * @param string $id 모달 ID (고유해야 함)
 * @param string $title 모달 제목
 * @param string $bodyContent 모달 본문 HTML 내용
 * @param array $options 추가 옵션
 *   - size: 모달 크기 ('sm'|'md'|'lg'|'xl', 기본: 'md')
 *   - closeButton: 닫기 버튼 표시 여부 (기본: true)
 *   - footerButtons: 푸터 버튼 배열 [['text' => '확인', 'type' => 'primary', 'onclick' => 'save()']]
 *   - class: 추가 CSS 클래스
 *   - closeOnBackdrop: 배경 클릭 시 닫기 (기본: true)
 *   - closeOnEsc: ESC 키로 닫기 (기본: true)
 *
 * @return string 렌더링된 HTML
 *
 * @example
 * // 기본 사용
 * <?= renderModal('confirm-modal', '확인', '<p>정말 삭제하시겠습니까?</p>') ?>
 *
 * // 푸터 버튼 포함
 * <?= renderModal('edit-modal', '회원 수정', $formHtml, [
 *     'footerButtons' => [
 *         ['text' => '저장', 'type' => 'primary', 'onclick' => 'saveUser()'],
 *         ['text' => '취소', 'type' => 'secondary', 'onclick' => 'closeModal("edit-modal")']
 *     ]
 * ]) ?>
 */
function renderModal($id, $title, $bodyContent, $options = []) {
    // 기본값 설정
    $defaults = [
        'size' => 'md',
        'closeButton' => true,
        'footerButtons' => [],
        'class' => '',
        'closeOnBackdrop' => true,
        'closeOnEsc' => true
    ];

    $opts = array_merge($defaults, $options);

    // 모달 크기 검증
    $validSizes = ['sm', 'md', 'lg', 'xl'];
    if (!in_array($opts['size'], $validSizes)) {
        $opts['size'] = 'md';
    }

    // CSS 클래스 생성
    $classes = ['modal'];
    if ($opts['class']) {
        $classes[] = $opts['class'];
    }
    $classString = implode(' ', $classes);

    // 모달 content 크기 클래스
    $contentClasses = ['modal-content', 'modal-' . $opts['size']];
    $contentClassString = implode(' ', $contentClasses);

    // data 속성 생성
    $dataAttrs = [];
    if ($opts['closeOnBackdrop']) {
        $dataAttrs[] = 'data-close-backdrop="true"';
    }
    if ($opts['closeOnEsc']) {
        $dataAttrs[] = 'data-close-esc="true"';
    }
    $dataAttrString = implode(' ', $dataAttrs);

    // HTML 생성
    $html = '<div id="' . htmlspecialchars($id) . '" class="' . htmlspecialchars($classString) . '" ' . $dataAttrString . '>';
    $html .= '<div class="' . htmlspecialchars($contentClassString) . '">';

    // 헤더
    $html .= '<div class="modal-header">';
    $html .= '<h3 class="modal-title">' . htmlspecialchars($title) . '</h3>';
    if ($opts['closeButton']) {
        $html .= '<button class="modal-close" onclick="closeModal(\'' . htmlspecialchars($id) . '\')" aria-label="닫기">&times;</button>';
    }
    $html .= '</div>';

    // 본문
    $html .= '<div class="modal-body">';
    $html .= $bodyContent; // HTML 내용이므로 escape 하지 않음
    $html .= '</div>';

    // 푸터 (버튼 있을 때만)
    if (!empty($opts['footerButtons'])) {
        $html .= '<div class="modal-footer">';
        foreach ($opts['footerButtons'] as $btn) {
            $btnText = $btn['text'] ?? '버튼';
            $btnType = $btn['type'] ?? 'secondary';
            $btnSize = $btn['size'] ?? 'md';
            $btnOnclick = $btn['onclick'] ?? '';
            $btnClass = $btn['class'] ?? '';

            // renderButton 사용
            require_once __DIR__ . '/Button.php';
            $html .= renderButton($btnText, $btnType, $btnSize, [
                'onclick' => $btnOnclick,
                'class' => $btnClass
            ]);
        }
        $html .= '</div>';
    }

    $html .= '</div>'; // modal-content
    $html .= '</div>'; // modal

    return $html;
}

/**
 * 간단한 확인 모달 렌더링 (Yes/No)
 *
 * @param string $id 모달 ID
 * @param string $title 모달 제목
 * @param string $message 확인 메시지
 * @param string $onConfirm 확인 버튼 클릭 시 실행할 JavaScript 코드
 * @param array $options 추가 옵션
 *
 * @return string 렌더링된 HTML
 *
 * @example
 * <?= renderConfirmModal('delete-confirm', '삭제 확인', '정말 삭제하시겠습니까?', 'deleteUser(123)') ?>
 */
function renderConfirmModal($id, $title, $message, $onConfirm, $options = []) {
    $bodyContent = '<p>' . htmlspecialchars($message) . '</p>';

    $defaultOptions = [
        'size' => 'sm',
        'footerButtons' => [
            ['text' => '확인', 'type' => 'danger', 'onclick' => $onConfirm],
            ['text' => '취소', 'type' => 'secondary', 'onclick' => 'closeModal("' . $id . '")']
        ]
    ];

    $mergedOptions = array_merge($defaultOptions, $options);

    return renderModal($id, $title, $bodyContent, $mergedOptions);
}
