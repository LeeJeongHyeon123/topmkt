<?php
/**
 * 공지사항 상세보기 페이지
 */

// 로그인 상태 확인
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
require_once SRC_PATH . '/helpers/HtmlSanitizerHelper.php';
$isLoggedIn = AuthMiddleware::isLoggedIn();
$currentUserId = AuthMiddleware::getCurrentUserId();

// 공지사항 정보가 없으면 404 처리
if (!isset($notice) || !$notice) {
    header('HTTP/1.1 404 Not Found');
    include SRC_PATH . '/views/templates/404.php';
    return;
}

// SEO 메타 데이터 설정
$pageTitle = htmlspecialchars($notice['title']) . ' - 공지사항';
$pageDescription = htmlspecialchars(mb_substr(strip_tags($notice['content']), 0, 150));
$pageUrl = 'https://www.topmktx.com/notices/' . $notice['id'];
$pageImage = !empty($notice['images']) ? $notice['images'][0]['file_path'] : '/assets/images/topmkt-og-notice.jpg';
?>

<!-- SEO 및 Open Graph 메타 태그 -->
<meta name="description" content="<?= $pageDescription ?>">
<meta name="keywords" content="탑마케팅, 공지사항, <?= htmlspecialchars($notice['company_name']) ?>, 마케팅">
<meta name="author" content="<?= htmlspecialchars($notice['company_name']) ?>">

<!-- Open Graph -->
<meta property="og:type" content="article">
<meta property="og:title" content="<?= $pageTitle ?>">
<meta property="og:description" content="<?= $pageDescription ?>">
<meta property="og:url" content="<?= $pageUrl ?>">
<meta property="og:image" content="<?= $pageImage ?>">
<meta property="og:site_name" content="탑마케팅">
<meta property="og:locale" content="ko_KR">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= $pageTitle ?>">
<meta name="twitter:description" content="<?= $pageDescription ?>">
<meta name="twitter:image" content="<?= $pageImage ?>">

<!-- Article 관련 메타태그 -->
<meta property="article:published_time" content="<?= date('c', strtotime($notice['created_at'])) ?>">
<meta property="article:modified_time" content="<?= date('c', strtotime($notice['updated_at'])) ?>">
<meta property="article:author" content="<?= htmlspecialchars($notice['company_name']) ?>">
<meta property="article:section" content="공지사항">

<style>
/* 공지사항 상세보기 페이지 스타일 */
.detail-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    min-height: calc(100vh - 200px);
}

.detail-navigation {
    margin-bottom: 20px;
}

.breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #718096;
}

.breadcrumb a {
    color: #2563eb;
    text-decoration: none;
}

.breadcrumb a:hover {
    text-decoration: underline;
}

.notice-container {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
    margin-bottom: 30px;
}

.notice-header {
    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
    color: white;
    padding: 30px;
    position: relative;
}

.notice-header.featured {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.notice-featured-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: #dc2626;
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 4px;
}

.notice-title {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 15px;
    line-height: 1.4;
    word-break: break-word;
    padding-right: 120px;
}

.notice-meta {
    display: flex;
    align-items: center;
    gap: 20px;
    font-size: 0.9rem;
    opacity: 1 !important;
    flex-wrap: wrap;
    color: #ffffff !important;
}

.notice-meta .meta-item {
    color: #ffffff !important;
}

.notice-meta .meta-item strong {
    color: #ffffff !important;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
}

.company-meta-with-avatar {
    display: flex;
    align-items: center;
    gap: 10px;
}

.company-avatar-small {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 1rem;
    flex-shrink: 0;
    overflow: hidden;
    transition: transform 0.2s ease;
    backdrop-filter: blur(10px);
}

.company-avatar-small:hover {
    transform: scale(1.1);
}

.company-avatar-small img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.notice-content {
    padding: 30px;
}

.content-body {
    font-size: 1rem;
    line-height: 1.8;
    color: #2d3748;
    word-break: break-word;
}

.content-body img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 15px 0;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.notice-footer {
    padding: 20px 30px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
}

.notice-stats {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 15px;
    font-size: 0.9rem;
    color: #718096;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 6px;
}

