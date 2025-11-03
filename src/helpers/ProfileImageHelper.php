<?php
/**
 * 프로필 이미지 헬퍼 클래스
 * 
 * 프로필 이미지 관련 기능을 중앙화하여 일관된 처리를 제공합니다.
 * 기존 8개 페이지에서 각각 다르게 처리하던 프로필 이미지 로직을 통합합니다.
 * 
 * 주요 기능:
 * - 프로필 이미지 우선순위 처리 통일
 * - 프로필 이미지 HTML 속성 생성
 * - 사용자 데이터 정규화
 * - 기본 이미지 경로 관리
 * 
 * @version 1.0.0
 * @author Claude (Anthropic)
 * @date 2025-08-11
 */
class ProfileImageHelper {
    
    /**
     * 기본 프로필 이미지 경로
     */
    const DEFAULT_AVATAR = '/assets/images/default-avatar.png';
    
    /**
     * 프로필 이미지 크기 옵션
     */
    const SIZE_ORIGINAL = 'original';
    const SIZE_PROFILE = 'profile';  
    const SIZE_THUMB = 'thumb';
    
    /**
     * 프로필 이미지 URL 가져오기 (우선순위 처리 통일)
     * 
     * 우선순위: 
     * 1. 요청된 사이즈의 이미지
     * 2. profile 사이즈 이미지
     * 3. original 사이즈 이미지
     * 4. 기본 아바타 이미지
     * 
     * @param array $user 사용자 데이터
     * @param string $size 원하는 이미지 크기 (thumb/profile/original)
     * @return string 프로필 이미지 URL
     */
    public static function getProfileImageUrl($user, $size = self::SIZE_THUMB) {
        if (!is_array($user)) {
            return self::DEFAULT_AVATAR;
        }
        
        // 요청된 사이즈의 필드명 생성
        $sizeField = "profile_image_{$size}";
        
        // 우선순위에 따른 이미지 반환 (profile_image를 최우선으로)
        $candidates = [
            $user['profile_image'] ?? null, // 기본 프로필 이미지 (최우선)
            $user[$sizeField] ?? null,
            $user['profile_image_profile'] ?? null,
            $user['profile_image_original'] ?? null,
            self::DEFAULT_AVATAR
        ];
        
        foreach ($candidates as $imageUrl) {
            if (!empty($imageUrl) && trim($imageUrl) !== '') {
                return $imageUrl;
            }
        }
        
        return self::DEFAULT_AVATAR;
    }
    
    /**
     * 원본 프로필 이미지 URL 가져오기 (모달용)
     * 
     * @param array $user 사용자 데이터
     * @return string|null 원본 프로필 이미지 URL (없으면 null)
     */
    public static function getOriginalImageUrl($user) {
        if (!is_array($user)) {
            return null;
        }
        
        // 원본 이미지 우선순위
        $candidates = [
            $user['profile_image_original'] ?? null,
            $user['profile_image_profile'] ?? null,
            $user['profile_image'] ?? null // 기존 호환성
        ];
        
        foreach ($candidates as $imageUrl) {
            if (!empty($imageUrl) && trim($imageUrl) !== '') {
                return $imageUrl;
            }
        }
        
        return null;
    }
    
    /**
     * 프로필 이미지 HTML 속성 생성 (data-* 통일)
     *
     * API 방식과 직접 표시 방식을 모두 지원합니다.
     * v3.89.5: 모달로 이미지 크게 보기 기능 추가
     *
     * @param array $user 사용자 데이터
     * @param string $mode 'api' (API 호출) 또는 'direct' (직접 표시)
     * @return array HTML 속성 배열
     */
    public static function getProfileImageAttributes($user, $mode = 'api') {
        if (!is_array($user)) {
            return [];
        }

        // 사용자 ID 추출 (다양한 필드명 대응)
        $userId = $user['id'] ?? $user['user_id'] ?? null;

        // 사용자 이름 추출 (다양한 필드명 대응)
        $userName = $user['nickname'] ??
                   $user['author_name'] ??
                   $user['company_name'] ??
                   '사용자';

        // 원본 프로필 이미지 URL 추출 (모달용)
        $originalImageUrl = self::getOriginalImageUrl($user);

        $attributes = [
            'data-user-id' => $userId,
            'data-user-name' => htmlspecialchars($userName),
            'class' => 'profile-image-clickable',
            'title' => htmlspecialchars($userName) . '님의 프로필 이미지 (클릭하여 크게 보기)'
        ];

        // 원본 이미지가 있으면 data 속성에 추가
        if ($originalImageUrl) {
            $attributes['data-original-image'] = htmlspecialchars($originalImageUrl);
        }

        // 유효한 사용자 ID가 있을 때만 클릭 이벤트 추가
        if ($userId && !empty($user['nickname'])) {
            // v3.98.0: 프로필 이미지 클릭 → 프로필 페이지로 이동 (통일)
            // (프로필 페이지 자체는 예외 처리: profile-image.php에서 플래그 확인)
            $attributes['onclick'] = "event.stopPropagation(); if(window.TopMarketingLoading) { window.TopMarketingLoading.show(); window.TopMarketingLoading.setMessage('프로필을 불러오는 중...'); } window.location.href='/profile?user_id=" . $userId . "';";
            $attributes['title'] = htmlspecialchars($userName) . '님의 프로필 보기';
            $attributes['style'] = 'cursor: pointer;';
        }

        return $attributes;
    }
    
