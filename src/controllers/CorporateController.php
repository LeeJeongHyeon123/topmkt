<?php
/**
 * CorporateController 클래스
 * 기업회원 인증 및 관리 컨트롤러
 */

require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/models/Corporate.php';
require_once SRC_PATH . '/helpers/CorporateFileUpload.php';
require_once SRC_PATH . '/helpers/ValidationHelper.php';
require_once SRC_PATH . '/config/upload.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';

use App\Helpers\ValidationHelper;

class CorporateController {
    private $corporateModel;
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->corporateModel = new Corporate();
        
        // CSRF 토큰 생성
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }
    
    /**
     * 기업회원 안내 페이지
     */
    public function info() {
        // 헤더 데이터
        $headerData = [
            'title' => '기업회원 안내 - 탑마케팅',
            'description' => '탑마케팅 기업회원 혜택 및 신청 안내',
            'pageSection' => 'corporate'
        ];
        
        // 로그인한 사용자의 신청 상태 확인
        $applicationStatus = null;
        if (AuthMiddleware::isLoggedIn()) {
            $userId = AuthMiddleware::getCurrentUserId();
            $applicationStatus = $this->corporateModel->getApplicationStatus($userId);
        }
        
        // 뷰 렌더링 (로그인 여부와 관계없이 안내 페이지는 모든 사용자에게 표시)
        $this->renderView('corporate/info', [
            'applicationStatus' => $applicationStatus
        ], $headerData);
    }
    
    /**
     * 기업 인증 신청 페이지
     */
    public function apply() {
        // 로그인 확인
        if (!AuthMiddleware::isLoggedIn()) {
            header('Location: /auth/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
        
        $userId = AuthMiddleware::getCurrentUserId();
        $applicationStatus = $this->corporateModel->getApplicationStatus($userId);
        
        // 이미 신청했거나 승인된 경우
        if (in_array($applicationStatus['status'], ['pending', 'approved'])) {
            header('Location: /corp/status');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleApplicationSubmit();
            return;
        }
        
        // 거절된 경우 기존 정보 표시
        $existingData = null;
        if ($applicationStatus['status'] === 'rejected') {
            $existingData = $applicationStatus['profile'];
        }
        
        $this->renderView('corporate/apply', [
            'existingData' => $existingData,
            'isReapply' => $applicationStatus['status'] === 'rejected',
            'csrfToken' => $_SESSION['csrf_token']
        ]);
    }
    
    /**
     * 신청 처리
     */
    private function handleApplicationSubmit() {
        try {
            // CSRF 토큰 검증
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                throw new Exception('보안 토큰이 유효하지 않습니다.');
            }
            
            $userId = $_SESSION['user_id'];
            
            // 입력값 검증
            $validationResult = $this->validateApplicationData($_POST, $_FILES);
            if (!$validationResult['success']) {
                throw new Exception($validationResult['message']);
            }
            
            // 파일 업로드 처리
            $uploadResult = $this->handleFileUpload($_FILES['business_registration_file'], $userId);
            if (!$uploadResult['success']) {
                throw new Exception($uploadResult['message']);
            }
            
            // 신청 데이터 준비
            $companyData = [
                'company_name' => trim($_POST['company_name']),
                'business_number' => trim($_POST['business_number']),
                'representative_name' => trim($_POST['representative_name']),
                'representative_phone' => trim($_POST['representative_phone']),
                'company_address' => trim($_POST['company_address']),
                'business_registration_file' => $uploadResult['filename'],
                'is_overseas' => isset($_POST['is_overseas']) ? 1 : 0
            ];
            
            // 신청 처리 (신규 신청 또는 재신청)
            $applicationStatus = $this->corporateModel->getApplicationStatus($userId);
            if ($applicationStatus['status'] === 'rejected') {
                $this->corporateModel->reapply($userId, $companyData);
                $message = '기업 인증이 재신청되었습니다. 1~3일 내 심사 후 결과를 알려드립니다.';
            } else {
                $this->corporateModel->submitApplication($userId, $companyData);
                $message = '기업 인증 신청이 완료되었습니다. 1~3일 내 심사 후 결과를 알려드립니다.';
            }
            
            // 성공 메시지와 함께 상태 페이지로 리다이렉트
            $_SESSION['success_message'] = $message;
            header('Location: /corp/status'); exit;
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = $e->getMessage();
            header('Location: /corp/apply'); exit;
        }
    }
    
    /**
     * 신청 현황 페이지
     */
    public function status() {
        // 로그인 확인
        if (!AuthMiddleware::isLoggedIn()) {
            header('Location: /auth/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $applicationStatus = $this->corporateModel->getApplicationStatus($userId);
        
        // 신청한 적이 없는 경우
        if ($applicationStatus['status'] === 'none') {
            header('Location: /corp/info'); exit;
            return;
        }
        
        // 신청 이력 조회
        $history = $this->corporateModel->getApplicationHistory($userId);
        
        $this->renderView('corporate/status', [
            'applicationStatus' => $applicationStatus,
            'profile' => $applicationStatus['profile'],
            'history' => $history
        ]);
    }
    
    /**
     * 기업 정보 수정 페이지
     */
    public function edit() {
        // 로그인 확인
        if (!AuthMiddleware::isLoggedIn()) {
            header('Location: /auth/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            return;
        }
        
        $userId = $_SESSION['user_id'];
        
        // 승인된 기업회원만 수정 가능
        if (!$this->corporateModel->hasCorpPermission($userId)) {
            $_SESSION['error_message'] = '승인된 기업회원만 정보를 수정할 수 있습니다.';
            header('Location: /corp/status'); exit;
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleInfoUpdate();
            return;
        }
        
        $profile = $this->corporateModel->getCompanyProfile($userId);
        
        $this->renderView('corporate/edit', [
            'profile' => $profile,
            'csrfToken' => $_SESSION['csrf_token']
        ]);
    }
    
    /**
     * 정보 수정 처리
     */
    private function handleInfoUpdate() {
        try {
            // CSRF 토큰 검증
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                throw new Exception('보안 토큰이 유효하지 않습니다.');
            }
            
            $userId = $_SESSION['user_id'];
            
            // 입력값 검증
            $validationResult = $this->validateUpdateData($_POST);
            if (!$validationResult['success']) {
                throw new Exception($validationResult['message']);
            }
            
            // 수정 데이터 준비
            $data = [
                'company_name' => trim($_POST['company_name']),
                'representative_name' => trim($_POST['representative_name']),
                'representative_phone' => trim($_POST['representative_phone']),
                'company_address' => trim($_POST['company_address'])
            ];
            
            $this->corporateModel->updateCompanyInfo($userId, $data);
            
            $_SESSION['success_message'] = '기업 정보가 성공적으로 수정되었습니다.';
            header('Location: /corp/status'); exit;
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = $e->getMessage();
            header('Location: /corp/edit'); exit;
        }
    }
    
    /**
     * 신청 데이터 검증
     */
    private function validateApplicationData($post, $files) {
        // 필수 필드 검증
        $requiredFields = [
            'company_name' => '회사명',
            'business_number' => '사업자등록번호',
            'representative_name' => '대표자명',
            'representative_phone' => '대표자 연락처',
            'company_address' => '회사 주소'
        ];
        
        foreach ($requiredFields as $field => $name) {
            if (empty($post[$field])) {
                return ['success' => false, 'message' => "{$name}을 입력해주세요."];
            }
        }
        
        // 길이 검증
        if (mb_strlen($post['company_name']) > 255) {
            return ['success' => false, 'message' => '회사명은 255자를 초과할 수 없습니다.'];
        }
        
        if (mb_strlen($post['representative_name']) > 100) {
            return ['success' => false, 'message' => '대표자명은 100자를 초과할 수 없습니다.'];
        }
        
        // 전화번호 형식 검증
        if (!ValidationHelper::isValidPhone($post['representative_phone'])) {
            return ['success' => false, 'message' => '올바른 전화번호 형식을 입력해주세요.'];
        }
        
        // 파일 검증
        if (empty($files['business_registration_file']['tmp_name'])) {
            return ['success' => false, 'message' => '사업자등록증 파일을 업로드해주세요.'];
        }
        
        return ['success' => true];
    }
    
    /**
     * 수정 데이터 검증
     */
    private function validateUpdateData($post) {
        // 필수 필드 검증
        $requiredFields = [
            'company_name' => '회사명',
            'representative_name' => '대표자명',
            'representative_phone' => '대표자 연락처',
            'company_address' => '회사 주소'
        ];
        
        foreach ($requiredFields as $field => $name) {
            if (empty($post[$field])) {
                return ['success' => false, 'message' => "{$name}을 입력해주세요."];
            }
        }
        
        // 길이 검증
        if (mb_strlen($post['company_name']) > 255) {
            return ['success' => false, 'message' => '회사명은 255자를 초과할 수 없습니다.'];
        }
        
        if (mb_strlen($post['representative_name']) > 100) {
            return ['success' => false, 'message' => '대표자명은 100자를 초과할 수 없습니다.'];
        }
        
        // 전화번호 형식 검증
        if (!ValidationHelper::isValidPhone($post['representative_phone'])) {
            return ['success' => false, 'message' => '올바른 전화번호 형식을 입력해주세요.'];
        }
        
        return ['success' => true];
    }
    
    /**
     * 파일 업로드 처리
     */
    private function handleFileUpload($file, $userId) {
        try {
            // 파일 업로드 검증
            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('파일 업로드 중 오류가 발생했습니다.');
            }
            
            // 파일 크기 검증 (공통 설정 사용: 30MB)
            if (!UploadConfig::validateFileSize($file['size'])) {
                throw new Exception(UploadConfig::getErrorMessage('file_too_large'));
            }
            
            // 파일 타입 검증
            $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedTypes)) {
                throw new Exception('JPG, PNG, PDF 파일만 업로드 가능합니다.');
            }
            
            // 업로드 디렉토리 설정
            $uploadDir = UPLOADS_PATH . '/corp_docs/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // 안전한 파일명 생성
            $extension = $this->getFileExtension($file['name']);
            $filename = sprintf(
                'business_reg_%d_%s.%s',
                $userId,
                uniqid(),
                $extension
            );
            
            $targetPath = $uploadDir . $filename;
            
            // 파일 이동
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                throw new Exception('파일 저장에 실패했습니다.');
            }
            
            return ['success' => true, 'filename' => $filename];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * 파일 확장자 추출
     */
    private function getFileExtension($filename) {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }
    
    /**
     * CSRF 토큰 검증
     */
    private function verifyCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && 
               hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * 뷰 렌더링
     */
    private function renderView($viewName, $data = [], $headerData = []) {
        // 뷰 데이터를 변수로 추출
        extract($data);
        
        // 공통 헤더 포함
        $defaultHeaderData = [
            'page_title' => '기업회원 - 탑마케팅',
            'page_description' => '탑마케팅 기업회원 서비스',
            'pageSection' => 'corporate'
        ];
        
        // 전달받은 헤더 데이터와 기본값 병합
        $headerData = array_merge($defaultHeaderData, $headerData);
        extract($headerData);
        
        // 헤더 포함
        require_once SRC_PATH . '/views/templates/header.php';
        
        // 메인 뷰 포함
        $viewPath = SRC_PATH . '/views/' . $viewName . '.php';
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            echo '<h1>뷰 파일을 찾을 수 없습니다</h1>';
            echo '<p>경로: ' . htmlspecialchars($viewPath) . '</p>';
        }
        
        // 푸터 포함
        require_once SRC_PATH . '/views/templates/footer.php';
    }
}