.notice-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn {
    padding: 12px 20px;
    border: 1px solid transparent;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
    background: #ffffff;
    color: #374151;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    position: relative;
    overflow: hidden;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn:active {
    transform: translateY(0);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.btn-primary {
    border-color: #e5e7eb;
}

.btn-primary:hover {
    background: #f0f9ff;
    border-color: #2563eb;
    color: #2563eb;
}

.btn-secondary {
    border-color: #e5e7eb;
}

.btn-secondary:hover {
    background: #f8fafc;
    border-color: #d1d5db;
    color: #4b5563;
}

.btn-warning {
    border-color: #e5e7eb;
}

.btn-warning:hover {
    background: #fffbeb;
    border-color: #f59e0b;
    color: #d97706;
}

.btn-danger {
    border-color: #e5e7eb;
}

.btn-danger:hover {
    background: #fef2f2;
    border-color: #fecaca;
    color: #dc2626;
}

.btn-success {
    border-color: #e5e7eb;
}

.btn-success:hover {
    background: #f0fdf4;
    border-color: #16a34a;
    color: #16a34a;
}

/* 댓글 섹션 */
.comments-section {
    background: white;
    border-radius: 12px;
    overflow: visible;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
    margin-top: 30px;
}

.comments-section h3 {
    padding: 20px 30px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    font-weight: 600;
    color: #2d3748;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.comment-form {
    padding: 20px 30px;
    border-bottom: 1px solid #e2e8f0;
}

.comment-form textarea {
    width: 100%;
    min-height: 100px;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-family: inherit;
    font-size: 14px;
    resize: vertical;
    transition: border-color 0.3s ease;
    box-sizing: border-box;
}

.comment-form textarea:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.comment-form-actions {
    display: flex;
    gap: 10px;
    margin-top: 10px;
    justify-content: flex-end;
}

.comment-form-actions .btn {
    min-width: 100px;
}

.comments-list {
    padding: 0;
}

.comment-item {
    padding: 20px 30px;
    border-bottom: 1px solid #f1f5f9;
    position: relative;
}

.comment-item:last-child {
    border-bottom: none;
}

.comment-item.reply {
    margin-left: 30px;
    border-left: 3px solid #e2e8f0;
    background: #f8fafc;
}

.comment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.comment-author {
    display: flex;
    align-items: center;
    gap: 10px;
}

.comment-author-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 0.9rem;
    flex-shrink: 0;
    overflow: hidden;
}

.comment-author-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.comment-author-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.comment-author-name {
    font-weight: 600;
    color: #2d3748;
    font-size: 0.9rem;
}

.comment-date {
    font-size: 0.8rem;
    color: #a0aec0;
}

.comment-actions {
    display: flex;
    gap: 8px;
}

.comment-action-btn {
    background: none;
    border: none;
    color: #718096;
    font-size: 0.8rem;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.comment-action-btn:hover {
    background: #f1f5f9;
    color: #4a5568;
}

.comment-content {
    color: #4a5568;
    line-height: 1.6;
    white-space: pre-wrap;
    word-break: break-word;
    margin-bottom: 10px;
}

.comment-edit-form {
    display: none;
}

.comment-edit-form textarea {
    width: 100%;
    min-height: 80px;
    padding: 10px 12px;
    border: 2px solid #e2e8f0;
    border-radius: 6px;
    font-family: inherit;
    font-size: 14px;
    resize: vertical;
    transition: border-color 0.3s ease;
    box-sizing: border-box;
}

.comment-edit-form textarea:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.comment-edit-actions {
    display: flex;
    gap: 8px;
    margin-top: 8px;
    justify-content: flex-end;
}

.comment-edit-actions .btn {
    font-size: 12px;
    padding: 6px 12px;
    min-width: 80px;
}

.reply-form {
    display: none;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #f1f5f9;
}

.reply-form textarea {
    width: 100%;
    min-height: 60px;
    padding: 8px 12px;
    border: 2px solid #e2e8f0;
    border-radius: 6px;
    font-family: inherit;
    font-size: 13px;
    resize: vertical;
    transition: border-color 0.3s ease;
    box-sizing: border-box;
}

.reply-form textarea:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.reply-form-actions {
    display: flex;
    gap: 8px;
    margin-top: 8px;
    justify-content: flex-end;
}

.reply-form-actions .btn {
    font-size: 12px;
    padding: 6px 12px;
    min-width: 80px;
}

/* 공유 모달 스타일 */
.share-modal {
    display: none;
    position: fixed;
    z-index: 10000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(3px);
}

.share-modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border-radius: 16px;
    padding: 24px;
    min-width: 320px;
    max-width: 95vw;
    max-height: 95vh;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    overflow: auto;
}

.share-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.share-modal-header h3 {
    margin: 0;
    color: #2d3748;
    font-size: 1.2rem;
    font-weight: 600;
}

.share-modal-close {
    background: none;
    border: none;
    font-size: 24px;
    color: #718096;
    cursor: pointer;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s ease;
}

.share-modal-close:hover {
    background: #f1f5f9;
    color: #2d3748;
}

.share-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 12px;
    margin-bottom: 20px;
}

