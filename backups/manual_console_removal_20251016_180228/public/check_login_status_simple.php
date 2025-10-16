<?php
/**
 * 로그인 상태 유지 진단 페이지 (간단 버전)
 * 오류를 방지하기 위해 단계별로 확인
 */

// 오류 표시 설정 (프로덕션에서는 비활성화)
ini_set('display_errors', 0);
error_reporting(0);

// 기본 세션 시작
session_start();

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>로그인 상태 진단 - 탑마케팅</title>
    <style>
        body {
            font-family: 'Noto Sans KR', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 1000px;
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
        .code {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
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
        .time-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .time-card {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #e9ecef;
        }
        .time-card h3 {
            margin: 0 0 10px 0;
            color: #495057;
            font-size: 1rem;
        }
        .time-card .value {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }
        .time-card .unit {
            font-size: 0.9rem;
            color: #6c757d;
        }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 로그인 상태 진단 (간단 버전)</h1>
        
        <!-- 현재 로그인 상태 -->
        <h2>📊 현재 로그인 상태</h2>
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="status-box status-success">
                <h3>✅ 로그인 중</h3>
                <table>
                    <tr>
                        <th width="30%">항목</th>
                        <th>값</th>
                    </tr>
                    <tr>
                        <td>사용자 ID</td>
                        <td><?= $_SESSION['user_id'] ?></td>
                    </tr>
                    <tr>
                        <td>닉네임</td>
                        <td><?= htmlspecialchars($_SESSION['username'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <td>전화번호</td>
                        <td><?= htmlspecialchars($_SESSION['phone'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <td>권한</td>
                        <td><?= $_SESSION['user_role'] ?? 'N/A' ?></td>
                    </tr>
                    <tr>
                        <td>세션 ID</td>
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
        <h2>⚙️ PHP 세션 설정</h2>
        <div class="status-box status-info">
            <table>
                <tr>
                    <th width="40%">설정</th>
                    <th width="30%">현재 값</th>
                    <th>설명</th>
                </tr>
                <tr>
                    <td class="code">session.gc_maxlifetime</td>
                    <td>
                        <?= ini_get('session.gc_maxlifetime') ?> 초 
                        <br><small>(<?= round(ini_get('session.gc_maxlifetime') / 60) ?> 분)</small>
                    </td>
                    <td>세션 파일 수명</td>
                </tr>
                <tr>
                    <td class="code">session.cookie_lifetime</td>
                    <td>
                        <?= ini_get('session.cookie_lifetime') ?> 
                        <?= ini_get('session.cookie_lifetime') == 0 ? '<br><small>(브라우저 종료시)</small>' : '초' ?>
                    </td>
                    <td>세션 쿠키 수명</td>
                </tr>
                <tr>
                    <td class="code">session.cookie_httponly</td>
                    <td><?= ini_get('session.cookie_httponly') ? '✅ 활성' : '❌ 비활성' ?></td>
                    <td>JavaScript 접근 차단</td>
                </tr>
                <tr>
                    <td class="code">session.cookie_secure</td>
                    <td><?= ini_get('session.cookie_secure') ? '✅ 활성' : '❌ 비활성' ?></td>
                    <td>HTTPS 전용</td>
                </tr>
                <tr>
                    <td class="code">session.cookie_samesite</td>
                    <td><?= ini_get('session.cookie_samesite') ?: 'None' ?></td>
                    <td>CSRF 보호</td>
                </tr>
            </table>
        </div>

        <!-- 세션 타이밍 정보 -->
        <?php if (isset($_SESSION['user_id'])): ?>
        <h2>⏱️ 세션 타이밍</h2>
        <div class="time-info">
            <?php 
            $lastActivity = $_SESSION['last_activity'] ?? time();
            $sessionLifetime = ini_get('session.gc_maxlifetime');
            $timeElapsed = time() - $lastActivity;
            $timeRemaining = max(0, $sessionLifetime - $timeElapsed);
            ?>
            
            <div class="time-card">
                <h3>마지막 활동</h3>
                <div class="value"><?= round($timeElapsed / 60) ?></div>
                <div class="unit">분 전</div>
            </div>
            
            <div class="time-card">
                <h3>세션 만료까지</h3>
                <div class="value"><?= round($timeRemaining / 60) ?></div>
                <div class="unit">분 남음</div>
            </div>
            
            <div class="time-card">
                <h3>세션 수명 설정</h3>
                <div class="value"><?= round($sessionLifetime / 60) ?></div>
                <div class="unit">분</div>
            </div>
        </div>
        <?php endif; ?>

        <!-- 쿠키 정보 -->
        <h2>🍪 쿠키 정보</h2>
        <div class="status-box status-info">
            <?php if (!empty($_COOKIE)): ?>
                <table>
                    <tr>
                        <th width="30%">쿠키 이름</th>
                        <th width="50%">값 (일부)</th>
                        <th>설명</th>
                    </tr>
                    <?php foreach ($_COOKIE as $name => $value): ?>
                    <tr>
                        <td class="code"><?= htmlspecialchars($name) ?></td>
                        <td>
                            <?php 
                            $displayValue = htmlspecialchars(substr($value, 0, 40));
                            if (strlen($value) > 40) $displayValue .= '...';
                            echo $displayValue;
                            ?>
                        </td>
                        <td>
                            <?php 
                            if ($name === 'PHPSESSID') echo '세션 ID';
                            elseif ($name === 'remember_token') echo '<strong>로그인 유지 토큰</strong>';
                            else echo '-';
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p>쿠키가 설정되지 않았습니다.</p>
            <?php endif; ?>
        </div>

        <!-- Remember Token 상태 -->
        <h2>🔐 로그인 유지 상태</h2>
        <?php if (isset($_COOKIE['remember_token'])): ?>
            <div class="status-box status-success">
                <h3>✅ Remember Token 있음</h3>
                <p>로그인 유지 쿠키가 설정되어 있습니다.</p>
                <p><strong>토큰 (일부):</strong> <span class="code"><?= substr($_COOKIE['remember_token'], 0, 20) ?>...</span></p>
            </div>
        <?php else: ?>
            <div class="status-box status-warning">
                <h3>⚠️ Remember Token 없음</h3>
                <p>로그인 유지 쿠키가 설정되지 않았습니다.</p>
                <p>로그인 시 "로그인 상태 유지"를 체크하지 않았거나, 쿠키가 삭제되었을 수 있습니다.</p>
            </div>
        <?php endif; ?>

        <!-- 세션 데이터 -->
        <h2>📂 전체 세션 데이터</h2>
        <div class="status-box status-info">
            <pre><?php 
                $sessionData = $_SESSION;
                // 민감한 정보 마스킹
                if (isset($sessionData['csrf_token'])) {
                    $sessionData['csrf_token'] = substr($sessionData['csrf_token'], 0, 10) . '... (보안상 숨김)';
                }
                if (isset($sessionData['remember_token'])) {
                    $sessionData['remember_token'] = substr($sessionData['remember_token'], 0, 10) . '... (보안상 숨김)';
                }
                echo htmlspecialchars(print_r($sessionData, true));
            ?></pre>
        </div>

        <!-- 진단 결과 -->
        <h2>🏥 진단 결과</h2>
        <?php
        $issues = [];
        
        // 세션 설정 확인
        $gcMaxLifetime = ini_get('session.gc_maxlifetime');
        if ($gcMaxLifetime < 1800) {
            $issues[] = [
                'type' => 'warning', 
                'message' => "세션 수명이 {$gcMaxLifetime}초(".round($gcMaxLifetime/60)."분)로 설정되어 있습니다. 30분(1800초) 이상 권장합니다."
            ];
        }
        
        // Remember Token 확인
        if (isset($_SESSION['user_id']) && !isset($_COOKIE['remember_token'])) {
            $issues[] = [
                'type' => 'info', 
                'message' => '로그인 유지 기능이 활성화되지 않았습니다. 브라우저를 닫거나 세션이 만료되면 로그아웃됩니다.'
            ];
        }
        
        // HTTPS 확인
        $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        if ($isHttps && !ini_get('session.cookie_secure')) {
            $issues[] = [
                'type' => 'warning', 
                'message' => 'HTTPS 환경에서 보안 쿠키가 비활성화되어 있습니다.'
            ];
        }
        
        if (empty($issues)): ?>
            <div class="status-box status-success">
                <h3>✅ 모든 설정이 정상입니다</h3>
                <p>로그인 상태 유지 기능이 올바르게 작동할 수 있는 환경입니다.</p>
            </div>
        <?php else: ?>
            <?php foreach ($issues as $issue): ?>
                <div class="status-box status-<?= $issue['type'] ?>">
                    <p><?= $issue['message'] ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- 권장사항 -->
        <h2>💡 로그인 상태 유지 가이드</h2>
        <div class="status-box status-info">
            <h4>30일간 로그인 상태를 유지하려면:</h4>
            <ol>
                <li><strong>로그인 시 "로그인 상태 유지" 체크박스를 반드시 선택</strong>하세요</li>
                <li>브라우저의 쿠키를 삭제하지 마세요</li>
                <li>시크릿/프라이빗 모드를 사용하지 마세요</li>
                <li>브라우저 설정에서 쿠키 차단을 해제하세요</li>
            </ol>
            
            <h4>현재 설정 요약:</h4>
            <ul>
                <li><strong>로그인 유지 미체크:</strong> <?= round($gcMaxLifetime / 60) ?>분 후 자동 로그아웃</li>
                <li><strong>로그인 유지 체크:</strong> 30일간 로그인 유지 (remember_token 쿠키 사용)</li>
                <li><strong>브라우저 종료:</strong> 로그인 유지를 체크한 경우에만 유지됨</li>
            </ul>
            
            <?php if (!isset($_COOKIE['remember_token']) && isset($_SESSION['user_id'])): ?>
            <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 5px;">
                <p><strong>⚠️ 주의:</strong> 현재 로그인은 되어 있지만 "로그인 상태 유지"가 설정되지 않았습니다.</p>
                <p>브라우저를 닫거나 <?= round($timeRemaining / 60) ?>분 후에는 자동으로 로그아웃됩니다.</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- 액션 버튼 -->
        <h2>🔧 테스트 도구</h2>
        <div style="margin: 20px 0;">
            <a href="/auth/login" class="btn">로그인 페이지</a>
            <a href="/auth/logout" class="btn" style="background: #dc3545;">로그아웃</a>
            <a href="/check_login_status_simple.php" class="btn" style="background: #28a745;">페이지 새로고침</a>
            <a href="/" class="btn" style="background: #6c757d;">메인으로</a>
        </div>
    </div>
</body>
</html>