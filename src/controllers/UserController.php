<?php
/**
 * 사용자 관련 컨트롤러
 */

require_once SRC_PATH . '/controllers/BaseController.php';
require_once SRC_PATH . '/models/User.php';
require_once SRC_PATH . '/models/UserOptimized.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
require_once SRC_PATH . '/helpers/ValidationHelper.php';
require_once SRC_PATH . '/helpers/ResponseHelper.php';
require_once SRC_PATH . '/config/upload.php';

class UserController extends BaseController {
    private $userModel;
    private $userOptimized;

    public function __construct() {
        parent::__construct(); // BaseController의 생성자 호출
        $this->userModel = new User();
        $this->userOptimized = new UserOptimized();
    }
    
    /**
     * 프로필 페이지 표시 (/profile 또는 /profile?user_id=XX)
     */
    public function showMyProfile() {
        // 로그인 확인
        if (!AuthMiddleware::isLoggedIn()) {
            header('Location: /auth/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            return;
        }
        
        // user_id 파라미터가 있으면 해당 사용자 프로필, 없으면 내 프로필
        $targetUserId = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
        $currentUserId = AuthMiddleware::getCurrentUserId();
        
        // 타겟 사용자 결정 (user_id 파라미터가 있으면 해당 사용자, 없으면 현재 사용자)
        $viewUserId = $targetUserId ?: $currentUserId;
        
        try {
            // 성능 모니터링 시작
            $startTime = microtime(true);
            $memoryStart = memory_get_usage(true);
            
            // 데이터베이스 쿼리 시간 측정 (최적화된 방식 사용)
            $dbStart = microtime(true);
            $user = $this->userOptimized->getOptimizedProfileDataWithCache($viewUserId);
            $dbTime = (microtime(true) - $dbStart) * 1000;
            
            if (!$user) {
                if ($targetUserId) {
                    // 다른 사용자 프로필 조회 실패 시 404 처리
                    http_response_code(404);
                    echo "사용자를 찾을 수 없습니다.";
                } else {
                    header('Location: /auth/login');
                }
                return;
            }
            
            // 최적화된 데이터에서 통계와 최근 활동 분리
            $stats = $user['stats'];
            $recentPosts = $user['recent_posts'] ?? [];
            $recentComments = $user['recent_comments'] ?? [];
            
            // 성능 측정 완료
            $totalTime = (microtime(true) - $startTime) * 1000;
            $memoryUsed = memory_get_usage(true) - $memoryStart;
            $peakMemory = memory_get_peak_usage(true);
            
            // 상세 성능 로깅
            $performanceLog = [
                'user_id' => $currentUserId,
                'total_time' => round($totalTime, 2),
                'db_time' => round($dbTime, 2),
                'memory_used' => round($memoryUsed / 1024 / 1024, 2),
                'peak_memory' => round($peakMemory / 1024 / 1024, 2),
                'post_count' => count($recentPosts),
                'comment_count' => count($recentComments),
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            // 성능 로그 기록 (항상 기록)
            error_log("Profile Performance: " . json_encode($performanceLog));
            
            // 느린 경우 경고 로그
            if ($totalTime > 500) {
                error_log("⚠️ SLOW PROFILE: {$totalTime}ms for user {$viewUserId}");
            } else if ($totalTime > 200) {
                error_log("⚠️ MODERATE PROFILE: {$totalTime}ms for user {$viewUserId}");
            }
            
            // 페이지 변수 설정
            $pageSection = 'profile';
            $page_title = $user['nickname'] . '님의 프로필';
            $isOwnProfile = ($viewUserId === $currentUserId); // 내 프로필인지 확인
            
            // OG 태그 설정
            $page_description = !empty($user['bio']) ? 
                htmlspecialchars(strip_tags(mb_substr($user['bio'], 0, 150))) : 
                $user['nickname'] . '님의 탑마케팅 프로필입니다.';
            
            $og_title = $user['nickname'] . '님의 프로필 - 탑마케팅';
            $og_description = $page_description;
            $og_type = 'profile';
            
            // 프로필 이미지가 있으면 OG 이미지로 사용
            $og_image = 'https://' . $_SERVER['HTTP_HOST'] . '/assets/images/topmkt-og-image.png?v=' . date('Ymd');
            if (!empty($user['profile_image_original'])) {
                $og_image = 'https://' . $_SERVER['HTTP_HOST'] . $user['profile_image_original'];
            } elseif (!empty($user['profile_image_profile'])) {
                $og_image = 'https://' . $_SERVER['HTTP_HOST'] . $user['profile_image_profile'];
            }
            
            $keywords = '탑마케팅, ' . $user['nickname'] . ', 프로필, 마케팅 전문가, 네트워크 마케팅';
            
            // 헤더 포함
            require_once SRC_PATH . '/views/templates/header.php';
            
            // 프로필 페이지 표시
            require_once SRC_PATH . '/views/user/profile.php';
            
            // 푸터 포함
            require_once SRC_PATH . '/views/templates/footer.php';
            
        } catch (Exception $e) {
            error_log('프로필 조회 오류: ' . $e->getMessage());
            error_log('오류 파일: ' . $e->getFile() . ':' . $e->getLine());
            error_log('스택 추적: ' . $e->getTraceAsString());

            header('HTTP/1.1 500 Internal Server Error');
            echo '프로필을 불러오는 중 오류가 발생했습니다.';
        }
    }
    
    /**
     * 다른 사용자 프로필 페이지 표시 (/profile/{nickname})
     */
    public function showPublicProfile($nickname) {
        try {
            // URL 디코딩
            $nickname = urldecode($nickname);
            
            // 공개 프로필 정보 조회
            $user = $this->userModel->getPublicProfile($nickname);
            if (!$user) {
                header('HTTP/1.1 404 Not Found');
                require_once SRC_PATH . '/views/templates/404.php';
                return;
            }
            
            // 활동 통계 조회
            $stats = $this->userModel->getProfileStats($user['id']);
            
            // 최근 게시글 (공개용)
            $recentPosts = $this->userModel->getRecentPosts($user['id'], 5);
            
            // 최근 댓글 (공개용)
            $recentComments = $this->userModel->getRecentComments($user['id'], 5);
            
            // 현재 사용자 확인
            $currentUserId = AuthMiddleware::getCurrentUserId();
            $isOwnProfile = ($currentUserId == $user['id']);
            
            // 페이지 변수 설정
            $pageSection = 'profile';
            $page_title = $user['nickname'] . '님의 프로필';
            
            // OG 태그 설정
            $page_description = !empty($user['bio']) ? 
                htmlspecialchars(strip_tags(mb_substr($user['bio'], 0, 150))) : 
                $user['nickname'] . '님의 탑마케팅 프로필입니다.';
            
            $og_title = $user['nickname'] . '님의 프로필 - 탑마케팅';
            $og_description = $page_description;
            $og_type = 'profile';
            
            // 프로필 이미지가 있으면 OG 이미지로 사용
            $og_image = 'https://' . $_SERVER['HTTP_HOST'] . '/assets/images/topmkt-og-image.png?v=' . date('Ymd');
            if (!empty($user['profile_image_original'])) {
                $og_image = 'https://' . $_SERVER['HTTP_HOST'] . $user['profile_image_original'];
            } elseif (!empty($user['profile_image_profile'])) {
                $og_image = 'https://' . $_SERVER['HTTP_HOST'] . $user['profile_image_profile'];
            }
            
            $keywords = '탑마케팅, ' . $user['nickname'] . ', 프로필, 마케팅 전문가, 네트워크 마케팅';
            
            // 헤더 포함
            require_once SRC_PATH . '/views/templates/header.php';
            
            // 프로필 페이지 표시
            require_once SRC_PATH . '/views/user/profile.php';
            
            // 푸터 포함
            require_once SRC_PATH . '/views/templates/footer.php';
            
        } catch (Exception $e) {
            error_log('공개 프로필 조회 오류: ' . $e->getMessage());
            header('HTTP/1.1 500 Internal Server Error');
            echo '프로필을 불러오는 중 오류가 발생했습니다.';
        }
    }
    
    /**
     * 프로필 편집 페이지 표시 (/profile/edit)
     */
    public function showEditProfile() {
        // 로그인 확인
        if (!AuthMiddleware::isLoggedIn()) {
            header('Location: /auth/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            return;
        }
        
        $currentUserId = AuthMiddleware::getCurrentUserId();
        
        try {
            // 프로필 정보 조회
            $user = $this->userModel->getFullProfile($currentUserId);
            if (!$user) {
                header('Location: /auth/login');
                return;
            }
            
            // 페이지 변수 설정
            $pageSection = 'profile';
            $page_title = '프로필 편집';
            
            // 헤더 포함
            require_once SRC_PATH . '/views/templates/header.php';
            
            // 프로필 편집 페이지 표시
            require_once SRC_PATH . '/views/user/edit.php';
            
            // 푸터 포함
            require_once SRC_PATH . '/views/templates/footer.php';
            
        } catch (Exception $e) {
            error_log('프로필 편집 페이지 오류: ' . $e->getMessage());
            header('HTTP/1.1 500 Internal Server Error');
            echo '페이지를 불러오는 중 오류가 발생했습니다.';
        }
    }
    
    /**
     * 프로필 정보 업데이트 (POST /profile/update)
     */
    public function updateProfile() {
        // 로그인 확인
        if (!AuthMiddleware::isLoggedIn()) {
            ResponseHelper::json(['error' => '로그인이 필요합니다.'], 401);
            return;
        }
        
        // CSRF 토큰 확인
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            ResponseHelper::json(['error' => 'CSRF 토큰이 유효하지 않습니다.'], 403);
            return;
        }
        
        $currentUserId = AuthMiddleware::getCurrentUserId();
        
        try {
            // 입력 데이터 검증 및 정제
            $profileData = [];
            
            // 닉네임 (필수)
            if (isset($_POST['nickname'])) {
                $nickname = trim($_POST['nickname']);
                if (empty($nickname)) {
                    ResponseHelper::json(['error' => '닉네임은 필수 입력 항목입니다.'], 400);
                    return;
                }
                
                // 닉네임 중복 확인 (본인 제외)
                $existingUser = $this->userModel->findByNickname($nickname);
                if ($existingUser && $existingUser['id'] != $currentUserId) {
                    ResponseHelper::json(['error' => '이미 사용 중인 닉네임입니다.'], 400);
                    return;
                }
                
                $profileData['nickname'] = $nickname;
            }
            
            // 이메일 (필수)
            if (isset($_POST['email'])) {
                $email = trim($_POST['email']);
                if (empty($email)) {
                    ResponseHelper::json(['error' => '이메일은 필수 입력 항목입니다.'], 400);
                    return;
                }
                
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    ResponseHelper::json(['error' => '유효한 이메일 주소를 입력해주세요.'], 400);
                    return;
                }
                
                // 이메일 중복 확인 (본인 제외)
                $existingUser = $this->userModel->findByEmail($email);
                if ($existingUser && $existingUser['id'] != $currentUserId) {
                    ResponseHelper::json(['error' => '이미 사용 중인 이메일입니다.'], 400);
                    return;
                }
                
                $profileData['email'] = $email;
            } else {
                ResponseHelper::json(['error' => '이메일은 필수 입력 항목입니다.'], 400);
                return;
            }
            
            // 자기소개 (선택)
            if (isset($_POST['bio'])) {
                $bio = trim($_POST['bio']);
                // HTML 태그를 제거하고 순수 텍스트 길이만 계산
                $bioText = strip_tags($bio);
                if (mb_strlen($bioText) > 2000) {
                    ResponseHelper::json(['error' => '자기소개는 2000자 이하로 입력해주세요. (현재: ' . mb_strlen($bioText) . '자)'], 400);
                    return;
                }
                $profileData['bio'] = $bio;
            }
            
            // 생년월일 (선택)
            if (isset($_POST['birth_date']) && !empty($_POST['birth_date'])) {
                $birthDate = $_POST['birth_date'];
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthDate)) {
                    ResponseHelper::json(['error' => '유효한 생년월일을 입력해주세요.'], 400);
                    return;
                }
                $profileData['birth_date'] = $birthDate;
            }
            
            // 성별 (선택)
            if (isset($_POST['gender']) && !empty($_POST['gender'])) {
                $gender = $_POST['gender'];
                if (!in_array($gender, ['M', 'F', 'OTHER'])) {
                    ResponseHelper::json(['error' => '유효한 성별을 선택해주세요.'], 400);
                    return;
                }
                $profileData['gender'] = $gender;
            }
            
            // 웹사이트 URL 필드 제거됨 (social_website를 사용)
            
            // 소셜 링크 (선택)
            $socialLinks = [];
            $socialPlatforms = ['kakao', 'website', 'instagram', 'facebook', 'youtube', 'tiktok'];
            
            foreach ($socialPlatforms as $platform) {
                if (isset($_POST["social_{$platform}"]) && !empty($_POST["social_{$platform}"])) {
                    $url = trim($_POST["social_{$platform}"]);
                    if (!filter_var($url, FILTER_VALIDATE_URL)) {
                        ResponseHelper::json(['error' => "유효한 {$platform} URL을 입력해주세요."], 400);
                        return;
                    }
                    $socialLinks[$platform] = $url;
                }
            }
            
            if (!empty($socialLinks)) {
                $profileData['social_links'] = $socialLinks;
            }
            
            // 프로필 업데이트
            $result = $this->userModel->updateProfile($currentUserId, $profileData);
            
            if ($result) {
                // 프로필 캐시 무효화
                $this->userOptimized->invalidateProfileCache($currentUserId);
                
                // 세션 정보 업데이트
                if (isset($profileData['nickname'])) {
                    $_SESSION['username'] = $profileData['nickname'];
                }
                
                // 프로필 이미지 업로드 처리
                $imageMessage = '';
                if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                    try {
                        $imageResult = $this->handleProfileImageUpload($currentUserId);
                        if ($imageResult['success']) {
                            $imageMessage = ' 프로필 이미지도 업데이트되었습니다.';
                            // 세션 프로필 이미지 업데이트
                            $_SESSION['profile_image'] = $imageResult['thumb_path'];
                        } else {
                            $imageMessage = ' (이미지 업로드는 실패했습니다: ' . $imageResult['error'] . ')';
                        }
                    } catch (Exception $e) {
                        error_log('프로필 이미지 업로드 오류: ' . $e->getMessage());
                        $imageMessage = ' (이미지 업로드 중 오류가 발생했습니다)';
                    }
                }
                
                ResponseHelper::json(['message' => '프로필이 성공적으로 업데이트되었습니다.' . $imageMessage]);
            } else {
                ResponseHelper::json(['error' => '프로필 업데이트에 실패했습니다.'], 500);
            }
            
        } catch (Exception $e) {
            error_log('프로필 업데이트 오류: ' . $e->getMessage());
            ResponseHelper::json(['error' => '프로필 업데이트 중 오류가 발생했습니다.'], 500);
        }
    }
    
    /**
     * 프로필 이미지 업로드 (POST /profile/upload-image)
     */
    public function uploadProfileImage() {
        // 로그인 확인
        if (!AuthMiddleware::isLoggedIn()) {
            ResponseHelper::json(['error' => '로그인이 필요합니다.'], 401);
            return;
        }
        
        // CSRF 토큰 확인
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            ResponseHelper::json(['error' => 'CSRF 토큰이 유효하지 않습니다.'], 403);
            return;
        }
        
        $currentUserId = AuthMiddleware::getCurrentUserId();
        
        try {
            // 파일 업로드 확인
            if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
                ResponseHelper::json(['error' => '파일 업로드에 실패했습니다.'], 400);
                return;
            }
            
            $file = $_FILES['profile_image'];
            
            // 파일 크기 확인 (공통 설정 사용: 30MB)
            if (!UploadConfig::validateFileSize($file['size'])) {
                ResponseHelper::json(['error' => UploadConfig::getErrorMessage('file_too_large')], 400);
                return;
            }
            
            // 파일 형식 확인
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedTypes)) {
                ResponseHelper::json(['error' => '지원하지 않는 파일 형식입니다. (JPG, PNG, GIF, WebP만 허용)'], 400);
                return;
            }
            
            // 업로드 디렉토리 생성 (보안 강화)
            $uploadDir = PROFILES_UPLOAD_PATH . '/' . date('Y/m');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // 안전한 파일명 생성 (확장자 검증 추가)
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($extension, $allowedExtensions)) {
                ResponseHelper::json(['error' => '허용되지 않는 파일 확장자입니다.'], 400);
                return;
            }
            
            // 경로 조작 공격 방지
            $cleanExtension = preg_replace('/[^a-z0-9]/', '', $extension);
            $filename = 'user_' . $currentUserId . '_' . time() . '_' . bin2hex(random_bytes(8));
            
            // 3가지 크기로 이미지 저장
            $originalPath = $this->saveProfileImage($file['tmp_name'], $uploadDir . '/' . $filename . '_original.' . $extension, 'original');
            $profilePath = $this->saveProfileImage($file['tmp_name'], $uploadDir . '/' . $filename . '_profile.' . $extension, 'profile');
            $thumbPath = $this->saveProfileImage($file['tmp_name'], $uploadDir . '/' . $filename . '_thumb.' . $extension, 'thumb');
            
            // 상대 경로로 변환
            $webOriginalPath = str_replace(ROOT_PATH . '/public', '', $originalPath);
            $webProfilePath = str_replace(ROOT_PATH . '/public', '', $profilePath);
            $webThumbPath = str_replace(ROOT_PATH . '/public', '', $thumbPath);
            
            // 데이터베이스 업데이트
            $result = $this->userModel->updateProfileImages($currentUserId, $webOriginalPath, $webProfilePath, $webThumbPath);
            
            if ($result) {
                // 세션 업데이트 (헤더 프로필 이미지 즉시 반영)
                $_SESSION['profile_image'] = $webThumbPath;
                
                ResponseHelper::json([
                    'message' => '프로필 이미지가 성공적으로 업로드되었습니다.',
                    'images' => [
                        'original' => $webOriginalPath,
                        'profile' => $webProfilePath,
                        'thumb' => $webThumbPath
                    ]
                ]);
            } else {
                ResponseHelper::json(['error' => '이미지 정보 저장에 실패했습니다.'], 500);
            }
            
        } catch (Exception $e) {
            error_log('프로필 이미지 업로드 오류: ' . $e->getMessage());
            ResponseHelper::json(['error' => '이미지 업로드 중 오류가 발생했습니다.'], 500);
        }
    }
    
    /**
     * 프로필 이미지 저장 및 리사이징
     */
    private function saveProfileImage($sourcePath, $targetPath, $type) {
        // 이미지 정보 가져오기
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            throw new Exception('유효하지 않은 이미지 파일입니다.');
        }
        
        list($originalWidth, $originalHeight, $imageType) = $imageInfo;
        
        // 타입별 크기 설정
        switch ($type) {
            case 'original':
                $maxWidth = 1000;
                $maxHeight = 1000;
                break;
            case 'profile':
                $maxWidth = 200;
                $maxHeight = 200;
                break;
            case 'thumb':
                $maxWidth = 80;
                $maxHeight = 80;
                break;
            default:
                throw new Exception('알 수 없는 이미지 타입입니다.');
        }
        
        // GD 확장이 없으면 원본 파일을 그대로 복사
        if (!extension_loaded('gd')) {
            error_log('GD 확장이 로드되지 않음 - 원본 파일 복사');
            if (!copy($sourcePath, $targetPath)) {
                throw new Exception('이미지 파일 저장에 실패했습니다.');
            }
            return $targetPath;
        }
        
        // 리사이징 계산
        if ($type === 'original' && $originalWidth <= $maxWidth && $originalHeight <= $maxHeight) {
            // 원본이 이미 작으면 그대로 복사
            copy($sourcePath, $targetPath);
            return $targetPath;
        }
        
        // 비율 유지하면서 리사이징
        $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
        $newWidth = round($originalWidth * $ratio);
        $newHeight = round($originalHeight * $ratio);
        
        // 소스 이미지 생성
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_GIF:
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            case IMAGETYPE_WEBP:
                if (function_exists('imagecreatefromwebp')) {
                    $sourceImage = imagecreatefromwebp($sourcePath);
                } else {
                    throw new Exception('WebP 형식은 지원되지 않습니다.');
                }
                break;
            default:
                throw new Exception('지원하지 않는 이미지 형식입니다.');
        }
        
        if (!$sourceImage) {
            throw new Exception('이미지 파일을 읽을 수 없습니다.');
        }
        
        // 새 이미지 생성
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        if (!$newImage) {
            imagedestroy($sourceImage);
            throw new Exception('새 이미지를 생성할 수 없습니다.');
        }
        
        // PNG나 GIF의 투명도 유지
        if ($imageType == IMAGETYPE_PNG || $imageType == IMAGETYPE_GIF) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        // 이미지 리샘플링
        $resampleResult = imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
        if (!$resampleResult) {
            imagedestroy($sourceImage);
            imagedestroy($newImage);
            throw new Exception('이미지 리사이징에 실패했습니다.');
        }
        
        // 🚀 Phase 4: 모든 이미지를 WebP로 변환 저장
        // 기존 확장자를 .webp로 변경
        $webpTargetPath = preg_replace('/\.(jpg|jpeg|png|gif)$/i', '.webp', $targetPath);

        // PNG 투명도 보존
        if ($imageType == IMAGETYPE_PNG || $imageType == IMAGETYPE_GIF) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
        }

        // WebP로 저장
        $saveResult = false;
        if (function_exists('imagewebp')) {
            $saveResult = imagewebp($newImage, $webpTargetPath, 85);
            if ($saveResult) {
                error_log("✅ UserController: WebP 변환 완료 - " . basename($webpTargetPath));
                // targetPath도 WebP 경로로 업데이트
                $targetPath = $webpTargetPath;
            }
        }

        // WebP 변환 실패 시 원본 형식으로 저장 (fallback)
        if (!$saveResult) {
            switch ($imageType) {
                case IMAGETYPE_JPEG:
                    $saveResult = imagejpeg($newImage, $targetPath, 85);
                    break;
                case IMAGETYPE_PNG:
                    $saveResult = imagepng($newImage, $targetPath, 8);
                    break;
                case IMAGETYPE_GIF:
                    $saveResult = imagegif($newImage, $targetPath);
                    break;
            }
            error_log("⚠️ UserController: WebP 변환 실패, 원본 형식 저장 - " . basename($targetPath));
        }
        
        // 메모리 해제
        imagedestroy($sourceImage);
        imagedestroy($newImage);
        
        if (!$saveResult) {
            throw new Exception('이미지 파일 저장에 실패했습니다.');
        }
        
        return $targetPath;
    }
    
    /**
     * 프로필 이미지 업로드 처리 (updateProfile에서 호출)
     */
    private function handleProfileImageUpload($userId) {
        $file = $_FILES['profile_image'];
        
        // 파일 크기 확인 (공통 설정 사용: 30MB)
        if (!UploadConfig::validateFileSize($file['size'])) {
            return ['success' => false, 'error' => UploadConfig::getErrorMessage('file_too_large')];
        }
        
        // 파일 형식 확인
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            return ['success' => false, 'error' => '지원하지 않는 파일 형식입니다.'];
        }
        
        // 업로드 디렉토리 생성
        $uploadDir = PROFILES_UPLOAD_PATH . '/' . date('Y/m');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // 안전한 파일명 생성
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (empty($extension)) {
            $extension = 'jpg'; // 기본 확장자
        }
        $filename = 'user_' . $userId . '_' . time() . '_' . bin2hex(random_bytes(8));
        
        // 3가지 크기로 이미지 저장
        $originalPath = $this->saveProfileImage($file['tmp_name'], $uploadDir . '/' . $filename . '_original.' . $extension, 'original');
        $profilePath = $this->saveProfileImage($file['tmp_name'], $uploadDir . '/' . $filename . '_profile.' . $extension, 'profile');
        $thumbPath = $this->saveProfileImage($file['tmp_name'], $uploadDir . '/' . $filename . '_thumb.' . $extension, 'thumb');
        
        // 상대 경로로 변환
        $webOriginalPath = str_replace(ROOT_PATH . '/public', '', $originalPath);
        $webProfilePath = str_replace(ROOT_PATH . '/public', '', $profilePath);
        $webThumbPath = str_replace(ROOT_PATH . '/public', '', $thumbPath);
        
        // 데이터베이스 업데이트
        $result = $this->userModel->updateProfileImages($userId, $webOriginalPath, $webProfilePath, $webThumbPath);
        
        if ($result) {
            return [
                'success' => true,
                'original_path' => $webOriginalPath,
                'profile_path' => $webProfilePath,
                'thumb_path' => $webThumbPath
            ];
        } else {
            return ['success' => false, 'error' => '이미지 정보 저장에 실패했습니다.'];
        }
    }
    
    // 기존 메서드들 (호환성 유지)
    public function showProfile() {
        return $this->showMyProfile();
    }
    
    public function getUser($id = null) {
        // 기존 API 호환성 유지
        if ($id === null) {
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            preg_match('/\/users\/(\d+)/', $uri, $matches);
            $id = $matches[1] ?? null;
        }
        
        if (!$id) {
            ResponseHelper::json(['error' => 'User ID is required'], 400);
            return;
        }
        
        try {
            $user = $this->userModel->getPublicProfile($id);
            if (!$user) {
                ResponseHelper::json(['error' => 'User not found'], 404);
                return;
            }
            
            $stats = $this->userModel->getProfileStats($user['id']);
            
            ResponseHelper::json([
                'user' => $user,
                'stats' => $stats
            ]);
            
        } catch (Exception $e) {
            error_log('API 사용자 조회 오류: ' . $e->getMessage());
            ResponseHelper::json(['error' => 'Internal server error'], 500);
        }
    }
    
    public function updateUser() {
        return $this->updateProfile();
    }
    
    public function deleteUser() {
        // 계정 삭제는 향후 구현
        ResponseHelper::json(['error' => 'Not implemented'], 501);
    }
    
    /**
     * 사용자 원본 프로필 이미지 API (/api/users/{id}/profile-image)
     */
    public function getProfileImage($userId = null) {
        // URL에서 사용자 ID 추출
        if ($userId === null) {
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            if (preg_match('/\/api\/users\/(\d+)\/profile-image/', $uri, $matches)) {
                $userId = $matches[1];
            }
        }
        
        if (!$userId) {
            ResponseHelper::json(['error' => 'User ID is required'], 400);
            return;
        }
        
        try {
            // User 모델의 메서드 사용
            $user = $this->userModel->getProfileImageInfo($userId);

            if (!$user) {
                ResponseHelper::json(['error' => 'User not found'], 404);
                return;
            }

            // 탈퇴한 회원 처리
            $isDeleted = (isset($user['status']) && $user['status'] === 'deleted');

            // 프로필 이미지 우선순위로 반환 (profile_image -> profile_image_original -> profile_image_profile)
            $profileImage = $user['profile_image'] ?? $user['profile_image_original'] ?? $user['profile_image_profile'] ?? null;
            $originalImage = $user['profile_image_original'] ?? null;

            ResponseHelper::json([
                'user_id' => $user['id'],
                'nickname' => $user['nickname'],
                'status' => $user['status'] ?? 'active',
                'is_deleted' => $isDeleted,
                'original_image' => $originalImage,
                'profile_image' => $profileImage,
                'thumb_image' => $user['profile_image_thumb']
            ]);

        } catch (Exception $e) {
            error_log('프로필 이미지 API 오류: ' . $e->getMessage());
            ResponseHelper::json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * 회원 탈퇴 처리
     * POST /api/user/delete-account
     */
    public function deleteAccount() {
        // 디버깅 로그 추가
        error_log("=== 회원탈퇴 API 호출 시작 ===");

        // 로그인 확인
        if (!AuthMiddleware::isLoggedIn()) {
            error_log("❌ 회원탈퇴 실패: 로그인 필요");
            ResponseHelper::json(['message' => '로그인이 필요합니다.'], 401);
            return;
        }

        // 현재 사용자 ID 가져오기
        $userId = AuthMiddleware::getCurrentUserId();
        error_log("✅ 현재 사용자 ID: " . $userId);

        // POST 데이터 확인
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("❌ 회원탈퇴 실패: 잘못된 요청 방식 - " . $_SERVER['REQUEST_METHOD']);
            ResponseHelper::json(['message' => '잘못된 요청입니다.'], 405);
            return;
        }

        // JSON 데이터 파싱
        $rawInput = file_get_contents('php://input');
        error_log("📋 Raw Input: " . $rawInput);

        $data = json_decode($rawInput, true);
        error_log("📋 Parsed Data: " . print_r($data, true));

        // 필수 필드 검증
        if (!isset($data['password']) || empty($data['password'])) {
            error_log("❌ 회원탈퇴 실패: 비밀번호 누락");
            ResponseHelper::json(['message' => '비밀번호를 입력해주세요.'], 400);
            return;
        }

        // CSRF 토큰 검증
        error_log("🔒 CSRF 토큰 검증 - 요청: " . ($data['csrf_token'] ?? 'null') . ", 세션: " . ($_SESSION['csrf_token'] ?? 'null'));
        if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
            error_log("❌ 회원탈퇴 실패: CSRF 토큰 불일치");
            ResponseHelper::json(['message' => '보안 검증에 실패했습니다. 페이지를 새로고침 후 다시 시도해주세요.'], 403);
            return;
        }

        try {
            // 회원 탈퇴 처리
            $result = $this->userModel->deleteAccount(
                $userId,
                $data['password'],
                isset($data['reason']) ? $data['reason'] : null
            );

            if ($result['success']) {
                // 세션 종료
                session_destroy();

                // 쿠키 삭제
                if (isset($_COOKIE[session_name()])) {
                    setcookie(session_name(), '', time() - 3600, '/');
                }

                ResponseHelper::json([
                    'success' => true,
                    'message' => $result['message']
                ], 200);
            } else {
                ResponseHelper::json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

        } catch (Exception $e) {
            error_log('회원 탈퇴 처리 오류: ' . $e->getMessage());
            ResponseHelper::json([
                'success' => false,
                'message' => '회원 탈퇴 처리 중 오류가 발생했습니다.'
            ], 500);
        }
    }
}