.share-option {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 16px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    color: #4a5568;
}

.share-option:hover {
    border-color: #2563eb;
    background: #f0f9ff;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.15);
}

.share-option-icon {
    font-size: 24px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.share-option-text {
    font-size: 12px;
    font-weight: 600;
    text-align: center;
}

.share-option.facebook .share-option-icon {
    background: #1877f2;
}

.share-option.twitter .share-option-icon {
    background: #1da1f2;
}

.share-option.kakao .share-option-icon {
    background: #fee500;
    color: #3c1e1e;
}

.share-option.link .share-option-icon {
    background: #6b7280;
}

.share-url-section {
    border-top: 1px solid #e2e8f0;
    padding-top: 20px;
}

.share-url-section h4 {
    margin: 0 0 10px 0;
    color: #374151;
    font-size: 14px;
    font-weight: 600;
}

.share-url-input {
    display: flex;
    gap: 8px;
}

.share-url-input input {
    flex: 1;
    padding: 10px 12px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    background: #f9fafb;
}

.share-url-input input:focus {
    outline: none;
    border-color: #2563eb;
    background: white;
}

/* 모바일 반응형 */
@media (max-width: 768px) {
    .detail-container {
        padding: 15px;
    }
    
    .notice-header {
        padding: 20px;
    }
    
    .notice-title {
        font-size: 1.4rem;
        padding-right: 60px;
    }
    
    .notice-featured-badge {
        top: 10px;
        right: 10px;
        padding: 4px 8px;
        font-size: 0.7rem;
    }
    
    .notice-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .notice-content,
    .notice-footer {
        padding: 20px;
    }
    
    .notice-actions {
        flex-direction: column;
    }
    
    .btn {
        justify-content: center;
        width: 100%;
    }
    
    .comment-item {
        padding: 15px 20px;
    }
    
    .comment-item.reply {
        margin-left: 15px;
    }
    
    .comment-form,
    .comments-section h3 {
        padding: 15px 20px;
    }
    
    .share-modal-content {
        margin: 20px;
        min-width: auto;
        max-width: calc(100vw - 40px);
    }
    
    .share-options {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* 화이트 배경 일관성 유지 */
body {
    background-color: white !important;
}

.detail-container {
    background: white !important;
}

.notice-container,
.comments-section {
    background: white !important;
    border-color: #e2e8f0 !important;
}

.notice-content,
.comment-item {
    background: white !important;
    color: #2d3748 !important;
}

.comment-item.reply {
    background: #f8fafc !important;
}

.notice-footer {
    background: #f8fafc !important;
}

.comments-section h3 {
    background: #f8fafc !important;
}
</style>

<div class="detail-container">
    <!-- 내비게이션 -->
    <div class="detail-navigation">
        <div class="breadcrumb">
            <a href="/">홈</a>
            <span>›</span>
            <a href="/notices">공지사항</a>
            <span>›</span>
            <span><?= htmlspecialchars($notice['title']) ?></span>
        </div>
    </div>
    
    <!-- 공지사항 본문 -->
    <div class="notice-container">
        <!-- 헤더 -->
        <div class="notice-header <?= $notice['is_featured'] ? 'featured' : '' ?>">
            <?php if ($notice['is_featured']): ?>
                <div class="notice-featured-badge">
                    <i class="fas fa-star"></i> 중요 공지
                </div>
            <?php endif; ?>
            
            <h1 class="notice-title"><?= htmlspecialchars($notice['title']) ?></h1>
            
            <div class="notice-meta">
                <div class="meta-item company-meta-with-avatar">
                    <div class="company-avatar-small">
                        <?php 
                        $companyLogo = $notice['company_logo'] ?? null;
                        $companyName = $notice['company_name'] ?? '기업';
                        
                        if ($companyLogo): ?>
                            <img src="<?= htmlspecialchars($companyLogo) ?>" 
                                 alt="<?= htmlspecialchars($companyName) ?>" 
                                 loading="lazy">
                        <?php else: ?>
                            <?= mb_substr($companyName, 0, 1) ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <strong><?= htmlspecialchars($companyName) ?></strong>
                    </div>
                </div>
                <div class="meta-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span><?= date('Y년 m월 d일 H:i', strtotime($notice['created_at'])) ?></span>
                </div>
                <?php if ($notice['created_at'] !== $notice['updated_at']): ?>
                <div class="meta-item">
                    <i class="fas fa-edit"></i>
                    <span>수정: <?= date('Y-m-d H:i', strtotime($notice['updated_at'])) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- 내용 -->
        <div class="notice-content">
            <div class="content-body">
                <?= $notice['content'] ?>
            </div>
        </div>
        
        <!-- 푸터 -->
        <div class="notice-footer">
            <div class="notice-stats">
                <div class="stat-item">
                    <i class="fas fa-eye"></i>
                    <span>조회 <?= number_format($notice['view_count']) ?></span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-comments"></i>
                    <span>댓글 <?= number_format($notice['comment_count'] ?? 0) ?></span>
                </div>
            </div>
            
            <div class="notice-actions">
                <!-- 공유 버튼 -->
                <button type="button" class="btn btn-primary" onclick="openShareModal()">
                    <i class="fas fa-share-alt"></i> 공유하기
                </button>
                
                <!-- 목록으로 버튼 -->
                <a href="/notices" class="btn btn-secondary">
                    <i class="fas fa-list"></i> 목록으로
                </a>
                
                <!-- 수정/삭제 버튼 (소유자만) -->
                <?php if ($canEdit): ?>
                    <a href="/notices/<?= $notice['id'] ?>/edit" class="btn btn-warning">
                        <i class="fas fa-edit"></i> 수정
                    </a>
                    <button type="button" class="btn btn-danger" onclick="deleteNotice(<?= $notice['id'] ?>)">
                        <i class="fas fa-trash"></i> 삭제
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- 댓글 섹션 -->
    <div class="comments-section">
        <h3>
            <i class="fas fa-comments"></i>
            댓글 <span id="commentCount"><?= number_format($notice['comment_count'] ?? 0) ?></span>개
        </h3>
        
        <!-- 댓글 작성 폼 -->
        <?php if ($isLoggedIn): ?>
        <div class="comment-form">
            <textarea id="commentContent" 
                      placeholder="댓글을 입력하세요... (최소 2자 이상)"
                      maxlength="1000"></textarea>
            <div class="comment-form-actions">
                <button type="button" class="btn btn-secondary" onclick="clearComment()">
                    <i class="fas fa-times"></i> 취소
                </button>
                <button type="button" class="btn btn-primary" onclick="submitComment()">
                    <i class="fas fa-paper-plane"></i> 댓글 작성
                </button>
            </div>
        </div>
        <?php else: ?>
        <div class="comment-form">
            <p style="text-align: center; color: #718096; margin: 20px 0;">
                댓글을 작성하려면 <a href="/auth/login?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" style="color: #2563eb;">로그인</a>이 필요합니다.
            </p>
        </div>
        <?php endif; ?>
        
        <!-- 댓글 목록 -->
        <div class="comments-list" id="commentsList">
            <?php if (!empty($comments)): ?>
                <?php foreach ($comments as $comment): ?>
                    <?= renderComment($comment, $currentUserId, 0) ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; color: #718096;">
                    <i class="fas fa-comment-slash" style="font-size: 2rem; margin-bottom: 15px; color: #cbd5e0;"></i>
                    <p>첫 번째 댓글을 작성해보세요!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- 공유 모달 -->
<div id="shareModal" class="share-modal" onclick="closeShareModal()">
    <div class="share-modal-content" onclick="event.stopPropagation()">
        <div class="share-modal-header">
            <h3>공지사항 공유</h3>
            <button class="share-modal-close" onclick="closeShareModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="share-options">
            <a href="#" class="share-option facebook" onclick="shareToFacebook()">
                <div class="share-option-icon">
                    <i class="fab fa-facebook-f"></i>
                </div>
                <div class="share-option-text">페이스북</div>
            </a>
            
            <a href="#" class="share-option twitter" onclick="shareToTwitter()">
                <div class="share-option-icon">
                    <i class="fab fa-twitter"></i>
                </div>
                <div class="share-option-text">트위터</div>
            </a>
            
            <a href="#" class="share-option kakao" onclick="shareToKakao()">
                <div class="share-option-icon">
                    <i class="fas fa-comment"></i>
                </div>
                <div class="share-option-text">카카오톡</div>
            </a>
            
            <a href="#" class="share-option link" onclick="copyShareUrl()">
                <div class="share-option-icon">
                    <i class="fas fa-link"></i>
                </div>
                <div class="share-option-text">링크 복사</div>
            </a>
        </div>
        
        <div class="share-url-section">
            <h4>공지사항 주소</h4>
            <div class="share-url-input">
                <input type="text" 
                       id="shareUrl" 
                       value="<?= $pageUrl ?>" 
                       readonly>
                <button type="button" class="btn btn-primary" onclick="copyShareUrl()">
                    <i class="fas fa-copy"></i> 복사
                </button>
            </div>
        </div>
    </div>
</div>

<?php
// 댓글 렌더링 함수
function renderComment($comment, $currentUserId, $depth = 0) {
    $isReply = $depth > 0;
    $canEditComment = $currentUserId && ($currentUserId == $comment['user_id']);
    
    $html = '<div class="comment-item' . ($isReply ? ' reply' : '') . '" data-comment-id="' . $comment['id'] . '">';
    
    // 댓글 헤더
    $html .= '<div class="comment-header">';
    $html .= '<div class="comment-author">';
    
    // 작성자 아바타
    $profileImage = $comment['profile_image'] ?? null;
    $authorName = $comment['nickname'] ?? '익명';
    
    $html .= '<div class="comment-author-avatar">';
    if ($profileImage) {
        $html .= '<img src="' . htmlspecialchars($profileImage) . '" alt="' . htmlspecialchars($authorName) . '" loading="lazy">';
    } else {
        $html .= mb_substr($authorName, 0, 1);
    }
    $html .= '</div>';
    
    // 작성자 정보
    $html .= '<div class="comment-author-info">';
    $html .= '<div class="comment-author-name">' . htmlspecialchars($authorName) . '</div>';
    $html .= '<div class="comment-date">' . date('Y-m-d H:i', strtotime($comment['created_at'])) . '</div>';
    $html .= '</div>';
    $html .= '</div>';
    
    // 댓글 액션 버튼
    $html .= '<div class="comment-actions">';
    if (!$isReply) {
        $html .= '<button type="button" class="comment-action-btn" onclick="showReplyForm(' . $comment['id'] . ')">답글</button>';
    }
    if ($canEditComment) {
        $html .= '<button type="button" class="comment-action-btn" onclick="editComment(' . $comment['id'] . ')">수정</button>';
        $html .= '<button type="button" class="comment-action-btn" onclick="deleteComment(' . $comment['id'] . ')">삭제</button>';
    }
    $html .= '</div>';
    $html .= '</div>';
    
    // 댓글 내용
    $html .= '<div class="comment-content" id="commentContent_' . $comment['id'] . '">';
    $html .= nl2br(htmlspecialchars($comment['content']));
    $html .= '</div>';
    
    // 댓글 수정 폼 (숨김)
    if ($canEditComment) {
        $html .= '<div class="comment-edit-form" id="editForm_' . $comment['id'] . '">';
        $html .= '<textarea id="editContent_' . $comment['id'] . '">' . htmlspecialchars($comment['content']) . '</textarea>';
        $html .= '<div class="comment-edit-actions">';
        $html .= '<button type="button" class="btn btn-secondary" onclick="cancelEditComment(' . $comment['id'] . ')">취소</button>';
        $html .= '<button type="button" class="btn btn-primary" onclick="updateComment(' . $comment['id'] . ')">저장</button>';
        $html .= '</div>';
        $html .= '</div>';
    }
    
    // 답글 작성 폼 (숨김)
    if (!$isReply) {
        $html .= '<div class="reply-form" id="replyForm_' . $comment['id'] . '">';
        $html .= '<textarea id="replyContent_' . $comment['id'] . '" placeholder="답글을 입력하세요..."></textarea>';
        $html .= '<div class="reply-form-actions">';
        $html .= '<button type="button" class="btn btn-secondary" onclick="hideReplyForm(' . $comment['id'] . ')">취소</button>';
        $html .= '<button type="button" class="btn btn-primary" onclick="submitReply(' . $comment['id'] . ')">답글 작성</button>';
        $html .= '</div>';
        $html .= '</div>';
    }
    
    $html .= '</div>';
    
    // 답글들 렌더링
    if (!empty($comment['replies'])) {
        foreach ($comment['replies'] as $reply) {
            $html .= renderComment($reply, $currentUserId, $depth + 1);
        }
    }
    
    return $html;
}
?>

<script>
// 공지사항 상세보기 페이지 JavaScript
document.addEventListener('DOMContentLoaded', function() {
    console.log('📢 공지사항 상세보기 페이지 로드 완료');
    
    // 페이지 조회수 증가 (비동기)
    updateViewCount();
    
    // ESC 키로 모달 닫기
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeShareModal();
        }
    });
});

