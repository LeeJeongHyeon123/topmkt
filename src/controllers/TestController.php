<?php

require_once SRC_PATH . '/controllers/BaseController.php';
/**
 * 테스트 컨트롤러
 */

class TestController extends BaseController {
    
    /**
     * 테스트1 페이지
     */
    public function test1() {
        echo "테스트 페이지입니다.";
    }
}
?>