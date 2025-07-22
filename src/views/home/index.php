<?php
/**
 * 탑마케팅 메인 페이지 - 모던 리디자인
 */
$page_title = '홈';
$page_description = '글로벌 네트워크 마케팅 리더들의 커뮤니티 - 성공을 함께 만들어가세요';
$current_page = 'home';

require_once SRC_PATH . '/views/templates/header.php';
?>

<!-- 1. 히어로 섹션 - 트렌디한 그라디언트 배경 -->
<section class="hero-section modern-hero">
    <div class="hero-background">
        <div class="gradient-overlay"></div>
        <div class="animated-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
    </div>
    <div class="container">
        <div class="hero-content">
            <div class="hero-badge">
                <span class="badge-icon">🚀</span>
                <span class="badge-text">네트워크 마케팅의 새로운 패러다임</span>
            </div>
            <h1 class="hero-title">
                <span class="gradient-text">글로벌 리더들과 함께</span><br>
                <span class="typing-effect">성공을 만들어가세요</span>
            </h1>
            <p class="hero-description">
                전 세계 네트워크 마케팅 전문가들이 모인 커뮤니티에서<br>
                지식을 공유하고, 인사이트를 얻으며, 함께 성장하세요
            </p>
            <div class="hero-actions">
                <a href="<?= isset($_SESSION['user_id']) ? '/community' : '/auth/signup' ?>" class="btn btn-primary-gradient rocket-launch-btn">
                    <span>무료로 시작하기</span>
                    <i class="fas fa-rocket rocket-icon"></i>
                </a>
                <a href="#features" class="btn btn-ghost">
                    <i class="fas fa-play"></i>
                    <span>둘러보기</span>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- 2. 핵심 기능 섹션 -->
<section id="features" class="features-section">
    <div class="container">
        <div class="section-header">
            <span class="section-badge">핵심 기능</span>
            <h2 class="section-title">탑마케팅이 제공하는 가치</h2>
            <p class="section-subtitle">성공적인 네트워크 마케팅을 위한 모든 도구가 여기에</p>
        </div>
        
        <div class="features-grid">
            <div class="feature-card featured">
                <div class="feature-icon">
                    <div class="icon-bg">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <h3>커뮤니티 네트워킹</h3>
                <p>전 세계 네트워크 마케팅 전문가들과 연결되어 경험과 노하우를 공유하세요</p>
                <a href="/community" class="feature-link">
                    <span>시작하기</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <div class="icon-bg green">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                </div>
                <h3>전문 강의</h3>
                <p>업계 전문가들의 실전 강의를 통해 실무 역량을 키워보세요</p>
                <a href="/lectures" class="feature-link">
                    <span>강의듣기</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <div class="icon-bg purple">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
                <h3>행사 참여</h3>
                <p>다양한 네트워킹 행사와 컨퍼런스에 참여하여 새로운 기회를 만나보세요</p>
                <a href="/events" class="feature-link">
                    <span>둘러보기</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>





<!-- 6. CTA 섹션 -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <div class="cta-text">
                <h2>성공의 여정을 함께 시작하세요</h2>
                <p>전 세계 네트워크 마케팅 리더들과 연결되어 새로운 기회를 발견하고 성공을 만들어가세요</p>
                <ul class="cta-benefits">
                    <li><i class="fas fa-check"></i> 무료 회원가입 및 기본 기능 이용</li>
                    <li><i class="fas fa-check"></i> 전문가 네트워크 액세스</li>
                    <li><i class="fas fa-check"></i> 독점 행사 및 강의 참여</li>
                </ul>
            </div>
            <div class="cta-actions">
                <a href="<?= isset($_SESSION['user_id']) ? '/community' : '/auth/signup' ?>" class="btn btn-primary-gradient btn-large rocket-launch-btn">
                    <span>지금 시작하기</span>
                    <i class="fas fa-rocket rocket-icon"></i>
                </a>
                <p class="cta-note">가입은 무료이며, 언제든지 탈퇴 가능합니다</p>
            </div>
        </div>
    </div>
</section>

<!-- 🚀 로켓 애니메이션 CSS -->
<style>
/* 로켓 애니메이션 효과 */
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

</style>

<?php require_once SRC_PATH . '/views/templates/footer.php'; ?> 