// 조회수 업데이트
function updateViewCount() {
    const noticeId = <?= $notice['id'] ?>;
    
    fetch(`/api/notices/${noticeId}/view`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            csrf_token: '<?= $_SESSION['csrf_token'] ?>'
        })
    }).catch(error => {
        console.warn('조회수 업데이트 실패:', error);
    });
}

// 댓글 작성
function submitComment() {
    const content = document.getElementById('commentContent').value.trim();
    
    if (content.length < 2) {
        alert('댓글은 2자 이상 입력해주세요.');
        return;
    }
    
    if (content.length > 1000) {
        alert('댓글은 1000자 이하로 입력해주세요.');
        return;
    }
    
    const noticeId = <?= $notice['id'] ?>;
    
    fetch('/api/notice-comments', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            notice_id: noticeId,
            content: content,
            csrf_token: '<?= $_SESSION['csrf_token'] ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 댓글 목록 새로고침
            location.reload();
        } else {
            throw new Error(data.message || '댓글 작성 중 오류가 발생했습니다.');
        }
    })
    .catch(error => {
        console.error('댓글 작성 오류:', error);
        alert('댓글 작성 중 오류가 발생했습니다: ' + error.message);
    });
}

// 댓글 내용 지우기
function clearComment() {
    document.getElementById('commentContent').value = '';
}

