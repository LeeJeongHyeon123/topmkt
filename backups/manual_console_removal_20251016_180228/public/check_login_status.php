<?php
/**
 * 로그인 상태 유지 진단 페이지
 * 세션 및 쿠키 상태를 실시간으로 확인
 */

// 설정 파일 로드
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';
require_once SRC_PATH . '/config/session.php';
require_once SRC_PATH . '/models/User.php';

// 세션 시작
initializeSession();

// 데이터베이스 연결
$db = Database::getInstance();
$userModel = new User();

// 현재 사용자 정보
$currentUser = null;
if (isset($_SESSION['user_id'])) {
    $currentUser = $userModel->findById($_SESSION['user_id']);
}

// Remember Token 정보
$rememberTokenInfo = null;
if (isset($_COOKIE['remember_token'])) {
    $tokenUser = $userModel->findByRememberToken($_COOKIE['remember_token']);
    if ($tokenUser) {
        $rememberTokenInfo = [
            'token' => substr($_COOKIE['remember_token'], 0, 20) . '...',
            'user' => $tokenUser,
            'cookie_expires' => isset($_COOKIE['remember_token']) ? 'Session Cookie' : 'N/A'
        ];
    }
}

// 페이지 HTML
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>로그인 상태 진단 - 탑마케팅</title>
    <style>
        body {
            font-family: 'Noto Sans KR', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #667eea;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        h2 {
            color: #555;
            margin-top: 30px;
            padding: 10px;
            background: #f8f9fa;
            border-left: 4px solid #667eea;
        }
        .status-box {
            padding: 20px;
            margin: 15px 0;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        .status-success {
            background: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .status-warning {
            background: #fff3cd;
            border-color: #ffeeba;
            color: #856404;
        }
        .status-error {
            background: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        .status-info {
            background: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .code {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
            font-size: 0.9em;
            color: #e83e8c;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px 5px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #5a67d8;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .time-info {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        .time-card {
            flex: 1;
            min-width: 200px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            text-align: center;
        }
        .time-card h3 {
            margin: 0 0 10px 0;
            color: #495057;
            font-size: 1.1em;
        }
        .time-card .value {
            font-size: 1.5em;
            font-weight: bold;
            color: #667eea;
        }
        .time-card .unit {
            font-size: 0.9em;
            color: #6c757d;
        }
        .debug-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            font-family: monospace;
            font-size: 0.9em;
            overflow-x: auto;
        }
        .warning {
            color: #ff6b6b;
            font-weight: bold;
        }
        .success {
            color: #51cf66;
            font-weight: bold;
        }
        .info {
            color: #339af0;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 로그인 상태 진단</h1>
        
        <!-- 현재 로그인 상태 -->
        <h2>📊 현재 로그인 상태</h2>
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="status-box status-success">
                <h3>✅ 로그인 중</h3>
                <table>
                    <tr>
                        <th>사용자 ID</th>
                        <td><?= $_SESSION['user_id'] ?></td>
                    </tr>
                    <tr>
                        <th>닉네임</th>
                        <td><?= htmlspecialchars($_SESSION['username'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th>전화번호</th>
                        <td><?= htmlspecialchars($_SESSION['phone'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th>권한</th>
                        <td><?= $_SESSION['user_role'] ?? 'N/A' ?></td>
                    </tr>
                    <tr>
                        <th>세션 ID</th>
                        <td class="code"><?= substr(session_id(), 0, 20) ?>...</td>
                    </tr>
                </table>
            </div>
        <?php else: ?>
            <div class="status-box status-error">
                <h3>❌ 로그인되지 않음</h3>
                <p>현재 로그인된 상태가 아닙니다.</p>
                <a href="/auth/login" class="btn">로그인 페이지로 이동</a>
            </div>
        <?php endif; ?>

        <!-- 세션 설정 정보 -->
        <h2>⚙️ 세션 설정</h2>
        <div class="status-box status-info">
            <table>
                <tr>
                    <th>설정</th>
                    <th>현재 값</th>
                    <th>설명</th>
                </tr>
                <tr>
                    <td class="code">session.gc_maxlifetime</td>
                    <td><?= ini_get('session.gc_maxlifetime') ?> 초 (<?= round(ini_get('session.gc_maxlifetime') / 60) ?> 분)</td>
                    <td>세션 가비지 컬렉션 수명</td>
                </tr>
                <tr>
                    <td class="code">session.cookie_lifetime</td>
                    <td><?= ini_get('session.cookie_lifetime') ?> <?= ini_get('session.cookie_lifetime') == 0 ? '(브라우저 종료시)' : '초' ?></td>
                    <td>세션 쿠키 수명</td>
                </tr>
                <tr>
                    <td class="code">session.cookie_httponly</td>
                    <td><?= ini_get('session.cookie_httponly') ? '✅ 활성' : '❌ 비활성' ?></td>
                    <td>HTTP Only 쿠키</td>
                </tr>
                <tr>
                    <td class="code">session.cookie_secure</td>
                    <td><?= ini_get('session.cookie_secure') ? '✅ 활성' : '❌ 비활성' ?></td>
                    <td>HTTPS 전용 쿠키</td>
                </tr>
                <tr>
                    <td class="code">session.cookie_samesite</td>
                    <td><?= ini_get('session.cookie_samesite') ?: 'None' ?></td>
                    <td>SameSite 정책</td>
                </tr>
            </table>
        </div>

        <!-- 세션 타이밍 정보 -->
        <?php if (isset($_SESSION['user_id'])): ?>
        <h2>⏱️ 세션 타이밍</h2>
        <div class="time-info">
            <?php if (isset($_SESSION['last_activity'])): ?>
            <div class="time-card">
                <h3>마지막 활동</h3>
                <div class="value"><?= round((time() - $_SESSION['last_activity']) / 60) ?></div>
                <div class="unit">분 전</div>
            </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['last_regeneration'])): ?>
            <div class="time-card">
                <h3>세션 ID 재생성</h3>
                <div class="value"><?= round((time() - $_SESSION['last_regeneration']) / 60) ?></div>
                <div class="unit">분 전</div>
            </div>
            <?php endif; ?>
            
            <div class="time-card">
                <h3>세션 만료까지</h3>
                <div class="value"><?= round((ini_get('session.gc_maxlifetime') - (time() - ($_SESSION['last_activity'] ?? time()))) / 60) ?></div>
                <div class="unit">분 남음</div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Remember Me 토큰 정보 -->
        <h2>🔐 Remember Me 토큰</h2>
        <?php if (isset($_COOKIE['remember_token'])): ?>
            <div class="status-box status-success">
                <h3>✅ Remember Token 존재</h3>
                <table>
                    <tr>
                        <th>토큰 (일부)</th>
                        <td class="code"><?= substr($_COOKIE['remember_token'], 0, 20) ?>...</td>
                    </tr>
                    <tr>
                        <th>쿠키 정보</th>
                        <td>브라우저에 저장된 Remember Me 쿠키</td>
                    </tr>
                </table>
                
                <?php if ($rememberTokenInfo && $rememberTokenInfo['user']): ?>
                    <h4>📌 토큰으로 조회된 사용자 정보</h4>
                    <table>
                        <tr>
                            <th>사용자 ID</th>
                            <td><?= $rememberTokenInfo['user']['id'] ?></td>
                        </tr>
                        <tr>
                            <th>닉네임</th>
                            <td><?= htmlspecialchars($rememberTokenInfo['user']['nickname']) ?></td>
                        </tr>
                        <tr>
                            <th>토큰 만료</th>
                            <td>
                                <?php 
                                $expires = strtotime($rememberTokenInfo['user']['remember_expires'] ?? '');
                                if ($expires): ?>
                                    <?= date('Y-m-d H:i:s', $expires) ?> 
                                    (<?= round(($expires - time()) / 86400) ?> 일 남음)
                                <?php else: ?>
                                    정보 없음
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                <?php else: ?>
                    <div class="status-box status-warning">
                        <p>⚠️ 토큰이 존재하지만 데이터베이스에서 유효한 사용자를 찾을 수 없습니다.</p>
                        <p>가능한 원인:</p>
                        <ul>
                            <li>토큰이 만료되었습니다</li>
                            <li>데이터베이스에 remember_token 컬럼이 없습니다</li>
                            <li>토큰이 변조되었습니다</li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="status-box status-warning">
                <h3>⚠️ Remember Token 없음</h3>
                <p>Remember Me 쿠키가 설정되지 않았습니다.</p>
                <p>로그인 시 "로그인 상태 유지"를 체크했는지 확인하세요.</p>
            </div>
        <?php endif; ?>

        <!-- 모든 쿠키 정보 -->
        <h2>🍪 모든 쿠키</h2>
        <div class="status-box status-info">
            <table>
                <tr>
                    <th>쿠키 이름</th>
                    <th>값 (일부)</th>
                    <th>설명</th>
                </tr>
                <?php foreach ($_COOKIE as $name => $value): ?>
                <tr>
                    <td class="code"><?= htmlspecialchars($name) ?></td>
                    <td><?= htmlspecialchars(substr($value, 0, 30)) ?><?= strlen($value) > 30 ? '...' : '' ?></td>
                    <td>
                        <?php 
                        if ($name === 'PHPSESSID') echo '세션 ID';
                        elseif ($name === 'remember_token') echo 'Remember Me 토큰';
                        else echo '-';
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- 세션 데이터 -->
        <h2>📂 세션 데이터</h2>
        <div class="debug-info">
            <pre><?php 
                $sessionData = $_SESSION;
                // 민감한 정보 마스킹
                if (isset($sessionData['csrf_token'])) {
                    $sessionData['csrf_token'] = substr($sessionData['csrf_token'], 0, 10) . '...';
                }
                if (isset($sessionData['remember_token'])) {
                    $sessionData['remember_token'] = substr($sessionData['remember_token'], 0, 10) . '...';
                }
                echo htmlspecialchars(json_encode($sessionData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            ?></pre>
        </div>

        <!-- 진단 결과 -->
        <h2>🏥 진단 결과</h2>
        <?php
        $issues = [];
        
        // 세션 설정 확인
        if (ini_get('session.gc_maxlifetime') < 1800) {
            $issues[] = ['type' => 'warning', 'message' => '세션 수명이 30분 미만으로 설정되어 있습니다.'];
        }
        
        // Remember Token 확인
        if (isset($_SESSION['user_id']) && !isset($_COOKIE['remember_token'])) {
            $issues[] = ['type' => 'info', 'message' => 'Remember Me 기능이 활성화되지 않았습니다. 브라우저를 닫으면 로그아웃됩니다.'];
        }
        
        // 토큰 유효성 확인
        if (isset($_COOKIE['remember_token']) && !$rememberTokenInfo) {
            $issues[] = ['type' => 'error', 'message' => 'Remember Token이 유효하지 않습니다. 데이터베이스 스키마를 확인하세요.'];
        }
        
        // HTTPS 확인
        if (!ini_get('session.cookie_secure') && isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $issues[] = ['type' => 'warning', 'message' => 'HTTPS 환경에서 보안 쿠키가 비활성화되어 있습니다.'];
        }
        
        if (empty($issues)): ?>
            <div class="status-box status-success">
                <h3>✅ 모든 설정이 정상입니다</h3>
                <p>로그인 상태 유지 기능이 올바르게 작동하고 있습니다.</p>
            </div>
        <?php else: ?>
            <?php foreach ($issues as $issue): ?>
                <div class="status-box status-<?= $issue['type'] ?>">
                    <p><?= $issue['message'] ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- 테스트 액션 -->
        <h2>🧪 테스트 도구</h2>
        <div style="margin: 20px 0;">
            <a href="/auth/login" class="btn">로그인 테스트</a>
            <a href="/auth/logout" class="btn btn-danger">로그아웃</a>
            <a href="/check_login_status.php" class="btn">페이지 새로고침</a>
            <a href="/" class="btn">메인으로</a>
        </div>

        <!-- 권장사항 -->
        <h2>💡 권장사항</h2>
        <div class="status-box status-info">
            <h4>로그인 상태를 30일간 유지하려면:</h4>
            <ol>
                <li>로그인 시 "로그인 상태 유지" 체크박스를 선택하세요</li>
                <li>브라우저의 쿠키를 삭제하지 마세요</li>
                <li>시크릿/프라이빗 모드를 사용하지 마세요</li>
                <li>브라우저 설정에서 쿠키를 차단하지 마세요</li>
            </ol>
            
            <h4>현재 설정:</h4>
            <ul>
                <li>로그인 유지 미체크: 30분 후 자동 로그아웃</li>
                <li>로그인 유지 체크: 30일간 로그인 유지</li>
                <li>브라우저 종료: 로그인 유지 체크 시에만 유지됨</li>
            </ul>
        </div>
    </div>
</body>
</html>