<?php
/**
 * 프로필 이미지 컴포넌트 템플릿
 * 
 * 재사용 가능한 프로필 이미지 표시 컴포넌트입니다.
 * 기존 8개 페이지에서 각각 다르게 구현되던 프로필 이미지를 통일합니다.
 * 
 * 사용법:
 * ```php
 * // API 방식 (community, notices, chat)
 * include '/path/to/components/profile-image.php';
 * 
 * // 직접 표시 방식 (lectures, events, profile)
 * $mode = 'direct';
 * include '/path/to/components/profile-image.php';
 * ```
 * 
 * 필수 변수:
 * - $user: 사용자 데이터 배열
 * 
 * 선택적 변수:
 * - $size: 이미지 크기 ('thumb', 'profile', 'original')
 * - $mode: 표시 모드 ('api', 'direct')
 * - $extraClasses: 추가 CSS 클래스 배열
 * - $showFallback: 이미지 없을 때 대체 표시 여부
 * 
 * @version 1.0.0
 * @author Claude (Anthropic)
 * @date 2025-08-11
 */

// ProfileImageHelper 클래스 로드 확인
if (!class_exists('ProfileImageHelper')) {
    require_once __DIR__ . '/../../helpers/ProfileImageHelper.php';
}

// 기본값 설정
$size = $size ?? ProfileImageHelper::SIZE_THUMB;
$mode = $mode ?? 'api';
$extraClasses = $extraClasses ?? [];
$showFallback = $showFallback ?? true;

// 사용자 데이터 검증 및 처리
if (isset($user) && is_array($user)) {
    // 사용자 데이터 정규화
    $user = ProfileImageHelper::normalizeUserData($user);

    // 프로필 이미지 URL 가져오기
    $imageUrl = ProfileImageHelper::getProfileImageUrl($user, $size);
    $originalImageUrl = ProfileImageHelper::getOriginalImageUrl($user);

    // 프로필 이미지가 있는지 확인
    $hasProfileImage = !empty($originalImageUrl) && $originalImageUrl !== ProfileImageHelper::DEFAULT_AVATAR;

    // 사용자 이름 추출
    $userName = $user['nickname'] ?? $user['author_name'] ?? $user['company_name'] ?? '사용자';

    // 프로필 이미지 HTML 출력
    if ($hasProfileImage || !$showFallback) {
        // 프로필 이미지 HTML 생성
        echo ProfileImageHelper::generateProfileImageHtml($user, $size, $mode, $extraClasses);
    } else {
        // 대체 이미지 표시 (이니셜)
        echo ProfileImageHelper::generateFallbackHtml($userName, $extraClasses);
    }
} else {
    // 유효하지 않은 사용자 데이터인 경우 기본 대체 이미지 표시
    if ($showFallback) {
        echo ProfileImageHelper::generateFallbackHtml('?', $extraClasses);
    }
}
?>