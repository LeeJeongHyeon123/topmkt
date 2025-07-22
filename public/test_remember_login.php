<?php
/**
 * Remember Me 기능 테스트 페이지
 */

session_start();

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>로그인 상태 유지 테스트</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
        .container { max-width: 800px; margin: 0 auto; }
        .status { padding: 20px; margin: 20px 0; border-radius: 8px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .warning { background: #fff3cd; border: 1px solid #ffeeba; color: #856404; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        .btn { display: inline-block; padding: 10px 20px; margin: 10px 5px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
        .btn:hover { background: #0056b3; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #f8f9fa; }
        .code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 로그인 상태 유지 테스트</h1>
        
        <!-- 현재 상태 -->
        <div class="status info">
            <h3>📊 현재 상태</h3>
            <table>
                <tr>
                    <th>항목</th>
                    <th>값</th>
                    <th>설명</th>
                </tr>
                <tr>
                    <td>로그인 상태</td>
                    <td><?= isset($_SESSION['user_id']) ? '✅ 로그인됨' : '❌ 로그아웃됨' ?></td>
                    <td>현재 세션 기반 로그인 상태</td>
                </tr>
                <?php if (isset($_SESSION['user_id'])): ?>
                <tr>
                    <td>사용자</td>
                    <td><?= htmlspecialchars($_SESSION['username'] ?? 'N/A') ?></td>
                    <td>로그인된 사용자 닉네임</td>
                </tr>
                <tr>
                    <td>사용자 ID</td>
                    <td><?= $_SESSION['user_id'] ?></td>
                    <td>데이터베이스 사용자 ID</td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td>Remember Token</td>
                    <td><?= isset($_COOKIE['remember_token']) ? '✅ 존재' : '❌ 없음' ?></td>
                    <td>브라우저 종료 후 자동 로그인용</td>
                </tr>
                <?php if (isset($_COOKIE['remember_token'])): ?>
                <tr>
                    <td>토큰 값</td>
                    <td class="code"><?= substr($_COOKIE['remember_token'], 0, 20) ?>...</td>
                    <td>Remember Token (일부)</td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td>세션 ID</td>
                    <td class="code"><?= session_id() ?></td>
                    <td>현재 PHP 세션 ID</td>
                </tr>
            </table>
        </div>

        <!-- 테스트 시나리오 -->
        <div class="status warning">
            <h3>🧪 테스트 시나리오</h3>
            <ol>
                <li><strong>로그인 유지 체크박스 테스트</strong>
                    <ul>
                        <li>로그아웃 → 로그인 시 "로그인 상태 유지" 체크</li>
                        <li>로그인 후 이 페이지에서 Remember Token 확인</li>
                    </ul>
                </li>
                <li><strong>브라우저 종료 테스트</strong>
                    <ul>
                        <li>브라우저를 <strong>완전히 종료</strong> (모든 탭 닫기)</li>
                        <li>브라우저 재시작 후 사이트 접속</li>
                        <li>자동으로 로그인되는지 확인</li>
                    </ul>
                </li>
                <li><strong>장기간 테스트</strong>
                    <ul>
                        <li>며칠 후에도 자동 로그인되는지 확인</li>
                        <li>최대 30일까지 유지됨</li>
                    </ul>
                </li>
            </ol>
        </div>

        <!-- 결과 해석 -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if (isset($_COOKIE['remember_token'])): ?>
                <div class="status success">
                    <h3>✅ 완벽한 설정</h3>
                    <p><strong>로그인 상태 유지가 올바르게 설정되었습니다!</strong></p>
                    <ul>
                        <li>현재 로그인되어 있음</li>
                        <li>Remember Token이 존재함</li>
                        <li>브라우저를 닫았다가 열어도 자동 로그인될 것입니다</li>
                    </ul>
                </div>
            <?php else: ?>
                <div class="status warning">
                    <h3>⚠️ 부분적 설정</h3>
                    <p><strong>현재 로그인되어 있지만 Remember Token이 없습니다.</strong></p>
                    <ul>
                        <li>브라우저를 닫으면 로그아웃될 수 있습니다</li>
                        <li>로그인 시 "로그인 상태 유지"를 체크하지 않았을 가능성</li>
                        <li>다시 로그인할 때 체크박스를 선택해보세요</li>
                    </ul>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <?php if (isset($_COOKIE['remember_token'])): ?>
                <div class="status warning">
                    <h3>🔄 자동 로그인 시도 중</h3>
                    <p><strong>Remember Token은 있지만 세션 로그인이 안된 상태입니다.</strong></p>
                    <ul>
                        <li>페이지를 새로고침하면 자동 로그인될 수 있습니다</li>
                        <li>토큰이 만료되었거나 데이터베이스 문제일 수 있습니다</li>
                    </ul>
                    <p><a href="/test_remember_login.php" class="btn">페이지 새로고침</a></p>
                </div>
            <?php else: ?>
                <div class="status warning">
                    <h3>❌ 로그아웃 상태</h3>
                    <p><strong>현재 로그아웃되어 있습니다.</strong></p>
                    <ul>
                        <li>로그인이 필요합니다</li>
                        <li>로그인 시 "로그인 상태 유지"를 반드시 체크하세요</li>
                    </ul>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- 액션 버튼 -->
        <div style="margin: 30px 0;">
            <h3>🔧 테스트 도구</h3>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/auth/logout" class="btn btn-danger">로그아웃</a>
            <?php else: ?>
                <a href="/auth/login" class="btn">로그인 (상태 유지 체크!)</a>
            <?php endif; ?>
            <a href="/test_remember_login.php" class="btn">페이지 새로고침</a>
            <a href="/check_login_status_simple.php" class="btn">세부 진단</a>
            <a href="/" class="btn" style="background: #6c757d;">메인으로</a>
        </div>

        <!-- 문제 해결 가이드 -->
        <div class="status info">
            <h3>🔧 문제 해결 가이드</h3>
            <h4>브라우저를 닫으면 로그아웃되는 경우:</h4>
            <ol>
                <li><strong>데이터베이스 컬럼 확인</strong>: <a href="/add_remember_columns.php?token=add_remember_columns_2025" target="_blank">여기서 확인</a></li>
                <li><strong>로그인 시 체크박스 선택</strong>: "로그인 상태 유지" 반드시 체크</li>
                <li><strong>브라우저 설정</strong>: 쿠키 차단 해제, 시크릿 모드 사용 안함</li>
                <li><strong>서버 설정</strong>: PHP 세션 및 쿠키 설정 확인</li>
            </ol>
            
            <h4>예상 동작:</h4>
            <ul>
                <li><strong>로그인 유지 체크 O</strong>: 30일간 자동 로그인</li>
                <li><strong>로그인 유지 체크 X</strong>: 브라우저 종료 시 로그아웃</li>
            </ul>
        </div>
    </div>
</body>
</html>