<?php
/**
 * 탑마케팅 라우팅 설정 파일
 */

/**
 * 라우팅 클래스
 * 요청 URL을 컨트롤러와 액션에 매핑합니다.
 */
class Router {
    private $routes = [];
    
    /**
     * 생성자 - 라우트 정의
     */
    public function __construct() {
        // 인증 관련 라우트
        $this->routes = [
            // 기본 라우트
            'GET:/' => ['HomeController', 'index'],
            
            // 인증 라우트
            'GET:/auth/login' => ['AuthController', 'showLogin'],
            'POST:/auth/login' => ['AuthController', 'login'],
            'GET:/auth/signup' => ['AuthController', 'showSignup'],
            'POST:/auth/signup' => ['AuthController', 'signup'],
            'GET:/auth/logout' => ['AuthController', 'logout'],
            'POST:/auth/logout' => ['AuthController', 'logout'],
            'GET:/auth/forgot-password' => ['AuthController', 'showForgotPassword'],
            'POST:/auth/forgot-password' => ['AuthController', 'forgotPassword'],
            'GET:/auth/reset-password' => ['AuthController', 'showResetPassword'],
            'POST:/auth/reset-password' => ['AuthController', 'resetPassword'],
            
            // 휴대폰 인증 라우트
            'POST:/auth/send-verification' => ['AuthController', 'sendVerification'],
            'POST:/auth/verify-code' => ['AuthController', 'verifyCode'],
            'POST:/auth/verify-reset-code' => ['AuthController', 'verifyResetCode'],
            
            // JWT API 라우트
            'POST:/auth/refresh' => ['AuthController', 'refreshToken'],
            'GET:/auth/me' => ['AuthController', 'me'],
            
            // 실시간 중복 검사 API 라우트
            'POST:/auth/check-nickname' => ['AuthController', 'checkNickname'],
            'POST:/auth/check-phone' => ['AuthController', 'checkPhone'],
            
            // 법적 문서 라우트
            'GET:/terms' => ['LegalController', 'showTerms'],
            'GET:/privacy' => ['LegalController', 'showPrivacy'],
            
            // 사용자 라우트 (기존)
            'GET:/users/me' => ['UserController', 'showProfile'],
            'GET:/users/{id}' => ['UserController', 'getUser'],
            'PUT:/users/{id}' => ['UserController', 'updateUser'],
            'DELETE:/users/{id}' => ['UserController', 'deleteUser'],
            
            // 프로필 라우트 (새로 추가)
            'GET:/profile' => ['UserController', 'showMyProfile'],
            'GET:/profile/edit' => ['UserController', 'showEditProfile'],
            'POST:/profile/update' => ['UserController', 'updateProfile'],
            'POST:/profile/upload-image' => ['UserController', 'uploadProfileImage'],
            'GET:/profile/{nickname}' => ['UserController', 'showPublicProfile'],
            
            // 커뮤니티 게시판 라우트
            'GET:/community' => ['CommunityController', 'index'],
            'GET:/community/posts/{id}' => ['CommunityController', 'show'],
            'GET:/community/write' => ['CommunityController', 'showWrite'],
            'POST:/community/posts' => ['CommunityController', 'create'],
            'GET:/community/posts/{id}/edit' => ['CommunityController', 'showEdit'],
            'PUT:/community/posts/{id}' => ['CommunityController', 'update'],
            'DELETE:/community/posts/{id}' => ['CommunityController', 'delete'],
            
            // 기존 게시글 라우트 (호환성 유지)
            'GET:/posts' => ['PostController', 'index'],
            'GET:/posts/{id}' => ['PostController', 'show'],
            'POST:/posts' => ['PostController', 'create'],
            'PUT:/posts/{id}' => ['PostController', 'update'],
            'DELETE:/posts/{id}' => ['PostController', 'delete'],
            
            // 댓글 라우트
            'POST:/api/comments' => ['CommentController', 'store'],
            'PUT:/api/comments/{id}' => ['CommentController', 'update'],
            'DELETE:/api/comments/{id}' => ['CommentController', 'delete'],
            'GET:/api/comments' => ['CommentController', 'list'],
            
            // 좋아요 라우트
            'POST:/api/posts/{id}/like' => ['LikeController', 'togglePostLike'],
            'GET:/api/posts/{id}/like' => ['LikeController', 'getPostLikeStatus'],
            
            // 미디어 업로드 라우트
            'POST:/api/media/upload-image' => ['MediaController', 'uploadImage'],
            'POST:/api/upload-event-image' => ['EventController', 'uploadEventImage'],
            
            // 사용자 프로필 이미지 API
            'GET:/api/users/{id}/profile-image' => ['UserController', 'getProfileImage'],

            // 회원 탈퇴 API
            'POST:/api/user/delete-account' => ['UserController', 'deleteAccount'],

            // Geocoding API (제거됨 - 클라이언트 사이드로 변경)
            
            // 강의 일정 라우트
            'GET:/lectures' => ['LectureController', 'index'],
            'GET:/lectures/{id}' => ['LectureController', 'show'],
            'GET:/lectures/create' => ['LectureController', 'create'],
            'POST:/lectures/store' => ['LectureController', 'store'],
            'GET:/lectures/{id}/edit' => ['LectureController', 'edit'],
            'POST:/lectures/{id}/update' => ['LectureController', 'update'],
            'PUT:/lectures/{id}/update' => ['LectureController', 'update'],
            'POST:/lectures/{id}/delete' => ['LectureController', 'delete'],
            // 구식 신청 라우트 제거됨 (모달 기반 신청 시스템 사용)
            'GET:/lectures/{id}/register' => ['LectureController', 'redirectToLecture'],
            'POST:/lectures/update-images' => ['LectureController', 'updateImages'],
            'GET:/lectures/{id}/ical' => ['LectureController', 'generateICal'],
            
            // 강의 신청 관리 API 라우트
            'GET:/api/lectures/{id}/registration-status' => ['RegistrationController', 'getRegistrationStatus'],
            'POST:/api/lectures/{id}/registration' => ['RegistrationController', 'createRegistration'],
            'DELETE:/api/lectures/{id}/registration' => ['RegistrationController', 'cancelRegistration'],

            // 강의 수정 가능 여부 체크 API
            'GET:/api/lectures/{id}/check-editable' => ['LectureController', 'checkEditable'],
            'GET:/api/lectures/{id}/previous-registration' => ['RegistrationController', 'getPreviousRegistration'],
            
            // 기업 신청 관리 대시보드 라우트
            'GET:/registrations' => ['RegistrationDashboardController', 'index'],
            'GET:/registrations/lectures/{id}' => ['RegistrationDashboardController', 'lectureRegistrations'],
            'GET:/registrations/events/{id}' => ['RegistrationDashboardController', 'lectureRegistrations'],
            'POST:/api/registrations/{id}/status' => ['RegistrationDashboardController', 'updateRegistrationStatus'],
            
            // 행사 일정 라우트
            'GET:/events' => ['EventController', 'index'],
            'GET:/events/detail' => ['EventController', 'detail'],
            'GET:/events/create' => ['EventController', 'create'],
            'POST:/events/store' => ['EventController', 'store'],
            'GET:/events/{id}/edit' => ['EventController', 'edit'],
            'POST:/events/{id}/update' => ['EventController', 'update'],
            'POST:/events/{id}/delete' => ['EventController', 'delete'],
            'GET:/events/{id}/ical' => ['EventController', 'generateICal'],
            
            // 행사 신청 관리 API 라우트 (새로운 event_registrations 테이블 사용)
            'GET:/api/events/{id}/registration-status' => ['EventController', 'registrationStatus'],
            'POST:/api/events/{id}/registration' => ['EventController', 'registerEvent'],
            'DELETE:/api/events/{id}/registration' => ['EventController', 'cancelEventRegistration'],
            'GET:/api/events/{id}/previous-registration' => ['EventController', 'getPreviousRegistration'],

            // 수정 가능 여부 체크 API
            'GET:/api/events/{id}/check-editable' => ['EventController', 'checkEditable'],
            
            // 신청 대기 알림 API
            'GET:/api/registrations/pending-count' => ['RegistrationNotificationController', 'getPendingCount'],
            
            // 채팅 라우트
            'GET:/chat' => ['ChatController', 'index'],
            'GET:/chat/rooms' => ['ChatController', 'getRooms'],
            'POST:/chat/rooms' => ['ChatController', 'createRoom'],
            'GET:/chat/search-users' => ['ChatController', 'searchUsers'],
            'GET:/chat/firebase-token' => ['ChatController', 'getFirebaseToken'],

            // 알림 설정 라우트
            'GET:/notifications/settings' => ['NotificationSettingsController', 'index'],
            'GET:/api/notifications/settings' => ['NotificationSettingsController', 'getSettings'],
            'PUT:/api/notifications/settings' => ['NotificationSettingsController', 'updateSettings'],
            'POST:/api/notifications/toggle-all' => ['NotificationSettingsController', 'toggleAll'],
            'GET:/api/notifications/check/{type}/{id}' => ['NotificationSettingsController', 'checkNotification'],

            // 공지사항 라우트
            'GET:/notices' => ['NoticeController', 'index'],
            'GET:/notices/write' => ['NoticeController', 'showWrite'],
            'GET:/notices/{id}' => ['NoticeController', 'show'],
            'GET:/notices/{id}/edit' => ['NoticeController', 'showEdit'],
            'POST:/api/notices' => ['NoticeController', 'create'],
            'POST:/api/notices/{id}' => ['NoticeController', 'update'], // FormData 호환성을 위한 POST 추가
            'PUT:/api/notices/{id}' => ['NoticeController', 'update'],
            'DELETE:/api/notices/{id}' => ['NoticeController', 'delete'],
            'POST:/api/notices/{id}/view' => ['NoticeController', 'incrementView'],
            
            // 공지사항 댓글 라우트
            'POST:/api/notice-comments' => ['NoticeCommentController', 'store'],
            'PUT:/api/notice-comments/{id}' => ['NoticeCommentController', 'update'],
            'DELETE:/api/notice-comments/{id}' => ['NoticeCommentController', 'delete'],
            'GET:/api/notice-comments' => ['NoticeCommentController', 'list'],
            
            // 기업회원 라우트
            'GET:/corp/info' => ['CorporateController', 'info'],
            'GET:/corp/apply' => ['CorporateController', 'apply'],
            'POST:/corp/apply' => ['CorporateController', 'apply'],
            'GET:/corp/status' => ['CorporateController', 'status'],
            'GET:/corp/edit' => ['CorporateController', 'edit'],
            'POST:/corp/edit' => ['CorporateController', 'edit'],
            
            // 관리자 라우트
            'GET:/admin' => ['AdminController', 'dashboard'],
            'GET:/admin/corporate/pending' => ['AdminController', 'corporatePending'],
            'GET:/admin/corporate/list' => ['AdminController', 'corporateList'],
            'POST:/admin/corporate/process' => ['AdminController', 'corporateProcess'],
            'POST:/admin/corporate/detail' => ['AdminController', 'corporateApplicationDetail'],
            'GET:/admin/document/view' => ['AdminController', 'viewDocument'],
            'POST:/admin/corporate/manage' => ['AdminController', 'manageCorporateMember'],
            
            // 사용자 관리 라우트
            'GET:/admin/users' => ['AdminController', 'userList'],
            'GET:/admin/users/data' => ['AdminController', 'getUsersData'],
            'GET:/admin/users/{id}/detail' => ['AdminController', 'getUserDetail'],
            'POST:/admin/users/{id}/status' => ['AdminController', 'updateUserStatus'],
            'POST:/admin/users/{id}/role' => ['AdminController', 'updateUserRole'],
            'POST:/admin/users/{id}/edit' => ['AdminController', 'editUser'],
            'POST:/admin/users/bulk-action' => ['AdminController', 'bulkUserAction'],
            'POST:/admin/users/{id}/notify' => ['AdminController', 'notifyUser'],
            'GET:/admin/users/export' => ['AdminController', 'exportUsers'],
            'GET:/admin/users/stats' => ['AdminController', 'getUserStats'],
            'GET:/admin/getUserStats' => ['AdminController', 'getUserStats'],
            
            // 요가 랜딩페이지 테스트 라우트
            'GET:/yoga-landing' => ['YogaController', 'landing'],
            
            // Tvelia 여행사 웹사이트 라우트
            'GET:/tvelia-travel' => ['TveliaController', 'landing'],
            
            // 테스트 라우트
            'GET:/test1' => ['TestController', 'test1'],
            
            // Sample 페이지 라우트 (불매 알림 페이지) - 비활성화됨
            // 'GET:/sample' => ['SampleController', 'index'],
        ];
    }
    
