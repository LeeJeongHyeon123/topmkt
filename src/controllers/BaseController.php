<?php
/**
 * 기본 컨트롤러 클래스
 * 모든 컨트롤러의 기본 기능을 제공합니다.
 */

require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/config/upload.php';

class BaseController
{
    protected $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * JSON 응답 반환
     */
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * 성공 JSON 응답
     */
    protected function success($message = 'Success', $data = null)
    {
        $response = [
            'status' => 'success',
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        return $this->json($response);
    }
    
    /**
     * 오류 JSON 응답
     */
    protected function error($message = 'Error', $statusCode = 400, $data = null)
    {
        $response = [
            'status' => 'error',
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        return $this->json($response, $statusCode);
    }
    
    /**
     * 입력값 검증
     */
    protected function validate($input, $rules)
    {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $input[$field] ?? null;
            
            if (strpos($rule, 'required') !== false && empty($value)) {
                $errors[$field] = ucfirst($field) . ' is required';
                continue;
            }
            
            if (strpos($rule, 'email') !== false && !empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = ucfirst($field) . ' must be a valid email';
            }
            
            if (preg_match('/min:(\d+)/', $rule, $matches) && !empty($value) && strlen($value) < $matches[1]) {
                $errors[$field] = ucfirst($field) . ' must be at least ' . $matches[1] . ' characters';
            }
            
            if (preg_match('/max:(\d+)/', $rule, $matches) && !empty($value) && strlen($value) > $matches[1]) {
                $errors[$field] = ucfirst($field) . ' must be no more than ' . $matches[1] . ' characters';
            }
        }
        
        return $errors;
    }
    
    /**
     * CSRF 토큰 검증
     */
    protected function validateCSRF($token)
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * 페이지네이션 정보 생성
     */
    protected function paginate($currentPage, $totalItems, $itemsPerPage = 20)
    {
        $totalPages = ceil($totalItems / $itemsPerPage);
        $offset = ($currentPage - 1) * $itemsPerPage;
        
        return [
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'total_items' => $totalItems,
            'items_per_page' => $itemsPerPage,
            'offset' => $offset,
            'has_prev' => $currentPage > 1,
            'has_next' => $currentPage < $totalPages
        ];
    }
    
    /**
     * 안전한 리다이렉트
     */
    protected function redirect($url, $statusCode = 302)
    {
        // XSS 방지를 위한 URL 검증
        $allowedDomains = ['/', 'http://localhost', 'https://localhost'];
        $isAllowed = false;
        
        foreach ($allowedDomains as $domain) {
            if (strpos($url, $domain) === 0) {
                $isAllowed = true;
                break;
            }
        }
        
        if (!$isAllowed && strpos($url, '/') === 0) {
            $isAllowed = true; // 상대 경로 허용
        }
        
        if ($isAllowed) {
            header("Location: $url", true, $statusCode);
            exit;
        } else {
            header("Location: /", true, $statusCode);
            exit;
        }
    }
    
    /**
     * 파일 업로드 처리
     */
    protected function handleFileUpload($file, $uploadDir, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'])
    {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        
        $fileName = $file['name'];
        $fileSize = $file['size'];
        $fileTmp = $file['tmp_name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // 파일 타입 검증
        if (!in_array($fileExt, $allowedTypes)) {
            return false;
        }
        
        // 파일 크기 검증 (공통 설정 사용: 30MB)
        if (!UploadConfig::validateFileSize($fileSize)) {
            return false;
        }
        
        // 고유한 파일명 생성
        $newFileName = uniqid() . '.' . $fileExt;
        $uploadPath = $uploadDir . '/' . $newFileName;
        
        // 업로드 디렉토리 생성
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // 파일 이동
        if (move_uploaded_file($fileTmp, $uploadPath)) {
            return $newFileName;
        }
        
        return false;
    }
}
?>