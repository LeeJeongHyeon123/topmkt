<?php
/**
 * 관리자 페이지 공통 사이드바 템플릿
 */
?>

<!-- 사이드바 -->
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">탑마케팅</div>
        <div class="sidebar-subtitle">관리자 페이지</div>
    </div>
    
    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-section-title">대시보드</div>
            <a href="/admin" class="nav-item <?= ($current_page ?? '') === 'dashboard' ? 'active' : '' ?>">
                <i>📊</i> 메인 대시보드
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">회원 관리</div>
            <a href="/admin/users" class="nav-item">
                <i>👥</i> 회원 목록
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">기업회원</div>
            <a href="/admin/corporate/pending" class="nav-item <?= ($current_page ?? '') === 'corporate-pending' ? 'active' : '' ?>">
                <i>⏱️</i> 인증 대기
                <?php 
                // 대기 중인 기업인증 수 표시 (향후 구현)
                if (isset($pending_corps_count) && $pending_corps_count > 0): ?>
                    <span class="nav-badge"><?= $pending_corps_count ?></span>
                <?php endif; ?>
            </a>
            <a href="/admin/corporate/list" class="nav-item <?= ($current_page ?? '') === 'corporate-list' ? 'active' : '' ?>">
                <i>📋</i> 기업회원 목록
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">콘텐츠 관리</div>
            <a href="/admin/posts" class="nav-item">
                <i>📝</i> 게시글 관리
            </a>
            <a href="/admin/comments" class="nav-item">
                <i>💬</i> 댓글 관리
            </a>
            <a href="/admin/reports" class="nav-item">
                <i>🚨</i> 신고 관리
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">시스템</div>
            <a href="/admin/settings" class="nav-item">
                <i>⚙️</i> 사이트 설정
            </a>
            <a href="/admin/logs" class="nav-item">
                <i>📋</i> 시스템 로그
            </a>
            <a href="/admin/backup" class="nav-item">
                <i>💾</i> 백업 관리
            </a>
        </div>
    </nav>
</aside>