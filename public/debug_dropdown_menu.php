<?php
/**
 * 🔍 드롭다운 메뉴 디버깅 및 임시 수정
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=UTF-8');

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>🔍 드롭다운 메뉴 디버깅</title>";
echo "<style>body{font-family:monospace;background:#000;color:#0f0;padding:20px;} .error{color:#f00;} .success{color:#0f0;} .warning{color:#fa0;} pre{background:#111;padding:15px;border-radius:5px;} .btn{background:#667eea;color:white;padding:10px 20px;border:none;cursor:pointer;border-radius:5px;margin:10px 5px;}</style>";
echo "</head><body>";

echo "<h1>🔍 드롭다운 메뉴 디버깅 및 수정</h1>";

// 경로 설정
define('ROOT_PATH', realpath(__DIR__ . '/..'));
define('SRC_PATH', ROOT_PATH . '/src');

session_start();

echo "<h2>1️⃣ 현재 사용자 정보</h2>";
echo "<pre>";

try {
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    
    $isLoggedIn = AuthMiddleware::isLoggedIn();
    $currentUser = $isLoggedIn ? AuthMiddleware::getCurrentUser() : null;
    $userRole = $isLoggedIn ? AuthMiddleware::getUserRole() : null;
    $userId = $isLoggedIn ? AuthMiddleware::getCurrentUserId() : null;
    
    echo "<span class='".($isLoggedIn ? 'success' : 'error')."'>";
    echo ($isLoggedIn ? '✅' : '❌') . " 로그인 상태: " . ($isLoggedIn ? '로그인됨' : '로그인 안됨') . "</span>\n";
    
    if ($isLoggedIn) {
        echo "<span class='success'>✅ 사용자 ID: $userId</span>\n";
        echo "<span class='success'>✅ 사용자 닉네임: " . ($currentUser['nickname'] ?? 'N/A') . "</span>\n";
        echo "<span class='success'>✅ 사용자 권한: '$userRole'</span>\n";
        echo "<span class='success'>✅ 권한 타입: " . gettype($userRole) . "</span>\n";
        echo "<span class='success'>✅ 권한 길이: " . strlen($userRole) . "자</span>\n";
        
        // 세션 정보도 확인
        echo "\n세션 정보:\n";
        foreach ($_SESSION as $key => $value) {
            if (is_string($value) || is_numeric($value)) {
                echo "  $key: '$value'\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "<span class='error'>❌ 오류: " . $e->getMessage() . "</span>\n";
}

echo "</pre>";

if ($isLoggedIn) {
    echo "<h2>2️⃣ 드롭다운 메뉴 조건 테스트</h2>";
    echo "<pre>";
    
    $conditions = [
        'ROLE_CORP' => $userRole === 'ROLE_CORP',
        'ROLE_USER' => $userRole === 'ROLE_USER', 
        'GENERAL' => $userRole === 'GENERAL',
        'ROLE_ADMIN' => $userRole === 'ROLE_ADMIN',
        'USER' => $userRole === 'USER',
        'CORP' => $userRole === 'CORP',
        '!empty($userRole)' => !empty($userRole),
        'isLoggedIn' => $isLoggedIn
    ];
    
    echo "조건별 테스트 결과:\n";
    foreach ($conditions as $condition => $result) {
        echo "  $condition: " . ($result ? '✅ TRUE' : '❌ FALSE') . "\n";
    }
    
    $finalCondition = $isLoggedIn && ($userRole === 'ROLE_CORP' || $userRole === 'ROLE_USER' || $userRole === 'GENERAL' || $userRole === 'ROLE_ADMIN' || $userRole === 'USER' || $userRole === 'CORP' || !empty($userRole));
    
    echo "\n최종 조건 결과: " . ($finalCondition ? '<span class="success">✅ 메뉴 보여야 함</span>' : '<span class="error">❌ 메뉴 안 보임</span>') . "\n";
    
    echo "</pre>";
    
    if (!$finalCondition) {
        echo "<h2>3️⃣ 긴급 수정</h2>";
        echo "<div style='background:#222;padding:20px;border-radius:5px;color:#fff;'>";
        echo "<p>현재 권한 '$userRole'으로는 드롭다운 메뉴가 보이지 않습니다.</p>";
        echo "<p>아래 버튼을 클릭하여 임시로 헤더 템플릿을 수정하겠습니다:</p>";
        
        if (isset($_POST['fix_header'])) {
            try {
                $headerPath = SRC_PATH . '/views/templates/header.php';
                $headerContent = file_get_contents($headerPath);
                
                // 현재 권한을 포함하도록 조건 수정
                $oldCondition = 'if ($isLoggedIn && ($userRole === \'ROLE_CORP\' || $userRole === \'ROLE_USER\' || $userRole === \'GENERAL\' || $userRole === \'ROLE_ADMIN\' || $userRole === \'USER\' || $userRole === \'CORP\' || !empty($userRole))):';
                $newCondition = 'if ($isLoggedIn): // 로그인된 모든 사용자에게 표시';
                
                $newContent = str_replace($oldCondition, $newCondition, $headerContent);
                
                if ($newContent !== $headerContent) {
                    file_put_contents($headerPath, $newContent);
                    echo "<span class='success'>✅ 헤더 템플릿 수정 완료!</span><br>";
                    echo "<p>이제 모든 로그인된 사용자에게 '📊 신청 관리' 메뉴가 표시됩니다.</p>";
                    echo "<p><a href='/' style='color:#0f0;'>👉 홈페이지로 이동하여 확인</a></p>";
                } else {
                    echo "<span class='warning'>⚠️ 이미 수정되었거나 조건을 찾을 수 없습니다.</span>";
                }
                
            } catch (Exception $e) {
                echo "<span class='error'>❌ 수정 실패: " . $e->getMessage() . "</span>";
            }
        } else {
            echo "<form method='post'>";
            echo "<button type='submit' name='fix_header' class='btn'>🔧 헤더 템플릿 즉시 수정</button>";
            echo "</form>";
        }
        
        echo "</div>";
    }
}

echo "<h2>4️⃣ 데이터베이스 사용자 정보</h2>";
echo "<pre>";

try {
    require_once SRC_PATH . '/config/database.php';
    $db = Database::getInstance();
    
    if ($isLoggedIn && $userId) {
        $result = $db->query("SELECT id, nickname, phone, role, status, created_at FROM users WHERE id = $userId");
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            echo "<span class='success'>✅ 데이터베이스 사용자 정보:</span>\n";
            foreach ($user as $key => $value) {
                echo "  $key: '$value'\n";
            }
            
            // 권한이 다른 경우 업데이트 옵션 제공
            if ($user['role'] !== 'ROLE_CORP' && $user['role'] !== 'ROLE_USER') {
                echo "\n<span class='warning'>⚠️ 현재 권한이 일반적이지 않습니다.</span>\n";
                echo "권한을 'ROLE_CORP'로 변경하시겠습니까?\n";
                
                if (isset($_POST['update_role'])) {
                    $updateQuery = "UPDATE users SET role = 'ROLE_CORP' WHERE id = $userId";
                    if ($db->query($updateQuery)) {
                        echo "<span class='success'>✅ 권한이 ROLE_CORP로 변경되었습니다!</span>\n";
                        echo "<a href='/auth/logout' style='color:#0f0;'>👉 로그아웃 후 다시 로그인하세요</a>\n";
                    } else {
                        echo "<span class='error'>❌ 권한 변경 실패</span>\n";
                    }
                }
            }
        }
    }
    
} catch (Exception $e) {
    echo "<span class='error'>❌ 데이터베이스 오류: " . $e->getMessage() . "</span>\n";
}

echo "</pre>";

if ($isLoggedIn && $userId && !isset($_POST['update_role']) && !isset($_POST['fix_header'])) {
    echo "<div style='background:#222;padding:20px;border-radius:5px;color:#fff;margin:20px 0;'>";
    echo "<h3>🚀 즉시 해결 옵션</h3>";
    echo "<form method='post' style='display:inline;'>";
    echo "<button type='submit' name='update_role' class='btn'>1️⃣ 권한을 ROLE_CORP로 변경</button>";
    echo "</form>";
    echo "<form method='post' style='display:inline;'>";
    echo "<button type='submit' name='fix_header' class='btn'>2️⃣ 헤더 템플릿 수정 (모든 사용자에게 표시)</button>";
    echo "</form>";
    echo "</div>";
}

echo "</body></html>";
?>