    /**
     * 프로필 이미지 HTML 생성
     * 
     * @param array $user 사용자 데이터
     * @param string $size 이미지 크기
     * @param string $mode 표시 모드 ('api' 또는 'direct')
     * @param array $extraClasses 추가 CSS 클래스
     * @return string 완성된 HTML 문자열
     */
    public static function generateProfileImageHtml($user, $size = self::SIZE_THUMB, $mode = 'api', $extraClasses = []) {
        if (!is_array($user)) {
            return self::generateFallbackHtml('?', $extraClasses);
        }
        
        $imageUrl = self::getProfileImageUrl($user, $size);
        $attributes = self::getProfileImageAttributes($user, $mode);
        $userName = $user['nickname'] ?? $user['author_name'] ?? '사용자';
        
        // CSS 클래스 병합
        $classes = array_merge(['profile-image-clickable'], $extraClasses);
        $attributes['class'] = implode(' ', $classes);
        
        // 속성 문자열 생성
        $attributeStrings = [];
        foreach ($attributes as $key => $value) {
            if ($value !== null && $value !== '') {
                $attributeStrings[] = $key . '="' . htmlspecialchars($value) . '"';
            }
        }
        
        return sprintf(
            '<img src="%s" alt="%s님의 프로필 이미지" %s>',
            htmlspecialchars($imageUrl),
            htmlspecialchars($userName),
            implode(' ', $attributeStrings)
        );
    }
    
    /**
     * 프로필 이미지가 없을 때 대체 HTML 생성 (아바타 이니셜)
     * 
     * @param string $nickname 사용자 닉네임
     * @param array $extraClasses 추가 CSS 클래스
     * @return string 대체 HTML
     */
    public static function generateFallbackHtml($nickname, $extraClasses = []) {
        $initial = mb_substr($nickname ?? '?', 0, 1, 'UTF-8');
        $classes = array_merge(['profile-image-fallback'], $extraClasses);
        
        return sprintf(
            '<div class="%s" title="%s님의 프로필">%s</div>',
            htmlspecialchars(implode(' ', $classes)),
            htmlspecialchars($nickname ?? '사용자'),
            htmlspecialchars($initial)
        );
    }
    
    /**
     * 사용자 데이터 정규화
     * 
     * 다양한 쿼리 결과에서 일관된 필드명으로 변환합니다.
     * 
     * @param array $user 원본 사용자 데이터
     * @return array 정규화된 사용자 데이터
     */
    public static function normalizeUserData($user) {
        if (!is_array($user)) {
            return [];
        }
        
        // 기본 구조 생성
        $normalized = $user;
        
        // ID 필드 정규화
        if (!isset($normalized['id']) && isset($normalized['user_id'])) {
            $normalized['id'] = $normalized['user_id'];
        }
        
        // 닉네임 필드 정규화
        if (!isset($normalized['nickname'])) {
            $normalized['nickname'] = $normalized['author_name'] ?? 
                                    $normalized['company_name'] ?? 
                                    '사용자';
        }
        
        // 프로필 이미지 필드 정규화 (COALESCE 로직)
        if (!isset($normalized['profile_image'])) {
            $normalized['profile_image'] = self::getProfileImageUrl($normalized);
        }
        
        return $normalized;
    }
    
    /**
     * 데이터베이스 쿼리용 프로필 이미지 SELECT 문 생성
     * 
     * @param string $userTableAlias 사용자 테이블 별칭 (예: 'u')
     * @return string SQL SELECT 절
     */
    public static function getProfileImageSelectQuery($userTableAlias = 'u') {
        return "
            COALESCE(
                {$userTableAlias}.profile_image_thumb, 
                {$userTableAlias}.profile_image_profile, 
                {$userTableAlias}.profile_image_original, 
                '" . self::DEFAULT_AVATAR . "'
            ) as profile_image,
            {$userTableAlias}.profile_image_original,
            {$userTableAlias}.profile_image_profile,
            {$userTableAlias}.profile_image_thumb
        ";
    }
    
    /**
     * 프로필 이미지 모달에 필요한 JavaScript 코드 생성
     * 
     * @return string JavaScript 코드
     */
    public static function getModalInitScript() {
        return "
        <script>
        // 프로필 이미지 모달 초기화 확인
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.profileModal === 'undefined') {
            } else {
            }
        });
        </script>
        ";
    }
}
?>