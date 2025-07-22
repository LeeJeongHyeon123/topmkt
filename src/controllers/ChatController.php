<?php
/**
 * 채팅 컨트롤러
 * Firebase Realtime Database 기반 실시간 채팅 기능 관리
 */

require_once SRC_PATH . '/models/User.php';
require_once SRC_PATH . '/helpers/ResponseHelper.php';
require_once SRC_PATH . '/helpers/ValidationHelper.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';

class ChatController {
    private $userModel;
    
    public function __construct() {
        try {
            $this->userModel = new User();
        } catch (Exception $e) {
            error_log("ChatController 초기화 오류: " . $e->getMessage());
            
            // AJAX 요청인 경우 JSON 응답
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' ||
                strpos($_SERVER['REQUEST_URI'], '/firebase-token') !== false ||
                strpos($_SERVER['REQUEST_URI'], '/search-users') !== false) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => '서버 초기화 오류']);
                exit;
            }
            
            header('Location: /?error=initialization');
            exit;
        }
    }
    
    /**
     * 채팅 메인 페이지
     */
    public function index() {
        // 로그인 체크
        if (!AuthMiddleware::isLoggedIn()) {
            header('Location: /auth/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
        
        try {
            $currentUserId = AuthMiddleware::getCurrentUserId();
            $currentUser = $this->userModel->findById($currentUserId);
            
            if (!$currentUser) {
                throw new Exception("사용자 정보를 찾을 수 없습니다.");
            }
            
            // 뷰에 전달할 데이터
            $data = [
                'page_title' => '채팅',
                'page_description' => '실시간 채팅으로 다른 회원들과 소통하세요.',
                'current_user' => $currentUser,
                'current_user_id' => $currentUserId,
                'firebase_config' => $this->getFirebaseConfig()
            ];
            
            $this->render('chat/index', $data);
            
        } catch (Exception $e) {
            error_log("ChatController::index 오류: " . $e->getMessage());
            $this->showErrorPage("채팅을 불러오는 중 오류가 발생했습니다.");
        }
    }
    
    /**
     * 채팅방 목록 API
     */
    public function getRooms() {
        if (!AuthMiddleware::isLoggedIn()) {
            ResponseHelper::json(['success' => false, 'message' => '로그인이 필요합니다.'], 401);
            return;
        }
        
        try {
            $currentUserId = AuthMiddleware::getCurrentUserId();
            
            // Firebase에서 사용자가 참여한 채팅방 목록을 가져오는 것은
            // 프론트엔드에서 Firebase SDK를 통해 직접 처리
            ResponseHelper::json([
                'success' => true,
                'user_id' => $currentUserId
            ]);
            
        } catch (Exception $e) {
            error_log("ChatController::getRooms 오류: " . $e->getMessage());
            ResponseHelper::json(['success' => false, 'message' => '서버 오류가 발생했습니다.'], 500);
        }
    }
    
    /**
     * 채팅방 생성 API
     */
    public function createRoom() {
        if (!AuthMiddleware::isLoggedIn()) {
            ResponseHelper::json(['success' => false, 'message' => '로그인이 필요합니다.'], 401);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            ResponseHelper::json(['success' => false, 'message' => '잘못된 요청입니다.'], 400);
            return;
        }
        
        try {
            $currentUserId = AuthMiddleware::getCurrentUserId();
            $data = json_decode(file_get_contents('php://input'), true);
            
            // 유효성 검사
            if (empty($data['type'])) {
                ResponseHelper::json(['success' => false, 'message' => '채팅방 타입을 선택해주세요.'], 400);
                return;
            }
            
            $type = $data['type']; // 'private', 'group'
            $targetUserId = $data['target_user_id'] ?? null;
            $roomName = $data['room_name'] ?? null;
            
            if ($type === 'private' && !$targetUserId) {
                ResponseHelper::json(['success' => false, 'message' => '대화 상대를 선택해주세요.'], 400);
                return;
            }
            
            if ($type === 'group' && empty($roomName)) {
                ResponseHelper::json(['success' => false, 'message' => '채팅방 이름을 입력해주세요.'], 400);
                return;
            }
            
            // Firebase에서 채팅방 생성은 프론트엔드에서 처리
            // 여기서는 성공 응답만 반환
            ResponseHelper::json([
                'success' => true,
                'message' => '채팅방 생성 준비 완료',
                'data' => [
                    'creator_id' => $currentUserId,
                    'type' => $type,
                    'target_user_id' => $targetUserId,
                    'room_name' => $roomName
                ]
            ]);
            
        } catch (Exception $e) {
            error_log("ChatController::createRoom 오류: " . $e->getMessage());
            ResponseHelper::json(['success' => false, 'message' => '서버 오류가 발생했습니다.'], 500);
        }
    }
    
    /**
     * 사용자 검색 API (1:1 채팅용)
     */
    public function searchUsers() {
        if (!AuthMiddleware::isLoggedIn()) {
            ResponseHelper::json(['success' => false, 'message' => '로그인이 필요합니다.'], 401);
            return;
        }
        
        try {
            $currentUserId = AuthMiddleware::getCurrentUserId();
            $query = $_GET['q'] ?? '';
            
            error_log("채팅 사용자 검색: query={$query}, currentUserId={$currentUserId}");
            
            if (strlen($query) < 2) {
                ResponseHelper::json(['success' => false, 'message' => '검색어를 2글자 이상 입력해주세요.'], 400);
                return;
            }
            
            // 사용자 검색
            $users = $this->userModel->searchUsers($query, $currentUserId);
            
            error_log("검색 결과: " . json_encode($users));
            
            ResponseHelper::json([
                'success' => true,
                'data' => $users
            ]);
            
        } catch (Exception $e) {
            error_log("ChatController::searchUsers 오류: " . $e->getMessage());
            ResponseHelper::json(['success' => false, 'message' => '서버 오류가 발생했습니다.'], 500);
        }
    }
    
    
    /**
     * Firebase 토큰 반환 (AJAX)
     */
    public function getFirebaseToken() {
        // JSON 헤더 설정
        header('Content-Type: application/json');
        
        // 로그인 확인
        if (!AuthMiddleware::isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.']);
            exit;
        }
        
        try {
            $firebase_config = $this->getFirebaseConfig();
            
            echo json_encode([
                'success' => true,
                'firebase_config' => $firebase_config
            ]);
            exit;
            
        } catch (Exception $e) {
            error_log('Firebase 토큰 반환 오류: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => '서버 오류가 발생했습니다.']);
            exit;
        }
    }
    
    // Private 메서드들
    
    /**
     * Firebase 설정 정보 반환
     */
    private function getFirebaseConfig() {
        // 실제 Firebase 프로젝트 설정 정보를 반환
        // 보안을 위해 환경변수나 설정 파일에서 가져와야 함
        
        // 환경변수에서 Firebase 설정 읽기
        $firebaseConfig = [
            'apiKey' => $_ENV['FIREBASE_API_KEY'] ?? "AIzaSyAlFQNcYxi29uhu5fW1MYy7iESy3GvmnUQ",
            'authDomain' => $_ENV['FIREBASE_AUTH_DOMAIN'] ?? "topmkt-832f2.firebaseapp.com",
            'databaseURL' => $_ENV['FIREBASE_DATABASE_URL'] ?? "https://topmkt-832f2-default-rtdb.asia-southeast1.firebasedatabase.app/",
            'projectId' => $_ENV['FIREBASE_PROJECT_ID'] ?? "topmkt-832f2",
            'storageBucket' => $_ENV['FIREBASE_STORAGE_BUCKET'] ?? "topmkt-832f2.firebasestorage.app",
            'messagingSenderId' => $_ENV['FIREBASE_MESSAGING_SENDER_ID'] ?? "856114239779",
            'appId' => $_ENV['FIREBASE_APP_ID'] ?? "1:856114239779:web:d8dd9049a9723ac8835496"
        ];
        
        return $firebaseConfig;
    }
    
    private function render($view, $data = []) {
        extract($data);
        require_once ROOT_PATH . '/src/views/templates/header.php';
        require_once ROOT_PATH . '/src/views/' . $view . '.php';
        require_once ROOT_PATH . '/src/views/templates/footer.php';
    }
    
    private function showErrorPage($message, $code = 500) {
        http_response_code($code);
        $data = [
            'page_title' => '오류 발생',
            'error_message' => $message
        ];
        $this->render('templates/error', $data);
    }
}