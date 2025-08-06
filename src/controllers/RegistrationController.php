<?php
/**
 * 강의/행사 신청 관리 컨트롤러
 */

require_once SRC_PATH . '/controllers/BaseController.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
require_once SRC_PATH . '/helpers/ResponseHelper.php';
require_once SRC_PATH . '/helpers/ValidationHelper.php';
require_once SRC_PATH . '/helpers/FirebaseHelper.php';
require_once SRC_PATH . '/services/EmailService.php';

class RegistrationController extends BaseController
{
    /**
     * 신청 상태 확인 API
     */
    public function getRegistrationStatus($lectureId)
    {
        header('Content-Type: application/json');
        
        try {
            // 로그인 확인
            if (!AuthMiddleware::isLoggedIn()) {
                return ResponseHelper::json(null, 401, '로그인이 필요합니다.');
            }
            
            $userId = AuthMiddleware::getCurrentUserId();
            
            // 강의 정보 조회
            $lectureQuery = "
                SELECT 
                    l.id, l.title, l.start_date, l.start_time, l.end_date, l.end_time,
                    l.max_participants, l.auto_approval,
                    l.registration_start_date, l.registration_end_date, l.allow_waiting_list,
                    l.status as lecture_status,
                    COUNT(DISTINCT CASE WHEN lr.status = 'approved' THEN lr.id END) as current_participants
                FROM lectures l
                LEFT JOIN lecture_registrations lr ON l.id = lr.lecture_id
                WHERE l.id = ? AND l.status = 'published'
                GROUP BY l.id, l.title, l.start_date, l.start_time, l.end_date, l.end_time,
                         l.max_participants, l.auto_approval, l.registration_start_date, 
                         l.registration_end_date, l.allow_waiting_list, l.status
            ";
            
            $stmt = $this->db->prepare($lectureQuery);
            $stmt->bind_param("i", $lectureId);
            $stmt->execute();
            $lecture = $stmt->get_result()->fetch_assoc();
            
            if (!$lecture) {
                return ResponseHelper::json(null, 404, '강의를 찾을 수 없습니다.');
            }
            
            // 사용자의 신청 정보 조회
            $registrationQuery = "
                SELECT 
                    id, status, is_waiting_list, waiting_order,
                    created_at, processed_at, admin_notes
                FROM lecture_registrations 
                WHERE lecture_id = ? AND user_id = ?
                ORDER BY created_at DESC 
                LIMIT 1
            ";
            
            $stmt = $this->db->prepare($registrationQuery);
            $stmt->bind_param("ii", $lectureId, $userId);
            $stmt->execute();
            $registration = $stmt->get_result()->fetch_assoc();
            
            // 응답 데이터 구성
            $responseData = [
                'lecture_info' => $lecture,
                'registration' => $registration,
                'user_id' => $userId
            ];
            
            return ResponseHelper::json($responseData, 200, '신청 상태 조회 완료');
            
        } catch (Exception $e) {
            error_log("신청 상태 조회 오류: " . $e->getMessage());
            return ResponseHelper::json(null, 500, '신청 상태 조회 중 오류가 발생했습니다.');
        }
    }
    