// 답글 폼 표시
function showReplyForm(commentId) {
    // 다른 답글 폼들 숨기기
    const allReplyForms = document.querySelectorAll('.reply-form');
    allReplyForms.forEach(form => {
        if (form.id !== `replyForm_${commentId}`) {
            form.style.display = 'none';
        }
    });
    
    const replyForm = document.getElementById(`replyForm_${commentId}`);
    replyForm.style.display = 'block';
    document.getElementById(`replyContent_${commentId}`).focus();
}

// 답글 폼 숨기기
function hideReplyForm(commentId) {
    const replyForm = document.getElementById(`replyForm_${commentId}`);
    replyForm.style.display = 'none';
    document.getElementById(`replyContent_${commentId}`).value = '';
}

// 답글 작성
function submitReply(parentId) {
    const content = document.getElementById(`replyContent_${parentId}`).value.trim();
    
    if (content.length < 2) {
        alert('답글은 2자 이상 입력해주세요.');
        return;
    }
    
    const noticeId = <?= $notice['id'] ?>;
    
    fetch('/api/notice-comments', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            notice_id: noticeId,
            parent_id: parentId,
            content: content,
            csrf_token: '<?= $_SESSION['csrf_token'] ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 댓글 목록 새로고침
            location.reload();
        } else {
            throw new Error(data.message || '답글 작성 중 오류가 발생했습니다.');
        }
    })
    .catch(error => {
        console.error('답글 작성 오류:', error);
        alert('답글 작성 중 오류가 발생했습니다: ' + error.message);
    });
}

