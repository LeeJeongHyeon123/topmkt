<?php
/**
 * 컨트롤러 로딩 및 기본 동작 테스트 스크립트
 */

// 설정
define('SRC_PATH', __DIR__ . '/src');

// 세션 시작 (컨트롤러에서 필요)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// CSRF 토큰 설정 (테스트용)
$_SESSION['csrf_token'] = 'test_csrf_token_12345';

echo "🧪 컨트롤러 테스트 시작\n";
echo str_repeat("=", 50) . "\n";

try {
    // 1. NoticeController 테스트
    echo "\n--- 1. NoticeController 로딩 테스트 ---\n";
    
    require_once SRC_PATH . '/controllers/NoticeController.php';
    $noticeController = new NoticeController();
    echo "✅ NoticeController 인스턴스 생성 성공\n";
    
    // 메서드 존재 확인
    $methods = ['index', 'show', 'showWrite', 'showEdit', 'create', 'update', 'delete'];
    foreach ($methods as $method) {
        if (method_exists($noticeController, $method)) {
            echo "✅ {$method}() 메서드 존재\n";
        } else {
            echo "❌ {$method}() 메서드 없음\n";
        }
    }
    
    // 2. NoticeCommentController 테스트
    echo "\n--- 2. NoticeCommentController 로딩 테스트 ---\n";
    
    require_once SRC_PATH . '/controllers/NoticeCommentController.php';
    $commentController = new NoticeCommentController();
    echo "✅ NoticeCommentController 인스턴스 생성 성공\n";
    
    // 메서드 존재 확인
    $commentMethods = ['store', 'update', 'delete', 'list'];
    foreach ($commentMethods as $method) {
        if (method_exists($commentController, $method)) {
            echo "✅ {$method}() 메서드 존재\n";
        } else {
            echo "❌ {$method}() 메서드 없음\n";
        }
    }
    
    // 3. 의존성 확인 테스트
    echo "\n--- 3. 컨트롤러 의존성 확인 ---\n";
    
    // NoticeController의 모델 의존성 확인
    $reflection = new ReflectionClass($noticeController);
    $properties = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);
    
    $hasNoticeModel = false;
    $hasUserModel = false;
    
    foreach ($properties as $property) {
        $property->setAccessible(true);
        $value = $property->getValue($noticeController);
        
        if ($value instanceof Notice) {
            $hasNoticeModel = true;
            echo "✅ Notice 모델 의존성 확인\n";
        } elseif ($value instanceof User) {
            $hasUserModel = true;
            echo "✅ User 모델 의존성 확인\n";
        }
    }
    
    if (!$hasNoticeModel) echo "⚠️ Notice 모델 의존성 미확인\n";
    if (!$hasUserModel) echo "⚠️ User 모델 의존성 미확인\n";
    
    // NoticeCommentController의 모델 의존성 확인
    $commentReflection = new ReflectionClass($commentController);
    $commentProperties = $commentReflection->getProperties(ReflectionProperty::IS_PRIVATE);
    
    $hasCommentModel = false;
    
    foreach ($commentProperties as $property) {
        $property->setAccessible(true);
        $value = $property->getValue($commentController);
        
        if ($value instanceof NoticeComment) {
            $hasCommentModel = true;
            echo "✅ NoticeComment 모델 의존성 확인\n";
            break;
        }
    }
    
    if (!$hasCommentModel) echo "⚠️ NoticeComment 모델 의존성 미확인\n";
    
    // 4. API 응답 형식 테스트 (ResponseHelper 사용 확인)
    echo "\n--- 4. API 응답 형식 테스트 ---\n";
    
    // 출력 버퍼링 시작 (실제 응답 캡처용)
    ob_start();
    
    // 로그인하지 않은 상태에서 댓글 작성 시도 (401 응답 기대)
    try {
        // 세션 초기화 (로그인하지 않은 상태 시뮬레이션)
        unset($_SESSION['user_id']);
        
        $commentController->store();
        $output = ob_get_clean();
        
        // JSON 응답인지 확인
        $response = json_decode($output, true);
        if ($response && isset($response['success']) && $response['success'] === false) {
            echo "✅ 인증 에러 응답 형식 올바름\n";
            echo "- 메시지: {$response['message']}\n";
        } else {
            echo "⚠️ API 응답 형식 확인 필요\n";
            echo "- 응답: " . substr($output, 0, 100) . "...\n";
        }
        
    } catch (Exception $e) {
        ob_end_clean(); // 버퍼 정리
        echo "✅ 예상된 인증 에러 발생: " . substr($e->getMessage(), 0, 50) . "...\n";
    }
    
    // 5. 에러 처리 테스트
    echo "\n--- 5. 에러 처리 테스트 ---\n";
    
    // 잘못된 ID로 공지사항 조회 시도
    ob_start();
    try {
        $noticeController->show(999999); // 존재하지 않는 ID
        $output = ob_get_clean();
        
        // 404 응답이나 적절한 에러 처리 확인
        if (strpos($output, '404') !== false || strpos($output, 'Not Found') !== false) {
            echo "✅ 존재하지 않는 공지사항 에러 처리 적절함\n";
        } else {
            echo "⚠️ 에러 처리 확인 필요\n";
        }
        
    } catch (Exception $e) {
        ob_end_clean(); // 버퍼 정리
        echo "✅ 예상된 에러 처리: " . substr($e->getMessage(), 0, 50) . "...\n";
    }
    
    // 6. 헬퍼 클래스 의존성 확인
    echo "\n--- 6. 헬퍼 클래스 의존성 확인 ---\n";
    
    $helperClasses = [
        'ResponseHelper' => SRC_PATH . '/helpers/ResponseHelper.php',
        'ValidationHelper' => SRC_PATH . '/helpers/ValidationHelper.php',
        'HtmlSanitizerHelper' => SRC_PATH . '/helpers/HtmlSanitizerHelper.php',
        'AuthMiddleware' => SRC_PATH . '/middlewares/AuthMiddleware.php'
    ];
    
    foreach ($helperClasses as $className => $filePath) {
        if (file_exists($filePath)) {
            echo "✅ {$className} 파일 존재\n";
            
            try {
                require_once $filePath;
                if (class_exists($className)) {
                    echo "✅ {$className} 클래스 로딩 성공\n";
                } else {
                    echo "❌ {$className} 클래스 로딩 실패\n";
                }
            } catch (Exception $e) {
                echo "❌ {$className} 로딩 중 오류: " . $e->getMessage() . "\n";
            }
        } else {
            echo "❌ {$className} 파일 없음\n";
        }
    }
    
    // 7. 권한 검증 시스템 테스트
    echo "\n--- 7. 권한 검증 시스템 테스트 ---\n";
    
    // AuthMiddleware 메서드들 확인
    if (class_exists('AuthMiddleware')) {
        $authMethods = ['isLoggedIn', 'getCurrentUserId'];
        foreach ($authMethods as $method) {
            if (method_exists('AuthMiddleware', $method)) {
                echo "✅ AuthMiddleware::{$method}() 메서드 존재\n";
                
                // 실제 호출 테스트 (static 메서드)
                try {
                    $result = AuthMiddleware::$method();
                    echo "- 현재 결과: " . ($result === null ? 'null' : ($result === false ? 'false' : $result)) . "\n";
                } catch (Exception $e) {
                    echo "- 호출 중 오류: " . $e->getMessage() . "\n";
                }
            } else {
                echo "❌ AuthMiddleware::{$method}() 메서드 없음\n";
            }
        }
    }
    
    // 8. 데이터베이스 연결 테스트 (컨트롤러 관점)
    echo "\n--- 8. 데이터베이스 연결 테스트 (컨트롤러 관점) ---\n";
    
    try {
        // Notice 모델을 통한 데이터베이스 연결 확인
        $noticeModel = new Notice();
        $totalCount = $noticeModel->getTotalCount();
        echo "✅ 데이터베이스 연결 정상 (총 공지사항: {$totalCount}개)\n";
        
        // NoticeComment 모델을 통한 연결 확인
        $commentModel = new NoticeComment();
        $recentComments = $commentModel->getRecentComments(1);
        echo "✅ 댓글 데이터베이스 연결 정상 (최근 댓글: " . count($recentComments) . "개)\n";
        
    } catch (Exception $e) {
        echo "❌ 데이터베이스 연결 오류: " . $e->getMessage() . "\n";
    }
    
    // 9. 로깅 시스템 확인
    echo "\n--- 9. 로깅 시스템 확인 ---\n";
    
    if (class_exists('WebLogger')) {
        echo "✅ WebLogger 클래스 존재\n";
        
        try {
            WebLogger::info("컨트롤러 테스트 - 로깅 시스템 확인");
            echo "✅ 로깅 시스템 정상 작동\n";
        } catch (Exception $e) {
            echo "❌ 로깅 시스템 오류: " . $e->getMessage() . "\n";
        }
    } else {
        echo "⚠️ WebLogger 클래스 미확인\n";
    }
    
    // 10. 캐시 시스템 확인
    echo "\n--- 10. 캐시 시스템 확인 ---\n";
    
    if (class_exists('CacheHelper')) {
        echo "✅ CacheHelper 클래스 존재\n";
        
        try {
            // 간단한 캐시 테스트
            $testKey = 'controller_test_' . time();
            $testValue = 'test_cache_value';
            
            $cached = CacheHelper::remember($testKey, function() use ($testValue) {
                return $testValue;
            }, 60);
            
            if ($cached === $testValue) {
                echo "✅ 캐시 시스템 정상 작동\n";
            } else {
                echo "⚠️ 캐시 시스템 확인 필요\n";
            }
        } catch (Exception $e) {
            echo "❌ 캐시 시스템 오류: " . $e->getMessage() . "\n";
        }
    } else {
        echo "⚠️ CacheHelper 클래스 미확인\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "🎉 컨트롤러 테스트 완료!\n";
    
} catch (Exception $e) {
    echo "❌ 테스트 중 오류 발생: " . $e->getMessage() . "\n";
    echo "스택 추적:\n" . $e->getTraceAsString() . "\n";
} finally {
    // 출력 버퍼 정리
    while (ob_get_level()) {
        ob_end_clean();
    }
}
?>