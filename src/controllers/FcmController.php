<?php
/**
 * FCM 컨트롤러
 * Firebase Cloud Messaging 토큰 관리 API
 *
 * 작성일: 2025-10-16
 */

require_once SRC_PATH . '/controllers/BaseController.php';
require_once SRC_PATH . '/models/FcmToken.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';

class FcmController extends BaseController
{
    private $fcmToken;

    public function __construct()
    {
        parent::__construct();
        $this->fcmToken = new FcmToken();
    }

    /**
     * FCM 토큰 등록 API (RESTful)
     * POST /api/fcm/tokens
     *
     * @return void
     */
    public function store()
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

            // 필수 파라미터 검증
            if (!isset($input['fcm_token']) || empty(trim($input['fcm_token']))) {
                return $this->error('FCM 토큰이 필요합니다.', 400);
            }

            $fcmToken = trim($input['fcm_token']);
            $deviceType = $input['device_type'] ?? 'android'; // android|ios|web
            $deviceName = $input['device_name'] ?? null;
            $appVersion = $input['app_version'] ?? null;

            // device_type 검증
            $validDeviceTypes = ['android', 'ios', 'web'];
            if (!in_array($deviceType, $validDeviceTypes)) {
                return $this->error('유효하지 않은 디바이스 타입입니다.', 400);
            }

            // FCM 토큰 등록
            $result = $this->fcmToken->registerToken(
                $userId,
                $fcmToken,
                $deviceType,
                $deviceName,
                $appVersion
            );

            if (!$result) {
                return $this->error('FCM 토큰 등록에 실패했습니다.', 500);
            }

            return $this->success('FCM 토큰이 등록되었습니다.', [
                'user_id' => $userId,
                'device_type' => $deviceType
            ]);
        } catch (Exception $e) {
            error_log('FcmController::store 오류: ' . $e->getMessage());
            return $this->error('FCM 토큰 등록 중 오류가 발생했습니다.', 500);
        }
    }

    /**
     * FCM 토큰 삭제 API (RESTful)
     * DELETE /api/fcm/tokens
     *
     * @return void
     */
    public function destroy()
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

            // 필수 파라미터 검증
            if (!isset($input['fcm_token']) || empty(trim($input['fcm_token']))) {
                return $this->error('FCM 토큰이 필요합니다.', 400);
            }

            $fcmToken = trim($input['fcm_token']);

            // FCM 토큰 삭제
            $result = $this->fcmToken->deleteToken($userId, $fcmToken);

            if (!$result) {
                return $this->error('FCM 토큰 삭제에 실패했습니다.', 500);
            }

            return $this->success('FCM 토큰이 삭제되었습니다.');
        } catch (Exception $e) {
            error_log('FcmController::destroy 오류: ' . $e->getMessage());
            return $this->error('FCM 토큰 삭제 중 오류가 발생했습니다.', 500);
        }
    }

    /**
     * 내 FCM 토큰 목록 조회 API (RESTful)
     * GET /api/fcm/tokens
     *
     * @return void
     */
    public function index()
    {
        try {
            // 로그인 확인
            if (!AuthMiddleware::isLoggedIn()) {
                return $this->error('로그인이 필요합니다.', 401);
            }

            $userId = AuthMiddleware::getCurrentUserId();

            // 내 FCM 토큰 목록 조회
            $tokens = $this->fcmToken->getTokensByUserId($userId);

            return $this->success('FCM 토큰 목록 조회 성공', [
                'count' => count($tokens),
                'tokens' => $tokens
            ]);
        } catch (Exception $e) {
            error_log('FcmController::index 오류: ' . $e->getMessage());
            return $this->error('FCM 토큰 조회 중 오류가 발생했습니다.', 500);
        }
    }

    /**
     * 테스트 푸시 전송 API (개발 환경 전용)
     * POST /api/fcm/test-push
     *
     * @return void
     */
    public function testPush()
    {
        try {
            // 개발 환경에서만 허용
            if (!APP_DEBUG) {
                return $this->error('이 기능은 개발 환경에서만 사용 가능합니다.', 403);
            }

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

            $title = $input['title'] ?? '테스트 알림';
            $body = $input['body'] ?? '테스트 푸시 알림입니다.';

            // 내 토큰 조회
            $tokens = $this->fcmToken->getTokensByUserId($userId);

            if (empty($tokens)) {
                return $this->error('등록된 FCM 토큰이 없습니다.', 404);
            }

            // FcmHelper로 푸시 전송
            require_once SRC_PATH . '/helpers/FcmHelper.php';

            $results = [];
            foreach ($tokens as $token) {
                $result = FcmHelper::sendPush(
                    $token['fcm_token'],
                    $title,
                    $body,
                    ['type' => 'test', 'user_id' => $userId]
                );
                $results[] = $result;
            }

            return $this->success('테스트 푸시가 전송되었습니다.', [
                'count' => count($results),
                'results' => $results
            ]);
        } catch (Exception $e) {
            error_log('FcmController::testPush 오류: ' . $e->getMessage());
            return $this->error('테스트 푸시 전송 중 오류가 발생했습니다.', 500);
        }
    }
}
