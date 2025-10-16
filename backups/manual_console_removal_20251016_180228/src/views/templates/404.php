<?php
/**
 * 404 에러 페이지 템플릿
 */
$page_title = '페이지를 찾을 수 없습니다';
$page_description = '요청하신 페이지가 존재하지 않거나 이동되었습니다.';
$current_page = '404';

require_once SRC_PATH . '/views/templates/header.php';
?>

<style>
.error-container {
    min-height: 70vh;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 60px 20px;
}

.error-content {
    max-width: 600px;
}

.error-code {
    font-size: 8rem;
    font-weight: 700;
    color: #667eea;
    margin-bottom: 20px;
    line-height: 1;
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}

.error-title {
    font-size: 2rem;
    font-weight: 600;
    color: #1a202c;
    margin-bottom: 16px;
}

.error-description {
    font-size: 1.1rem;
    color: #64748b;
    margin-bottom: 40px;
    line-height: 1.6;
}

.error-actions {
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-home {
    background: var(--primary-gradient);
    color: white;
    padding: 14px 28px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 500;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-home:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
    text-decoration: none;
    color: white;
}

.btn-back {
    background: transparent;
    color: #64748b;
    padding: 14px 28px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-back:hover {
    background: #f8fafc;
    border-color: #cbd5e0;
    text-decoration: none;
    color: #374151;
}

.helpful-links {
    margin-top: 40px;
    padding-top: 30px;
    border-top: 1px solid #e2e8f0;
}

.helpful-links h3 {
    font-size: 1.1rem;
    color: #374151;
    margin-bottom: 20px;
}

.links-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    max-width: 400px;
    margin: 0 auto;
}

.helpful-link {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #667eea;
    text-decoration: none;
    padding: 8px 12px;
    border-radius: 8px;
    transition: background-color 0.2s ease;
}

.helpful-link:hover {
    background: rgba(102, 126, 234, 0.1);
    text-decoration: none;
    color: #5a67d8;
}

@media (max-width: 768px) {
    .error-code {
        font-size: 6rem;
    }
    
    .error-title {
        font-size: 1.5rem;
    }
    
    .error-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .btn-home,
    .btn-back {
        width: 100%;
        max-width: 280px;
        justify-content: center;
    }
}
</style>

<div class="error-container">
    <div class="error-content">
        <div class="error-code">404</div>
        <h1 class="error-title">페이지를 찾을 수 없습니다</h1>
        <p class="error-description">
            죄송합니다. 요청하신 페이지가 존재하지 않거나 이동되었습니다.<br>
            URL을 다시 확인하시거나 아래 링크를 이용해 주세요.
        </p>
        
        <div class="error-actions">
            <a href="/" class="btn-home">
                <i class="fas fa-home"></i>
                홈으로 돌아가기
            </a>
            <a href="javascript:history.back()" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                이전 페이지
            </a>
        </div>
        
        <div class="helpful-links">
            <h3>도움이 될 만한 링크</h3>
            <div class="links-grid">
                <a href="/community" class="helpful-link">
                    <i class="fas fa-comments"></i>
                    커뮤니티
                </a>
                <a href="/events" class="helpful-link">
                    <i class="fas fa-calendar-alt"></i>
                    행사 일정
                </a>
                <a href="/lectures" class="helpful-link">
                    <i class="fas fa-graduation-cap"></i>
                    강의 일정
                </a>
                <a href="/auth/login" class="helpful-link">
                    <i class="fas fa-sign-in-alt"></i>
                    로그인
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once SRC_PATH . '/views/templates/footer.php'; ?> 