    /**
     * 신청 등록 API
     */
    public function createRegistration($lectureId)
    {
        header('Content-Type: application/json');
        
        try {
            // HTTP 메소드 확인
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return ResponseHelper::json(null, 405, 'POST 메소드만 허용됩니다.');
            }
            
            // 로그인 확인
            if (!AuthMiddleware::isLoggedIn()) {
                return ResponseHelper::json(null, 401, '로그인이 필요합니다.');
            }
            
            $userId = AuthMiddleware::getCurrentUserId();
            
            // 데이터 파싱 (JSON 또는 폼 데이터 지원)
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            error_log("📝 Content-Type: " . $contentType);
            
            $input = [];
            if (strpos($contentType, 'application/json') !== false) {
                // JSON 데이터 처리
                $rawInput = file_get_contents('php://input');
                error_log("📝 Raw JSON input length: " . strlen($rawInput));
                
                // php://input이 비어있으면 $_POST 사용 (fallback)
                if (empty($rawInput)) {
                    error_log("📝 php://input 비어있음, _POST 사용");
                    $input = $_POST;
                } else {
                    $decoded = json_decode($rawInput, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $input = $decoded;
                        error_log("✅ JSON 디코딩 성공");
                    } else {
                        error_log("❌ JSON 디코딩 실패: " . json_last_error_msg());
                        error_log("❌ Raw input: " . substr($rawInput, 0, 100)); // 처음 100자만
                        $input = $_POST; // fallback
                    }
                }
            } else {
                // 폼 데이터 처리
                $input = $_POST;
                error_log("📝 Form data input: " . json_encode($_POST, JSON_UNESCAPED_UNICODE));
            }
            
            if (empty($input)) {
                error_log("❌ 입력 데이터가 비어있음");
                return ResponseHelper::json(null, 400, '입력 데이터가 없습니다.');
            }
            
            // CSRF 토큰 검증
            if (!$this->validateCsrfToken($input['csrf_token'] ?? '')) {
                return ResponseHelper::json(null, 403, 'CSRF 토큰이 유효하지 않습니다.');
            }
            
            // 강의 정보 조회
            $lectureQuery = "
                SELECT 
                    id, title, start_date, start_time, max_participants, 
                    current_participants, auto_approval, registration_start_date, 
                    registration_end_date, allow_waiting_list, status, user_id as organizer_id
                FROM lectures 
                WHERE id = ? AND status = 'published'
            ";
            
            $stmt = $this->db->prepare($lectureQuery);
            $stmt->bind_param("i", $lectureId);
            $stmt->execute();
            $lecture = $stmt->get_result()->fetch_assoc();
            
            if (!$lecture) {
                return ResponseHelper::json(null, 404, '강의를 찾을 수 없습니다.');
            }
            
            // 본인 강의 신청 방지
            if ($lecture['organizer_id'] == $userId) {
                return ResponseHelper::json(null, 400, '본인이 등록한 강의에는 신청할 수 없습니다.');
            }
            
            // 기존 신청 확인
            $existingQuery = "SELECT id, status FROM lecture_registrations WHERE lecture_id = ? AND user_id = ?";
            $stmt = $this->db->prepare($existingQuery);
            $stmt->bind_param("ii", $lectureId, $userId);
            $stmt->execute();
            $existing = $stmt->get_result()->fetch_assoc();
            
            if ($existing && in_array($existing['status'], ['pending', 'approved', 'waiting'])) {
                return ResponseHelper::json(null, 400, '이미 신청하셨습니다.');
            }
            
            // 취소되거나 거절된 신청이 있으면 삭제 (재신청을 위해)
            if ($existing && in_array($existing['status'], ['cancelled', 'rejected'])) {
                error_log("재신청 가능한 기존 신청 발견 (ID: {$existing['id']}, 상태: {$existing['status']}), 재신청을 위해 삭제");
                $deleteQuery = "DELETE FROM lecture_registrations WHERE id = ?";
                $stmt = $this->db->prepare($deleteQuery);
                $stmt->bind_param("i", $existing['id']);
                $stmt->execute();
                error_log("기존 신청 삭제 완료 (재신청 허용)");
            }
            
            // 신청 기간 확인
            $now = new DateTime();
            
            if ($lecture['registration_start_date']) {
                $startDate = new DateTime($lecture['registration_start_date']);
                if ($now < $startDate) {
                    return ResponseHelper::json(null, 400, '아직 신청 기간이 아닙니다.');
                }
            }
            
            if ($lecture['registration_end_date']) {
                $endDate = new DateTime($lecture['registration_end_date']);
                if ($now > $endDate) {
                    return ResponseHelper::json(null, 400, '신청 기간이 마감되었습니다.');
                }
            }
            
            // 강의 시작 시간 확인
            $lectureStart = new DateTime($lecture['start_date'] . ' ' . $lecture['start_time']);
            if ($now >= $lectureStart) {
                return ResponseHelper::json(null, 400, '강의가 이미 시작되었습니다.');
            }
            
            // 사용자 정보 조회
            $userQuery = "SELECT nickname, phone, email FROM users WHERE id = ?";
            $stmt = $this->db->prepare($userQuery);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            
            if (!$user) {
                return ResponseHelper::json(null, 404, '사용자 정보를 찾을 수 없습니다.');
            }
            
            // 정원 확인 및 대기자 처리
            $isWaitingList = false;
            $waitingOrder = null;
            $status = 'pending';
            
            if ($lecture['max_participants'] && $lecture['current_participants'] >= $lecture['max_participants']) {
                if (!$lecture['allow_waiting_list']) {
                    return ResponseHelper::json(null, 400, '정원이 마감되었습니다.');
                }
                
                // 대기자로 등록
                $isWaitingList = true;
                $status = 'waiting';
                
                // 대기 순번 계산
                $waitingQuery = "SELECT MAX(waiting_order) as max_order FROM lecture_registrations WHERE lecture_id = ? AND is_waiting_list = 1";
                $stmt = $this->db->prepare($waitingQuery);
                $stmt->bind_param("i", $lectureId);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();
                $waitingOrder = ($result['max_order'] ?? 0) + 1;
            }
            
            // 자동 승인 확인
            if (!$isWaitingList && $lecture['auto_approval']) {
                $status = 'approved';
            }
            
            // 디버깅: 입력 데이터 로깅
            error_log("=== 강의 신청 디버깅 시작 ===");
            error_log("강의 ID: " . $lectureId);
            error_log("사용자 ID: " . $userId);
            error_log("입력 데이터: " . json_encode($input, JSON_UNESCAPED_UNICODE));
            error_log("사용자 정보: " . json_encode($user, JSON_UNESCAPED_UNICODE));
            
            // 입력 데이터 검증
            $validationErrors = $this->validateRegistrationData($input, $user);
            if (!empty($validationErrors)) {
                error_log("검증 오류: " . json_encode($validationErrors, JSON_UNESCAPED_UNICODE));
                return ResponseHelper::json(['errors' => $validationErrors], 400, '입력 데이터에 오류가 있습니다.');
            }
            error_log("✅ 입력 데이터 검증 통과");
            
            // 신청 데이터 구성
            $registrationData = [
                'lecture_id' => $lectureId,
                'user_id' => $userId,
                'participant_name' => trim($input['participant_name'] ?? $user['nickname']),
                'participant_email' => trim($input['participant_email'] ?? $user['email']),
                'participant_phone' => trim($input['participant_phone'] ?? $user['phone']),
                'company_name' => trim($input['company_name'] ?? ''),
                'position' => trim($input['position'] ?? ''),
                'motivation' => trim($input['motivation'] ?? ''),
                'special_requests' => trim($input['special_requests'] ?? ''),
                'how_did_you_know' => trim($input['how_did_you_know'] ?? ''),
                'status' => $status,
                'is_waiting_list' => $isWaitingList,
                'waiting_order' => $waitingOrder,
                'processed_by' => null,
                'processed_at' => null
            ];
            
            // 자동 승인인 경우 처리자 정보 설정
            if ($status === 'approved') {
                $registrationData['processed_by'] = $userId;
                $registrationData['processed_at'] = date('Y-m-d H:i:s');
            }
            
            // 디버깅: 신청 데이터 로깅
            error_log("📋 구성된 신청 데이터: " . json_encode($registrationData, JSON_UNESCAPED_UNICODE));
            error_log("📋 신청 상태: " . $status);
            error_log("📋 대기자 여부: " . ($isWaitingList ? 'true' : 'false'));
            
            // 트랜잭션 시작
            error_log("🔄 데이터베이스 트랜잭션 시작");
            $this->db->beginTransaction();
            
            try {
                // 신청 등록
                $insertQuery = "
                    INSERT INTO lecture_registrations 
                    (lecture_id, user_id, registration_date, participant_name, participant_email, participant_phone,
                     company_name, position, motivation, special_requests, how_did_you_know,
                     status, is_waiting_list, waiting_order, processed_by, processed_at)
                    VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ";
                
                error_log("📝 준비된 INSERT 쿼리: " . $insertQuery);
                
                $stmt = $this->db->prepare($insertQuery);
                if (!$stmt) {
                    $error = $this->db->getConnection()->error;
                    error_log("❌ Prepare 실패: " . $error);
                    throw new Exception('쿼리 준비에 실패했습니다: ' . $error);
                }
                error_log("✅ 쿼리 Prepare 성공");
                
                // 바인딩할 값들 로깅
                $bindValues = [
                    $registrationData['lecture_id'],
                    $registrationData['user_id'],
                    $registrationData['participant_name'],
                    $registrationData['participant_email'],
                    $registrationData['participant_phone'],
                    $registrationData['company_name'],
                    $registrationData['position'],
                    $registrationData['motivation'],
                    $registrationData['special_requests'],
                    $registrationData['how_did_you_know'],
                    $registrationData['status'],
                    $registrationData['is_waiting_list'],
                    $registrationData['waiting_order'],
                    $registrationData['processed_by'],
                    $registrationData['processed_at']
                ];
                error_log("📋 바인딩 값들: " . json_encode($bindValues, JSON_UNESCAPED_UNICODE));
                error_log("📋 바인딩 타입: iisssssssssiiis");
                
                $stmt->bind_param(
                    "iisssssssssiiis",
                    $registrationData['lecture_id'],
                    $registrationData['user_id'],
                    $registrationData['participant_name'],
                    $registrationData['participant_email'],
                    $registrationData['participant_phone'],
                    $registrationData['company_name'],
                    $registrationData['position'],
                    $registrationData['motivation'],
                    $registrationData['special_requests'],
                    $registrationData['how_did_you_know'],
                    $registrationData['status'],
                    $registrationData['is_waiting_list'],
                    $registrationData['waiting_order'],
                    $registrationData['processed_by'],
                    $registrationData['processed_at']
                );
                error_log("✅ 파라미터 바인딩 완료");
                
                error_log("🚀 쿼리 실행 시작...");
                if (!$stmt->execute()) {
                    $error = $stmt->error;
                    error_log("❌ 신청 등록 쿼리 실행 실패: " . $error);
                    error_log("❌ affected_rows: " . $stmt->affected_rows);
                    error_log("❌ errno: " . $stmt->errno);
                    throw new Exception('신청 등록에 실패했습니다: ' . $error);
                }
                error_log("✅ 쿼리 실행 성공! affected_rows: " . $stmt->affected_rows);
                
                $registrationId = $this->db->lastInsertId();
                error_log("✅ 생성된 신청 ID: " . $registrationId);
                
                // 커밋
                error_log("💾 트랜잭션 커밋 시작");
                $this->db->commit();
                error_log("✅ 트랜잭션 커밋 완료");
                
                // 강의 주최자에게 Firebase 실시간 알림 발송
                try {
                    $this->updateOrganizerNotification($lectureId);
                } catch (Exception $e) {
                    error_log("Firebase 실시간 알림 업데이트 오류: " . $e->getMessage());
                    // Firebase 실패는 전체 프로세스를 중단하지 않음
                }
                
                // 신청 확인 SMS 발송
                try {
                    require_once SRC_PATH . '/helpers/SmsHelper.php';
                    $smsResult = sendLectureApplicationSms($registrationData['participant_phone']);
                    if ($smsResult['success']) {
                        error_log("강의 신청 확인 SMS 발송 성공: " . $registrationData['participant_phone']);
                    } else {
                        error_log("강의 신청 확인 SMS 발송 실패: " . $smsResult['message']);
                    }
                } catch (Exception $e) {
                    error_log("SMS 발송 오류: " . $e->getMessage());
                    // SMS 실패는 전체 프로세스를 중단하지 않음
                }
                
                $message = $isWaitingList ? 
                    "대기자로 신청이 완료되었습니다. (대기순번: {$waitingOrder}번)" :
                    ($status === 'approved' ? '신청이 승인되었습니다.' : '신청이 완료되었습니다. 승인을 기다려주세요.');
                
                return ResponseHelper::json([
                    'registration_id' => $registrationId,
                    'status' => $status,
                    'is_waiting_list' => $isWaitingList,
                    'waiting_order' => $waitingOrder
                ], 200, $message);
                
            } catch (Exception $e) {
                error_log("❌ 트랜잭션 내부 오류: " . $e->getMessage());
                error_log("❌ 파일: " . $e->getFile() . ", 라인: " . $e->getLine());
                error_log("🔄 트랜잭션 롤백 시작");
                $this->db->rollback();
                error_log("✅ 트랜잭션 롤백 완료");
                throw $e;
            }
            
        } catch (Exception $e) {
            error_log("❌❌❌ 최종 신청 등록 오류: " . $e->getMessage());
            error_log("❌ 파일: " . $e->getFile());
            error_log("❌ 라인: " . $e->getLine());
            error_log("❌ 스택 추적: " . $e->getTraceAsString());
            error_log("=== 강의 신청 디버깅 종료 ===");
            return ResponseHelper::json(null, 500, '신청 처리 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
    
    /**
     * 신청 취소 API
     */
    public function cancelRegistration($lectureId)
    {
        header('Content-Type: application/json');
        
        try {
            // HTTP 메소드 확인
            if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
                return ResponseHelper::json(null, 405, 'DELETE 메소드만 허용됩니다.');
            }
            
            // 로그인 확인
            if (!AuthMiddleware::isLoggedIn()) {
                return ResponseHelper::json(null, 401, '로그인이 필요합니다.');
            }
            
            $userId = AuthMiddleware::getCurrentUserId();
            
            // 데이터 파싱 (JSON 또는 쿼리 파라미터 지원)
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            
            if (strpos($contentType, 'application/json') !== false) {
                // JSON 데이터 처리
                $input = json_decode(file_get_contents('php://input'), true);
            } else {
                // 쿼리 파라미터나 POST 데이터 처리
                $input = array_merge($_GET, $_POST);
            }
            
            // CSRF 토큰 검증 (다양한 방식 지원)
            $csrfToken = $input['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            
            if (!$this->validateCsrfToken($csrfToken)) {
                return ResponseHelper::json(null, 403, 'CSRF 토큰이 유효하지 않습니다.');
            }
            
            // 신청 정보 조회
            $registrationQuery = "
                SELECT r.*, l.start_date, l.start_time 
                FROM lecture_registrations r
                JOIN lectures l ON r.lecture_id = l.id
                WHERE r.lecture_id = ? AND r.user_id = ? 
                AND r.status IN ('pending', 'approved', 'waiting')
                ORDER BY r.created_at DESC LIMIT 1
            ";
            
            $stmt = $this->db->prepare($registrationQuery);
            $stmt->bind_param("ii", $lectureId, $userId);
            $stmt->execute();
            $registration = $stmt->get_result()->fetch_assoc();
            
            if (!$registration) {
                return ResponseHelper::json(null, 404, '취소할 신청을 찾을 수 없습니다.');
            }
            
            // 강의 시작 시간 확인
            $now = new DateTime();
            $lectureStart = new DateTime($registration['start_date'] . ' ' . $registration['start_time']);
            
            if ($now >= $lectureStart) {
                return ResponseHelper::json(null, 400, '강의가 이미 시작되어 취소할 수 없습니다.');
            }
            
            // 신청 취소 처리
            $updateQuery = "
                UPDATE lecture_registrations 
                SET status = 'cancelled', processed_at = NOW() 
                WHERE id = ?
            ";
            
            $stmt = $this->db->prepare($updateQuery);
            if (!$stmt) {
                error_log("취소 쿼리 prepare 실패: " . $this->db->getConnection()->error);
                return ResponseHelper::json(null, 500, '신청 취소 준비에 실패했습니다.');
            }
            
            $stmt->bind_param("i", $registration['id']);
            
            if ($stmt->execute()) {
                $affectedRows = $stmt->affected_rows;
                error_log("신청 취소 성공 - 영향받은 행: " . $affectedRows . ", 신청 ID: " . $registration['id']);
                
                if ($affectedRows > 0) {
                    return ResponseHelper::json([
                        'registration_id' => $registration['id']
                    ], 200, '신청이 취소되었습니다.');
                } else {
                    error_log("신청 취소 실패 - 업데이트된 행이 없음");
                    return ResponseHelper::json(null, 500, '신청 정보를 업데이트할 수 없습니다.');
                }
            } else {
                error_log("취소 쿼리 실행 실패: " . $stmt->error);
                return ResponseHelper::json(null, 500, '신청 취소에 실패했습니다: ' . $stmt->error);
            }
            
        } catch (Exception $e) {
            error_log("신청 취소 오류: " . $e->getMessage());
            return ResponseHelper::json(null, 500, '신청 취소 중 오류가 발생했습니다.');
        }
    }
    
    /**
     * 신청 데이터 검증
     */
    private function validateRegistrationData($input, $user)
    {
        $errors = [];
        
        // 필수 필드 검증
        $participantName = trim($input['participant_name'] ?? '');
        $participantEmail = trim($input['participant_email'] ?? '');
        $participantPhone = trim($input['participant_phone'] ?? '');
        
        // 이름 검증
        if (empty($participantName)) {
            $errors['participant_name'] = '이름을 입력해주세요.';
        } elseif (strlen($participantName) < 2) {
            $errors['participant_name'] = '이름은 2글자 이상 입력해주세요.';
        } elseif (strlen($participantName) > 100) {
            $errors['participant_name'] = '이름이 너무 깁니다.';
        }
        
        // 이메일 검증
        if (empty($participantEmail)) {
            $errors['participant_email'] = '이메일을 입력해주세요.';
        } elseif (!filter_var($participantEmail, FILTER_VALIDATE_EMAIL)) {
            $errors['participant_email'] = '올바른 이메일 형식을 입력해주세요.';
        } elseif (strlen($participantEmail) > 255) {
            $errors['participant_email'] = '이메일이 너무 깁니다.';
        }
        
        // 전화번호 검증
        if (empty($participantPhone)) {
            $errors['participant_phone'] = '연락처를 입력해주세요.';
        } elseif (!$this->isValidPhone($participantPhone)) {
            $errors['participant_phone'] = '올바른 연락처 형식을 입력해주세요. (예: 010-1234-5678)';
        }
        
        // 선택적 필드 길이 검증
        if (!empty($input['company_name']) && strlen(trim($input['company_name'])) > 255) {
            $errors['company_name'] = '회사명이 너무 깁니다.';
        }
        
        if (!empty($input['position']) && strlen(trim($input['position'])) > 100) {
            $errors['position'] = '직책명이 너무 깁니다.';
        }
        
        if (!empty($input['motivation']) && strlen(trim($input['motivation'])) > 1000) {
            $errors['motivation'] = '참가 동기가 너무 깁니다. (최대 1000자)';
        }
        
        if (!empty($input['special_requests']) && strlen(trim($input['special_requests'])) > 1000) {
            $errors['special_requests'] = '특별 요청사항이 너무 깁니다. (최대 1000자)';
        }
        
        // how_did_you_know 값 검증
        $validSources = ['website', 'social_media', 'friend_referral', 'company_notice', 'email', 'search_engine', 'advertisement', 'other'];
        if (!empty($input['how_did_you_know']) && !in_array($input['how_did_you_know'], $validSources)) {
            $errors['how_did_you_know'] = '올바른 항목을 선택해주세요.';
        }
        
        return $errors;
    }
    
    /**
     * 전화번호 형식 검증
     */
    private function isValidPhone($phone)
    {
        // 공백 제거
        $phone = preg_replace('/\s/', '', $phone);
        
        // 한국 휴대폰 번호 형식 검증
        return preg_match('/^(010|011|016|017|018|019)[-]?\d{3,4}[-]?\d{4}$/', $phone);
    }
    
    /**
     * 이전 신청 데이터 조회 API
     */
    public function getPreviousRegistration($lectureId)
    {
        header('Content-Type: application/json');
        
        try {
            // 로그인 확인
            if (!AuthMiddleware::isLoggedIn()) {
                return ResponseHelper::json(null, 401, '로그인이 필요합니다.');
            }
            
            $userId = AuthMiddleware::getCurrentUserId();
            
            // 가장 최근 취소된 또는 거절된 신청 정보 조회
            $query = "
                SELECT 
                    participant_name, participant_email, participant_phone,
                    company_name, position, motivation, special_requests, how_did_you_know
                FROM lecture_registrations 
                WHERE lecture_id = ? AND user_id = ? AND status IN ('cancelled', 'rejected')
                ORDER BY created_at DESC 
                LIMIT 1
            ";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ii", $lectureId, $userId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            if ($result) {
                return ResponseHelper::json($result, 200, '이전 신청 데이터 조회 완료');
            } else {
                return ResponseHelper::json(null, 200, '이전 신청 데이터가 없습니다.');
            }
            
        } catch (Exception $e) {
            error_log("이전 신청 데이터 조회 오류: " . $e->getMessage());
            return ResponseHelper::json(null, 500, '이전 신청 데이터 조회 중 오류가 발생했습니다.');
        }
    }
    
    /**
     * CSRF 토큰 검증
     */
    private function validateCsrfToken($token)
    {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * 강의 주최자의 Firebase 실시간 알림 업데이트
     * 
     * @param int $lectureId 강의 ID
     */
    private function updateOrganizerNotification($lectureId)
    {
        try {
            // 강의 주최자 ID 조회
            $organizerQuery = "SELECT user_id FROM lectures WHERE id = ?";
            $stmt = $this->db->prepare($organizerQuery);
            $stmt->bind_param("i", $lectureId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            if (!$result) {
                error_log("Firebase 알림: 강의를 찾을 수 없음 (ID: {$lectureId})");
                return;
            }
            
            $organizerId = $result['user_id'];
            
            // 주최자의 현재 대기 신청 수 계산
            $pendingData = FirebaseHelper::calculatePendingCount($organizerId);
            
            // Firebase 실시간 알림 업데이트
            $updateResult = FirebaseHelper::updatePendingNotification(
                $organizerId,
                $pendingData['count'],
                $pendingData['details']
            );
            
            if ($updateResult) {
                error_log("Firebase 실시간 알림 업데이트 성공 - 주최자: {$organizerId}, 대기수: {$pendingData['count']}");
            } else {
                error_log("Firebase 실시간 알림 업데이트 실패 - 주최자: {$organizerId}");
            }
            
        } catch (Exception $e) {
            error_log("Firebase 알림 업데이트 중 오류: " . $e->getMessage());
            throw $e;
        }
    }
}
?>