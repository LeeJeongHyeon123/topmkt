<?php
/**
 * 법적 문서 관련 컨트롤러
 */

class LegalController {
    
    /**
     * 이용약관 페이지 표시
     */
    public function showTerms() {
        include SRC_PATH . '/views/legal/terms.php';
    }
    
    /**
     * 개인정보처리방침 페이지 표시
     */
    public function showPrivacy() {
        include SRC_PATH . '/views/legal/privacy.php';
    }
} 