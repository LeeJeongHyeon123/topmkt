<?php include SRC_PATH . '/views/templates/header.php'; ?>

<div class="post-detail-container">
    <div class="post-header">
        <h2 class="post-title"><?= $post['title'] ?></h2>
        <div class="post-meta">
            <span class="post-author"><?= $post['author'] ?></span>
            <span class="post-date"><?= $post['created_at'] ?></span>
        </div>
    </div>
    
    <div class="post-content">
        <?= $post['content'] ?>
    </div>
    
    <div class="post-actions">
        <a href="/posts" class="btn btn-secondary">목록으로</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- 실제 구현에서는 게시글 작성자만 수정/삭제 가능하도록 검사 -->
            <a href="/posts/<?= $post['id'] ?>/edit" class="btn btn-secondary">수정</a>
            <form action="/posts/<?= $post['id'] ?>" method="post" class="delete-form" onsubmit="return confirm('정말 삭제하시겠습니까?');">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="btn btn-danger">삭제</button>
            </form>
        <?php endif; ?>
    </div>
    
    <div class="comments-section">
        <h3>댓글 (<?= count($comments) ?>)</h3>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="comment-form">
                <form action="/posts/<?= $post['id'] ?>/comments" method="post">
                    <div class="form-group">
                        <textarea name="content" placeholder="댓글을 작성해주세요." required></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">댓글 작성</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
        
        <div class="comments-list">
            <?php if (empty($comments)): ?>
                <div class="comments-empty">
                    <p>아직 댓글이 없습니다.</p>
                </div>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment-item">
                        <div class="comment-meta">
                            <span class="comment-author"><?= $comment['author'] ?></span>
                            <span class="comment-date"><?= $comment['created_at'] ?></span>
                        </div>
                        <div class="comment-content">
                            <?= $comment['content'] ?>
                        </div>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <!-- 실제 구현에서는 댓글 작성자만 수정/삭제 가능하도록 검사 -->
                            <div class="comment-actions">
                                <button class="btn-link btn-edit-comment" data-id="<?= $comment['id'] ?>">수정</button>
                                <form action="/comments/<?= $comment['id'] ?>" method="post" class="delete-form" onsubmit="return confirm('정말 삭제하시겠습니까?');">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="btn-link btn-delete-comment">삭제</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include SRC_PATH . '/views/templates/footer.php'; ?> 