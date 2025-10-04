<?php
/**
 * 기업 인증 신청 현황 페이지
 */

// SecurityHelper 클래스 로드
require_once SRC_PATH . '/helpers/SecurityHelper.php';

$status = $applicationStatus['status'];
$profile = $applicationStatus['profile'];
?>

<style>
/* 기업 인증 현황 페이지 스타일 */
.corp-status-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 40px 20px;
}

.corp-status-header {
    text-align: center;
    margin-bottom: 40px;
    padding: 40px 30px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 16px;
    margin-top: 60px;
}

.corp-status-header h1 {
    font-size: 2.5rem;
    margin-bottom: 15px;
    font-weight: 700;
}

.corp-status-header p {
    font-size: 1.1rem;
    opacity: 0.9;
    line-height: 1.6;
}

/* 상태 카드 */
.status-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    margin-bottom: 30px;
    border: 1px solid #e2e8f0;
}

.status-header {
    padding: 30px;
    text-align: center;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.status-badge {
    display: inline-block;
    padding: 12px 24px;
    border-radius: 25px;
    font-weight: 600;
    font-size: 1.1rem;
    margin-bottom: 15px;
}

.status-pending {
    background: #fef3cd;
    color: #856404;
    border: 2px solid #fceecf;
}

.status-approved {
    background: #d1ecf1;
    color: #0c5460;
    border: 2px solid #bee5eb;
}

.status-rejected {
    background: #f8d7da;
    color: #721c24;
    border: 2px solid #f5c6cb;
}

.status-title {
    font-size: 1.5rem;
    margin-bottom: 10px;
    font-weight: 600;
    color: #2d3748;
}

.status-description {
    color: #4a5568;
    line-height: 1.6;
}

/* 기업 정보 카드 */
.company-info-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    margin-bottom: 30px;
    border: 1px solid #e2e8f0;
}

.card-header {
    background: #f8fafc;
    padding: 25px 30px;
    border-bottom: 1px solid #e2e8f0;
}

.card-header h3 {
    font-size: 1.3rem;
    color: #2d3748;
    margin: 0;
    font-weight: 600;
}

.card-body {
    padding: 30px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
}

.info-item {
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 15px;
}

.info-item:last-child {
    border-bottom: none;
}

.info-label {
    font-size: 0.9rem;
    color: #718096;
    margin-bottom: 5px;
    font-weight: 500;
}

.info-value {
    font-size: 1rem;
    color: #2d3748;
    font-weight: 500;
    word-break: break-word;
}

.info-value.overseas {
    color: #3182ce;
    font-weight: 600;
}

/* 액션 버튼 */
.action-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 30px;
}

