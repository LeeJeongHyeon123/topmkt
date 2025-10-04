<?php
/**
 * 기업 신청 관리 대시보드 메인 페이지
 */
?>

<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ko.js"></script>

<style>
/* 대시보드 전용 스타일 */
.dashboard-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
    min-height: calc(100vh - 200px);
}

.dashboard-header {
    margin: 80px 0 40px 0;
    text-align: center;
}

.dashboard-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
}

.dashboard-subtitle {
    font-size: 1.1rem;
    color: #718096;
    margin-bottom: 32px;
}

/* 통계 카드 */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
    transition: all 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.stat-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
}

.stat-icon {
    font-size: 2rem;
    padding: 12px;
    border-radius: 12px;
    color: white;
}

.stat-icon.primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.stat-icon.success { background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); }
.stat-icon.warning { background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%); }
.stat-icon.danger { background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%); }

.stat-value {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 4px;
}

.stat-label {
    font-size: 0.9rem;
    color: #718096;
    font-weight: 500;
}

/* 강의 목록 섹션 */
.section {
    margin-bottom: 40px;
}

.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2d3748;
    display: flex;
    align-items: center;
    gap: 12px;
}

.lecture-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
}

.lecture-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
    transition: all 0.2s ease;
}

.lecture-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.lecture-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px 24px;
}

.lecture-title {
    font-size: 1.2rem;
    font-weight: 700;
    margin-bottom: 8px;
    line-height: 1.3;
    display: flex;
    align-items: center;
    gap: 12px;
}

.lecture-id-badge {
    background: rgba(255, 255, 255, 0.25);
    color: white;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.lecture-meta {
    display: flex;
    align-items: center;
    gap: 16px;
    font-size: 0.9rem;
    opacity: 0.9;
    color: white;
    flex-wrap: wrap;
}

/* 신청 상태 배지 */
.registration-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border: 1px solid;
    backdrop-filter: blur(5px);
}

.registration-status-badge.open {
    background: rgba(34, 197, 94, 0.9);
    color: white;
    border-color: rgba(34, 197, 94, 0.5);
    box-shadow: 0 2px 4px rgba(34, 197, 94, 0.2);
}

.registration-status-badge.closed {
    background: rgba(239, 68, 68, 0.9);
    color: white;
    border-color: rgba(239, 68, 68, 0.5);
    box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2);
}

.registration-status-badge.full {
    background: rgba(249, 115, 22, 0.9);
    color: white;
    border-color: rgba(249, 115, 22, 0.5);
    box-shadow: 0 2px 4px rgba(249, 115, 22, 0.2);
}

.registration-status-badge.completed {
    background: rgba(107, 114, 128, 0.9);
    color: white;
    border-color: rgba(107, 114, 128, 0.5);
    box-shadow: 0 2px 4px rgba(107, 114, 128, 0.2);
}

.lecture-body {
    padding: 24px;
}

.lecture-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 20px;
}

.lecture-stat {
    text-align: center;
    padding: 12px;
    background: #f8fafc;
    border-radius: 8px;
}

.lecture-stat-value {
    font-size: 1.3rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 4px;
}

.lecture-stat-label {
    font-size: 0.8rem;
    color: #718096;
}

.lecture-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover {
    background: #5a67d8;
    transform: translateY(-1px);
}

.btn-outline {
    background: transparent;
    border: 1px solid #e2e8f0;
    color: #4a5568;
}

.btn-outline:hover {
    background: #f8fafc;
    border-color: #cbd5e0;
}

