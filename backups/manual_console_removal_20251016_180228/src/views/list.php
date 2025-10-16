<?php
/**
 * list.php - events/list.php로 리다이렉트
 */

// events/list.php 파일을 포함
if (file_exists(SRC_PATH . '/views/events/list.php')) {
    require_once SRC_PATH . '/views/events/list.php';
} else {
    // list 뷰가 없으면 index 뷰를 사용
    require_once SRC_PATH . '/views/events/index.php';
}
?>