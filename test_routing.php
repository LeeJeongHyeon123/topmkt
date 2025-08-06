<?php
/**
 * 라우팅 시스템 테스트 스크립트
 */

// 설정
define('SRC_PATH', __DIR__ . '/src');
require_once SRC_PATH . '/config/routes.php';

echo "🧪 라우팅 시스템 테스트 시작\n";
echo str_repeat("=", 50) . "\n";

try {
    // 라우터 인스턴스 생성
    $router = new Router();
    echo "✅ Router 인스턴스 생성 성공\n";
    
    // 라우터의 routes 프로퍼티에 리플렉션으로 접근
    $reflection = new ReflectionClass($router);
    $routesProperty = $reflection->getProperty('routes');
    $routesProperty->setAccessible(true);
    $routes = $routesProperty->getValue($router);
    
    echo "\n--- 1. 공지사항 관련 라우트 확인 ---\n";
    $noticeRoutes = array_filter($routes, function($route, $key) {
        return strpos($key, 'notices') !== false || strpos($key, 'notice-comments') !== false;
    }, ARRAY_FILTER_USE_BOTH);
    
    echo "공지사항 관련 라우트 수: " . count($noticeRoutes) . "개\n";
    foreach ($noticeRoutes as $route => $controller) {
        echo "- {$route} => [{$controller[0]}, {$controller[1]}]\n";
    }
    
    // 2. 라우트 매칭 테스트
    echo "\n--- 2. 라우트 매칭 테스트 ---\n";
    
    // 테스트할 URL 패턴들
    $testUrls = [
        'GET:/notices' => 'NoticeController::index',
        'GET:/notices/write' => 'NoticeController::showWrite', 
        'GET:/notices/123' => 'NoticeController::show',
        'GET:/notices/123/edit' => 'NoticeController::showEdit',
        'POST:/api/notices' => 'NoticeController::create',
        'PUT:/api/notices/123' => 'NoticeController::update',
        'DELETE:/api/notices/123' => 'NoticeController::delete',
        'POST:/api/notice-comments' => 'NoticeCommentController::store',
        'PUT:/api/notice-comments/123' => 'NoticeCommentController::update',
        'DELETE:/api/notice-comments/123' => 'NoticeCommentController::delete',
        'GET:/api/notice-comments' => 'NoticeCommentController::list'
    ];
    
    foreach ($testUrls as $url => $expectedController) {
        list($method, $path) = explode(':', $url, 2);
        
        // 라우트 키 확인
        if (isset($routes[$url])) {
            $controller = $routes[$url];
            $controllerName = $controller[0] . '::' . $controller[1];
            $match = ($controllerName === $expectedController);
            echo ($match ? "✅" : "❌") . " {$url} => {$controllerName}" . 
                 ($match ? "" : " (예상: {$expectedController})") . "\n";
        } else {
            // 동적 라우트 패턴 확인 (예: {id} 파라미터가 있는 경우)
            $dynamicMatch = false;
            $matchedRoute = '';
            
            foreach ($routes as $routePattern => $controller) {
                if (strpos($routePattern, '{id}') !== false) {
                    // {id}를 숫자로 치환하여 패턴 매칭
                    $testPattern = str_replace('{id}', '123', $routePattern);
                    if ($testPattern === $url) {
                        $controllerName = $controller[0] . '::' . $controller[1];
                        $match = ($controllerName === $expectedController);
                        echo ($match ? "✅" : "❌") . " {$url} => {$controllerName} (매칭: {$routePattern})" . 
                             ($match ? "" : " (예상: {$expectedController})") . "\n";
                        $dynamicMatch = true;
                        break;
                    }
                }
            }
            
            if (!$dynamicMatch) {
                echo "❌ {$url} => 매칭되는 라우트 없음\n";
            }
        }
    }
    
    // 3. 컨트롤러 클래스 존재 확인
    echo "\n--- 3. 컨트롤러 클래스 존재 확인 ---\n";
    $controllerClasses = ['NoticeController', 'NoticeCommentController'];
    
    foreach ($controllerClasses as $className) {
        $filePath = SRC_PATH . '/controllers/' . $className . '.php';
        if (file_exists($filePath)) {
            echo "✅ {$className} 파일 존재: {$filePath}\n";
            
            // 클래스 로딩 시도
            try {
                require_once $filePath;
                if (class_exists($className)) {
                    echo "✅ {$className} 클래스 로딩 성공\n";
                    
                    // 클래스 메서드 확인
                    $reflection = new ReflectionClass($className);
                    $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
                    $publicMethods = array_map(function($method) {
                        return $method->name;
                    }, array_filter($methods, function($method) {
                        return !$method->isConstructor();
                    }));
                    
                    echo "- 공개 메서드: " . implode(', ', $publicMethods) . "\n";
                } else {
                    echo "❌ {$className} 클래스 로딩 실패\n";
                }
            } catch (Exception $e) {
                echo "❌ {$className} 로딩 중 오류: " . $e->getMessage() . "\n";
            }
        } else {
            echo "❌ {$className} 파일 없음: {$filePath}\n";
        }
    }
    
    // 4. 모델 의존성 확인
    echo "\n--- 4. 모델 의존성 확인 ---\n";
    $modelClasses = ['Notice', 'NoticeComment'];
    
    foreach ($modelClasses as $className) {
        $filePath = SRC_PATH . '/models/' . $className . '.php';
        if (file_exists($filePath)) {
            echo "✅ {$className} 모델 파일 존재\n";
            
            try {
                require_once $filePath;
                if (class_exists($className)) {
                    echo "✅ {$className} 모델 클래스 로딩 성공\n";
                } else {
                    echo "❌ {$className} 모델 클래스 로딩 실패\n";
                }
            } catch (Exception $e) {
                echo "❌ {$className} 모델 로딩 중 오류: " . $e->getMessage() . "\n";
            }
        } else {
            echo "❌ {$className} 모델 파일 없음\n";
        }
    }
    
    // 5. 라우팅 충돌 확인
    echo "\n--- 5. 라우팅 충돌 확인 ---\n";
    $conflictRoutes = [];
    $routePaths = array_keys($routes);
    
    // 기존 community 라우트와 충돌 확인
    $communityRoutes = array_filter($routePaths, function($route) {
        return strpos($route, '/community') !== false;
    });
    
    $noticeRoutePaths = array_filter($routePaths, function($route) {
        return strpos($route, '/notices') !== false;
    });
    
    echo "기존 community 라우트 수: " . count($communityRoutes) . "개\n";
    echo "새로운 notices 라우트 수: " . count($noticeRoutePaths) . "개\n";
    
    // 유사한 패턴 확인
    foreach ($communityRoutes as $communityRoute) {
        $communityPath = preg_replace('/^[A-Z]+:/', '', $communityRoute);
        foreach ($noticeRoutePaths as $noticeRoute) {
            $noticePath = preg_replace('/^[A-Z]+:/', '', $noticeRoute);
            
            // 경로 유사성 검사
            $communitySegments = explode('/', trim($communityPath, '/'));
            $noticeSegments = explode('/', trim($noticePath, '/'));
            
            if (count($communitySegments) === count($noticeSegments)) {
                $similarity = 0;
                for ($i = 0; $i < count($communitySegments); $i++) {
                    if ($communitySegments[$i] === $noticeSegments[$i] || 
                        (strpos($communitySegments[$i], '{') !== false && strpos($noticeSegments[$i], '{') !== false)) {
                        $similarity++;
                    }
                }
                
                // 75% 이상 유사하면 잠재적 충돌 가능성
                if ($similarity / count($communitySegments) >= 0.75 && $communityPath !== $noticePath) {
                    $conflictRoutes[] = [
                        'community' => $communityRoute,
                        'notice' => $noticeRoute,
                        'similarity' => round($similarity / count($communitySegments) * 100, 1)
                    ];
                }
            }
        }
    }
    
    if (empty($conflictRoutes)) {
        echo "✅ 라우팅 충돌 없음 - 안전함\n";
    } else {
        echo "⚠️ 잠재적 라우팅 충돌 발견:\n";
        foreach ($conflictRoutes as $conflict) {
            echo "- {$conflict['community']} ↔ {$conflict['notice']} (유사도: {$conflict['similarity']}%)\n";
        }
    }
    
    // 6. 라우팅 순서 확인
    echo "\n--- 6. 라우팅 순서 확인 ---\n";
    $routeOrder = array_keys($routes);
    $noticeRouteIndices = [];
    $communityRouteIndices = [];
    
    foreach ($routeOrder as $index => $route) {
        if (strpos($route, '/notices') !== false) {
            $noticeRouteIndices[] = $index;
        } elseif (strpos($route, '/community') !== false) {
            $communityRouteIndices[] = $index;
        }
    }
    
    if (!empty($noticeRouteIndices) && !empty($communityRouteIndices)) {
        $avgNoticeIndex = array_sum($noticeRouteIndices) / count($noticeRouteIndices);
        $avgCommunityIndex = array_sum($communityRouteIndices) / count($communityRouteIndices);
        
        echo "평균 community 라우트 위치: " . round($avgCommunityIndex) . "\n";
        echo "평균 notices 라우트 위치: " . round($avgNoticeIndex) . "\n";
        
        if ($avgNoticeIndex > $avgCommunityIndex) {
            echo "✅ 라우팅 순서 적절함 (notices가 community 뒤에 위치)\n";
        } else {
            echo "⚠️ 라우팅 순서 주의 필요\n";
        }
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "🎉 라우팅 시스템 테스트 완료!\n";
    
} catch (Exception $e) {
    echo "❌ 테스트 중 오류 발생: " . $e->getMessage() . "\n";
    echo "스택 추적:\n" . $e->getTraceAsString() . "\n";
}
?>