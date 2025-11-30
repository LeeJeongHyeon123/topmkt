<?php
/**
 * 강의 시스템 오류 페이지
 */
?>

<style>
.error-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 40px 20px;
    text-align: center;
    min-height: calc(100vh - 200px);
}

.error-header {
    background: linear-gradient(135deg, #e53e3e 0%, #fc8181 100%);
    color: white;
    padding: 40px;
    border-radius: 12px;
    margin-bottom: 30px;
}

.error-header h1 {
    font-size: 2.5rem;
    margin-bottom: 15px;
    font-weight: 700;
}

.error-header p {
    font-size: 1.1rem;
    opacity: 0.9;
    margin: 0;
}

.error-content {
    background: white;
    border-radius: 12px;
    padding: 40px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
}

.error-message {
    background: #fff5f5;
    border: 1px solid #fed7d7;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
    color: #c53030;
}

.error-details {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
    color: #4a5568;
    font-family: monospace;
    font-size: 0.9rem;
    text-align: left;
    overflow-x: auto;
}

.error-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 30px;
    flex-wrap: wrap;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover {
    background: #5a67d8;
    transform: translateY(-1px);
}

.btn-secondary {
    background: #718096;
    color: white;
}

.btn-secondary:hover {
    background: #4a5568;
}

.troubleshooting {
    margin-top: 30px;
    padding: 20px;
    background: #f0fff4;
    border: 1px solid #9ae6b4;
    border-radius: 8px;
    text-align: left;
}

.troubleshooting h3 {
    color: #38a169;
    margin-bottom: 15px;
}

.troubleshooting ul {
    margin: 10px 0;
    padding-left: 20px;
}

.troubleshooting li {
    margin: 5px 0;
    color: #2d3748;
}

@media (max-width: 768px) {
    .error-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .btn {
        width: 100%;
        max-width: 280px;
        justify-content: center;
    }
}
</style>

<div class="error-container">
    <div class="error-header">
        <h1><i data-lucide="x-circle" width="20" height="20" style="display: inline; vertical-align: middle; margin-right: 8px;"></i>오류 발생</h1>
        <p>강의 시스템에서 문제가 발생했습니다</p>
    </div>
    
    <div class="error-content">
        <h2>시스템 오류</h2>
        
        <div class="error-message">
            <h3><i data-lucide="alert-octagon" width="20" height="20" style="display: inline; vertical-align: middle; margin-right: 8px;"></i>오류 메시지</h3>
            <p><?= htmlspecialchars($errorMessage ?? '알 수 없는 오류가 발생했습니다.') ?></p>
        </div>

        <?php if (!empty($errorDetails)): ?>
        <div class="error-details">
            <strong>상세 오류 정보:</strong><br>
            <?= htmlspecialchars($errorDetails) ?>
        </div>
        <?php endif; ?>
        
        <div class="error-actions">
            <a href="/lectures" class="btn btn-primary">
                <i data-lucide="refresh-cw" width="16" height="16"></i> 다시 시도
            </a>
            <a href="/" class="btn btn-secondary">
                <i data-lucide="home" width="16" height="16"></i> 홈으로
            </a>
        </div>
        
        <div class="troubleshooting">
            <h3><i data-lucide="wrench" width="20" height="20" style="display: inline; vertical-align: middle; margin-right: 8px;"></i>문제 해결 방법</h3>
            <p><strong>다음 단계를 시도해보세요:</strong></p>
            <ul>
                <li><strong>데이터베이스 연결 확인:</strong> 데이터베이스 서버가 실행 중인지 확인하세요</li>
                <li><strong>테이블 존재 확인:</strong> 강의 관련 테이블이 생성되어 있는지 확인하세요</li>
                <li><strong>권한 확인:</strong> 데이터베이스 사용자 권한이 올바른지 확인하세요</li>
                <li><strong>로그 확인:</strong> 서버 로그에서 더 자세한 오류 정보를 확인하세요</li>
                <li><strong>관리자 문의:</strong> 문제가 지속되면 시스템 관리자에게 문의하세요</li>
            </ul>
        </div>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
            <p style="color: #718096; font-size: 0.9rem;">
                <strong>개발자 정보:</strong><br>
                이 오류는 개발 환경에서 표시되는 상세 정보입니다.<br>
                운영 환경에서는 일반적인 오류 메시지만 표시됩니다.
            </p>
        </div>
    </div>
</div>