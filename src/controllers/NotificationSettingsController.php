<?php
/**
 * 알림 설정 컨트롤러
 * FCM 앱 푸시 알림 설정 관리
 *
 * 작성일: 2025-10-16
 */

require_once SRC_PATH . '/controllers/BaseController.php';
require_once SRC_PATH . '/models/NotificationSettings.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';

class NotificationSettingsController extends BaseController
{
    private $notificationSettings;

    public function __construct()
    {
        parent::__construct();
        $this->notificationSettings = new NotificationSettings();
    }

    /**
     * 알림 설정 페이지 표시
     */
    public function index()
    {
        // 로그인 확인
        if (!AuthMiddleware::isLoggedIn()) {
            $this->redirect('/auth/login?redirect=' . urlencode('/notifications/settings'));
            return;
        }

        $userId = AuthMiddleware::getCurrentUserId();

        // 사용자 정보 조회
        require_once SRC_PATH . '/models/User.php';
        $userModel = new User();
        $user = $userModel->findById($userId);

        // 알림 설정 조회
        $settings = $this->notificationSettings->getSettings($userId);

        // 헤더 설정
        $pageTitle = '알림 설정';
        $pageDescription = 'FCM 앱 푸시 알림 설정을 관리합니다.';
        $pageSection = 'notifications';

        // 뷰 렌더링
        require_once SRC_PATH . '/views/templates/header.php';
        require_once SRC_PATH . '/views/notifications/settings.php';
        require_once SRC_PATH . '/views/templates/footer.php';
    }

    /**
     * 알림 설정 조회 API
     * GET /api/notifications/settings
     */
    public function getSettings()
    {
        try {
            // 로그인 확인
            if (!AuthMiddleware::isLoggedIn()) {
                return $this->error('로그인이 필요합니다.', 401);
            }

            $userId = AuthMiddleware::getCurrentUserId();

            // 알림 설정 조회
            $settings = $this->notificationSettings->getSettings($userId);

            if (!$settings) {
                return $this->error('알림 설정을 불러올 수 없습니다.', 500);
            }

            return $this->success('알림 설정 조회 성공', $settings);

        } catch (Exception $e) {
            error_log('NotificationSettingsController::getSettings 오류: ' . $e->getMessage());
            return $this->error('알림 설정 조회 중 오류가 발생했습니다.', 500);
        }
    }

    /**
     * 알림 설정 업데이트 API
     * PUT /api/notifications/settings
     */
    public function updateSettings()
    {
        try {
            // 로그인 확인
            if (!AuthMiddleware::isLoggedIn()) {
                return $this->error('로그인이 필요합니다.', 401);
            }

            $userId = AuthMiddleware::getCurrentUserId();

            // 요청 데이터 파싱
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                return $this->error('잘못된 요청 데이터입니다.', 400);
            }

            // CSRF 토큰 검증
            if (!isset($input['csrf_token']) || !$this->validateCSRF($input['csrf_token'])) {
                return $this->error('유효하지 않은 요청입니다.', 403);
            }

            // 업데이트할 설정 구성
            $settings = [];

            $allowedFields = [
                'all_notifications',
                'comments_enabled',
                'likes_enabled',
                'lectures_events_enabled',
                'registration_enabled',
                'notices_enabled'
            ];

            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    // boolean 값으로 변환
                    $settings[$field] = filter_var($input[$field], FILTER_VALIDATE_BOOLEAN);
                }
            }

            if (empty($settings)) {
                return $this->error('업데이트할 설정이 없습니다.', 400);
            }

            // 알림 설정 업데이트
            $result = $this->notificationSettings->updateSettings($userId, $settings);

            if (!$result) {
                return $this->error('알림 설정 업데이트에 실패했습니다.', 500);
            }

            // 업데이트된 설정 조회
            $updatedSettings = $this->notificationSettings->getSettings($userId);

            return $this->success('알림 설정이 성공적으로 업데이트되었습니다.', $updatedSettings);

        } catch (Exception $e) {
            error_log('NotificationSettingsController::updateSettings 오류: ' . $e->getMessage());
            return $this->error('알림 설정 업데이트 중 오류가 발생했습니다.', 500);
        }
    }

    /**
     * 전체 알림 ON/OFF 토글 API
     * POST /api/notifications/toggle-all
     */
    public function toggleAll()
    {
        try {
            // 로그인 확인
            if (!AuthMiddleware::isLoggedIn()) {
                return $this->error('로그인이 필요합니다.', 401);
            }

            $userId = AuthMiddleware::getCurrentUserId();

            // 요청 데이터 파싱
            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['enabled'])) {
                return $this->error('enabled 값이 필요합니다.', 400);
            }

            // CSRF 토큰 검증
            if (!isset($input['csrf_token']) || !$this->validateCSRF($input['csrf_token'])) {
                return $this->error('유효하지 않은 요청입니다.', 403);
            }

            $enabled = filter_var($input['enabled'], FILTER_VALIDATE_BOOLEAN);

            // 전체 알림 토글
            $result = $this->notificationSettings->toggleAllNotifications($userId, $enabled);

            if (!$result) {
                return $this->error('알림 설정 변경에 실패했습니다.', 500);
            }

            // 업데이트된 설정 조회
            $updatedSettings = $this->notificationSettings->getSettings($userId);

            $message = $enabled ? '모든 알림이 활성화되었습니다.' : '모든 알림이 비활성화되었습니다.';

            return $this->success($message, $updatedSettings);

        } catch (Exception $e) {
            error_log('NotificationSettingsController::toggleAll 오류: ' . $e->getMessage());
            return $this->error('알림 설정 변경 중 오류가 발생했습니다.', 500);
        }
    }

    /**
     * 특정 알림 타입 활성화 여부 확인 API
     * GET /api/notifications/check/:type/:userId
     *
     * 알림 발송 시스템에서 사용
     */
    public function checkNotification($type, $targetUserId = null)
    {
        try {
            $userId = $targetUserId ?? AuthMiddleware::getCurrentUserId();

            if (!$userId) {
                return $this->error('사용자 ID가 필요합니다.', 400);
            }

            // 알림 타입 검증
            $validTypes = ['comments', 'likes', 'lectures_events', 'registration', 'notices'];
            if (!in_array($type, $validTypes)) {
                return $this->error('유효하지 않은 알림 타입입니다.', 400);
            }

            // 알림 활성화 여부 확인
            $enabled = $this->notificationSettings->isNotificationEnabled($userId, $type);

            return $this->success('알림 설정 조회 성공', [
                'user_id' => $userId,
                'type' => $type,
                'enabled' => $enabled
            ]);

        } catch (Exception $e) {
            error_log('NotificationSettingsController::checkNotification 오류: ' . $e->getMessage());
            return $this->error('알림 설정 확인 중 오류가 발생했습니다.', 500);
        }
    }
}
