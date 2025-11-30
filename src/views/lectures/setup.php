<?php
/**
 * 강의 시스템 설정 페이지
 */
?>

<style>
.setup-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 40px 20px;
    text-align: center;
    min-height: calc(100vh - 200px);
}

.setup-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 40px;
    border-radius: 12px;
    margin-bottom: 30px;
}

.setup-header h1 {
    font-size: 2.5rem;
    margin-bottom: 15px;
    font-weight: 700;
}

.setup-header p {
    font-size: 1.1rem;
    opacity: 0.9;
    margin: 0;
}

.setup-content {
    background: white;
    border-radius: 12px;
    padding: 40px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
}

.setup-steps {
    display: grid;
    gap: 20px;
    margin: 30px 0;
    text-align: left;
}

.setup-step {
    padding: 20px;
    background: #f8fafc;
    border-radius: 8px;
    border-left: 4px solid #667eea;
}

.step-title {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 8px;
}

.step-description {
    color: #718096;
    font-size: 0.9rem;
}

.setup-action {
    margin-top: 30px;
}

.btn-setup {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    color: white;
    padding: 15px 30px;
    border: none;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
}

.btn-setup:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(72, 187, 120, 0.4);
}

.warning-box {
    background: #fff5f5;
    border: 1px solid #fed7d7;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
    color: #c53030;
}

.warning-box h3 {
    color: #c53030;
    margin-bottom: 10px;
}
</style>

<div class="setup-container">
    <div class="setup-header">
        <h1>🛠️ 강의 시스템 설정</h1>
        <p>강의 일정 시스템을 초기화합니다</p>
    </div>
    
    <div class="setup-content">
        <h2>강의 시스템이 아직 설정되지 않았습니다</h2>
        <p>강의 일정 기능을 사용하려면 데이터베이스 테이블을 생성해야 합니다.</p>
        
        <div class="warning-box">
            <h3><i data-lucide="alert-triangle" width="20" height="20" style="display: inline; vertical-align: middle; margin-right: 8px;"></i>설정 필요</h3>
            <p>
                강의 시스템 데이터베이스 테이블이 존재하지 않습니다.<br>
                관리자에게 문의하거나 다음 단계를 따라 설정을 완료해주세요.
            </p>
        </div>
        
        <div class="setup-steps">
            <div class="setup-step">
                <div class="step-title">1단계: 데이터베이스 연결 확인</div>
                <div class="step-description">
                    데이터베이스 서버가 실행 중이고 연결 설정이 올바른지 확인합니다.
                </div>
            </div>
            
            <div class="setup-step">
                <div class="step-title">2단계: 테이블 생성</div>
                <div class="step-description">
                    강의 관련 테이블(lectures, lecture_registrations, lecture_categories)을 생성합니다.
                </div>
            </div>
            
            <div class="setup-step">
                <div class="step-title">3단계: 기본 데이터 추가</div>
                <div class="step-description">
                    강의 카테고리 등 기본 데이터를 추가합니다.
                </div>
            </div>
        </div>
        
        <div class="setup-action">
            <p><strong>관리자인 경우:</strong></p>
            <a href="/create_tables.php?token=create_lectures_2025" class="btn-setup" target="_blank">
                <i data-lucide="rocket" width="16" height="16"></i> 테이블 생성하기
            </a>
        </div>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
            <p><strong>문제가 지속되는 경우:</strong></p>
            <ul style="text-align: left; display: inline-block;">
                <li>데이터베이스 서버 상태 확인</li>
                <li>데이터베이스 연결 설정 확인</li>
                <li>PHP MySQL 확장 모듈 설치 확인</li>
                <li>시스템 관리자에게 문의</li>
            </ul>
        </div>
        
        <div style="margin-top: 20px;">
            <a href="/" style="color: #667eea; text-decoration: none;">
                ← 홈페이지로 돌아가기
            </a>
        </div>
    </div>
</div>