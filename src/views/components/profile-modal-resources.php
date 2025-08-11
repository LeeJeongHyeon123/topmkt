<?php
/**
 * 프로필 이미지 모달 리소스 로더
 * 
 * 통합된 프로필 이미지 모달 시스템에 필요한 CSS, JavaScript 리소스를 로드합니다.
 * 각 페이지에서 개별적으로 로드하던 리소스들을 중앙화합니다.
 * 
 * 사용법:
 * ```php
 * // <head> 섹션에서
 * include '/path/to/components/profile-modal-resources.php';
 * ```
 * 
 * 포함되는 리소스:
 * - profile-modal.css (통합 스타일)
 * - profile-modal.js (통합 JavaScript)
 * - 초기화 스크립트
 * 
 * @version 1.0.0
 * @author Claude (Anthropic)
 * @date 2025-08-11
 */

// ProfileImageHelper 클래스 로드 확인
if (!class_exists('ProfileImageHelper')) {
    require_once __DIR__ . '/../../helpers/ProfileImageHelper.php';
}
?>

<!-- 프로필 이미지 모달 통합 CSS -->
<link rel="stylesheet" href="/assets/css/components/profile-modal.css?v=<?= date('Ymd-His') ?>">

<!-- 프로필 이미지 모달 통합 JavaScript -->
<script src="/assets/js/profile-modal.js?v=<?= date('Ymd-His') ?>"></script>

<?php
// 모달 초기화 스크립트 출력
echo ProfileImageHelper::getModalInitScript();
?>