.btn {
    padding: 12px 30px;
    border: none;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(72, 187, 120, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(72, 187, 120, 0.4);
    text-decoration: none;
    color: white;
}

.btn-secondary {
    background: #e2e8f0;
    color: #4a5568;
}

.btn-secondary:hover {
    background: #cbd5e0;
    text-decoration: none;
    color: #4a5568;
}

.btn-warning {
    background: #fed7d7;
    color: #c53030;
    border: 1px solid #feb2b2;
}

.btn-warning:hover {
    background: #fbb6ce;
    text-decoration: none;
    color: #c53030;
}

/* 관리자 노트 */
.admin-notes {
    background: #fff5f5;
    border: 1px solid #fed7d7;
    border-radius: 12px;
    padding: 20px;
    margin-top: 20px;
}

.admin-notes h4 {
    color: #c53030;
    margin-bottom: 10px;
    font-size: 1.1rem;
    font-weight: 600;
}

.admin-notes p {
    color: #744210;
    line-height: 1.6;
    margin: 0;
}

/* 이력 섹션 */
.history-section {
    margin-top: 40px;
}

.history-timeline {
    position: relative;
    padding-left: 30px;
}

.history-timeline::before {
    content: '';
    position: absolute;
    left: 12px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e2e8f0;
}

.history-item {
    position: relative;
    margin-bottom: 25px;
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
}

.history-item::before {
    content: '';
    position: absolute;
    left: -25px;
    top: 25px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #48bb78;
    border: 3px solid white;
    box-shadow: 0 0 0 2px #48bb78;
}

.history-item.rejected::before {
    background: #e53e3e;
    box-shadow: 0 0 0 2px #e53e3e;
}

.history-item.pending::before {
    background: #ed8936;
    box-shadow: 0 0 0 2px #ed8936;
}

.history-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.history-action {
    font-weight: 600;
    color: #2d3748;
}

.history-date {
    font-size: 0.875rem;
    color: #718096;
}

.history-notes {
    color: #4a5568;
    line-height: 1.5;
}

/* 반응형 디자인 */
@media (max-width: 768px) {
    .corp-status-container {
        padding: 20px 15px;
    }
    
    .corp-status-header {
        padding: 30px 20px;
        margin-top: 20px;
    }
    
    .corp-status-header h1 {
        font-size: 2rem;
    }
    
    .card-body {
        padding: 20px;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .action-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .btn {
        width: 100%;
        max-width: 300px;
        justify-content: center;
    }
    
    .history-timeline {
        padding-left: 20px;
    }
    
    .history-item::before {
        left: -15px;
    }
}
</style>

<div class="corp-status-container">
    <!-- 헤더 컴포넌트 -->
    <?php include_once SRC_PATH . '/components/CorporateStatusHeader.php'; ?>

    <!-- 현재 상태 카드 -->
    <div class="status-card">
        <div class="status-header">
            <?php if ($status === 'pending'): ?>
                <div class="status-badge status-pending">⏳ 심사 중</div>
                <div class="status-title">기업 인증 심사가 진행 중입니다</div>
                <div class="status-description">
                    신청해주신 기업 인증이 현재 심사 중입니다.<br>
                    1~3일 내 심사 완료 후 결과를 알려드리겠습니다.
                </div>
            <?php elseif ($status === 'approved'): ?>
                <div class="status-badge status-approved">✅ 승인 완료</div>
                <div class="status-title">축하합니다! 기업회원으로 승인되었습니다</div>
                <div class="status-description">
                    이제 강의와 행사를 자유롭게 등록하고 관리하실 수 있습니다.<br>
                    기업회원 전용 혜택을 마음껏 누려보세요!
                </div>
            <?php elseif ($status === 'rejected'): ?>
                <div class="status-badge status-rejected">❌ 승인 거절</div>
                <div class="status-title">기업 인증이 거절되었습니다</div>
                <div class="status-description">
                    제출하신 서류나 정보에 문제가 있어 승인이 어렵습니다.<br>
                    아래 거절 사유를 확인하고 보완 후 재신청해주세요.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 기업 정보 카드 -->
    <?php if ($profile): ?>
    <div class="company-info-card">
        <div class="card-header">
            <h3>🏢 등록된 기업 정보</h3>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">회사명</div>
                    <div class="info-value"><?= htmlspecialchars($profile['company_name']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">사업자등록번호</div>
                    <div class="info-value"><?= htmlspecialchars(SecurityHelper::isEncrypted($profile['business_number'] ?? '') ? SecurityHelper::decrypt($profile['business_number']) : ($profile['business_number'] ?? '')) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">대표자명</div>
                    <div class="info-value"><?= htmlspecialchars($profile['representative_name']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">대표자 연락처</div>
                    <div class="info-value"><?= htmlspecialchars(SecurityHelper::isEncrypted($profile['representative_phone'] ?? '') ? SecurityHelper::decrypt($profile['representative_phone']) : ($profile['representative_phone'] ?? '')) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">회사 주소</div>
                    <div class="info-value"><?= nl2br(htmlspecialchars($profile['company_address'])) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">신청 일시</div>
                    <div class="info-value"><?= date('Y-m-d H:i', strtotime($profile['created_at'])) ?></div>
                </div>
                <?php if ($profile['is_overseas']): ?>
                <div class="info-item">
                    <div class="info-label">기업 유형</div>
                    <div class="info-value overseas">🌍 해외 기업</div>
                </div>
                <?php endif; ?>
                <?php if ($profile['processed_at']): ?>
                <div class="info-item">
                    <div class="info-label">처리 일시</div>
                    <div class="info-value"><?= date('Y-m-d H:i', strtotime($profile['processed_at'])) ?></div>
                </div>
                <?php endif; ?>
                <?php if ($profile['processed_by_name']): ?>
                <div class="info-item">
                    <div class="info-label">처리자</div>
                    <div class="info-value"><?= htmlspecialchars($profile['processed_by_name']) ?></div>
                </div>
                <?php endif; ?>
            </div>

            <!-- 관리자 노트 (거절된 경우) -->
            <?php if ($status === 'rejected' && !empty($profile['admin_notes'])): ?>
            <div class="admin-notes">
                <h4>📝 거절 사유</h4>
                <p><?= nl2br(htmlspecialchars($profile['admin_notes'])) ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- 액션 버튼 -->
    <div class="action-buttons">
        <?php if ($status === 'approved'): ?>
            <a href="/lectures/create" class="btn btn-primary">
                <span>📚</span> 강의 등록하기
            </a>
            <a href="/events/create" class="btn btn-primary">
                <span>🎯</span> 행사 등록하기
            </a>
            <a href="/corp/edit" class="btn btn-secondary">
                <span>✏️</span> 기업 정보 수정
            </a>
        <?php elseif ($status === 'rejected'): ?>
            <a href="/corp/apply" class="btn btn-warning">
                <span>🔄</span> 재신청하기
            </a>
            <a href="/corp/info" class="btn btn-secondary">
                <span>📋</span> 신청 안내 보기
            </a>
        <?php elseif ($status === 'pending'): ?>
            <a href="/corp/info" class="btn btn-secondary">
                <span>📋</span> 기업회원 안내
            </a>
        <?php endif; ?>
        
        <a href="/community" class="btn btn-secondary">
            <span>🏠</span> 커뮤니티로 이동
        </a>
    </div>

    <!-- 신청 이력 -->
    <?php if (!empty($history)): ?>
    <div class="history-section">
        <div class="company-info-card">
            <div class="card-header">
                <h3>📈 신청 이력</h3>
            </div>
            <div class="card-body">
                <div class="history-timeline">
                    <?php foreach ($history as $item): ?>
                    <div class="history-item <?= $item['action_type'] ?>">
                        <div class="history-header">
                            <div class="history-action">
                                <?php
                                $actionNames = [
                                    'apply' => '🆕 최초 신청',
                                    'reapply' => '🔄 재신청',
                                    'modify' => '✏️ 정보 수정',
                                    'approve' => '✅ 승인',
                                    'reject' => '❌ 거절'
                                ];
                                echo $actionNames[$item['action_type']] ?? $item['action_type'];
                                ?>
                            </div>
                            <div class="history-date">
                                <?= date('Y-m-d H:i', strtotime($item['created_at'])) ?>
                            </div>
                        </div>
                        <?php if (!empty($item['admin_notes'])): ?>
                        <div class="history-notes">
                            처리자: <?= htmlspecialchars($item['created_by_name']) ?><br>
                            메모: <?= nl2br(htmlspecialchars($item['admin_notes'])) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- 문의 안내 -->
    <div style="text-align: center; color: #718096; font-size: 0.9rem; margin-top: 40px; padding: 20px; background: #f8fafc; border-radius: 12px;">
        <p>
            <strong>📞 문의사항이 있으시면</strong><br>
            전화: <strong>1577-9794</strong> | 이메일: <strong>jh@wincard.kr</strong>
        </p>
    </div>
</div>