/* 최근 신청 목록 */
.registrations-table {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table thead {
    background: #f8fafc;
}

.table th {
    padding: 16px 20px;
    text-align: left;
    font-weight: 600;
    color: #4a5568;
    font-size: 0.9rem;
    border-bottom: 1px solid #e2e8f0;
}

.table td {
    padding: 16px 20px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.9rem;
}

.table tbody tr:hover {
    background: #f8fafc;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-align: center;
    min-width: 80px;
    display: inline-block;
}

.status-pending { background: #fed7d7; color: #c53030; }
.status-approved { background: #c6f6d5; color: #2d3748; }
.status-rejected { background: #fed7d7; color: #c53030; }
.status-waiting { background: #bee3f8; color: #2b6cb0; }

/* 날짜 필터 스타일 */
.date-filter-container {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.date-filter {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.date-filter label {
    font-size: 0.9rem;
    font-weight: 500;
    color: #4a5568;
    white-space: nowrap;
}

.date-input {
    padding: 6px 10px;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    font-size: 0.9rem;
    background: white;
    color: #2d3748;
    transition: border-color 0.2s ease;
    cursor: pointer;
}

.date-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.1);
}

/* Flatpickr 커스터마이징 */
.flatpickr-calendar {
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12) !important;
    border-radius: 12px !important;
    border: 1px solid #e2e8f0 !important;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
}

.flatpickr-calendar .flatpickr-months {
    background: #667eea !important;
    border-radius: 12px 12px 0 0 !important;
    padding: 15px 20px !important;
}

.flatpickr-calendar .flatpickr-month {
    color: white !important;
}

.flatpickr-calendar .flatpickr-prev-month,
.flatpickr-calendar .flatpickr-next-month {
    fill: white !important;
}

.flatpickr-calendar .flatpickr-current-month {
    color: white !important;
}

.flatpickr-calendar .flatpickr-weekday {
    color: #718096 !important;
    font-weight: 600 !important;
    font-size: 0.8rem !important;
}

.flatpickr-calendar .flatpickr-day {
    border-radius: 8px !important;
    font-weight: 500 !important;
}

.flatpickr-calendar .flatpickr-day:hover {
    background: #667eea !important;
    color: white !important;
}

.flatpickr-calendar .flatpickr-day.selected {
    background: #667eea !important;
    border-color: #667eea !important;
}

.flatpickr-calendar .flatpickr-day.today {
    background: #48bb78 !important;
    color: white !important;
    border-color: #48bb78 !important;
}

/* 커스텀 버튼 스타일 */
.flatpickr-custom-buttons {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
    border-radius: 0 0 12px 12px;
}

.flatpickr-today-btn {
    background: #48bb78;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.flatpickr-today-btn:hover {
    background: #38a169;
    transform: translateY(-1px);
}

.flatpickr-apply-btn {
    background: #667eea;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.flatpickr-apply-btn:hover {
    background: #5a67d8;
    transform: translateY(-1px);
}

.btn-sm {
    padding: 6px 12px;
    font-size: 0.85rem;
}

/* 컨텐츠 타입 탭 스타일 */
.content-type-tabs {
    display: flex;
    gap: 12px;
    margin-top: 24px;
    justify-content: center;
}

.tab-button {
    padding: 12px 24px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    background: white;
    color: #4a5568;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.tab-button:hover {
    border-color: #667eea;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
}

.tab-button.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: #667eea;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

/* 행사 전용 스타일링 */
.event-card .lecture-header {
    background: linear-gradient(135deg, #4A90E2 0%, #2E86AB 100%);
}

.event-card .lecture-title {
    color: white;
}

.event-card .lecture-id-badge {
    background: rgba(255, 255, 255, 0.25);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.2);
}


/* 반응형 디자인 */
@media (max-width: 768px) {
    .dashboard-container {
        padding: 16px;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    
    .lecture-grid {
        grid-template-columns: 1fr;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }
    
    .lecture-stats {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    
    .lecture-actions {
        flex-direction: column;
    }
    
    .table-responsive {
        overflow-x: auto;
    }
    
    .date-filter-container {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
    }
    
    .date-filter {
        flex-wrap: wrap;
        gap: 8px;
        padding: 12px;
    }
    
    .date-filter label {
        min-width: 60px;
    }
    
    .date-input {
        flex: 1;
        min-width: 120px;
    }
    
    .content-type-tabs {
        flex-direction: column;
        gap: 8px;
    }
    
    .tab-button {
        padding: 10px 16px;
        font-size: 0.9rem;
    }
    
    .lecture-title {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .lecture-id-badge {
        font-size: 0.7rem;
        padding: 3px 8px;
    }
    
    .registration-status-badge {
        font-size: 0.6rem;
        padding: 3px 6px;
        gap: 2px;
    }
    
    .lecture-meta {
        gap: 8px;
        font-size: 0.8rem;
    }
    
}
</style>

<div class="dashboard-container">
    <!-- 대시보드 헤더 -->
    <div class="dashboard-header">
        <h1 class="dashboard-title">
            📊 신청 관리 대시보드
        </h1>
        <p class="dashboard-subtitle">
            <?= ($contentType ?? 'lecture') === 'event' ? '행사' : '강의' ?> 신청 현황을 한눈에 확인하고 효율적으로 관리하세요
        </p>
        
        <!-- 컨텐츠 타입 탭 -->
        <div class="content-type-tabs">
            <button class="tab-button <?= ($contentType ?? 'lecture') === 'lecture' ? 'active' : '' ?>" 
                    onclick="switchContentType('lecture')">
                🎓 강의 관리
            </button>
            <button class="tab-button <?= ($contentType ?? 'lecture') === 'event' ? 'active' : '' ?>" 
                    onclick="switchContentType('event')">
                🎉 행사 관리
            </button>
        </div>
    </div>
    
    <!-- 통계 카드 -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon primary"><?= ($contentType ?? 'lecture') === 'event' ? '🎉' : '📚' ?></div>
            </div>
            <div class="stat-value"><?= number_format($stats['total_lectures']) ?></div>
            <div class="stat-label">등록된 <?= ($contentType ?? 'lecture') === 'event' ? '행사' : '강의' ?></div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon success">✅</div>
            </div>
            <div class="stat-value"><?= number_format($stats['approved_applications']) ?></div>
            <div class="stat-label">승인된 신청</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon warning">⏳</div>
            </div>
            <div class="stat-value"><?= number_format($stats['pending_applications']) ?></div>
            <div class="stat-label">대기중인 신청</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon primary">📊</div>
            </div>
            <div class="stat-value"><?= number_format($stats['total_applications']) ?></div>
            <div class="stat-label">전체 신청</div>
        </div>
    </div>
    
    <!-- 내 강의 목록 -->
    <div class="section">
        <div class="section-header">
            <h2 class="section-title">
                <?= ($contentType ?? 'lecture') === 'event' ? '🎉 최근 행사 목록' : '🎯 최근 강의 목록' ?>
            </h2>
            <div class="date-filter-container">
                <div class="date-filter">
                    <label for="startDate">시작일:</label>
                    <input type="date" id="startDate" class="date-input">
                    <label for="endDate">종료일:</label>
                    <input type="date" id="endDate" class="date-input">
                    <button onclick="applyDateFilter()" class="btn btn-primary btn-sm">필터 적용</button>
                    <button onclick="resetDateFilter()" class="btn btn-outline btn-sm">초기화</button>
                </div>
            </div>
        </div>
        
        <?php if (empty($lectures)): ?>
            <div style="text-align: center; padding: 60px 20px; color: #718096;">
                <div style="font-size: 3rem; margin-bottom: 16px;">
                    <?= ($contentType ?? 'lecture') === 'event' ? '🎉' : '📚' ?>
                </div>
                <h3 style="margin-bottom: 8px;">
                    등록된 <?= ($contentType ?? 'lecture') === 'event' ? '행사' : '강의' ?>가 없습니다
                </h3>
                <p>새로운 <?= ($contentType ?? 'lecture') === 'event' ? '행사' : '강의' ?>를 등록하여 참가자들을 모집해보세요!</p>
                <a href="<?= ($contentType ?? 'lecture') === 'event' ? '/events/create' : '/lectures/create' ?>" 
                   class="btn btn-primary" style="margin-top: 20px;">
                    ➕ <?= ($contentType ?? 'lecture') === 'event' ? '행사' : '강의' ?> 등록하기
                </a>
            </div>
        <?php else: ?>
            <div class="lecture-grid">
                <?php foreach ($lectures as $lecture): ?>
                    <div class="lecture-card <?= $lecture['content_type'] === 'event' ? 'event-card' : '' ?>">
                        <div class="lecture-header">
                            <div class="lecture-title">
                                <?= htmlspecialchars($lecture['title']) ?>
                                <span class="lecture-id-badge">
                                    #<?= $lecture['id'] ?>
                                </span>
                            </div>
                            <div class="lecture-meta">
                                <span>📅 <?= date('Y-m-d H:i', strtotime($lecture['start_date'] . ' ' . $lecture['start_time'])) ?></span>
                                <span>👥 
                                    <?= number_format($lecture['approved_count']) ?>/<?= 
                                        $lecture['max_participants'] ? number_format($lecture['max_participants']) . '명' : '무제한' 
                                    ?>
                                </span>
                                <?php if ($lecture['content_type'] === 'event' && $lecture['location_type'] === 'offline'): ?>
                                    <span>📍 현장 행사</span>
                                <?php elseif ($lecture['content_type'] === 'event' && $lecture['location_type'] === 'online'): ?>
                                    <span>💻 온라인 행사</span>
                                <?php endif; ?>
                                
                                <!-- 신청 상태 배지 -->
                                <span class="registration-status-badge <?= $lecture['registration_status']['status'] ?>">
                                    <?= $lecture['registration_status']['icon'] ?> <?= $lecture['registration_status']['label'] ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="lecture-body">
                            <div class="lecture-stats">
                                <div class="lecture-stat">
                                    <div class="lecture-stat-value"><?= number_format($lecture['total_applications'] ?? 0) ?></div>
                                    <div class="lecture-stat-label">전체 신청</div>
                                </div>
                                <div class="lecture-stat">
                                    <div class="lecture-stat-value"><?= number_format($lecture['pending_count'] ?? 0) ?></div>
                                    <div class="lecture-stat-label">대기중</div>
                                </div>
                                <div class="lecture-stat">
                                    <div class="lecture-stat-value"><?= number_format($lecture['approved_count'] ?? 0) ?></div>
                                    <div class="lecture-stat-label">승인됨</div>
                                </div>
                            </div>
                            
                            <div class="lecture-actions">
                                <a href="/registrations/lectures/<?= $lecture['id'] ?>" class="btn btn-primary">
                                    👥 신청자 관리
                                </a>
                                <a href="<?= $lecture['content_type'] === 'event' ? '/events/detail?id=' : '/lectures/' ?><?= $lecture['id'] ?>" class="btn btn-outline">
                                    📋 <?= $lecture['content_type'] === 'event' ? '행사' : '강의' ?> 상세
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    
    <!-- 최근 신청 목록 -->
    <?php if (!empty($recentRegistrations)): ?>
        <div class="section">
            <div class="section-header">
                <h2 class="section-title">
                    ⏰ 최근 <?= ($contentType ?? 'lecture') === 'event' ? '행사' : '강의' ?> 신청 현황
                </h2>
            </div>
            
            <div class="registrations-table">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>신청자</th>
                                <th><?= ($contentType ?? 'lecture') === 'event' ? '행사명' : '강의명' ?></th>
                                <th>상태</th>
                                <th>신청일</th>
                                <th>관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentRegistrations as $registration): ?>
                                <tr>
                                    <td>
                                        <div>
                                            <strong><?= htmlspecialchars($registration['participant_name']) ?></strong>
                                            <div style="font-size: 0.8rem; color: #718096;">
                                                <?= htmlspecialchars($registration['participant_email']) ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="<?= ($contentType ?? 'lecture') === 'event' ? '/events/detail?id=' : '/lectures/' ?><?= $registration['lecture_id'] ?>" 
                                           style="color: #667eea; text-decoration: none;">
                                            <?= htmlspecialchars($registration['lecture_title']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?= $registration['status'] ?>">
                                            <?= [
                                                'pending' => '⏳ 대기중',
                                                'approved' => '✅ 승인됨',
                                                'rejected' => '❌ 거절됨',
                                                'waiting' => '⏰ 대기자'
                                            ][$registration['status']] ?? $registration['status'] ?>
                                        </span>
                                        <?php if ($registration['is_waiting_list']): ?>
                                            <small style="color: #718096;">(<?= $registration['waiting_order'] ?>번째)</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= date('Y-m-d H:i', strtotime($registration['created_at'])) ?>
                                    </td>
                                    <td>
                                        <a href="/registrations/lectures/<?= $registration['lecture_id'] ?>" 
                                           class="btn btn-outline" style="font-size: 0.8rem; padding: 6px 12px;">
                                            관리
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Flatpickr 인스턴스를 저장할 변수
let startDatePicker, endDatePicker;

// 날짜 필터 기능
function applyDateFilter() {
    const startDate = startDatePicker.selectedDates[0];
    const endDate = endDatePicker.selectedDates[0];

    if (!startDate || !endDate) {
        alert('시작일과 종료일을 모두 선택해주세요.');
        return;
    }

    if (startDate > endDate) {
        alert('시작일이 종료일보다 늦을 수 없습니다.');
        return;
    }

    // 현재 URL에 날짜 파라미터 추가
    const url = new URL(window.location.href);
    url.searchParams.set('start_date', startDatePicker.formatDate(startDate, 'Y-m-d'));
    url.searchParams.set('end_date', endDatePicker.formatDate(endDate, 'Y-m-d'));
    window.location.href = url.toString();
}

function resetDateFilter() {
    // URL에서 날짜 파라미터 제거
    const url = new URL(window.location.href);
    url.searchParams.delete('start_date');
    url.searchParams.delete('end_date');
    window.location.href = url.toString();
}

// 컨텐츠 타입 전환 기능
function switchContentType(type) {
    const url = new URL(window.location.href);
    url.searchParams.set('type', type);
    // 날짜 필터는 유지
    window.location.href = url.toString();
}

// 커스텀 버튼 생성 함수
function createCustomButtons(picker) {
    return {
        onReady: function() {
            // 기존 버튼들 숨기기/제거
            const clearButton = picker.calendarContainer.querySelector('.flatpickr-clear');
            if (clearButton) {
                clearButton.style.display = 'none';
            }

            // 커스텀 버튼 컨테이너 생성
            const buttonContainer = document.createElement('div');
            buttonContainer.className = 'flatpickr-custom-buttons';

            // 오늘 버튼 (왼쪽에 위치 - 기존 삭제 버튼 자리)
            const todayBtn = document.createElement('button');
            todayBtn.className = 'flatpickr-today-btn';
            todayBtn.textContent = '오늘';
            todayBtn.addEventListener('click', function(e) {
                e.preventDefault();
                picker.setDate(new Date());
            });

            // 적용 버튼 (오른쪽에 위치 - 기존 오늘 버튼 자리)
            const applyBtn = document.createElement('button');
            applyBtn.className = 'flatpickr-apply-btn';
            applyBtn.textContent = '적용';
            applyBtn.addEventListener('click', function(e) {
                e.preventDefault();
                picker.close();
            });

            buttonContainer.appendChild(todayBtn);
            buttonContainer.appendChild(applyBtn);
            picker.calendarContainer.appendChild(buttonContainer);
        }
    };
}

// 페이지 로드 시 Flatpickr 초기화
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const startDate = urlParams.get('start_date');
    const endDate = urlParams.get('end_date');
    const contentType = urlParams.get('type') || 'lecture';

    // 기본값: 최근 1개월
    const today = new Date();
    const oneMonthAgo = new Date(today.getFullYear(), today.getMonth() - 1, today.getDate());

    // 시작일 Flatpickr 초기화
    startDatePicker = flatpickr('#startDate', {
        locale: 'ko',
        dateFormat: 'Y-m-d',
        defaultDate: startDate || oneMonthAgo,
        onReady: function() {
            const buttons = createCustomButtons(this);
            buttons.onReady.call(this);
        },
        onChange: function(selectedDates, dateStr) {
            // 종료일의 최소값을 시작일로 설정
            if (endDatePicker) {
                endDatePicker.set('minDate', dateStr);
            }
        }
    });

    // 종료일 Flatpickr 초기화
    endDatePicker = flatpickr('#endDate', {
        locale: 'ko',
        dateFormat: 'Y-m-d',
        defaultDate: endDate || today,
        minDate: startDate || oneMonthAgo,
        onReady: function() {
            const buttons = createCustomButtons(this);
            buttons.onReady.call(this);
        },
        onChange: function(selectedDates, dateStr) {
            // 시작일의 최대값을 종료일로 설정
            if (startDatePicker) {
                startDatePicker.set('maxDate', dateStr);
            }
        }
    });
});
</script>