// 댓글 수정 모드
function editComment(commentId) {
    const contentDiv = document.getElementById(`commentContent_${commentId}`);
    const editForm = document.getElementById(`editForm_${commentId}`);
    
    contentDiv.style.display = 'none';
    editForm.style.display = 'block';
    document.getElementById(`editContent_${commentId}`).focus();
}

// 댓글 수정 취소
function cancelEditComment(commentId) {
    const contentDiv = document.getElementById(`commentContent_${commentId}`);
    const editForm = document.getElementById(`editForm_${commentId}`);
    
    contentDiv.style.display = 'block';
    editForm.style.display = 'none';
}

// 댓글 수정 저장
function updateComment(commentId) {
    const content = document.getElementById(`editContent_${commentId}`).value.trim();
    
    if (content.length < 2) {
        alert('댓글은 2자 이상 입력해주세요.');
        return;
    }
    
    fetch(`/api/notice-comments/${commentId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            content: content,
            csrf_token: '<?= $_SESSION['csrf_token'] ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            throw new Error(data.message || '댓글 수정 중 오류가 발생했습니다.');
        }
    })
    .catch(error => {
        console.error('댓글 수정 오류:', error);
        alert('댓글 수정 중 오류가 발생했습니다: ' + error.message);
    });
}

// 댓글 삭제
function deleteComment(commentId) {
    if (!confirm('댓글을 삭제하시겠습니까?')) {
        return;
    }
    
    fetch(`/api/notice-comments/${commentId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            csrf_token: '<?= $_SESSION['csrf_token'] ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            throw new Error(data.message || '댓글 삭제 중 오류가 발생했습니다.');
        }
    })
    .catch(error => {
        console.error('댓글 삭제 오류:', error);
        alert('댓글 삭제 중 오류가 발생했습니다: ' + error.message);
    });
}

