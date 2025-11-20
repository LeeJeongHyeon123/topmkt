<?php
/**
 * 탑마케팅 메인 페이지 - 모던 리디자인
 */
$page_title = '홈';
$page_description = '글로벌 네트워크 마케팅 리더들의 커뮤니티 - 성공을 함께 만들어가세요';
$current_page = 'home';

// Card 컴포넌트 로드 (v3.38.0)
require_once SRC_PATH . '/components/ui/Card.php';

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
                    <i data-lucide="rocket" width="20" height="20" class="rocket-icon"></i>
                </a>
                <a href="/community" class="btn btn-ghost">
                    <i data-lucide="play" width="20" height="20"></i>
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
            <?= Card::feature([
                'icon' => 'users',
                'iconBg' => 'blue',
                'title' => '커뮤니티 네트워킹',
                'description' => '전 세계 네트워크 마케팅 전문가들과 연결되어 경험과 노하우를 공유하세요',
                'link' => '/community',
                'linkText' => '시작하기'
            ]) ?>

            <?= Card::feature([
                'icon' => 'graduation-cap',
                'iconBg' => 'green',
                'title' => '전문 강의',
                'description' => '업계 전문가들의 실전 강의를 통해 실무 역량을 키워보세요',
                'link' => '/lectures',
                'linkText' => '강의듣기'
            ]) ?>

            <?= Card::feature([
                'icon' => 'calendar',
                'iconBg' => 'purple',
                'title' => '행사 참여',
                'description' => '다양한 네트워킹 행사와 컨퍼런스에 참여하여 새로운 기회를 만나보세요',
                'link' => '/events',
                'linkText' => '둘러보기'
            ]) ?>

            <?= Card::feature([
                'icon' => 'megaphone',
                'iconBg' => 'orange',
                'title' => '공지사항',
                'description' => '플랫폼의 최신 소식과 중요한 공지사항을 확인하고 소통하세요',
                'link' => '/notices',
                'linkText' => '확인하기'
            ]) ?>
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
                    <li><i data-lucide="check" width="20" height="20"></i> 무료 회원가입 및 기본 기능 이용</li>
                    <li><i data-lucide="check" width="20" height="20"></i> 전문가 네트워크 액세스</li>
                    <li><i data-lucide="check" width="20" height="20"></i> 독점 행사 및 강의 참여</li>
                </ul>
            </div>
            <div class="cta-actions">
                <a href="<?= isset($_SESSION['user_id']) ? '/community' : '/auth/signup' ?>" class="btn btn-primary-gradient btn-large rocket-launch-btn">
                    <span>지금 시작하기</span>
                    <i data-lucide="rocket" width="20" height="20" class="rocket-icon"></i>
                </a>
                <p class="cta-note">가입은 무료이며, 언제든지 탈퇴 가능합니다</p>
            </div>
        </div>
    </div>
</section>

<!-- 🚀 로켓 애니메이션 CSS -->
<style>
/* 기본 레이아웃 및 반응형 스타일 */
* {
    box-sizing: border-box;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* 히어로 섹션 */
.hero-section {
    min-height: 100vh;
    display: flex;
    align-items: center;
    position: relative;
    overflow: hidden;
}

.hero-content {
    text-align: center;
    z-index: 2;
    position: relative;
}

.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    padding: 8px 16px;
    border-radius: 50px;
    margin-bottom: 24px;
    font-size: 14px;
    color: white;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: 24px;
    color: white;
}