    /**
     * 요청 디스패치
     * URL 경로와 HTTP 메서드를 기반으로 적절한 컨트롤러와 액션 호출
     */
    public function dispatch() {
        // API 강의 신청 요청 디버깅
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['REQUEST_URI'], '/api/lectures/') !== false && strpos($_SERVER['REQUEST_URI'], '/registration') !== false) {
            error_log("=== ROUTER 디스패치 시작 ===");
            error_log("라우터에서 받은 URI: " . $_SERVER['REQUEST_URI']);
            error_log("라우터에서 받은 METHOD: " . $_SERVER['REQUEST_METHOD']);
        }
        // 웹 서버가 아닌 환경에서 실행되는 경우 기본값 설정
        if (!isset($_SERVER['REQUEST_URI'])) {
            $_SERVER['REQUEST_URI'] = '/';
        }
        if (!isset($_SERVER['REQUEST_METHOD'])) {
            $_SERVER['REQUEST_METHOD'] = 'GET';
        }
        
        // 요청 URI 파싱 및 URL 디코딩
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = urldecode($uri); // URL 디코딩 추가
        $uri = rtrim($uri, '/');
        if (empty($uri)) {
            $uri = '/';
        }
        
        // 🔧 GET 파라미터 보존: $_GET 슈퍼글로벌에 쿼리 파라미터 설정
        $queryString = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
        if ($queryString) {
            parse_str($queryString, $_GET);
            
            // 디버깅: GET 파라미터 확인
            if (class_exists('WebLogger')) {
                WebLogger::debug('라우터에서 GET 파라미터 복원', [
                    'query_string' => $queryString,
                    'parsed_get' => $_GET
                ]);
            }
        }
        
        // HTTP 메서드 가져오기
        $method = $_SERVER['REQUEST_METHOD'];
        
        // 라우트 키 생성
        $routeKey = $method . ':' . $uri;
        
        // API 강의 신청 요청 디버깅
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['REQUEST_URI'], '/api/lectures/') !== false && strpos($_SERVER['REQUEST_URI'], '/registration') !== false) {
            error_log("생성된 라우트 키: " . $routeKey);
            error_log("사용 가능한 라우트들:");
            foreach ($this->routes as $key => $route) {
                if (strpos($key, 'registration') !== false) {
                    error_log("  - " . $key . " => " . $route[0] . "::" . $route[1]);
                }
            }
        }
        
        // 정적 라우트 먼저 검색
        if (isset($this->routes[$routeKey])) {
            // API 강의 신청 요청 디버깅
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['REQUEST_URI'], '/api/lectures/') !== false && strpos($_SERVER['REQUEST_URI'], '/registration') !== false) {
                error_log("✅ 정적 라우트 매치: " . $routeKey);
                error_log("실행할 컨트롤러: " . $this->routes[$routeKey][0] . "::" . $this->routes[$routeKey][1]);
            }
            $this->executeRoute($this->routes[$routeKey]);
            return;
        }
        
        // 동적 라우트 검색
        foreach ($this->routes as $pattern => $route) {
            if ($this->matchDynamicRoute($pattern, $routeKey)) {
                // API 강의 신청 요청 디버깅
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['REQUEST_URI'], '/api/lectures/') !== false && strpos($_SERVER['REQUEST_URI'], '/registration') !== false) {
                    error_log("✅ 동적 라우트 매치: " . $pattern);
                    error_log("실행할 컨트롤러: " . $route[0] . "::" . $route[1]);
                }
                $this->executeRoute($route);
                return;
            }
        }
        
        // 매칭되는 라우트가 없으면 404 페이지 표시
        // API 강의 신청 요청 디버깅
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['REQUEST_URI'], '/api/lectures/') !== false && strpos($_SERVER['REQUEST_URI'], '/registration') !== false) {
            error_log("❌ 매칭되는 라우트가 없음!");
            error_log("찾으려고 한 라우트: " . $routeKey);
        }
        
        header('HTTP/1.1 404 Not Found');
        $this->show404();
    }
    
    /**
     * 동적 라우트 매칭
     */
    private function matchDynamicRoute($pattern, $requestRoute) {
        // 닉네임 라우트 특별 처리
        if (strpos($pattern, '{nickname}') !== false) {
            $regexPattern = preg_replace('/\{nickname\}/', '([^\/]+)', $pattern);
        } else {
            // {id} 패턴을 정규식으로 변환 (숫자만)
            $regexPattern = preg_replace('/\{[^}]+\}/', '(\d+)', $pattern);
        }
        
        $regexPattern = '#^' . str_replace('/', '\/', $regexPattern) . '$#u';
        
        return preg_match($regexPattern, $requestRoute);
    }
    
    /**
     * 라우트 실행
     */
    private function executeRoute($route) {
        list($controllerName, $action) = $route;
        $controllerPath = SRC_PATH . '/controllers/' . $controllerName . '.php';
        
        // API 강의 신청 요청 디버깅
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['REQUEST_URI'], '/api/lectures/') !== false && strpos($_SERVER['REQUEST_URI'], '/registration') !== false) {
            error_log("=== 라우트 실행 시작 ===");
            error_log("컨트롤러명: " . $controllerName);
            error_log("액션명: " . $action);
            error_log("컨트롤러 파일 경로: " . $controllerPath);
            error_log("파일 존재 여부: " . (file_exists($controllerPath) ? 'YES' : 'NO'));
        }
        
        // 라우트 실행 로깅
        WebLogger::info('=== ROUTE EXECUTION ===', [
            'controller' => $controllerName,
            'action' => $action,
            'path' => $controllerPath,
            'cwd' => getcwd(),
            'file' => __FILE__
        ]);
        
        if (file_exists($controllerPath)) {
            WebLogger::info('Controller file exists', ['path' => $controllerPath]);
            require_once $controllerPath;
            if (class_exists($controllerName)) {
                WebLogger::info('Controller class exists', ['class' => $controllerName]);
                $controller = new $controllerName();
                if (method_exists($controller, $action)) {
                    WebLogger::info('Method exists, calling action', ['action' => $action]);
                    // 동적 라우트에서 파라미터 추출
                    $params = $this->extractRouteParams();
                    
                    // API 강의 신청 디버깅
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['REQUEST_URI'], '/api/lectures/') !== false && strpos($_SERVER['REQUEST_URI'], '/registration') !== false) {
                        error_log("🔍 추출된 파라미터: " . json_encode($params));
                        error_log("🔍 파라미터 개수: " . count($params));
                    }
                    
                    if (!empty($params)) {
                        WebLogger::info('Calling action with params', ['action' => $action, 'params' => $params]);
                        
                        // 🚨 강제 예외 처리 추가
                        try {
                            $controller->$action(...$params);
                        } catch (Exception $e) {
                            WebLogger::error('Exception in action', [
                                'action' => $action,
                                'message' => $e->getMessage(),
                                'file' => $e->getFile(),
                                'line' => $e->getLine()
                            ]);
                            throw $e;
                        } catch (Error $e) {
                            WebLogger::critical('Fatal Error in action', [
                                'action' => $action,
                                'message' => $e->getMessage(),
                                'file' => $e->getFile(),
                                'line' => $e->getLine()
                            ]);
                            throw $e;
                        }
                    } else {
                        WebLogger::info('Calling action without params', ['action' => $action]);

                        // 🚨 강제 예외 처리 추가
                        try {
                            $controller->$action();
                        } catch (Exception $e) {
                            WebLogger::error('Exception in action (no params)', [
                                'action' => $action,
                                'message' => $e->getMessage(),
                                'file' => $e->getFile(),
                                'line' => $e->getLine()
                            ]);
                            throw $e;
                        } catch (Error $e) {
                            WebLogger::critical('Fatal Error in action (no params)', [
                                'action' => $action,
                                'message' => $e->getMessage(),
                                'file' => $e->getFile(),
                                'line' => $e->getLine()
                            ]);
                            throw $e;
                        }
                    }
                    return;
                } else {
                    WebLogger::error('Method not found in controller', [
                        'controller' => $controllerName,
                        'action' => $action
                    ]);
                }
            } else {
                WebLogger::error('Controller class not found', [
                    'controller' => $controllerName
                ]);
            }
        } else {
            WebLogger::error('Controller file not found', [
                'path' => $controllerPath
            ]);
        }
        
        // 컨트롤러 실행 실패 시 500 에러
        header('HTTP/1.1 500 Internal Server Error');
        echo '<h1>500 Internal Server Error</h1>';
        echo '<p>An error occurred while processing your request.</p>';
    }
    
    /**
     * 라우트 파라미터 추출
     */
    private function extractRouteParams() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = rtrim($uri, '/');
        if (empty($uri)) {
            $uri = '/';
        }
        
        $method = $_SERVER['REQUEST_METHOD'];
        $routeKey = $method . ':' . $uri;
        
        foreach ($this->routes as $pattern => $route) {
            // 닉네임 라우트 특별 처리
            if (strpos($pattern, '{nickname}') !== false) {
                $regexPattern = preg_replace('/\{nickname\}/', '([^\/]+)', $pattern);
            } else {
                $regexPattern = preg_replace('/\{[^}]+\}/', '(\d+)', $pattern);
            }
            
            $regexPattern = '#^' . str_replace('/', '\/', $regexPattern) . '$#';
            
            if (preg_match($regexPattern, $routeKey, $matches)) {
                // 첫 번째 매치는 전체 문자열이므로 제거
                array_shift($matches);
                
                // URL 디코딩 처리 (한국어 닉네임 지원)
                $decodedParams = [];
                foreach ($matches as $param) {
                    $decodedParams[] = urldecode($param);
                }
                
                return $decodedParams;
            }
        }
        
        return [];
    }
    
    /**
     * 404 페이지 표시
     */
    private function show404() {
        $templatePath = SRC_PATH . '/views/templates/404.php';
        if (file_exists($templatePath)) {
            include $templatePath;
        } else {
            echo '<h1>404 - Page Not Found</h1>';
            echo '<p>The requested page could not be found.</p>';
        }
    }
} 