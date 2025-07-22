<?php
/**
 * 기업회원 안내 페이지
 */
?>

<style>
/* 기업회원 안내 페이지 스타일 */
.corp-info-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
}

.corp-info-header {
    text-align: center;
    margin-bottom: 60px;
    padding: 60px 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 20px;
    margin-top: 60px;
}

.corp-info-header h1 {
    font-size: 3rem;
    margin-bottom: 20px;
    font-weight: 700;
}

.corp-info-header p {
    font-size: 1.3rem;
    opacity: 0.9;
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.6;
}

/* 혜택 섹션 */
.benefits-section {
    margin-bottom: 60px;
}

.section-title {
    text-align: center;
    font-size: 2.5rem;
    margin-bottom: 40px;
    color: #2d3748;
    font-weight: 700;
}

.benefits-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 30px;
    margin-bottom: 40px;
}

.benefit-card {
    background: white;
    padding: 40px 30px;
    border-radius: 16px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    text-align: center;
    transition: all 0.3s ease;
    border: 1px solid #e2e8f0;
}

.benefit-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
}

.benefit-icon {
    font-size: 3rem;
    margin-bottom: 20px;
    display: block;
}

.benefit-card h3 {
    font-size: 1.5rem;
    margin-bottom: 15px;
    color: #2d3748;
    font-weight: 600;
}

.benefit-card p {
    color: #4a5568;
    line-height: 1.6;
    font-size: 1rem;
}

/* 신청 자격 섹션 */
.requirements-section {
    background: #f8fafc;
    padding: 50px 40px;
    border-radius: 16px;
    margin-bottom: 60px;
}

.requirements-list {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
    margin-top: 30px;
}

.requirement-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    padding: 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.requirement-icon {
    font-size: 1.5rem;
    color: #48bb78;
    margin-top: 2px;
}

.requirement-content h4 {
    font-size: 1.1rem;
    margin-bottom: 5px;
    color: #2d3748;
    font-weight: 600;
}

.requirement-content p {
    color: #4a5568;
    font-size: 0.95rem;
    line-height: 1.5;
}

/* 신청 절차 섹션 */
.process-section {
    margin-bottom: 60px;
}

.process-steps {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    margin-top: 40px;
}

.process-step {
    text-align: center;
    position: relative;
}

.step-number {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 auto 20px;
    box-shadow: 0 4px 15px rgba(72, 187, 120, 0.3);
}

.step-title {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 10px;
    color: #2d3748;
}

.step-description {
    color: #4a5568;
    line-height: 1.5;
    font-size: 0.95rem;
}

/* 액션 버튼 섹션 */
.action-section {
    text-align: center;
    padding: 50px 40px;
    background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    border-radius: 16px;
    margin-bottom: 40px;
}

.action-buttons {
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 30px;
}

.btn-apply {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    color: white;
    padding: 15px 40px;
    border: none;
    border-radius: 12px;
    font-size: 1.1rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 4px 15px rgba(72, 187, 120, 0.3);
}

.btn-apply:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(72, 187, 120, 0.4);
    text-decoration: none;
    color: white;
}

