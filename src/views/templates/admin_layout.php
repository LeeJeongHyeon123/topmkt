<?php
/**
 * 관리자 페이지 공통 레이아웃 템플릿
 */
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? '관리자 대시보드' ?> - 탑마케팅 관리자</title>
    <meta name="robots" content="noindex, nofollow">
    
    <!-- 기본 CSS -->
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- 관리자 공통 스타일 -->
    <?php include SRC_PATH . '/views/templates/admin_styles.php'; ?>
    
    <!-- 페이지별 추가 스타일 -->
    <?php if (isset($additional_styles)): ?>
        <?= $additional_styles ?>
    <?php endif; ?>
</head>
<body class="admin-page">
    <div class="admin-container">
        <!-- 사이드바 -->
        <?php include SRC_PATH . '/views/templates/admin_sidebar.php'; ?>
        
        <!-- 메인 콘텐츠 -->
        <main class="admin-main">
            <!-- 헤더 -->
            <header class="main-header">
                <div class="header-left">
                    <h1><?= $page_title ?? '관리자 대시보드' ?></h1>
                    <p><?= $page_description ?? '탑마케팅 플랫폼을 관리하세요' ?></p>
                </div>
                <div class="header-right">
                    <a href="/" class="main-site-btn">🏠 메인페이지</a>
                    <div class="admin-user-info">
                        <?php 
                        $currentUser = AuthMiddleware::getCurrentUser();
                        $userInitial = $currentUser ? strtoupper(mb_substr($currentUser['nickname'], 0, 1)) : 'A';
                        $userName = $currentUser['nickname'] ?? '관리자';
                        $userRole = $currentUser['role'] === 'ROLE_ADMIN' ? '시스템 관리자' : 
                                   ($currentUser['role'] === 'SUPER_ADMIN' ? '최고 관리자' : '관리자');
                        ?>
                        <div class="user-avatar"><?= $userInitial ?></div>
                        <div class="user-details">
                            <div class="user-name"><?= htmlspecialchars($userName) ?></div>
                            <div class="user-role"><?= htmlspecialchars($userRole) ?></div>
                        </div>
                    </div>
                    <a href="/auth/logout" class="logout-btn">🚪 로그아웃</a>
                </div>
            </header>
            
            <!-- 페이지 콘텐츠 -->
            <div class="page-content">
                <?= $content ?? '' ?>
            </div>
        </main>
    </div>
    
    <!-- 페이지별 추가 스크립트 -->
    <?php if (isset($additional_scripts)): ?>
        <?= $additional_scripts ?>
    <?php endif; ?>
</body>
</html>