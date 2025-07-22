<?php
/**
 * 🔍 사용자 권한 및 드롭다운 메뉴 디버깅
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=UTF-8');

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>🔍 사용자 권한 디버깅</title>";
echo "<style>body{font-family:monospace;background:#000;color:#0f0;padding:20px;} .error{color:#f00;} .success{color:#0f0;} .warning{color:#fa0;} pre{background:#111;padding:15px;border-radius:5px;}</style>";
echo "</head><body>";

echo "<h1>🔍 사용자 권한 및 드롭다운 메뉴 디버깅</h1>";

// 경로 설정
define('ROOT_PATH', realpath(__DIR__ . '/..'));
define('SRC_PATH', ROOT_PATH . '/src');

session_start();

echo "<h2>1️⃣ 현재 세션 정보</h2>";
echo "<pre>";

if (!empty($_SESSION)) {
    echo "<span class='success'>✅ 세션 데이터:</span>\n";
    foreach ($_SESSION as $key => $value) {
        if (is_string($value) || is_numeric($value)) {
            echo "  $key: $value\n";
        } else {
            echo "  $key: " . gettype($value) . "\n";
        }
    }
} else {
    echo "<span class='error'>❌ 세션이 비어있음</span>\n";
}

echo "</pre>";

echo "<h2>2️⃣ AuthMiddleware 권한 확인</h2>";
echo "<pre>";

try {
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    
    $isLoggedIn = AuthMiddleware::isLoggedIn();
    echo "<span class='" . ($isLoggedIn ? 'success' : 'error') . "'>" . 
         ($isLoggedIn ? '✅' : '❌') . " 로그인 상태: " . ($isLoggedIn ? '로그인됨' : '로그인 안됨') . "</span>\n";
    
    if ($isLoggedIn) {
        $userId = AuthMiddleware::getCurrentUserId();
        $userRole = AuthMiddleware::getUserRole();
        $currentUser = AuthMiddleware::getCurrentUser();
        
        echo "<span class='success'>✅ 사용자 ID: $userId</span>\n";
        echo "<span class='success'>✅ 사용자 권한: $userRole</span>\n";
        echo "<span class='success'>✅ 사용자 닉네임: " . ($currentUser['nickname'] ?? 'N/A') . "</span>\n";
        
        // 권한별 메뉴 표시 조건 확인
        echo "\n권한별 메뉴 표시 조건:\n";
        echo "  ROLE_CORP 또는 ROLE_USER: " . (($userRole === 'ROLE_CORP' || $userRole === 'ROLE_USER') ? '✅ 표시됨' : '❌ 표시 안됨') . "\n";
        echo "  ROLE_ADMIN: " . (($userRole === 'ROLE_ADMIN') ? '✅ 표시됨' : '❌ 표시 안됨') . "\n";
    }
    
} catch (Exception $e) {
    echo "<span class='error'>❌ AuthMiddleware 오류: " . $e->getMessage() . "</span>\n";
}

echo "</pre>";

echo "<h2>3️⃣ 데이터베이스 사용자 정보 확인</h2>";
echo "<pre>";

try {
    require_once SRC_PATH . '/config/database.php';
    $db = Database::getInstance();
    
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        
        $result = $db->query("SELECT id, nickname, phone, role, status, created_at FROM users WHERE id = $userId");
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            echo "<span class='success'>✅ 데이터베이스 사용자 정보:</span>\n";
            foreach ($user as $key => $value) {
                echo "  $key: $value\n";
            }
        } else {
            echo "<span class='error'>❌ 사용자 정보를 찾을 수 없음</span>\n";
        }
    } else {
        echo "<span class='warning'>⚠️ 세션에 user_id 없음</span>\n";
    }
    
} catch (Exception $e) {
    echo "<span class='error'>❌ 데이터베이스 오류: " . $e->getMessage() . "</span>\n";
}

echo "</pre>";

echo "<h2>4️⃣ 드롭다운 메뉴 조건 시뮬레이션</h2>";
echo "<pre>";

try {
    $userRole = AuthMiddleware::getUserRole();
    
    echo "현재 사용자 권한: $userRole\n\n";
    
    echo "드롭다운 메뉴 표시 조건 확인:\n";
    
    // 기업 사용자 메뉴 조건
    if ($userRole === 'ROLE_CORP' || $userRole === 'ROLE_USER') {
        echo "<span class='success'>✅ 기업/일반 사용자 메뉴 표시 조건 만족</span>\n";
        echo "  → '📊 신청 관리' 메뉴가 표시되어야 함\n";
    } else {
        echo "<span class='error'>❌ 기업/일반 사용자 메뉴 표시 조건 불만족</span>\n";
        echo "  → 현재 권한: $userRole (ROLE_CORP 또는 ROLE_USER 필요)\n";
    }
    
    // 관리자 메뉴 조건
    if ($userRole === 'ROLE_ADMIN') {
        echo "<span class='success'>✅ 관리자 메뉴 표시 조건 만족</span>\n";
        echo "  → '⚙️ 관리자' 및 '📊 신청 관리' 메뉴가 표시되어야 함\n";
    } else {
        echo "<span class='warning'>⚠️ 관리자 메뉴 표시 조건 불만족</span>\n";
        echo "  → 현재 권한: $userRole (ROLE_ADMIN 필요)\n";
    }
    
} catch (Exception $e) {
    echo "<span class='error'>❌ 조건 확인 오류: " . $e->getMessage() . "</span>\n";
}

echo "</pre>";

echo "<h2>5️⃣ 헤더 템플릿 디버깅</h2>";
echo "<pre>";

echo "헤더 템플릿에서 사용하는 조건:\n";
echo "1. 메인 네비게이션: userRole === 'ROLE_CORP' || userRole === 'ROLE_ADMIN' || userRole === 'ROLE_USER'\n";
echo "2. 드롭다운 메뉴: userRole === 'ROLE_CORP' || userRole === 'ROLE_USER'\n";
echo "3. 관리자 메뉴: userRole === 'ROLE_ADMIN'\n\n";

$userRole = AuthMiddleware::getUserRole();
echo "현재 권한으로 확인:\n";
echo "1. 메인 네비게이션 표시: " . (($userRole === 'ROLE_CORP' || $userRole === 'ROLE_ADMIN' || $userRole === 'ROLE_USER') ? '✅ YES' : '❌ NO') . "\n";
echo "2. 드롭다운 메뉴 표시: " . (($userRole === 'ROLE_CORP' || $userRole === 'ROLE_USER') ? '✅ YES' : '❌ NO') . "\n";
echo "3. 관리자 메뉴 표시: " . (($userRole === 'ROLE_ADMIN') ? '✅ YES' : '❌ NO') . "\n";

echo "</pre>";

echo "<h2>6️⃣ 해결 방안</h2>";
echo "<div style='color:#fff;padding:15px;background:#222;border-radius:5px;'>";

$userRole = AuthMiddleware::getUserRole();
if ($userRole !== 'ROLE_CORP' && $userRole !== 'ROLE_USER' && $userRole !== 'ROLE_ADMIN') {
    echo "<h3>🔧 문제: 권한이 올바르지 않음</h3>";
    echo "<p>현재 권한: <strong>$userRole</strong></p>";
    echo "<p>필요한 권한: ROLE_CORP, ROLE_USER, 또는 ROLE_ADMIN</p>";
    echo "<h3>🚀 해결책:</h3>";
    echo "<p>1. 데이터베이스에서 사용자 권한을 ROLE_CORP 또는 ROLE_USER로 변경</p>";
    echo "<p>2. 또는 헤더 템플릿의 조건을 현재 권한에 맞게 수정</p>";
} else {
    echo "<h3>✅ 권한은 정상이지만 드롭다운에 안 보임</h3>";
    echo "<p>헤더 템플릿 캐시 문제이거나 조건문 오류일 수 있음</p>";
    echo "<h3>🚀 해결책:</h3>";
    echo "<p>1. 브라우저 캐시 클리어</p>";
    echo "<p>2. 헤더 템플릿 재확인</p>";
}

echo "</div>";

echo "</body></html>";
?>