.btn-cancel {
    background: #e2e8f0;
    color: #4a5568;
    padding: 15px 40px;
    border: none;
    border-radius: 12px;
    font-size: 1.1rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.btn-cancel:hover {
    background: #cbd5e0;
    text-decoration: none;
    color: #4a5568;
}

/* 상태별 메시지 */
.status-message {
    padding: 20px 30px;
    border-radius: 12px;
    margin-bottom: 30px;
    text-align: center;
    font-weight: 500;
}

.status-pending {
    background: #fef3cd;
    color: #856404;
    border: 1px solid #fceecf;
}

.status-rejected {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* 반응형 디자인 */
@media (max-width: 768px) {
    .corp-info-container {
        padding: 20px 15px;
    }
    
    .corp-info-header {
        padding: 40px 20px;
        margin-top: 20px;
    }
    
    .corp-info-header h1 {
        font-size: 2.2rem;
    }
    
    .corp-info-header p {
        font-size: 1.1rem;
    }
    
    .section-title {
        font-size: 2rem;
    }
    
    .benefits-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .benefit-card {
        padding: 30px 20px;
    }
    
    .requirements-section,
    .action-section {
        padding: 30px 20px;
    }
    
    .requirements-list {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .process-steps {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .action-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .btn-apply,
    .btn-cancel {
        width: 100%;
        max-width: 300px;
        justify-content: center;
    }
}
</style>

<div class="corp-info-container">
    <!-- 헤더 섹션 -->
    <div class="corp-info-header">
        <h1>🏢 기업회원 시스템</h1>
        <p>강의와 행사를 등록하고 더 많은 사람들과 지식을 나누세요.<br>
           기업회원만의 특별한 혜택을 경험해보세요.</p>
    </div>

    <!-- 현재 상태 메시지 -->
    <?php if ($applicationStatus && $applicationStatus['status'] === 'pending'): ?>
        <div class="status-message status-pending">
            <strong>신청 검토 중</strong><br>
            현재 기업 인증 신청이 검토 중입니다. 1~3일 내 심사 후 결과를 알려드립니다.
        </div>
    <?php elseif ($applicationStatus && $applicationStatus['status'] === 'rejected'): ?>
        <div class="status-message status-rejected">
            <strong>인증 거절</strong><br>
            기업 인증이 거절되었습니다. 거절 사유를 확인하고 재신청하실 수 있습니다.
        </div>
    <?php endif; ?>

    <!-- 기업회원 혜택 -->
    <div class="benefits-section">
        <h2 class="section-title">🌟 기업회원 혜택</h2>
        <div class="benefits-grid">
            <div class="benefit-card">
                <span class="benefit-icon">📚</span>
                <h3>강의 등록 및 관리</h3>
                <p>전문 강의를 등록하고 참가자를 관리할 수 있습니다. 지식을 나누며 브랜드 인지도를 높여보세요.</p>
            </div>
            <div class="benefit-card">
                <span class="benefit-icon">🎯</span>
                <h3>행사 개최 권한</h3>
                <p>세미나, 워크샵, 컨퍼런스 등 다양한 행사를 개최하고 홍보할 수 있습니다.</p>
            </div>
            <div class="benefit-card">
                <span class="benefit-icon">🤝</span>
                <h3>비즈니스 네트워킹</h3>
                <p>다른 기업회원들과 네트워킹하며 파트너십 기회를 발견할 수 있습니다.</p>
            </div>
            <div class="benefit-card">
                <span class="benefit-icon">✅</span>
                <h3>인증 배지 표시</h3>
                <p>프로필에 기업 인증 배지가 표시되어 신뢰도와 전문성을 높일 수 있습니다.</p>
            </div>
        </div>
    </div>

    <!-- 신청 자격 -->
    <div class="requirements-section">
        <h2 class="section-title">📋 신청 자격 및 필요 서류</h2>
        <div class="requirements-list">
            <div class="requirement-item">
                <span class="requirement-icon">🏢</span>
                <div class="requirement-content">
                    <h4>개인사업자 또는 법인</h4>
                    <p>사업자등록증을 보유한 개인사업자나 법인 모두 신청 가능합니다.</p>
                </div>
            </div>
            <div class="requirement-item">
                <span class="requirement-icon">🌍</span>
                <div class="requirement-content">
                    <h4>해외 기업도 가능</h4>
                    <p>한국 외 해외 기업도 유사한 사업자 등록 서류로 신청할 수 있습니다.</p>
                </div>
            </div>
            <div class="requirement-item">
                <span class="requirement-icon">📄</span>
                <div class="requirement-content">
                    <h4>필수 서류</h4>
                    <p>사업자등록증, 회사 정보, 대표자 정보 및 연락처가 필요합니다.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 신청 절차 -->
    <div class="process-section">
        <h2 class="section-title">🚀 신청 절차</h2>
        <div class="process-steps">
            <div class="process-step">
                <div class="step-number">1</div>
                <div class="step-title">신청서 작성</div>
                <div class="step-description">회사 정보와 대표자 정보를 입력하고 사업자등록증을 업로드합니다.</div>
            </div>
            <div class="process-step">
                <div class="step-number">2</div>
                <div class="step-title">서류 심사</div>
                <div class="step-description">탑마케팅 직원이 제출된 서류를 검토합니다. (1~3일 소요)</div>
            </div>
            <div class="process-step">
                <div class="step-number">3</div>
                <div class="step-title">승인 완료</div>
                <div class="step-description">승인 시 기업회원 권한이 부여되며 모든 기능을 이용할 수 있습니다.</div>
            </div>
        </div>
    </div>

    <!-- 액션 버튼 -->
    <div class="action-section">
        <h3>지금 바로 기업회원 혜택을 누려보세요!</h3>
        <p>기업 인증 신청은 <strong>무료</strong>이며, 승인 후 모든 기능을 <strong>무료</strong>로 이용할 수 있습니다.</p>
        
        <div class="action-buttons">
            <?php if (!$applicationStatus || $applicationStatus['status'] === 'none'): ?>
                <a href="/corp/apply" class="btn-apply">
                    <span>📝</span> 기업 인증 신청하기
                </a>
            <?php elseif ($applicationStatus['status'] === 'rejected'): ?>
                <a href="/corp/apply" class="btn-apply">
                    <span>🔄</span> 기업 인증 재신청하기
                </a>
                <a href="/corp/status" class="btn-cancel">
                    <span>📊</span> 거절 사유 확인하기
                </a>
            <?php else: ?>
                <a href="/corp/status" class="btn-apply">
                    <span>📊</span> 신청 현황 확인하기
                </a>
            <?php endif; ?>
            
            <?php if (isset($redirectUrl) && $redirectUrl): ?>
                <a href="<?= htmlspecialchars($redirectUrl) ?>" class="btn-cancel">
                    <span>↩️</span> 이전 페이지로
                </a>
            <?php else: ?>
                <a href="/community" class="btn-cancel">
                    <span>🏠</span> 커뮤니티로 이동
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- 추가 안내 -->
    <div style="text-align: center; color: #718096; font-size: 0.9rem; margin-top: 40px;">
        <p>
            📞 문의사항이 있으시면 <strong>070-4138-8899</strong>로 연락주세요.<br>
            📧 이메일: <strong>jh@wincard.kr</strong>
        </p>
    </div>
</div>