.gradient-text {
    background: linear-gradient(135deg, #60a5fa, #34d399);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hero-description {
    font-size: 1.25rem;
    line-height: 1.6;
    margin-bottom: 32px;
    color: rgba(255, 255, 255, 0.9);
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.hero-actions {
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
}

/* 버튼 스타일 */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 16px 32px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    font-size: 16px;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    min-height: 56px;
    box-sizing: border-box;
}

.btn-primary-gradient {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
}

.btn-ghost {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
}

/* 기능 섹션 */
.features-section {
    padding: 100px 0;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

.section-header {
    text-align: center;
    margin-bottom: 80px;
}

.section-badge {
    display: inline-block;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    padding: 8px 20px;
    border-radius: 50px;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 16px;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 16px;
}

.section-subtitle {
    font-size: 1.25rem;
    color: #64748b;
    max-width: 600px;
    margin: 0 auto;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 32px;
    max-width: 1200px;
    margin: 0 auto;
}

.feature-card {
    background: white;
    padding: 32px;
    border-radius: 16px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    border: 2px solid #3b82f6;
}

.feature-card:hover {
    transform: none !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
}


.feature-icon {
    margin-bottom: 24px;
}

.icon-bg {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    font-size: 24px;
}

.icon-bg.green {
    background: linear-gradient(135deg, #10b981, #059669);
}

.icon-bg.purple {
    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
}

.feature-card h3 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 16px;
}

.feature-card p {
    font-size: 16px;
    color: #64748b;
    line-height: 1.6;
    margin-bottom: 24px;
}

.feature-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #3b82f6;
    text-decoration: none;
    font-weight: 600;
    font-size: 16px;
    padding: 12px 0;
    min-height: 44px;
    transition: all 0.3s ease;
}

.feature-link:hover {
    color: #1d4ed8;
    transform: translateX(4px);
}

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

/* 모바일 반응형 최적화 - UltraThink */
@media (max-width: 768px) {
    .container {
        padding: 0 16px;
    }
    
    .hero-section {
        min-height: 90vh;
        padding: 60px 0 40px;
    }
    
    .hero-badge {
        font-size: 13px;
        padding: 6px 12px;
        margin-bottom: 20px;
    }
    
    .hero-title {
        font-size: 2.25rem;
        line-height: 1.1;
        margin-bottom: 20px;
    }
    
    .hero-description {
        font-size: 1.125rem;
        margin-bottom: 28px;
        padding: 0 10px;
    }
    
    .hero-description br {
        display: none;
    }
    
    .hero-actions {
        flex-direction: column;
        gap: 12px;
        align-items: center;
    }
    
    .btn {
        width: 100%;
        max-width: 280px;
        padding: 18px 24px;
        font-size: 16px;
        min-height: 56px;
        justify-content: center;
    }
    
    .features-section {
        padding: 60px 0;
    }
    
    .section-header {
        margin-bottom: 40px;
    }
    
    .section-badge {
        font-size: 13px;
        padding: 6px 16px;
    }
    
    .section-title {
        font-size: 2rem;
        margin-bottom: 12px;
    }
    
    .section-subtitle {
        font-size: 1.125rem;
        padding: 0 10px;
    }
    
    .features-grid {
        grid-template-columns: 1fr;
        gap: 24px;
        padding: 0 4px;
    }
    
    .feature-card {
        padding: 24px 20px;
        border-radius: 12px;
    }
    
    .icon-bg {
        width: 56px;
        height: 56px;
        font-size: 20px;
    }
    
    .feature-card h3 {
        font-size: 1.25rem;
        margin-bottom: 12px;
    }
    
    .feature-card p {
        font-size: 15px;
        margin-bottom: 20px;
        line-height: 1.5;
    }
    
    .feature-link {
        font-size: 15px;
        padding: 14px 0;
        min-height: 48px;
    }
}

/* 소형 모바일 최적화 */
@media (max-width: 480px) {
    .container {
        padding: 0 12px;
    }
    
    .hero-section {
        min-height: 85vh;
        padding: 40px 0 30px;
    }
    
    .hero-title {
        font-size: 1.875rem;
        margin-bottom: 16px;
    }
    
    .hero-description {
        font-size: 1rem;
        margin-bottom: 24px;
    }
    
    .btn {
        max-width: 100%;
        padding: 20px 24px;
        min-height: 60px;
        font-size: 16px;
    }
    
    .section-title {
        font-size: 1.75rem;
    }
    
    .section-subtitle {
        font-size: 1rem;
    }
    
    .features-grid {
        gap: 20px;
    }
    
    .feature-card {
        padding: 20px 16px;
    }
    
    .icon-bg {
        width: 48px;
        height: 48px;
        font-size: 18px;
    }
    
    .feature-card h3 {
        font-size: 1.125rem;
    }
    
    .feature-card p {
        font-size: 14px;
        line-height: 1.4;
    }
    
    .feature-link {
        font-size: 14px;
        min-height: 52px;
        padding: 16px 0;
    }
}

</style>

<?php require_once SRC_PATH . '/views/templates/footer.php'; ?> 