// 공지사항 삭제
function deleteNotice(noticeId) {
    if (!confirm('정말로 이 공지사항을 삭제하시겠습니까?\n삭제된 공지사항은 복구할 수 없습니다.')) {
        return;
    }
    
    fetch(`/api/notices/${noticeId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            csrf_token: '<?= $_SESSION['csrf_token'] ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('공지사항이 삭제되었습니다.');
            window.location.href = '/notices';
        } else {
            throw new Error(data.message || '삭제 중 오류가 발생했습니다.');
        }
    })
    .catch(error => {
        console.error('삭제 오류:', error);
        alert('삭제 중 오류가 발생했습니다: ' + error.message);
    });
}

// 공유 모달 열기
function openShareModal() {
    document.getElementById('shareModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

// 공유 모달 닫기
function closeShareModal() {
    document.getElementById('shareModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// 페이스북 공유
function shareToFacebook() {
    const url = encodeURIComponent('<?= $pageUrl ?>');
    const title = encodeURIComponent('<?= addslashes($notice['title']) ?>');
    
    window.open(
        `https://www.facebook.com/sharer/sharer.php?u=${url}&t=${title}`,
        'facebook-share',
        'width=600,height=400,scrollbars=yes'
    );
}

// 트위터 공유
function shareToTwitter() {
    const url = encodeURIComponent('<?= $pageUrl ?>');
    const text = encodeURIComponent('<?= addslashes($notice['title']) . ' - 탑마케팅 공지사항' ?>');
    
    window.open(
        `https://twitter.com/intent/tweet?url=${url}&text=${text}`,
        'twitter-share',
        'width=600,height=400,scrollbars=yes'
    );
}

// 카카오톡 공유 (카카오 SDK 필요)
function shareToKakao() {
    // 카카오 SDK가 로드되지 않은 경우 URL 복사로 대체
    if (typeof Kakao === 'undefined') {
        copyShareUrl();
        alert('카카오톡 공유는 현재 지원하지 않습니다. URL이 복사되었습니다.');
        return;
    }
    
    Kakao.Link.sendDefault({
        objectType: 'feed',
        content: {
            title: '<?= addslashes($notice['title']) ?>',
            description: '<?= addslashes($pageDescription) ?>',
            imageUrl: '<?= $pageImage ?>',
            link: {
                webUrl: '<?= $pageUrl ?>',
                mobileWebUrl: '<?= $pageUrl ?>'
            }
        }
    });
}

// URL 복사
function copyShareUrl() {
    const urlInput = document.getElementById('shareUrl');
    urlInput.select();
    urlInput.setSelectionRange(0, 99999);
    
    try {
        document.execCommand('copy');
        alert('URL이 클립보드에 복사되었습니다.');
    } catch (err) {
        console.error('URL 복사 실패:', err);
        alert('URL 복사에 실패했습니다. 수동으로 복사해주세요.');
    }
}

// Web Share API 사용 (지원하는 브라우저에서)
if (navigator.share) {
    function nativeShare() {
        navigator.share({
            title: '<?= addslashes($notice['title']) ?>',
            text: '<?= addslashes($pageDescription) ?>',
            url: '<?= $pageUrl ?>'
        }).catch(err => {
            console.log('공유 취소됨:', err);
        });
    }
}
</script>