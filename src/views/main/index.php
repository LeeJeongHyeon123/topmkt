        <?php
        // 컴포넌트 로드
        require_once SRC_PATH . '/components/ui/Button.php';
        ?>

        <!-- 성공/에러 메시지 표시 -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message">
                <div class="container">
                    <div class="message-content">
                        <i data-lucide="check-circle" width="20" height="20"></i>
                        <span><?= htmlspecialchars($_SESSION['success']) ?></span>
                        <?= renderButton('', 'secondary', 'sm', [
                            'class' => 'close-message',
                            'onclick' => 'this.parentElement.parentElement.remove()',
                            'icon' => 'x',
                            'ariaLabel' => '닫기'
                        ]) ?>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <!-- 히어로 섹션 -->
        <div class="hero-content">
            <div class="container">
                <div class="hero-text">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- 로그인된 사용자 환영 메시지 -->
                        <div class="welcome-back">
                            <div class="welcome-icon">
                                <i data-lucide="hand" width="48" height="48"></i>
                            </div>
                            <h1 class="hero-title">안녕하세요, <?= htmlspecialchars($_SESSION['username'] ?? '회원') ?>님!</h1>
                            <p class="hero-subtitle">
                                오늘도 탑마케팅과 함께 성공적인 하루를 만들어가세요.<br>
                                새로운 기회와 인사이트가 여러분을 기다리고 있습니다.
                            </p>
                            
                            <div class="user-quick-stats">
                                <div class="quick-stat">
                                    <div class="stat-icon">
                                        <i data-lucide="calendar-check" width="24" height="24"></i>
                                    </div>
                                    <div class="stat-info">
                                        <div class="stat-number">
                                            <?php 
                                            $memberDays = isset($_SESSION['user_id']) ? 
                                                ceil((time() - strtotime('2024-01-01')) / 86400) : 0; // 임시 계산
                                            echo $memberDays;
                                            ?>
                                        </div>
                                        <div class="stat-label">일째 함께</div>
                                    </div>
                                </div>
                                <div class="quick-stat">
                                    <div class="stat-icon">
                                        <i data-lucide="heart" width="24" height="24"></i>
                                    </div>
                                    <div class="stat-info">
                                        <div class="stat-number">
                                            <?php 
                                            // 활동 포인트 계산 (임시)
                                            echo isset($_SESSION['user_id']) ? '1,247' : '0';
                                            ?>
                                        </div>
                                        <div class="stat-label">활동 포인트</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="hero-actions">
                                <a href="/community" class="btn btn-primary-gradient">
                                    <i data-lucide="message-circle" width="20" height="20"></i>
                                    커뮤니티 참여하기
                                </a>
                                <a href="/profile" class="btn btn-outline-white">
                                    <i data-lucide="user-cog" width="20" height="20"></i>
                                    프로필 관리
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- 비로그인 사용자 -->
                        <h1 class="hero-title">글로벌 네트워크 마케팅의 새로운 시작</h1>
                        <p class="hero-subtitle">
                            전 세계 마케팅 전문가들과 함께 성장하고, 무한한 가능성을 발견하세요.<br>
                            탑마케팅과 함께라면 성공은 선택이 아닌 필연입니다.
                        </p>
                        
                        <div class="hero-actions">
                            <a href="/auth/signup" class="btn btn-primary-gradient rocket-launch-btn">
                                <i data-lucide="rocket" width="20" height="20" class="rocket-icon"></i>
                                무료로 시작하기
                            </a>
                            <a href="/auth/login" class="btn btn-outline-white">
                                <i data-lucide="log-in" width="20" height="20"></i>
                                로그인
                            </a>
                        </div>
                        
                        <div class="hero-features">
                            <div class="feature-item">
                                <i data-lucide="check" width="20" height="20"></i>
                                <span>무료 가입</span>
                            </div>
                            <div class="feature-item">
                                <i data-lucide="check" width="20" height="20"></i>
                                <span>전문가 네트워킹</span>
                            </div>
                            <div class="feature-item">
                                <i data-lucide="check" width="20" height="20"></i>
                                <span>실시간 교육</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="hero-visual">
                    <div class="floating-cards">
                        <div class="card card-1">
                            <i data-lucide="trending-up" width="24" height="24"></i>
                            <span>성장 분석</span>
                        </div>
                        <div class="card card-2">
                            <i data-lucide="users" width="24" height="24"></i>
                            <span>네트워킹</span>
                        </div>
                        <div class="card card-3">
                            <i data-lucide="graduation-cap" width="24" height="24"></i>
                            <span>전문 교육</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <!-- 성공/에러 메시지 스타일 -->
    <style>
        .success-message {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 15px 0;
            position: relative;
            animation: slideInDown 0.5s ease-out;
        }
        
        .message-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            position: relative;
        }
        
        .close-message {
            position: absolute;
            right: 0;
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            padding: 5px;
            opacity: 0.8;
            transition: opacity 0.3s ease;
        }
        
        .close-message:hover {
            opacity: 1;
        }
        
        /* 환영 메시지 스타일 */
        .welcome-back {
            text-align: center;
            animation: fadeInUp 0.8s ease-out;
        }
        
        .welcome-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            animation: wave 2s ease-in-out infinite;
        }
        
        .user-quick-stats {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin: 30px 0;
            flex-wrap: wrap;
        }
        
        .quick-stat {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255, 255, 255, 0.1);
            padding: 15px 20px;
            border-radius: 12px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .stat-icon {
            font-size: 1.5rem;
            color: #fbbf24;
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: bold;
            color: white;
            line-height: 1;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
        }
        
        /* 히어로 특징 */
        .hero-features {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.95rem;
        }
        
        .feature-item i {
            color: #10b981;
            font-size: 1.1rem;
        }
        
        /* 애니메이션 */
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translate3d(0, -100%, 0);
            }
            to {
                opacity: 1;
                transform: translate3d(0, 0, 0);
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translate3d(0, 40px, 0);
            }
            to {
                opacity: 1;
                transform: translate3d(0, 0, 0);
            }
        }
        
        @keyframes wave {
            0%, 100% {
                transform: rotate(0deg);
            }
            25% {
                transform: rotate(-10deg);
            }
            75% {
                transform: rotate(10deg);
            }
        }
        
        /* 🚀 로켓 애니메이션 효과 */
        .rocket-icon {
            display: inline-block;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transform-origin: center bottom;
            position: relative;
        }
        
        .rocket-launch-btn {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .rocket-launch-btn::before {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 50%;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, #fbbf24, #f59e0b, #d97706, transparent);
            transform: translateX(-50%);
            transition: width 0.6s ease;
        }
        
        .rocket-launch-btn::after {
            content: '💨';
            position: absolute;
            left: -30px;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }
        
        /* 기본 로켓 애니메이션 - 둥둥 떠다니기 */
        .rocket-icon {
            animation: rocketFloat 3s ease-in-out infinite;
        }
        
        @keyframes rocketFloat {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            25% {
                transform: translateY(-3px) rotate(2deg);
            }
            50% {
                transform: translateY(-6px) rotate(0deg);
            }
            75% {
                transform: translateY(-3px) rotate(-2deg);
            }
        }
        
        /* 호버 시 로켓 발사 준비 */
        .rocket-launch-btn:hover .rocket-icon {
            animation: rocketPrepare 0.6s ease-in-out;
            transform: translateY(-5px) rotate(-10deg) scale(1.1);
        }
        
        @keyframes rocketPrepare {
            0% {
                transform: translateY(0px) rotate(0deg) scale(1);
            }
            50% {
                transform: translateY(-2px) rotate(-5deg) scale(1.05);
            }
            100% {
                transform: translateY(-5px) rotate(-10deg) scale(1.1);
            }
        }
        
        /* 호버 시 추진 불꽃 효과 */
        .rocket-launch-btn:hover::before {
            width: 60px;
            animation: thrusterFlame 0.3s ease-in-out infinite alternate;
        }
        
        .rocket-launch-btn:hover::after {
            opacity: 1;
            left: -15px;
            animation: smokeTrail 1s ease-in-out infinite;
        }
        
        @keyframes thrusterFlame {
            0% {
                height: 2px;
                box-shadow: 0 0 5px #fbbf24;
            }
            100% {
                height: 4px;
                box-shadow: 0 0 10px #f59e0b, 0 0 20px #d97706;
            }
        }
        
        @keyframes smokeTrail {
            0% {
                opacity: 0.8;
                transform: translateY(-50%) scale(1);
            }
            100% {
                opacity: 0.4;
                transform: translateY(-50%) scale(1.2);
            }
        }
        
        /* 클릭 시 로켓 발사 애니메이션 */
        .rocket-launch-btn:active .rocket-icon {
            animation: rocketLaunch 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            transform: translateY(-20px) rotate(-15deg) scale(1.2);
        }
        
        @keyframes rocketLaunch {
            0% {
                transform: translateY(-5px) rotate(-10deg) scale(1.1);
            }
            30% {
                transform: translateY(-15px) rotate(-12deg) scale(1.15);
            }
            60% {
                transform: translateY(-25px) rotate(-15deg) scale(1.25);
            }
            100% {
                transform: translateY(-20px) rotate(-15deg) scale(1.2);
            }
        }
        
        /* 클릭 시 강력한 추진력 효과 */
        .rocket-launch-btn:active::before {
            width: 80px;
            height: 6px;
            box-shadow: 
                0 0 15px #fbbf24, 
                0 0 30px #f59e0b, 
                0 0 45px #d97706,
                0 2px 0 #ef4444,
                0 4px 0 #dc2626;
            animation: superThruster 0.2s ease-in-out infinite;
        }
        
        .rocket-launch-btn:active::after {
            content: '💨💨💨';
            left: -40px;
            font-size: 1rem;
            animation: intenseSmokeTrail 0.4s ease-in-out infinite;
        }
        
        @keyframes superThruster {
            0% {
                transform: translateX(-50%) scaleX(1);
            }
            100% {
                transform: translateX(-50%) scaleX(1.1);
            }
        }
        
        @keyframes intenseSmokeTrail {
            0% {
                opacity: 1;
                transform: translateY(-50%) translateX(0) scale(1);
            }
            100% {
                opacity: 0.6;
                transform: translateY(-50%) translateX(-10px) scale(1.3);
            }
        }
        
        /* 터치 기기를 위한 추가 효과 */
        @media (hover: hover) {
            .rocket-launch-btn:hover {
                transform: translateY(-2px);
                box-shadow: 
                    0 8px 25px rgba(59, 130, 246, 0.3),
                    0 4px 15px rgba(59, 130, 246, 0.2),
                    0 0 0 1px rgba(255, 255, 255, 0.1);
            }
        }
        
        /* 모바일에서의 터치 효과 */
        @media (hover: none) {
            .rocket-launch-btn:active {
                transform: translateY(-1px) scale(0.98);
            }
        }
        
        /* 반응형 */
        @media (max-width: 768px) {
            .user-quick-stats {
                gap: 20px;
            }
            
            .quick-stat {
                padding: 12px 16px;
            }
            
            .hero-features {
                gap: 20px;
            }
            
            .message-content {
                flex-direction: column;
                text-align: center;
            }
            
            .close-message {
                position: static;
                margin-top: 10px;
            }
        }
    </style> 