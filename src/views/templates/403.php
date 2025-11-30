<?php
/**
 * 403 권한 없음 에러 페이지 템플릿
 */
$page_title = $title ?? '접근 권한이 없습니다';
$page_description = $message ?? '이 페이지에 접근할 권한이 없습니다.';
$current_page = '403';

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
    color: #e53e3e;
    margin-bottom: 20px;
    line-height: 1;
    background: linear-gradient(135deg, #e53e3e 0%, #fc8181 100%);
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

.permission-info {
    margin-top: 40px;
    padding: 20px;
    background: #fff5f5;
    border: 1px solid #fed7d7;
    border-radius: 12px;
    color: #c53030;
}

.permission-info h3 {
    font-size: 1.1rem;
    margin-bottom: 10px;
    color: #c53030;
}

.permission-info p {
    margin: 0;
    font-size: 0.95rem;
}

.upgrade-info {
    margin-top: 20px;
    padding: 20px;
    background: #f0fff4;
    border: 1px solid #9ae6b4;
    border-radius: 12px;
    color: #2d3748;
}

.upgrade-info h3 {
    font-size: 1.1rem;
    margin-bottom: 10px;
    color: #38a169;
}

.upgrade-info ul {
    text-align: left;
    margin: 15px 0;
}

.upgrade-info li {
    margin: 8px 0;
    color: #4a5568;
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
        <div class="error-code">403</div>
        <h1 class="error-title"><?= htmlspecialchars($page_title) ?></h1>
        <p class="error-description">
            <?= htmlspecialchars($page_description) ?>
        </p>
        
        <div class="error-actions">
            <a href="/" class="btn-home">
                <i data-lucide="home" width="20" height="20"></i>
                홈으로 돌아가기
            </a>
            <a href="javascript:history.back()" class="btn-back">
                <i data-lucide="arrow-left" width="20" height="20"></i>
                이전 페이지
            </a>
        </div>
        
        <div class="permission-info">
            <h3><i data-lucide="lock" width="20" height="20" style="display: inline; vertical-align: middle; margin-right: 8px;"></i>접근 제한 안내</h3>
            <p>
                이 기능은 특정 권한을 가진 회원만 사용할 수 있습니다.<br>
                현재 회원 등급으로는 접근이 제한됩니다.
            </p>
        </div>
        
        <?php if (strpos($page_description, '기업회원') !== false): ?>
        <div class="upgrade-info">
            <h3><i data-lucide="building" width="20" height="20" style="display: inline; vertical-align: middle; margin-right: 8px;"></i>기업회원 혜택</h3>
            <p><strong>기업회원으로 업그레이드하시면 다음 기능을 이용하실 수 있습니다:</strong></p>
            <ul>
                <li><i data-lucide="book-open" width="16" height="16" style="display: inline; vertical-align: middle; margin-right: 6px;"></i>강의 및 세미나 등록</li>
                <li><i data-lucide="target" width="16" height="16" style="display: inline; vertical-align: middle; margin-right: 6px;"></i>회사 소개 및 비전 게시판 작성</li>
                <li><i data-lucide="handshake" width="16" height="16" style="display: inline; vertical-align: middle; margin-right: 6px;"></i>파트너 매칭 서비스 이용</li>
                <li><i data-lucide="star" width="16" height="16" style="display: inline; vertical-align: middle; margin-right: 6px;"></i>기업 인증 배지 제공</li>
                <li><i data-lucide="mail" width="16" height="16" style="display: inline; vertical-align: middle; margin-right: 6px;"></i>참가자 대상 이메일/SMS 발송</li>
            </ul>
            <p style="margin-top: 15px; font-size: 0.9rem; color: #718096;">
                <i data-lucide="lightbulb" width="16" height="16" style="display: inline; vertical-align: middle; margin-right: 4px;"></i>기업회원 인증은 사업자등록증 제출 후 관리자 승인을 통해 진행됩니다.
            </p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once SRC_PATH . '/views/templates/footer.php'; ?>