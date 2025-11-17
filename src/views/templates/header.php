<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' - 탑마케팅' : (isset($title) ? $title : '탑마케팅') ?></title>
    <meta name="description" content="<?= $page_description ?? '글로벌 네트워크 마케팅 리더들의 커뮤니티' ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="<?= $og_type ?? 'website' ?>">
    <meta property="og:url" content="<?= 'https://' . ($_SERVER['HTTP_HOST'] ?? 'www.topmktx.com') . ($_SERVER['REQUEST_URI'] ?? '/') ?>">
    <meta property="og:title" content="<?= $og_title ?? ($page_title ? $page_title . ' - 탑마케팅' : '탑마케팅 - 마케팅 전문가들의 지식 공유 플랫폼') ?>">
    <meta property="og:description" content="<?= $og_description ?? ($page_description ?? '마케팅 전문가들이 모여 지식을 공유하고 함께 성장하는 플랫폼입니다. 세미나, 워크샵, 커뮤니티를 통해 최신 마케팅 트렌드를 만나보세요.') ?>">
    <meta property="og:image" content="<?= $og_image ?? 'https://' . ($_SERVER['HTTP_HOST'] ?? 'www.topmktx.com') . '/assets/images/topmkt-og-image.png?v=' . date('Ymd') ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="탑마케팅">
    <meta property="og:locale" content="ko_KR">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?= 'https://' . ($_SERVER['HTTP_HOST'] ?? 'www.topmktx.com') . ($_SERVER['REQUEST_URI'] ?? '/') ?>">
    <meta property="twitter:title" content="<?= $og_title ?? ($page_title ? $page_title . ' - 탑마케팅' : '탑마케팅 - 마케팅 전문가들의 지식 공유 플랫폼') ?>">
    <meta property="twitter:description" content="<?= $og_description ?? ($page_description ?? '마케팅 전문가들이 모여 지식을 공유하고 함께 성장하는 플랫폼입니다. 세미나, 워크샵, 커뮤니티를 통해 최신 마케팅 트렌드를 만나보세요.') ?>">
    <meta property="twitter:image" content="<?= $og_image ?? 'https://' . ($_SERVER['HTTP_HOST'] ?? 'www.topmktx.com') . '/assets/images/topmkt-og-image.png?v=' . date('Ymd') ?>">
    
    <!-- 추가 메타 태그 -->
    <meta name="keywords" content="<?= $keywords ?? '마케팅, 네트워크 마케팅, 세미나, 워크샵, 커뮤니티, 마케팅 교육, 온라인 강의, 탑마케팅, TopMKT, 비즈니스 매칭, 마케팅 플랫폼' ?>">
    <meta name="author" content="(주)윈카드">
    <meta name="robots" content="index, follow">
    <meta name="googlebot" content="index, follow">
    <meta name="theme-color" content="#6366f1">
    <meta name="msapplication-navbutton-color" content="#6366f1">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <?php 
    // CSRF 토큰 생성
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    ?>
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?>">
    <?php 
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    try {
        $currentUserId = AuthMiddleware::getCurrentUserId();
        $currentUserRole = AuthMiddleware::getUserRole();
        if ($currentUserId): ?>
    <meta name="user-id" content="<?= $currentUserId ?>">
    <meta name="user-role" content="<?= $currentUserRole ?>">
    <?php endif;
    } catch (Exception $e) {
        // 로그인하지 않은 사용자의 경우 무시
        $currentUserId = null;
    } ?>
    <link rel="canonical" href="<?= 'https://' . ($_SERVER['HTTP_HOST'] ?? 'www.topmktx.com') . ($_SERVER['REQUEST_URI'] ?? '/') ?>">
    
    <!-- 파비콘 - 모든 페이지 통일 -->
    <link rel="icon" type="image/x-icon" href="//www.topmktx.com/favicon.ico?v=20250609">
    <link rel="icon" type="image/svg+xml" href="//www.topmktx.com/assets/images/favicon.svg?v=20250609">
    <link rel="apple-touch-icon" sizes="180x180" href="//www.topmktx.com/assets/images/apple-touch-icon.png?v=20250609">
    <link rel="shortcut icon" href="//www.topmktx.com/favicon.ico?v=20250609">
    
    <!-- CSS -->
    <!-- 컴포넌트 CSS 파일들 (PHP에서 직접 출력) -->
    <link rel="stylesheet" href="//www.topmktx.com/assets/css/php/base.css.php?v=<?= uniqid() ?>">
    <link rel="stylesheet" href="//www.topmktx.com/assets/css/php/layout.css.php?v=<?= uniqid() ?>">
    <link rel="stylesheet" href="//www.topmktx.com/assets/css/php/buttons.css.php?v=<?= uniqid() ?>">
    <!-- 메인 CSS (강의 상세 스타일 포함) -->
    <link rel="stylesheet" href="//www.topmktx.com/assets/css/main.css?v=<?= uniqid() ?>">
    <link rel="stylesheet" href="//www.topmktx.com/assets/css/loading.css">
    <link rel="stylesheet" href="//www.topmktx.com/assets/css/badges.css"><!-- 🚀 v3.28.0: 통합 배지 시스템 -->
    <link rel="stylesheet" href="//www.topmktx.com/assets/css/search-filter.css"><!-- 🚀 v3.37.0: 검색/필터 컴포넌트 시스템 -->
    <!-- Font Awesome 6.4.0 with fallback for connection issues -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/webfonts/fa-solid-900.woff2" as="font" type="font/woff2" crossorigin="anonymous">
    <style>
    /* Font Awesome fallback - 연결 오류시 대체 스타일 */
    .fa-solid::before, .fas::before { 
        font-family: "Font Awesome 6 Free", "Font Awesome 5 Free", sans-serif !important; 
        font-weight: 900 !important;
    }
    .fa-regular::before, .far::before { 
        font-family: "Font Awesome 6 Free", "Font Awesome 5 Free", sans-serif !important; 
        font-weight: 400 !important;
    }
    /* CDN 연결 실패시 대체 텍스트 */
    .fa-user::before { content: "👤"; }
    .fa-home::before { content: "🏠"; }
    .fa-search::before { content: "🔍"; }
    .fa-bell::before { content: "🔔"; }
    .fa-envelope::before { content: "✉️"; }
    .fa-cog::before { content: "⚙️"; }
    .fa-plus::before { content: "+"; }
    .fa-edit::before { content: "✏️"; }
    .fa-trash::before { content: "🗑️"; }
    .fa-check::before { content: "✓"; }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- 레이지 로딩 스타일 -->
    <?php
    if (file_exists(SRC_PATH . '/helpers/LazyLoadHelper.php')) {
        require_once SRC_PATH . '/helpers/LazyLoadHelper.php';
        echo LazyLoadHelper::getStyles();
    }
    ?>
    
    <!-- JavaScript -->
    <!-- 🔥 v3.91.1: Chrome Extension 에러 억제 (전역) -->
    <script src="//www.topmktx.com/assets/js/error-suppressor.js"></script>
    <script src="//www.topmktx.com/assets/js/loading.js?v=<?= time() ?>"></script>
    <script src="//www.topmktx.com/assets/js/jwt-auth.js" defer></script>
    <script src="//www.topmktx.com/assets/js/main.js" defer></script>
    
    <!-- 디바이스 감지 및 반응형 시스템 (모든 페이지 공통) -->
    <?php include SRC_PATH . '/views/includes/device-detection.js.php'; ?>
    
    <!-- Firebase SDK (채팅 알림용) -->
    <?php if (isset($currentUserId) && $currentUserId && $_SERVER['REQUEST_URI'] !== '/chat'): ?>
    <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-database-compat.js"></script>
    <script src="//www.topmktx.com/assets/js/chat-notifications.js"></script>
    <?php endif; ?>
    
    <!-- Firebase 실시간 신청 대기 알림 시스템 (기업 유저용) -->
    <?php if (isset($currentUserId) && $currentUserId): ?>
    <script src="//www.topmktx.com/assets/js/registration-notifications-realtime.js"></script>
    <?php endif; ?>
    
    <!-- 구조화 데이터 (JSON-LD) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "탑마케팅",
        "alternateName": "TopMKT",
        "url": "https://www.topmktx.com",
        "description": "글로벌 네트워크 마케팅 리더들의 커뮤니티 플랫폼",
        "publisher": {
            "@type": "Organization",
            "name": "(주)윈카드",
            "logo": {
                "@type": "ImageObject",
                "url": "https://www.topmktx.com/assets/images/logo.png"
            }
        },
        "potentialAction": {
            "@type": "SearchAction",
            "target": "https://www.topmktx.com/community?search={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>
    
    <?php if (isset($structured_data)): ?>
    <script type="application/ld+json">
    <?= json_encode($structured_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
    </script>
    <?php endif; ?>
</head>
<body class="<?= isset($current_page) && $current_page === 'home' ? 'home-page' : '' ?>">
    <header class="main-header modern-header" style="overflow: visible;">
        <!-- ============================================================
             헤더 로고 위치 통합 시스템 v4.0 (SINGLE SOURCE OF TRUTH)
             - 모든 breakpoint에서 로고 좌측 고정
             - 페이지 로딩 시 중앙 이동 방지
             - 반응형 여백 최적화
             ============================================================ -->
        
        <!-- 즉시 실행 스크립트: DOM 파싱 전 스타일 적용 -->
        <script>
        (function() {
            'use strict';
            // 🚨 최상위 우선순위로 즉시 스타일 적용
            var style = document.createElement('style');
            style.id = 'logo-position-nuclear';
            style.textContent = `
                .header-content,
                header .header-content,
                .main-header .header-content,
                html body .header-content,
                html body header .header-content,
                html body .main-header .header-content,
                * .header-content,
                * header .header-content,
                * .main-header .header-content {
                    justify-content: flex-start !important;
                    gap: 20px !important;
                    display: flex !important;
                    align-items: center !important;
                }
                .header-left,
                header .header-left,
                .header-content .header-left,
                html body .header-left,
                html body header .header-left,
                html body .header-content .header-left,
                * .header-left,
                * header .header-left,
                * .header-content .header-left {
                    margin-left: 0 !important;
                    flex: 0 0 auto !important;
                    order: -1 !important;
                }
            `;
            document.head.insertBefore(style, document.head.firstChild);
        })();
        </script>
        
        <style>
        /* ============================================================
           헤더 로고 위치 최종 해결 시스템 v4.1
           - 모든 가능한 충돌에 대비한 최상위 우선순위
           ============================================================ */

        /* 🚨 최상위 우선순위: 모든 가능한 셀렉터로 헤더 보호 */
        .header-content,
        header .header-content,
        .main-header .header-content,
        html body .header-content,
        html body header .header-content,
        html body .main-header .header-content,
        * .header-content,
        * header .header-content,
        * .main-header .header-content {
            display: flex !important;
            align-items: center !important;
            justify-content: flex-start !important;
            gap: 20px !important;
            width: 100% !important;
            box-sizing: border-box !important;
            overflow: visible !important;
            flex-direction: row !important;
            flex-wrap: nowrap !important;
        }

        .header-left,
        header .header-left,
        .header-content .header-left,
        html body .header-left,
        html body header .header-left,
        html body .header-content .header-left,
        * .header-left,
        * header .header-left,
        * .header-content .header-left {
            flex: 0 0 auto !important;
            order: -1 !important;
            position: relative !important;
            transform: none !important;
            left: auto !important;
            right: auto !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            width: auto !important;
            box-sizing: border-box !important;
        }

        /* ============================================================
           반응형 Breakpoints (모든 미디어 쿼리에서 최상위 우선순위 적용)
           ============================================================ */

        /* 🚨 모든 미디어 쿼리에서 최상위 우선순위로 헤더 보호 */
        @media (min-width: 1920px),
        @media (min-width: 1440px) and (max-width: 1919px),
        @media (min-width: 1024px) and (max-width: 1439px),
        @media (min-width: 768px) and (max-width: 1023px),
        @media (min-width: 425px) and (max-width: 767px),
        @media (min-width: 375px) and (max-width: 424px),
        @media (max-width: 374px) {
            .header-content,
            header .header-content,
            .main-header .header-content,
            html body .header-content,
            html body header .header-content,
            html body .main-header .header-content {
                justify-content: flex-start !important;
                gap: 20px !important;
                display: flex !important;
                align-items: center !important;
            }

            .header-left,
            header .header-left,
            .header-content .header-left,
            html body .header-left,
            html body header .header-left,
            html body .header-content .header-left {
                flex: 0 0 auto !important;
                order: -1 !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
            }
        }

        /* PC (1920px 이상) */
        @media (min-width: 1920px) {
            .header-content {
                padding: 15px 40px !important;
            }
        }

        /* 큰 태블릿 (1440px ~ 1919px) */
        @media (min-width: 1440px) and (max-width: 1919px) {
            .header-content {
                padding: 15px 30px !important;
            }
        }

        /* 태블릿 (1024px ~ 1439px) */
        @media (min-width: 1024px) and (max-width: 1439px) {
            .header-content {
                padding: 15px 25px !important;
            }
        }

        /* 작은 태블릿 (768px ~ 1023px) */
        @media (min-width: 768px) and (max-width: 1023px) {
            .header-content {
                padding: 12px 20px !important;
            }
        }

        /* 큰 모바일 (425px ~ 767px) */
        @media (min-width: 425px) and (max-width: 767px) {
            .header-content {
                padding: 12px 15px !important;
            }
        }

        /* 모바일 (375px ~ 424px) */
        @media (min-width: 375px) and (max-width: 424px) {
            .header-content {
                padding: 10px 12px !important;
            }
        }

        /* 작은 모바일 (320px ~ 374px) */
        @media (max-width: 374px) {
            .header-content {
                padding: 10px 10px !important;
            }
        }
        </style>
        <!-- 모바일 햄버거 메뉴 (태블릿/모바일 전용 - 1024px 이하) -->
        <button class="mobile-hamburger" id="mobile-hamburger">
            <span></span>
            <span></span>
            <span></span>
        </button>
        
        <div class="container" style="overflow: visible !important; position: relative;">
            <div class="header-content" style="display: flex !important; justify-content: flex-start !important; align-items: center !important; width: 100% !important; padding: 15px 20px 15px 20px !important; overflow: visible !important; flex-direction: row !important; flex-wrap: nowrap !important; box-sizing: border-box !important; gap: 20px !important;">
                <!-- 로고 -->
                <div class="header-left" style="flex: 0 0 auto !important; order: -1 !important; position: relative !important; transform: none !important; left: auto !important; right: auto !important; margin-left: 0 !important; margin-right: 0 !important; width: auto !important; min-width: 0 !important; max-width: none !important; box-sizing: border-box !important;">
                    <h1 class="logo">
                        <a href="/" class="logo-link">
                            <div class="logo-icon">
                                <i class="fas fa-rocket header-rocket"></i>
                            </div>
                            <span class="logo-text">탑마케팅</span>
                        </a>
                    </h1>
                </div>

                <!-- 메인 네비게이션 (PC 전용 - 1025px 이상) -->
                <nav class="main-nav" id="main-nav">
                    <ul class="nav-menu">
                        <li><a href="/" class="<?= ($pageSection ?? '') === 'home' ? 'active' : '' ?>">홈</a></li>
                        <li><a href="/community" class="<?= ($pageSection ?? '') === 'community' ? 'active' : '' ?>">커뮤니티</a></li>
                        <li><a href="/lectures" class="<?= ($pageSection ?? '') === 'lectures' ? 'active' : '' ?>">강의 일정</a></li>
                        <li><a href="/events" class="<?= ($pageSection ?? '') === 'events' ? 'active' : '' ?>">행사 일정</a></li>
                        <li><a href="/notices" class="<?= ($pageSection ?? '') === 'notices' ? 'active' : '' ?>">공지사항</a></li>
                    </ul>
                </nav>

                <!-- 로그인 상태별 우측 메뉴 -->
                <div class="nav-auth">
                    <?php 
                    try {
                        $isLoggedIn = AuthMiddleware::isLoggedIn();
                    } catch (Exception $e) {
                        $isLoggedIn = false;
                    }
                    if ($isLoggedIn): ?>
                        <!-- 로그인된 사용자 메뉴 -->
                        <div class="user-menu">
                            <div class="user-avatar">
                                <?php 
                                require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
                                $profileImage = AuthMiddleware::getCurrentUserProfileImage();
                                $defaultImage = '/assets/images/default-avatar.png';
                                $imageUrl = $profileImage ? $profileImage : $defaultImage;
                                ?>
                                <img src="<?= htmlspecialchars($imageUrl) ?>" alt="프로필" 
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="avatar-fallback">
                                    👤
                                </div>
                            </div>
                            <?php 
                            try {
                                $currentUser = AuthMiddleware::getCurrentUser();
                            } catch (Exception $e) {
                                $currentUser = null;
                            } ?>
                            <span class="user-name"><?= htmlspecialchars($currentUser['nickname'] ?? '사용자') ?></span>
                            <i class="fas fa-chevron-down"></i>
                            
                        </div>
                    <?php else: ?>
                        <!-- 비로그인 사용자 메뉴 -->
                        <a href="/auth/login?redirect=<?= urlencode($_SERVER['REQUEST_URI'] ?? '/') ?>" class="nav-link login-btn">
                            <i class="fas fa-sign-in-alt"></i>
                            로그인
                        </a>
                        <a href="/auth/signup" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i>
                            회원가입
                        </a>
                    <?php endif; ?>
                </div>

                <!-- 중복 햄버거 버튼 제거됨 - mobile-hamburger만 사용 -->
                
                <style>
                /* 🎯 모바일 햄버거 메뉴 기본 스타일 */
                .mobile-hamburger {
                    /* 위치 고정 (항상) */
                    position: fixed;
                    top: 14px;
                    right: 20px;
                    z-index: 999999;

                    /* 레이아웃 */
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    align-items: center;
                    width: 44px;
                    height: 44px;
                    gap: 4px;
                    padding: 0;

                    /* 디자인 */
                    background: rgba(255, 255, 255, 0.95);
                    border: 1px solid rgba(0, 0, 0, 0.1);
                    border-radius: 8px;
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                    backdrop-filter: blur(20px);

                    /* 인터랙션 */
                    cursor: pointer;
                    transition: all 0.2s ease;
                }
                
                .mobile-hamburger span {
                    display: block;
                    width: 18px;
                    height: 2px;
                    background: #374151;
                    border-radius: 1px;
                    transition: all 0.2s ease;
                    transform-origin: center;
                    pointer-events: none;
                }
                
                /* 호버 효과 */
                .mobile-hamburger:hover {
                    background: rgba(255, 255, 255, 1);
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                    border-color: rgba(0, 0, 0, 0.2);
                }
                
                .mobile-hamburger:hover span {
                    background: #1f2937;
                }
                
                /* 클릭 효과 */
                .mobile-hamburger:active {
                    transform: scale(0.95);
                    transition: transform 0.1s ease;
                }
                
                /* 메뉴 열렸을 때 X자 변환 애니메이션 */
                .mobile-hamburger.active span:nth-child(1) {
                    transform: rotate(45deg) translate(5px, 5px);
                }
                
                .mobile-hamburger.active span:nth-child(2) {
                    opacity: 0;
                    transform: scale(0);
                }
                
                .mobile-hamburger.active span:nth-child(3) {
                    transform: rotate(-45deg) translate(5px, -5px);
                }
                
                /* 🚨 ULTRA CRITICAL FIX: 반응형 네비게이션 시스템 */

                /* 기본 스타일: 메인 네비게이션 */
                .main-nav {
                    flex: 1;
                    justify-content: center;
                    margin: 0 40px;
                }

                .nav-menu {
                    display: flex;
                    list-style: none;
                    margin: 0;
                    padding: 0;
                    gap: 30px;
                }

                .nav-menu li {
                    display: block;
                }

                .nav-menu a {
                    display: block;
                    color: #374151;
                    text-decoration: none;
                    padding: 10px 15px;
                    border-radius: 4px;
                    font-weight: 500;
                    font-size: 16px;
                    transition: all 0.2s ease;
                }

                .nav-menu a:hover {
                    background: rgba(99, 102, 241, 0.1);
                    color: #6366f1;
                }

                .nav-menu a.active {
                    background: rgba(99, 102, 241, 0.1);
                    color: #6366f1;
                    font-weight: 600;
                }

                /* PC 전용 (1025px 이상) - 네비게이션 표시 */
                @media (min-width: 1025px) {
                    .main-nav,
                    header .main-nav,
                    .header-container .main-nav,
                    nav.main-nav,
                    header nav.main-nav {
                        display: flex !important;
                        visibility: visible !important;
                        opacity: 1 !important;
                        position: relative !important;
                        z-index: 999 !important;
                    }

                    .nav-menu {
                        display: flex !important;
                        visibility: visible !important;
                        opacity: 1 !important;
                    }
                }

                /* PC에서 햄버거 숨김 */
                @media (min-width: 1025px) {
                    .mobile-hamburger {
                        display: none !important;
                        visibility: hidden !important;
                        opacity: 0 !important;
                        position: absolute !important;
                        left: -99999px !important;
                        top: -99999px !important;
                        z-index: -1 !important;
                        pointer-events: none !important;
                    }
                }

                /* 태블릿/모바일 (1024px 이하) - 햄버거 표시 */
                @media (max-width: 1024px) {
                    .mobile-hamburger,
                    header .mobile-hamburger,
                    .header-container .mobile-hamburger {
                        display: flex !important;
                        visibility: visible !important;
                    }

                    /* 메인 네비게이션 완전 숨김 (CRITICAL FIX) - 높은 특이성 */
                    .main-nav,
                    header .main-nav,
                    .header-container .main-nav,
                    nav.main-nav,
                    header nav.main-nav {
                        display: none !important;
                        visibility: hidden !important;
                        opacity: 0 !important;
                        position: absolute !important;
                        left: -9999px !important;
                        width: 0 !important;
                        height: 0 !important;
                        overflow: hidden !important;
                        pointer-events: none !important;
                        max-width: 0 !important;
                        max-height: 0 !important;
                        margin: 0 !important;
                        padding: 0 !important;
                    }

                    /* 사용자 메뉴 완전 숨김 - 높은 특이성 */
                    .user-menu,
                    header .user-menu,
                    .header-container .user-menu,
                    .nav-auth .user-menu {
                        display: none !important;
                        visibility: hidden !important;
                        opacity: 0 !important;
                        position: absolute !important;
                        left: -9999px !important;
                        pointer-events: none !important;
                        max-width: 0 !important;
                        max-height: 0 !important;
                        margin: 0 !important;
                        padding: 0 !important;
                    }

                    /* nav-auth 영역도 숨김 - 높은 특이성 */
                    .nav-auth,
                    header .nav-auth,
                    .header-container .nav-auth {
                        display: none !important;
                        visibility: hidden !important;
                        opacity: 0 !important;
                        width: 0 !important;
                        height: 0 !important;
                        overflow: hidden !important;
                        pointer-events: none !important;
                        max-width: 0 !important;
                        max-height: 0 !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        position: absolute !important;
                        left: -9999px !important;
                    }

                    /* 🚨 ULTRA FORCE: 헤더 레이아웃 조정 - 높은 특이성 + 로딩 완료 후 중앙 이동 방지 */
                    .header-content,
                    header .header-content,
                    html body .header-content,
                    html body header .header-content,
                    html body .main-header .header-content {
                        display: flex !important;
                        justify-content: flex-start !important; /* 로고 좌측 고정 - 중앙 이동 절대 방지 */
                        gap: 20px !important;
                        align-items: center !important;
                        flex-wrap: nowrap !important;
                    }

                    .header-left,
                    header .header-left {
                        order: -1 !important;
                        flex: 0 0 auto !important; /* 로고가 좌측에 고정되도록 설정 */
                    }

                    /* 모든 미디어 쿼리에서 로고 위치 강제 보호 - CSS 미디어 쿼리와 동일하게 1024px 기준 */
                    @media (max-width: 1024px) {
                        .header-left,
                        header .header-left,
                        .header-content .header-left {
                            order: -1 !important;
                            flex: 0 0 auto !important;
                            position: relative !important;
                            transform: none !important;
                            left: auto !important;
                            right: auto !important;
                            margin-left: 0 !important;
                            margin-right: 0 !important;
                        }

                        /* 최상위 우선순위로 모바일에서도 로고 위치 보호 */
                        html body .header-left,
                        html body header .header-left,
                        html body .header-content .header-left {
                            flex: 0 0 auto !important;
                            order: -1 !important;
                            position: relative !important;
                            transform: none !important;
                            left: auto !important;
                            right: auto !important;
                            margin-left: 0 !important;
                            margin-right: 0 !important;
                        }
                    }

                    /* 812×858 사이즈 타겟 특별 처리 */
                    @media (width: 812px) and (height: 858px) {
                        .main-nav, .user-menu, .nav-auth {
                            transform: translateX(-99999px) !important;
                            clip: rect(0, 0, 0, 0) !important;
                        }
                    }
                }

                /* 🚨 CRITICAL FIX: 모바일 메뉴 모달 완전 수정 */
                .mobile-menu-modal {
                    background: rgba(0, 0, 0, 0.95) !important;
                }

                .mobile-menu-modal.active {
                    background: rgba(0, 0, 0, 0.95) !important;
                }

                /* 🎨 세련된 토글 메뉴 모달 복구 */
                .mobile-modal-overlay {
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    position: fixed !important;
                    top: 0 !important;
                    left: 0 !important;
                    right: 0 !important;
                    bottom: 0 !important;
                    padding: 20px !important;
                    backdrop-filter: blur(8px) !important;
                }

                .mobile-modal-content {
                    background: white !important;
                    border-radius: 20px !important;
                    padding: 0 !important;
                    width: 100% !important;
                    max-width: 400px !important;
                    max-height: 80vh !important;
                    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25) !important;
                    overflow-y: auto !important;
                    -webkit-overflow-scrolling: touch !important;
                    transform: scale(1) !important;
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
                    border: 1px solid rgba(255, 255, 255, 0.2) !important;
                }

                .mobile-modal-content::-webkit-scrollbar {
                    width: 0px !important;
                    background: transparent !important;
                }

                /* 🌟 트렌디한 프로필 헤더 디자인 */
                .mobile-profile-header {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
                    border-radius: 20px 20px 0 0 !important;
                    padding: 28px 24px !important;
                    position: relative !important;
                    overflow: hidden !important;
                }

                .mobile-profile-header::before {
                    content: '' !important;
                    position: absolute !important;
                    top: -50% !important;
                    left: -50% !important;
                    width: 200% !important;
                    height: 200% !important;
                    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%) !important;
                    animation: float 6s ease-in-out infinite !important;
                    pointer-events: none !important;
                }

                @keyframes float {
                    0%, 100% { transform: translate(0, 0) rotate(0deg); }
                    33% { transform: translate(30px, -30px) rotate(120deg); }
                    66% { transform: translate(-20px, 20px) rotate(240deg); }
                }

                .mobile-profile-header .profile-image-large {
                    width: 64px !important;
                    height: 64px !important;
                    border-radius: 20px !important;
                    overflow: hidden !important;
                    border: 3px solid rgba(255, 255, 255, 0.3) !important;
                    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2) !important;
                    position: relative !important;
                    z-index: 2 !important;
                    transition: all 0.3s ease !important;
                }

                .mobile-profile-header .profile-image-large:hover {
                    transform: scale(1.05) !important;
                    border-color: rgba(255, 255, 255, 0.5) !important;
                }

                .mobile-profile-header .profile-image-large img {
                    width: 100% !important;
                    height: 100% !important;
                    object-fit: cover !important;
                    border-radius: 17px !important;
                }

                .mobile-profile-header .profile-info {
                    display: flex !important;
                    flex-direction: column !important;
                    justify-content: center !important;
                    gap: 4px !important;
                    position: relative !important;
                    z-index: 2 !important;
                }

                .mobile-profile-header .user-display-name {
                    font-size: 20px !important;
                    font-weight: 700 !important;
                    color: white !important;
                    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2) !important;
                    margin: 0 !important;
                    letter-spacing: -0.3px !important;
                }

                .mobile-profile-header .user-role {
                    font-size: 13px !important;
                    font-weight: 500 !important;
                    color: rgba(255, 255, 255, 0.85) !important;
                    background: rgba(255, 255, 255, 0.15) !important;
                    padding: 4px 12px !important;
                    border-radius: 20px !important;
                    display: inline-block !important;
                    width: fit-content !important;
                    backdrop-filter: blur(10px) !important;
                    border: 1px solid rgba(255, 255, 255, 0.2) !important;
                    margin: 2px 0 !important;
                }

                .mobile-profile-header .user-welcome {
                    font-size: 14px !important;
                    font-weight: 400 !important;
                    color: rgba(255, 255, 255, 0.9) !important;
                    margin: 4px 0 0 0 !important;
                    font-style: italic !important;
                }

                /* 메뉴 아이템 스타일 개선 */
                .mobile-modal-content .dropdown-item {
                    padding: 16px 24px !important;
                    border-radius: 0 !important;
                    transition: all 0.2s ease !important;
                    font-size: 16px !important;
                    min-height: auto !important;
                }

                .mobile-modal-content .dropdown-item:hover {
                    background: linear-gradient(90deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.05)) !important;
                    transform: translateX(4px) !important;
                }

                .mobile-modal-content .section-title {
                    padding: 20px 24px 8px !important;
                    font-weight: 700 !important;
                    color: #374151 !important;
                    font-size: 14px !important;
                }

                /* PC에서 모바일 요소들 완전 숨김 - CSS 미디어 쿼리와 동일하게 1025px 기준 */
                @media (min-width: 1025px) {
                    .mobile-hamburger {
                        display: none !important;
                        visibility: hidden !important;
                        opacity: 0 !important;
                        pointer-events: none !important;
                        position: absolute !important;
                        left: -99999px !important;
                        top: -99999px !important;
                        right: auto !important;
                        width: 0 !important;
                        height: 0 !important;
                        overflow: hidden !important;
                        z-index: -1 !important;
                    }

                    .mobile-menu-modal {
                        display: none !important;
                        visibility: hidden !important;
                        opacity: 0 !important;
                        pointer-events: none !important;
                    }
                }
                </style>

            </div>
        </div>

        <!-- 모바일 메뉴 오버레이 -->
        <div class="mobile-menu-overlay" id="mobile-menu-overlay"></div>
    </header>

    <!-- 모바일/태블릿 전용 햄버거 메뉴 모달 -->
    <div class="mobile-menu-modal" id="mobileMenuModal">
        <div class="mobile-modal-overlay">
            <!-- 모바일 닫기 버튼 -->
            <button class="mobile-dropdown-close" id="mobileDropdownClose">
                <i class="fas fa-times"></i>
            </button>
            
            <!-- 모달 콘텐츠 -->
            <div class="mobile-modal-content">
                <?php if ($isLoggedIn): ?>
                <!-- 로그인된 사용자 프로필 헤더 -->
                <div class="mobile-profile-header">
                    <div class="profile-image-large">
                        <?php 
                        $profileImage = AuthMiddleware::getCurrentUserProfileImage();
                        $defaultImage = '/assets/images/default-avatar.png';
                        $imageUrl = $profileImage ? $profileImage : $defaultImage;
                        ?>
                        <img src="<?= htmlspecialchars($imageUrl) ?>" alt="프로필" 
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="avatar-fallback-large">
                            👤
                        </div>
                    </div>
                    <div class="profile-info">
                        <div class="user-display-name"><?= htmlspecialchars($currentUser['nickname'] ?? '사용자') ?></div>
                        <div class="user-role">
                            <?php 
                            $userRole = AuthMiddleware::getCurrentUserRole();
                            if ($userRole === 'ROLE_ADMIN') {
                                echo '시스템 관리자';
                            } elseif ($userRole === 'ROLE_CORPORATE') {
                                echo '기업 회원';
                            } else {
                                echo '일반 회원';
                            }
                            ?>
                        </div>
                        <div class="user-welcome">안녕하세요! 👋</div>
                    </div>
                </div>
                <?php else: ?>
                <!-- 비로그인 사용자 헤더 -->
                <div class="mobile-guest-header">
                    <div class="guest-icon">👋</div>
                    <div class="guest-message">탑마케팅에 오신 것을 환영합니다!</div>
                    <div class="auth-buttons">
                        <a href="/auth/login?redirect=<?= urlencode($_SERVER['REQUEST_URI'] ?? '/') ?>" class="mobile-login-btn">로그인</a>
                        <a href="/auth/signup" class="mobile-signup-btn">회원가입</a>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- 메인 네비게이션 섹션 -->
                <div class="menu-section">
                    <div class="section-title">메인 메뉴</div>
                    <a href="/" class="dropdown-item <?= ($pageSection ?? '') === 'home' ? 'active' : '' ?>">
                        <i class="fas fa-home"></i>
                        <span>홈</span>
                    </a>
                    <a href="/community" class="dropdown-item <?= ($pageSection ?? '') === 'community' ? 'active' : '' ?>">
                        <i class="fas fa-comments"></i>
                        <span>커뮤니티</span>
                    </a>
                    <a href="/lectures" class="dropdown-item <?= ($pageSection ?? '') === 'lectures' ? 'active' : '' ?>">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>강의 일정</span>
                    </a>
                    <a href="/events" class="dropdown-item <?= ($pageSection ?? '') === 'events' ? 'active' : '' ?>">
                        <i class="fas fa-calendar-alt"></i>
                        <span>행사 일정</span>
                    </a>
                    <a href="/notices" class="dropdown-item <?= ($pageSection ?? '') === 'notices' ? 'active' : '' ?>">
                        <i class="fas fa-bullhorn"></i>
                        <span>공지사항</span>
                    </a>
                </div>
                
                <?php if ($isLoggedIn): ?>
                <div class="dropdown-divider"></div>
                
                <!-- 개인 메뉴 섹션 (로그인 사용자만) -->
                <div class="menu-section">
                    <div class="section-title">개인 메뉴</div>
                    <a href="/profile" class="dropdown-item">
                        <i class="fas fa-user"></i>
                        <span>프로필</span>
                    </a>
                    <a href="/chat" class="dropdown-item">
                        <i class="fas fa-envelope"></i>
                        <span>채팅</span>
                    </a>
                    <a href="/notifications/settings" class="dropdown-item">
                        <i class="fas fa-bell"></i>
                        <span>알림 설정</span>
                    </a>
                    <?php
                    // 기업 회원(승인된)만 신청 관리 메뉴 표시 (자신의 강의/행사 신청 관리용)
                    $showRegistrationMenu = false;
                    try {
                        if (file_exists(SRC_PATH . '/middlewares/CorporateMiddleware.php')) {
                            require_once SRC_PATH . '/middlewares/CorporateMiddleware.php';
                            $showRegistrationMenu = CorporateMiddleware::hasCorpPermission();
                        }
                    } catch (Exception $e) {
                        error_log('Header registration menu check failed: ' . $e->getMessage());
                        $showRegistrationMenu = false;
                    }

                    if ($showRegistrationMenu): ?>
                    <a href="/registrations" class="dropdown-item">
                        <i class="fas fa-clipboard-list"></i>
                        <span>신청 관리</span>
                    </a>
                    <?php endif; ?>
                    
                    <?php 
                    // 관리자를 위한 관리자 대시보드 메뉴
                    try {
                        $userRole = AuthMiddleware::getUserRole();
                        if ($userRole === 'ROLE_ADMIN'): ?>
                    <a href="/admin" class="dropdown-item admin-item">
                        <i class="fas fa-cog"></i>
                        <span>관리자 대시보드</span>
                    </a>
                    <?php endif;
                    } catch (Exception $e) {
                        // 권한 확인 실패 시 무시
                    } ?>
                </div>
                
                <div class="dropdown-divider"></div>
                
                <!-- 시스템 메뉴 -->
                <a href="/auth/logout" class="dropdown-item logout-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>로그아웃</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <main class="main-content">
        <!-- 알림 메시지 (Toast로 전환) -->
        <?php if (isset($_SESSION['error'])): ?>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                Toast.error('<?= addslashes(htmlspecialchars($_SESSION['error'])) ?>');
            });
            </script>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                Toast.success('<?= addslashes(htmlspecialchars($_SESSION['success'])) ?>');
            });
            </script>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <!-- 페이지 컨텐츠 시작 -->

    <!-- 사용자 메뉴 스타일 -->
    <style>
    
    /* 🌊 로켓 파동 애니메이션 짤림 완전 방지 */
    html, body {
        overflow-x: visible !important;
    }
    
    * {
        box-sizing: border-box;
    }
    
    .main-header, .container, .header-content, .header-left, .logo, .logo-link, .logo-icon {
        overflow: visible !important;
    }
    
    /* 🚀 헤더 로켓 애니메이션 - 완전 짤림 방지 */
    .header-rocket {
        display: inline-block;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        transform-origin: center center;
        position: relative;
        color: #3b82f6;
        font-size: 1.8rem;
        padding: 10px; /* 패딩을 크게 늘려서 안전 영역 확보 */
        margin: -8px; /* 네거티브 마진으로 시각적 위치는 유지 */
        z-index: 9999 !important;
        overflow: visible !important;
        contain: none !important; /* CSS containment 비활성화 */
    }
    
    /* 페이지 로딩 시 로켓 착륙 애니메이션 - 메인 페이지에서만 동작 */
    body.home-page .header-rocket {
        animation: rocketLanding 2.5s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards,
                   headerRocketFloat 4s ease-in-out infinite 2.5s;
    }
    
    /* 메인 페이지가 아닌 경우 기본 플로팅 애니메이션만 적용 */
    body:not(.home-page) .header-rocket {
        animation: headerRocketFloat 4s ease-in-out infinite;
    }
    
    @keyframes rocketLanding {
        0% {
            transform: translateX(-150vw) translateY(-50vh) rotate(-45deg) scale(0.3);
            opacity: 0;
            filter: blur(3px);
        }
        20% {
            opacity: 0.3;
            filter: blur(2px);
        }
        40% {
            transform: translateX(-80vw) translateY(-20vh) rotate(-30deg) scale(0.5);
            opacity: 0.6;
            filter: blur(1px);
        }
        60% {
            transform: translateX(-20vw) translateY(-5vh) rotate(-15deg) scale(0.8);
            opacity: 0.8;
            filter: blur(0.5px);
        }
        80% {
            transform: translateX(-5vw) translateY(-1vh) rotate(-5deg) scale(0.95);
            opacity: 0.9;
            filter: blur(0px);
        }
        90% {
            transform: translateX(0) translateY(2px) rotate(5deg) scale(1.1);
            opacity: 1;
        }
        95% {
            transform: translateX(0) translateY(-2px) rotate(-2deg) scale(1.05);
        }
        100% {
            transform: translateX(0) translateY(0) rotate(0deg) scale(1);
            opacity: 1;
            filter: blur(0px);
        }
    }
    
    /* 기본 헤더 로켓 애니메이션 - 우주에서 떠다니는 느낌 (착륙 후) */
    
    @keyframes headerRocketFloat {
        0%, 100% {
            transform: translateY(0px) rotate(0deg);
        }
        20% {
            transform: translateY(-2px) rotate(3deg);
        }
        40% {
            transform: translateY(-4px) rotate(0deg);
        }
        60% {
            transform: translateY(-2px) rotate(-3deg);
        }
        80% {
            transform: translateY(-1px) rotate(1deg);
        }
    }
    
    /* 로고 링크 호버 시 로켓 특수 효과 - z-index 최상위 설정 */
    .logo-link {
        position: relative;
        text-decoration: none;
        transition: all 0.3s ease;
        z-index: 9996 !important; /* 로고 링크에 높은 z-index 적용 */
    }
    
    /* 반짝이는 라인선 제거됨 */
    
    /* 착륙 시 추진 효과 - 메인 페이지에서만 */
    body.home-page .logo-icon::before {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 50%;
        width: 0;
        height: 0;
        background: linear-gradient(90deg, transparent, #fbbf24, #f59e0b, #ef4444, transparent);
        transform: translateX(-50%);
        opacity: 0;
        transition: all 0.3s ease;
        z-index: 9995 !important; /* 파동 애니메이션 z-index 추가 */
        animation: landingThruster 2.5s ease-out;
    }
    
    body.home-page .logo-icon::after {
        content: '💨';
        position: absolute;
        left: -35px;
        top: 50%;
        transform: translateY(-50%);
        opacity: 0;
        font-size: 0.8rem;
        animation: landingSmoke 2.5s ease-out;
        z-index: 9994 !important; /* 연기 애니메이션 z-index 추가 */
    }
    
    /* 메인 페이지가 아닌 경우 착륙 효과 제거 */
    body:not(.home-page) .logo-icon::before,
    body:not(.home-page) .logo-icon::after {
        display: none;
    }
    
    /* 착륙 추진 효과 애니메이션 */
    @keyframes landingThruster {
        0%, 70% {
            width: 0;
            height: 0;
            opacity: 0;
        }
        75% {
            width: 30px;
            height: 3px;
            opacity: 0.8;
            box-shadow: 0 0 10px #fbbf24, 0 0 20px #f59e0b;
        }
        85% {
            width: 40px;
            height: 5px;
            opacity: 1;
            box-shadow: 0 0 15px #fbbf24, 0 0 30px #f59e0b, 0 0 45px #ef4444;
        }
        95% {
            width: 20px;
            height: 2px;
            opacity: 0.5;
        }
        100% {
            width: 0;
            height: 0;
            opacity: 0;
        }
    }
    
    /* 착륙 연기 효과 */
    @keyframes landingSmoke {
        0%, 60% {
            opacity: 0;
            left: -35px;
        }
        70% {
            opacity: 0.8;
            left: -25px;
            content: '💨';
        }
        80% {
            opacity: 1;
            left: -20px;
            content: '💨💨';
        }
        90% {
            opacity: 0.6;
            left: -15px;
            content: '💨💨💨';
        }
        100% {
            opacity: 0;
            left: -10px;
        }
    }
    
    /* 착륙 시 로고 아이콘에 추진 효과 적용 - 메인 페이지에서만 */
    .logo-icon {
        position: relative;
        overflow: visible;
    }
    
    /* 착륙 완료 시 충격파 효과 */
    @keyframes landingShockwave {
        0% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7);
        }
        25% {
            transform: scale(1.1);
            box-shadow: 0 0 0 10px rgba(59, 130, 246, 0.5);
        }
        50% {
            transform: scale(1.05);
            box-shadow: 0 0 0 20px rgba(59, 130, 246, 0.3);
        }
        75% {
            transform: scale(1.02);
            box-shadow: 0 0 0 30px rgba(59, 130, 246, 0.1);
        }
        100% {
            transform: scale(1);
            box-shadow: 0 0 0 40px rgba(59, 130, 246, 0);
        }
    }
    
    /* 착륙 완료 시 로고 아이콘에 충격파 적용 */
    .logo-icon {
        animation: landingShockwave 1s ease-out 2.3s;
    }
    
    /* 호버 시 로켓 엔진 점화! - 안전한 범위로 제한 */
    .logo-link:hover .header-rocket {
        animation: headerRocketIgnition 0.8s ease-in-out;
        transform: translateY(-3px) rotate(-5deg) scale(1.05); /* 회전과 크기를 줄여서 안전하게 */
        color: #1d4ed8;
        filter: drop-shadow(0 0 8px rgba(59, 130, 246, 0.4));
    }
    
    @keyframes headerRocketIgnition {
        0% {
            transform: translateY(0px) rotate(0deg) scale(1);
        }
        30% {
            transform: translateY(-1px) rotate(-4deg) scale(1.05);
        }
        60% {
            transform: translateY(-2px) rotate(-6deg) scale(1.08);
        }
        100% {
            transform: translateY(-3px) rotate(-8deg) scale(1.1);
        }
    }
    
    /* 호버 시 반짝이는 효과 제거됨 */
    
    /* 클릭 시 로켓 발사! - 안전한 범위로 제한 */
    .logo-link:active .header-rocket {
        animation: headerRocketLaunch 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        transform: translateY(-5px) rotate(-10deg) scale(1.08); /* 변환 범위를 줄여서 안전하게 */
    }
    
    @keyframes headerRocketLaunch {
        0% {
            transform: translateY(-3px) rotate(-5deg) scale(1.05);
        }
        40% {
            transform: translateY(-4px) rotate(-8deg) scale(1.06);
        }
        70% {
            transform: translateY(-6px) rotate(-10deg) scale(1.08);
        }
        100% {
            transform: translateY(-5px) rotate(-10deg) scale(1.08);
        }
    }
    
    /* 로고 텍스트 호버 효과 */
    .logo-text {
        transition: all 0.3s ease;
        /* 색상은 기본 CSS에서 처리 - !important 제거 */
        font-weight: 700;
        font-size: 1.5rem;
    }
    
    /* 메인 페이지에서만 로고 텍스트 나타나기 애니메이션 */
    body.home-page .logo-text {
        opacity: 0;
        animation: logoTextAppear 2.8s ease-out forwards;
    }
    
    /* 메인 페이지가 아닌 경우 즉시 표시 */
    body:not(.home-page) .logo-text {
        opacity: 1;
    }
    
    /* 로고 링크 텍스트 색상 - 기본 CSS에서 처리 */
    .logo-link {
        /* 색상은 기본 CSS에서 처리 - !important 제거 */
    }
    
    .logo-link .logo-text {
        /* 색상은 기본 CSS에서 처리 - !important 제거 */
    }
    
    @keyframes logoTextAppear {
        0%, 60% {
            opacity: 0;
            transform: translateY(10px);
        }
        70% {
            opacity: 0.3;
            transform: translateY(5px);
        }
        80% {
            opacity: 0.6;
            transform: translateY(2px);
        }
        90% {
            opacity: 0.8;
            transform: translateY(-1px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .logo-link:hover .logo-text {
        color: #1d4ed8;
        text-shadow: 0 0 10px rgba(59, 130, 246, 0.3);
    }
    
    /* 로고 아이콘 컨테이너 - z-index로 로켓 애니메이션 짤림 방지 */
    .logo-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(29, 78, 216, 0.05));
        transition: all 0.3s ease;
        margin-right: 12px;
        margin-left: 0px; /* 로고를 좌측으로 더 붙이기 위해 마진 제거 */
        position: relative;
        overflow: visible;
        z-index: 9998 !important; /* 로켓보다 약간 낮은 z-index로 설정 */
        contain: none !important; /* CSS containment 비활성화로 애니메이션 짤림 방지 */
    }
    
    /* 메인 페이지에서만 로고 아이콘 나타나기 애니메이션 */
    body.home-page .logo-icon {
        opacity: 0;
        animation: logoIconAppear 2.6s ease-out forwards,
                   landingShockwave 1s ease-out 2.3s;
    }
    
    /* 메인 페이지가 아닌 경우 즉시 표시 */
    body:not(.home-page) .logo-icon {
        opacity: 1;
    }
    
    @keyframes logoIconAppear {
        0%, 50% {
            opacity: 0;
            transform: scale(0.8);
        }
        70% {
            opacity: 0.5;
            transform: scale(0.9);
        }
        85% {
            opacity: 0.8;
            transform: scale(1.05);
        }
        100% {
            opacity: 1;
            transform: scale(1);
        }
    }
    
    .logo-link:hover .logo-icon {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(29, 78, 216, 0.1));
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.2);
    }
    
    /* 태블릿/모바일 반응형 */
    @media (max-width: 768px) {
        .header-rocket {
            font-size: 1.5rem;
        }

        .logo-text {
            font-size: 1.3rem;
        }

        .logo-icon {
            width: 35px;
            height: 35px;
            margin-right: 8px;
            margin-left: 10px; /* 태블릿/모바일에서도 좌측 여백 유지 */
        }

        .logo-link::after {
            right: -20px;
            font-size: 0.6rem;
        }
    }
    
    /* ============================================================
       추가 헤더 컴포넌트 스타일 (로고 관련 아님)
       ============================================================ */
    .header-left {
        z-index: 9997 !important; /* 로고 영역 z-index (로켓 애니메이션용) */
    }
    
    .main-nav {
        flex: 1;
        display: flex;
        justify-content: center;
        margin: 0 40px;
    }
    
    .nav-auth {
        flex: 0 0 auto;
    }
    
    /* 메인 네비게이션 메뉴 스타일 수정 */
    .nav-menu a {
        color: #374151 !important;
        text-decoration: none;
        padding: 10px 15px;
        border-radius: 4px;
        transition: all 0.3s ease;
        font-weight: 500;
        font-size: 16px;
    }
    
    .nav-menu a:hover {
        background-color: rgba(59, 130, 246, 0.1);
        color: #1d4ed8 !important;
        text-decoration: none;
    }
    
    .nav-menu a.active {
        background-color: rgba(59, 130, 246, 0.15);
        color: #1d4ed8 !important;
    }
    
    .nav-auth {
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .login-btn {
        color: #374151;
        text-decoration: none;
        padding: 8px 16px;
        border-radius: 6px;
        transition: background-color 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }
    
    .login-btn:hover {
        background-color: rgba(59, 130, 246, 0.1);
        color: #1d4ed8;
    }
    
    .user-menu {
        position: relative;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        background: rgba(0, 0, 0, 0.05);
        border-radius: 25px;
        cursor: pointer;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(0, 0, 0, 0.1);
    }
    
    .user-menu:hover {
        background: rgba(0, 0, 0, 0.08);
    }
    
    .user-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        overflow: hidden;
        position: relative;
    }
    
    .user-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .avatar-fallback {
        width: 100%;
        height: 100%;
        background: #667eea;
        display: none;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }
    
    .user-name {
        color: #374151;
        font-size: 14px;
        font-weight: 500;
        max-width: 100px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .user-menu i {
        color: rgba(0, 0, 0, 0.5);
        font-size: 12px;
        transition: transform 0.3s ease;
    }
    
    .user-menu.active i {
        transform: rotate(180deg);
    }
    
    /* 드롭다운 메뉴 - 데스크톱에서만 적용 - CSS 미디어 쿼리와 동일하게 1025px 기준 */
    @media (min-width: 1025px) {
        .user-dropdown {
            position: absolute !important;
            top: calc(100% + 10px) !important;
            right: 0 !important;
            min-width: 200px !important;
            background: white !important;
            border-radius: 8px !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
            opacity: 0 !important;
            visibility: hidden !important;
            transform: translateY(-10px) !important;
            transition: all 0.3s ease !important;
            z-index: 1000 !important;
            border: 1px solid #e5e7eb !important;
            display: block !important;
            pointer-events: none !important;
        }
        
        .user-menu.active .user-dropdown {
            opacity: 0 !important;
            visibility: hidden !important;
            transform: translateY(-10px) !important;
            display: none !important;
            z-index: 1000 !important;
            pointer-events: none !important;
        }
    }
    
    .dropdown-header {
        padding: 15px;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .user-display-name {
        display: block;
        font-weight: 600;
        color: #1f2937;
        font-size: 14px;
    }
    
    .dropdown-divider {
        height: 1px;
        background: #f3f4f6;
        margin: 0;
    }
    
    .dropdown-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 15px;
        color: #374151;
        text-decoration: none;
        font-size: 14px;
        transition: background-color 0.2s ease;
        position: relative;
    }
    
    .dropdown-item:hover {
        background-color: #f9fafb;
    }
    
    .dropdown-item i {
        width: 16px;
        color: #6b7280;
    }
    
    .logout-item {
        color: #dc2626;
    }
    
    .logout-item:hover {
        background-color: #fef2f2;
    }
    
    .logout-item i {
        color: #dc2626;
    }
    
    .notification-badge {
        background: #ef4444;
        color: white;
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 10px;
        margin-left: auto;
        font-weight: bold;
        min-width: 16px;
        text-align: center;
        position: static;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    /* 반응형 - 태블릿/모바일 헤더 한 줄 유지 */
    @media (max-width: 768px) {
        .header-content {
            display: flex !important;
            flex-direction: row !important;
            justify-content: space-between !important;
            align-items: center !important;
            padding: 12px 0 !important;
            gap: 0 !important;
        }

        .main-nav {
            display: none !important;
        }

        .nav-auth {
            flex: 0 0 auto !important;
            margin-left: auto !important;
        }

        .user-name {
            display: none;
        }

        .user-dropdown {
            min-width: 180px;
        }

        .nav-auth {
            gap: 10px;
        }

        .login-btn {
            padding: 6px 12px;
            font-size: 13px;
        }
    }

    /* 작은 모바일에서 드롭다운 위치 조정 */
    @media (max-width: 480px) {
        .user-dropdown {
            right: -10px;
            min-width: 160px;
        }

        /* 작은 모바일에서 헤더 더 컴팩트하게 */
        .header-content {
            padding: 10px 0 !important;
        }

        .logo-text {
            font-size: 1.2rem !important;
        }

        .header-rocket {
            font-size: 1.4rem !important;
        }
    }
    </style>

    <script>
    <?php
    // 관리자 권한 확인을 위한 JavaScript 변수 설정
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    $isAdmin = AuthMiddleware::isAdmin();
    $currentRole = AuthMiddleware::getCurrentUserRole();
    $currentUserId = AuthMiddleware::getCurrentUserId();
    
    // PHP 디버깅 정보를 JavaScript 콘솔에 출력
    ?>
    
    // 관리자 여부를 JavaScript 변수로 전달
    const isAdmin = <?= $isAdmin ? 'true' : 'false' ?>;
    
    // PHP에서 전달된 관리자 정보 디버깅

    // 🚀 v3.64.0: 모바일 메뉴 초기화 중복 실행 방지 (3번 반복 → 1번만 실행)
    if (!window.headerMobileMenuInitialized) {
        window.headerMobileMenuInitialized = true;

        document.addEventListener('DOMContentLoaded', function() {
            // 🚀 New Mobile Hamburger Menu v4.0.0 (단일 버튼)
            const mobileHamburger = document.getElementById('mobile-hamburger');
            const mobileMenuModal = document.getElementById('mobileMenuModal');
            const mobileDropdownClose = document.getElementById('mobileDropdownClose');

            // 화면 크기 감지 함수 (태블릿 포함) - CSS 미디어 쿼리와 동일하게 1024px 기준
            const isMobile = () => window.innerWidth <= 1024;

            // 📱 레이아웃 강제 수정 함수 (812×858 사이즈 긴급 대응)
            function forceCorrectLayout() {
                const screenWidth = window.innerWidth;
                const screenHeight = window.innerHeight;

                // CSS 미디어 쿼리와 동일하게 1024px 이하 모든 경우 처리
                if (screenWidth <= 1024) {
                    // 강제로 요소들 숨기기
                    const elementsToHide = [
                        '.main-nav',
                        '.user-menu',
                        '.nav-auth',
                        'nav.main-nav',
                        'header .main-nav',
                        'header .user-menu',
                        'header .nav-auth'
                    ];

                    elementsToHide.forEach(selector => {
                        const elements = document.querySelectorAll(selector);
                        elements.forEach(el => {
                            if (el) {
                                // 헤더 레이아웃에 영향을 주지 않도록 display: none만 사용
                                el.style.setProperty('display', 'none', 'important');
                                el.style.setProperty('visibility', 'hidden', 'important');
                                el.style.setProperty('opacity', '0', 'important');
                                // position과 크기 설정 제거 (헤더 레이아웃 보호)
                                el.style.setProperty('pointer-events', 'none', 'important');
                            }
                        });
                    });

                    // 헤더 레이아웃 보호를 위한 추가 규칙 적용
                    const headerContent = document.querySelector('.header-content');
                    const headerLeft = document.querySelector('.header-left');

                    if (headerContent) {
                        headerContent.style.setProperty('display', 'flex', 'important');
                        headerContent.style.setProperty('justify-content', 'flex-start', 'important');
                        headerContent.style.setProperty('align-items', 'center', 'important');
                        headerContent.style.setProperty('width', '100%', 'important');
                        headerContent.style.setProperty('gap', '20px', 'important');
                    }

                    // 로고 위치 강제 보호 (JavaScript 변경 방지)
                    if (headerLeft) {
                        // 모든 가능한 스타일 속성 강제 설정
                        const logoStyles = {
                            'flex': '0 0 auto',
                            'order': '-1',
                            'position': 'relative',
                            'transform': 'none',
                            'left': 'auto',
                            'right': 'auto',
                            'margin-left': '0',
                            'margin-right': '0'
                        };

                        Object.entries(logoStyles).forEach(([property, value]) => {
                            headerLeft.style.setProperty(property, value, 'important');
                        });


                    }

                    // 햄버거 메뉴 강제 표시
                    const hamburgerElements = document.querySelectorAll('.mobile-hamburger');
                    hamburgerElements.forEach(el => {
                        if (el) {
                            el.style.setProperty('display', 'flex', 'important');
                            el.style.setProperty('visibility', 'visible', 'important');
                            el.style.setProperty('position', 'fixed', 'important');
                            el.style.setProperty('top', '14px', 'important');
                            el.style.setProperty('right', '20px', 'important');
                            el.style.setProperty('z-index', '999999', 'important');
                        }
                    });
                }
            }

            // 📱 모바일 메뉴 모달 강제 닫힌 상태 초기화 (CRITICAL FIX)
            function ensureMobileMenuClosed() {
                if (mobileMenuModal) {
                    mobileMenuModal.classList.remove('active');
                    mobileMenuModal.style.position = 'fixed';
                    mobileMenuModal.style.top = '0';
                    mobileMenuModal.style.left = '0';
                    mobileMenuModal.style.right = '0';
                    mobileMenuModal.style.bottom = '0';
                    mobileMenuModal.style.zIndex = '9999';
                    mobileMenuModal.style.opacity = '0';
                    mobileMenuModal.style.visibility = 'hidden';
                    mobileMenuModal.style.pointerEvents = 'none';
                    mobileMenuModal.style.display = 'none'; // 완전히 숨기기
                }

                if (mobileHamburger) {
                    mobileHamburger.classList.remove('active');
                }

                // body 스크롤 복원
                document.body.style.overflow = '';

                // 🚀 v3.64.0: 중복 로그 제거 (3번 반복 방지)
                if (window.DEBUG_MODE) {

                }
            }

            // 페이지 로드 시 즉시 실행 (1회만)
            forceCorrectLayout();
            ensureMobileMenuClosed();

            // 🚀 v3.64.0: 리사이즈 이벤트 중복 등록 방지
            if (!window.headerResizeListenerAdded) {
                window.headerResizeListenerAdded = true;
                window.addEventListener('resize', forceCorrectLayout);
            }

            // 🚀 v3.64.0: setTimeout 중복 제거 (불필요한 3번 반복 실행 제거)
            // 기존: setTimeout(forceCorrectLayout, 100/500) × 2
            //      setTimeout(ensureMobileMenuClosed, 100/500) × 2
            // 삭제 이유: 이미 즉시 실행되고 있으며, resize 이벤트로 충분

        // 햄버거 메뉴 버튼 클릭 이벤트 (애니메이션 포함)
        function toggleMobileMenu(e) {
            e.preventDefault();
            e.stopPropagation();

            if (mobileMenuModal && mobileHamburger) {
                const isActive = mobileMenuModal.classList.contains('active');

                if (!isActive) {
                    // 모달 열기 - CSS 클래스만 사용하고 인라인 스타일 제거
                    mobileMenuModal.style.removeProperty('display');
                    mobileMenuModal.style.removeProperty('opacity');
                    mobileMenuModal.style.removeProperty('visibility');
                    mobileMenuModal.style.removeProperty('pointer-events');
                    mobileMenuModal.style.removeProperty('height');
                    mobileMenuModal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                } else {
                    // 모달 닫기
                    mobileMenuModal.classList.remove('active');
                    document.body.style.overflow = '';
                }

                // 햄버거 아이콘 X자 변환 애니메이션
                mobileHamburger.classList.toggle('active');
            }
        }
        
        if (mobileHamburger) {
            mobileHamburger.addEventListener('click', toggleMobileMenu);
        }
        
        // 모바일 닫기 버튼 이벤트
        if (mobileDropdownClose) {
            mobileDropdownClose.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                closeMobileMenu();
            });
        }
        
        // 모바일 오버레이 배경 클릭으로 닫기
        if (mobileMenuModal) {
            const overlay = mobileMenuModal.querySelector('.mobile-modal-overlay');
            if (overlay) {
                overlay.addEventListener('click', function(e) {
                    if (e.target === overlay) {
                        closeMobileMenu();
                    }
                });
            }
        }
        
        // 데스크톱에서는 기존 드롭다운 방식 유지
        const userMenu = document.querySelector('.user-menu');
        if (userMenu) {
            userMenu.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                if (!isMobile()) {
                    // 데스크톱: 기존 작은 드롭다운 방식 유지
                    createDesktopDropdown();
                }
            });
            
            // ESC 키로 드롭다운 닫기
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    if (isMobile()) {
                        closeMobileMenu();
                    } else {
                        closeDesktopDropdown();
                    }
                }
            });
            
            // 외부 클릭으로 데스크톱 드롭다운 닫기
            document.addEventListener('click', function(e) {
                if (!isMobile()) {
                    closeDesktopDropdown(e);
                }
            });
            
            // 화면 크기 변경 시 드롭다운 정리
            window.addEventListener('resize', function() {
                if (isMobile()) {
                    closeDesktopDropdown();
                } else {
                    closeMobileMenu();
                }
            });
        }
        
        // 모바일 메뉴 닫기 함수 (강화 버전)
        function closeMobileMenu() {
            if (mobileMenuModal) {
                mobileMenuModal.classList.remove('active');
                mobileMenuModal.style.display = 'none';
                mobileMenuModal.style.opacity = '0';
                mobileMenuModal.style.visibility = 'hidden';
                mobileMenuModal.style.pointerEvents = 'none';
                document.body.style.overflow = '';
            }

            // 햄버거 아이콘 애니메이션 원복
            if (mobileHamburger) {
                mobileHamburger.classList.remove('active');
            }


        }
        
        // 데스크톱 드롭다운 생성 함수
        <?php
        // 데스크톱 드롭다운용 신청 관리 메뉴 HTML 생성
        $desktopRegistrationMenuHtml = '';
        $showRegistrationMenuDesktop = false;
        try {
            if (file_exists(SRC_PATH . '/middlewares/CorporateMiddleware.php')) {
                require_once SRC_PATH . '/middlewares/CorporateMiddleware.php';
                $showRegistrationMenuDesktop = CorporateMiddleware::hasCorpPermission();
            }
        } catch (Exception $e) {
            error_log('Desktop dropdown registration menu check failed: ' . $e->getMessage());
            $showRegistrationMenuDesktop = false;
        }

        if ($showRegistrationMenuDesktop) {
            $desktopRegistrationMenuHtml = '<a href="/registrations" class="dropdown-item">
                <i class="fas fa-clipboard-list"></i>
                <span>신청 관리</span>
            </a>';
        }
        ?>

        function createDesktopDropdown() {
            // 기존 드롭다운 확인
            let existingDropdown = document.getElementById('floating-user-dropdown');

            if (existingDropdown) {
                existingDropdown.remove();
                userMenu.classList.remove('active');
                return;
            }

            userMenu.classList.add('active');
            const rect = userMenu.getBoundingClientRect();

            // 현재 읽지 않은 메시지 수 가져오기
            const currentBadge = document.getElementById('chatNotificationBadge');
            const unreadCount = currentBadge ? parseInt(currentBadge.textContent) || 0 : 0;
            const badgeHtml = unreadCount > 0 ? `<span class="notification-badge dropdown-chat-badge">${unreadCount}</span>` : '';

            // 관리자 메뉴 HTML 생성
            const adminMenuHtml = isAdmin ? `
                <a href="/admin" class="dropdown-item admin-item">
                    <span>⚙️</span>
                    <span>관리자 페이지</span>
                </a>
                <div class="dropdown-divider"></div>` : '';

            const floatingDropdown = document.createElement('div');
            floatingDropdown.id = 'floating-user-dropdown';
            const userNickname = '<?= htmlspecialchars($currentUser['nickname'] ?? '사용자') ?>';
            const registrationMenuHtml = `<?= $desktopRegistrationMenuHtml ?>`;

            floatingDropdown.innerHTML = `
                <div class="dropdown-header">
                    <div class="user-info">
                        <span class="user-display-name">${userNickname}</span>
                    </div>
                </div>
                <div class="dropdown-divider"></div>
                <a href="/profile" class="dropdown-item">
                    <i class="fas fa-user"></i>
                    <span>프로필</span>
                </a>
                <a href="/chat" class="dropdown-item">
                    <i class="fas fa-envelope"></i>
                    <span>채팅</span>
                    ${badgeHtml}
                </a>
                <a href="/notifications/settings" class="dropdown-item">
                    <i class="fas fa-bell"></i>
                    <span>알림 설정</span>
                </a>
                ${registrationMenuHtml}
                ${adminMenuHtml}
                <div class="dropdown-divider"></div>
                <a href="/auth/logout" class="dropdown-item logout-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>로그아웃</span>
                </a>
            `;
            
            // 데스크톱 드롭다운 스타일
            floatingDropdown.style.cssText = `
                position: fixed !important;
                top: ${rect.bottom + 10}px !important;
                right: ${window.innerWidth - rect.right}px !important;
                width: 200px !important;
                background: white !important;
                border-radius: 8px !important;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
                z-index: 999999 !important;
                border: 1px solid #e5e7eb !important;
                display: block !important;
                opacity: 1 !important;
                visibility: visible !important;
                padding: 0 !important;
                margin: 0 !important;
                font-family: 'Noto Sans KR', sans-serif !important;
                color: #374151 !important;
            `;
            
            // 데스크톱 드롭다운 스타일 적용
            applyDesktopDropdownStyles(floatingDropdown);
            document.body.appendChild(floatingDropdown);
        }
        
        // 데스크톱 드롭다운 닫기 함수
        function closeDesktopDropdown(e) {
            const existingDropdown = document.getElementById('floating-user-dropdown');
            if (existingDropdown) {
                if (!e || (!userMenu.contains(e.target) && !existingDropdown.contains(e.target))) {
                    existingDropdown.remove();
                    userMenu.classList.remove('active');
                }
            }
        }
        
        // 데스크톱 드롭다운 스타일 적용 함수
        async function applyDesktopDropdownStyles(dropdown) {
            dropdown.querySelectorAll('.dropdown-header').forEach(el => {
                el.style.cssText = 'padding: 15px; border-bottom: 1px solid #f3f4f6;';
            });
            dropdown.querySelectorAll('.user-display-name').forEach(el => {
                el.style.cssText = 'display: block; font-weight: 600; color: #1f2937; font-size: 14px;';
            });
            dropdown.querySelectorAll('.dropdown-divider').forEach(el => {
                el.style.cssText = 'height: 1px; background: #f3f4f6; margin: 0;';
            });
            dropdown.querySelectorAll('.dropdown-item').forEach(el => {
                el.style.cssText = 'display: flex; align-items: center; gap: 12px; padding: 12px 15px; color: #374151; text-decoration: none; font-size: 14px; transition: background-color 0.2s ease;';
                el.addEventListener('mouseenter', () => el.style.backgroundColor = '#f9fafb');
                el.addEventListener('mouseleave', () => el.style.backgroundColor = 'transparent');
            });
            dropdown.querySelectorAll('.logout-item').forEach(el => {
                el.style.color = '#dc2626';
                el.addEventListener('mouseenter', () => el.style.backgroundColor = '#fef2f2');
                el.addEventListener('mouseleave', () => el.style.backgroundColor = 'transparent');
            });
            dropdown.querySelectorAll('.admin-item').forEach(el => {
                el.style.color = '#7c3aed';
                el.addEventListener('mouseenter', () => el.style.backgroundColor = '#f3f0ff');
                el.addEventListener('mouseleave', () => el.style.backgroundColor = 'transparent');
            });
            dropdown.querySelectorAll('.notification-badge').forEach(el => {
                el.style.cssText = 'background: #ef4444; color: white; font-size: 10px; padding: 2px 6px; border-radius: 10px; margin-left: auto; font-weight: bold; min-width: 16px; text-align: center;';
            });
        }
        
        // 로그아웃 확인 (Modal.confirm() 사용)
        window.confirmLogout = async function() {
            return await Modal.confirm('정말 로그아웃하시겠습니까?', {
                type: 'warning',
                confirmText: '로그아웃',
                cancelText: '취소'
            });
        };

        // 로그아웃 링크 이벤트 리스너 (동적 생성된 요소 포함)
        document.addEventListener('click', async function(e) {
            const logoutItem = e.target.closest('.logout-item');
            if (logoutItem) {
                e.preventDefault();
                const confirmed = await confirmLogout();
                if (confirmed) {
                    window.location.href = logoutItem.href;
                }
            }
        });

        // 전역 에러 핸들러 (브라우저 확장 프로그램 에러 방지)
        window.addEventListener('error', function(e) {
            // 브라우저 확장 프로그램 관련 에러는 무시
            if (e.message && e.message.includes('message channel closed')) {
                e.preventDefault();
                return false;
            }
            if (e.message && e.message.includes('asynchronous response')) {
                e.preventDefault();
                return false;
            }
        });
        
        // Promise rejection 에러 핸들러
        window.addEventListener('unhandledrejection', function(e) {
            // 브라우저 확장 프로그램 관련 에러는 무시
            if (e.reason && e.reason.message && 
                (e.reason.message.includes('message channel closed') || 
                 e.reason.message.includes('asynchronous response'))) {
                e.preventDefault();
                return false;
            }
        });
        }, { once: true }); // 🚀 v3.64.0: DOMContentLoaded 중복 실행 방지

        // ============================================================
        // 헤더 로고 위치 지속 검증 시스템 v4.1 (ENHANCED)
        // - 모든 가능한 셀렉터로 지속적 검증
        // - 더 자주 그리고 광범위하게 확인
        // ============================================================
        // ✅ v3.83.0: 헤더 로고 위치 수정 완료
        // 검증 시스템 제거 - 로고 위치가 order: -1로 영구 고정되었으므로 불필요


    } // 🚀 v3.64.0: headerMobileMenuInitialized 플래그 종료
    </script> 