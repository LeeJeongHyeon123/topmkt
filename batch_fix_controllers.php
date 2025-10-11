<?php
// BaseController 상속화 일괄 수정 스크립트

$controllers = [
    'NoticeCommentController.php',
    'PostController.php', 
    'RegistrationNotificationController.php',
    'SampleController.php',
    'TestController.php'
];

foreach ($controllers as $controller) {
    $filePath = __DIR__ . '/src/controllers/' . $controller;
    
    if (!file_exists($filePath)) {
        echo "파일 없음: $controller\n";
        continue;
    }
    
    $content = file_get_contents($filePath);
    
    // BaseController import 추가
    if (strpos($content, 'require_once SRC_PATH . \'/controllers/BaseController.php\';') === false) {
        $content = str_replace(
            '<?php',
            "<?php\n\nrequire_once SRC_PATH . '/controllers/BaseController.php';",
            $content
        );
    }
    
    // class 선언에 extends BaseController 추가
    $content = preg_replace('/class (\w+)Controller\s*{/', 'class $1Controller extends BaseController {', $content);
    
    // 생성자에서 parent::__construct() 호출 추가
    $content = preg_replace(
        '/public function __construct\(\)\s*{\s*(?!parent::)/',
        "public function __construct() {\n        parent::__construct(); // BaseController의 생성자 호출",
        $content
    );
    
    file_put_contents($filePath, $content);
    echo "수정 완료: $controller\n";
}

echo "모든 컨트롤러 수정 완료!\n";
