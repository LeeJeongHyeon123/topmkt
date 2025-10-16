<?php

require_once SRC_PATH . '/controllers/BaseController.php';
/**
 * Sample Controller
 * 불매 알림 페이지 컨트롤러
 */

class SampleController extends BaseController {
    /**
     * 불매 알림 페이지 표시
     */
    public function index() {
        // 현재 sample.php 파일을 직접 포함
        $samplePath = ROOT_PATH . '/public/sample.php';
        
        if (file_exists($samplePath)) {
            // sample.php 파일을 포함하여 출력
            require $samplePath;
        } else {
            // 파일이 없으면 404 에러
            header('HTTP/1.1 404 Not Found');
            echo '<h1>404 - Page Not Found</h1>';
            echo '<p>sample.php 파일을 찾을 수 없습니다.</p>';
        }
    }
}